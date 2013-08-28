<?php

/**
 * hMailserver password driver
 *
 * @version 2.0
 * @author Roland 'rosali' Liebl <myroundcube@mail4us.net>
 */

class rcube_hmail_password
{
    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        if ($curpass == '' || $passwd == '') {
            return PASSWORD_ERROR;
        }

        try {
            $remote = $rcmail->config->get('hmailserver_remote_dcom', false);
            if ($remote)
                $obApp = new COM("hMailServer.Application", $rcmail->config->get('hmailserver_server'));
            else
                $obApp = new COM("hMailServer.Application");
        }
        catch (Exception $e) {
            rcube::write_log('errors', "Plugin password (hmail driver): " . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', "Plugin password (hmail driver): This problem is often caused by DCOM permissions not being set.");
            return PASSWORD_ERROR;
        }

        $username = $rcmail->user->data['username'];
        if (strstr($username,'@')){
            $temparr = explode('@', $username);
            $domain = $temparr[1];
        }
        else {
            $domain = $rcmail->config->get('username_domain',false);
            if (!$domain) {
                rcube::write_log('errors','Plugin password (hmail driver): $config[\'username_domain\'] is not defined.');
                return PASSWORD_ERROR;
            }
            $username = $username . "@" . $domain;
        }

        $obApp->Authenticate($username, $curpass);
        try {
            $obDomain = $obApp->Domains->ItemByName($domain);
            $obAccount = $obDomain->Accounts->ItemByAddress($username);
            $obAccount->Password = $passwd;
            $obAccount->Save();
            return PASSWORD_SUCCESS;
        }
        catch (Exception $e) {
            rcube::write_log('errors', "Plugin password (hmail driver): " . trim(strip_tags($e->getMessage())));
            rcube::write_log('errors', "Plugin password (hmail driver): This problem is often caused by DCOM permissions not being set.");
            return PASSWORD_ERROR;
        }
    }
}
