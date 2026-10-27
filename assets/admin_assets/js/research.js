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
 *   Sobuj_researchView        one research on its own page (read only)
 *   Sobuj_researchAdminView   admin: one research with its whole history
 *   Sobuj_completionRequests  admin: the requests to finish a research
 *   Sobuj_studentDashboard    student dashboard: the numbers + a search box with suggestions
 *   Sobuj_myResearch          student / teacher: their own research and requests
 *   Sobuj_researchTeam        student: the team of an approved research
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
        requested: 'Requested', pending: 'Pending', approved: 'Approved', ongoing: 'Ongoing', completed: 'Completed', rejected: 'Rejected', correction: 'Needs correction',
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


// The category and tag filters of the search boxes (a research needs at least one of the chosen tags)
function Sobuj_searchFilters(optionsUrl) {
    return {
        categoryId: '',
        tagIds: [],
        categoryOptions: [],
        tagOptions: [],
        tagMenuOpen: false,
        tagSearch: '',

        async loadFilterOptions() {
            if (!optionsUrl) {
                return;
            }
            const res = await Sobuj_api(optionsUrl);

            if (res.ok) {
                this.categoryOptions = res.data.categories;
                this.tagOptions = res.data.tags;
            }
        },

        filterParams() {
            return { category_id: this.categoryId, tag_ids: this.tagIds.join(',') };
        },

        hasFilter() {
            return this.categoryId !== '' || this.tagIds.length > 0;
        },

        hasTag(id) {
            return this.tagIds.includes(Number(id));
        },

        toggleTag(id) {
            id = Number(id);
            this.tagIds = this.hasTag(id) ? this.tagIds.filter(function (tagId) { return tagId !== id; }) : this.tagIds.concat(id);
            this.refresh();
        },

        // "All tags": no tag chosen
        clearTags() {
            this.tagIds = [];
            this.refresh();
        },

        // The tags that match what was typed in the tag menu
        visibleTags() {
            const text = this.tagSearch.trim().toLowerCase();
            return this.tagOptions.filter(function (tag) { return tag.name.toLowerCase().includes(text); });
        },

        // The text on the tag button: "All tags", the names (up to 2) or "3 tags"
        tagLabel() {
            const chosen = this.tagOptions.filter((tag) => this.hasTag(tag.id)).map(function (tag) { return tag.name; });

            if (chosen.length === 0) {
                return 'All tags';
            }
            return chosen.length <= 2 ? chosen.join(', ') : chosen.length + ' tags';
        },
    };
}


// =====================================================================
// PART 2 - Components
// =====================================================================

