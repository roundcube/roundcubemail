<?php

/*

+-----------------------------------------------------------------------+
| language/pt_PT/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Authors: Sergio Rocha <sergio.rocha@makeitsimple.pt>                  |
|          João Vale <jpvale@gmail.com>                                 |
|         Nuno Costa <nuno@criacaoweb.net>                              |
|         Teotónio Ricardo <teotonio.ricardo@webtuga.pt>                |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Erro ao entrar';
$messages['cookiesdisabled'] = 'O seu navegador não suporta cookies';
$messages['sessionerror'] = 'A sessão do seu navegador é inválida ou expirou';
$messages['imaperror'] = 'A ligação ao servidor IMAP falhou';
$messages['servererror'] = 'Erro do Servidor!';
$messages['invalidrequest'] = 'Pedido inválido! Nenhuma informação foi salva.';
$messages['nomessagesfound'] = 'Nenhuma mensagem encontrada na caixa de entrada';
$messages['loggedout'] = 'A sua sessão foi finalizada com sucesso. Até breve!';
$messages['mailboxempty'] = 'A caixa de entrada está vazia';
$messages['loading'] = 'A carregar...';
$messages['uploading'] = 'Enviando ficheiro...';
$messages['loadingdata'] = 'A carregar informações...';
$messages['checkingmail'] = 'Verificando novas mensagens...';
$messages['sendingmessage'] = 'Enviando mensagem...';
$messages['messagesent'] = 'Mensagem enviada com sucesso';
$messages['savingmessage'] = 'A gravar a mensagem...';
$messages['messagesaved'] = 'Mensagem gravada como rascunho';
$messages['successfullysaved'] = 'Gravado com sucesso';
$messages['addedsuccessfully'] = 'Contacto adicionado com sucesso';
$messages['contactexists'] = 'Já existe um contacto com esse e-mail';
$messages['blockedimages'] = 'Para proteger sua privacidade, as imagens desta mensagem foram bloqueadas';
$messages['encryptedmessage'] = 'Esta mensagem está encriptada e não pode ser exibida. Desculpe.';
$messages['nocontactsfound'] = 'Nenhum contacto encontrado';
$messages['contactnotfound'] = 'O contacto especificado não foi encontrado';
$messages['sendingfailed'] = 'Falha no envio da mensagem';
$messages['senttooquickly'] = 'Por favor espere $sec seg(s). antes de enviar esta mensagem';
$messages['errorsavingsent'] = 'Ocorreu um erro ao salvar mensagem enviada';
$messages['errorsaving'] = 'Ocorreu um erro a gravar';
$messages['errormoving'] = 'Não foi possível mover a mensagem';
$messages['errordeleting'] = 'Não foi possível apagar a mensagem';
$messages['errormarking'] = 'Não foi possível marcar a mensagem.';
$messages['deletecontactconfirm'] = 'Deseja realmente apagar o(s) contacto(s) selecionado(s)?';
$messages['deletemessagesconfirm'] = 'Deseja realmente apagar a(s) mensagem(ns) selecionada(s)?';
$messages['deletefolderconfirm'] = 'Deseja realmente apagar esta pasta?';
$messages['purgefolderconfirm'] = 'Deseja realmente apagar todas mensagens desta pasta?';
$messages['foldercreating'] = 'A criar pasta...';
$messages['folderdeleting'] = 'A apagar pasta...';
$messages['folderrenaming'] = 'A renomear pasta...';
$messages['foldermoving'] = 'A mover pasta...';
$messages['formincomplete'] = 'Os campos não foram completamente preenchidos';
$messages['noemailwarning'] = 'Por favor escreva um endereço de e-mail válido';
$messages['nonamewarning'] = 'Por favor escreva o nome';
$messages['nopagesizewarning'] = 'Por favor escreva o tamanho da página';
$messages['nosenderwarning'] = 'Por favor insira endereço de email do remetente';
$messages['norecipientwarning'] = 'Por favor escreva pelo menos um destinatário';
$messages['nosubjectwarning'] = 'O campo "Assunto" não foi preenchido. Deseja incluí-lo agora?';
$messages['nobodywarning'] = 'Enviar a mensagem sem texto?';
$messages['notsentwarning'] = 'A mensagem não foi enviada, deseja apagá-la?';
$messages['noldapserver'] = 'Selecione um servidor LDAP para a pesquisa';
$messages['nocontactsreturned'] = 'Nenhum contacto foi encontrado';
$messages['nosearchname'] = 'Por favor, escreva o nome do contacto ou seu endereço de e-mail';
$messages['notuploadedwarning'] = 'Nem todos os anexos foram já enviados. Por favor espere ou cancele o envio.';
$messages['searchsuccessful'] = '$nr mensagens encontradas';
$messages['searchnomatch'] = 'Pesquisa não devolveu resultados';
$messages['searching'] = 'Pesquisando...';
$messages['checking'] = 'A verificar...';
$messages['nospellerrors'] = 'Nenhum erro ortográfico foi encontrado';
$messages['folderdeleted'] = 'Pasta apagada com sucesso';
$messages['deletedsuccessfully'] = 'Apagado com sucesso';
$messages['converting'] = 'Removendo formatação da mensagem...';
$messages['messageopenerror'] = 'Não foi possível carregar mensagem';
$messages['fileuploaderror'] = 'Falha ao puxar o ficheiro';
$messages['filesizeerror'] = 'O ficheiro excede o tamanho máximo de $size';
$messages['copysuccess'] = 'Foram copiados $nr endereços com sucesso';
$messages['copyerror'] = 'Não foi possível copiar os endereços';
$messages['sourceisreadonly'] = 'Esta fonte de endereços é só de leitura';
$messages['errorsavingcontact'] = 'Não foi possível guardar o endereço de contacto';
$messages['movingmessage'] = 'Movendo mensagem...';
$messages['receiptsent'] = 'Recibo de leitura enviado';
$messages['errorsendingreceipt'] = 'Não foi possível enviar o recibo';
$messages['nodeletelastidentity'] = 'Esta é a última identidade, não é possível apagá-la.';
$messages['addsubfolderhint'] = 'Esta pasta será criada como uma sub-pasta da que está seleccionada';
$messages['forbiddencharacter'] = 'O nome da pasta contém um caractere proibido';
$messages['selectimportfile'] = 'Por favor selecione um ficheiro para submeter';
$messages['addresswriterror'] = 'Não é possível escrever no livro de endereços selecionado';
$messages['importwait'] = 'A importar, por favor agurade...';
$messages['importerror'] = 'Falhou importação! O ficheiro submetido não é um válido ficheiro vCard.';
$messages['importconfirm'] = '<b>$inserted contactos importados com sucesso, $skipped contactos já existentes ignorados</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operação não permitida';
$messages['nofromaddress'] = 'Falta endereço de email na identidade selecionada';
$messages['editorwarning'] = 'Ao mudar para o editor plain text vai perder toda a formação de texto. Deseja continuar?';
$messages['httpreceivedencrypterror'] = 'Ocorreu um erro fatal de configuração. Contacte o seu administrador imediatamente. <b>A sua mensagem não pôde ser enviada.</b>';
$messages['smtpconnerror'] = 'Erro SMTP ($code): Ligação ao servidor falhou';
$messages['smtpautherror'] = 'Erro SMTP ($code): Autenticação falhou';
$messages['smtpfromerror'] = 'Erro SMTP ($code): Falhou a definir remetente "$from"';
$messages['smtptoerror'] = 'Erro SMTP ($code): Falhou ao adicionar destinatário: "$to"';
$messages['smtprecipientserror'] = 'Erro SMTP: Impossível analisar lista de destinatários';
$messages['smtperror'] = 'Erro SMTP: $msg';
$messages['emailformaterror'] = 'Endereço de e-mail incorrecto: $email';

?>
