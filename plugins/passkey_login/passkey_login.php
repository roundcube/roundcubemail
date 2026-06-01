<?php

/**
 * Passkey login
 *
 * Adds passkey (WebAuthn) sign-in on top of the standard Roundcube login,
 * building on the username-first flow of the `twostep_login` plugin.
 *
 * Flow
 * ----
 *  1. The user types a username. The browser asks the server (pre-auth AJAX)
 *     whether any passkey credentials are stored for that username.
 *  2. If a credential exists and the device supports WebAuthn + the PRF
 *     extension, a "Sign in with a passkey" button is offered. The passkey is
 *     used to derive a symmetric key (WebAuthn PRF), which decrypts the stored
 *     IMAP password *in the browser*; the decrypted password is then submitted
 *     through the normal Roundcube login form.
 *  3. If no credential exists, the regular password field is shown, with an
 *     opt-in to enroll a passkey on this device. On a successful password
 *     login the typed password is encrypted in the browser (again with a
 *     PRF-derived key) and the ciphertext is stored server-side.
 *
 * Security model (see README.md for the full discussion)
 * ------------------------------------------------------
 *  - The real IMAP password is needed to talk to the mail server, so it must
 *    be recoverable. It is therefore stored, but **only as ciphertext**.
 *  - Encryption/decryption happens **entirely in the browser** (WebCrypto
 *    AES-256-GCM). The key is derived from the passkey via the WebAuthn PRF
 *    extension, which requires the physical authenticator plus user
 *    verification (biometric/PIN). The server stores ciphertext only and never
 *    sees the key or the plaintext.
 *  - This plugin does NOT perform server-side WebAuthn attestation/assertion
 *    signature verification. The effective authentication gate is twofold:
 *    you must hold the authenticator to derive the key, and the decrypted
 *    password must still be accepted by IMAP.
 *
 * @license GNU GPLv3+
 * @author Claude
 */
class passkey_login extends rcube_plugin
{
    /** @var rcmail */
    private $rc;

    /** Maximum accepted sizes for stored values (defensive bounds). */
    private const MAX_CRED_ID = 512;
    private const MAX_IV = 64;
    private const MAX_SECRET = 8192;
    private const MAX_PUBKEY = 2048;

    /** WebAuthn assertion challenge lifetime, in seconds. */
    private const CHALLENGE_TTL = 300;

    /** COSE algorithm identifiers we accept (ES256, RS256). */
    private const ALLOWED_ALGS = [-7, -257];

    /**
     * Plugins that also take over the login form / authentication flow and
     * therefore cannot run alongside this one. Extend as needed.
     */
    private const CONFLICTING_PLUGINS = [
        'twostep_login',
    ];

    #[\Override]
    public function init()
    {
        $this->rc = rcmail::get_instance();

        // If a conflicting plugin is enabled, log it for the administrator and
        // stay out of the way (no hooks) rather than fight over the login form.
        if ($this->has_conflict()) {
            return;
        }

        // The 'startup' hook runs before authentication and before the request
        // is dispatched, so it is the only place a plugin can answer AJAX
        // calls made from the (unauthenticated) login page. We handle our
        // small JSON endpoints here and exit.
        $this->add_hook('startup', [$this, 'startup']);

        // Inject the behaviour script, styles and labels onto the login page
        // (for the sign-in/enrollment UI) and onto authenticated pages (so a
        // pending enrollment created during login can be persisted).
        $this->add_hook('render_page', [$this, 'render_page']);
    }

