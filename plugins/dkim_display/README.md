DKIM display plugin for Roundcube
=================================

Shows a verdict banner above each received message describing its **DKIM**
status and whether the signing domain aligns with the **From** address the user
actually sees:

| Situation | Banner | Box |
|---|---|---|
| DKIM valid **and** signing domain matches From | "Signature valid and matches the sender domain" | green |
| DKIM valid **but** signing domain ≠ From | "Signature is valid but was signed by …, which does not match the From address — the sender may be impersonated" | yellow |
| DKIM check failed | "Signature check failed … the sender may be forged" | red |
| DKIM signature present but the server did not verify it | "A DKIM signature is present but your mail server did not verify it" | blue (info) |
| No DKIM signature at all | "This message has no DKIM signature — its sender cannot be verified" | yellow |

How it works
------------

DKIM verification itself is performed by the **receiving mail server**, which
records the outcome in the `Authentication-Results` header (RFC 8601):

```
Authentication-Results: mx.example.org; dkim=pass header.d=example.com; spf=pass ...
```

This plugin:

1. Asks the core to fetch `DKIM-Signature` and `Authentication-Results` from
   IMAP (`storage_init` → `fetch_headers`).
2. On message display (`message_objects` hook), parses `Authentication-Results`
   for the `dkim=` result and the signing domain (`header.d` / `header.i`).
3. Compares that domain to the From address domain (relaxed alignment: equal,
   or one a subdomain of the other).
4. When there is **no** `Authentication-Results`, falls back to detecting a raw
   `DKIM-Signature` header and reports it as *present but unverified* — it does
   **not** cryptographically verify the signature itself.

Security: trust the right header
--------------------------------

`Authentication-Results` headers added by mail hops you don't control can be
**forged**. Set `dkim_display_trusted_authserv` to your own inbound server's
authserv-id (the token before the first `;`) so only its verdict is used:

```php
$config['dkim_display_trusted_authserv'] = ['mx.example.org'];
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
- The banner reflects DKIM only; SPF/DMARC are not surfaced (though they're in
  the same `Authentication-Results` header and could be added later).

Installation
------------

1. Place this directory in Roundcube's `plugins/` folder.
2. Enable it in `config/config.inc.php`:
   ```php
   $config['plugins'] = ['dkim_display'];
   ```
3. Optionally copy `config.inc.php.dist` to `config.inc.php` and set
   `dkim_display_trusted_authserv`.

License
-------

GNU GPLv3+ (with exceptions for skins & plugins, like Roundcube itself).
