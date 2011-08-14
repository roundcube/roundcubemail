<?php

/*

+-----------------------------------------------------------------------+
| language/sv_SE/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
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
$messages['imaperror'] = 'Anslutning till IMAP-servern misslyckades';
$messages['servererror'] = 'Serverfel!';
$messages['servererrormsg'] = 'Serverfel: $msg';
$messages['databaserror'] = 'Databasfel!';
$messages['errorreadonly'] = 'Åtgärden kunde inte utföras. Katalogen är skrivskyddad';
$messages['errornoperm'] = 'Åtgärden kunde inte utföras. Otillräcklig befogenhet';
$messages['invalidrequest'] = 'Ogiltig begäran! Informationen sparades inte.';
$messages['nomessagesfound'] = 'Inga meddelanden';
$messages['loggedout'] = 'Du är utloggad. Välkommen åter!';
$messages['mailboxempty'] = 'Katalogen är tom';
$messages['loading'] = 'Laddar...';
$messages['uploading'] = 'Överför fil...';
$messages['uploadingmany'] = 'Överför filer...';
$messages['loadingdata'] = 'Laddar data...';
$messages['checkingmail'] = 'Hämtar nya meddelanden...';
$messages['sendingmessage'] = 'Skickar meddelande...';
$messages['messagesent'] = 'Meddelandet har skickats';
$messages['savingmessage'] = 'Sparar meddelande...';
$messages['messagesaved'] = 'Meddelandet har sparats i Utkast';
$messages['successfullysaved'] = 'Informationen har sparats';
$messages['addedsuccessfully'] = 'Kontakten har lagts till i adressboken';
$messages['contactexists'] = 'En kontakt med den här adressen finns redan';
$messages['blockedimages'] = 'Externt länkade bilder i meddelandet har blockerats.';
$messages['encryptedmessage'] = 'Meddelandet är krypterat och kan tyvärr inte visas.';
$messages['nocontactsfound'] = 'Inga kontakter hittades';
$messages['contactnotfound'] = 'Efterfrågad kontakt hittades inte';
$messages['sendingfailed'] = 'Meddelandet kunde inte skickas';
$messages['senttooquickly'] = 'Vänta ytterligare $sec sekunder med att skicka meddelandet';
$messages['errorsavingsent'] = 'Ett fel inträffade när det skickade meddelandet skulle sparas';
$messages['errorsaving'] = 'Meddelandet kunde inte sparas';
$messages['errormoving'] = 'Meddelandet kunde inte flyttas';
$messages['errorcopying'] = 'Meddelandet kunde inte kopieras';
$messages['errordeleting'] = 'Meddelandet kunde inte tas bort';
$messages['errormarking'] = 'Meddelandet kunde inte markeras';
$messages['deletecontactconfirm'] = 'Vill du verkligen ta bort valda kontakter?';
$messages['deletemessagesconfirm'] = 'Vill du verkligen ta bort valda meddelanden?';
$messages['deletefolderconfirm'] = 'Vill du verkligen ta bort den här katalogen?';
$messages['purgefolderconfirm'] = 'Vill du verkligen ta bort alla meddelanden i den här katalogen?';
$messages['folderdeleting'] = 'Tar bort katalog...';
$messages['foldermoving'] = 'Flyttar katalog...';
$messages['foldersubscribing'] = 'Startar prenumeration på katalog...';
$messages['folderunsubscribing'] = 'Avslutar prenumeration på katalog...';
$messages['formincomplete'] = 'Formuläret var inte komplett ifyllt';
$messages['noemailwarning'] = 'Ange en giltig adress';
$messages['nonamewarning'] = 'Ange ett namn';
$messages['nopagesizewarning'] = 'Ange en sidstorlek';
$messages['nosenderwarning'] = 'Ange en avsändaradress';
$messages['norecipientwarning'] = 'Ange minst en mottagare';
$messages['nosubjectwarning'] = 'Ämnesraden är tom. Vill du ange ämne nu?';
$messages['nobodywarning'] = 'Skicka det här meddelandet utan text?';
$messages['notsentwarning'] = 'Meddelandet har inte skickats. Vill du avbryta meddelandet?';
$messages['noldapserver'] = 'Ange en LDAP-server för att söka';
$messages['nocontactsreturned'] = 'Inga kontakter hittades';
$messages['nosearchname'] = 'Ange ett kontaktnamn eller en adress';
$messages['notuploadedwarning'] = 'Alla bilagor har inte överförts ännu. Vänligen vänta eller avbryt överföringen.';
$messages['searchsuccessful'] = '$nr meddelanden hittades';
$messages['searchnomatch'] = 'Sökningen gav inget resultat';
$messages['searching'] = 'Söker...';
$messages['checking'] = 'Kontrollerar...';
$messages['nospellerrors'] = 'Inget stavfel hittades';
$messages['folderdeleted'] = 'Katalogen togs bort';
$messages['foldersubscribed'] = 'Prenumeration på katalog startad';
$messages['folderunsubscribed'] = 'Prenumeration på katalog avslutad';
$messages['folderpurged'] = 'Katalog rensad';
$messages['folderexpunged'] = 'Katalog tömd';
$messages['deletedsuccessfully'] = 'Lyckad borttagning';
$messages['converting'] = 'Tar bort formatering från meddelande...';
$messages['messageopenerror'] = 'Meddelandet kunde inte hämtas från servern';
$messages['fileuploaderror'] = 'Filuppladdning misslyckades';
$messages['filesizeerror'] = 'Den uppladdade filens storlek överstiger högsta tillåtna $size';
$messages['copysuccess'] = '$nr adresser har kopierats';
$messages['copyerror'] = 'Några adresser kunde inte kopieras';
$messages['sourceisreadonly'] = 'Denna adresskälla är skrivskyddad';
$messages['errorsavingcontact'] = 'Kontaktadressen kunde inte sparas';
$messages['movingmessage'] = 'Flyttar meddelande...';
$messages['copyingmessage'] = 'Kopierar meddelande...';
$messages['deletingmessage'] = 'Tar bort meddelande...';
$messages['markingmessage'] = 'Markerar meddelande...';
$messages['receiptsent'] = 'Mottagarkvitto har skickats';
$messages['errorsendingreceipt'] = 'Mottagarkvitto kunde inte skickas';
$messages['nodeletelastidentity'] = 'Du kan inte ta bort identiteten, den är din sista.';
$messages['forbiddencharacter'] = 'Katalognamnet innehåller otillåtna tecken';
$messages['selectimportfile'] = 'Välj en fil att ladda upp';
$messages['addresswriterror'] = 'Angiven adressbok är skrivskyddad';
$messages['contactaddedtogroup'] = 'Kontakterna har lagts till i gruppen';
$messages['contactremovedfromgroup'] = 'Kontakterna har tagits bort från gruppen';
$messages['importwait'] = 'Importerar, var god vänta...';
$messages['importerror'] = 'Importen misslyckades! Filen är inte i vCard-format.';
$messages['importconfirm'] = '<b>Lyckad import av $inserted kontakter, $skipped dubbletter har ignorerats:</b><p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Hoppade över $skipped befintliga poster</b>';
$messages['opnotpermitted'] = 'Otillåten operation!';
$messages['nofromaddress'] = 'Adress saknas i den valda identiteten';
$messages['editorwarning'] = 'Genom att växla till text-läge går formateringen förlorad. Vill du fortsätta?';
$messages['httpreceivedencrypterror'] = 'Ett irreparabelt fel har uppstått. Kontakta administratören omgående. <b>Meddelandet kan inte skickas.</b>';
$messages['smtpconnerror'] = 'SMTP-fel ($code): Anslutning till servern misslyckades';
$messages['smtpautherror'] = 'SMTP-fel ($code): Inloggningen misslyckades';
$messages['smtpfromerror'] = 'SMTP-fel ($code): Kan inte sätta avsändaradress till "$from"';
$messages['smtptoerror'] = 'SMTP-fel ($code): Kan inte lägga till mottagaradress "$to"';
$messages['smtprecipientserror'] = 'SMTP-fel: Felaktigt formaterad lista med mottagaradresser';
$messages['smtpdsnerror'] = 'SMTP-fel: Inget stöd för leveransstatusmeddelande';
$messages['smtperror'] = 'SMTP-fel: $msg';
$messages['emailformaterror'] = 'Felaktig adress: $email';
$messages['toomanyrecipients'] = 'Förmånga mottagare. Minska antalet till högst $max.';
$messages['maxgroupmembersreached'] = 'Antalet gruppmedlemmar får inte överstiga $max';
$messages['internalerror'] = 'Ett internt fel uppstod. Försök igen.';
$messages['contactdelerror'] = 'Kontakt kunde inte tas bort';
$messages['contactdeleted'] = 'Kontakt borttagen';
$messages['groupdeleted'] = 'Grupp borttagen';
$messages['grouprenamed'] = 'Gruppnamn ändrat';
$messages['groupcreated'] = 'Grupp skapad';
$messages['messagedeleted'] = 'Meddelande borttaget';
$messages['messagemoved'] = 'Meddelande flyttat';
$messages['messagecopied'] = 'Meddelande kopierat';
$messages['messagemarked'] = 'Meddelande markerat';
$messages['autocompletechars'] = 'Ange minst $min tecken för automatisk komplettering';
$messages['namecannotbeempty'] = 'Namnet får inte vara tomt';
$messages['nametoolong'] = 'Namnet är för långt';
$messages['folderupdated'] = 'Katalog uppdaterad';
$messages['foldercreated'] = 'Katalog skapad';
$messages['invalidimageformat'] = 'Ogiltigt bildfilsformat';
$messages['mispellingsfound'] = 'Stavfel hittades i meddelandet';

?>
