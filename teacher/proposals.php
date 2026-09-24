<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Project Proposals";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Project Proposals" => ""];
$proposals = [["id" => "PROP-2026-014", "title" => "AI Based Student Advising Platform", "team" => "Team Horizon", "submitted" => "Sep 18, 2026", "status" => "pending"], ["id" => "PROP-2026-013", "title" => "Digital Archive for University Research", "team" => "Team Archive", "submitted" => "Sep 15, 2026", "status" => "revision"], ["id" => "PROP-2026-011", "title" => "Campus Transport Tracking System", "team" => "Team Route", "submitted" => "Sep 10, 2026", "status" => "approved"]];
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
                            class="table-search-input" placeholder="Search proposal or team...">
</div>
                </div>
                <div class="col-12 col-md-4">
<select class="form-select-custom">
                        <option>All Status</option>
                        <option>Pending Review</option>
                        <option>Revision Required</option>
                        <option>Approved</option>
                    </select>
</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Proposal</th>
                        <th>Team</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Review</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($proposals as $proposal): ?>
<tr>
                        <td>
                            <div class="table-user-name">
<?= htmlspecialchars($proposal['title']) ?>
</div>
                            <div class="table-user-sub">
<?= htmlspecialchars($proposal['id']) ?>
</div>
                        </td>
                        <td>
<?= htmlspecialchars($proposal['team']) ?>
</td>
                        <td>
<?= htmlspecialchars($proposal['submitted']) ?>
</td>
                        <td>
<?php if ($proposal['status'] === 'approved'): ?>
<span
                                class="badge-table success">Approved</span>
<?php elseif ($proposal['status'] === 'revision'): ?>
<span
                                class="badge-table pending">Revision Required</span>
<?php else: ?>
<span
                                class="badge-table pending">Pending Review</span>
<?php endif; ?>
</td>
                        <td class="text-center">
<a href="<?= url('teacher/proposal.php') ?>" class="table-btn-action"
                                title="Review proposal">
<i class="bi bi-eye">
</i>
</a>
</td>
                    </tr>
<?php endforeach; ?>
</tbody>
</table>
        </div>
        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($proposals) ?> of <?= count($proposals) ?>
                proposals</div>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>