<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

$me = App\Controllers\Auth::currentUser();

require_once(base_path('admin/includes/header.php'));
require_once(base_path('student/includes/sidebar.php'));

$pageHeader = "My Research";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('student/index.php'),
    "Research Library" => url('student/research/index.php'),
    "My Research" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_myResearch('<?= url('api/my_research.php') ?>', 'student', '<?= url('api/project.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Requested</div><div class="fs-4 fw-bold" x-text="counts.requested ?? 0"></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Approved (waiting for supervisor)</div><div class="fs-4 fw-bold" x-text="counts.approved ?? 0"></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Ongoing</div><div class="fs-4 fw-bold" x-text="counts.ongoing ?? 0"></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-light shadow-sm p-3"><div class="small text-muted">Completed</div><div class="fs-4 fw-bold" x-text="counts.completed ?? 0"></div></div>
        </div>
    </div>

    <div class="table-card-custom">

        <div class="table-header-control">

            <div class="table-search-box">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Search my research..." x-model="search"
                    @input.debounce.400ms="filtersChanged()">
            </div>

            <div class="table-filter-group">

                <select class="form-select-custom" style="width: auto;" x-model="status" @change="filtersChanged()">
                    <option value="">All status</option>
                    <option value="requested">Requested</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="correction">Needs correction</option>
                    <option value="rejected">Rejected (final)</option>
                </select>

                <a href="<?= url('student/research/manage.php?action=create') ?>" class="btn btn-primary" x-cloak x-show="canAdd">
                    <i class="bi bi-plus-circle me-1"></i>
                    Request a Topic
                </a>

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
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Sent</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="8" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="8" class="text-center text-muted py-4">You have no research yet.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                            <td style="max-width: 420px; white-space: normal;">
                                <div class="table-user-name" x-text="item.title"></div>
                                <div class="table-user-sub" x-text="item.code + ' · ' + item.type"></div>
                                <div class="small text-warning-emphasis mt-1" x-cloak x-show="item.status === 'correction' && item.review_note"
                                    x-text="'Correction needed: ' + item.review_note"></div>
                                <div class="small text-danger mt-1" x-cloak x-show="item.status === 'rejected' && item.review_note"
                                    x-text="'Rejected for good: ' + item.review_note"></div>
                                <div class="small text-muted mt-1" x-cloak x-show="item.supervisor_name"
                                    x-text="'Supervisor: ' + item.supervisor_name"></div>
                            </td>

                            <td style="max-width: 230px; white-space: normal;">
                                <div class="table-user-name" x-text="item.team_name || 'No group/team'"></div>
                                <div class="table-user-sub" x-text="Sobuj_authors(item)"></div>
                            </td>
                            <td x-text="item.batch_name || '-'"></td>
                            <td x-text="item.category_name"></td>
                            <td><span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span></td>
                            <td style="min-width: 150px;">
                                <div x-cloak x-show="['ongoing', 'completed'].includes(item.status)">
                                    <div class="small mb-1" x-text="item.percent + '% · ' + item.done + ' of ' + item.tasks + ' tasks'"></div>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" :style="'width: ' + item.percent + '%;'"></div></div>
                                    <div class="small text-muted mt-1" x-cloak x-show="item.finish_status"
                                        x-text="{ teacher: 'Finish: supervisor to decide', team: 'Finish: team to confirm', admin: 'Finish: administrator to decide' }[item.finish_status] || ''"></div>
                                </div>
                                <span class="text-muted small" x-cloak x-show="!['ongoing', 'completed'].includes(item.status)">-</span>
                            </td>
                            <td x-text="item.created_at.substring(0, 10)"></td>

                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a :href="'<?= url('student/research/manage.php?action=update&id=') ?>' + item.id" class="table-btn-action"
                                        title="Edit" x-cloak x-show="isOpen(item) && item.created_by == <?= (int) $me['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a :href="'<?= url('student/project.php?id=') ?>' + item.id" class="table-btn-action" title="Project work"
                                        x-cloak x-show="['ongoing', 'completed'].includes(item.status)">
                                        <i class="bi bi-kanban"></i>
                                    </a>
                                    <a href="<?= url('student/team.php') ?>" class="table-btn-action" title="Group/Team"
                                        x-cloak x-show="item.team_id">
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <button type="button" class="table-btn-action delete" title="Withdraw" @click="withdraw(item)"
                                        x-cloak x-show="canWithdraw(item) && item.created_by == <?= (int) $me['id'] ?>">
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

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
