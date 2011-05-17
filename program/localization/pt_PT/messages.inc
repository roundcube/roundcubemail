<?php

/*

+-----------------------------------------------------------------------+
| language/pt_PT/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Authors: Sergio Rocha <sergio.rocha@makeitsimple.pt>                  |
|          João Vale <jpvale@gmail.com>                                |
|         Nuno Costa <nuno@criacaoweb.net>                              |
|         Teotónio Ricardo <teotonio.ricardo@webtuga.pt>               |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'O utilizador ou palavra-passe que introduziu está incorrecto.';
$messages['cookiesdisabled'] = 'O seu navegador não suporta cookies';
$messages['sessionerror'] = 'A sua sessão é inválida ou expirou';
$messages['imaperror'] = 'A ligação ao servidor IMAP falhou';
$messages['servererror'] = 'Erro do Servidor!';
$messages['servererrormsg'] = 'Erro do Servidor: $msg';
$messages['databaserror'] = 'Erro de base de dados!';
$messages['errorreadonly'] = 'Não foi possível efectuar a operação. A pasta é só de leitura.';
$messages['errornoperm'] = 'Não tem permissões para efectuar esta operação.';
$messages['invalidrequest'] = 'Pedido inválido! Não foi guardada nenhuma informação.';
$messages['nomessagesfound'] = 'Nenhuma mensagem encontrada na caixa de entrada';
$messages['loggedout'] = 'A sua sessão foi terminada com sucesso. Até breve!';
$messages['mailboxempty'] = 'A caixa de entrada está vazia';
$messages['loading'] = 'A carregar...';
$messages['uploading'] = 'A enviar ficheiro...';
$messages['uploadingmany'] = 'A carregar ficheiros...';
$messages['loadingdata'] = 'A carregar informações...';
$messages['checkingmail'] = 'A verificar novas mensagens...';
$messages['sendingmessage'] = 'A enviar mensagem...';
$messages['messagesent'] = 'Mensagem enviada com sucesso';
$messages['savingmessage'] = 'A guardar mensagem...';
$messages['messagesaved'] = 'Mensagem guardada como rascunho';
$messages['successfullysaved'] = 'Guardado com sucesso';
$messages['addedsuccessfully'] = 'Contacto adicionado com sucesso';
$messages['contactexists'] = 'Já existe um contacto com esse e-mail';
$messages['blockedimages'] = 'Para proteger a sua privacidade, as imagens desta mensagem foram bloqueadas';
$messages['encryptedmessage'] = 'Esta mensagem está encriptada e não pode ser exibida. Desculpe.';
$messages['nocontactsfound'] = 'Nenhum contacto encontrado';
$messages['contactnotfound'] = 'O contacto especificado não foi encontrado';
$messages['sendingfailed'] = 'Falha no envio da mensagem';
$messages['senttooquickly'] = 'Por favor espere $sec seg(s). antes de enviar esta mensagem';
$messages['errorsavingsent'] = 'Ocorreu um erro ao guardar a mensagem enviada';
$messages['errorsaving'] = 'Ocorreu um erro ao guardar';
$messages['errormoving'] = 'Não foi possível mover a mensagem';
$messages['errorcopying'] = 'Não foi possível copiar a mensagem';
$messages['errordeleting'] = 'Não foi possível apagar a mensagem';
$messages['errormarking'] = 'Não foi possível marcar a mensagem.';
$messages['deletecontactconfirm'] = 'Deseja realmente eliminar o(s) contacto(s) selecionado(s)?';
$messages['deletemessagesconfirm'] = 'Deseja realmente eliminar a(s) mensagem(ns) selecionada(s)?';
$messages['deletefolderconfirm'] = 'Deseja realmente eliminar esta pasta?';
$messages['purgefolderconfirm'] = 'Deseja realmente eliminar todas mensagens desta pasta?';
$messages['folderdeleting'] = 'A eliminar pasta...';
$messages['foldermoving'] = 'A mover pasta...';
$messages['foldersubscribing'] = 'A subscrever pasta...';
$messages['folderunsubscribing'] = 'A cancelar subscrição da pasta...';
$messages['formincomplete'] = 'Os campos não foram completamente preenchidos';
$messages['noemailwarning'] = 'Por favor escreva um endereço de e-mail válido';
$messages['nonamewarning'] = 'Por favor escreva o nome';
$messages['nopagesizewarning'] = 'Por favor escreva o tamanho da página';
$messages['nosenderwarning'] = 'Por favor insira o endereço de e-mail do remetente';
$messages['norecipientwarning'] = 'Por favor escreva pelo menos um destinatário';
$messages['nosubjectwarning'] = 'O campo "Assunto" não foi preenchido. Deseja incluí-lo agora?';
$messages['nobodywarning'] = 'Enviar a mensagem sem texto?';
$messages['notsentwarning'] = 'A mensagem não foi enviada, deseja apagá-la?';
$messages['noldapserver'] = 'Seleccione um servidor LDAP para a pesquisa';
$messages['nocontactsreturned'] = 'Não foi encontrado nenhum contacto';
$messages['nosearchname'] = 'Por favor, escreva o nome do contacto ou endereço de e-mail';
$messages['notuploadedwarning'] = 'Nem todos os anexos foram ainda enviados. Por favor aguarde ou cancele o envio.';
$messages['searchsuccessful'] = '$nr mensagens encontradas';
$messages['searchnomatch'] = 'A pesquisa não devolveu resultados';
$messages['searching'] = 'A pesquisar...';
$messages['checking'] = 'A verificar...';
$messages['nospellerrors'] = 'Não foi encontrado nenhum erro ortográfico';
$messages['folderdeleted'] = 'Pasta eliminada com sucesso';
$messages['foldersubscribed'] = 'Subscrição da pasta concluída com sucesso';
$messages['folderunsubscribed'] = 'Subscrição da pasta cancelada com sucesso';
$messages['folderpurged'] = 'Pasta eliminada com sucesso';
$messages['folderexpunged'] = 'Pasta compactada com sucesso.';
$messages['deletedsuccessfully'] = 'Eliminado com sucesso';
$messages['converting'] = 'A remover a formatação...';
$messages['messageopenerror'] = 'Não foi possível carregar a mensagem';
$messages['fileuploaderror'] = 'Falha ao carregar o ficheiro';
$messages['filesizeerror'] = 'O ficheiro excede o tamanho máximo permitido - $size';
$messages['copysuccess'] = 'Foram copiados $nr endereços com sucesso';
$messages['copyerror'] = 'Não foi possível copiar os endereços';
$messages['sourceisreadonly'] = 'Esta origem de endereços é só de leitura';
$messages['errorsavingcontact'] = 'Não foi possível guardar o endereço deste contacto';
$messages['movingmessage'] = 'A mover mensagem...';
$messages['copyingmessage'] = 'A copiar mensagem...';
$messages['deletingmessage'] = 'A eliminar mensagens...';
$messages['markingmessage'] = 'A marcar mensagens...';
$messages['receiptsent'] = 'Recibo de leitura enviadocom sucesso';
$messages['errorsendingreceipt'] = 'Não foi possível enviar o recibo';
$messages['nodeletelastidentity'] = 'Esta é a última identidade, não é possível eliminá-la.';
$messages['forbiddencharacter'] = 'O nome da pasta contém um caractere inválido';
$messages['selectimportfile'] = 'Por favor seleccione um ficheiro para carregar';
$messages['addresswriterror'] = 'Não é possível escrever no livro de endereços seleccionado';
$messages['contactaddedtogroup'] = 'Contacto adicionado com sucesso a este grupo';
$messages['contactremovedfromgroup'] = 'Contacto removido com sucesso deste grupo';
$messages['importwait'] = 'A importar, por favor aguarde...';
$messages['importerror'] = 'A importação falhou! O ficheiro submetido não é um ficheiro vCard válido.';
$messages['importconfirm'] = '<b>$inserted contactos importados com sucesso, $skipped contactos já existentes ignorados</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = 'Ignoradas $skipped entradas já existentes.';
$messages['opnotpermitted'] = 'Operação não permitida';
$messages['nofromaddress'] = 'Falta o endereço de e-mail na identidade seleccionada';
$messages['editorwarning'] = 'Ao mudar para o editor Plain Text vai perder toda a formação de texto. Deseja continuar?';
$messages['httpreceivedencrypterror'] = 'Ocorreu um erro fatal de configuração. Contacte o seu Administrador imediatamente. <b>A sua mensagem não pôde ser enviada.</b>';
$messages['smtpconnerror'] = 'Erro SMTP ($code): A ligação ao servidor falhou';
$messages['smtpautherror'] = 'Erro SMTP ($code): A autenticação falhou';
$messages['smtpfromerror'] = 'Erro SMTP ($code): Falha ao definir o remetente "$from" ($msg)';
$messages['smtptoerror'] = 'Erro SMTP ($code): Falha ao adicionar o destinatário: "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erro SMTP: Impossível analisar a lista de destinatários';
$messages['smtpdsnerror'] = 'Erro SMTP: Não há suporte para recibo de entrega';
$messages['smtperror'] = 'Erro SMTP: $msg';
$messages['emailformaterror'] = 'Endereço de e-mail incorrecto: $email';
$messages['toomanyrecipients'] = 'Demasiados destinatários. Reduza o numero de destinatários para o máximo de $max.';
$messages['maxgroupmembersreached'] = 'O número de membros do grupo ultrapassa o máximo permitido de $max.';
$messages['internalerror'] = 'Ocorreu um erro interno. Por favor, tente de novamente.';
$messages['contactdelerror'] = 'Não foi possível eliminar os contactos';
$messages['contactdeleted'] = 'Contactos eliminados com sucesso';
$messages['groupdeleted'] = 'Grupo eliminado com sucesso';
$messages['grouprenamed'] = 'Grupo renomeado com sucesso';
$messages['groupcreated'] = 'Grupo criado com sucesso';
$messages['messagedeleted'] = 'Mensagens eliminadas com sucesso';
$messages['messagemoved'] = 'Mensagens movidas com sucesso';
$messages['messagecopied'] = 'Mensagens copiadas com sucesso';
$messages['messagemarked'] = 'Mensagens marcadas com sucesso';
$messages['autocompletechars'] = 'Indique pelo menos $min caracteres para auto completar';
$messages['namecannotbeempty'] = 'O nome não pode estar em branco';
$messages['nametoolong'] = 'O nome é demasiado longo';
$messages['folderupdated'] = 'Actualização da pasta concluída com sucesso';
$messages['foldercreated'] = 'Pasta criada com sucesso';
$messages['invalidimageformat'] = 'Formato de imagem não suportado';

?>
