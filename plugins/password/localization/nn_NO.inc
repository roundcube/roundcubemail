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

$labels['curpasswd']  = 'Noverande passord:';
$labels['newpasswd']  = 'Nytt passord:';
$labels['confpasswd']  = 'Bekreft nytt passord';
$messages['nopassword'] = 'Venlegast skriv inn nytt passord.';
$messages['nocurpassword'] = 'Venlegast skriv inn noverande passord.';
$messages['passwordincorrect'] = 'Noverande passord er feil.';
$messages['passwordinconsistency'] = 'Passorda er ikkje like, venlegast prøv igjen.';
$messages['crypterror'] = 'Kunne ikkje lagre nytt passord. Krypteringsfunksjonen manglar.';
$messages['connecterror'] = 'Kunne ikkje lagre nytt passord. Tilkoblingsfeil.';
$messages['internalerror'] = 'Kunne ikkje lagre nytt passord.';
$messages['passwordshort'] = 'Passordet må minimum innehalde $length teikn.';
$messages['passwordweak'] = 'Passordet må innehalde minst eitt tal og eitt skilleteikn.';
$messages['passwordforbidden'] = 'Passordet inneheld forbodne teikn.';

$labels['hestia_err_policy_short'] = 'Passordet er for kort (minimumslengde: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia-endepunktet er ikke konfigurert eller ugyldig.';
$labels['hestia_err_identity'] = 'Kan ikke analysere brukernavn i local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Kan ikke fastslå domeneeier for %domain.';
$labels['hestia_err_unknown'] = 'Ukjent feil fra Hestia API.';
$labels['hestia_err_default'] = 'Hestia returnerte feilkoden %code.';
$labels['hestia_err_1'] = 'Det ble ikke gitt nok argumenter.';
$labels['hestia_err_2'] = 'Ugyldig objekt eller argument.';
$labels['hestia_err_3'] = 'Objektet finnes ikke (postkasse eller domene ble ikke funnet).';
$labels['hestia_err_4'] = 'Objektet finnes allerede.';
$labels['hestia_err_5'] = 'Objektet er allerede suspendert.';
$labels['hestia_err_6'] = 'Objektet er allerede ikke suspendert.';
$labels['hestia_err_7'] = 'Objekt i bruk; kan ikke endres.';
$labels['hestia_err_8'] = 'Drift blokkert av pakkebegrensninger.';
$labels['hestia_err_9'] = 'Feil eller ugyldig passord.';
$labels['hestia_err_10'] = 'Tillatelse avslått av Hestia (API-identitet mangler rettigheter eller IP ikke tillatt).';
$labels['hestia_err_11'] = 'Funksjon deaktivert av systempolicy.';
$labels['hestia_err_12'] = 'Parsing feil.';
$labels['hestia_err_13'] = 'Diskfeil.';
$labels['hestia_err_14'] = 'Serverbelastningen er for høy.';
$labels['hestia_err_15'] = 'Feil i tilkoblingen.';
$labels['hestia_err_16'] = 'FTP-feil.';
$labels['hestia_err_17'] = 'Databasefeil.';
$labels['hestia_err_18'] = 'RRD-feil.';
$labels['hestia_err_19'] = 'Oppdateringsfeil.';
$labels['hestia_err_20'] = 'Feil ved omstart.';
?>
