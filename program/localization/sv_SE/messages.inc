<?php

/*

+-----------------------------------------------------------------------+
| language/sv_SE/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Jonas Nasholm <rcm@bitfuse.net>                               |
|         Fredrik Nygren <f.nygren@gmail.com>                           |
|         Andreas Henriksson <andreas@fatal.se>                         |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Inloggningen misslyckades';
$messages['cookiesdisabled'] = 'Din webbläsare accepterar inte cookies';
$messages['sessionerror'] = 'Din inloggning är felaktig eller har gått ut';
$messages['imaperror'] = 'Kontakt med IMAP-servern misslyckades';
$messages['servererror'] = 'Fel i servern!';
$messages['invalidrequest'] = 'Ogiltig begäran! Informationen sparades inte.';
$messages['nomessagesfound'] = 'Inga meddelanden';
$messages['loggedout'] = 'Du är utloggad. Välkommen åter!';
$messages['mailboxempty'] = 'Katalogen är tom';
$messages['loading'] = 'Laddar...';
$messages['loadingdata'] = 'Laddar data...';
$messages['checkingmail'] = 'Letar efter nya meddelanden...';
$messages['sendingmessage'] = 'Skickar meddelande...';
$messages['messagesent'] = 'Meddelandet har skickats';
$messages['savingmessage'] = 'Sparar meddelande...';
$messages['messagesaved'] = 'Meddelande sparades till utkast';
$messages['successfullysaved'] = 'Informationen har sparats';
$messages['addedsuccessfully'] = 'Kontakten har lagts till i adressboken';
$messages['contactexists'] = 'En kontakt med den här adressen finns redan';
$messages['blockedimages'] = 'Externt länkade bilder i meddelandet har blockerats.';
$messages['encryptedmessage'] = 'Meddelandet är krypterat och kan tyvärr inte visas.';
$messages['nocontactsfound'] = 'Inga kontakter hittades';
$messages['contactnotfound'] = 'Den efterfrågade kontanten hittades inte';
$messages['sendingfailed'] = 'Meddelandet kunde inte skickas';
$messages['senttooquickly'] = 'Vänta ytterligare $sec sekunder med att skicka meddelandet';
$messages['errorsavingsent'] = 'Ett fel inträffade när det skickade meddelandet sparades';
$messages['errorsaving'] = 'Meddelandet kunde inte sparads';
$messages['errormoving'] = 'Meddelandet kunde inte flyttas';
$messages['errordeleting'] = 'Meddelandet kunde inte raderas';
$messages['errormarking'] = 'Meddelandet kunde inte markeras';
$messages['deletecontactconfirm'] = 'Vill du verkligen radera valda kontakter?';
$messages['deletemessagesconfirm'] = 'Vill du verkligen ta bort valda meddelanden?';
$messages['deletefolderconfirm'] = 'Vill du verkligen radera den här katalogen?';
$messages['purgefolderconfirm'] = 'Vill du verkligen radera alla meddelanden i den här katalogen?';
$messages['foldercreating'] = 'Skapar katalog...';
$messages['folderdeleting'] = 'Raderar katalog...';
$messages['folderrenaming'] = 'Namnändrar katalog...';
$messages['foldermoving'] = 'Flyttar katalog...';
$messages['formincomplete'] = 'Formuläret var inte ifyllt i sin helhet';
$messages['noemailwarning'] = 'Vänligen ange en giltig adress';
$messages['nonamewarning'] = 'Vänligen ange ett namn';
$messages['nopagesizewarning'] = 'Vänligen ange en sidstorlek';
$messages['nosenderwarning'] = 'Vänligen ange en avsändaradress';
$messages['norecipientwarning'] = 'Vänligen ange minst en mottagare';
$messages['nosubjectwarning'] = 'Ämnesfältet är tomt. Vill du lägga till något nu?';
$messages['nobodywarning'] = 'Skicka det här meddelandet utan text?';
$messages['notsentwarning'] = 'Meddelandet har inte skickats. Vill du avbryta meddelandet?';
$messages['noldapserver'] = 'Vänligen ange en LDAP-server att söka';
$messages['nocontactsreturned'] = 'Inga kontakter hittades';
$messages['nosearchname'] = 'Vänligen ange ett kontaktnamn eller en adress';
$messages['searchsuccessful'] = '$nr meddelanden hittades';
$messages['searchnomatch'] = 'Sökningen gav inget resultat';
$messages['searching'] = 'Söker...';
$messages['checking'] = 'Kontrollerar...';
$messages['nospellerrors'] = 'Inget stavfel hittades';
$messages['folderdeleted'] = 'Katalogen raderades';
$messages['deletedsuccessfully'] = 'Lyckad borttagning';
$messages['converting'] = 'Tar bort formatering från meddelande...';
$messages['messageopenerror'] = 'Kunde inte ladda meddelande från servern';
$messages['fileuploaderror'] = 'Filuppladdning misslyckades';
$messages['filesizeerror'] = 'Den uppladdade filen överstiger maxstorleken $size';
$messages['copysuccess'] = 'Lyckad kopiering av $nr adresser';
$messages['copyerror'] = 'Kunde inte kopiera några adresser';
$messages['sourceisreadonly'] = 'Denna adresskälla är skrivskyddad';
$messages['errorsavingcontact'] = 'Kunde inte spara kontaktadressen';
$messages['movingmessage'] = 'Flyttar meddelande...';
$messages['receiptsent'] = 'Mottagarkvitto har skickats';
$messages['errorsendingreceipt'] = 'Kunde inte skicka mottagarkvitto';
$messages['nodeletelastidentity'] = 'Du kan inte radera den här identiteten, det är din sista.';
$messages['addsubfolderhint'] = 'Den här katalogen kommer att skapas som en underkatalog till den valda';
$messages['forbiddencharacter'] = 'Katalognamnet innehåller otillåtna tecken';
$messages['selectimportfile'] = 'Välj en fil att ladda upp';
$messages['addresswriterror'] = 'Angiven adressbok är skrivskyddad';
$messages['importwait'] = 'Importerar, var god vänta...';
$messages['importerror'] = 'Importen misslyckades! Filen är inte i vCard-format.';
$messages['importconfirm'] = '<b>Lyckad import av $inserted kontakter, $skipped dubbletter har ignorerats:</b><p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Otillåten operation!';
$messages['nofromaddress'] = 'Adressen saknas i den valda identiteten';
$messages['editorwarning'] = 'Genom att växla till text-läge går formateringen förlorad. Vill du fortsätta?';
$messages['httpreceivedencrypterror'] = 'Ett irreparabelt fel har uppstått. Kontakta administratören omgående. <b>Meddelandet kan inte skickas.</b>';
$messages['smtpconnerror'] = 'SMTP-fel ($code): Anslutning till servern misslyckades';
$messages['smtpautherror'] = 'SMTP-fel ($code): Inloggningen misslyckades';
$messages['smtpfromerror'] = 'SMTP-fel ($code): Kan inte sätta avsändaradress till "$from"';
$messages['smtptoerror'] = 'SMTP-fel ($code): Kan inte lägga till mottagaradress "$to"';
$messages['smtprecipientserror'] = 'SMTP-fel: Felaktigt formaterad lista med mottagaradresser';
$messages['smtperror'] = 'SMTP-fel: $msg';

?>
