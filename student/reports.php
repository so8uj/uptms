<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Reports";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Reports" => "",
];

$reportStats = [
    ["label" => "Completed Tasks", "value" => "18", "detail" => "This semester", "icon" => "bi-check-circle", "color" => "success"],
    ["label" => "Pending Tasks", "value" => "05", "detail" => "Actions needed", "icon" => "bi-clock-history", "color" => "warning"],
    ["label" => "Research Papers", "value" => "03", "detail" => "Submitted", "icon" => "bi-journal-bookmark", "color" => "primary"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="row g-4 mb-4">
        <?php foreach ($reportStats as $stat): ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card border-light shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-2"><?= htmlspecialchars($stat['label']) ?></p>
                                <h3 class="mb-1"><?= htmlspecialchars($stat['value']) ?></h3>
                                <small class="text-<?= htmlspecialchars($stat['color']) ?>"><?= htmlspecialchars($stat['detail']) ?></small>
                            </div>
                            <div class="bg-<?= htmlspecialchars($stat['color']) ?> bg-opacity-10 text-<?= htmlspecialchars($stat['color']) ?> rounded-3 p-3">
                                <i class="bi <?= htmlspecialchars($stat['icon']) ?> fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card border-light shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Academic Summary</h5>
            <p class="text-muted mb-0">Your current performance is strong. Continue improving documentation quality, milestone compliance, and project demo preparation for the next review cycle.</p>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
