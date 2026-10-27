<?php

namespace App\Controllers;

use App\Models\CompletionRequest;
use App\Models\Evaluation;
use App\Models\Milestone;
use App\Models\Research;
use App\Models\ResearchActivity;
use App\Models\ResearchComment;
use App\Services\Validation;

/**
 * The work on an ongoing research (the "project"), used by api/project.php.
 * It is the page where the supervisor (teacher) and the team (students) work together:
 * milestones with tasks, comments, and the supervisor's evaluations.
 *
 * Who may open a project:
 *   teacher  the supervisor of the research
 *   student  a member of the team
 *   admin    any (read only)
 * A research that is completed can still be opened, but nothing can be changed any more.
 *
 * Rules:
 *   milestones and tasks   the supervisor and the team can add them and finish tasks.
 *                          The one who made it can delete it. The supervisor can delete any, so the team can never
 *                          remove what the supervisor made.
 *   comments               everyone in the project can write. Each person can delete only their own.
 *   evaluations            only the supervisor writes them. The team reads them.
 *   finishing              a student asks, the supervisor approves, every team member confirms, then the administrator
 *                          completes the research (that last step is ResearchController::completionDecision).
 */
class ProjectController {

    // The statuses that have a project page.
    private const PROJECT = ['ongoing', 'completed'];

    // ---------------------------------------------------------------------------------
    // Reading
    // ---------------------------------------------------------------------------------

    // The list of the projects of this user, with the progress of each, and the numbers for the dashboards.
    public static function index($actor){

        $filters = ['statuses' => self::PROJECT];

        if ($actor['role'] === 'teacher') {
            $filters['supervisor_id'] = (int) $actor['id'];
        } elseif ($actor['role'] === 'student') {
            $filters['mine'] = (int) $actor['id'];
        }

        $projects = (new Research())->paginate($filters, 100, 0);
        $ids = array_map(fn($project) => (int) $project['id'], $projects);

        $progress = (new Milestone())->progressFor($ids);
        $evaluations = (new Evaluation())->countsFor($ids);

        $summary = ['ongoing' => 0, 'completed' => 0, 'milestones' => 0, 'tasks' => 0, 'done' => 0, 'evaluations' => 0];

        foreach ($projects as $i => $project) {
            $numbers = $progress[(int) $project['id']] ?? ['milestones' => 0, 'tasks' => 0, 'done' => 0];

            $projects[$i]['milestones'] = $numbers['milestones'];
            $projects[$i]['tasks'] = $numbers['tasks'];
            $projects[$i]['done'] = $numbers['done'];
            $projects[$i]['percent'] = self::percent($numbers['done'], $numbers['tasks']);
            $projects[$i]['evaluations'] = $evaluations[(int) $project['id']] ?? 0;

            // Only what the project list needs: nothing about who submitted it or the review.
            $projects[$i] = array_diff_key($projects[$i], array_flip(['review_note', 'reviewed_at', 'target_status', 'creator_name']));

            $summary[$project['status']]++;
            $summary['milestones'] += $numbers['milestones'];
            $summary['tasks'] += $numbers['tasks'];
            $summary['done'] += $numbers['done'];
            $summary['evaluations'] += $projects[$i]['evaluations'];
        }

        return ['projects' => $projects, 'summary' => $summary];
    }

