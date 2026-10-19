<?php

require_once(__DIR__ . '/../../app/bootstrap.php');

require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Pending Submissions";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "Research Library" => url('admin/research/index.php'),
    "Pending Submissions" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_researchAdminList('<?= url('api/research.php') ?>', '<?= url('api/research_options.php') ?>', 'review')">
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
                        <th>Submitted by</th>
                        <th>Category</th>
                        <th>Wants to be</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <tr x-cloak x-show="loading && items.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">Loading...</td>
                    </tr>

                    <tr x-cloak x-show="!loading && items.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">Nothing is waiting for review.</td>
                    </tr>

                    <template x-for="item in items" :key="item.id">
                        <tr>
                        <td style="max-width: 340px; white-space: normal;">
                            <div class="table-user-name" x-text="item.title"></div>
                            <div class="table-user-sub" x-text="item.code + ' · ' + item.type + ' · ' + item.year"></div>
                        </td>

                        <td>
                            <div class="table-user-name" x-text="item.creator_name"></div>
                            <div class="table-user-sub" x-text="item.created_at.substring(0, 10)"></div>
                        </td>

                        <td x-text="item.category_name"></td>

                        <td x-text="Sobuj_statusText(item.target_status)"></td>

                        <td><span class="badge-table" :class="Sobuj_statusBadge(item.status)" x-text="Sobuj_statusText(item.status)"></span></td>

                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="table-btn-action" title="Approve" @click="openReview(item, 'approve')">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button type="button" class="table-btn-action delete" title="Reject" @click="openReview(item, 'reject')">
                                    <i class="bi bi-x-lg"></i>
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

    <div x-cloak x-show="review.open" class="position-fixed top-0 start-0 w-100 h-100"
        style="background: rgba(0, 0, 0, 0.45); z-index: 2000;" @keydown.escape.window="closeReview()">

        <div class="card shadow p-4 position-absolute top-50 start-50 translate-middle"
            style="max-width: 560px; width: calc(100% - 2rem); max-height: 92vh; overflow-y: auto;">

            <template x-if="review.item">
                <div>
                    <div class="small text-muted" x-text="review.item.code + ' · ' + review.item.type + ' · ' + review.item.category_name"></div>
                    <h5 class="mb-2" x-text="review.item.title"></h5>
                    <p class="text-muted small mb-1" x-text="review.item.abstract"></p>
                    <div class="small mb-3">Submitted by <strong x-text="review.item.creator_name"></strong>,
                        wants it as <strong x-text="Sobuj_statusText(review.item.target_status)"></strong>.</div>
                </div>
            </template>

            <div class="btn-group w-100 mb-3" role="group">
                <button type="button" class="btn" :class="review.decision === 'approve' ? 'btn-primary' : 'btn-light'"
                    @click="review.decision = 'approve'; review.errors = {}">Approve</button>
                <button type="button" class="btn" :class="review.decision === 'reject' ? 'btn-danger' : 'btn-light'"
                    @click="review.decision = 'reject'; review.errors = {}">Reject</button>
            </div>

            <div x-show="review.decision === 'approve'">

                <div class="mb-3">
                    <label for="reviewStatus" class="form-label-custom">It becomes</label>
                    <select class="form-select-custom" id="reviewStatus" x-model="review.status">
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                    </select>
                    <template x-for="message in (review.errors.status || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

                <div class="mb-3">
                    <label for="reviewSupervisor" class="form-label-custom">
                        Supervisor <span class="text-danger" x-show="review.status === 'ongoing'">*</span>
                    </label>
                    <select class="form-select-custom" id="reviewSupervisor" x-model="review.supervisor_id">
                        <option value="">No supervisor</option>
                        <template x-for="teacher in options.teachers" :key="teacher.id">
                            <option :value="String(teacher.id)" x-text="teacher.name + (teacher.employee_id ? ' (' + teacher.employee_id + ')' : '')"></option>
                        </template>
                    </select>
                    <template x-for="message in (review.errors.supervisor_id || [])">
                        <div class="text-danger small mt-1" x-text="message"></div>
                    </template>
                </div>

            </div>

            <div class="mb-3">
                <label for="reviewNote" class="form-label-custom">
                    Note <span x-text="review.decision === 'reject' ? '(the reason, the student or teacher will see it)' : '(optional)'"></span>
                    <span class="text-danger" x-show="review.decision === 'reject'">*</span>
                </label>
                <textarea class="form-control-custom" id="reviewNote" rows="3" maxlength="500" x-model="review.note"></textarea>
                <template x-for="message in (review.errors.note || [])">
                    <div class="text-danger small mt-1" x-text="message"></div>
                </template>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-light" @click="closeReview()">Cancel</button>
                <button type="button" class="btn" :class="review.decision === 'reject' ? 'btn-danger' : 'btn-primary'"
                    :disabled="review.saving" @click="submitReview()"
                    x-text="review.decision === 'reject' ? 'Reject research' : 'Approve research'"></button>
            </div>

        </div>
    </div>

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js"></script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
