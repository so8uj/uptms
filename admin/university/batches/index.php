<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Batch Management";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Batch Management" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_batchPage('<?= url('api/batches.php') ?>', '<?= url('api/options.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="row g-4">

        <div class="col-12 col-lg-8">
            <div class="table-card-custom">

                <div class="table-header-control">

                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" id="batchSearch"
                            placeholder="Search batches..." x-model="search" @input.debounce.400ms="searchChanged()">
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
                                <th>Batch</th>
                                <th>Department</th>
                                <th>Current Term</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr x-cloak x-show="loading && items.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                            </tr>

                            <tr x-cloak x-show="!loading && items.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No batches found.</td>
                            </tr>

                            <template x-for="item in items" :key="item.id">
                                <tr>
                                    <td>
                                        <div class="table-user-name" x-text="item.name"></div>
                                        <div class="table-user-sub"
                                            x-text="item.code + ' · ' + item.student_count + (item.student_count == 1 ? ' student' : ' students')"></div>
                                    </td>

                                    <td x-text="item.department_code" :title="item.department_name"></td>

                                                                        <td>
                                        <div class="table-user-name"
                                            x-text="Sobuj_termLabel(item.term_system, item.current_term_no, item.total_terms)"></div>
                                        <div class="table-user-sub" x-text="item.shift + ' · ' + (item.current_session_name || 'No session set')"></div>
                                    </td>

                                    <td>
                                        <span class="badge-table" :class="item.status == 1 ? 'success' : 'failed'"
                                            x-text="item.status == 1 ? 'Active' : 'Inactive'"></span>
                                    </td>

                                    <td>
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

                    <nav aria-label="Batch pagination" x-cloak x-show="pages > 1">
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

                <h5 class="card-title mb-4" x-text="(isEdit ? 'Update' : 'Add') + ' Batch'"></h5>

                <form @submit.prevent="save()" novalidate>

                    <div class="mb-3">
                        <label for="itemName" class="form-label-custom">
                            Name <span class="text-danger">*</span>
                        </label>

                        <input type="text" class="form-control-custom" id="itemName" placeholder="Enter batch name"
                            x-model="form.name">
                        <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemCode" class="form-label-custom">
                            Code <span class="text-danger">*</span>
                        </label>

                        <input type="text" class="form-control-custom" id="itemCode" placeholder="For example CSE-65B" x-model="form.code">
                        
                        <template x-for="message in (errors.code || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemDepartment" class="form-label-custom">
                            Department <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="itemDepartment" x-model="form.department_id">
                            <option value="">Select department</option>
                            <template x-for="department in departments" :key="department.id">
                                <option :value="String(department.id)" x-text="department.name"></option>
                            </template>
                        </select>
                        
                        <template x-for="message in (errors.department_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemShift" class="form-label-custom">
                            Shift <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="itemShift" x-model="form.shift">
                            <option value="Regular">Regular</option>
                            <option value="Evening">Evening</option>
                        </select>
                        
                        <template x-for="message in (errors.shift || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemSystem" class="form-label-custom">
                            Term system <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="itemSystem" x-model="form.term_system" @change="systemChanged()">
                            <option value="">Select system</option>
                            <option value="yearly">Yearly</option>
                            <option value="semester">Semester</option>
                            <option value="trimester">Trimester</option>
                        </select>
                        
                        <template x-for="message in (errors.term_system || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemLength" class="form-label-custom">
                            Program length <span x-show="form.term_system" x-text="'(' + Sobuj_termPlural(form.term_system) + ')'"></span> <span class="text-danger">*</span>
                        </label>

                        <input type="number" class="form-control-custom" id="itemLength" placeholder="For example 4, 6 or 12" x-model="form.total_terms" min="1" max="20">
                        <template x-for="message in (errors.total_terms || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemAdmission" class="form-label-custom">
                            Admission session <span class="text-danger">*</span>
                        </label>

                        <select class="form-select-custom" id="itemAdmission" x-model="form.admission_session_id" :disabled="!form.term_system">
                            <option value="">Select session</option>
                            <template x-for="session in sessionOptions" :key="session.id">
                                <option :value="String(session.id)" x-text="session.name"></option>
                            </template>
                        </select>
                        <small class="text-muted" x-show="!form.term_system">Choose the term system first.</small>
                        <template x-for="message in (errors.admission_session_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemCurrentSession" class="form-label-custom">
                            Current session
                        </label>

                        <select class="form-select-custom" id="itemCurrentSession" x-model="form.current_session_id" :disabled="!form.term_system">
                            <option value="">No session set yet</option>
                            <template x-for="session in sessionOptions" :key="session.id">
                                <option :value="String(session.id)" x-text="session.name"></option>
                            </template>
                        </select>
                        
                        <template x-for="message in (errors.current_session_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemTerm" class="form-label-custom">
                            Current term <span x-show="form.term_system" x-text="'(' + Sobuj_termName(form.term_system) + ' number)'"></span> <span class="text-danger">*</span>
                        </label>

                        <input type="number" class="form-control-custom" id="itemTerm" placeholder="For example 2" x-model="form.current_term_no" min="1" max="20">
                        <template x-for="message in (errors.current_term_no || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="itemDescription" class="form-label-custom">
                            Description
                        </label>

                        <textarea class="form-control-custom" id="itemDescription" rows="5"
                            placeholder="Enter a short description..." x-model="form.description"></textarea>
                        <template x-for="message in (errors.description || [])">
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
                            <span x-text="(isEdit ? 'Update' : 'Add') + ' Batch'"></span>
                        </button>

                        <button type="button" class="btn btn-light" x-cloak x-show="isEdit" @click="resetForm()">
                            Cancel
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/term.js"></script>
<script>
function Sobuj_fetchBatches(apiUrl, filters) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams(filters));
}

