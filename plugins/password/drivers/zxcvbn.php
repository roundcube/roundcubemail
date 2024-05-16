<?php

use ZxcvbnPhp\Zxcvbn;

/**
 * Zxcvb Password Strength Driver
 *
 * Driver to check password strength using Zxcvbn-PHP
 *
 * @version 0.1
 *
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
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_zxcvbn_password
{
    public function strength_rules()
    {
        $rcmail = rcmail::get_instance();
        $rules = [
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
    public function check_strength($passwd)
    {
        if (!class_exists('ZxcvbnPhp\Zxcvbn')) {
            rcube::raise_error('Password plugin: Zxcvbn library not found.', true, true);
        }

        $rcmail = rcmail::get_instance();
        $userData = [
            $rcmail->user->get_username('local'),
            $_SESSION['username'],
        ];

        $zxcvbn = new Zxcvbn(); // @phpstan-ignore-line
        $strength = $zxcvbn->passwordStrength($passwd, $userData); // @phpstan-ignore-line

        return [$strength['score'] + 1, $strength['feedback']['warning']];
    }
}
