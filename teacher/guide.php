<?php

require_once(__DIR__ . '/../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));

$pageHeader = "User Guide";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('teacher/index.php'),
    "User Guide" => ""
];

$guide = require(base_path('includes/guide/content/teacher.php'));

// A committee member can switch between the two guides. Another teacher gets no link to the committee guide.
if (App\Controllers\Auth::isCommitteeMember()) {
    $guide['switch'] = [
        ['Teacher guide', 'teacher/guide.php', true],
        ['Committee guide', 'teacher/committee/guide.php', false],
    ];
} else {
    foreach ($guide['sections'] as $i => $section) {
        if ($section['id'] === 'committee') {
            unset($guide['sections'][$i]['link']);
        }
    }
}

?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));

    require(base_path('includes/guide/page.php'));
    ?>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
