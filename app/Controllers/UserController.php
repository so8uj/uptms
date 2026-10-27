<?php

namespace App\Controllers;

use App\Models\Batch;
use App\Models\Department;
use App\Models\User;
use App\Services\FileUpload;
use App\Services\Validation;

/**
 * One controller for the three kinds of user (admin, teacher, student).
 * The role is always passed in, and api/users.php only passes one of the three real roles.
 *
 * Called by api/users.php. It only works with data and returns arrays (no HTML, no redirects).
 * The API file checks the admin role and the CSRF token before any method here runs.
 */
class UserController {

    private const PER_PAGE = 10;

    private const LABELS = ['admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student'];

    private const DESIGNATIONS = ['Professor', 'Associate Professor', 'Assistant Professor', 'Senior Lecturer', 'Lecturer'];

    // The users of one role: the current page plus what the page needs to draw the page numbers.
    public static function index($role, $query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        // the batch filter (students) and the sorting; the model ignores a sort it does not know
        $options = [
            'batch_id' => (int) ($query['batch_id'] ?? 0),
            'sort'     => $query['sort'] ?? '',
            'dir'      => $query['dir'] ?? 'asc',
        ];

        $model = new User();
        $total = $model->count($role, $search, $status, $options);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        return [
            'users'   => $model->paginate($role, $search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE, $options),
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'perPage' => self::PER_PAGE,
        ];
    }

    // One user for the edit form. Returns null when the user does not exist (or was deleted).
    public static function find($role, $id){
        return (new User())->findByRole($role, (int) $id);
    }

