<?php

/**
 * Teams API (admin and student). Always answers with JSON.
 *
 *   GET  api/teams.php?q=nova&status=open&department_id=1&batch_id=3&page=2   list (a student sees only their teams)
 *   GET  api/teams.php?id=5                                                    one team with its members
 *   GET  api/teams.php?pending=1                                               admin: everything that waits for a decision (new requests + member changes)
 *   GET  api/teams.php?available=1                                             admin: the teams a research can take
 *   GET  api/teams.php?students=1&department_id=1&batch_id=3&q=rah&team_id=5  the students who can join (team_id = the team being edited)
 *   POST api/teams.php   {"op":"save", "id":5, "name":"Team Nova", "department_id":1, "batch_id":3, "students":[4,7,9]}
 *                        (leave "id" out to add a team)
 *   POST api/teams.php   {"op":"review", "id":5, "decision":"approve"}   admin only (decision: approve | reject, "note" for reject)
 *   POST api/teams.php   {"op":"review_change", "id":3, "decision":"approve"}   admin only: a student's member change request (id = the request)
 *   POST api/teams.php   {"op":"delete", "id":5}      admin only
 *
 * "save" with an id by a student does not change the members: it sends a change request for the admin.
 *
 * A student's new team is "requested" until the admin approves it. Only an approved ("open") team can take a research.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\TeamController;
use App\Services\Api;

Api::start();
$actor = Api::requireRoleOrManager('admin', 'student');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['students'])) {
        Api::json(TeamController::students($actor, $_GET));
    }

    if (!empty($_GET['pending'])) {
        $actor['role'] === 'admin' ? Api::json(TeamController::pending()) : Api::json(['message' => 'You are not allowed to do this.'], 403);
    }

    if (!empty($_GET['available'])) {
        $actor['role'] === 'admin' ? Api::json(TeamController::available()) : Api::json(['message' => 'You are not allowed to do this.'], 403);
    }

    if (!empty($_GET['id'])) {
        $team = TeamController::one($actor, $_GET['id']);
        $team ? Api::json($team) : Api::json(['message' => 'This team cannot be opened.'], 404);
    }

    Api::json(TeamController::index($actor, $_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    if (($body['op'] ?? '') === 'save') {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = TeamController::save($actor, $body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'review' && $actor['role'] === 'admin') {
        $result = TeamController::review($actor, $body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'review_change' && $actor['role'] === 'admin') {
        $result = TeamController::reviewChange($actor, $body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if (($body['op'] ?? '') === 'delete' && $actor['role'] === 'admin') {
        $result = TeamController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
