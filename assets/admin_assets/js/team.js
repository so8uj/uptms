/*
 * Teams (groups) of students.
 *
 *   api/teams.php (JSON) -> Sobuj_api()  js/api.js
 *   Sobuj_teamPicker       the member picker used by the admin form and the student page
 *   Sobuj_teamList         admin: the list of teams
 *   Sobuj_teamForm         admin: add / edit a team
 *   Sobuj_studentTeam      student: my team (see it, change the members, or make a team)
 * (Sobuj_toastMixin and Sobuj_pagingMixin come from js/research.js)
 */

// Shows the students who can join, and keeps the students that are chosen
function Sobuj_teamPicker() {
    return {
        maxSize: 3,
        candidates: [],
        chosen: [],
        search: '',
        pickerTimer: null,
        pickerRequest: 0,

        // a student can be ticked until the size is right: the save is refused when there are too many or too few
        sizeProblem() {
            if (this.chosen.length > this.maxSize) {
                return 'Too many students. Untick ' + (this.chosen.length - this.maxSize) + ' first: a group/team has at most ' + this.maxSize + '.';
            }
            if (this.chosen.length < this.minSize) {
                return 'Choose at least ' + this.minSize + ' students.';
            }
            return '';
        },

        isChosen(student) {
            return this.chosen.some(function (item) { return item.id === student.id; });
        },

        toggle(student) {
            if (student.busy && !this.isChosen(student)) {
                return;
            }

            if (this.isChosen(student)) {
                this.chosen = this.chosen.filter(function (item) { return item.id !== student.id; });
            } else {
                this.chosen.push({ id: student.id, name: student.name, student_id: student.student_id });
            }
        },

        // the student who is logged in cannot be taken out of the list (only the student page sets this)
        locked(student) {
            return false;
        },

        label(student) {
            return student.name + (student.student_id ? ' (' + student.student_id + ')' : '');
        },

        // params: department_id, batch_id, team_id
        async loadCandidates(apiUrl, params) {
            const myRequest = ++this.pickerRequest;
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ students: 1, q: this.search, ...params }));

            if (myRequest === this.pickerRequest) {
                // the students who can join first (white), the ones who cannot last (grey)
                this.candidates = res.ok
                    ? res.data.students.sort(function (a, b) { return (a.busy ? 1 : 0) - (b.busy ? 1 : 0); })
                    : [];
            }
        },
    };
}


