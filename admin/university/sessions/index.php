<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Academic Sessions";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Academic Sessions" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_sessionPage('<?= url('api/sessions.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="row g-4">

        <div class="col-12 col-lg-8">
            <div class="table-card-custom">

                <div class="table-header-control">

                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" id="sessionSearch"
                            placeholder="Search sessions..." x-model="search" @input.debounce.400ms="searchChanged()">
                    </div>

                    <div class="table-filter-group">

                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                x-text="statusLabel">
                            </button>

                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" @click.prevent="setStatus('')">All Status</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="setStatus('1')">Active</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="setStatus('0')">Inactive</a></li>
                            </ul>
                        </div>

                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">

                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Current</th>
                                <th>Batches</th>
                                <th>Status</th>
                                <th class="text-center col-actions">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr x-cloak x-show="loading && items.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                            </tr>

                            <tr x-cloak x-show="!loading && items.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No sessions found.</td>
                            </tr>

                            <template x-for="item in items" :key="item.id">
                                <tr>
                                    <td>
                                        <div class="table-user-name" x-text="item.name"></div>
                                        <div class="table-user-sub" x-text="item.year"></div>
                                    </td>

                                    <td>
                                        <span class="badge-table success" x-cloak x-show="item.is_current == 1">Current</span>
                                    </td>

                                    <td x-text="item.batch_count"></td>

                                    <td>
                                        <span class="badge-table" :class="item.status == 1 ? 'success' : 'failed'"
                                            x-text="item.status == 1 ? 'Active' : 'Inactive'"></span>
                                    </td>

                                    <td class="col-actions">
                                        <div class="d-flex justify-content-center gap-1">

                                            <button type="button" class="table-btn-action" title="Edit" @click="edit(item)">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button type="button" class="table-btn-action delete" title="Delete"
                                                @click="remove(item)">
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

                    <nav aria-label="Session pagination" x-cloak x-show="pages > 1">
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

        <div class="col-12 col-lg-4">
            <div class="card border-light shadow-sm p-4">

                <h5 class="card-title mb-4" x-text="(isEdit ? 'Update' : 'Add') + ' Session'"></h5>

                <form @submit.prevent="save()" novalidate>

                    <div class="mb-3">
                        <label for="itemName" class="form-label-custom">
                            Name <span class="text-danger">*</span>
                        </label>

                        <input type="text" class="form-control-custom" id="itemName" placeholder="For example Spring 2026"
                            x-model="form.name">
                        <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemYear" class="form-label-custom">
                            Year <span class="text-danger">*</span>
                        </label>

                        <input type="number" class="form-control-custom" id="itemYear" placeholder="For example 2026" x-model="form.year" min="2000" max="2100">
                        
                        <template x-for="message in (errors.year || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemCurrent" class="form-label-custom">
                            Current session
                        </label>

                        <select class="form-select-custom" id="itemCurrent" x-model="form.is_current">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                        <small class="text-muted">Only one session can be current. Choosing Yes replaces the old one.</small>
                        <template x-for="message in (errors.is_current || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-4">
                        <label for="itemStatus" class="form-label-custom">
                            Status
                        </label>

                        <select class="form-select-custom" id="itemStatus" x-model="form.status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <template x-for="message in (errors.status || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="d-flex gap-2">

                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-plus-circle'"></i>
                            <span x-text="(isEdit ? 'Update' : 'Add') + ' Session'"></span>
                        </button>

                        <button type="button" class="btn btn-light" x-cloak x-show="isEdit" @click="resetForm()">
                            Cancel
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script>
function Sobuj_fetchSessions(apiUrl, filters) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams(filters));
}

function Sobuj_saveSession(apiUrl, form) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'save', ...form } });
}

function Sobuj_deleteSession(apiUrl, id) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'delete', id: id } });
}

function Sobuj_sessionPage(apiUrl) {
    return {

        items: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        loading: false,

        search: '',
        status: '',

        form: { id: null, name: '', year: new Date().getFullYear(), is_current: '0', status: '1' },
        errors: {},
        saving: false,

        toast: { text: '', type: 'success' },

        toastTimer: null,
        requestId: 0,

        init() {
            this.load();
        },

        get isEdit() {
            return this.form.id !== null;
        },

        get statusLabel() {
            return { '': 'All Status', '1': 'Active', '0': 'Inactive' }[this.status];
        },

        get from() {
            return this.total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },
        get to() {
            return (this.page - 1) * this.perPage + this.items.length;
        },

        shorten(text, length = 60) {
            text = text || '';
            return text.length > length ? text.slice(0, length) + '...' : text;
        },

        notify(text, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { text, type };
            this.toastTimer = setTimeout(() => { this.toast.text = ''; }, 4000);
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchSessions(apiUrl, { q: this.search, status: this.status, page: this.page });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the sessions.', 'error');
                return;
            }

            this.items   = res.data.sessions;
            this.total   = res.data.total;
            this.page    = res.data.page;
            this.pages   = res.data.pages;
            this.perPage = res.data.perPage;
        },

        searchChanged() {
            this.page = 1;
            this.load();
        },

        setStatus(value) {
            this.status = value;
            this.page = 1;
            this.load();
        },

        goTo(number) {
            if (number < 1 || number > this.pages || number === this.page) {
                return;
            }
            this.page = number;
            this.load();
        },

        edit(item) {
            this.errors = {};
            this.form = {
                id: item.id,
                name: item.name,
                year: String(item.year),
                is_current: String(item.is_current),
                status: String(item.status),
            };
        },

        resetForm() {
            this.errors = {};
            this.form = { id: null, name: '', year: new Date().getFullYear(), is_current: '0', status: '1' };
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const res = await Sobuj_saveSession(apiUrl, this.form);
            this.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.resetForm();
                this.load();
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save the session.', 'error');

            if (Object.keys(this.errors).length === 0) {
                this.load();
            }
        },

        async remove(item) {
            if (!await Sobuj_confirm('Delete the session "' + item.name + '"?', { title: 'Delete session', confirmText: 'Delete', danger: true })) {
                return;
            }

            const res = await Sobuj_deleteSession(apiUrl, item.id);

            this.notify(res.data.message || 'Could not delete the session.', res.ok ? 'success' : 'error');

            if (res.ok && this.form.id === item.id) {
                this.resetForm();
            }
            this.load();
        },
    };
}
</script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
