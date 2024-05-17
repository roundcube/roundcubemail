<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | CONTENTS:                                                             |
 |   Roundcube OAuth2 utilities                                          |
 |   @see https://datatracker.ietf.org/doc/html/rfc6749                  |
 |   please note that it implements Oauth2 and OpenID Connect extension  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\MessageFormatter;

/**
 * Roundcube OAuth2 utilities
 */
class rcmail_oauth
{
    public const TOKEN_REFRESHED = 1;
    public const TOKEN_STILL_VALID = 0;
    public const TOKEN_REFRESH_FAILED = -1;
    public const TOKEN_NOT_FOUND = -2;
    public const TOKEN_ERROR = -3;
    public const TOKEN_REFRESH_EXPIRED = -4;
    public const TOKEN_REVOKED = -5;
    public const TOKEN_COMPROMISED = -6;

    public const JWKS_CACHE_TTL = 30; // TTL for JWKS (in seconds)

    /** @var string XOAUTH2, OAUTHBEAER, OAUTH=choose the supported method */
    protected $auth_type = 'OAUTH';

    /** @var rcmail */
    protected $rcmail;

    /** @var array */
    protected $options = [];

    /** @var array */
    protected $jwks;

    /** @var ?string */
    protected $last_error;

    /** @var bool */
    protected $no_redirect = false;

    /** @var ?rcube_cache */
    protected $cache;

    /** @var HttpClient */
    protected $http_client;

    /** @var string */
    protected $logout_redirect_url;

    /** @var ?array parameters used during the login phase */
    protected $login_phase;

    /** @var array list of allowed keys in user_create_map (note that user and host are protected) */
    protected static $user_create_allowed_keys = ['user_name', 'user_email', 'language'];

    /** @var array map of .well-known entries to config (discovery URI) */
    protected static $config_mapper = [
        'issuer' => 'issuer',
        'authorization_endpoint' => 'auth_uri',
        'token_endpoint' => 'token_uri',
        'userinfo_endpoint' => 'identity_uri',
        'end_session_endpoint' => 'logout_uri',
        'jwks_uri' => 'jwks_uri',
    ];

    /** @var array map PKCE code_challenge_method to hash method */
    protected static $pkce_mapper = [
        'S256' => 'sha256',
        // plain method is not implemented: @see RFC7636 4.2: "If the client is capable of using "S256", it MUST use "S256"
    ];

    /** @var ?rcmail_oauth */
    protected static $instance;

