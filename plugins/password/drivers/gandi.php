<?php

/**
 * Roundcube password driver for gandi mail.
 *
 * This driver changes the e-mail password via the gandi mail API.
 *
 * @author Aaron Hermann
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * Configuration can either be the absolute path to a JSON file,
 * or an array which contains the key-value mapping:
 * $config['password_gandi_apikeys'] = '/absolute/path/to/somewhere';
 * $config['password_gandi_apikeys'] = array(
 *     'example.com' => 'first-api-key',
 *     'foo.com' => 'second-api-key',
 * );
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


class rcube_gandi_password
{
    // constants
    private const GANDI_MIN_PASS_LENGTH = 8;
    private const GANDI_MAX_PASS_LENGTH = 200;
    private const GANDI_MIN_PASS_NUMS = 3;
    private const GANDI_API_URL = 'https://api.gandi.net/v5';
    private const GANDI_API_SUCCESS_MSG = 'The email mailbox is being updated.';
    private const PASSWORD_ALGO = 'sha512-crypt';

    /**
     * This method is called from roundcube to change the password
     *
     * roundcube already validated the old password so we just need to change it at this point
     *
     * @param string $curpass  Current password
     * @param string $newpass  New password
     * @param string $username The username
     *
     * @return int PASSWORD_SUCCESS|PASSWORD_ERROR|PASSWORD_CONNECT_ERROR
     */
    public function save($curpass, $newpass, $username)
    {
        // get configuration
        $passformat = rcmail::get_instance()->config->get('password_username_format');
        $minpasslength = rcmail::get_instance()->config->get('password_minimum_length');
        $passalgo = rcmail::get_instance()->config->get('password_algorithm');
        $apikey = rcmail::get_instance()->config->get('password_gandi_apikeys');
        $userdom = explode('@', $username);

        // log error and return if config is invalid
        if (strcmp($passformat, '%u') !== 0 || is_int($minpasslength) === false || $minpasslength < self::GANDI_MIN_PASS_LENGTH
         || $minpasslength > (self::GANDI_MAX_PASS_LENGTH - self::GANDI_MIN_PASS_LENGTH) || strcasecmp($passalgo, self::PASSWORD_ALGO) !== 0 || empty($apikey)) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid configuration option for 'password_username_format', 'password_minimum_length', 'password_algorithm' or 'password_gandi_apikeys'. Refer to the README for more information.",
                ],
                true, false
            );
            return array('code' => PASSWORD_ERROR, 'message' => 'Invalid configuration.');
        }

        // try to get the api-key and log error & return on failure
        $result = $this->getApiKey($apikey, $userdom[1]);
        if ($result['result'] === false) {
            if (empty($result['msg']) === false) {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Problem with configuration option 'password_gandi_apikeys': ".$result['msg'],
                    ],
                    true, false
                );
            }
            return array('code' => PASSWORD_ERROR, 'message' => $result['msg']);
        }

        // assign api-key and initialize curl
        $apikey = $result['msg'];
        $curl = curl_init();

        // set curl options
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ));

        // try to get the mailbox id
        $result = $this->reqMailboxId($curl, $userdom[0], $userdom[1], $apikey);

        // log error and cleanup if required
        if ($result['code'] !== PASSWORD_SUCCESS) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Failed to get mailbox id of '".$username."': ".$result['msg'],
                ],
                true, false
            );
            goto cleanup;
        }

        // hash password and try to apply it
        $hashedpass = password::hash_password($newpass);
        $result = $this->tellPassword($curl, $userdom[1], $hashedpass, $result['msg'], $apikey);

        // log error if required
        if ($result['code'] !== PASSWORD_SUCCESS) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Failed to set new password for '".$username."': ".$result['msg'],
                ],
                true, false
            );
        }

        // cleanup curl handle
        cleanup:
        curl_close($curl);

        // return result
        return array('code' => $result['code'], message => $result['msg']);
    }

    /**
     * Password strength check.
     * Return values:
     *     1 - if password is to weak.
     *     2 - if password is strong enough.
     *
     * @param string $passwd Password
     *
     * @return array password score (1 to 2) and (optional) reason message
     */
    public function check_strength($newpass)
    {
        // variables
        $passminscore = rcmail::get_instance()->config->get('password_minimum_score');
        $passlenght = strlen($newpass);

        // log and return if config invalid
        if (is_numeric($passminscore) === false || $passminscore !== 2) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid configuration option for 'password_minimum_score'. Refer to the README for more information.",
                ],
                true, false
            );
            return [1, 'Invalid configuration.'];
        }

        // get password properties
        $uppercase = preg_match('@[A-Z]@', $newpass);
        $numbers = preg_match_all('@[0-9]@', $newpass);
        $specialChars = preg_match('@[^\w]@', $newpass);

        // return if password is not strong enough
        if($passlenght < self::GANDI_MIN_PASS_LENGTH || $passlenght > self::GANDI_MAX_PASS_LENGTH
        || !$uppercase || $numbers < self::GANDI_MIN_PASS_NUMS || !$specialChars) {
            return [1, $this->strength_rules()];
        }

        // success
        return [2, ''];
    }

    /**
     * Password strength rules.
     *
     * @return string The strenght rules
     */
    public function strength_rules()
    {
        // return message
        return sprintf("Password must contain between %s and %s characters and contain at least 1 upper-case letter, %d numbers,
                        and a special character.", self::GANDI_MIN_PASS_LENGTH, self::GANDI_MAX_PASS_LENGTH, self::GANDI_MIN_PASS_NUMS);
    }


    /**
     * Get the users api-key
     *
     * @param string $apikey The API-key field of the config
     * @param string $domain The domain name
     *
     * @return array ['result' => true/false, 'msg' => 'api_key_or_error_message']
     */
    private function getApiKey($apikey, $domain)
    {
        // check if API-keys are stored in file
        if (is_array($apikey) === false)
        {
            // read file and return on failure
            $json = file_get_contents($apikey);
            if ($json === false) {
                return array('result' => false, 'msg' => "Failed to open JSON file.");
            }

            // parse json and return on failure
            $json = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return array('result' => false, 'msg' => "Failed to parse JSON file.");
            }

            // assign api-key
            $apikey = $json[$domain];
        }
        // API-keys are stored in an array => assign api-key directly
        else {
            $apikey = $apikey[$domain];
        }

        // return if api-key invalid
        if (empty($apikey)) {
            return array('result' => false, 'msg' => '');
        }

        // return result
        return array('result' => true, 'msg' => $apikey);
    }

    /**
     * Request the users mailbox id from the server
     *
     * @param int    $curl     The curl handle
     * @param string $username The username
     * @param string $domain   The domain name
     * @param string $apikey   The api-key
     *
     * @return array ['code' => 'result_code', 'msg' => 'id_or_error_message']
     */
    private function reqMailboxId($curl, $username, $domain, $apikey)
    {
        // add curl options
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%s/email/mailboxes/%s', self::GANDI_API_URL, $domain),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => "login=".$username,
            CURLOPT_HTTPHEADER => array(
                "authorization: Apikey ".$apikey,
            ),
        ));

        // read and parse response
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $json = json_decode(trim($response, '[]'), true);

        // return if failed
        if ($err) {
            return array('code' => PASSWORD_CONNECT_ERROR, 'msg' => $err);
        }
        else if (json_last_error() !== JSON_ERROR_NONE) {
            return array('code' => PASSWORD_ERROR, 'msg' => 'Failed to parse JSON.');
        }
        else if (empty($json['id'])) {
            return array('code' => PASSWORD_ERROR, 'msg' => (empty($json['message']) ? 'unknown.' : $json['message']));
        }

        // return result
        return array('code' => PASSWORD_SUCCESS, 'msg' => $json['id']);
    }

    /**
     * Send the new password to the server
     *
     * @param int    $curl      The curl handle
     * @param string $domain    The domain name
     * @param string $newpass   The new password
     * @param string $mailboxid The mailbox id of the users mailbox
     * @param string $apikey    The api-key
     *
     * @return array ['code' => 'result_code', 'msg' => 'error_message']
     */
    private function tellPassword($curl, $domain, $newpass, $mailboxid, $apikey)
    {
        // overrride curl options
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%s/email/mailboxes/%s/%s', self::GANDI_API_URL, $domain, $mailboxid),
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => "{\"password\":\"".$newpass."\"}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Apikey ".$apikey,
                'content-type: application/json'
            ),
        ));

        // read and parse response
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $json = json_decode(trim($response, '[]'), true);

        // return if failed
        if ($err) {
            return array('code' => PASSWORD_CONNECT_ERROR, 'msg' => $err);
        }
        else if (json_last_error() !== JSON_ERROR_NONE) {
            return array('code' => PASSWORD_ERROR, 'msg' => 'Failed to parse JSON.');
        }
        else if (empty($json['message']) || stripos($json['message'], self::GANDI_API_SUCCESS_MSG) === false) {
            return array('code' => PASSWORD_ERROR, 'msg' => (empty($json['message']) ? 'unknown.' : $json['message']));
        }

        // success
        return array('code' => PASSWORD_SUCCESS, 'msg' => '');
    }
}
