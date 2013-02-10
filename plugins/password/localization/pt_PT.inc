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
$labels['changepasswd']  = 'Alterar password';
$labels['curpasswd']  = 'Password atual:';
$labels['newpasswd']  = 'Nova password:';
$labels['confpasswd']  = 'Confirmar password:';

$messages = array();
$messages['nopassword'] = 'Introduza a nova password.';
$messages['nocurpassword'] = 'Introduza a password actual.';
$messages['passwordincorrect'] = 'Password actual errada.';
$messages['passwordinconsistency'] = 'Password\'s não combinam, tente novamente..';
$messages['crypterror'] = 'Não foi possível gravar a nova password. Função de criptografica inexistente.';
$messages['connecterror'] = 'Não foi possível gravar a nova password. Erro de ligação.';
$messages['internalerror'] = 'Não foi possível gravar a nova password.';
$messages['passwordshort'] = 'A palavra-passe deve ter pelo menos $length caracteres';
$messages['passwordweak'] = 'A palavra-passe deve incluir pelo menos um numero e um sinal de pontuação.';
$messages['passwordforbidden'] = 'A palavra-passe contém caracteres não suportados.';

?>
