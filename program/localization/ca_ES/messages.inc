<?php

/*

+-----------------------------------------------------------------------+
| language/ca_ES/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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
$messages['nomessagesfound'] = 'No s\'han trobat missatges en aquesta bústia';
$messages['loggedout'] = 'Ha tancat la sessió. Fins aviat!';
$messages['mailboxempty'] = 'La bústia està buida';
$messages['loading'] = 'Carregant...';
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
$messages['errordeleting'] = 'No s\'ha pogut eliminar el missatge';
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
$messages['receiptsent'] = 'Confirmació de lectura enviada satisfactòriament';
$messages['errorsendingreceipt'] = 'No es pot enviar la confirmació';
$messages['nodeletelastidentity'] = 'No pots eliminar aquesta identitat, és l\'última.';
$messages['addsubfolderhint'] = 'Aquesta carpeta serà creada com a subcarpeta de la seleccionada actualment';
$messages['forbiddencharacter'] = 'El nom de carpeta conté un caràcter prohibit';
$messages['selectimportfile'] = 'Si us plau, seleccioneu un fitxer per pujar';
$messages['addresswriterror'] = 'La llibreta d\'adreces seleccionada no és modificable';
$messages['importwait'] = 'Important, espereu si us plau...';
$messages['importerror'] = 'Importació fallida! El fitxer pujat no és un fitxer vCard vàlid';
$messages['importconfirm'] = '<b>S\'han importat $inserted contactes satisfactòriament, s\'han descartat $skipped entrades ja existents</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operació no permesa!';
$messages['nofromaddress'] = 'Falta l\'adreça d\'e-mail a la identitat seleccionada';
$messages['editorwarning'] = 'Si canvieu a l\'editor de text pla perdreu tot el format del text. Voleu continuar?';

?>