Message security info plugin for Roundcube
==========================================

Surfaces a received message's sender-authentication status (SPF / DKIM / DMARC)
and lets users inspect the relevant headers.

Adds a **Message Security** link to the message header's links row (next to
*Summary / Headers / Plain text*). The link is always present; its icon colour
reflects the overall sender-authentication verdict. **DMARC** drives the icon
when the receiving server evaluated it (DMARC already implies an aligned SPF or
DKIM pass); otherwise the present **SPF** and **DKIM** results are combined,
omitting whichever is missing:

| Situation | Icon |
|---|---|
| DMARC pass — or all present SPF/DKIM checks pass (DKIM aligned with From) | green check |
| A present check is soft/incomplete (SPF softfail/neutral, DKIM valid but not aligned, or nothing to check) | amber triangle |
| DMARC fail, or any present SPF/DKIM check failed | red cross |
| Only an unverified DKIM signature is present | grey question mark |

The link text stays the normal blue link colour (like the other header links);
only the icon is coloured. A tooltip on the link gives the one-line verdict.

**Clicking the link opens a popup** (the same dialog style as *Message
headers*) with the parsed **SPF / DKIM / DMARC** results — Gmail-style — plus a
**Transport (TLS)** line showing whether the message reached your server over
an encrypted SMTP connection, and below them the raw `Authentication-Results` /
`Received-SPF` lines plus any administrator-configured extra headers.

Enabling / disabling checks
---------------------------

SPF, DKIM and DMARC each have to be deployed by the mail administrator, so it's
fine for a site not to use all of them. Each can be turned off — a disabled
mechanism is dropped from **both** the verdict and the popup, so users aren't
shown a perpetual warning for a check that simply isn't deployed:

```php
$config['message_security_info_check_spf']   = true;
$config['message_security_info_check_dkim']  = true;
$config['message_security_info_check_dmarc'] = true;
```

If **all three** are disabled there is nothing to evaluate, so the link (and
its icon) is hidden entirely.

The transport (TLS) line is informational only — it never changes the icon —
and has its own toggle:

```php
$config['message_security_info_check_tls'] = true;
```

The TLS state is read from the topmost `Received` header (the most recent hop,
typically your own receiving server): an `ESMTPS`/`ESMTPSA`/`LMTPS` transmission
type (RFC 3848) or a logged `TLSv…` version means the delivery to you was
encrypted.

How it works
------------

The cryptographic checks are performed by the **receiving mail server**, which
records the outcome in the `Authentication-Results` header (RFC 8601):

```
Authentication-Results: mx.example.org; dkim=pass header.d=example.com;
    spf=pass smtp.mailfrom=example.com; dmarc=pass header.from=example.com
```

This plugin:

1. Asks the core to fetch `DKIM-Signature`, `Authentication-Results`,
   `Received-SPF` and any configured extra headers from IMAP (`storage_init` →
   `fetch_headers`).
2. On message display (`message_objects` hook), parses `Authentication-Results`
   for the `dkim=` / `spf=` / `dmarc=` results and their domains, and compares
   the DKIM signing domain to the From domain (relaxed alignment: equal, or one
   a subdomain of the other).
3. Hands the verdict + details to the client via `set_env`; `message_security_info.js`
   builds the header link and the popup.
4. When there is **no** `Authentication-Results`, falls back to detecting a raw
   `DKIM-Signature` header and reports it as *present but unverified* — it does
   **not** cryptographically verify the signature itself.

Configurable extra headers
--------------------------

Any raw message headers can be shown at the bottom of the popup, below the
parsed SPF / DKIM / DMARC summary — handy for surfacing spam scores, routing,
message ids, etc. Headers absent from a message are simply omitted.

**Per user:** each user sets their own list under **Settings → Message
security** (one header name per line). This is stored in the standard
per-user preferences (`users.preferences`) — no extra database table.

**Admin default:** `message_security_info_extra_headers` provides the default
for users who haven't customized it:

```php
$config['message_security_info_extra_headers'] = ['X-Spam-Status', 'X-Spam-Score', 'Message-ID', 'Return-Path'];
```

To force the list and stop users changing it, add
`'message_security_info_extra_headers'` to the global `$config['dont_override']`.

Security: trust the right header
--------------------------------

`Authentication-Results` headers added by mail hops you don't control can be
**forged**. Set `message_security_info_trusted_authserv` to your own inbound server's
authserv-id (the token before the first `;`) so only its verdict is used:

```php
$config['message_security_info_trusted_authserv'] = ['mx.example.org'];
```

Leave it empty to consider every `Authentication-Results` header (convenient
for testing, but spoofable).

Limitations (initial version)
-----------------------------

- **No in-plugin cryptographic verification.** The pass/fail verdict comes from
  your mail server's `Authentication-Results`. If your server doesn't add that
  header, signed mail shows as *present but unverified*. Doing the crypto here
  would require the full raw RFC822 message plus DNS public-key lookups.
- **Alignment is not PSL-based.** Subdomain/equality only; it does not compute
  organizational domains via the Public Suffix List, so it is neither a full
  DMARC alignment check nor aware of registrable-domain boundaries.
- **The icon is a summary, not a policy decision.** Without DMARC the combined
  SPF + DKIM verdict treats an SPF-only pass as green even though SPF does not
  authenticate the visible From address; the per-check breakdown in the popup
  shows the detail.

Installation
------------

1. Place this directory in Roundcube's `plugins/` folder.
2. Enable it in `config/config.inc.php`:
   ```php
   $config['plugins'] = ['message_security_info'];
   ```
3. Optionally copy `config.inc.php.dist` to `config.inc.php` and set
   `message_security_info_trusted_authserv`.

License
-------

GNU GPLv3+ (with exceptions for skins & plugins, like Roundcube itself).
