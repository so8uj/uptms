<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));

$pageHeader = "Committee Guide";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('teacher/index.php'),
    "Thesis Committee" => "",
    "Committee Guide" => ""
];

$guide = require(base_path('includes/guide/content/committee.php'));
$guide['switch'] = [
    ['Teacher guide', 'teacher/guide.php', false],
    ['Committee guide', 'teacher/committee/guide.php', true],
];

?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));

    require(base_path('includes/guide/page.php'));
    ?>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
