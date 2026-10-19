<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

$listUrl = url('admin/admins/index.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
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

                <div class="d-flex align-items-center gap-4 mb-4">

                    <template x-if="shownPhoto">
                        <img class="table-user-avatar" style="width: 110px; height: 110px;" :src="shownPhoto" alt="Photo">
                    </template>
                    <template x-if="!shownPhoto">
                        <div class="table-user-avatar" style="width: 110px; height: 110px; font-size: 2rem;" x-text="Sobuj_initials(form.name)"></div>
                    </template>

                    <div>
                        <label for="photo" class="form-label-custom">Photo</label>
                        <input type="file" class="form-control-custom" id="photo" x-ref="photoInput"
                            accept="image/jpeg,image/png,image/webp" @change="photoChosen($event)">
                        <small class="text-muted d-block">JPG, PNG or WebP, up to 2 MB. Without a photo the initials are shown.</small>
                        <template x-for="message in (errors.photo || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                        <button type="button" class="btn btn-link btn-sm p-0 mt-1" x-cloak x-show="photoFile"
                            @click="clearPhoto()">Remove the selected photo</button>
                    </div>

                </div>

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

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
