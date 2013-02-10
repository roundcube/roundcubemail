<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels = array();
$labels['changepasswd']  = 'Wijzig Wachtwoord';
$labels['curpasswd']  = 'Huidig Wachtwoord:';
$labels['newpasswd']  = 'Nieuw Wachtwoord:';
$labels['confpasswd']  = 'Bevestig Nieuw Wachtwoord:';

$messages = array();
$messages['nopassword'] = 'Vul een wachtwoord in.';
$messages['nocurpassword'] = 'vul het huidige wachtwoord in.';
$messages['passwordincorrect'] = 'Huidig wachtwoord is onjuist.';
$messages['passwordinconsistency'] = 'Wachtwoorden komen niet overeen, probeer het opnieuw.';
$messages['crypterror'] = 'De server mist een functie om uw wachtwoord et beveiligen.';
$messages['connecterror'] = 'Kan het nieuwe wachtwoord niet opslaan. Verbindingsfout.';
$messages['internalerror'] = 'Uw wachtwoord kan niet worden opgeslagen.';
$messages['passwordshort'] = 'Het wachtwoord moet minimaal $length tekens lang zijn.';
$messages['passwordweak'] = 'Het wachtwoord moet minimaal één nummer en één leesteken bevatten.';
$messages['passwordforbidden'] = 'Het wachtwoord bevat tekens die niet toegestaan zijn.';

?>
