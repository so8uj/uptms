<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Project Evaluation";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Evaluation" => "",
    "Project Evaluation" => ""
];

$evaluations = [
    ["id" => "EVAL-2026-014", "project" => "University Thesis Management System", "team" => "Team Alpha", "supervisor" => "Dr. Mahmud Hasan", "evaluator" => "Prof. Rezaul Karim", "score" => "86/100", "status" => "completed"],
    ["id" => "EVAL-2026-011", "project" => "Smart Campus Resource Platform", "team" => "Team Nova", "supervisor" => "Dr. Farhana Rahman", "evaluator" => "Not assigned", "score" => "-", "status" => "pending"],
    ["id" => "EVAL-2025-018", "project" => "Secure IoT Monitoring Network", "team" => "Team Vertex", "supervisor" => "Prof. Rezaul Karim", "evaluator" => "Dr. Nasir Uddin", "score" => "91/100", "status" => "completed"],
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
                    <div class="table-search-box"><i class="bi bi-search table-search-icon"></i><input type="text"
                            class="table-search-input" placeholder="Search project, team, or evaluation ID..."></div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Evaluators</option>
                        <option>Prof. Rezaul Karim</option>
                        <option>Dr. Nasir Uddin</option>
                        <option>Not assigned</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select-custom">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
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
                        <th>Supervisor</th>
                        <th>Evaluator</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $evaluation): ?><tr>
                        <td>
                            <div class="table-user-name"><?= htmlspecialchars($evaluation['project']) ?></div>
                            <div class="table-user-sub"><?= htmlspecialchars($evaluation['id']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($evaluation['team']) ?></td>
                        <td><?= htmlspecialchars($evaluation['supervisor']) ?></td>
                        <td><?= htmlspecialchars($evaluation['evaluator']) ?></td>
                        <td><strong><?= htmlspecialchars($evaluation['score']) ?></strong></td>
                        <td><?php if ($evaluation['status'] === 'completed'): ?><span
                                class="badge-table success">Completed</span><?php else: ?><span
                                class="badge-table pending">Pending</span><?php endif; ?></td>
                        <td>
                            <div class="d-flex justify-content-center gap-1"><a href="#" class="table-btn-action"
                                    title="View evaluation" aria-label="View evaluation"><i class="bi bi-eye"></i></a><a
                                    href="#" class="table-btn-action" title="Edit evaluation"
                                    aria-label="Edit evaluation"><i class="bi bi-pencil"></i></a></div>
                        </td>
                    </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer-control">
            <div class="table-pagination-info">Showing 1 to <?= count($evaluations) ?> of <?= count($evaluations) ?>
                project evaluations</div>
            <nav aria-label="Project evaluation pagination">
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