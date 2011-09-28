<?php

/*

+-----------------------------------------------------------------------+
| language/ca_ES/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
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
$messages['servererrormsg'] = 'Error del Servidor: $msg';
$messages['dberror'] = 'Error a la Base de Dades!';
$messages['errorreadonly'] = 'Impossible de realitzar l\'operació. La carpeta és de només lectura';
$messages['errornoperm'] = 'Impossible de realitzar l\'operació. Permís denegat';
$messages['invalidrequest'] = 'Petició no vàlida! No s\'ha desat les dades.';
$messages['nomessagesfound'] = 'No s\'han trobat missatges en aquesta bústia';
$messages['loggedout'] = 'Heu tancat la sessió. Fins aviat!';
$messages['mailboxempty'] = 'La bústia és buida';
$messages['loading'] = 'Carregant...';
$messages['uploading'] = 'Pujant fitxer...';
$messages['uploadingmany'] = 'Pujant fitxer...';
$messages['loadingdata'] = 'Carregant dades...';
$messages['checkingmail'] = 'Cercant nous missatges...';
$messages['sendingmessage'] = 'Enviant missatge...';
$messages['messagesent'] = 'Missatge enviat correctament';
$messages['savingmessage'] = 'Desant missatge...';
$messages['messagesaved'] = 'Missatge desat a Esborranys';
$messages['successfullysaved'] = 'Desat correctament';
$messages['addedsuccessfully'] = 'Contacte afegit correctament a la llibreta d\'adreces';
$messages['contactexists'] = 'Ja hi ha un contacte amb aquesta adreça de correu';
$messages['contactnameexists'] = 'Ja existeix un contacte amb el mateix nom.';
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
$messages['deletegroupconfirm'] = 'De veres voleu eliminar el grup seleccionat?';
$messages['deletemessagesconfirm'] = 'Esteu segurs de voler suprimir el(s) missatge(s) selecionat(s)?';
$messages['deletefolderconfirm'] = 'Esteu segurs de voler suprimir aquesta carpeta?';
$messages['purgefolderconfirm'] = 'Esteu segurs de voler suprimir tots els missatges d\'aquesta carpeta?';
$messages['groupdeleting'] = 'Eliminant el grup...';
$messages['folderdeleting'] = 'Suprimint la carpeta...';
$messages['foldermoving'] = 'Movent la carpeta...';
$messages['foldersubscribing'] = 'Subscribint la carpeta...';
$messages['folderunsubscribing'] = 'Desubscribint la carpeta...';
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
$messages['nosearchname'] = 'Introduïu un nom de contacte o una adreça e-mail';
$messages['notuploadedwarning'] = 'Encara no s\'han pujat tots els adjunts. Si us plau, espereu o cancel·leu la pujada.';
$messages['searchsuccessful'] = '$nr missatges trobats';
$messages['contactsearchsuccessful'] = '$nr contactes trobats';
$messages['searchnomatch'] = 'No s\'ha trobat cap coincidència';
$messages['searching'] = 'Cercant...';
$messages['checking'] = 'Comprovant...';
$messages['nospellerrors'] = 'No s\'han trobat errors d\'ortografia';
$messages['folderdeleted'] = 'Carpeta eliminada correctament';
$messages['foldersubscribed'] = 'Carpeta subscrita correctament';
$messages['folderunsubscribed'] = 'Carpeta desubscrita correctament';
$messages['folderpurged'] = 'La carpeta ha estat buidada satisfactòriament';
$messages['folderexpunged'] = 'La carpeta ha estat compactada satisfactòriament';
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
$messages['copyingcontact'] = 'Copiant contacte(s)...';
$messages['deletingmessage'] = 'Esborrant missatge(s)...';
$messages['markingmessage'] = 'Marcant missatge(s)...';
$messages['addingmember'] = 'Afegint contacte(s) al grup...';
$messages['removingmember'] = 'Eliminant contacte(s) del grup';
$messages['receiptsent'] = 'Confirmació de lectura enviada satisfactòriament';
$messages['errorsendingreceipt'] = 'No es pot enviar la confirmació';
$messages['nodeletelastidentity'] = 'No podeu suprimir aquesta identitat, és l\'última.';
$messages['forbiddencharacter'] = 'El nom de carpeta conté un caràcter prohibit';
$messages['selectimportfile'] = 'Si us plau, seleccioneu un fitxer per pujar';
$messages['addresswriterror'] = 'La llibreta d\'adreces seleccionada no és modificable';
$messages['contactaddedtogroup'] = 'S\'han afegit correctament els contactes a aquest grup';
$messages['contactremovedfromgroup'] = 'S\'han suprimit correctament els contactes d\'aquest grup';
$messages['importwait'] = 'S\'està important, espereu si us plau...';
$messages['importerror'] = 'La importació ha fallt! El fitxer pujat no és un fitxer vCard vàlid';
$messages['importconfirm'] = '<b>S\'han importat $inserted contactes satisfactòriament, s\'han descartat $skipped entrades ja existents</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Saltades $skipped entrades existents</b>';
$messages['opnotpermitted'] = 'Operació no permesa!';
$messages['nofromaddress'] = 'Falta l\'adreça d\'e-mail a la identitat seleccionada';
$messages['editorwarning'] = 'Si canvieu a l\'editor de text pla perdreu tot el format del text. Voleu continuar?';
$messages['httpreceivedencrypterror'] = 'Hi ha hagut un error fatal de configuració. Contacteu amb el vostre administrador immediatament. <b>El vostre missatge no pot ser enviat.</b>';
$messages['smtpconnerror'] = 'Error SMTP ($code): La connexió al servidor ha fallat.';
$messages['smtpautherror'] = 'Error SMTP ($code): La identificació ha fallat.';
$messages['smtpfromerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$from" com a remitent ($msg)';
$messages['smtptoerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$to" com a destinatari ($msg)';
$messages['smtprecipientserror'] = 'Error SMTP: No s\'ha pogut analitzar la lista de destinataris';
$messages['smtpdsnerror'] = 'Error SMTP: No es suporten les notificacions d\'estat de lliuraments';
$messages['smtperror'] = 'Error SMTP: $msg';
$messages['emailformaterror'] = 'Adreça d\'e-mail incorrecta: $email';
$messages['toomanyrecipients'] = 'Massa destinataris. Reduïu el nombre de destinataris a $max.';
$messages['maxgroupmembersreached'] = 'El nombre de membres del grup excedeix el màxim de $max.';
$messages['internalerror'] = 'Hi ha hagut un error intern. Si us plau torneu-ho a provar';
$messages['contactdelerror'] = 'No s\'ha pogut esborrar el(s) contacte(s)';
$messages['contactdeleted'] = 'Contacte(s) esborrat(s) satisfactòriament';
$messages['contactrestoreerror'] = 'No s\'ha pogut restaurar el(s) contacte(s) eliminat(s).';
$messages['contactrestored'] = 'Contacte(s) restaurats correctament.';
$messages['groupdeleted'] = 'Grup esborrat satisfactòriament';
$messages['grouprenamed'] = 'Grup renombrat satisfactòriament';
$messages['groupcreated'] = 'Grup creat satisfactòriament';
$messages['savedsearchdeleted'] = 'S\'ha eliminat correctament la cerca desada.';
$messages['savedsearchdeleteerror'] = 'No s\'ha pogut eliminar la cerca desada.';
$messages['savedsearchcreated'] = 'S\'ha creat correctament la cerca desada.';
$messages['savedsearchcreateerror'] = 'No s\'ha pogut crear la cerca desada.';
$messages['messagedeleted'] = 'Missatge(s) esborrat(s) satisfactòriament';
$messages['messagemoved'] = 'Missatge(s) mogut(s) satisfactòriament';
$messages['messagecopied'] = 'Missatge(s) copiat(s) satisfactòriament';
$messages['messagemarked'] = 'Missatge(s) marcat(s) satisfactòriament';
$messages['autocompletechars'] = 'Introdueix al menys $min caracters per a l\'autocompleció';
$messages['namecannotbeempty'] = 'El nom no pot estar buit';
$messages['nametoolong'] = 'El nom és massa llarg';
$messages['folderupdated'] = 'La carpeta ha estat actualitzada satisfactòriament';
$messages['foldercreated'] = 'La carpeta ha estat creada satisfactòriament';
$messages['invalidimageformat'] = 'No és un format d\'imatge vàlid.';
$messages['mispellingsfound'] = 'S\'han detectat errors d\'ortografia al misstage.';
$messages['parentnotwritable'] = 'No ha etsat possible crear/moure la carpeta dins de la carpeta ascendent seleccionada. No hi ha permisos d\'escriptura.';

?>
