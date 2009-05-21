<?php

/*

+-----------------------------------------------------------------------+
| language/ast/labels.inc                                             |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Mikel González <dangerous_piper@softastur.org>                |
|         http://blogs.altuxa.com/gyg                                   |
|         http://softastur.org                                          |
+-----------------------------------------------------------------------+

 @version $Id: labels.inc 2124 2009-02-02 03:55:53Z mikelg $

*/

$labels = array();

// login page
$labels['welcome'] = 'Bienllegáu a $product';
$labels['username'] = 'Usuariu';
$labels['password'] = 'Clave';
$labels['server'] = 'Sirvidor';
$labels['login'] = 'Entrar';

// taskbar
$labels['logout'] = 'Zarrar';
$labels['mail'] = 'Corréu';
$labels['settings'] = 'Preferencies personales';
$labels['addressbook'] = 'Llibru de direiciones';

// mailbox names
$labels['inbox'] = 'Entrantes';
$labels['drafts'] = 'Borradores';
$labels['sent'] = 'Unviaos';
$labels['trash'] = 'Papelera';
$labels['junk'] = 'Puxarra';

// message listing
$labels['subject'] = 'Asuntu';
$labels['from'] = 'De';
$labels['to'] = 'Pa';
$labels['cc'] = 'Copia';
$labels['bcc'] = 'Bcc';
$labels['replyto'] = 'Contestar a';
$labels['date'] = 'Fecha';
$labels['size'] = 'Tamañu';
$labels['priority'] = 'Prioridá';
$labels['organization'] = 'Organización';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Carpetes';
$labels['messagesfromto'] = 'Mensaxes del $from a $to de $count';
$labels['messagenrof'] = 'Mensaxe $nr de $count';

$labels['moveto'] = 'mover pa...';
$labels['download'] = 'baxar';

$labels['filename'] = 'Nome del Ficheru';
$labels['filesize'] = 'Tamañu del ficheru';

$labels['preferhtml'] = 'Amosar HTML';
$labels['htmlmessage'] = 'Mensaxe HTML';
$labels['prettydate'] = 'Fecha prestosa';

$labels['addtoaddressbook'] = 'Amestar al llibru de direiciones';

// weekdays short
$labels['sun'] = 'Dom';
$labels['mon'] = 'Llu';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mie';
$labels['thu'] = 'Xue';
$labels['fri'] = 'Vie';
$labels['sat'] = 'Sab';

// weekdays long
$labels['sunday'] = 'Domingu';
$labels['monday'] = 'Llunes';
$labels['tuesday'] = 'Martes';
$labels['wednesday'] = 'Miércoles';
$labels['thursday'] = 'Xueves';
$labels['friday'] = 'Vienres';
$labels['saturday'] = 'Sábadu';

// months short
$labels['jan'] = 'Xin';
$labels['feb'] = 'Feb';
$labels['mar'] = 'Mar';
$labels['apr'] = 'Abr';
$labels['may'] = 'May';
$labels['jun'] = 'Xun';
$labels['jul'] = 'Xnt';
$labels['aug'] = 'Ago';
$labels['sep'] = 'Set';
$labels['oct'] = 'Och';
$labels['nov'] = 'Pay';
$labels['dec'] = 'Avi';

// months long
$labels['longjan'] = 'Xineru';
$labels['longfeb'] = 'Febreru';
$labels['longmar'] = 'Marzu';
$labels['longapr'] = 'Abril';
$labels['longmay'] = 'Mayu';
$labels['longjun'] = 'Xunu';
$labels['longjul'] = 'Xunetu';
$labels['longaug'] = 'Agostu';
$labels['longsep'] = 'Setiembre';
$labels['longoct'] = 'Ochobre';
$labels['longnov'] = 'Payares';
$labels['longdec'] = 'Avientu';

$labels['today'] = 'Güei';

// toolbar buttons
$labels['checkmail'] = 'Buscar mensaxes nuevos';
$labels['writenewmessage'] = 'Facer un mensaxe nuevu';
$labels['replytomessage'] = 'Contestar al mensaxe';
$labels['replytoallmessage'] = 'Contestar a toos';
$labels['forwardmessage'] = 'Reunviar mensaxe';
$labels['deletemessage'] = 'Desaniciar mensaxe';
$labels['movemessagetotrash'] = 'Mover mensaxe a la papelera';
$labels['printmessage'] = 'Imprentar esti mensaxe';
$labels['previousmessage'] = 'Amosar el mensaxe anterior';
$labels['previousmessages'] = 'Amosar grupu de mensaxes anterior';
$labels['firstmessage'] = 'Amosar primer mensaxe';
$labels['firstmessages'] = 'Amosar primer grupu de mensaxes';
$labels['nextmessage'] = 'Amosar el mensaxe siguiente';
$labels['nextmessages'] = 'Amosar grupu de mensaxes que siguientes';
$labels['lastmessage'] = 'Amosar l\'últimu mensaxe';
$labels['lastmessages'] = 'Amosar l\'últimu grupu de mensaxes';
$labels['backtolist'] = 'Tornar a la llista de mensaxes';
$labels['viewsource'] = 'Amosar el códigu';
$labels['markmessages'] = 'Marcar mensaxes';
$labels['markread'] = 'Como lleíu';
$labels['markunread'] = 'Como non lleíu';
$labels['markflagged'] = 'Como marcáu';
$labels['markunflagged'] = 'Como non marcáu';

