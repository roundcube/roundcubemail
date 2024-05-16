<?php

/**
 * hMailserver password driver
 *
 * @version 2.0
 *
 * @author Roland 'rosali' Liebl <myroundcube@mail4us.net>
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

class rcube_hmail_password
{
    public function save($curpass, $passwd, $username)
    {
        $rcmail = rcmail::get_instance();

        try {
            $remote = $rcmail->config->get('hmailserver_remote_dcom', false);
            if ($remote) {
                $obApp = new COM('hMailServer.Application', $rcmail->config->get('hmailserver_server'));
            } else {
                $obApp = new COM('hMailServer.Application');
            }
        } catch (Exception $e) {
            rcube::raise_error('Password plugin: hMail error: ' . trim(strip_tags($e->getMessage())), true);
            rcube::raise_error('Password plugin: This problem is often caused by DCOM permissions not being set.', true);

            return PASSWORD_ERROR;
        }

        if (strstr($username, '@')) {
            [, $domain] = explode('@', $username);
        } else {
            $domain = $rcmail->config->get('username_domain', false);
            if (!$domain) {
                rcube::raise_error('Password plugin: $config[\'username_domain\'] is not defined.', true);
                return PASSWORD_ERROR;
            }
            $username = $username . '@' . $domain;
        }

        try {
            $obApp->Authenticate($username, $curpass); // @phpstan-ignore-line

            $obDomain = $obApp->Domains->ItemByName($domain); // @phpstan-ignore-line
            $obAccount = $obDomain->Accounts->ItemByAddress($username);
            $obAccount->Password = $passwd;
            $obAccount->Save();

            return PASSWORD_SUCCESS;
        } catch (Exception $e) {
            rcube::raise_error('Password plugin: hMail error: ' . trim(strip_tags($e->getMessage())));
            rcube::raise_error('Password plugin: This problem is often caused by DCOM permissions not being set.', true);

            return PASSWORD_ERROR;
        }
    }
}
