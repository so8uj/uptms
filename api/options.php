<?php

/**
 * The lists that fill the dropdowns in the admin forms (admin only, read only).
 *
 *   GET api/options.php   ->   { "departments": [...], "batches": [...], "sessions": [...] }
 *
 * Only active (not deleted, not inactive) rows are listed.
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Department;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Api::json(['message' => 'Method not allowed.'], 405);
}

Api::json([
    'departments' => (new Department())->options(),
    'batches'     => (new Batch())->options(),
    'sessions'    => (new AcademicSession())->options(),
]);