// ---------------------------------------------------------------------
// Admin: the list of teams
// ---------------------------------------------------------------------
function Sobuj_teamList(apiUrl, optionsUrl) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_pagingMixin(),

        search: '',
        status: '',
        departmentId: '',
        batchId: '',
        options: { departments: [], batches: [] },
        pending: [],
        minSize: 2,
        maxSize: 3,

        init() {
            const message = Sobuj_takeMessage();
            if (message) {
                this.notify(message);
            }
            this.load();
            this.loadOptions();
            this.loadPending();
        },

        // what waits for the admin's decision: shown on top of the list
        async loadPending() {
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ pending: 1 }));
            if (res.ok) {
                this.pending = res.data.teams;
            }
        },

        // a new group/team request, or a member change of an approved one
        pendingKind(item) {
            return item.status === 'requested' ? 'New group/team request' : 'Member change request';
        },

        async loadOptions() {
            const res = await Sobuj_api(optionsUrl);
            if (res.ok) {
                this.options = res.data;
            }
        },

        get batchOptions() {
            const departmentId = this.departmentId;
            return this.options.batches.filter(function (batch) { return !departmentId || String(batch.department_id) === departmentId; });
        },

        departmentChanged() {
            this.batchId = '';
            this.filtersChanged();
        },

        async load() {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({
                q: this.search, status: this.status, department_id: this.departmentId, batch_id: this.batchId, page: this.page,
            }));

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the groups/teams.', 'error');
                return;
            }
            this.takePaging(res.data, 'teams');
            this.minSize = res.data.min_size;
            this.maxSize = res.data.max_size;
        },

        members(item) {
            return item.members.map(function (member) { return member.name; }).join(', ');
        },

        statusText(status) {
            return { requested: 'Pending approval', open: 'Approved', rejected: 'Rejected', closed: 'Completed' }[status] || status;
        },

        statusBadge(status) {
            return { requested: 'pending', open: 'success', rejected: 'failed', closed: 'pending' }[status] || 'pending';
        },

        // Approve works at once. Reject and delete open the small box of the page (no browser popup).
        // box.kind: reject_group | reject_change | delete
        box: { open: false, kind: '', item: null, note: '', error: '', saving: false },

        openBox(kind, item) {
            this.box = { open: true, kind: kind, item: item, note: '', error: '', saving: false };
        },

        closeBox() {
            this.box.open = false;
        },

        get boxTitle() {
            return { reject_group: 'Reject the group/team', reject_change: 'Reject the member change', delete: 'Delete the group/team' }[this.box.kind] || '';
        },

        get boxNeedsReason() {
            return this.box.kind === 'reject_group' || this.box.kind === 'reject_change';
        },

        // the button of the box
        async confirmBox() {
            if (this.boxNeedsReason && this.box.note.trim() === '') {
                this.box.error = 'Please write the reason.';
                return;
            }

            this.box.saving = true;
            const item = this.box.item;
            const note = this.box.note.trim();
            let res;

            if (this.box.kind === 'delete') {
                res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'delete', id: item.id } });
            } else if (this.box.kind === 'reject_change') {
                res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'review_change', id: item.change.id, decision: 'reject', note: note } });
            } else {
                res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'review', id: item.id, decision: 'reject', note: note } });
            }
            this.box.saving = false;

            // a mistake of the reason stays in the box, anything else closes it
            if (!res.ok && res.data.errors && res.data.errors.note) {
                this.box.error = res.data.errors.note[0];
                return;
            }

            this.closeBox();
            this.notify(res.data.message || 'Could not save.', res.ok ? 'success' : 'error');
            this.load();
            this.loadPending();
        },

        // the approve buttons: no question, it is done
        async approve(item) {
            const body = item.status === 'requested'
                ? { op: 'review', id: item.id, decision: 'approve' }
                : { op: 'review_change', id: item.change.id, decision: 'approve' };

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: body });
            this.notify(res.data.message || 'Could not save.', res.ok ? 'success' : 'error');
            this.load();
            this.loadPending();
        },

        reject(item) {
            this.openBox(item.status === 'requested' ? 'reject_group' : 'reject_change', item);
        },

        remove(item) {
            this.openBox('delete', item);
        },
    };
}


