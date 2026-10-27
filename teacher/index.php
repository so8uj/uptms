<?php
require_once(__DIR__ . '/../app/bootstrap.php');

$me = App\Controllers\Auth::currentUser();

require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));

$pageHeader = "Teacher Dashboard";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => ""
];

$projectUrl = url('teacher/project.php?id=');
$researchUrl = url('teacher/research/my_research.php');
$alertRole = 'teacher';
?>

<div class="main-wrapper" x-data="Sobuj_projectList('<?= url('api/project.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <p class="text-muted">Welcome, <strong><?= htmlspecialchars($me['name']) ?></strong>.</p>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Ongoing projects</div><div class="fs-4 fw-bold" x-text="summary.ongoing"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Open tasks</div><div class="fs-4 fw-bold" x-text="openTasks()"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Not evaluated yet</div><div class="fs-4 fw-bold" x-text="notEvaluated()"></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Completed projects</div><div class="fs-4 fw-bold" x-text="summary.completed"></div></div>
        </div>
    </div>

    <div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

    <?php require(base_path('includes/project/alerts.php')); ?>

    <div class="card border-light shadow-sm">
        <div class="card-header">
            <h5 class="card-title">My Projects</h5>
            <a href="<?= url('teacher/research/my_research.php') ?>" class="btn btn-sm btn-light">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Research</th>
                        <th>Group/Team</th>
                        <th>Batch</th>
                        <th>Progress</th>
                        <th>Evaluations</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr x-cloak x-show="loading">
                        <td colspan="6" class="text-center text-muted py-4">Loading...</td>
                    </tr>
                    <tr x-cloak x-show="!loading && projects.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">No project yet. A research becomes your project when the administrator assigns you as its supervisor.</td>
                    </tr>
                    <template x-for="project in recent()" :key="project.id">
                        <tr>
                            <td style="max-width: 320px; white-space: normal;">
                                <div class="table-user-name" x-text="project.title"></div>
                                <div class="table-user-sub" x-text="project.code + ' · ' + Sobuj_statusText(project.status)"></div>
                            </td>
                            <td style="max-width: 220px; white-space: normal;">
                                <div class="table-user-name" x-text="project.team_name || 'No group/team'"></div>
                                <div class="table-user-sub" x-text="names(project)"></div>
                            </td>
                            <td x-text="project.batch_name || '-'"></td>
                            <td style="min-width: 150px;">
                                <div class="small mb-1" x-text="project.percent + '% · ' + project.done + ' of ' + project.tasks + ' tasks'"></div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" :style="'width: ' + project.percent + '%;'"></div>
                                </div>
                            </td>
                            <td x-text="project.evaluations"></td>
                            <td class="text-center">
                                <a :href="'<?= $projectUrl ?>' + project.id" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/project.js?v=<?= filemtime(base_path('assets/admin_assets/js/project.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
