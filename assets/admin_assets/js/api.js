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
