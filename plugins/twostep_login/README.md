Two-step login plugin for Roundcube
===================================

Splits the Roundcube login form into a two-step flow, similar to modern
identity providers:

1. **Step 1** — only the username field (`#rcmloginuser`), plus the server
   selector if one is configured, is shown together with a **Next** button.
2. **Step 2** — after the user enters a username and clicks *Next* (or presses
   Enter), the password field (`#rcmloginpwd`) and the **Login** button are
   revealed. The username stays visible (locked) with a *Change* link to go
   back to step 1.

The credentials are submitted with the standard `_user` / `_pass` / `_token`
POST request, so the regular Roundcube authentication and CSRF protection are
used unchanged.

How it works
------------

This is a pure **client-side, progressive-enhancement** plugin. On the
`render_page` hook (gated to the `login` template) it adds a small script and
stylesheet to the login page; the script hides/reveals fields in the browser.
The server-side login pipeline (`authenticate` / `rcmail->login()`) is not
modified.

> **Why `render_page` and not `template_object_loginform`?**
> The two-step toggle is inherently client-side, so the only thing the server
> needs to do is get the behaviour script onto the login page. In modern skins
> (e.g. Elastic) injecting assets from `template_object_loginform` did not
> reliably reach the rendered page, so the script never ran. `render_page`
> fires for the fully assembled `login` template on every skin and runs
> *before* the page `<head>`/footer scripts are written out, so the includes
> are reliably emitted.

Because the two-step behaviour is implemented entirely in JavaScript, the form
gracefully degrades to the normal single-step login form when JavaScript is
disabled (all fields are present in the rendered HTML; the script only toggles
their visibility).

Session/CSRF token refresh
--------------------------

Roundcube embeds a CSRF token in the login form. If the page sits idle until
the PHP session expires, that token goes stale and the login is rejected as an
*invalid request* — no matter what credentials are entered, and only after the
user has typed their password.

This plugin closes that gap: when the user advances past the username step, the
client calls a small pre-auth endpoint (`plugin.twostep_token`) that
re-establishes the temporary login session and returns a fresh token, which is
written into the form's hidden `_token` field. By the time the password is
submitted a valid token is in place. It is best-effort — if the request fails,
the existing token is kept and behaviour is unchanged.

Installation
------------

1. Copy the `twostep_login` directory into your Roundcube `plugins/` folder
   (it is already there if installed from source).
2. Enable it in your main config (`config/config.inc.php`):

   ```php
   $config['plugins'] = ['twostep_login'];
   ```

No further configuration is required. It works with the bundled Elastic and
Classic skins.

License
-------

GNU GPLv3+ (with exceptions for skins & plugins, like Roundcube itself).
