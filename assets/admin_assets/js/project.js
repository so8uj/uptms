/*
 * The project pages (the work on an ongoing research), shared by the teacher and the student panels.
 *
 *   database -> Milestone / ResearchComment / Evaluation models -> ProjectController -> api/project.php (JSON)
 *       -> Sobuj_api()                      js/api.js
 *       -> the components below
 *
 *   Sobuj_projectList        the list of my projects with the progress of each (and the numbers for a dashboard)
 *   Sobuj_projectWorkspace   one project: milestones with tasks, comments and evaluations
 */

// "2026-12-01" -> "01 Dec 2026"
function Sobuj_formatDate(value) {
    if (!value) {
        return '';
    }
    const date = new Date(value.substring(0, 10) + 'T00:00:00');
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

// "1 new milestone, 2 new comments" - the news of one research (item.counts = { action: number })
function Sobuj_alertLines(item, role) {
    const c = item.counts;
    const plural = function (n, one, many) { return n + ' ' + (n === 1 ? one : many); };
    const lines = [];

    if (c.approved) { lines.push(role === 'teacher' ? 'Your research was approved' : 'Your topic was approved. Add your team members'); }
    if (c.correction) { lines.push(role === 'teacher' ? 'Your research needs a correction. Read the note and send it again' : 'Your topic needs a correction. Read the note and send it again'); }
    if (c.rejected) { lines.push(role === 'teacher' ? 'Your research was rejected for good. Read the reason' : 'Your topic was rejected for good. Read the reason'); }
    if (c.supervisor_assigned) { lines.push(role === 'teacher' ? 'You were assigned as the supervisor' : 'A supervisor was assigned'); }
    if (c.milestone_added) { lines.push(plural(c.milestone_added, 'new milestone', 'new milestones')); }
    if (c.task_added) { lines.push(plural(c.task_added, 'new task', 'new tasks')); }
    if (c.task_completed) { lines.push(plural(c.task_completed, 'task finished', 'tasks finished')); }
    if (c.commented) { lines.push(plural(c.commented, 'new comment or feedback', 'new comments or feedback')); }
    if (c.evaluated) { lines.push(plural(c.evaluated, 'new evaluation', 'new evaluations')); }
    if (c.completion_requested) { lines.push('A request to finish the research is waiting'); }
    if (c.completion_approved) { lines.push('The supervisor approved finishing. Please confirm'); }
    if (c.completion_confirmed) { lines.push(plural(c.completion_confirmed, 'team member confirmed', 'team members confirmed')); }
    if (c.completion_rejected) { lines.push('The request to finish was rejected'); }
    if (c.completed) { lines.push('The research was completed'); }

    return lines;
}

function Sobuj_ratingText(rating) {
    return { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent' }[rating] || '';
}


// ---------------------------------------------------------------------
// The list of my projects (also used by the dashboards)
// ---------------------------------------------------------------------
function Sobuj_projectList(apiUrl) {
    return {
        projects: [],
        typeFilter: '',      // '' = all, or Project | Thesis | Research Paper (the tabs at the top of the list)
        alerts: [],
        summary: { ongoing: 0, completed: 0, milestones: 0, tasks: 0, done: 0, evaluations: 0 },
        loading: true,
        error: '',

        async init() {
            const res = await Sobuj_api(apiUrl);
            this.loading = false;

            if (!res.ok) {
                this.error = res.data.message || 'Could not load the projects.';
                return;
            }
            this.projects = res.data.projects;
            this.summary = res.data.summary;

            const news = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ view: 'alerts' }));
            if (news.ok) {
                this.alerts = news.data.items;
            }
        },

        // The tasks that are still open in all my projects
        openTasks() {
            return this.summary.tasks - this.summary.done;
        },

        // The ongoing projects that have no evaluation yet
        notEvaluated() {
            return this.projects.filter(function (project) { return project.status === 'ongoing' && project.evaluations === 0; }).length;
        },

        // The projects of the chosen kind
        shown() {
            const type = this.typeFilter;
            return this.projects.filter(function (project) { return type === '' || project.type === type; });
        },

        // The first five projects (for a dashboard)
        recent() {
            return this.projects.slice(0, 5);
        },

        names(project) {
            const names = (project.students || []).map(function (student) { return student.name; });
            return names.length ? names.join(', ') : '-';
        },
    };
}


