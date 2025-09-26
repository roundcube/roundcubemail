<?php

/**
 * Modoboa Password Driver
 * Minor modification from 2.0, updated to use Modoboa v2 password endpoint.
 * Payload uses form-encoded data: current password and new password.
 * Endpoint: PUT /api/v2/accounts/{id}/password/
 *
 * Return value is a status constant (PASSWORD_SUCCESS on success,
 * PASSWORD_CONNECT_ERROR on failure).
 *
 * @version 2.1
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
 * along with this program. If not, see https://www.gnu.org/licenses/.
 *
 * The driver needs Modoboa core 1.10.6 or later
 *
 * You need to define these variables in plugin/password/config.inc.php
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
        $token  = $rcmail->config->get('password_modoboa_api_token');
        $IMAPhost = $_SESSION['imap_host'];
        // uncomment and add your url if you use a differente url for your modoboa instance and api:
        //$IMAPhost = 'mymodoboa.somewhere.net';

        // Use v2 API: search user by username
        $client = password::get_http_client();
        $url = "https://{$IMAPhost}/api/v2/accounts/?search=" . urlencode($username);

        $options = [
            'http_errors' => true,
            'headers' => [
                'Authorization' => "Token " . $token,
                'Accept'        => 'application/json',
            ],
        ];

        // Call GET to fetch user details
        try {
            $response = $client->get($url, $options);
            $responseBody = $response->getBody()->getContents();
        } catch (\Exception $e) {
            rcube::raise_error("Password plugin: Error fetching {$url} : {$e->getMessage()}", true);
            return PASSWORD_CONNECT_ERROR;
        }

        // Decode json string
        $decoded = json_decode($responseBody);

        if (!is_array($decoded) || empty($decoded)) {
            return PASSWORD_CONNECT_ERROR;
        }

        // Get user ID (pk)
        $userid = $decoded[0]->pk;

        // Call v2 password endpoint with form-encoded data
        $url2 = "https://{$IMAPhost}/api/v2/accounts/" . $userid . "/password/";
        $options2 = [
            'http_errors' => true,
            'headers' => [
                'Authorization' => "Token " . $token,
                'Accept'        => 'application/json',
                // Content-Type will be set automatically when using form_params
            ],
            'form_params' => [
                'password'     => $curpass,   // current password
                'new_password' => $passwd     // new password
            ],
        ];

        // Execute password change
        try {
            $response2 = $client->put($url2, $options2);
            $responseBody2 = $response2->getBody()->getContents();
            $httpCode = $response2->getStatusCode();
        } catch (\Exception $e) {
            rcube::raise_error("Password plugin: Error on {$url2} : {$e->getMessage()}", true);
            return PASSWORD_CONNECT_ERROR;
        }

        // Check for success
        if ($httpCode >= 200 && $httpCode < 300) {
            return PASSWORD_SUCCESS;
        }

        // Log for debugging if needed
        rcube::raise_error("Password plugin: Unexpected response from {$url2}. HTTP {$httpCode}. Response: {$responseBody2}", true);
        return PASSWORD_CONNECT_ERROR;
    }
}
?>
