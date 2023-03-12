<?php

/**
 * Roundcube password driver for generic HTTP APIs.
 *
 * This driver changes the e-mail password via any generic HTTP/HTTPS API.
 *
 * @author David Croft
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * Config variables:
 * $config['password_httpapi_url']         = 'https://passwordserver.example.org'; // required
 * $config['password_httpapi_method']      = 'POST'; // default
 * $config['password_httpapi_var_user']    = 'user'; // optional
 * $config['password_httpapi_var_curpass'] = 'curpass'; // optional
 * $config['password_httpapi_var_newpass'] = 'newpass'; // optional
 * $config['password_httpapi_expect']      = '/^ok$/i'; // optional
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

class rcube_httpapi_password
{
    /**
     * This method is called from roundcube to change the password
     *
     * roundcube already validated the old password so we just need to change it at this point
     *
     * @param string $curpass  Current password
     * @param string $newpass  New password
     * @param string $username Login username
     *
     * @return int PASSWORD_SUCCESS|PASSWORD_ERROR|PASSWORD_CONNECT_ERROR
     */
    function save($curpass, $newpass, $username)
    {
        $rcmail = rcmail::get_instance();
        $client = password::get_http_client();

        // Get configuration with defaults
        $url         = $rcmail->config->get('password_httpapi_url');
        $method      = $rcmail->config->get('password_httpapi_method', 'POST');
        $var_user    = $rcmail->config->get('password_httpapi_var_user');
        $var_curpass = $rcmail->config->get('password_httpapi_var_curpass');
        $var_newpass = $rcmail->config->get('password_httpapi_var_newpass');
        $expect      = $rcmail->config->get('password_httpapi_expect');

        // Set the variables on the GET query string or POST vars
        $vars = [];

        if ($var_user) {
            $vars[$var_user] = $username;
        }

        if ($var_curpass) {
            $vars[$var_curpass] = $curpass;
        }

        if ($var_newpass) {
            $vars[$var_newpass] = $newpass;
        }

        $method = strtoupper($method);
        $params = [];

        if ($method == 'POST') {
            $params['form_params'] = $vars;
        }
        else if ($method == 'GET') {
            $params['query'] = $vars;
        }
        else {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid httpapi method",
                ],
                true, false
            );

            return PASSWORD_CONNECT_ERROR;
        }

        try {
            $response = $client->request($method, $url, $params);

            $response_code = $response->getStatusCode();
            $result        = $response->getBody();
        }
        catch (Exception $e) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: " . $e->getMessage()
                ],
                true, false
            );

            return PASSWORD_CONNECT_ERROR;
        }

        // Non-2xx response codes mean the password change failed
        if ($response_code < 200 || $response_code > 299) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Unexpected response code {$response_code}: "
                        . substr($result, 0, 1024)
                ],
                true, false
            );

            return ($response_code == 404 || $response_code > 499) ? PASSWORD_CONNECT_ERROR : PASSWORD_ERROR;
        }

        // If configured, check the body of the response
        if ($expect && !preg_match($expect, $result)) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Unexpected response body: " . substr($result, 0, 1024)
                ],
                true, false
            );

            return PASSWORD_ERROR;
        }

        return PASSWORD_SUCCESS;
    }
}
