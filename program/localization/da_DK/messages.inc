<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'Der opstod en fejl!';
$messages['loginfailed']  = 'Det lykkedes ikke at logge på.';
$messages['cookiesdisabled'] = 'Din browser accepterer ikke cookies.';
$messages['sessionerror'] = 'Din session er ugyldig eller udløbet.';
$messages['storageerror'] = 'Forbindelse til e-mailserveren fejlede.';
$messages['servererror'] = 'Server fejl!';
$messages['servererrormsg'] = 'Server fejl: $msg';
$messages['dberror'] = 'Database fejl!';
$messages['requesttimedout'] = 'Forespørgselstiden udløb';
$messages['errorreadonly'] = 'Kunne ikke udføre den ønskede handling. Mappen er skrivebeskyttet.';
$messages['errornoperm'] = 'Kunne ikke udføre den ønskede handling. Adgang nægtet.';
$messages['erroroverquota'] = 'Kan ikke udføre handlingen. Der er ikke nok ledig diskplads.';
$messages['erroroverquotadelete'] = 'Der er ikke nok ledig diskplads. Brug SHIFT+DEL til at slette en meddelelse.';
$messages['invalidrequest'] = 'Ugyldig forespørgsel! Ingen data blev gemt.';
$messages['invalidhost'] = 'Ugyldigt servernavn.';
$messages['nomessagesfound'] = 'Der blev ikke fundet nogen beskeder i denne postkasse.';
$messages['loggedout'] = 'Du er nu logget af webmail. Farvel så længe!';
$messages['mailboxempty'] = 'Postkassen er tom.';
$messages['refreshing'] = 'Opdaterer...';
$messages['loading'] = 'Indlæser...';
$messages['uploading'] = 'Uploader fil...';
$messages['uploadingmany'] = 'Uploader filer...';
$messages['loadingdata'] = 'Indlæser data...';
$messages['checkingmail'] = 'Tjekker for nye beskeder...';
$messages['sendingmessage'] = 'Sender besked...';
$messages['messagesent'] = 'Beskeden blev afsendt succesfuldt.';
$messages['savingmessage'] = 'Gemmer besked...';
$messages['messagesaved'] = 'Beskeden er gemt i kladdemappen.';
$messages['successfullysaved'] = 'Gemt succesfuldt';
$messages['addedsuccessfully'] = 'Kontakten blev tilføjet adressebogen.';
$messages['contactexists'] = 'Der er allerede en kontakt med denne e-mailadresse.';
$messages['contactnameexists'] = 'En kontakt med samme navn eksisterer allerede.';
$messages['blockedimages'] = 'For at beskytte dit privatliv er billeder fra eksterne internetservere blokeret.';
$messages['encryptedmessage'] = 'Beskeden er krypteret og kan ikke vises. Beklager!';
$messages['nocontactsfound'] = 'Ingen kontakter blev fundet.';
$messages['contactnotfound'] = 'Den søgte kontakt blev ikke fundet.';
$messages['contactsearchonly'] = 'Indtast søgeord for at finde kontakter.';
$messages['sendingfailed'] = 'Beskeden kunne ikke sendes.';
$messages['senttooquickly'] = 'Vent venligst $sec sekunder før du sender denne besked.';
$messages['errorsavingsent'] = 'Der opstod en fejl da den sendte besked blev gemt.';
$messages['errorsaving'] = 'Der opstod en fejl under gemning.';
$messages['errormoving'] = 'Beskeden kunne ikke flyttes.';
$messages['errorcopying'] = 'Beskeden kunne ikke kopieres.';
$messages['errordeleting'] = 'Beskeden kunne ikke slettes.';
$messages['errormarking'] = 'Beskeden kunne ikke markeres.';
$messages['deletecontactconfirm']  = 'Vil du virkelig slette den/de valgte kontakt(er)?';
$messages['deletegroupconfirm']  = 'Vil du virkelig slette den/de valgte gruppe(r)?';
$messages['deletemessagesconfirm'] = 'Vil du virkelig slette den/de valgte besked(er)?';
$messages['deletefolderconfirm']  = 'Vil du virkelig slette den valgte mappe?';
$messages['purgefolderconfirm']  = 'Vil du virkelig slette alle beskeder i denne mappe?';
$messages['contactdeleting'] = 'Sletter kontakt(er)...';
$messages['groupdeleting'] = 'Sletter gruppe...';
$messages['folderdeleting'] = 'Sletter mappen...';
$messages['foldermoving'] = 'Flytter mappen...';
$messages['foldersubscribing'] = 'Abonnere på mappen...';
$messages['folderunsubscribing'] = 'Fjerner abonnement på mappen...';
$messages['formincomplete'] = 'Formularen var ikke fuldstændig fyldt ud.';
$messages['noemailwarning'] = 'Indtast venligst en gyldig e-mailadresse.';
$messages['nonamewarning']  = 'Angiv venligst et navn.';
$messages['nopagesizewarning'] = 'Angiv sidestørrelsen.';
$messages['nosenderwarning'] = 'Indtast venligst afsenders e-mailadresse.';
$messages['norecipientwarning'] = 'Indtast mindst én modtager.';
$messages['nosubjectwarning']  = '\'Emne\'-feltet er tomt. Kunne du tænke dig at skrive et nu?';
$messages['nobodywarning'] = 'Send denne besked uden tekst?';
$messages['notsentwarning'] = 'Beskeden er ikke sendt. Vil du kassere din besked?';
$messages['noldapserver'] = 'Vælg venligst hvilken LDAP-server der skal søges i.';
$messages['nosearchname'] = 'Indtast venligst en kontakts navn eller e-mailadresse.';
$messages['notuploadedwarning'] = 'Ikke alle vedhæftede filer er blevet uploadet endnu. Vent venligst eller afbryd upload.';
$messages['searchsuccessful'] = 'Fandt $nr beskeder';
$messages['contactsearchsuccessful'] = 'Fandt $nr kontakter.';
$messages['searchnomatch'] = 'Søgningen fandt ingen beskeder.';
$messages['searching'] = 'Søger...';
$messages['checking'] = 'Tjekker...';
$messages['nospellerrors'] = 'Ingen stavefejl fundet.';
$messages['folderdeleted'] = 'Mappen er slettet.';
$messages['foldersubscribed'] = 'Mappe-abonnement oprettet.';
$messages['folderunsubscribed'] = 'Mappe-abonnement opsagt.';
$messages['folderpurged'] = 'Mappen er tømt.';
$messages['folderexpunged'] = 'Mappen er blevet optimeret.';
$messages['deletedsuccessfully'] = 'Slettet.';
$messages['converting'] = 'Fjerner formatering fra besked...';
$messages['messageopenerror'] = 'Beskeden kunne ikke hentes fra serveren.';
$messages['fileuploaderror'] = 'Upload mislykkedes.';
$messages['filesizeerror'] = 'Den indsatte fil fylder mere end det maksimale på $size.';
$messages['copysuccess'] = 'Det lykkedes at kopiere $nr adresser.';
$messages['copyerror'] = 'Kunne ikke kopiere adresserne.';
$messages['sourceisreadonly'] = 'Denne adressekilde er kun til læsning.';
$messages['errorsavingcontact'] = 'Kunne ikke gemme kontaktadressen.';
$messages['movingmessage'] = 'Flytter besked(er)...';
$messages['copyingmessage'] = 'Kopierer besked(er)...';
$messages['copyingcontact'] = 'Kopierer kontakt(er)...';
$messages['deletingmessage'] = 'Sletter besked(er)...';
$messages['markingmessage'] = 'Markerer besked(er)...';
$messages['addingmember'] = 'Tilføjer kontakt(er) til gruppen...';
$messages['removingmember'] = 'Fjerner kontakt(er) fra gruppen...';
$messages['receiptsent'] = 'Kvittering for læsning er sendt.';
$messages['errorsendingreceipt'] = 'Kvitteringen kunne ikke sendes.';
$messages['deleteidentityconfirm'] = 'Vil du slette denne identitet?';
$messages['nodeletelastidentity'] = 'Du kan ikke slette denne identitet, da det er den eneste der er tilbage.';
$messages['forbiddencharacter'] = 'Mappe-navnet indeholder ugyldige tegn.';
$messages['selectimportfile'] = 'Vælg venligst den fil der skal overføres.';
$messages['addresswriterror'] = 'Der kan ikke skrives i den valgte adressebog.';
$messages['contactaddedtogroup'] = 'Succesfuldt tilføjet kontakten til denne gruppe';
$messages['contactremovedfromgroup'] = 'Kontakten fjernet fra denne gruppe';
$messages['nogroupassignmentschanged'] = 'Ingen gruppetildelinger ændret.';
$messages['importwait'] = 'Importerer, vent venligst...';
$messages['importformaterror'] = 'Importering mislykkedes! Den uploadede fil indeholder ikke gyldige data til importering.';
$messages['importconfirm'] = '<b>Importerede $inserted kontakter</b>';
$messages['importconfirmskipped'] = '<b>Sprang over $skipped eksisterende kontakter</b>';
$messages['opnotpermitted'] = 'Handlingen er ikke tilladt!';
$messages['nofromaddress'] = 'Der mangler en e-mailadresse i den valgte identitet.';
$messages['editorwarning'] = 'Al formatering af teksten forsvinder, hvis der skiftes til ren tekst. Vil du fortsætte?';
$messages['httpreceivedencrypterror'] = 'Der er opstået en fatal konfigurationsfejl. Kontakt venligst din system-administrator med det samme. <b>Din besked kunne ikke afsendes.</b>';
$messages['smtpconnerror'] = 'SMTP fejl ($code): Forbindelsen til serveren afbrudt';
$messages['smtpautherror'] = 'SMTP fejl ($code): Autenticering fejlede';
$messages['smtpfromerror'] = 'SMTP fejl ($code): Kunne ikke afsende som "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP fejl ($code): Kunne ikke tilføje modtageren "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP fejl: Kan ikke fortolke listen af modtagere.';
$messages['smtperror'] = 'SMTP fejl: $msg';
$messages['emailformaterror'] = 'Ugyldig e-mailadresse: $email';
$messages['toomanyrecipients'] = 'For mange modtagere. Reducer antallet af modtagere til $max.';
$messages['maxgroupmembersreached'] = 'Antallet af gruppemedlemmer overstiger maksimum på $max.';
$messages['internalerror'] = 'Der opstod en intern fejl - prøv venligst igen.';
$messages['contactdelerror'] = 'Kunne ikke slette kontakt(er).';
$messages['contactdeleted'] = 'Kontakt(er) slettet.';
$messages['contactrestoreerror'] = 'Kunne ikke gendanne slettede kontakt(er).';
$messages['contactrestored'] = 'Kontakt(er) gendannet.';
$messages['groupdeleted'] = 'Gruppen er slettet.';
$messages['grouprenamed'] = 'Gruppen er omdøbt.';
$messages['groupcreated'] = 'Gruppen er oprettet.';
$messages['savedsearchdeleted'] = 'Gemt søgning slettet.';
$messages['savedsearchdeleteerror'] = 'Kunne ikke slette Gemt søgning.';
$messages['savedsearchcreated'] = 'Gemt søgning oprettet.';
$messages['savedsearchcreateerror'] = 'Kunne ikke oprette Gemt søgning.';
$messages['messagedeleted'] = 'Besked(er) slettet.';
$messages['messagemoved'] = 'Besked(er) flyttet.';
$messages['messagecopied'] = 'Besked(er) kopieret.';
$messages['messagemarked'] = 'Besked(er) markeret.';
$messages['autocompletechars'] = 'Du skal indtaste mindst $min tegn for at benytte autoopslag.';
$messages['autocompletemore'] = 'Flere emner fundet. Indtast flere tegn.';
$messages['namecannotbeempty'] = 'Navn må ikke være tomt.';
$messages['nametoolong'] = 'Navnet er for langt.';
$messages['folderupdated'] = 'Mappen er opdateret.';
$messages['foldercreated'] = 'Mappen er oprettet.';
$messages['invalidimageformat'] = 'Ikke et gyldigt billedformat.';
$messages['mispellingsfound'] = 'Der er fundet stavefejl i beskeden.';
$messages['parentnotwritable'] = 'Kan ikke oprette/flytte mappe ind i valgt mappe. Ingen adgangsrettigheder.';
$messages['messagetoobig'] = 'Denne del af beskeden er for stor til at blive behandlet.';
$messages['attachmentvalidationerror'] = 'ADVARSEL! Den vedhæftede fil er mistænkelig - Typen stemmer ikke overens med den oplyste type. Hvis du ikke har tillid til afsender, skal du ikke åbne filen da den kan indholde onsindet indhold. <br/><br/><em>Forventet: $expected; Fundet: $detected</em>';
$messages['noscriptwarning'] = 'Advarsel: Denne webmailclient kræver Javascript! For at kunne bruge den slå venligst Javascripts til i din browserindstillinger.';

?>
