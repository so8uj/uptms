/*
 * Profile page (admin, teacher, student).
 *
 *   api/account.php (JSON) -> Sobuj_api()  js/api.js -> Sobuj_accountPage
 *   (Sobuj_initials and Sobuj_photoUrl come from js/users.js)
 *
 * view: 'show' (the details), 'edit' (name and phone) or 'password'
 */
function Sobuj_accountPage(apiUrl) {
    return {
        ...Sobuj_toastMixin(),

        account: null,
        loading: true,
        photoSaving: false,
        error: '',
        view: 'show',

        form: { name: '', phone: '', errors: {}, saving: false },
        password: { old_password: '', new_password: '', new_password_again: '', errors: {}, saving: false },

        init() {
            this.load();
        },

        async load() {
            const res = await Sobuj_api(apiUrl);
            this.loading = false;

            if (!res.ok) {
                this.error = res.data.message || 'Could not load your profile.';
                return;
            }
            this.account = res.data.account;
        },

        get avatar() {
            return this.account && this.account.photo ? Sobuj_photoUrl(this.account.photo) : '';
        },

        openEdit() {
            this.form = { name: this.account.name, phone: this.account.phone || '', errors: {}, saving: false };
            this.view = 'edit';
        },

        openPassword() {
            this.password = { old_password: '', new_password: '', new_password_again: '', errors: {}, saving: false };
            this.view = 'password';
        },

        async save() {
            this.form.saving = true;
            this.form.errors = {};

            const res = await Sobuj_api(apiUrl, {
                method: 'POST',
                body: { op: 'update', name: this.form.name, phone: this.form.phone },
            });
            this.form.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.view = 'show';
                this.load();
                return;
            }

            this.form.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not save.', 'error');
        },

        // The pencil on the picture: the chosen file is sent at once, then the page reloads so the top bar shows it too.
        async changePhoto(event) {
            const file = event.target.files[0];
            event.target.value = '';   // the same file can be chosen again later
            if (!file) {
                return;
            }

            const body = new FormData();
            body.append('op', 'photo');
            body.append('photo', file);

            this.photoSaving = true;
            const res = await Sobuj_api(apiUrl, { method: 'POST', body });
            this.photoSaving = false;

            if (res.ok) {
                this.notify(res.data.message);
                setTimeout(() => window.location.reload(), 700);
                return;
            }

            this.notify((res.data.errors && res.data.errors.photo ? res.data.errors.photo[0] : res.data.message) || 'Could not change the picture.', 'error');
        },

        async changePassword() {
            this.password.saving = true;
            this.password.errors = {};

            const res = await Sobuj_api(apiUrl, {
                method: 'POST',
                body: {
                    op: 'password',
                    old_password: this.password.old_password,
                    new_password: this.password.new_password,
                    new_password_again: this.password.new_password_again,
                },
            });
            this.password.saving = false;

            if (res.ok) {
                this.notify(res.data.message);
                this.view = 'show';
                return;
            }

            this.password.errors = res.data.errors || {};
            this.notify(res.data.message || 'Could not change the password.', 'error');
        },
    };
}
