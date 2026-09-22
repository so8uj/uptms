<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Proposals";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Project Management" => "",
    "Project Proposals" => ""
];

$proposals = [
    ["id" => "PROP-2026-014", "title" => "AI Based Student Advising Platform", "team" => "Team Horizon", "submitted" => "Sep 18, 2026", "supervisor" => "Dr. Mahmud Hasan", "status" => "under_review"],
    ["id" => "PROP-2026-013", "title" => "Digital Archive for University Research", "team" => "Team Archive", "submitted" => "Sep 15, 2026", "supervisor" => "Not assigned", "status" => "revision"],
    ["id" => "PROP-2026-011", "title" => "Campus Transport Tracking System", "team" => "Team Route", "submitted" => "Sep 10, 2026", "supervisor" => "Dr. Farhana Rahman", "status" => "approved"],
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" placeholder="Search proposal, team, or ID...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Supervisors</option>
                        <option>Dr. Mahmud Hasan</option>
                        <option>Dr. Farhana Rahman</option>
                        <option>Not assigned</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Status</option>
                        <option value="under_review">Under Review</option>
                        <option value="revision">Revision Required</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Proposal</th><th>Team</th><th>Submitted</th><th>Supervisor</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($proposals as $proposal): ?>
                        <tr>
                            <td><div class="table-user-name"><?= htmlspecialchars($proposal['title']) ?></div><div class="table-user-sub"><?= htmlspecialchars($proposal['id']) ?></div></td>
                            <td><?= htmlspecialchars($proposal['team']) ?></td>
                            <td><?= htmlspecialchars($proposal['submitted']) ?></td>
                            <td><?= htmlspecialchars($proposal['supervisor']) ?></td>
                            <td>
                                <?php if ($proposal['status'] === 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success">Approved</span>
                                <?php elseif ($proposal['status'] === 'revision'): ?>
                                    <span class="badge bg-warning-subtle text-warning">Revision Required</span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary">Under Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="#" class="table-btn-action" title="View proposal" aria-label="View proposal"><i class="bi bi-eye"></i></a>
                                <a href="#" class="table-btn-action" title="Edit proposal" aria-label="Edit proposal"><i class="bi bi-pencil"></i></a>
                                <button type="button" class="table-btn-action delete" title="Delete proposal" aria-label="Delete proposal"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer-control"><div class="table-pagination-info">Showing 1 to <?= count($proposals) ?> of <?= count($proposals) ?> proposals</div><nav aria-label="Proposal pagination"><ul class="pagination pagination-sm mb-0"><li class="page-item disabled"><span class="page-link">Previous</span></li><li class="page-item active"><span class="page-link">1</span></li><li class="page-item disabled"><span class="page-link">Next</span></li></ul></nav></div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
