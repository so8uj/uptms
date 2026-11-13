<?php

namespace App\Controllers;

use App\Models\Committee;
use App\Models\Department;
use App\Models\User;
use App\Services\Validation;

/**
 * Called by api/committees.php (admin only). It only works with data and returns arrays.
 * The admin makes the committees and chooses their members. The members then decide on topics,
 * requests to finish and groups/teams (see api/committee.php).
 */
class CommitteeController {

    private const PER_PAGE = 10;

    public static function index($query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        $model = new Committee();
        $total = $model->count($search, $status);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'committees' => $model->paginate($search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'perPage'    => self::PER_PAGE,
        ];
    }

    // The lists the form needs: the departments for the scope and the teachers for the members.
    public static function options(){

        return [
            'departments' => (new Department())->options(),
            'teachers'    => (new User())->teacherOptions(),
        ];
    }

    /**
     * $id is null when adding. $request = name, department_id ('' = the whole university), status, members = [teacher ids],
     * approvers = [the teacher ids of the members who can approve], chair_id
     * Returns ['success' => bool, 'errors' => [field => [messages]], 'message' => text for the toast]
     */
    public static function save($request, $id = null){

        $model = new Committee();

        if ($id !== null && !$model->find((int) $id)) {
            return ['success' => false, 'errors' => [], 'message' => 'This committee no longer exists.'];
        }

        $ignore = $id !== null ? ',' . (int) $id : '';

        $validate = Validation::validate($request, [
            'name'   => 'required|max:120|unique_active:committees,name' . $ignore,
            'status' => 'required|in:0,1',
        ]);
        $errors = $validate['errors'];

        // The scope: empty = the whole university, otherwise an active department.
        $departmentId = null;
        $rawDepartment = $request['department_id'] ?? '';
        if ($rawDepartment !== '' && $rawDepartment !== null) {
            $departmentId = (int) $rawDepartment;
            $known = array_column((new Department())->options(), 'id');

            if (!in_array($departmentId, array_map('intval', $known), true)) {
                $errors['department_id'][] = 'Please choose a valid department.';
            }
        }

        // The members: active teachers only. The list can be tampered with, so it is checked.
        $teacherIds = array_values(array_unique(array_filter(array_map('intval', is_array($request['members'] ?? null) ? $request['members'] : []))));
        if (!$teacherIds) {
            $errors['members'][] = 'Please add at least one member.';
        } elseif ($model->countActiveTeachers($teacherIds) !== count($teacherIds)) {
            $errors['members'][] = 'Please choose valid teachers.';
        }

        $chairId = (int) ($request['chair_id'] ?? 0);
        if ($chairId !== 0 && !in_array($chairId, $teacherIds, true)) {
            $errors['members'][] = 'The chair must be one of the members.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        // Only the ticked members can approve (any 2 of them decide). At least one must be ticked.
        $approverIds = array_values(array_unique(array_map('intval', is_array($request['approvers'] ?? null) ? $request['approvers'] : [])));
        if ($teacherIds && !array_intersect($approverIds, $teacherIds)) {
            $errors['members'][] = 'Please tick at least one member who can approve.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $members = array_map(fn($teacherId) => [
            'teacher_id'  => $teacherId,
            'role'        => $teacherId === $chairId ? 'chair' : 'member',
            'can_approve' => in_array($teacherId, $approverIds, true),
        ], $teacherIds);
        $name = trim($request['name']);

        if ($id === null) {
            $model->create($name, $departmentId, (int) $request['status'], $members);
            $message = 'Committee added.';
        } else {
            $model->update((int) $id, $name, $departmentId, (int) $request['status'], $members);
            $message = 'Committee updated.';
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    public static function delete($request){

        $model = new Committee();
        $committee = $model->find((int) ($request['id'] ?? 0));

        if (!$committee) {
            return ['success' => false, 'message' => 'This committee no longer exists.'];
        }

        $model->softDelete((int) $committee['id']);

        return ['success' => true, 'message' => 'Committee deleted.'];
    }
}
