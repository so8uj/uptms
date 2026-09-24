<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Progress Updates"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Progress Updates" => ""];
$updates = [["project" => "University Thesis Management System", "team" => "Team Alpha", "update" => "Completed dashboard and student-management interface.", "progress" => 72, "date" => "Sep 20, 2026", "status" => "on_track"], ["project" => "Smart Campus Resource Platform", "team" => "Team Nova", "update" => "Completed API requirements and initial data model.", "progress" => 48, "date" => "Sep 19, 2026", "status" => "on_track"], ["project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "update" => "Dataset preparation is awaiting supervisor feedback.", "progress" => 12, "date" => "Sep 16, 2026", "status" => "attention"]];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <div class="table-search-box">
<i class="bi bi-search table-search-icon">
</i>
<input
                            class="table-search-input" placeholder="Search project or progress update...">
</div>
                </div>
                <div class="col-12 col-md-4">
<select class="form-select-custom">
                        <option>All Status</option>
                        <option>On Track</option>
                        <option>Needs Attention</option>
                    </select>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Latest Update</th>
                        <th>Progress</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($updates as $update): ?>
<tr>
                        <td>
                            <div class="table-user-name">
<?= htmlspecialchars($update['project']) ?>
</div>
                            <div class="table-user-sub">
<?= htmlspecialchars($update['team']) ?>
</div>
                        </td>
                        <td>
<?= htmlspecialchars($update['update']) ?>
</td>
                        <td style="min-width:140px">
                            <div class="small mb-1">
<?= $update['progress'] ?>%</div>
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-primary" style="width:<?= $update['progress'] ?>%">
</div>
                            </div>
                        </td>
                        <td>
<?= htmlspecialchars($update['date']) ?>
</td>
                        <td>
<?php if ($update['status'] === 'attention'): ?>
<span class="badge-table pending">Needs
                                Attention</span>
<?php else: ?>
<span class="badge-table success">On
                                Track</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="#" class="table-btn-action" title="Review progress">
<i
                                    class="bi bi-eye">
</i>
</a>
</td>
                    </tr>
<?php endforeach; ?>
</tbody>
</table>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>