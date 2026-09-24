<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Reports";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Reports" => "", "Project Reports" => ""];

$reports = [
    ["project" => "University Thesis Management System", "department" => "Computer Science & Engineering", "team" => "Team Alpha", "progress" => "72%", "status" => "Ongoing", "updated" => "Sep 20, 2026"],
    ["project" => "Smart Campus Resource Platform", "department" => "Information Technology", "team" => "Team Nova", "progress" => "48%", "status" => "Ongoing", "updated" => "Sep 19, 2026"],
    ["project" => "Secure IoT Monitoring Network", "department" => "Electrical & Electronic Engineering", "team" => "Team Vertex", "progress" => "100%", "status" => "Completed", "updated" => "Sep 17, 2026"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text"
                            class="table-search-input" placeholder="Search project or team..."></div>
                </div>
                <div class="col-12 col-md-3"><select class="form-select-custom">
                        <option>All Departments</option>
                        <option>Computer Science & Engineering</option>
                        <option>Information Technology</option>
                    </select></div>
                <div class="col-12 col-md-3"><select class="form-select-custom">
                        <option>All Status</option>
                        <option>Ongoing</option>
                        <option>Completed</option>
                    </select></div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Department</th>
                        <th>Team</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?><tr>
                        <td>
                            <div class="table-user-name"><?= htmlspecialchars($report['project']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($report['department']) ?></td>
                        <td><?= htmlspecialchars($report['team']) ?></td>
                        <td><strong><?= htmlspecialchars($report['progress']) ?></strong></td>
                        <td><?php if ($report['status'] === 'Completed'): ?><span
                                class="badge-table success">Completed</span><?php else: ?><span
                                class="badge-table pending">Ongoing</span><?php endif; ?></td>
                        <td><?= htmlspecialchars($report['updated']) ?></td>
                        <td class="text-center"><a href="#" class="table-btn-action" title="View report"
                                aria-label="View report"><i class="bi bi-eye"></i></a></td>
                    </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($reports) ?> of <?= count($reports) ?> project
                reports</div>
            <nav aria-label="Project report pagination">
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