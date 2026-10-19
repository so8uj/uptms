<?php

/**
 * The lists that fill the research forms (admin, teacher and student). Read only.
 *
 *   GET api/research_options.php   ->   { "categories": [...], "tags": [...], "types": [...] }
 *                                       and, for the admin only, "teachers": [...] for the supervisor dropdown
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

Api::json(ResearchController::options(Auth::currentUser()));
