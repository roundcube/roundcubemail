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
$labels['changepasswd']  = 'Spremeni geslo';
$labels['curpasswd']  = 'Obstoječe geslo:';
$labels['newpasswd']  = 'Novo geslo:';
$labels['confpasswd']  = 'Potrdi novo geslo:';

$messages = array();
$messages['nopassword'] = 'Vnesite novo geslo.';
$messages['nocurpassword'] = 'Vnesite obstoječe geslo.';
$messages['passwordincorrect'] = 'Obstoječe geslo ni veljavno.';
$messages['passwordinconsistency'] = 'Gesli se ne ujemata, poskusite znova.';
$messages['crypterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake pri šifriranju.';
$messages['connecterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake v povezavi.';
$messages['internalerror'] = 'Novega gesla ni bilo mogoče shraniti.';
$messages['passwordshort'] = 'Geslo mora vsebovati vsaj $length znakov';
$messages['passwordweak'] = 'Geslo mora vključevati vsaj eno številko in ločilo.';
$messages['passwordforbidden'] = 'Geslo vsebuje neveljavne znake.';

?>
