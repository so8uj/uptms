<?php

require_once(__DIR__ . '/../../../app/bootstrap.php');
require_once(base_path('admin/includes/header.php'));
require_once(base_path('admin/includes/sidebar.php'));

$pageHeader = "Thesis Committees";
$showBreadcrumb = true;
$breadcrumbs = [
    "Dashboard" => url('admin/index.php'),
    "University" => "",
    "Thesis Committees" => ""
];

?>

<div class="main-wrapper" x-data="Sobuj_committeePage('<?= url('api/committees.php') ?>')">
    <?php
    require_once(base_path('admin/includes/dashboard_header.php'));
    require_once(base_path('includes/admin/page_header_breadcrumb.php'));
    ?>

    <?php require(base_path('admin/includes/university_nav_open.php')); ?>

    <div x-cloak x-show="toast.text" class="alert py-2" :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        x-text="toast.text"></div>

    <div class="alert alert-light border py-2 small">
        A committee can have many members, but only the members ticked <strong>Can approve</strong> sign the topics and the requests to finish
        (approve, send back for correction, or reject). Any <strong>2</strong> of the ticked members decide. A committee works for the whole university or for one department.
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-7">
            <div class="table-card-custom">

                <div class="table-header-control">

                    <div class="table-search-box">
                        <i class="bi bi-search table-search-icon"></i>
                        <input type="text" class="table-search-input" placeholder="Search committees..." x-model="search"
                            @input.debounce.400ms="searchChanged()">
                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table-custom">

                        <thead>
                            <tr>
                                <th>Committee</th>
                                <th>Members</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr x-cloak x-show="loading && items.length === 0">
                                <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                            </tr>

                            <tr x-cloak x-show="!loading && items.length === 0">
                                <td colspan="4" class="text-center text-muted py-4">No committee yet. Add one, otherwise nobody can approve a topic.</td>
                            </tr>

                            <template x-for="item in items" :key="item.id">
                                <tr>
                                    <td style="white-space: normal;">
                                        <div class="table-user-name" x-text="item.name"></div>
                                        <div class="table-user-sub" x-text="item.department_name ? 'Department: ' + item.department_name : 'Whole university'"></div>
                                    </td>

                                    <td style="white-space: normal; min-width: 180px;">
                                        <template x-for="member in item.members" :key="member.teacher_id">
                                            <div class="small">
                                                <span x-text="member.name"></span>
                                                <span class="badge-table success ms-1" x-show="member.role === 'chair'">Chair</span>
                                                <span class="badge-table pending ms-1" x-show="member.can_approve">Approves</span>
                                            </div>
                                        </template>
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

                    <nav aria-label="Committee pagination" x-cloak x-show="pages > 1">
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

        <div class="col-12 col-lg-5">
            <div class="card border-light shadow-sm p-4">

                <h5 class="card-title mb-4" x-text="(isEdit ? 'Update' : 'Add') + ' Committee'"></h5>

                <form @submit.prevent="save()" novalidate>

                    <div class="mb-3">
                        <label for="committeeName" class="form-label-custom">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control-custom" id="committeeName" placeholder="For example: CSE Thesis Committee"
                            x-model="form.name">
                        <template x-for="message in (errors.name || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label for="committeeDepartment" class="form-label-custom">Works for</label>
                        <select class="form-select-custom" id="committeeDepartment" x-model="form.department_id">
                            <option value="">The whole university</option>
                            <template x-for="department in options.departments" :key="department.id">
                                <option :value="String(department.id)" x-text="department.name"></option>
                            </template>
                        </select>
                        <template x-for="message in (errors.department_id || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Members <span class="text-danger">*</span></label>
                        <div class="small text-muted mb-1">Tick <strong>Can approve</strong> for the members who sign. Any 2 of the ticked members approve a topic or a finish.</div>

                        <?php $pickerModel = 'memberPick'; $pickerEmpty = 'Add a teacher'; $pickerId = 'committeeMember'; require(base_path('includes/research/supervisor_picker.php')); ?>

                        <div class="mt-2" x-cloak x-show="form.members.length === 0">
                            <span class="small text-muted">No member added yet.</span>
                        </div>

                        <template x-for="teacherId in form.members" :key="teacherId">
                            <div class="d-flex align-items-center justify-content-between border rounded-3 px-3 py-2 mt-2">
                                <span x-text="Sobuj_teacherLabel(options.teachers, teacherId, 'Teacher')"></span>
                                <span class="d-flex align-items-center gap-3">
                                    <label class="small mb-0" style="cursor: pointer;" title="Only the ticked members can approve a topic or a finish. Any 2 of them decide.">
                                        <input type="checkbox" :value="teacherId" x-model="form.approvers"> Can approve
                                    </label>
                                    <label class="small text-muted mb-0" style="cursor: pointer;">
                                        <input type="radio" name="committeeChair" :value="teacherId" x-model="form.chair_id"> Chair
                                    </label>
                                    <button type="button" class="table-btn-action delete" title="Remove" @click="removeMember(teacherId)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </span>
                            </div>
                        </template>

                        <template x-for="message in (errors.members || [])">
                            <div class="text-danger small mt-1" x-text="message"></div>
                        </template>
                    </div>

                    <div class="mb-4">
                        <label for="committeeStatus" class="form-label-custom">Status</label>
                        <select class="form-select-custom" id="committeeStatus" x-model="form.status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <i class="bi me-1" :class="isEdit ? 'bi-pencil' : 'bi-plus-circle'"></i>
                            <span x-text="(isEdit ? 'Update' : 'Add') + ' Committee'"></span>
                        </button>

                        <button type="button" class="btn btn-light" x-cloak x-show="isEdit" @click="resetForm()">Cancel</button>
                    </div>

                </form>

            </div>
        </div>

    </div>

    <?php require(base_path('admin/includes/section_nav_close.php')); ?>