    /**
     * Detect a plugin that also rewrites the login flow. On a conflict an
     * error is written to the Roundcube error log so the administrator is
     * notified; the caller then disables this plugin's functionality.
     *
     * The configured `plugins` list is checked (not just already-loaded
     * plugins) so a conflict is detected regardless of load order.
     *
     * @return bool True if a conflicting plugin is enabled
     */
    private function has_conflict()
    {
        $enabled = array_merge(
            (array) $this->rc->config->get('plugins', []),
            (array) $this->rc->plugins->active_plugins
        );

        foreach (self::CONFLICTING_PLUGINS as $plugin) {
            if (in_array($plugin, $enabled, true)) {
                rcube::raise_error([
                    'code' => 520,
                    'type' => 'php',
                    'message' => "Plugin '{$this->ID}' is disabled: it cannot be used together with"
                        . " '{$plugin}' (both take over the login form). Enable only one of them"
                        . ' in the $config[\'plugins\'] list.',
                ], true, false);

                return true;
            }
        }

        return false;
    }

    /**
     * Pre-dispatch hook: answer our AJAX endpoints directly.
     *
     * @param array $args Hook arguments ('task', 'action')
     *
     * @return array
     */
    public function startup($args)
    {
        switch ($args['action'] ?? '') {
            case 'plugin.passkey_token':
                $this->action_token();   // exits
                break;
            case 'plugin.passkey_check':
                $this->action_check();   // exits
                break;
            case 'plugin.passkey_verify':
                $this->action_verify();  // exits
                break;
            case 'plugin.passkey_store':
                $this->action_store();   // exits
                break;
            case 'plugin.passkey_remove':
                $this->action_remove();  // exits
                break;
        }

        return $args;
    }

    /**
     * Inject assets where they are needed.
     *
     * @param array $args Hook arguments ('template', ...)
     *
     * @return array
     */
    public function render_page($args)
    {
        $template = $args['template'] ?? '';
        $logged_in = !empty($_SESSION['user_id']);

        // Login page: full sign-in/enrollment UI.
        // Authenticated full pages: only needed to flush a pending enrollment
        // that was prepared during the just-completed login.
        if ($template !== 'login' && !$logged_in) {
            return $args;
        }

        $this->add_texts('localization/', true);

        $this->include_stylesheet('passkey_login.css');
        $this->include_script('passkey_login.js');

        $this->rc->output->set_env('passkey_login', [
            'token_url' => $this->rc->url(['_task' => 'login', '_action' => 'plugin.passkey_token']),
            'check_url' => $this->rc->url(['_task' => 'login', '_action' => 'plugin.passkey_check']),
            'verify_url' => $this->rc->url(['_task' => 'login', '_action' => 'plugin.passkey_verify']),
            'store_url' => $this->rc->url(['_action' => 'plugin.passkey_store']),
            'rp_name' => $this->rc->config->get('passkey_login_rp_name', 'Roundcube Webmail'),
            'enroll' => (bool) $this->rc->config->get('passkey_login_enroll', true),
            // User-agent substrings for which passkeys are NOT offered (their
            // WebAuthn PRF support is unreliable and can't be detected at
            // runtime). Default excludes Firefox. Set to [] to allow all.
            'excluded_browsers' => array_values((array) $this->rc->config->get('passkey_login_excluded_browsers', ['Firefox'])),
        ]);

        return $args;
    }

    /**
     * POST plugin.passkey_token (pre-auth).
     *
     * Re-establishes the temporary login session and returns a fresh CSRF
     * token. The client calls this when advancing past the username step so a
     * valid token is in place for the subsequent check/verify calls and the
     * final login submit — even if the PHP session expired while the login
     * page sat idle. No request-token check here: the whole point is that the
     * token the client holds may be stale.
     */
    private function action_token()
    {
        $_SESSION['temp'] = true;
        $token = $this->rc->get_request_token();
        $this->rc->session->write_close();

        $this->json(['token' => $token]);
    }

