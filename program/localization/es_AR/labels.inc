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
$labels['sender']  = 'Sender';
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
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Carpetas';
$labels['messagesfromto'] = 'Mensajes $from a $to de $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Mensaje $nr de $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Copiar';
$labels['move']     = 'Mover';
$labels['moveto']   = 'Mover a...';
$labels['download'] = 'Descargar';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

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
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Revisar si hay nuevos mensajes';
$labels['compose']          = 'Escribir un mensaje';
$labels['writenewmessage']  = 'Crear nuevo mensaje';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'Responder mensaje';
$labels['replytoallmessage'] = 'Responder al emisor y a todos los destinatarios';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
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
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'Marcar mensajes';
$labels['markread']         = 'Como leído';
$labels['markunread']       = 'Como no leído';
$labels['markflagged']      = 'Como marcado';
$labels['markunflagged']    = 'Como no marcado';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'Elija';
$labels['all'] = 'Todos';
$labels['none'] = 'Ninguno';
$labels['currpage'] = 'Página actual';
$labels['unread'] = 'Sin leer';
$labels['flagged'] = 'Marcado';
$labels['unanswered'] = 'Sin respuesta';
$labels['deleted'] = 'Eliminado';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invertir';
$labels['filter'] = 'Filtrar';
$labels['list'] = 'List';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'Expandir Todos';
$labels['expand-unread'] = 'Expandir No Leidos';
$labels['collapse-all'] = 'Colapsar Todos';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'solo con mensajes no leídos';
$labels['fromto'] = 'From/To';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Adjunto';
$labels['nonesort'] = 'Ninguno';
$labels['sentdate'] = 'Fecha de Enviado';
$labels['arrival'] = 'Fecha de Recepción';
$labels['asc'] = 'Ascendente';
$labels['desc'] = 'Descendente';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
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

// message compose
$labels['editasnew']      = 'Editar como nuevo';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Enviar ahora el mensaje';
$labels['savemessage']    = 'Guardar como borrador';
$labels['addattachment']  = 'Añadir un archivo';
$labels['charset']        = 'Codificación';
$labels['editortype']     = 'Tipo de editor';
$labels['returnreceipt']  = 'Acuse de recibo';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Revisar ortografía';
$labels['resumeediting'] = 'Continuar edición';
$labels['revertto']      = 'Revertir a';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Adjuntos';
$labels['upload'] = 'Agregar';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Cerrar';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Bajo';
$labels['lowest']  = 'Bajísimo';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alto';
$labels['highest'] = 'Altísimo';

$labels['nosubject']  = '(sin asunto)';
$labels['showimages'] = 'Mostrar imágenes';
$labels['alwaysshow'] = 'Siempre mostrar imágenes de $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
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
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Correo';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'State/Province';
$labels['country']      = 'Country';
$labels['birthday']     = 'Birthday';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'male';
$labels['female'] = 'female';
$labels['manager'] = 'Manager';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'Other';

$labels['typehome']   = 'Home';
$labels['typework']   = 'Work';
$labels['typeother']  = 'Other';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'Home Fax';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'Añadir nuevo contacto';
$labels['editcontact'] = 'Editar contacto';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancelar';
$labels['save']   = 'Guardar';
$labels['delete'] = 'Eliminar';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

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
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Mostrar grupo anterior';
$labels['firstpage']      = 'Mostrar primer grupo';
$labels['nextpage']       = 'Mostrar siguiente grupo';
$labels['lastpage']       = 'Mostrar último grupo';

$labels['group'] = 'Grupo';
$labels['groups'] = 'Grupos';
$labels['personaladrbook'] = 'Direcciones personales';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'Importar';
$labels['importcontacts'] = 'Importar contactos';
$labels['importfromfile'] = 'Importar desde el archivo:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Reemplazar completamente la lista de contactos';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Hecho';

// settings
$labels['settingsfor'] = 'Configuración para';
$labels['about'] = 'About';
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
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Fecha detallada';
$labels['setdefault']  = 'Seleccionar opción por defecto';
$labels['autodetect']  = 'Automático';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Zona horaria';
$labels['pagesize']  = 'Filas por página';
$labels['signature'] = 'Firma';
$labels['dstactive']  = 'Cambio de horario';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Componer mensaje en HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
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
$labels['replysignaturepos'] = 'Agregar firma al responder o reenviar';
$labels['belowquote'] = 'debajo de la cita';
$labels['abovequote'] = 'sobre la cita';
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
