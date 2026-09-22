<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "System Settings";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "System" => "", "Settings" => ""];
?>

<div class="main-wrapper">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <form action="" method="post">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-4">Application Settings</h5>
                    <div class="row g-3">
                        <div class="col-12"><label for="app_name" class="form-label-custom">Application Name</label><input type="text" id="app_name" name="app_name" class="form-control-custom" value="University Project & Thesis Management System"></div>
                        <div class="col-12 col-md-6"><label for="institution" class="form-label-custom">Institution Name</label><input type="text" id="institution" name="institution" class="form-control-custom" value="World University of Bangladesh"></div>
                        <div class="col-12 col-md-6"><label for="academic_year" class="form-label-custom">Current Academic Year</label><select id="academic_year" name="academic_year" class="form-select-custom"><option>2025 - 2026</option><option>2026 - 2027</option><option>2024 - 2025</option></select></div>
                        <div class="col-12"><label for="support_email" class="form-label-custom">Support Email</label><input type="email" id="support_email" name="support_email" class="form-control-custom" value="support@uptms.edu"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-4">System Preferences</h5>
                    <div class="form-switch-custom"><input type="checkbox" class="form-switch-input-custom" id="maintenance_mode" name="maintenance_mode"><label for="maintenance_mode" class="form-label-custom mb-0">Maintenance Mode</label></div>
                    <div class="form-switch-custom"><input type="checkbox" class="form-switch-input-custom" id="email_notifications" name="email_notifications" checked><label for="email_notifications" class="form-label-custom mb-0">Email Notifications</label></div>
                    <p class="text-muted small mt-3 mb-0">These settings are static during the current UI development phase.</p>
                </div>
            </div>
            <div class="col-12"><div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button></div></div>
        </div>
    </form>
</div>

<?php require_once(base_path('admin/includes/footer.php')); ?>
