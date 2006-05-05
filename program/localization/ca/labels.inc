<?php

/*
 +-----------------------------------------------------------------------+
 | language/ca/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Miguel Canteras i Cañizares <miguel@canteras.org>             |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Nom d\'usuari';
$labels['password']  = 'Contrasenya';
$labels['server']    = 'Servidor';
$labels['login']     = 'Entrar';

// taskbar
$labels['logout']   = 'Tancar sessió';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Configuració';
$labels['addressbook'] = 'Contactes';

// mailbox names
$labels['inbox']  = 'Entrada';
$labels['sent']   = 'Enviats';
$labels['trash']  = 'Paperera';
$labels['drafts'] = 'Esborranys';
$labels['junk']   = 'Correu brossa';

// message listing
$labels['subject'] = 'Assumpte';
$labels['from']    = 'Remitent';
$labels['to']      = 'Destinatari';
$labels['cc']      = 'CC';
$labels['bcc']     = 'BCC';
$labels['replyto'] = 'Respondre a';
$labels['date']    = 'Data';
$labels['size']    = 'Grandària';
$labels['priority'] = 'Prioritat';
$labels['organization'] = 'Organització';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Carpetes';
$labels['messagesfromto'] = 'Missatges des de $from a $to de $count';
$labels['messagenrof'] = 'Missatge $nr de $count';

$labels['moveto']   = 'moure a...';
$labels['download'] = 'descarregar';

$labels['filename'] = 'Nom del fitxer';
$labels['filesize'] = 'Grandaria de fitxer';

$labels['preferhtml'] = 'Preferisc HTML';
$labels['htmlmessage'] = 'Missatge HTML';
$labels['prettydate'] = 'Dates curtes';

$labels['addtoaddressbook'] = 'Afegir a contactes';

// weekdays short
$labels['sun'] = 'DM';
$labels['mon'] = 'DL';
$labels['tue'] = 'DM';
$labels['wed'] = 'DC';
$labels['thu'] = 'DJ';
$labels['fri'] = 'DV';
$labels['sat'] = 'DS';

// weekdays long
$labels['sunday']    = 'Diumenge';
$labels['monday']    = 'Dilluns';
$labels['tuesday']   = 'Dimarts';
$labels['wednesday'] = 'Dimecres';
$labels['thursday']  = 'Dijous';
$labels['friday']    = 'Divendres';
$labels['saturday']  = 'Dissabte';

$labels['today'] = 'Avui';

// toolbar buttons
$labels['writenewmessage']  = 'Crear nou missatge';
$labels['replytomessage']   = 'Respondre al missatge';
$labels['replytoallmessage'] = 'Respondre al remitent i a tots els destinataris';
$labels['forwardmessage']   = 'Reenviar missatge';
$labels['deletemessage']    = 'Moure missatge a la paperera';
$labels['printmessage']     = 'Imprimir aquest missatge';
$labels['previousmessages'] = 'Mostrar missatges anteriors';
$labels['nextmessages']     = 'Mostrar missatges següents';
$labels['backtolist']       = 'Tornar a la llista de missatges';
$labels['viewsource']       = 'Visualitza el codi font';

$labels['select'] = 'Seleccionar';
$labels['all'] = 'Tots';
$labels['none'] = 'Cap';
$labels['unread'] = 'No llegits';

$labels['compact'] = 'Compacta';
$labels['empty'] = 'Buida';
$labels['purge'] = 'Purga';

$labels['quota'] = 'Utilització de disc';


// message compose
$labels['compose']  = 'Escriure un missatge';
$labels['sendmessage']  = 'Enviar ara el missatge';
$labels['addattachment']  = 'Afegir un fitxer';
$labels['charset']  = 'Codificació de caràcters';

$labels['attachments'] = 'Adjunts';
$labels['upload'] = 'Afegir';
$labels['close']  = 'Cancel·lar';

$labels['low']     = 'Baixa';
$labels['lowest']  = 'Molt baixa';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alta';
$labels['highest'] = 'Molt alta';

$labels['nosubject']  = '(sense assumpte)';
$labels['showimages'] = 'Mostra imatges';


// address boook
$labels['name']      = 'Nom a mostrar';
$labels['firstname'] = 'Nom';
$labels['surname']   = 'Cognom';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Afegir nou contacte';
$labels['editcontact'] = 'Editar contacte';

$labels['edit']   = 'Editar';
$labels['cancel'] = 'Cancel·lar';
$labels['save']   = 'Desar';
$labels['delete'] = 'Suprimir';

$labels['newcontact']     = 'Crear nou contacte';
$labels['deletecontact']  = 'Suprimir contactes seleccionats';
$labels['composeto']      = 'Redactar correu per a';
$labels['contactsfromto'] = 'Contactes $from a $to de $count';
$labels['print']          = 'Imprimeix';
$labels['export']         = 'Exportar';

// LDAP search
$labels['ldapsearch'] = 'Cerca el directori LDAP';

$labels['ldappublicsearchname']    = 'Nom de contacte';
$labels['ldappublicsearchtype'] = 'Cerca exacta?';
$labels['ldappublicserverselect'] = 'Selecciona els servidors';
$labels['ldappublicsearchfield'] = 'Cerca activa';
$labels['ldappublicsearchform'] = 'Cerca un contacte';
$labels['ldappublicsearch'] = 'Cerca';


// settings
$labels['settingsfor']  = 'Configuració per a';

$labels['preferences']  = 'Preferències';
$labels['userpreferences']  = 'Preferències d\'usuari';
$labels['editpreferences']  = 'Editar preferències d\'usuari';

$labels['identities']  = 'Identitats';
$labels['manageidentities']  = 'Gestionar identitats per a aquest compte';
$labels['newidentity']  = 'Nova identitat';

$labels['newitem']  = 'Nou';
$labels['edititem']  = 'Editar';

$labels['setdefault']  = 'Seleccionar opció per defecte';
$labels['language']  = 'Idioma';
$labels['timezone']  = 'Zona horària';
$labels['pagesize']  = 'Files per pàgina';
$labels['signature'] = 'Signatura';


$labels['folder']  = 'Carpeta';
$labels['folders']  = 'Carpetes';
$labels['foldername']  = 'Nom de carpeta';
$labels['subscribed']  = 'Subscriure\'s';
$labels['create']  = 'Crear';
$labels['createfolder']  = 'Crear nova carpeta';
$labels['deletefolder']  = 'Suprimir carpeta';
$labels['managefolders']  = 'Gestionar carpetes';

$labels['sortby'] = 'Ordena per';
$labels['sortasc']  = 'Ordena ascendentment';
$labels['sortdesc'] = 'Ordena descendentment';

?>
