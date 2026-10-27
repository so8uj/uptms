<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\FileUpload;
use App\Services\Validation;

/**
 * "Profile" of the logged-in user, any role (api/account.php): see my details, change my name and phone,
 * change my password. The email stays with the administrator.
 * Every method works on the logged-in user only: there is no id in the request that could be changed.
 */
class AccountController {

    // My details with the profile of my role (department, batch ...).
    public static function show($actor){

        $user = (new User())->findByRole($actor['role'], (int) $actor['id']);

        if (!$user) {
            return null;
        }

        return ['account' => array_merge($user, ['role' => $actor['role'], 'email' => $actor['email']])];
    }

    // The name and the phone number.
    public static function update($actor, $request){

        $validate = Validation::validate($request, ['name' => 'required|max:100', 'phone' => 'max:20'], ['name' => 'Name']);
        if (!$validate['success']) {
            return ['success' => false, 'errors' => $validate['errors'], 'message' => 'Please fix the errors below.'];
        }

        (new User())->updateProfile((int) $actor['id'], trim($request['name']), trim($request['phone'] ?? '') ?: null);

        return ['success' => true, 'errors' => [], 'message' => 'Your profile was updated.'];
    }

    // The profile picture. $files is $_FILES. The old picture is deleted after the new one is saved.
    public static function photo($actor, $files){

        $file = $files['photo'] ?? null;

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'errors' => ['photo' => ['Please choose a picture.']], 'message' => 'Please choose a picture.'];
        }

        $upload = FileUpload::save($file, 'photos', 'image');
        if (!$upload['success']) {
            return ['success' => false, 'errors' => ['photo' => [$upload['error']]], 'message' => $upload['error']];
        }

        $model = new User();
        $old = $model->findByRole($actor['role'], (int) $actor['id'])['photo'] ?? null;

        try {
            $model->updatePhoto((int) $actor['id'], $upload['path']);
        } catch (\Throwable $e) {
            FileUpload::delete($upload['path']);   // do not leave the new file behind
            throw $e;
        }

        if ($old) {
            FileUpload::delete($old);
        }

        return ['success' => true, 'errors' => [], 'message' => 'Your picture was changed.'];
    }

    // The old password is needed, the new one has at least 8 characters.
    public static function changePassword($actor, $request){

        $validate = Validation::validate($request, [
            'old_password' => 'required',
            'new_password' => 'required|min:8|max:72',
        ], ['old_password' => 'Current password', 'new_password' => 'New password']);
        $errors = $validate['errors'];

        $users = new User();
        $user = $users->findByEmail($actor['email']);

        if (empty($errors['old_password']) && (!$user || !password_verify($request['old_password'], $user['password']))) {
            $errors['old_password'][] = 'The current password is not correct.';
        }

        if (empty($errors['new_password'])) {
            if (($request['new_password_again'] ?? '') !== $request['new_password']) {
                $errors['new_password_again'][] = 'The two new passwords are not the same.';
            } elseif (($request['old_password'] ?? '') === $request['new_password']) {
                $errors['new_password'][] = 'The new password must be different from the current one.';
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below.'];
        }

        $users->updatePasswordHash((int) $actor['id'], password_hash($request['new_password'], PASSWORD_DEFAULT));

        return ['success' => true, 'errors' => [], 'message' => 'Your password was changed.'];
    }
}
