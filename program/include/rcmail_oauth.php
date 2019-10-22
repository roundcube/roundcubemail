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
    protected $options = array();

    /** @var string */
    protected $last_error = null;

    /** @var rcmail_oauth */
    static protected $instance;

    /**
     * Singleton factory
     *
     * @return rcmail_oauth The one and only instance
     */
    static function get_instance($options = array())
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
    public function __construct($options = array())
    {
        $this->rcmail  = rcmail::get_instance();
        $this->options = (array) $options + array(
            'provider' => $this->rcmail->config->get('oauth_provider'),
            'auth_uri' => $this->rcmail->config->get('oauth_auth_uri'),
            'token_uri' => $this->rcmail->config->get('oauth_token_uri'),
            'client_id' => $this->rcmail->config->get('oauth_client_id'),
            'client_secret' => $this->rcmail->config->get('oauth_client_secret'),
            'identity_uri' => $this->rcmail->config->get('oauth_identity_uri'),
            'scope' => $this->rcmail->config->get('oauth_scope'),
            'auth_parameters' => $this->rcmail->config->get('oauth_auth_parameters', array()),
        );
    }

    /**
     * Initialize this instance
     *
     * @return void
     */
    protected function init()
    {
        // subscrbe to storage and smtp init events
        if ($this->is_enabled()) {
            $this->rcmail->plugins->register_hook('storage_init', [$this, 'storage_init']);
            $this->rcmail->plugins->register_hook('smtp_connect', [$this, 'smtp_connect']);
        }
    }

    /**
     * Check if OAuth is generally enabled in config
     *
     * @return boolean
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
        return $this->rcmail->url(['action' => 'oauth'], true, true);
    }

    /**
     * Getter for the last error occured
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
        list($headb64, $bodyb64, $cryptob64) = explode('.', $jwt);
        $header = json_decode(base64_decode($headb64), true);
        $body = json_decode(base64_decode($bodyb64), true);

        if (!isset($body['azp']) || $body['azp'] !== $this->options['client_id']) {
            throw new RuntimeException('Failed to validate JWT: invalid azp value');
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
                'client_id' => $this->options['client_id'],
                'scope' => $this->options['scope'],
                'redirect_uri' => $this->get_redirect_uri(),
                'state' => $_SESSION['oauth_state'],
            ] + (array)$this->options['auth_parameters']);
            $this->rcmail->output->redirect($this->options['auth_uri'] . $delimiter . $query);  // exit
        } else {
            // log error about missing config options
            rcube::raise_error(array(
                'message' => "Missing required OAuth config options 'oauth_auth_uri', 'oauth_client_id'",
                'file'    => __FILE__,
                'line'    => __LINE__,
            ), true, false);
        }
    }

    /**
     * Request access token with auth code returned from oauth login
     * 
     * @param string $auth_code
     * @param string $state
     * @return array Authorization data as hash array with entries
     *   `username` as the authentication user name
     *   `authorization` as the oauth authorization string "<type> <access-token>"
     *   `token` as the complete oauth response to be stored in session
     */
    public function request_access_token($auth_code, $state = null)
    {
        $oauth_token_uri = $this->options['token_uri'];
        $oauth_client_id = $this->options['client_id'];
        $oauth_client_secret = $this->options['client_secret'];
        $oauth_identity_uri = $this->options['identity_uri'];

        if (!empty($oauth_token_uri) && !empty($oauth_client_secret)) {
            // validate state parameter against $_SESSION['oauth_state']
            if (!empty($_SESSION['oauth_state']) && $_SESSION['oauth_state'] !== $state) {
                throw new RuntimeException('Invalid state parameter');
            }

            // send token request to get a real access token for the given auth code
            try {
                $client = new Client([
                    'timeout' => 10.0,
                ]);
                $response = $client->post($oauth_token_uri, array(
                    'body' => array(
                        'code' => $auth_code,
                        'client_id' => $oauth_client_id,
                        'client_secret' => $oauth_client_secret,
                        'redirect_uri' => $this->get_redirect_uri(),
                        'grant_type' => 'authorization_code',
                    ),
                ));
                $data = $response->json();

                // auth success
                if (!empty($data['access_token'])) {
                    $username = null;
                    $authorization = sprintf('%s %s', $data['token_type'], $data['access_token']);

                    // decode JWT id_token if provided
                    if (!empty($data['id_token'])) {
                        try {
                            $identity = $this->jwt_decode($data['id_token']);
                            $username = $identity['email'];
                            unset($data['id_token']);
                        } catch (\Exception $e) {
                            // ignore
                        }
                    }

                    // request user identity (email)
                    if (empty($username) && !empty($oauth_identity_uri)) {
                        $identity = $client->get($oauth_identity_uri, array(
                            'headers' => array(
                                'Authorization' => $authorization,
                                'Accept' => 'application/json',
                            ),
                        ))->json();
                        if (isset($identity['email'])) {
                            $username = $identity['email'];
                        }
                    }

                    $data['identity'] = $username;
                    self::mask_auth_data($data);

                    $this->rcmail->write_log('oauth2', 'Auth code success: ' . json_encode($data));

                    $this->rcmail->session->remove('oauth_state');

                    // return auth data
                    return array(
                        'username' => $username,
                        'authorization' => $authorization,
                        'token' => $data,
                    );
                } else {
                    throw new Exception('Unexpected response from OAuth service');
                }
            } catch (RequestException $e) {
                $this->last_error = "OAuth token request failed: " . $e->getMessage();
                rcube::raise_error(array(
                    'message' => $this->last_error . '; ' . $e->getResponse(),
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ), true, false);
                return false;
            } catch (Exception $e) {
                $this->last_error = "OAuth token request failed: " . $e->getMessage();
                rcube::raise_error(array(
                    'message' => $this->last_error,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                ), true, false);
                return false;
            }
        } else {
            $this->last_error = "Missing required OAuth config options 'oauth_token_uri', 'oauth_client_id', 'oauth_client_secret'";
            rcube::raise_error(array(
                'message' => $this->last_error,
                'file'    => __FILE__,
                'line'    => __LINE__,
            ), true, false);
            return false;
        }
    }

    /**
     * Obtain a new access token using the refresh_token grant type
     * 
     * If successful, this will update the `oauth_token` entry in
     * session data.
     *
     * @param array $token
     * @return array Updated authorization data
     */
    public function refresh_access_token(array $token)
    {
        // send token request to get a real access token for the given auth code
        try {
            $client = new Client([
                'timeout' => 10.0,
            ]);
            $response = $client->post($oauth_token_uri, array(
                'body' => array(
                    'client_id' => $oauth_client_id,
                    'client_secret' => $oauth_client_secret,
                    'refresh_token' => $token['refresh_token'],
                    'grant_type' => 'refresh_token',
                ),
            ));
            $data = $response->json();

            // auth success
            if (!empty($data['access_token'])) {
                // update access token stored as password
                $authorization = sprintf('%s %s', $data['token_type'], $data['access_token']);
                $_SESSION['password'] = $this->rcmail->encrypt($authorization);

                self::mask_auth_data($data);

                // update session data
                $_SESSION['oauth_token'] = array_merge($token, $data);

                return [
                    'token' => $data,
                    'authorization' => $authorization,
                ];
            }
        } catch (RequestException $e) {
            $this->last_error = "OAuth refresh token request failed: " . $e->getMessage();
            rcube::raise_error(array(
                'message' => $this->last_error . '; ' . $e->getResponse(),
                'file'    => __FILE__,
                'line'    => __LINE__,
            ), true, false);
            return false;
        } catch (Exception $e) {
            $this->last_error = "OAuth refresh token request failed: " . $e->getMessage();
            rcube::raise_error(array(
                'message' => $this->last_error,
                'file'    => __FILE__,
                'line'    => __LINE__,
            ), true, false);
            return false;
        }
    }

    /**
     * Modify some properties of the received auth response
     *
     * @param array $token
     * @return void
     */
    protected static function mask_auth_data(&$data)
    {
        // compute absolute token expiration date
        $data['expires'] = time() + $data['expires_in'] - 600;

        // mask access token before storing in session
        $data['access_token'] = substr($data['access_token'], 0, 12) . str_repeat('*', strlen($data['access_token']) - 6);
    }

    /**
     * Check the given access token data if still valid
     *
     * ... and attempt to refresh if possible.
     *
     * @param array $token
     * @return void
     */
    protected function check_token_validity($token)
    {
        if ($token['expires'] < time() && isset($token['refresh_token'])) {
            $this->refresh_access_token($token);
        }
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
            $this->check_token_validity($_SESSION['oauth_token']);

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
}
