<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');

$listUrl = url('admin/university/teams/index.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
    exit;
}

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = $isEdit ? "Edit Group/Team" : "Add Group/Team";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Groups/Teams" => $listUrl,
    $pageHeader => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_teamForm('<?= url('api/teams.php') ?>', '<?= url('api/options.php') ?>', <?= $isEdit ? $id : 0 ?>, '<?= $listUrl ?>', <?= multi_department() ? 'true' : 'false' ?>)">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="card border-light shadow-sm">
        <div class="card-body p-4">

            <div class="alert alert-warning py-2" x-cloak x-show="isEdit && ['closed', 'rejected'].includes(status)">
                This group/team is closed or rejected. It cannot be changed.
            </div>

            <form @submit.prevent="save()" novalidate x-cloak x-show="!loading">

                <div class="row g-3">

                    <div class="col-12">
                        <label for="name" class="form-label-custom">Group/Team name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control-custom" id="name" maxlength="100" placeholder="For example Team Nova" x-model="form.name">
                        <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="col-12 col-md-6" <?= multi_department() ? '' : 'hidden' ?>>
                        <label for="department_id" class="form-label-custom">Department <span class="text-danger">*</span></label>
                        <select class="form-select-custom" id="department_id" x-model="form.department_id" @change="departmentChanged()" :disabled="isEdit">
                            <option value="">Select department</option>
                            <template x-for="department in options.departments" :key="department.id">
                                <option :value="String(department.id)" x-text="department.name"></option>
                            </template>
                        </select>
                        <template x-for="message in (errors.department_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="col-12 <?= multi_department() ? 'col-md-6' : '' ?>">
                        <label for="batch_id" class="form-label-custom">Batch <span class="text-danger">*</span></label>
                        <select class="form-select-custom" id="batch_id" x-model="form.batch_id" @change="batchChanged()" :disabled="isEdit || form.department_id === ''">
                            <option value="">Select batch</option>
                            <template x-for="batch in batchOptions" :key="batch.id">
                                <option :value="String(batch.id)" x-text="batch.name + ' · ' + batch.shift"></option>
                            </template>
                        </select>
                        <template x-for="message in (errors.batch_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="col-12" x-cloak x-show="!ready">
                        <div class="text-muted">Choose a batch. Then the students of that batch are loaded.</div>
                    </div>

                    <div class="col-12" x-cloak x-show="ready">
                        <?php require(base_path('includes/team/picker.php')); ?>
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving || sizeProblem() !== '' || (isEdit && ['closed', 'rejected'].includes(status))">
                        <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-plus-circle'"></i>
                        <span x-text="isEdit ? 'Update Group/Team' : 'Add Group/Team'"></span>
                    </button>
                    <a href="<?= $listUrl ?>" class="btn btn-light">Cancel</a>
                </div>

            </form>

        </div>
    </div>

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/team.js?v=<?= filemtime(base_path('assets/admin_assets/js/team.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
