<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "Review Proposal";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('teacher/index.php'), "Project Proposals" => url('teacher/proposals.php'), "Review Proposal" => ""];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <div class="text-muted small mb-2">PROP-2026-014 · Team Horizon</div>
                        <h4 class="mb-3">AI Based Student Advising Platform</h4>
                    </div><span class="badge-table pending">Pending Review</span>
                </div>
                <h5 class="card-title mt-3">Abstract</h5>
                <p class="text-muted">A proposed platform that uses student academic information to provide personalized
                    advising support and improve academic planning.</p>
                <h5 class="card-title mt-4">Objectives</h5>
                <ul class="text-muted">
                    <li>Centralize student advising information.</li>
                    <li>Provide progress and recommendation reports.</li>
                    <li>Support supervisors and students during academic planning.</li>
                </ul>
                <h5 class="card-title mt-4">Methodology</h5>
                <p class="text-muted mb-0">The team proposes an iterative web application development process using PHP,
                    MySQL, Bootstrap, and JavaScript.</p>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card border-light shadow-sm p-4">
                <h5 class="card-title mb-4">Review Decision</h5><label for="decision"
                    class="form-label-custom">Decision</label><select id="decision" class="form-select-custom mb-3">
                    <option>Pending Review</option>
                    <option>Approve Proposal</option>
                    <option>Request Revision</option>
                    <option>Reject Proposal</option>
                </select><label for="feedback" class="form-label-custom">Review Feedback</label><textarea id="feedback"
                    class="form-control-custom mb-3" rows="6"
                    placeholder="Write feedback for the project team..."></textarea><button type="button"
                    class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Review</button>
            </div>
        </div>
    </div>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>