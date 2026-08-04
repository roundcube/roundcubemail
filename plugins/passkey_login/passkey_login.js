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
        for (var b of bytes) {
            bin += String.fromCodePoint(b);
        }
        return btoa(bin);
    }

    function b64decode(str) {
        var bin = atob(str),
            bytes = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) {
            bytes[i] = bin.codePointAt(i);
        }
        return bytes;
    }

    function b64urlEncode(buf) {
        // b64 padding is only ever a run of trailing '=', so dropping every '='
        // is equivalent to the anchored /=+$/ strip but avoids a backtracking regex.
        return b64encode(buf).replaceAll('+', '-').replaceAll('/', '_').replaceAll('=', '');
    }

    function b64urlDecode(str) {
        str = str.replaceAll('-', '+').replaceAll('_', '/');
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
            && navigator.credentials?.get
            && navigator.credentials?.create
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
        if (window.console?.log) {
            window.console.log('[passkey_login]', ...arguments);
        }
    }

    function prfResult(credential) {
        var ext = credential.getClientExtensionResults ? credential.getClientExtensionResults() : {};
        dbg('prf extension result:', ext?.prf);
        if (ext?.prf?.results?.first) {
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
                for (var cred of credentials) {
                    if (cred.credId === usedId) {
                        match = cred;
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
                            dbg('signin: decryption failed -> PRF not usable', err?.name);
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
//
// Wrapped in its own scope so the many small helpers below live beside the
// controller rather than nested inside it (keeping each function simple)
// without leaking generic names like show()/label() into the global scope.
// ----------------------------------------------------------------------

(function () {
    var PRF_KEY = 'passkey_login_prf';
    // Remember the last authentication method used on this device so the next
    // login can default to it (and so we can offer the matching switch link).
    var METHOD_COOKIE = 'passkey_login_method';

    // ---- small DOM/util helpers ---------------------------------------

    function row_of(el) {
        return el ? (el.closest?.('tr') || el.parentNode) : null;
    }

    function show(el, visible) {
        if (el) {
            el.style.display = visible ? '' : 'none';
        }
    }

    function insertAfter(node, ref) {
        ref.parentNode.insertBefore(node, ref.nextSibling);
    }

    function label(name) {
        return rcmail.get_label(name, 'passkey_login');
    }

    function mkbutton(ctx, id, text) {
        var b = document.createElement('button');
        b.type = 'button';
        b.id = id;
        b.className = (ctx.submit ? ctx.submit.className + ' ' : 'button mainaction submit ') + id;
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

    function setStatus(ctx, text, isError) {
        ctx.status.textContent = text || '';
        ctx.status.className = 'passkey-status' + (isError ? ' error' : '');
        show(ctx.status, !!text);
    }

    // ---- PRF support memory -------------------------------------------

    // Remember a real PRF outcome per browser. This is the only reliable
    // signal: getClientCapabilities() is missing on some browsers (Firefox)
    // and a few misreport, so once an actual ceremony tells us PRF does/doesn't
    // work we trust that and stop guessing.
    function rememberPrf(ctx, value) {
        ctx.prfSupported = value;
        try {
            if (window.localStorage) {
                window.localStorage.setItem(PRF_KEY, value ? '1' : '0');
            }
        } catch (e) { /* localStorage unavailable — non-fatal */ }
    }

    // Some browsers advertise PRF support they can't actually deliver — most
    // notably Firefox on Windows, where the failure surfaces only as an
    // OS-level "security key can't be used" dialog and a generic, cancel-like
    // error indistinguishable from a real user cancellation. Runtime detection
    // is therefore unreliable, so an explicit user-agent exclude list
    // (configurable, default Firefox) simply turns passkeys off for them.
    function applyExcludedBrowsers(ctx) {
        if (!ctx.env.excluded_browsers?.length) {
            return;
        }
        var ua = navigator.userAgent || '';
        for (var name of ctx.env.excluded_browsers) {
            if (name && ua.includes(name)) {
                ctx.prfSupported = false;
                break;
            }
        }
    }

    // Otherwise remember a real per-browser outcome (the only fully reliable
    // signal), then fall back to a best-effort capability query.
    function applyCachedPrf(ctx) {
        if (!window.localStorage) {
            return;
        }
        var cached = null;
        // Guard only the storage read; the branches below can't throw.
        try {
            cached = window.localStorage.getItem(PRF_KEY);
        } catch (e) { /* localStorage blocked — leave undetermined */ }
        if (cached === '0') {
            ctx.prfSupported = false;
        } else if (cached === '1') {
            ctx.prfSupported = true;
        }
    }

    function queryPrfCapability(ctx) {
        rcube_passkey.prfCapable().then(function (v) {
            if (v !== null && ctx.prfSupported === null) {
                ctx.prfSupported = v;
            }
        });
    }

    function detectPrfSupport(ctx) {
        applyExcludedBrowsers(ctx);
        if (ctx.prfSupported === null) {
            applyCachedPrf(ctx);
        }
        if (ctx.webauthn && ctx.prfSupported === null) {
            queryPrfCapability(ctx);
        }
    }

    // ---- method cookie -------------------------------------------------

    function setMethod(value) {
        var secure = location.protocol === 'https:' ? '; Secure' : '';
        // 30 days, in seconds. Guard only the cookie write.
        try {
            document.cookie = METHOD_COOKIE + '=' + value
                + '; Max-Age=' + (30 * 24 * 60 * 60) + '; Path=/; SameSite=Lax' + secure;
        } catch (e) { /* cookies unavailable — non-fatal */ }
    }

    function lastMethod() {
        var cookie;
        // Guard only the cookie read; parsing below can't throw.
        try {
            cookie = document.cookie;
        } catch (e) { /* cookie unreadable — treat as no preference */ return null; }
        var m = /(?:^|;\s*)passkey_login_method=([^;]*)/.exec(cookie);
        return m ? decodeURIComponent(m[1]) : null;
    }

    // Whether a passkey sign-in can be offered right now: the browser supports
    // it, PRF isn't known-broken, and we have credentials + an unused challenge.
    function passkeyAvailable(ctx) {
        return !!(ctx.webauthn && ctx.prfSupported !== false && ctx.credentials.length && ctx.challenge);
    }

    // ---- step transitions ---------------------------------------------

    function toUsername(ctx) {
        ctx.state = 'username';
        ctx.form.classList.remove('passkey-step-password', 'passkey-step-passkey');
        ctx.form.classList.add('passkey-login', 'passkey-step-username');
        show(ctx.pass_row, false);
        show(ctx.host_row, true);
        show(ctx.submit, false);
        show(ctx.signinBtn, false);
        show(ctx.next, true);
        show(ctx.change, false);
        show(ctx.usepass, false);
        show(ctx.usepasskey, false);
        show(ctx.enrollWrap, false);
        setStatus(ctx, '');
        ctx.pass.removeAttribute('required');
        ctx.user.removeAttribute('readonly');
        try { ctx.user.focus(); } catch (e) { /* focus is best-effort */ }
    }

    function toPasskey(ctx) {
        ctx.state = 'passkey';
        ctx.form.classList.remove('passkey-step-username', 'passkey-step-password');
        ctx.form.classList.add('passkey-login', 'passkey-step-passkey');
        show(ctx.pass_row, false);
        show(ctx.host_row, false);
        show(ctx.submit, false);
        show(ctx.next, false);
        show(ctx.signinBtn, true);
        show(ctx.change, true);
        show(ctx.usepass, true);
        show(ctx.usepasskey, false);
        show(ctx.enrollWrap, false);
        ctx.pass.removeAttribute('required');
        ctx.user.setAttribute('readonly', 'readonly');
        try { ctx.signinBtn.focus(); } catch (e) { /* focus is best-effort */ }
    }

    function toPassword(ctx) {
        ctx.state = 'password';
        ctx.form.classList.remove('passkey-step-username', 'passkey-step-passkey');
        ctx.form.classList.add('passkey-login', 'passkey-step-password');
        show(ctx.pass_row, true);
        show(ctx.host_row, false);
        show(ctx.submit, true);
        show(ctx.next, false);
        show(ctx.signinBtn, false);
        show(ctx.change, true);
        show(ctx.usepass, false);
        // Offer switching (back) to passkey only when one can actually be used.
        show(ctx.usepasskey, passkeyAvailable(ctx));
        // Only offer enrollment when PRF support isn't known to be missing.
        show(ctx.enrollWrap, ctx.prfSupported !== false);
        if (ctx.enrollBox) {
            ctx.enrollBox.checked = false;
        }
        if (ctx.submit) {
            ctx.submit.disabled = false; // recover if a prior submit disabled it
        }
        ctx.pass.setAttribute('required', 'required');
        ctx.user.setAttribute('readonly', 'readonly');
        try { ctx.pass.focus(); } catch (e) { /* focus is best-effort */ }
    }

    // ---- behaviour -----------------------------------------------------

    function realSubmit(ctx) {
        HTMLFormElement.prototype.submit.call(ctx.form);
    }

    // Re-establish the temp login session and pull a fresh CSRF token so a
    // valid token is in place for the check/verify calls and the final login
    // submit, even if the PHP session expired while the page sat idle.
    // Best-effort: on failure the existing token is kept. Updates both
    // rcmail.env.request_token and the form's hidden _token field.
    function refresh_token(ctx) {
        if (!ctx.env.token_url) {
            return Promise.resolve();
        }
        return fetch(ctx.env.token_url, {
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
                if (data?.token) {
                    rcmail.env.request_token = data.token;
                    var tokenField = ctx.form.querySelector('input[name="_token"]');
                    if (tokenField) {
                        tokenField.value = data.token;
                    }
                }
            })
            .catch(function () { /* keep the existing token */ });
    }

    function afterCheck(ctx, data) {
        ctx.next.disabled = false;
        setStatus(ctx, '');
        if (ctx.webauthn && ctx.prfSupported !== false
            && data?.found && data.credentials?.length && data.challenge
        ) {
            ctx.credentials = data.credentials;
            ctx.challenge = data.challenge;
            // Default to whichever method this device used last; the
            // password step still offers a link back to the passkey.
            if (lastMethod() === 'password') {
                toPassword(ctx);
            } else {
                toPasskey(ctx);
            }
        } else {
            toPassword(ctx);
        }
    }

    function advance(ctx) {
        var name = (ctx.user.value || '').trim();
        if (!name) {
            ctx.user.classList.add('error');
            try { ctx.user.focus(); } catch (e) { /* focus is best-effort */ }
            return;
        }
        ctx.user.classList.remove('error');
        ctx.user.setAttribute('readonly', 'readonly');
        ctx.next.disabled = true;
        setStatus(ctx, label('checking'));

        // Refresh the CSRF token first so the check/verify and the eventual
        // login submit all use a token valid for the current session.
        refresh_token(ctx)
            .then(function () {
                return fetch(ctx.env.check_url, {
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
            .then(function (data) { afterCheck(ctx, data); })
            .catch(function () {
                ctx.next.disabled = false;
                setStatus(ctx, '');
                // Network/endpoint problem: fall back to password login.
                toPassword(ctx);
            });
    }

    function verifyAssertion(ctx, payload) {
        return fetch(ctx.env.verify_url, {
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
                if (!v?.ok) {
                    throw new Error('verify_failed:' + (v?.error || 'unknown'));
                }
                return true;
            });
    }

    function doSignin(ctx) {
        ctx.signinBtn.disabled = true;
        setStatus(ctx, label('passkeyprompt'));
        rcube_passkey.signin(ctx.user.value, ctx.credentials, ctx.challenge)
            .then(function (r) {
                rememberPrf(ctx, true); // PRF worked on this browser
                // The server must confirm the assertion before we submit.
                return verifyAssertion(ctx, r.verify).then(function () {
                    ctx.pass.value = r.password;
                    setStatus(ctx, label('passkeyok'));
                    setMethod('passkey'); // remember for next time on this device
                    realSubmit(ctx);
                });
            })
            .catch(function (err) {
                ctx.signinBtn.disabled = false;
                ctx.challenge = null; // consumed/!valid; a new one is needed
                if (err?.message === 'prf_unsupported') {
                    rememberPrf(ctx, false); // stop offering passkeys on this browser
                }
                setStatus(ctx, label('passkeyfailed'), true);
                if (window.console) {
                    console.warn('passkey_login: sign-in failed', err);
                }
                // offer the password fallback
                toPassword(ctx);
            });
    }

    function enrollThenSubmit(ctx) {
        ctx.submit.disabled = true;
        setStatus(ctx, label('enrolling'));
        rcube_passkey.enroll(ctx.user.value, ctx.pass.value, ctx.env.rp_name)
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
                } catch (e) { /* sessionStorage unavailable — store step is skipped */ }
                // The user just opted into passkeys — prefer them next time.
                setMethod('passkey');
                realSubmit(ctx);
            })
            .catch(function (err) {
                ctx.submit.disabled = false;
                if (window.console) {
                    console.warn('passkey_login: enrollment failed', err);
                }

                // Show a specific, *visible* reason and let the user retry as a
                // normal password login. We intentionally do NOT auto-submit:
                // realSubmit() would navigate away before the message is read,
                // which is what made failures (e.g. on Firefox) look mysterious.
                var reason = err?.message;
                if (reason === 'prf_unsupported' || reason === 'pubkey_unavailable') {
                    // Definitive: this browser can't produce what passkey
                    // encryption needs. Stop offering passkeys here.
                    rememberPrf(ctx, false);
                }
                setStatus(ctx, label(reason === 'prf_unsupported' ? 'prfunsupported' : 'enrollfailed'), true);

                // Clear the opt-in so the next Login click signs in with the
                // password instead of retrying enrollment.
                if (ctx.enrollBox) {
                    ctx.enrollBox.checked = false;
                }
            });
    }

    // ---- DOM construction & wiring ------------------------------------

    function buildEnrollRow(ctx) {
        ctx.enrollWrap = null;
        ctx.enrollBox = null;
        if (!(ctx.webauthn && ctx.env.enroll)) {
            return;
        }
        var enrollWrap = document.createElement('label');
        enrollWrap.id = 'passkey-enroll-row';
        enrollWrap.className = 'passkey-enroll';
        var enrollBox = document.createElement('input');
        enrollBox.type = 'checkbox';
        enrollBox.id = 'passkey-enroll';
        enrollWrap.appendChild(enrollBox);
        enrollWrap.appendChild(document.createTextNode(' ' + label('enrolllabel')));
        ctx.enrollWrap = enrollWrap;
        ctx.enrollBox = enrollBox;
        if (ctx.pass_row?.parentNode) {
            insertAfter(enrollWrap, ctx.pass_row);
        } else {
            ctx.buttons.insertBefore(enrollWrap, ctx.submit || null);
        }
    }

    function buildControls(ctx) {
        var next = mkbutton(ctx, 'passkey-next', label('next'));
        ctx.next = next;
        ctx.buttons.insertBefore(next, ctx.submit || null);

        var signinBtn = mkbutton(ctx, 'passkey-signin', label('signinpasskey'));
        signinBtn.classList.add('passkey-signin');
        ctx.signinBtn = signinBtn;
        ctx.buttons.insertBefore(signinBtn, ctx.submit || null);

        var status = document.createElement('div');
        status.id = 'passkey-status';
        status.className = 'passkey-status';
        ctx.status = status;
        ctx.buttons.appendChild(status);

        ctx.change = mklink('passkey-change', label('changeuser'));
        ctx.usepass = mklink('passkey-usepass', label('usepassword'));
        ctx.usepasskey = mklink('passkey-usepasskey', label('usepasskey'));
        ctx.buttons.appendChild(ctx.usepass);
        ctx.buttons.appendChild(ctx.usepasskey);
        ctx.buttons.appendChild(ctx.change);

        buildEnrollRow(ctx);

        // Copy the real submit button's styling onto our buttons once the skin
        // has finished decorating it (deferred a tick so class changes land).
        window.setTimeout(function () {
            if (ctx.submit) {
                var cls = ctx.submit.className;
                ctx.next.className = cls + ' passkey-next';
                ctx.signinBtn.className = cls + ' passkey-signin';
            }
        }, 0);
    }

    function onFormSubmit(ctx, e) {
        if (e.target !== ctx.form) {
            return;
        }
        if (ctx.state === 'username') {
            e.preventDefault();
            e.stopPropagation();
            advance(ctx);
        } else if (ctx.state === 'password' && ctx.enrollBox?.checked) {
            e.preventDefault();
            e.stopPropagation();
            enrollThenSubmit(ctx);
        } else if (ctx.state === 'password') {
            // Plain password login: record the method, then submit normally.
            setMethod('password');
        }
    }

    function wireEvents(ctx) {
        ctx.next.addEventListener('click', function (e) { e.preventDefault(); advance(ctx); });
        ctx.signinBtn.addEventListener('click', function (e) { e.preventDefault(); doSignin(ctx); });
        ctx.change.addEventListener('click', function (e) { e.preventDefault(); toUsername(ctx); });
        ctx.usepass.addEventListener('click', function (e) { e.preventDefault(); toPassword(ctx); });
        ctx.usepasskey.addEventListener('click', function (e) { e.preventDefault(); toPasskey(ctx); });

        // Roundcube's own login-form handler (program/js/app.js) shows a
        // persistent "Loading…" message and disables the submit button on every
        // submit event, without preventing navigation. For the submits we
        // intercept (the username step, and the enrollment step which we re-submit
        // programmatically) we must keep that handler from running — otherwise the
        // page is left stuck on "Loading…" with the button disabled and no request
        // sent. A capture-phase listener on the document runs before the
        // form-bound handler, so stopPropagation() here suppresses it. The plain
        // password submit is allowed to propagate so the form posts normally.
        document.addEventListener('submit', function (e) { onFormSubmit(ctx, e); }, true);
    }

    function rcube_passkey_login() {
        var user = document.getElementById('rcmloginuser'),
            pass = document.getElementById('rcmloginpwd'),
            host = document.getElementById('rcmloginhost'),
            submit = document.getElementById('rcmloginsubmit'),
            form = user?.form || document.getElementById('login-form');

        if (!user || !pass || !form) {
            return;
        }

        // A pending enrollment only survives a *successful* login (it is read on
        // the next authenticated page). If we are back on the login page, any
        // pending blob is stale (e.g. the password was wrong) and must be dropped.
        try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (e) { /* no sessionStorage — nothing to drop */ }

        var ctx = {
            env: rcmail.env.passkey_login || {},
            user: user,
            pass: pass,
            host: host,
            submit: submit,
            form: form,
            webauthn: rcube_passkey.supported(),
            prfSupported: null, // null = unknown, true/false once known
            state: 'username',
            credentials: [],
            challenge: null,
        };
        ctx.pass_row = row_of(pass);
        ctx.host_row = host ? row_of(host) : null;
        ctx.buttons = submit?.parentNode || form;

        detectPrfSupport(ctx);
        buildControls(ctx);
        wireEvents(ctx);
        toUsername(ctx);
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
            try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (error_) { /* best-effort cleanup */ }
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
                if (res?.ok) {
                    // Stored — forget the pending blob.
                    try { window.sessionStorage.removeItem(rcube_passkey.PENDING_KEY); } catch (e) { /* nothing to clean up */ }
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
})();
