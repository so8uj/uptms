/*
 * Shared by the admins, teachers and students pages (the role is passed in by each page).
 *
 *   database -> User model -> UserController -> api/users.php (JSON)
 *       -> Sobuj_api()                                  js/api.js
 *       -> Sobuj_fetchUsers / Sobuj_fetchUser / Sobuj_saveUser / Sobuj_deleteUser     PART 1: one function per API call
 *       -> Sobuj_userList()   the list pages            PART 2
 *       -> Sobuj_userForm()   the add / edit pages      PART 3
 *       -> the HTML of the page only shows their variables (x-for, x-text, x-model)
 */


// =====================================================================
// PART 1 - Data layer
// =====================================================================

function Sobuj_fetchUsers(apiUrl, role, filters) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams({ role: role, ...filters }));
}

function Sobuj_fetchUser(apiUrl, role, id) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams({ role: role, id: id }));
}

// formData holds the fields and the photo file
function Sobuj_saveUser(apiUrl, formData) {
    return Sobuj_api(apiUrl, { method: 'POST', body: formData });
}

function Sobuj_deleteUser(apiUrl, role, id) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { role: role, op: 'delete', id: id } });
}


// =====================================================================
// Avatar: the photo, or the initials when there is no photo
// =====================================================================

// "Dr. Jannatul Naeem" -> "JN",  "Sobuj" -> "S"  (titles like Dr and Prof are skipped)
function Sobuj_initials(name) {
    const titles = ['dr', 'prof', 'mr', 'mrs', 'ms'];
    const words = (name || '').trim().split(/\s+/).map(function (word) {
        return word.replace(/^[^\p{L}\p{N}]+/u, '');      // "(Student)" -> "Student)"
    }).filter(function (word) {
        return word && !titles.includes(word.replace('.', '').toLowerCase());
    });

    if (words.length === 0) {
        return '?';
    }

    const first = Array.from(words[0])[0].toUpperCase();
    return words.length === 1 ? first : first + Array.from(words[1])[0].toUpperCase();
}

function Sobuj_appUrl() {
    const meta = document.querySelector('meta[name="app-url"]');
    return meta ? meta.content : '';
}

function Sobuj_photoUrl(path) {
    return Sobuj_appUrl() + '/uploads/' + path;
}

// =====================================================================
// PART 2 - The list page (admins, teachers or students)
// =====================================================================

