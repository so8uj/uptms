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
            team_min_size: '', team_max_size: '', panel_signatures_needed: '',
            login_max_attempts: '', login_lock_minutes: '',
            maintenance_mode: false, maintenance_message: '',
            mail_enabled: false, mail_host: '', mail_port: '587', mail_encryption: 'tls', mail_username: '',
            mail_password: '', mail_from_email: '', mail_from_name: '',
        },
        mailPasswordSet: false,
        testTo: '',
        testing: false,
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
                if (saved[name] === undefined) {
                    return;   // the SMTP password is never sent back
                }
                this.form[name] = (name === 'maintenance_mode' || name === 'mail_enabled') ? saved[name] === '1' : saved[name];
            });
            this.mailPasswordSet = res.data.mail_password_set;
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
                this.mailPasswordSet = this.mailPasswordSet || this.form.mail_password !== '';
                this.form.mail_password = '';
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save the settings.', 'error');
        },

        // Sends one email with the saved mail settings
        async sendTest() {
            this.testing = true;
            this.errors = {};

            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'test_mail', to: this.testTo } });
            this.testing = false;

            if (res.ok) {
                this.notify(res.data.message);
                return;
            }

            this.errors = res.data.errors || {};
            this.notify(res.data.message || 'The test email could not be sent.', 'error');
        },
    };
}
