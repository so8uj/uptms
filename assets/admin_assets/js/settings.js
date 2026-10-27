/*
 * The Settings page (admin).
 *
 *   api/settings.php (JSON) -> Sobuj_api()  js/api.js -> Sobuj_settingsPage
 *
 * One form for all the settings. The save button sends all of them; the server checks them and saves them together.
 */
function Sobuj_settingsPage(apiUrl) {
    return {
        ...Sobuj_toastMixin(),

        form: {
            institution_name: '', department_name: '', support_email: '',
            team_min_size: '', team_max_size: '',
            login_max_attempts: '', login_lock_minutes: '',
            maintenance_mode: false, maintenance_message: '',
        },
        multiDepartment: false,
        errors: {},
        loading: true,
        saving: false,

        init() {
            this.load();
        },

        async load() {
            const res = await Sobuj_api(apiUrl);
            this.loading = false;

            if (!res.ok) {
                this.notify(res.data.message || 'Could not load the settings.', 'error');
                return;
            }

            const saved = res.data.settings;
            Object.keys(this.form).forEach((name) => {
                this.form[name] = name === 'maintenance_mode' ? saved[name] === '1' : saved[name];
            });
            this.multiDepartment = res.data.multi_department;
        },

        // the messages of one field
        err(name) {
            return this.errors[name] || [];
        },

        async save() {
            if (this.form.maintenance_mode) {
                const sure = await Sobuj_confirm('With the maintenance mode on, only administrators can log in. Teachers and students will be logged out. Save anyway?', {
                    title: 'Maintenance mode',
                    confirmText: 'Save',
                });
                if (!sure) {
                    return;
                }
            }

            this.saving = true;
            this.errors = {};

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: this.form });
            this.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save the settings.', 'error');
        },
    };
}
