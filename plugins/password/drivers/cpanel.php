<?php

/**
 * cPanel Password Driver
 *
 * It uses Cpanel's Webmail UAPI to change the users password.
 *
 * This driver has been tested successfully with Digital Pacific hosting.
 *
 * @author Maikel Linke <maikel@email.org.au>
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
 */

class rcube_cpanel_password
{
    /**
     * Changes the user's password. It is called by password.php.
     * See "Driver API" README and password.php for the interface details.
     *
     * @param string $curpass  Current (old) password
     * @param string $newpass  New password
     * @param string $username Current username
     *
     * @return int|array Error code or assoc array with 'code' and 'message', see
     *                   "Driver API" README and password.php
     */
    public function save($curpass, $newpass, $username)
    {
        $client = password::get_http_client();

        $url = self::url();

        $options = [
            'auth' => [$username, $curpass],
            'form_params' => [
                'email' => password::username('%l'),
                'password' => $newpass,
            ],
            'http_errors' => true,
        ];

        try {
            $response = $client->post($url, $options);
            $response = $response->getBody()->getContents();
        } catch (Exception $e) {
            rcube::raise_error("Password plugin: Failed to post to {$url}: {$e->getMessage()}", true);

            return PASSWORD_ERROR;
        }

        return self::decode_response($response);
    }

    /**
     * Provides the UAPI URL of the Email::passwd_pop function.
     *
     * @return string HTTPS URL
     */
    public static function url()
    {
        $config = rcmail::get_instance()->config;
        $storage_host = $_SESSION['storage_host'];

        $host = $config->get('password_cpanel_host', $storage_host);
        $port = $config->get('password_cpanel_port', 2096);

        return "https://{$host}:{$port}/execute/Email/passwd_pop";
    }

    /**
     * Converts a UAPI response to a password driver response.
     *
     * @param string $response JSON response by the Cpanel UAPI
     *
     * @return mixed Response code or array, see <code>save</code>
     */
    public static function decode_response($response)
    {
        if (!$response) {
            return PASSWORD_CONNECT_ERROR;
        }

        // $result should be `null` or `stdClass` object
        $result = json_decode($response);

        // The UAPI may return HTML instead of JSON on missing authentication
        if ($result && isset($result->status) && $result->status === 1) {
            return PASSWORD_SUCCESS;
        }

        if ($result && !empty($result->errors) && is_array($result->errors)) {
            return [
                'code' => PASSWORD_ERROR,
                'message' => $result->errors[0],
            ];
        }

        return PASSWORD_ERROR;
    }
}
