<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

$listUrl = url('admin/admins/index.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
    exit;
}

// Your own record is edited on the Profile page.
if ($isEdit && $id === (int) (App\Controllers\Auth::currentUser()['id'] ?? 0)) {
    header('Location: ' . url('admin/profile.php'));
    exit;
}

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = $isEdit ? "Edit Admin" : "Add Admin";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Admins" => url('admin/admins/index.php'),
    $pageHeader => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_userForm('<?= url('api/users.php') ?>', '<?= url('api/options.php') ?>', 'admin', <?= $isEdit ? $id : 0 ?>, '<?= $listUrl ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

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
                    <label class="form-label-custom">Password</label>

                    <div class="small text-muted" x-show="!isEdit">
                        A new account gets the default password <strong>wub@admin123</strong>.
                        The user must choose a new one at the first login.
                    </div>
                    <div x-cloak x-show="isEdit">
                        <button type="button" class="btn btn-light border btn-sm" :disabled="resetting" @click="resetPassword()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to default password
                        </button>
                        <div class="small text-muted mt-1">Gives <strong>wub@admin123</strong> again (for a forgotten password). The user must change it at the next login.</div>
                    </div>
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
                        <span x-text="(isEdit ? 'Update' : 'Add') + ' Admin'"></span>
                    </button>

                    <a href="<?= $listUrl ?>" class="btn btn-light">Cancel</a>

                </div>

            </form>

        </div>
    </div>

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js?v=<?= filemtime(base_path('assets/admin_assets/js/users.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
