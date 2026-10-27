<?php

require_once(__DIR__ . '/app/bootstrap.php');

use App\Controllers\Auth;

Auth::redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authAttempt = Auth::attempt($_POST);

    if ($authAttempt['success']) {
        header('Location: ' . $authAttempt['redirect']);
        exit;
    }

    $errors = $authAttempt['errors'];

}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> University Project & Thesis Management System | Login</title>

    <!-- SEO Optimization -->
    <meta name="description" content=" University Project & Thesis Management System | Login">
    <meta name="author" content="Spark Admin Team">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= env('ASSET_URL') ?>/images/favicon.ico">

    <!-- Local Third-Party Libraries (100% Offline Compatible) -->
    <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap-icons/bootstrap-icons.css">

    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/css/main.css">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>


        <div class="login-card">


            <a href="<?= url('index.php') ?>" class="login-brand text-decoration-none">
                <img src="<?= env('ASSET_URL') ?>/images/wub-logo.png" alt="WORLD UNIVERSITY OF BANGLADESH" width="400" />
            </a>

            <?php if (\App\Models\Setting::bool('maintenance_mode')): ?>
                <div class="alert alert-warning small py-2"><?= htmlspecialchars(\App\Models\Setting::get('maintenance_message')) ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" id="loginForm" class="needs-validation">
                <?php csrf_field(); ?>
                <?php error('csrf', $errors); ?>

                <!-- Email Input Group -->
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email Address</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="login-input" placeholder="name@company.com" required autocomplete="username" value="<?= old('email') ?>">
                    </div>
                    <?php error('email', $errors); ?>
                </div>

                <!-- Password Input Group -->
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="login-input login-input-password"
                            placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password"
                            aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php error('password', $errors); ?>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Sign In to Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>




        </div>
    </div>
    <script src="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= env('ASSET_URL') ?>/admin_assets/js/auth.js?v=<?= filemtime(base_path('assets/admin_assets/js/auth.js')) ?>"></script>

</body>

</html>