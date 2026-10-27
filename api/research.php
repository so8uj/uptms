<?php

/**
 * Research catalog API (admin only). Always answers with JSON.
 *
 *   GET  api/research.php?status=review&q=ai&category_id=1&page=2   list (status: '' all, one status, review = requested + pending, progress = approved + ongoing)
 *   GET  api/research.php?id=5                                      one research (for the edit form)
 *   GET  api/research.php?completion=1                              the requests to finish a research that wait for the admin
 *   GET  api/research.php?activity=5                               the history of research 5 (every action, oldest first)
 *   POST api/research.php   {"op":"save", "id":5, "title":"...", "abstract":"...", "type":"Thesis", "category_id":1,
 *                            "tags":[1,2], "year":2026, "status":"completed", "supervisor_id":7, "students":[3,4]}
 *                           (leave "id" out to add a new research)
 *   POST api/research.php   {"op":"review", "id":5, "decision":"approve", "status":"ongoing", "supervisor_id":7}
 *   POST api/research.php   {"op":"review", "id":5, "decision":"reject", "note":"The topic is too broad."}
 *   POST api/research.php   {"op":"review", "id":5, "decision":"assign", "supervisor_id":7}   (an approved topic gets its supervisor)
 *   POST api/research.php   {"op":"complete", "id":5}
 *   POST api/research.php   {"op":"completion", "request_id":3, "decision":"approve"}   (or "reject" with a "note")
 *   POST api/research.php   {"op":"delete", "id":5}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
$actor = Api::requireRoleOrManager('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['completion'])) {
        Api::json(ResearchController::completionList());
    }

    if (isset($_GET['activity'])) {
        $history = ResearchController::activity($actor, (int) $_GET['activity']);
        $history ? Api::json($history) : Api::json(['message' => 'This research no longer exists.'], 404);
    }

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
        'review'   => ResearchController::review($actor, $body),
        'complete' => ResearchController::complete($actor, $body),
        'completion' => ResearchController::completionDecision($actor, $body),
        'delete'   => ResearchController::delete($actor, $body),
        default    => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
