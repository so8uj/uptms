<?php

/**
 * The notifications of the logged-in user (admin, teacher and student). Always answers with JSON.
 *
 *   GET  api/notifications.php?filter=unread&page=2&per_page=8   my notifications (filter: all | unread)
 *   GET  api/notifications.php?count=1                           {"unread": 3} only the number for the bell
 *   POST api/notifications.php   {"op":"read", "id":5}           mark one as read
 *   POST api/notifications.php   {"op":"unread", "id":5}         mark one as unread again
 *   POST api/notifications.php   {"op":"read_all"}               mark all of mine as read
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\NotificationController;
use App\Services\Api;

Api::start();
Api::requireRole('admin', 'teacher', 'student');
$actor = Auth::currentUser();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['count'])) {
        Api::json(NotificationController::unread($actor));
    }

    Api::json(NotificationController::index($actor, $_GET));
}

if ($method === 'POST') {
    Api::requireCsrf();
    $result = NotificationController::change($actor, Api::body());
    Api::json($result, $result['success'] ? 200 : 400);
}

Api::json(['message' => 'Method not allowed.'], 405);
