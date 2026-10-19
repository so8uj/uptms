/*
 * Research pages, shared by the admin, student and teacher panels.
 *
 *   database -> Research model -> ResearchController -> api/research.php | library.php | my_research.php (JSON)
 *       -> Sobuj_api()                                         js/api.js
 *       -> Sobuj_fetchResearch ... (PART 1: one function per API call)
 *       -> the components (PART 2): the variables and actions each page shows
 *
 *   Sobuj_researchAdminList   admin: all research, pending review, ongoing
 *   Sobuj_researchLibrary     everyone: the read-only library
 *   Sobuj_myResearch          student / teacher: their own research and requests
 *   Sobuj_researchAdminForm   admin: add / edit
 *   Sobuj_researchOwnerForm   student request / teacher add
 */


// =====================================================================
// PART 1 - Data layer
// =====================================================================

function Sobuj_fetchResearch(apiUrl, filters) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams(filters));
}

function Sobuj_fetchOneResearch(apiUrl, id) {
    return Sobuj_api(apiUrl + '?' + new URLSearchParams({ id: id }));
}

function Sobuj_saveResearch(apiUrl, body) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'save', ...body } });
}

function Sobuj_reviewResearch(apiUrl, body) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'review', ...body } });
}

function Sobuj_completeResearch(apiUrl, id) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'complete', id: id } });
}

function Sobuj_deleteResearch(apiUrl, id) {
    return Sobuj_api(apiUrl, { method: 'POST', body: { op: 'delete', id: id } });
}


// =====================================================================
// Small helpers used by the pages
// =====================================================================

// The wording and badge colour of each status
function Sobuj_statusText(status) {
    return {
        requested: 'Requested', pending: 'Pending', ongoing: 'Ongoing', completed: 'Completed', rejected: 'Rejected',
    }[status] || status;
}

function Sobuj_statusBadge(status) {
    return { completed: 'success', rejected: 'failed' }[status] || 'pending';
}

// "Rahim Uddin, Karima Begum" - the students of a research ("-" when it has none)
function Sobuj_authors(item) {
    const names = (item.students || []).map(function (student) { return student.name; });
    return names.length ? names.join(', ') : '-';
}

// A message box that shows for 4 seconds
function Sobuj_toastMixin() {
    return {
        toast: { text: '', type: 'success' },
        toastTimer: null,

        notify(text, type = 'success') {
            clearTimeout(this.toastTimer);
            this.toast = { text, type };
            this.toastTimer = setTimeout(() => { this.toast.text = ''; }, 4000);
        },
    };
}

// The page number logic every list shares
function Sobuj_pagingMixin() {
    return {
        items: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        loading: false,
        requestId: 0,

        // "Showing 11 to 13 of 13 records" (these are methods, not getters, because the mixin is copied with ...)
        rangeFrom() {
            return this.total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },
        rangeTo() {
            return (this.page - 1) * this.perPage + this.items.length;
        },

        goTo(number) {
            if (number < 1 || number > this.pages || number === this.page) {
                return;
            }
            this.page = number;
            this.load();
        },

        filtersChanged() {
            this.page = 1;
            this.load();
        },

        // Copies the paging numbers of an API answer into the variables
        takePaging(data, key) {
            this.items   = data[key];
            this.total   = data.total;
            this.page    = data.page;
            this.pages   = data.pages;
            this.perPage = data.perPage;
        },
    };
}


// =====================================================================
// PART 2 - Components
// =====================================================================

