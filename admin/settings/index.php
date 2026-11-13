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

                    <h6 class="mt-4 mb-1">Thesis committee signatures</h6>
                    <p class="text-muted small">How many of the committee members who are ticked "Can approve" must sign to approve a topic or the finish of a research.
                        The members are set in University &gt; Thesis Committees.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="panel_signatures_needed" class="form-label-custom">Signatures needed <span class="text-danger">*</span></label>
                            <input type="number" id="panel_signatures_needed" min="1" max="3" class="form-control-custom" x-model="form.panel_signatures_needed">
                            <template x-for="message in err('panel_signatures_needed')"><div class="text-danger small mt-1" x-text="message"></div></template>
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

            <!-- Mail (SMTP) -->
            <div class="col-12">
                <div class="card border-light shadow-sm p-4">
                    <h5 class="card-title mb-3">Email (SMTP)</h5>
                    <p class="text-muted small">
                        With this the system can email the login details when you add a new teacher, student or admin.
                        Ask your mail provider for the server (for example smtp.gmail.com, port 587, TLS) and the account.
                        With Gmail use an <strong>app password</strong>, not the normal password.
                        <strong>Nothing is sent while the switch below is off.</strong>
                    </p>

                    <div class="form-switch-custom mb-3">
                        <input type="checkbox" class="form-switch-input-custom" id="mail_enabled" x-model="form.mail_enabled">
                        <label for="mail_enabled" class="form-label-custom mb-0">Send emails is <strong x-text="form.mail_enabled ? 'ON' : 'off'"></strong></label>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="mail_host" class="form-label-custom">Mail server (host)</label>
                            <input type="text" id="mail_host" class="form-control-custom" maxlength="150" placeholder="smtp.gmail.com" x-model="form.mail_host">
                            <template x-for="message in err('mail_host')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="mail_port" class="form-label-custom">Port</label>
                            <input type="number" id="mail_port" class="form-control-custom" min="1" max="65535" x-model="form.mail_port">
                            <template x-for="message in err('mail_port')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-6 col-md-3">
                            <label for="mail_encryption" class="form-label-custom">Security</label>
                            <select id="mail_encryption" class="form-select-custom" x-model="form.mail_encryption">
                                <option value="tls">TLS (port 587)</option>
                                <option value="ssl">SSL (port 465)</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mail_username" class="form-label-custom">Username</label>
                            <input type="text" id="mail_username" class="form-control-custom" maxlength="150" autocomplete="off" x-model="form.mail_username">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mail_password" class="form-label-custom">Password</label>
                            <input type="password" id="mail_password" class="form-control-custom" maxlength="200" autocomplete="new-password"
                                :placeholder="mailPasswordSet ? 'Saved (leave empty to keep it)' : ''" x-model="form.mail_password">
                            <small class="text-muted">It is saved encrypted and never shown again.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mail_from_email" class="form-label-custom">Sender email</label>
                            <input type="email" id="mail_from_email" class="form-control-custom" maxlength="150" placeholder="uptms@your-university.edu" x-model="form.mail_from_email">
                            <template x-for="message in err('mail_from_email')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mail_from_name" class="form-label-custom">Sender name</label>
                            <input type="text" id="mail_from_name" class="form-control-custom" maxlength="100" placeholder="UPTMS" x-model="form.mail_from_name">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-6">
                            <label for="mail_test_to" class="form-label-custom">Send a test email to</label>
                            <input type="email" id="mail_test_to" class="form-control-custom" placeholder="Your email (empty = your account email)" x-model="testTo">
                            <template x-for="message in err('mail_test_to')"><div class="text-danger small mt-1" x-text="message"></div></template>
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="button" class="btn btn-light border" :disabled="testing" @click="sendTest()">
                                <i class="bi bi-envelope me-1"></i><span x-text="testing ? 'Sending...' : 'Send test email'"></span>
                            </button>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">The test uses the settings that are <strong>saved</strong>. Press Save Settings first when you changed something. It works even when the switch is off.</div>
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
