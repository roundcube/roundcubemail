<?php

/*
 +-----------------------------------------------------------------------+
 | localization/es_AR/messages.inc                                       |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas <Unknown>                                              |
 +-----------------------------------------------------------------------+
 @version $Id$
*/

$messages = array();
$messages['loginfailed'] = 'Contraseña o nombre de usuario incorrecto';
$messages['cookiesdisabled'] = 'Su navegador no acepta cookies';
$messages['sessionerror'] = 'Su sesión no existe o ha expirado';
$messages['imaperror'] = 'Error de conexión con el servidor IMAP';
$messages['servererror'] = '¡Error en el servidor!';
$messages['invalidrequest'] = 'Peteción invalida! No se guardó nada.';
$messages['nomessagesfound'] = 'No se han encontrado mensajes en esta casilla';
$messages['loggedout'] = 'Ha cerrado la sesión. ¡Hasta pronto!';
$messages['mailboxempty'] = 'La casilla está vacía';
$messages['loading'] = 'Cargando...';
$messages['uploading'] = 'Subiendo archivo...';
$messages['loadingdata'] = 'Cargando datos...';
$messages['checkingmail'] = 'Verificando si hay nuevos mensajes...';
$messages['sendingmessage'] = 'Enviando mensaje...';
$messages['messagesent'] = 'Mensaje enviado correctamente';
$messages['savingmessage'] = 'Guardar mensaje...';
$messages['messagesaved'] = 'Mensaje guardado en borradores';
$messages['successfullysaved'] = 'Guardado correctamente';
$messages['addedsuccessfully'] = 'Contacto añadido correctamente a la libreta de direcciones';
$messages['contactexists'] = 'Ya existe un contacto con esta dirección de correo';
$messages['blockedimages'] = 'Para proteger su privacidad, las imágenes externas han sido bloqueadas en este mensaje';
$messages['encryptedmessage'] = 'Este es un mensaje cifrado y no puede ser mostrado. ¡Lo siento!';
$messages['nocontactsfound'] = 'No hay contactos';
$messages['contactnotfound'] = 'El contacto solicitado no existe';
$messages['sendingfailed'] = 'Error al enviar mensaje';
$messages['senttooquickly'] = 'Por favor, espere $sec segundo(s) antes de enviar este mensaje';
$messages['errorsavingsent'] = 'Ocurrió un error al guardar el mensaje enviado';
$messages['errorsaving'] = 'Ocurrió un error al guardar';
$messages['errormoving'] = 'No se pudo mover el mensaje';
$messages['errorcopying'] = 'No se pudo copiar el mensaje';
$messages['errordeleting'] = 'No se ha podido eliminar el mensaje';
$messages['errormarking'] = 'No se puede marcar el mensaje.';
$messages['deletecontactconfirm'] = '¿Realmente quiere eliminar los contactos seleccionados?';
$messages['deletemessagesconfirm'] = '¿Realmente quiere eliminar los mensajes seleccionados?';
$messages['deletefolderconfirm'] = '¿Realmente quiere eliminar esta carpeta?';
$messages['purgefolderconfirm'] = '¿Realmente quiere eliminar todos los mensajes de esta carpeta?';
$messages['folderdeleting'] = 'Eliminando carpeta...';
$messages['foldermoving'] = 'Moviendo carpeta...';
$messages['formincomplete'] = 'No se han llenado todos los campos del formulario';
$messages['noemailwarning'] = 'Por favor, introduzca un e-mail válido';
$messages['nonamewarning'] = 'Por favor, introduzca su nombre';
$messages['nopagesizewarning'] = 'Por favor, introduzca un tamaño de página';
$messages['nosenderwarning'] = 'Por favor, introduzca el e-mail del emisor';
$messages['norecipientwarning'] = 'Por favor, introduzca al menos un destinatario';
$messages['nosubjectwarning'] = 'El campo "Asunto" esta vacío. ¿Desea completarlo en este momento?';
$messages['nobodywarning'] = '¿Quiere enviar este mensaje sin texto?';
$messages['notsentwarning'] = 'El mensaje no ha sido enviado. ¿Desea descartar su mensaje?';
$messages['noldapserver'] = 'Por favor, seleccione un servidor LDAP para buscar';
$messages['nosearchname'] = 'Por favor, introduzca un nombre o la dirección de e-mail';
$messages['notuploadedwarning'] = 'Todavia no se subieron todos los archivos adjuntos. Por favor aguarde o cancele la subida.';
$messages['searchsuccessful'] = 'Se encontraron $nr mensajes';
$messages['searchnomatch'] = 'La búsqueda no obtuvo resultados';
$messages['searching'] = 'Buscando...';
$messages['checking'] = 'Revisando...';
$messages['nospellerrors'] = 'No se encontraron errores ortográficos';
$messages['folderdeleted'] = 'Carpeta eliminada exitosamente';
$messages['deletedsuccessfully'] = 'Eliminado exitosamente';
$messages['converting'] = 'Removiendo el formato del mensaje...';
$messages['messageopenerror'] = 'No puedo descargar el mensaje del servidor';
$messages['fileuploaderror'] = 'Error al subir archivos';
$messages['filesizeerror'] = 'El archivo excede el tamaño maximo ($size)';
$messages['copysuccess'] = '$nr direcciones copiadas con éxito';
$messages['copyerror'] = 'No se pudo copiar ninguna dirección';
$messages['sourceisreadonly'] = 'Esta dirección es de sólo-lectura';
$messages['errorsavingcontact'] = 'No se pudo guardar la dirección de contacto';
$messages['movingmessage'] = 'Moviendo mensaje...';
$messages['copyingmessage'] = 'Copiando mensaje...';
$messages['receiptsent'] = 'La notificación de lectura se ha enviado con éxito.';
$messages['errorsendingreceipt'] = 'No se ha podido enviar la notificación de lectura.';
$messages['nodeletelastidentity'] = 'No se puede borrar esta identidad puesto que es la última.';
$messages['forbiddencharacter'] = 'El nombre de la carpeta contiene un carácter prohibido';
$messages['selectimportfile'] = 'Por favor, seleccione el archivo a subir';
$messages['addresswriterror'] = 'La libreta de direcciones seleccionada es de solo-lectura';
$messages['contactaddedtogroup'] = 'Contactos agregados al grupo correctamente';
$messages['contactremovedfromgroup'] = 'Contactos eliminados del grupo correctamente';
$messages['importwait'] = 'Importando, aguarde por favor...';
$messages['importerror'] = 'Falló la importación! El archivo seleccionado parece no ser un archivo del tipo vCard válido.';
$messages['importconfirm'] = '<b>Se importaron $inserted contactos correctamente. $skipped ya existentes fueron ignorados</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operación no permitida!';
$messages['nofromaddress'] = 'El contacto seleccionado no tiene dirección de e-mail';
$messages['editorwarning'] = 'Si cambia a texto plano se perderán todas las opciones de formato. ¿Desea continuar?';
$messages['httpreceivedencrypterror'] = 'Ha ocurrido un error fatal de configuración. Contacte inmediatamente a su administrador. <b>Su mensaje no ha sido enviado.</b>';
$messages['smtpconnerror'] = 'Error SMTP ($code): Conexión al servidor falló';
$messages['smtpautherror'] = 'Error SMTP ($code): Falló la autentificación';
$messages['smtpfromerror'] = 'Error SMTP ($code): No se pudo establecer el remitente "$from" ($msg)';
$messages['smtptoerror'] = 'Error SMTP ($code): No se puedo agregar el destinatario "$to" ($msg)';
$messages['smtprecipientserror'] = 'Error SMTP: No se pudo leer la lista de destinatarios';
$messages['smtperror'] = 'Error SMTP: $msg';
$messages['emailformaterror'] = 'La dirección $email no es válida';
$messages['toomanyrecipients'] = 'Demasiados destinatarios. Reduzca la cantidad a un máximo de $max.';
$messages['maxgroupmembersreached'] = 'El número de miembros del grupo excede el máximo de $max';

