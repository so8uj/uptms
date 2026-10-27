<?php
/*
 * The shared page of Evaluations, Progress Updates, Milestones and Feedback (teacher and student).
 * Without an id: the list of my projects (Project | Thesis | Research Paper tabs).
 * With an id: that one project with the four tabs, opened on the tab of this page.
 *
 * The page file sets: $role (teacher | student), $pageHeader, $file (for example 'feedback.php'),
 * $tab (evaluations | activity | milestones | comments), then requires this file.
 */

require_once(base_path('admin/includes/header.php'));
require_once(base_path($role . '/includes/sidebar.php'));

$researchId = (int) ($_GET['id'] ?? 0);

$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url($role . '/index.php'),
    $pageHeader => $researchId > 0 ? url($role . '/' . $file) : ""
];
if ($researchId > 0) {
    $breadcrumbs["Project"] = "";
}

$api = url('api/project.php');
$insideUrl = url($role . '/' . $file . '?id=');
$backUrl = url($role . '/' . $file);
$workUrl = url($role . '/project.php?id=');
$projectUrl = $workUrl;

?>

<?php if ($researchId > 0): ?>

<div class="main-wrapper" x-data="Sobuj_projectInside('<?= $api ?>', <?= $researchId ?>, '<?= $tab ?>')">
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
