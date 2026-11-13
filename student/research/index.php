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

    <div class="table-card-custom">

        <div class="p-3 pb-0">
            <div class="table-search-box" style="max-width: 100%;">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search a topic, title or keyword to see what was already done..." x-model="search"
                    @input.debounce.400ms="filtersChanged()">
            </div>
        </div>

        <?php require(base_path('includes/research/type_tabs.php')); ?>

        <div class="table-header-control">
            <div class="table-filter-group">

                <?php require(base_path('includes/research/search_filters.php')); ?>

                <select class="form-select-custom" style="width: auto;" x-model="year" @change="filtersChanged()">
                    <option value="">Any year</option>
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

        <div class="px-3 pb-2 small text-muted">
            <span x-show="total > 0">About <strong x-text="total"></strong> <span x-text="total === 1 ? 'result' : 'results'"></span></span>
            <span x-show="total === 0 && !loading">0 results</span>
        </div>

        <div class="px-3 pb-3">

            <div class="text-center text-muted py-4" x-cloak x-show="loading && items.length === 0">Loading...</div>
            <div class="text-center text-muted py-4" x-cloak x-show="!loading && items.length === 0">
                Nothing found for this topic. Try fewer words or clear the filters.
            </div>

            <template x-for="item in items" :key="item.id">
                <div class="py-3 border-bottom">
                    <a :href="'<?= url('student/research/view.php?id=') ?>' + item.id" class="fw-semibold fs-5 text-primary text-decoration-none" x-text="item.title"></a>

                    <div class="small text-success mt-1">
                        <span x-text="item.type"></span>
                        <span x-show="item.year"> · <span x-text="item.year"></span></span>
                        <span x-show="item.category_name"> · <span x-text="item.category_name"></span></span>
                        <span class="badge-table pending ms-1" x-show="item.status !== 'completed'" x-text="Sobuj_statusText(item.status)"></span>
                    </div>

                    <div class="text-muted mt-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" x-text="item.abstract"></div>

                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <template x-for="tag in item.tags" :key="tag.id">
                            <span class="badge-table pending" x-text="tag.name"></span>
                        </template>
                    </div>
                </div>
            </template>

        </div>

        <div class="table-footer-control">

            <span class="table-pagination-info">
                <template x-if="total > 0">
                    <span>Showing <span x-text="rangeFrom()"></span> to <span x-text="rangeTo()"></span> of <span x-text="total"></span> results</span>
                </template>
                <template x-if="total === 0">
                    <span>Showing 0 results</span>
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
