/**
 * Two-step login plugin script
 *
 * Progressive enhancement of the standard Roundcube login form: the username
 * is asked for first, and only after the user confirms it does the password
 * field get revealed. With JavaScript disabled the regular single-step form
 * is left intact.
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */

window.rcmail && rcmail.addEventListener('init', function () {
    rcube_twostep_login();
});

function rcube_twostep_login() {
    var user = document.getElementById('rcmloginuser'),
        pass = document.getElementById('rcmloginpwd'),
        host = document.getElementById('rcmloginhost'),
        submit = document.getElementById('rcmloginsubmit'),
        form = (user && user.form) || document.getElementById('login-form');

    // If the expected fields are not present (not the login page, or an
    // unexpected markup), do nothing and leave the default form behaviour.
    if (!user || !pass || !form) {
        return;
    }

    // Returns the layout "row" that wraps an input: a table row in the
    // Elastic/Classic skins, otherwise the field's direct parent.
    function row_of(el) {
        if (!el) {
            return null;
        }
        return (el.closest && el.closest('tr')) || el.parentNode;
    }

    function show(el, visible) {
        if (el) {
            el.style.display = visible ? '' : 'none';
        }
    }

    var env = rcmail.env.twostep_login || {},
        pass_row = row_of(pass),
        host_row = host ? row_of(host) : null,
        token_field = form.querySelector('input[name="_token"]');

    // Refresh the CSRF token so a valid one is in place by the time the
    // password is submitted, even if the PHP session expired while the login
    // page was sitting idle. Best-effort: on failure the existing token is
    // kept (login still works as long as the session is alive).
    function refresh_token() {
        if (!env.token_url) {
            return;
        }
        fetch(env.token_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Roundcube-Request': rcmail.env.request_token || '',
            },
            credentials: 'same-origin',
            body: '',
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.token) {
                    if (token_field) {
                        token_field.value = data.token;
                    }
                    rcmail.env.request_token = data.token;
                }
            })
            .catch(function () { /* keep the existing token */ });
    }

    // "Next" button (advances from step 1 to step 2)
    var next = document.createElement('button');
    next.type = 'button';
    next.id = 'twostep-next';
    next.className = (submit ? submit.className + ' ' : 'button mainaction submit ') + 'twostep-next';
    next.textContent = rcmail.get_label('next', 'twostep_login');

    if (submit && submit.parentNode) {
        submit.parentNode.insertBefore(next, submit);
    } else {
        form.appendChild(next);
    }

    // The skin (e.g. Elastic) styles #rcmloginsubmit during its own init,
    // which may run after us. Mirror the final button look on a later tick so
    // the "Next" button matches the "Login" button across skins.
    window.setTimeout(function () {
        if (submit) {
            next.className = submit.className + ' twostep-next';
        }
    }, 0);

    // "Change" link to go back to step 1 (kept with the buttons so it does not
    // interfere with the skin's input-group layout around the username field)
    var change = document.createElement('a');
    change.href = '#';
    change.id = 'twostep-change';
    change.className = 'twostep-change';
    change.textContent = rcmail.get_label('changeuser', 'twostep_login');

    (next.parentNode || form).appendChild(change);

    function step1() {
        form.classList.remove('twostep-step2');
        form.classList.add('twostep-login', 'twostep-step1');

        show(pass_row, false);
        show(host_row, true);
        show(submit, false);
        show(next, true);
        show(change, false);

        // a hidden required field would block native validation/submit
        pass.removeAttribute('required');
        user.removeAttribute('readonly');

        try { user.focus(); } catch (e) {}
    }

    function step2() {
        form.classList.remove('twostep-step1');
        form.classList.add('twostep-login', 'twostep-step2');

        show(pass_row, true);
        show(host_row, false);
        show(submit, true);
        show(next, false);
        show(change, true);

        pass.setAttribute('required', 'required');
        // keep the chosen username visible but locked while entering password
        user.setAttribute('readonly', 'readonly');

        try { pass.focus(); } catch (e) {}
    }

    function advance() {
        var name = (user.value || '').replace(/^\s+|\s+$/g, '');

        if (!name) {
            user.classList.add('error');
            try { user.focus(); } catch (e) {}
            return;
        }

        user.classList.remove('error');
        step2();
        refresh_token();
    }

    next.addEventListener('click', function (e) {
        e.preventDefault();
        advance();
    });

    change.addEventListener('click', function (e) {
        e.preventDefault();
        step1();
    });

    // Enter in the username field advances instead of submitting the form
    user.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && form.classList.contains('twostep-step1')) {
            e.preventDefault();
            advance();
        }
    });

    // Safety net: never let the form submit while still on step 1
    form.addEventListener('submit', function (e) {
        if (form.classList.contains('twostep-step1')) {
            e.preventDefault();
            advance();
        }
    });

    // start on step 1
    step1();
}
