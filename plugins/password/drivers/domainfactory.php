<?php

/**
 * domainFACTORY Password Driver
 *
 * Driver to change passwords with the hosting provider domainFACTORY.
 * http://www.df.eu/
 *
 * @version 2.1
 *
 * @author Till Krüss <me@tillkruess.com>
 *
 * @see http://tillkruess.com/projects/roundcube/
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

class rcube_domainfactory_password
{
    public function save($curpass, $passwd, $username)
    {
        $client = password::get_http_client();
        $options = ['http_errors' => true];
        $url = 'https://ssl.df.eu/chmail.php';

        try {
            // initial login
            $options['form_params'] = [
                'login' => $username,
                'pwd' => $curpass,
                'action' => 'change',
            ];

            $response = $client->post($url, $options);
            $response = $response->getBody()->getContents();

            // login successful, get token!
            $options['form_params'] = [
                'pwd1' => $passwd,
                'pwd2' => $passwd,
                'action[update]' => 'Speichern',
            ];

            preg_match_all('~<input name="(.+?)" type="hidden" value="(.+?)">~i', $response, $fields);
            foreach ($fields[1] as $field_key => $field_name) {
                $options['form_params'][$field_name] = $fields[2][$field_key];
            }

            // change password
            $response = $client->post($url, $options);
            $response = $response->getBody()->getContents();

            // has the password been changed?
            if (strpos($response, 'Einstellungen erfolgreich') !== false) {
                return PASSWORD_SUCCESS;
            }

            // show error message(s) if possible
            if (strpos($response, '<div class="d-msg-text">') !== false) {
                preg_match_all('#<div class="d-msg-text">(.*?)</div>#s', $response, $errors);
                if (isset($errors[1])) {
                    $error_message = '';
                    foreach ($errors[1] as $error) {
                        $error_message .= trim(rcube_charset::convert($error, 'ISO-8859-15')) . ' ';
                    }

                    return ['code' => PASSWORD_ERROR, 'message' => $error_message];
                }
            }
        } catch (Exception $e) {
            rcube::raise_error("Password plugin: Error fetching {$url} : {$e->getMessage()}", true);
            return PASSWORD_CONNECT_ERROR;
        }

        return PASSWORD_ERROR;
    }
}
