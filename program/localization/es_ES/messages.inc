<?php

/*

+-----------------------------------------------------------------------+
| language/es_ES/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Javier Smaldone <javier@smaldone.com.ar>                      |
|         http://www.smaldone.com.ar                                    |
|         David Grajal Blanco <dgrabla@gmail.com>                       |
|         http://david.grajal.net                                       |
|         Lito Jornero <jornero@gmail.com>                              |
|         Angel Bueno Prieto <info@mediaiberia.com>                     |
|         http://www.mediaiberia.com                                    |
+-----------------------------------------------------------------------+

@version $Id$
*/

$messages = array();
$messages['loginfailed'] = 'Contraseña o nombre de usuario incorrecto';
$messages['cookiesdisabled'] = 'Su navegador no acepta cookies';
$messages['sessionerror'] = 'Su sesión no existe o ha expirado';
$messages['imaperror'] = 'Error de conexión con el servidor IMAP';
$messages['servererror'] = '¡Error en el servidor!';
$messages['nomessagesfound'] = 'No se han encontrado mensajes en esta casilla';
$messages['loggedout'] = 'Ha cerrado la sesión. ¡Hasta pronto!';
$messages['mailboxempty'] = 'La casilla está vacía';
$messages['loading'] = 'Cargando...';
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
$messages['senttooquickly'] = 'Por favor, espere $sec segundo(s) antes de mandar este mensaje';
$messages['errorsavingsent'] = 'Ha ocurrido un error al guardar el mensaje enviado';
$messages['errorsaving'] = 'Ocurrió un error mientras se guardaba';
$messages['errormoving'] = 'No se ha podido mover el mensaje';
$messages['errordeleting'] = 'No se ha podido eliminar el mensaje';
$messages['errormarking'] = 'No se ha podido marcar el mensaje';
$messages['deletecontactconfirm'] = '¿Realmente quiere eliminar los contactos seleccionados?';
$messages['deletemessagesconfirm'] = '¿Realmente quiere eliminar los mensajes seleccionados?';
$messages['deletefolderconfirm'] = '¿Realmente quiere eliminar esta carpeta?';
$messages['purgefolderconfirm'] = '¿Realmente quiere eliminar todos los mensajes de esta carpeta?';
$messages['foldercreating'] = 'Creando carpeta...';
$messages['folderdeleting'] = 'Borrando carpeta...';
$messages['folderrenaming'] = 'Renombrando carpeta...';
$messages['foldermoving'] = 'Moviendo carpeta...';
$messages['formincomplete'] = 'No ha rellenado todos los campos del formulario';
$messages['noemailwarning'] = 'Por favor, introduzca un e-mail válido';
$messages['nonamewarning'] = 'Por favor, introduzca su nombre';
$messages['nopagesizewarning'] = 'Por favor, introduzca un tamaño de página';
$messages['nosenderwarning'] = 'Por favor, introduzca el e-mail del emisor';
$messages['norecipientwarning'] = 'Por favor, introduzca al menos un destinatario';
$messages['nosubjectwarning'] = 'El campo "Asunto" está vacío. ¿Desea completarlo en este momento?';
$messages['nobodywarning'] = '¿Quiere enviar este mensaje sin texto?';
$messages['notsentwarning'] = 'El mensaje no ha sido enviado. ¿Desea descartar su mensaje?';
$messages['noldapserver'] = 'Por favor, seleccione un servidor LDAP para buscar';
$messages['nocontactsreturned'] = 'No se han encontrado contactos';
$messages['nosearchname'] = 'Por favor, introduzca un nombre o la dirección de e-mail';
$messages['searchsuccessful'] = 'Se encontraron $nr mensajes';
$messages['searchnomatch'] = 'No se obtuvieron resultados';
$messages['searching'] = 'Buscando...';
$messages['checking'] = 'Revisando...';
$messages['nospellerrors'] = 'No se encontraron errores ortográficos';
$messages['folderdeleted'] = 'Carpeta eliminada exitosamente';
$messages['deletedsuccessfully'] = 'Eliminado exitosamente';
$messages['converting'] = 'Removiendo el formato del mensaje...';
$messages['messageopenerror'] = 'No puedo descargar el mensaje del servidor';
$messages['fileuploaderror'] = 'Error al subir archivos';
$messages['filesizeerror'] = 'El archivo excede el tamaño máximo ($size)';
$messages['copysuccess'] = '$nr direcciones copiadas con éxito';
$messages['copyerror'] = 'No se pudo copiar ninguna dirección';
$messages['sourceisreadonly'] = 'Esta dirección es de sólo-lectura';
$messages['errorsavingcontact'] = 'No se pudo guardar la dirección de contacto';
$messages['movingmessage'] = 'Moviendo mensaje...';
$messages['receiptsent'] = 'La notificación de lectura se ha enviado con éxito.';
$messages['errorsendingreceipt'] = 'No se ha podido enviar la notificación de lectura.';
$messages['nodeletelastidentity'] = 'No se puede borrar esta identidad puesto que es la última.';
$messages['addsubfolderhint'] = 'Esta carpeta se creará como una subcarpeta dentro de la carpeta seleccionada';
$messages['forbiddencharacter'] = 'El nombre de la carpeta contiene un carácter prohibido';
$messages['selectimportfile'] = 'Por favor, seleccione un archivo para subir';
$messages['addresswriterror'] = 'No se puede escribir la dirección de contacto seleccionada';
$messages['importwait'] = 'Importando, espere...';
$messages['importerror'] = '¡Error al importar! Este archivo no es un formato válido vCard.';
$messages['importconfirm'] = '<b>Se han insertado: $inserted contactos, y se han omitido: $skipped porque ya existían</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = '¡Operación no permitida!';
$messages['nofromaddress'] = 'El contacto seleccionado no tiene dirección de e-mail';
$messages['editorwarning'] = 'Cambiando a texto plano perderá el formato del mensaje. ¿Desea continuar?';
$messages['httpreceivedencrypterror'] = 'Ha ocurrido un error fatal de configuración. Contacte inmediatamente a su administrador. <b>Su mensaje no ha sido enviado.</b>';
$messages['smtpconnerror'] = 'Error SMTP ($code): Ha fallado la conexión al servidor';
$messages['smtpautherror'] = 'Error SMTP ($code): Error de autenticación';
$messages['smtpfromerror'] = 'Error SMTP ($code): No se ha podido asignar el emisor "$from"';
$messages['smtptoerror'] = 'Error SMTP ($code): No se ha podido establecer remitente "$to"';
$messages['smtprecipientserror'] = 'Error SMTP: No es posible analizar la lista de destinatarios';
$messages['smtperror'] = 'Error SMTP: $msg';

?>
