<?php

/*

+-----------------------------------------------------------------------+
| language/es_ES/labels.inc                                             |
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
|         Angel Bueno Prieto <info@mediaiberia.com>                     |
|         http://www.mediaiberia.com                                    |
+-----------------------------------------------------------------------+

@version $Id$

*/

$labels = array();
// login page
$labels['welcome'] = 'Bienvenido a $product';
$labels['username'] = 'Nombre de usuario';
$labels['password'] = 'Contraseña';
$labels['server'] = 'Servidor';
$labels['login'] = 'Entrar';

// taskbar
$labels['logout'] = 'Cerrar sesión';
$labels['mail'] = 'Correo';
$labels['settings'] = 'Configuración';
$labels['addressbook'] = 'Contactos';

// mailbox names
$labels['inbox'] = 'Entrada';
$labels['drafts'] = 'Borradores';
$labels['sent'] = 'Enviados';
$labels['trash'] = 'Papelera';
$labels['junk'] = 'Basura';

// message listing
$labels['subject'] = 'Asunto';
$labels['from'] = 'Remitente';
$labels['to'] = 'Destinatario';
$labels['cc'] = 'Copia';
$labels['bcc'] = 'Bcc';
$labels['replyto'] = 'Respuesta a';
$labels['date'] = 'Fecha';
$labels['size'] = 'Tamaño';
$labels['priority'] = 'Prioridad';
$labels['organization'] = 'Organización';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Bandejas';
$labels['messagesfromto'] = 'Mensajes $from a $to de $count';
$labels['messagenrof'] = 'Mensaje $nr de $count';

$labels['moveto'] = 'Mover a...';
$labels['download'] = 'Descargar';

$labels['filename'] = 'Nombre del archivo';
$labels['filesize'] = 'Tamaño del archivo';

$labels['preferhtml'] = 'Prefiero HTML';
$labels['htmlmessage'] = 'Mensaje HTML';
$labels['prettydate'] = 'Fecha detallada';

$labels['addtoaddressbook'] = 'Añadir a contactos';

// weekdays short
$labels['sun'] = 'Dom';
$labels['mon'] = 'Lun';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mié';
$labels['thu'] = 'Jue';
$labels['fri'] = 'Vie';
$labels['sat'] = 'Sáb';

// weekdays long
$labels['sunday'] = 'Domingo';
$labels['monday'] = 'Lunes';
$labels['tuesday'] = 'Martes';
$labels['wednesday'] = 'Miércoles';
$labels['thursday'] = 'Jueves';
$labels['friday'] = 'Viernes';
$labels['saturday'] = 'Sábado';

// months short
$labels['jan'] = 'Ene';
$labels['feb'] = 'Feb';
$labels['mar'] = 'Mar';
$labels['apr'] = 'Abr';
$labels['may'] = 'May';
$labels['jun'] = 'Jun';
$labels['jul'] = 'Jul';
$labels['aug'] = 'Ago';
$labels['sep'] = 'Sep';
$labels['oct'] = 'Oct';
$labels['nov'] = 'Nov';
$labels['dec'] = 'Dic';

// months long
$labels['longjan'] = 'Enero';
$labels['longfeb'] = 'Febrero';
$labels['longmar'] = 'Marzo';
$labels['longapr'] = 'Abril';
$labels['longmay'] = 'Mayo';
$labels['longjun'] = 'Junio';
$labels['longjul'] = 'Julio';
$labels['longaug'] = 'Agosto';
$labels['longsep'] = 'Septiembre';
$labels['longoct'] = 'Octubre';
$labels['longnov'] = 'Noviembre';
$labels['longdec'] = 'Diciembre';

$labels['today'] = 'Hoy';

// toolbar buttons
$labels['checkmail']         = 'Revisar si hay nuevos mensajes';
$labels['writenewmessage']   = 'Crear nuevo mensaje';
$labels['replytomessage']    = 'Responder mensaje';
$labels['replytoallmessage'] = 'Responder al emisor y a todos los destinatarios';
$labels['forwardmessage']    = 'Reenviar mensaje';
$labels['deletemessage']     = 'Eliminar mensaje';
$labels['movemessagetotrash'] = 'Mover mensaje a la papelera';
$labels['printmessage']      = 'Imprimir este mensaje';
$labels['previousmessage']   = 'Mostrar mensaje anterior';
$labels['previousmessages']  = 'Mostrar grupo anterior de mensajes';
$labels['firstmessage']      = 'Mostrar primer mensaje';
$labels['firstmessages']     = 'Mostrar primer grupo de mensajes';
$labels['nextmessage']       = 'Mostrar siguente mensaje';
$labels['nextmessages']      = 'Mostrar siguente grupo de mensajes';
$labels['lastmessage']       = 'Mostrar último mensaje';
$labels['lastmessages']      = 'Mostrar último grupo de mensajes';
$labels['backtolist']        = 'Volver a la lista de mensajes';
$labels['viewsource']        = 'Mostrar código';
$labels['markmessages']      = 'Marcar mensajes';
$labels['markread']          = 'Como leído';
$labels['markunread']        = 'Como no leído';
$labels['markflagged']       = 'Como marcado';
$labels['markunflagged']     = 'Como no marcado';