// ---------------------------------------------------------------------
// Admin: add / edit a team
// ---------------------------------------------------------------------
function Sobuj_teamForm(apiUrl, optionsUrl, id, listUrl, multiDepartment) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_teamPicker(),

        form: { name: '', department_id: '', batch_id: '' },
        options: { departments: [], batches: [] },
        status: 'open',
        hasResearch: false,
        minSize: 2,
        errors: {},
        saving: false,
        loading: true,

        async init() {
            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.options = options.data;
            }

            if (!multiDepartment && this.options.departments.length > 0) {
                this.form.department_id = String(this.options.departments[0].id);
            }

            if (id) {
                await this.loadTeam();
            }
            this.loading = false;
        },

        get isEdit() {
            return id > 0;
        },

        get batchOptions() {
            const departmentId = this.form.department_id;
            return this.options.batches.filter(function (batch) { return String(batch.department_id) === departmentId; });
        },

        get ready() {
            return this.form.department_id !== '' && this.form.batch_id !== '';
        },

        async loadTeam() {
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ id: id }));

            if (!res.ok) {
                Sobuj_rememberMessage(res.data.message || 'This team no longer exists.');
                window.location.href = listUrl;
                return;
            }

            const team = res.data.team;
            this.form.name = team.name;
            this.form.department_id = String(team.department_id);
            this.form.batch_id = String(team.batch_id);
            this.chosen = team.members;
            this.status = team.status;
            this.hasResearch = team.has_research;
            this.minSize = res.data.min_size;
            this.maxSize = res.data.max_size;
            this.refresh();
        },

        departmentChanged() {
            this.form.batch_id = '';
            this.chosen = [];
            this.candidates = [];
        },

        batchChanged() {
            this.chosen = [];
            this.refresh();
        },

        refresh() {
            if (!this.ready) {
                this.candidates = [];
                return;
            }
            this.loadCandidates(apiUrl, { department_id: this.form.department_id, batch_id: this.form.batch_id, team_id: id });
        },

        searchChanged() {
            clearTimeout(this.pickerTimer);
            this.pickerTimer = setTimeout(() => { this.refresh(); }, 300);
        },

        pick(student) {
            this.toggle(student);
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const body = {
                op: 'save',
                name: this.form.name,
                department_id: this.form.department_id,
                batch_id: this.form.batch_id,
                students: this.chosen.map(function (student) { return student.id; }),
            };
            if (id) {
                body.id = id;
            }

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: body });
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
// Student: my team
// ---------------------------------------------------------------------
function Sobuj_studentTeam(apiUrl, meId, researchUrl) {
    return {
        ...Sobuj_toastMixin(),
        ...Sobuj_teamPicker(),

        teams: [],
        minSize: 2,
        loading: true,
        mode: 'view',
        form: { name: '' },
        errors: {},
        saving: false,

        init() {
            this.load();
        },

        // A student has one group for now: the newest one
        get team() {
            return this.teams[0] || null;
        },

        get hasPendingChange() {
            return !!(this.team && this.team.change && this.team.change.status === 'pending');
        },

        // the one member change request was made (waiting, approved or rejected): no more requests
        get changeUsed() {
            return !!(this.team && this.team.change);
        },

        get canRequest() {
            return !this.team || this.team.status === 'rejected';
        },

        statusText(status) {
            return { requested: 'Waiting for approval', open: 'Approved', rejected: 'Rejected', closed: 'Completed' }[status] || status;
        },

        statusBadge(status) {
            return { requested: 'pending', open: 'success', rejected: 'failed', closed: 'pending' }[status] || 'pending';
        },

        projectText() {
            return this.team && this.team.research_status ? Sobuj_statusText(this.team.research_status) : 'No project yet';
        },

        async load() {
            const res = await Sobuj_api(apiUrl);
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load your group/team.', 'error');
                return;
            }
            this.teams = res.data.teams;
            this.minSize = res.data.min_size;
            this.maxSize = res.data.max_size;
            this.mode = 'view';
        },

        openCreate() {
            this.errors = {};
            this.form.name = '';
            this.search = '';
            this.chosen = [];
            this.mode = 'create';
            this.refresh();
        },

        openEdit() {
            this.errors = {};
            this.search = '';
            this.chosen = this.team.members.map(function (member) { return { ...member }; });
            this.mode = 'edit';
            this.refresh();
        },

        cancel() {
            this.mode = 'view';
        },

        async refresh() {
            await this.loadCandidates(apiUrl, { team_id: this.mode === 'edit' && this.team ? this.team.id : 0 });

            if (this.mode === 'create' && !this.isChosen({ id: meId })) {
                const me = this.candidates.find(function (student) { return student.id === meId; });
                if (me) {
                    this.toggle(me);
                }
            }
        },

        searchChanged() {
            clearTimeout(this.pickerTimer);
            this.pickerTimer = setTimeout(() => { this.refresh(); }, 300);
        },

        // I am always a member: I can only swap my teammates
        locked(student) {
            return student.id === meId;
        },

        pick(student) {
            if (this.locked(student)) {
                return;
            }
            this.toggle(student);
        },

        researchLink() {
            return this.team && this.team.research_id ? researchUrl + this.team.research_id : '';
        },

        async save() {
            this.saving = true;
            this.errors = {};

            const body = {
                op: 'save',
                name: this.form.name,
                students: this.chosen.map(function (student) { return student.id; }),
            };
            if (this.mode === 'edit') {
                body.id = this.team.id;
            }

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: body });
            this.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.load();
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save.', 'error');
        },
    };
}
