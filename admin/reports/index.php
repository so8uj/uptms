<?php

require_once(__DIR__ . '/../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Reports";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Reports" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_reportPage('<?= url('api/reports.php') ?>', '<?= url('api/options.php') ?>', '<?= url('api/research_options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <!-- the numbers -->
    <div class="row row-cols-2 row-cols-md-4 row-cols-xl-8 g-3 mb-3 no-print" x-cloak x-show="summary">
        <template x-for="card in [
            ['Students', summary ? summary.students : 0], ['Teachers', summary ? summary.teachers : 0],
            ['Groups/Teams', summary ? summary.teams : 0], ['Research', summary ? summary.research : 0],
            ['Waiting', summary ? summary.waiting : 0], ['Ongoing', summary ? summary.ongoing : 0],
            ['Completed', summary ? summary.completed : 0], ['Evaluations', summary ? summary.evaluations : 0]]" :key="card[0]">
            <div class="col">
                <div class="card border-light shadow-sm p-0 mb-0 rounded-3 h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted" x-text="card[0]"></div>
                        <div class="fs-4 fw-bold lh-sm" x-text="card[1]"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="small text-muted mb-3 no-print" x-cloak x-show="summary">
        By kind:
        <strong x-text="summary ? summary.by_type['Project'] : 0"></strong> Project ·
        <strong x-text="summary ? summary.by_type['Thesis'] : 0"></strong> Thesis ·
        <strong x-text="summary ? summary.by_type['Research Paper'] : 0"></strong> Research Paper
    </div>

    <!-- which report -->
    <div class="d-flex flex-wrap gap-2 mb-3 no-print">
        <template x-for="(name, key) in types" :key="key">
            <button type="button" class="filter-pill" :class="type === key ? 'active' : ''" @click="setType(key)" x-text="name"></button>
        </template>
    </div>

    <div class="table-card-custom">

        <!-- the title of a printed page -->
        <div class="print-only px-3 pt-3">
            <h4 class="mb-0">UPTMS report: <span x-text="reportName"></span></h4>
            <div class="small"><?= htmlspecialchars(\App\Models\Setting::get('institution_name', 'World University of Bangladesh')) ?> · <?= htmlspecialchars(\App\Models\Setting::get('department_name', 'Department of CSE')) ?> ·<span x-text="generated"></span> · <span x-text="total"></span> rows</div>
        </div>

        <!-- the kind -->
        <div class="no-print" x-show="shows('kind')">
            <div class="d-flex flex-wrap gap-2 px-3 pt-3">
                <button type="button" class="filter-pill" :class="filters.kind === '' ? 'active' : ''" @click="filters.kind = ''; filtersChanged()">All</button>
                <button type="button" class="filter-pill" :class="filters.kind === 'Project' ? 'active' : ''" @click="filters.kind = 'Project'; filtersChanged()">Project</button>
                <button type="button" class="filter-pill" :class="filters.kind === 'Thesis' ? 'active' : ''" @click="filters.kind = 'Thesis'; filtersChanged()">Thesis</button>
                <button type="button" class="filter-pill" :class="filters.kind === 'Research Paper' ? 'active' : ''" @click="filters.kind = 'Research Paper'; filtersChanged()">Research Paper</button>
            </div>
        </div>

        <!-- the filters and the export buttons -->
        <div class="table-header-control no-print">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search..." x-model="filters.q" @input.debounce.400ms="filtersChanged()">
            </div>

            <div class="table-filter-group">

                <select class="form-select-custom" style="width: auto;" x-show="shows('status')" x-model="filters.status" @change="filtersChanged()">
                    <option value="">All status</option>
                    <template x-for="item in (statuses[type] || [])" :key="item[0]">
                        <option :value="item[0]" x-text="item[1]"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-show="shows('category_id')" x-model="filters.category_id" @change="filtersChanged()">
                    <option value="">All categories</option>
                    <template x-for="category in categories" :key="category.id">
                        <option :value="String(category.id)" x-text="category.name"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-show="shows('batch_id')" x-model="filters.batch_id" @change="filtersChanged()">
                    <option value="">All batches</option>
                    <template x-for="batch in batches" :key="batch.id">
                        <option :value="String(batch.id)" x-text="batch.name"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-show="shows('year')" x-model="filters.year" @change="filtersChanged()">
                    <option value="">All years</option>
                    <template x-for="year in years" :key="year">
                        <option :value="String(year)" x-text="year"></option>
                    </template>
                </select>

                <button type="button" class="btn btn-success" @click="exportFile('xlsx')" title="Download all the rows as an Excel file">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
                <button type="button" class="btn btn-outline-primary" @click="exportFile('csv')" title="Download all the rows as a CSV file">
                    <i class="bi bi-filetype-csv me-1"></i> CSV
                </button>
                <button type="button" class="btn btn-light" @click="printReport()" title="Print, or save as PDF">
                    <i class="bi bi-printer me-1"></i> Print / PDF
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom table-compact">
                <thead>
                    <tr>
                        <template x-for="column in columns" :key="column.key">
                            <th x-text="column.label"></th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <tr x-cloak x-show="loading && rows.length === 0">
                        <td :colspan="columns.length || 1" class="text-center text-muted py-4">Loading...</td>
                    </tr>
                    <tr x-cloak x-show="!loading && rows.length === 0">
                        <td :colspan="columns.length || 1" class="text-center text-muted py-4">No data for these filters.</td>
                    </tr>

                    <template x-for="(row, index) in rows" :key="index">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <td :style="wide(column) ? 'min-width: 200px; max-width: 320px; white-space: normal;' : ''" x-text="cell(row, column)"></td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="table-footer-control no-print">

            <span class="table-pagination-info">
                <template x-if="total > 0">
                    <span>Showing <span x-text="rangeFrom()"></span> to <span x-text="rangeTo()"></span> of <span x-text="total"></span> rows</span>
                </template>
                <template x-if="total === 0">
                    <span>Showing 0 rows</span>
                </template>
            </span>

            <nav aria-label="Report pagination" x-cloak x-show="pages > 1">
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
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/reports.js?v=<?= filemtime(base_path('assets/admin_assets/js/reports.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
