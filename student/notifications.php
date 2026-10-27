<?php

require_once(__DIR__ . '/../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Notifications";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Notifications" => ""
];

?>

<div class="main-wrapper" @notifications-changed.window="load()" x-data="Sobuj_notificationPage('<?= url('api/notifications.php') ?>', '<?= url() ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('includes/notifications/list.php')); ?>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
