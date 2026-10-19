<?php

/**
 * Departments API (admin only). Always answers with JSON.
 *
 *   GET  api/departments.php?q=cse&status=1&page=2   list (search, status filter, page)
 *   POST api/departments.php   {"op":"save", "id":5, "name":"...", "code":"CSE", "description":"...", "status":"1"}
 *                              (leave "id" out to add a new department)
 *   POST api/departments.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\DepartmentController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(DepartmentController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = DepartmentController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = DepartmentController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
