<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\Validation;

class Auth {

    // Wrong logins allowed in a row, and how long the account stays locked after that: both are on the Settings page.
    private static function maxAttempts(): int
    {
        return Setting::int('login_max_attempts', 5);
    }

    private static function lockMinutes(): int
    {
        return Setting::int('login_lock_minutes', 5);
    }

    private static array $portals = [
        'admin'   => 'admin/index.php',
        'teacher' => 'teacher/index.php',
        'student' => 'student/index.php',
    ];

    /**
     * Always returns ['success' => true/false, 'errors' => [...]].
     * On success it also returns 'redirect' with the page the user must be sent to.
     */
    public static function attempt($request){

        // Step 0: the form must carry our CSRF token (proves the form came from our own site).
        if (!csrf_verify($request)) {
            return self::fail('csrf', 'Your session expired. Please refresh the page and try again.');
        }

        // Step 1: validate the form input.
        $validate = Validation::validate($request,[
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validate['success']) {
            return $validate;
        }

        // Step 2: find the user by email. The role is not asked on the form; it comes from the account itself.
        $email = trim($request['email']);
        $user = (new User())->findByEmail($email);

        // Step 3: rate limit. A locked account cannot log in, even with the correct password.
        if ($user && $user['lock_seconds'] > 0) {
            $minutes = (int) ceil($user['lock_seconds'] / 60);
            return self::fail('password', "Too many failed attempts. Please try again in {$minutes} minute(s).");
        }

        // Step 4: check the password.
        // One shared message, so nobody can tell whether the email exists or the password was wrong.
        if (!$user || !password_verify($request['password'], $user['password'])) {
            if ($user) {
                // Count this failure. After MAX_ATTEMPTS the account is locked for LOCK_MINUTES.
                (new User())->recordFailedAttempt((int) $user['id'], self::maxAttempts(), self::lockMinutes());
            }
            return self::fail('password', 'Invalid email or password.');
        }

        // Step 5: inactive accounts cannot log in.
        if ((int) $user['status'] !== 1) {
            return self::fail('email', 'This account is inactive. Please contact the administrator.');
        }

        // Maintenance mode: only the administrator may log in.
        if (self::inMaintenanceFor($user['role'])) {
            return self::fail('email', Setting::get('maintenance_message'));
        }

        // Step 6: log the user in by saving their details in the session.
        // The redirect below sends them to the portal of their own role (admin / teacher / student).
        session_regenerate_id(true);   // new session id after login (stops session fixation)
        $_SESSION['user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        (new User())->clearFailedAttempts((int) $user['id']);
        (new User())->touchLastLogin((int) $user['id']);

        return [
            'success'  => true,
            'errors'   => [],
            'redirect' => url(self::$portals[$user['role']]),
        ];
    }

    /**
     * Put this at the top of every panel page: Auth::requireRole('admin');
     * - not logged in          -> back to the login page
     * - logged in, wrong role  -> sent to their own portal
     * - correct role           -> the page continues to load
     */
    // True when the maintenance mode is on and this role is not the administrator.
    public static function inMaintenanceFor(string $role): bool
    {
        return $role !== 'admin' && Setting::bool('maintenance_mode');
    }

    public static function requireRole(string $role): void
    {
        // Tell the browser not to keep a copy, so the Back button cannot show a page after logout.
        header('Cache-Control: no-store');

        $user = self::currentUser();

        if (!$user) {
            // Not logged in, or the account was deleted or deactivated since the login: end the session.
            self::logout();
            header('Location: ' . url('index.php'));
            exit;
        }

        if ($user['role'] !== $role) {
            header('Location: ' . url(self::$portals[$user['role']]));
            exit;
        }
    }

    /**
     * The logged-in user, read from the database (so the name and photo are always current).
     * Returns null when nobody is logged in, or when the account is deleted, inactive or its role changed.
     * The navbar uses this to show the name and photo.
     */
    public static function currentUser(): ?array
    {
        static $loaded = false;
        static $user = null;

        if (empty($_SESSION['user']['id'])) {
            return null;
        }

        if (!$loaded) {
            $loaded = true;
            $found = (new User())->findActiveById((int) $_SESSION['user']['id']);

            // The role stored at login must still match the database.
            $user = ($found && $found['role'] === $_SESSION['user']['role']) ? $found : null;
        }

        return $user;
    }

    /**
     * Called once from bootstrap.php, so no panel page needs its own guard line.
     * The role is decided by the folder of the page being opened:
     *   /admin/...   needs role admin
     *   /teacher/... needs role teacher
     *   /student/... needs role student
     * Pages outside these folders (login, logout) are not touched.
     */
    public static function guardPanel(): void
    {
        // Full path of the page being opened, with / slashes, for example D:/Xampp/htdocs/UPTMS/admin/index.php
        $page = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        $root = str_replace('\\', '/', base_path());

        // Only look at pages inside our project folder (the comparison ignores upper/lower case, for Windows).
        if (stripos($page, $root . '/') !== 0) {
            return;
        }

        // Cut the project folder off the front, then take the first folder name: "admin/index.php" -> "admin"
        $relative = substr($page, strlen($root) + 1);
        $folder = explode('/', $relative)[0];

        if (array_key_exists($folder, self::$portals)) {
            // Maintenance mode: everybody except the administrator is sent back to the login page.
            $who = $_SESSION['user']['role'] ?? null;
            if ($who !== null && self::inMaintenanceFor($who)) {
                self::logout();
                header('Location: ' . url('index.php'));
                exit;
            }

            // A teacher with the research management permission may open some pages of the admin panel.
            if ($folder === 'admin' && self::managerMayOpen($relative)) {
                header('Cache-Control: no-store');
                return;
            }

            self::requireRole($folder);
        }
    }

    // The admin pages a research manager (a teacher with research_management) may open.
    private const MANAGER_PAGES = ['admin/research/', 'admin/university/batches/', 'admin/university/teams/'];

    private static function managerMayOpen(string $relative): bool
    {
        foreach (self::MANAGER_PAGES as $folder) {
            if (str_starts_with($relative, $folder)) {
                return self::isResearchManager();
            }
        }

        return false;
    }

    // Is the logged-in user a teacher who has the research management permission?
    public static function isResearchManager(): bool
    {
        static $answer = null;

        if ($answer === null) {
            $user = self::currentUser();
            $answer = $user && $user['role'] === 'teacher' && (new User())->hasResearchManagement((int) $user['id']);
        }

        return $answer;
    }

    // Used by the login page: a user who is already logged in does not need the login form.
    public static function redirectIfLoggedIn(): void
    {
        $user = self::currentUser();

        if ($user) {
            header('Location: ' . url(self::$portals[$user['role']]));
            exit;
        }
    }

    // Ends the session completely: data, cookie and the session itself.
    public static function logout(): void
    {
        $_SESSION = [];

        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);

        session_destroy();
    }

    // Builds an error result in the same shape that Validation returns.
    private static function fail(string $field, string $message): array
    {
        return ['success' => false, 'errors' => [$field => [$message]]];
    }

}
