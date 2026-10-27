<?php

namespace App\Services;

/**
 * Small helper for the files in /api. Every API file starts with:
 *
 *     Api::start();                 // JSON errors only, never HTML
 *     Api::requireRole('admin');    // who may use this endpoint
 *
 * and answers with Api::json([...], 200).
 */
class Api
{
    // Makes sure a PHP error can never print HTML into a JSON answer.
    public static function start(): void
    {
        ini_set('display_errors', '0');

        set_exception_handler(function (\Throwable $e) {
            error_log('API error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            self::json(['message' => 'Something went wrong on the server.'], 500);
        });
    }

    // Sends the JSON answer and stops. $status is the HTTP code (200 ok, 422 invalid input, 401, 403, ...).
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Only logged-in users with one of these roles may continue.
    public static function requireRole(string ...$roles): void
    {
        // Read from the database, so a deleted or deactivated account is refused straight away.
        $user = \App\Controllers\Auth::currentUser();

        if (!$user) {
            self::json(['message' => 'Please log in again.'], 401);
        }

        self::refuseInMaintenance($user);

        if (!in_array($user['role'], $roles, true)) {
            self::json(['message' => 'You are not allowed to do this.'], 403);
        }
    }

    // Maintenance mode: only the administrator may use the API.
    private static function refuseInMaintenance(array $user): void
    {
        if (\App\Controllers\Auth::inMaintenanceFor($user['role'])) {
            self::json(['message' => \App\Models\Setting::get('maintenance_message')], 503);
        }
    }

    /**
     * Like requireRole, and returns the logged-in user. A teacher with the research management permission is
     * also let in where 'admin' is allowed: they get the user back with the role 'admin', so the controllers
     * treat them as the administrator. Their id stays their own, so the history shows who did it.
     */
    public static function requireRoleOrManager(string ...$roles): array
    {
        $user = \App\Controllers\Auth::currentUser();

        if (!$user) {
            self::json(['message' => 'Please log in again.'], 401);
        }

        self::refuseInMaintenance($user);

        if (in_array($user['role'], $roles, true)) {
            return $user;
        }

        if (in_array('admin', $roles, true) && \App\Controllers\Auth::isResearchManager()) {
            $user['role'] = 'admin';
            return $user;
        }

        self::json(['message' => 'You are not allowed to do this.'], 403);
    }

    // For every request that changes data. The page sends the token in the X-CSRF-Token header.
    public static function requireCsrf(): void
    {
        $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!csrf_verify(['csrf_token' => $sent])) {
            // 403 (not a custom code like 419: Apache turns unknown status codes into 500).
            self::json([
                'code'    => 'csrf',
                'message' => 'Your session expired. Please refresh the page and try again.',
            ], 403);
        }
    }

    // What the browser sent, as an array. A form with a file (FormData) arrives in $_POST; everything else is JSON.
    public static function body(): array
    {
        if (str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data')) {
            return $_POST;
        }

        $body = json_decode(file_get_contents('php://input'), true);

        if (!is_array($body)) {
            self::json(['message' => 'Invalid request.'], 400);
        }

        return $body;
    }
}