$labels['select'] = 'Elija';
$labels['all'] = 'Todos';
$labels['none'] = 'Ninguno';
$labels['unread'] = 'Sin leer';
$labels['flagged']    = 'Señalado';
$labels['unanswered'] = 'Sin respuesta';
$labels['filter'] = 'Filtrar';

$labels['compact'] = 'Compacta';
$labels['empty'] = 'Vacía';
$labels['purge'] = 'Eliminar';

$labels['quota'] = 'Uso de cuenta';
$labels['unknown'] = 'desconocido';
$labels['unlimited'] = 'sin límite';

$labels['quicksearch'] = 'Búsqueda rápida';
$labels['resetsearch'] = 'Afinar la búsqueda';

$labels['openinextwin'] = 'Abrir en nueva ventana';

// message compose
$labels['compose'] = 'Escribir un mensaje';
$labels['savemessage'] = 'Guardar como borrador';
$labels['sendmessage'] = 'Enviar ahora el mensaje';
$labels['addattachment'] = 'Añadir un archivo';
$labels['charset'] = 'Codificación';
$labels['editortype'] = 'Tipo de editor';
$labels['returnreceipt'] = 'Acuse de recibo';

$labels['checkspelling'] = 'Revisar ortografía';
$labels['resumeediting'] = 'Continuar edición';
$labels['revertto'] = 'Revertir a';

$labels['attachments'] = 'Adjuntos';
$labels['upload'] = 'Subir';
$labels['close'] = 'Cerrar';

$labels['low'] = 'Bajo';
$labels['lowest'] = 'Bajísimo';
$labels['normal'] = 'Normal';
$labels['high'] = 'Alto';
$labels['highest'] = 'Altísimo';

$labels['nosubject'] = '(sin asunto)';
$labels['showimages'] = 'Mostrar imágenes';
$labels['alwaysshow'] = 'Muestra siempre imágenes de $sender';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto';
$labels['savesentmessagein'] = 'Guardar mensaje enviado en';
$labels['dontsave'] = 'No guardar';
$labels['maxuploadsize'] = 'Tamaño máximo permitido: $size';

$labels['addcc'] = 'Añadir Cc';
$labels['addbcc'] = 'Añadir Bcc';
$labels['addreplyto'] = 'Añadir Respuesta a';

// mdn
$labels['mdnrequest'] = 'El emisor de este mensaje desea ser notificado cuando usted lo lea. ¿Quiere enviar esta notificación?';
$labels['receiptread'] = 'Notificación de lectura';
$labels['yourmessage'] = 'Esta es una notificación de lectura de su mensaje';
$labels['receiptnote'] = 'Nota: Esta notificación sólo significa que su mensaje fue mostrado en la computadora del receptor. No hay garantía de que el receptor haya leído o entendido el contenido del mensaje.';

// address boook
$labels['name'] = 'Nombre completo';
$labels['firstname'] = 'Nombre';
$labels['surname'] = 'Apellido';
$labels['email'] = 'Correo';

$labels['addcontact'] = 'Añadir nuevo contacto';
$labels['editcontact'] = 'Editar contacto';

$labels['edit'] = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save'] = 'Guardar';
$labels['delete'] = 'Eliminar';

$labels['newcontact'] = 'Crear nuevo contacto';
$labels['deletecontact'] = 'Eliminar contactos seleccionados';
$labels['composeto'] = 'Enviar mensaje a';
$labels['contactsfromto'] = 'Contactos $from a $to de $count';
$labels['print'] = 'Imprimir';
$labels['export'] = 'Exportar';
$labels['exportvcards']   = 'Exportar contactos en formato vCard';

$labels['previouspage'] = 'Mostrar grupo anterior';
$labels['firstpage'] = 'Mostrar primer grupo';
$labels['nextpage'] = 'Mostrar grupo siguiente';
$labels['lastpage'] = 'Mostrar último grupo';

