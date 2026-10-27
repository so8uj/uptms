<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Groups/Teams";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Groups/Teams" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_teamList('<?= url('api/teams.php') ?>', '<?= url('api/options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="alert alert-info py-2">
        A group/team has <strong x-text="minSize"></strong> to <strong x-text="maxSize"></strong> students of one department and batch. A group/team a student asks for
        needs your approval. A student can be in only one group/team until its research is completed, and a group/team works on only one research at a time.
    </div>

    <!-- what waits for the admin: on top, with clear buttons -->
    <div class="card border-warning shadow-sm mb-3" x-cloak x-show="pending.length > 0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-bell-fill text-warning"></i>
                <h6 class="mb-0">Waiting for your decision (<span x-text="pending.length"></span>)</h6>
            </div>

            <template x-for="item in pending" :key="item.id">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top py-2">
                    <div style="min-width: 260px; flex: 1;">
                        <div>
                            <span class="badge-table pending" x-text="pendingKind(item)"></span>
                            <span class="fw-semibold ms-1" x-text="item.name"></span>
                            <span class="text-muted small" x-text="' · ' + item.department_code + ' · ' + item.batch_name"></span>
                        </div>

                        <!-- a new group/team: who is in it -->
                        <div class="small text-muted mt-1" x-show="item.status === 'requested'">
                            Members: <span x-text="members(item)"></span>
                        </div>

                        <!-- a member change: who comes and who goes -->
                        <div class="small mt-1" x-show="item.status !== 'requested' && item.change">
                            <span class="text-muted">Now: <span x-text="members(item)"></span></span><br>
                            <template x-for="student in (item.change ? item.change.added : [])" :key="'a' + student.id">
                                <span class="text-success me-2"><i class="bi bi-plus-circle"></i> <span x-text="student.name"></span></span>
                            </template>
                            <template x-for="student in (item.change ? item.change.removed : [])" :key="'r' + student.id">
                                <span class="text-danger me-2"><i class="bi bi-dash-circle"></i> <span x-text="student.name"></span></span>
                            </template>
                            <span class="text-muted" x-show="item.change && item.change.requested_by_name">
                                (asked by <span x-text="item.change ? item.change.requested_by_name : ''"></span>)
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" @click="approve(item)">
                            <i class="bi bi-check-lg me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" @click="reject(item)">
                            <i class="bi bi-x-lg me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <?php require(base_path('includes/team/decision_box.php')); ?>

    <div class="table-card-custom">

        <div class="table-header-control">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search group/team, student name or ID..." x-model="search"
                    @input.debounce.400ms="filtersChanged()">
            </div>

            <div class="table-filter-group">
                <select class="form-select-custom" style="width: auto;" x-model="departmentId" @change="departmentChanged()"
                    <?= multi_department() ? '' : 'hidden' ?>>
                    <option value="">All departments</option>
                    <template x-for="department in options.departments" :key="department.id">
                        <option :value="String(department.id)" x-text="department.name"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-model="batchId" @change="filtersChanged()">
                    <option value="">All batches</option>
                    <template x-for="batch in batchOptions" :key="batch.id">
                        <option :value="String(batch.id)" x-text="batch.name + ' · ' + batch.shift"></option>
                    </template>
                </select>

                <select class="form-select-custom" style="width: auto;" x-model="status" @change="filtersChanged()">
                    <option value="">All status</option>
                    <option value="requested">Pending approval</option>
                    <option value="open">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="closed">Completed</option>
                </select>

                <a href="<?= url('admin/university/teams/manage.php?action=create') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Add Group/Team
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom">

                <thead>
                    <tr>
                        <th>Group/Team</th>
                        <th>Members</th>
                        <th>Research</th>
                        <th>Status</th>
                        <th class="text-center col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="5" class="text-center text-muted py-4">No groups/teams found.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td>
                                <div class="table-user-name" x-text="item.name"></div>
                                <div class="table-user-sub" x-text="item.department_code + ' · ' + item.batch_name + ' · ' + item.batch_shift"></div>
                            </td>

                            <td style="max-width: 300px; white-space: normal;">
                                <div class="table-user-name" x-text="item.member_count + ' members'"></div>
                                <div class="table-user-sub" x-text="members(item)"></div>

                                <div class="mt-1 small" x-show="item.change && item.change.status === 'pending'">
                                    <span class="badge-table pending">Member change waiting</span>
                                </div>
                            </td>

                            <td style="max-width: 280px; white-space: normal;">
                                <template x-if="item.research_id">
                                    <div>
                                        <div class="table-user-name" x-text="item.research_title"></div>
                                        <div class="table-user-sub" x-text="item.research_code + ' · ' + Sobuj_statusText(item.research_status)"></div>
                                    </div>
                                </template>
                                <span class="text-muted" x-show="!item.research_id">No research yet</span>
                            </td>

                            <td>
                                <span class="badge-table" :class="statusBadge(item.status)" x-text="statusText(item.status)"></span>
                                <div class="table-user-sub" x-show="item.status === 'rejected' && item.review_note" x-text="item.review_note"></div>
                            </td>

                            <td class="col-actions">
                                <div class="d-flex justify-content-center gap-1">
                                    <a :href="'<?= url('admin/university/teams/manage.php?action=update&id=') ?>' + item.id" class="table-btn-action" title="Edit"
                                        x-show="['requested', 'open'].includes(item.status)">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="table-btn-action delete" title="Delete" @click="remove(item)"
                                        x-show="!item.research_id">
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

            <nav aria-label="Group/Team pagination" x-cloak x-show="pages > 1">
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
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/team.js?v=<?= filemtime(base_path('assets/admin_assets/js/team.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
