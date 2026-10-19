<?php

/**
 * Admins, teachers and students API (admin only). Always answers with JSON.
 * The same endpoint serves the three kinds of user: the page says which one with "role".
 *
 *   GET  api/users.php?role=student&q=ali&status=1&page=2   list
 *   GET  api/users.php?role=student&id=5                    one user (for the edit form)
 *   POST api/users.php   save   (a form with the photo, sent as FormData)
 *                               role, op=save, id (leave out to add), name, email, ... and the photo file
 *   POST api/users.php   delete {"role":"student", "op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\UserController;
use App\Models\User;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $role = $_GET['role'] ?? '';
    if (!in_array($role, User::ROLES, true)) {
        Api::json(['message' => 'Unknown user type.'], 400);
    }

    if (isset($_GET['id'])) {
        $user = UserController::find($role, (int) $_GET['id']);
        $user ? Api::json(['user' => $user]) : Api::json(['message' => 'This ' . $role . ' no longer exists.'], 404);
    }

    Api::json(UserController::index($role, $_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    // The role decides which rules and which table are used, so only the three real values are accepted.
    $role = $body['role'] ?? '';
    if (!in_array($role, User::ROLES, true)) {
        Api::json(['message' => 'Unknown user type.'], 400);
    }

    if (($body['op'] ?? '') === 'delete') {
        $result = UserController::delete($role, $body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = UserController::save($role, $body, $_FILES, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
