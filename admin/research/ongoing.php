<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Ongoing Research";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Ongoing Research" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchAdminList('<?= url('api/research.php') ?>', '<?= url('api/research_options.php') ?>', 'ongoing')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/research_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>


    <div class="table-card-custom">

        <div class="table-header-control">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search title, code, tag or student..." x-model="search"
                    @input.debounce.400ms="filtersChanged()">
            </div>

            <div class="table-filter-group">
                <select class="form-select-custom" style="width: auto;" x-model="categoryId" @change="filtersChanged()">
                    <option value="">All categories</option>
                    <template x-for="category in options.categories" :key="category.id">
                        <option :value="String(category.id)" x-text="category.name"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-model="type" @change="filtersChanged()">
                    <option value="">All types</option>
                    <template x-for="typeName in options.types" :key="typeName">
                        <option :value="typeName" x-text="typeName"></option>
                    </template>
                </select>

                <a href="<?= url('admin/research/manage.php?action=create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Research
                </a>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Research</th>
                        <th>People</th>
                        <th>Progress</th>
                        <th>Last activity</th>
                        <th>Status</th>
                        <th class="text-center col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">No research in progress.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                        <td style="max-width: 340px; white-space: normal;">
                            <div class="table-user-name" x-text="item.title"></div>
                            <div class="table-user-sub" x-text="item.code + ' · ' + item.type + ' · ' + item.category_name + ' · ' + item.year"></div>
                        </td>

                        <td>
                            <div class="table-user-name" x-text="Sobuj_authors(item)"></div>
                            <div class="table-user-sub" x-text="item.supervisor_name ? 'Supervisor: ' + item.supervisor_name : 'No supervisor yet'"></div>
                        </td>

                        <td style="min-width: 150px;">
                            <div x-show="item.status === 'ongoing'">
                                <div class="small mb-1" x-text="item.percent + '% · ' + item.done + ' of ' + item.tasks + ' tasks · ' + item.milestones + ' milestones'"></div>
                                <div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" :style="'width: ' + item.percent + '%;'"></div></div>
                                <div class="small text-muted mt-1" x-text="item.last_rating ? 'Last rating: ' + item.last_rating + ' of 5 (' + item.evaluations + ' evaluations)' : 'Not evaluated yet'"></div>
                            </div>
                            <span class="text-muted small" x-show="item.status !== 'ongoing'">-</span>
                        </td>

                        <td>
                            <div x-text="item.last_activity.substring(0, 10)"></div>
                            <span class="badge-table failed" x-show="item.status === 'ongoing' && item.quiet_days >= 14" x-text="'Quiet for ' + item.quiet_days + ' days'"></span>
                        </td>

                        <td>
                            <span class="badge-table" :class="item.status === 'ongoing' ? 'success' : 'pending'"
                                x-text="item.status === 'ongoing' ? 'Ongoing' : 'Needs supervisor'"></span>
                            <div class="mt-1" x-show="item.finish_status">
                                <span class="badge-table pending"
                                    x-text="{ teacher: 'Finish: supervisor to decide', team: 'Finish: team to confirm', admin: 'Finish: needs your decision' }[item.finish_status] || ''"></span>
                            </div>
                        </td>

                        <td class="col-actions">
                            <div class="d-flex justify-content-center gap-1">
                                <a :href="'<?= url('admin/research/view.php?id=') ?>' + item.id" class="table-btn-action" title="Full details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a :href="'<?= url('admin/research/project.php?id=') ?>' + item.id" class="table-btn-action" title="Project work"
                                    x-cloak x-show="item.status === 'ongoing'">
                                    <i class="bi bi-kanban"></i>
                                </a>
                                <a :href="'<?= url('admin/research/view.php?id=') ?>' + item.id + '#activity'" class="table-btn-action" title="Activity">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <button type="button" class="table-btn-action" title="Assign supervisor" @click="openReview(item, 'assign')"
                                    x-cloak x-show="item.status === 'approved'">
                                    <i class="bi bi-person-check"></i>
                                </button>
                                <button type="button" class="table-btn-action" title="Mark as completed" @click="complete(item)"
                                    x-cloak x-show="item.status === 'ongoing'">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                                <a :href="'<?= url('admin/research/manage.php?action=update&id=') ?>' + item.id" class="table-btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="table-btn-action delete" title="Delete" @click="remove(item)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                        </tr>
                    </template>

                </tbody>

            </table>
        </div>

        <div class="table-footer-control">

            <span class="table-pagination-info">
                <template x-if="total > 0">
                    <span>Showing <span x-text="rangeFrom()"></span> to <span x-text="rangeTo()"></span> of <span x-text="total"></span> records</span>
                </template>
                <template x-if="total === 0">
                    <span>Showing 0 records</span>
                </template>
            </span>

            <nav aria-label="Research pagination" x-cloak x-show="pages > 1">
                <ul class="pagination mb-0">

                    <li class="page-item" :class="page <= 1 ? 'disabled' : ''">
                        <a class="page-link" href="#" @click.prevent="goTo(page - 1)">Previous</a>
                    </li>

                    <template x-for="number in pages" :key="number">
                        <li class="page-item" :class="number === page ? 'active' : ''">
                            <a class="page-link" href="#" @click.prevent="goTo(number)" x-text="number"></a>
                        </li>
                    </template>

                    <li class="page-item" :class="page >= pages ? 'disabled' : ''">
                        <a class="page-link" href="#" @click.prevent="goTo(page + 1)">Next</a>
                    </li>

                </ul>
            </nav>

        </div>

    </div>

    <?php require(base_path('includes/research/review_modal.php')); ?>

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
