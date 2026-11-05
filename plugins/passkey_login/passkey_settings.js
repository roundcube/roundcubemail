/**
 * Passkey login plugin - Settings management UI
 *
 * Lists the user's enrolled passkeys and lets them rename the description,
 * delete a single key, or register a new one. The "add a passkey" ceremony
 * reuses rcube_passkey.enroll() from passkey_login.js (loaded on every
 * authenticated page), so all encryption still happens in the browser.
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * @licend  The above is the entire license notice for the JavaScript code in
 * this file.
 */

window.rcmail && rcmail.addEventListener('init', function () {
    var env = rcmail.env.passkey_login_manage,
        container = document.getElementById('passkey-manage');

    if (!env || !container) {
        return;
    }

    var labels = env.labels || {};

    function token() {
        return rcmail.env.request_token || '';
    }

    // POST url-encoded data; resolve with the parsed JSON response.
    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Roundcube-Request': token(),
            },
            body: new URLSearchParams(data).toString(),
            credentials: 'same-origin',
        }).then(function (r) { return r.json(); });
    }

    function flash(input, ok) {
        input.classList.remove('passkey-saved', 'passkey-error');
        input.classList.add(ok ? 'passkey-saved' : 'passkey-error');
        window.setTimeout(function () {
            input.classList.remove('passkey-saved', 'passkey-error');
        }, 2000);
    }

    function credOf(el) {
        var row = el.closest('.passkey-row');
        return row ? row.getAttribute('data-cred') : '';
    }

    function refreshEmptyState() {
        var tbody = document.querySelector('#passkey-manage-table tbody'),
            table = document.getElementById('passkey-manage-table'),
            empty = document.getElementById('passkey-manage-empty'),
            has = tbody && tbody.children.length > 0;

        if (table) { table.style.display = has ? '' : 'none'; }
        if (empty) { empty.style.display = has ? 'none' : ''; }
    }

    // --- rename -------------------------------------------------------

    function renameRow(input) {
        var cred = credOf(input);
        if (!cred) {
            return;
        }

        post(env.rename_url, { cred_id: cred, description: input.value })
            .then(function (res) {
                if (res && res.ok) {
                    input.value = res.description; // server-sanitized value
                    flash(input, true);
                } else {
                    flash(input, false);
                    rcmail.display_message(labels.savefailed, 'error');
                }
            })
            .catch(function () {
                flash(input, false);
                rcmail.display_message(labels.savefailed, 'error');
            });
    }

    // --- delete -------------------------------------------------------

    function deleteRow(link) {
        var cred = credOf(link);
        if (!cred) {
            return;
        }

        rcmail.confirm_dialog(labels.deleteconfirm, labels.delete, function () {
            post(env.delete_url, { cred_id: cred })
                .then(function (res) {
                    if (res && res.ok) {
                        var row = link.closest('.passkey-row');
                        if (row) { row.remove(); }
                        refreshEmptyState();
                    } else {
                        rcmail.display_message(labels.savefailed, 'error');
                    }
                })
                .catch(function () {
                    rcmail.display_message(labels.savefailed, 'error');
                });
        });
    }

    // --- add ----------------------------------------------------------

    function addRow(cred, description, created) {
        var tbody = document.querySelector('#passkey-manage-table tbody');
        if (!tbody) {
            return;
        }

        var tr = document.createElement('tr');
        tr.className = 'passkey-row';
        tr.setAttribute('data-cred', cred);

        var descCell = document.createElement('td');
        descCell.className = 'passkey-desc-cell';
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'passkey-desc';
        input.maxLength = 255;
        input.value = description || '';
        descCell.appendChild(input);

        var createdCell = document.createElement('td');
        createdCell.className = 'passkey-created';
        createdCell.textContent = created || '';

        var actionCell = document.createElement('td');
        actionCell.className = 'passkey-actions';
        var del = document.createElement('a');
        del.href = '#';
        del.className = 'passkey-delete button delete';
        del.textContent = labels.delete;
        actionCell.appendChild(del);

        tr.appendChild(descCell);
        tr.appendChild(createdCell);
        tr.appendChild(actionCell);
        tbody.appendChild(tr);

        refreshEmptyState();
    }

    function doAdd(password, description, dialog, button) {
        if (!password) {
            rcmail.display_message(labels.passwordhint, 'error');
            return;
        }

        if (button) { button.disabled = true; }

        rcube_passkey.enroll(env.username, password, env.rp_name)
            .then(function (payload) {
                return post(env.store_url, {
                    cred_id: payload.cred_id,
                    iv: payload.iv,
                    secret: payload.secret,
                    public_key: payload.public_key,
                    alg: payload.alg,
                    password: password,
                    description: description,
                });
            })
            .then(function (res) {
                if (res && res.ok) {
                    addRow(res.credId, res.description, res.created);
                    if (dialog) { dialog.dialog('close'); }
                    rcmail.display_message(labels.saved, 'confirmation');
                    return;
                }

                if (button) { button.disabled = false; }
                rcmail.display_message(res && res.error === 'bad_password' ? labels.badpassword : labels.addfailed, 'error');
            })
            .catch(function (err) {
                if (button) { button.disabled = false; }
                // NotAllowedError == the user dismissed the browser prompt.
                if (err && err.name === 'NotAllowedError') {
                    return;
                }
                var reason = err && err.message ? ' (' + err.message + ')' : '';
                rcmail.display_message(labels.addfailed + reason, 'error');
            });
    }

    function addPasskey() {
        var box = document.createElement('div');
        box.className = 'passkey-add-dialog';

        var pwLabel = document.createElement('label');
        pwLabel.textContent = labels.password;
        var pw = document.createElement('input');
        pw.type = 'password';
        pw.className = 'passkey-add-password';
        pw.autocomplete = 'current-password';
        pwLabel.appendChild(pw);

        var hint = document.createElement('div');
        hint.className = 'hint';
        hint.textContent = labels.passwordhint;

        var descLabel = document.createElement('label');
        descLabel.textContent = labels.description;
        var desc = document.createElement('input');
        desc.type = 'text';
        desc.className = 'passkey-add-desc';
        desc.maxLength = 255;
        descLabel.appendChild(desc);

        box.appendChild(pwLabel);
        box.appendChild(hint);
        box.appendChild(descLabel);

        var dialog = rcmail.show_popup_dialog(box, labels.addtitle, [
            {
                text: labels.add,
                class: 'mainaction create',
                click: function (e) {
                    doAdd(pw.value, desc.value, $(this), e.target);
                },
            },
            {
                text: rcmail.get_label('cancel'),
                click: function () {
                    $(this).dialog('close');
                },
            },
        ]);

        window.setTimeout(function () {
            try { pw.focus(); } catch (err) { /* ignore */ }
        }, 50);

        return dialog;
    }

    // --- wiring (delegated) -------------------------------------------

    container.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('passkey-desc')) {
            renameRow(e.target);
        }
    });

    container.addEventListener('click', function (e) {
        var target = e.target;
        if (!target) {
            return;
        }
        if (target.classList.contains('passkey-delete')) {
            e.preventDefault();
            deleteRow(target);
        } else if (target.id === 'passkey-add') {
            e.preventDefault();
            addPasskey();
        }
    });
});
