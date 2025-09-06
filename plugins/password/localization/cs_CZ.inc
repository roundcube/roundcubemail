<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
$messages['pwned_mustnotbedisclosed'] = 'Heslo nesmí být <a href="$href" target="_blank">běžně užívané</a>.';
$messages['pwned_isdisclosed'] = 'Heslo je běžně užívané.';
$messages['pwned_fetcherror'] = 'Síla hesla se nedá ověřit.';

$labels['hestia_err_policy_short'] = 'Příliš krátké heslo (minimální délka: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Koncový bod Hestia není nakonfigurován nebo je neplatný.';
$labels['hestia_err_identity'] = 'Nelze analyzovat uživatelské jméno do local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Nelze určit vlastníka domény pro %doménu.';
$labels['hestia_err_unknown'] = 'Neznámá chyba z rozhraní Hestia API.';
$labels['hestia_err_default'] = 'Hestia vrátila chybový kód %code.';
$labels['hestia_err_1'] = 'Nebyl poskytnut dostatek argumentů.';
$labels['hestia_err_2'] = 'Neplatný objekt nebo argument.';
$labels['hestia_err_3'] = 'Objekt neexistuje (schránka nebo doména nebyla nalezena).';
$labels['hestia_err_4'] = 'Objekt již existuje.';
$labels['hestia_err_5'] = 'Objekt je již pozastaven.';
$labels['hestia_err_6'] = 'Objekt již není zavěšený.';
$labels['hestia_err_7'] = 'Objekt v používání; nelze upravovat.';
$labels['hestia_err_8'] = 'Operace je blokována omezeními balíčků.';
$labels['hestia_err_9'] = 'Špatné nebo neplatné heslo.';
$labels['hestia_err_10'] = 'Oprávnění zamítnuto společností Hestia (identita API nemá práva nebo IP není povolena).';
$labels['hestia_err_11'] = 'Funkce zakázaná zásadami systému.';
$labels['hestia_err_12'] = 'Chyba při parsování.';
$labels['hestia_err_13'] = 'Chyba disku.';
$labels['hestia_err_14'] = 'Příliš vysoké zatížení serveru.';
$labels['hestia_err_15'] = 'Chyba připojení.';
$labels['hestia_err_16'] = 'Chyba FTP.';
$labels['hestia_err_17'] = 'Chyba databáze.';
$labels['hestia_err_18'] = 'Chyba RRD.';
$labels['hestia_err_19'] = 'Chyba aktualizace.';
$labels['hestia_err_20'] = 'Chyba při restartování.';
?>
