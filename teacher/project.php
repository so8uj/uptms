<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Project Overview";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Assigned Projects" => url('teacher/projects.php'), "Project Overview" => ""];
$members = [["id" => "3917", "name" => "Sobuj", "role" => "Team Leader", "cgpa" => "3.70"], ["id" => "3919", "name" => "Md Adil Miah", "role" => "Member", "cgpa" => "3.60"], ["id" => "3921", "name" => "Prodip Rajbongshi", "role" => "Member", "cgpa" => "3.55"]];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-muted small mb-2">PRJ-2026-001 · Team Alpha</div>
                        <h4 class="mb-2">University Thesis Management System</h4>
                        <p class="text-muted mb-0">A centralized platform for managing thesis projects, documents,
                            milestones, feedback, and evaluation.</p>
                    </div>
<span class="badge-table success">On Track</span>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
<small class="text-muted d-block">Department</small>
<strong>CSE</strong>
                    </div>
                    <div class="col-6 col-md-3">
<small
                            class="text-muted d-block">Session</small>
<strong>2025-2026</strong>
</div>
                    <div class="col-6 col-md-3">
<small class="text-muted d-block">Progress</small>
<strong>72%</strong>
                    </div>
                    <div class="col-6 col-md-3">
<small
                            class="text-muted d-block">Milestone</small>
<strong>Analysis</strong>
</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card border-light shadow-sm p-4 h-100">
                <h5 class="card-title mb-4">Supervisor Actions</h5>
                <div class="d-grid gap-2">
<a href="<?= url('teacher/milestones.php') ?>"
                        class="btn btn-light text-start">
<i class="bi bi-flag me-2">
</i>Review Milestones</a>
<a
                        href="<?= url('teacher/documents.php') ?>" class="btn btn-light text-start">
<i
                            class="bi bi-file-earmark-text me-2">
</i>Review Documents</a>
<a
                        href="<?= url('teacher/feedback.php') ?>" class="btn btn-light text-start">
<i
                            class="bi bi-chat-left-text me-2">
</i>Give Feedback</a>
<a
                        href="<?= url('teacher/evaluations.php') ?>" class="btn btn-primary text-start">
<i
                            class="bi bi-clipboard-check me-2">
</i>Evaluate Project</a>
</div>
            </div>
        </div>
        <div class="col-12">
            <div class="table-card-custom">
                <div class="table-header-control">
                    <h5 class="card-title">Team Members</h5>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Role</th>
                                <th>CGPA</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($members as $member): ?>
<tr>
                                <td class="table-user-name">
<?= htmlspecialchars($member['name']) ?>
</td>
                                <td>
<?= htmlspecialchars($member['id']) ?>
</td>
                                <td>
<?= htmlspecialchars($member['role']) ?>
</td>
                                <td>
<?= htmlspecialchars($member['cgpa']) ?>
</td>
                            </tr>
<?php endforeach; ?>
</tbody>
</table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>