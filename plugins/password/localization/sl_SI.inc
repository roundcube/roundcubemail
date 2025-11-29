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
*/

$labels['changepasswd']  = 'Spremenjeno geslo';
$labels['curpasswd']  = 'Obstoječe geslo:';
$labels['newpasswd']  = 'Novo geslo:';
$labels['confpasswd']  = 'Potrdi novo geslo:';
$messages['nopassword'] = 'Vnesite novo geslo.';
$messages['nocurpassword'] = 'Vnesite obstoječe geslo.';
$messages['passwordincorrect'] = 'Obstoječe geslo ni veljavno.';
$messages['passwordinconsistency'] = 'Gesli se ne ujemata, poskusite znova.';
$messages['crypterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake pri šifriranju.';
$messages['connecterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake v povezavi.';
$messages['internalerror'] = 'Novega gesla ni bilo mogoče shraniti.';
$messages['passwordshort'] = 'Geslo mora vsebovati vsaj $length znakov';
$messages['passwordweak'] = 'Geslo mora vključevati vsaj eno številko in ločilo.';
$messages['passwordtooweak'] = 'Geslo je prešibko.';
$messages['passwordnoseq'] = 'Geslo ne sme biti zaporedje, kot je 123456 ali QWERTY.';
$messages['passwordnocommon'] = 'Geslo ne sme biti pogosta beseda ali ime.';
$messages['passwordforbidden'] = 'Geslo vsebuje neveljavne znake.';
$messages['firstloginchange'] = 'To je vaša prva prijava. Spremenite začetno geslo.';
$messages['disablednotice'] = 'Sistem se trenutno posodablja, zato sprememba gesla v tem trenutku ni mogoča. Postopek bo mogoče opraviti v kratkem. Za nevšečnosti se opravičujemo.';
$messages['passwinhistory'] = 'To geslo ste v preteklosti že uporabili.';
$messages['samepasswd'] = 'Novo geslo se mora razlikovati od prejšnjega.';
$messages['passwdexpirewarning'] = 'Opozorilo! Vaše geslo bo kmalu poteklo, spremenite ga pred $expirationdatetime.';
$messages['passwdexpired'] = 'Vaše geslo je poteklo, spremenite ga!';
$messages['passwdconstraintviolation'] = 'Geslo ne ustreza zahtevam. Verjetno je prešibko.';
$messages['pwned_mustnotbedisclosed'] = 'Geslo ne sme biti <a href="$href" target="_blank">splošno znano</a>.';
$messages['pwned_isdisclosed'] = 'To geslo je splošno znano.';
$messages['pwned_fetcherror'] = 'Preverjanje moči gesla ni uspelo.';
