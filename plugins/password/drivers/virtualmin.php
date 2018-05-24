<?php

/**
 * Virtualmin Password Driver
 *
 * Driver that adds functionality to change the users Virtualmin password.
 * The code is derrived from the Squirrelmail "Change Cyrus/SASL Password" Plugin
 * by Thomas Bruederli.
 *
 * It only works with virtualmin on the same host where Roundcube runs
 * and requires shell access and gcc in order to compile the binary.
 *
 * @version 3.0
 * @author Martijn de Munnik
 *
 * Copyright (C) 2005-2013, The Roundcube Dev Team
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

class rcube_virtualmin_password
{
    function save($currpass, $newpass, $username)
    {
        $rcmail   = rcmail::get_instance();
        $curdir   = RCUBE_PLUGINS_DIR . 'password/helpers';
        $username = escapeshellarg($username);

        // Get the domain using virtualmin CLI:
        exec("$curdir/chgvirtualminpasswd list-domains --mail-user $username --name-only", $output_domain, $returnvalue);

        if ($returnvalue == 0 && count($output_domain) == 1) {
            $domain = trim($output_domain[0]);
        }
        else {
            rcube::raise_error(array(
                'code' => 600,
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $curdir/chgvirtualminpasswd or domain for mail-user '$username' not known to Virtualmin"
                ), true, false);

            return PASSWORD_ERROR;
        }

        $domain  = escapeshellarg($domain);
        $newpass = escapeshellarg($newpass);

        exec("$curdir/chgvirtualminpasswd modify-user --domain $domain --user $username --pass $newpass", $output, $returnvalue);

        if ($returnvalue == 0) {
            return PASSWORD_SUCCESS;
        }

        rcube::raise_error(array(
                'code' => 600,
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to execute $curdir/chgvirtualminpasswd"
            ), true, false);

        return PASSWORD_ERROR;
    }
}