    /**
     * Singleton factory
     *
     * @return rcmail_oauth The one and only instance
     */
    public static function get_instance($options = [])
    {
        if (!self::$instance) {
            self::$instance = new self($options);
            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * Helper to log oauth
     */
    private function logger($level, $message): void
    {
        $token = $this->login_phase['token'] ?? $_SESSION['oauth_token'] ?? [];
        $sub = $token['identity']['sub'] ?? '-';
        $ses = $token['session_state'] ?? '-';
        rcube::write_log('oauth', sprintf('%s: [ip=%s sub=%s ses=%s] %s', $level, rcube_utils::remote_ip(), $sub, $ses, $message));
    }

    /**
     * Helper to log oauth debug message (only if `oauth_debug`is true)
     *
     * XXX for debug only, please use rcube::raise_error to raise errors in a centralized place
     */
    public function log_debug(...$args): void
    {
        if ($this->options['debug']) {
            $this->logger('DEBUG', sprintf(...$args));
        }
    }

    /**
     * Object constructor
     *
     * @param array $options Config options:
     */
    public function __construct($options = [])
    {
        $this->rcmail = rcmail::get_instance();

        // use `oauth_cache` to define engine & `oauth_cache_ttl` to define ttl (default 1d))
        $this->cache = $this->rcmail->get_cache_shared('oauth');

        $this->options = (array) $options + [
            'provider' => $this->rcmail->config->get('oauth_provider'),
            'provider_name' => $this->rcmail->config->get('oauth_provider_name', 'OAuth'),
            'auth_uri' => $this->rcmail->config->get('oauth_auth_uri'),
            'config_uri' => $this->rcmail->config->get('oauth_config_uri'),
            'issuer' => $this->rcmail->config->get('oauth_issuer'),
            'logout_uri' => $this->rcmail->config->get('oauth_logout_uri'),
            'token_uri' => $this->rcmail->config->get('oauth_token_uri'),
            'jwks_uri' => $this->rcmail->config->get('oauth_jwks_uri'),
            'client_id' => $this->rcmail->config->get('oauth_client_id'),
            'client_secret' => $this->rcmail->config->get('oauth_client_secret'),
            'identity_uri' => $this->rcmail->config->get('oauth_identity_uri'),
            'identity_fields' => $this->rcmail->config->get('oauth_identity_fields', ['email']),
            'user_create_map' => $this->rcmail->config->get('oauth_user_create_map', [
                // rc key => OIDC Claim @see: https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims )
                'user_name' => ['name'],
                'user_email' => ['email'],
                'language' => ['locale'],
            ]),

            'scope' => $this->rcmail->config->get('oauth_scope', ''),
            'timeout' => $this->rcmail->config->get('oauth_timeout', 10),
            'verify_peer' => $this->rcmail->config->get('oauth_verify_peer', true),
            'auth_parameters' => $this->rcmail->config->get('oauth_auth_parameters', []),
            'login_redirect' => $this->rcmail->config->get('oauth_login_redirect', false),
            'pkce' => $this->rcmail->config->get('oauth_pkce', 'S256'),
            'debug' => $this->rcmail->config->get('oauth_debug', false),
        ];

        // http_options will be used in test phase to add a mock
        if (!isset($options['http_options'])) {
            $options['http_options'] = [];
        }

        // sanity check on PKCE value
        if ($this->options['pkce'] && !array_key_exists($this->options['pkce'], self::$pkce_mapper)) {
            // will stops on error
            rcube::raise_error("PKCE method not supported (oauth_pkce='{$this->options['pkce']}')", true, true);
        }

        // sanity check that configuration user_create_map contains only allowed keys
        foreach ($this->options['user_create_map'] as $key => $ignored) {
            if (!in_array($key, self::$user_create_allowed_keys)) {
                // will stops on error
                rcube::raise_error("Use of key `{$key}` in `oauth_user_create_map` is not allowed", true, true);
            }
        }

        // prepare a http client with the correct options
        $this->http_client = $this->rcmail->get_http_client((array) $options['http_options'] + [
            'timeout' => $this->options['timeout'],
            'verify' => $this->options['verify_peer'],
        ]);
    }

    /**
     * discover .well-known/oidc-configuration according config_uri and complete options
     *
     * use cache if defined
     *
     * @see https://datatracker.ietf.org/doc/html/rfc8414
     */
    protected function discover(): void
    {
        $config_uri = $this->options['config_uri'];
        if (empty($config_uri)) {
            return;
        }
        $key_cache = 'discovery.' . md5($config_uri);

        try {
            $data = $this->cache ? $this->cache->get($key_cache) : null;
            if ($data === null) {
                // Caveat: if .well-known URL is not answering it will break login display (will not display the button)
                $response = $this->http_client->get($config_uri);
                $data = json_decode($response->getBody(), true);

                // sanity check
                if (!isset($data['issuer'])) {
                    throw new RuntimeException('incorrect response from %s', $config_uri);
                }

                // cache answer
                if ($this->cache) {
                    $this->cache->set($key_cache, $data);
                }
            }

            // map discovery to our options
            foreach (self::$config_mapper as $config_key => $options_key) {
                if (empty($data[$config_key])) {
                    rcube::raise_error("Key {$config_key} not found in answer of {$config_uri}", true);
                } else {
                    $this->options[$options_key] = $data[$config_key];
                }
            }

            // check if pkce method is supported by this server
            if ($this->options['pkce'] && isset($data['code_challenge_methods_supported']) && is_array($data['code_challenge_methods_supported'])) {
                if (!in_array($this->options['pkce'], $data['code_challenge_methods_supported'])) {
                    rcube::raise_error("OAuth server does not support this PKCE method (oauth_pkce='{$this->options['pkce']}')", true);
                }
            }
        } catch (Exception $e) {
            rcube::raise_error("Error fetching {$config_uri} : {$e->getMessage()}", true);
        }
    }

    /**
     * Fetch JWKS certificates (use cache if active)
     */
    protected function fetch_jwks(): void
    {
        if (!$this->options['jwks_uri']) {
            // not activated
            return;
        }

        if ($this->jwks !== null) {
            // already defined
            return;
        }

        $jwks_uri = $this->options['jwks_uri'];
        $key_cache = 'jwks.' . md5($jwks_uri);
        $this->jwks = $this->cache ? $this->cache->get($key_cache) : null;

        if ($this->jwks !== null && $this->jwks['expires'] > time()) {
            return;
        }

        // not in cache, fetch json web key set
        $response = $this->http_client->get($jwks_uri);
        $this->jwks = json_decode($response->getBody(), true);

        // sanity check
        if (!isset($this->jwks['keys'])) {
            $this->log_debug('incorrect jwks response from %s', $jwks_uri);
        } elseif ($this->cache) {
            // this is a hack because we cannot specify the TTL in the shared_cache
            // and cache must not be too high as the Identity Provider can rotate it's keys
            $this->jwks['expires'] = time() + self::JWKS_CACHE_TTL;
            $this->cache->set($key_cache, $this->jwks);
        }
    }

    /**
     * Initialize this instance
     */
    public function init(): void
    {
        // important must be called before is_enabled()
        $this->discover();

        if (!$this->is_enabled()) {
            return;
        }

        if ($this->cache === null) {
            $this->log_debug('cache is disabled');
        }

        // subscribe to storage and smtp init events
        $this->rcmail->plugins->register_hook('loginform_content', [$this, 'loginform_content']);
        $this->rcmail->plugins->register_hook('startup', [$this, 'startup']);

        $this->rcmail->plugins->register_hook('storage_init', [$this, 'storage_init']);
        $this->rcmail->plugins->register_hook('smtp_connect', [$this, 'smtp_connect']);
        $this->rcmail->plugins->register_hook('managesieve_connect', [$this, 'managesieve_connect']);

        $this->rcmail->plugins->register_hook('authenticate', [$this, 'authenticate']);
        $this->rcmail->plugins->register_hook('login_after', [$this, 'login_after']);
        $this->rcmail->plugins->register_hook('login_failed', [$this, 'login_failed']);
        $this->rcmail->plugins->register_hook('user_create', [$this, 'user_create']);
        $this->rcmail->plugins->register_hook('logout_after', [$this, 'logout_after']);
        $this->rcmail->plugins->register_hook('unauthenticated', [$this, 'unauthenticated']);

        $this->rcmail->plugins->register_hook('refresh', [$this, 'refresh']);
    }

    /**
     * Check if OAuth is generally enabled in config
     *
     * @return bool
     */
    public function is_enabled()
    {
        return !empty($this->options['provider'])
            && !empty($this->options['token_uri'])
            && !empty($this->options['client_id']);
    }

    /**
     * Getter for the last error occurred
     *
     * @return mixed
     */
    public function get_last_error()
    {
        return $this->last_error;
    }

    /**
     * Callback for `loginform_content` hook
     *
     * Append Oauth button on login page if defined (this is a hook)
     * can also hide default user/pass form if flag oauth_login_redirect is true
     */
    public function loginform_content(array $form_content)
    {
        // hide login form fields when `oauth_login_redirect` is configured
        if ($this->options['login_redirect']) {
            $form_content['hidden'] = [];
            $form_content['inputs'] = [];
            $form_content['buttons'] = [];
        }

        $link_attr = [
            'href' => $this->rcmail->url(['action' => 'oauth']),
            'id' => 'rcmloginoauth',
            'class' => 'button oauth ' . $this->options['provider'],
        ];

        $provider = $this->options['provider_name'];
        $button = html::a($link_attr, $this->rcmail->gettext(['name' => 'oauthlogin', 'vars' => ['provider' => $provider]]));

        $form_content['buttons']['oauthlogin'] = ['outterclass' => 'oauthlogin', 'content' => $button];

        return $form_content;
    }

    // TODO: move it into an helper class
    protected static function base64url_decode($encoded)
    {
        return base64_decode(strtr($encoded, '-_', '+/'), true);
    }

    protected static function base64url_encode($payload)
    {
        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    /**
     * Helper method to decode a JWT and check payload OIDC consistency
     *
     * @param string $jwt
     *
     * @return array Hash array with decoded body
     */
    public function jwt_decode($jwt)
    {
        $body = [];

        [$headb64, $bodyb64, $cryptob64] = explode('.', $jwt);

        $header = json_decode(static::base64url_decode($headb64), true);
        $body = json_decode(static::base64url_decode($bodyb64), true);
        // $crypto = static::base64url_decode($cryptob64);

        if ($this->options['jwks_uri']) {
            // jwks_uri defined, will check JWT signature

            $this->fetch_jwks();

            $kid = $header['kid'];
            $alg = $header['alg'];

            $jwk = null;

            foreach ($this->jwks['keys'] as $current_jwk) {
                if ($current_jwk['kid'] === $kid) {
                    $jwk = $current_jwk;
                    break;
                }
            }

            if ($jwk === null) {
                throw new RuntimeException('JWS key to verify JWT not found');
            }

            // TODO: check alg. matches
            // TODO should check signature, note will use https://github.com/firebase/php-jwt later as it requires ^php7.4
        }

        // FIXME depends on body type: ID, Logout, Bearer, Refresh,
        if (isset($body['azp']) && $body['azp'] !== $this->options['client_id']) {
            throw new RuntimeException('Failed to validate JWT: invalid azp value');
        } elseif (isset($body['aud']) && !in_array($this->options['client_id'], (array) $body['aud'])) {
            throw new RuntimeException('Failed to validate JWT: invalid aud value');
        } elseif (!isset($body['azp']) && !isset($body['aud'])) {
            throw new RuntimeException('Failed to validate JWT: missing aud/azp value');
        }

        // if defined in parameters, check that issuer match
        if (isset($this->options['issuer']) && $body['iss'] !== $this->options['issuer']) {
            throw new RuntimeException('Failed to validate JWT: issuer mismatch');
        }

        // check that token is not an outdated message
        if (isset($body['exp']) && (time() > $body['exp'])) {
            throw new RuntimeException('Failed to validate JWT: expired message');
        }

        $this->log_debug('jwt: %s', json_encode($body));

        return $body;
    }

    /**
     * Compose a fully qualified redirect URI for auth requests
     *
     * @return string
     */
    public function get_redirect_uri()
    {
        $url = $this->rcmail->url([], true, true);

        // rewrite redirect URL to not contain query parameters because some providers do not support this
        $url = preg_replace('/\?.*/', '', $url);

        return slashify($url) . 'index.php/login/oauth';
    }

    /**
     * Login action: redirect to `oauth_auth_uri`
     *
     * Authorization Code Request
     *
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.1
     */
    public function login_redirect(): void
    {
        if (empty($this->options['auth_uri']) || empty($this->options['client_id'])) {
            // log error about missing config options
            rcube::raise_error("Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'", true);
            return;
        }

        // create a secret string (OAuth security)
        $_SESSION['oauth_state'] = rcube_utils::random_bytes(12);

        // create a nonce (OIDC security)
        $_SESSION['oauth_nonce'] = rcube_utils::random_bytes(32);

        // compose full oauth login uri
        $query = [
            'response_type' => 'code',
            'client_id' => $this->options['client_id'],
            'scope' => $this->options['scope'],
            'redirect_uri' => $this->get_redirect_uri(),
            'state' => $_SESSION['oauth_state'],
            'nonce' => $_SESSION['oauth_nonce'],
        ];

        // implementation of PKCE @see: rfc7636
        if ($this->options['pkce']) {
            $code_verifier = rcube_utils::random_bytes(64);
            $code_challenge_method = $this->options['pkce'];
            $hash_method = self::$pkce_mapper[$code_challenge_method];

            // do not store it in clear, do not want it to be readable
            $_SESSION['oauth_code_verifier'] = $this->rcmail->encrypt($code_verifier);

            $query += [
                'code_challenge_method' => $code_challenge_method,
                'code_challenge' => self::base64url_encode(hash($hash_method, $code_verifier, true)),
            ];
        }

        $this->log_debug("requesting authorization code via a redirect to %s with scope='%s' and pkce method=%s",
            $this->options['auth_uri'], $this->options['scope'], $this->options['pkce']);

        $delimiter = strpos($this->options['auth_uri'], '?') > 0 ? '&' : '?';
        $url = $this->options['auth_uri'] . $delimiter . http_build_query($query + (array) $this->options['auth_parameters']);

        $this->last_error = null; // clean last error
        $this->rcmail->output->redirect($url);  // exit
    }

    /**
     * Call ODIC to get identity for an given authorization
     *
     * @param string $authorization the Bearer authorization
     *
     * @return array|null The identity
     *
     * @see: https://openid.net/specs/openid-connect-core-1_0.html#UserInfo
     */
    protected function fetch_userinfo($authorization)
    {
        if (empty($this->options['identity_uri'])) {
            // service not available
            return null;
        }

        $identity_response = $this->http_client->get($this->options['identity_uri'], [
            'headers' => [
                'Authorization' => $authorization,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($identity_response->getBody(), true);
    }

    /**
     * Request access token with auth code returned from oauth login
     *
     * @param string $auth_code
     * @param string $state
     *
     * @return bool true on access token, false on error
     *
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.3
     */
    public function request_access_token($auth_code, $state = null)
    {
        $oauth_token_uri = $this->options['token_uri'];
        $oauth_client_id = $this->options['client_id'];
        $oauth_client_secret = $this->options['client_secret'];

        try {
            // sanity check
            if (empty($oauth_token_uri) || empty($oauth_client_id) || empty($oauth_client_secret)) {
                throw new RuntimeException("Missing required OAuth config options 'oauth_token_uri', 'oauth_client_id', 'oauth_client_secret'");
            }

            // validate state parameter against $_SESSION['oauth_state']
            if (!isset($_SESSION['oauth_state']) || ($_SESSION['oauth_state'] !== $state)) {
                throw new RuntimeException('state parameter mismatch');
            }

            $this->rcmail->session->remove('oauth_state');

            $this->log_debug('requesting a grant_type=authorization_code to %s', $oauth_token_uri);

            $form = [
                'grant_type' => 'authorization_code',
                'code' => $auth_code,
                'client_id' => $oauth_client_id,
                'client_secret' => $oauth_client_secret,
                'redirect_uri' => $this->get_redirect_uri(),
            ];

            if ($this->options['pkce']) {
                $form['code_verifier'] = $this->rcmail->decrypt($_SESSION['oauth_code_verifier']);
            }

            $response = $this->http_client->post($oauth_token_uri, ['form_params' => $form]);
            $data = json_decode($response->getBody(), true);

            [$authorization, $identity] = $this->parse_tokens('authorization_code', $data);

            $username = null;

            if ($identity) {
                // note that id_token values depend on scopes
                foreach ($this->options['identity_fields'] as $field) {
                    if (isset($identity[$field])) {
                        $username = $identity[$field];
                        break;
                    }
                }
            }

            // request user identity (email)
            if (empty($username)) {
                $fetched_identity = $this->fetch_userinfo($authorization);

                $this->log_debug('fetched identity: %s', json_encode($fetched_identity, true));

                if (!empty($fetched_identity)) {
                    $identity = $fetched_identity;

                    foreach ($this->options['identity_fields'] as $field) {
                        if (isset($identity[$field])) {
                            $username = $identity[$field];
                            break;
                        }
                    }
                }
            }

            // store the full identity (usually contains `sub`, `name`, `preferred_username`, `given_name`, `family_name`, `locale`, `email`)
            $data['identity'] = $identity;

            // the username
            $data['username'] = $username;

            $this->mask_auth_data($data);

            $this->rcmail->plugins->exec_hook('oauth_login', array_merge($data, [
                'username' => $username,
                'identity' => $identity,
            ]));

            $this->last_error = null; // clean last error

            // return auth data
            $this->login_phase = [
                'username' => $username,
                'authorization' => $authorization, // the payload to authentificate through IMAP, SMTP, SIEVE .. servers
                'token' => $data,
                'nonce' => $_SESSION['oauth_nonce'],
            ];

            if ($this->options['pkce']) {
                // store crypted code_verifier because session is going to be killed
                $this->login_phase['code_verifier'] = $_SESSION['oauth_code_verifier'];
            }

            return true;
        } catch (RequestException $e) {
            $this->last_error = 'OAuth token request failed: ' . $e->getMessage();
            $this->no_redirect = true;
            $formatter = new MessageFormatter();

            rcube::raise_error($this->last_error . '; ' . $formatter->format($e->getRequest(), $e->getResponse()), true);
        } catch (Exception $e) {
            $this->last_error = 'OAuth token request failed: ' . $e->getMessage();
            $this->no_redirect = true;

            rcube::raise_error($this->last_error, true);
        }

        return false;
    }

    /**
     * Obtain a new access token using the refresh_token grant type
     *
     * If successful, this will update the `oauth_token` entry in
     * session data.
     *
     * @return array|false Updated authorization data
     *
     * @see https://datatracker.ietf.org/doc/html/rfc6749#section-6
     */
    public function refresh_access_token(array $token)
    {
        $oauth_token_uri = $this->options['token_uri'];
        $oauth_client_id = $this->options['client_id'];
        $oauth_client_secret = $this->options['client_secret'];

        // send token request to get a real access token for the given auth code
        try {
            $this->log_debug('requesting a grant_type=refresh_token to %s', $oauth_token_uri);

            $form = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->rcmail->decrypt($token['refresh_token']),
                'client_id' => $oauth_client_id,
                'client_secret' => $oauth_client_secret,
            ];

            if ($this->options['pkce']) {
                $form['code_verifier'] = $this->rcmail->decrypt($_SESSION['oauth_code_verifier']);
            }

            $response = $this->http_client->post($oauth_token_uri, ['form_params' => $form]);
            $data = json_decode($response->getBody(), true);

            [$authorization, $identity] = $this->parse_tokens('refresh_token', $data, $token);

            // update access token stored as password
            $_SESSION['password'] = $this->rcmail->encrypt($authorization);

            $this->mask_auth_data($data);

            // update session data
            $_SESSION['oauth_token'] = array_merge($token, $data);

            $this->rcmail->plugins->exec_hook('oauth_refresh_token', $data);

            $this->last_error = null; // clean last error

            return [
                'token' => $data,
                'authorization' => $authorization,
            ];
        } catch (RequestException $e) {
            $this->last_error = 'OAuth refresh token request failed: ' . $e->getMessage();
            $formatter = new MessageFormatter();
            rcube::raise_error($this->last_error . '; ' . $formatter->format($e->getRequest(), $e->getResponse()), true);

            // refrehsing token failed, mark session as expired
            if ($e->getCode() >= 400 && $e->getCode() < 500) {
                $this->rcmail->kill_session();
            }
        } catch (Exception $e) {
            $this->last_error = 'OAuth refresh token request failed: ' . $e->getMessage();
            rcube::raise_error($this->last_error, true);
        }

        return false;
    }

    /**
     * Store a token revokation for a given sub (can be used by the backchannel logout)
     *
     * Warning: cache TTL should be at least > refresh_token frequency
     *
     * @param string $sub the sub of the identity
     */
    public function schedule_token_revocation($sub): void
    {
        if ($this->cache === null) {
            rcube::raise_error('Received a token revocation request, you must activate `oauth_cache` to enable this feature', true);
            return;
        }
        $this->cache->set("revoke_{$sub}", time());
    }

    /**
     * Check is a token has been revoked (condition: sub match & token is older than the request timestamp)
     *
     * @param array $token the token to verify
     *
     * @return bool true if token revoked
     */
    protected function is_token_revoked($token)
    {
        if ($this->cache === null) {
            // oops cache not enabled
            return false;
        }

        $revoked_time = $this->cache->get("revoke_{$token['identity']['sub']}");

        if (!$revoked_time) {
            return false;
        }

        if ($token['created_at'] < $revoked_time) {
            return true;
        }

        return false;
    }

    /**
     * Parse and update the token from a grant request
     *
     * @param string $grant_type    The request type
     * @param array  $data          The payload from the request (will be updated)
     * @param array  $previous_data The data from a previous request
     *
     * @return array Token properties:
     *               1st element: the bearer authorization to use on different transports
     *               2nd element: the decoded identity
     */
    protected function parse_tokens($grant_type, &$data, $previous_data = null)
    {
        // TODO move it into to log_info ?
        $this->log_debug('received tokens from a grant request %s: session: %s with scope %s, '
            . 'access_token type %s exp in %ss, refresh_token exp in %ss, id_token present: %s, not-before-policy: %s',
            $grant_type,
            $data['session_state'], $data['scope'],
            $data['token_type'], $data['expires_in'],
            $data['refresh_expires_in'],
            isset($data['id_token']),
            $data['not-before-policy'] ?? null
        );

        if (is_array($previous_data)) {
            $this->log_debug(
                'changes: session_state: %s, access_token: %s, refresh_token: %s, id_token: %s',
                isset($previous_data['session_state']) ? $previous_data['session_state'] !== $data['session_state'] : null,
                isset($previous_data['access_token']) ? $previous_data['access_token'] !== $data['access_token'] : null,
                isset($previous_data['refresh_token']) ? $previous_data['refresh_token'] !== $data['refresh_token'] : null,
                isset($previous_data['id_token']) ? $previous_data['id_token'] !== $data['id_token'] : null,
            );
        }

        // sanity check, check that payload correctly contains access_token
        if (empty($data['access_token'])) {
            throw new RuntimeException('access_token missing ins answer, error from server');
        }

        // sanity check, check that payload correctly contains access_token
        if (empty($data['refresh_token'])) {
            throw new RuntimeException('refresh_token missing ins answer, error from server');
        }

        // (> 0, it means that all token generated before this timestamp date are compromisd and that we need to download a new version of JWKS)
        if (!empty($data['not-before-policy']) && $data['not-before-policy'] > 0) {
            $this->log_debug('all tokens generated before %s timestmp are compromised', $data['not-before-policy']);
        }

        // please note that id_token / identity may have changed, could be interesting to grab it and refresh values, right now it is not used
        // decode JWT id_token if provided
        $identity = null;
        if (!empty($data['id_token'])) {
            $identity = $this->jwt_decode($data['id_token']);

            // sanity check, ensure that the identity have the same nonce
            if (!isset($identity['nonce']) || $identity['nonce'] !== $_SESSION['oauth_nonce']) {
                throw new RuntimeException("identity's nonce mismatch");
            }
        }

        // creation time. Information also present in JWT, but it is faster here
        $data['created_at'] = time();

        $refresh_interval = $this->rcmail->config->get('refresh_interval');

        if ($data['expires_in'] <= $refresh_interval) {
            rcube::raise_error(sprintf('Warning token expiration (%s) will expire before the refresh_interval (%s)', $data['expires_in'], $refresh_interval), true);
            // note: remove 10 sec by security (avoid tangent issues)
            $data['expires'] = time() + $data['expires_in'] - 10;
        } else {
            // try to request a refresh before it's too late according refesh interval
            // note: remove 10 sec by security (avoid tangent issues)
            $data['expires'] = time() + $data['expires_in'] - $refresh_interval - 10;
        }

        $data['refresh_expires'] = time() + $data['refresh_expires_in'];

        if (strcasecmp($data['token_type'], 'Bearer') == 0) {
            // always normalize Bearer (uppercase then lower case)
            $authorization = sprintf('Bearer %s', $data['access_token']);
        } else {
            // unknown token type, do not alter it
            $authorization = sprintf('%s %s', $data['token_type'], $data['access_token']);
        }

        return [$authorization, $identity];
    }

    /**
     * Modify some properties of the received auth response
     *
     * @param array $data
     */
    protected function mask_auth_data(&$data): void
    {
        // remove by security access_token as it is crypted in $_SESSION['password']
        unset($data['access_token']);

        // encrypt refresh token if provided
        if (isset($data['refresh_token'])) {
            $data['refresh_token'] = $this->rcmail->encrypt($data['refresh_token']);
        }
    }

    /**
     * Check the given access token data if still valid
     *
     * ... and attempt to refresh if possible.
     *
     * @param array $token OAuth token
     *
     * @return int
     */
    protected function check_token_validity($token)
    {
        if (!isset($token['refresh_token'])) {
            return self::TOKEN_NOT_FOUND;
        }

        if ($this->is_token_revoked($token)) {
            $this->log_debug('abort, token for sub %s has been revoked', $token['identity']['sub']);
            // in a such case, we are blocked, can only kill session
            $this->rcmail->kill_session();
            return self::TOKEN_REVOKED;
        }

        if ($token['expires'] > time()) {
            return self::TOKEN_STILL_VALID;
        }

        if (isset($token['refresh_expires']) && $token['refresh_expires'] < time()) {
            $this->log_debug('abort, reresh token has expired');
            // in a such case, we are blocked, can only kill session
            $this->rcmail->kill_session();
            return self::TOKEN_REFRESH_EXPIRED;
        }

        if (!empty($this->last_error)) {
            // TODO: challenge this part, what about transcient errors ?
            $this->log_debug('abort, got an previous error %s', $this->last_error);
            return self::TOKEN_ERROR;
        }

        if ($this->refresh_access_token($token) === false) {
            // FIXME: can have 2 kind of errors: transcient (can retry) or non recovreable error
            // currently it's up to refresh_access_token to kill_session is necessary
            $this->log_debug('token refresh failed: %s', $this->last_error);
            return self::TOKEN_REFRESH_FAILED;
        }

        return self::TOKEN_REFRESHED;
    }

    /**
     * Callback for 'refresh' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function refresh($options)
    {
        if (isset($_SESSION['oauth_token'])) {
            $this->check_token_validity($_SESSION['oauth_token']);
        }

        return $options;
    }

    /**
     * Callback for 'storage_init' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function storage_init($options)
    {
        if ($options['driver'] !== 'imap') {
            return $options;
        }

        if ($this->login_phase) {
            $options['auth_type'] = $this->auth_type;
        } elseif (isset($_SESSION['oauth_token'])) {
            if ($this->check_token_validity($_SESSION['oauth_token']) === self::TOKEN_REFRESHED) {
                $options['password'] = $this->rcmail->decrypt($_SESSION['password']);
            }
            $options['auth_type'] = $this->auth_type;
        }

        return $options;
    }

    /**
     * Callback for 'smtp_connect' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function smtp_connect($options)
    {
        $smtp_user = $options['smtp_user'];
        $smtp_pass = $options['smtp_pass'];

        // skip XOAUTH2 authorization, if indicated
        if (($smtp_user == '') || ($smtp_pass == '')) {
            return $options;
        }

        if (isset($_SESSION['oauth_token'])) {
            // check token validity
            $this->check_token_validity($_SESSION['oauth_token']);

            $options['smtp_user'] = '%u';
            $options['smtp_pass'] = '%p';
            $options['smtp_auth_type'] = $this->auth_type;
        }

        return $options;
    }

    /**
     * Callback for 'managesieve_connect' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function managesieve_connect($options)
    {
        if (isset($_SESSION['oauth_token'])) {
            // check token validity
            $this->check_token_validity($_SESSION['oauth_token']);
            $options['auth_type'] = $this->auth_type;
        }

        return $options;
    }

    /**
     * Callback for 'authenticate' hook
     *
     * @param array $options
     *
     * @return array the authenticate parameters
     */
    public function authenticate($options)
    {
        if (!$this->login_phase) {
            return $options;
        }

        $options['user'] = $this->login_phase['username'];
        $options['pass'] = $this->login_phase['authorization'];
        $this->rcmail->config->set('login_password_maxlen', strlen($options['pass']));

        $this->log_debug('calling authenticate for user %s', $options['user']);

        return $options;
    }

    /**
     * Callback for 'login_after' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function login_after($options)
    {
        if (!$this->login_phase) {
            return $options;
        }

        // store important data to new freshly created session
        $_SESSION['oauth_token'] = $this->login_phase['token'];
        $_SESSION['oauth_nonce'] = $this->login_phase['nonce'];
        if ($this->options['pkce']) {
            $_SESSION['oauth_code_verifier'] = $this->login_phase['code_verifier'];
        }

        $this->log_debug('login successful for OIDC sub=%s with username=%s which is rcube-id=%s',
            $this->login_phase['token']['identity']['sub'], $this->login_phase['username'], $this->rcmail->user->ID);

        // login phase is terminated
        $this->login_phase = null;

        return $options;
    }

    /**
     * Callback for 'user_create' hook (create user using OIDC claims))
     *
     * @param array $data user_create parameters (user_name, user_email, language))
     *
     * @return array $data key/values to setup user's identity
     */
    public function user_create($data)
    {
        if (!$this->login_phase) {
            return $data;
        }

        if (!isset($this->login_phase['token']['identity'])) {
            $this->log_debug("identity not found, was the scope 'openid' defined?");
            return $data;
        }

        $identity = $this->login_phase['token']['identity'];

        foreach ($this->options['user_create_map'] as $rc_key => $oidc_claims) {
            $oidc_claims = (array) $oidc_claims;
            foreach ($oidc_claims as $oidc_claim) {
                // use the first defined claim
                if (isset($identity[$oidc_claim]) && is_string($identity[$oidc_claim]) && strlen($identity[$oidc_claim]) > 0) {
                    $value = $identity[$oidc_claim];
                    // normalize and check well known keys
                    switch ($rc_key) {
                        case 'user_email':
                            // normalize to punicode for intl. domains (IDN)
                            $value = rcube_utils::idn_to_ascii($value);
                            // check format
                            if (!rcube_utils::check_email($value, false)) {
                                rcube::raise_error("user_create: ignoring invalid email '{$value}' (from claim '{$oidc_claim}')", true);
                                continue 2; // continue on next foreach iteration
                            }

                            break;
                        case 'language':
                            // normalize language
                            $value = strtr($value, '-', '_');
                            // sanity check no extra chars than an language format (RFC5646)
                            if (!preg_match('/^[a-z0-9_]{2,8}$/i', $value)) {
                                rcube::raise_error("user_create: ignoring language '{$value}' (from claim '{$oidc_claim}')", true);
                                continue 2; // continue on next foreach iteration
                            }

                            break;
                    }
                    $data[$rc_key] = $value;

                    $this->log_debug('user_create: setting %s=%s (from claim %s)', $rc_key, $value, $oidc_claim);
                    break; // no need to continue
                }
            }
        }

        return $data;
    }

    /**
     * Callback for 'logout_after' hook
     *
     * @param array $options Hook parameters
     *
     * @return array
     */
    public function logout_after(array $options)
    {
        $this->no_redirect = true;

        if ($this->logout_redirect_url) {
            // propagate logout request to the identity provider
            $this->rcmail->output->redirect($this->logout_redirect_url); // exit
        }

        return $options;
    }

    /**
     * Callback for 'startup' hook
     *
     * @params array $args array containing task and action
     *
     * @return array the arguments provided in entry and altered if so
     */
    public function startup(array $args)
    {
        if (!$this->is_enabled()) {
            return $args;
        }

        if ($args['task'] == 'login' && $args['action'] == 'oauth') {
            // handle oauth login requests
            $oauth_handler = new rcmail_action_login_oauth();
            $handler_answer = $oauth_handler->run();
            if ($handler_answer && is_array($handler_answer)) {
                // on success, handler will request next action = login
                $args = $handler_answer + $args;
            }
        } elseif ($args['task'] == 'login' && $args['action'] == 'backchannel') {
            // handle oauth login requests
            $oauth_handler = new rcmail_action_login_oauth_backchannel();
            $oauth_handler->run();
        } elseif ($args['task'] == 'logout') {
            // handle only logout task
            $this->handle_logout();
        }

        return $args;
    }

    /**
     * Implement OpenID Connect RP-Initiated Logout 1.0
     *
     * will generate during the logout task the RP-initiated Logout URL and
     * store it in `logout_redirect_url`
     *
     * @see https://openid.net/specs/openid-connect-rpinitiated-1_0.html
     */
    public function handle_logout(): void
    {
        // if no logout URI, or no refresh token, safe to give up
        if (!$this->options['logout_uri'] || !isset($_SESSION['oauth_token'])) {
            return;
        }

        // refresh token only if expired, we do this to ensure it will propagate session close to IDP
        switch ($this->check_token_validity($_SESSION['oauth_token'])) {
            case self::TOKEN_REFRESHED:
            case self::TOKEN_STILL_VALID:
                // token still ok or refreshed
                break;
            default:
                // got an error, cannot request IDP to cleanup other sessions
                return;
        }

        // generate redirect URL for post-logout
        $params = [
            'post_logout_redirect_uri' => $this->rcmail->url([], true, true),
            'client_id' => $this->options['client_id'],
        ];

        if (isset($_SESSION['oauth_token']['id_token'])) {
            $params['id_token_hint'] = $_SESSION['oauth_token']['id_token'];
        }

        $this->logout_redirect_url = $this->options['logout_uri'] . '?' . http_build_query($params);
        $this->log_debug('creating logout call: %s', $this->logout_redirect_url);
    }

    /**
     * Callback for 'login_failed' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function login_failed($options)
    {
        // no redirect on imap login failures
        $this->no_redirect = true;
        $this->login_phase = null;
        return $options;
    }

    /**
     * Callback for 'unauthenticated' hook
     *
     * @param array $options
     *
     * @return array
     */
    public function unauthenticated($options)
    {
        if (
            $this->options['login_redirect']
            && !$this->rcmail->output->ajax_call
            && !$this->no_redirect
            && empty($options['error'])
            && $options['http_code'] === 200
        ) {
            $this->login_redirect();
        }

        return $options;
    }
}