</div>

<script src="<?= env('ASSET_URL') ?>/admin_assets/js/api.js?v=<?= filemtime(base_path('assets/admin_assets/js/api.js')) ?>"></script>
<script src="<?= env('ASSET_URL') ?>/admin_assets/js/research.js?v=<?= filemtime(base_path('assets/admin_assets/js/research.js')) ?>"></script>
<script>
function Sobuj_committeePage(apiUrl) {
    return {

        items: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        loading: false,
        search: '',

        options: { departments: [], teachers: [] },
        memberPick: '',

        form: { id: null, name: '', department_id: '', status: '1', members: [], approvers: [], chair_id: '' },
        errors: {},
        saving: false,

        toast: { text: '', type: 'success' },
        toastTimer: null,
        requestId: 0,

        async init() {
            this.$watch('memberPick', (value) => {
                if (value) {
                    this.addMember(value);
                    this.memberPick = '';
                }
            });

            const res = await Sobuj_api(apiUrl + '?options=1');
            if (res.ok) {
                this.options = res.data;
            }

            this.load();
        },

        get isEdit() {
            return this.form.id !== null;
        },
        get from() {
            return this.total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },
        get to() {
            return (this.page - 1) * this.perPage + this.items.length;
        },

        notify(text, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { text, type };
            this.toastTimer = setTimeout(() => { this.toast.text = ''; }, 4000);
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ q: this.search, page: this.page }));

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the committees.', 'error');
                return;
            }

            this.items   = res.data.committees;
            this.total   = res.data.total;
            this.page    = res.data.page;
            this.pages   = res.data.pages;
            this.perPage = res.data.perPage;
        },

        searchChanged() {
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

        // a new member can approve at once (untick the box for a member who only takes part)
        addMember(id) {
            id = String(id);
            if (!this.form.members.includes(id)) {
                this.form.members.push(id);
                this.form.approvers.push(id);
            }
        },

        removeMember(id) {
            this.form.members = this.form.members.filter((item) => item !== id);
            this.form.approvers = this.form.approvers.filter((item) => item !== id);
            if (this.form.chair_id === id) {
                this.form.chair_id = '';
            }
        },

        edit(item) {
            this.errors = {};
            const chair = item.members.find((member) => member.role === 'chair');

            this.form = {
                id: item.id,
                name: item.name,
                department_id: item.department_id ? String(item.department_id) : '',
                status: String(item.status),
                members: item.members.map((member) => String(member.teacher_id)),
                approvers: item.members.filter((member) => member.can_approve).map((member) => String(member.teacher_id)),
                chair_id: chair ? String(chair.teacher_id) : '',
            };
        },

        resetForm() {
            this.errors = {};
            this.form = { id: null, name: '', department_id: '', status: '1', members: [], approvers: [], chair_id: '' };
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'save', ...this.form } });
            this.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.resetForm();
                this.load();
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save the committee.', 'error');

            if (Object.keys(this.errors).length === 0) {
                this.load();
            }
        },

        async remove(item) {
            if (!await Sobuj_confirm('Delete the committee "' + item.name + '"? Its members will no longer be able to decide.', { title: 'Delete committee', confirmText: 'Delete', danger: true })) {
                return;
            }

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'delete', id: item.id } });

            this.notify(res.data.message || 'Could not delete the committee.', res.ok ? 'success' : 'error');

            if (res.ok && this.form.id === item.id) {
                this.resetForm();
            }
            this.load();
        },
    };
}
</script>

<?php require_once(base_path('admin/includes/footer.php')); ?>