    /**
     * One kind of list across all my projects, for the menu pages.
     * $view = milestones | comments | evaluations | activity | alerts (what is new). Returns null for an unknown view.
     */
    // $researchId > 0: only the list of that project (it must be one of mine), with the project's name for the page.
    public static function feed($actor, $view, $researchId = 0){

        if ($view === 'alerts') {
            return self::alerts($actor);
        }

        $filters = ['statuses' => self::PROJECT];

        if ($actor['role'] === 'teacher') {
            $filters['supervisor_id'] = (int) $actor['id'];
        } elseif ($actor['role'] === 'student') {
            $filters['mine'] = (int) $actor['id'];
        }

        $projects = (new Research())->paginate($filters, 100, 0);
        $ids = array_map(fn($project) => (int) $project['id'], $projects);

        // one project: it has to be one of the projects this person may see
        $research = null;
        if ((int) $researchId > 0) {
            foreach ($projects as $project) {
                if ((int) $project['id'] === (int) $researchId) {
                    $research = [
                        'id' => (int) $project['id'], 'code' => $project['code'], 'title' => $project['title'], 'status' => $project['status'],
                        'team_name' => $project['team_name'] ?? null, 'batch_name' => $project['batch_name'] ?? null,
                    ];
                }
            }

            if ($research === null) {
                return null;
            }
            $ids = [(int) $researchId];
        }

        $items = match ($view) {
            'milestones'  => (new Milestone())->forResearchIds($ids),
            'comments'    => (new ResearchComment())->forResearchIds($ids),
            'evaluations' => (new Evaluation())->forResearchIds($ids),
            'activity'    => (new ResearchActivity())->forResearchIds($ids),
            default       => null,
        };

        return $items === null ? null : ['items' => $items, 'research' => $research];
    }

    // What is new for this person in all their research: what other people did since they last looked.
    public static function alerts($actor){

        $filters = $actor['role'] === 'teacher' ? ['supervisor_id' => (int) $actor['id']] : ['mine' => (int) $actor['id']];
        $ids = array_map(fn($row) => (int) $row['id'], (new Research())->paginate($filters, 100, 0));

        $actions = [
            'milestone_added', 'task_added', 'task_completed', 'commented', 'evaluated',
            'completion_requested', 'completion_approved', 'completion_confirmed', 'completion_rejected', 'completed',
            'approved', 'rejected', 'correction', 'supervisor_assigned',
        ];

        $items = [];
        foreach ((new ResearchActivity())->unseenFor((int) $actor['id'], $ids, $actions) as $row) {
            $id = (int) $row['research_id'];

            $items[$id] ??= ['research_id' => $id, 'code' => $row['code'], 'title' => $row['title'], 'status' => $row['status'], 'counts' => []];
            $items[$id]['counts'][$row['action']] = (int) $row['total'];
        }

        return ['items' => array_values($items)];
    }

    // The My Research page was opened: the news about topics that are not projects yet (approved, rejected ...) were read.
    private static function seenTopics($actor): array
    {
        $filters = $actor['role'] === 'teacher' ? ['supervisor_id' => (int) $actor['id']] : ['mine' => (int) $actor['id']];
        $topics = (new Research())->paginate($filters + ['statuses' => ['requested', 'pending', 'approved', 'rejected', 'correction']], 100, 0);

        (new ResearchActivity())->markSeen((int) $actor['id'], array_map(fn($row) => (int) $row['id'], $topics));

        return self::ok('Seen.');
    }

    // Everything the project page shows. Returns null when this user may not open it.
    public static function show($actor, $id){

        $research = self::open($actor, (int) $id);

        if (!$research) {
            return null;
        }

        // The person looks at this project now, so what happened before is not new any more.
        (new ResearchActivity())->markSeen((int) $actor['id'], [(int) $research['id']]);

        $milestones = (new Milestone())->forResearch((int) $research['id']);

        $tasks = 0;
        $done = 0;
        foreach ($milestones as $milestone) {
            foreach ($milestone['tasks'] as $task) {
                $tasks++;
                $done += $task['is_done'];
            }
        }

        $isSupervisor = $actor['role'] === 'teacher';
        $open = $research['status'] === 'ongoing' && $actor['role'] !== 'admin';

        return [
            'research' => [
                'id' => (int) $research['id'], 'code' => $research['code'], 'title' => $research['title'],
                'abstract' => $research['abstract'], 'type' => $research['type'], 'year' => $research['year'],
                'status' => $research['status'], 'category_name' => $research['category_name'],
                'supervisor_name' => $research['supervisor_name'], 'team_name' => $research['team_name'] ?? null, 'batch_name' => $research['batch_name'] ?? null,
                'students' => $research['students'], 'tags' => $research['tags'],
            ],
            'me'          => ['id' => (int) $actor['id'], 'role' => $actor['role']],
            'can_work'    => $open,
            'can_evaluate' => $open && $isSupervisor,
            'progress'    => ['tasks' => $tasks, 'done' => $done, 'percent' => self::percent($done, $tasks)],
            'milestones'  => $milestones,
            'comments'    => (new ResearchComment())->forResearch((int) $research['id']),
            'evaluations' => (new Evaluation())->forResearch((int) $research['id']),
            'completion'  => self::completionData($actor, $research),
        ];
    }

