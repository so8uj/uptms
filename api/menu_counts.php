<?php

/**
 * The numbers shown on the sidebar menu badges (admin, teacher and student). Read only. Always answers with JSON.
 *
 *   GET api/menu_counts.php   ->   admin:           { "new_requests": 2, "need_supervisor": 1, "in_progress": 6 }
 *                                  student/teacher: { "requested": 1, "ongoing": 2 }  (their own research only)
 *                                  a committee member also gets committee_topics, committee_completion, committee_groups, committee_assign
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
Api::requireRole('admin', 'teacher', 'student');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

$user = Auth::currentUser();

Api::json(ResearchController::menuCounts($user));
