<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Feedback"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Feedback" => ""];
$feedback = [["project" => "University Thesis Management System", "team" => "Team Alpha", "subject" => "Progress Review", "message" => "Please include the database relationship diagram in the next submission.", "date" => "Sep 20, 2026", "status" => "Awaiting Response"], ["project" => "Smart Campus Resource Platform", "team" => "Team Nova", "subject" => "Requirement Analysis", "message" => "The requirements are clear. Continue with the system design documentation.", "date" => "Sep 18, 2026", "status" => "Reviewed"]];
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
                            class="table-search-input" placeholder="Search feedback or project...">
</div>
                </div>
                <div class="col-12 col-md-4">
<a href="#" class="btn btn-primary w-100">
<i
                            class="bi bi-plus-lg me-1">
</i>Add Feedback</a>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Subject</th>
                        <th>Feedback</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($feedback as $item): ?>
<tr>
                        <td>
                            <div class="table-user-name">
<?= htmlspecialchars($item['project']) ?>
</div>
                            <div class="table-user-sub">
<?= htmlspecialchars($item['team']) ?>
</div>
                        </td>
                        <td>
<?= htmlspecialchars($item['subject']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['message']) ?>
</td>
                        <td>
<?= htmlspecialchars($item['date']) ?>
</td>
                        <td>
<?php if ($item['status'] === 'Reviewed'): ?>
<span
                                class="badge-table success">Reviewed</span>
<?php else: ?>
<span
                                class="badge-table pending">Awaiting Response</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="#" class="table-btn-action" title="View feedback">
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