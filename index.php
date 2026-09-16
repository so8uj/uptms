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
    <link rel="icon" type="image/png" href="./assets/images/favicon.ico">

    <!-- Local Third-Party Libraries (100% Offline Compatible) -->
    <link rel="stylesheet" href="./assets/admin_assets/libs/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/admin_assets/libs/bootstrap-icons/bootstrap-icons.css">

    <!-- Main Design System & Custom Stylesheet -->
    <link rel="stylesheet" href="./assets/admin_assets/css/main.css">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>


        <div class="login-card">


            <a href="index.html" class="login-brand text-decoration-none">
                <img src="./assets/images/wub-logo.png" alt="WORLD UNIVERSITY OF BANGLADESH" width="400" />
            </a>


            <!-- Login Form -->
            <form action="index.html" method="GET" id="loginForm" class="needs-validation" novalidate>
                <div class="login-form-group d-flex justify-content-center column-gap-2">

                    <div class="user-type">
                        <input type="radio" class="btn-check" name="options" id="Admin" autocomplete="off">
                        <label class="btn btn-secondary" for="Admin">Admin</label>
                    </div>
                    <div class="user-type">
                        <input type="radio" class="btn-check" name="options" id="Supervisor" autocomplete="off">
                        <label class="btn btn-secondary" for="Supervisor">Supervisor</label>
                    </div>
                    <div class="user-type">
                        <input type="radio" class="btn-check" name="options" id="Student" autocomplete="off" checked>
                        <label class="btn btn-secondary" for="Student">Student</label>
                    </div>
                </div>
                <!-- Email Input Group -->
                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email Address</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" class="login-input" placeholder="name@company.com" required>
                    </div>
                </div>

                <!-- Password Input Group -->
                <div class="login-form-group">
                    <label for="password" class="login-form-label">Password</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="password" class="login-input login-input-password"
                            placeholder="••••••••" required>
                        <button type="button" class="password-toggle-btn" id="toggle-password"
                            aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Sign In to Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>




        </div>
    </div>
    <script src="./assets/admin_assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/admin_assets/js/auth.js"></script>

</body>

</html>