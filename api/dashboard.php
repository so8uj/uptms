<?php

/**
 * The numbers on the admin dashboard (admin only). Read only. Always answers with JSON.
 *
 *   GET api/dashboard.php   ->   { "students": 12, "teachers": 3, "departments": 2, "batches": 4,
 *                                  "new_requests": 1, "need_supervisor": 0, "ongoing": 2, "completed": 5,
 *                                  "milestones": 6, "open_tasks": 4, "done_tasks": 9, "evaluations": 2 }
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Models\Dashboard;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

Api::json((new Dashboard())->adminNumbers());