// ---------------------------------------------------------------------
// Admin lists. mode: 'all' | 'review' (requested + pending) | 'ongoing' (approved + ongoing)
// ---------------------------------------------------------------------
function Sobuj_researchAdminList(apiUrl, optionsUrl, mode) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        status: mode === 'review' ? 'review' : (mode === 'ongoing' ? 'progress' : ''),
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

            // the library links to this page with ?q=RS-2026-0007, so the research is found at once
            this.search = new URLSearchParams(window.location.search).get('q') || '';

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

        // An approved topic only needs its supervisor (decision "assign")
        openReview(item, decision) {
            this.review = {
                open: true,
                item: item,
                decision: item.status === 'approved' ? 'assign' : decision,
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
            if (!await Sobuj_confirm('Mark "' + item.title + '" as completed?', { title: 'Complete research', confirmText: 'Mark completed' })) {
                return;
            }

            const res = await Sobuj_completeResearch(apiUrl, item.id);
            this.notify(res.data.message || 'Could not mark it as completed.', res.ok ? 'success' : 'error');
            this.load();
        },

        async remove(item) {
            if (!await Sobuj_confirm('Delete "' + item.title + '"?', { title: 'Delete research', confirmText: 'Delete', danger: true })) {
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
        ...Sobuj_searchFilters(optionsUrl),

        search: '',
        type: '',
        year: '',
        includeOngoing: false,

        counts: { completed: 0, ongoing: 0 },
        categories: [],      // [{id, name, total}] with how many research each has
        years: [],
        types: ['Thesis', 'Research Paper', 'Project'],
        opened: {},          // which rows show their abstract

        init() {
            const fromAddress = new URLSearchParams(location.search);
            this.search = fromAddress.get('q') || '';
            this.categoryId = fromAddress.get('category_id') || '';
            this.tagIds = (fromAddress.get('tag_ids') || '').split(',').filter(Boolean).map(Number);
            this.loadFilterOptions();
            this.load();
        },

        refresh() {
            this.filtersChanged();
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_fetchResearch(apiUrl, {
                q: this.search, ...this.filterParams(), type: this.type, year: this.year,
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

        toggle(id) {
            this.opened[id] = !this.opened[id];
        },
    };
}


// ---------------------------------------------------------------------
// The admin's page for one research: all its details and its whole history
// ---------------------------------------------------------------------
function Sobuj_activityText(entry) {
    const detail = entry.detail || '';

    return {
        requested: 'requested this topic',
        submitted: 'submitted this research',
        added: 'added this research' + (detail ? ' (' + detail.toLowerCase() + ')' : ''),
        updated: 'updated the details',
        resubmitted: 'improved the topic and sent it again',
        approved: 'approved the topic',
        rejected: 'rejected the topic for good' + (detail ? ': ' + detail : ''),
        correction: 'sent the topic back for correction' + (detail ? ': ' + detail : ''),
        supervisor_assigned: 'assigned the supervisor ' + detail,
        member_added: 'added ' + detail + ' to the team',
        member_removed: 'removed ' + detail + ' from the team',
        status_changed: 'changed the status',
        milestone_added: 'added the milestone "' + detail + '"',
        milestone_removed: 'deleted the milestone "' + detail + '"',
        task_added: 'added the task "' + detail + '"',
        task_completed: 'completed the task "' + detail + '"',
        task_reopened: 'opened the task "' + detail + '" again',
        task_removed: 'deleted the task "' + detail + '"',
        commented: 'wrote a comment',
        completion_requested: 'asked to finish the research',
        completion_approved: 'approved the request to finish',
        completion_confirmed: 'confirmed that the work is finished',
        completion_rejected: 'rejected the request to finish',
        completion_cancelled: 'cancelled the request to finish',
        evaluated: 'evaluated the progress',
        completed: 'marked the research as completed',
        withdrawn: 'withdrew the request',
        deleted: 'deleted the research',
    }[entry.action] || entry.action;
}

// The note under a line (only for the actions where the text is a note, not a name)
function Sobuj_activityNote(entry) {
    const noteActions = ['approved', 'rejected', 'status_changed', 'commented', 'evaluated', 'completion_approved', 'completion_rejected'];
    return noteActions.includes(entry.action) ? (entry.detail || '') : '';
}

function Sobuj_activityIcon(action) {
    return {
        requested: 'bi-send', submitted: 'bi-send', added: 'bi-plus-circle', updated: 'bi-pencil', resubmitted: 'bi-arrow-repeat',
        approved: 'bi-check-circle', rejected: 'bi-x-circle', correction: 'bi-pencil-square', supervisor_assigned: 'bi-person-check',
        member_added: 'bi-person-plus', member_removed: 'bi-person-dash', status_changed: 'bi-arrow-left-right',
        milestone_added: 'bi-flag', milestone_removed: 'bi-flag', task_added: 'bi-list-check', task_completed: 'bi-check2-square',
        task_reopened: 'bi-arrow-counterclockwise', task_removed: 'bi-list-check', commented: 'bi-chat-left-text', evaluated: 'bi-clipboard-check',
        completion_requested: 'bi-flag-fill', completion_approved: 'bi-check-circle', completion_confirmed: 'bi-check2-all',
        completion_rejected: 'bi-x-circle', completion_cancelled: 'bi-slash-circle',
        completed: 'bi-trophy', withdrawn: 'bi-box-arrow-left', deleted: 'bi-trash',
    }[action] || 'bi-circle';
}

function Sobuj_researchAdminView(apiUrl, id) {
    return {
        item: null,
        activity: [],
        loading: true,
        error: '',

        async init() {
            const [one, history] = await Promise.all([
                Sobuj_api(apiUrl + '?' + new URLSearchParams({ id: id })),
                Sobuj_api(apiUrl + '?' + new URLSearchParams({ activity: id })),
            ]);
            this.loading = false;

            if (!one.ok) {
                this.error = one.data.message || 'Could not open this research.';
                return;
            }
            this.item = one.data.research;
            this.activity = history.ok ? history.data.activity : [];
        },

        // "Mohammad Sobuj (Admin)": the role helps when two people have the same name
        actor(entry) {
            if (!entry.user_name) {
                return 'Administrator';
            }
            const role = { admin: 'Admin', teacher: 'Teacher', student: 'Student' }[entry.user_role] || '';
            return entry.user_name + (role ? ' (' + role + ')' : '');
        },
    };
}


// ---------------------------------------------------------------------
// The admin's page of the requests to finish a research
// ---------------------------------------------------------------------
function Sobuj_completionRequests(apiUrl) {
    return {
        ...Sobuj_toastMixin(),

        requests: [],
        loading: true,
        rejecting: null,      // the id of the request whose reject box is open
        note: '',
        errors: {},

        init() {
            this.load();
        },

        async load() {
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ completion: 1 }));
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the requests.', 'error');
                return;
            }
            this.requests = res.data.requests;
        },

        async decide(request, decision) {
            if (decision === 'approve' && !await Sobuj_confirm('Complete "' + request.title + '"? It will join the library as completed.', { title: 'Complete research', confirmText: 'Complete' })) {
                return;
            }

            this.errors = {};
            const res = await Sobuj_api(apiUrl, {
                method: 'POST',
                body: { op: 'completion', request_id: request.id, decision: decision, note: this.note },
            });

            if (res.ok) {
                this.notify(res.data.message);
                this.rejecting = null;
                this.note = '';
                this.load();
                return;
            }

            this.errors = res.data.errors || {};
            if (Object.keys(this.errors).length === 0) {
                this.notify(res.data.message || 'Could not save the decision.', 'error');
                this.load();
            }
        },

        openReject(request) {
            this.rejecting = request.id;
            this.note = '';
            this.errors = {};
        },
    };
}


