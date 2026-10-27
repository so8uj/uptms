/*
 * The Reports page (admin).
 *
 *   api/reports.php (JSON, or a file for the export) -> Sobuj_api()  js/api.js -> Sobuj_reportPage
 *
 * One page, five reports (Research, Groups/Teams, Students, Supervisors, Evaluations). The filters change with the report.
 * Export Excel and Export CSV download all rows with the same filters. Print / PDF prints all rows (use "Save as PDF" in the print box).
 */
function Sobuj_reportPage(apiUrl, optionsUrl, researchOptionsUrl) {
    return {
        types: {},
        summary: null,
        years: [],
        batches: [],
        categories: [],

        type: 'research',
        filters: { kind: '', status: '', category_id: '', batch_id: '', year: '', q: '' },

        columns: [],
        rows: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 15,
        loading: true,
        requestId: 0,
        generated: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }),

        // Which filters each report has
        has: {
            research: ['kind', 'status', 'category_id', 'batch_id', 'year', 'q'],
            teams: ['status', 'batch_id', 'q'],
            students: ['batch_id', 'q'],
            supervisors: ['q'],
            evaluations: ['kind', 'batch_id', 'q'],
        },

        // The words of the status filter of each report
        statuses: {
            research: [['waiting', 'Waiting for review'], ['approved', 'Approved'], ['ongoing', 'Ongoing'], ['completed', 'Completed'], ['correction', 'Needs correction'], ['rejected', 'Rejected']],
            teams: [['requested', 'Pending approval'], ['open', 'Approved'], ['rejected', 'Rejected'], ['closed', 'Completed']],
        },

        async init() {
            this.load();

            const overview = await Sobuj_api(apiUrl);
            if (overview.ok) {
                this.summary = overview.data.summary;
                this.types = overview.data.types;
                this.years = overview.data.years;
            }

            const options = await Sobuj_api(optionsUrl);
            if (options.ok) {
                this.batches = options.data.batches;
            }

            const research = await Sobuj_api(researchOptionsUrl);
            if (research.ok) {
                this.categories = research.data.categories;
            }
        },

        shows(filter) {
            return this.has[this.type].includes(filter);
        },

        // the filters in the address of the API
        params(extra) {
            const params = new URLSearchParams({ type: this.type, ...extra });

            this.has[this.type].forEach((name) => {
                if (this.filters[name] !== '') {
                    params.set(name, this.filters[name]);
                }
            });

            return params;
        },

        async load(all) {
            const myRequest = ++this.requestId;
            this.loading = true;

            const res = await Sobuj_api(apiUrl + '?' + this.params(all ? { all: 1 } : { page: this.page }));

            if (myRequest !== this.requestId) {
                return;
            }
            this.loading = false;

            if (!res.ok) {
                await Sobuj_alert(res.data.message || 'Could not load the report.', { title: 'Report' });
                return;
            }

            this.columns = res.data.columns;
            this.rows = res.data.rows;
            this.total = res.data.total;
            this.page = res.data.page;
            this.pages = res.data.pages;
            this.perPage = res.data.perPage;
        },

        setType(type) {
            this.type = type;
            this.filters = { kind: '', status: '', category_id: '', batch_id: '', year: '', q: '' };
            this.page = 1;
            this.load();
        },

        filtersChanged() {
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

        rangeFrom() {
            return this.total === 0 ? 0 : (this.page - 1) * this.perPage + 1;
        },

        rangeTo() {
            return (this.page - 1) * this.perPage + this.rows.length;
        },

        // the columns with long text may use more lines; the others stay on one line
        wide(column) {
            return ['title', 'members', 'research', 'remarks', 'category'].includes(column.key);
        },

        // a value for a cell
        cell(row, column) {
            const value = row[column.key];
            return value === null || value === undefined || value === '' ? '-' : value;
        },

        // Excel or CSV: the browser downloads the file (the session cookie goes with the request)
        exportFile(format) {
            window.location.href = apiUrl + '?' + this.params({ export: format });
        },

        // Print / PDF: all rows of the report are shown, then the print box opens
        async printReport() {
            await this.load(true);
            await this.$nextTick();
            window.print();
            this.load();
        },

        get reportName() {
            return this.types[this.type] || '';
        },
    };
}