function Sobuj_userList(apiUrl, role, batchesUrl) {
    return {

        items: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        loading: false,

        search: '',
        status: '',
        batchId: '',
        batches: [],
        sort: '',
        dir: 'asc',

        toast: { text: '', type: 'success' },
        toastTimer: null,
        requestId: 0,

        init() {
            const message = Sobuj_takeMessage();
            if (message) {
                this.notify(message);
            }

            // a link from the batch list opens this page with ?batch_id=3
            this.batchId = new URLSearchParams(window.location.search).get('batch_id') || '';

            this.load();
            this.loadBatches();
        },

        async loadBatches() {
            if (!batchesUrl) {
                return;
            }
            const res = await Sobuj_api(batchesUrl);
            if (res.ok) {
                this.batches = res.data.batches;

                // the options exist now: set the chosen batch again so the dropdown shows it
                const chosen = this.batchId;
                this.batchId = '';
                this.$nextTick(() => { this.batchId = chosen; });
            }
        },

        // a click on a column title: sort by it, a second click turns the direction
        setSort(key) {
            if (this.sort === key) {
                this.dir = this.dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = key;
                this.dir = 'asc';
            }
            this.page = 1;
            this.load();
        },

        sortIcon(key) {
            if (this.sort !== key) {
                return 'bi-arrow-down-up text-muted';
            }
            return this.dir === 'asc' ? 'bi-sort-up' : 'bi-sort-down';
        },

        batchChanged() {
            this.page = 1;
            this.load();
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

        notify(text, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { text, type };
            this.toastTimer = setTimeout(() => { this.toast.text = ''; }, 4000);
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchUsers(apiUrl, role, { q: this.search, status: this.status, batch_id: this.batchId, sort: this.sort, dir: this.dir, page: this.page });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the list.', 'error');
                return;
            }

            this.items   = res.data.users;
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

        async remove(item) {
            if (!await Sobuj_confirm('Delete "' + item.name + '"?', { title: 'Delete', confirmText: 'Delete', danger: true })) {
                return;
            }

            const res = await Sobuj_deleteUser(apiUrl, role, item.id);
            this.notify(res.data.message || 'Could not delete.', res.ok ? 'success' : 'error');
            this.load();
        },
    };
}


// =====================================================================
// PART 3 - The add / edit page
// =====================================================================

// Which fields each kind of user has (these are sent to the server)
const Sobuj_userFields = {
    admin:   ['name', 'email', 'phone', 'password', 'status'],
    teacher: ['name', 'email', 'phone', 'password', 'status', 'employee_id', 'department_id', 'designation',
              'specialization', 'research_area', 'research_management'],
    student: ['name', 'email', 'phone', 'password', 'status', 'student_id', 'department_id', 'batch_id', 'gender',
              'cgpa', 'completed_credit_hours'],
};

function Sobuj_userForm(apiUrl, optionsUrl, role, id, listUrl) {
    return {

        form: {
            name: '', email: '', phone: '', password: '', status: '1',
            employee_id: '', department_id: '', designation: '', specialization: '', research_area: '', research_management: '0',
            student_id: '', batch_id: '', gender: '', cgpa: '', completed_credit_hours: '',
        },
        errors: {},
        saving: false,
        loading: true,

        departments: [],
        batches: [],

        photoFile: null,
        photoPreview: '',
        currentPhoto: '',

        toast: { text: '', type: 'success' },
        toastTimer: null,

        async init() {
            if (role !== 'admin') {
                const options = await Sobuj_api(optionsUrl);
                if (options.ok) {
                    this.departments = options.data.departments;
                    this.batches = options.data.batches;
                }
            }

            if (id) {
                await this.loadUser();
            }
            this.loading = false;
        },

        get isEdit() {
            return id > 0;
        },

        // The batches of the chosen department
        get batchOptions() {
            return this.batches.filter((batch) => String(batch.department_id) === String(this.form.department_id));
        },

        // What the avatar shows: the chosen file, else the saved photo, else (empty) the initials
        get shownPhoto() {
            if (this.photoPreview) {
                return this.photoPreview;
            }
            return this.currentPhoto ? Sobuj_photoUrl(this.currentPhoto) : '';
        },

        notify(text, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { text, type };
            this.toastTimer = setTimeout(() => { this.toast.text = ''; }, 4000);
        },

        async loadUser() {
            const res = await Sobuj_fetchUser(apiUrl, role, id);

            if (!res.ok) {
                Sobuj_rememberMessage(res.data.message || 'This user no longer exists.');
                window.location.href = listUrl;
                return;
            }

            const user = res.data.user;
            for (const field of Sobuj_userFields[role]) {
                if (field !== 'password' && user[field] !== undefined && user[field] !== null) {
                    this.form[field] = String(user[field]);
                }
            }
            this.currentPhoto = user.photo || '';
        },

        // The department changed: the old batch may not belong to it
        departmentChanged() {
            this.form.batch_id = '';
        },

        // The user picked a file: show it straight away (nothing is uploaded yet)
        photoChosen(event) {
            const file = event.target.files[0];
            this.errors.photo = [];

            if (!file) {
                return this.clearPhoto();
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                event.target.value = '';
                this.errors.photo = ['Allowed file types: JPG, PNG, WEBP.'];
                return this.clearPhoto();
            }
            if (file.size > 2 * 1024 * 1024) {
                event.target.value = '';
                this.errors.photo = ['The file must be smaller than 2 MB.'];
                return this.clearPhoto();
            }

            if (this.photoPreview) {
                URL.revokeObjectURL(this.photoPreview);
            }
            this.photoFile = file;
            this.photoPreview = URL.createObjectURL(file);
        },

        clearPhoto() {
            if (this.photoPreview) {
                URL.revokeObjectURL(this.photoPreview);
            }
            this.photoFile = null;
            this.photoPreview = '';
            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const data = new FormData();
            data.append('role', role);
            data.append('op', 'save');
            if (id) {
                data.append('id', id);
            }
            for (const field of Sobuj_userFields[role]) {
                data.append(field, this.form[field]);
            }
            if (this.photoFile) {
                data.append('photo', this.photoFile);
            }

            const res = await Sobuj_saveUser(apiUrl, data);
            this.saving = false;

            if (res.ok) {
                Sobuj_rememberMessage(res.data.message);
                window.location.href = listUrl;
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save.', 'error');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    };
}
