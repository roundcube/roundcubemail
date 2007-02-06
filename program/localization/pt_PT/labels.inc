<?php

/*

 +-----------------------------------------------------------------------+
 | language/pt_PT/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Sergio Rocha <sergio.rocha@makeitsimple.pt>                   |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// Página de login
$labels['username']  = 'Utilizador';
$labels['password']  = 'Senha';
$labels['server']    = 'Servidor';
$labels['login']     = 'Entrar';

// taskbar
$labels['logout']   = 'Sair';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Configurações pessoais';
$labels['addressbook'] = 'Lista de Contactos';

// Nome das pastas de correio
$labels['inbox']  = 'Caixa de entrada';
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Lixeira';
$labels['drafts'] = 'Rascunhos';
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

$labels['preferhtml'] = 'Prefere HTML';
$labels['htmlmessage'] = 'Mensagem HTML';
$labels['prettydate'] = 'Formatar datas';

$labels['addtoaddressbook'] = 'Adicionar ao catálogo de endereços';

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
$labels['writenewmessage']  = 'Criar nova mensagem';
$labels['replytomessage']   = 'Responder';
$labels['replytoallmessage'] = 'Responder a todos';
$labels['forwardmessage']   = 'Encaminhar';
$labels['deletemessage']    = 'Mover para lixeira';
$labels['printmessage']     = 'Imprimir';
$labels['previousmessages'] = 'Anterior';
$labels['nextmessages']     = 'Próximo';
$labels['backtolist']       = 'Voltar';
$labels['viewsource']       = 'Mostrar código fonte';

$labels['select'] = 'Seleccionar';
$labels['all'] = 'Todas';
$labels['none'] = 'Nenhuma';
$labels['unread'] = 'Não lidas';

$labels['compact'] = 'Compactar';
$labels['empty'] = 'Vazio';
$labels['purge'] = 'Apagar';

$labels['quota'] = 'Utilização';

// message compose
$labels['compose']  = 'Escrever mensagem';
$labels['sendmessage']  = 'Enviar';
$labels['addattachment']  = 'Anexar';
$labels['charset']  = 'Charset';

$labels['attachments'] = 'Anexos';
$labels['upload'] = 'Upload';
$labels['close']  = 'Fechar';

$labels['low']     = 'Mais baixo';
$labels['lowest']  = 'Baixo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alta';
$labels['highest'] = 'Mais alta';

$labels['nosubject']  = '(no assunto)';
$labels['showimages'] = 'Exibir imagens';

// address boook
$labels['name']      = 'Nome completo';
$labels['firstname'] = 'Primeiro nome';
$labels['surname']   = 'Apelido';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Incluir novo contacto';
$labels['editcontact'] = 'Editar contacto';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Gravar';
$labels['delete'] = 'Apagar';

$labels['newcontact']     = 'Criar novo contacto';
$labels['deletecontact']  = 'Apagar contactos selecionados';
$labels['composeto']      = 'Escrever mensagem para';
$labels['contactsfromto'] = 'Contactos $from - $to of $count';
$labels['print']          = 'Imprimir';
$labels['export']         = 'Exportar';

// LDAP search
$labels['ldapsearch'] = 'Pesquisa no diretório LDAP';

$labels['ldappublicsearchname']    = 'Nome do contacto';
$labels['ldappublicsearchtype'] = 'Pesquisa exacta?';
$labels['ldappublicserverselect'] = 'Selecionar servidores';
$labels['ldappublicsearchfield'] = 'Pesquisar em';
$labels['ldappublicsearchform'] = 'Procurar por um contacto';
$labels['ldappublicsearch'] = 'Pesquisar';

// settings
$labels['settingsfor']  = 'Configurações para';

$labels['preferences']  = 'Preferências';
$labels['userpreferences']  = 'Preferências do utilizador';
$labels['editpreferences']  = 'Editar preferências do utilizador';

$labels['identities']  = 'Identidades';
$labels['manageidentities']  = 'Gerir identidades para esta conta';
$labels['newidentity']  = 'Nova identidade';

$labels['newitem']  = 'Novo item';
$labels['edititem']  = 'Editar item';

$labels['setdefault']  = 'Principal';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Linhas por página';
$labels['signature'] = 'Assinatura';

$labels['folder']  = 'Pasta';
$labels['folders']  = 'Pastas';
$labels['foldername']  = 'Nome da pasta';
$labels['subscribed']  = 'Subscrito';
$labels['create']  = 'Criar';
$labels['createfolder']  = 'Criar nova pasta';
$labels['deletefolder']  = 'Apagar pasta';
$labels['managefolders']  = 'Gerir pastas';

$labels['sortby'] = 'Ordenado por';
$labels['sortasc']  = 'Ascendente';
$labels['sortdesc'] = 'Descendente';

// New labels since 0.1beta

$labels['welcome'] = 'Bem-vindo ao $product';

$labels['unknown'] = 'Desconhecido';
$labels['unlimited'] = 'Ilimitado';
$labels['dstactive'] = 'Horário de verão';

$labels['previouspage'] = 'Exibir página anterior';
$labels['nextpage']     = 'Exibir próxima página';

$labels['returnreceipt'] = 'Confirmação de leitura';

$labels['checkmail'] = 'Verificar novas mensagens';
$labels['checkspelling'] = 'Verificar ortografia';
$labels['resumeediting'] = 'Continuar a edição';
$labels['revertto'] = 'Reverter para';

$labels['savemessage']  = 'Salvar rascunho';
$labels['rename'] = 'Renomear';
$labels['renamefolder'] = 'Renomear pasta';

// I use an additional description field - this might be used somewhere else
$labels['description'] = 'Descrição';

?>