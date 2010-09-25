<?php

/*

+-----------------------------------------------------------------------+
| language/ca_ES/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Miguel Canteras i Cañizares <miguel@canteras.org>             |
|         Simo <sim6@graciasensefils.net>                               |
|         Jordi Sanfeliu <jordi@fibranet.cat>                           |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Contrasenya incorrecta';
$messages['cookiesdisabled'] = 'El vostre navegador no accepta cookies';
$messages['sessionerror'] = 'La vostra sessió no existeix o ha expirat';
$messages['imaperror'] = 'Error de conexió amb el servidor IMAP';
$messages['servererror'] = 'Error del Servidor!';
$messages['invalidrequest'] = 'Petició no vàlida! No s\'ha desat les dades.';
$messages['nomessagesfound'] = 'No s\'han trobat missatges en aquesta bústia';
$messages['loggedout'] = 'Heu tancat la sessió. Fins aviat!';
$messages['mailboxempty'] = 'La bústia és buida';
$messages['loading'] = 'Carregant...';
$messages['uploading'] = 'Pujant fitxer...';
$messages['loadingdata'] = 'Carregant dades...';
$messages['checkingmail'] = 'Cercant nous missatges...';
$messages['sendingmessage'] = 'Enviant missatge...';
$messages['messagesent'] = 'Missatge enviat correctament';
$messages['savingmessage'] = 'Desant missatge...';
$messages['messagesaved'] = 'Missatge desat a Esborranys';
$messages['successfullysaved'] = 'Desat correctament';
$messages['addedsuccessfully'] = 'Contacte afegit correctament a la llibreta d\'adreces';
$messages['contactexists'] = 'Ja hi ha un contacte amb aquesta adreça de correu';
$messages['blockedimages'] = 'Per a protegir la vostra privacitat, les imatges remotes han estat bloquejades en aquest missatge';
$messages['encryptedmessage'] = 'Aquest és un missatge xifrat i no pot ser mostrat. Ho sento!';
$messages['nocontactsfound'] = 'No hi ha contactes';
$messages['contactnotfound'] = 'No s\'ha trobat el contacte sol·licitat';
$messages['sendingfailed'] = 'Error enviant missatge';
$messages['senttooquickly'] = 'Si us plau, espereu $sec segon(s) abans d\'enviar aquest missatge';
$messages['errorsavingsent'] = 'Hi ha hagut un error mentre es desava el missatge enviat';
$messages['errorsaving'] = 'Hi ha hagut un error mentre es desava';
$messages['errormoving'] = 'No s\'ha pogut moure el missatge';
$messages['errorcopying'] = 'No s\'ha pogut copiar el(s) missatge(s)';
$messages['errordeleting'] = 'No s\'ha pogut eliminar el missatge';
$messages['errormarking'] = 'No s\'ha pogut marcar el missatge.';
$messages['deletecontactconfirm'] = 'Esteu segurs de voler suprimir el(s) contacte(s) selecionat(s)?';
$messages['deletemessagesconfirm'] = 'Esteu segurs de voler suprimir el(s) missatge(s) selecionat(s)?';
$messages['deletefolderconfirm'] = 'Esteu segurs de voler suprimir aquesta carpeta?';
$messages['purgefolderconfirm'] = 'Esteu segurs de voler suprimir tots els missatges d\'aquesta carpeta?';
$messages['foldercreating'] = 'Creant la carpeta...';
$messages['folderdeleting'] = 'Suprimint la carpeta...';
$messages['folderrenaming'] = 'Reanomenant la carpeta...';
$messages['foldermoving'] = 'Movent la carpeta...';
$messages['formincomplete'] = 'El formulari no ha sigut emplenat totalment';
$messages['noemailwarning'] = 'Introduïu una adreça d\'e-mail vàlida';
$messages['nonamewarning'] = 'Introduïu un nom';
$messages['nopagesizewarning'] = 'Introduïu una mida de pàgina';
$messages['nosenderwarning'] = 'Si us plau, entreu l\'adreça d\'e-mail del remitent';
$messages['norecipientwarning'] = 'Introduïu com a mínim un destinatari';
$messages['nosubjectwarning'] = 'El camp \"Assumpte\" és buit. Voleu introduir-ne un ara?';
$messages['nobodywarning'] = 'Voleu enviar aquest missatge sense text?';
$messages['notsentwarning'] = 'El missatge no s\'ha enviat. Voleu descartar el vostre missatge?';
$messages['noldapserver'] = 'Seleccioneu un servidor LDAP per a cercar';
$messages['nocontactsreturned'] = 'No s\'han trobat contactes';
$messages['nosearchname'] = 'Introduïu un nom de contacte o una adreça e-mail';
$messages['notuploadedwarning'] = 'Encara no s\'han pujat tots els adjunts. Si us plau, espereu o cancel·leu la pujada.';
$messages['searchsuccessful'] = '$nr missatges trobats';
$messages['searchnomatch'] = 'No s\'ha trobat cap coincidència';
$messages['searching'] = 'Cercant...';
$messages['checking'] = 'Comprovant...';
$messages['nospellerrors'] = 'No s\'han trobat errors d\'ortografia';
$messages['folderdeleted'] = 'Carpeta eliminada correctament';
$messages['deletedsuccessfully'] = 'Eliminat correctament';
$messages['converting'] = 'Traient format del missatge...';
$messages['messageopenerror'] = 'No s\'ha pogut carregar el missatge del servidor';
$messages['fileuploaderror'] = 'La pujada del fitxer ha fallat';
$messages['filesizeerror'] = 'El fitxer pujat excedeix la grandària màxima de $size';
$messages['copysuccess'] = 'S\'han copiat $nr adreces correctament';
$messages['copyerror'] = 'No s\'ha pogut copiar cap adreça';
$messages['sourceisreadonly'] = 'Aquesta adreça és només de lectura';
$messages['errorsavingcontact'] = 'No s\'ha pogut desar l\'adreça de contacte';
$messages['movingmessage'] = 'Movent el missatge...';
$messages['copyingmessage'] = 'Copiant missatge...';
$messages['receiptsent'] = 'Confirmació de lectura enviada satisfactòriament';
$messages['errorsendingreceipt'] = 'No es pot enviar la confirmació';
$messages['nodeletelastidentity'] = 'No podeu suprimir aquesta identitat, és l\'última.';
$messages['addsubfolderhint'] = 'Aquesta carpeta serà creada com a subcarpeta de la seleccionada actualment';
$messages['forbiddencharacter'] = 'El nom de carpeta conté un caràcter prohibit';
$messages['selectimportfile'] = 'Si us plau, seleccioneu un fitxer per pujar';
$messages['addresswriterror'] = 'La llibreta d\'adreces seleccionada no és modificable';
$messages['contactaddedtogroup'] = 'S\'han afegit correctament els contactes a aquest grup';
$messages['contactremovedfromgroup'] = 'S\'han suprimit correctament els contactes d\'aquest grup';
$messages['importwait'] = 'S\'està important, espereu si us plau...';
$messages['importerror'] = 'La importació ha fallt! El fitxer pujat no és un fitxer vCard vàlid';
$messages['importconfirm'] = '<b>S\'han importat $inserted contactes satisfactòriament, s\'han descartat $skipped entrades ja existents</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operació no permesa!';
$messages['nofromaddress'] = 'Falta l\'adreça d\'e-mail a la identitat seleccionada';
$messages['editorwarning'] = 'Si canvieu a l\'editor de text pla perdreu tot el format del text. Voleu continuar?';
$messages['httpreceivedencrypterror'] = 'Hi ha hagut un error fatal de configuració. Contacteu amb el vostre administrador immediatament. <b>El vostre missatge no pot ser enviat.</b>';
$messages['smtpconnerror'] = 'Error SMTP ($code): La connexió al servidor ha fallat.';
$messages['smtpautherror'] = 'Error SMTP ($code): La identificació ha fallat.';
$messages['smtpfromerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$from" com a remitent ($msg)';
$messages['smtptoerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$to" com a destinatari ($msg)';
$messages['smtprecipientserror'] = 'Error SMTP: No s\'ha pogut analitzar la lista de destinataris';
$messages['smtperror'] = 'Error SMTP: $msg';
$messages['emailformaterror'] = 'Adreça d\'e-mail incorrecta: $email';
$messages['toomanyrecipients'] = 'Massa destinataris. Reduïu el nombre de destinataris a $max.';
$messages['maxgroupmembersreached'] = 'El nombre de membres del grup excedeix el màxim de $max.';
$messages['internalerror'] = 'Hi ha hagut un error intern. Si us plau torneu-ho a provar';

?>