    // ---------------------------------------------------------------------------------
    // Changing (teacher and student only)
    // ---------------------------------------------------------------------------------

    // $request = op + the fields of that op (see api/project.php)
    public static function change($actor, $request){

        if (!in_array($actor['role'], ['teacher', 'student'], true)) {
            return self::fail('You are not allowed to do this.');
        }

        $op = $request['op'] ?? '';

        return match ($op) {
            'milestone_add'    => self::addMilestone($actor, $request),
            'milestone_delete' => self::deleteMilestone($actor, $request),
            'task_add'         => self::addTask($actor, $request),
            'task_toggle'      => self::toggleTask($actor, $request),
            'task_delete'      => self::deleteTask($actor, $request),
            'comment_add'      => self::addComment($actor, $request),
            'comment_delete'   => self::deleteComment($actor, $request),
            'evaluation_add'   => self::addEvaluation($actor, $request),
            'seen_topics'      => self::seenTopics($actor),
            'completion_request' => self::requestCompletion($actor, $request),
            'completion_cancel'  => self::cancelCompletion($actor, $request),
            'completion_decide'  => self::decideCompletion($actor, $request),
            'completion_confirm' => self::confirmCompletion($actor, $request),
            default            => self::fail('Unknown action.'),
        };
    }

    private static function addMilestone($actor, $request): array
    {
        $research = self::openForWork($actor, (int) ($request['id'] ?? 0));
        if (!$research) {
            return self::fail('This project cannot be changed.');
        }

        $validate = Validation::validate($request, [
            'title'       => 'required|max:150',
            'description' => 'max:500',
            'due_date'    => 'date',
        ], ['due_date' => 'Due date']);

        if (!$validate['success']) {
            return ['success' => false, 'errors' => $validate['errors'], 'message' => 'Please fix the errors below.'];
        }

        $title = trim($request['title']);

        (new Milestone())->create([
            'research_id' => (int) $research['id'],
            'title'       => $title,
            'description' => trim($request['description'] ?? '') ?: null,
            'due_date'    => trim($request['due_date'] ?? '') ?: null,
            'created_by'  => (int) $actor['id'],
        ]);
        self::log($actor, (int) $research['id'], 'milestone_added', $title);

        return self::ok('Milestone added.');
    }

    private static function deleteMilestone($actor, $request): array
    {
        $model = new Milestone();
        $milestone = $model->find((int) ($request['milestone_id'] ?? 0));
        $research = $milestone ? self::openForWork($actor, (int) $milestone['research_id']) : null;

        if (!$research) {
            return self::fail('This milestone cannot be changed.');
        }
        if (!self::mayDelete($actor, $milestone['created_by'])) {
            return self::fail('Only the person who made this milestone or the supervisor can delete it.');
        }

        $model->softDelete((int) $milestone['id']);
        self::log($actor, (int) $research['id'], 'milestone_removed', $milestone['title']);

        return self::ok('Milestone deleted.');
    }

    private static function addTask($actor, $request): array
    {
        $model = new Milestone();
        $milestone = $model->find((int) ($request['milestone_id'] ?? 0));
        $research = $milestone ? self::openForWork($actor, (int) $milestone['research_id']) : null;

        if (!$research) {
            return self::fail('This milestone cannot be changed.');
        }

        $validate = Validation::validate($request, ['title' => 'required|max:200']);
        if (!$validate['success']) {
            return ['success' => false, 'errors' => $validate['errors'], 'message' => 'Please write the task.'];
        }

        $title = trim($request['title']);

        $model->createTask((int) $milestone['id'], $title, (int) $actor['id']);
        self::log($actor, (int) $research['id'], 'task_added', $title);

        return self::ok('Task added.');
    }

