<?php

use GuzzleHttp\Psr7\Query;

/**
 * DirectAdmin Password Driver
 *
 * Driver to change passwords via DirectAdmin Control Panel
 *
 * @version 3.0
 *
 * @author Victor Benincasa <vbenincasa @ gmail.com>
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

class rcube_directadmin_password
{
    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        $da_user = $_SESSION['username'];
        $da_curpass = $curpass;
        $da_newpass = $passwd;
        $da_host = $rcmail->config->get('password_directadmin_host');
        $da_port = $rcmail->config->get('password_directadmin_port');

        if (strpos($da_user, '@') === false) {
            return ['code' => PASSWORD_ERROR, 'message' => 'Change the SYSTEM user password through control panel!'];
        }

        $da_host = str_replace('%h', $_SESSION['imap_host'], $da_host);
        $da_host = str_replace('%d', $rcmail->user->get_username('domain'), $da_host);

        if (!is_numeric($da_port)) {
            $da_port = 2222;
        }

        if (strpos($da_host, '://') === false) {
            $da_host = 'https://' . $da_host;
        }

        $client = password::get_http_client();

        $url = "{$da_host}:{$da_port}/CMD_CHANGE_EMAIL_PASSWORD";
        $options = [
            'http_errors' => true,
            'form_params' => [
                'email' => $da_user,
                'oldpassword' => $da_curpass,
                'password1' => $da_newpass,
                'password2' => $da_newpass,
                'api' => '1',
            ],
        ];

        try {
            $response = $client->post($url, $options);

            $body = $response->getBody()->getContents();
            $body = preg_replace_callback('/&#([0-9]{2})/', static function ($val) { return chr($val[1]); }, $body);

            $response = Query::parse($body);
        } catch (Exception $e) {
            rcube::raise_error("Password plugin: Error fetching {$url} : {$e->getMessage()}", true);
            return PASSWORD_ERROR;
        }

        if (isset($response['error']) && $response['error'] == 1) {
            return ['code' => PASSWORD_ERROR, 'message' => strip_tags($response['text'])];
        }

        return PASSWORD_SUCCESS;
    }
}
