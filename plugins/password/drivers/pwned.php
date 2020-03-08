<?php

/**
 * Have I Been Pwned Password Strength Driver
 *
 * Driver to check passwords using HIBP:
 * https://haveibeenpwned.com/Passwords
 *
 * This driver will return a strength of:
 *     3: if the password WAS NOT found in HIBP
 *     1: if the password WAS found in HIBP
 *     2: if there was an ERROR retrieving data.
 *
 * To use this driver, configure (in ../config.inc.php):
 *
 * $config['password_strength_driver'] = 'pwned';
 * $config['password_minimum_score'] = 3;
 *
 * Set the minimum score to 3 if you want to make sure that all
 * passwords are successfully checked against HIBP (recommended).
 *
 * Set it to 2 if you still want to accept passwords in case a
 * HIBP check fails for some (technical) reason.
 *
 * Setting the minimum score to 1 or less effectively renders
 * the checks useless, as all passwords would be accepted.
 * Setting it to 4 or more will effectively reject all passwords.
 *
 * This driver will only return a maximum score of 3 because not
 * being listed in HIBP does not necessarily mean that the
 * password is a good one. It is therefore recommended to also
 * configure a minimum length for the password.
 *
 * Background reading (don't worry, your passwords are not sent anywhere):
 * https://www.troyhunt.com/ive-just-launched-pwned-passwords-version-2/#cloudflareprivacyandkanonymity
 *
 * @version 1.0
 * @author Christoph Langguth
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

class rcube_pwned_password
{
    // API URL. Note: the trailing slash is mandatory.
    const API_URL = 'https://api.pwnedpasswords.com/range/';

    // See https://www.troyhunt.com/enhancing-pwned-passwords-privacy-with-padding/
    const ENHANCED_PRIVACY_CURL = 1;

    // check result constants
    const CHECKED_NOT_LISTED = 0;
    const CHECKED_LISTED = 1;
    const CHECK_RUNTIME_ERROR = 2;

    const CONFIGURATION_ERROR = 'CONFIGURATION ERROR: Need curl or allow_url_fopen to check for compromised passwords';

    /**
     * Rule description.
     *
     * @return human-readable description of the check rule.
     */
    function strength_rules()
    {
        // show error message (only) if configuration won't allow to check for pwned passwords.
        if (!$this->can_retrieve()) {
            return array("<font size='+1' color='red'><b>" .self::CONFIGURATION_ERROR. "</b></font>");
        }

        // otherwise, show hint.
        $rc = rcmail::get_instance();
        return array($rc->gettext('password.pwned_mustnotbedisclosed'));
    }

    /**
     * Password strength check.
     * Return values:
     *     1 - if password is definitely compromised.
     *     2 - if status for password can't be determined (network failures etc.)
     *     3 - if password is not publicly known to be compromised.
     * @param string $passwd Password
     *
     * @return array Score (1 to 3) and Reason
     */
    function check_strength($passwd)
    {
        $result = $this->is_pwned($passwd);

        if ($result === self::CHECKED_NOT_LISTED) {
            // all good
            return array(3, null);
        } elseif ($result === self::CHECKED_LISTED) {
            // compromised password
            $rc = rcmail::get_instance();
            return array(1, $rc->gettext('password.pwned_isdisclosed'));
        } else {
            // other error message, return unchanged
            return array(2, $result);
        }
    }

    function is_pwned($passwd)
    {
        if (!($this->can_retrieve())) {
            return self::CONFIGURATION_ERROR;
        }

        list($prefix, $suffix) = $this->hash_split($passwd);

        $suffixes = $this->retrieve_suffixes(self::API_URL . $prefix);

        if ($suffixes) {
            $result = $this->is_in_list($suffix, $suffixes);
            if ($result !== self::CHECK_RUNTIME_ERROR) {
                return $result;
            }
        }

        // fallthrough: some error occurred while retrieving or parsing list
        $rc = rcmail::get_instance();
        return $rc->gettext('password.pwned_fetcherror');
    }

    function hash_split($passwd)
    {
        $hash = strtolower(sha1($passwd));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);
        return array($prefix, $suffix);
    }

    function can_retrieve()
    {
        return $this->can_curl() || $this->can_fopen();
    }

    function can_curl()
    {
        return (in_array('curl', get_loaded_extensions())
            && function_exists('curl_init'));
    }

    function can_fopen()
    {
        return ini_get('allow_url_fopen');
    }

    function retrieve_suffixes($url)
    {
        if ($this->can_curl()) {
            return $this->retrieve_curl($url);
        } else {
            return $this->retrieve_fopen($url);
        }
    }

    function retrieve_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (self::ENHANCED_PRIVACY_CURL == 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Add-Padding: true'));
        }
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function retrieve_fopen($url)
    {
        $output = '';
        $ch = fopen($url, 'r');
        while (!feof($ch)) {
            $output .= fgets($ch);
        }
        fclose($ch);
        return $output;
    }

    function is_in_list($candidate, $list)
    {
        // initialize to error message in case there are no lines at all
        $result = self::CHECK_RUNTIME_ERROR;

        foreach(preg_split('/[\r\n]+/', $list) as $line) {
            $line = strtolower($line);
            if (preg_match('/^([0-9a-f]{35}):(\d)+$/', $line, $matches)) {
                if (($matches[2] > 0) && ($matches[1] === $candidate)) {
                    // more than 0 occurrences, and suffix matches
                    // -> password is compromised
                    return self::CHECKED_LISTED;
                }
                // valid line, not matching the current password
                $result = self::CHECKED_NOT_LISTED;
            } else {
                // invalid line
                return self::CHECK_RUNTIME_ERROR;
            }
        }
        return $result;
    }
}
