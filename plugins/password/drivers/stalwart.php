<?php

/**
 * Stalwart Password Driver
 *
 * Permissions required for the Stalwart API key:
 * - Retrieve specific account information => On
 * - Modify user account information       => On
 *
 * @author Armand Vignat <armand@vignat.org>
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
 */

class rcube_stalwart_password
{
    private function hash_password($password)
    {
        $salt = bin2hex(random_bytes(16));
        return crypt($password, '$6$' . $salt . '$');
    }

    private function hash_slated_password($password, $salt)
    {
        return crypt($password, '$6$' . $salt . '$');
    }

    private function fetch_user($username)
    {
        $config = rcmail::get_instance()->config;
        $url = $config->get('password_stalwart_api_host');
        $token = $config->get('password_stalwart_api_token');

        $client = password::get_http_client();
        $response = $client->request('GET', $url . '/principal/' . $username, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        $code = $response->getStatusCode();
        $resp = (string) $response->getBody();

        if ($code !== 200) {
            throw new \Exception("Failed to fetch data, HTTP status {$code}");
        }

        return json_decode($resp, true);
    }

    public function save($curpass, $newpass, $username)
    {
        $client = password::get_http_client();
        $config = rcmail::get_instance()->config;
        $url = $config->get('password_stalwart_api_host');
        $token = $config->get('password_stalwart_api_token');

        try {
            $data = $this->fetch_user($username);

            if (!isset($data['data']['secrets'])) {
                return PASSWORD_ERROR;
            }

            $current_api_entry = null;
            foreach ($data['data']['secrets'] as $entry) {
                if (!str_starts_with($entry, '$6$')) {
                    continue;
                }

                $parts = explode('$', $entry);
                if (count($parts) < 4) {
                    continue;
                }

                $salt = $parts[2];
                $hash = $this->hash_slated_password($curpass, $salt);
                if ($hash === $entry) {
                    $current_api_entry = $entry;
                    break;
                }
            }

            if ($current_api_entry === null) {
                return PASSWORD_ERROR;
            }

            $payload = [
                [
                    'action' => 'addItem',
                    'field' => 'secrets',
                    'value' => $this->hash_password($newpass),
                ],
                [
                    'action' => 'removeItem',
                    'field' => 'secrets',
                    'value' => $current_api_entry,
                ],
            ];

            $response = $client->request('PATCH', $url . '/principal/' . urlencode($data['data']['name']), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $code = $response->getStatusCode();
            $resp = (string) $response->getBody();

            if ($code !== 200) {
                return PASSWORD_ERROR;
            }

            return PASSWORD_SUCCESS;
        } catch (\Exception $e) {
            // on any failure return the constant rather than throwing
            return PASSWORD_ERROR;
        }
    }
}
