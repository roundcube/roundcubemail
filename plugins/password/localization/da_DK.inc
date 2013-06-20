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
$labels['changepasswd']  = 'Skift adgangskode';
$labels['curpasswd']  = 'Nuværende adgangskode:';
$labels['newpasswd']  = 'Ny adgangskode:';
$labels['confpasswd']  = 'Bekræft ny adgangskode:';

$messages = array();
$messages['nopassword'] = 'Indtast venligst en ny adgangskode.';
$messages['nocurpassword'] = 'Indtast venligst nuværende adgangskode.';
$messages['passwordincorrect'] = 'Nuværende adgangskode er forkert.';
$messages['passwordinconsistency'] = 'Adgangskoderne er ikke ens, prøv igen.';
$messages['crypterror'] = 'Kunne ikke gemme den nye adgangskode. Krypteringsfunktion mangler.';
$messages['connecterror'] = 'Kunne ikke gemme den nye adgangskode. Fejl ved forbindelsen.';
$messages['internalerror'] = 'Kunne ikke gemme den nye adgangskode.';
$messages['passwordshort'] = 'Adgangskoden skal være mindst $length tegn lang.';
$messages['passwordweak'] = 'Adgangskoden skal indeholde mindst et tal og et tegnsætningstegn (-.,)';
$messages['passwordforbidden'] = 'Adgangskoden indeholder forbudte tegn.';

?>
