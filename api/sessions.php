<?php

/**
 * Academic sessions API (admin only). Always answers with JSON.
 *
 *   GET  api/sessions.php?q=2026&status=1&type=semester&page=2   list (search, status, type, page)
 *   POST api/sessions.php   {"op":"save", "id":5, "name":"Spring 2026", "term_type":"semester", "year":2026,
 *                            "start_date":"2026-01-05", "end_date":"2026-05-30", "is_current":"1", "status":"1"}
 *                           (leave "id" out to add a new session)
 *   POST api/sessions.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\AcademicSessionController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(AcademicSessionController::index($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'delete') {
        $result = AcademicSessionController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = AcademicSessionController::save($body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
