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

class oauth_client
{
    static private $providers = null;

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
            self::$providers = $rc->config->get("calendar_oauth_providers", array());
        }

        return self::$providers;
    }

    public function __construct($rc, $provider)
    {
        $this->provider = is_array($provider) ? $provider : self::get_provider($provider);
        $this->token_storage = new \oauth_token_storage($rc->db, $this->provider);
        $this->client_config = $this->_get_client_config($this->provider);
        $this->api = new Api($this->client_config_id, $this->client_config, $this->token_storage, new Guzzle3Client());
        $this->context = new Context($rc->user->ID, $provider["scope"]);
    }

    public function has_access_token()
    {
        return $this->api->getAccessToken($this->context) !== false;
    }

    public function redirect_to_auth_server()
    {
        header("HTTP/1.1 302 Found");
        header("Location: ".$this->api->getAuthorizeUri($this->context));
        exit;
    }

    public function handle_callback()
    {
        $callback = new Callback($this->client_config_id, $this->client_config, $this->token_storage, new Guzzle3Client());
        $callback->handleCallback($_GET);
    }

    private function _get_client_config($provider)
    {
        if(isset($provider["json"])) {
            return new GoogleClientConfig(json_decode(file_get_contents($provider["json"]), true));
        }

        return new ClientConfig($provider);
    }
}