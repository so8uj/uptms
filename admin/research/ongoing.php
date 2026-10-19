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
                        <th>Category</th>
                        <th>People</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">No ongoing research.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                        <td style="max-width: 340px; white-space: normal;">
                            <div class="table-user-name" x-text="item.title"></div>
                            <div class="table-user-sub" x-text="item.code + ' · ' + item.type + ' · ' + item.year"></div>
                        </td>

                        <td x-text="item.category_name"></td>

                        <td>
                            <div class="table-user-name" x-text="Sobuj_authors(item)"></div>
                            <div class="table-user-sub" x-text="item.supervisor_name ? 'Supervisor: ' + item.supervisor_name : 'No supervisor yet'"></div>
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="table-btn-action" title="Mark as completed" @click="complete(item)">
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

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