// ---------------------------------------------------------------------
// One project: milestones, tasks, comments, evaluations
// ---------------------------------------------------------------------
function Sobuj_projectWorkspace(apiUrl, id) {
    return {
        ...Sobuj_toastMixin(),

        data: null,
        loading: true,
        error: '',

        feedbackOpen: {},     // which milestones show their feedback: { milestoneId: true }
        feedbackText: {},     // what is typed in the feedback box of each milestone
        milestoneForm: { open: false, title: '', description: '', due_date: '', errors: {} },
        taskTitles: {},       // what is typed in the "add task" box of each milestone: { milestoneId: 'text' }
        commentBody: '',
        evaluationForm: { rating: '', remarks: '', errors: {} },
        completionNote: '',
        completionErrors: {},

        init() {
            this.load();
        },

        async load() {
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ id: id }));
            this.loading = false;

            if (!res.ok) {
                this.error = res.data.message || 'Could not open this project.';
                return;
            }
            this.data = res.data;
        },

        // The supervisor can delete anything. A student only what they made.
        canDelete(createdBy) {
            return this.data.can_work && (this.data.me.role === 'teacher' || Number(createdBy) === this.data.me.id);
        },

        // Sends one change to the server, shows the answer and reloads the page data.
        async send(body) {
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: body });
            this.notify(res.data.message || 'Could not save.', res.ok ? 'success' : 'error');

            if (res.ok) {
                await this.load();
            }
            return res;
        },

        // ----- milestones -----

        openMilestoneForm() {
            this.milestoneForm = { open: true, title: '', description: '', due_date: '', errors: {} };
        },

        async addMilestone() {
            const form = this.milestoneForm;
            const res = await this.send({ op: 'milestone_add', id: id, title: form.title, description: form.description, due_date: form.due_date });

            if (res.ok) {
                this.milestoneForm = { open: false, title: '', description: '', due_date: '', errors: {} };
            } else {
                form.errors = res.data.errors || {};
            }
        },

        async deleteMilestone(milestone) {
            if (await Sobuj_confirm('Delete the milestone "' + milestone.title + '" and its tasks?', { title: 'Delete milestone', confirmText: 'Delete', danger: true })) {
                this.send({ op: 'milestone_delete', milestone_id: milestone.id });
            }
        },

        // The percent of finished tasks in one milestone
        milestonePercent(milestone) {
            const total = milestone.tasks.length;
            const done = milestone.tasks.filter(function (task) { return task.is_done === 1; }).length;
            return total ? Math.round(done * 100 / total) : 0;
        },

        // ----- feedback on a milestone -----

        feedbackFor(milestone) {
            return this.data.comments.filter(function (comment) { return comment.milestone_id === milestone.id; }).reverse();
        },

        // The comments that are not about one milestone
        generalComments() {
            return this.data.comments.filter(function (comment) { return comment.milestone_id === null; });
        },

        async addFeedback(milestone) {
            const body = (this.feedbackText[milestone.id] || '').trim();
            if (body === '') {
                return;
            }

            const res = await this.send({ op: 'comment_add', id: id, body: body, milestone_id: milestone.id });
            if (res.ok) {
                this.feedbackText[milestone.id] = '';
            }
        },

        // ----- tasks -----

        async addTask(milestone) {
            const title = (this.taskTitles[milestone.id] || '').trim();
            if (title === '') {
                return;
            }

            const res = await this.send({ op: 'task_add', milestone_id: milestone.id, title: title });
            if (res.ok) {
                this.taskTitles[milestone.id] = '';
            }
        },

        toggleTask(task) {
            if (this.data.can_work) {
                this.send({ op: 'task_toggle', task_id: task.id });
            }
        },

        async deleteTask(task) {
            if (await Sobuj_confirm('Delete the task "' + task.title + '"?', { title: 'Delete task', confirmText: 'Delete', danger: true })) {
                this.send({ op: 'task_delete', task_id: task.id });
            }
        },

        // ----- comments -----

        async addComment() {
            if (this.commentBody.trim() === '') {
                return;
            }

            const res = await this.send({ op: 'comment_add', id: id, body: this.commentBody });
            if (res.ok) {
                this.commentBody = '';
            }
        },

        async deleteComment(comment) {
            if (await Sobuj_confirm('Delete this comment?', { title: 'Delete comment', confirmText: 'Delete', danger: true })) {
                this.send({ op: 'comment_delete', comment_id: comment.id });
            }
        },

        // ----- finishing the research -----

        // The words for each step of a request
        completionSteps() {
            const request = this.data.completion.request;
            if (!request) {
                return [];
            }

            const status = request.status;
            const confirmed = request.members.filter(function (member) { return member.confirmed; }).length;

            return [
                { done: true, text: 'Asked by ' + request.requested_by_name },
                { done: status !== 'teacher', text: status === 'teacher' ? 'Waiting for the supervisor to approve' : 'Approved by the supervisor' },
                { done: status === 'admin' || status === 'done', text: 'Team confirmation: ' + confirmed + ' of ' + request.members.length },
                { done: status === 'done', text: status === 'done' ? 'Completed by the administrator' : 'Administrator decision' },
            ];
        },

        isRequestAlive() {
            const request = this.data.completion.request;
            return !!request && ['teacher', 'team', 'admin'].includes(request.status);
        },

        async requestCompletion() {
            if (await Sobuj_confirm('Send a request to your supervisor to finish this research?', { title: 'Finish the research', confirmText: 'Send request' })) {
                this.send({ op: 'completion_request', id: id });
            }
        },

        async cancelCompletion() {
            if (await Sobuj_confirm('Cancel this request to finish the research?', { title: 'Cancel the request', confirmText: 'Cancel request', cancelText: 'Keep it', danger: true })) {
                this.send({ op: 'completion_cancel', request_id: this.data.completion.request.id });
            }
        },

        confirmCompletion() {
            this.send({ op: 'completion_confirm', request_id: this.data.completion.request.id });
        },

        async decideCompletion(decision) {
            this.completionErrors = {};
            const res = await this.send({ op: 'completion_decide', request_id: this.data.completion.request.id, decision: decision, note: this.completionNote });

            if (res.ok) {
                this.completionNote = '';
            } else {
                this.completionErrors = res.data.errors || {};
            }
        },

        // ----- evaluations (the supervisor only) -----

        async addEvaluation() {
            const form = this.evaluationForm;
            const res = await this.send({ op: 'evaluation_add', id: id, rating: form.rating, remarks: form.remarks });

            if (res.ok) {
                this.evaluationForm = { rating: '', remarks: '', errors: {} };
            } else {
                form.errors = res.data.errors || {};
            }
        },
    };
}


