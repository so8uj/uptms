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

