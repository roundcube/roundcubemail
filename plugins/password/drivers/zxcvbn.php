<?php

/**
 * Zxcvb Password Strength Driver
 *
 * Driver to check password strength using Zxcvbn-PHP
 *
 * @version 0.1
 * @author Philip Weir
 *
 * Copyright (C) 2018 Philip Weir
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

use ZxcvbnPhp\Zxcvbn;

class rcube_zxcvbn_password
{
    function strength_rules()
    {
        $rcmail  = rcmail::get_instance();

        $rules   = array();
        $rules[] = $rcmail->gettext('password.passwordweak');
        $rules[] = $rcmail->gettext('password.passwordnoseq');
        $rules[] = $rcmail->gettext('password.passwordnocommon');

        return $rules;
    }

    function check_strength($passwd)
    {
        $rcmail   = rcmail::get_instance();
        $zxcvbn   = new Zxcvbn();
        $strength = $zxcvbn->passwordStrength($passwd);
        $result   = null;

        if ($strength['score'] < $rcmail->config->get('password_zxcvbn_min_score', 3)) {
            $reason = $strength['feedback']['warning'];
            $result = $rcmail->gettext(array('name' => 'password.passwordweakreason', 'vars' => array('reason' => $reason)));
        }

        return $result;
    }
}
