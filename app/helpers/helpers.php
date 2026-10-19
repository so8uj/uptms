<?php

// Loading Environment Variables from .env file
if (class_exists(Dotenv\Dotenv::class)) {
    $rootPath = dirname(__DIR__, 2);
    if (file_exists($rootPath . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($rootPath);
        $dotenv->load();
    }
}
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

// Get Directory, File Name, Paths, Page Custom Actions
if (!function_exists('current_folder')) {
    function current_folder(): string
    {
        return basename(dirname($_SERVER['SCRIPT_NAME']));
    }
}
if (!function_exists('current_file')) {
    function current_file(): string
    {
        return basename($_SERVER['SCRIPT_NAME']);
    }
}
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $root = dirname(__DIR__, 2);
        $path = ltrim($path, '/\\');
        return $root . ($path === '' ? '' : DIRECTORY_SEPARATOR . $path);
    }
}

if(!function_exists('page_action')) {
    function page_action(): string
    {
        if (isset($_GET['action'])) {
            $action = strtolower($_GET['action']);
            if (in_array($action, ['create', 'update'])) {
                return $action;
            }
        }
        return $_GET['action'] ?? 'create';
    }
}

// Generating Absolute URL for routes
if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
        $path = ltrim($path, '/');
        return $path === '' ? $baseUrl : "{$baseUrl}/{$path}";
    }
}

// Returns what the user typed in a form field, so we can show it again after a failed submit.
if (!function_exists('old')) {
    function old(string $field, string $default = ''): string
    {
        // If the field was not submitted (for example the first page load), use the default.
        $value = $_POST[$field] ?? $default;

        // htmlspecialchars stops typed HTML/JavaScript from running on the page (XSS protection).
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Prints every error message of one field. Prints nothing when the field has no errors.
if (!function_exists('error')) {
    function error(string $field, array $errors): void
    {
        if (!isset($errors[$field])) {
            return;
        }

        foreach ($errors[$field] as $message) {
            echo '<div class="text-danger small mt-1">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
        }
    }
}

// CSRF protection: a secret token that is placed in every form and checked when the form is submitted.
// It proves the form came from our own site and not from another website.

// Returns the token of this visitor. It is created once and kept in the session.
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Prints the hidden input. Put this line inside every <form>.
if (!function_exists('csrf_field')) {
    function csrf_field(): void
    {
        echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

// Checks the token that came with the form. Use it at the start of every controller that handles a POST.
if (!function_exists('csrf_verify')) {
    function csrf_verify(array $request): bool
    {
        $sent = $request['csrf_token'] ?? '';

        return is_string($sent) && hash_equals(csrf_token(), $sent);
    }
}

// Flash message: a one-time message that survives a redirect (for example "Category added.").
// flash() saves it in the session, show_flash() prints it once and then forgets it.
if (!function_exists('flash')) {
    function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
}
if (!function_exists('show_flash')) {
    function show_flash(): void
    {
        if (empty($_SESSION['flash'])) {
            return;
        }

        $type = $_SESSION['flash']['type'] === 'error' ? 'danger' : 'success';
        echo '<div class="alert alert-' . $type . ' py-2">' . htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8') . '</div>';

        unset($_SESSION['flash']);
    }
}

// The letters shown instead of a photo: "Dr. Jannatul Naeem" -> "JN", "Sobuj" -> "S".
// Titles like Dr and Prof are skipped. (The same rule as Sobuj_initials() in users.js.)
if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $titles = ['dr', 'prof', 'mr', 'mrs', 'ms'];
        $words = [];

        foreach (preg_split('/\s+/u', trim($name)) as $word) {
            $word = preg_replace('/^[^\p{L}\p{N}]+/u', '', $word);   // "(Student)" -> "Student)"

            if ($word !== '' && !in_array(strtolower(str_replace('.', '', $word)), $titles, true)) {
                $words[] = $word;
            }
        }

        if (!$words) {
            return '?';
        }

        $first = mb_strtoupper(mb_substr($words[0], 0, 1));

        return count($words) === 1 ? $first : $first . mb_strtoupper(mb_substr($words[1], 0, 1));
    }
}