    // Finished or opened again.
    private static function toggleTask($actor, $request): array
    {
        $model = new Milestone();
        $task = $model->findTask((int) ($request['task_id'] ?? 0));
        $research = $task ? self::openForWork($actor, (int) $task['research_id']) : null;

        if (!$research) {
            return self::fail('This task cannot be changed.');
        }

        $done = !$task['is_done'];

        $model->setTaskDone((int) $task['id'], $done, (int) $actor['id']);
        self::log($actor, (int) $research['id'], $done ? 'task_completed' : 'task_reopened', $task['title']);

        return self::ok($done ? 'Task completed.' : 'Task opened again.');
    }

    private static function deleteTask($actor, $request): array
    {
        $model = new Milestone();
        $task = $model->findTask((int) ($request['task_id'] ?? 0));
        $research = $task ? self::openForWork($actor, (int) $task['research_id']) : null;

        if (!$research) {
            return self::fail('This task cannot be changed.');
        }
        if (!self::mayDelete($actor, $task['created_by'])) {
            return self::fail('Only the person who made this task or the supervisor can delete it.');
        }

        $model->softDeleteTask((int) $task['id']);
        self::log($actor, (int) $research['id'], 'task_removed', $task['title']);

        return self::ok('Task deleted.');
    }

    private static function addComment($actor, $request): array
    {
        $research = self::openForWork($actor, (int) ($request['id'] ?? 0));
        if (!$research) {
            return self::fail('This project cannot be changed.');
        }

        $validate = Validation::validate($request, ['body' => 'required|max:1000'], ['body' => 'Comment']);
        if (!$validate['success']) {
            return ['success' => false, 'errors' => $validate['errors'], 'message' => 'Please write a comment.'];
        }

        $body = trim($request['body']);

        // A comment can be the feedback on one milestone of this research.
        $milestoneId = null;
        if (!empty($request['milestone_id'])) {
            $milestone = (new Milestone())->find((int) $request['milestone_id']);

            if (!$milestone || (int) $milestone['research_id'] !== (int) $research['id']) {
                return self::fail('This milestone cannot be changed.');
            }
            $milestoneId = (int) $milestone['id'];
        }

        (new ResearchComment())->create((int) $research['id'], (int) $actor['id'], $body, $milestoneId);
        self::log($actor, (int) $research['id'], 'commented', mb_substr($body, 0, 100));

        return self::ok('Comment added.');
    }

    private static function deleteComment($actor, $request): array
    {
        $model = new ResearchComment();
        $comment = $model->find((int) ($request['comment_id'] ?? 0));
        $research = $comment ? self::openForWork($actor, (int) $comment['research_id']) : null;

        if (!$research) {
            return self::fail('This comment cannot be changed.');
        }
        if ((int) $comment['user_id'] !== (int) $actor['id']) {
            return self::fail('You can delete only your own comments.');
        }

        $model->softDelete((int) $comment['id']);

        return self::ok('Comment deleted.');
    }

    private static function addEvaluation($actor, $request): array
    {
        if ($actor['role'] !== 'teacher') {
            return self::fail('Only the supervisor can evaluate the research.');
        }

        $research = self::openForWork($actor, (int) ($request['id'] ?? 0));
        if (!$research) {
            return self::fail('This project cannot be changed.');
        }

        $validate = Validation::validate($request, [
            'rating'  => 'required|integer|between:1,5',
            'remarks' => 'required|max:1000',
        ], ['rating' => 'Rating']);

        if (!$validate['success']) {
            return ['success' => false, 'errors' => $validate['errors'], 'message' => 'Please fix the errors below.'];
        }

        (new Evaluation())->create((int) $research['id'], (int) $actor['id'], (int) $request['rating'], trim($request['remarks']));
        self::log($actor, (int) $research['id'], 'evaluated', 'Rating ' . (int) $request['rating'] . ' of 5');

        return self::ok('Evaluation saved.');
    }

