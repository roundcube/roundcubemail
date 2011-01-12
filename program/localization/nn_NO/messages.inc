<?php

/*

+-----------------------------------------------------------------------+
| language/nn_NO/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Per-Stian Vatne <psv@orsta.org>                               |
| Update: Odin Omdal Hørthe <odin.omdal@gmail.com>                      |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Feil ved innlogging';
$messages['cookiesdisabled'] = 'Nettlesaren din tillet ikkje informasjonskapslar (cookies)';
$messages['sessionerror'] = 'Sesjonen din er ulovleg eller har gått ut på tid';
$messages['imaperror'] = 'Feil ved tilkopling til IMAP-tenar';
$messages['servererror'] = 'Tenarfeil!';
$messages['invalidrequest'] = 'Eg forstår ingen ting! Ingen data lagra.';
$messages['nomessagesfound'] = 'Ingen meldingar i denne postkassa';
$messages['loggedout'] = 'Du har avslutta sesjonen. Ha det bra!';
$messages['mailboxempty'] = 'Postkassa er tom';
$messages['loading'] = 'Lastar...';
$messages['uploading'] = 'Lastar opp fil...';
$messages['loadingdata'] = 'Lastar inn data...';
$messages['checkingmail'] = 'Ser etter nye meldingar...';
$messages['sendingmessage'] = 'Sender melding...';
$messages['messagesent'] = 'Meldinga vart send';
$messages['savingmessage'] = 'Lagrar melding...';
$messages['messagesaved'] = 'Melding lagra som kladd';
$messages['successfullysaved'] = 'Meldinga vart lagra';
$messages['addedsuccessfully'] = 'Kontakta vart lagt til i adresseboka';
$messages['contactexists'] = 'Ei kontakt med denne e-postadressa eksisterer allereie';
$messages['blockedimages'] = 'For å ta vare på privatlivet ditt er eksterne bilete blokkert i denne meldinga.';
$messages['encryptedmessage'] = 'Dette er ei kryptert melding og kan ikkje visast. Orsak!';
$messages['nocontactsfound'] = 'Fann ingen kontaktar';
$messages['contactnotfound'] = 'Fann ikkje kontakten du såg etter';
$messages['sendingfailed'] = 'Kunne ikkje sende meldinga';
$messages['senttooquickly'] = 'Du må venta $sec sekund for å senda meldinga';
$messages['errorsavingsent'] = 'Klarte ikkje lagra den sende meldinga';
$messages['errorsaving'] = 'Det skjedde ein feil ved lagring';
$messages['errormoving'] = 'Kunne ikkje flytte meldinga';
$messages['errorcopying'] = 'Kan ikkje kopiera epost';
$messages['errordeleting'] = 'Kunne ikkje slette meldinga';
$messages['errormarking'] = 'Kan ikkje markera epost';
$messages['deletecontactconfirm'] = 'Sletta kontakt(ar)?';
$messages['deletemessagesconfirm'] = 'Sletta melding(ar)?';
$messages['deletefolderconfirm'] = 'Sletta mappa?';
$messages['purgefolderconfirm'] = 'Sletta alle meldingane i denne mappa?';
$messages['folderdeleting'] = 'Slettar mappe...';
$messages['foldermoving'] = 'Flyttar mappe...';
$messages['formincomplete'] = 'Fyll ut skjema';
$messages['noemailwarning'] = 'Skriv inn korrekt epostadresse';
$messages['nonamewarning'] = 'Skriv inn namn';
$messages['nopagesizewarning'] = 'Skriv inn sidestorleik';
$messages['nosenderwarning'] = 'Skriv inn avsendar si e-postadresse';
$messages['norecipientwarning'] = 'Skriv inn minst ein mottakar';
$messages['nosubjectwarning'] = 'Emnefeltet er tomt. Vil du skriva inn eitt emne no?';
$messages['nobodywarning'] = 'Senda melding utan tekst?';
$messages['notsentwarning'] = 'Meldinga er ikkje sendt. Vil du kasta ho?';
$messages['noldapserver'] = 'Vel LDAP-tenar å søkja i';
$messages['nocontactsreturned'] = 'Fann ingen kontaktar';
$messages['nosearchname'] = 'Skriv inn kontaktnamn eller epostadressa';
$messages['notuploadedwarning'] = 'Ikkje alle vedlegga er lasta opp. Vent eller avslutt opplastinga.';
$messages['searchsuccessful'] = 'Fann $nr meldingar';
$messages['searchnomatch'] = 'Søket fann ingenting';
$messages['searching'] = 'Søkjer...';
$messages['checking'] = 'Sjekkar...';
$messages['nospellerrors'] = 'Fann ingen stavefeil';
$messages['folderdeleted'] = 'Mappe sletta';
$messages['deletedsuccessfully'] = 'Sletta';
$messages['converting'] = 'Fjerner formattering frå melding...';
$messages['messageopenerror'] = 'Kunne ikkje lasta meldinga frå tenaren';
$messages['fileuploaderror'] = 'Filopplasting feila';
$messages['filesizeerror'] = 'Fila du lasta opp, er større enn maksimumsstorleiken, $size';
$messages['copysuccess'] = 'Kopierte $nr adresser';
$messages['copyerror'] = 'Kunne ikkje kopiera adresser';
$messages['sourceisreadonly'] = 'Denne adressekjelda er berre lesbar';
$messages['errorsavingcontact'] = 'Kunne ikkje lagra kontaktadressa';
$messages['movingmessage'] = 'Flyttar melding...';
$messages['copyingmessage'] = 'Kopierer epost...';
$messages['receiptsent'] = 'Sende melding-er-lest-kvittering';
$messages['errorsendingreceipt'] = 'Kunne ikkje senda melding-er-lest-kvittering';
$messages['nodeletelastidentity'] = 'Du kan ikkje sletta denne identiteten, det er den siste.';
$messages['forbiddencharacter'] = 'Mappenamn inneheld eit teikn som ikkje er støtta';
$messages['selectimportfile'] = 'Vel ei fil til å lasta opp';
$messages['addresswriterror'] = 'Denne adresseboka er ikkje skrivbar';
$messages['contactaddedtogroup'] = 'La til kontaktane til denne gruppa';
$messages['contactremovedfromgroup'] = 'Tok vekk kontaktane frå denne gruppa';
$messages['importwait'] = 'Importerer, vent...';
$messages['importerror'] = 'Importering feila! Fila er ikkje ei korrekt vCard-file.';
$messages['importconfirm'] = '<b>Importerte $inserted kontakar, hoppa over $skipped oppføringar</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Ulovleg operasjon.';
$messages['nofromaddress'] = 'Manglar epostadresse i denne identiteten';
$messages['editorwarning'] = 'Du mistar all formatering når du bytar til rein tekst. Er det greit?';
$messages['httpreceivedencrypterror'] = 'Noko er totalt feilkonfigurert, send ein sint epost til administrator. No. <b>Meldinga di kunne ikkje bli sendt.</b>';
$messages['smtpconnerror'] = 'SMTP-feil ($code): Tilkopling til tenar feila';
$messages['smtpautherror'] = 'SMTP-feil ($code): Autentisering feila';
$messages['smtpfromerror'] = 'SMTP-feil ($code): Feila å setja sendar «$from» ($msg)';
$messages['smtptoerror'] = 'SMTP-feil ($code): Feila å leggja til mottakar «$to» ($msg)';
$messages['smtprecipientserror'] = 'SMTP-feil: Eg forstår ikkje mottakarlista (sjå over han...)';
$messages['smtperror'] = 'SMTP-feil: $msg';
$messages['emailformaterror'] = 'Ukorrekt e-postadresse: $email';
$messages['toomanyrecipients'] = 'For mange mottakarar. Senk talet til maksimalt $max.';
$messages['maxgroupmembersreached'] = 'Du kan berre ha $max grupper, du er over grensa søta.';

?>