// ---------------------------------------------------------------------
// A list across all my projects (the menu pages: milestones, progress updates, feedback, evaluations)
// view = milestones | comments | evaluations | activity
// ---------------------------------------------------------------------
// researchId > 0: the list of that one project (the page of one project inside Progress Updates)
function Sobuj_projectFeed(apiUrl, view, researchId) {
    return {
        items: [],
        research: null,
        single: Number(researchId) > 0,
        loading: true,
        error: '',

        async init() {
            const params = { view: view };
            if (this.single) {
                params.research_id = researchId;
            }

            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams(params));
            this.loading = false;

            if (!res.ok) {
                this.error = res.data.message || 'Could not load the list.';
                return;
            }
            this.items = res.data.items;
            this.research = res.data.research;
        },

        percent(item) {
            return item.tasks ? Math.round(item.done * 100 / item.tasks) : 0;
        },

        // "Mohammad Sobuj (Student)"
        who(item) {
            const role = { admin: 'Admin', teacher: 'Teacher', student: 'Student' }[item.user_role] || '';
            return (item.user_name || 'Administrator') + (role ? ' (' + role + ')' : '');
        },
    };
}


// ---------------------------------------------------------------------
// One project with tabs: its evaluations, progress updates, milestones and feedback.
// The page of "Evaluation" opens it after the list of projects. startTab = evaluations | activity | milestones | comments
// ---------------------------------------------------------------------
function Sobuj_projectInside(apiUrl, researchId, startTab) {
    return {
        single: true,
        tab: startTab || 'evaluations',
        research: null,
        lists: { evaluations: [], activity: [], milestones: [], comments: [] },
        loading: true,
        error: '',

        async init() {
            const names = ['evaluations', 'activity', 'milestones', 'comments'];

            const answers = await Promise.all(names.map(function (view) {
                return Sobuj_api(apiUrl + '?' + new URLSearchParams({ view: view, research_id: researchId }));
            }));
            this.loading = false;

            if (!answers[0].ok) {
                this.error = answers[0].data.message || 'Could not open this project.';
                return;
            }

            answers.forEach((res, i) => {
                if (res.ok) {
                    this.lists[names[i]] = res.data.items;
                    this.research = res.data.research;
                }
            });
        },

        percent(item) {
            return item.tasks ? Math.round(item.done * 100 / item.tasks) : 0;
        },

        // "Mohammad Sobuj (Student)"
        who(item) {
            const role = { admin: 'Admin', teacher: 'Teacher', student: 'Student' }[item.user_role] || '';
            return (item.user_name || 'Administrator') + (role ? ' (' + role + ')' : '');
        },
    };
}
