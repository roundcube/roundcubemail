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
$labels['changepasswd']  = 'Zmeniť heslo';
$labels['curpasswd']  = 'Súčasné heslo:';
$labels['newpasswd']  = 'Nové heslo:';
$labels['confpasswd']  = 'Potvrď nové heslo:';

$messages = array();
$messages['nopassword'] = 'Prosím zadaj nové heslo.';
$messages['nocurpassword'] = 'Prosím zadaj súčasné heslo.';
$messages['passwordincorrect'] = 'Súčasné heslo je nesprávne.';
$messages['passwordinconsistency'] = 'Heslá nie sú rovnaké, skús znova.';
$messages['crypterror'] = 'Nemôžem uložiť nové heslo. Chýba šifrovacia funkcia.';
$messages['connecterror'] = 'Nemôžem uložiť nové heslo. Chyba spojenia.';
$messages['internalerror'] = 'Nemôžem uložiť nové heslo.';
$messages['passwordshort'] = 'Heslo musí mať najmenej $length znakov.';
$messages['passwordweak'] = 'Heslo musí obsahovať aspoň jedno číslo a jedno interpunkčné znamienko.';
$messages['passwordforbidden'] = 'Heslo obsahuje nepovolené znaky.';

?>
