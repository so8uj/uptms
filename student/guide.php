<?php

require_once(__DIR__ . '/../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "User Guide";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "User Guide" => ""
];

$guide = require(base_path('includes/guide/content/student.php'));

?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));

    require(base_path('includes/guide/page.php'));
    ?>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
