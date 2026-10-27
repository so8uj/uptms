<?php

/**
 * System settings API (admin only). Always answers with JSON.
 *
 *   GET  api/settings.php   {"settings": {...}, "multi_department": false}
 *   POST api/settings.php   {"institution_name":"...", "team_min_size":"2", ...}   saves all the settings
 */

require_once(__DIR__ . '/../app/bootstrap.php');

use App\Controllers\SettingController;
use App\Services\Api;

Api::start();
Api::requireRole('admin');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    Api::json(SettingController::show());
}

if ($method === 'POST') {
    Api::requireCsrf();
    $result = SettingController::save(Api::body());
    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
