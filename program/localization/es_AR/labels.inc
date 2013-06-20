<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Bienvenido a $product';
$labels['username']  = 'Nombre de usuario';
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
$labels['sender']  = 'Remitente';
$labels['to']      = 'Destinatario';
$labels['cc']      = 'Copia';
$labels['bcc']     = 'Cco';
$labels['replyto'] = 'Responder a';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Fecha';
$labels['size']    = 'Tamaño';
$labels['priority'] = 'Prioridad';
$labels['organization'] = 'Organización';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'Listar opciones...';

$labels['mailboxlist'] = 'Carpetas';
$labels['messagesfromto'] = 'Mensajes $from a $to de $count';
$labels['threadsfromto'] = '$from a $to de $count conversaciones';
$labels['messagenrof'] = 'Mensaje $nr de $count';
$labels['fromtoshort'] = '$from – $to de $count';

$labels['copy']     = 'Copiar';
$labels['move']     = 'Mover';
$labels['moveto']   = 'Mover a...';
$labels['download'] = 'Descargar';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Mostrar';
$labels['showanyway'] = 'Mostrar esto siempre';

$labels['filename'] = 'Nombre del archivo';
$labels['filesize'] = 'Tamaño del archivo';

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
$labels['sunday']    = 'Domingo';
$labels['monday']    = 'Lunes';
$labels['tuesday']   = 'Martes';
$labels['wednesday'] = 'Miércoles';
$labels['thursday']  = 'Jueves';
$labels['friday']    = 'Viernes';
$labels['saturday']  = 'Sábado';

// months short
$labels['jan']	= 'Ene';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Abr';
$labels['may']	= 'Mayo';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Ago';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dic';

// months long
$labels['longjan']	= 'Enero';
$labels['longfeb']	= 'Febrero';
$labels['longmar']	= 'Marzo';
$labels['longapr']	= 'Abril';
$labels['longmay']	= 'Mayo';
$labels['longjun']	= 'Junio';
$labels['longjul']	= 'Julio';
$labels['longaug']	= 'Agosto';
$labels['longsep']	= 'Septiembre';
$labels['longoct']	= 'Octubre';
$labels['longnov']	= 'Noviembre';
$labels['longdec']	= 'Diciembre';

$labels['today'] = 'Hoy';

// toolbar buttons
$labels['refresh']          = 'Actualizar';
$labels['checkmail']        = 'Revisar si hay nuevos mensajes';
$labels['compose']          = 'Escribir un mensaje';
$labels['writenewmessage']  = 'Crear nuevo mensaje';
$labels['reply']            = 'Responder';
$labels['replytomessage']   = 'Responder mensaje';
$labels['replytoallmessage'] = 'Responder al emisor y a todos los destinatarios';
$labels['replyall']         = 'Responder a todos';
$labels['replylist']        = 'Responder a lista';
$labels['forward']          = 'Reenviar';
$labels['forwardinline']    = 'Reenviar en linea';
$labels['forwardattachment'] = 'Reenviar como adjunto';
$labels['forwardmessage']   = 'Reenviar mensaje';
$labels['deletemessage']    = 'Eliminar mensaje';
$labels['movemessagetotrash'] = 'Mover mensaje a la papelera';
$labels['printmessage']     = 'Imprimir este mensaje';
$labels['previousmessage']  = 'Mostrar mensaje anterior';
$labels['firstmessage']     = 'Mostrar primer mensaje';
$labels['nextmessage']      = 'Mostrar siguente mensaje';
$labels['lastmessage']      = 'Mostrar último mensaje';
$labels['backtolist']       = 'Volver a la lista de mensajes';
$labels['viewsource']       = 'Mostrar código';
$labels['mark']             = 'Marcar';
$labels['markmessages']     = 'Marcar mensajes';
$labels['markread']         = 'Como leído';
$labels['markunread']       = 'Como no leído';
$labels['markflagged']      = 'Como marcado';
$labels['markunflagged']    = 'Como no marcado';
$labels['moreactions']      = 'Mas acciones...';
$labels['more']             = 'Más';
$labels['back']             = 'Atrás';
$labels['options']          = 'Opciones';

$labels['select'] = 'Elija';
$labels['all'] = 'Todos';
$labels['none'] = 'Ninguno';
$labels['currpage'] = 'Página actual';
$labels['unread'] = 'Sin leer';
$labels['flagged'] = 'Marcado';
$labels['unanswered'] = 'Sin respuesta';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Eliminado';
$labels['undeleted'] = 'No eliminado';
$labels['invert'] = 'Invertir';
$labels['filter'] = 'Filtrar';
$labels['list'] = 'Lista';
$labels['threads'] = 'Conversaciones';
$labels['expand-all'] = 'Expandir Todos';
$labels['expand-unread'] = 'Expandir No Leidos';
$labels['collapse-all'] = 'Colapsar Todos';
$labels['threaded'] = 'Como conversaciones';

