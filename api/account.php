<?php

/**
 * Profile (admin, teacher and student). Always answers with JSON. It works on the logged-in user only.
 *
 *   GET  api/account.php                                         my details
 *   POST api/account.php   {"op":"update", "name":"...", "phone":"..."}   change my name and phone
 *   POST api/account.php   a form (FormData): op=photo and the file "photo"   change my picture (jpg, png, webp, up to 2 MB)
 *   POST api/account.php   {"op":"password", "old_password":"...", "new_password":"...", "new_password_again":"..."}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\AccountController;
use App\Controllers\Auth;
use App\Services\Api;

Api::start();
Api::requireRole('admin', 'teacher', 'student');
$actor = Auth::currentUser();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $account = AccountController::show($actor);
    $account ? Api::json($account) : Api::json(['message' => 'Your account was not found.'], 404);
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    $result = match ($body['op'] ?? '') {
        'update'   => AccountController::update($actor, $body),
        'password' => AccountController::changePassword($actor, $body),
        'photo'    => AccountController::photo($actor, $_FILES),
        default    => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
