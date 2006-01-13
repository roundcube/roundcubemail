<?php

/*
 +-----------------------------------------------------------------------+
 | language/pt_BR/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
 | Portuguese translation: Anderson Ferreira <anderson@cnpm.embrapa.br>  |
 +-----------------------------------------------------------------------+


 $Id$

*/

$labels = array();

// Página de login
$labels['username']  = 'Usuário';
$labels['password']  = 'Senha';
$labels['server']    = 'Servidor';
$labels['login']     = 'Login';

// Barra de tarefas
$labels['logout']   = 'Sair';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Configurações pessoais';
$labels['addressbook'] = 'Catálogo de endereços';

// Nome das pastas de correio
$labels['inbox']  = 'Caixa de entrada';
# Added by Cadu
# start
$labels['inbox']['drafts']='Rascunhos';
$labels['inbox']['sent']='Enviados';
$labels['inbox']['trash']='Lixeira';
# end
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Lixeira';
$labels['drafts'] = 'Rascunhos';
$labels['junk']   = 'Spam';

// Listagem das mensagem
$labels['subject'] = 'Assunto';
$labels['from']    = 'De';
$labels['to']      = 'Para';
$labels['cc']      = 'Cópia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Responder para';
$labels['date']    = 'Data';
$labels['size']    = 'Tamanho';
$labels['priority'] = 'Prioridade';
$labels['organization'] = 'Empresa';

// Rótulos
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mensagens';
$labels['messagesfromto'] = 'Mensagens $from - $to de $count';
$labels['messagenrof'] = 'Mensagem $nr de $count';

$labels['moveto']   = 'mover para ...';
$labels['download'] = 'download';

$labels['filename'] = 'Nome do arquivo';
$labels['filesize'] = 'Tamanho do arquivo';

$labels['preferhtml'] = 'Prefere HTML';
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

// Botões da barra de ferramentas
$labels['writenewmessage']  = 'Escrever nova mensagem';
$labels['replytomessage']   = 'Responder';
$labels['forwardmessage']   = 'Encaminhar';
$labels['deletemessage']    = 'Mover para a lixeira';
$labels['printmessage']     = 'Imprimir';
$labels['previousmessages'] = 'Anterior';
$labels['nextmessages']     = 'Próximo';
$labels['backtolist']       = 'Voltar';

$labels['select'] = 'Selecionar';
$labels['all'] = 'Todos';
$labels['none'] = 'Nenhum';
$labels['unread'] = 'Não lido';

// Escrever mensagem
$labels['compose']  = 'Escrever nova mensagem';
$labels['sendmessage']  = 'Enviar mensagem agora';
$labels['addattachment']  = 'Anexar arquivo';

$labels['attachments'] = 'Anexos';
$labels['upload'] = 'Enviar';
$labels['close']  = 'Fechar';

$labels['low']     = 'Baixa';
$labels['lowest']  = 'Muito baixa';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alta';
$labels['highest'] = 'Urgente';

$labels['showimages'] = 'Exibir imagens';


// Catálogo de endereços
$labels['name']      = 'Nome';
$labels['firstname'] = 'Primeiro nome';
$labels['surname']   = 'Sobrenome';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Incluir novo contato';
$labels['editcontact'] = 'Editar contato';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Salvar';
$labels['delete'] = 'Excluir';

$labels['newcontact']     = 'Criar novo contato';
$labels['deletecontact']  = 'Excluir contatos selecionados';
$labels['composeto']      = 'Compor email para ';
$labels['contactsfromto'] = 'Contatos de $from até $to de $count';


// Configurações
$labels['settingsfor']  = 'Configurações para';

$labels['preferences']  = 'Preferências';
$labels['userpreferences']  = 'Preferências do usuário';
$labels['editpreferences']  = 'Editar preferências do usuário';

$labels['identities']  = 'Identidade';
$labels['manageidentities']  = 'Gerenciar identidades para essa conta';
$labels['newidentity']  = 'Nova identidade';

$labels['newitem']  = 'Novo item';
$labels['edititem']  = 'Editar item';

$labels['setdefault']  = 'Padrão';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Linhas por página';


$labels['folders']  = 'Pastas';
$labels['foldername']  = 'Nova pasta';
$labels['subscribed']  = 'Assinadas';
$labels['create']  = 'Criar';
$labels['createfolder']  = 'Criar nova pasta';
$labels['deletefolder']  = 'Excluir pasta';
$labels['managefolders']  = 'Gerenciar pastas';

?>