    // ---------------------------------------------------------------------------------
    // Finishing the research: student asks -> supervisor approves -> team confirms -> administrator completes
    // ---------------------------------------------------------------------------------

    // What the project page shows about the request, and which buttons this user may use.
    private static function completionData($actor, array $research): array
    {
        $model = new CompletionRequest();
        $request = $model->latest((int) $research['id']);
        $info = null;

        if ($request) {
            $confirmed = $model->confirmedIds((int) $request['id']);

            $info = [
                'id'                => (int) $request['id'],
                'status'            => $request['status'],
                'note'              => $request['note'],
                'requested_by'      => (int) $request['requested_by'],
                'requested_by_name' => $request['requested_by_name'],
                'created_at'        => $request['created_at'],
                'members'           => array_map(fn($student) => [
                    'id' => (int) $student['id'], 'name' => $student['name'], 'confirmed' => in_array((int) $student['id'], $confirmed, true),
                ], $research['students']),
            ];
        }

        $active = $info && in_array($info['status'], CompletionRequest::ACTIVE, true);
        $ongoing = $research['status'] === 'ongoing';
        $isStudent = $actor['role'] === 'student';
        $me = (int) $actor['id'];

        $iConfirmed = false;
        foreach ($info['members'] ?? [] as $member) {
            if ($member['id'] === $me && $member['confirmed']) {
                $iConfirmed = true;
            }
        }

        return [
            'request'     => $info,
            'can_request' => $isStudent && $ongoing && !$active,
            'can_cancel'  => $isStudent && $ongoing && $active && in_array($info['status'], ['teacher', 'team'], true) && $info['requested_by'] === $me,
            'can_decide'  => $actor['role'] === 'teacher' && $ongoing && $active && $info['status'] === 'teacher',
            'can_confirm' => $isStudent && $ongoing && $info && $info['status'] === 'team' && !$iConfirmed,
        ];
    }

    // A student of the team asks to finish the research.
    private static function requestCompletion($actor, $request): array
    {
        $research = self::openForWork($actor, (int) ($request['id'] ?? 0));
        if (!$research || $actor['role'] !== 'student') {
            return self::fail('This project cannot be changed.');
        }

        $model = new CompletionRequest();
        if ($model->hasActive((int) $research['id'])) {
            return self::fail('A request to finish this research is already open.');
        }

        $model->create((int) $research['id'], (int) $actor['id']);
        self::log($actor, (int) $research['id'], 'completion_requested');

        return self::ok('Your request was sent to the supervisor.');
    }

    // The student who asked takes the request back.
    private static function cancelCompletion($actor, $request): array
    {
        $model = new CompletionRequest();
        $item = $model->find((int) ($request['request_id'] ?? 0));
        $research = $item ? self::openForWork($actor, (int) $item['research_id']) : null;

        if (!$research || $actor['role'] !== 'student') {
            return self::fail('This request cannot be changed.');
        }
        if ((int) $item['requested_by'] !== (int) $actor['id'] || !in_array($item['status'], ['teacher', 'team'], true)) {
            return self::fail('Only the student who asked can cancel it, and only before the administrator gets it.');
        }

        $model->setStatus((int) $item['id'], 'cancelled', (int) $actor['id']);
        self::log($actor, (int) $research['id'], 'completion_cancelled');

        return self::ok('The request was cancelled.');
    }

