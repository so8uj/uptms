<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Research Details";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Research Library" => url('student/research/index.php'),
    "Details" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchView('<?= url('api/library.php') ?>', <?= (int) ($_GET['id'] ?? 0) ?>, '<?= url('api/my_research.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

    <div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

    <template x-if="item">
        <div class="row g-4">

            <div class="col-12 col-xl-8">
                <div class="card border-light shadow-sm">
                    <div class="card-body">
                        <div class="small text-muted" x-text="item.code + ' · ' + item.type"></div>
                        <h4 class="mt-1 mb-3" x-text="item.title"></h4>

                        <div class="alert alert-warning py-2" x-show="own && item.status === 'correction' && item.review_note">
                            <strong>Correction needed:</strong> <span x-text="item.review_note"></span><br>
                            <span class="small">Correct it and save, and it goes back to the administrator.</span>
                        </div>
                        <div class="alert alert-danger py-2" x-show="own && item.status === 'rejected'">
                            <strong>Rejected for good:</strong> <span x-text="item.review_note"></span><br>
                            <span class="small">Nothing more can be done with this research.</span>
                        </div>
                        <div class="alert alert-info py-2" x-show="own && ['requested', 'pending'].includes(item.status)">
                            This research is waiting for the administrator.
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-4">
                            <template x-for="tag in item.tags" :key="tag.id">
                                <span class="badge-table pending" x-text="tag.name"></span>
                            </template>
                        </div>

                        <h6>Abstract</h6>
                        <p class="text-muted mb-0" style="white-space: pre-line;" x-text="item.abstract"></p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-light shadow-sm">
                    <div class="card-body">

                        <div class="mb-3">
                            <div class="small text-muted">Status</div>
                            <span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Category</div>
                            <div class="fw-semibold" x-text="item.category_name"></div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Year</div>
                            <div class="fw-semibold" x-text="item.year"></div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted">Supervisor</div>
                            <div class="fw-semibold" x-text="item.supervisor_name || '-'"></div>
                        </div>

                        <div>
                            <div class="small text-muted mb-1">Students</div>
                            <template x-for="student in item.students" :key="student.id">
                                <div class="mb-1">
                                    <span class="fw-semibold" x-text="student.name"></span>
                                    <span class="small text-muted" x-text="student.student_id ? ' (' + student.student_id + ')' : ''"></span>
                                </div>
                            </template>
                            <div x-show="item.students.length === 0">-</div>
                        </div>

                    </div>
                </div>

                <a :href="'<?= url('student/research/manage.php?action=update&id=') ?>' + item.id" class="btn btn-primary mt-3" x-show="canEdit">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="<?= url('student/research/my_research.php') ?>" class="btn btn-light mt-3" x-show="own"><i class="bi bi-arrow-left"></i> Back to My Research</a>
                <a href="<?= url('student/research/index.php') ?>" class="btn btn-light mt-3" x-show="!own"><i class="bi bi-arrow-left"></i> Back to library</a>
            </div>

        </div>
    </template>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
