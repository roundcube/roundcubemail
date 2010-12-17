<?php

/*

+-----------------------------------------------------------------------+
| language/hr/messages.inc                                              |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Edi Budimilic <edi@gemstudio.hr>                              |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Prijava neuspješna';
$messages['cookiesdisabled'] = 'Vaš Web preglednik ne podržava kolačiće (cookies)';
$messages['sessionerror'] = 'Morate se ponovno ulogirati';
$messages['imaperror'] = 'Neuspješna veza na IMAP server';
$messages['servererror'] = 'Greška poslužitelja';
$messages['invalidrequest'] = 'Nepravilan zahtijev! Podaci nisu spremljeni.';
$messages['nomessagesfound'] = 'Nema poruka u sandučiću';
$messages['loggedout'] = 'Uspješna odjava.';
$messages['mailboxempty'] = 'Sandučuć je prazan';
$messages['loading'] = 'Učitavanje...';
$messages['uploading'] = 'Presnimavak datoteku na poslužitelj';
$messages['loadingdata'] = 'Učitavanje podataka...';
$messages['checkingmail'] = 'Provjera novih poruka u tijeku...';
$messages['sendingmessage'] = 'Poruka se šalje...';
$messages['messagesent'] = 'Poruka je uspješno poslana';
$messages['savingmessage'] = 'Poruka se sprema...';
$messages['messagesaved'] = 'Poruka uspješno spremljena u \'Predlošci\'';
$messages['successfullysaved'] = 'Spremanje uspješno obavljeno';
$messages['addedsuccessfully'] = 'Kontakt uspješno dodan u imenik';
$messages['contactexists'] = 'Kontakt sa ovom e-mail adresom već postoji u imeniku';
$messages['blockedimages'] = 'Zbog zaštite privatnosti, slike sa udaljenih servera su blokirane.';
$messages['encryptedmessage'] = 'Ova poruka je zaštićena pa se ne može prikazati';
$messages['nocontactsfound'] = 'Imenik je prazan';
$messages['contactnotfound'] = 'Traženi kontakt nije pronađen';
$messages['sendingfailed'] = 'Greška pri slanju poruke';
$messages['senttooquickly'] = 'Molimo sačekajte $sec sek. prije slanja ove poruke';
$messages['errorsavingsent'] = 'Greška pri spremanju poslane poruke';
$messages['errorsaving'] = 'Greška pri spremanju';
$messages['errormoving'] = 'Greška pri premještanju poruke';
$messages['errordeleting'] = 'Greška pri brisanju poruke';
$messages['errormarking'] = 'Nije moguće označiti poruku';
$messages['deletecontactconfirm'] = 'Želite li obrisati izabrane kontakte?';
$messages['deletemessagesconfirm'] = 'Sigurno želite obrisati odabrane poruke?';
$messages['deletefolderconfirm'] = 'Želite li obrisati ovu mapu?';
$messages['purgefolderconfirm'] = 'Želite li obrisati sve poruke u mapi?';
$messages['folderdeleting'] = 'Brišem mapu...';
$messages['foldermoving'] = 'Premještam mapu...';
$messages['formincomplete'] = 'Obrazac nije u cjelosti popunjen';
$messages['noemailwarning'] = 'Unesite valjanu e-mail adresu';
$messages['nonamewarning'] = 'Unesite ime';
$messages['nopagesizewarning'] = 'Unesite veličinu stranice';
$messages['nosenderwarning'] = 'Unesite e-mail adresu pošiljatelja';
$messages['norecipientwarning'] = 'Unesite primatelja (ZA)';
$messages['nosubjectwarning'] = 'Polje \'Naslov\' je prazno. Želite li unijeti naslov?';
$messages['nobodywarning'] = 'Želite li poslati poruku bez teksta?';
$messages['notsentwarning'] = 'Poruka nije poslana. Želite li odbaciti ovu poruku?';
$messages['noldapserver'] = 'Unesite LDAP poslužitelj za pretragu';
$messages['nocontactsreturned'] = 'Nije pronađen niti jedan kontakt';
$messages['nosearchname'] = 'Unesite ime ili e-mail adresu';
$messages['notuploadedwarning'] = 'Svi prilozi još nisu presnimljeni na poslužitelj. Molim pričekajte ili zaustavite prsnimavanje.';
$messages['searchsuccessful'] = 'Broj pronađenih poruka: $nr';
$messages['searchnomatch'] = 'Traženi termin nije pronađen ni u jednoj poruci';
$messages['searching'] = 'Pretraga u tijeku...';
$messages['checking'] = 'Provjera u tijeku...';
$messages['nospellerrors'] = 'Nije pronađena niti jedna pravopisna greška';
$messages['folderdeleted'] = 'Mapa uspješno obrisana';
$messages['deletedsuccessfully'] = 'Uspješno obrisano';
$messages['converting'] = 'Formatiranje poruke';
$messages['messageopenerror'] = 'Nije moguće ';
$messages['fileuploaderror'] = 'Prijenos datoteke nije uspio';
$messages['filesizeerror'] = 'Datoteka je prevelika. Maksimalna veličina je $size';
$messages['copysuccess'] = 'Uspješno kopirano $n adresa';
$messages['copyerror'] = 'Nije uspjelo kopiranje adresa';
$messages['sourceisreadonly'] = 'Ovaj resurs adresa je samo za čitanje';
$messages['errorsavingcontact'] = 'Nije uspjelo spremanje adrese kontakta';
$messages['movingmessage'] = 'Premještanje poruke...';
$messages['receiptsent'] = 'Uspješno poslana potvrda (pročitano)';
$messages['errorsendingreceipt'] = 'Ne može poslati potvrdu';
$messages['nodeletelastidentity'] = 'Ne možete izbrisati zadnji identitet.';
$messages['forbiddencharacter'] = 'Naziv mape sadrži zabranjene znakove';
$messages['selectimportfile'] = 'Odaberite datoteku za prijenos';
$messages['addresswriterror'] = 'U odabrani adresar nije moguće zapisivat...';
$messages['importwait'] = 'Uvozim, molimo sačekajte...';
$messages['importerror'] = 'Greška pri uvozu! Datoteka ne sadrži pravilan vCard format.';
$messages['importconfirm'] = '<b>Uspješno je uvezeno $inserted kontakt(a), preskočeno $skipped već postojećih</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operacija nije dozvoljena!';
$messages['nofromaddress'] = 'Nije upisana e-mail adresa u odabrani identitet';
$messages['editorwarning'] = 'Prebacivanje u čisti tekstualni uređivač će prouzrokovati gubljenje formatiranje teksta. Želite li nastaviti?';
$messages['httpreceivedencrypterror'] = 'Dogodila se fatalna greška u konfiguraciji. Odmah kontaktirajte administratora. <b>Vaša poruka se nemože poslati.</b>';
$messages['smtpconnerror'] = 'SMTP Greška ($code): Veza na server nije uspjela';
$messages['smtpautherror'] = 'SMTP Greška ($code): Autentikacija nije uspjela';
$messages['smtpfromerror'] = 'SMTP Greška ($code): Nije uspjelo postavljanje pošiljatelja "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP Greška ($code): Nije uspjelo dodavanje primatelja "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP Greška: Nije moguće pročitati listu primatelja';
$messages['smtperror'] = 'SMTP Greška: $msg';
$messages['emailformaterror'] = 'Nepravilna e-mail adresa: $email';

?>
