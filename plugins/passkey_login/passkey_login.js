/**
 * Passkey login plugin script
 *
 * Implements a username-first login flow with optional passkey (WebAuthn)
 * sign-in. The passkey is used via the WebAuthn PRF extension to derive a
 * symmetric key that decrypts the user's IMAP password, which was stored
 * (as ciphertext) during enrollment. All encryption/decryption happens here
 * in the browser; the server only ever sees ciphertext.
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

/* global rcmail */

var rcube_passkey = (function () {
    'use strict';

    var PENDING_KEY = 'passkey_login_pending';
    // PRF eval salt is derived deterministically from the username so that
    // every device enrolled for the same account uses the same salt (the salt
    // is not secret; the per-authenticator PRF output is what differs).
    var SALT_CONTEXT = 'roundcube-passkey-login:';
    var HKDF_INFO = 'roundcube-passkey-login/aes-gcm';

    // ---- small binary/base64 helpers ----------------------------------

    function enc(str) {
        return new TextEncoder().encode(str);
    }

    function dec(buf) {
        return new TextDecoder().decode(buf);
    }

    function toBytes(buf) {
        return buf instanceof Uint8Array ? buf : new Uint8Array(buf);
    }

    function b64encode(buf) {
        var bytes = toBytes(buf),
            bin = '';
        for (var i = 0; i < bytes.length; i++) {
            bin += String.fromCharCode(bytes[i]);
        }
        return btoa(bin);
    }

    function b64decode(str) {
        var bin = atob(str),
            bytes = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) {
            bytes[i] = bin.charCodeAt(i);
        }
        return bytes;
    }

    function b64urlEncode(buf) {
        return b64encode(buf).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    function b64urlDecode(str) {
        str = str.replace(/-/g, '+').replace(/_/g, '/');
        while (str.length % 4) {
            str += '=';
        }
        return b64decode(str);
    }

    function randomBytes(n) {
        return crypto.getRandomValues(new Uint8Array(n));
    }

    // ---- crypto --------------------------------------------------------

    function saltFor(username) {
        return crypto.subtle.digest('SHA-256', enc(SALT_CONTEXT + username)).then(toBytes);
    }

    function deriveKey(prfOutput) {
        return crypto.subtle.importKey('raw', toBytes(prfOutput), 'HKDF', false, ['deriveKey'])
            .then(function (ikm) {
                return crypto.subtle.deriveKey(
                    {
                        name: 'HKDF',
                        hash: 'SHA-256',
                        salt: new Uint8Array(0),
                        info: enc(HKDF_INFO),
                    },
                    ikm,
                    { name: 'AES-GCM', length: 256 },
                    false,
                    ['encrypt', 'decrypt']
                );
            });
    }

    function encryptPassword(key, password) {
        var iv = randomBytes(12);
        return crypto.subtle.encrypt({ name: 'AES-GCM', iv: iv }, key, enc(password))
            .then(function (ct) {
                return { iv: b64encode(iv), secret: b64encode(ct) };
            });
    }

    function decryptPassword(key, ivB64, secretB64) {
        return crypto.subtle.decrypt({ name: 'AES-GCM', iv: b64decode(ivB64) }, key, b64decode(secretB64))
            .then(dec);
    }

    // ---- WebAuthn ------------------------------------------------------

    function supported() {
        return !!(window.isSecureContext
            && window.PublicKeyCredential
            && navigator.credentials
            && navigator.credentials.get
            && navigator.credentials.create
            && window.crypto && crypto.subtle);
    }

    // Resolve to whether this client supports the WebAuthn PRF extension (which
    // we require to derive the encryption key): true / false, or null when it
    // can't be determined (older browsers without getClientCapabilities) — in
    // which case the caller should fall back to attempt-and-fail.
    function prfCapable() {
        if (!window.PublicKeyCredential
            || typeof PublicKeyCredential.getClientCapabilities !== 'function'
        ) {
            return Promise.resolve(null);
        }

        return PublicKeyCredential.getClientCapabilities()
            .then(function (caps) {
                // 'extension:prf' is the standardized capability key.
                return caps && typeof caps['extension:prf'] === 'boolean' ? caps['extension:prf'] : null;
            })
            .catch(function () { return null; });
    }

    function dbg() {
        if (window.console && window.console.log) {
            window.console.log.apply(window.console, ['[passkey_login]'].concat(Array.prototype.slice.call(arguments)));
        }
    }

    function prfResult(credential) {
        var ext = credential.getClientExtensionResults ? credential.getClientExtensionResults() : {};
        dbg('prf extension result:', ext && ext.prf);
        if (ext && ext.prf && ext.prf.results && ext.prf.results.first) {
            return toBytes(ext.prf.results.first);
        }
        return null;
    }

    // Create a new passkey and return {cred_id, iv, secret} for `password`.
    function enroll(username, password, rpName) {
        var salt;
        return saltFor(username)
            .then(function (s) {
                salt = s;
                return navigator.credentials.create({
                    publicKey: {
                        rp: { id: location.hostname, name: rpName || 'Roundcube Webmail' },
                        user: { id: randomBytes(16), name: username, displayName: username },
                        challenge: randomBytes(32),
                        pubKeyCredParams: [
                            { type: 'public-key', alg: -7 }, // ES256
                            { type: 'public-key', alg: -257 }, // RS256
                        ],
                        authenticatorSelection: { residentKey: 'preferred', userVerification: 'required' },
                        timeout: 60000,
                        extensions: { prf: { eval: { first: salt } } },
                    },
                });
            })
            .then(function (cred) {
                var prf = prfResult(cred);
                if (prf) {
                    return { cred: cred, prf: prf };
                }
                // Some platforms only return PRF output from get(); do a follow-up.
                return navigator.credentials.get({
                    publicKey: {
                        challenge: randomBytes(32),
                        allowCredentials: [{ type: 'public-key', id: cred.rawId }],
                        userVerification: 'required',
                        rpId: location.hostname,
                        extensions: { prf: { eval: { first: salt } } },
                    },
                }).then(function (assertion) {
                    var p = prfResult(assertion);
                    if (!p) {
                        throw new Error('prf_unsupported');
                    }
                    return { cred: cred, prf: p };
                });
            })
            .then(function (r) {
                // Capture the credential public key (SPKI DER) so the server can
                // verify future authentication assertions. Required here.
                var resp = r.cred.response,
                    spki = resp.getPublicKey ? resp.getPublicKey() : null,
                    alg = resp.getPublicKeyAlgorithm ? resp.getPublicKeyAlgorithm() : null;

                dbg('enroll: getPublicKey present:', !!spki, 'alg:', alg);

                if (!spki || (alg !== -7 && alg !== -257)) {
                    throw new Error('pubkey_unavailable');
                }

                return deriveKey(r.prf).then(function (key) {
                    return encryptPassword(key, password).then(function (e) {
                        return {
                            cred_id: b64urlEncode(r.cred.rawId),
                            iv: e.iv,
                            secret: e.secret,
                            public_key: b64encode(spki),
                            alg: alg,
                        };
                    });
                });
            });
    }

    // Authenticate with an existing passkey. Returns the decrypted password
    // plus the raw assertion fields the server needs to verify the signature.
    function signin(username, credentials, challengeB64url) {
        var salt;
        return saltFor(username)
            .then(function (s) {
                salt = s;
                return navigator.credentials.get({
                    publicKey: {
                        challenge: b64urlDecode(challengeB64url),
                        allowCredentials: credentials.map(function (c) {
                            return { type: 'public-key', id: b64urlDecode(c.credId) };
                        }),
                        userVerification: 'required',
                        rpId: location.hostname,
                        extensions: { prf: { eval: { first: salt } } },
                    },
                });
            })
            .then(function (assertion) {
                var prf = prfResult(assertion);
                if (!prf) {
                    throw new Error('prf_unsupported');
                }
                var usedId = b64urlEncode(assertion.rawId),
                    match = null;
                for (var i = 0; i < credentials.length; i++) {
                    if (credentials[i].credId === usedId) {
                        match = credentials[i];
                        break;
                    }
                }
                if (!match) {
                    throw new Error('credential_mismatch');
                }

                var resp = assertion.response,
                    verify = {
                        cred_id: usedId,
                        authenticator_data: b64encode(resp.authenticatorData),
                        client_data: b64encode(resp.clientDataJSON),
                        signature: b64encode(resp.signature),
                    };

                return deriveKey(prf).then(function (key) {
                    return decryptPassword(key, match.iv, match.secret).then(
                        function (password) {
                            return { password: password, verify: verify };
                        },
                        function (err) {
                            // The PRF-derived key cannot decrypt the stored
                            // secret: this browser's PRF output is not
                            // reproducible (it reported support but doesn't
                            // really work — e.g. Firefox on Windows). This is
                            // the only reliable proof that PRF works here.
                            dbg('signin: decryption failed -> PRF not usable', err && err.name);
                            throw new Error('prf_unsupported');
                        }
                    );
                });
            });
    }

    return {
        PENDING_KEY: PENDING_KEY,
        supported: supported,
        prfCapable: prfCapable,
        enroll: enroll,
        signin: signin,
    };
})();

