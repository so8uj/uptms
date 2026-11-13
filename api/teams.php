<?php

/**
 * Teams API (admin, student and the thesis committee). Always answers with JSON.
 *
 *   GET  api/teams.php?q=nova&status=open&department_id=1&batch_id=3&page=2   list (a student sees only their teams)
 *   GET  api/teams.php?id=5                                                    one team with its members
 *   GET  api/teams.php?pending=1                                               admin: everything that waits for a decision (new requests + member changes)
 *   GET  api/teams.php?available=1&q=nova&department_id=1&batch_id=3          admin, committee: the teams that can take a supervisor or a research (filters are optional)
 *   GET  api/teams.php?students=1&department_id=1&batch_id=3&q=rah&team_id=5  the students who can join (team_id = the team being edited)
 *   POST api/teams.php   {"op":"save", "id":5, "name":"Team Nova", "department_id":1, "batch_id":3, "students":[4,7,9]}
 *                        (leave "id" out to add a team)                    admin, student
 *   POST api/teams.php   {"op":"review", "id":5, "decision":"approve"}   admin only (decision: approve | reject, "note" for reject)
 *   POST api/teams.php   {"op":"review_change", "id":3, "decision":"approve"}   admin only: a student's member change request (id = the request)
 *   POST api/teams.php   {"op":"delete", "id":5}      admin only
 *
 * "save" with an id by a student does not change the members: it sends a change request for the admin.
 *
 * A student's new team is "requested" until the admin approves it. Only an approved ("open") team can take a research.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\TeamController;
use App\Services\Api;

Api::start();
Api::requireRole('admin', 'student', 'teacher');
$actor = Auth::currentUser();

// A teacher is let in only as a member of a thesis committee, and only to see the groups that can get a supervisor.
$isCommittee = $actor['role'] === 'teacher';
if ($isCommittee && !Auth::isCommitteeMember()) {
    Api::json(['message' => 'Only a member of the thesis committee can do this.'], 403);
}
if ($isCommittee && (empty($_GET['available']) || $_SERVER['REQUEST_METHOD'] !== 'GET')) {
    Api::json(['message' => 'You are not allowed to do this.'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$deny = fn() => Api::json(['message' => 'You are not allowed to do this.'], 403);

if ($method === 'GET') {
    if (!empty($_GET['students'])) {
        $actor['role'] === 'teacher' ? $deny() : Api::json(TeamController::students($actor, $_GET));
    }

    if (!empty($_GET['pending'])) {
        $actor['role'] === 'student' ? $deny() : Api::json(TeamController::pending($actor));
    }

    if (!empty($_GET['available'])) {
        $actor['role'] === 'student' ? $deny() : Api::json(TeamController::available($actor, $_GET));
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
    $op = $body['op'] ?? '';

    if ($op === 'save' && !$isCommittee) {
        $id = !empty($body['id']) ? (int) $body['id'] : null;
        $result = TeamController::save($actor, $body, $id);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if ($op === 'review' && $actor['role'] === 'admin') {
        $result = TeamController::review($actor, $body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if ($op === 'review_change' && $actor['role'] === 'admin') {
        $result = TeamController::reviewChange($actor, $body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    if ($op === 'delete' && $actor['role'] === 'admin') {
        $result = TeamController::delete($body);
        Api::json($result, $result['success'] ? 200 : 422);
    }

    in_array($op, ['save', 'review', 'review_change', 'delete'], true) ? $deny() : Api::json(['message' => 'Unknown action.'], 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
