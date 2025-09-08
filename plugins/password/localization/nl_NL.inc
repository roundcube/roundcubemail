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

$labels['changepasswd']  = 'Wijzig wachtwoord';
$labels['curpasswd']  = 'Huidig wachtwoord:';
$labels['newpasswd']  = 'Nieuw wachtwoord:';
$labels['confpasswd']  = 'Bevestig nieuw wachtwoord:';
$messages['nopassword'] = 'Vul uw nieuwe wachtwoord in.';
$messages['nocurpassword'] = 'Vul uw huidige wachtwoord in.';
$messages['passwordincorrect'] = 'Huidig wachtwoord is onjuist.';
$messages['passwordinconsistency'] = 'Wachtwoorden komen niet overeen, probeer het opnieuw.';
$messages['crypterror'] = 'Nieuwe wachtwoord kan niet opgeslagen worden; de server mist een versleutelfunctie.';
$messages['connecterror'] = 'Nieuwe wachtwoord kan niet opgeslagen worden; verbindingsfout.';
$messages['internalerror'] = 'Uw nieuwe wachtwoord kan niet worden opgeslagen.';
$messages['passwordshort'] = 'Het wachtwoord moet minimaal $length tekens lang zijn.';
$messages['passwordweak'] = 'Het wachtwoord moet minimaal één cijfer en één leesteken bevatten.';
$messages['passwordtooweak'] = 'Uw wachtwoord is niet sterk genoeg.';
$messages['passwordnoseq'] = 'Uw wachtwoord mag geen opeenvolging zijn zoals 123456 of AZERTY.';
$messages['passwordnocommon'] = 'Uw wachtwoord mag geen veel voorkomende naam of tekst zijn.';
$messages['passwordforbidden'] = 'Het wachtwoord bevat tekens die niet toegestaan zijn.';
$messages['firstloginchange'] = 'Dit is uw eerste aanmelding. Verander uw wachtwoord alstublieft.';
$messages['disablednotice'] = 'Het systeem is momenteel in onderhoud en wachtwoord wijzigen is op dit moment dus niet mogelijk. Alles werkt binnenkort weer naar behoren. Onze excuses voor het ongemak.';
$messages['passwinhistory'] = 'Dit wachtwoord is al eerder gebruikt.';
$messages['samepasswd'] = 'Het nieuwe paswoord dient verschillend ten opzichte van de oude te zijn.';
$messages['passwdexpirewarning'] = 'Waarschuwing! je wachtwoord verloopt binnenkort, Wijzig het voor $expirationdatetime.';
$messages['passwdexpired'] = 'Je wachtwoord is verlopen, je dient het nu te wijzigen!';
$messages['passwdconstraintviolation'] = 'Wachtwoord voldoet niet aan beleid. Waarschijnlijk te zwak.';
$messages['pwned_isdisclosed'] = 'Dit wachtwoord is algemeen bekend.';
$messages['pwned_fetcherror'] = 'De wachtwoordsterkte kon niet geverifieerd worden.';

$labels['hestia_err_policy_short'] = 'Wachtwoord te kort (minimale lengte: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia endpoint is niet geconfigureerd of ongeldig.';
$labels['hestia_err_identity'] = 'Kan gebruikersnaam niet parseren in local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Kan domeineigenaar voor %domein niet bepalen.';
$labels['hestia_err_unknown'] = 'Onbekende fout van Hestia API.';
$labels['hestia_err_default'] = 'Hestia heeft foutcode %code teruggestuurd.';
$labels['hestia_err_1'] = 'Niet genoeg argumenten gegeven.';
$labels['hestia_err_2'] = 'Ongeldig object of argument.';
$labels['hestia_err_3'] = 'Object bestaat niet (mailbox of domein niet gevonden).';
$labels['hestia_err_4'] = 'Object bestaat al.';
$labels['hestia_err_5'] = 'Object is al opgeschort.';
$labels['hestia_err_6'] = 'Object is al uit suspensie.';
$labels['hestia_err_7'] = 'Object in gebruik; kan niet wijzigen.';
$labels['hestia_err_8'] = 'Bediening geblokkeerd door pakketlimieten.';
$labels['hestia_err_9'] = 'Verkeerd of ongeldig wachtwoord.';
$labels['hestia_err_10'] = 'Toestemming geweigerd door Hestia (API-identiteit heeft geen rechten of IP is niet toegestaan).';
$labels['hestia_err_11'] = 'Functie uitgeschakeld door systeembeleid.';
$labels['hestia_err_12'] = 'Parsingfout.';
$labels['hestia_err_13'] = 'Schijffout.';
$labels['hestia_err_14'] = 'Serverbelasting te hoog.';
$labels['hestia_err_15'] = 'Fout bij verbinding.';
$labels['hestia_err_16'] = 'FTP-fout.';
$labels['hestia_err_17'] = 'Database fout.';
$labels['hestia_err_18'] = 'RRD-fout.';
$labels['hestia_err_19'] = 'Fout bijwerken.';
$labels['hestia_err_20'] = 'Fout bij opnieuw opstarten.';
?>
