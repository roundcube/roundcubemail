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

$labels['changepasswd']  = 'Cambiar o contrasinal';
$labels['curpasswd']  = 'Contrasinal actual:';
$labels['newpasswd']  = 'Novo contrasinal:';
$labels['confpasswd']  = 'Confirmar o novo contrasinal:';
$messages['nopassword'] = 'Por favor, introduce un novo contrasinal.';
$messages['nocurpassword'] = 'Por favor, introduce o contrasinal actual.';
$messages['passwordincorrect'] = 'O contrasinal actual é incorrecto.';
$messages['passwordinconsistency'] = 'Os contrasinais non coinciden. Por favor, inténtao outra vez.';
$messages['crypterror'] = 'Non foi posible gardar o novo contrasinal. Falta a función de cifraxe.';
$messages['connecterror'] = 'Non foi posible gardar o novo contrasinal. Erro de conexión.';
$messages['internalerror'] = 'Non foi posible gardar o novo contrasinal.';
$messages['passwordshort'] = 'O contrasinal debe ter polo menos $length caracteres.';
$messages['passwordweak'] = 'O contrasinal debe incluír polo menos un número e un signo de puntuación.';
$messages['passwordtooweak'] = 'Contrasinal moi débil.';
$messages['passwordnoseq'] = 'O contrasinal non debe ser unha secuencia como 123456 ou QWERTY.';
$messages['passwordnocommon'] = 'O contrasinal non debe ser unha palabra ou nome común.';
$messages['passwordforbidden'] = 'O contrasinal contén caracteres non permitidos.';
$messages['firstloginchange'] = 'Este é o teu primeiro acceso. Por favor, cambia o contrasinal.';
$messages['disablednotice'] = 'O sistema está actualmente en mantemento e o cambio de contrasinal non é posible neste momento. Todo volverá á normalidade en breve. Sentimos as molestias ocasionadas.';
$messages['passwinhistory'] = 'Este contrasinal xa foi utilizado anteriormente.';
$messages['samepasswd'] = 'O novo contrasinal debe ser diferente do anterior.';
$messages['passwdexpirewarning'] = 'Aviso! O teu contrasinal caducará pronto, cámbiao antes de $expirationdatetime.';
$messages['passwdexpired'] = 'O teu contrasinal caducou, tes que cambialo agora!';
$messages['passwdconstraintviolation'] = 'Violación de seguridade do contrasinal. Contrasinal probablemente demasiado débil.';
$messages['pwned_mustnotbedisclosed'] = 'O contrasinal non debe ser <a href="$href" target="_blank">facilmente recoñecible</a>.';
$messages['pwned_isdisclosed'] = 'Este contrasinal é facilmente recoñecible.';
$messages['pwned_fetcherror'] = 'Non se puido verificar a seguridade do contrasinal.';
