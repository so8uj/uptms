<?php

/**
 * A student's or teacher's own research. Always answers with JSON.
 *
 *   GET  api/my_research.php?status=requested&page=2   my research (submitted, supervised or worked on)
 *   GET  api/my_research.php?id=5                      one of my research that can still be edited
 *   POST api/my_research.php   {"op":"save", "id":5, "title":"...", "abstract":"...", "type":"Thesis",
 *                               "category_id":1, "tags":[1,2], "year":2026, "target_status":"ongoing"}
 *                              (a student sends only title, abstract, type, category_id and tags;
 *                               a teacher also sends year and target_status; leave "id" out to add)
 *   POST api/my_research.php   {"op":"delete", "id":5}    (withdraws a research that was not approved yet)
 *
 * Whatever is added here waits for the administrator.
 * A teacher can add research only when the administrator gave that permission.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
Api::requireRole('teacher', 'student');
$actor = Auth::currentUser();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $item = ResearchController::find($actor, (int) $_GET['id']);
        $item ? Api::json(['research' => $item]) : Api::json(['message' => 'This research cannot be opened.'], 404);
    }

    Api::json(ResearchController::mine($actor, $_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();
    $op = $body['op'] ?? '';

    $result = match ($op) {
        'save'   => ResearchController::save($actor, $body, !empty($body['id']) ? (int) $body['id'] : null),
        'delete' => ResearchController::delete($actor, $body),
        default  => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
