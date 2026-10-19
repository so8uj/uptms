<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

$listUrl = url('teacher/research/my_research.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
    exit;
}

require_once(base_path('admin/includes/header.php'));
require_once(base_path('teacher/includes/sidebar.php'));

$pageHeader = $isEdit ? "Edit My Research" : "Add Research";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('teacher/index.php'),
    "My Research" => $listUrl,
    $pageHeader => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchOwnerForm('<?= url('api/my_research.php') ?>', '<?= url('api/research_options.php') ?>', 'teacher', <?= $isEdit ? $id : 0 ?>, '<?= $listUrl ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="card border-light shadow-sm">
        <div class="card-body p-4">

            <div class="alert alert-info py-2">
                What you add is reviewed by the administrator before it appears in the library.
            </div>

            <div class="alert alert-danger py-2" x-cloak x-show="savedStatus === 'rejected' && reviewNote">
                <strong>Rejected:</strong> <span x-text="reviewNote"></span><br>
                <span class="small">Improve it and save, and it goes back to the administrator.</span>
            </div>

            <form @submit.prevent="save()" novalidate x-cloak x-show="!loading">

                <div class="row g-3">

                <div class="col-12">
                    <label for="title" class="form-label-custom">
                        Title <span class="text-danger">*</span>
                    </label>

                    <input type="text" class="form-control-custom" id="title" placeholder="Enter the research title" x-model="form.title">
                    
                    <template x-for="message in (errors.title || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12">
                    <label for="abstract" class="form-label-custom">
                        Abstract <span class="text-danger">*</span>
                    </label>

                    <textarea class="form-control-custom" id="abstract" rows="6" maxlength="5000" placeholder="Write a short abstract (at least 20 characters)" x-model="form.abstract"></textarea>
                    
                    <template x-for="message in (errors.abstract || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="type" class="form-label-custom">
                        Type <span class="text-danger">*</span>
                    </label>

                    <select class="form-select-custom" id="type" x-model="form.type">
                        <template x-for="type in options.types" :key="type">
                            <option :value="type" x-text="type"></option>
                        </template>
                    </select>
                    
                    <template x-for="message in (errors.type || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="category_id" class="form-label-custom">
                        Category <span class="text-danger">*</span>
                    </label>

                    <select class="form-select-custom" id="category_id" x-model="form.category_id">
                        <option value="">Select category</option>
                        <template x-for="category in options.categories" :key="category.id">
                            <option :value="String(category.id)" x-text="category.name"></option>
                        </template>
                    </select>
                    
                    <template x-for="message in (errors.category_id || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="year" class="form-label-custom">
                        Year <span class="text-danger">*</span>
                    </label>

                    <input type="number" class="form-control-custom" id="year" placeholder="For example 2026" x-model="form.year" min="1990" max="2100">
                    
                    <template x-for="message in (errors.year || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="target_status" class="form-label-custom">
                        Progress <span class="text-danger">*</span>
                    </label>

                    <select class="form-select-custom" id="target_status" x-model="form.target_status">
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                    <small class="text-muted">What it should become after the administrator approves it.</small>
                    <template x-for="message in (errors.target_status || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Tags</label>

                    <div class="d-flex flex-wrap gap-3">
                        <template x-for="tag in options.tags" :key="tag.id">
                            <label class="form-check form-check-inline m-0">
                                <input type="checkbox" class="form-check-input" :value="String(tag.id)" x-model="form.tags">
                                <span class="form-check-label" x-text="tag.name"></span>
                            </label>
                        </template>
                        <small class="text-muted" x-show="options.tags.length === 0">No tags yet.</small>
                    </div>
                    <small class="text-muted d-block mt-1">Choose up to 10 tags.</small>
                    <template x-for="message in (errors.tags || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-send'"></i>
                        <span x-text="isEdit ? 'Save changes' : 'Submit research'"></span>
                    </button>
                    <a href="<?= $listUrl ?>" class="btn btn-light">Cancel</a>
                </div>

            </form>

        </div>
    </div>

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
