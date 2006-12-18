<?php

/*
 +-----------------------------------------------------------------------+
 | language/es/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Javier Smaldone <javier@smaldone.com.ar                       |
 |         http://www.smaldone.com.ar                                    |
 | Based on previous work by David Grajal Blanco <dgrabla@gmail.com>     |
 |         http://david.grajal.net                                       |
 +-----------------------------------------------------------------------+
 | Changelog:                                                            |
 |  - 29/9/2006 Syntax check and style modifications                     |
 |  - 6/2/2006 Translations of new features and improvements             |
 |  - 17/9/2005 First release                                            |
 +-----------------------------------------------------------------------+

*/

$labels = array();

// login page
$labels['welcome']   = '$product';
$labels['username']  = 'Dirección de Correo';
$labels['password']  = 'Contraseña';
$labels['server']    = 'Servidor';
$labels['login']     = 'Entrar';

// taskbar
$labels['logout']   = 'Cerrar sesión';
$labels['mail']     = 'Correo';
$labels['settings'] = 'Configuración';
$labels['addressbook'] = 'Contactos';

// mailbox names
$labels['inbox']  = 'Entrada';
$labels['drafts'] = 'Borradores';
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Papelera';
$labels['junk']   = 'Basura';

// message listing
$labels['subject'] = 'Asunto';
$labels['from']    = 'Remitente';
$labels['to']      = 'Destinatario';
$labels['cc']      = 'Copia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Responder';
$labels['date']    = 'Fecha';
$labels['size']    = 'Tamaño';
$labels['priority'] = 'Prioridad';
$labels['organization'] = 'Organización';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Bandejas';
$labels['messagesfromto'] = 'Mensajes desde $from a $to de $count';
$labels['messagenrof'] = 'Mensaje $nr de $count';

$labels['moveto']   = 'mover a...';
$labels['download'] = 'descargar';

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
$labels['wed'] = 'Mie';
$labels['thu'] = 'Jue';
$labels['fri'] = 'Vie';
$labels['sat'] = 'Sáb';

// weekdays long
$labels['sunday']    = 'Domingo';
$labels['monday']    = 'Lunes';
$labels['tuesday']   = 'Martes';
$labels['wednesday'] = 'Miércoles';
$labels['thursday']  = 'Jueves';
$labels['friday']    = 'Viernes';
$labels['saturday']  = 'Sábado';

$labels['today'] = 'Hoy';

// toolbar buttons
$labels['checkmail']        = 'Revisar si hay nuevos mensajes';
$labels['writenewmessage']  = 'Crear nuevo mensaje';
$labels['replytomessage']   = 'Responder mensaje';
$labels['replytoallmessage'] = 'Responder al emisor y a todos los destinatarios';
$labels['forwardmessage']   = 'Reenviar mensaje';
$labels['deletemessage']    = 'Mover mensaje a la papelera';
$labels['printmessage']     = 'Imprimir este mensaje';
$labels['previousmessage'] = 'Mostrar mensaje anterior';
$labels['previousmessages'] = 'Mostrar grupo anterior de mensajes';
$labels['firstmessage']     = 'Mostrar primer mensaje';
$labels['firstmessages']    = 'Mostrar primer grupo de mensajes';
$labels['nextmessage']     = 'Mostrar siguente mensaje';
$labels['nextmessages']     = 'Mostrar siguente grupo de mensajes';
$labels['lastmessage']      = 'Mostrar último mensaje';
$labels['lastmessages']     = 'Mostrar último grupo de mensajes';
$labels['backtolist']       = 'Volver a la lista de mensajes';
$labels['viewsource']       = 'Mostrar código';

$labels['select'] = 'Elija';
$labels['all'] = 'Todos';
$labels['none'] = 'Ninguno';
$labels['unread'] = 'Sin leer';

$labels['compact'] = 'Compacta';
$labels['empty'] = 'Vacia';
$labels['purge'] = 'Eliminar';

$labels['quota'] = 'Uso de disco';
$labels['unknown']  = 'desconocido';
$labels['unlimited']  = 'sin límite';

$labels['quicksearch']  = 'Búsqueda rápida';
$labels['resetsearch']  = 'Reajustar la búsqueda';


