<?php

/**
 * Research catalog API (admin only). Always answers with JSON.
 * The admin manages the catalog. The decisions (approve, correction, reject, finish) belong to the thesis committee
 * (api/committee.php). The admin may assign a supervisor: to an approved topic, or to a group without any research.
 *
 *   GET  api/research.php?status=review&q=ai&category_id=1&page=2   list (status: '' all, one status, review = requested + pending, progress = approved + ongoing)
 *   GET  api/research.php?id=5                                      one research (for the edit form)
 *   GET  api/research.php?completion=1                              the requests to finish a research that wait for the admin
 *   GET  api/research.php?activity=5                               the history of research 5 (every action, oldest first)
 *   POST api/research.php   {"op":"save", "id":5, "title":"...", "abstract":"...", "type":"Thesis", "category_id":1,
 *                            "tags":[1,2], "year":2026, "status":"completed", "supervisor_id":7, "students":[3,4]}
 *                           (leave "id" out to add a new research)
 *   POST api/research.php   {"op":"review", "id":5, "decision":"assign", "supervisor_id":7}   (an approved topic gets its supervisor)
 *   POST api/research.php   {"op":"assign_group", "team_id":4, "supervisor_id":7}   (a supervisor for a group, without a research)
 *   POST api/research.php   {"op":"delete", "id":5}
 *   GET  api/research.php?impact=5                              what a permanent delete would remove (counts)
 *   GET  api/research.php?trash=1                               admin only: the deleted research (the trash)
 *   POST api/research.php   {"op":"restore", "id":5}            admin only: take a research out of the trash
 *   POST api/research.php   {"op":"purge", "id":5}              admin only: delete the research and ALL its records for ever
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
    if (isset($_GET['completion'])) {
        Api::json(ResearchController::completionList());
    }

    if (isset($_GET['trash'])) {
        $trash = ResearchController::trash();
        $trash ? Api::json($trash) : Api::json(['message' => 'Only the administrator can do this.'], 403);
    }

    if (isset($_GET['impact'])) {
        // Only the real administrator (a research manager teacher is not enough).
        if ((Auth::currentUser()['role'] ?? '') !== 'admin') {
            Api::json(['message' => 'Only the administrator can do this.'], 403);
        }

        $impact = ResearchController::purgeImpact((int) $_GET['impact']);
        $impact ? Api::json($impact) : Api::json(['message' => 'This research no longer exists.'], 404);
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
        'assign_group' => ResearchController::assignGroup($actor, $body),
        'delete'   => ResearchController::delete($actor, $body),
        'purge'    => ResearchController::purge($body),
        'restore'  => ResearchController::restore($actor, $body),
        default    => null,
    };

    if ($result === null) {
        Api::json(['message' => 'Unknown action.'], 400);
    }

    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
