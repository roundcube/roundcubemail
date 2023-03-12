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

$labels['changepasswd']  = 'Jelszó módosítás';
$labels['curpasswd']  = 'Jelenlegi jelszó:';
$labels['newpasswd']  = 'Új jelszó:';
$labels['confpasswd']  = 'Új jelszó mégegyszer:';
$messages['nopassword'] = 'Kérjük adja meg az új jelszót.';
$messages['nocurpassword'] = 'Kérjük adja meg a jelenlegi jelszót.';
$messages['passwordincorrect'] = 'Érvénytelen a jelenlegi jelszó.';
$messages['passwordinconsistency'] = 'A beírt jelszavak nem azonosak. Próbálja újra.';
$messages['crypterror'] = 'Hiba történt a kérés feldolgozása során.';
$messages['connecterror'] = 'Az új jelszó mentése nem sikerült. Hiba a kapcsolatban';
$messages['internalerror'] = 'Hiba történt a kérés feldolgozása során.';
$messages['passwordshort'] = 'A jelszónak legalább $length karakter hosszúnak kell lennie.';
$messages['passwordweak'] = 'A jelszónak mindenképpen kell tartalmaznia egy számot és egy írásjelet.';
$messages['passwordtooweak'] = 'A jelszó túl gyenge.';
$messages['passwordnoseq'] = 'A jelszavak nem lehetnek olyan karaktereksorozatok mint például az 123456 vagy a QWERTY.';
$messages['passwordnocommon'] = 'A jelszó nem lehet egy általános szó vagy név.';
$messages['passwordforbidden'] = 'A jelszó tiltott karaktert is tartalmaz.';
$messages['firstloginchange'] = 'Ez az első belépésed. Változtass jelszót.';
$messages['disablednotice'] = 'A rendszer jelenleg karbantartás alatt van és a jelszó módosítás nem lehetséges ebben a pillanatban. Minden visszaáll normálisra hamarosan. A  kellemetlenségért elnézést kérünk.';
$messages['passwinhistory'] = 'A megadott jelszó volt már használva.';
$messages['samepasswd'] = 'Az új jelszó nem lehet azonos a régi jelszóval.';
$messages['passwdexpirewarning'] = 'Figyelem! A jelszavad hamarosan lejár, változtasd meg még $expirationdatetime elött.';
$messages['passwdexpired'] = 'A jelszavad lejárt, adj meg újat.';
$messages['passwdconstraintviolation'] = 'Jelszó szabály megsértése. A jelszó valószínűleg túl gyenge.';
$messages['pwned_mustnotbedisclosed'] = 'A jelszó nem lehet <a href="$href" target="_blank">általánosan használtként ismert</a>.';
$messages['pwned_isdisclosed'] = 'Ez a jelszó általánosan használtként ismert.';
$messages['pwned_fetcherror'] = 'Nem sikerült a jelszó erősségének ellenőrzése.';
