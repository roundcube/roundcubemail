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

$labels['changepasswd']  = 'Ændre adgangskode ';
$labels['curpasswd']  = 'Nuværende adgangskode:';
$labels['newpasswd']  = 'Ny adgangskode:';
$labels['confpasswd']  = 'Bekræft ny adgangskode:';
$messages['nopassword'] = 'Indtast venligst en ny adgangskode.';
$messages['nocurpassword'] = 'Indtast venligst nuværende adgangskode.';
$messages['passwordincorrect'] = 'Nuværende adgangskode er forkert.';
$messages['passwordinconsistency'] = 'Adgangskoderne er ikke ens, prøv igen.';
$messages['crypterror'] = 'Kunne ikke gemme den nye adgangskode. Krypteringsfunktion mangler.';
$messages['connecterror'] = 'Kunne ikke gemme den nye adgangskode. Fejl ved forbindelsen.';
$messages['internalerror'] = 'Kunne ikke gemme den nye adgangskode.';
$messages['passwordshort'] = 'Adgangskoden skal være mindst $length tegn lang.';
$messages['passwordweak'] = 'Adgangskoden skal indeholde mindst et tal og et tegnsætningstegn (-.,)';
$messages['passwordtooweak'] = 'Adgangskoden er for svag';
$messages['passwordnoseq'] = 'Adgangskoden bør ikke være en række som 123456 eller QWERTY.';
$messages['passwordnocommon'] = 'Adgangskoden bør ikke være et almindeligt ord eller navn.';
$messages['passwordforbidden'] = 'Adgangskoden indeholder forbudte tegn.';
$messages['firstloginchange'] = 'Dette er første gang du logger ind, ændre venligst din adgangskode';
$messages['disablednotice'] = 'Systemet vedligeholdes i øjeblikket og det er pt. ikke muligt at skifte kodeord. Alt burde være oppe igen om lidt. Vi beklager ulejligheden. ';
$messages['passwinhistory'] = 'Kodeordet har tidligere været anvendt. ';
$messages['samepasswd'] = 'Det nye kodeord skal være forskelligt fra det gamle. ';
$messages['passwdexpirewarning'] = 'Advarsel, Dit kodeord udløber snart. Du skal ændre det før $expirationdatetime.';
$messages['passwdexpired'] = 'Dit kodeord er udløbet, du skal ændre det nu!';
$messages['passwdconstraintviolation'] = 'Password er i strid med reglerne. Det er sikkert for svagt.';
$messages['pwned_mustnotbedisclosed'] = 'Adgangskoden må ikke være <a href="$href" target="_blank">almindeligt brugt</a>.';
$messages['pwned_isdisclosed'] = 'Denne adgangskode er almindeligt brugt.';
$messages['pwned_fetcherror'] = 'Kunne ikke bekræfte adgangskodens styrken.';

$labels['hestia_err_policy_short'] = 'Adgangskoden er for kort (minimumslængde: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia-slutpunktet er ikke konfigureret eller ugyldigt.';
$labels['hestia_err_identity'] = 'Kan ikke analysere brugernavn i local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Det er ikke muligt at bestemme domæneejeren for %domain.';
$labels['hestia_err_unknown'] = 'Ukendt fejl fra Hestia API.';
$labels['hestia_err_default'] = 'Hestia returnerede fejlkoden %code.';
$labels['hestia_err_1'] = 'Ikke nok argumenter.';
$labels['hestia_err_2'] = 'Ugyldigt objekt eller argument.';
$labels['hestia_err_3'] = 'Objektet findes ikke (postkasse eller domæne ikke fundet).';
$labels['hestia_err_4'] = 'Objektet findes allerede.';
$labels['hestia_err_5'] = 'Objektet er allerede suspenderet.';
$labels['hestia_err_6'] = 'Objektet er allerede afbrudt.';
$labels['hestia_err_7'] = 'Objekt i brug; kan ikke ændres.';
$labels['hestia_err_8'] = 'Betjening blokeret af pakkebegrænsninger.';
$labels['hestia_err_9'] = 'Forkert eller ugyldig adgangskode.';
$labels['hestia_err_10'] = 'Tilladelse afvist af Hestia (API-identitet mangler rettigheder eller IP ikke tilladt).';
$labels['hestia_err_11'] = 'Funktion deaktiveret af systempolitik.';
$labels['hestia_err_12'] = 'Parsing-fejl.';
$labels['hestia_err_13'] = 'Diskfejl.';
$labels['hestia_err_14'] = 'Serverbelastningen er for høj.';
$labels['hestia_err_15'] = 'Forbindelsesfejl.';
$labels['hestia_err_16'] = 'FTP-fejl.';
$labels['hestia_err_17'] = 'Databasefejl.';
$labels['hestia_err_18'] = 'RRD-fejl.';
$labels['hestia_err_19'] = 'Opdateringsfejl.';
$labels['hestia_err_20'] = 'Fejl ved genstart.';
?>