// ---------------------------------------------------------------------
// Admin lists. mode: 'all' | 'review' (requested + pending) | 'ongoing'
// ---------------------------------------------------------------------
function Sobuj_researchAdminList(apiUrl, optionsUrl, mode) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        status: mode === 'review' ? 'review' : (mode === 'ongoing' ? 'ongoing' : ''),
        categoryId: '',
        type: '',
        counts: {},
        options: { categories: [], tags: [], types: [], teachers: [] },

        // the approve / reject box
        review: { open: false, item: null, decision: 'approve', status: 'ongoing', supervisor_id: '', note: '', errors: {}, saving: false },

        init() {
            const message = Sobuj_takeMessage();
            if (message) {
                this.notify(message);
            }
            this.load();
            this.loadOptions();
        },

        async loadOptions() {
            const res = await Sobuj_api(optionsUrl);
            if (res.ok) {
                this.options = res.data;
            }
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchResearch(apiUrl, {
                q: this.search, status: this.status, category_id: this.categoryId, type: this.type, page: this.page,
            });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the research list.', 'error');
                return;
            }

            this.takePaging(res.data, 'research');
            this.counts = res.data.counts;
        },

        // ----- approve / reject -----

        openReview(item, decision) {
            this.review = {
                open: true,
                item: item,
                decision: decision,
                status: item.target_status,
                supervisor_id: item.supervisor_id ? String(item.supervisor_id) : '',
                note: '',
                errors: {},
                saving: false,
            };
        },

        closeReview() {
            this.review.open = false;
        },

        async submitReview() {
            this.review.saving = true;
            this.review.errors = {};

            const res = await Sobuj_reviewResearch(apiUrl, {
                id: this.review.item.id,
                decision: this.review.decision,
                status: this.review.status,
                supervisor_id: this.review.supervisor_id,
                note: this.review.note,
            });
            this.review.saving = false;

            if (res.ok) {
                this.closeReview();
                this.notify(res.data.message);
                this.load();
                return;
            }

            this.review.errors = res.data.errors || {};
            if (Object.keys(this.review.errors).length === 0) {
                this.closeReview();
                this.notify(res.data.message || 'Could not save the decision.', 'error');
                this.load();
            }
        },

        // ----- quick actions -----

        async complete(item) {
            if (!confirm('Mark "' + item.title + '" as completed?')) {
                return;
            }

            const res = await Sobuj_completeResearch(apiUrl, item.id);
            this.notify(res.data.message || 'Could not mark it as completed.', res.ok ? 'success' : 'error');
            this.load();
        },

        async remove(item) {
            if (!confirm('Delete "' + item.title + '"?')) {
                return;
            }

            const res = await Sobuj_deleteResearch(apiUrl, item.id);
            this.notify(res.data.message || 'Could not delete.', res.ok ? 'success' : 'error');
            this.load();
        },
    };
}


// ---------------------------------------------------------------------
// The library (read only). Works the same for admin, teacher and student.
// ---------------------------------------------------------------------
function Sobuj_researchLibrary(apiUrl, optionsUrl) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        categoryId: '',
        type: '',
        year: '',
        includeOngoing: false,

        counts: { completed: 0, ongoing: 0 },
        categories: [],      // [{id, name, total}] with how many research each has
        years: [],
        types: ['Thesis', 'Research Paper', 'Project'],
        opened: {},          // which rows show their abstract

        init() {
            this.load();
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchResearch(apiUrl, {
                q: this.search, category_id: this.categoryId, type: this.type, year: this.year,
                include_ongoing: this.includeOngoing ? '1' : '', page: this.page,
            });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the library.', 'error');
                return;
            }

            this.takePaging(res.data, 'research');
            this.counts = res.data.counts;
            this.categories = res.data.categories;
            this.years = res.data.years;
        },

        // A click on a category button filters by it (a second click clears the filter)
        pickCategory(id) {
            this.categoryId = String(this.categoryId) === String(id) ? '' : String(id);
            this.filtersChanged();
        },

        toggle(id) {
            this.opened[id] = !this.opened[id];
        },
    };
}


// ---------------------------------------------------------------------
// My research (student or teacher): their own requests and submissions
// ---------------------------------------------------------------------
function Sobuj_myResearch(apiUrl, role) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        status: '',
        canAdd: true,
        opened: {},

        init() {
            const message = Sobuj_takeMessage();
            if (message) {
                this.notify(message);
            }
            this.load();
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchResearch(apiUrl, { q: this.search, status: this.status, page: this.page });

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load your research.', 'error');
                return;
            }

            this.takePaging(res.data, 'research');
            this.canAdd = res.data.can_add;
        },

        // Only research that was not approved yet can still be edited or withdrawn
        isOpen(item) {
            return ['requested', 'pending', 'rejected'].includes(item.status);
        },

        toggle(id) {
            this.opened[id] = !this.opened[id];
        },

        async withdraw(item) {
            if (!confirm('Withdraw "' + item.title + '"?')) {
                return;
            }

            const res = await Sobuj_deleteResearch(apiUrl, item.id);
            this.notify(res.data.message || 'Could not withdraw it.', res.ok ? 'success' : 'error');
            this.load();
        },
    };
}


