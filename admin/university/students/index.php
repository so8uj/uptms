<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Student Management";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Student Management" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_userList('<?= url('api/users.php') ?>', 'student', '<?= url('api/options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <?php $importType = 'students'; require(base_path('includes/import/panel.php')); ?>

    <div class="table-card-custom">

        <div class="table-header-control">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search students..." x-model="search"
                    @input.debounce.400ms="searchChanged()">
            </div>

            <div class="table-filter-group">

                <select class="form-select-custom" style="width: auto;" x-model="batchId" @change="batchChanged()">
                    <option value="">All batches</option>
                    <template x-for="batch in batches" :key="batch.id">
                        <option :value="String(batch.id)" x-text="batch.name + ' · ' + batch.shift"></option>
                    </template>
                </select>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" x-text="statusLabel">
                    </button>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('')">All Status</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('1')">Active</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('0')">Inactive</a></li>
                    </ul>
                </div>

                <button type="button" class="btn btn-outline-primary" @click="$dispatch('toggle-import')">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i>
                    Import CSV
                </button>

                <a href="<?= url('admin/university/students/manage.php?action=create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Student
                </a>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom table-compact">

                <thead>
                    <tr>
                        <th class="sortable" @click="setSort('name')">Student <i class="bi" :class="sortIcon('name')"></i></th>
                        <th class="sortable" @click="setSort('roll')">ID <i class="bi" :class="sortIcon('roll')"></i></th>
                        <th class="sortable" @click="setSort('batch')">Batch <i class="bi" :class="sortIcon('batch')"></i></th>
                        <th class="sortable" @click="setSort('credits')">Credit Hours <i class="bi" :class="sortIcon('credits')"></i></th>
                        <th class="sortable" @click="setSort('cgpa')">CGPA <i class="bi" :class="sortIcon('cgpa')"></i></th>
                        <?php if (multi_department()): ?><th>Department</th><?php endif; ?>
                        <th class="sortable" @click="setSort('status')">Status <i class="bi" :class="sortIcon('status')"></i></th>
                        <th class="text-center col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="<?= multi_department() ? 8 : 7 ?>" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="<?= multi_department() ? 8 : 7 ?>" class="text-center text-muted py-4">No students found.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td>
                                        <div class="table-user-cell">
                                            <template x-if="item.photo">
                                                <img class="table-user-avatar" :src="Sobuj_photoUrl(item.photo)" :alt="item.name">
                                            </template>
                                            <template x-if="!item.photo">
                                                <div class="table-user-avatar" x-text="Sobuj_initials(item.name)"></div>
                                            </template>
                                            <div>
                                                <div class="table-user-name" x-text="item.name"></div>
                                                <div class="table-user-sub" x-text="item.email"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td x-text="item.student_id || '-'"></td>

                                    <td>
                                <div class="table-user-name" x-text="item.batch_name ? item.batch_name + ' · ' + item.batch_shift : '-'"></div>
                                <div class="table-user-sub"
                                    x-text="item.batch_name ? item.batch_session_name : ''"></div>
                            </td>

                                    <td x-text="item.completed_credit_hours ?? '-'"></td>

                                    <td x-text="item.cgpa ?? '-'"></td>

                                    <?php if (multi_department()): ?><td x-text="item.department_name || '-'"></td><?php endif; ?>

                            <td>
                                <span class="badge-table" :class="item.status == 1 ? 'success' : 'failed'"
                                    x-text="item.status == 1 ? 'Active' : 'Inactive'"></span>
                            </td>

                            <td class="col-actions">
                                <div class="d-flex justify-content-center gap-1">

                                    <a :href="'<?= url('admin/university/students/manage.php?action=update&id=') ?>' + item.id" class="table-btn-action"
                                        title="Edit">
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
                    <span>Showing <span x-text="from"></span> to <span x-text="to"></span> of <span x-text="total"></span> records</span>
                </template>
                <template x-if="total === 0">
                    <span>Showing 0 records</span>
                </template>
            </span>

            <nav aria-label="Student pagination" x-cloak x-show="pages > 1">
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

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/import.js?v=<?= filemtime(base_path('assets/admin_assets/js/import.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js?v=<?= filemtime(base_path('assets/admin_assets/js/users.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
