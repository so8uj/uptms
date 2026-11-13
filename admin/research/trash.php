<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Trash";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Trash" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchTrash('<?= url('api/research.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <p class="text-muted">Research you deleted are kept here with all their milestones, feedback and evaluations. <strong>Restore</strong> puts one back in the library. <strong>Delete for ever</strong> removes it and everything that belongs to it, and cannot be undone.</p>

    <div class="table-card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Research</th>
                        <th>People</th>
                        <th>Records</th>
                        <th>Deleted on</th>
                        <th class="text-center col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr x-cloak x-show="loading"><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">The trash is empty.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td style="min-width: 200px; max-width: 300px; white-space: normal;">
                                <div class="table-user-name" x-text="item.title"></div>
                                <div class="table-user-sub" x-text="item.code + ' · ' + item.type + ' · ' + Sobuj_statusText(item.status)"></div>
                            </td>

                            <td style="min-width: 170px; max-width: 220px; white-space: normal;">
                                <div class="table-user-name" x-text="item.team_name || 'No group/team'"></div>
                                <div class="table-user-sub" x-show="item.batch_name" x-text="'Batch ' + item.batch_name"></div>
                                <template x-for="student in (item.students || [])" :key="student.id">
                                    <div class="small" x-text="student.name"></div>
                                </template>
                                <div class="table-user-sub mt-1" x-text="item.supervisor_name ? 'Supervisor: ' + item.supervisor_name : 'No supervisor'"></div>
                            </td>

                            <td class="small" style="white-space: normal;"
                                x-text="item.milestones + ' milestones, ' + item.comments + ' feedback, ' + item.evaluations + ' evaluations'"></td>

                            <td x-text="item.deleted_at.substring(0, 10)"></td>

                            <td class="col-actions">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-primary btn-sm" @click="restore(item)">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="purge(item)">
                                        <i class="bi bi-x-octagon me-1"></i> Delete for ever
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
