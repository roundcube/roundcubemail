<?php

/*

+-----------------------------------------------------------------------+
| language/ca_ES/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Miguel Canteras i Cañizares <miguel@canteras.org>             |
|         Simo <sim6@graciasensefils.net>                               |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Contrasenya incorrecta';
$messages['cookiesdisabled'] = 'El seu navegador no accepta cookies';
$messages['sessionerror'] = 'La seva sessió no existeix o ha expirat';
$messages['imaperror'] = 'Error de conexió amb el servidor IMAP';
$messages['servererror'] = 'Error del Servidor!';
$messages['invalidrequest'] = 'Petició invàlida! No s\'ha desat les dades.';
$messages['nomessagesfound'] = 'No s\'han trobat missatges en aquesta bústia';
$messages['loggedout'] = 'Ha tancat la sessió. Fins aviat!';
$messages['mailboxempty'] = 'La bústia està buida';
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
$messages['blockedimages'] = 'Per a protegir la seva privacitat, les imatges remotes han sigut bloquejades en aquest missatge';
$messages['encryptedmessage'] = 'Aquest és un missatge xifrat i no pot ser mostrat. Ho sento!';
$messages['nocontactsfound'] = 'No hi ha contactes';
$messages['contactnotfound'] = 'No s\'ha trobat el contacte sol·licitat';
$messages['sendingfailed'] = 'Error enviant missatge';
$messages['senttooquickly'] = 'Si us plau, espereu $sec segon(s) abans d\'enviar aquest missatge';
$messages['errorsavingsent'] = 'Va ocòrrer un error mentre es desava el missatge enviat';
$messages['errorsaving'] = 'Va ocòrrer un error mentre es desava';
$messages['errormoving'] = 'No s\'ha pogut moure el missatge';
$messages['errorcopying'] = 'No s\'ha pogut copiar el(s) missatge(s)';
$messages['errordeleting'] = 'No s\'ha pogut eliminar el missatge';
$messages['errormarking'] = 'No s\'ha pogut marcar el missatge.';
$messages['deletecontactconfirm'] = 'Realment vol suprimir el(s) contacte(s) selecionat(s)?';
$messages['deletemessagesconfirm'] = 'Realment vol suprimir el(s) missatge(s) selecionat(s)?';
$messages['deletefolderconfirm'] = 'Realment vol suprimir aquesta carpeta?';
$messages['purgefolderconfirm'] = 'Realment vol suprimir tots els missatges d\'aquesta carpeta?';
$messages['foldercreating'] = 'Creant la carpeta...';
$messages['folderdeleting'] = 'Eliminant la carpeta...';
$messages['folderrenaming'] = 'Reanomenant la carpeta...';
$messages['foldermoving'] = 'Movent la carpeta...';
$messages['formincomplete'] = 'El formulari no ha sigut emplenat totalment';
$messages['noemailwarning'] = 'Introdueixi una adreça d\'e-mail vàlida';
$messages['nonamewarning'] = 'Introdueixi un nom';
$messages['nopagesizewarning'] = 'Introdueixi un tamany de pàgina';
$messages['nosenderwarning'] = 'Si us plau, entreu l\'adreça d\'e-mail del remitent';
$messages['norecipientwarning'] = 'Introdueixi al menys un destinatari';
$messages['nosubjectwarning'] = 'El camp \"Assumpte\" està buit. Vol introduir-ne un ara?';
$messages['nobodywarning'] = 'Vol enviar aquest missatge sense text?';
$messages['notsentwarning'] = 'El missatge no s\'ha enviat. Vol descartar el seu missatge?';
$messages['noldapserver'] = 'Seleccioni un servidor LDAP per a cercar';
$messages['nocontactsreturned'] = 'No s\'han trobat contactes';
$messages['nosearchname'] = 'Introdueixi un nom de contacte o una adreça e-mail';
$messages['notuploadedwarning'] = 'Encara no s\'han pujat tots els adjunts. Si us plau, espera o cancel·la la pujada.';
$messages['searchsuccessful'] = '$nr missatges trobats';
$messages['searchnomatch'] = 'No s\'ha trobat cap coincidència';
$messages['searching'] = 'Cercant...';
$messages['checking'] = 'Comprovant...';
$messages['nospellerrors'] = 'No s\'han trobat errors d\'ortografia';
$messages['folderdeleted'] = 'Carpeta eliminada correctament';
$messages['deletedsuccessfully'] = 'Eliminat correctament';
$messages['converting'] = 'Traient format del missatge...';
$messages['messageopenerror'] = 'No s\'ha pogut carregar el missatge del servidor';
$messages['fileuploaderror'] = 'Pujada de fitxer fallida';
$messages['filesizeerror'] = 'El fitxer pujat excedeix la grandària màxima de $size';
$messages['copysuccess'] = 'S\'han copiat $nr adreces correctament';
$messages['copyerror'] = 'No s\'ha pogut copiar cap adreça';
$messages['sourceisreadonly'] = 'Aquesta adreça és només de lectura';
$messages['errorsavingcontact'] = 'No s\'ha pogut guardar l\'adreça de contacte';
$messages['movingmessage'] = 'Movent el missatge...';
$messages['copyingmessage'] = 'Copiant missatge...';
$messages['receiptsent'] = 'Confirmació de lectura enviada satisfactòriament';
$messages['errorsendingreceipt'] = 'No es pot enviar la confirmació';
$messages['nodeletelastidentity'] = 'No pots eliminar aquesta identitat, és l\'última.';
$messages['addsubfolderhint'] = 'Aquesta carpeta serà creada com a subcarpeta de la seleccionada actualment';
$messages['forbiddencharacter'] = 'El nom de carpeta conté un caràcter prohibit';
$messages['selectimportfile'] = 'Si us plau, seleccioneu un fitxer per pujar';
$messages['addresswriterror'] = 'La llibreta d\'adreces seleccionada no és modificable';
$messages['contactaddedtogroup'] = 'S\'han afegit correctament els contactes a aquest grup';
$messages['contactremovedfromgroup'] = 'S\'han esborrat correctament els contactes d\'aquest grup';
$messages['importwait'] = 'Important, espereu si us plau...';
$messages['importerror'] = 'Importació fallida! El fitxer pujat no és un fitxer vCard vàlid';
$messages['importconfirm'] = '<b>S\'han importat $inserted contactes satisfactòriament, s\'han descartat $skipped entrades ja existents</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operació no permesa!';
$messages['nofromaddress'] = 'Falta l\'adreça d\'e-mail a la identitat seleccionada';
$messages['editorwarning'] = 'Si canvieu a l\'editor de text pla perdreu tot el format del text. Voleu continuar?';
$messages['httpreceivedencrypterror'] = 'Ha ocorregut un error fatal de configuració. Contacta amb el teu administrador inmediatament. <b>El teu missatge no pot ser enviat.</b>';
$messages['smtpconnerror'] = 'Error SMTP ($code): La connexió al servidor ha fallat.';
$messages['smtpautherror'] = 'Error SMTP ($code): Identificació fallida.';
$messages['smtpfromerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$from" com a remitent';
$messages['smtptoerror'] = 'Error SMTP ($code): No s\'ha pogut posar "$to" com a destinatari';
$messages['smtprecipientserror'] = 'Error SMTP: No s\'ha pogut analitzar la lista de destinataris';
$messages['smtperror'] = 'Error SMTP: $msg';
$messages['emailformaterror'] = 'Adreça d\'e-mail incorrecte: $email';
$messages['toomanyrecipients'] = 'Massa destinataris. Redueix el nombre de destinataris a $max.';
$messages['maxgroupmembersreached'] = 'El nombre de membres del grup excedeix el màxim de $max.';

?>
