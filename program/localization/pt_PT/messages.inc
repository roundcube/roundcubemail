<?php

/*
 +-----------------------------------------------------------------------+
 | localization/pt_PT/messages.inc                                       |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: David                                                         |
 +-----------------------------------------------------------------------+
*/

$messages = array();
$messages['errortitle'] = 'Ocorreu um erro!';
$messages['loginfailed'] = 'O utilizador ou palavra-passe que introduziu não correspondem.';
$messages['cookiesdisabled'] = 'O seu navegador não suporta cookies';
$messages['sessionerror'] = 'A sua sessão é inválida ou expirou.';
$messages['storageerror'] = 'A ligação ao servidor IMAP falhou';
$messages['servererror'] = 'Erro do Servidor!';
$messages['servererrormsg'] = 'Erro do Servidor: $msg';
$messages['dberror'] = 'Erro na base de dados!';
$messages['requesttimedout'] = 'A solicitação expirou';
$messages['errorreadonly'] = 'Não foi possível efectuar a operação. A pasta é só de leitura.';
$messages['errornoperm'] = 'Não tem permissões para efectuar esta operação.';
$messages['invalidrequest'] = 'Pedido inválido! Não foi guardada nenhuma informação.';
$messages['nomessagesfound'] = 'Não existem mensagens nesta pasta.';
$messages['loggedout'] = 'A sua sessão foi encerrada com sucesso. Até breve!';
$messages['mailboxempty'] = 'A caixa de entrada está vazia';
$messages['loading'] = 'A carregar...';
$messages['uploading'] = 'A enviar ficheiro...';
$messages['uploadingmany'] = 'A enviar ficheiros...';
$messages['loadingdata'] = 'A carregar dados...';
$messages['checkingmail'] = 'A verificar existência de novas mensagens...';
$messages['sendingmessage'] = 'A enviar mensagem...';
$messages['messagesent'] = 'Mensagem enviada com sucesso';
$messages['savingmessage'] = 'A guardar mensagem...';
$messages['messagesaved'] = 'Mensagem guardada como rascunho';
$messages['successfullysaved'] = 'Guardado com sucesso';
$messages['addedsuccessfully'] = 'Contacto adicionado com sucesso';
$messages['contactexists'] = 'Já existe um contacto com este e-mail';
$messages['contactnameexists'] = 'Já existe um contacto com este nome.';
$messages['blockedimages'] = 'Para proteger a sua privacidade, as imagens desta mensagem foram bloqueadas';
$messages['encryptedmessage'] = 'Esta mensagem está encriptada e não pode ser apresentada. Desculpe!';
$messages['nocontactsfound'] = 'Nenhum contacto encontrado';
$messages['contactnotfound'] = 'O contacto especificado não foi encontrado';
$messages['contactsearchonly'] = 'Insira os termos de pesquisa para localizar os contactos';
$messages['sendingfailed'] = 'Falha no envio da mensagem';
$messages['senttooquickly'] = 'Por favor espere $sec seg(s) antes de enviar esta mensagem';
$messages['errorsavingsent'] = 'Ocorreu um erro ao guardar a mensagem enviada';
$messages['errorsaving'] = 'Ocorreu um erro ao guardar';
$messages['errormoving'] = 'Não foi possível mover a mensagem';
$messages['errorcopying'] = 'Não foi possível copiar a mensagem';
$messages['errordeleting'] = 'Não foi possível eliminar a mensagem';
$messages['errormarking'] = 'Não foi possível marcar a mensagem.';
$messages['deletecontactconfirm'] = 'Deseja realmente eliminar o(s) contacto(s) seleccionado(s)?';
$messages['deletegroupconfirm'] = 'Tem a certeza que pretende eliminar o grupo seleccionado?';
$messages['deletemessagesconfirm'] = 'Deseja realmente eliminar a(s) mensagem(ns) seleccionada(s)?';
$messages['deletefolderconfirm'] = 'Deseja realmente eliminar esta pasta?';
$messages['purgefolderconfirm'] = 'Deseja realmente eliminar todas mensagens desta pasta?';
$messages['contactdeleting'] = 'A eliminar contacto(s)...';
$messages['groupdeleting'] = 'A eliminar grupo...';
$messages['folderdeleting'] = 'A eliminar pasta...';
$messages['foldermoving'] = 'A mover pasta...';
$messages['foldersubscribing'] = 'A subscrever pasta...';
$messages['folderunsubscribing'] = 'A cancelar subscrição da pasta...';
$messages['formincomplete'] = 'Os campos não foram completamente preenchidos';
$messages['noemailwarning'] = 'Por favor, introduza um endereço de e-mail válido.';
$messages['nonamewarning'] = 'Por favor, escreva um nome.';
$messages['nopagesizewarning'] = 'Por favor, escreva o tamanho da página';
$messages['nosenderwarning'] = 'Por favor, introsuza o endereço de e-mail do remetente';
$messages['norecipientwarning'] = 'Por favor, insira pelo menos um destinatário';
$messages['nosubjectwarning'] = 'O campo "Assunto" não foi preenchido. Deseja incluí-lo agora?';
$messages['nobodywarning'] = 'Enviar a mensagem sem texto?';
$messages['notsentwarning'] = 'A mensagem não foi enviada, deseja apagá-la?';
$messages['noldapserver'] = 'Seleccione um servidor LDAP para efectuar a pesquisa';
$messages['nosearchname'] = 'Por favor, insira o nome do contacto ou endereço de e-mail';
$messages['notuploadedwarning'] = 'Nem todos os anexos foram ainda enviados. Por favor aguarde ou cancele o envio.';
$messages['searchsuccessful'] = '$nr mensagens encontradas';
$messages['contactsearchsuccessful'] = '$nr contactos encontrados.';
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
$messages['messageopenerror'] = 'Não foi possível carregar a mensagem.';
$messages['fileuploaderror'] = 'Falha ao carregar o ficheiro';
$messages['filesizeerror'] = 'O ficheiro excede o tamanho máximo permitido de $size';
$messages['copysuccess'] = 'Foram copiados $nr endereços com sucesso';
$messages['copyerror'] = 'Não foi possível copiar os endereços';
$messages['sourceisreadonly'] = 'Esta origem de endereços é só de leitura';
$messages['errorsavingcontact'] = 'Não foi possível guardar o endereço deste contacto';
$messages['movingmessage'] = 'A mover mensagem(ns)...';
$messages['copyingmessage'] = 'A copiar mensagem(ns)...';
$messages['copyingcontact'] = 'A copiar contacto(s)...';
$messages['deletingmessage'] = 'A eliminar mensagem(ns)...';
$messages['markingmessage'] = 'A marcar mensagem(ns)...';
$messages['addingmember'] = 'A adicionar contacto(s) ao grupo...';
$messages['removingmember'] = 'A remover contacto(s) do grupo...';
$messages['receiptsent'] = 'Recibo de leitura enviado com sucesso';
$messages['errorsendingreceipt'] = 'Não foi possível enviar o recibo';
$messages['deleteidentityconfirm'] = 'Pretende realmente eliminar esta identidade?';
$messages['nodeletelastidentity'] = 'Esta é a sua última identidade, não é possível eliminá-la.';
$messages['forbiddencharacter'] = 'O nome da pasta contém um caractere inválido';
$messages['selectimportfile'] = 'Por favor, seleccione um ficheiro para enviar.';
$messages['addresswriterror'] = 'Não é possível escrever no livro de endereços seleccionado.';
$messages['contactaddedtogroup'] = 'Contacto adicionado com sucesso a este grupo.';
$messages['contactremovedfromgroup'] = 'Contacto removido com sucesso deste grupo.';
$messages['importwait'] = 'A importar, por favor aguarde...';
$messages['importerror'] = 'A importação falhou! O ficheiro submetido não é um ficheiro vCard válido.';
$messages['importconfirm'] = '<b>$inserted contactos importados com sucesso, $skipped contactos já existentes foram ignorados</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = 'Ignoradas $skipped entradas já existentes.';
$messages['opnotpermitted'] = 'Operação não permitida';
$messages['nofromaddress'] = 'Falta o endereço de e-mail na identidade seleccionada';
$messages['editorwarning'] = 'Ao mudar para o editor Plain Text vai perder toda a formação de texto. Deseja continuar?';
$messages['httpreceivedencrypterror'] = 'Ocorreu um erro fatal de configuração. Contacte o seu Administrador imediatamente. <b>A sua mensagem não pôde ser enviada.</b>';
$messages['smtpconnerror'] = 'Erro SMTP ($code): A ligação ao servidor falhou.';
$messages['smtpautherror'] = 'Erro SMTP ($code): A autenticação falhou.';
$messages['smtpfromerror'] = 'Erro SMTP ($code): Falha ao definir o remetente "$from" ($msg).';
$messages['smtptoerror'] = 'Erro SMTP ($code): Falha ao adicionar o destinatário: "$to" ($msg).';
$messages['smtprecipientserror'] = 'Erro SMTP: Impossível analisar a lista de destinatários.';
$messages['smtperror'] = 'Erro SMTP: $msg';
$messages['emailformaterror'] = 'Endereço de e-mail incorrecto: $email';
$messages['toomanyrecipients'] = 'Demasiados destinatários. Reduza o numero de destinatários para o máximo de $max.';
$messages['maxgroupmembersreached'] = 'O número de membros do grupo ultrapassa o máximo permitido de $max.';
$messages['internalerror'] = 'Ocorreu um erro interno. Por favor, tente novamente.';
$messages['contactdelerror'] = 'Não foi possível eliminar o(s) contacto(s).';
$messages['contactdeleted'] = 'Contacto(s) eliminado(s) com sucesso.';
$messages['contactrestoreerror'] = 'Não foi possível restaurar o(s) contacto(s) eliminados.';
$messages['contactrestored'] = 'Contacto(s) restaurado(s) com sucesso.';
$messages['groupdeleted'] = 'Grupo eliminado com sucesso.';
$messages['grouprenamed'] = 'Grupo renomeado com sucesso.';
$messages['groupcreated'] = 'Grupo criado com sucesso.';
$messages['savedsearchdeleted'] = 'A pesquisa guardada foi eliminada com sucesso.';
$messages['savedsearchdeleteerror'] = 'Não foi possível eliminar a pesquisa guardada.';
$messages['savedsearchcreated'] = 'Pesquisa guardada criada com sucesso.';
$messages['savedsearchcreateerror'] = 'Não foi possível criar a pesquisa guardada.';
$messages['messagedeleted'] = 'Mensagem(ns) eliminada(s) com sucesso.';
$messages['messagemoved'] = 'Mensagem(ns) movida(s) com sucesso.';
$messages['messagecopied'] = 'Mensagem(ns) copiada(s) com sucesso.';
$messages['messagemarked'] = 'Mensage(m)ns marcada(s) com sucesso.';
$messages['autocompletechars'] = 'Indique pelo menos $min caracteres para o auto preenchimento.';
$messages['autocompletemore'] = 'Demasiados registos encontrados. Por favor, insira mais caracteres.';
$messages['namecannotbeempty'] = 'O nome não pode estar em branco.';
$messages['nametoolong'] = 'O nome é demasiado longo.';
$messages['folderupdated'] = 'Actualização da pasta concluída com sucesso.';
$messages['foldercreated'] = 'Pasta criada com sucesso.';
$messages['invalidimageformat'] = 'O formato da imagem não é suportado.';
$messages['mispellingsfound'] = 'Foram detectados erros de ortografia nesta mensagem.';
$messages['parentnotwritable'] = 'Não é possível criar/mover esta pasta para a pasta seleccionada. O acesso foi negado.';
$messages['messagetoobig'] = 'A mensagem é demasiado grande para ser processada.';

