<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Proposal Details";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Proposals" => url('student/proposals.php'),
    "Proposal Details" => "",
];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="card border-light shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div>
                    <div class="small text-primary fw-semibold mb-2">Proposal</div>
                    <h3 class="mb-1">AI Based Student Advising Platform</h3>
                    <div class="text-muted">Submitted by Team Horizon</div>
                </div>
                <span class="badge-table warning">Pending Review</span>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Supervisor</div>
                        <div class="fw-semibold">Dr. Mahmud Hasan</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="bg-light rounded-4 p-3 h-100">
                        <div class="small text-muted mb-1">Submission Date</div>
                        <div class="fw-semibold">Sep 18, 2026</div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="mb-3">Objective</h5>
                <p class="text-muted mb-0">A proposed platform that helps students receive personalized advising support based on academic performance, attendance, and course requirements. The system allows students and supervisors to monitor progress and guidance more effectively.</p>
            </div>

            <div class="mb-4">
                <h5 class="mb-3">Key Features</h5>
                <ul class="text-muted mb-0 ps-3">
                    <li>Centralized student advising information.</li>
                    <li>Personalized academic recommendations.</li>
                    <li>Supervisor dashboards for guidance and review.</li>
                    <li>Progress tracking for projects and academic milestones.</li>
                </ul>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= url('student/proposals.php') ?>" class="btn btn-light">Back</a>
                <button type="button" class="btn btn-primary">Edit Proposal</button>
            </div>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
