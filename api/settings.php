<?php

/**
 * System settings API (admin only). Always answers with JSON.
 *
 *   GET  api/settings.php   {"settings": {...}, "multi_department": false}
 *   POST api/settings.php   {"institution_name":"...", "team_min_size":"2", ...}   saves all the settings
 *                           (mail_password: empty keeps the saved SMTP password, which is stored encrypted)
 *   POST api/settings.php   {"op":"test_mail", "to":"me@example.com"}   one test email with the saved mail settings
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
    $body = Api::body();

    if (($body['op'] ?? '') === 'test_mail') {
        $result = SettingController::testMail($body, \App\Controllers\Auth::currentUser());
        Api::json($result, $result['success'] ? 200 : 422);
    }

    $result = SettingController::save($body);
    Api::json($result, $result['success'] ? 200 : 422);
}

Api::json(['message' => 'Method not allowed.'], 405);
