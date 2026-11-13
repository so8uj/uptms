<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Research Details";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Ongoing Research" => url('admin/research/ongoing.php'),
    "Details" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchAdminView('<?= url('api/research.php') ?>', <?= (int) ($_GET['id'] ?? 0) ?>)">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <?php $detailsBack = url('admin/research/ongoing.php'); $detailsAdmin = true; require(base_path('includes/research/details_body.php')); ?>
    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
