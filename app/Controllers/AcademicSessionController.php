<?php

namespace App\Controllers;

use App\Models\AcademicSession;
use App\Services\Validation;

/**
 * Called by api/sessions.php. It only works with data and returns arrays (no HTML, no redirects).
 * The API file checks the role and the CSRF token before any method here runs.
 */
class AcademicSessionController {

    private const PER_PAGE = 10;

    public static function index($query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        $model = new AcademicSession();
        $total = $model->count($search, $status);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'sessions' => $model->paginate($search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'perPage'  => self::PER_PAGE,
        ];
    }

    // $id is null when adding and the session id when editing.
    public static function save($request, $id = null){

        $model   = new AcademicSession();
        $current = null;

        if ($id !== null) {
            $current = $model->find((int) $id);
            if (!$current) {
                return ['success' => false, 'errors' => [], 'message' => 'This session no longer exists.'];
            }
        }

        $ignore = $id !== null ? ',' . (int) $id : '';

        $validate = Validation::validate($request, [
            'name'       => 'required|max:50|unique_active:academic_sessions,name' . $ignore,
            'year'       => 'required|integer|between:2000,2100',
            'is_current' => 'in:0,1',
            'status'     => 'required|in:0,1',
        ], ['is_current' => 'Current session']);
        $errors = $validate['errors'];

        // An inactive session cannot be the current one.
        if ((string) ($request['is_current'] ?? '0') === '1' && (string) ($request['status'] ?? '') === '0') {
            $errors['is_current'][] = 'An inactive session cannot be the current session.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $data = [
            'name'       => trim($request['name']),
            'year'       => (int) $request['year'],
            'is_current' => (int) ($request['is_current'] ?? 0),
            'status'     => (int) $request['status'],
        ];

        if ($id === null) {
            $model->create($data);
            $message = 'Session added.';
        } else {
            $model->update((int) $id, $data);
            $message = 'Session updated.';
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    // Soft deletes a session, unless batches still use it.
    public static function delete($request){

        $id    = (int) ($request['id'] ?? 0);
        $model = new AcademicSession();

        if (!$model->find($id)) {
            return ['success' => false, 'message' => 'This session no longer exists.'];
        }

        $batches = $model->batchCount($id);
        if ($batches > 0) {
            return ['success' => false, 'message' => "This session is used by {$batches} batch(es), so it cannot be deleted."];
        }

        $model->softDelete($id);

        return ['success' => true, 'message' => 'Session deleted.'];
    }

}
