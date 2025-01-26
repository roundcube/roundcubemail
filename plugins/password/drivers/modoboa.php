<?php

/**
 * Modoboa Password Driver
 *
 * Payload is json string containing username, oldPassword and newPassword
 * Return value is a json string saying result: true if success.
 *
 * @version 1.0.1
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
    function save($curpass, $passwd)
    {
        // Init config access
        $rcmail           = rcmail::get_instance();
        $ModoboaToken     = $rcmail->config->get('password_modoboa_api_token');
        $RoudCubeUsername = $_SESSION['username'];
        $IMAPhost         = $_SESSION['imap_host'];

        // Call GET to fetch values from modoboa server
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://" . $IMAPhost . "/api/v1/accounts/?search=" . urlencode($RoudCubeUsername),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => [
                "Authorization: Token " . $ModoboaToken,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
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
        $ret['username'] = $decoded[0]->username;
        $ret['mailbox']  = $decoded[0]->mailbox;
        $ret['role']     = $decoded[0]->role;
        $ret['password'] = $passwd; // new password
        $encoded         = json_encode($ret);

        // Call HTTP API Modoboa
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://" . $IMAPhost . "/api/v1/accounts/" . $userid . "/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_POSTFIELDS     => "" . $encoded . "",
            CURLOPT_HTTPHEADER     => [
                "Authorization: Token " . $ModoboaToken,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return PASSWORD_CONNECT_ERROR;
        }

        return PASSWORD_SUCCESS;
    }
}
