<?php

/*
+-----------------------------------------------------------------------+
| language/da_DK/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author : Martin Moeller <martin@liga.dk>                              |
|         Jesper R. Meyer <jesper@upthere.dk>                           |
|         Søren Aggeboe <soren@aggeboe.dk>                              |
|         John Loft Christiansen <john@nansensvej.dk>                   |
+-----------------------------------------------------------------------+

@version $Id$
*/

$messages = array();
$messages['loginfailed'] = 'Det lykkedes ikke at logge på';
$messages['cookiesdisabled'] = 'Din browser accepterer ikke cookies';
$messages['sessionerror'] = 'Din session er ugyldig eller udløbet';
$messages['imaperror'] = 'Forbindelse til IMAP serveren fejlede';
$messages['servererror'] = 'Server fejl!';
$messages['servererrormsg'] = 'Server fejl: $msg';
$messages['dberror'] = 'Database fejl!';
$messages['errorreadonly'] = 'Kunne ikke udføre den ønskede handling. Mappen er skrivebeskyttet';
$messages['errornoperm'] = 'Kunne ikke udføre den ønskede handling. Adgang nægtet';
$messages['invalidrequest'] = 'Ugyldig forespørgsel! Ingen data blev gemt.';
$messages['nomessagesfound'] = 'Der blev ikke fundet nogen beskeder i denne postkasse';
$messages['loggedout'] = 'Du er nu logget af webmail. Farvel så længe!';
$messages['mailboxempty'] = 'Postkassen er tom!';
$messages['loading'] = 'Indlæser...';
$messages['uploading'] = 'Uploader fil...';
$messages['uploadingmany'] = 'Uploader filer...';
$messages['loadingdata'] = 'Indlæser data...';
$messages['checkingmail'] = 'Tjekker for nye beskeder...';
$messages['sendingmessage'] = 'Sender besked...';
$messages['messagesent'] = 'Beskeden blev sendt korrekt';
$messages['savingmessage'] = 'Gemmer besked...';
$messages['messagesaved'] = 'Beskeden er gemt i kladdemappen';
$messages['successfullysaved'] = 'Det lykkedes at gemme';
$messages['addedsuccessfully'] = 'Kontakten blev tilføjet adressebogen';
$messages['contactexists'] = 'Der er allerede en kontakt med denne e-mail adresse';
$messages['contactnameexists'] = 'En kontakt med samme navn eksisterer allerede.';
$messages['blockedimages'] = 'For at beskytte dit privatliv er billeder fra internetservere blokeret i denne besked.';
$messages['encryptedmessage'] = 'Beskeden er krypteret og kan ikke vises. Beklager!';
$messages['nocontactsfound'] = 'Ingen kontakter blev fundet';
$messages['contactnotfound'] = 'Den søgte kontakt blev ikke fundet';
$messages['contactsearchonly'] = 'Indtast ord for at finde kontakten';
$messages['sendingfailed'] = 'Beskeden kunne ikke sendes';
$messages['senttooquickly'] = 'Vent venligst $sec sekunder før du sender denne besked';
$messages['errorsavingsent'] = 'Der opstod en fejl da den sendte besked blev gemt';
$messages['errorsaving'] = 'Der opstod en fejl ved lagring af data';
$messages['errormoving'] = 'Beskeden kunne ikke flyttes';
$messages['errorcopying'] = 'Beskeden kunne ikke kopieres';
$messages['errordeleting'] = 'Beskeden kunne ikke slettes';
$messages['errormarking'] = 'Beskeden kunne ikke markeres';
$messages['deletecontactconfirm'] = 'Vil du virkelig slette den/de valgte kontakt(er)?';
$messages['deletegroupconfirm']  = 'Vil du virkelig slette den/de valgte gruppe(r)?';
$messages['deletemessagesconfirm'] = 'Vil du virkelig slette den/de valgte besked(er)?';
$messages['deletefolderconfirm'] = 'Vil du virkelig slette den valgte mappe';
$messages['purgefolderconfirm'] = 'Vil du virkelig slette alle beskeder i denne mappe?';
$messages['contactdeleting'] = 'Sletter kontakt(er)...';
$messages['groupdeleting'] = 'Sletter gruppe...';
$messages['folderdeleting'] = 'Sletter mappen...';
$messages['foldermoving'] = 'Flytter mappen...';
$messages['foldersubscribing'] = 'Abonnere på mappen...';
$messages['folderunsubscribing'] = 'Fjerner abonnement på mappen...';
$messages['formincomplete'] = 'Formularen var ikke fuldstændig fyldt ud';
$messages['noemailwarning'] = 'Indtast venligst en gyldig e-mail adresse';
$messages['nonamewarning'] = 'Angiv venligst et navn';
$messages['nopagesizewarning'] = 'Angiv sidestørrelsen';
$messages['nosenderwarning'] = 'Indtast venligst afsenders email-adresse';
$messages['norecipientwarning'] = 'Indtast mindst én modtager';
$messages['nosubjectwarning'] = '\'Emne\'-feltet er tomt. Kunne du tænke dig at skrive et nu?';
$messages['nobodywarning'] = 'Send denne besked uden tekst?';
$messages['notsentwarning'] = 'Beskeden er ikke blevet sendt. Vil du kassere din besked?';
$messages['noldapserver'] = 'Vælg venligst hvilken LDAP-server der skal søges i';
$messages['nocontactsreturned'] = 'Ingen kontakter blev fundet';
$messages['nosearchname'] = 'Indtast venligst en kontakts navn eller e-mail adresse';
$messages['notuploadedwarning'] = 'Ikke alle vedhæftede filer er blevet uploadet endnu. Vent venligst eller afbryd upload.';
$messages['searchsuccessful'] = 'Fandt $nr beskeder';
$messages['searchnomatch'] = 'Søgningen fandt ingen beskeder';
$messages['searching'] = 'Søger...';
$messages['checking'] = 'Tjekker...';
$messages['nospellerrors'] = 'Ingen stavefejl fundet';
$messages['folderdeleted'] = 'Mappen er slettet';
$messages['foldersubscribed'] = 'Mappe abonnement oprettet';
$messages['folderunsubscribed'] = 'Mappe abonnement opsagt';
$messages['folderpurged'] = 'Mappen er tømt';
$messages['folderexpunged'] = 'Mappen er blevet optimeret';
$messages['deletedsuccessfully'] = 'Slettet';
$messages['converting'] = 'Fjerner formatering fra besked...';
$messages['messageopenerror'] = 'Beskeden kunne ikke hentes fra serveren';
$messages['fileuploaderror'] = 'Den indsatte fil fejlede';
$messages['filesizeerror'] = 'Den indsatte fil fylder mere end det maksimale på $size';
$messages['copysuccess'] = 'Det lykkedes at kopiere $nr adresser';
$messages['copyerror'] = 'Kunne ikke kopiere adresserne';
$messages['sourceisreadonly'] = 'Denne adressekilde er kun til læsning';
$messages['errorsavingcontact'] = 'Kunne ikke gemme kontakt adressen';
$messages['movingmessage'] = 'Flytter besked...';
$messages['copyingmessage'] = 'Kopierer besked...';
$messages['copyingcontact'] = 'Kopierer kontakt(er)...';
$messages['deletingmessage'] = 'Sletter besked(er)...';
$messages['markingmessage'] = 'Markerer besked(er)...';
$messages['addingmember'] = 'Tilføjer kontakt(er) til gruppen...';
$messages['removingmember'] = 'Fjerner kontakt(er) fra gruppen...';
$messages['receiptsent'] = 'Kvittering for læsning er sendt';
$messages['errorsendingreceipt'] = 'Kvitteringen kunne ikke sendes';
$messages['nodeletelastidentity'] = 'Du kan ikke slette denne identitet, da det er den eneste der er tilbage.';
$messages['forbiddencharacter'] = 'Mappe-navnet indeholder ugyldige tegn';
$messages['selectimportfile'] = 'Vælg venligst den fil der skal uploades';
$messages['addresswriterror'] = 'Der kan ikke skrives i den valgte adressebog';
$messages['contactaddedtogroup'] = 'Succesfuldt tilføjet kontakten til denne gruppe';
$messages['contactremovedfromgroup'] = 'Succesfuldt fjernet kontakten fra denne gruppe';
$messages['importwait'] = 'Importerer, vent venligst...';
$messages['importerror'] = 'Fejl i importen! Den uploadede fil er ikke en gyldig vCard fil.';
$messages['importconfirm'] = '<b>Importerede $inserted kontakter, sprang over $skipped allerede eksisterende kontakter</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Skipped $skipped existing entries</b>';
$messages['opnotpermitted'] = 'Handlingen er ikke tilladt!';
$messages['nofromaddress'] = 'Der mangler en email-adresse i den valgte identitet';
$messages['editorwarning'] = 'Al formatering af teksten forsvinder, hvis der skiftes til ren tekst. Vil du fortsætte?';
$messages['httpreceivedencrypterror'] = 'Der er opstået en fatal konfigurationsfejl. Kontakt venligst din system-administrator med det samme.<b>Din besked kunne ikke afsendes.</b>';
$messages['smtpconnerror'] = 'SMTP fejl ($code): Forbindelsen til serveren afbrudt';
$messages['smtpautherror'] = 'SMTP fejl ($code): Autenticering fejlede';
$messages['smtpfromerror'] = 'SMTP fejl ($code): Kunne ikke afsende som "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP fejl ($code): Kunne ikke tilføje modtageren "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP fejl: kan ikke fortolke listen af modtagere';
$messages['smtperror'] = 'SMTP fejl: $msg';
$messages['emailformaterror'] = 'Ugyldig email-adresse: $email';
$messages['toomanyrecipients'] = 'For mange modtagere. Reducer antallet af modtagere til $max';
$messages['maxgroupmembersreached'] = 'Antallet af gruppemedlemmer overstiger maksimum på $max';
$messages['internalerror'] = 'Der opstod en intern fejl - prøv venligst igen';
$messages['contactdelerror'] = 'Kunne ikke slette kontakt(er)';
$messages['contactdeleted'] = 'Kontakt(er) slettet';
$messages['contactrestoreerror'] = 'Kunne ikke gendanne slettede kontakt(er).';
$messages['contactrestored'] = 'Kontakt(er) gendannet.';
$messages['groupdeleted'] = 'Gruppen er slettet';
$messages['grouprenamed'] = 'Gruppen er omdøbt';
$messages['groupcreated'] = 'Gruppen er oprettet';
$messages['savedsearchdeleted'] = 'Gemt søgning slettet.';
$messages['savedsearchdeleteerror'] = 'Kunne ikke slette Gemt søgning.';
$messages['savedsearchcreated'] = 'Gemt søgning oprettet.';
$messages['savedsearchcreateerror'] = 'Kunne ikke oprette Gemt søgning.';
$messages['messagedeleted'] = 'Besked(er) slettet';
$messages['messagemoved'] = 'Besked(er) flyttet';
$messages['messagecopied'] = 'Besked(er) kopieret';
$messages['messagemarked'] = 'Besked(er) markeret';
$messages['autocompletechars'] = 'Du skal min. indtaste $min for at benytte autoopslag';
$messages['autocompletemore'] = 'Flere emner fundet. Task flere bogstaver.';
$messages['namecannotbeempty'] = 'Navnet kan ikke været tomt';
$messages['nametoolong'] = 'Navnet er for langt';
$messages['folderupdated'] = 'Mappen er opdateret';
$messages['foldercreated'] = 'Mappen er oprettet';
$messages['invalidimageformat'] = 'Ikke et gyldigt billedformat';
$messages['mispellingsfound'] = 'Der er fundet stavefejl i beskeden.';
$messages['parentnotwritable'] = 'Kan ikke oprette/flytte mappe ind i valgt mappe. Ingen adgangsrettigheder.';
$messages['messagetoobig'] = 'Besked delen er for stor til at behandle.';

?>
