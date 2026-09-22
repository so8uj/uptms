<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Management";
$showBreadcrumb = true;

$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Project Management" => "",
    "All Projects" => ""
];

$projects = [
    [
        "id" => "PRJ-2026-001",
        "title" => "University Thesis Management System",
        "team" => "Team Alpha",
        "department" => "Computer Science & Engineering",
        "supervisor" => "Dr. Mahmud Hasan",
        "progress" => 72,
        "status" => "ongoing",
    ],
    [
        "id" => "PRJ-2026-002",
        "title" => "Smart Campus Resource Platform",
        "team" => "Team Nova",
        "department" => "Information Technology",
        "supervisor" => "Dr. Farhana Rahman",
        "progress" => 48,
        "status" => "ongoing",
    ],
    [
        "id" => "PRJ-2025-018",
        "title" => "Secure IoT Monitoring Network",
        "team" => "Team Vertex",
        "department" => "Electrical & Electronic Engineering",
        "supervisor" => "Prof. Rezaul Karim",
        "progress" => 100,
        "status" => "completed",
    ],
    [
        "id" => "PRJ-2026-009",
        "title" => "Machine Learning Based Crop Forecasting",
        "team" => "Team Insight",
        "department" => "Computer Science & Engineering",
        "supervisor" => "Not assigned",
        "progress" => 12,
        "status" => "pending",
    ],
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

                <div class="col-12 col-md-5">
                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input
                            type="text"
                            class="table-search-input"
                            placeholder="Search project title, ID, or team..."
                        >
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Departments</option>
                        <option value="cse">Computer Science & Engineering</option>
                        <option value="eee">Electrical & Electronic Engineering</option>
                        <option value="it">Information Technology</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Status</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select class="form-select-custom">
                        <option value="">All Supervisors</option>
                        <option value="mahmud">Dr. Mahmud Hasan</option>
                        <option value="farhana">Dr. Farhana Rahman</option>
                        <option value="rezaul">Prof. Rezaul Karim</option>
                    </select>
                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Team</th>
                        <th>Department</th>
                        <th>Supervisor</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <div class="table-user-name">
                                    <?= htmlspecialchars($project['title']) ?>
                                </div>
                                <div class="table-user-sub">
                                    <?= htmlspecialchars($project['id']) ?>
                                </div>
                            </td>

                            <td><?= htmlspecialchars($project['team']) ?></td>
                            <td><?= htmlspecialchars($project['department']) ?></td>
                            <td><?= htmlspecialchars($project['supervisor']) ?></td>

                            <td style="min-width: 150px;">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><?= $project['progress'] ?>%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width: <?= $project['progress'] ?>%;"
                                        aria-valuenow="<?= $project['progress'] ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </td>

                            <td>
                                <?php if ($project['status'] === 'completed'): ?>
                                    <span class="badge bg-success-subtle text-success">Completed</span>
                                <?php elseif ($project['status'] === 'pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary">Ongoing</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end">
                                <a href="#" class="table-btn-action" title="View project" aria-label="View project">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="#" class="table-btn-action" title="Edit project" aria-label="Edit project">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="table-btn-action delete" title="Delete project" aria-label="Delete project">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <div class="table-footer-control">
            <div class="table-pagination-info">
                Showing 1 to <?= count($projects) ?> of <?= count($projects) ?> projects
            </div>

            <nav aria-label="Project pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Next</span>
                    </li>
                </ul>
            </nav>
        </div>

    </div>

</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
