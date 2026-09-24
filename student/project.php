<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Project Details";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "My Projects" => url('student/projects.php'),
    "Project Details" => "",
];

$project = [
    "title" => "University Thesis Management System",
    "team" => "Team Alpha",
    "supervisor" => "Dr. Mahmud Hasan",
    "category" => "Thesis Project",
    "status" => "On Track",
    "progress" => 72,
    "nextMilestone" => "Requirement Analysis Presentation",
    "teamMembers" => [
        ["name" => "Nusrat Jahan", "role" => "Team Leader"],
        ["name" => "Mohammad Sobuj", "role" => "Developer"],
        ["name" => "Tanvir Hasan", "role" => "Researcher"],
    ],
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card border-light shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="small text-primary fw-semibold mb-2">Project</div>
                            <h3 class="mb-1"><?= htmlspecialchars($project['title']) ?></h3>
                            <div class="text-muted"><?= htmlspecialchars($project['team']) ?></div>
                        </div>
                        <span class="badge-table success"><?= htmlspecialchars($project['status']) ?></span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Supervisor</div>
                                <div class="fw-semibold"><?= htmlspecialchars($project['supervisor']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Category</div>
                                <div class="fw-semibold"><?= htmlspecialchars($project['category']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Project Completion</span>
                            <span class="text-muted"><?= $project['progress'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: <?= $project['progress'] ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-light rounded-4 p-3">
                        <div class="small text-muted mb-2">Next milestone</div>
                        <div class="fw-semibold"><?= htmlspecialchars($project['nextMilestone']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-light shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">Team Members</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($project['teamMembers'] as $member): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($member['name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($member['role']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
