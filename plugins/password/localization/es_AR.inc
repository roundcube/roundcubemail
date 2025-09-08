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

$labels['hestia_err_policy_short'] = 'Contraseña demasiado corta (longitud mínima: %min).';
$labels['hestia_err_endpoint_invalid'] = 'El punto final de Hestia no está configurado o no es válido.';
$labels['hestia_err_identity'] = 'No se puede parsear el nombre de usuario en local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'No se puede determinar el propietario del dominio para %dominio.';
$labels['hestia_err_unknown'] = 'Error desconocido de la API de Hestia.';
$labels['hestia_err_default'] = 'Hestia ha devuelto el código de error %code.';
$labels['hestia_err_1'] = 'No se aportan suficientes argumentos.';
$labels['hestia_err_2'] = 'Objeto o argumento no válido.';
$labels['hestia_err_3'] = 'El objeto no existe (buzón o dominio no encontrado).';
$labels['hestia_err_4'] = 'El objeto ya existe.';
$labels['hestia_err_5'] = 'El objeto ya está suspendido.';
$labels['hestia_err_6'] = 'El objeto ya no está suspendido.';
$labels['hestia_err_7'] = 'Objeto en uso; no se puede modificar.';
$labels['hestia_err_8'] = 'Operación bloqueada por límites de paquetes.';
$labels['hestia_err_9'] = 'Contraseña incorrecta o no válida.';
$labels['hestia_err_10'] = 'Permiso denegado por Hestia (la identidad API carece de derechos o la IP no está permitida).';
$labels['hestia_err_11'] = 'Función desactivada por la política del sistema.';
$labels['hestia_err_12'] = 'Error de análisis.';
$labels['hestia_err_13'] = 'Error de disco.';
$labels['hestia_err_14'] = 'Carga del servidor demasiado alta.';
$labels['hestia_err_15'] = 'Error de conexión.';
$labels['hestia_err_16'] = 'Error FTP.';
$labels['hestia_err_17'] = 'Error en la base de datos.';
$labels['hestia_err_18'] = 'Error RRD.';
$labels['hestia_err_19'] = 'Error de actualización.';
$labels['hestia_err_20'] = 'Error de reinicio.';
?>
