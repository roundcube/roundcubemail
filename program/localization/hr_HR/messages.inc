<?php

/*

+-----------------------------------------------------------------------+
| language/hr/messages.inc                                              |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Edi Budimilic <edi@gemstudio.hr>                              |
|       Tanja Dobričić <tanja.dobricic@everymail.info>                  |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Prijava neuspješna';
$messages['cookiesdisabled'] = 'Vaš Web preglednik ne podržava kolačiće (cookies)';
$messages['sessionerror'] = 'Morate se ponovno ulogirati';
$messages['imaperror'] = 'Neuspješna veza na IMAP server';
$messages['servererror'] = 'Greška poslužitelja';
$messages['servererrormsg'] = 'Greška na serveru: $msg';
$messages['databaserror'] = 'Greška baze podataka!';
$messages['errorreadonly'] = 'Nemoguća izvedba operacije. Mapa je samo za čitanje.';
$messages['errornoperm'] = 'Nemoguća izvedba operacije. Dozvola odbijena.';
$messages['invalidrequest'] = 'Nepravilan zahtijev! Podaci nisu spremljeni.';
$messages['nomessagesfound'] = 'Nema poruka u sandučiću';
$messages['loggedout'] = 'Uspješno ste se odjavili. Zbogom!';
$messages['mailboxempty'] = 'Sandučuć je prazan';
$messages['loading'] = 'Učitavanje...';
$messages['uploading'] = 'Prenosim datoteku...';
$messages['uploadingmany'] = 'Prenosim datoteke...';
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
$messages['errorcopying'] = 'Greška pri kopiranju poruke';
$messages['errordeleting'] = 'Greška pri brisanju poruke';
$messages['errormarking'] = 'Nije moguće označiti poruku';
$messages['deletecontactconfirm'] = 'Želite li obrisati izabrane kontakte?';
$messages['deletemessagesconfirm'] = 'Sigurno želite obrisati odabrane poruke?';
$messages['deletefolderconfirm'] = 'Želite li obrisati ovu mapu?';
$messages['purgefolderconfirm'] = 'Želite li obrisati sve poruke u mapi?';
$messages['folderdeleting'] = 'Brišem mapu...';
$messages['foldermoving'] = 'Premještam mapu...';
$messages['foldersubscribing'] = 'Pretplata na mapu...';
$messages['folderunsubscribing'] = 'Poništavanje pretplate na mapu...';
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
$messages['foldersubscribed'] = 'Mapa uspješno pretplaćena';
$messages['folderunsubscribed'] = 'Pretplata na mapu uspješno poništena';
$messages['folderpurged'] = 'Mapa uspješno ispražnjena';
$messages['folderexpunged'] = 'Mapa uspješno zbijena';
$messages['deletedsuccessfully'] = 'Uspješno obrisano';
$messages['converting'] = 'Formatiranje poruke';
$messages['messageopenerror'] = 'Učitavanje poruke nije uspjelo';
$messages['fileuploaderror'] = 'Prijenos datoteke nije uspio';
$messages['filesizeerror'] = 'Datoteka je prevelika. Maksimalna veličina je $size';
$messages['copysuccess'] = 'Uspješno kopirano $n adresa';
$messages['copyerror'] = 'Nije uspjelo kopiranje adresa';
$messages['sourceisreadonly'] = 'Ovaj resurs adresa je samo za čitanje';
$messages['errorsavingcontact'] = 'Nije uspjelo spremanje adrese kontakta';
$messages['movingmessage'] = 'Premještanje poruke...';
$messages['copyingmessage'] = 'Kopiranje poruke...';
$messages['deletingmessage'] = 'Brisanje poruke...';
$messages['markingmessage'] = 'Označavanje poruke...';
$messages['receiptsent'] = 'Uspješno poslana potvrda (pročitano)';
$messages['errorsendingreceipt'] = 'Ne može poslati potvrdu';
$messages['nodeletelastidentity'] = 'Ne možete izbrisati zadnji identitet.';
$messages['forbiddencharacter'] = 'Naziv mape sadrži zabranjene znakove';
$messages['selectimportfile'] = 'Odaberite datoteku za prijenos';
$messages['addresswriterror'] = 'U odabrani adresar nije moguće zapisivat...';
$messages['contactaddedtogroup'] = 'Kontakti uspješno dodani ovoj grupi';
$messages['contactremovedfromgroup'] = 'Kontakti uspješno izbrisani iz ove grupe';
$messages['importwait'] = 'Uvozim, molimo sačekajte...';
$messages['importerror'] = 'Greška pri uvozu! Datoteka ne sadrži pravilan vCard format.';
$messages['importconfirm'] = '<b>Uspješno je uvezeno $inserted kontakt(a), preskočeno $skipped već postojećih</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Preskočeno $skipped postojećih unosa</b>';
$messages['opnotpermitted'] = 'Operacija nije dozvoljena!';
$messages['nofromaddress'] = 'Nije upisana e-mail adresa u odabrani identitet';
$messages['editorwarning'] = 'Prebacivanje u čisti tekstualni uređivač će prouzrokovati gubljenje formatiranje teksta. Želite li nastaviti?';
$messages['httpreceivedencrypterror'] = 'Dogodila se fatalna greška u konfiguraciji. Odmah kontaktirajte administratora. <b>Vaša poruka se nemože poslati.</b>';
$messages['smtpconnerror'] = 'SMTP Greška ($code): Veza na server nije uspjela';
$messages['smtpautherror'] = 'SMTP Greška ($code): Autentikacija nije uspjela';
$messages['smtpfromerror'] = 'SMTP Greška ($code): Nije uspjelo postavljanje pošiljatelja "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP Greška ($code): Nije uspjelo dodavanje primatelja "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP Greška: Nije moguće pročitati listu primatelja';
$messages['smtpdsnerror'] = 'SMTP Greška: Nema podrške za obaviještenje o statusu isporuke';
$messages['smtperror'] = 'SMTP Greška: $msg';
$messages['emailformaterror'] = 'Nepravilna e-mail adresa: $email';
$messages['toomanyrecipients'] = 'Previše primatelja. Smanjite broj primatelja na $max.';
$messages['maxgroupmembersreached'] = 'Broj članova grupe prelazi preko maximuma od $max';
$messages['internalerror'] = 'Dogodila se interna greška. Molimo Vas pokušajte ponovo.';
$messages['contactdelerror'] = 'Kontakti ne mogu biti obrisani.';
$messages['contactdeleted'] = 'Kontakti uspješno obrisani.';
$messages['groupdeleted'] = 'Grupa uspješno obrisana.';
$messages['grouprenamed'] = 'Grupa uspješno preimenovana.';
$messages['groupcreated'] = 'Grupa uspješno kreirana.';
$messages['messagedeleted'] = 'Poruke uspješno obrisane.';
$messages['messagemoved'] = 'Poruke uspješno pomjerene.';
$messages['messagecopied'] = 'Poruke uspješno kopirane.';
$messages['messagemarked'] = 'Poruke uspješno označene.';
$messages['autocompletechars'] = 'Unesite barem $min znakova za auto-dopunjavanje.';
$messages['namecannotbeempty'] = 'Polje za ime ne može biti prazno.';
$messages['nametoolong'] = 'Ime predugo.';
$messages['folderupdated'] = 'Ažuriranje mape uspješno.';
$messages['foldercreated'] = 'Kreiranje mape uspješno.';
$messages['invalidimageformat'] = 'Format slike nije ispravan.';

?>
