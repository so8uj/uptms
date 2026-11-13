<?php

/**
 * Thesis committees API (admin only: the admin makes the committees). Always answers with JSON.
 *
 *   GET  api/committees.php?q=thesis&status=1&page=2   list (search, status filter, page)
 *   GET  api/committees.php?options=1                  the departments and the teachers for the form
 *   POST api/committees.php   {"op":"save", "id":5, "name":"...", "department_id":"" (the whole university) or 1,
 *                              "status":"1", "members":[7,9], "chair_id":7}     (leave "id" out to add a committee)
 *   POST api/committees.php   {"op":"delete", "id":5}
 *
 * What the members do is in api/committee.php.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\CommitteeController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['options'])) {
        Api::json(CommitteeController::options());
    }

    Api::json(CommitteeController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = CommitteeController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = CommitteeController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
