<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> University Project & Thesis Management System | Login</title>

  <!-- SEO Optimization -->
  <meta name="description" content=" University Project & Thesis Management System | Login">
  <meta name="author" content="Spark Admin Team">

  <!-- CSRF token: the page JavaScript sends it back in the X-CSRF-Token header when it saves data -->
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <meta name="app-url" content="<?= url() ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= env('ASSET_URL') ?>/images/favicon.ico">

  <!-- Local Third-Party Libraries (100% Offline Compatible) -->
  <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/libs/bootstrap-icons/bootstrap-icons.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="<?= env('ASSET_URL') ?>/admin_assets/css/main.css?v=<?= filemtime(base_path('assets/admin_assets/css/main.css')) ?>">

  <!-- Alpine.js (local copy). "defer" is required so Alpine starts after the page HTML is ready. -->
  <script defer src="<?= env('ASSET_URL') ?>/admin_assets/libs/alpinejs/alpine.min.js"></script>
</head>

<body>