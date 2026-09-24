<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Proposals";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Proposals" => "",
];

$proposals = [
    ["id" => "PROP-2026-014", "title" => "AI Based Student Advising Platform", "team" => "Team Horizon", "submitted" => "Sep 18, 2026", "status" => "pending"],
    ["id" => "PROP-2026-013", "title" => "Digital Archive for University Research", "team" => "Team Archive", "submitted" => "Sep 15, 2026", "status" => "revision"],
    ["id" => "PROP-2026-011", "title" => "Campus Transport Tracking System", "team" => "Team Route", "submitted" => "Sep 10, 2026", "status" => "approved"],
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input class="table-search-input" placeholder="Search proposal or team...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Revision</option>
                        <option>Approved</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <a href="#" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>New Proposal</a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Team</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proposals as $proposal): ?>
                        <tr>
                            <td><?= htmlspecialchars($proposal['id']) ?></td>
                            <td class="table-user-name"><?= htmlspecialchars($proposal['title']) ?></td>
                            <td><?= htmlspecialchars($proposal['team']) ?></td>
                            <td><?= htmlspecialchars($proposal['submitted']) ?></td>
                            <td>
                                <?php if ($proposal['status'] === 'pending'): ?>
                                    <span class="badge-table pending">Pending</span>
                                <?php elseif ($proposal['status'] === 'revision'): ?>
                                    <span class="badge-table warning">Revision</span>
                                <?php else: ?>
                                    <span class="badge-table success">Approved</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('student/proposal.php') ?>" class="table-btn-action" title="View proposal"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
