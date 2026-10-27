<?php

namespace App\Controllers;

use App\Models\Department;
use App\Services\Validation;

/**
 * Called by api/departments.php. It only works with data and returns arrays (no HTML, no redirects).
 * The API file checks the role and the CSRF token before any method here runs.
 */
class DepartmentController {

    private const PER_PAGE = 10;

    public static function index($query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        $model = new Department();
        $total = $model->count($search, $status);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'departments' => $model->paginate($search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'       => $total,
            'page'        => $page,
            'pages'       => $pages,
            'perPage'     => self::PER_PAGE,
        ];
    }

    // $id is null when adding and the department id when editing.
    public static function save($request, $id = null){

        if (!multi_department()) {
            return ['success' => false, 'errors' => [], 'message' => 'Departments cannot be added or changed. This project works for one department.'];
        }

        if ($id !== null && !(new Department())->find((int) $id)) {
            return ['success' => false, 'errors' => [], 'message' => 'This department no longer exists.'];
        }

        $ignore = $id !== null ? ',' . (int) $id : '';

        $validate = Validation::validate($request, [
            'name'        => 'required|max:100|unique_active:departments,name' . $ignore,
            'code'        => 'required|max:20|unique_active:departments,code' . $ignore,
            'description' => 'max:255',
            'status'      => 'required|in:0,1',
        ]);
        if (!$validate['success']) {
            $validate['message'] = 'Please fix the errors below.';
            return $validate;
        }

        $name        = trim($request['name']);
        $code        = strtoupper(trim($request['code']));
        $description = trim($request['description'] ?? '');
        $status      = (int) $request['status'];

        $model = new Department();
        if ($id === null) {
            $model->create($name, $code, $description, $status);
            $message = 'Department added.';
        } else {
            $model->update((int) $id, $name, $code, $description, $status);
            $message = 'Department updated.';
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    // Soft deletes a department, unless batches, teachers or students still use it.
    public static function delete($request){

        if (!multi_department()) {
            return ['success' => false, 'message' => 'Departments cannot be deleted. This project works for one department.'];
        }

        $id    = (int) ($request['id'] ?? 0);
        $model = new Department();

        if (!$model->find($id)) {
            return ['success' => false, 'message' => 'This department no longer exists.'];
        }

        $usage = $model->usage($id);
        if (array_sum($usage) > 0) {
            return ['success' => false, 'message' => "This department is still used by {$usage['batches']} batch(es), "
                . "{$usage['teachers']} teacher(s) and {$usage['students']} student(s), so it cannot be deleted."];
        }

        $model->softDelete($id);

        return ['success' => true, 'message' => 'Department deleted.'];
    }

}
