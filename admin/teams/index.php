<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Teams";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "Project Management" => "", "Project Teams" => ""];

$teams = [
    ["name" => "Team Alpha", "project" => "University Thesis Management System", "leader" => "Mohammad Sobuj", "members" => 4, "department" => "Computer Science & Engineering", "status" => "active"],
    ["name" => "Team Nova", "project" => "Smart Campus Resource Platform", "leader" => "Nusrat Jahan", "members" => 3, "department" => "Information Technology", "status" => "active"],
    ["name" => "Team Vertex", "project" => "Secure IoT Monitoring Network", "leader" => "Tanvir Hasan", "members" => 4, "department" => "Electrical & Electronic Engineering", "status" => "completed"],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="table-card-custom">
        <div class="table-header-control">
            <div class="row g-3">
                <div class="col-12 col-md-7">
                    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text"
                            class="table-search-input" placeholder="Search team, project, or leader..."></div>
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
                        <th>Team</th>
                        <th>Project</th>
                        <th>Team Leader</th>
                        <th>Members</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?><tr>
                        <td>
                            <div class="table-user-name"><?= htmlspecialchars($team['name']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($team['project']) ?></td>
                        <td><?= htmlspecialchars($team['leader']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= $team['members'] ?> members</span></td>
                        <td><?= htmlspecialchars($team['department']) ?></td>
                        <td><?php if ($team['status'] === 'completed'): ?><span
                                class="badge bg-success-subtle text-success">Completed</span><?php else: ?><span
                                class="badge bg-primary-subtle text-primary">Active</span><?php endif; ?></td>
                        <td class="text-end"><a href="#" class="table-btn-action" title="View team"
                                aria-label="View team"><i class="bi bi-eye"></i></a><a href="#" class="table-btn-action"
                                title="Edit team" aria-label="Edit team"><i class="bi bi-pencil"></i></a><button
                                type="button" class="table-btn-action delete" title="Delete team"
                                aria-label="Delete team"><i class="bi bi-trash"></i></button></td>
                    </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($teams) ?> of <?= count($teams) ?> teams</div>
            <nav aria-label="Team pagination">
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