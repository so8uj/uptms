<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Research Details";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Ongoing Research" => url('admin/research/ongoing.php'),
    "Details" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchAdminView('<?= url('api/research.php') ?>', <?= (int) ($_GET['id'] ?? 0) ?>)">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <div x-cloak x-show="loading" class="text-center text-muted py-5">Loading...</div>

    <div x-cloak x-show="error" class="alert alert-danger" x-text="error"></div>

    <template x-if="item">
        <div class="row g-4">

            <div class="col-12 col-xl-7">
                <div class="card border-light shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="small text-muted" x-text="item.code + ' · ' + item.type + ' · ' + item.category_name + ' · ' + item.year"></div>
                                <h4 class="mt-1 mb-3" x-text="item.title"></h4>
                            </div>
                            <span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mb-4">
                            <template x-for="tag in item.tags" :key="tag.id">
                                <span class="badge-table pending" x-text="tag.name"></span>
                            </template>
                        </div>

                        <h6>Abstract</h6>
                        <p class="text-muted mb-4" style="white-space: pre-line;" x-text="item.abstract"></p>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="small text-muted">Requested by</div>
                                <div class="fw-semibold" x-text="item.creator_name"></div>
                                <div class="small text-muted" x-text="item.created_at.substring(0, 16)"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="small text-muted">Supervisor</div>
                                <div class="fw-semibold" x-text="item.supervisor_name || 'Not assigned yet'"></div>
                            </div>
                            <div class="col-12">
                                <div class="small text-muted mb-1">Team</div>
                                <template x-for="student in item.students" :key="student.id">
                                    <div class="mb-1">
                                        <span class="fw-semibold" x-text="student.name"></span>
                                        <span class="small text-muted" x-text="student.student_id ? ' (' + student.student_id + ')' : ''"></span>
                                    </div>
                                </template>
                                <div x-show="item.students.length === 0">-</div>
                            </div>
                            <div class="col-12" x-show="item.review_note">
                                <div class="small text-muted">Admin note</div>
                                <div x-text="item.review_note"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="<?= url('admin/research/ongoing.php') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
                <a :href="'<?= url('admin/research/manage.php?action=update&id=') ?>' + item.id" class="btn btn-light"><i class="bi bi-pencil"></i> Edit</a>
                <a :href="'<?= url('admin/research/project.php?id=') ?>' + item.id" class="btn btn-light" x-show="['ongoing', 'completed'].includes(item.status)"><i class="bi bi-kanban"></i> Project work</a>
            </div>

            <div class="col-12 col-xl-5" id="activity">
                <div class="card border-light shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title">Activity</h5>
                        <span class="small text-muted" x-text="activity.length + (activity.length === 1 ? ' action' : ' actions')"></span>
                    </div>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item text-muted small" x-show="activity.length === 0">No activity yet.</div>

                        <template x-for="entry in activity" :key="entry.id">
                            <div class="list-group-item">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                                        <i class="bi" :class="Sobuj_activityIcon(entry.action)"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div>
                                            <span class="fw-semibold" x-text="actor(entry)"></span>
                                            <span x-text="Sobuj_activityText(entry)"></span>
                                        </div>
                                        <div class="small mt-1 p-2 rounded bg-light" x-show="Sobuj_activityNote(entry)" x-text="Sobuj_activityNote(entry)"></div>
                                        <div class="small text-muted mt-1" x-text="entry.created_at.substring(0, 16)"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </template>
    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
