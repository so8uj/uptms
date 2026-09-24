<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Evaluations"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Evaluations" => ""];
$evaluations = [["project" => "University Thesis Management System", "team" => "Team Alpha", "due" => "Oct 12, 2026", "score" => "-", "status" => "pending"], ["project" => "Secure IoT Monitoring Network", "team" => "Team Vertex", "due" => "Completed", "score" => "91/100", "status" => "completed"], ["project" => "Smart Campus Resource Platform", "team" => "Team Nova", "due" => "Oct 25, 2026", "score" => "-", "status" => "pending"]];
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
                            class="table-search-input" placeholder="Search project or team...">
</div>
                </div>
                <div class="col-12 col-md-4">
<select class="form-select-custom">
                        <option>All Evaluation Status</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Team</th>
                        <th>Due / Completed</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($evaluations as $evaluation): ?>
<tr>
                        <td class="table-user-name">
<?= htmlspecialchars($evaluation['project']) ?>
</td>
                        <td>
<?= htmlspecialchars($evaluation['team']) ?>
</td>
                        <td>
<?= htmlspecialchars($evaluation['due']) ?>
</td>
                        <td>
<strong>
<?= htmlspecialchars($evaluation['score']) ?>
</strong>
</td>
                        <td>
<?php if ($evaluation['status'] === 'completed'): ?>
<span
                                class="badge-table success">Completed</span>
<?php else: ?>
<span
                                class="badge-table pending">Pending</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="#"
                                class="btn btn-sm <?= $evaluation['status'] === 'completed' ? 'btn-light' : 'btn-primary' ?>">
<?= $evaluation['status'] === 'completed' ? 'View' : 'Evaluate' ?>
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