$labels['autoexpand_threads'] = 'Expandir mensajes en conversación';
$labels['do_expand'] = 'todas las conversaciones';
$labels['expand_only_unread'] = 'solo con mensajes no leídos';
$labels['fromto'] = 'De/A';
$labels['flag'] = 'Marca';
$labels['attachment'] = 'Adjunto';
$labels['nonesort'] = 'Ninguno';
$labels['sentdate'] = 'Fecha de Enviado';
$labels['arrival'] = 'Fecha de Recepción';
$labels['asc'] = 'Ascendente';
$labels['desc'] = 'Descendente';
$labels['listcolumns'] = 'Listar columnas';
$labels['listsorting'] = 'Ordenar por';
$labels['listorder'] = 'Ordenado por';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Acciones de carpeta...';
$labels['compact'] = 'Compactar';
$labels['empty'] = 'Vaciar';

$labels['quota'] = 'Uso de disco';
$labels['unknown']  = 'desconocido';
$labels['unlimited']  = 'sin límite';

$labels['quicksearch']  = 'Búsqueda rápida';
$labels['resetsearch']  = 'Reajustar la búsqueda';
$labels['searchmod']  = 'Opciones de búsqueda';
$labels['msgtext']  = 'Mensaje completo';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Abrir en ventana nueva';
$labels['emlsave'] = 'Guardar (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Editar como nuevo';
$labels['send']           = 'Enviar';
$labels['sendmessage']    = 'Enviar ahora el mensaje';
$labels['savemessage']    = 'Guardar como borrador';
$labels['addattachment']  = 'Añadir un archivo';
$labels['charset']        = 'Codificación';
$labels['editortype']     = 'Tipo de editor';
$labels['returnreceipt']  = 'Acuse de recibo';
$labels['dsn']            = 'Notificación de estado del envío';
$labels['mailreplyintro'] = 'El $date, $sender escribió:';
$labels['originalmessage'] = 'Mensaje original';

$labels['editidents']    = 'Editar identidades';
$labels['spellcheck']    = 'Gramática';
$labels['checkspelling'] = 'Revisar ortografía';
$labels['resumeediting'] = 'Continuar edición';
$labels['revertto']      = 'Revertir a';

$labels['attach'] = 'Adjuntar';
$labels['attachments'] = 'Adjuntos';
$labels['upload'] = 'Agregar';
$labels['uploadprogress'] = '$percent ($current de $total)';
$labels['close']  = 'Cerrar';
$labels['messageoptions']  = 'Opciones de mensaje...';

$labels['low']     = 'Bajo';
$labels['lowest']  = 'Bajísimo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alto';
$labels['highest'] = 'Altísimo';

$labels['nosubject']  = '(sin asunto)';
$labels['showimages'] = 'Mostrar imágenes';
$labels['alwaysshow'] = 'Siempre mostrar imágenes de $sender';
$labels['isdraft']    = 'Este es un borrador.';
$labels['andnmore']   = '$nr más...';
$labels['togglemoreheaders'] = 'Mostrarme más encabezados de mensaje';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto';
$labels['savesentmessagein'] = 'Guardar mensaje enviado en';
$labels['dontsave'] = 'no guardar';
$labels['maxuploadsize'] = 'El tamaño maximo permitido por archivo es $size';

$labels['addcc'] = 'Añadir Cc';
$labels['addbcc'] = 'Añadir Cco';
$labels['addreplyto'] = 'Añadir Respuesta a';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'El emisor de este mensaje desea ser notificado cuando usted lo lea. ¿Quiere enviar esta notificación?';
$labels['receiptread'] = 'Notificación de lectura';
$labels['yourmessage'] = 'Esta es una notificación de lectura de su mensaje';
$labels['receiptnote'] = 'Nota: Esta notificación sólo significa que su mensaje fue mostrado en la computadora del receptor. No hay garantía de que el receptor haya leído o entendido el contenido del mensaje.';

