<?php

namespace App\Controllers;

use App\Models\Batch;
use App\Models\Department;
use App\Models\ResearchActivity;
use App\Models\Team;
use App\Models\User;
use App\Services\Notifier;
use App\Services\Validation;

/**
 * Called by api/teams.php (admin and student). It only works with data and returns arrays.
 *
 * The rules:
 *   - a team has 2 to 3 members, all of the same department and batch
 *   - a student can be in only one open team, and not while they work on another unfinished research
 *   - the admin creates, renames and deletes teams (delete only while no research used the team)
 *   - a student may ask for a team of their own batch ("requested"); the admin approves it ("open") or rejects it
 *   - a student has only one group for now, and can ask to change the members of their own approved team (never the name):
 *     the change is a request, the admin approves or rejects it. A team may ask only ONCE (whatever the answer was);
 *     after that only the admin changes the members.
 */
class TeamController {

    private const PER_PAGE = 10;

    // The list. The admin sees every team, a student only the teams they are in.
    public static function index($actor, $query){

        $filters = [
            'q'             => trim($query['q'] ?? ''),
            'status'        => $query['status'] ?? '',
            'department_id' => (int) ($query['department_id'] ?? 0),
            'batch_id'      => (int) ($query['batch_id'] ?? 0),
        ];

        if ($actor['role'] === 'student') {
            $filters['member_id'] = (int) $actor['id'];
        }

        $model = new Team();
        $page  = max(1, (int) ($query['page'] ?? 1));
        $total = $model->count($filters);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'teams'   => $model->paginate($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => self::PER_PAGE,
            'min_size' => Team::minSize(),
            'max_size' => Team::maxSize(),
        ];
    }

    // One team (null when it does not exist, or a student is not in it).
    public static function one($actor, $id){

        $model = new Team();
        $team  = $model->find((int) $id);

        if (!$team || ($actor['role'] === 'student' && !$model->isMember((int) $team['id'], (int) $actor['id']))) {
            return null;
        }

        $team['can_edit_members'] = $team['status'] === 'open' || ($actor['role'] === 'admin' && $team['status'] === 'requested');
        $team['has_research'] = $model->hasResearch((int) $team['id']);

        return ['team' => $team, 'min_size' => Team::minSize(), 'max_size' => Team::maxSize()];
    }

    // Everything that waits for the admin: new group/team requests and member change requests (no paging).
    public static function pending(){

        return ['teams' => (new Team())->paginate(['pending' => 1], 100, 0)];
    }

    // The teams the admin can give to a research: open and without an unfinished research.
    public static function available(){

        return ['teams' => (new Team())->paginate(['available' => 1], 200, 0)];
    }

    // The students who can join a team of this department and batch. A student only sees their own batch.
    public static function students($actor, $query){

        $departmentId = (int) ($query['department_id'] ?? 0);
        $batchId      = (int) ($query['batch_id'] ?? 0);

        if ($actor['role'] === 'student') {
            $me = (new User())->findByRole('student', (int) $actor['id']);
            $departmentId = (int) ($me['department_id'] ?? 0);
            $batchId      = (int) ($me['batch_id'] ?? 0);
        }

        if ($departmentId < 1 || $batchId < 1) {
            return ['students' => []];
        }

        return ['students' => (new Team())->freeStudents(
            $departmentId, $batchId, trim($query['q'] ?? ''), (int) ($query['team_id'] ?? 0), $actor['role'] === 'student'
        )];
    }