// ---------------------------------------------------------------------
// Admin add / edit page
// ---------------------------------------------------------------------
function Sobuj_researchAdminForm(apiUrl, optionsUrl, lookupUrl, id, listUrl) {
    return {
        ...Sobuj_toastMixin(),

        form: {
            title: '', abstract: '', type: 'Thesis', category_id: '', year: String(new Date().getFullYear()),
            status: 'completed', supervisor_id: '', tags: [],
        },
        students: [],            // the chosen students [{id, name, student_id}]
        savedStatus: '',         // the status the research has now (when editing)
        code: '',
        errors: {},
        saving: false,
        loading: true,

        options: { categories: [], tags: [], types: [], teachers: [] },

        studentQuery: '',
        studentResults: [],
        searchTimer: null,

        async init() {
            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.options = options.data;
            }

            if (id) {
                await this.loadResearch();
            }
            this.loading = false;
        },

        get isEdit() {
            return id > 0;
        },

        // A research that waits for review changes its status on the Pending page, not here
        get statusLocked() {
            return this.isEdit && !['ongoing', 'completed'].includes(this.savedStatus);
        },

        async loadResearch() {
            const res = await Sobuj_fetchOneResearch(apiUrl, id);

            if (!res.ok) {
                Sobuj_rememberMessage(res.data.message || 'This research no longer exists.');
                window.location.href = listUrl;
                return;
            }

            const item = res.data.research;
            this.form.title = item.title;
            this.form.abstract = item.abstract;
            this.form.type = item.type;
            this.form.category_id = String(item.category_id);
            this.form.year = String(item.year);
            this.form.status = ['ongoing', 'completed'].includes(item.status) ? item.status : item.target_status;
            this.form.supervisor_id = item.supervisor_id ? String(item.supervisor_id) : '';
            this.form.tags = item.tags.map(function (tag) { return String(tag.id); });
            this.students = item.students;
            this.savedStatus = item.status;
            this.code = item.code;
        },

        // ----- the student picker -----

        studentQueryChanged() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(async () => {
                if (this.studentQuery.trim() === '') {
                    this.studentResults = [];
                    return;
                }
                const res = await Sobuj_api(lookupUrl + '?' + new URLSearchParams({ q: this.studentQuery }));
                this.studentResults = res.ok
                    ? res.data.students.filter((s) => !this.students.some((chosen) => chosen.id === s.id))
                    : [];
            }, 300);
        },

        addStudent(student) {
            this.students.push(student);
            this.studentResults = [];
            this.studentQuery = '';
        },

        removeStudent(student) {
            this.students = this.students.filter((chosen) => chosen.id !== student.id);
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const body = {
                ...this.form,
                tags: this.form.tags.map(Number),
                students: this.students.map((student) => student.id),
            };
            if (this.statusLocked) {
                delete body.status;
            }
            if (id) {
                body.id = id;
            }

            const res = await Sobuj_saveResearch(apiUrl, body);
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


// ---------------------------------------------------------------------
// Student request / teacher add page
// ---------------------------------------------------------------------
function Sobuj_researchOwnerForm(apiUrl, optionsUrl, role, id, listUrl) {
    return {
        ...Sobuj_toastMixin(),

        form: {
            title: '', abstract: '', type: 'Thesis', category_id: '', tags: [],
            year: String(new Date().getFullYear()), target_status: 'ongoing',
        },
        savedStatus: '',
        reviewNote: '',
        errors: {},
        saving: false,
        loading: true,

        options: { categories: [], tags: [], types: [] },

        async init() {
            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.options = options.data;
            }

            if (id) {
                await this.loadResearch();
            }
            this.loading = false;
        },

        get isEdit() {
            return id > 0;
        },

        async loadResearch() {
            const res = await Sobuj_fetchOneResearch(apiUrl, id);

            if (!res.ok) {
                Sobuj_rememberMessage('This research cannot be edited.');
                window.location.href = listUrl;
                return;
            }

            const item = res.data.research;
            this.form.title = item.title;
            this.form.abstract = item.abstract;
            this.form.type = item.type;
            this.form.category_id = String(item.category_id);
            this.form.year = String(item.year);
            this.form.target_status = item.target_status;
            this.form.tags = item.tags.map(function (tag) { return String(tag.id); });
            this.savedStatus = item.status;
            this.reviewNote = item.review_note || '';
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const body = { ...this.form, tags: this.form.tags.map(Number) };
            if (role === 'student') {
                delete body.year;
                delete body.target_status;
            }
            if (id) {
                body.id = id;
            }

            const res = await Sobuj_saveResearch(apiUrl, body);
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