// ----------------------------------------------------------------------
// Login-page controller
// ----------------------------------------------------------------------

function rcube_passkey_login() {
    var env = rcmail.env.passkey_login || {},
        user = document.getElementById('rcmloginuser'),
        pass = document.getElementById('rcmloginpwd'),
        host = document.getElementById('rcmloginhost'),
        submit = document.getElementById('rcmloginsubmit'),
        form = (user && user.form) || document.getElementById('login-form');

    if (!user || !pass || !form) {
        return;
    }

    // A pending enrollment only survives a *successful* login (it is read on
    // the next authenticated page). If we are back on the login page, any
    // pending blob is stale (e.g. the password was wrong) and must be dropped.
    try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (e) {}

    var PRF_KEY = 'passkey_login_prf',
        webauthn = rcube_passkey.supported(),
        prfSupported = null, // null = unknown, true/false once known
        state = 'username',
        credentials = [],
        challenge = null;

    // Remember a real PRF outcome per browser. This is the only reliable
    // signal: getClientCapabilities() is missing on some browsers (Firefox)
    // and a few misreport, so once an actual ceremony tells us PRF does/doesn't
    // work we trust that and stop guessing.
    function rememberPrf(value) {
        prfSupported = value;
        try {
            if (window.localStorage) {
                window.localStorage.setItem(PRF_KEY, value ? '1' : '0');
            }
        } catch (e) {}
    }

    // Some browsers advertise PRF support they can't actually deliver — most
    // notably Firefox on Windows, where the failure surfaces only as an
    // OS-level "security key can't be used" dialog and a generic, cancel-like
    // error indistinguishable from a real user cancellation. Runtime detection
    // is therefore unreliable, so an explicit user-agent exclude list
    // (configurable, default Firefox) simply turns passkeys off for them.
    if (env.excluded_browsers && env.excluded_browsers.length) {
        var ua = navigator.userAgent || '';
        for (var i = 0; i < env.excluded_browsers.length; i++) {
            if (env.excluded_browsers[i] && ua.indexOf(env.excluded_browsers[i]) !== -1) {
                prfSupported = false;
                break;
            }
        }
    }

    // Otherwise remember a real per-browser outcome (the only fully reliable
    // signal), then fall back to a best-effort capability query.
    if (prfSupported === null) {
        try {
            if (window.localStorage) {
                var cached = window.localStorage.getItem(PRF_KEY);
                if (cached === '0') {
                    prfSupported = false;
                } else if (cached === '1') {
                    prfSupported = true;
                }
            }
        } catch (e) {}
    }

    if (webauthn && prfSupported === null) {
        rcube_passkey.prfCapable().then(function (v) {
            if (v !== null && prfSupported === null) {
                prfSupported = v;
            }
        });
    }

    function row_of(el) {
        return el ? ((el.closest && el.closest('tr')) || el.parentNode) : null;
    }

    function show(el, visible) {
        if (el) {
            el.style.display = visible ? '' : 'none';
        }
    }

    function label(name) {
        return rcmail.get_label(name, 'passkey_login');
    }

    var pass_row = row_of(pass),
        host_row = host ? row_of(host) : null,
        buttons = (submit && submit.parentNode) || form;

    // --- injected controls ---------------------------------------------

    var next = mkbutton('passkey-next', label('next'));
    buttons.insertBefore(next, submit || null);

    var signinBtn = mkbutton('passkey-signin', label('signinpasskey'));
    signinBtn.classList.add('passkey-signin');
    buttons.insertBefore(signinBtn, submit || null);

    var status = document.createElement('div');
    status.id = 'passkey-status';
    status.className = 'passkey-status';
    buttons.appendChild(status);

    var change = mklink('passkey-change', label('changeuser'));
    var usepass = mklink('passkey-usepass', label('usepassword'));
    buttons.appendChild(usepass);
    buttons.appendChild(change);

    // Enrollment opt-in (shown on the password step when supported).
    var enrollWrap = null, enrollBox = null;
    if (webauthn && env.enroll) {
        enrollWrap = document.createElement('label');
        enrollWrap.id = 'passkey-enroll-row';
        enrollWrap.className = 'passkey-enroll';
        enrollBox = document.createElement('input');
        enrollBox.type = 'checkbox';
        enrollBox.id = 'passkey-enroll';
        enrollWrap.appendChild(enrollBox);
        enrollWrap.appendChild(document.createTextNode(' ' + label('enrolllabel')));
        if (pass_row && pass_row.parentNode) {
            insertAfter(enrollWrap, pass_row);
        } else {
            buttons.insertBefore(enrollWrap, submit || null);
        }
    }

    // Match the skin's styling of the primary button once it has run its init.
    window.setTimeout(function () {
        if (submit) {
            var cls = submit.className;
            next.className = cls + ' passkey-next';
            signinBtn.className = cls + ' passkey-signin';
        }
    }, 0);

    function mkbutton(id, text) {
        var b = document.createElement('button');
        b.type = 'button';
        b.id = id;
        b.className = (submit ? submit.className + ' ' : 'button mainaction submit ') + id;
        b.textContent = text;
        return b;
    }

    function mklink(id, text) {
        var a = document.createElement('a');
        a.href = '#';
        a.id = id;
        a.className = id;
        a.textContent = text;
        return a;
    }

    function insertAfter(node, ref) {
        ref.parentNode.insertBefore(node, ref.nextSibling);
    }

    function setStatus(text, isError) {
        status.textContent = text || '';
        status.className = 'passkey-status' + (isError ? ' error' : '');
        show(status, !!text);
    }

    // --- states ---------------------------------------------------------

    function toUsername() {
        state = 'username';
        form.classList.remove('passkey-step-password', 'passkey-step-passkey');
        form.classList.add('passkey-login', 'passkey-step-username');
        show(pass_row, false);
        show(host_row, true);
        show(submit, false);
        show(signinBtn, false);
        show(next, true);
        show(change, false);
        show(usepass, false);
        show(enrollWrap, false);
        setStatus('');
        pass.removeAttribute('required');
        user.removeAttribute('readonly');
        try { user.focus(); } catch (e) {}
    }

    function toPasskey() {
        state = 'passkey';
        form.classList.remove('passkey-step-username', 'passkey-step-password');
        form.classList.add('passkey-login', 'passkey-step-passkey');
        show(pass_row, false);
        show(host_row, false);
        show(submit, false);
        show(next, false);
        show(signinBtn, true);
        show(change, true);
        show(usepass, true);
        show(enrollWrap, false);
        pass.removeAttribute('required');
        user.setAttribute('readonly', 'readonly');
        try { signinBtn.focus(); } catch (e) {}
    }

    function toPassword() {
        state = 'password';
        form.classList.remove('passkey-step-username', 'passkey-step-passkey');
        form.classList.add('passkey-login', 'passkey-step-password');
        show(pass_row, true);
        show(host_row, false);
        show(submit, true);
        show(next, false);
        show(signinBtn, false);
        show(change, true);
        show(usepass, false);
        // Only offer enrollment when PRF support isn't known to be missing.
        show(enrollWrap, prfSupported !== false);
        if (enrollBox) {
            enrollBox.checked = false;
        }
        if (submit) {
            submit.disabled = false; // recover if a prior submit disabled it
        }
        pass.setAttribute('required', 'required');
        user.setAttribute('readonly', 'readonly');
        try { pass.focus(); } catch (e) {}
    }

    // --- behaviour ------------------------------------------------------

    function realSubmit() {
        HTMLFormElement.prototype.submit.call(form);
    }

    // Re-establish the temp login session and pull a fresh CSRF token so a
    // valid token is in place for the check/verify calls and the final login
    // submit, even if the PHP session expired while the page sat idle.
    // Best-effort: on failure the existing token is kept. Updates both
    // rcmail.env.request_token and the form's hidden _token field.
    function refresh_token() {
        if (!env.token_url) {
            return Promise.resolve();
        }
        return fetch(env.token_url, {
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
                    rcmail.env.request_token = data.token;
                    var tokenField = form.querySelector('input[name="_token"]');
                    if (tokenField) {
                        tokenField.value = data.token;
                    }
                }
            })
            .catch(function () { /* keep the existing token */ });
    }

    function advance() {
        var name = (user.value || '').replace(/^\s+|\s+$/g, '');
        if (!name) {
            user.classList.add('error');
            try { user.focus(); } catch (e) {}
            return;
        }
        user.classList.remove('error');
        user.setAttribute('readonly', 'readonly');
        next.disabled = true;
        setStatus(label('checking'));

        // Refresh the CSRF token first so the check/verify and the eventual
        // login submit all use a token valid for the current session.
        refresh_token()
            .then(function () {
                return fetch(env.check_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Roundcube-Request': rcmail.env.request_token || '',
                    },
                    body: new URLSearchParams({ _user: name }).toString(),
                    credentials: 'same-origin',
                });
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                next.disabled = false;
                setStatus('');
                if (webauthn && prfSupported !== false
                    && data && data.found && data.credentials && data.credentials.length && data.challenge
                ) {
                    credentials = data.credentials;
                    challenge = data.challenge;
                    toPasskey();
                } else {
                    toPassword();
                }
            })
            .catch(function () {
                next.disabled = false;
                setStatus('');
                // Network/endpoint problem: fall back to password login.
                toPassword();
            });
    }

    function verifyAssertion(payload) {
        return fetch(env.verify_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Roundcube-Request': rcmail.env.request_token || '',
            },
            body: new URLSearchParams(payload).toString(),
            credentials: 'same-origin',
        })
            .then(function (r) { return r.json(); })
            .then(function (v) {
                if (!v || !v.ok) {
                    throw new Error('verify_failed:' + ((v && v.error) || 'unknown'));
                }
                return true;
            });
    }

    function doSignin() {
        signinBtn.disabled = true;
        setStatus(label('passkeyprompt'));
        rcube_passkey.signin(user.value, credentials, challenge)
            .then(function (r) {
                rememberPrf(true); // PRF worked on this browser
                // The server must confirm the assertion before we submit.
                return verifyAssertion(r.verify).then(function () {
                    pass.value = r.password;
                    setStatus(label('passkeyok'));
                    realSubmit();
                });
            })
            .catch(function (err) {
                signinBtn.disabled = false;
                challenge = null; // consumed/!valid; a new one is needed
                if (err && err.message === 'prf_unsupported') {
                    rememberPrf(false); // stop offering passkeys on this browser
                }
                setStatus(label('passkeyfailed'), true);
                if (window.console) {
                    console.warn('passkey_login: sign-in failed', err);
                }
                // offer the password fallback
                toPassword();
            });
    }

    function enrollThenSubmit() {
        submit.disabled = true;
        setStatus(label('enrolling'));
        rcube_passkey.enroll(user.value, pass.value, env.rp_name)
            .then(function (blob) {
                // NB: a successful enrollment is NOT proof that PRF works — some
                // browsers (Firefox/Windows) produce a PRF output that encrypts
                // fine but can't be reproduced at sign-in. We only mark this
                // browser "supported" once a real sign-in decrypts successfully.
                try {
                    window.sessionStorage.setItem(rcube_passkey.PENDING_KEY, JSON.stringify({
                        cred_id: blob.cred_id,
                        iv: blob.iv,
                        secret: blob.secret,
                        public_key: blob.public_key,
                        alg: blob.alg,
                    }));
                } catch (e) {}
                realSubmit();
            })
            .catch(function (err) {
                submit.disabled = false;
                if (window.console) {
                    console.warn('passkey_login: enrollment failed', err);
                }

                // Show a specific, *visible* reason and let the user retry as a
                // normal password login. We intentionally do NOT auto-submit:
                // realSubmit() would navigate away before the message is read,
                // which is what made failures (e.g. on Firefox) look mysterious.
                var reason = err && err.message;
                if (reason === 'prf_unsupported' || reason === 'pubkey_unavailable') {
                    // Definitive: this browser can't produce what passkey
                    // encryption needs. Stop offering passkeys here.
                    rememberPrf(false);
                }
                setStatus(label(reason === 'prf_unsupported' ? 'prfunsupported' : 'enrollfailed'), true);

                // Clear the opt-in so the next Login click signs in with the
                // password instead of retrying enrollment.
                if (enrollBox) {
                    enrollBox.checked = false;
                }
            });
    }

    next.addEventListener('click', function (e) { e.preventDefault(); advance(); });
    signinBtn.addEventListener('click', function (e) { e.preventDefault(); doSignin(); });
    change.addEventListener('click', function (e) { e.preventDefault(); toUsername(); });
    usepass.addEventListener('click', function (e) { e.preventDefault(); toPassword(); });

    // Roundcube's own login-form handler (program/js/app.js) shows a
    // persistent "Loading…" message and disables the submit button on every
    // submit event, without preventing navigation. For the submits we
    // intercept (the username step, and the enrollment step which we re-submit
    // programmatically) we must keep that handler from running — otherwise the
    // page is left stuck on "Loading…" with the button disabled and no request
    // sent. A capture-phase listener on the document runs before the
    // form-bound handler, so stopPropagation() here suppresses it. The plain
    // password submit is allowed to propagate so the form posts normally.
    document.addEventListener('submit', function (e) {
        if (e.target !== form) {
            return;
        }
        if (state === 'username') {
            e.preventDefault();
            e.stopPropagation();
            advance();
        } else if (state === 'password' && enrollBox && enrollBox.checked) {
            e.preventDefault();
            e.stopPropagation();
            enrollThenSubmit();
        }
        // password step without enrollment: allow normal submit.
    }, true);

    toUsername();
}