    /**
     * Adds a team ($id = null) or changes its members.
     * $request = name, department_id, batch_id, students (list of student ids)
     */
    public static function save($actor, $request, $id = null){

        $model   = new Team();
        $isAdmin = $actor['role'] === 'admin';
        $current = null;
        $errors  = [];

        // ---------- who may do this, and which department and batch ----------

        if ($id !== null) {
            $current = $model->find((int) $id);

            if (!$current || (!$isAdmin && !$model->isMember((int) $id, (int) $actor['id']))) {
                return self::fail('This group/team cannot be changed.');
            }
            if ($current['status'] === 'closed') {
                return self::fail('This group/team is closed because its research was completed.');
            }
            if ($current['status'] === 'rejected') {
                return self::fail('This group/team request was rejected.');
            }
            if ($current['status'] === 'requested' && !$isAdmin) {
                return self::fail('This group/team is waiting for the administrator. It can be changed after it is approved.');
            }

            $departmentId = (int) $current['department_id'];
            $batchId      = (int) $current['batch_id'];
        } elseif ($isAdmin) {
            $departmentId = (int) ($request['department_id'] ?? 0);
            $batchId      = (int) ($request['batch_id'] ?? 0);
        } else {
            // A student can only make a team of their own department and batch.
            $me = (new User())->findByRole('student', (int) $actor['id']);
            $departmentId = (int) ($me['department_id'] ?? 0);
            $batchId      = (int) ($me['batch_id'] ?? 0);

            if ($departmentId < 1 || $batchId < 1) {
                return self::fail('Your department and batch are not set. Please contact the administrator.');
            }
            if ($model->hasGroup((int) $actor['id'])) {
                return self::fail('You already have a group/team. A student can have only one group/team for now.');
            }
        }

        // ---------- the rules ----------

        // A student cannot rename a team, so the name is only checked when it is going to be saved.
        $name = null;
        if ($id === null || $isAdmin) {
            $validate = Validation::validate($request, ['name' => 'required|max:100'], ['name' => 'Group/Team name']);
            $errors = $validate['errors'];
            $name = trim($request['name'] ?? '');
        }

        if ($id === null && $isAdmin) {
            if (!(new Department())->find($departmentId)) {
                $errors['department_id'][] = 'Please choose a department.';
            }

            $batch = (new Batch())->find($batchId);
            if (!$batch) {
                $errors['batch_id'][] = 'Please choose a batch.';
            } elseif ((int) $batch['department_id'] !== $departmentId) {
                $errors['batch_id'][] = 'This batch does not belong to the chosen department.';
            }
        }

        $studentIds = array_values(array_unique(array_map('intval', (array) ($request['students'] ?? []))));

        // A student who makes a team is always one of its members, and cannot take himself out later:
        // he can only swap his teammates.
        if ($id === null && !$isAdmin && !in_array((int) $actor['id'], $studentIds, true)) {
            $studentIds[] = (int) $actor['id'];
        }
        if ($id !== null && !$isAdmin && !in_array((int) $actor['id'], $studentIds, true)) {
            return self::fail('You cannot take yourself out of your group/team. You can only change your teammates.');
        }

        if (count($studentIds) < Team::minSize()) {
            $errors['students'][] = 'A group/team needs at least ' . Team::minSize() . ' members.';
        } elseif (count($studentIds) > Team::maxSize()) {
            $errors['students'][] = 'A group/team can have at most ' . Team::maxSize() . ' members.';
        } elseif (empty($errors['department_id']) && empty($errors['batch_id'])
            && $model->allowedCount($studentIds, $departmentId, $batchId, (int) $id, !$isAdmin) !== count($studentIds)) {
            $errors['students'][] = 'Every member must be an active student of this department and batch who is not in another group/team or working on another research.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        // ---------- save ----------

        if ($id === null) {
            $model->create($name, $departmentId, $batchId, (int) $actor['id'], $studentIds, $isAdmin ? 'open' : 'requested');

            if ($isAdmin) {
                Notifier::toUsers($studentIds, 'team', 'You are in a group/team', 'The administrator added you to the group/team "' . $name . '".', 'student/team.php', (int) $actor['id']);
            } else {
                Notifier::toAdmins('team', 'New group/team request', self::nameOf((int) $actor['id']) . ' asked for the group/team "' . $name . '" (' . count($studentIds) . ' students).', 'admin/university/teams/index.php', (int) $actor['id']);
            }

            return ['success' => true, 'errors' => [], 'message' => $isAdmin ? 'Group/Team created.' : 'Your group/team request was sent to the administrator.'];
        }

        $before = array_map(fn($member) => (int) $member['id'], $current['members']);

        // A student only asks: the administrator decides.
        if (!$isAdmin) {
            if ($model->pendingChange((int) $id)) {
                return self::fail('A member change request is already waiting for the administrator.');
            }
            if ($model->hasChangeRequest((int) $id)) {
                return self::fail('Your group/team already used its one member change request. Please contact the administrator.');
            }
            if (!array_diff($studentIds, $before) && !array_diff($before, $studentIds)) {
                return self::fail('Nothing was changed. Add or remove a student first.');
            }

            $model->createChange((int) $id, (int) $actor['id'], $studentIds);

            Notifier::toAdmins('team', 'Member change requested', self::nameOf((int) $actor['id']) . ' asked to change the members of "' . $current['name'] . '".', 'admin/university/teams/index.php', (int) $actor['id']);

            return ['success' => true, 'errors' => [], 'message' => 'Your member change request was sent to the administrator.'];
        }

        // The admin changes the group/team at once. A waiting request of the students is closed.
        self::applyMembers($actor, $model, $current, $name, $studentIds);
        Notifier::toUsers(array_merge($before, $studentIds), 'team', 'Group/Team changed', 'The administrator changed the members of the group/team "' . $current['name'] . '".', 'student/team.php', (int) $actor['id']);
        $pending = $model->pendingChange((int) $id);
        if ($pending) {
            $model->reviewChange((int) $pending['id'], 'rejected', 'The administrator changed the members directly.', (int) $actor['id']);
        }

        return ['success' => true, 'errors' => [], 'message' => 'Group/Team updated.'];
    }

    // Saves the new members of a team and writes who was added or removed in the history of its research.
    private static function applyMembers($actor, Team $model, array $team, ?string $name, array $studentIds): void
    {
        $before = array_map(fn($member) => (int) $member['id'], $team['members']);
        $model->update((int) $team['id'], $name, $studentIds);

        $researchId = $model->activeResearchId((int) $team['id']);
        if ($researchId !== null) {
            $activity = new ResearchActivity();
            foreach (array_diff($studentIds, $before) as $studentId) {
                $activity->add($researchId, (int) $actor['id'], 'member_added', self::nameOf($studentId));
            }
            foreach (array_diff($before, $studentIds) as $studentId) {
                $activity->add($researchId, (int) $actor['id'], 'member_removed', self::nameOf($studentId));
            }
        }
    }

    /**
     * Admin only: approves or rejects a member change request of a student.
     * $request = id (of the change request), decision (approve | reject), note (needed for reject)
     */
    public static function reviewChange($actor, $request){

        $model  = new Team();
        $change = $model->findChange((int) ($request['id'] ?? 0));

        if (!$change || $change['status'] !== 'pending') {
            return self::fail('This request was already reviewed.');
        }

        if (($request['decision'] ?? '') === 'reject') {
            $note = trim($request['note'] ?? '');
            if ($note === '' || mb_strlen($note) > 500) {
                return ['success' => false, 'errors' => ['note' => ['Please write the reason (up to 500 characters).']], 'message' => 'Please write the reason for rejecting.'];
            }

            $model->reviewChange((int) $change['id'], 'rejected', $note, (int) $actor['id']);

            $team = $model->find((int) $change['team_id']);
            Notifier::toUsers(array_merge([(int) $change['requested_by']], array_column($team['members'] ?? [], 'id')), 'team', 'Member change rejected', 'The member change of "' . ($team['name'] ?? 'your group/team') . '" was rejected: ' . $note, 'student/team.php', (int) $actor['id']);

            return ['success' => true, 'errors' => [], 'message' => 'The member change was rejected.'];
        }

        if (($request['decision'] ?? '') !== 'approve') {
            return self::fail('Unknown decision.');
        }

        $team = $model->find((int) $change['team_id']);
        if (!$team || $team['status'] !== 'open') {
            return self::fail('This group/team is no longer open.');
        }

        // The students may have joined another group since the request was made.
        $ids = $change['member_ids'];
        if (count($ids) < Team::minSize() || count($ids) > Team::maxSize()
            || $model->allowedCount($ids, (int) $team['department_id'], (int) $team['batch_id'], (int) $team['id']) !== count($ids)) {
            return self::fail('The new members are not all free any more (or the size is wrong). Please reject this request.');
        }

        self::applyMembers($actor, $model, $team, null, $ids);
        $model->reviewChange((int) $change['id'], 'approved', null, (int) $actor['id']);
        Notifier::toUsers(array_merge(array_column($team['members'], 'id'), $ids), 'team', 'Member change approved', 'The member change of "' . $team['name'] . '" was approved.', 'student/team.php', (int) $actor['id']);

        return ['success' => true, 'errors' => [], 'message' => 'The member change was approved.'];
    }

    /**
     * Admin only: approves or rejects a requested team.
     * $request = id, decision (approve | reject), note (needed for reject)
     */
    public static function review($actor, $request){

        $model = new Team();
        $team  = $model->find((int) ($request['id'] ?? 0));

        if (!$team || $team['status'] !== 'requested') {
            return self::fail('This request was already reviewed.');
        }

        if (($request['decision'] ?? '') === 'reject') {
            $note = trim($request['note'] ?? '');
            if ($note === '' || mb_strlen($note) > 500) {
                return ['success' => false, 'errors' => ['note' => ['Please write the reason (up to 500 characters).']], 'message' => 'Please write the reason for rejecting.'];
            }

            $model->review((int) $team['id'], 'rejected', $note);
            Notifier::toUsers(array_column($team['members'], 'id'), 'team', 'Group/Team rejected', 'Your group/team "' . $team['name'] . '" was rejected: ' . $note, 'student/team.php', (int) $actor['id']);

            return ['success' => true, 'errors' => [], 'message' => 'The request was rejected.'];
        }

        if (($request['decision'] ?? '') !== 'approve') {
            return self::fail('Unknown decision.');
        }

        // Somebody may have joined another group since the request was made.
        $ids = array_map(fn($member) => (int) $member['id'], $team['members']);
        if ($model->allowedCount($ids, (int) $team['department_id'], (int) $team['batch_id'], (int) $team['id']) !== count($ids)) {
            return self::fail('A member is no longer free (already in another group/team or working on a research). Please change the members first.');
        }

        $model->review((int) $team['id'], 'open', null);
        Notifier::toUsers($ids, 'team', 'Group/Team approved', 'Your group/team "' . $team['name'] . '" was approved. You can request a research topic now.', 'student/team.php', (int) $actor['id']);

        return ['success' => true, 'errors' => [], 'message' => 'Group/Team approved.'];
    }

    // Admin only. A team that was used for a research is kept for the history.
    public static function delete($request){

        $model = new Team();
        $team  = $model->find((int) ($request['id'] ?? 0));

        if (!$team) {
            return self::fail('This group/team no longer exists.');
        }
        if ($model->hasResearch((int) $team['id'])) {
            return self::fail('This group/team has a research, so it cannot be deleted.');
        }

        $model->softDelete((int) $team['id']);

        return ['success' => true, 'message' => 'Group/Team deleted.'];
    }

    private static function nameOf(int $userId): string
    {
        $user = (new User())->findActiveById($userId);

        return $user['name'] ?? 'A student';
    }

    private static function fail(string $message): array
    {
        return ['success' => false, 'errors' => [], 'message' => $message];
    }
}
