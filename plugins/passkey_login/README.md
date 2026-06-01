Passkey login plugin for Roundcube
==================================

Adds a **username-first** login flow with optional **passkey (WebAuthn)**
sign-in, building on the idea of the `twostep_login` plugin.

```
Step 1            Step 2 (passkey enrolled)      Step 2 (no passkey)
┌───────────┐     ┌───────────────────────┐      ┌────────────────────┐
│ username  │ ──▶ │ [Sign in with passkey]│  or  │ password           │
│  [Next]   │     │  Use password instead │      │ ☐ set up a passkey │
└───────────┘     └───────────────────────┘      │ [Login]            │
                                                  └────────────────────┘
```

1. The user enters a username. The browser asks the server (pre-auth) whether
   any passkey is stored for that username.
2. **If a passkey exists** and the device supports WebAuthn + the PRF
   extension, a *Sign in with a passkey* button is shown. The passkey unlocks
   (decrypts) the stored IMAP password in the browser and submits it through
   the normal login form.
3. **If no passkey exists**, the password field is shown with an opt-in to
   *set up a passkey on this device*. On a successful password login, the typed
   password is encrypted in the browser and stored for next time.

How the password is protected
------------------------------

IMAP needs the *actual* password to authenticate, so the password has to be
recoverable — it cannot be one-way hashed. This plugin therefore stores it, but
only as **ciphertext**, and arranges things so the **decryption key never
leaves the user's authenticator**:

- A passkey created with the **WebAuthn PRF extension** can produce a stable,
  high-entropy secret (`prf.results.first`) for a given salt. Producing it
  requires the physical authenticator **and** user verification
  (biometric / PIN).
- That PRF output is run through **HKDF-SHA256** to derive an **AES-256-GCM**
  key (WebCrypto). The salt is derived deterministically from the username
  (`SHA-256("roundcube-passkey-login:" + username)`) so every device enrolled
  for the same account uses the same salt — the per-authenticator PRF output is
  what differs, so each device produces its own ciphertext.
- The browser encrypts the password and sends `{cred_id, iv, secret}` plus the
  credential **public key** (SPKI, from `getPublicKey()`) to the server, which
  stores it in the `passkey_login` table.
- At sign-in the server returns the ciphertext; the browser uses the passkey to
  re-derive the key, decrypts, and logs in. **The server only ever sees
  ciphertext.**

Server-side assertion verification
----------------------------------

On every passkey sign-in the server verifies the WebAuthn **assertion** before
the login is allowed to proceed:

1. The `plugin.passkey_check` endpoint issues a fresh, **single-use challenge**
   (32 random bytes, stored in the session for 5 minutes).
2. The browser's `navigator.credentials.get()` signs that challenge.
3. The `plugin.passkey_verify` endpoint then checks, server-side:
   - `clientDataJSON.type === "webauthn.get"`, the challenge matches the issued
     one, and the origin matches the expected origin;
   - the authenticator-data RP ID hash matches `SHA-256(rpId)`, and the
     **User Present** + **User Verified** flags are set;
   - the **signature** verifies against the stored public key
     (`openssl_verify`, ES256/RS256), over
     `authenticatorData || SHA-256(clientDataJSON)`;
   - the signature **counter** has increased (cloned-authenticator detection).

   The challenge is consumed on first use (replay protection). Only if all
   checks pass does the browser submit the decrypted password to the normal
   login form.

The expected RP ID and origin are derived from the request by default; override
with `passkey_login_rpid` / `passkey_login_origin` (see `config.inc.php.dist`)
behind a reverse proxy. Requires PHP's OpenSSL extension.

Database
--------

Create the table from the appropriate file in `SQL/`:

```sh
# pick one
mysql   roundcube < plugins/passkey_login/SQL/mysql.sql
psql    roundcube < plugins/passkey_login/SQL/postgres.sql
sqlite3 /path/to/roundcube.db < plugins/passkey_login/SQL/sqlite.sql
```

If you use a `db_prefix`, add it to the table (and Postgres sequence) names in
the SQL file before importing.

Installation
------------

1. Place this directory in Roundcube's `plugins/` folder.
2. Create the database table (above).
3. Enable it in `config/config.inc.php`:
   ```php
   $config['plugins'] = ['passkey_login'];
   ```
   Enable **either** `passkey_login` **or** `twostep_login`, not both — they
   both transform the login form.
4. Optionally copy `config.inc.php.dist` to `config.inc.php` to tune the
   relying-party name or disable enrollment.

**Passkeys require a secure context** — serve Roundcube over HTTPS (or use
`localhost` for development). The plugin detects support and silently falls
back to a normal password login where passkeys/PRF are unavailable.

**Browser support.** This relies on the WebAuthn **PRF** extension to derive the
encryption key. It works on Chrome/Edge and Safari. **Firefox (especially on
Windows) advertises PRF support it can't actually deliver** — the ceremony fails
only at the OS level ("This security key can't be used"), with a generic error
that can't be told apart from a user cancellation, so PRF support there cannot
be detected reliably at runtime.

Because of that, passkeys are gated by an explicit **user-agent exclude list**,
`passkey_login_excluded_browsers`, which **defaults to `['Firefox']`** — those
browsers are never offered passkey creation or sign-in and fall straight through
to the password form. Set it to `[]` to allow every browser. As secondary
safeguards the plugin also honours `getClientCapabilities()` where it's truthful
and remembers a real PRF failure per browser (`localStorage`).

Security considerations & limitations
-------------------------------------

This is a working implementation suitable for evaluation. Be aware of the
following deliberate trade-offs:

- **Authentication assertions are verified; registration attestation is not.**
  Sign-in assertions are fully verified server-side (challenge, origin, RP ID,
  flags, signature, counter — see above). Registration does **not** verify the
  attestation statement: the credential public key is captured via the
  browser's `getPublicKey()` and trusted on enrollment. Because enrollment is
  bound to the already-authenticated `user_id` and a forged key would only
  break the enroller's own future sign-in, this is a safe simplification here;
  full attestation verification would require parsing the CBOR attestation
  object (e.g. via a PHP WebAuthn library).
- **Pre-auth lookup / username enumeration.** The `plugin.passkey_check`
  endpoint runs before login (it must, to offer the passkey) and reveals
  whether a username has an enrolled passkey. It is protected by the
  request-token, but it still allows enumeration. The ciphertext it returns is
  useless without the authenticator.
- **Ownership binding.** Rows are keyed by `(user_id, cred_id)` and bound to
  the core `users` table via a foreign key. The pre-auth lookup resolves the
  typed username to a `user_id` the same way login does (`username_domain` /
  `login_lc` normalization, then `rcube_user::query`), so a credential is only
  ever returned for its own account. Deployments that resolve logins through
  virtuser `email2user` are not mirrored here; passkey sign-in then falls back
  to the password form.
- **Shared device.** Anyone who can pass the device's user verification
  (unlock) can sign in. Treat passkey enrollment as "trust this device".
- **Password rotation.** If the IMAP password changes, the stored ciphertext
  becomes stale; sign-in will fail IMAP and the user falls back to entering
  (and re-enrolling) the new password.

License
-------

GNU GPLv3+ (with exceptions for skins & plugins, like Roundcube itself).