// address boook
$labels['name']         = 'Nombre completo';
$labels['firstname']    = 'Nombre';
$labels['surname']      = 'Apellido';
$labels['middlename']   = 'Segundo nombre';
$labels['nameprefix']   = 'Prefijo';
$labels['namesuffix']   = 'Subfijo';
$labels['nickname']     = 'Sobre nombre';
$labels['jobtitle']     = 'Puesto';
$labels['department']   = 'Departamento';
$labels['gender']       = 'Sexo';
$labels['maidenname']   = 'Apellido de soltera';
$labels['email']        = 'Correo';
$labels['phone']        = 'Teléfono';
$labels['address']      = 'Dirección';
$labels['street']       = 'Calle';
$labels['locality']     = 'Ciudad';
$labels['zipcode']      = 'Código Postal';
$labels['region']       = 'Estado/Provincia';
$labels['country']      = 'País';
$labels['birthday']     = 'Cumpleaños';
$labels['anniversary']  = 'Aniversario';
$labels['website']      = 'Sitio Web';
$labels['instantmessenger'] = 'Mensajería Instantanea';
$labels['notes'] = 'Notas';
$labels['male']   = 'masculino';
$labels['female'] = 'femenino';
$labels['manager'] = 'Administrador';
$labels['assistant'] = 'Asistente';
$labels['spouse'] = 'Cónygue';
$labels['allfields'] = 'Todos los campos';
$labels['search'] = 'Buscar';
$labels['advsearch'] = 'Búsqueda Avanzada';
$labels['advanced'] = 'Avanzado';
$labels['other'] = 'Otro';

$labels['typehome']   = 'Particular';
$labels['typework']   = 'Laboral';
$labels['typeother']  = 'Otro';
$labels['typemobile']  = 'Móvil';
$labels['typemain']  = 'Principal';
$labels['typehomefax']  = 'Fax Particular';
$labels['typeworkfax']  = 'Fax Laboral';
$labels['typecar']  = 'Auto';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Asistente';
$labels['typehomepage']  = 'Página Personal';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Perfil';

$labels['addfield'] = 'Agregar campo...';
$labels['addcontact'] = 'Añadir nuevo contacto';
$labels['editcontact'] = 'Editar contacto';
$labels['contacts'] = 'Contactos';
$labels['contactproperties'] = 'Propiedades del contacto';
$labels['personalinfo'] = 'Información personal';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Guardar';
$labels['delete'] = 'Eliminar';
$labels['rename'] = 'Renombrar';
$labels['addphoto'] = 'Agregar';
$labels['replacephoto'] = 'Reemplazar';
$labels['uploadphoto'] = 'Subir foto';

$labels['newcontact']     = 'Añadir nuevo contacto';
$labels['deletecontact']  = 'Eliminar contactos seleccionados';
$labels['composeto']      = 'Enviar mensaje a';
$labels['contactsfromto'] = 'Contactos $from a $to de $count';
$labels['print']          = 'Imprimir';
$labels['export']         = 'Exportar';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Exportar contactos en format vCard';
$labels['newcontactgroup'] = 'Crear Nuevo Grupo de Contacto';
$labels['grouprename']    = 'Renombrar grupo';
$labels['groupdelete']    = 'Eliminar grupo';
$labels['groupremoveselected'] = 'Remover del grupo los contactos seleccionados';

$labels['previouspage']   = 'Mostrar grupo anterior';
$labels['firstpage']      = 'Mostrar primer grupo';
$labels['nextpage']       = 'Mostrar siguiente grupo';
$labels['lastpage']       = 'Mostrar último grupo';

$labels['group'] = 'Grupo';
$labels['groups'] = 'Grupos';
$labels['personaladrbook'] = 'Direcciones personales';

$labels['searchsave'] = 'Guardar búsqueda';
$labels['searchdelete'] = 'Eliminar búsqueda';

$labels['import'] = 'Importar';
$labels['importcontacts'] = 'Importar contactos';
$labels['importfromfile'] = 'Importar desde el archivo:';
$labels['importtarget'] = 'Agregar nuevos contactos a la libreta de direcciones:';
$labels['importreplace'] = 'Reemplazar completamente la lista de contactos';
$labels['importdesc'] = 'Puedes subir contactos desde una libreta de direcciones existente.<br/>Actualmente soportamos la importación de direcciones utilizando el formato <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> o CSV (Valores Separados por Coma).';
$labels['done'] = 'Hecho';

// settings
$labels['settingsfor'] = 'Configuración para';
$labels['about'] = 'Acerca de';
$labels['preferences'] = 'Preferencias';
$labels['userpreferences'] = 'Preferencias de usuario';
$labels['editpreferences'] = 'Editar preferencias de usuario';

$labels['identities'] = 'Identidades';
$labels['manageidentities'] = 'Gestionar identidades para esta cuenta';
$labels['newidentity'] = 'Nueva identidad';

$labels['newitem'] = 'Nuevo';
$labels['edititem'] = 'Editar';