$labels['groups'] = 'Grupos';
$labels['personaladrbook'] = 'Direcciones personales';

$labels['import'] = 'Importar';
$labels['importcontacts'] = 'Importar contactos';
$labels['importfromfile'] = 'Importar desde archivo:';
$labels['importreplace'] = 'Reemplazar toda la lista de contactos';
$labels['importtext'] = 'Puedes importar contactos de una lista existente.<br/>Puedes ver el formato soportado de datos en: <a href="http://en.wikipedia.org/wiki/VCard">vCard</a>.';
$labels['done'] = 'Realizado';

// settings
$labels['settingsfor'] = 'Configuración para';

$labels['preferences'] = 'Preferencias';
$labels['userpreferences'] = 'Preferencias de usuario';
$labels['editpreferences'] = 'Editar preferencias de usuario';

$labels['identities'] = 'Identidades';
$labels['manageidentities'] = 'Gestionar identidades para esta cuenta';
$labels['newidentity'] = 'Nueva identidad';

$labels['newitem'] = 'Nuevo';
$labels['edititem'] = 'Editar';

$labels['setdefault'] = 'Seleccionar opción por defecto';
$labels['autodetect']  = 'Auto';
$labels['language'] = 'Idioma';
$labels['timezone'] = 'Zona horaria';
$labels['pagesize'] = 'Filas por página';
$labels['signature'] = 'Firma';
$labels['dstactive'] = 'Cambio de horario';
$labels['htmleditor'] = 'Componer mensaje en HTML';
$labels['htmlsignature'] = 'Firma HTML';
$labels['previewpane'] = 'Mostrar vista preliminar';
$labels['skin'] = 'Apariencia de la interfaz';
$labels['logoutclear'] = 'Vaciar papelera al cerrar sesión';
$labels['logoutcompact'] = 'Compactar la bandeja de entrada al cerrar sesión';
$labels['uisettings'] = 'Interfaz de usuario';
$labels['serversettings'] = 'Configuración del servidor';
$labels['mailboxview'] = 'Vista de buzón';
$labels['mdnrequests'] = 'Notificaciones de envío';
$labels['askuser'] = 'preguntar al usuario';
$labels['autosend'] = 'enviar automáticamente';
$labels['ignore'] = 'ignorar';
$labels['readwhendeleted'] = 'Marcar el mensaje como leído al borrarlo';
$labels['flagfordeletion'] = 'Marcar el mensaje para borrarse en vez de borrarlo';
$labels['skipdeleted'] = 'No mostrar mensajes borrados';
$labels['showremoteimages'] = 'Mostrar las imágenes externas';
$labels['fromknownsenders'] = 'de remitentes conocidos';
$labels['always'] = 'siempre';
$labels['showinlineimages'] = 'Mostrar imágenes adjuntas debajo del mensaje';
$labels['autosavedraft'] = 'Guardar borrador automáticamente';
$labels['everynminutes'] = 'cada $n minutos';
$labels['keepaliveevery']  = 'cada $n minuto(s)';
$labels['keepalive']  = 'Comprobar mensajes en línea';
$labels['never'] = 'nunca';
$labels['messagesdisplaying'] = 'Vista de mensajes';
$labels['messagescomposition'] = 'Composición de mensajes';
$labels['mimeparamfolding'] = 'Nombres de archivos adjuntos';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (otro)';
$labels['advancedoptions'] = 'Opciones Avanzadas';
$labels['focusonnewmessage'] = 'Activar la ventana del navegador en un mensaje nuevo';
$labels['checkallfolders'] = 'Comprobar todas las carpetas para mensajes nuevos';

$labels['folder'] = 'Bandeja';
$labels['folders'] = 'Bandejas';
$labels['foldername'] = 'Nombre de bandeja';
$labels['subscribed'] = 'Suscrita';
$labels['messagecount'] = 'Mensajes';
$labels['create'] = 'Crear';
$labels['createfolder'] = 'Crear nueva bandeja';
$labels['rename'] = 'Renombrar';
$labels['renamefolder'] = 'Renombrar bandeja';
$labels['deletefolder'] = 'Eliminar bandeja';
$labels['managefolders'] = 'Administrar bandejas';
$labels['specialfolders'] = 'Bandejas Especiales';

$labels['sortby'] = 'Ordenar por';
$labels['sortasc'] = 'Orden ascendente';
$labels['sortdesc'] = 'Orden descendente';

// units
$labels['B'] = 'B';
$labels['KB'] = 'Kb';
$labels['MB'] = 'Mb';
$labels['GB'] = 'Gb';

?>
