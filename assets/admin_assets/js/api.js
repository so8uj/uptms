/*
 * Sobuj_api() - the one function every page uses to talk to the PHP API (files in /api).
 *
 *   const res = await Sobuj_api('http://.../api/categories.php?page=2');                    // GET
 *   const res = await Sobuj_api(url, { method: 'POST', body: { op: 'delete', id: 5 } });    // POST (JSON)
 *   const res = await Sobuj_api(url, { method: 'POST', body: formData });                   // POST a form with a file
 *
 * It always returns { ok, status, data }, so the page code never needs try/catch:
 *   ok     true when the server answered 200-299
 *   status the HTTP code (0 = the server could not be reached)
 *   data   the JSON the server sent, for example { success, errors, message }
 *
 * The CSRF token is read from <meta name="csrf-token"> (printed by admin/includes/header.php)
 * and sent with every request in the X-CSRF-Token header.
 */
async function Sobuj_api(url, options = {}) {
    const meta = document.querySelector('meta[name="csrf-token"]');

    const settings = {
        method: options.method || 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-Token': meta ? meta.content : '',
        },
    };

    if (options.body instanceof FormData) {
        // A form with a file: the browser sets the Content-Type itself, so it must not be set here.
        settings.body = options.body;
    } else if (options.body !== undefined) {
        settings.headers['Content-Type'] = 'application/json';
        settings.body = JSON.stringify(options.body);
    }

    let response;
    try {
        response = await fetch(url, settings);
    } catch (error) {
        return { ok: false, status: 0, data: { message: 'Could not reach the server. Please check your connection.' } };
    }

    let data;
    try {
        data = await response.json();
    } catch (error) {
        data = { message: 'The server sent an unexpected answer.' };
    }

    // The session ended (for example the user logged out in another tab): go to the login page.
    if (response.status === 401) {
        const appUrl = document.querySelector('meta[name="app-url"]');
        window.location.href = (appUrl ? appUrl.content : '') + '/index.php';
    }

    // Something was saved: the numbers on the sidebar menu may be different now, so they ask again (menu_counts.js).
    if (response.ok && settings.method !== 'GET') {
        window.dispatchEvent(new CustomEvent('data-changed'));
    }

    return { ok: response.ok, status: response.status, data };
}


// A message that must survive a page change (after saving, the form page opens the list page)
function Sobuj_rememberMessage(text) {
    try { sessionStorage.setItem('sobuj_message', text); } catch (error) { /* storage blocked: no message */ }
}

function Sobuj_takeMessage() {
    try {
        const text = sessionStorage.getItem('sobuj_message');
        sessionStorage.removeItem('sobuj_message');
        return text;
    } catch (error) {
        return null;
    }
}


// ---------------------------------------------------------------------
// The question and the message box of the project. The browser's own popups (alert, confirm, prompt) are not used.
//
//   if (await Sobuj_confirm('Delete "Test"?', { title: 'Delete', confirmText: 'Delete', danger: true })) { ... }
//   Sobuj_alert('Something to read.');
// ---------------------------------------------------------------------
function Sobuj_popup(message, options, withCancel) {
    return new Promise(function (resolve) {
        const settings = Object.assign({ title: withCancel ? 'Please confirm' : 'Notice', confirmText: withCancel ? 'Yes' : 'OK', cancelText: 'Cancel', danger: false }, options || {});

        const overlay = document.createElement('div');
        overlay.className = 'sobuj-popup-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        const box = document.createElement('div');
        box.className = 'sobuj-popup card shadow';

        const title = document.createElement('h5');
        title.className = 'mb-2';
        title.textContent = settings.title;

        const text = document.createElement('p');
        text.className = 'mb-4 sobuj-popup-text';
        text.textContent = message;   // text, not HTML: a name typed by a user cannot add markup

        const buttons = document.createElement('div');
        buttons.className = 'd-flex justify-content-end gap-2';

        function close(answer) {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
            resolve(answer);
        }

        function onKey(event) {
            if (event.key === 'Escape') {
                close(false);
            }
        }

        if (withCancel) {
            const cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.className = 'btn btn-light';
            cancel.textContent = settings.cancelText;
            cancel.addEventListener('click', function () { close(false); });
            buttons.appendChild(cancel);
        }

        const ok = document.createElement('button');
        ok.type = 'button';
        ok.className = 'btn ' + (settings.danger ? 'btn-danger' : 'btn-primary');
        ok.textContent = settings.confirmText;
        ok.addEventListener('click', function () { close(true); });
        buttons.appendChild(ok);

        // a click on the dark area behind the box means "no"
        overlay.addEventListener('mousedown', function (event) {
            if (event.target === overlay) {
                close(false);
            }
        });

        box.append(title, text, buttons);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
        document.addEventListener('keydown', onKey);
        ok.focus();
    });
}

// Asks a question. Answers true (the button) or false (Cancel, Escape or a click outside).
function Sobuj_confirm(message, options) {
    return Sobuj_popup(message, options, true);
}

// Shows a message with one button.
function Sobuj_alert(message, options) {
    return Sobuj_popup(message, options, false);
}

// If any script still calls the browser's alert(), the message opens in the same box.
window.alert = function (message) {
    Sobuj_alert(String(message));
};
