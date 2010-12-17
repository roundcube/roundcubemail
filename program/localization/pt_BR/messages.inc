<?php

/*

+-----------------------------------------------------------------------+
| language/pt_BR/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Anderson S. Ferreira <anderson@cnpm.embrapa.br>               |
|         Robson F. Ramaldes <rframaldes@gmail.com>                     |
|         Alessandro Lenzi <alessandro@mistymountain.com.br>            |
|         Gervasio Antonio <gdsa32-translations@yahoo.com.br>           |
|         Victor Benincasa <vbenincasa@gmail.com>                       |
|         Jarbas Peixoto Junior <jarbas.peixoto@gmail.com>              |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Falha no login';
$messages['cookiesdisabled'] = 'Seu navegador não suporta cookies';
$messages['sessionerror'] = 'A sessão do seu navegador é inválida ou expirou';
$messages['imaperror'] = 'Falha na conexão com o servidor IMAP';
$messages['servererror'] = 'Erro no Servidor!';
$messages['invalidrequest'] = 'Requisição inválida! Nenhum dado foi salvo.';
$messages['nomessagesfound'] = 'Nenhuma mensagem foi encontrada';
$messages['loggedout'] = 'Sua sessão foi finalizada com sucesso. Até breve!';
$messages['mailboxempty'] = 'A caixa de mensagens está vazia';
$messages['loading'] = 'Carregando...';
$messages['uploading'] = 'Enviando anexo...';
$messages['loadingdata'] = 'Carregando informações...';
$messages['checkingmail'] = 'Verificando se há novas mensagens...';
$messages['sendingmessage'] = 'Enviando mensagem...';
$messages['messagesent'] = 'Mensagem enviada com sucesso';
$messages['savingmessage'] = 'Salvando Mensagem...';
$messages['messagesaved'] = 'Mensagem gravada como rascunho';
$messages['successfullysaved'] = 'Gravado com sucesso';
$messages['addedsuccessfully'] = 'Contato incluído com sucesso';
$messages['contactexists'] = 'Já existe um contato com esse e-mail';
$messages['blockedimages'] = 'Para proteger sua privacidade, as imagens desta mensagem foram bloqueadas';
$messages['encryptedmessage'] = 'Esta mensagem está criptografada e não pode ser exibida. Desculpe.';
$messages['nocontactsfound'] = 'Nenhum contato foi encontrado';
$messages['contactnotfound'] = 'O contato solicitado não foi encontrado';
$messages['sendingfailed'] = 'Falha no envio da mensagem';
$messages['senttooquickly'] = 'Aguarde $sec s para enviar a mensagem';
$messages['errorsavingsent'] = 'Ocorreu um erro ao salvar a mensagem enviada';
$messages['errorsaving'] = 'Ocorreu um erro ao salvar';
$messages['errormoving'] = 'Não foi possível mover a mensagem';
$messages['errorcopying'] = 'Não foi possível copiar a(s) mensagem(ns)';
$messages['errordeleting'] = 'Não foi possível apagar a mensagem';
$messages['errormarking'] = 'Não foi possível marcar a mensagem';
$messages['deletecontactconfirm'] = 'Deseja realmente excluir o(s) contato(s) selecionado(s)?';
$messages['deletemessagesconfirm'] = 'Deseja realmente excluir a(s) mensagem(s) selecionada(s)?';
$messages['deletefolderconfirm'] = 'Deseja realmente excluir esta pasta?';
$messages['purgefolderconfirm'] = 'Deseja realmente excluir todas mensagens desta pasta?';
$messages['folderdeleting'] = 'Apagando pasta...';
$messages['foldermoving'] = 'Movendo pasta...';
$messages['formincomplete'] = 'Os campos não foram completamente preenchidos';
$messages['noemailwarning'] = 'Por favor, informe um endereço de e-mail válido';
$messages['nonamewarning'] = 'Por favor, informe o nome';
$messages['nopagesizewarning'] = 'Por favor, informe o tamanho da página';
$messages['nosenderwarning'] = 'Por favor, informe o e-mail do remetente';
$messages['norecipientwarning'] = 'Por favor, informe pelo menos um destinatário';
$messages['nosubjectwarning'] = 'O campo "Assunto" não foi preenchido. Deseja incluí-lo agora?';
$messages['nobodywarning'] = 'Enviar a mensagem sem texto?';
$messages['notsentwarning'] = 'A mensagem não foi enviada, deseja excluí-la?';
$messages['noldapserver'] = 'Por favor, selecione um servidor LDAP para a pesquisa';
$messages['nocontactsreturned'] = 'Nenhum contato foi encontrado';
$messages['nosearchname'] = 'Por favor, informe o nome do contado ou seu endereço de e-mail';
$messages['notuploadedwarning'] = 'Há anexos ainda não enviados. Aguarde ou cancele o envio.';
$messages['searchsuccessful'] = '$nr mensagens encontradas';
$messages['searchnomatch'] = 'A pesquisa não encontrou resultados';
$messages['searching'] = 'Pesquisando...';
$messages['checking'] = 'Verificando...';
$messages['nospellerrors'] = 'Nenhum erro ortográfico foi encontrado';
$messages['folderdeleted'] = 'Pasta excluída com sucesso';
$messages['deletedsuccessfully'] = 'Excluído com sucesso';
$messages['converting'] = 'Removendo formatação...';
$messages['messageopenerror'] = 'Não foi possível carregar a mensagem do servidor';
$messages['fileuploaderror'] = 'Falha ao enviar o arquivo';
$messages['filesizeerror'] = 'O arquivo enviado excede o tamanho máximo de $size';
$messages['copysuccess'] = '$nr endereço(s) copiado(s) com sucesso';
$messages['copyerror'] = 'Não foi possível copiar os endereços';
$messages['sourceisreadonly'] = 'Esta fonte de endereço é somente leitura';
$messages['errorsavingcontact'] = 'Não foi possível salvar o endereço de contato';
$messages['movingmessage'] = 'Movendo mensagem...';
$messages['copyingmessage'] = 'Copiando mensagem...';
$messages['receiptsent'] = 'Confirmação de recebimento enviada com sucesso';
$messages['errorsendingreceipt'] = 'Não foi possível enviar a confirmação de recebimento';
$messages['nodeletelastidentity'] = 'Você não pode excluir a única identidade';
$messages['forbiddencharacter'] = 'O nome da pasta contém um caractere não permitido';
$messages['selectimportfile'] = 'Selecione o arquivo para envio';
$messages['addresswriterror'] = 'O catálogo de endereços selecionado não tem permissão de modificação';
$messages['contactaddedtogroup'] = 'Adicionado os contatos a este grupo com sucesso';
$messages['contactremovedfromgroup'] = 'Removido contatos deste grupo com sucesso';
$messages['importwait'] = 'Importando, aguarde por favor...';
$messages['importerror'] = 'Falha na importação! O arquivo enviado não é um arquivo vCard válido.';
$messages['importconfirm'] = '<b>Foram importados com sucesso $inserted contatos, e $skipped contatos foram ignorados</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operação não permitida!';
$messages['nofromaddress'] = 'Falta o endereço de email na identidade selecionada';
$messages['editorwarning'] = 'Mudar para o editor de texto simples elimina toda a formatação de texto. Deseja continuar?';
$messages['httpreceivedencrypterror'] = 'Ocorreu um erro de configuração. Informe ao administrador do sistema imediatamente. <b>Sua mensagem não pode ser enviada.</b>';
$messages['smtpconnerror'] = 'Erro SMTP ($code): Conexão ao servidor falhou';
$messages['smtpautherror'] = 'Erro SMTP ($code): Falha na autenticação';
$messages['smtpfromerror'] = 'Erro SMTP ($code): Falha ao definir o remetente "$from" ($msg)';
$messages['smtptoerror'] = 'Erro SMTP ($code): Falha ao adicionar o destinatário "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erro SMTP: Não é possível analisar a lista destinatários';
$messages['smtperror'] = 'Erro SMTP: $msg';
$messages['emailformaterror'] = 'Endereço de Email incorreto: $email';
$messages['toomanyrecipients'] = 'Muitos destinatários. Reduza o número de destinatários para $max.';
$messages['maxgroupmembersreached'] = 'O número de membros do grupo excede o máximo de $max';
$messages['internalerror'] = 'Ocorreu um erro interno. Por favor tente novamente.';

?>