    /**
     * GET/POST plugin.passkey_check (pre-auth).
     * Given a username, return the stored passkey credentials (ciphertext).
     */
    private function action_check()
    {
        if (!$this->rc->check_request()) {
            $this->json(['error' => 'invalid_request']);
        }

        $user = trim((string) rcube_utils::get_input_string('_user', rcube_utils::INPUT_POST));
        $user_id = $user !== '' ? $this->resolve_user_id($user) : 0;

        $credentials = [];

        if ($user_id) {
            $db = $this->rc->get_dbh();
            $result = $db->query(
                'SELECT `cred_id`, `iv`, `secret` FROM ' . $db->table_name('passkey_login', true)
                    . ' WHERE `user_id` = ?',
                $user_id
            );

            while ($row = $db->fetch_assoc($result)) {
                $credentials[] = [
                    'credId' => $row['cred_id'],
                    'iv' => $row['iv'],
                    'secret' => $row['secret'],
                ];
            }
        }

        // Issue a fresh, single-use challenge for the assertion (sign-in).
        // It is stored server-side and verified in action_verify().
        $challenge = $this->b64url(random_bytes(32));
        $_SESSION['passkey_login_challenge'] = $challenge;
        $_SESSION['passkey_login_challenge_time'] = time();
        $this->rc->session->write_close();

        $this->json([
            'found' => count($credentials) > 0,
            'credentials' => $credentials,
            'challenge' => $challenge,
        ]);
    }

    /**
     * POST plugin.passkey_verify (pre-auth).
     * Verify a WebAuthn authentication assertion against the stored public key
     * and the single-use challenge issued by action_check().
     */
    private function action_verify()
    {
        if (!function_exists('openssl_verify')) {
            $this->json(['ok' => false, 'error' => 'no_openssl']);
        }
        if (!$this->rc->check_request()) {
            $this->json(['ok' => false, 'error' => 'invalid_request']);
        }

        // Consume the single-use challenge (one attempt per issued challenge).
        $challenge = $_SESSION['passkey_login_challenge'] ?? null;
        $issued = (int) ($_SESSION['passkey_login_challenge_time'] ?? 0);
        unset($_SESSION['passkey_login_challenge'], $_SESSION['passkey_login_challenge_time']);
        $this->rc->session->write_close();

        if (!$challenge || (time() - $issued) > self::CHALLENGE_TTL) {
            $this->json(['ok' => false, 'error' => 'challenge']);
        }

        $cred_id = (string) rcube_utils::get_input_string('cred_id', rcube_utils::INPUT_POST);
        $auth_data = base64_decode((string) rcube_utils::get_input_string('authenticator_data', rcube_utils::INPUT_POST), true);
        $client_json = base64_decode((string) rcube_utils::get_input_string('client_data', rcube_utils::INPUT_POST), true);
        $signature = base64_decode((string) rcube_utils::get_input_string('signature', rcube_utils::INPUT_POST), true);

        if (
            !$this->valid_b64($cred_id, self::MAX_CRED_ID)
            || $auth_data === false || strlen($auth_data) < 37
            || $client_json === false || $client_json === ''
            || $signature === false || $signature === ''
        ) {
            $this->json(['ok' => false, 'error' => 'malformed']);
        }

        // Load the stored credential (public key + replay counter).
        $db = $this->rc->get_dbh();
        $row = $db->fetch_assoc($db->query(
            'SELECT `public_key`, `alg`, `sign_count` FROM ' . $db->table_name('passkey_login', true)
                . ' WHERE `cred_id` = ?',
            $cred_id
        ));

        if (empty($row) || empty($row['public_key'])) {
            $this->json(['ok' => false, 'error' => 'unknown_credential']);
        }

        // 1) clientDataJSON: correct ceremony, our challenge, our origin.
        $client = json_decode($client_json, true);
        if (
            !is_array($client)
            || ($client['type'] ?? '') !== 'webauthn.get'
            || !hash_equals($challenge, (string) ($client['challenge'] ?? ''))
            || !$this->origin_allowed((string) ($client['origin'] ?? ''))
        ) {
            $this->json(['ok' => false, 'error' => 'client_data']);
        }

        // 2) authenticatorData: RP ID hash, user present + verified.
        if (!hash_equals(hash('sha256', $this->rp_id(), true), substr($auth_data, 0, 32))) {
            $this->json(['ok' => false, 'error' => 'rpid']);
        }
        $flags = ord($auth_data[32]);
        if (!($flags & 0x01) || !($flags & 0x04)) {  // UP and UV
            $this->json(['ok' => false, 'error' => 'flags']);
        }
        $sign_count = unpack('N', substr($auth_data, 33, 4))[1];

        // 3) signature over authenticatorData || SHA-256(clientDataJSON).
        $pem = $this->spki_to_pem($row['public_key']);
        $pubkey = $pem ? openssl_pkey_get_public($pem) : false;
        if (!$pubkey) {
            $this->json(['ok' => false, 'error' => 'pubkey']);
        }

        $signed = $auth_data . hash('sha256', $client_json, true);
        if (openssl_verify($signed, $signature, $pubkey, \OPENSSL_ALGO_SHA256) !== 1) {
            $this->json(['ok' => false, 'error' => 'signature']);
        }

        // 4) cloned-authenticator detection via the signature counter.
        $stored = (int) $row['sign_count'];
        if (($sign_count > 0 || $stored > 0) && $sign_count <= $stored) {
            $this->json(['ok' => false, 'error' => 'counter']);
        }

        $db->query(
            'UPDATE ' . $db->table_name('passkey_login', true) . ' SET `sign_count` = ? WHERE `cred_id` = ?',
            $sign_count, $cred_id
        );

        $this->json(['ok' => true]);
    }