// ---------------------------------------------------------------------
// One research on its own page (read only)
// ---------------------------------------------------------------------
// The library only has completed and ongoing research. ownUrl (api/my_research.php) opens the research of the
// person who is logged in in any status (requested, correction, rejected ...), so a notification can always open it.
function Sobuj_researchView(apiUrl, id, ownUrl) {
    return {
        item: null,
        own: false,
        loading: true,
        error: '',

        async init() {
            let res = await Sobuj_fetchOneResearch(apiUrl, id);

            if (!res.ok && res.status === 404 && ownUrl) {
                res = await Sobuj_api(ownUrl + '?' + new URLSearchParams({ view: id }));
                this.own = res.ok;
            }
            this.loading = false;

            if (!res.ok) {
                this.error = res.data.message || 'Could not open this research.';
                return;
            }
            this.item = res.data.research;
        },

        // the owner can still edit it
        get canEdit() {
            return this.own && this.item.can_edit === true;
        },
    };
}


// ---------------------------------------------------------------------
// Student dashboard: the numbers, and one search box that suggests research while typing
// ---------------------------------------------------------------------
function Sobuj_studentDashboard(libraryUrl, myUrl, viewUrl, libraryPage, optionsUrl, projectUrl) {
    return {
        ...Sobuj_searchFilters(optionsUrl),

        counts: { completed: 0, ongoing: 0 },
        mineTotal: 0,
        projectTotal: 0,
        alerts: [],

        search: '',
        suggestions: [],
        suggestionTotal: 0,
        showSuggestions: false,
        searching: false,
        active: -1,
        requestId: 0,

        async init() {
            this.loadFilterOptions();

            const library = await Sobuj_fetchResearch(libraryUrl, { include_ongoing: '1' });
            if (library.ok) {
                this.counts = library.data.counts;
            }

            const mine = await Sobuj_fetchResearch(myUrl, { page: 1 });
            if (mine.ok) {
                this.mineTotal = mine.data.total;
            }

            const projects = await Sobuj_api(projectUrl);
            if (projects.ok) {
                this.projectTotal = projects.data.summary.ongoing;
            }

            const news = await Sobuj_api(projectUrl + '?' + new URLSearchParams({ view: 'alerts' }));
            if (news.ok) {
                this.alerts = news.data.items;
            }
        },

        async suggest() {
            const text = this.search.trim();
            this.active = -1;

            if (text.length < 2 && !this.hasFilter()) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }

            const myRequest = ++this.requestId;
            this.searching = true;

            const res = await Sobuj_fetchResearch(libraryUrl, { q: text, ...this.filterParams(), include_ongoing: '1' });

            if (myRequest !== this.requestId) {
                return;
            }
            this.searching = false;

            if (!res.ok) {
                return;
            }
            this.suggestions = res.data.research.slice(0, 6);
            this.suggestionTotal = res.data.total;
            this.showSuggestions = true;
        },

        refresh() {
            this.suggest();
        },

        // A short piece of the abstract, as the overview
        overview(item) {
            const text = item.abstract || '';
            return text.length > 140 ? text.slice(0, 140) + '...' : text;
        },

        move(step) {
            if (this.suggestions.length === 0) {
                return;
            }
            this.active = (this.active + step + this.suggestions.length) % this.suggestions.length;
        },

        // Enter: open the highlighted suggestion, or show every result in the library
        submit() {
            if (this.active >= 0 && this.suggestions[this.active]) {
                location.href = viewUrl + this.suggestions[this.active].id;
            } else {
                this.showAll();
            }
        },

        showAll() {
            location.href = libraryPage + '?' + new URLSearchParams({ q: this.search.trim(), ...this.filterParams() });
        },
    };
}


