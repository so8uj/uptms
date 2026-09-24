<?php
require_once(__DIR__ . '/../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "My Profile";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "My Profile" => "",
];

$profile = [
    "name" => "Nusrat Jahan",
    "student_id" => "CSE-2026-018",
    "email" => "nusrat.jahan@student.wub.edu.bd",
    "phone" => "+8801700000000",
    "department" => "Computer Science & Engineering",
    "batch" => "2022-2026",
    "cgpa" => "3.82",
    "semester" => "7th Semester",
];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 96px; height: 96px; font-size: 2rem;">
                        <i class="bi bi-person"></i>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($profile['name']) ?></h4>
                    <div class="text-muted"><?= htmlspecialchars($profile['student_id']) ?></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card border-light shadow-sm h-100">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Email</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Phone</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['phone']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Department</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['department']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Batch</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['batch']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">CGPA</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['cgpa']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <div class="small text-muted mb-1">Semester</div>
                                <div class="fw-semibold"><?= htmlspecialchars($profile['semester']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
