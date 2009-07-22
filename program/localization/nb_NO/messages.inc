<?php

/*

+-----------------------------------------------------------------------+
| language/nb_NO/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Kjetil Waage - Nextline AS <kjetil@nextline.no>               |
| Author: Tor Erik Linnerud - JKLM <tel@jklm.no>                        |
| Author: Odd Henriksen <odd@digicon.no>                                |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Pålogging mislyktes';
$messages['cookiesdisabled'] = 'Din nettleser aksepterer ikke cookies';
$messages['sessionerror'] = 'Din session er ugyldig eller utløpt';
$messages['imaperror'] = 'Forbindelsen til IMAP serveren sviktet';
$messages['servererror'] = 'Server feil!';
$messages['invalidrequest'] = 'Ugyldig forespørsel! Ingen data ble lagret.';
$messages['nomessagesfound'] = 'Det ble ikke funnet noen meldinger i denne innboksen';
$messages['loggedout'] = 'Du er logget av webmail. Ha det bra!';
$messages['mailboxempty'] = 'Postkassen er tom!';
$messages['loading'] = 'Laster...';
$messages['loadingdata'] = 'Laster data...';
$messages['checkingmail'] = 'Sjekker for nye meldinger...';
$messages['sendingmessage'] = 'Sender melding...';
$messages['messagesent'] = 'Meldingen ble sendt uten feil';
$messages['savingmessage'] = 'Lagrer melding...';
$messages['messagesaved'] = 'Melding er lagret som utkast';
$messages['successfullysaved'] = 'Lagret';
$messages['addedsuccessfully'] = 'Kontakten ble tilføyd i adresseboken';
$messages['contactexists'] = 'Der er allerede en kontakt med denne e-postadressen';
$messages['blockedimages'] = 'Eksterne bilder er blitt blokkert i denne e-postmeldingen.';
$messages['encryptedmessage'] = 'Dette er en kryptert melding og kan ikke vises. Beklager!';
$messages['nocontactsfound'] = 'Ingen kontakter ble funnet';
$messages['contactnotfound'] = 'Den forespurte kontakten ble ikke funnet';
$messages['sendingfailed'] = 'Kunne ikke sende e-posten';
$messages['senttooquickly'] = 'Vennligst vent $sec sekund(er) før du sender denne meldingen';
$messages['errorsavingsent'] = 'En feil oppsto under lagring av sendt melding';
$messages['errorsaving'] = 'Det oppstod en feil ved lagring av data';
$messages['errormoving'] = 'Kunne ikke flytte meldingen';
$messages['errordeleting'] = 'Kunne ikke slette meldingen';
$messages['errormarking'] = 'Kunne ikke markere meldingen';
$messages['deletecontactconfirm'] = 'Vil du virkelig slette valgte kontakt(er)?';
$messages['deletemessagesconfirm'] = 'Vil du virkelig slette de(n) valgte meldingen(e)?';
$messages['deletefolderconfirm'] = 'Vil du virkelig slette denne mappen?';
$messages['purgefolderconfirm'] = 'Vil du virkelig slette alle meldingene i denne mappen?';
$messages['foldercreating'] = 'Oppretter mappe...';
$messages['folderdeleting'] = 'Sletter mappe...';
$messages['folderrenaming'] = 'Gir nytt navn til mappe...';
$messages['foldermoving'] = 'Flytter mappe...';
$messages['formincomplete'] = 'Skjemaet var ikke fullstendig fylt ut';
$messages['noemailwarning'] = 'Skriv inn en gyldig e-post adresse';
$messages['nonamewarning'] = 'Angi et navn';
$messages['nopagesizewarning'] = 'Angi sidestørrelsen';
$messages['nosenderwarning'] = 'Vennligst angi avsenderadresse';
$messages['norecipientwarning'] = 'Skriv inn minst en mottaker';
$messages['nosubjectwarning'] = '"Emne" feltet er tomt. Vil du skrive det inn nå?';
$messages['nobodywarning'] = 'Send denne e-post uten tekst?';
$messages['notsentwarning'] = 'E-posten ble ikke sendt. Vil du slette meldingen?';
$messages['noldapserver'] = 'Venligst velg hvilken LDAP server det skal søkes i';
$messages['nocontactsreturned'] = 'Ingen kontakter ble funnet';
$messages['nosearchname'] = 'Venligst skriv inn kontaktens navn eller e-postadresse';
$messages['searchsuccessful'] = '$nr meldinger funnet';
$messages['searchnomatch'] = 'Søket ga ingen resultater';
$messages['searching'] = 'Søker...';
$messages['checking'] = 'Sjekker...';
$messages['nospellerrors'] = 'Ingen stavefeil funnet';
$messages['folderdeleted'] = 'Mappe slettet';
$messages['deletedsuccessfully'] = 'Slettet';
$messages['converting'] = 'Fjerner formatering fra meldingen...';
$messages['messageopenerror'] = 'Kunne ikke laste meldingen fra server';
$messages['fileuploaderror'] = 'Opplasting feilet';
$messages['filesizeerror'] = 'Filen overstiger maksimum tillatt filstørrelse ($size)';
$messages['copysuccess'] = '$nr adresser ble kopiert';
$messages['copyerror'] = 'Kunne ikke kopiere';
$messages['sourceisreadonly'] = 'Denne adressekilden er skrivebeskyttet';
$messages['errorsavingcontact'] = 'Kunne ikke lagre adressen';
$messages['movingmessage'] = 'Flytter e-post...';
$messages['receiptsent'] = 'Kvittering har blitt sent';
$messages['errorsendingreceipt'] = 'Kunne ikke sende kvittering';
$messages['nodeletelastidentity'] = 'Du kan ikke slette denne identiteten, det er din siste.';
$messages['addsubfolderhint'] = 'Mappen vil bli opprettet som en undermappe av den nåværende valgte mappe';
$messages['forbiddencharacter'] = 'Mappenavnet inneholder et tegn som ikke er tillatt';
$messages['selectimportfile'] = 'Vennligst velg en fil for opplasting';
$messages['addresswriterror'] = 'Den valgte adresseboken er ikke skrivbar';
$messages['importwait'] = 'Importerer, vennligst vent...';
$messages['importerror'] = 'Importering feilet! Den opplastede filen er ikke en gyldig vCard-fil.';
$messages['importconfirm'] = '<b>Importerte $inserted kontakter vellykket, hoppet over $skipped eksisterende oppføringer</b>: <p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Handling ikke tillatt!';
$messages['nofromaddress'] = 'E-postadresse mangler i valgt identitet';
$messages['editorwarning'] = 'Å bytte format til ren tekst vil føre til at all tekstformatering går tapt. Ønsker du å fortsette?';
$messages['httpreceivedencrypterror'] = 'En uopprettelig feil oppsto. Vennligst kontakt din administrator umiddelbart. <b>Din melding kunne ikke sendes.</b>';
$messages['smtpconnerror'] = 'SMTP Feil ($code): Tilkobling til server feilet';
$messages['smtpautherror'] = 'SMTP Feil ($code): Pålogging feilet';
$messages['smtpfromerror'] = 'SMTP Feil ($code): Kunne ikke sette avsender "$from"';
$messages['smtptoerror'] = 'SMTP Feil ($code): Kunne ikke legge til mottaker "$to"';
$messages['smtprecipientserror'] = 'SMTP Feil ($code): Kunne ikke lese mottaker listen';
$messages['smtperror'] = 'SMTP Feil ($code): $msg';

?>
