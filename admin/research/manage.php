<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

$listUrl = url('admin/research/index.php');
$id = (int) ($_GET['id'] ?? 0);
$isEdit = page_action() === 'update' && $id > 0;

if (page_action() === 'update' && $id < 1) {
    header('Location: ' . $listUrl);
    exit;
}

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = $isEdit ? "Edit Research" : "Add Research";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => $listUrl,
    $pageHeader => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchAdminForm('<?= url('api/research.php') ?>', '<?= url('api/research_options.php') ?>', '<?= url('api/teams.php') ?>', <?= $isEdit ? $id : 0 ?>, '<?= $listUrl ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="card border-light shadow-sm">
        <div class="card-body p-4">

            <form @submit.prevent="save()" novalidate x-cloak x-show="!loading">

                <div class="mb-3 small text-muted" x-show="isEdit">
                    Code <strong x-text="code"></strong> ·
                    Status <strong x-text="Sobuj_statusText(savedStatus)"></strong>
                </div>

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

                <div class="col-12 col-md-4">
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

                <div class="col-12 col-md-4">
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

                <div class="col-12 col-md-4">
                    <label for="year" class="form-label-custom">
                        Year <span class="text-danger">*</span>
                    </label>

                    <input type="number" class="form-control-custom" id="year" placeholder="For example 2026" x-model="form.year" min="1990" max="2100">
                    
                    <template x-for="message in (errors.year || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="status" class="form-label-custom">
                        Status
                    </label>

                    <select class="form-select-custom" id="status" x-model="form.status" :disabled="statusLocked">
                        <option value="completed">Completed</option>
                        <option value="ongoing">Ongoing</option>
                    </select>
                    <small class="text-muted" x-show="statusLocked">Approve or reject this research on the Pending Submissions page.</small>
                    <template x-for="message in (errors.status || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12 col-md-6">
                    <label for="supervisor_id" class="form-label-custom">
                        Supervisor (optional)
                    </label>

                    <select class="form-select-custom" id="supervisor_id" x-model="form.supervisor_id">
                        <option value="">No supervisor</option>
                        <template x-for="teacher in options.teachers" :key="teacher.id">
                            <option :value="String(teacher.id)" x-text="teacher.name + (teacher.employee_id ? ' (' + teacher.employee_id + ')' : '')"></option>
                        </template>
                    </select>
                    
                    <template x-for="message in (errors.supervisor_id || [])">
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

                <div class="col-12" x-show="!isEdit">
                    <label for="team_id" class="form-label-custom">
                        Group/Team <span class="text-muted fw-normal">(needed for an ongoing research)</span>
                    </label>

                    <select class="form-select-custom" id="team_id" x-model="form.team_id">
                        <option value="">No group/team</option>
                        <template x-for="team in teams" :key="team.id">
                            <option :value="String(team.id)" x-text="team.name + ' · ' + team.department_code + ' ' + team.batch_name"></option>
                        </template>
                    </select>
                    <small class="text-muted" x-show="teams.length === 0">No free group/team yet. Add one in University &gt; Groups/Teams.</small>

                    <div class="d-flex flex-wrap gap-2 mt-2" x-show="teamMembers.length > 0">
                        <template x-for="student in teamMembers" :key="student.id">
                            <span class="badge-table pending" x-text="student.name + (student.student_id ? ' (' + student.student_id + ')' : '')"></span>
                        </template>
                    </div>

                    <template x-for="message in (errors.team_id || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="col-12" x-show="isEdit">
                    <label class="form-label-custom">Group/Team</label>
                    <div class="fw-semibold" x-text="teamName || 'No group/team'"></div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <template x-for="student in students" :key="student.id">
                            <span class="badge-table pending" x-text="student.name + (student.student_id ? ' (' + student.student_id + ')' : '')"></span>
                        </template>
                    </div>
                    <small class="text-muted d-block mt-1">The members of a group/team are changed in University &gt; Groups/Teams.</small>
                </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary" :disabled="saving">
                        <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-plus-circle'"></i>
                        <span x-text="isEdit ? 'Update Research' : 'Add Research'"></span>
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

<?php require_once(base_path('admin/includes/footer.php')); ?>
