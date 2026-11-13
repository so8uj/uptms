<?php

/**
 * The thesis committee API: only a teacher who is a member of an active thesis committee. Always answers with JSON.
 * It has the same shape as api/research.php, so the research lists of the admin pages can be used here too.
 * A member only sees (and signs) the research of the departments the committee works for. 2 signatures decide.
 *
 *   GET  api/committee.php?status=review&q=ai&page=2     the research list (status: review = waiting topics, progress = approved + ongoing, ...)
 *   GET  api/committee.php?id=5                          one research
 *   GET  api/committee.php?completion=1                  the requests to finish a research that wait for the committee
 *   GET  api/committee.php?activity=5                    the history of research 5
 *   POST api/committee.php   {"op":"review", "id":5, "decision":"approve"}   my signature on a topic (or "correction" / "reject"
 *                             with a "note"; or "assign" with a "supervisor_id" for an approved topic). 2 signatures decide.
 *   POST api/committee.php   {"op":"completion", "request_id":3, "decision":"approve"}   my signature on a request to finish (or "reject" with a "note")
 *   POST api/committee.php   {"op":"assign_group", "team_id":4, "supervisor_id":7}        a supervisor for a group, without a research
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
$actor = Api::requireCommittee();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['completion'])) {
        Api::json(ResearchController::completionList($actor));
    }

    if (isset($_GET['activity'])) {
        $history = ResearchController::activity($actor, (int) $_GET['activity']);
        $history ? Api::json($history) : Api::json(['message' => 'This research no longer exists.'], 404);
    }

    if (isset($_GET['id'])) {
        $item = ResearchController::find($actor, (int) $_GET['id']);
        $item ? Api::json(['research' => $item]) : Api::json(['message' => 'This research no longer exists.'], 404);
    }

    Api::json(ResearchController::adminIndex($_GET, $actor));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();
    $op = $body['op'] ?? '';

    $result = match ($op) {
        'review'       => ResearchController::review($actor, $body),
        'completion'   => ResearchController::completionDecision($actor, $body),
        'assign_group' => ResearchController::assignGroup($actor, $body),
        default        => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