$labels['preferhtml'] = 'Prefiero HTML';
$labels['defaultcharset'] = 'Juego de Caracteres por Defecto';
$labels['htmlmessage'] = 'Mensaje HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Formato de fecha';
$labels['timeformat'] = 'Formato de tiempo';
$labels['prettydate'] = 'Fecha detallada';
$labels['setdefault']  = 'Seleccionar opción por defecto';
$labels['autodetect']  = 'Automático';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Zona horaria';
$labels['pagesize']  = 'Filas por página';
$labels['signature'] = 'Firma';
$labels['dstactive']  = 'Cambio de horario';
$labels['showinextwin'] = 'Abrir mensaje en nueva ventana';
$labels['composeextwin'] = 'Redactar en nueva ventana';
$labels['htmleditor'] = 'Componer mensaje en HTML';
$labels['htmlonreply'] = 'al responder un mensaje HTML';
$labels['htmlonreplyandforward'] = 'al reenviar o responder un mensaje HTML';
$labels['htmlsignature'] = 'Firma HTML';
$labels['showemail'] = 'Show email address with display name';
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
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ignorar';
$labels['readwhendeleted'] = 'Marcar el mensage como leído al borrarlo';
$labels['flagfordeletion'] = 'Marcar el mensage para borrarse en vez de borrarlo';
$labels['skipdeleted'] = 'No mostrar mensajes eliminados';
$labels['deletealways'] = 'Cuando no se pueda mover un mensaje a la papelera, eliminarlo';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Mostrar imágenes remotas';
$labels['fromknownsenders'] = 'de remitentes conocidos';
$labels['always'] = 'siempre';
$labels['showinlineimages'] = 'Mostrar imágenes adjuntas debajo del mensaje';
$labels['autosavedraft']  = 'Guardar borrador automáticamente';
$labels['everynminutes']  = 'cada $n minuto(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'nunca';
$labels['immediately']  = 'inmediatamente';
$labels['messagesdisplaying'] = 'Vista de mensajes';
$labels['messagescomposition'] = 'Composición de mensajes';
$labels['mimeparamfolding'] = 'Nombre de adjuntos';
$labels['2231folding'] = 'RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'RFC 2047 (Otro)';
$labels['force7bit'] = 'Usar codificación MIME para caracteres de 8 bit';
$labels['advancedoptions'] = 'Opciones Avanzadas';
$labels['focusonnewmessage'] = 'Traer al frente la ventana del navegador cuando haya nuevos mensajes';
$labels['checkallfolders'] = 'Verificar todas las carpetas por nuevos mensajes';
$labels['displaynext'] = 'Luedo de eliminar/mover, mostrar el próximo mensaje';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Opciones Principales';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Selección';
$labels['maintenance'] = 'Mantenimiento';
$labels['newmessage'] = 'Nuevo Mensaje';
$labels['signatureoptions'] = 'Opciones de Firma';
$labels['whenreplying'] = 'Al Responder';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'iniciar el nuevo mensaje sobre el original';
$labels['replybottomposting'] = 'iniciar el nuevo mensaje debajo del original';
$labels['replyremovesignature'] = 'Al responder, eliminar la firma del mensaje original';
$labels['autoaddsignature'] = 'Agregar la firma automáticamente';
$labels['newmessageonly'] = 'nuevos mensajes solamente';
$labels['replyandforwardonly'] = 'respuestas o reenvios solamente';
$labels['insertsignature'] = 'Insertar firma';
$labels['previewpanemarkread']  = 'Marcar mensajes previsualizados como leidos';
$labels['afternseconds']  = 'despues de $n segundos';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Add to dictionary';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Carpeta';
$labels['folders']  = 'Carpetas';
$labels['foldername']  = 'Nombre de carpeta';
$labels['subscribed']  = 'Suscripta';
$labels['messagecount'] = 'Mensajes';
$labels['create']  = 'Crear';
$labels['createfolder']  = 'Crear nueva carpeta';
$labels['managefolders']  = 'Administrar carpetas';
$labels['specialfolders'] = 'Carpetas Especiales';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'Ordenar por';
$labels['sortasc']  = 'Orden ascendente';
$labels['sortdesc'] = 'Orden descendente';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Ingles';
$labels['westerneuropean'] = 'Europa Occidental';
$labels['easterneuropean'] = 'Europa del Este';
$labels['southeasterneuropean'] = 'Sudeste de Europa';
$labels['baltic'] = 'Báltico';
$labels['cyrillic'] = 'Cirílico';
$labels['arabic'] = 'Árabe';
$labels['greek'] = 'Griego';
$labels['hebrew'] = 'Hebreo';
$labels['turkish'] = 'Turco';
$labels['nordic'] = 'Nórdico';
$labels['thai'] = 'Tailandés';
$labels['celtic'] = 'Céltico';
$labels['vietnamese'] = 'Vietnamita';
$labels['japanese'] = 'Japonés';
$labels['korean'] = 'Coreano';
$labels['chinese'] = 'Chino';

?>
