<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Student Dashboard";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => "",
];

$metrics = [
    ["label" => "Active Project", "value" => "01", "detail" => "Current semester work", "icon" => "bi-kanban", "color" => "primary"],
    ["label" => "Pending Milestones", "value" => "03", "detail" => "Need submission", "icon" => "bi-flag", "color" => "warning"],
    ["label" => "Documents", "value" => "06", "detail" => "Submitted & reviewed", "icon" => "bi-file-earmark-arrow-up", "color" => "danger"],
    ["label" => "Supervisor Feedback", "value" => "04", "detail" => "Recent updates", "icon" => "bi-chat-left-text", "color" => "success"],
];

$projects = [
    ["title" => "University Thesis Management System", "supervisor" => "Dr. Mahmud Hasan", "progress" => 72, "milestone" => "Requirement Analysis", "status" => "On Track"],
    ["title" => "Smart Campus Resource Platform", "supervisor" => "Dr. Rezaul Karim", "progress" => 48, "milestone" => "System Design", "status" => "On Track"],
    ["title" => "Machine Learning Based Crop Forecasting", "supervisor" => "Dr. Tahmina Akter", "progress" => 18, "milestone" => "Proposal Revision", "status" => "Needs Attention"],
];

$tasks = [
    ["type" => "Milestone", "title" => "Submit requirement analysis report", "date" => "Sep 28, 2026", "icon" => "bi-flag"],
    ["type" => "Document", "title" => "Upload project proposal draft", "date" => "Sep 30, 2026", "icon" => "bi-file-earmark-arrow-up"],
    ["type" => "Feedback", "title" => "Review supervisor comments on prototype", "date" => "Oct 02, 2026", "icon" => "bi-chat-left-text"],
];
?>

<div class="main-wrapper">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div class="row g-4 mb-4">
        <?php foreach ($metrics as $metric): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-light shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-2"><?= htmlspecialchars($metric['label']) ?></p>
                                <h3 class="mb-1"><?= htmlspecialchars($metric['value']) ?></h3>
                                <small class="text-<?= htmlspecialchars($metric['color']) ?>"><?= htmlspecialchars($metric['detail']) ?></small>
                            </div>
                            <div class="bg-<?= htmlspecialchars($metric['color']) ?> bg-opacity-10 text-<?= htmlspecialchars($metric['color']) ?> rounded-3 p-3">
                                <i class="bi <?= htmlspecialchars($metric['icon']) ?> fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-light shadow-sm">
                <div class="card-header">
                    <h5 class="card-title">My Project Progress</h5>
                    <a href="<?= url('student/projects.php') ?>" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Supervisor</th>
                                <th>Progress</th>
                                <th>Milestone</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td>
                                        <div class="table-user-name"><?= htmlspecialchars($project['title']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($project['supervisor']) ?></td>
                                    <td style="min-width: 130px;">
                                        <div class="small mb-1"><?= $project['progress'] ?>%</div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= $project['progress'] ?>%;"></div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($project['milestone']) ?></td>
                                    <td>
                                        <?php if ($project['status'] === 'Needs Attention'): ?>
                                            <span class="badge-table pending">Needs Attention</span>
                                        <?php else: ?>
                                            <span class="badge-table success">On Track</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-light shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">Upcoming Tasks</h5>
                    <i class="bi bi-clock-history text-muted"></i>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($tasks as $task): ?>
                        <a href="#" class="list-group-item list-group-item-action px-0">
                            <div class="d-flex align-items-start gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                    <i class="bi <?= htmlspecialchars($task['icon']) ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted"><?= htmlspecialchars($task['type']) ?> · <?= htmlspecialchars($task['date']) ?></div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($task['title']) ?></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