$labels['select'] = 'Escueye';
$labels['all'] = 'Toos';
$labels['none'] = 'Nengún';
$labels['unread'] = 'Ensin lleer';
$labels['flagged'] = 'Marcáu';
$labels['unanswered'] = 'Ensin contestar';
$labels['filter'] = 'Filtru';

$labels['compact'] = 'Compautar';
$labels['empty'] = 'Vaciar';
$labels['purge'] = 'Desaniciar';

$labels['quota'] = 'Espaciu en discu';
$labels['unknown'] = 'desconocíu';
$labels['unlimited'] = 'ensin llímite';

$labels['quicksearch'] = 'Búsqueda rápida';
$labels['resetsearch'] = 'Anovar la búsqueda';

$labels['openinextwin'] = 'Abrir en una ventana nueva';

// message compose
$labels['compose'] = 'Escribir un mensaxe';
$labels['savemessage'] = 'Guardar como borrador';
$labels['sendmessage'] = 'Unviar el mensaxe yá';
$labels['addattachment'] = 'Amestar un archivu';
$labels['charset'] = 'Codificación';
$labels['editortype'] = 'Tipo d\'editor';
$labels['returnreceipt'] = 'Avisu de recibu';

$labels['checkspelling'] = 'Revisar ortografía';
$labels['resumeediting'] = 'Siguir cola edición';
$labels['revertto'] = 'Desfacer a';

$labels['attachments'] = 'Axuntos';
$labels['upload'] = 'Xubir';
$labels['close'] = 'Zarrar';

$labels['low'] = 'Baxu';
$labels['lowest'] = 'Mui baxu';
$labels['normal'] = 'Normal';
$labels['high'] = 'Altu';
$labels['highest'] = 'Mui altu';

$labels['nosubject'] = '(ensin asuntu)';
$labels['showimages'] = 'Amosar imáxenes';
$labels['alwaysshow'] = 'Amosar siempre les imáxenes de $sender';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Testu';
$labels['savesentmessagein'] = 'Guardar mensaxe unviau en';
$labels['dontsave'] = 'Desanicialu';
$labels['maxuploadsize'] = 'El tamañu másimu pa un ficheru ye $size';

$labels['addcc'] = 'Amestar Cc';
$labels['addbcc'] = 'Amestar Bcc';
$labels['addreplyto'] = 'Amestar Rempuesta pa';

// mdn
$labels['mdnrequest'] = 'El qu\'unvia esti mensaxe quier que lo avises cuando lleas el corréu. ¿Quies avisalo?';
$labels['receiptread'] = 'Avisu de llectura';
$labels['yourmessage'] = 'Esto ye un avisu de llectura del to mensaxe';
$labels['receiptnote'] = 'Nota: Esti avisu namás quier dicir que\'l to mensaxe amosośe nel equipu del receptor. Nun hai forma de garantizate que lo lleera o que pescanciara\'l to mensaxe.';

// address boook
$labels['name'] = 'Nome completu';
$labels['firstname'] = 'Nome';
$labels['surname'] = 'Apellíu';
$labels['email'] = 'Corréu';

$labels['addcontact'] = 'Amestar contautu nuevu';
$labels['editcontact'] = 'Editar contautu';

$labels['edit'] = 'Editar';
$labels['cancel'] = 'Encaboxar';
$labels['save'] = 'Guardar';
$labels['delete'] = 'Desaniciar';

$labels['newcontact'] = 'Facer un contautu nuevu';
$labels['deletecontact'] = 'Desaniciar los contautos marcaos';
$labels['composeto'] = 'Unviar mensaxe a';
$labels['contactsfromto'] = 'Contautos $from a $to de $count';
$labels['print'] = 'Imprentar';
$labels['export'] = 'Esportar';
$labels['exportvcards']   = 'Esportar contautos en formatu vCard';

$labels['previouspage'] = 'Amosar grupu anterior';
$labels['firstpage'] = 'Amosar primer grupu';
$labels['nextpage'] = 'Amosar siguiente grupu';
$labels['lastpage'] = 'Amosar l\'últimu grupu';

