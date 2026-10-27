/*
 * The "Import from CSV" box (includes/import/panel.php).
 *
 *   api/import.php (reference / sample / upload) -> Sobuj_api()  js/api.js -> Sobuj_csvImport
 *
 * Step 1: the user picks the department (and the session or the batch). The values come from the database.
 * Step 2: the sample file is downloaded with those values already in its rows.
 * Step 3: the file is uploaded.
 */
function Sobuj_csvImport(apiUrl, type) {
    return {
        open: false,
        uploading: false,
        result: null,

        ref: null,
        loadingRef: false,
        showInfo: false,
        picks: { department: '', session: '', batch: '' },

        toggle() {
            this.open = !this.open;

            if (this.open && !this.ref) {
                this.loadReference();
            }
        },

        async loadReference() {
            this.loadingRef = true;
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ type: type, reference: 1 }));
            this.loadingRef = false;

            if (!res.ok) {
                return;
            }
            this.ref = res.data;

            // one department only: it is picked at once
            if (this.ref.departments.length === 1) {
                this.picks.department = this.ref.departments[0].code;
            }
        },

        // the batches of the picked department (for the student file)
        get batchChoices() {
            if (!this.ref) {
                return [];
            }
            const department = this.picks.department;
            return this.ref.batches.filter(function (batch) { return !department || batch.department === department; });
        },

        departmentChanged() {
            this.picks.batch = '';
        },

        get noSession() {
            return type === 'batches' && this.ref !== null && this.ref.sessions.length === 0;
        },

        get noBatch() {
            return type === 'students' && this.ref !== null && this.picks.department !== '' && this.batchChoices.length === 0;
        },

        // the sample can be downloaded when everything this file needs was picked
        get canDownload() {
            if (!this.ref || this.picks.department === '') {
                return false;
            }
            if (type === 'batches') {
                return this.picks.session !== '';
            }
            if (type === 'students') {
                return this.picks.batch !== '';
            }
            return true;
        },

        get sampleUrl() {
            return apiUrl + '?' + new URLSearchParams({
                type: type, sample: 1, department: this.picks.department, session: this.picks.session, batch: this.picks.batch,
            });
        },

        async upload() {
            const file = this.$refs.file.files[0];

            if (!file) {
                this.result = { success: false, message: 'Please choose a CSV file first.', created: 0, failed: 0, errors: [] };
                return;
            }

            const data = new FormData();
            data.append('type', type);
            data.append('file', file);

            this.uploading = true;
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: data });
            this.uploading = false;

            this.result = res.data.errors !== undefined
                ? res.data
                : { success: false, message: res.data.message || 'Could not upload the file.', created: 0, failed: 0, errors: [] };

            if (res.ok) {
                this.$refs.file.value = '';
            }
        },
    };
}
