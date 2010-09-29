<?php

/**
 * hMailserver password driver
 *
 * @version 1.1
 * @author Roland 'rosali' Liebl <myroundcube@mail4us.net>
 *
 */

function password_save($curpass, $passwd)
{
    $rcmail = rcmail::get_instance();

    if($curpass == '' || $passwd == '')
      return PASSWORD_ERROR;

    try {
        $obApp = new COM('hMailServer.Application');
    }
    catch (Exception $e) {
        write_log('errors', "Plugin password (hmail driver):" .  $e->getMessage() . ". This problem is often caused by DCOM permissions not being set.");
        return PASSWORD_ERROR;
    }

    $username = $rcmail->user->data['username'];
    $temparr = explode('@', $username);
    $domain = $temparr[1];
    $obApp->Authenticate($username, $curpass);

    try {
      $obDomain = $obApp->Domains->ItemByName($domain);
      $obAccount = $obDomain->Accounts->ItemByAddress($username);
      $obAccount->Password = $passwd;
      $obAccount->Save();
      return PASSWORD_SUCCESS;
    }
    catch(Exception $e) {
      write_log('errors', "Plugin password (hmail driver):" .  $e->getMessage());
      return PASSWORD_ERROR;
    }
}

?>
