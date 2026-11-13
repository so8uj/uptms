<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Assign Supervisor";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Assign Supervisor" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_assignPanel('<?= url('api/teams.php') ?>', '<?= url('api/research.php') ?>', '<?= url('api/research_options.php') ?>', '<?= url('api/options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <?php require(base_path('includes/committee/assign_panel.php')); ?>
    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/committee.js?v=<?= filemtime(base_path('assets/admin_assets/js/committee.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
