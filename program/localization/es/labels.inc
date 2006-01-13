<?php

/*
 +-----------------------------------------------------------------------+
 | language/en/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: David Grajal Blanco <dgrabla@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: labels.inc,v 1.1.1.1 2005/09/17

*/

$labels = array();

// login page
$labels['username']  = 'Nombre de usuario';
$labels['password']  = 'Contraseña';
$labels['server']    = 'Servidor';
$labels['login']     = 'Entrar';

// taskbar
$labels['logout']   = 'Cerrar sesión';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Configuración';
$labels['addressbook'] = 'Contactos';

// mailbox names
$labels['inbox']  = 'Entrada';
$labels['sent']   = 'Enviados';
$labels['trash']  = 'Papelera';
$labels['drafts'] = 'Borradores';
$labels['junk']   = 'Basura';

// message listing
$labels['subject'] = 'Asunto';
$labels['from']    = 'Remitente';
$labels['to']      = 'Destinatario';
$labels['cc']      = 'CC';
$labels['bcc']     = 'BCC';
$labels['replyto'] = 'Responder';
$labels['date']    = 'Fecha';
$labels['size']    = 'Tamaño';
$labels['priority'] = 'Prioridad';
$labels['organization'] = 'Organización';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Carpetas';
$labels['messagesfromto'] = 'Mensajes desde $from a $to de $count';
$labels['messagenrof'] = 'Mensaje $nr de $count';

$labels['moveto']   = 'mover a...';
$labels['download'] = 'descargar';

$labels['filename'] = 'Nombre del fichero';
$labels['filesize'] = 'Tamaño del fichero';

$labels['preferhtml'] = 'Prefiero HTML';
$labels['htmlmessage'] = 'Mensaje HTML';
$labels['prettydate'] = 'Fechas cortas';

$labels['addtoaddressbook'] = 'Añadir a contactos';

// weekdays short
$labels['sun'] = 'D';
$labels['mon'] = 'L';
$labels['tue'] = 'M';
$labels['wed'] = 'X';
$labels['thu'] = 'J';
$labels['fri'] = 'V';
$labels['sat'] = 'S';

// weekdays long
$labels['sunday']    = 'Domingo';
$labels['monday']    = 'Lunes';
$labels['tuesday']   = 'Martes';
$labels['wednesday'] = 'Miercoles';
$labels['thursday']  = 'Jueves';
$labels['friday']    = 'Viernes';
$labels['saturday']  = 'Sábado';

$labels['today'] = 'Hoy';

// toolbar buttons
$labels['writenewmessage']  = 'Crear nuevo mensaje';
$labels['replytomessage']   = 'Responder al mensaje';
$labels['replytoallmessage'] = 'Responder a todos';
$labels['forwardmessage']   = 'Reenviar mensaje';
$labels['deletemessage']    = 'Move message to trash';
$labels['printmessage']     = 'Imprimir este mensaje';
$labels['previousmessages'] = 'Mostrar mensajes anteriores';
$labels['nextmessages']     = 'Mostrar mensajes siguientes';
$labels['backtolist']       = 'Volver a la lista de mensajes';

$labels['select'] = 'Seleccionar';
$labels['all'] = 'Todos';
$labels['none'] = 'Ninguno';
$labels['unread'] = 'No leidos';

// message compose
$labels['compose']  = 'Escribir un mensaje';
$labels['sendmessage']  = 'Enviar ahora el mensaje';
$labels['addattachment']  = 'Añadir un fichero';

$labels['attachments'] = 'Adjuntos';
$labels['upload'] = 'Subir';
$labels['close']  = 'Cerrar';

$labels['low']     = 'Bajo';
$labels['lowest']  = 'Bajísimo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alto';
$labels['highest'] = 'Altísimo';

$labels['showimages'] = 'Mostrar imágenes';


// address boook
$labels['name']      = 'Mostrar nombre';
$labels['firstname'] = 'Nombre';
$labels['surname']   = 'Apellido';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Añadir nuevo contacto';
$labels['editcontact'] = 'Editar contacto';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Salvar';
$labels['delete'] = 'Eliminar';

$labels['newcontact']     = 'Crear nuevo contacto';
$labels['deletecontact']  = 'Eliminar contactos seleccionados';
$labels['composeto']      = 'Redactar correo a';
$labels['contactsfromto'] = 'Contactos $from a $to de $count';


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


$labels['folders']  = 'Carpetas';
$labels['foldername']  = 'Nombre de carpeta';
$labels['subscribed']  = 'Suscribirse';
$labels['create']  = 'Crear';
$labels['createfolder']  = 'Crear nueva carpeta';
$labels['deletefolder']  = 'Eliminar carpeta';
$labels['managefolders']  = 'Gestionar carpetas';


?>
