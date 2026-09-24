<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Project Milestones"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Milestones" => ""];
$milestones = [["name" => "Proposal Approval", "project" => "University Thesis Management System", "due" => "Aug 30, 2026", "progress" => 100, "status" => "completed"], ["name" => "Requirement Analysis", "project" => "University Thesis Management System", "due" => "Sep 25, 2026", "progress" => 80, "status" => "ongoing"], ["name" => "System Design", "project" => "Smart Campus Resource Platform", "due" => "Oct 08, 2026", "progress" => 35, "status" => "ongoing"]];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <div class="table-search-box">
<i class="bi bi-search table-search-icon">
</i>
<input
                            class="table-search-input" placeholder="Search milestone or project...">
</div>
                </div>
                <div class="col-12 col-md-5">
<select class="form-select-custom">
                        <option>All Projects</option>
                        <option>University Thesis Management System</option>
                        <option>Smart Campus Resource Platform</option>
                    </select>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Milestone</th>
                        <th>Project</th>
                        <th>Due Date</th>
                        <th>Completion</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($milestones as $milestone): ?>
<tr>
                        <td class="table-user-name">
<?= htmlspecialchars($milestone['name']) ?>
</td>
                        <td>
<?= htmlspecialchars($milestone['project']) ?>
</td>
                        <td>
<?= htmlspecialchars($milestone['due']) ?>
</td>
                        <td style="min-width:140px">
                            <div class="small mb-1">
<?= $milestone['progress'] ?>%</div>
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-primary" style="width:<?= $milestone['progress'] ?>%">
</div>
                            </div>
                        </td>
                        <td>
<?php if ($milestone['status'] === 'completed'): ?>
<span
                                class="badge-table success">Completed</span>
<?php else: ?>
<span
                                class="badge-table pending">Ongoing</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="#" class="table-btn-action" title="Review milestone">
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