// ---------------------------------------------------------------------
// My research (student or teacher): their own requests and submissions
// ---------------------------------------------------------------------
function Sobuj_myResearch(apiUrl, role, projectUrl) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        status: '',
        canAdd: true,
        counts: {},
        opened: {},

        init() {
            const message = Sobuj_takeMessage();
            if (message) {
                this.notify(message);
            }
            this.load();

            // Looking at My Research reads the news about the topics (approved, rejected ...)
            if (projectUrl) {
                Sobuj_api(projectUrl, { method: 'POST', body: { op: 'seen_topics' } });
            }
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
            this.counts = res.data.counts;
        },

        // Only research that was not approved yet can still be edited
        isOpen(item) {
            return ['requested', 'pending', 'correction'].includes(item.status);
        },

        // It can be withdrawn until a supervisor is assigned
        canWithdraw(item) {
            return this.isOpen(item) || item.status === 'approved';
        },

        toggle(id) {
            this.opened[id] = !this.opened[id];
        },

        async withdraw(item) {
            if (!await Sobuj_confirm('Withdraw "' + item.title + '"?', { title: 'Withdraw the request', confirmText: 'Withdraw', danger: true })) {
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
function Sobuj_researchAdminForm(apiUrl, optionsUrl, teamsUrl, id, listUrl) {
    return {
        ...Sobuj_toastMixin(),

        form: {
            title: '', abstract: '', type: 'Thesis', category_id: '', year: String(new Date().getFullYear()),
            status: 'completed', supervisor_id: '', team_id: '', tags: [],
        },
        students: [],            // the students of the research [{id, name, student_id}]
        teams: [],               // the teams a research can take
        teamName: '',            // the team of the research (when editing)
        savedStatus: '',         // the status the research has now (when editing)
        code: '',
        errors: {},
        saving: false,
        loading: true,

        options: { categories: [], tags: [], types: [], teachers: [] },

        async init() {
            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.options = options.data;
            }

            if (!id) {
                const teams = await Sobuj_api(teamsUrl + '?available=1');
                if (teams.ok) {
                    this.teams = teams.data.teams;
                }
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
            this.teamName = item.team_name || '';
            this.savedStatus = item.status;
            this.code = item.code;
        },

        // The members of the chosen team are shown at once
        get teamMembers() {
            const team = this.teams.find((item) => String(item.id) === this.form.team_id);
            return team ? team.members : [];
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const body = {
                ...this.form,
                tags: this.form.tags.map(Number),
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
function Sobuj_researchOwnerForm(apiUrl, optionsUrl, role, id, listUrl, teamsUrl) {
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
        team: null,          // the open team of the student (a student asks with their team)

        async init() {
            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.options = options.data;
            }

            if (teamsUrl) {
                const teams = await Sobuj_api(teamsUrl);
                if (teams.ok) {
                    this.team = teams.data.teams.find(function (item) { return item.status === 'open'; }) || null;
                }
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