// message compose
$labels['compose']  = 'Escribir un mensaje';
$labels['savemessage']  = 'Guardar como borrador';
$labels['sendmessage']  = 'Enviar ahora el mensaje';
$labels['addattachment']  = 'Añadir un archivo';
$labels['charset']  = 'Codificación';
$labels['editortype'] = 'Tipo de editor';
$labels['returnreceipt'] = 'Acuse de recibo';

$labels['checkspelling'] = 'Revisar ortografía';
$labels['resumeediting'] = 'Continuar edición';
$labels['revertto'] = 'Revertir a';

$labels['attachments'] = 'Adjuntos';
$labels['upload'] = 'Subir';
$labels['close']  = 'Cerrar';

$labels['low']     = 'Bajo';
$labels['lowest']  = 'Bajísimo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alto';
$labels['highest'] = 'Altísimo';


$labels['nosubject']  = '(sin asunto)';
$labels['showimages'] = 'Mostrar imágenes';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto';


// address boook
$labels['name']      = 'Nombre completo';
$labels['firstname'] = 'Nombre';
$labels['surname']   = 'Apellido';
$labels['email']     = 'Correo';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Guardar';
$labels['delete'] = 'Eliminar';

$labels['newcontact']     = 'Crear nuevo contacto';
$labels['addcontact']     = 'Añadir nuevo contacto';
$labels['editcontact']    = 'Editar contacto';
$labels['deletecontact']  = 'Eliminar contactos seleccionados';
$labels['composeto']      = 'Enviar mensaje a';
$labels['contactsfromto'] = 'Contactos $from a $to de $count';
$labels['print']          = 'Imprimir';
$labels['export']         = 'Exportar';

$labels['previouspage']   = 'Mostrar grupo anterior';
$labels['firstpage']   = 'Mostrar primer grupo';
$labels['nextpage']       = 'Mostrar grupo siguiente';
$labels['lastpage']       = 'Mostrar último grupo';


// LDAP search
$labels['ldapsearch'] = 'Búsqueda en el directorio LDAP';

$labels['ldappublicsearchname'] = 'Nombre';
$labels['ldappublicsearchtype'] = '¿Búsqueda exacta?';
$labels['ldappublicserverselect'] = 'Elegir servidores';
$labels['ldappublicsearchfield'] = 'Buscando';
$labels['ldappublicsearchform'] = 'Buscar un contacto';
$labels['ldappublicsearch'] = 'Buscar';


// settings
$labels['settingsfor']  = 'Configuración para';

$labels['preferences']  = 'Preferencias';
$labels['userpreferences']  = 'Preferencias de usuario';
$labels['editpreferences']  = 'Editar preferencias de usuario';

$labels['identities']  = 'Identidades';
$labels['manageidentities']  = 'Gestionar identidades para esta cuenta';
$labels['newidentity']  = 'Nueva identidad';

$labels['newitem']  = 'Nuevo';
$labels['edititem']  = 'Editar';

$labels['setdefault']  = 'Seleccionar opción por defecto';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Zona horaria';
$labels['pagesize']  = 'Filas por página';
$labels['signature'] = 'Firma';
$labels['dstactive']  = 'Cambio de horario';
$labels['htmleditor'] = 'Componer mensaje en HTML';
$labels['htmlsignature'] = 'Firma HTML';
$labels['previewpane'] = 'Mostrar vista preliminar';

$labels['autosavedraft']  = 'Guardar borrador automáticamente';
$labels['everynminutes']  = 'cada $n minutos';
$labels['never']  = 'nunca';

$labels['folder']  = 'Bandeja';
$labels['folders']  = 'Bandejas';
$labels['foldername']  = 'Nombre de bandeja';
$labels['subscribed']  = 'Suscribipta';
$labels['create']  = 'Crear';
$labels['createfolder']  = 'Crear nueva bandeja';
$labels['rename'] = 'Renombrar';
$labels['renamefolder'] = 'Renombrar bandeja';
$labels['deletefolder']  = 'Eliminar bandeja';
$labels['managefolders']  = 'Administrar bandejas';

$labels['sortby'] = 'Ordenar por';
$labels['sortasc']  = 'Orden ascendente';
$labels['sortdesc'] = 'Orden descendente';

$labels['changepasswd']  = 'Cambiar Contraseña';
$labels['newpasswd']  = 'Nueva Contraseña';
$labels['confpasswd']  = 'Confirmar Nueva Contraseña';

?>
