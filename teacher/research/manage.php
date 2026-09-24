<?php
require_once(__DIR__ . '/../../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$isCreate = page_action() === 'create'; $pageHeader = $isCreate ? "Submit Research" : "Edit My Research"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Research Library" => url('teacher/research/index.php'), $pageHeader => ""];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <?php require_once(base_path('includes/research/research_form.php')); ?>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>