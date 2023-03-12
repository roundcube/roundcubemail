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

$labels['changepasswd']  = 'Alterar senha';
$labels['curpasswd']  = 'Senha actual:';
$labels['newpasswd']  = 'Nova senha:';
$labels['confpasswd']  = 'Confirmar senha:';
$messages['nopassword'] = 'Introduza a nova senha.';
$messages['nocurpassword'] = 'Introduza a senha atual.';
$messages['passwordincorrect'] = 'Senha atual incorreta.';
$messages['passwordinconsistency'] = 'As senhas não coincidem, tente novamente.';
$messages['crypterror'] = 'Não foi possível guardar a nova senha. Função de criptografia em falta.';
$messages['connecterror'] = 'Não foi possível guardar a nova senha. Erro de ligação.';
$messages['internalerror'] = 'Não foi possível guardar a nova senha.';
$messages['passwordshort'] = 'A senha deve ter pelo menos $length caracteres';
$messages['passwordweak'] = 'A senha deve incluir pelo menos um número e um sinal de pontuação.';
$messages['passwordtooweak'] = 'Senha demasiado fraca';
$messages['passwordnoseq'] = 'A senha não deve ser uma sequência como 123456 ou QWERTY.';
$messages['passwordnocommon'] = 'A senha não deve ser uma palavra ou nome comum.';
$messages['passwordforbidden'] = 'A senha contém caracteres não suportados.';
$messages['firstloginchange'] = 'Este é o seu primeiro acesso. Por favor, altere a sua senha.';
$messages['disablednotice'] = 'O sistema está em manutenção e a alteração da password não é possível neste momento. Tudo deve volta ao normal em breve. Pedimos desculpas por qualquer inconveniente.';
$messages['passwinhistory'] = 'Esta senha já foi usada anteriormente.';
$messages['samepasswd'] = 'A nova senha tem de ser diferente da antiga.';
$messages['passwdexpirewarning'] = 'Aviso! A sua senha irá expirar em breve. Deve alterá-la antes de $expirationdatetime.';
$messages['passwdexpired'] = 'A sua senha expirou. Tem de alterá-la agora!';
$messages['passwdconstraintviolation'] = 'Violação da limitação de senha. Senha provavelmente muito fraca.';
$messages['pwned_mustnotbedisclosed'] = 'A password não deve ser <a href="$href" target="_blank">vulgarmente conhecida</a>.';
$messages['pwned_isdisclosed'] = 'Esta senha é vulgarmente conhecida.';
$messages['pwned_fetcherror'] = 'Falha ao verificar a força da senha.';
