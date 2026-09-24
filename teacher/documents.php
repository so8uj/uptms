<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Submitted Documents"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Documents" => ""];
$documents = [["name" => "Campus Platform - Requirement Analysis.docx", "project" => "Smart Campus Resource Platform", "team" => "Team Nova", "type" => "Requirement Document", "date" => "Sep 16, 2026", "status" => "pending"], ["name" => "Thesis Management - Progress Report.pdf", "project" => "University Thesis Management System", "team" => "Team Alpha", "type" => "Progress Report", "date" => "Sep 15, 2026", "status" => "reviewed"], ["name" => "Crop Forecasting - Dataset Notes.pdf", "project" => "Machine Learning Based Crop Forecasting", "team" => "Team Insight", "type" => "Supporting File", "date" => "Sep 12, 2026", "status" => "pending"]];
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
                            class="table-search-input" placeholder="Search document or project...">
</div>
                </div>
                <div class="col-12 col-md-5">
<select class="form-select-custom">
                        <option>All Status</option>
                        <option>Pending Review</option>
                        <option>Reviewed</option>
                    </select>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Project</th>
                        <th>Type</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($documents as $document): ?>
<tr>
                        <td>
                            <div class="table-user-name">
<i
                                    class="bi bi-file-earmark-text text-primary me-2">
</i>
<?= htmlspecialchars($document['name']) ?>
                            </div>
                            <div class="table-user-sub">
<?= htmlspecialchars($document['team']) ?>
</div>
                        </td>
                        <td>
<?= htmlspecialchars($document['project']) ?>
</td>
                        <td>
<?= htmlspecialchars($document['type']) ?>
</td>
                        <td>
<?= htmlspecialchars($document['date']) ?>
</td>
                        <td>
<?php if ($document['status'] === 'reviewed'): ?>
<span
                                class="badge-table success">Reviewed</span>
<?php else: ?>
<span
                                class="badge-table pending">Pending Review</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="#" class="table-btn-action" title="View document">
<i
                                    class="bi bi-eye">
</i>
</a>
<a href="#" class="table-btn-action"
                                title="Download document">
<i class="bi bi-download">
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