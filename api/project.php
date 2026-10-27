<?php

/**
 * The project of an ongoing research: milestones, tasks, comments and evaluations. Always answers with JSON.
 *
 *   GET  api/project.php            my projects (teacher: the ones I supervise, student: the ones I work on) + numbers
 *   GET  api/project.php?id=5       everything of project 5 (404 when this user may not open it)
 *   GET  api/project.php?view=milestones|comments|evaluations|activity|alerts   one kind of list across all my projects (alerts = what is new)
 *   POST api/project.php   {"op":"milestone_add", "id":5, "title":"...", "description":"...", "due_date":"2026-12-01"}
 *   POST api/project.php   {"op":"milestone_delete", "milestone_id":3}
 *   POST api/project.php   {"op":"task_add", "milestone_id":3, "title":"..."}
 *   POST api/project.php   {"op":"task_toggle", "task_id":7}        (finish it, or open it again)
 *   POST api/project.php   {"op":"task_delete", "task_id":7}
 *   POST api/project.php   {"op":"comment_add", "id":5, "body":"...", "milestone_id":3}   (milestone_id = feedback on that milestone)
 *   POST api/project.php   {"op":"seen_topics"}                    (the My Research page was opened)
 *   POST api/project.php   {"op":"comment_delete", "comment_id":2}   (only your own)
 *   POST api/project.php   {"op":"evaluation_add", "id":5, "rating":4, "remarks":"..."}   (supervisor only)
 *
 * The admin can read every project but cannot change one. Nothing can be changed once the research is completed.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\ProjectController;
use App\Services\Api;

Api::start();
// api/project_admin.php sets PROJECT_AS_ADMIN: the administrator and a research manager see every project there.
if (defined('PROJECT_AS_ADMIN')) {
    $actor = Api::requireRoleOrManager('admin');
} else {
    Api::requireRole('admin', 'teacher', 'student');
    $actor = Auth::currentUser();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['view'])) {
        $list = ProjectController::feed($actor, $_GET['view'], (int) ($_GET['research_id'] ?? 0));
        $list ? Api::json($list) : Api::json(['message' => 'This list cannot be opened.'], 404);
    }

    if (isset($_GET['id'])) {
        $project = ProjectController::show($actor, (int) $_GET['id']);
        $project ? Api::json($project) : Api::json(['message' => 'This project cannot be opened.'], 404);
    }

    Api::json(ProjectController::index($actor));
}

if ($method === 'POST') {
    Api::requireCsrf();

    $result = ProjectController::change($actor, Api::body());
    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
