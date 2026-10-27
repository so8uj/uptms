/*
 * Notifications: the bell on the top bar and the Notifications page.
 *
 *   api/notifications.php (JSON) -> Sobuj_api()  js/api.js
 *   Sobuj_notificationBell   the bell: the number of unread ones, and the newest in a small list
 *   Sobuj_notificationPage   the page with all of them: All / Unread, read / unread buttons, page numbers
 */

// "5 min ago", "2 days ago" (seconds_ago is counted by the server)
function Sobuj_timeAgo(seconds) {
    if (seconds < 60) {
        return 'just now';
    }
    if (seconds < 3600) {
        return Math.floor(seconds / 60) + ' min ago';
    }
    if (seconds < 86400) {
        return Math.floor(seconds / 3600) + ' hour' + (seconds >= 7200 ? 's' : '') + ' ago';
    }
    const days = Math.floor(seconds / 86400);
    return days + ' day' + (days > 1 ? 's' : '') + ' ago';
}

// Tells the bell and the page that something changed, so both show the same numbers
function Sobuj_notificationsChanged() {
    window.dispatchEvent(new CustomEvent('notifications-changed'));
}

// The icon of a kind of notification
function Sobuj_notificationIcon(type) {
    return { research: 'bi-journal-text', team: 'bi-people' }[type] || 'bi-bell';
}


// ---------------------------------------------------------------------
// The bell
// ---------------------------------------------------------------------
function Sobuj_notificationBell(apiUrl, baseUrl) {
    return {
        unread: 0,
        items: [],
        filter: 'all',
        loading: false,
        timer: null,

        init() {
            this.loadCount();
            // the number is asked again every minute
            this.timer = setInterval(() => { this.loadCount(); }, 60000);
        },

        async loadCount() {
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ count: 1 }));
            if (res.ok) {
                this.unread = res.data.unread;
            }
        },

        // called when the bell is opened
        async loadList() {
            this.loading = true;
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ filter: this.filter, per_page: 8 }));
            this.loading = false;

            if (res.ok) {
                this.items = res.data.items;
                this.unread = res.data.unread;
            }
        },

        setFilter(filter) {
            this.filter = filter;
            this.loadList();
        },

        ago(item) {
            return Sobuj_timeAgo(item.seconds_ago);
        },

        icon(item) {
            return Sobuj_notificationIcon(item.type);
        },

        // a click on a notification: it is read, and its page opens
        async open(item) {
            await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'read', id: item.id } });

            if (item.link) {
                window.location.href = baseUrl + '/' + item.link;
                return;
            }
            this.loadList();
            Sobuj_notificationsChanged();
        },

        async toggleRead(item) {
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: item.is_read ? 'unread' : 'read', id: item.id } });
            if (res.ok) {
                this.loadList();
                Sobuj_notificationsChanged();
            }
        },

        async readAll() {
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'read_all' } });
            if (res.ok) {
                this.loadList();
                Sobuj_notificationsChanged();
            }
        },
    };
}


// ---------------------------------------------------------------------
// The page of all notifications
// ---------------------------------------------------------------------
function Sobuj_notificationPage(apiUrl, baseUrl) {
    return {
        items: [],
        total: 0,
        page: 1,
        pages: 1,
        perPage: 10,
        unread: 0,
        filter: 'all',
        loading: true,

        init() {
            this.load();
        },

        async load() {
            this.loading = true;
            const res = await Sobuj_api(apiUrl + '?' + new URLSearchParams({ filter: this.filter, page: this.page }));
            this.loading = false;

            if (!res.ok) {
                return;
            }
            this.items = res.data.items;
            this.total = res.data.total;
            this.page = res.data.page;
            this.pages = res.data.pages;
            this.perPage = res.data.perPage;
            this.unread = res.data.unread;
        },

        setFilter(filter) {
            this.filter = filter;
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

        ago(item) {
            return Sobuj_timeAgo(item.seconds_ago);
        },

        icon(item) {
            return Sobuj_notificationIcon(item.type);
        },

        async open(item) {
            await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'read', id: item.id } });

            if (item.link) {
                window.location.href = baseUrl + '/' + item.link;
                return;
            }
            Sobuj_notificationsChanged();
        },

        async toggleRead(item) {
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: item.is_read ? 'unread' : 'read', id: item.id } });
            if (res.ok) {
                Sobuj_notificationsChanged();
            }
        },

        async readAll() {
            const res = await Sobuj_api(apiUrl, { method: 'POST', body: { op: 'read_all' } });
            if (res.ok) {
                Sobuj_notificationsChanged();
            }
        },
    };
}