function Sobuj_saveBatch(apiUrl, form) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'save', ...form } });
}

function Sobuj_deleteBatch(apiUrl, id) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'delete', id: id } });
}

function Sobuj_batchPage(apiUrl, optionsUrl) {
    return {

        items: [],
        departments: [],
        sessions: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        loading: false,

        search: '',
        status: '',

        form: { id: null, name: '', code: '', department_id: '', shift: 'Regular', term_system: '', total_terms: '', admission_session_id: '', current_session_id: '', current_term_no: '1', description: '', status: '1' },
        errors: {},
        saving: false,

        toast: { text: '', type: 'success' },

        toastTimer: null,
        requestId: 0,

        init() {
            this.load();
            this.loadOptions();
        },

        async loadOptions() {
            const res = await Sobuj_api(optionsUrl);
            if (res.ok) {
                this.departments = res.data.departments;
                this.sessions = res.data.sessions;
            }
        },

        get sessionOptions() {
            return this.sessions.filter((session) => session.term_type === this.form.term_system);
        },

        systemChanged() {
            this.form.admission_session_id = '';
            this.form.current_session_id = '';
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

            const res = await Sobuj_fetchBatches(apiUrl, { q: this.search, status: this.status, page: this.page });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the batches.', 'error');
                return;
            }

            this.items   = res.data.batches;
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
                code: item.code,
                department_id: String(item.department_id),
                shift: item.shift,
                term_system: item.term_system,
                total_terms: String(item.total_terms),
                admission_session_id: '',
                current_session_id: '',
                current_term_no: String(item.current_term_no),
                description: item.description || '',
                status: String(item.status),
            };

            this.$nextTick(() => {
                this.form.admission_session_id = String(item.admission_session_id);
                this.form.current_session_id = item.current_session_id ? String(item.current_session_id) : '';
            });
        },

        resetForm() {
            this.errors = {};
            this.form = { id: null, name: '', code: '', department_id: '', shift: 'Regular', term_system: '', total_terms: '', admission_session_id: '', current_session_id: '', current_term_no: '1', description: '', status: '1' };
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const res = await Sobuj_saveBatch(apiUrl, this.form);
            this.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.resetForm();
                this.load();
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save the batch.', 'error');

            if (Object.keys(this.errors).length === 0) {
                this.load();
            }
        },

        async remove(item) {
            if (!confirm('Delete the batch "' + item.name + '"?')) {
                return;
            }

            const res = await Sobuj_deleteBatch(apiUrl, item.id);

            this.notify(res.data.message || 'Could not delete the batch.', res.ok ? 'success' : 'error');

            if (res.ok && this.form.id === item.id) {
                this.resetForm();
            }
            this.load();
        },
    };
}
</script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
