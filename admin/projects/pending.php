<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Pending Proposals";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Project Management" => "", "Pending Proposals" => ""];

$pendingProposals = [
    ["id" => "PROP-2026-014", "title" => "AI Based Student Advising Platform", "team" => "Team Horizon", "department" => "Computer Science & Engineering", "submitted" => "Sep 18, 2026", "priority" => "high"],
    ["id" => "PROP-2026-012", "title" => "Accessible Digital Learning Portal", "team" => "Team Bridge", "department" => "Information Technology", "submitted" => "Sep 14, 2026", "priority" => "normal"],
    ["id" => "PROP-2026-010", "title" => "Smart Laboratory Inventory", "team" => "Team Ledger", "department" => "Electrical & Electronic Engineering", "submitted" => "Sep 11, 2026", "priority" => "normal"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text"
                            class="table-search-input" placeholder="Search pending proposal or team..."></div>
                </div>
                <div class="col-12 col-md-5"><select class="form-select-custom">
                        <option value="">All Departments</option>
                        <option>Computer Science & Engineering</option>
                        <option>Information Technology</option>
                        <option>Electrical & Electronic Engineering</option>
                    </select></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Proposal</th>
                        <th>Team</th>
                        <th>Department</th>
                        <th>Submitted</th>
                        <th>Priority</th>
                        <th class="text-end">Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingProposals as $proposal): ?><tr>
                        <td>
                            <div class="table-user-name"><?= htmlspecialchars($proposal['title']) ?></div>
                            <div class="table-user-sub"><?= htmlspecialchars($proposal['id']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($proposal['team']) ?></td>
                        <td><?= htmlspecialchars($proposal['department']) ?></td>
                        <td><?= htmlspecialchars($proposal['submitted']) ?></td>
                        <td><?php if ($proposal['priority'] === 'high'): ?><span
                                class="badge bg-danger-subtle text-danger">High Priority</span><?php else: ?><span
                                class="badge bg-secondary-subtle text-secondary">Normal</span><?php endif; ?></td>
                        <td class="text-end"><a href="#" class="btn btn-sm btn-primary"><i
                                    class="bi bi-clipboard-check me-1"></i>Review</a></td>
                    </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($pendingProposals) ?> of
                <?= count($pendingProposals) ?> pending proposals</div>
            <nav aria-label="Pending proposal pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    <li class="page-item active"><span class="page-link">1</span></li>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>