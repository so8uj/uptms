<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "Research Library";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Research Library" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchLibrary('<?= url('api/library.php') ?>', '<?= url('api/research_options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Completed research</div><div class="fs-4 fw-bold" x-text="counts.completed"></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Ongoing research</div><div class="fs-4 fw-bold" x-text="counts.ongoing"></div></div>
        </div>
    </div>

    <div class="table-card-custom">

        <?php require(base_path('includes/research/type_tabs.php')); ?>

        <div class="table-header-control">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search research..." x-model="search"
                    @input.debounce.400ms="filtersChanged()">
            </div>

            <div class="table-filter-group">

                <?php require(base_path('includes/research/search_filters.php')); ?>

                <select class="form-select-custom" style="width: auto;" x-model="year" @change="filtersChanged()">
                    <option value="">All years</option>
                    <template x-for="y in years" :key="y">
                        <option :value="String(y)" x-text="y"></option>
                    </template>
                </select>

                <label class="form-check m-0 d-flex align-items-center gap-2">
                    <input type="checkbox" class="form-check-input mt-0" x-model="includeOngoing" @change="filtersChanged()">
                    <span class="form-check-label">Include ongoing</span>
                </label>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Research</th>
                        <th>Group/Team</th>
                        <th>Batch</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="7" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="7" class="text-center text-muted py-4">No research found.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr style="cursor: pointer;" @click="toggle(item.id)">
                            <td style="max-width: 420px; white-space: normal;">
                                <div class="table-user-name" x-text="item.title"></div>
                                <div class="table-user-sub" x-text="item.code + ' · ' + item.type"></div>

                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    <template x-for="tag in item.tags" :key="tag.id">
                                        <span class="badge-table pending" x-text="tag.name"></span>
                                    </template>
                                </div>

                                <div class="small text-muted mt-2" x-cloak x-show="opened[item.id]" x-text="item.abstract"></div>
                                <div class="small text-primary mt-1" x-text="opened[item.id] ? 'Hide abstract' : 'Read abstract'"></div>
                            </td>

                            <td style="max-width: 230px; white-space: normal;">
                                <div class="table-user-name" x-text="item.team_name || 'No group/team'"></div>
                                <div class="table-user-sub" x-text="Sobuj_authors(item)"></div>
                                <div class="table-user-sub" x-show="item.supervisor_name" x-text="'Supervisor: ' + item.supervisor_name"></div>
                            </td>
                            <td x-text="item.batch_name || '-'"></td>

                            <td x-text="item.category_name"></td>
                            <td x-text="item.year"></td>
                            <td><span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span></td>

                            <td class="text-center">
                                <a :href="'<?= url('student/research/view.php?id=') ?>' + item.id" class="table-btn-action" title="View" @click.stop>
                                    <i class="bi bi-eye"></i>
                                </a>
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

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
