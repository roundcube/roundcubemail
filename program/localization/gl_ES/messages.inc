<?php

/*
+-----------------------------------------------------------------------+
| language/gl_ES/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author:    David Garabana Barro <david garabana com>                  |
+-----------------------------------------------------------------------+

*/

$messages = array();
$messages['loginfailed'] = 'Fallou o acceso';
$messages['cookiesdisabled'] = 'O seu navegador non acepta galletas';
$messages['sessionerror'] = 'A súa sesión non é válida ou expirou';
$messages['imaperror'] = 'Fallou a conexión co servidor IMAP';
$messages['servererror'] = 'Erro do servidor!';
$messages['servererrormsg'] = 'Erro do servidor: $msg';
$messages['errorreadonly'] = 'Non foi posible realizar a operación. O cartafol é de só lectura';
$messages['errornoperm'] = 'Non foi posible realizar a operación. Permiso denegado.';
$messages['invalidrequest'] = 'Petición inválida!. Non se gardou ningún dato.';
$messages['nomessagesfound'] = 'Non se atoparon mensaxes nesta caixa de correo';
$messages['loggedout'] = 'Rematou correctamente a súa sesión. Ata logo!';
$messages['mailboxempty'] = 'A caixa de correo está vacía';
$messages['loading'] = 'Cargando...';
$messages['uploading'] = 'Cargando ficheiro...';
$messages['loadingdata'] = 'Cargando datos...';
$messages['checkingmail'] = 'Á procura de novas mensaxes...';
$messages['sendingmessage'] = 'Enviando mensaxe...';
$messages['messagesent'] = 'A mensaxe enviouse correctamente';
$messages['savingmessage'] = 'Gardando mensaxe...';
$messages['messagesaved'] = 'A mensaxe gardouse en "Borradores"';
$messages['successfullysaved'] = 'Gardada correctamente';
$messages['addedsuccessfully'] = 'O contacto engadiuse correctamente ao caderno de enderezos';
$messages['contactexists'] = 'Xa existe un contacto con este enderezo de correo electrónico';
$messages['blockedimages'] = 'Estanse a bloquear as imaxes remotas para protexer a súa privacidade';
$messages['encryptedmessage'] = 'Síntoo!. Non se pode amosar a mensaxe porque está cifrada';
$messages['nocontactsfound'] = 'Non se atoparon contactos';
$messages['contactnotfound'] = 'Non se atopou o contacto solicitado';
$messages['sendingfailed'] = 'Fallou o envío da mensaxe';
$messages['senttooquickly'] = 'Por favor, espere $sec segundos antes de enviar esta mensaxe';
$messages['errorsavingsent'] = 'Ocurriu un erro mentres se gardaba a mensaxe enviada';
$messages['errorsaving'] = 'Ocurriu un erro mentres se gardaba';
$messages['errormoving'] = 'Non foi posible mover a(s) mensaxe(s)';
$messages['errorcopying'] = 'Non foi posible copiar a(s) mensaxe(s)';
$messages['errordeleting'] = 'Non foi posible eliminar a(s) mensaxe(s)';
$messages['errormarking'] = 'Non foi posible marcar a(s) mensaxe(s)';
$messages['deletecontactconfirm'] = 'Quere eliminar o(s) contacto(s) seleccionado(s)?';
$messages['deletemessagesconfirm'] = 'Quere eliminar a(s) mensaxe(s) seleccionadas?';
$messages['deletefolderconfirm'] = 'Quere eliminar este cartafol?';
$messages['purgefolderconfirm'] = 'Quere eliminar tódalas mensaxes neste cartafol?';
$messages['folderdeleting'] = 'Eliminando o cartafol...';
$messages['foldermoving'] = 'Movendo o cartafol...';
$messages['foldersubscribing'] = 'Subscribindo o cartafol...';
$messages['folderunsubscribing'] = 'Desubscribindo o cartafol...';
$messages['formincomplete'] = 'Non se cumprimentou completamente o formulario';
$messages['noemailwarning'] = 'Por favor, introduza un enderezo de correo electrónico válido';
$messages['nonamewarning'] = 'Por favor, introduza un nome';
$messages['nopagesizewarning'] = 'Por favor, introduza un tamaño de páxina';
$messages['nosenderwarning'] = 'Por favor, introduza o enderezo de correo electrónico do remitente';
$messages['norecipientwarning'] = 'Por favor, introduza polo menos un destinatario';
$messages['nosubjectwarning'] = 'O campo "Asunto" está baleiro. Quere introducir un?';
$messages['nobodywarning'] = 'Quere enviar esta mensaxe sen texto?';
$messages['notsentwarning'] = 'A mensaxe non se enviou. Quere descartala?';
$messages['noldapserver'] = 'Por favor, elixa un servidor LDAP para buscar';
$messages['nocontactsreturned'] = 'Non se atoparon contactos';
$messages['nosearchname'] = 'Por favor, introduza un contacto ou un enderezo de correo electrónico';
$messages['notuploadedwarning'] = 'Aínda non se cargaron tódolos ficheiros. Por favor, agarde ou cancele a carga.';
$messages['searchsuccessful'] = 'Atopáronse $nr mensaxes';
$messages['searchnomatch'] = 'A busca non atopou coincidencias';
$messages['searching'] = 'Buscando...';
$messages['checking'] = 'Comprobando...';
$messages['nospellerrors'] = 'Non hai erros ortográficos';
$messages['folderdeleted'] = 'O cartafol eliminouse correctamente';
$messages['foldersubscribed'] = 'O cartafol suscribiuse correctamente';
$messages['folderunsubscribed'] = 'O cartafol desuscribiuse correctamente';
$messages['folderpurged'] = 'O cartafol purgouse correctamente';
$messages['folderexpunged'] = 'O cartafol baleirouse correctamente';
$messages['deletedsuccessfully'] = 'Eliminouse correctamente';
$messages['converting'] = 'Eliminando o formato da mensaxe...';
$messages['messageopenerror'] = 'Non foi posible cargar a mensaxe desde o servidor';
$messages['fileuploaderror'] = 'Fallou a carga do ficheiro';
$messages['filesizeerror'] = 'O ficheiro cargado é máis grande que o tamaño máximo de $size';
$messages['copysuccess'] = 'Copiáronse correctamente $nr enderezos';
$messages['copyerror'] = 'Non foi posible copiar ningún enderezo';
$messages['sourceisreadonly'] = 'A orixe é de só lectura';
$messages['errorsavingcontact'] = 'Non foi posible gardar o contacto';
$messages['movingmessage'] = 'Movendo a(s) mensaxe(s)...';
$messages['copyingmessage'] = 'Copiando a(s) mensaxe(s)...';
$messages['deletingmessage'] = 'Eliminando a(s) mensaxe(s)...';
$messages['markingmessage'] = 'Marcando a(s) mensaxe(s)...';
$messages['receiptsent'] = 'A notificación da entrega enviouse correctamente';
$messages['errorsendingreceipt'] = 'Non foi posible enviar a notificación da entrega';
$messages['nodeletelastidentity'] = 'Non pode eliminar esta identidade, é a última';
$messages['forbiddencharacter'] = 'O nome do cartafol contén un carácter non válido';
$messages['selectimportfile'] = 'Por favor, escolla un ficheiro para cargar';
$messages['addresswriterror'] = 'Non se pode escribir no caderno de enderezos que escolleu';
$messages['contactaddedtogroup'] = 'Engadíronse correctamente os contactos a este grupo';
$messages['contactremovedfromgroup'] = 'Suprimíronse correctamente os contactos de este grupo';
$messages['importwait'] = 'Estou a importar. Por favor, agarde...';
$messages['importerror'] = 'Fallou a importación! O ficheiro que cargou non é un vCard correcto.';
$messages['importconfirm'] = '<b>Importáronse correctamente $inserted contactos. Ignoráronse $skipped contactos que xa existían</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operación non permitida';
$messages['nofromaddress'] = 'Falta o enderezo de correo electrónico na identidade que escolleu';
$messages['editorwarning'] = 'Se troca neste intre ao editor de texto plano, vai perder todo o formato do texto. Quere continuar?';
$messages['httpreceivedencrypterror'] = 'Produciuse un erro fatal de configuración. Contacte ao administrador inmediatamente. <b>Non foi posible enviar a súa mensaxe.</b>';
$messages['smtpconnerror'] = 'Erro SMTP ($code): Fallou a conexión co servidor';
$messages['smtpautherror'] = 'Erro SMTP ($code): Fallou a autenticación';
$messages['smtpfromerror'] = 'Erro SMTP ($code): Non foi posible establecer o remitente "$from" ($msg)';
$messages['smtptoerror'] = 'Erro SMTP ($code): Non foi posible engadir o destinatario "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erro SMTP: Non se pode analizar a lista de destinatarios';
$messages['smtpdsnerror'] = 'Erro SMTP: Non hai soporte para notificacións do estado de envío (DSN)';
$messages['smtperror'] = 'Erro SMTP: $msg';
$messages['emailformaterror'] = 'O enderezo de correo electrónico é incorrecto: $email';
$messages['toomanyrecipients'] = 'Especificou destinatarios de máis. Por favor, redúzaos a un máximo de $max.';
$messages['maxgroupmembersreached'] = 'O número de membros do grupo excede o máximo de $max.';
$messages['internalerror'] = 'Ocurriu un erro interno. Por favor, probe outra vez.';
$messages['contactdelerror'] = 'Non foi posible borrar o(s) contacto(s)';
$messages['contactdeleted'] = 'Borráronse correctamente o(s) contacto(s)';
$messages['groupdeleted'] = 'Borrouse correctamente o grupo';
$messages['grouprenamed'] = 'Mudouse correctamente o nome do grupo';
$messages['groupcreated'] = 'Creouse correctamente o grupo';
$messages['messagedeleted'] = 'Borráronse correctamente a(s) mensaxe(s)';
$messages['messagemoved'] = 'Movéronse correctamente a(s) mensaxe(s)';
$messages['messagecopied'] = 'Copiáronse correctamente a(s) mensaxe(s)';
$messages['messagemarked'] = 'Marcáronse correctamente a(s) mensaxe(s)';
$messages['autocompletechars'] = 'Introduza polo menos $min caracteres para autocompletar';
$messages['namecannotbeempty'] = 'O nome non pode estar baleiro';
$messages['nametoolong'] = 'O nome é longo de máis';
$messages['folderupdated'] = 'O cartafol actualizouse correctamente';
$messages['foldercreated'] = 'O cartafol creouse correctamente';

?>
