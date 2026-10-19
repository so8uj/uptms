<?php

/**
 * The research library (admin, teacher and student). Read only. Always answers with JSON.
 *
 *   GET api/library.php?q=iot&category_id=2&type=Thesis&year=2026&include_ongoing=1&page=2
 *
 * It lists completed research, and ongoing research too when include_ongoing=1.
 * Research that is requested, pending or rejected is never listed here.
 * The answer also has "counts" (how many completed and ongoing), "categories" (how many per category) and "years".
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\ResearchController;
use App\Services\Api;

Api::start();
Api::requireRole('admin', 'teacher', 'student');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

Api::json(ResearchController::libraryIndex($_GET));
