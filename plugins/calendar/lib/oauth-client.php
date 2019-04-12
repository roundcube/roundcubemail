<?php
/**
 * OAuth2 Client
 *
 * @version @package_version@
 * @author Daniel Morlock <daniel.morlock@awesome-it.de>
 *
 * Copyright (C) Awesome IT GbR <info@awesome-it.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

require_once (dirname(__FILE__).'/vendor/autoload.php');
require_once (dirname(__FILE__).'/oauth-token-storage.php');

use fkooman\OAuth\Client\GoogleClientConfig;
use fkooman\OAuth\Client\ClientConfig;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\Callback;
use fkooman\OAuth\Client\Guzzle3Client;

class oauth_exception extends Exception
{

}

class oauth_client
{
    static private $providers = null;

    private $rc;
    private $client_config_id = "php-kolab-client";
    private $provider;
    private $token_storage;
    private $client_config;
    private $api;
    private $context;

    public static function get_provider($id)
    {
        $providers = self::get_providers();
        if(isset($providers[$id]))
            return $providers[$id];

        return false;
    }

    public static function get_providers()
    {
        if(self::$providers == null) {
            $rc = \rcmail::get_instance();
            self::$providers = array();
            foreach($rc->config->get("calendar_oauth_providers", array()) as $id => $provider) {
                $provider["id"] = $id;
                self::$providers[$id] = $provider;
            }
        }

        return self::$providers;
    }

    public function __construct(rcube $rc, $provider)
    {
        $this->rc = $rc;
        $this->provider = is_array($provider) ? $provider : self::get_provider($provider);
        $this->token_storage = new \oauth_token_storage($rc, $this->provider);
        $this->client_config = $this->_get_client_config($this->provider);
        $this->api = new Api($this->client_config_id, $this->client_config, $this->token_storage, new Guzzle3Client());
        $this->context = new Context($rc->user->ID, array($this->provider["scope"]));
    }

    public function has_access_token()
    {
        return $this->api->getAccessToken($this->context) !== false;
    }

    public function get_access_token()
    {
        $token = $this->api->getAccessToken($this->context);
        if(!$token) {
            throw new oauth_exception("access_token_invalid");
        }
        return $token;
    }

    public function redirect_to_auth_server()
    {
        header("HTTP/1.1 302 Found");
        header("Location: ".$this->get_authorize_uri());
        exit;
    }

    public function get_authorize_uri()
    {
        return $this->api->getAuthorizeUri($this->context, $this->_encode_state(), "offline");
    }

    public function handle_callback()
    {
        $callback = new Callback($this->client_config_id, $this->client_config, $this->token_storage, new Guzzle3Client());

        $query = array();
        foreach(array("state", "code", "error", "error_description") as $key)
        {
            $input_value = get_input_value(isset($_GET[$key]) ? $key : "_".$key, rcube_utils::INPUT_GET);
            $query[$key] = $input_value;
        }

        $callback->handleCallback($query);
    }

    private function _get_client_config($provider)
    {
        if(isset($provider["json"]))
            return new GoogleClientConfig(json_decode(file_get_contents($provider["json"]), true));

        return new ClientConfig($provider);
    }

    private function _encode_state()
    {
        // Store provider and current user id in the state
        $data = array("provider_id" => $this->provider["id"]);

        // Encode as base64, see: https://en.wikipedia.org/wiki/Base64#URL_applications
        return strtr(base64_encode(serialize($data)), '+/=', '-_,');
    }

    public static function decode_state($state = null)
    {
        if(!$state) $state = get_input_value(isset($_GET["state"]) ? "state" : "_state", rcube_utils::INPUT_GET);
        return unserialize(base64_decode(strtr($state, '-_,', '+/=')));
    }

    public function logout()
    {
        if(($access_token = $this->api->getAccessToken($this->context)))
            $this->token_storage->deleteAccessToken($access_token);

        if(($refresh_token = $this->api->getRefreshToken($this->context)))
            $this->token_storage->deleteRefreshToken($refresh_token);
    }
}