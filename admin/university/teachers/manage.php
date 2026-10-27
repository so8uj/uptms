<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');

$listUrl = url('admin/university/teachers/index.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
    exit;
}

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = $isEdit ? "Edit Teacher" : "Add Teacher";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Teacher Management" => url('admin/university/teachers/index.php'),
    $pageHeader => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_userForm('<?= url('api/users.php') ?>', '<?= url('api/options.php') ?>', 'teacher', <?= $isEdit ? $id : 0 ?>, '<?= $listUrl ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="card border-light shadow-sm">
        <div class="card-body p-4">

            <form @submit.prevent="save()" novalidate x-cloak x-show="!loading">

                <?php require(base_path('includes/account/photo_field.php')); ?>

                <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label for="name" class="form-label-custom">
                        Full Name <span class="text-danger">*</span>
                    </label>

                    <input type="text" class="form-control-custom" id="name" placeholder="Enter full name"
                            x-model="form.name">
                    <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label-custom">
                        Email <span class="text-danger">*</span>
                    </label>

                    <input type="email" class="form-control-custom" id="email" placeholder="name@example.com"
                            x-model="form.email" autocomplete="off">
                    <template x-for="message in (errors.email || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="phone" class="form-label-custom">
                        Phone
                    </label>

                    <input type="text" class="form-control-custom" id="phone" placeholder="Enter phone number"
                            x-model="form.phone">
                    <template x-for="message in (errors.phone || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="password" class="form-label-custom">
                        Password <span class="text-danger" x-show="!isEdit">*</span>
                    </label>

                    <input type="password" class="form-control-custom" id="password" autocomplete="new-password"
                        placeholder="At least 8 characters" x-model="form.password">
                    <small class="text-muted" x-show="isEdit">Leave empty to keep the current password.</small>
                    <template x-for="message in (errors.password || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="employee_id" class="form-label-custom">
                        Employee ID <span class="text-danger">*</span>
                    </label>

                    <input type="text" class="form-control-custom" id="employee_id" placeholder="For example T-1001"
                            x-model="form.employee_id">
                    <template x-for="message in (errors.employee_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="department_id" class="form-label-custom">
                        Department <span class="text-danger">*</span>
                    </label>

                    <select class="form-select-custom" id="department_id" x-model="form.department_id">
                            <option value="">Select department</option>
                            <template x-for="department in departments" :key="department.id">
                                <option :value="String(department.id)" x-text="department.name"></option>
                            </template>
                        </select>
                    <template x-for="message in (errors.department_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="designation" class="form-label-custom">
                        Designation <span class="text-danger">*</span>
                    </label>

                    <select class="form-select-custom" id="designation" x-model="form.designation">
                            <option value="">Select designation</option>
                            <option>Professor</option>
                            <option>Associate Professor</option>
                            <option>Assistant Professor</option>
                            <option>Senior Lecturer</option>
                            <option>Lecturer</option>
                        </select>
                    <template x-for="message in (errors.designation || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="specialization" class="form-label-custom">
                        Specialization
                    </label>

                    <input type="text" class="form-control-custom" id="specialization" placeholder="Enter specialization"
                            x-model="form.specialization">
                    <template x-for="message in (errors.specialization || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="research_area" class="form-label-custom">
                        Research Area
                    </label>

                    <input type="text" class="form-control-custom" id="research_area" placeholder="Enter research area"
                            x-model="form.research_area">
                    <template x-for="message in (errors.research_area || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="research_management" class="form-label-custom">
                        Research management
                    </label>

                    <select class="form-select-custom" id="research_management" x-model="form.research_management">
                            <option value="0">No</option>
                            <option value="1">Yes (adds research, approves research, assigns supervisors, manages batches and groups/teams)</option>
                        </select>
                    <template x-for="message in (errors.research_management || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="status" class="form-label-custom">
                        Status
                    </label>

                    <select class="form-select-custom" id="status" x-model="form.status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    <template x-for="message in (errors.status || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                </div>

                </div>

                <div class="d-flex gap-2 mt-4">

                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-plus-circle'"></i>
                        <span x-text="(isEdit ? 'Update' : 'Add') + ' Teacher'"></span>
                    </button>

                    <a href="<?= $listUrl ?>" class="btn btn-light">Cancel</a>

                </div>

            </form>

        </div>
    </div>

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js?v=<?= filemtime(base_path('assets/admin_assets/js/users.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
