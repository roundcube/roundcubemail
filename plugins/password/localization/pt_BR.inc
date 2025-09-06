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

$labels['changepasswd']  = 'Alterar a senha';
$labels['curpasswd']  = 'Senha atual:';
$labels['newpasswd']  = 'Nova senha:';
$labels['confpasswd']  = 'Confirmar nova senha:';
$messages['nopassword'] = 'Por favor, informe a nova senha.';
$messages['nocurpassword'] = 'Por favor, informe a senha atual.';
$messages['passwordincorrect'] = 'Senha atual incorreta.';
$messages['passwordinconsistency'] = 'Senhas não combinam, por favor tente novamente.';
$messages['crypterror'] = 'Não foi possível gravar a nova senha. Função de criptografia ausente.';
$messages['connecterror'] = 'Não foi possível gravar a nova senha. Erro de conexão.';
$messages['internalerror'] = 'Não foi possível gravar a nova senha.';
$messages['passwordshort'] = 'A senha precisa ter ao menos $length caracteres.';
$messages['passwordweak'] = 'A senha precisa conter ao menos um número e um caractere de pontuação.';
$messages['passwordtooweak'] = 'Senha fraca demais.';
$messages['passwordnoseq'] = 'Senha não deve ser uma sequência como 123456 ou QWERTY.';
$messages['passwordnocommon'] = 'Senha não deve ser uma palavra comum ou nome.';
$messages['passwordforbidden'] = 'A senha contém caracteres proibidos.';
$messages['firstloginchange'] = 'Este é o seu primeiro acesso. Por favor altere sua senha.';
$messages['disablednotice'] = 'O sistema está em manutenção e a senha não pode ser alterada no momento. Tudo voltará ao normal em breve. Pedimos desculpas pelo inconveniente.';
$messages['passwinhistory'] = 'Esta senha já foi usada antes.';
$messages['samepasswd'] = 'A nova senha deve ser diferente da antiga.';
$messages['passwdexpirewarning'] = 'Atenção! Sua senha vai expirar em breve, altere ela antes de $expirationdatetime.';
$messages['passwdexpired'] = 'Sua senha expirou, você precisa alterá-la agora!';
$messages['passwdconstraintviolation'] = 'Restrição de senha violada. Senha provavelmente muito fraca.';
$messages['pwned_mustnotbedisclosed'] = 'A senha não deve ser <a href="$href" target="_blank">vulgarmente conhecida</a>.';
$messages['pwned_isdisclosed'] = 'Esta senha é vulgarmente conhecida.';
$messages['pwned_fetcherror'] = 'Falha ao verificar a força da senha.';

$labels['hestia_err_policy_short'] = 'Senha muito curta (comprimento mínimo: %min).';
$labels['hestia_err_endpoint_invalid'] = 'O endpoint da Hestia não está configurado ou é inválido.';
$labels['hestia_err_identity'] = 'Não é possível analisar o nome de usuário em local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Não foi possível determinar o proprietário do domínio para %domain.';
$labels['hestia_err_unknown'] = 'Erro desconhecido da API da Hestia.';
$labels['hestia_err_default'] = 'A Hestia retornou o código de erro %code.';
$labels['hestia_err_1'] = 'Não foram fornecidos argumentos suficientes.';
$labels['hestia_err_2'] = 'Objeto ou argumento inválido.';
$labels['hestia_err_3'] = 'O objeto não existe (caixa de correio ou domínio não encontrado).';
$labels['hestia_err_4'] = 'O objeto já existe.';
$labels['hestia_err_5'] = 'O objeto já está suspenso.';
$labels['hestia_err_6'] = 'O objeto já não está suspenso.';
$labels['hestia_err_7'] = 'Objeto em uso; não pode ser modificado.';
$labels['hestia_err_8'] = 'Operação bloqueada por limites de pacotes.';
$labels['hestia_err_9'] = 'Senha incorreta ou inválida.';
$labels['hestia_err_10'] = 'Permissão negada pela Hestia (a identidade da API não tem direitos ou o IP não é permitido).';
$labels['hestia_err_11'] = 'Recurso desativado pela política do sistema.';
$labels['hestia_err_12'] = 'Erro de análise.';
$labels['hestia_err_13'] = 'Erro no disco.';
$labels['hestia_err_14'] = 'Carga do servidor muito alta.';
$labels['hestia_err_15'] = 'Erro de conexão.';
$labels['hestia_err_16'] = 'Erro de FTP.';
$labels['hestia_err_17'] = 'Erro no banco de dados.';
$labels['hestia_err_18'] = 'Erro de RRD.';
$labels['hestia_err_19'] = 'Erro de atualização.';
$labels['hestia_err_20'] = 'Erro de reinicialização.';
?>
