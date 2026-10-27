<?php

namespace App\Controllers;

use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Department;
use App\Services\Validation;

/**
 * Called by api/batches.php. It only works with data and returns arrays (no HTML, no redirects).
 * The API file checks the role and the CSRF token before any method here runs.
 */
class BatchController {

    private const PER_PAGE = 10;

    public static function index($query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        $model = new Batch();
        $total = $model->count($search, $status);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'batches' => $model->paginate($search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => self::PER_PAGE,
        ];
    }

    // $id is null when adding and the batch id when editing.
    public static function save($request, $id = null){

        if ($id !== null && !(new Batch())->find((int) $id)) {
            return ['success' => false, 'errors' => [], 'message' => 'This batch no longer exists.'];
        }

        $ignore = $id !== null ? ',' . (int) $id : '';

        $validate = Validation::validate($request, [
            'name'                 => 'required|max:50|unique_active:batches,name' . $ignore,
            'code'                 => 'required|max:30|unique_active:batches,code' . $ignore,
            'department_id'        => 'required',
            'shift'                => 'required|in:Regular,Evening',
            'admission_session_id' => 'required',
            'description'          => 'max:255',
            'status'               => 'required|in:0,1',
        ], [
            'department_id'        => 'Department',
            'admission_session_id' => 'Admission session',
        ]);
        $errors = $validate['errors'];

        // ---------- checks that need the database or another field ----------

        // The dropdowns can be tampered with: the department must be real.
        if (empty($errors['department_id']) && !(new Department())->find((int) $request['department_id'])) {
            $errors['department_id'][] = 'Please choose a valid department.';
        }

        // The admission session must exist.
        if (empty($errors['admission_session_id']) && !(new AcademicSession())->find((int) $request['admission_session_id'])) {
            $errors['admission_session_id'][] = 'Please choose a valid session.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $data = [
            'name'                 => trim($request['name']),
            'code'                 => strtoupper(trim($request['code'])),
            'shift'                => $request['shift'],
            'admission_session_id' => (int) $request['admission_session_id'],
            'department_id'        => (int) $request['department_id'],
            'description'          => trim($request['description'] ?? '') ?: null,
            'status'               => (int) $request['status'],
        ];

        $model = new Batch();
        if ($id === null) {
            $model->create($data);
            $message = 'Batch added.';
        } else {
            $model->update((int) $id, $data);
            $message = 'Batch updated.';
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    // Soft deletes a batch, unless students are still in it.
    public static function delete($request){

        $id    = (int) ($request['id'] ?? 0);
        $model = new Batch();

        if (!$model->find($id)) {
            return ['success' => false, 'message' => 'This batch no longer exists.'];
        }

        $students = $model->studentCount($id);
        if ($students > 0) {
            return ['success' => false, 'message' => "This batch has {$students} student(s), so it cannot be deleted."];
        }

        $model->softDelete($id);

        return ['success' => true, 'message' => 'Batch deleted.'];
    }

}
