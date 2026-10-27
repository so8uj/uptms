<?php

/**
 * Batches API (admin only). Always answers with JSON.
 *
 *   GET  api/batches.php?q=65&status=1&page=2   list (search, status filter, page)
 *   POST api/batches.php   {"op":"save", "id":5, "name":"65B", "code":"CSE-65B", "session":"2025-2026",
 *                           "department_id":1, "description":"...", "status":"1"}
 *                          (leave "id" out to add a new batch)
 *   POST api/batches.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\BatchController;
use App\Services\Api;

Api::start();
Api::requireRoleOrManager('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(BatchController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = BatchController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = BatchController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
