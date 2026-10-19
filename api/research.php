<?php

/**
 * Research catalog API (admin only). Always answers with JSON.
 *
 *   GET  api/research.php?status=review&q=ai&category_id=1&page=2   list (status: '' all, one status, or review)
 *   GET  api/research.php?id=5                                      one research (for the edit form)
 *   POST api/research.php   {"op":"save", "id":5, "title":"...", "abstract":"...", "type":"Thesis", "category_id":1,
 *                            "tags":[1,2], "year":2026, "status":"completed", "supervisor_id":7, "students":[3,4]}
 *                           (leave "id" out to add a new research)
 *   POST api/research.php   {"op":"review", "id":5, "decision":"approve", "status":"ongoing", "supervisor_id":7}
 *   POST api/research.php   {"op":"review", "id":5, "decision":"reject", "note":"The topic is too broad."}
 *   POST api/research.php   {"op":"complete", "id":5}
 *   POST api/research.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');
$actor = Auth::currentUser();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $item = ResearchController::find($actor, (int) $_GET['id']);
        $item ? Api::json(['research' => $item]) : Api::json(['message' => 'This research no longer exists.'], 404);
    }

    Api::json(ResearchController::adminIndex($_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();
    $op = $body['op'] ?? '';

    $result = match ($op) {
        'save'     => ResearchController::save($actor, $body, !empty($body['id']) ? (int) $body['id'] : null),
        'review'   => ResearchController::review($body),
        'complete' => ResearchController::complete($body),
        'delete'   => ResearchController::delete($actor, $body),
        default    => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