    /**
     * POST plugin.passkey_store (authenticated).
     * Persist a freshly enrolled credential + ciphertext for the logged-in user.
     */
    private function action_store()
    {
        $user_id = (int) $this->rc->get_user_id();

        if (!$user_id || !$this->rc->check_request()) {
            $this->json(['error' => 'unauthorized']);
        }

        $cred_id = (string) rcube_utils::get_input_string('cred_id', rcube_utils::INPUT_POST);
        $iv = (string) rcube_utils::get_input_string('iv', rcube_utils::INPUT_POST);
        $secret = (string) rcube_utils::get_input_string('secret', rcube_utils::INPUT_POST);
        $public_key = (string) rcube_utils::get_input_string('public_key', rcube_utils::INPUT_POST);
        $alg = (int) rcube_utils::get_input_string('alg', rcube_utils::INPUT_POST);

        // Validate shape: non-empty, within bounds, base64-ish.
        if (
            !$this->valid_b64($cred_id, self::MAX_CRED_ID)
            || !$this->valid_b64($iv, self::MAX_IV)
            || !$this->valid_b64($secret, self::MAX_SECRET)
            || !$this->valid_b64($public_key, self::MAX_PUBKEY)
            || !in_array($alg, self::ALLOWED_ALGS, true)
        ) {
            $this->json(['error' => 'invalid_input']);
        }

        $db = $this->rc->get_dbh();
        $table = $db->table_name('passkey_login', true);

        // Replace any prior row for this exact credential (re-enrollment).
        $db->query("DELETE FROM {$table} WHERE `cred_id` = ? AND `user_id` = ?", $cred_id, $user_id);

        $db->query(
            "INSERT INTO {$table}"
                . ' (`user_id`, `cred_id`, `iv`, `secret`, `public_key`, `alg`, `sign_count`, `created`)'
                . ' VALUES (?, ?, ?, ?, ?, ?, 0, ' . $db->now() . ')',
            $user_id, $cred_id, $iv, $secret, $public_key, $alg
        );

        if ($error = $db->is_error()) {
            // Likely a privilege/grant issue or a schema mismatch. Log the
            // exact reason for the admin; the client only gets a generic flag.
            rcube::raise_error([
                'code' => 600,
                'type' => 'php',
                'message' => 'passkey_login: failed to store credential: ' . $error,
            ], true, false);

            $this->json(['ok' => false, 'error' => 'db']);
        }

        $this->json(['ok' => true]);
    }

