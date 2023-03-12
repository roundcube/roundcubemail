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

$labels['changepasswd']  = 'Cambiar contraseña';
$labels['curpasswd']  = 'Contraseña actual:';
$labels['newpasswd']  = 'Nueva contraseña:';
$labels['confpasswd']  = 'Confirmar contraseña:';
$messages['nopassword'] = 'Por favor ingresá una nueva contraseña.';
$messages['nocurpassword'] = 'Por favor ingresá la contraseña actual.';
$messages['passwordincorrect'] = 'Contraseña actual incorrecta.';
$messages['passwordinconsistency'] = 'Las contraseñas no coinciden, por favor intentalo de nuevo.';
$messages['crypterror'] = 'No se pudo guardar la contraseña nueva. Falta la función de cifrado.';
$messages['connecterror'] = 'No se pudo guardar la contraseña nueva. Error de conexión';
$messages['internalerror'] = 'No se pudo guardar la contraseña nueva.';
$messages['passwordshort'] = 'Tu contraseña debe tener una longitud mínima de $length.';
$messages['passwordweak'] = 'Tu nueva contraseña debe incluir al menos un número y un signo de puntuación.';
$messages['passwordtooweak'] = 'La contraseña es demasiado débil.';
$messages['passwordnoseq'] = 'La contraseña no debe ser una secuencia como 123456 o QWERTY.';
$messages['passwordnocommon'] = 'La contraseña no debe ser ni una palabra común ni un nombre.';
$messages['passwordforbidden'] = 'La contraseña contiene caracteres no válidos.';
$messages['firstloginchange'] = 'Este es tu primer inicio de sesión. Por favor, cambia tu contraseña.';
$messages['disablednotice'] = 'Actualmente el sistema se encuentra en mantenimiento y el cambio de contraseña no es posible en este momento. Todo debería volver a la normalidad pronto. Te pedimos disculpas por el inconveniente.';
$messages['passwinhistory'] = 'La contraseña ya ha sido usada con anterioridad.';
$messages['samepasswd'] = 'La nueva contraseña debe ser diferente de la anterior.';
$messages['passwdexpirewarning'] = '¡Advertencia! Tu contraseña vencerá pronto, cambiala antes del $expirationdatetime.';
$messages['passwdexpired'] = 'Tu contraseña expiró. ¡Tenés que cambiarla ahora!';
$messages['passwdconstraintviolation'] = 'Violación de restricción de contraseña. Probablemente tu contraseña es demasiado débil.';
$messages['pwned_mustnotbedisclosed'] = 'La constraseña no debe ser <a href="$href" target="_blank">comúnmente conocida</a>.';
$messages['pwned_isdisclosed'] = 'La contraseña es comúnmente conocida.';
$messages['pwned_fetcherror'] = 'No se pudo verificar la seguridad de la contraseña.';
