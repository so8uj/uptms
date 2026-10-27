<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Work";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Ongoing Research" => url('admin/research/ongoing.php'),
    "Project Work" => ""
];

$backUrl = url('admin/research/ongoing.php');

?>

<div class="main-wrapper" x-data="Sobuj_projectWorkspace('<?= url('api/project_admin.php') ?>', <?= (int) ($_GET['id'] ?? 0) ?>)">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <div class="alert alert-info py-2 small">You are reading this project. Only the supervisor and the team can change it.</div>

    <?php require(base_path('includes/project/workspace.php')); ?>
    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/project.js?v=<?= filemtime(base_path('assets/admin_assets/js/project.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
