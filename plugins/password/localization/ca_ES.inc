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
$labels['changepasswd']  = 'Canvia la contrasenya';
$labels['curpasswd']  = 'Contrasenya actual:';
$labels['newpasswd']  = 'Nova contrasenya:';
$labels['confpasswd']  = 'Confirmeu la nova contrasenya:';

$messages = array();
$messages['nopassword'] = 'Si us plau, introduïu la nova contrasenya.';
$messages['nocurpassword'] = 'Si us plau, introduïu la contrasenya actual.';
$messages['passwordincorrect'] = 'Contrasenya actual incorrecta.';
$messages['passwordinconsistency'] = 'La contrasenya nova no coincideix, torneu-ho a provar.';
$messages['crypterror'] = 'No es pot desar la nova contrasenya. No existeix la funció d\'encriptació.';
$messages['connecterror'] = 'No es pot desar la nova contrasenya. Error de connexió.';
$messages['internalerror'] = 'No es pot desar la nova contrasenya.';
$messages['passwordshort'] = 'La nova contrasenya ha de tenir com a mínim $length caràcters de llarg.';
$messages['passwordweak'] = 'La nova contrasenya ha d\'incloure com a mínim un nombre i un caràcter de puntuació.';
$messages['passwordforbidden'] = 'La contrasenya conté caràcters no permesos.';

?>
