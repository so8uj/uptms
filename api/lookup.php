<?php

/**
 * Search helper for the admin forms (admin only, read only).
 *
 *   GET api/lookup.php?q=rah   ->   { "students": [{id, name, student_id}] }   (at most 10 matches)
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

Api::json(ResearchController::lookupStudents($_GET));
