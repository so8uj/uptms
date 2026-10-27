<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

// Evaluation: without an id the list of all projects; with an id that one project, with its evaluations and progress updates.
$researchId = (int) ($_GET['id'] ?? 0);

$pageHeader = "Evaluation";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Evaluation" => $researchId > 0 ? url('admin/evaluation/index.php') : ""
];
if ($researchId > 0) {
    $breadcrumbs["Project"] = "";
}

$api = url('api/project_admin.php');
$insideUrl = url('admin/evaluation/index.php?id=');
$backUrl = url('admin/evaluation/index.php');
$workUrl = url('admin/research/project.php?id=');
$projectUrl = $workUrl;

?>

<?php if ($researchId > 0): ?>

<div class="main-wrapper" x-data="Sobuj_projectInside('<?= $api ?>', <?= $researchId ?>, 'evaluations')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('includes/project/inside.php')); ?>
</div>

<?php else: ?>

<div class="main-wrapper" x-data="Sobuj_projectList('<?= $api ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('includes/project/project_list.php')); ?>
</div>

<?php endif; ?>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/project.js?v=<?= filemtime(base_path('assets/admin_assets/js/project.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
