<?php
require_once(__DIR__ . '/../app/bootstrap.php'); require_once(base_path('admin/includes/header.php')); require_once(base_path('teacher/includes/sidebar.php'));
$pageHeader = "My Profile"; $showBreadcrumb = true; $breadcrumbs = ["Dashboard" => url('teacher/index.php'), "My Profile" => ""];
$teacher = ["name" => "Dr. Jannatul Naeem", "employee_id" => "TCH-001", "email" => "jannatul.naeem@wub.edu.bd", "phone" => "+880 1XXX-XXXXXX", "designation" => "Assistant Professor", "department" => "Computer Science & Engineering", "specialization" => "Software Engineering and Project Management"];
?>
<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>
    <form action="" method="post">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Teacher Information</h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Full Name</label>
<input
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['name']) ?>">
</div>
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Employee ID</label>
<input
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['employee_id']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Email</label>
<input type="email"
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['email']) ?>">
</div>
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Phone</label>
<input
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['phone']) ?>">
</div>
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Designation</label>
<input
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['designation']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
<label class="form-label-custom">Department</label>
<input
                                class="form-control-custom" value="<?= htmlspecialchars($teacher['department']) ?>">
                        </div>
                        <div class="col-12">
<label class="form-label-custom">Research Specialization</label>
<textarea
                                class="form-control-custom"
                                rows="4">
<?= htmlspecialchars($teacher['specialization']) ?>
</textarea>
</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Account</h5>
                    <p class="text-muted small">Profile changes are static during the current UI phase.</p>
<button
                        type="submit" class="btn btn-primary w-100">
<i class="bi bi-check-lg me-1">
</i>Save
                        Profile</button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php require_once(base_path('admin/includes/footer.php')); ?>