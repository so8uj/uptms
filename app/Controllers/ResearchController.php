<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\CompletionRequest;
use App\Models\Evaluation;
use App\Models\Milestone;
use App\Models\Research;
use App\Models\ResearchActivity;
use App\Models\Tag;
use App\Models\Team;
use App\Models\User;
use App\Services\Validation;

/**
 * One controller for research, used by four API files:
 *   api/research.php        admin: the whole catalog, add / edit / review / delete
 *   api/library.php         everyone logged in: the read-only library
 *   api/my_research.php     student and teacher: their own submissions
 *   api/research_options.php  the dropdown lists for the forms
 *
 * It only works with data and returns arrays (no HTML, no redirects).
 * $actor is the logged in user: ['id' => .., 'role' => ..., 'name' => ...] from Auth::currentUser().
 *
 * Who may do what:
 *   admin    everything. Research the admin adds is approved straight away (ongoing or completed).
 *   student  asks for a topic: status "requested", and the admin decides. Only students submit research.
 *   teacher  never submits research: a teacher supervises the research the admin assigned.
 * Everything a student submits waits for the admin, because the thesis committee decides what stays.
 */
class ResearchController {

    private const PER_PAGE = 10;
    private const MAX_TAGS = 10;

    // The statuses an owner may still edit (nothing was approved yet). "correction" = the admin sent it back.
    // "rejected" is not here: a rejected research is refused for good, nothing more can be done with it.
    private const OPEN = ['requested', 'pending', 'correction'];

    // The statuses an owner may withdraw: the same, and also "approved" (the supervisor is not assigned yet).
    private const WITHDRAW = ['requested', 'pending', 'correction', 'approved'];

    // ---------------------------------------------------------------------------------
    // Lists
    // ---------------------------------------------------------------------------------

