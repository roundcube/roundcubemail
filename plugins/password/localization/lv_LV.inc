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
$labels['changepasswd']  = 'Nomainīt paroli';
$labels['curpasswd']  = 'Pašreizējā parole:';
$labels['newpasswd']  = 'Jaunā parole:';
$labels['confpasswd']  = 'Vēlreiz jauno paroli:';

$messages = array();
$messages['nopassword'] = 'Lūdzu, ievadiet jauno paroli.';
$messages['nocurpassword'] = 'Lūdzu, ievadiet pašreizējo paroli.';
$messages['passwordincorrect'] = 'Pašreizējā parole nepareiza.';
$messages['passwordinconsistency'] = 'Paroles nesakrīt. Lūdzu, ievadiet vēlreiz.';
$messages['crypterror'] = 'Nevarēja saglabāt jauno paroli. Trūkst kriptēšanas funkcija.';
$messages['connecterror'] = 'Nevarēja saglabāt jauno paroli. Savienojuma kļūda.';
$messages['internalerror'] = 'Nevarēja saglabāt jauno paroli.';
$messages['passwordshort'] = 'Jaunajai parolei jābūt vismaz $length simbola garai.';
$messages['passwordweak'] = 'Jaunajai parolei jāsatur vismaz viens cipars un punktuācijas simbols.';
$messages['passwordforbidden'] = 'Password contains forbidden characters.';

?>
