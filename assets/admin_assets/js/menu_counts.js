/*
 * The numbers on the sidebar menu.
 * A menu item that wants a number has <span class="sidebar-menu-badge" data-menu-count="requested" hidden></span>.
 * The number comes from api/menu_counts.php. A zero keeps the badge hidden.
 */
// The footer loads this file with data-url (the address of the API)
const Sobuj_menuCountsUrl = document.currentScript.dataset.url;

async function Sobuj_loadMenuCounts() {
    const badges = document.querySelectorAll('[data-menu-count]');
    if (badges.length === 0) {
        return;
    }

    let data;
    try {
        const response = await fetch(Sobuj_menuCountsUrl, { headers: { 'Accept': 'application/json' } });
        data = await response.json();
        if (!response.ok) {
            return;
        }
    } catch (error) {
        return;
    }

    badges.forEach(function (badge) {
        const total = Number(data[badge.dataset.menuCount] || 0);
        badge.textContent = total;
        badge.hidden = total === 0;
    });
}

document.addEventListener('DOMContentLoaded', Sobuj_loadMenuCounts);

// After anything was saved on the page (Sobuj_api tells it) the numbers are loaded again, so a badge never shows an old number.
let Sobuj_menuCountsTimer = null;
window.addEventListener('data-changed', function () {
    clearTimeout(Sobuj_menuCountsTimer);
    Sobuj_menuCountsTimer = setTimeout(Sobuj_loadMenuCounts, 200);
});


// The notice on the admin dashboard: "You have 2 new research requests waiting for review."
function Sobuj_adminNotice(apiUrl) {
    return {
        newRequests: 0,
        needSupervisor: 0,
        completion: 0,

        async init() {
            try {
                const response = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();

                if (response.ok) {
                    this.newRequests = data.new_requests || 0;
                    this.needSupervisor = data.need_supervisor || 0;
                    this.completion = data.completion || 0;
                }
            } catch (error) {
                return;
            }
        },

        message() {
            const parts = [];

            if (this.newRequests > 0) {
                parts.push('You have ' + this.newRequests + ' new research ' + (this.newRequests === 1 ? 'request' : 'requests') + ' waiting for review.');
            }
            if (this.needSupervisor > 0) {
                parts.push(this.needSupervisor + ' approved ' + (this.needSupervisor === 1 ? 'topic needs' : 'topics need') + ' a supervisor.');
            }
            if (this.completion > 0) {
                parts.push(this.completion + (this.completion === 1 ? ' research is' : ' research are') + ' ready to be completed.');
            }

            return parts.join(' ');
        },
    };
}


// The numbers on the admin dashboard cards (api/dashboard.php)
function Sobuj_adminDashboard(apiUrl) {
    return {
        numbers: {},

        async init() {
            try {
                const response = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();

                if (response.ok) {
                    this.numbers = data;
                }
            } catch (error) {
                return;
            }
        },

        value(key) {
            return this.numbers[key] ?? 0;
        },
    };
}
