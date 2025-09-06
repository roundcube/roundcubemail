<?php

/**
 * Zxcvb Password Strength Driver
 *
 * Driver to check password strength using Zxcvbn-PHP
 *
 * @version 0.1
 * @author Philip Weir
 *
 * Copyright (C) Philip Weir
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

class rcube_zxcvbn_password
{
    function strength_rules()
    {
        $rcmail = rcmail::get_instance();
        $rules  = [
            $rcmail->gettext('password.passwordnoseq'),
            $rcmail->gettext('password.passwordnocommon'),
        ];

        return $rules;
    }

    /**
     * Password strength check
     *
     * @param string $passwd Password
     *
     * @return array Score (1 to 5) and Reason
     */
    function check_strength($passwd)
    {
        if (!class_exists('ZxcvbnPhp\Zxcvbn')) {
            rcube::raise_error([
                    'code' => 600,
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => "Password plugin: Zxcvbn library not found."
                ], true, false
            );

            return;
        }

        $zxcvbn   = new ZxcvbnPhp\Zxcvbn();
        $strength = $zxcvbn->passwordStrength($passwd);

        return [$strength['score'] + 1, $strength['feedback']['warning']];
    }
}
