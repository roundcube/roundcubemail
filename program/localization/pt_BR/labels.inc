<?php

/*
 +-----------------------------------------------------------------------+
 | language/pt_BR/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundQube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Anderson S. Ferreira <anderson@cnpm.embrapa.br>               |
 | Updated by: Robson F. Ramaldes <rframaldes@gmail.com>                 |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// Página de loginOnthaal aan $product
$labels['welcome']   = 'Bem Vindo ao $product';
$labels['username']  = 'Usuário';
$labels['password']  = 'Senha';
$labels['server']    = 'Servidor';
$labels['login']     = 'Entrar';

// taskbar
$labels['logout']   = 'Sair';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Configurações pessoais';
$labels['addressbook'] = 'Catálogo de endereços';

// Nome das pastas de correio
$labels['inbox']  = 'Caixa de entrada';
$labels['drafts'] = 'Rascunhos';
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Lixeira';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Assunto';
$labels['from']    = 'Remetente';
$labels['to']      = 'Para';
$labels['cc']      = 'Cópia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Responder para';
$labels['date']    = 'Data';
$labels['size']    = 'Tamanho';
$labels['priority'] = 'Prioridade';
$labels['organization'] = 'Organização';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Pastas';
$labels['messagesfromto'] = 'Mensagens $from - $to de $count';
$labels['messagenrof'] = 'Mensagem $nr de $count';

$labels['moveto']   = 'mover para...';
$labels['download'] = 'download';

$labels['filename'] = 'Arquivo';
$labels['filesize'] = 'Tamanho';

$labels['preferhtml'] = 'Preferir HTML';
$labels['htmlmessage'] = 'Mensagem HTML';
$labels['prettydate'] = 'Formatar datas';

$labels['addtoaddressbook'] = 'Incluir no catálogo de endereços';

// Dias da semana abreviados
$labels['sun'] = 'Dom';
$labels['mon'] = 'Seg';
$labels['tue'] = 'Ter';
$labels['wed'] = 'Qua';
$labels['thu'] = 'Qui';
$labels['fri'] = 'Sex';
$labels['sat'] = 'Sáb';

// Dias da semana completos
$labels['sunday']    = 'Domingo';
$labels['monday']    = 'Segunda-feira';
$labels['tuesday']   = 'Terça-feira';
$labels['wednesday'] = 'Quarta-feira';
$labels['thursday']  = 'Quinta-feira';
$labels['friday']    = 'Sexta-feira';
$labels['saturday']  = 'Sábado';

$labels['today'] = 'Hoje';

// toolbar buttons
$labels['checkmail']        = 'Verificar se há novas mensagens';
$labels['writenewmessage']  = 'Criar nova mensagem';
$labels['replytomessage']   = 'Responder';
$labels['replytoallmessage'] = 'Responder para todos';
$labels['forwardmessage']   = 'Encaminhar	';
$labels['deletemessage']    = 'Mover para lixeira';
$labels['printmessage']     = 'Imprimir';
$labels['previousmessages'] = 'Página Anterior';
$labels['nextmessages']     = 'Próxima Página';
$labels['previousmessage']  = 'Mensagem Anterior';
$labels['firstmessage']     = 'Primeira Mensagem';
$labels['firstmessages']    = 'Primeira Página';
$labels['nextmessage']      = 'Próxima Mensagem';
$labels['lastmessage']      = 'Ultima Mensagem';
$labels['lastmessages']     = 'Ultima Página';
$labels['backtolist']       = 'Voltar';
$labels['viewsource']       = 'Exibir código fonte';

$labels['select'] = 'Selecionar';
$labels['all'] = 'Todas';
$labels['none'] = 'Nenhuma';
$labels['unread'] = 'Não lidas';

$labels['compact'] = 'Compactar';
$labels['empty'] = 'Vazio';
$labels['purge'] = 'Apagar';

$labels['quota'] = 'Utilização';
$labels['unknown']  = 'desconhecido';
$labels['unlimited']  = 'sem limitação';

$labels['quicksearch']  = 'Pesquisa Rápida';
$labels['resetsearch']  = 'Limpar';


// message compose
$labels['compose']  = 'Escrever mensagem';
$labels['savemessage']  = 'Salvar Rascunho';
$labels['sendmessage']  = 'Enviar';
$labels['addattachment']  = 'Anexar';
$labels['charset']  = 'Codificação';
$labels['returnreceipt'] = 'Receber Confirmação';
$labels['editortype']     = 'Tipo';

$labels['checkspelling'] = 'Verificar Ortografia';
$labels['resumeediting'] = 'Continuar';
$labels['revertto'] = 'Revert to';


$labels['attachments'] = 'Anexos';
$labels['upload'] = 'Enviar';
$labels['close']  = 'Fechar';

$labels['low']     = 'Mais baixo';
$labels['lowest']  = 'Baixo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alta';
$labels['highest'] = 'Mais alta';

$labels['nosubject']  = '(sem assunto)';
$labels['showimages'] = 'Exibir imagens';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto Puro';


// address boook
$labels['name']      = 'Nome completo';
$labels['firstname'] = 'Primeiro nome';
$labels['surname']   = 'Sobrenome';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Incluir novo contato';
$labels['editcontact'] = 'Editar contato';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Salvar';
$labels['delete'] = 'Apagar';

$labels['newcontact']     = 'Criar novo contato';
$labels['deletecontact']  = 'Apagar contatos selecionados';
$labels['composeto']      = 'Escrever mensagem para';
$labels['contactsfromto'] = 'Contatos $from - $to of $count';
$labels['print']          = 'Imprimir';
$labels['export']         = 'Exportar';

$labels['previouspage']   = 'Página Anterior';
$labels['nextpage']       = 'Próxima Página';
$labels['firstpage']      = 'Primeira Página ';
$labels['lastpage']       = 'Ultima Página';


// LDAP search
$labels['ldapsearch'] = 'Pesquisa no diretório LDAP';

$labels['ldappublicsearchname']    = 'Nome do contado';
$labels['ldappublicsearchtype'] = 'Pesquisa exata?';
$labels['ldappublicserverselect'] = 'Selecionar servidores';
$labels['ldappublicsearchfield'] = 'Pesquisar em';
$labels['ldappublicsearchform'] = 'Procurar por um contato';
$labels['ldappublicsearch'] = 'Pesquisar';


// settings
$labels['settingsfor']  = 'Configurações para';

$labels['preferences']  = 'Preferências';
$labels['userpreferences']  = 'Preferências do usuário';
$labels['editpreferences']  = 'Editar preferências do usuário';

$labels['identities']  = 'Identidades';
$labels['manageidentities']  = 'Gerenciar identidades para essa conta';
$labels['newidentity']  = 'Nova identidade';

$labels['newitem']  = 'Novo item';
$labels['edititem']  = 'Editar item';

$labels['setdefault']  = 'Padrão';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Linhas por página';
$labels['signature'] = 'Assinatura';
$labels['dstactive']  = 'Horário de Verão';
$labels['htmleditor'] = 'Criar mensagens em HTML';
$labels['htmlsignature'] = 'Assinatura em HTML';
$labels['previewpane'] = 'Exibir Pre-Vizualização';

$labels['autosavedraft']  = 'Salvar Rascunho automaticamente';
$labels['everynminutes']  = 'a cada $n minutos';
$labels['never']  = 'nunca';

$labels['folder']  = 'Pasta';
$labels['folders']  = 'Pastas';
$labels['foldername']  = 'Nome da pasta';
$labels['subscribed']  = 'Assinado';
$labels['create']  = 'Criar';
$labels['createfolder']  = 'Criar nova pasta';
$labels['rename'] = 'Renomear';
$labels['renamefolder'] = 'Renomear Pasta';
$labels['deletefolder']  = 'Apagar pasta';
$labels['managefolders']  = 'Gerenciar pastas';

$labels['sortby'] = 'Ordenado por';
$labels['sortasc']  = 'Ascendente';
$labels['sortdesc'] = 'Descendente';

?>