    /**
     * POST plugin.passkey_remove (authenticated).
     * Delete all of the current user's stored passkeys.
     */
    private function action_remove()
    {
        $user_id = (int) $this->rc->get_user_id();

        if (!$user_id || !$this->rc->check_request()) {
            $this->json(['error' => 'unauthorized']);
        }

        $db = $this->rc->get_dbh();
        $db->query(
            'DELETE FROM ' . $db->table_name('passkey_login', true) . ' WHERE `user_id` = ?',
            $user_id
        );

        $this->json(['ok' => !$db->is_error()]);
    }

    /**
     * Resolve a typed login name to a Roundcube user_id (pre-auth).
     *
     * Mirrors the username normalization rcmail::login() applies before it
     * looks the user up, so the value matches the stored `users.username`.
     * Exotic resolution paths used by login() (virtuser email2user) are not
     * replicated; if a deployment relies on those, sign-in simply falls back
     * to the password form.
     *
     * @return int user_id, or 0 if no matching user exists
     */
    private function resolve_user_id($typed)
    {
        $username = $typed;
        $host = rcube_utils::idn_to_ascii($this->rc->autoselect_host());

        $domain = $this->rc->config->get('username_domain');
        if (is_array($domain)) {
            $domain = $domain[$host] ?? null;
        }

        if (!empty($domain)) {
            $pos = strpos($username, '@');
            if ($pos !== false && $this->rc->config->get('username_domain_forced')) {
                $username = substr($username, 0, $pos) . '@' . $domain;
            } elseif ($pos === false) {
                $username .= '@' . $domain;
            }
        }

        $login_lc = $this->rc->config->get('login_lc', 2);
        if ($login_lc == 2 || $login_lc === true) {
            $username = mb_strtolower($username);
        } elseif ($login_lc && strpos($username, '@')) {
            [$local, $dom] = rcube_utils::explode('@', $username);
            $username = $local . '@' . mb_strtolower($dom);
        }

        if (strpos($username, '@')) {
            $username = rcube_utils::idn_to_ascii($username);
        }

        $user = rcube_user::query($username, $host);

        return $user ? (int) $user->ID : 0;
    }

    /**
     * Validate a base64/base64url string and enforce a length bound.
     */
    private function valid_b64($value, $max)
    {
        return is_string($value)
            && $value !== ''
            && strlen($value) <= $max
            && preg_match('/^[A-Za-z0-9+\/=_-]+$/', $value) === 1;
    }

    /**
     * URL-safe base64 without padding (the encoding used in WebAuthn clientData).
     */
    private function b64url($bin)
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    /**
     * The WebAuthn relying-party ID (the registrable domain). Defaults to the
     * request hostname; override with `passkey_login_rpid`.
     */
    private function rp_id()
    {
        $rpid = $this->rc->config->get('passkey_login_rpid');
        if (is_string($rpid) && $rpid !== '') {
            return $rpid;
        }
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return preg_replace('/:\d+$/', '', $host);
    }

    /**
     * Check the assertion's stated origin against the expected one(s).
     */
    private function origin_allowed($origin)
    {
        $configured = $this->rc->config->get('passkey_login_origin');
        $allowed = $configured ? (array) $configured : [$this->default_origin()];

        foreach ($allowed as $candidate) {
            if (is_string($candidate) && $origin !== '' && hash_equals($candidate, $origin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Best-effort reconstruction of this deployment's web origin.
     */
    private function default_origin()
    {
        $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) == 443)
            || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        return ($https ? 'https' : 'http') . '://' . $host;
    }

    /**
     * Wrap a base64-encoded SPKI public key as a PEM string for OpenSSL.
     */
    private function spki_to_pem($b64_spki)
    {
        $der = base64_decode((string) $b64_spki, true);
        if ($der === false || $der === '') {
            return null;
        }

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Emit a JSON response and end the request.
     *
     * @param array $data
     *
     * @return never
     */
    private function json($data)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('X-Content-Type-Options: nosniff');
        }

        echo json_encode($data);
        exit;
    }
}
