# Roundcube Password Plugin — HestiaCP Driver

A production-ready driver for Roundcube’s `password` plugin that changes **mailbox** passwords on **Hestia Control Panel** via its API.

- **Driver class**: `rcube_hestia_password`
- **Driver file**: `plugins/password/drivers/hestia.php`
- **Roundcube compatibility**: 1.6.x `password` plugin (zero-arg constructor, optional 3rd `$username` param in `save()`)
- **Auth methods**: `password`, `hash`, `accesskey`
- **Owner discovery**: `v-search-domain-owner` (no `returncode`)
- **Password change**: `v-change-mail-account-password` (`returncode=yes`, expects `0`)

---

## 1) Files in this driver
- `plugins/password/drivers/hestia.php` — the driver
- `plugins/password/config.inc.php` — your live plugin config (you maintain this)
- `plugins/password/config.inc.php.dist` — template (optional)
- `plugins/password/localization/en_US.inc` — **append** the Hestia labels you were given

> You do **not** need any stub/diagnostic files in production.

---

## 2) Install
1. Copy `plugins/password/drivers/hestia.php` into place (overwrite any existing Hestia driver).
2. Edit `plugins/password/config.inc.php` and set the full block below (fill your values):
   ```php
   <?php
   $config['password_driver'] = 'hestia';

   $config['password_hestia'] = [
       // Must end with /api/
       'endpoint' => 'https://panel.example.com:8083/api/',

       // Choose ONE auth method
       'auth' => [
           'type' => 'password',   // 'password' | 'hash' | 'accesskey'

           // if 'password'
           'user' => 'admin',
           'pass' => 'CHANGEME',

           // if 'hash'
           // 'user' => 'admin',
           // 'hash' => 'CHANGEME',

           // if 'accesskey'
           // 'access_key' => 'CHANGEME',
           // 'secret_key' => 'CHANGEME',
       ],

       'username_is_email' => true,
       'timeout'     => 10,
       'verify_peer' => true,
       'verify_host' => true,
       'min_length'  => 8,
       'debug'       => false,      // true -> logs/password
       // 'force_owner' => 'ownername', // bypass lookup (rare)
   ];

   $config['password_confirm_current'] = true;
   $config['password_minimum_length']  = 8;
   ```
3. **Localization (translations):** open `plugins/password/localization/en_US.inc` and **append** the Hestia `$labels[...]` entries you received (do not remove existing lines). This keeps messages translatable with the rest of the plugin.
4. Clear PHP opcache (restart php-fpm or Apache).

---

## 3) Hestia settings
On the Hestia server (UI → *Server Settings → Configure → Security → System*):
- **Enable API access** = ON
- **Allowed IP addresses for API** = **Roundcube server’s public IP**
- If using `auth.type = 'password'` or `hash`: **Enable Legacy API access** = ON
- If using `auth.type = 'accesskey'`: configure access/secret keys accordingly in Hestia and use them here.

---

## 4) How the driver works
1. Parse Roundcube login as `local@domain` (configurable; default = true).
2. `v-search-domain-owner <domain> mail` (no `returncode`) → returns owner username.
3. `v-change-mail-account-password <owner> <domain> <local> <newpass>` with `returncode=yes` → expects `0`.

Both calls are over HTTPS to `https://<host>:8083/api/` with the configured auth mode.

---

## 5) Error codes → human messages
When Hestia returns a numeric code, the driver maps it to a translatable key and shows a helpful message. Common codes:
- **0** — success
- **10** — permission denied (bad API identity / IP not allowed / legacy disabled)
- **3** — object not found (mailbox or domain)
- **9** — wrong/invalid password (policy)  
…and others (1–20). All keys are provided so translators can localize them.

> Driver also logs details to `logs/password` when `'debug' => true`.

---

## 6) Quick CLI smoke tests (from Roundcube host)
Replace values, then run:

```bash
PANEL='https://panel.example.com:8083/api/'
HUSER='admin'
HPASS='yourpassword'
MAIL='alice@example.com'
NEWP='TempPassw0rd!'

LOCAL="${MAIL%@*}"; DOMAIN="${MAIL#*@}"

# 1) Owner lookup (no returncode) — expect owner name (e.g., 'user1')
curl -sS -X POST "$PANEL"   --data-urlencode "user=$HUSER"   --data-urlencode "password=$HPASS"   --data-urlencode "cmd=v-search-domain-owner"   --data-urlencode "arg1=$DOMAIN"   --data-urlencode "arg2=mail"

# 2) Change password (returncode=yes) — expect 0
OWNER="$(curl -sS -X POST "$PANEL"   --data-urlencode "user=$HUSER"   --data-urlencode "password=$HPASS"   --data-urlencode "cmd=v-search-domain-owner"   --data-urlencode "arg1=$DOMAIN"   --data-urlencode "arg2=mail")"

curl -sS -X POST "$PANEL"   --data-urlencode "user=$HUSER"   --data-urlencode "password=$HPASS"   --data-urlencode "returncode=yes"   --data-urlencode "cmd=v-change-mail-account-password"   --data-urlencode "arg1=$OWNER"   --data-urlencode "arg2=$DOMAIN"   --data-urlencode "arg3=$LOCAL"   --data-urlencode "arg4=$NEWP"
```

If #1 returns a number or empty, you called it with `returncode=yes` by mistake or the domain doesn’t exist. If #2 returns non-zero, check API permissions/IP allowlist and auth mode.

---

## 7) Debugging
- Turn on `'debug' => true` in `plugins/password/config.inc.php` → logs detailed steps to `logs/password`.
- Turn on Roundcube file logging (`config/config.inc.php`):
  ```php
  $config['log_driver']  = 'file';
  $config['log_dir']     = RCUBE_INSTALL_PATH . 'logs';
  $config['debug_level'] = 1;
  ```
- If the UI shows “Could not save password” with **no** driver logs, fix session/CSRF:
  ```php
  $config['check_ip'] = false;
  $config['use_https'] = true;
  $config['force_https'] = true;
  $config['session_domain'] = 'YOUR-WEBMAIL-HOST';
  $config['session_path'] = '/';      // or '/mail'
  $config['same_site'] = 'Lax';       // or 'None' if framed (HTTPS required)
  $config['des_key'] = 'long-static-random-string';
  ```

---

## 8) Security hardening
- Keep TLS verification **on** (`verify_peer`, `verify_host`).
- Restrict API allowed IPs to the Roundcube host.
- Prefer `hash` or `accesskey` auth for distribution; avoid storing panel passwords if you can.
- Rotate credentials/keys periodically.

---

## 9) Contributing
- Driver class/filename must remain: `rcube_hestia_password` in `plugins/password/drivers/hestia.php`.
- Keep messages translatable under the `password` domain; add labels to `en_US.inc` and friends.
- PRs welcome for extended error-code maps and policy mirroring.

---

## 10) License
Same as Roundcube’s password plugin unless you specify otherwise in your distribution. If in doubt, include the original plugin’s license alongside this driver.
