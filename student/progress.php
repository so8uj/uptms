<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Progress Updates";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Progress Updates" => "",
];

$updates = [
    ["project" => "University Thesis Management System", "team" => "Team Alpha", "update" => "Completed dashboard and student-management interface.", "progress" => 72, "date" => "Sep 20, 2026", "status" => "on_track"],
    ["project" => "Smart Campus Resource Platform", "team" => "Team Nova", "update" => "Prepared system architecture and UI wireframes.", "progress" => 48, "date" => "Sep 15, 2026", "status" => "on_track"],
    ["project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "update" => "Need more dataset validation before prototype submission.", "progress" => 18, "date" => "Sep 08, 2026", "status" => "attention"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="row g-4">
        <?php foreach ($updates as $update): ?>
            <div class="col-12">
                <div class="card border-light shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-column flex-md-row gap-3 mb-3">
                            <div>
                                <div class="small text-primary fw-semibold mb-1"><?= htmlspecialchars($update['project']) ?></div>
                                <h5 class="mb-1"><?= htmlspecialchars($update['team']) ?></h5>
                            </div>
                            <div>
                                <?php if ($update['status'] === 'attention'): ?>
                                    <span class="badge-table pending">Needs Attention</span>
                                <?php else: ?>
                                    <span class="badge-table success">On Track</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="text-muted mb-3"><?= htmlspecialchars($update['update']) ?></p>

                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Progress</span>
                            <span><?= $update['progress'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: <?= $update['progress'] ?>%"></div>
                        </div>

                        <div class="small text-muted mt-3"><?= htmlspecialchars($update['date']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
