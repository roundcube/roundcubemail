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

    // Score constants, these directly correspond to the score that is returned.
    const SCORE_LISTED = 1;
    const SCORE_ERROR = 2;
    const SCORE_NOT_LISTED = 3;

    /**
     * Rule description.
     *
     * @return array human-readable description of the check rule.
     */
    function strength_rules()
    {
        $rc = rcmail::get_instance();
        $href = 'https://haveibeenpwned.com/Passwords';

        return [$rc->gettext(['name' => 'password.pwned_mustnotbedisclosed', 'vars' => ['href' => $href]])];
    }

    /**
     * Password strength check.
     * Return values:
     *     1 - if password is definitely compromised.
     *     2 - if status for password can't be determined (network failures etc.)
     *     3 - if password is not publicly known to be compromised.
     *
     * @param string $passwd Password
     *
     * @return array password score (1 to 3) and (optional) reason message
     */
    function check_strength($passwd)
    {
        $score   = $this->check_pwned($passwd);
        $message = null;

        if ($score !== self::SCORE_NOT_LISTED) {
            $rc = rcmail::get_instance();
            if ($score === self::SCORE_LISTED) {
                $message = $rc->gettext('password.pwned_isdisclosed');
            }
            else {
                $message = $rc->gettext('password.pwned_fetcherror');
            }
        }

        return [$score, $message];
    }

    /**
     * Check password using HIBP.
     *
     * @param string $passwd
     *
     * @return int score, one of the SCORE_* constants (between 1 and 3).
     */
    function check_pwned($passwd)
    {
        // initialize with error score
        $result = self::SCORE_ERROR;

        if (!$this->can_retrieve()) {
            // Log the fact that we cannot check because of configuration error.
            rcube::raise_error("Need curl or allow_url_fopen to check password strength with 'pwned'", true, true);
        }
        else {
            list($prefix, $suffix) = $this->hash_split($passwd);

            $suffixes = $this->retrieve_suffixes(self::API_URL . $prefix);

            if ($suffixes) {
                $result = $this->check_suffix_in_list($suffix, $suffixes);
            }
        }

        return $result;
    }

    function hash_split($passwd)
    {
        $hash   = strtolower(sha1($passwd));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        return [$prefix, $suffix];
    }

    function can_retrieve()
    {
        return $this->can_curl() || $this->can_fopen();
    }

    function can_curl()
    {
        return function_exists('curl_init');
    }

    function can_fopen()
    {
        return ini_get('allow_url_fopen');
    }

    function retrieve_suffixes($url)
    {
        if ($this->can_curl()) {
            return $this->retrieve_curl($url);
        }
        else {
            return $this->retrieve_fopen($url);
        }
    }

    function retrieve_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (self::ENHANCED_PRIVACY_CURL == 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Add-Padding: true']);
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

    function check_suffix_in_list($candidate, $list)
    {
        // initialize to error in case there are no lines at all
        $result = self::SCORE_ERROR;

        foreach (preg_split('/[\r\n]+/', $list) as $line) {
            $line = strtolower($line);

            if (preg_match('/^([0-9a-f]{35}):(\d+)$/', $line, $matches)) {
                if ($matches[2] > 0 && $matches[1] === $candidate) {
                    // more than 0 occurrences, and suffix matches
                    // -> password is compromised
                    return self::SCORE_LISTED;
                }

                // valid line, not matching the current password
                $result = self::SCORE_NOT_LISTED;
            }
            else {
                // invalid line
                return self::SCORE_ERROR;
            }
        }

        return $result;
    }
}
