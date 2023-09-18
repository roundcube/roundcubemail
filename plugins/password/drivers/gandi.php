<?php

/**
 * Roundcube password driver for gandi mail.
 *
 * This driver changes the user's password via gandi.net mail- API and
 * verifies the password strength according to official documentation.
 *
 * @author Aaron Hermann
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * The configuration can take various forms depending on your needs.
 *     (1) The raw Personal Access Token (PAT) or an array containing the key-value mapping.
 *         $config['password_gandi_pats'] = 'your-pat';
 *         $config['password_gandi_pats'] = array(
 *             'example.com' => 'first-pat',
 *             'foo.com' => 'second-pat',
 *         );
 *     (2) The absolute path to a file containing either the raw Personal Access Token (PAT) or a key-value mapping in JSON format.
 *         $config['password_gandi_pats'] = '/absolute/path/to/somewhere';
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
    private const PASSWORD_ALGO = 'sha512-crypt';
    private const GANDI_API_URL = 'https://api.gandi.net/v5';
    private const GANDI_API_SUCCESS_MSG = 'The email mailbox is being updated.';
    private const GANDI_PASS_MIN_LENGTH = 8;
    private const GANDI_PASS_MAX_LENGTH = 200;
    private const GANDI_PASS_AMOUNT_UCASE = 1;
    private const GANDI_PASS_AMOUNT_NUMS = 3;
    private const GANDI_PASS_AMOUNT_SCHARS = 1;

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
        // get roundcube instance and configuration
        $rcmail = rcmail::get_instance();
        $passformat = $rcmail->config->get('password_username_format');
        $passalgo = $rcmail->config->get('password_algorithm');
        $pat = $rcmail->config->get('password_gandi_pats');
        $userdom = explode('@', $username);

        // log error and return if config is invalid
        if (strcmp($passformat, '%u') !== 0 || strcasecmp($passalgo, self::PASSWORD_ALGO) !== 0) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid configuration option for 'password_username_format' or 'password_algorithm'. Refer to the README for more information.",
                ],
                true, false
            );
            return PASSWORD_ERROR;
        }

        // try to get the pat and log error & return on failure
        $result = $this->get_pat($pat, $userdom[1]);
        if ($result['result'] === false) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid configuration option for 'password_gandi_pats': ".$result['msg'],
                ],
                true, false
            );
            return PASSWORD_ERROR;
        }

        // assign pat and initialize curl
        $pat = $result['msg'];
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
        $result = $this->req_mailbox_id($curl, $userdom[0], $userdom[1], $pat);

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
        $result = $this->tell_password($curl, $userdom[1], $hashedpass, $result['msg'], $pat);

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
        return $result['code'];
    }

    /**
     * Password strength check.
     * Return values:
     *     1 - if password is to weak.
     *     2 - if password is strong enough.
     *
     * @param string $passwd Password
     *
     * @return array Password score (1 to 2) and (optional) reason message
     */
    public function check_strength($newpass)
    {
        // variables
        $rcmail = rcmail::get_instance();
        $passminscore = $rcmail->config->get('password_minimum_score');
        $passlenght = strlen($newpass);

        // log and return if config invalid
        if (is_numeric($passminscore) === false || $passminscore !== 2) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Invalid configuration option for 'password_minimum_score'. Refer to the README for more information.",
                ],
                true, false
            );
            return array(1, $rcmail->gettext('errortitle'));
        }

        // get password properties
        $uppercase = preg_match_all('@[A-Z]@', $newpass);
        $numbers = preg_match_all('@[0-9]@', $newpass);
        $specialchars = preg_match_all('@[^\w]@', $newpass);

        // return if password is not strong enough
        if($passlenght < self::GANDI_PASS_MIN_LENGTH || $passlenght > self::GANDI_PASS_MAX_LENGTH || $uppercase < self::GANDI_PASS_AMOUNT_UCASE
        || $numbers < self::GANDI_PASS_AMOUNT_NUMS || $specialchars < self::GANDI_PASS_AMOUNT_SCHARS) {
            return array(1, $this->strength_rules());
        }

        // success
        return array(2, '');
    }

    /**
     * Password strength rules.
     *
     * @return string The strength rules
     */
    public function strength_rules()
    {
        // return strength rules
        return rcmail::get_instance()->gettext(['name' => 'password.gandi_strengthrules',
                                                'vars' => ['minlen' => self::GANDI_PASS_MIN_LENGTH,
                                                           'maxlen' => self::GANDI_PASS_MAX_LENGTH,
                                                           'uppercase' => self::GANDI_PASS_AMOUNT_UCASE,
                                                           'nums' => self::GANDI_PASS_AMOUNT_NUMS,
                                                           'specialchars' => self::GANDI_PASS_AMOUNT_SCHARS,
                                                          ],
                                               ],
                                       );
    }


    /**
     * Get the users personal access token (pat)
     *
     * @param string $pat    The pat field from the config
     * @param string $domain The domain name
     *
     * @return array [
     *                'result' => (boolean) Result of the operation.
     *                'msg'    => (string) pat or error message if 'result' is equal to false.
     *               ]
     */
    private function get_pat($pat, $domain)
    {
        // assign pats directly if they are stored in an array
        if (is_array($pat)) {
            $pat = $pat[$domain];
        }
        // check if pats are stored in file
        else if (strpos($pat, '/') !== false) {
            // try to read file
            $file = @file_get_contents($pat);

            // return if the file could not be opened or is empty
            if ($file === false) {
                return array('result' => false, 'msg' => 'The pat file could not be opened.');
            }
            else if (empty($file)) {
                return array('result' => false, 'msg' => "No mapping was found for '".$domain."'.");
            }

            // remove all newlines and try to parse json
            $file = str_replace("\n", '', $file);
            $json = json_decode($file, true);

            // try to assign the pat appropriately and return on failure
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $pat = $json[$domain];
            }
            else if (preg_match('@"|\\\\|[[:cntrl:]]@', $file) === 0) {
                $pat = $file;
            }
            else {
                return array('result' => false, 'msg' => 'The pat file could not be interpreted.');
            }
        }

        // return if pat invalid
        if (empty($pat)) {
            return array('result' => false, 'msg' => "No mapping was found for '".$domain."'.");
        }

        // return result
        return array('result' => true, 'msg' => $pat);
    }

    /**
     * Request the users mailbox id from the server
     *
     * @param int    $curl     The curl handle
     * @param string $username The username
     * @param string $domain   The domain name
     * @param string $pat      The personal access token (pat)
     *
     * @return array [
     *                'code' => (int) PASSWORD_SUCCESS|PASSWORD_ERROR|PASSWORD_CONNECT_ERROR
     *                'msg'  => (int|string) Mailbox id or error message if 'code' is not equal to 'PASSWORD_SUCCESS'.
     *               ]
     */
    private function req_mailbox_id($curl, $username, $domain, $pat)
    {
        // add curl options
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%s/email/mailboxes/%s?login=%s', self::GANDI_API_URL, $domain, $username),
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$pat,
            ),
        ));

        // read response
        $response = curl_exec($curl);
        $err = curl_error($curl);

        // return if failed
        if ($err) {
            return array('code' => PASSWORD_CONNECT_ERROR, 'msg' => $err);
        }

        // try to parse JSON
        $json = json_decode(trim($response, '[]'), true);

        // return if failed
        if (json_last_error() !== JSON_ERROR_NONE) {
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
     * @param string $pat       The personal access token (pat)
     *
     * @return array [
     *                'code' => (int) PASSWORD_SUCCESS|PASSWORD_ERROR|PASSWORD_CONNECT_ERROR
     *                'msg'  => (string) Optional error message.
     *               ]
     */
    private function tell_password($curl, $domain, $newpass, $mailboxid, $pat)
    {
        // overrride curl options
        curl_setopt_array($curl, array(
            CURLOPT_URL => sprintf('%s/email/mailboxes/%s/%s', self::GANDI_API_URL, $domain, $mailboxid),
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => "{\"password\":\"".$newpass."\"}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$pat,
                'content-type: application/json'
            ),
        ));

        // read response
        $response = curl_exec($curl);
        $err = curl_error($curl);

        // return if failed
        if ($err) {
            return array('code' => PASSWORD_CONNECT_ERROR, 'msg' => $err);
        }

        // try to parse JSON
        $json = json_decode(trim($response, '[]'), true);

        // return if failed
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('code' => PASSWORD_ERROR, 'msg' => 'Failed to parse JSON.');
        }
        else if (empty($json['message']) || stripos($json['message'], self::GANDI_API_SUCCESS_MSG) === false) {
            return array('code' => PASSWORD_ERROR, 'msg' => (empty($json['message']) ? 'unknown.' : $json['message']));
        }

        // success
        return array('code' => PASSWORD_SUCCESS, 'msg' => '');
    }
}