    // Admin: the whole catalog. status = '' (all), one status, 'review' (requested + pending) or 'progress' (approved + ongoing).
    public static function adminIndex($query){

        $status = $query['status'] ?? '';
        $statuses = match (true) {
            $status === 'review'                    => ['requested', 'pending'],
            $status === 'progress'                  => ['approved', 'ongoing'],
            in_array($status, Research::STATUSES, true) => [$status],
            default                                 => [],
        };

        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses], $query);
        $result['counts'] = (new Research())->statusCounts();

        // The Ongoing Research page tracks the work: progress, the last activity, the last rating and the request to finish.
        if ($status === 'progress') {
            $result['research'] = self::withTracking($result['research']);
        }

        return $result;
    }

    // Adds to each research: milestones, tasks, done, percent, evaluations, last_rating, last_activity, quiet_days, finish_status.
    private static function withTracking(array $rows): array
    {
        $ids = array_map(fn($row) => (int) $row['id'], $rows);

        $progress = (new Milestone())->progressFor($ids);
        $evaluations = new Evaluation();
        $counts = $evaluations->countsFor($ids);
        $ratings = $evaluations->latestRatings($ids);
        $lastTimes = (new ResearchActivity())->lastTimes($ids);
        $finish = (new CompletionRequest())->activeStatuses($ids);

        foreach ($rows as $i => $row) {
            $id = (int) $row['id'];
            $numbers = $progress[$id] ?? ['milestones' => 0, 'tasks' => 0, 'done' => 0];
            $last = $lastTimes[$id] ?? $row['created_at'];

            $rows[$i]['milestones'] = $numbers['milestones'];
            $rows[$i]['tasks'] = $numbers['tasks'];
            $rows[$i]['done'] = $numbers['done'];
            $rows[$i]['percent'] = $numbers['tasks'] > 0 ? (int) round($numbers['done'] * 100 / $numbers['tasks']) : 0;
            $rows[$i]['evaluations'] = $counts[$id] ?? 0;
            $rows[$i]['last_rating'] = $ratings[$id] ?? null;
            $rows[$i]['last_activity'] = $last;
            $rows[$i]['quiet_days'] = (int) floor((time() - strtotime($last)) / 86400);   // days since anything happened
            $rows[$i]['finish_status'] = $finish[$id] ?? null;
        }

        return $rows;
    }

    // The requests to finish a research that wait for the administrator, with what the admin needs to decide.
    public static function completionList(){

        $research = new Research();
        $requests = (new CompletionRequest())->waitingForAdmin();
        $progress = (new Milestone())->progressFor(array_map(fn($row) => (int) $row['research_id'], $requests));

        foreach ($requests as $i => $request) {
            $item = $research->find((int) $request['research_id']);
            $numbers = $progress[(int) $request['research_id']] ?? ['milestones' => 0, 'tasks' => 0, 'done' => 0];

            $requests[$i]['supervisor_name'] = $item['supervisor_name'] ?? null;
            $requests[$i]['team'] = array_map(fn($student) => $student['name'], $item['students'] ?? []);
            $requests[$i]['milestones'] = $numbers['milestones'];
            $requests[$i]['tasks'] = $numbers['tasks'];
            $requests[$i]['done'] = $numbers['done'];
        }

        return ['requests' => $requests];
    }

    /**
     * The library: only completed research, plus ongoing research when include_ongoing is on.
     * Requested, pending and rejected research is never shown here, whoever asks.
     */
    public static function libraryIndex($query){

        $includeOngoing = ($query['include_ongoing'] ?? '') === '1';
        $statuses = $includeOngoing ? ['completed', 'ongoing'] : ['completed'];

        $model = new Research();
        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses], $query);

        // Only what a reader needs: nothing about who submitted it or why it was rejected.
        $result['research'] = array_map(fn($row) => array_diff_key($row, array_flip(
            ['review_note', 'reviewed_at', 'target_status', 'created_by', 'creator_name']
        )), $result['research']);

        $counts = $model->statusCounts();
        $result['counts'] = ['completed' => $counts['completed'], 'ongoing' => $counts['ongoing']];
        $result['categories'] = $model->categoryCounts();
        $result['years'] = $model->years();

        return $result;
    }

    // One research for the "view" page. Only completed or ongoing research can be opened, whoever asks.
    public static function libraryOne($id){

        $item = (new Research())->find((int) $id);

        if (!$item || !in_array($item['status'], ['completed', 'ongoing'], true)) {
            return null;
        }

        return array_diff_key($item, array_flip(
            ['review_note', 'reviewed_at', 'target_status', 'created_by', 'creator_name']
        ));
    }

    // The history of one research, for the admin. Returns null when the research does not exist.
    public static function activity($actor, $id){

        $research = (new Research())->find((int) $id);

        if (!$research || $actor['role'] !== 'admin') {
            return null;
        }

        return ['activity' => (new ResearchActivity())->forResearch((int) $research['id'])];
    }

    // The numbers on the menu badges. Each panel gets only its own numbers.
    public static function menuCounts($actor){

        $model = new Research();

        if ($actor['role'] === 'admin') {
            $counts = $model->statusCounts();

            return [
                'new_requests'    => $counts['requested'] + $counts['pending'],   // waiting for the admin's decision
                'team_requests'   => (new Team())->requestedCount() + (new Team())->changeRequestCount(),   // groups/teams and member changes that students asked for
                'need_supervisor' => $counts['approved'],                         // approved, waiting for a supervisor
                'in_progress'     => $counts['approved'] + $counts['ongoing'],    // the Ongoing Research page
                'completion'      => count((new CompletionRequest())->waitingForAdmin()),   // requests to finish a research
            ];
        }

        $counts = $model->statusCountsFor((int) $actor['id']);
        $requests = new CompletionRequest();

        return [
            'requested'  => $counts['requested'],
            'ongoing'    => $counts['ongoing'],
            // a teacher: requests to finish that wait for the decision. A student: requests that wait for the confirmation.
            'completion' => $actor['role'] === 'teacher' ? $requests->countForTeacher((int) $actor['id']) : $requests->countForStudent((int) $actor['id']),
        ];
    }

    // A student's or teacher's own research (submitted, supervised or worked on), with every status.
    public static function mine($actor, $query){

        $status = $query['status'] ?? '';
        $statuses = in_array($status, Research::STATUSES, true) ? [$status] : [];

        $result = self::page(self::filtersFrom($query) + ['statuses' => $statuses, 'mine' => $actor['id']], $query);

        $result['counts'] = (new Research())->statusCountsFor((int) $actor['id']);

        // The progress of each research (tasks done, the request to finish ...) for the progress column.
        $result['research'] = self::withTracking($result['research']);

        // Only students ask for a topic.
        $result['can_add'] = $actor['role'] === 'student';

        return $result;
    }

    // The search and filters shared by the lists.
    private static function filtersFrom($query): array
    {
        return [
            'q'           => trim($query['q'] ?? ''),
            'category_id' => (int) ($query['category_id'] ?? 0),
            'tag_ids'     => array_values(array_filter(array_map('intval', explode(',', (string) ($query['tag_ids'] ?? ''))))),
            'type'        => $query['type'] ?? '',
            'year'        => (int) ($query['year'] ?? 0),
        ];
    }

    private static function page(array $filters, $query): array
    {
        $model = new Research();
        $total = $model->count($filters);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min(max(1, (int) ($query['page'] ?? 1)), $pages);

        return [
            'research' => $model->paginate($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'perPage'  => self::PER_PAGE,
        ];
    }

    // ---------------------------------------------------------------------------------
    // One research
    // ---------------------------------------------------------------------------------

    // For the edit form. Admin can open any; others only their own research that can still be edited.
    public static function find($actor, $id){

        $item = (new Research())->find((int) $id);

        if (!$item) {
            return null;
        }

        if ($actor['role'] !== 'admin' && !self::ownerMayChange($actor, $item)) {
            return null;
        }

        return $item;
    }

    /**
     * One research for the Research Details page of a teacher or a student, in any status
     * (the library only has completed and ongoing research). Only the people of that research may open it:
     * the one who made it, a student of its team and its supervisor. 'can_edit' tells if the owner can still edit it.
     */
    public static function viewMine($actor, $id){

        $model = new Research();
        $item  = $model->find((int) $id);
        $me    = (int) $actor['id'];

        if (!$item) {
            return null;
        }

        $isMine = (int) $item['created_by'] === $me
            || (int) ($item['supervisor_id'] ?? 0) === $me
            || $model->isMember((int) $item['id'], $me);

        if (!$isMine) {
            return null;
        }

        $item['can_edit'] = self::ownerMayChange($actor, $item);

        return $item;
    }

    private static function ownerMayChange($actor, $item): bool
    {
        return (int) $item['created_by'] === (int) $actor['id'] && in_array($item['status'], self::OPEN, true);
    }

    private static function ownerMayWithdraw($actor, $item): bool
    {
        return (int) $item['created_by'] === (int) $actor['id'] && in_array($item['status'], self::WITHDRAW, true);
    }

    // ---------------------------------------------------------------------------------
    // The team (student side)
    // ---------------------------------------------------------------------------------

    // The dropdown lists for the forms. Only the admin also gets the teachers (for the supervisor).
    public static function options($actor){

        $options = [
            'categories' => (new Category())->options(),
            'tags'       => (new Tag())->options(),
            'types'      => Research::TYPES,
        ];

        if ($actor['role'] === 'admin' || Auth::isResearchManager()) {
            $options['teachers'] = (new User())->teacherOptions();
        }

        return $options;
    }

    // ---------------------------------------------------------------------------------
    // Save (add or edit)
    // ---------------------------------------------------------------------------------

    /**
     * $id is null when adding and the research id when editing.
     * Returns ['success' => bool, 'errors' => [field => [messages]], 'message' => text for the toast]
     */
    public static function save($actor, $request, $id = null){

        $role  = $actor['role'];
        $model = new Research();
        $current = null;

        // ---------- who may do this ----------

        if ($role === 'teacher') {
            return self::fail('Only students submit research. A teacher supervises the research the administrator assigns.');
        }

        if ($id !== null) {
            $current = $model->find((int) $id);

            if (!$current || ($role !== 'admin' && !self::ownerMayChange($actor, $current))) {
                return self::fail('This research cannot be changed.');
            }
        }

        // ---------- the rules ----------

        $rules = [
            'title'       => 'required|max:255',
            'abstract'    => 'required|min:20|max:5000',
            'type'        => 'required|in:' . implode(',', Research::TYPES),
            'category_id' => 'required|integer',
        ];

        if ($role === 'admin') {
            $rules['year'] = 'required|integer|between:1990,2100';
        }
        if ($role === 'admin' && $id === null) {
            $rules['status'] = 'required|in:ongoing,completed';
        }
        if ($role === 'admin' && $id !== null) {
            $rules['status'] = 'in:ongoing,completed';
        }

        $validate = Validation::validate($request, $rules, [
            'category_id'   => 'Category',
            'target_status' => 'Progress',
        ]);
        $errors = $validate['errors'];

        // ---------- the lists and the dropdowns (they can be tampered with, so check them) ----------

        if (empty($errors['category_id']) && !(new Category())->isActive((int) $request['category_id'])) {
            $errors['category_id'][] = 'Please choose a valid category.';
        }

        $tagIds = self::ids($request['tags'] ?? []);
        if (count($tagIds) > self::MAX_TAGS) {
            $errors['tags'][] = 'Please choose at most ' . self::MAX_TAGS . ' tags.';
        } elseif ($tagIds && (new Tag())->countActive($tagIds) !== count($tagIds)) {
            $errors['tags'][] = 'Please choose valid tags.';
        }

        $teams = new Team();
        $supervisorId = null;
        $teamId = null;
        $studentIds = null;   // null = keep the current students

        // A student asks with their own team. A rejected request that is sent again also needs its team to be free.
        if ($role === 'student') {
            $teamId = $current ? ($current['team_id'] !== null ? (int) $current['team_id'] : null) : $teams->openTeamOf((int) $actor['id']);
            $team = $teamId !== null ? $teams->find($teamId) : null;

            if ($current === null && $team === null) {
                $waiting = $teams->paginate(['member_id' => (int) $actor['id'], 'status' => 'requested'], 1, 0);

                return self::fail($waiting
                    ? 'Your group/team is waiting for the administrator to approve it.'
                    : 'Please request a group/team of ' . Team::minSize() . ' to ' . Team::maxSize() . ' students first. A research is always done by a group/team.');
            }
            if ($team !== null && ($team['status'] !== 'open' || $teams->isBusy($teamId, (int) $id))) {
                return self::fail('Your group/team is already working on another research.');
            }
            if ($team !== null && $current === null) {
                $studentIds = array_map(fn($member) => (int) $member['id'], $team['members']);
            }
        }

        if ($role === 'admin') {
            $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
            if ($supervisorId !== null && !self::isActiveTeacher($supervisorId)) {
                $errors['supervisor_id'][] = 'Please choose a valid supervisor.';
            }

            // The admin picks a team when adding a research: the students of the research are the members of the team.
            if ($id === null) {
                $teamId = self::idOrNull($request['team_id'] ?? null);

                if ($teamId !== null) {
                    $team = $teams->find($teamId);

                    if (!$team || $team['status'] !== 'open') {
                        $errors['team_id'][] = 'Please choose an open group/team.';
                    } elseif ($teams->isBusy($teamId)) {
                        $errors['team_id'][] = 'This group/team is already working on another research.';
                    } else {
                        $studentIds = array_map(fn($member) => (int) $member['id'], $team['members']);
                    }
                } elseif (($request['status'] ?? '') === 'ongoing') {
                    $errors['team_id'][] = 'An ongoing research needs a group/team.';
                }
            }

            // A research that is still waiting for review changes status only through review().
            if ($current && isset($request['status']) && !in_array($current['status'], ['ongoing', 'completed'], true)
                && $request['status'] !== $current['status']) {
                $errors['status'][] = 'Approve or reject this research on the Pending page first.';
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        // ---------- save ----------

        $data = [
            'title'         => trim($request['title']),
            'abstract'      => trim($request['abstract']),
            'type'          => $request['type'],
            'category_id'   => (int) $request['category_id'],
            'year'          => $role === 'student' ? (int) ($current['year'] ?? date('Y')) : (int) $request['year'],
            'supervisor_id' => $supervisorId,
        ];

        if ($id === null) {
            [$status, $target, $supervisor, $students] = match ($role) {
                'admin'   => [$request['status'], $request['status'], $supervisorId, $studentIds ?? []],
                default   => ['requested', 'ongoing', null, $studentIds],   // a student's request: the members of the team
            };

            // array_merge (not +) so these values replace the ones already in $data, such as supervisor_id.
            $newId = $model->create(array_merge($data, [
                'supervisor_id' => $supervisor,
                'team_id'       => $teamId,
                'status'        => $status,
                'target_status' => $target,
                'created_by'    => (int) $actor['id'],
            ]), $tagIds, $students);

            $action = match ($role) {
                'admin'   => 'added',
                default   => 'requested',
            };
            self::log($actor, $newId, $action, $role === 'admin' ? 'As ' . $status : null);

            if ($role === 'admin' && $supervisor !== null) {
                self::log($actor, $newId, 'supervisor_assigned', self::nameOf($supervisor));
            }

            $message = $role === 'admin' ? 'Research added.' : 'Your research was sent to the administrator for approval.';

            return ['success' => true, 'errors' => [], 'message' => $message];
        }

        // Editing: the owner cannot change who supervises or which students work on it.
        if ($role !== 'admin') {
            $data['supervisor_id'] = $current['supervisor_id'] !== null ? (int) $current['supervisor_id'] : null;
        }

        $model->update((int) $id, $data, $tagIds, null);

        if ($role === 'admin' && isset($request['status']) && in_array($current['status'], ['ongoing', 'completed'], true)) {
            $model->setStatus((int) $id, $request['status']);
            if ($request['status'] !== $current['status']) {
                self::log($actor, (int) $id, 'status_changed', 'From ' . $current['status'] . ' to ' . $request['status']);
            }
        }

        // A research the admin sent back for correction, improved by the owner, goes back to the administrator.
        if ($role !== 'admin' && $current['status'] === 'correction') {
            $model->resubmit((int) $id, $role === 'teacher' ? 'pending' : 'requested');
            self::log($actor, (int) $id, 'resubmitted');
        } else {
            self::log($actor, (int) $id, 'updated');
        }

        // The admin may also change the supervisor and the students on the form: write those changes down too.
        if ($role === 'admin') {
            if ($data['supervisor_id'] !== null && (int) $data['supervisor_id'] !== (int) $current['supervisor_id']) {
                self::log($actor, (int) $id, 'supervisor_assigned', self::nameOf((int) $data['supervisor_id']));
            }
        }

        return ['success' => true, 'errors' => [], 'message' => 'Research updated.'];
    }

    // ---------------------------------------------------------------------------------
    // Admin decisions
    // ---------------------------------------------------------------------------------

    /**
     * The admin's decisions.
     *   requested (a student's topic)   approve -> approved (the student adds the team), or reject
     *   pending (a teacher's research)  approve -> ongoing or completed, or reject
     *   approved                        assign -> the supervisor is chosen and the research becomes ongoing
     * $request = id, decision (approve | correction | reject | assign), status (ongoing | completed, for a teacher's research),
     *            supervisor_id, note (needed for reject)
     */
    public static function review($actor, $request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }
        if (!in_array($item['status'], ['requested', 'pending', 'approved'], true)) {
            return self::fail('This research was already reviewed.');
        }

        $decision = $request['decision'] ?? '';
        $errors = [];

        // ---------- approved: the admin assigns the supervisor ----------

        if ($item['status'] === 'approved') {
            if ($decision !== 'assign') {
                return self::fail('Please choose a supervisor for this research.');
            }

            $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
            if ($supervisorId === null || !self::isActiveTeacher($supervisorId)) {
                $errors['supervisor_id'][] = 'Please choose a supervisor.';
            }

            if ($errors) {
                return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
            }

            $model->review((int) $item['id'], 'ongoing', $supervisorId, $item['review_note']);
            self::log($actor, (int) $item['id'], 'supervisor_assigned', self::nameOf($supervisorId));

            return ['success' => true, 'errors' => [], 'message' => 'Supervisor assigned. The research is now ongoing.'];
        }

        // ---------- requested or pending ----------

        if ($decision === 'reject') {
            $note = trim($request['note'] ?? '');
            if ($note === '') {
                $errors['note'][] = 'Please write the reason for rejecting.';
            } elseif (mb_strlen($note) > 500) {
                $errors['note'][] = 'The reason must not be more than 500 characters.';
            }

            if ($errors) {
                return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
            }

            $model->review((int) $item['id'], 'rejected', $item['supervisor_id'] !== null ? (int) $item['supervisor_id'] : null, $note);
            self::log($actor, (int) $item['id'], 'rejected', $note);

            return ['success' => true, 'errors' => [], 'message' => 'Research rejected. Nothing more can be done with it.'];
        }

        // The admin sends it back: the owner can edit it and send it again. The note says what to correct.
        if ($decision === 'correction') {
            $note = trim($request['note'] ?? '');
            if ($note === '') {
                $errors['note'][] = 'Please write what must be corrected.';
            } elseif (mb_strlen($note) > 500) {
                $errors['note'][] = 'The note must not be more than 500 characters.';
            }

            if ($errors) {
                return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
            }

            $model->review((int) $item['id'], 'correction', $item['supervisor_id'] !== null ? (int) $item['supervisor_id'] : null, $note);
            self::log($actor, (int) $item['id'], 'correction', $note);

            return ['success' => true, 'errors' => [], 'message' => 'Sent back for correction.'];
        }

        if ($decision !== 'approve') {
            return self::fail('Unknown decision.');
        }

        // A student's topic is only accepted for now. The supervisor comes after the team is ready.
        if ($item['status'] === 'requested') {
            $model->review((int) $item['id'], 'approved', null, trim($request['note'] ?? '') ?: null);
            self::log($actor, (int) $item['id'], 'approved', trim($request['note'] ?? ''));

            return ['success' => true, 'errors' => [], 'message' => 'Topic approved. Now assign a supervisor.'];
        }

        $status = $request['status'] ?? $item['target_status'];
        if (!in_array($status, ['ongoing', 'completed'], true)) {
            $errors['status'][] = 'Please choose Ongoing or Completed.';
        }

        $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
        if ($supervisorId === null && $status === 'ongoing') {
            $errors['supervisor_id'][] = 'Please choose a supervisor for an ongoing research.';
        } elseif ($supervisorId !== null && !self::isActiveTeacher($supervisorId)) {
            $errors['supervisor_id'][] = 'Please choose a valid supervisor.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $model->review((int) $item['id'], $status, $supervisorId, trim($request['note'] ?? '') ?: null);
        self::log($actor, (int) $item['id'], 'approved', trim('As ' . $status . '. ' . trim($request['note'] ?? '')));
        if ($supervisorId !== null) {
            self::log($actor, (int) $item['id'], 'supervisor_assigned', self::nameOf($supervisorId));
        }

        return ['success' => true, 'errors' => [], 'message' => 'Research approved.'];
    }

    // Ongoing -> completed (the research is finished and joins the library as completed).
    public static function complete($actor, $request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }
        if ($item['status'] !== 'ongoing') {
            return self::fail('Only an ongoing research can be marked as completed.');
        }

        $model->setStatus((int) $item['id'], 'completed');
        (new CompletionRequest())->closeActive((int) $item['id'], (int) $actor['id']);
        self::log($actor, (int) $item['id'], 'completed');

        return ['success' => true, 'message' => 'Marked as completed.'];
    }

    /**
     * The administrator's decision on a request to finish a research (it came through the supervisor and the team).
     * $request = request_id, decision (approve | reject), note (needed for reject)
     */
    public static function completionDecision($actor, $request){

        $requests = new CompletionRequest();
        $item = $requests->find((int) ($request['request_id'] ?? 0));

        if (!$item || $item['status'] !== 'admin') {
            return self::fail('This request is not waiting for the administrator.');
        }

        if (($request['decision'] ?? '') === 'reject') {
            $note = trim($request['note'] ?? '');
            if ($note === '' || mb_strlen($note) > 500) {
                return ['success' => false, 'errors' => ['note' => ['Please write the reason (up to 500 characters).']], 'message' => 'Please write the reason.'];
            }

            $requests->setStatus((int) $item['id'], 'rejected', (int) $actor['id'], $note);
            self::log($actor, (int) $item['research_id'], 'completion_rejected', $note);

            return ['success' => true, 'errors' => [], 'message' => 'The request was rejected.'];
        }

        if (($request['decision'] ?? '') !== 'approve') {
            return self::fail('Unknown decision.');
        }

        (new Research())->setStatus((int) $item['research_id'], 'completed');
        $requests->setStatus((int) $item['id'], 'done', (int) $actor['id']);
        self::log($actor, (int) $item['research_id'], 'completed');

        return ['success' => true, 'errors' => [], 'message' => 'The research is completed.'];
    }

    // Admin deletes any research. An owner can only withdraw their own research until a supervisor is assigned.
    public static function delete($actor, $request){

        $model = new Research();
        $item  = $model->find((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }

        if ($actor['role'] !== 'admin' && !self::ownerMayWithdraw($actor, $item)) {
            return self::fail('This research cannot be deleted.');
        }

        self::log($actor, (int) $item['id'], $actor['role'] === 'admin' ? 'deleted' : 'withdrawn');
        $model->softDelete((int) $item['id']);

        return ['success' => true, 'message' => $actor['role'] === 'admin' ? 'Research deleted.' : 'Your request was withdrawn.'];
    }

    // ---------------------------------------------------------------------------------
    // Small helpers
    // ---------------------------------------------------------------------------------

    // Writes one line of the research history.
    private static function log($actor, int $researchId, string $action, ?string $detail = null): void
    {
        (new ResearchActivity())->add($researchId, (int) $actor['id'], $action, $detail);
    }

    private static function nameOf(int $userId): string
    {
        $user = (new User())->findActiveById($userId);

        return $user ? $user['name'] : 'a user';
    }

    private static function fail(string $message): array
    {
        return ['success' => false, 'errors' => [], 'message' => $message];
    }

    // A list of whole numbers from the request (anything else is thrown away).
    private static function ids($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, fn($v) => is_scalar($v) && ctype_digit((string) $v)))));
    }

    private static function idOrNull($value): ?int
    {
        return is_scalar($value) && ctype_digit((string) $value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function isActiveTeacher(int $id): bool
    {
        $teacher = (new User())->findByRole('teacher', $id);

        return $teacher !== null && (int) $teacher['status'] === 1;
    }

}
