<?php

/**
 * Research categories API (admin only). Always answers with JSON.
 *
 *   GET  api/categories.php?q=ai&status=1&page=2   list (search, status filter, page)
 *   POST api/categories.php   {"op":"save", "id":5, "name":"...", "description":"...", "status":"1"}
 *                             (leave "id" out to add a new category)
 *   POST api/categories.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\CategoryController;
use App\Services\Api;

Api::start();
Api::requireRoleOrManager('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(CategoryController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = CategoryController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = CategoryController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
