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

<div class="main-wrapper" x-data="Sobuj_userList('<?= url('api/users.php') ?>', 'student')">
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
                <input type="text" class="table-search-input" placeholder="Search students..." x-model="search"
                    @input.debounce.400ms="searchChanged()">
            </div>

            <div class="table-filter-group">

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" x-text="statusLabel">
                    </button>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('')">All Status</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('1')">Active</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="setStatus('0')">Inactive</a></li>
                    </ul>
                </div>

                <a href="<?= url('admin/university/students/manage.php?action=create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Student
                </a>

            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Student</th>
                        <th>ID</th>
                        <th>Batch</th>
                        <th>Credit Hours</th>
                        <th>CGPA</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="8" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="8" class="text-center text-muted py-4">No students found.</td>
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
                                    x-text="item.batch_name ? Sobuj_termLabel(item.batch_term_system, item.batch_current_term_no, item.batch_total_terms) + (item.batch_session_name ? ' · ' + item.batch_session_name : '') : ''"></div>
                            </td>

                                    <td x-text="item.completed_credit_hours ?? '-'"></td>

                                    <td x-text="item.cgpa ?? '-'"></td>

                                    <td x-text="item.department_name || '-'"></td>

                            <td>
                                <span class="badge-table" :class="item.status == 1 ? 'success' : 'failed'"
                                    x-text="item.status == 1 ? 'Active' : 'Inactive'"></span>
                            </td>

                            <td>
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

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/term.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/users.js"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
