<?php
/**
 * Roundcube Password Driver for HestiaCP (polished)
 *
 * Compatible with Roundcube password plugin 1.6.x:
 *   - Class name MUST be rcube_hestia_password
 *   - Constructor MUST be zero-argument
 *   - save() accepts optional 3rd $username parameter
 *
 * Features:
 *   - Auth modes: password | hash | accesskey (access_key + secret_key)
 *   - Owner discovery via v-search-domain-owner (no returncode)
 *   - Password change via v-change-mail-account-password (returncode=yes)
 *   - Debug logging to logs/password when 'debug' => true
 *   - Human-friendly error messages mapped to translation keys (see en_US.hestia.add.inc)
 */

class rcube_hestia_password
{
    /** @var rcmail */
    private $rc;

    /** @var array */
    private $opts = [];

    /** @var bool */
    private $debug = false;

    public function __construct()
    {
        $this->rc   = rcmail::get_instance();
        $this->opts = (array) $this->rc->config->get('password_hestia', []);
        $this->debug = !empty($this->opts['debug']);
        $this->log('constructor: loaded options');
    }

    /**
     * Change password entry-point called by the Password plugin
     *
     * @param string $curpass   Current password (may be empty if confirm disabled)
     * @param string $newpass   New password
     * @param string $username  Optional username (usually email address)
     * @return int              PASSWORD_* constant code
     */
    public function save($curpass, $newpass, $username = null)
    {
        $this->log('save(): begin');

        $min = (int) ($this->opts['min_length'] ?? 0);
        if ($min > 0 && strlen($newpass) < $min) {
            return $this->error($this->tr('hestia_err_policy_short', ['min' => $min]));
        }

        $endpoint = rtrim((string) ($this->opts['endpoint'] ?? ''), '/') . '/';
        if (!$endpoint || !preg_match('#^https?://#', $endpoint)) {
            return $this->error($this->tr('hestia_err_endpoint_invalid'));
        }
        $this->log("endpoint={$endpoint}");

        // Username (prefer the provided $username from plugin, fallback to session)
        [$local, $domain, $hint] = $this->extract_identity($username);
        $this->log("identity local={$local} domain={$domain}");
        if (!$local || !$domain) {
            return $this->error($this->tr('hestia_err_identity', ['hint' => $hint]));
        }

        // 1) Owner lookup (no returncode)
        $owner = $this->api_owner_lookup($endpoint, $domain);
        $this->log("owner_lookup result=[" . ($owner ?? 'NULL') . "]");
        if (!$owner) {
            return $this->error($this->tr('hestia_err_owner_unknown', ['domain' => $domain]));
        }

        // 2) Change password (use returncode)
        $res = $this->api_call($endpoint, [
            'cmd'  => 'v-change-mail-account-password',
            'arg1' => $owner,
            'arg2' => $domain,
            'arg3' => $local,
            'arg4' => $newpass,
        ], /*use_returncode=*/true);

        if ($res['error']) {
            return $this->error($res['error']);
        }

        $body = trim((string) $res['body']);
        $this->log("change_password http={$res['http']} body=[{$body}]");

        if ($body === '0') {
            $this->log('save(): success');
            return PASSWORD_SUCCESS;
        }

        // Human-friendly message for known numeric codes, else fallback
        if ($body !== '' && ctype_digit($body)) {
            $code = (int) $body;
            $key  = $this->map_error_key($code);
            $msg  = $this->tr($key, ['code' => $code, 'domain' => $domain, 'local' => $local, 'owner' => $owner]);
            // Show a visible message for the user
            $this->rc->output->show_message($msg, 'error');
            return $this->error("Hestia error {$code}: " . $msg);
        }

        return $this->error($this->tr('hestia_err_unknown'));
    }

    // ---- Helpers ---------------------------------------------------------

    /** Translate helper within the password plugin domain */
    private function tr($name, array $vars = [])
    {
        // Ask Roundcube to translate using the password plugin domain
        $msg = $this->rc->gettext(['name' => $name, 'domain' => 'password']);
        if (!$msg || $msg === $name) {
            // Fallback to a simple English message for safety
            $fallbacks = [
                'hestia_err_policy_short'  => 'Password too short (minimum length: %min).',
                'hestia_err_endpoint_invalid' => 'Hestia endpoint is not configured or invalid.',
                'hestia_err_identity'      => 'Cannot parse username into local@domain%hint.',
                'hestia_err_owner_unknown' => 'Unable to determine domain owner for %domain.',
                'hestia_err_unknown'       => 'Unknown error from Hestia API.',
                'hestia_err_default'       => 'Hestia returned error code %code.',
                'hestia_err_1'             => 'Not enough arguments provided.',
                'hestia_err_2'             => 'Invalid object or argument.',
                'hestia_err_3'             => 'Object does not exist (mailbox or domain not found).',
                'hestia_err_4'             => 'Object already exists.',
                'hestia_err_5'             => 'Object is already suspended.',
                'hestia_err_6'             => 'Object is already unsuspended.',
                'hestia_err_7'             => 'Object in use; cannot modify.',
                'hestia_err_8'             => 'Operation blocked by package limits.',
                'hestia_err_9'             => 'Wrong or invalid password.',
                'hestia_err_10'            => 'Permission denied by Hestia (API identity lacks rights or IP not allowed).',
                'hestia_err_11'            => 'Feature disabled by system policy.',
                'hestia_err_12'            => 'Parsing error.',
                'hestia_err_13'            => 'Disk error.',
                'hestia_err_14'            => 'Server load too high.',
                'hestia_err_15'            => 'Connection error.',
                'hestia_err_16'            => 'FTP error.',
                'hestia_err_17'            => 'Database error.',
                'hestia_err_18'            => 'RRD error.',
                'hestia_err_19'            => 'Update error.',
                'hestia_err_20'            => 'Restart error.',
            ];
            $msg = $fallbacks[$name] ?? $name;
        }
        // Simple var replacement
        foreach ($vars as $k => $v) {
            $msg = str_replace('%' . $k, (string)$v, $msg);
        }
        return $msg;
    }

