<?php

/**
 * Modoboa Password Driver
 *
 * Payload is json string containing username, oldPassword and newPassword
 * Return value is a json string saying result: true if success.
 *
 * @version 2.0
 *
 * @author stephane @actionweb.fr
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 *
 * The driver need modoboa core 1.10.6 or later
 *
 * You need to define theses variables in plugin/password/config.inc.php
 *
 * $config['password_driver'] = 'modoboa'; // use modoboa as driver
 * $config['password_modoboa_api_token'] = ''; // put token number from Modoboa server
 * $config['password_minimum_length'] = 8; // select same number as in Modoboa server
 */

class rcube_modoboa_password
{
    public function save($curpass, $passwd, $username)
    {
        // Init config access
        $rcmail = rcmail::get_instance();
        $token = $rcmail->config->get('password_modoboa_api_token');
        $IMAPhost = $_SESSION['imap_host'];

        $client = password::get_http_client();
        $url = "https://{$IMAPhost}/api/v1/accounts/?search=" . urlencode($username);

        $options = [
            'http_errors' => true,
            'headers' => [
                'Authorization' => "Token {$token}",
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
            ],
        ];

        // Call GET to fetch values from modoboa server
        try {
            $response = $client->get($url, $options);
            $response = $response->getBody()->getContents();
        } catch (Exception $e) {
            rcube::raise_error("Password plugin: Error fetching {$url} : {$e->getMessage()}", true);
            return PASSWORD_CONNECT_ERROR;
        }

        // Decode json string
        $decoded = json_decode($response);

        if (!is_array($decoded)) {
            return PASSWORD_CONNECT_ERROR;
        }

        // Get user ID (pk)
        $userid = $decoded[0]->pk;

        // Encode json with new password
        $options['body'] = json_encode([
                'username' => $decoded[0]->username,
                'mailbox' => $decoded[0]->mailbox,
                'role' => $decoded[0]->role,
                'password' => $passwd, // new password
        ]);

        $url = "https://{$IMAPhost}/api/v1/accounts/{$userid}/";

        // Call HTTP API Modoboa
        try {
            $response = $client->put($url, $options);
            $response = $response->getBody()->getContents();
        } catch (Exception $e) {
            rcube::raise_error("Password plugin: Error on {$url} : {$e->getMessage()}", true);
            return PASSWORD_CONNECT_ERROR;
        }

        return PASSWORD_SUCCESS;
    }
}
