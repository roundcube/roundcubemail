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
$labels['changepasswd']  = 'Změna hesla';
$labels['curpasswd']  = 'Aktuální heslo:';
$labels['newpasswd']  = 'Nové heslo:';
$labels['confpasswd']  = 'Nové heslo (pro kontrolu):';
$messages['nopassword'] = 'Prosím zadejte nové heslo.';
$messages['nocurpassword'] = 'Prosím zadejte aktuální heslo.';
$messages['passwordincorrect'] = 'Zadané aktuální heslo není správné.';
$messages['passwordinconsistency'] = 'Zadaná hesla se neshodují. Prosím zkuste to znovu.';
$messages['crypterror'] = 'Heslo se nepodařilo uložit. Chybí šifrovací funkce.';
$messages['connecterror'] = 'Heslo se nepodařilo uložit. Problém s připojením.';
$messages['internalerror'] = 'Heslo se nepodařilo uložit.';
$messages['passwordshort'] = 'Heslo musí mít alespoň $length znaků.';
$messages['passwordweak'] = 'Heslo musí obsahovat alespoň jedno číslo a jedno interpunkční znaménko.';
$messages['passwordtooweak'] = 'Heslo je příliš slabé.';
$messages['passwordnoseq'] = 'Heslo by nemělo být tvořeno posloupností znaků typu 123456 nebo QWERTY. ';
$messages['passwordnocommon'] = 'Heslo by nemělo být tvořeno běžným slovem či jménem.';
$messages['passwordforbidden'] = 'Heslo obsahuje nepovolené znaky.';
$messages['firstloginchange'] = 'Vaše první přihlášení, změňte si prosím heslo.';
$messages['disablednotice'] = 'Z důvodu údržby systému není momentálně možné změnit heslo. Za chvíli by mělo být vše v pořádku, omlouváme se za tuto nepříjemnost.';
$messages['passwinhistory'] = 'Toto heslo již bylo v minulosti použito.';
$messages['samepasswd'] = 'Nové heslo musí být odlišné od předchozího.';
$messages['passwdexpirewarning'] = 'Varování! Vaše heslo bude brzy zneplatněno, změňte ho před $expirationdatetime.';
$messages['passwdexpired'] = 'Vaše heslo bylo zneplatněno, musíte ho nyní změnit!';
$messages['passwdconstraintviolation'] = ' Nesplnění nutných pravidel pro heslo. Heslo je zrejmě příliš slabé.';
