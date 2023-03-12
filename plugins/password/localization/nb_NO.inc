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

$labels['changepasswd']  = 'Bytt passord';
$labels['curpasswd']  = 'Nåværende passord:';
$labels['newpasswd']  = 'Nytt passord:';
$labels['confpasswd']  = 'Bekreft nytt passord';
$messages['nopassword'] = 'Vennligst skriv inn nytt passord';
$messages['nocurpassword'] = 'Vennligst skriv inn nåværende passord';
$messages['passwordincorrect'] = 'Nåværende passord er feil.';
$messages['passwordinconsistency'] = 'Passordene er ikke like, vennligst prøv igjen.';
$messages['crypterror'] = 'Kunne ikke lagre nytt passord. Krypteringsfunksjonen mangler.';
$messages['connecterror'] = 'Kunne ikke lagre nytt passord. Tilkoblingsfeil.';
$messages['internalerror'] = 'Kunne ikke lagre nytt passord';
$messages['passwordshort'] = 'Passordet må minimum inneholde $length tegn.';
$messages['passwordweak'] = 'Passordet må inneholde minst ett tall og ett tegnsettingssymbol.';
$messages['passwordtooweak'] = 'Passordet er for svakt.';
$messages['passwordnoseq'] = 'Passordet burde ikke være en setning slik som 123456 eller QWERTY.';
$messages['passwordnocommon'] = 'Passordet burde ikke være et ord eller navn.';
$messages['passwordforbidden'] = 'Passordet inneholder forbudte tegn.';
$messages['firstloginchange'] = 'Dette er din første innlogging. Vennligst bytt ditt passord.';
$messages['disablednotice'] = 'Systemet er under vedlikehold og man kan ikke endre passord for øyeblikket. Alt skal være tilbake til normalt snart. Vi beklager de ulempene dette måtte medføre.';
$messages['passwinhistory'] = 'Dette passordet er benyttet tidligere.';
$messages['samepasswd'] = 'Det nye passordet må være anderledes fra det gamle.';
$messages['passwdexpirewarning'] = 'Advarsel! Ditt passord løper snart ut, bytt det før $expirationdatetime.';
$messages['passwdexpired'] = 'Ditt passord har løpt ut, du må bytte det nå!';
$messages['passwdconstraintviolation'] = 'Brudd på passordbegrensning. Passordet er sannsynligvis for svakt.';
$messages['pwned_isdisclosed'] = 'Dette passordet er allment kjent.';
$messages['pwned_fetcherror'] = 'Feilet i verifiseringen av passordstyrke.';