    // The supervisor approves or rejects (with the reason).
    private static function decideCompletion($actor, $request): array
    {
        $model = new CompletionRequest();
        $item = $model->find((int) ($request['request_id'] ?? 0));
        $research = $item ? self::openForWork($actor, (int) $item['research_id']) : null;

        if (!$research || $actor['role'] !== 'teacher' || $item['status'] !== 'teacher') {
            return self::fail('This request cannot be decided.');
        }

        $decision = $request['decision'] ?? '';
        $note = trim($request['note'] ?? '');

        if ($decision === 'reject') {
            if ($note === '' || mb_strlen($note) > 500) {
                return ['success' => false, 'errors' => ['note' => ['Please write the reason (up to 500 characters).']], 'message' => 'Please write the reason.'];
            }

            $model->setStatus((int) $item['id'], 'rejected', (int) $actor['id'], $note);
            self::log($actor, (int) $research['id'], 'completion_rejected', $note);

            return self::ok('The request was rejected.');
        }

        if ($decision !== 'approve') {
            return self::fail('Unknown decision.');
        }

        // The student who asked has confirmed already. If the team is only that student, it goes straight to the administrator.
        $next = self::everyoneConfirmed($research, $model->confirmedIds((int) $item['id'])) ? 'admin' : 'team';

        $model->setStatus((int) $item['id'], $next, (int) $actor['id']);
        self::log($actor, (int) $research['id'], 'completion_approved', $note);

        return self::ok($next === 'admin' ? 'Approved. The request went to the administrator.' : 'Approved. The team has to confirm now.');
    }

    // A member of the team confirms. When everybody has confirmed, the request goes to the administrator.
    private static function confirmCompletion($actor, $request): array
    {
        $model = new CompletionRequest();
        $item = $model->find((int) ($request['request_id'] ?? 0));
        $research = $item ? self::openForWork($actor, (int) $item['research_id']) : null;

        if (!$research || $actor['role'] !== 'student' || $item['status'] !== 'team') {
            return self::fail('This request cannot be confirmed.');
        }

        $model->confirm((int) $item['id'], (int) $actor['id']);
        self::log($actor, (int) $research['id'], 'completion_confirmed');

        if (self::everyoneConfirmed($research, $model->confirmedIds((int) $item['id']))) {
            $model->setStatus((int) $item['id'], 'admin');

            return self::ok('Everybody confirmed. The request went to the administrator.');
        }

        return self::ok('Thank you. Your confirmation was saved.');
    }

    private static function everyoneConfirmed(array $research, array $confirmedIds): bool
    {
        foreach ($research['students'] as $student) {
            if (!in_array((int) $student['id'], $confirmedIds, true)) {
                return false;
            }
        }

        return true;
    }

    // ---------------------------------------------------------------------------------
    // Small helpers
    // ---------------------------------------------------------------------------------

    // The research when this user belongs to it (and it has a project page), otherwise null.
    private static function open($actor, int $id): ?array
    {
        $model = new Research();
        $research = $model->find($id);

        if (!$research || !in_array($research['status'], self::PROJECT, true)) {
            return null;
        }

        $allowed = match ($actor['role']) {
            'admin'   => true,
            'teacher' => (int) $research['supervisor_id'] === (int) $actor['id'],
            'student' => $model->isMember($id, (int) $actor['id']),
            default   => false,
        };

        return $allowed ? $research : null;
    }

    // The same, but only while the research is ongoing (a completed research cannot be changed).
    private static function openForWork($actor, int $id): ?array
    {
        $research = self::open($actor, $id);

        return $research && $research['status'] === 'ongoing' ? $research : null;
    }

    // The one who made it can delete it. The supervisor can delete anything in the project.
    private static function mayDelete($actor, $createdBy): bool
    {
        return $actor['role'] === 'teacher' || (int) $createdBy === (int) $actor['id'];
    }

    private static function percent(int $done, int $tasks): int
    {
        return $tasks > 0 ? (int) round($done * 100 / $tasks) : 0;
    }

    private static function log($actor, int $researchId, string $action, ?string $detail = null): void
    {
        (new ResearchActivity())->add($researchId, (int) $actor['id'], $action, $detail);
    }

    private static function ok(string $message): array
    {
        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    private static function fail(string $message): array
    {
        return ['success' => false, 'errors' => [], 'message' => $message];
    }
}
