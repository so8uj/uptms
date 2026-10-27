<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// APP_DEBUG in .env: true shows PHP errors on the page (development). false (the default, for the real site)
// hides them from the visitors; they are still written to the server error log.
ini_set('display_errors', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN) ? '1' : '0');
ini_set('log_errors', '1');

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);
    session_start();

    \App\Controllers\Auth::guardPanel();
}
