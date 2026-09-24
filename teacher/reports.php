<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Teacher Reports"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Reports" => ""];
$reports = [["name" => "Assigned Project Report", "description" => "Overview of all projects currently assigned to you.", "updated" => "Sep 20, 2026"], ["name" => "Progress Monitoring Report", "description" => "Progress, milestones, and attention areas across assigned projects.", "updated" => "Sep 19, 2026"], ["name" => "Document Review Report", "description" => "Submitted documents and outstanding review tasks.", "updated" => "Sep 18, 2026"]];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="row g-4">
<?php foreach ($reports as $report): ?>
<div class="col-12 col-md-6 col-xl-4">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 d-inline-flex mb-4">
<i
                            class="bi bi-bar-chart-line fs-4">
</i>
</div>
                    <h5 class="card-title mb-2">
<?= htmlspecialchars($report['name']) ?>
</h5>
                    <p class="text-muted mb-4">
<?= htmlspecialchars($report['description']) ?>
</p>
                    <div class="d-flex justify-content-between align-items-center">
<small class="text-muted">Updated
                            <?= htmlspecialchars($report['updated']) ?>
</small>
<a href="#"
                            class="btn btn-sm btn-primary">
<i class="bi bi-eye me-1">
</i>View</a>
</div>
                </div>
            </div>
        </div>
<?php endforeach; ?>
</div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>