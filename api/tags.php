<?php

/**
 * Research tags API (admin only). Always answers with JSON.
 *
 *   GET  api/tags.php?q=ai&status=1&page=2   list (search, status filter, page)
 *   POST api/tags.php   {"op":"save", "id":5, "name":"...", "description":"...", "status":"1"}
 *                             (leave "id" out to add a new tag)
 *   POST api/tags.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\TagController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(TagController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = TagController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = TagController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
