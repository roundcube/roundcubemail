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

$labels['changepasswd']  = 'Canvia la contrasenya';
$labels['curpasswd']  = 'Contrasenya actual:';
$labels['newpasswd']  = 'Nova contrasenya:';
$labels['confpasswd']  = 'Confirmeu la nova contrasenya:';
$messages['nopassword'] = 'Si us plau, introduïu la nova contrasenya.';
$messages['nocurpassword'] = 'Si us plau, introduïu la contrasenya actual.';
$messages['passwordincorrect'] = 'Contrasenya actual incorrecta.';
$messages['passwordinconsistency'] = 'La contrasenya nova no coincideix, torneu-ho a provar.';
$messages['crypterror'] = 'No s\'ha pogut desar la nova contrasenya. No existeix la funció d\'encriptació.';
$messages['connecterror'] = 'No s\'ha pogut desar la nova contrasenya. Error de connexió.';
$messages['internalerror'] = 'No s\'ha pogut desar la nova contrasenya.';
$messages['passwordshort'] = 'La nova contrasenya ha de tenir com a mínim $length caràcters.';
$messages['passwordweak'] = 'La nova contrasenya ha d\'incloure com a mínim un nombre i un caràcter de puntuació.';
$messages['passwordtooweak'] = 'La contrasenya és massa feble.';
$messages['passwordnoseq'] = 'La contrasenya no hauria de ser una seqüència com ara 123456 o QWERTY.';
$messages['passwordnocommon'] = 'La contrasenya no hauria de ser una paraula o un nom comú.';
$messages['passwordforbidden'] = 'La contrasenya conté caràcters no permesos.';
$messages['firstloginchange'] = 'Aquest és el vostre primer accés. Si us plau, canvieu-vos la contrasenya.';
$messages['disablednotice'] = 'El sistema és actualment sota manteniment i els canvis de contrasenya no són permesos en aquest moment. En breu tot tornarà a la normalitat. Us demanem disculpes pels inconvenients.';
$messages['passwinhistory'] = 'Aquesta contrasenya ja es va fer servir anteriorment.';
$messages['samepasswd'] = 'La nova contrasenya ha de ser diferent de l\'antiga.';
$messages['passwdexpirewarning'] = 'Compte!, la vostra contrasenya caducarà aviat. Canvieu-la abans de $expirationdatetime.';
$messages['passwdexpired'] = 'La vostra contrasenya ha caducat, l\'heu de canviar ara!';
$messages['passwdconstraintviolation'] = 'Violació de restricció de les contrasenyes. La contrasenya és probablement massa feble.';
$messages['pwned_mustnotbedisclosed'] = 'La contrasenya no pot ser&nbsp;<a href="$href" target="_blank">habitualment coneguda</a>.';
$messages['pwned_isdisclosed'] = 'Aquesta contrasenya és coneguda habitualment.';
$messages['pwned_fetcherror'] = 'No s\'ha pogut verificar la fortalesa de la contrasenya.';