$labels['groups'] = 'Grupos';
$labels['personaladrbook'] = 'Direiciones personales';

$labels['import'] = 'Importar';
$labels['importcontacts'] = 'Importar contautos';
$labels['importfromfile'] = 'Importar dende un ficheru:';
$labels['importreplace'] = 'Trocar el llibru de direiciones enteru';
$labels['importtext'] = 'Puedes xubir direcione d\'un llibr que yá tengas.<br/>Anguaño puedes importar direiciones en formatu <a href="http://ast.wikipedia.org/wiki/VCard">vCard</a>.';
$labels['done'] = 'Fecho';

// settings
$labels['settingsfor'] = 'Configuración pa';

$labels['preferences'] = 'Preferencies';
$labels['userpreferences'] = 'Preferencies d\'usuariu';
$labels['editpreferences'] = 'Editar preferencies d\'usuariu';

$labels['identities'] = 'Identidaes';
$labels['manageidentities'] = 'Remanar identidaes pa esta cuenta';
$labels['newidentity'] = 'Identidad nueva';

$labels['newitem'] = 'Nuevu';
$labels['edititem'] = 'Editar';

$labels['setdefault'] = 'Escoyer opción preferída';
$labels['autodetect']  = 'Escoyer automáticamente';
$labels['language'] = 'Idioma';
$labels['timezone'] = 'Franxa horaria';
$labels['pagesize'] = 'Fileres per páxina';
$labels['signature'] = 'Firma';
$labels['dstactive'] = 'Cambéu horariu';
$labels['htmleditor'] = 'Facer el mensaxe n\'HTML';
$labels['htmlsignature'] = 'Firma HTML';
$labels['previewpane'] = 'Entever';
$labels['skin'] = 'Estilu';
$labels['logoutclear'] = 'Vaciar la papelera al zarrar la sesión';
$labels['logoutcompact'] = 'Compautar la bandexa d\'entrada al zarrar la sesión';
$labels['uisettings'] = 'Interfaz d\'usuariu';
$labels['serversettings'] = 'Configuración del sirvidor';
$labels['mailboxview'] = 'Vista del buzón';
$labels['mdnrequests'] = 'Avisos d\'unvios';
$labels['askuser'] = 'preguntar al usuariu';
$labels['autosend'] = 'unviar de secute';
$labels['ignore'] = 'ignoralos';
$labels['readwhendeleted'] = 'Marcar el mensaxe como lleíu al desanicialu';
$labels['flagfordeletion'] = 'Marcar el mensaxe pa desaniciase en cuenta de desanicialu dafechu';
$labels['skipdeleted'] = 'Nun amosar los mensaxes desaniciaos';
$labels['showremoteimages'] = 'Amosar imáxenes remotes';
$labels['fromknownsenders'] = 'de contautos conocíos';
$labels['always'] = 'siempre';
$labels['showinlineimages'] = 'Amosar imáxenes axuntes embaxu\'l mensaxe';
$labels['autosavedraft'] = 'Guardar borrador de secute';
$labels['everynminutes']  = 'cada $n minutu/os';
$labels['keepalive']  = 'Guetar mensaxes nuevos cada';
$labels['never'] = 'nunca';
$labels['messagesdisplaying'] = 'Vista de mensaxes';
$labels['messagescomposition'] = 'Edición de mensaxes';
$labels['mimeparamfolding'] = 'Nomes d\'axuntos';
$labels['2231folding'] = 'Too RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Too RFC 2047 (otros)';
$labels['advancedoptions'] = 'Opciones avanzaes';
$labels['focusonnewmessage'] = 'Poner el focu nos mensaxes nuevos';
$labels['checkallfolders'] = 'Guetar mensaxes nuevos en toles bandexes';

$labels['folder'] = 'Bandexa';
$labels['folders'] = 'Bandexes';
$labels['foldername'] = 'Nome de bandexa';
$labels['subscribed'] = 'Soscrita';
$labels['messagecount'] = 'Mensaxes';
$labels['create'] = 'Facer';
$labels['createfolder'] = 'Facer una bandexa nueva';
$labels['rename'] = 'Renomar';
$labels['renamefolder'] = 'Renomar bandexa';
$labels['deletefolder'] = 'Desaniciar bandexa';
$labels['managefolders'] = 'Alministrar bandexes';
$labels['specialfolders'] = 'Bandexes especiales';

$labels['sortby'] = 'Ordenar por';
$labels['sortasc'] = 'Orden ascendente';
$labels['sortdesc'] = 'Orden descendente';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

//personalizau
$labels['anu'] = date(Y);
?>
