<?php

/*

+-----------------------------------------------------------------------+
| language/nb_NO/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Kjetil Waage - Nextline AS <kjetil@nextline.no>               |
| Author: Tor Erik Linnerud - JKLM <tel@jklm.no>                        |
| Author: Axel Sjøstedt <roundcube@sjostedt.no>                         |
| Author: Odd Henriksen <odd@digicon.no>                                |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Pålogging mislyktes';
$messages['cookiesdisabled'] = 'Din nettleser aksepterer ikke cookies';
$messages['sessionerror'] = 'Din nettlesertilkobling er ugyldig eller utløpt';
$messages['imaperror'] = 'Feil ved tilkobling til IMAP-server';
$messages['servererror'] = 'Serverfeil!';
$messages['invalidrequest'] = 'Ugyldig forespørsel! Ingen data ble lagret.';
$messages['nomessagesfound'] = 'Det ble ikke funnet noen meldinger i denne mappen';
$messages['loggedout'] = 'Du er logget av webmail. Ha det bra!';
$messages['mailboxempty'] = 'Mappen er tom!';
$messages['loading'] = 'Laster...';
$messages['uploading'] = 'Laster opp fil...';
$messages['loadingdata'] = 'Laster data...';
$messages['checkingmail'] = 'Sjekker for nye meldinger...';
$messages['sendingmessage'] = 'Sender melding...';
$messages['messagesent'] = 'Meldingen ble sendt uten feil';
$messages['savingmessage'] = 'Lagrer melding...';
$messages['messagesaved'] = 'Melding er lagret som kladd';
$messages['successfullysaved'] = 'Lagret';
$messages['addedsuccessfully'] = 'Kontakten ble lagt til i adresseboken';
$messages['contactexists'] = 'Der finnes allerede en kontakt med denne e-postadressen';
$messages['blockedimages'] = 'Av sikkerhetsgrunner er bilder lagret på eksterne servere blokkert fra denne e-postmeldingen';
$messages['encryptedmessage'] = 'Dette er en kryptert melding som ikke kan vises. Beklager!';
$messages['nocontactsfound'] = 'Ingen kontakter ble funnet';
$messages['contactnotfound'] = 'Den ønskede kontakten ble ikke funnet';
$messages['sendingfailed'] = 'Kunne ikke sende e-posten';
$messages['senttooquickly'] = 'Vennligst vent $sec sekund(er) før du sender denne meldingen';
$messages['errorsavingsent'] = 'En feil oppsto under lagring av sendt melding';
$messages['errorsaving'] = 'Det oppstod en feil ved lagring av data';
$messages['errormoving'] = 'Kunne ikke flytte meldingen(e)';
$messages['errorcopying'] = 'Kunne ikke kopiere meldingen(e)';
$messages['errordeleting'] = 'Kunne ikke slette meldingen(e)';
$messages['errormarking'] = 'Kunne ikke markere meldingen(e)';
$messages['deletecontactconfirm'] = 'Vil du virkelig slette valgte kontakt(er)?';
$messages['deletemessagesconfirm'] = 'Vil du virkelig slette de(n) valgte meldingen(e)?';
$messages['deletefolderconfirm'] = 'Vil du virkelig slette denne mappen?';
$messages['purgefolderconfirm'] = 'Vil du virkelig slette alle meldingene i denne mappen?';
$messages['foldercreating'] = 'Oppretter mappe...';
$messages['folderdeleting'] = 'Sletter mappe...';
$messages['folderrenaming'] = 'Gir nytt navn til mappe...';
$messages['foldermoving'] = 'Flytter mappe...';
$messages['formincomplete'] = 'Skjemaet var ikke fullstendig fylt ut';
$messages['noemailwarning'] = 'Skriv inn en gyldig e-postadresse';
$messages['nonamewarning'] = 'Angi et navn';
$messages['nopagesizewarning'] = 'Angi sidestørrelsen';
$messages['nosenderwarning'] = 'Vennligst angi avsenderadresse';
$messages['norecipientwarning'] = 'Skriv inn minst én mottaker';
$messages['nosubjectwarning'] = 'Emnefeltet er tomt. Vil du skrive det inn nå?';
$messages['nobodywarning'] = 'Send denne meldingen uten tekst?';
$messages['notsentwarning'] = 'E-posten er ikke sendt. Er du sikker på at du vil avbryte skrivingen?';
$messages['noldapserver'] = 'Venligst velg hvilken LDAP-server det skal søkes i';
$messages['nocontactsreturned'] = 'Ingen kontakter funnet';
$messages['nosearchname'] = 'Venligst skriv inn kontaktens navn eller e-postadresse';
$messages['notuploadedwarning'] = 'Ikke alle vedlegg er ferdig opplastet. Vennligst vent eller avbryt opplastingen.';
$messages['searchsuccessful'] = '$nr meldinger funnet';
$messages['searchnomatch'] = 'Søket ga ingen resultater';
$messages['searching'] = 'Søker...';
$messages['checking'] = 'Sjekker...';
$messages['nospellerrors'] = 'Ingen skrivefeil funnet';
$messages['folderdeleted'] = 'Mappe slettet';
$messages['deletedsuccessfully'] = 'Slettet';
$messages['converting'] = 'Fjerner formatering fra meldingen...';
$messages['messageopenerror'] = 'Kunne ikke hente meldingen fra server';
$messages['fileuploaderror'] = 'Feil under opplasting';
$messages['filesizeerror'] = 'Filen overstiger maksimum tillatt filstørrelse ($size)';
$messages['copysuccess'] = '$nr adresser ble kopiert';
$messages['copyerror'] = 'Kunne ikke kopiere';
$messages['sourceisreadonly'] = 'Denne adressekilden er skrivebeskyttet';
$messages['errorsavingcontact'] = 'Kunne ikke lagre adressen';
$messages['movingmessage'] = 'Flytter e-post...';
$messages['copyingmessage'] = 'Kopierer e-post...';
$messages['receiptsent'] = 'Kvittering er sent';
$messages['errorsendingreceipt'] = 'Kunne ikke sende kvittering';
$messages['nodeletelastidentity'] = 'Du kan ikke slette denne identiteten, det er din eneste.';
$messages['addsubfolderhint'] = 'Mappen vil bli opprettet som en undermappe av nåværende valgt mappe';
$messages['forbiddencharacter'] = 'Mappenavnet inneholder tegn som ikke er tillatt';
$messages['selectimportfile'] = 'Vennligst velg en fil for opplasting';
$messages['addresswriterror'] = 'Den valgte adresseboken er ikke skrivbar';
$messages['contactaddedtogroup'] = 'Kontaktene ble vellykket lagt til i denne gruppen';
$messages['contactremovedfromgroup'] = 'Kontaktene ble vellykket fjernet fra denne gruppen';
$messages['importwait'] = 'Importerer, vennligst vent...';
$messages['importerror'] = 'Feil ved importering! Den opplastede filen er ikke en gyldig vCard-fil.';
$messages['importconfirm'] = '<b>Importerte $inserted kontakter, hoppet over $skipped oppføringer som allerede eksisterte</b>: <p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Handling ikke tillatt!';
$messages['nofromaddress'] = 'E-postadresse mangler i valgt identitet';
$messages['editorwarning'] = 'Ved å bytte for mat til ren tekst vil all tekstformatering gå tapt. Ønsker du å fortsette?';
$messages['httpreceivedencrypterror'] = 'En uopprettelig feil oppsto. Vennligst kontakt systemansvarlig umiddelbart. <b>Din melding kunne ikke sendes.</b>';
$messages['smtpconnerror'] = 'SMTP-feil ($code): Klarte ikke koble til server';
$messages['smtpautherror'] = 'SMTP-feil ($code): Pålogging feilet';
$messages['smtpfromerror'] = 'SMTP-feil ($code): Kunne ikke sette avsender "$from"';
$messages['smtptoerror'] = 'SMTP-feil ($code): Kunne ikke legge til mottaker "$to"';
$messages['smtprecipientserror'] = 'SMTP-feil ($code): Kunne ikke lese mottakerliste';
$messages['smtperror'] = 'SMTP-feil ($code): $msg';
$messages['emailformaterror'] = 'Feil e-postadresse: $email';
$messages['toomanyrecipients'] = 'For mange mottakere. Reduser antall mottakere til $max.';
$messages['maxgroupmembersreached'] = 'Antall gruppemedlemmer overskrider maksgrensen på $max';

?>
