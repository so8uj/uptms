<?php
require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "System Settings";
$showBreadcrumb = true;
$breadcrumbs = ["Dashboard" => url('admin/index.php'), "System" => "", "Settings" => ""];
?>

<div class="main-wrapper" x-data="Sobuj_settingsPage('<?= url('api/settings.php') ?>')">
    <?php require_once(base_path('admin/includes/dashboard_header.php')); require_once(base_path('includes/admin/page_header_breadcrumb.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

    <form x-cloak x-show="!loading" @submit.prevent="save()" novalidate>
        <div class="row g-3">

            <!-- Institution -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-3">Institution</h5>
                    <p class="text-muted small">Shown on the printed reports.</p>

                    <div class="mb-3">
                        <label for="institution_name" class="form-label-custom">Institution name <span class="text-danger">*</span></label>
                        <input type="text" id="institution_name" class="form-control-custom" maxlength="150" x-model="form.institution_name">
                        <template x-for="message in err('institution_name')"><div class="text-danger small mt-1" x-text="message"></div></template>
                    </div>
                    <div class="mb-3">
                        <label for="department_name" class="form-label-custom">Department name <span class="text-danger">*</span></label>
                        <input type="text" id="department_name" class="form-control-custom" maxlength="150" x-model="form.department_name">
                        <template x-for="message in err('department_name')"><div class="text-danger small mt-1" x-text="message"></div></template>
                    </div>
                    <div>
                        <label for="support_email" class="form-label-custom">Support email</label>
                        <input type="email" id="support_email" class="form-control-custom" maxlength="150" x-model="form.support_email">
                        <template x-for="message in err('support_email')"><div class="text-danger small mt-1" x-text="message"></div></template>
                    </div>
                </div>
            </div>

            <!-- Groups/Teams -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-3">Groups/Teams</h5>
                    <p class="text-muted small">How many students a group/team must have. Groups that already exist are not changed.</p>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="team_min_size" class="form-label-custom">Smallest group <span class="text-danger">*</span></label>
                            <input type="number" id="team_min_size" min="1" max="10" class="form-control-custom" x-model="form.team_min_size">
                            <template x-for="message in err('team_min_size')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-6">
                            <label for="team_max_size" class="form-label-custom">Largest group <span class="text-danger">*</span></label>
                            <input type="number" id="team_max_size" min="1" max="10" class="form-control-custom" x-model="form.team_max_size">
                            <template x-for="message in err('team_max_size')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                    </div>

                    <div class="small">
                        Departments:
                        <span class="badge-table" :class="multiDepartment ? 'success' : 'pending'" x-text="multiDepartment ? 'Many departments' : 'One department (CSE)'"></span>
                        <div class="text-muted mt-1">This is the <code>ENABLE_MULTI_DEPARTMENT</code> line in the <code>.env</code> file.</div>
                    </div>
                </div>
            </div>

            <!-- Login -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-3">Login security</h5>
                    <p class="text-muted small">After too many wrong passwords the account is locked for a while.</p>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="login_max_attempts" class="form-label-custom">Wrong passwords allowed <span class="text-danger">*</span></label>
                            <input type="number" id="login_max_attempts" min="3" max="10" class="form-control-custom" x-model="form.login_max_attempts">
                            <template x-for="message in err('login_max_attempts')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-6">
                            <label for="login_lock_minutes" class="form-label-custom">Locked for (minutes) <span class="text-danger">*</span></label>
                            <input type="number" id="login_lock_minutes" min="1" max="60" class="form-control-custom" x-model="form.login_lock_minutes">
                            <template x-for="message in err('login_lock_minutes')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance -->
            <div class="col-12 col-lg-6">
                <div class="card border-light shadow-sm p-4 h-100">
                    <h5 class="card-title mb-3">Maintenance mode</h5>
                    <p class="text-muted small">When it is on, only administrators can log in. Teachers and students are logged out and see the message below.</p>

                    <div class="form-switch-custom mb-3">
                        <input type="checkbox" class="form-switch-input-custom" id="maintenance_mode" x-model="form.maintenance_mode">
                        <label for="maintenance_mode" class="form-label-custom mb-0">Maintenance mode is <strong x-text="form.maintenance_mode ? 'ON' : 'off'"></strong></label>
                    </div>

                    <label for="maintenance_message" class="form-label-custom">Message</label>
                    <textarea id="maintenance_message" rows="2" maxlength="300" class="form-control-custom" x-model="form.maintenance_message"></textarea>
                    <template x-for="message in err('maintenance_message')"><div class="text-danger small mt-1" x-text="message"></div></template>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    <i class="bi bi-check-lg me-1"></i><span x-text="saving ? 'Saving...' : 'Save Settings'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/settings.js?v=<?= filemtime(base_path('assets/admin_assets/js/settings.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
