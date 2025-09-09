<?php

/**
 * Mailcow Password Driver
 *
 * @version 1.0
 * @author Lukas "Hexaris" Matula <hexaris@gbely.net>
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
 * It is necessary to set the following variables in plugin/password/config.inc.php
 * $config['password_driver'] = 'mailcow';
 * $config['password_mailcow_api_host'] = '';
 * $config['password_mailcow_api_token'] = '';
  */

class rcube_mailcow_password
{
    function save($curpass, $passwd, $username)
    {
        $rcmail = rcmail::get_instance();

        $host  = $rcmail->config->get('password_mailcow_api_host');
        $token = $rcmail->config->get('password_mailcow_api_token');

        try {
            $client = password::get_http_client();

            $headers = [
                'X-API-Key' => $token,
                'accept'    => 'application/json'
            ];

            $cowdata = [
                'attr' => [
                    'password'  => $passwd,
                    'password2' => $passwd
                ],
                'items' => [ $username ]
            ];

            if (!strpos($host, '://')) {
                $host = "https://{$host}";
            }

            $response = $client->post("{$host}/api/v1/edit/mailbox", [
                'headers' => $headers,
                'json'    => $cowdata
            ]);

            $cowreply = json_decode($response->getBody(),true);

            if ($cowreply[0]['type'] == 'success') {
                return PASSWORD_SUCCESS;
            }

            return PASSWORD_ERROR;
        }
        catch (Exception $e) {
            $result = $e->getMessage();
        }

        rcube::raise_error([
                'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Problem with Mailcow API: $result",
            ],
            true, false
        );

        return PASSWORD_CONNECT_ERROR;
    }
}
