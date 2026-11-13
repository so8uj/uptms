<?php

namespace App\Controllers;

use App\Models\Batch;
use App\Models\Approval;
use App\Models\Category;
use App\Models\Committee;
use App\Models\CompletionRequest;
use App\Models\Evaluation;
use App\Models\Milestone;
use App\Models\Research;
use App\Models\Setting;
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
    public static function adminIndex($query, $actor = null){

        $status = $query['status'] ?? '';
        $statuses = match (true) {
            $status === 'review'                    => ['requested', 'pending'],
            $status === 'progress'                  => ['approved', 'assigned', 'ongoing'],
            $status === 'working'                   => ['assigned', 'requested', 'correction', 'ongoing'],
            in_array($status, Research::STATUSES, true) => [$status],
            default                                 => [],
        };

        $filters = self::filtersFrom($query) + ['statuses' => $statuses];

        // A committee member sees only the research of the groups of the departments the committee works for.
        $departments = self::committeeDepartments($actor);
        if ($departments !== null) {
            $filters['department_ids'] = $departments ?: [0];
        }
        if ($status === 'working') {
            $filters['has_supervisor'] = 1;
        }

        $result = self::page($filters, $query);
        $result['counts'] = (new Research())->statusCounts();
        $result['research'] = self::withSignatures($result['research'], $actor);

        // The Ongoing Research page tracks the work: progress, the last activity, the last rating and the request to finish.
        if ($status === 'progress') {
            $result['research'] = self::withTracking($result['research']);
        }

        return $result;
    }

    // Who must sign this research and who signed: signers, needed, signed (for the project page and the details).
    public static function signingInfo(array $item): array
    {
        $row = self::withSignatures([$item], null)[0];

        return array_intersect_key($row, array_flip(['signers', 'needed', 'signed']));
    }

    /**
     * Adds to each research: signers (the committee teachers and how each one signed), needed (how many signatures
     * decide), signed (how many approved), can_sign (this person may sign it now), my_vote.
     */
    private static function withSignatures(array $rows, $actor): array
    {
        $ids = array_map(fn($row) => (int) $row['id'], $rows);
        $votes = (new Approval())->votesFor('topic', $ids);
        $committee = new Committee();
        $me = (int) ($actor['id'] ?? 0);
        $byDepartment = [];

        foreach ($rows as $i => $row) {
            $department = (new Research())->departmentId($row);
            $key = (string) $department;
            $byDepartment[$key] = $byDepartment[$key] ?? $committee->membersOf($department);
            $members = $byDepartment[$key];
            $voters = array_column($members, 'teacher_id');

            $cast = [];
            foreach ($votes[(int) $row['id']] ?? [] as $vote) {
                $cast[$vote['user_id']] = $vote;
            }

            $rows[$i]['signers'] = array_map(fn($teacher) => $teacher + ['decision' => $cast[$teacher['teacher_id']]['decision'] ?? null], $members);
            $rows[$i]['needed'] = self::needed($voters);
            $rows[$i]['signed'] = count(array_filter($cast, fn($vote) => $vote['decision'] === 'approve' && in_array($vote['user_id'], $voters, true)));
            $rows[$i]['can_sign'] = in_array($me, $voters, true) && in_array($row['status'], ['requested', 'pending'], true);
            $rows[$i]['my_vote'] = $cast[$me]['decision'] ?? null;
        }

        return $rows;
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
    public static function completionList($actor = null){

        $research = new Research();
        $requests = (new CompletionRequest())->waitingForAdmin();

        // A committee member sees only the requests of the research they sign.
        if ($actor !== null && $actor['role'] === 'teacher') {
            $requests = array_values(array_filter($requests, function ($request) use ($actor, $research) {
                $item = $research->find((int) $request['research_id']);
                return $item && in_array((int) $actor['id'], self::voters($item), true);
            }));
        }

        $votes = (new Approval())->votesFor('completion', array_map(fn($row) => (int) $row['id'], $requests));
        $committee = new Committee();

        $progress = (new Milestone())->progressFor(array_map(fn($row) => (int) $row['research_id'], $requests));

        foreach ($requests as $i => $request) {
            $item = $research->find((int) $request['research_id']);
            $numbers = $progress[(int) $request['research_id']] ?? ['milestones' => 0, 'tasks' => 0, 'done' => 0];

            $members = $item ? $committee->membersOf($research->departmentId($item)) : [];
            $voters = array_column($members, 'teacher_id');
            $cast = [];
            foreach ($votes[(int) $request['id']] ?? [] as $vote) {
                $cast[$vote['user_id']] = $vote;
            }

            $requests[$i]['signers'] = array_map(fn($teacher) => $teacher + ['decision' => $cast[$teacher['teacher_id']]['decision'] ?? null], $members);
            $requests[$i]['needed'] = self::needed($voters);
            $requests[$i]['signed'] = count(array_filter($cast, fn($vote) => $vote['decision'] === 'approve' && in_array($vote['user_id'], $voters, true)));
            $requests[$i]['can_sign'] = $actor !== null && in_array((int) ($actor['id'] ?? 0), $voters, true);
            $requests[$i]['my_vote'] = $cast[(int) ($actor['id'] ?? 0)]['decision'] ?? null;
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

        if (!$research || !self::mayRead($actor)) {
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
                'in_progress'     => $counts['approved'] + $counts['assigned'] + $counts['ongoing'],    // the Ongoing Research page
                'completion'      => count((new CompletionRequest())->waitingForAdmin()),   // requests to finish a research
            ];
        }

        $counts = $model->statusCountsFor((int) $actor['id']);
        $requests = new CompletionRequest();

        $numbers = [
            'requested'  => $counts['requested'],
            'ongoing'    => $counts['ongoing'],
            // a teacher: requests to finish that wait for the decision. A student: requests that wait for the confirmation.
            'completion' => $actor['role'] === 'teacher' ? $requests->countForTeacher((int) $actor['id']) : $requests->countForStudent((int) $actor['id']),
        ];

        // A member of a thesis committee also sees what waits for the committee.
        if ($actor['role'] === 'teacher' && Auth::isCommitteeMember()) {
            $departments = self::committeeDepartments($actor);
            $scope = $departments !== null ? ['department_ids' => $departments ?: [0]] : [];

            // the topics this teacher still has to sign (not signed yet by this teacher)
            $mine = array_filter(self::withSignatures($model->paginate(['statuses' => ['requested', 'pending']] + $scope, 200, 0), $actor),
                fn($row) => $row['can_sign'] && $row['my_vote'] === null);

            $numbers['committee_topics'] = count($mine);
            $numbers['committee_completion'] = count(array_filter(self::completionList($actor)['requests'], fn($row) => $row['my_vote'] === null));
            $numbers['committee_assign'] = $model->count(['statuses' => ['approved']] + $scope);
        }

        return $numbers;
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
            'batch_id'    => (int) ($query['batch_id'] ?? 0),
            'student'     => trim($query['student'] ?? ''),
            'sort'        => $query['sort'] ?? '',
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

        if (!self::mayRead($actor) && !self::ownerMayChange($actor, $item)) {
            return null;
        }

        // the admin and the committee also see who must sign
        return self::mayRead($actor) ? self::withSignatures([$item], $actor)[0] : $item;
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

    // The owner (or, once the supervisor was assigned first, any student of the group) may still edit the topic.
    private static function ownerMayChange($actor, $item): bool
    {
        if (!in_array($item['status'], array_merge(self::OPEN, ['assigned']), true)) {
            return false;
        }

        return (int) $item['created_by'] === (int) $actor['id']
            || ($actor['role'] === 'student' && (new Research())->isMember((int) $item['id'], (int) $actor['id']));
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

        if ($actor['role'] === 'admin' || Auth::isCommitteeMember()) {
            $options['teachers'] = (new User())->teacherOptions();
            $options['batches']  = (new Batch())->options();
            $options['years']    = (new Research())->years(true);
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
            return self::fail('Only students submit research. A teacher supervises the research of the groups he or she is assigned to.');
        }

        if ($id !== null) {
            $current = $model->find((int) $id);

            if (!$current || ($role !== 'admin' && !self::ownerMayChange($actor, $current))) {
                return self::fail('This research cannot be changed.');
            }
            if ($role === 'admin' && $current['status'] === 'assigned') {
                return self::fail('This group has not sent its topic yet. The group sends it from its Research page.');
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
        // The admin adds only finished research for the archive. New work starts with a group and a supervisor,
        // and its topic is approved by the thesis committee.
        if ($role === 'admin' && $id === null) {
            $rules['status'] = 'required|in:completed';
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
                }
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

            $message = $role === 'admin' ? 'Research added.' : 'Your research was sent to the thesis committee for approval.';

            return ['success' => true, 'errors' => [], 'message' => $message];
        }

        // Editing: the owner cannot change who supervises or which students work on it.
        if ($role !== 'admin') {
            $data['supervisor_id'] = $current['supervisor_id'] !== null ? (int) $current['supervisor_id'] : null;
        }

        $model->update((int) $id, $data, $tagIds, null);

        // The group sends its topic for the first time (the supervisor was assigned first): it waits for the committee now.
        if ($role !== 'admin' && $current['status'] === 'assigned') {
            (new Approval())->clearVotes('topic', (int) $id);
            $model->setTopic((int) $id, [
                'title'       => $data['title'],
                'abstract'    => $data['abstract'],
                'type'        => $data['type'],
                'category_id' => $data['category_id'],
            ], $tagIds, 'requested');
            self::log($actor, (int) $id, 'topic_submitted', $data['title']);

            return ['success' => true, 'errors' => [], 'message' => 'Your topic was sent to the thesis committee for approval.'];
        }

        // A topic the committee sent back for correction, improved by the owner, goes back to the committee.
        if ($role !== 'admin' && $current['status'] === 'correction') {
            (new Approval())->clearVotes('topic', (int) $id);
            $model->resubmit((int) $id, $role === 'teacher' ? 'pending' : 'requested');
            self::log($actor, (int) $id, 'resubmitted');

            return ['success' => true, 'errors' => [], 'message' => 'Research updated.'];
        }

        self::log($actor, (int) $id, 'updated');

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

        // ---------- an approved topic: the administrator or a committee member chooses the supervisor ----------

        if ($decision === 'assign') {
            if ($item['status'] !== 'approved') {
                return self::fail('Only an approved topic gets its supervisor here.');
            }
            if (!self::isAdmin($actor) && !self::mayDecide($actor, $item)) {
                return self::fail('Only the administrator or a member of the thesis committee can assign a supervisor.');
            }

            $errors = [];
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

        // ---------- a topic that waits for the signatures ----------

        if ($item['status'] === 'approved') {
            return self::fail('Please choose a supervisor for this research.');
        }
        if (!in_array($decision, ['approve', 'correction', 'reject'], true)) {
            return self::fail('Unknown decision.');
        }

        return self::signTopic($actor, $item, $decision, trim($request['note'] ?? ''));
    }

    // ---------------------------------------------------------------------------------
    // The signatures: the members of the thesis committee, and a few signatures decide
    // ---------------------------------------------------------------------------------

    /**
     * The teachers who may sign a research: the members of the active thesis committees of its department (and of the
     * whole university) who are ticked "can approve". Nobody is chosen per research.
     * Returns a list of teacher ids.
     */
    private static function voters(array $item): array
    {
        return array_column((new Committee())->membersOf((new Research())->departmentId($item)), 'teacher_id');
    }

    // How many signatures decide: the setting (2), but never more than the committee members who can sign.
    private static function needed(array $voters): int
    {
        return max(1, min(Setting::int('panel_signatures_needed', 2), count($voters)));
    }

    /**
     * Counts the signatures of the teachers who can sign now.
     * Returns ['approve' => n, 'correction' => n, 'reject' => n, 'needed' => n, 'votes' => [...]]
     */
    private static function tally(string $subject, int $subjectId, array $voters): array
    {
        $votes = array_values(array_filter((new Approval())->votes($subject, $subjectId), fn($vote) => in_array($vote['user_id'], array_map('intval', $voters), true)));
        $counts = ['approve' => 0, 'correction' => 0, 'reject' => 0];

        foreach ($votes as $vote) {
            $counts[$vote['decision']]++;
        }

        return $counts + ['needed' => self::needed($voters), 'votes' => $votes];
    }

    // One signature on a topic, then the decision when enough teachers signed.
    private static function signTopic($actor, array $item, string $decision, string $note): array
    {
        $voters = self::voters($item);
        if (!in_array((int) $actor['id'], $voters, true)) {
            return self::fail('Only a committee member who is allowed to approve can sign a topic.');
        }

        if ($decision !== 'approve') {
            if ($note === '') {
                $what = $decision === 'reject' ? 'Please write the reason for rejecting.' : 'Please write what must be corrected.';
                return ['success' => false, 'errors' => ['note' => [$what]], 'message' => $what];
            }
            if (mb_strlen($note) > 500) {
                return ['success' => false, 'errors' => ['note' => ['The note must not be more than 500 characters.']], 'message' => 'The note is too long.'];
            }
        }

        $id = (int) $item['id'];
        $approvals = new Approval();
        $lock = 'uptms_topic_' . $id;

        if (!$approvals->lock($lock)) {
            return self::fail('Please try again in a moment.');
        }

        try {
            $model = new Research();
            $item = $model->find($id);

            if (!$item || !in_array($item['status'], ['requested', 'pending'], true)) {
                return self::fail('This topic was already decided.');
            }

            $approvals->vote('topic', $id, (int) $actor['id'], $decision, $note);
            self::log($actor, $id, 'topic_signed', ['approve' => 'Approved', 'correction' => 'Correction', 'reject' => 'Rejected'][$decision] . ($note !== '' ? ': ' . $note : ''));

            $tally = self::tally('topic', $id, $voters);
            $noteOf = function (string $kind) use ($tally, $note): string {
                foreach ($tally['votes'] as $vote) {
                    if ($vote['decision'] === $kind && ($vote['note'] ?? '') !== '') {
                        return $vote['note'];
                    }
                }

                return $note;
            };

            // One correction is enough to send the topic back.
            if ($tally['correction'] > 0) {
                return self::applyTopic($actor, $model, $item, 'correction', $noteOf('correction'));
            }
            if ($tally['reject'] >= $tally['needed']) {
                return self::applyTopic($actor, $model, $item, 'reject', $noteOf('reject'));
            }
            if ($tally['approve'] >= $tally['needed']) {
                return self::applyTopic($actor, $model, $item, 'approve', '');
            }

            return ['success' => true, 'errors' => [], 'message' => 'Your signature is saved (' . max($tally['approve'], $tally['reject']) . ' of ' . $tally['needed'] . '). The topic is decided when ' . $tally['needed'] . ' teachers agree.'];
        } finally {
            $approvals->unlock($lock);
        }
    }

    // The result of the signatures: the topic becomes approved, goes back for correction, or is rejected.
    private static function applyTopic($actor, Research $model, array $item, string $decision, string $note): array
    {
        $id = (int) $item['id'];
        $supervisorId = $item['supervisor_id'] !== null ? (int) $item['supervisor_id'] : null;

        if ($decision === 'reject') {
            // The supervisor was assigned first: the group keeps working and sends another topic.
            if ($supervisorId !== null) {
                self::log($actor, $id, 'topic_rejected', '"' . $item['title'] . '": ' . $note);
                $model->returnToAssigned($id, $note);

                return ['success' => true, 'errors' => [], 'message' => 'Topic rejected. The group keeps its supervisor and can send a new topic.'];
            }

            $model->review($id, 'rejected', null, $note);
            self::log($actor, $id, 'rejected', $note);

            return ['success' => true, 'errors' => [], 'message' => 'Research rejected. Nothing more can be done with it.'];
        }

        if ($decision === 'correction') {
            $model->review($id, 'correction', $supervisorId, $note);
            self::log($actor, $id, 'correction', $note);

            return ['success' => true, 'errors' => [], 'message' => 'Sent back for correction.'];
        }

        // approved: the supervisor is already set, so the work goes on as an ongoing research
        if ($supervisorId !== null) {
            $model->review($id, 'ongoing', $supervisorId, null);
            self::log($actor, $id, 'approved', 'Signed by the committee');

            return ['success' => true, 'errors' => [], 'message' => 'Topic approved. The research is now ongoing.'];
        }

        $model->review($id, 'approved', null, null);
        self::log($actor, $id, 'approved', 'Signed by the committee');

        return ['success' => true, 'errors' => [], 'message' => 'Topic approved. Now assign a supervisor.'];
    }

    /**
     * A committee member signs the request to finish a research (it came through the supervisor and the team).
     * $request = request_id, decision (approve | reject), note (needed for reject). 2 signatures decide.
     */
    public static function completionDecision($actor, $request){

        $requests = new CompletionRequest();
        $item = $requests->find((int) ($request['request_id'] ?? 0));

        if (!$item || $item['status'] !== 'admin') {
            return self::fail('This request is not waiting for the signatures.');
        }

        $forResearch = (new Research())->find((int) $item['research_id']);
        if (!$forResearch) {
            return self::fail('This research no longer exists.');
        }

        $voters = self::voters($forResearch);
        if (!in_array((int) $actor['id'], $voters, true)) {
            return self::fail('Only a committee member who is allowed to approve can sign a request to finish.');
        }

        $decision = $request['decision'] ?? '';
        if (!in_array($decision, ['approve', 'reject'], true)) {
            return self::fail('Unknown decision.');
        }

        $note = trim($request['note'] ?? '');
        if ($decision === 'reject' && ($note === '' || mb_strlen($note) > 500)) {
            return ['success' => false, 'errors' => ['note' => ['Please write the reason (up to 500 characters).']], 'message' => 'Please write the reason.'];
        }

        $approvals = new Approval();
        $lock = 'uptms_completion_' . (int) $item['id'];

        if (!$approvals->lock($lock)) {
            return self::fail('Please try again in a moment.');
        }

        try {
            $item = $requests->find((int) $item['id']);
            if (!$item || $item['status'] !== 'admin') {
                return self::fail('This request was already decided.');
            }

            $approvals->vote('completion', (int) $item['id'], (int) $actor['id'], $decision, $note);
            self::log($actor, (int) $item['research_id'], 'completion_signed', ($decision === 'approve' ? 'Approved' : 'Rejected') . ($note !== '' ? ': ' . $note : ''));

            $tally = self::tally('completion', (int) $item['id'], $voters);

            if ($tally['reject'] >= $tally['needed']) {
                $requests->setStatus((int) $item['id'], 'rejected', (int) $actor['id'], $note);
                self::log($actor, (int) $item['research_id'], 'completion_rejected', $note);

                return ['success' => true, 'errors' => [], 'message' => 'The request was rejected.'];
            }

            if ($tally['approve'] >= $tally['needed']) {
                (new Research())->setStatus((int) $item['research_id'], 'completed');
                $requests->setStatus((int) $item['id'], 'done', (int) $actor['id']);
                self::log($actor, (int) $item['research_id'], 'completed');

                return ['success' => true, 'errors' => [], 'message' => 'The research is completed.'];
            }

            return ['success' => true, 'errors' => [], 'message' => 'Your signature is saved (' . max($tally['approve'], $tally['reject']) . ' of ' . $tally['needed'] . '). The request is decided when ' . $tally['needed'] . ' teachers agree.'];
        } finally {
            $approvals->unlock($lock);
        }
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

    /**
     * Permanent delete: the research and all its milestones, tasks, feedback, evaluations, history and
     * completion requests are removed for ever. Only the real administrator (not a research manager teacher).
     */
    public static function purge($request){

        $real = Auth::currentUser();
        if (!$real || $real['role'] !== 'admin') {
            return self::fail('Only the administrator can delete a research permanently.');
        }

        $model = new Research();
        $item  = $model->findAny((int) ($request['id'] ?? 0));

        if (!$item) {
            return self::fail('This research no longer exists.');
        }

        $model->hardDelete((int) $item['id']);
        error_log('UPTMS: research ' . $item['code'] . ' (#' . $item['id'] . ') was permanently deleted by admin #' . $real['id']);

        return ['success' => true, 'errors' => [], 'message' => $item['code'] . ' and everything that belongs to it was deleted for ever.'];
    }

    // The trash page: the deleted research. Real administrator only.
    public static function trash(){

        $real = Auth::currentUser();
        if (!$real || $real['role'] !== 'admin') {
            return null;
        }

        return ['research' => (new Research())->trashed()];
    }

    // Puts a deleted research back.
    public static function restore($actor, $request){

        $real = Auth::currentUser();
        if (!$real || $real['role'] !== 'admin') {
            return self::fail('Only the administrator can restore a research.');
        }

        $model = new Research();
        $item  = $model->findAny((int) ($request['id'] ?? 0));

        if (!$item || $item['deleted_at'] === null) {
            return self::fail('This research is not in the trash.');
        }

        $model->restore((int) $item['id']);
        self::log($actor, (int) $item['id'], 'restored');

        return ['success' => true, 'errors' => [], 'message' => $item['code'] . ' was restored.'];
    }

    // What a permanent delete would remove (for the warning before it).
    public static function purgeImpact($id){

        $model = new Research();
        $item  = $model->findAny((int) $id);

        return $item ? ['research' => ['id' => (int) $item['id'], 'code' => $item['code'], 'title' => $item['title']], 'impact' => $model->impact((int) $item['id'])] : null;
    }

    // ---------------------------------------------------------------------------------
    // A supervisor for a group, without a research request
    // ---------------------------------------------------------------------------------

    /**
     * The administrator or a member of the thesis committee gives an approved group a supervisor.
     * A research is made with the status "assigned" and no topic: the group starts to work with the supervisor now,
     * and sends its topic later (2 members of the thesis committee sign it).  $request = team_id, supervisor_id
     */
    public static function assignGroup($actor, $request){

        $teams = new Team();
        $team  = $teams->find((int) ($request['team_id'] ?? 0));

        if (!$team) {
            return self::fail('Please choose a group/team.');
        }
        if (!self::isAdmin($actor) && !(new Committee())->canDecide((int) $actor['id'], (int) $team['department_id'])) {
            return self::fail('Only the administrator or a member of the thesis committee can assign a supervisor.');
        }
        if ($team['status'] !== 'open') {
            return self::fail('This group/team is not approved yet.');
        }
        if ($teams->isBusy((int) $team['id'])) {
            return self::fail('This group/team already has a supervisor or is working on a research.');
        }

        $errors = [];
        $supervisorId = self::idOrNull($request['supervisor_id'] ?? null);
        if ($supervisorId === null || !self::isActiveTeacher($supervisorId)) {
            $errors['supervisor_id'][] = 'Please choose a supervisor.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => reset($errors)[0]];
        }

        $model = new Research();
        $newId = $model->create([
            'title'         => null,
            'abstract'      => null,
            'type'          => 'Thesis',
            'category_id'   => null,
            'year'          => (int) date('Y'),
            'status'        => 'assigned',
            'target_status' => 'ongoing',
            'supervisor_id' => $supervisorId,
            'team_id'       => (int) $team['id'],
            'created_by'    => (int) $actor['id'],
        ], [], array_map(fn($member) => (int) $member['id'], $team['members']));

        self::log($actor, $newId, 'supervisor_assigned', self::nameOf($supervisorId));

        return ['success' => true, 'errors' => [], 'message' => self::nameOf($supervisorId) . ' is now the supervisor of "' . $team['name'] . '". The group can start to work.'];
    }

    // ---------------------------------------------------------------------------------
    // Who may do what
    // ---------------------------------------------------------------------------------

    private static function isAdmin($actor): bool
    {
        return ($actor['role'] ?? '') === 'admin';
    }

    // Reading the whole catalog and the history: the administrator and the members of a thesis committee.
    private static function mayRead($actor): bool
    {
        return self::isAdmin($actor) || (($actor['role'] ?? '') === 'teacher' && Auth::isCommitteeMember());
    }

    // Deciding on a topic, a request to finish and a group: only a member of a committee that works for the
    // department of this research. (The administrator does not decide.)
    private static function mayDecide($actor, array $item): bool
    {
        if (($actor['role'] ?? '') !== 'teacher') {
            return false;
        }

        return (new Committee())->canDecide((int) $actor['id'], (new Research())->departmentId($item));
    }

    // The departments the committee member decides for: null = every department (also for the administrator).
    private static function committeeDepartments($actor): ?array
    {
        if ($actor === null || ($actor['role'] ?? '') !== 'teacher') {
            return null;
        }

        return (new Committee())->departmentsOf((int) $actor['id']);
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
