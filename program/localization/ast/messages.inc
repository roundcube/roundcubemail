<?php

/*

+-----------------------------------------------------------------------+
| language/ast/messages.inc                                             |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Mikel González <dangerous_piper@softastur.org>                |
|         http://blogs.altuxa.com/gyg                                   |
|         http://softastur.org                                          |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'La clave nun val';
$messages['cookiesdisabled'] = 'El to navegador nun acepta "cookies"';
$messages['sessionerror'] = 'La to sesión nun val';
$messages['imaperror'] = 'Fallu de conexón col sirvidor IMAP';
$messages['servererror'] = '¡Fallu del sirvidor!';
$messages['invalidrequest'] = 'Fallu de la solicitú. Nun se guardaron datos.';
$messages['nomessagesfound'] = 'Nun doi con mensaxes';
$messages['loggedout'] = 'Zarraste la sesión.';
$messages['mailboxempty'] = 'La to cuenta nun tien mensaxes';
$messages['loading'] = 'Cargando...';
$messages['loadingdata'] = 'Cargando datos...';
$messages['checkingmail'] = 'A la gueta de mensaxes nuevos...';
$messages['sendingmessage'] = 'Unviando mensaxe...';
$messages['messagesent'] = 'Unviose\'l mensaxe';
$messages['savingmessage'] = 'Guardar mensaxe...';
$messages['messagesaved'] = 'El mensaxe guardóse en borradores';
$messages['successfullysaved'] = 'Guardose correchamente';
$messages['addedsuccessfully'] = 'El contautu amestose correchamente a la llibreta de direiciones';
$messages['contactexists'] = 'Yá hai un contuaut con es direición de corréu';
$messages['blockedimages'] = 'Pa protexer la to privacidá, torgáronse les imáxenes esternes n\'esti mensaxe';
$messages['encryptedmessage'] = 'Esti ye un mensaxe cifráu y nun soi a amosalu.';
$messages['nocontactsfound'] = 'Nun doi con nengún contautu';
$messages['contactnotfound'] = 'Nun doi col contautu que pides';
$messages['sendingfailed'] = 'Falló l\'unvio del mensaxe';
$messages['senttooquickly'] = 'Por favor espera $sec segundu/os enantes d\'unviar esti mensaxe.';
$messages['errorsavingsent'] = 'Di con un fallu al guardar el mensaxe';
$messages['errorsaving'] = 'Falló al guardase';
$messages['errormoving'] = 'Nun fui a mover el mensaxe';
$messages['errordeleting'] = 'Nun fui a desaniciar el mensaxe';
$messages['errormarking'] = 'Nun se puede marcar el mensaxe';
$messages['deletecontactconfirm'] = '¿Tas seguru de desaniciar los contautos que marcaste?';
$messages['deletemessagesconfirm'] = '¿Tas seguru de desaniciar los mensaxes que marcaste?';
$messages['deletefolderconfirm'] = '¿Tas seguru de desaniciar esta bandexa?';
$messages['purgefolderconfirm'] = '¿Tas seguru de desaniciar tolos mensaxes d\'esta bandexa?';
$messages['foldercreating'] = 'Faciendo la bandexa...';
$messages['folderdeleting'] = 'Desaniciando la bandexa...';
$messages['folderrenaming'] = 'Renomando la bandexa...';
$messages['foldermoving'] = 'Moviendo la bandexa...';
$messages['formincomplete'] = 'Tienes de rellenar tolos campos del formulariu';
$messages['noemailwarning'] = 'Por favor, dame un corréu válidu';
$messages['nonamewarning'] = 'Por favor, dame\'l to nome';
$messages['nopagesizewarning'] = 'Por favor, dame un tamañu de páxina';
$messages['nosenderwarning'] = 'Por favor dame un corréu dende donde unviar mesaxes';
$messages['norecipientwarning'] = 'Por favor, dame polo menos un destinatariu';
$messages['nosubjectwarning'] = 'El campo "Asuntu" ta vacíu. ¿Quiés rellenalu anguaño?';
$messages['nobodywarning'] = '¿Quies unviar esti mensaxe ensin testu?';
$messages['notsentwarning'] = 'Nun unvie\'l mensaxe. ¿Quies desanicialu?';
$messages['noldapserver'] = 'Por favor, escueye un sirvidor LDAP pa buscar';
$messages['nocontactsreturned'] = 'Nun di con nengún contautu';
$messages['nosearchname'] = 'Por favor, dame un nome o una direición de corréu';
$messages['searchsuccessful'] = 'Di con $nr mensaxes';
$messages['searchnomatch'] = 'Nun di con nengún resultáu';
$messages['searching'] = 'A la gueta...';
$messages['checking'] = 'Comprobando...';
$messages['nospellerrors'] = 'Nun di con errores ortográficos';
$messages['folderdeleted'] = 'Desaniciose la bandexa correchamente';
$messages['deletedsuccessfully'] = 'Desaniciose correchamente';
$messages['converting'] = 'Desaniciando\'l formatu del mensaxe...';
$messages['messageopenerror'] = 'Nun soi a baxer el mensaxe del sirvidor';
$messages['fileuploaderror'] = 'Fallu al xubir ficheros';
$messages['filesizeerror'] = 'El ficheru ye más grande de lo permitío ($size)';
$messages['copysuccess'] = 'Copiaronse $nr direiciones correchamente';
$messages['copyerror'] = 'Nun fui a copiar nenguna direición';
$messages['sourceisreadonly'] = 'Esta direición ye de namái-llectura';
$messages['errorsavingcontact'] = 'Nun fui a guardar la direición del contautu';
$messages['movingmessage'] = 'Moviendo\'l mensaxe...';
$messages['receiptsent'] = 'Unviose correchamente l\'avisu de llectura.';
$messages['errorsendingreceipt'] = 'Nun fui a unviar l\'avisu de llectura.';
$messages['nodeletelastidentity'] = 'Nun puedo desaniciar esa identidá porque ye la última.';
$messages['addsubfolderhint'] = 'Esta bandexa va ser subandexa de la que ta marcada';
$messages['forbiddencharacter'] = 'El nome de la bandexa tien un caráuter que nun val';
$messages['selectimportfile'] = 'Por favor escueye el ficheru que quies xubir';
$messages['addresswriterror'] = 'Nun puedo escribir nel llibru de direiciones qu\'escoyisti';
$messages['importwait'] = 'Importando, dame un minutín...';
$messages['importerror'] = 'Nun fui a importar. El ficheru qu\'escoyiste nun ye un ficheru vCard válidu.';
$messages['importconfirm'] = '<b>Importaronse correchamente $inserted contautos, saltáronse $skipped entrase que yá esistíen</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Nun tienes permisu pa facelo.';
$messages['nofromaddress'] = 'Perdiose la direición de corréu de la identidá qu\'escoyisti';
$messages['editorwarning'] = 'Si pases a editor en modu testu vas perder tol estilu aplicáu al mensaxe. ¿Tas seguru de que quies facelo?';
$messages['httpreceivedencrypterror'] = 'Hai un fallu garrafal de configuración. Contauta col alministrador de secute. <b>Nun se pude unviar el mesaxe</b>';
$messages['smtpconnerror'] = 'Fallu SMTP ($code): Fallu de conexón col sirvidor';
$messages['smtpautherror'] = 'Fallu SMTP ($code): Fallu d\'autorización';
$messages['smtpfromerror'] = 'Fallu SMTP ($code): Fallu al aficar el remitente "$from"';
$messages['smtptoerror'] = 'Fallu SMTP ($code): Fallu al amestar el destinatariu "$to"';
$messages['smtprecipientserror'] = 'Fallu SMTP: Nun soi a amosar la llista de destinatarios';
$messages['smtperror'] = 'Fallu SMTP: $msg';

?>