    /**
     * Saves the form. $id is null when adding and the user id when editing.
     * $files is $_FILES (the photo).
     * Returns ['success' => bool, 'errors' => [field => [messages]], 'message' => text for the toast]
     */
    public static function save($role, $request, $files, $id = null){

        $label = self::LABELS[$role];
        $model = new User();
        $current = null;

        if ($id !== null) {
            $current = $model->findByRole($role, (int) $id);
            if (!$current) {
                return ['success' => false, 'errors' => [], 'message' => "This {$role} no longer exists."];
            }
        }

        // ---------- 1. the rules ----------

        $ignore = $id !== null ? ',' . (int) $id : '';

        $rules = [
            'name'     => 'required|max:100',
            'email'    => 'required|email|max:150|unique:users,email' . $ignore,
            'phone'    => 'max:20',
            'status'   => 'required|in:0,1',
            'password' => $id === null ? 'required|min:8|max:72' : 'min:8|max:72',
        ];

        if ($role === 'teacher') {
            $rules += [
                'employee_id'      => 'required|max:30|unique:teacher_profiles,employee_id' . ($id !== null ? $ignore . ',user_id' : ''),
                'department_id'    => 'required',
                'designation'      => 'required|in:' . implode(',', self::DESIGNATIONS),
                'specialization'   => 'max:150',
                'research_area'    => 'max:150',
                'research_management' => 'in:0,1',
            ];
        }

        if ($role === 'student') {
            $rules += [
                'student_id'             => 'required|max:30|unique:student_profiles,student_id' . ($id !== null ? $ignore . ',user_id' : ''),
                'department_id'          => 'required',
                'batch_id'               => 'required',
                'gender'                 => 'in:Male,Female,Other',
                'cgpa'                   => 'numeric|between:0,4',
                'completed_credit_hours' => 'integer|between:0,400',
            ];
        }

        $validate = Validation::validate($request, $rules, [
            'employee_id'            => 'Employee ID',
            'student_id'             => 'Student ID',
            'department_id'          => 'Department',
            'batch_id'               => 'Batch',
            'cgpa'                   => 'CGPA',
            'completed_credit_hours' => 'Completed credit hours',
        ]);
        $errors = $validate['errors'];

        // ---------- 2. checks that need the database ----------

        // The department and batch come from dropdowns, which can be tampered with: check they are real.
        if (isset($rules['department_id']) && empty($errors['department_id'])) {
            if (!(new Department())->find((int) $request['department_id'])) {
                $errors['department_id'][] = 'Please choose a valid department.';
            }
        }

        if ($role === 'student' && empty($errors['batch_id'])) {
            $batch = (new Batch())->find((int) $request['batch_id']);

            if (!$batch) {
                $errors['batch_id'][] = 'Please choose a valid batch.';
            } elseif (empty($errors['department_id']) && (int) $batch['department_id'] !== (int) $request['department_id']) {
                $errors['batch_id'][] = 'This batch does not belong to the chosen department.';
            }
        }

        // An admin must not lock themselves out, or leave the system without an active admin.
        if ($role === 'admin' && $id !== null && (string) ($request['status'] ?? '') === '0') {
            if ((int) $id === self::currentUserId()) {
                $errors['status'][] = 'You cannot deactivate your own account.';
            } elseif ((int) $current['status'] === 1 && $model->activeAdminCount() <= 1) {
                $errors['status'][] = 'This is the last active admin, so it cannot be deactivated.';
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        // ---------- 3. the photo (only when everything else is valid, so no unused file is left behind) ----------

        $photoPath = null;
        $photo = $files['photo'] ?? null;

        if ($photo && ($photo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUpload::save($photo, 'photos', 'image');

            if (!$upload['success']) {
                return ['success' => false, 'errors' => ['photo' => [$upload['error']]], 'message' => 'Please fix the errors below.'];
            }
            $photoPath = $upload['path'];
        }

        // ---------- 4. save ----------

        $account = [
            'name'     => trim($request['name']),
            'email'    => trim($request['email']),
            'phone'    => trim($request['phone'] ?? ''),
            'status'   => (int) $request['status'],
            'password' => $request['password'] ?? '',
            'photo'    => $photoPath,
        ];

        $profile = [];
        if ($role === 'teacher') {
            $profile = [
                'employee_id'      => trim($request['employee_id']),
                'department_id'    => (int) $request['department_id'],
                'designation'      => $request['designation'],
                'specialization'   => trim($request['specialization'] ?? ''),
                'research_area'    => trim($request['research_area'] ?? ''),
                'research_management' => (int) ($request['research_management'] ?? 0),
            ];
        }
        if ($role === 'student') {
            $profile = [
                'student_id'             => trim($request['student_id']),
                'department_id'          => (int) $request['department_id'],
                'batch_id'               => (int) $request['batch_id'],
                'gender'                 => $request['gender'] ?? '',
                'cgpa'                   => trim($request['cgpa'] ?? ''),
                'completed_credit_hours' => trim($request['completed_credit_hours'] ?? ''),
            ];
        }

        try {
            if ($id === null) {
                $model->createAccount($role, $account, $profile);
                $message = "{$label} added.";
            } else {
                $model->updateAccount($role, (int) $id, $account, $profile);
                $message = "{$label} updated.";

                // The new photo replaces the old one, so the old file is no longer needed.
                if ($photoPath && !empty($current['photo'])) {
                    FileUpload::delete($current['photo']);
                }
            }
        } catch (\Throwable $e) {
            // Saving failed after the photo was uploaded: do not leave the file behind.
            FileUpload::delete($photoPath);
            throw $e;
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    // Soft deletes a user. Not yourself, and not the last active admin.
    public static function delete($role, $request){

        $id    = (int) ($request['id'] ?? 0);
        $model = new User();
        $user  = $model->findByRole($role, $id);

        if (!$user) {
            return ['success' => false, 'message' => 'This ' . $role . ' no longer exists.'];
        }

        if ($id === self::currentUserId()) {
            return ['success' => false, 'message' => 'You cannot delete your own account.'];
        }

        if ($role === 'admin' && (int) $user['status'] === 1 && $model->activeAdminCount() <= 1) {
            return ['success' => false, 'message' => 'This is the last active admin, so it cannot be deleted.'];
        }

        $model->softDelete($id);

        return ['success' => true, 'message' => self::LABELS[$role] . ' deleted.'];
    }

    private static function currentUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }

}
