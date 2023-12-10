<?php

/**
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
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

use GuzzleHttp\Client;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Exception\RequestException;

/**
 * Roundcube OAuth2 utilities
 *
 * @package    Webmail
 * @subpackage Utils
 */
class rcmail_oauth
{
    /** @var rcmail */
    protected $rcmail;

    /** @var array */
    protected $options = [];

    /** @var string */
    protected $last_error = null;

    /** @var bool */
    protected $no_redirect = false;

    /** @var rcmail_oauth */
    static protected $instance;

    /**
     * Singleton factory
     *
     * @return rcmail_oauth The one and only instance
     */
    static function get_instance($options = [])
    {
        if (!self::$instance) {
            self::$instance = new rcmail_oauth($options);
            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * Object constructor
     *
     * @param array $options Config options:
     */
    public function __construct($options = [])
    {
        $this->rcmail  = rcmail::get_instance();
        $this->options = (array) $options + [
            'provider'        => $this->rcmail->config->get('oauth_provider'),
            'auth_uri'        => $this->rcmail->config->get('oauth_auth_uri'),
            'token_uri'       => $this->rcmail->config->get('oauth_token_uri'),
            'client_id'       => $this->rcmail->config->get('oauth_client_id'),
            'client_secret'   => $this->rcmail->config->get('oauth_client_secret'),
            'identity_uri'    => $this->rcmail->config->get('oauth_identity_uri'),
            'identity_fields' => $this->rcmail->config->get('oauth_identity_fields', ['email']),
            'scope'           => $this->rcmail->config->get('oauth_scope'),
            'verify_peer'     => $this->rcmail->config->get('oauth_verify_peer', true),
            'auth_parameters' => $this->rcmail->config->get('oauth_auth_parameters', []),
            'login_redirect'  => $this->rcmail->config->get('oauth_login_redirect', false),
        ];
    }

    /**
     * Initialize this instance
     *
     * @return void
     */
    protected function init()
    {
        // subscribe to storage and smtp init events
        if ($this->is_enabled()) {
            $this->rcmail->plugins->register_hook('storage_init', [$this, 'storage_init']);
            $this->rcmail->plugins->register_hook('smtp_connect', [$this, 'smtp_connect']);
            $this->rcmail->plugins->register_hook('managesieve_connect', [$this, 'managesieve_connect']);
            $this->rcmail->plugins->register_hook('logout_after', [$this, 'logout_after']);
            $this->rcmail->plugins->register_hook('login_failed', [$this, 'login_failed']);
            $this->rcmail->plugins->register_hook('unauthenticated', [$this, 'unauthenticated']);
            $this->rcmail->plugins->register_hook('refresh', [$this, 'refresh']);
        }
    }

    /**
     * Check if OAuth is generally enabled in config
     *
     * @return bool
     */
    public function is_enabled()
    {
        return !empty($this->options['provider']) &&
            !empty($this->options['token_uri']) &&
            !empty($this->options['client_id']);
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
     * Getter for the last error occurred
     *
     * @return mixed
     */
    public function get_last_error()
    {
        return $this->last_error;
    }

    /**
     * Helper method to decode a JWT
     * 
     * @param string $jwt
     * @return array Hash array with decoded body
     */
    public function jwt_decode($jwt)
    {
        list($headb64, $bodyb64, $cryptob64) = explode('.', strtr($jwt, '-_', '+/'));

        $header = json_decode(base64_decode($headb64), true);
        $body   = json_decode(base64_decode($bodyb64), true);

        if (isset($body['azp']) && $body['azp'] !== $this->options['client_id']) {
            throw new RuntimeException('Failed to validate JWT: invalid azp value');
        }
        else if (isset($body['aud']) && !in_array($this->options['client_id'], (array) $body['aud'])) {
            throw new RuntimeException('Failed to validate JWT: invalid aud value');
        }
        else if (!isset($body['azp']) && !isset($body['aud'])) {
            throw new RuntimeException('Failed to validate JWT: missing aud/azp value');
        }

        return $body;
    }

    /**
     * Login action: redirect to `oauth_auth_uri`
     *
     * @return void
     */
    public function login_redirect()
    {
        if (!empty($this->options['auth_uri']) && !empty($this->options['client_id'])) {
            // create a secret string
            $_SESSION['oauth_state'] = rcube_utils::random_bytes(12);

            // compose full oauth login uri
            $delimiter = strpos($this->options['auth_uri'], '?') > 0 ? '&' : '?';
            $query = http_build_query([
                'response_type' => 'code',
                'client_id'     => $this->options['client_id'],
                'scope'         => $this->options['scope'],
                'redirect_uri'  => $this->get_redirect_uri(),
                'state'         => $_SESSION['oauth_state'],
            ] + (array) $this->options['auth_parameters']);
            $this->rcmail->output->redirect($this->options['auth_uri'] . $delimiter . $query);  // exit
        }
        else {
            // log error about missing config options
            rcube::raise_error([
                    'message' => "Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'",
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ], true, false
            );
        }
    }

    /**
     * Request access token with auth code returned from oauth login
     *
     * @param string $auth_code
     * @param string $state
     *
     * @return array Authorization data as hash array with entries
     *   `username` as the authentication user name
     *   `authorization` as the oauth authorization string "<type> <access-token>"
     *   `token` as the complete oauth response to be stored in session
     */
    public function request_access_token($auth_code, $state = null)
    {
        $oauth_token_uri     = $this->options['token_uri'];
        $oauth_client_id     = $this->options['client_id'];
        $oauth_client_secret = $this->options['client_secret'];
        $oauth_identity_uri  = $this->options['identity_uri'];

        if (!empty($oauth_token_uri) && !empty($oauth_client_secret)) {
            try {
                // validate state parameter against $_SESSION['oauth_state']
                if (!empty($_SESSION['oauth_state']) && $_SESSION['oauth_state'] !== $state) {
                    throw new RuntimeException('Invalid state parameter');
                }

                // send token request to get a real access token for the given auth code
                $client = new Client([
                    'timeout' => 10.0,
                    'verify' => $this->options['verify_peer'],
                ]);

                $response = $client->post($oauth_token_uri, [
                        'form_params'       => [
                            'code'          => $auth_code,
                            'client_id'     => $oauth_client_id,
                            'client_secret' => $oauth_client_secret,
                            'redirect_uri'  => $this->get_redirect_uri(),
                            'grant_type'    => 'authorization_code',
                        ],
                ]);

                $data = \GuzzleHttp\json_decode($response->getBody(), true);

                // auth success
                if (!empty($data['access_token'])) {
                    $username = null;
                    $identity = null;
                    $authorization = sprintf('%s %s', $data['token_type'], $data['access_token']);

                    // decode JWT id_token if provided
                    if (!empty($data['id_token'])) {
                        try {
                            $identity = $this->jwt_decode($data['id_token']);
                            foreach ($this->options['identity_fields'] as $field) {
                                if (isset($identity[$field])) {
                                    $username = $identity[$field];
                                    break;
                                }
                            }
                        } catch (\Exception $e) {
                            // log error
                            rcube::raise_error([
                                    'message' => $e->getMessage(),
                                    'file'    => __FILE__,
                                    'line'    => __LINE__,
                                ], true, false
                            );
                        }
                    }

                    // request user identity (email)
                    if (empty($username) && !empty($oauth_identity_uri)) {
                        $identity_response = $client->get($oauth_identity_uri, [
                                'headers' => [
                                    'Authorization' => $authorization,
                                    'Accept' => 'application/json',
                                ],
                        ]);

                        $identity = \GuzzleHttp\json_decode($identity_response->getBody(), true);

                        foreach ($this->options['identity_fields'] as $field) {
                            if (isset($identity[$field])) {
                                $username = $identity[$field];
                                break;
                            }
                        }
                    }

                    $data['identity'] = $username;
                    $this->mask_auth_data($data);

                    $this->rcmail->session->remove('oauth_state');

                    $this->rcmail->plugins->exec_hook('oauth_login', array_merge($data, [
                        'username' => $username,
                        'identity' => $identity,
                    ]));

                    // remove some data we don't want to store in session
                    unset($data['id_token']);

                    // return auth data
                    return [
                        'username'      => $username,
                        'authorization' => $authorization,
                        'token'         => $data,
                    ];
                }
                else {
                    throw new Exception('Unexpected response from OAuth service');
                }
            }
            catch (RequestException $e) {
                $this->last_error = "OAuth token request failed: " . $e->getMessage();
                $this->no_redirect = true;
                $formatter = new MessageFormatter();

                rcube::raise_error([
                        'message' => $this->last_error . '; ' . $formatter->format($e->getRequest(), $e->getResponse()),
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                    ], true, false
                );

                return false;
            }
            catch (Exception $e) {
                $this->last_error = "OAuth token request failed: " . $e->getMessage();
                $this->no_redirect = true;

                rcube::raise_error([
                        'message' => $this->last_error,
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                    ], true, false
                );

                return false;
            }
        }
        else {
            $this->last_error = "Missing required OAuth config options 'oauth_token_uri', 'oauth_client_id', 'oauth_client_secret'";

            rcube::raise_error([
                    'message' => $this->last_error,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ], true, false
            );

            return false;
        }
    }

    /**
     * Obtain a new access token using the refresh_token grant type
     *
     * If successful, this will update the `oauth_token` entry in
     * session data.
     *
     *
     * @return array Updated authorization data
     */
    public function refresh_access_token(array $token)
    {
        $oauth_token_uri     = $this->options['token_uri'];
        $oauth_client_id     = $this->options['client_id'];
        $oauth_client_secret = $this->options['client_secret'];

        // send token request to get a real access token for the given auth code
        try {
            $client = new Client([
                'timeout' => 10.0,
                'verify' => $this->options['verify_peer'],
            ]);
            $response = $client->post($oauth_token_uri, [
                    'form_params' => [
                        'client_id'     => $oauth_client_id,
                        'client_secret' => $oauth_client_secret,
                        'refresh_token' => $this->rcmail->decrypt($token['refresh_token']),
                        'grant_type'    => 'refresh_token',
                    ],
            ]);
            $data = \GuzzleHttp\json_decode($response->getBody(), true);

            // auth success
            if (!empty($data['access_token'])) {
                // update access token stored as password
                $authorization = sprintf('%s %s', $data['token_type'], $data['access_token']);
                $_SESSION['password'] = $this->rcmail->encrypt($authorization);

                $this->mask_auth_data($data);

                // update session data
                $_SESSION['oauth_token'] = array_merge($token, $data);

                $this->rcmail->plugins->exec_hook('oauth_refresh_token', $data);

                return [
                    'token' => $data,
                    'authorization' => $authorization,
                ];
            }
        }
        catch (RequestException $e) {
            $this->last_error = "OAuth refresh token request failed: " . $e->getMessage();
            $formatter = new MessageFormatter();
            rcube::raise_error([
                    'message' => $this->last_error . '; ' . $formatter->format($e->getRequest(), $e->getResponse()),
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ], true, false
            );

            // refrehsing token failed, mark session as expired
            if ($e->getCode() >= 400 && $e->getCode() < 500) {
                $this->rcmail->kill_session();
            }

            return false;
        }
        catch (Exception $e) {
            $this->last_error = "OAuth refresh token request failed: " . $e->getMessage();
            rcube::raise_error([
                    'message' => $this->last_error,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ], true, false
            );

            return false;
        }
    }

    /**
     * Modify some properties of the received auth response
     *
     * @param array $data
     * @return void
     */
    protected function mask_auth_data(&$data)
    {
        // compute absolute token expiration date
        $data['expires'] = time() + $data['expires_in'] - 10;

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
     * @param array $token
     * @return bool
     */
    protected function check_token_validity($token)
    {
        if ($token['expires'] < time() && isset($token['refresh_token']) && empty($this->last_error)) {
            return $this->refresh_access_token($token) !== false;
        }
        return false;
    }

    /**
     * Callback for 'storage_init' hook
     *
     * @param array $options
     * @return array
     */
    public function storage_init($options)
    {
        if (isset($_SESSION['oauth_token']) && $options['driver'] === 'imap') {
            // check token validity
            if ($this->check_token_validity($_SESSION['oauth_token'])) {
                $options['password'] = $this->rcmail->decrypt($_SESSION['password']);
            }

            // enforce XOAUTH2 authorization type
            $options['auth_type'] = 'XOAUTH2';
        }

        return $options;
    }

    /**
     * Callback for 'smtp_connect' hook
     *
     * @param array $options
     * @return array
     */
    public function smtp_connect($options)
    {
        if (isset($_SESSION['oauth_token'])) {
            // check token validity
            $this->check_token_validity($_SESSION['oauth_token']);

            // enforce XOAUTH2 authorization type
            $options['smtp_user'] = '%u';
            $options['smtp_pass'] = '%p';
            $options['smtp_auth_type'] = 'XOAUTH2';
        }

        return $options;
    }

    /**
     * Callback for 'managesieve_connect' hook
     *
     * @param array $options
     * @return array
     */
    public function managesieve_connect($options)
    {
        if (isset($_SESSION['oauth_token'])) {
            // check token validity
            $this->check_token_validity($_SESSION['oauth_token']);

            // enforce XOAUTH2 authorization type
            $options['auth_type'] = 'XOAUTH2';
        }

        return $options;
    }

    /**
     * Callback for 'logout_after' hook
     *
     * @param array $options
     * @return array
     */
    public function logout_after($options)
    {
        $this->no_redirect = true;
    }

    /**
     * Callback for 'login_failed' hook
     *
     * @param array $options
     * @return array
     */
    public function login_failed($options)
    {
        // no redirect on imap login failures
        $this->no_redirect = true;
        return $options;
    }

    /**
     * Callback for 'unauthenticated' hook
     *
     * @param array $options
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


    /**
     * Callback for 'refresh' hook
     *
     * @param array $options
     * @return void
     */
    public function refresh($options)
    {
        if (isset($_SESSION['oauth_token'])) {
            $this->check_token_validity($_SESSION['oauth_token']);
        }
    }
}