    /** Map Hestia/Vesta numeric error codes to translation keys */
    private function map_error_key($code)
    {
        $known = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20];
        if (in_array($code, $known, true)) {
            return 'hestia_err_' . $code;
        }
        return 'hestia_err_default';
    }

    /**
     * @param ?string $username Prefer explicit username, else use session user
     */
    private function extract_identity($username = null)
    {
        $u   = $username ?: $this->rc->get_user_name();
        $cfg = (bool) ($this->opts['username_is_email'] ?? true);

        if ($cfg) {
            $at = strrpos($u, '@');
            if ($at !== false) {
                $local  = substr($u, 0, $at);
                $domain = substr($u, $at + 1);
                return [$local, $domain, ''];
            }
            return [null, null, ' (expected email-like login)'];
        }

        return [null, null, ''];
    }

    private function api_owner_lookup($endpoint, $domain)
    {
        if (!empty($this->opts['force_owner'])) {
            $this->log("force_owner is set: {$this->opts['force_owner']}");
            return $this->opts['force_owner'];
        }

        $res = $this->api_call($endpoint, [
            'cmd'  => 'v-search-domain-owner',
            'arg1' => $domain,
            'arg2' => 'mail',
        ], /*use_returncode=*/false);

        if ($res['error']) {
            $this->log("owner_lookup error: {$res['error']} http={$res['http']} body=[" . (string)$res['body'] . "]");
            return null;
        }

        $owner = trim((string) $res['body']);
        if ($owner === '' || ctype_digit($owner)) {
            $this->log("owner_lookup suspicious body: [{$owner}]");
            return null;
        }
        return $owner;
    }

    /**
     * @param string $endpoint
     * @param array  $params
     * @param bool   $use_returncode When true, include returncode=yes to get numeric codes.
     */
    private function api_call($endpoint, array $params, $use_returncode = true)
    {
        try {
            $post = $this->build_auth_fields((array) ($this->opts['auth'] ?? []));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'body' => null, 'http' => 0];
        }

        if ($use_returncode) {
            $post['returncode'] = 'yes';
        }

        foreach ($params as $k => $v) {
            $post[$k] = $v;
        }

        $this->log('api_call POST=' . json_encode($this->mask_sensitive($post)));

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($post, '', '&'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) ($this->opts['timeout'] ?? 10),
            CURLOPT_HEADER         => false,
        ]);

        $verify_peer = (bool) ($this->opts['verify_peer'] ?? true);
        $verify_host = (bool) ($this->opts['verify_host'] ?? true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify_peer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verify_host ? 2 : 0);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $this->log("api_call transport error: {$err} http={$http}");
            return ['error' => "Transport error: {$err}", 'body' => null, 'http' => $http];
        }

        $this->log("api_call http={$http} body=[" . (string)$body . "]");
        if ($http < 200 || $http >= 300) {
            return ['error' => "HTTP {$http} from Hestia API", 'body' => $body, 'http' => $http];
        }

        return ['error' => null, 'body' => $body, 'http' => $http];
    }

    private function build_auth_fields(array $auth)
    {
        $type = strtolower((string) ($auth['type'] ?? ''));

        if ($type === 'password') {
            $user = $auth['user'] ?? '';
            $pass = $auth['pass'] ?? '';
            if ($user === '' || $pass === '') {
                throw new \Exception('Hestia legacy auth requires user + pass.');
            }
            return ['user' => $user, 'password' => $pass];
        }

        if ($type === 'hash') {
            $user = $auth['user'] ?? '';
            $hash = $auth['hash'] ?? '';
            if ($user === '' || $hash === '') {
                throw new \Exception('Hestia legacy auth requires user + hash.');
            }
            return ['user' => $user, 'hash' => $hash];
        }

        if ($type === 'accesskey') {
            $ak = $auth['access_key'] ?? '';
            $sk = $auth['secret_key'] ?? '';
            if ($ak === '' || $sk === '') {
                throw new \Exception('Hestia access_key/secret_key not configured.');
            }
            return ['access_key' => $ak, 'secret_key' => $sk];
        }

        throw new \Exception('Unsupported Hestia auth type. Use password, hash, or accesskey.');
    }

    private function mask_sensitive(array $post)
    {
        $mask = $post;
        foreach (['password','pass','hash','secret_key','access_key','arg4'] as $k) {
            if (isset($mask[$k])) $mask[$k] = '***';
        }
        return $mask;
    }

    private function log($msg)
    {
        if ($this->debug) {
            rcube::write_log('password', '[password-hestia] ' . $msg);
        }
    }

    private function error($msg)
    {
        $this->log('ERROR: ' . $msg);
        rcube::raise_error(['code' => 500, 'type' => 'php', 'message' => "[password-hestia] {$msg}"], true, false);
        return PASSWORD_ERROR;
    }
}
