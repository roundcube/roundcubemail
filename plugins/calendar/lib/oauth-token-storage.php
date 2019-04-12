<?php
/**
 * OAuth2 Storage Provider
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

use fkooman\OAuth\Common\Scope;
use fkooman\OAuth\Client\AccessToken;
use fkooman\OAuth\Client\RefreshToken;
use fkooman\OAuth\Client\State;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\StorageInterface;

class oauth_token_storage implements StorageInterface
{
    private $db;
    private $provider;

    private $db_access_token = "calendar_oauth_access_tokens";
    private $db_refresh_token = "calendar_oauth_refresh_tokens";
    private $db_states = "calendar_oauth_states";

    public function __construct($db, $provider)
    {
        $this->db = $db;
        $this->provider = $provider;
    }

    public function storeAccessToken(AccessToken $access_token) {
        $result = $this->db->query(
            "INSERT INTO ".$this->db_access_token." (provider, client_config_id, user_id, scope, access_token, token_type, expires_in, issue_time) ".
            "VALUES(?, ?, ?, ?, ?, ?, ?, ?)",

            $this->provider["name"],
            $access_token->getClientConfigId(),
            $access_token->getUserId(),
            $access_token->getScope(),
            $access_token->getAccessToken(),
            $access_token->getTokenType(),
            $access_token->getExpiresIn(),
            $access_token->getIssueTime()
        );

        return $result !== false;
    }

    public function getAccessToken($client_config_id, Context $context) {
        $result = $this->db->query(
            "SELECT * FROM ".$this->db_access_token." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND user_id=? ".
            "AND scope=?",

            $this->provider["name"],
            $client_config_id,
            $context->getUserId(),
            $context->getScope()->toString());

        if($result && ($data = $this->db->fetch_assoc($result))) {
            $data["scope"] = Scope::fromString($data["scope"]);
            return new AccessToken($data);
        }

        return false;
    }

    public function deleteAccessToken(AccessToken $access_token) {
        $result = $this->db->query(
            "DELETE FROM ".$this->db_access_token." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND user_id=? ".
            "AND access_token=?",
        
            $this->provider["name"],
            $access_token->getClientConfigId(),
            $access_token->getUserId(),
            $access_token->getAccessToken());

        return $this->db->affected_rows($result) == 1;
    }

    public function storeRefreshToken(RefreshToken $refresh_token) {
        $result = $this->db->query(
            "INSERT INTO ".$this->db_refresh_token." (provider, client_config_id, user_id, scope, refresh_token, issue_time) ".
            "VALUES(?, ?, ?, ?, ?, ?)",

            $this->provider["name"],
            $refresh_token->getClientConfigId(),
            $refresh_token->getUserId(),
            $refresh_token->getScope(),
            $refresh_token->getRefreshToken(),
            $refresh_token->getIssueTime()
        );

        return $result !== false;
    }

    public function getRefreshToken($client_config_id, Context $context) {
        $result = $this->db->query(
            "SELECT * FROM ".$this->db_refresh_token." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND user_id=? ".
            "AND scope=?",

            $this->provider["name"],
            $client_config_id,
            $context->getUserId(),
            $context->getScope()->toString());

        if($result && ($data = $this->db->fetch_assoc($result))) {
            $data["scope"] = Scope::fromString($data["scope"]);
            return new RefreshToken($data);
        }

        return false;
    }

    public function deleteRefreshToken(RefreshToken $refresh_token) {
        $result = $this->db->query(
            "DELETE FROM ".$this->db_refresh_token." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND user_id=? ".
            "AND refresh_token=?",

            $this->provider["name"],
            $refresh_token->getClientConfigId(),
            $refresh_token->getUserId(),
            $refresh_token->getRefreshToken());

        return $this->db->affected_rows($result) == 1;
    }

    public function storeState(State $state) {
        $result = $this->db->query(
            "INSERT INTO ".$this->db_states." (provider, client_config_id, user_id, scope, issue_time, state) ".
            "VALUES(?, ?, ?, ?, ?, ?)",

            $this->provider["name"],
            $state->getClientConfigId(),
            $state->getUserId(),
            $state->getScope()->toString(),
            $state->getIssueTime(),
            $state->getState()
        );

        return $result !== false;
    }

    public function getState($client_config_id, $state) {
        $result = $this->db->query(
            "SELECT * FROM ".$this->db_states." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND state=? ",

            $this->provider["name"],
            $client_config_id,
            $state);

        if($result && ($data = $this->db->fetch_assoc($result))) {
            $data["scope"] = Scope::fromString($data["scope"]);
            return new State($data);
        }

        return false;
    }

    public function deleteState(State $state) {
        $result = $this->db->query(
            "DELETE FROM ".$this->db_states." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND state=?",

            $this->provider["name"],
            $state->getClientConfigId(),
            $state->getState());

        return $this->db->affected_rows($result) == 1;
    }

    public function deleteStateForContext($client_config_id, Context $context) {
        $result = $this->db->query(
            "DELETE FROM ".$this->db_states." ".
            "WHERE provider=? ".
            "AND client_config_id=? ".
            "AND user_id=?",

            $this->provider["name"],
            $client_config_id,
            $context->getUserId());

        return $this->db->affected_rows($result) == 1;
    }
}