// ----------------------------------------------------------------------
// Authenticated-page tail: persist a pending enrollment, then forget it
// ----------------------------------------------------------------------

function rcube_passkey_flush_pending() {
    if (window.self !== window.top) {
        return; // never run inside a content iframe
    }

    var env = rcmail.env.passkey_login || {},
        raw;

    try { raw = window.sessionStorage.getItem(rcube_passkey.PENDING_KEY); } catch (e) { return; }
    if (!raw || !env.store_url) {
        return;
    }

    var data;
    try { data = JSON.parse(raw); } catch (e) {
        try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (e2) {}
        return;
    }

    fetch(env.store_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Roundcube-Request': rcmail.env.request_token || '',
        },
        body: new URLSearchParams({
            cred_id: data.cred_id,
            iv: data.iv,
            secret: data.secret,
            public_key: data.public_key,
            alg: data.alg,
        }).toString(),
        credentials: 'same-origin',
    })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.ok) {
                // Stored — forget the pending blob.
                try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (e) {}
            } else if (window.console) {
                // Keep the blob so it retries on the next page load, and make
                // the reason visible (e.g. {error: "db"} => check the table).
                console.warn('passkey_login: storing the passkey failed', res);
            }
        })
        .catch(function (err) {
            if (window.console) {
                console.warn('passkey_login: store request failed', err);
            }
        });
}

window.rcmail && rcmail.addEventListener('init', function () {
    if (document.getElementById('rcmloginuser')) {
        rcube_passkey_login();
    } else {
        rcube_passkey_flush_pending();
    }
});
