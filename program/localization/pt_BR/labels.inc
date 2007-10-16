<?php

/*

 +-----------------------------------------------------------------------+
 | language/pt_BR/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Anderson S. Ferreira <anderson@cnpm.embrapa.br>               |
 | Updated by: Robson F. Ramaldes <rframaldes@gmail.com>                 |
 | Updated by: Alessandro Lenzi <alessandro@mistymountain.com.br>        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome'] 	 = 'Bem Vindo ao $product';
$labels['username']  = 'Usuário';
$labels['password']  = 'Senha';
$labels['server'] 	 = 'Servidor';
$labels['login'] 	 = 'Entrar';

// taskbar
$labels['logout'] 	= 'Sair';
$labels['mail'] 	= 'E-mail';
$labels['settings'] = 'Configurações pessoais';
$labels['addressbook'] = 'Catálogo de endereços';

// mailbox names
$labels['inbox']  = 'Entrada';
$labels['drafts'] = 'Rascunhos';
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Lixeira';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Assunto';
$labels['from']    = 'Remetente';
$labels['to']      = 'Para';
$labels['cc']      = 'Cópia';
$labels['bcc']     = 'Cópia Oculta';
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

$labels['moveto'] 	= 'mover para...';
$labels['download'] = 'download';

$labels['filename'] = 'Arquivo';
$labels['filesize'] = 'Tamanho';

$labels['preferhtml'] = 'Preferir HTML';
$labels['htmlmessage'] = 'Mensagem HTML';
$labels['prettydate'] = 'Formatar datas';

$labels['addtoaddressbook'] = 'Incluir no catálogo de endereços';

// weekdays short
$labels['sun'] = 'Dom';
$labels['mon'] = 'Seg';
$labels['tue'] = 'Ter';
$labels['wed'] = 'Qua';
$labels['thu'] = 'Qui';
$labels['fri'] = 'Sex';
$labels['sat'] = 'Sáb';

// weekdays long
$labels['sunday'] 	 = 'Domingo';
$labels['monday']	 = 'Segunda-feira';
$labels['tuesday']	 = 'Terça-feira';
$labels['wednesday'] = 'Quarta-feira';
$labels['thursday']	 = 'Quinta-feira';
$labels['friday']	 = 'Sexta-feira';
$labels['saturday']	 = 'Sábado';

$labels['today'] = 'Hoje';

// toolbar buttons
$labels['checkmail'] 		= 'Verificar se há novas mensagens.';
$labels['writenewmessage']	= 'Criar nova mensagem';
$labels['replytomessage'] 	= 'Responder';
$labels['replytoallmessage'] = 'Responder para todos';
$labels['forwardmessage'] 	= 'Encaminhar';
$labels['deletemessage'] 	= 'Excluir';
$labels['movemessagetotrash'] = 'Mover para Lixeira';
$labels['printmessage'] 	= 'Imprimir';
$labels['previousmessage'] 	= 'Mensagem Anterior';
$labels['previousmessages'] = 'Página Anterior';
$labels['firstmessage'] 	= 'Primeira Mensagem';
$labels['firstmessages'] 	= 'Primeira Página';
$labels['nextmessage'] 		= 'Próxima Mensagem';
$labels['nextmessages'] 	= 'Próxima Página';
$labels['lastmessage'] 		= 'Ultima Mensagem';
$labels['lastmessages'] 	= 'Ultima Página';
$labels['backtolist'] 		= 'Voltar';
$labels['viewsource'] 		= 'Exibir código fonte';

$labels['select'] = 'Selecionar';
$labels['all'] = 'Todas';
$labels['none'] = 'Nenhuma';
$labels['unread'] = 'Não lidas';

$labels['compact'] = 'Compactar';
$labels['empty'] = 'Esvaziar';
$labels['purge'] = 'Apagar';

$labels['quota'] = 'Utilização';
$labels['unknown']  = 'desconhecido';
$labels['unlimited']  = 'Sem limitação';

$labels['quicksearch'] = 'Pesquisa Rápida';
$labels['resetsearch'] = 'Limpar';


// message compose
$labels['compose'] 	      = 'Escrever mensagem';
$labels['sendmessage']	  = 'Enviar';
$labels['savemessage']    = 'Salvar Rascunho';
$labels['addattachment']  = 'Anexar';
$labels['charset']        = 'Codificação';
$labels['editortype']     = 'Tipo';
$labels['returnreceipt']  = 'Receber Confirmação';

$labels['checkspelling'] = 'Verificar Ortografia';
$labels['resumeediting'] = 'Continuar';
$labels['revertto']		 = 'Reverter para';

$labels['attachments'] = 'Anexos';
$labels['upload'] = 'Enviar';
$labels['close']  = 'Fechar';

$labels['low'] 	   = 'Mais baixa';
$labels['lowest']  = 'Baixa';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alta';
$labels['highest'] = 'Mais alta';

$labels['nosubject']  = '(Sem assunto)';
$labels['showimages'] = 'Exibir imagens';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto Puro';

$labels['addcc'] = 'Cópia';
$labels['addbcc'] = 'Cópia Oculta';
$labels['addreplyto'] = 'Responder para';


// address boook
$labels['name'] 	 = 'Nome completo';
$labels['firstname'] = 'Primeiro nome';
$labels['surname']	 = 'Sobrenome';
$labels['email'] 	 = 'E-Mail';

$labels['addcontact'] = 'Incluir novo contato';
$labels['editcontact'] = 'Editar contato';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Salvar';
$labels['delete'] = 'Apagar';

$labels['newcontact'] 	  = 'Criar novo contato';
$labels['deletecontact']  = 'Apagar contatos selecionados';
$labels['composeto'] 	  = 'Escrever mensagem para';
$labels['contactsfromto'] = 'Contatos de $from à $to de $count';
$labels['print'] 		  = 'Imprimir';
$labels['export'] 		  = 'Exportar';

$labels['previouspage']   = 'Página Anterior';
$labels['firstpage'] 	  = 'Primeira Página ';
$labels['nextpage'] 	  = 'Próxima Página';
$labels['lastpage']	  	  = 'Última Página';

$labels['groups'] = 'Grupos';
$labels['personaladrbook'] = 'Endereços Pessoais';


// settings
$labels['settingsfor']  = 'Configurações para';

$labels['preferences']  = 'Preferências';
$labels['userpreferences']  = 'Preferências do usuário';
$labels['editpreferences']  = 'Editar preferências do usuário';

$labels['identities']  = 'Identidades';
$labels['manageidentities']  = 'Gerenciar identidades para esta conta';
$labels['newidentity']  = 'Nova identidade';

$labels['newitem']  = 'Novo item';
$labels['edititem']  = 'Editar item';

$labels['setdefault']  = 'Padrão';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Fuso Horário';
$labels['pagesize']  = 'Linhas por página';
$labels['signature'] = 'Assinatura';
$labels['dstactive']  = 'Horário de Verão';
$labels['htmleditor'] = 'Criar mensagens em HTML';
$labels['htmlsignature'] = 'Assinatura em HTML';
$labels['previewpane'] = 'Exibir Pré-Visualização';

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