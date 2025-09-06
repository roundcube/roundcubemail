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

$labels['changepasswd']  = 'Ändra lösenord';
$labels['curpasswd']  = 'Nuvarande lösenord';
$labels['newpasswd']  = 'Nytt lösenord';
$labels['confpasswd']  = 'Bekräfta nytt lösenord';
$messages['nopassword'] = 'Ange nytt lösenord.';
$messages['nocurpassword'] = 'Ange nuvarande lösenord.';
$messages['passwordincorrect'] = 'Felaktigt nuvarande lösenord.';
$messages['passwordinconsistency'] = 'Bekräftelsen av lösenordet stämmer inte, försök igen.';
$messages['crypterror'] = 'Lösenordet kunde inte ändras. Krypteringsfunktionen saknas.';
$messages['connecterror'] = 'Lösenordet kunde inte ändras. Anslutningen misslyckades.';
$messages['internalerror'] = 'Lösenordet kunde inte ändras.';
$messages['passwordshort'] = 'Lösenordet måste vara minst $length tecken långt.';
$messages['passwordweak'] = 'Lösenordet måste innehålla minst en siffra och ett specialtecken.';
$messages['passwordtooweak'] = 'Lösenordet är inte tillräckligt bra.';
$messages['passwordnoseq'] = 'Lösenord bör aldrig vara sekvenser liknande 123456 eller QWERTY.';
$messages['passwordnocommon'] = 'Lösenord bör aldrig vara vanliga ord eller namn.';
$messages['passwordforbidden'] = 'Lösenordet innehåller otillåtna tecken.';
$messages['firstloginchange'] = 'Du loggar in för första gången. Vänligen ändra ditt lösenord.';
$messages['disablednotice'] = 'Underhåll av systemet pågår och därför kan lösenord inte ändras för tillfället. Allt ska fungera normalt igen så snart som möjligt. Ursäkta den eventuella olägenheten underhållet orsakar.';
$messages['passwinhistory'] = 'Lösenordet har redan använts tidigare.';
$messages['samepasswd'] = 'Lösenordet får inte vara samma som det gamla.';
$messages['passwdexpirewarning'] = 'Varning! Lösenordet blir snart ogiltigt, ändra det innan $expirationdatetime.';
$messages['passwdexpired'] = 'Lösenordet är ogiltigt, du måste ändra det nu!';
$messages['passwdconstraintviolation'] = 'Lösenordet uppfyller inte gällande kriterier. Lösenordet är troligtvis inte tillräckligt bra.';
$messages['pwned_mustnotbedisclosed'] = 'Lösenordet får inte vara <a href="$href" target="_blank">allmänt känt</a>.';
$messages['pwned_isdisclosed'] = 'Detta lösenord är allmänt känt.';
$messages['pwned_fetcherror'] = 'Det gick inte att verifiera lösenordsstyrkan.';

$labels['hestia_err_policy_short'] = 'Lösenordet är för kort (minsta längd: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia-slutpunkten är inte konfigurerad eller ogiltig.';
$labels['hestia_err_identity'] = 'Det går inte att tolka användarnamnet i local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Det går inte att fastställa domänägaren för %domän.';
$labels['hestia_err_unknown'] = 'Okänt fel från Hestia API.';
$labels['hestia_err_default'] = 'Hestia returnerade felkoden %code.';
$labels['hestia_err_1'] = 'Inte tillräckligt med argument.';
$labels['hestia_err_2'] = 'Ogiltigt objekt eller argument.';
$labels['hestia_err_3'] = 'Objektet finns inte (brevlåda eller domän hittades inte).';
$labels['hestia_err_4'] = 'Objektet finns redan.';
$labels['hestia_err_5'] = 'Objektet är redan avstängt.';
$labels['hestia_err_6'] = 'Objektet är redan uppskjutet.';
$labels['hestia_err_7'] = 'Objekt i bruk; kan inte modifieras.';
$labels['hestia_err_8'] = 'Drift blockerad av paketgränser.';
$labels['hestia_err_9'] = 'Fel eller ogiltigt lösenord.';
$labels['hestia_err_10'] = 'Tillstånd nekat av Hestia (API-identitet saknar rättigheter eller IP inte tillåtet).';
$labels['hestia_err_11'] = 'Funktion inaktiverad av systempolicy.';
$labels['hestia_err_12'] = 'Parsing-fel.';
$labels['hestia_err_13'] = 'Diskfel.';
$labels['hestia_err_14'] = 'Serverbelastningen är för hög.';
$labels['hestia_err_15'] = 'Fel i anslutningen.';
$labels['hestia_err_16'] = 'FTP-fel.';
$labels['hestia_err_17'] = 'Fel i databasen.';
$labels['hestia_err_18'] = 'RRD-fel.';
$labels['hestia_err_19'] = 'Fel vid uppdatering.';
$labels['hestia_err_20'] = 'Fel vid omstart.';
?>
