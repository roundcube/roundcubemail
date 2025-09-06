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
$labels['curpasswd']  = 'Contraseña actual: ';
$labels['newpasswd']  = 'Contraseña nueva:';
$labels['confpasswd']  = 'Confirmar contraseña nueva:';
$messages['nopassword'] = 'Por favor, ingrese la nueva contraseña.';
$messages['nocurpassword'] = 'Por favor, ingrese la contraseña actual.';
$messages['passwordincorrect'] = 'Contraseña actual incorrecta.';
$messages['passwordinconsistency'] = 'Las contraseñas no coinciden, por favor intente nuevamente.';
$messages['crypterror'] = 'No se pudo guardar la nueva contraseña. No se encuentra la función de encriptación.';
$messages['connecterror'] = 'No se pudo guardar la nueva contraseña. Error de conexión.';
$messages['internalerror'] = 'No se pudo guardar la nueva contraseña.';
$messages['passwordshort'] = 'La contraseña debe tener al menos $length carácteres.';
$messages['passwordweak'] = 'La contraseña debe incluir por lo menos un número y un signo de puntuación.';
$messages['passwordtooweak'] = 'La contraseña es muy débil.';
$messages['passwordnoseq'] = 'La contraseña no puede ser una secuencia como 123456 or QWERTY.';
$messages['passwordnocommon'] = 'La contraseña no puede ser una palabra o nombre común.';
$messages['passwordforbidden'] = 'La contraseña contiene carácteres ilegales.';
$messages['firstloginchange'] = 'Esta es la primera vez que ingresa. Por favor cambie su contraseña.';
$messages['disablednotice'] = 'El sistema se encuentra en mantenimiento actualmente y el cambio de contraseña no es posible en este momento. Todo volverá a la normalidad pronto. Lamentamos los inconvenientes.';
$messages['passwinhistory'] = 'La contraseña ya ha sido usada previamente.';
$messages['samepasswd'] = 'La nueva contraseña tiene que ser diferente a la anterior.';
$messages['passwdexpirewarning'] = 'Alerta! Su contraseña expirará pronto, cambiela antes de $expirationdatetime.';
$messages['passwdexpired'] = 'Su contraseña ha expirado, tiene que cambiarla ahora!';
$messages['passwdconstraintviolation'] = 'Violación de restricción de contraseña. Probablemente la contraseña sea demasiado débil.';
$messages['pwned_isdisclosed'] = 'Esta contraseña es comúnmente conocida.';
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
