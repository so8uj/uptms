<?php

/**
 * CSV import of batches, teachers and students (admin only).
 *
 *   GET  api/import.php?type=students&sample=1    downloads the sample CSV file (type = batches | teachers | students)
 *                                                 optional: &department=CSE&session=Spring 2026&batch=CSE-66R fill the dummy rows with real values
 *   GET  api/import.php?type=students&reference=1 JSON for the "Columns & values" box: columns, departments, sessions, batches
 *   POST api/import.php   multipart: type, file (the .csv)   ->   {success, message, created, failed, errors:[{line, name, messages}]}
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\Auth;
use App\Controllers\ImportController;
use App\Services\Api;

Api::start();
Api::requireRoleOrManager('admin');
$managerOnly = Auth::currentUser()['role'] !== 'admin';   // a research manager may only import batches

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    if (!ImportController::isType($type) || ($managerOnly && $type !== 'batches')) {
        Api::json(['message' => 'Unknown file type.'], 400);
    }

    if (!empty($_GET['reference'])) {
        Api::json(ImportController::reference($type));
    }

    [$name, $csv] = ImportController::sample($type, [
        'department' => $_GET['department'] ?? '',
        'session'    => $_GET['session'] ?? '',
        'batch'      => $_GET['batch'] ?? '',
    ]);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Cache-Control: no-store');
    echo $csv;
    exit;
}

if ($method === 'POST') {
    Api::requireCsrf();
    $body = Api::body();

    $type = $body['type'] ?? '';
    if (!ImportController::isType($type)) {
        Api::json(['message' => 'Unknown file type.'], 400);
    }
    if ($managerOnly && $type !== 'batches') {
        Api::json(['message' => 'You are not allowed to do this.'], 403);
    }

    $result = ImportController::run($type, $_FILES['file'] ?? null);
    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
