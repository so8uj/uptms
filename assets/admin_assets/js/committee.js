/*
 * The "Assign supervisor" panel (includes/committee/assign_panel.php).
 * Used by the administrator (admin/research/assign.php, api/research.php) and by the thesis committee
 * (teacher/committee/assign.php, api/committee.php). It needs api.js and research.js.
 *
 *   groups     the approved groups that have no supervisor yet: a supervisor makes them start to work, without any research request
 *   approved   topics that were approved before a supervisor was set: they only need their supervisor
 * The filters (search, department, batch) keep the lists short when there are many batches and departments.
 */
function Sobuj_assignPanel(teamsUrl, assignUrl, optionsUrl, filterOptionsUrl) {
    return {
        ...Sobuj_toastMixin(),

        groups: [],
        approved: [],
        options: { teachers: [] },
        filterOptions: { departments: [], batches: [] },
        search: '',
        departmentId: '',
        batchId: '',
        groupPicks: {},     // team id -> teacher id
        topicPicks: {},     // research id -> teacher id
        loading: true,
        busy: 0,            // the id of the row that is being saved

        async init() {
            const [options, filterOptions] = await Promise.all([Sobuj_api(optionsUrl), Sobuj_api(filterOptionsUrl)]);
            if (options.ok) {
                this.options = options.data;
            }
            if (filterOptions.ok) {
                this.filterOptions = filterOptions.data;
            }

            this.load();
        },

        // the batches of the chosen department (all batches when no department is chosen)
        get batchOptions() {
            const departmentId = this.departmentId;
            return this.filterOptions.batches.filter(function (batch) { return !departmentId || String(batch.department_id) === departmentId; });
        },

        hasFilters() {
            return !!(this.search || this.departmentId || this.batchId);
        },

        departmentChanged() {
            this.batchId = '';
            this.load();
        },

        clearFilters() {
            this.search = '';
            this.departmentId = '';
            this.batchId = '';
            this.load();
        },

        async load() {
            const [teams, topics] = await Promise.all([
                Sobuj_api(teamsUrl + '?' + new URLSearchParams({ available: 1, q: this.search, department_id: this.departmentId, batch_id: this.batchId })),
                Sobuj_api(assignUrl + '?' + new URLSearchParams({ status: 'approved', q: this.search, batch_id: this.batchId })),
            ]);
            this.loading = false;

            if (teams.ok) {
                this.groups = teams.data.teams;
            }
            if (topics.ok) {
                this.approved = topics.data.research;
            }
        },

        members(team) {
            return (team.members || []).map(function (member) { return member.name; }).join(', ');
        },

        async assignGroup(team) {
            const supervisor = this.groupPicks[team.id];
            if (!supervisor) {
                this.notify('Please choose a supervisor for ' + team.name + '.', 'error');
                return;
            }

            if (!await Sobuj_confirm('Make ' + Sobuj_teacherLabel(this.options.teachers, supervisor, 'this teacher') + ' the supervisor of "' + team.name + '"? The group starts to work with the supervisor at once.',
                { title: 'Assign supervisor', confirmText: 'Assign' })) {
                return;
            }

            this.busy = team.id;
            const res = await Sobuj_api(assignUrl, { method: 'POST', body: { op: 'assign_group', team_id: team.id, supervisor_id: supervisor } });
            this.busy = 0;

            this.notify(res.data.message || 'Could not assign the supervisor.', res.ok ? 'success' : 'error');
            if (res.ok) {
                delete this.groupPicks[team.id];
            }
            this.load();
        },

        async assignTopic(item) {
            const supervisor = this.topicPicks[item.id];
            if (!supervisor) {
                this.notify('Please choose a supervisor for ' + item.code + '.', 'error');
                return;
            }

            this.busy = item.id;
            const res = await Sobuj_api(assignUrl, { method: 'POST', body: { op: 'review', id: item.id, decision: 'assign', supervisor_id: supervisor } });
            this.busy = 0;

            this.notify(res.data.message || 'Could not assign the supervisor.', res.ok ? 'success' : 'error');
            if (res.ok) {
                delete this.topicPicks[item.id];
            }
            this.load();
        },
    };
}
