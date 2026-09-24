<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "My Projects";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "My Projects" => "",
];

$projects = [
    ["id" => "PRJ-2026-001", "title" => "University Thesis Management System", "supervisor" => "Dr. Mahmud Hasan", "department" => "Computer Science & Engineering", "progress" => 72, "milestone" => "Requirement Analysis", "status" => "on_track"],
    ["id" => "PRJ-2026-002", "title" => "Smart Campus Resource Platform", "supervisor" => "Dr. Rezaul Karim", "department" => "Information Technology", "progress" => 48, "milestone" => "System Design", "status" => "on_track"],
    ["id" => "PRJ-2026-009", "title" => "Machine Learning Based Crop Forecasting", "supervisor" => "Dr. Tahmina Akter", "department" => "Computer Science & Engineering", "progress" => 18, "milestone" => "Proposal Revision", "status" => "attention"],
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
                        <input class="table-search-input" placeholder="Search project or supervisor...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option>All Departments</option>
                        <option>Computer Science & Engineering</option>
                        <option>Information Technology</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option>All Status</option>
                        <option>On Track</option>
                        <option>Needs Attention</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Supervisor</th>
                        <th>Department</th>
                        <th>Progress</th>
                        <th>Milestone</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <div class="table-user-name"><?= htmlspecialchars($project['title']) ?></div>
                                <div class="table-user-sub"><?= htmlspecialchars($project['id']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($project['supervisor']) ?></td>
                            <td><?= htmlspecialchars($project['department']) ?></td>
                            <td style="min-width:140px">
                                <div class="small mb-1"><?= $project['progress'] ?>%</div>
                                <div class="progress" style="height:6px">
                                    <div class="progress-bar bg-primary" style="width:<?= $project['progress'] ?>%"></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($project['milestone']) ?></td>
                            <td>
                                <?php if ($project['status'] === 'attention'): ?>
                                    <span class="badge-table pending">Needs Attention</span>
                                <?php else: ?>
                                    <span class="badge-table success">On Track</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('student/project.php') ?>" class="table-btn-action" title="View project"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($projects) ?> of <?= count($projects) ?> assigned projects</div>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>
