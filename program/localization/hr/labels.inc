<?php

/*
 +-----------------------------------------------------------------------+
 | language/hr/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundQube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Edi Budimilic <edifreak@gmail.com>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Dobrodošli na $product';
$labels['username']  = 'E-mail';
$labels['password']  = 'Lozinka';
$labels['server']    = 'Server';
$labels['login']     = 'Prijava';

// taskbar
$labels['logout']   = 'Odjava';
$labels['mail']     = 'Sandučić';
$labels['settings'] = 'Postavke';
$labels['addressbook'] = 'Imenik';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Predlošci';
$labels['sent']   = 'Poslano';
$labels['trash']  = 'Smeće';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Naslov';
$labels['from']    = 'Od';
$labels['to']      = 'Za';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'Datum';
$labels['size']    = 'Veličina';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organizacija';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Folderi';
$labels['messagesfromto'] = 'Poruke od $from do $to od ukupno $count';
$labels['messagenrof'] = 'Poruka broj $nr od $count';

$labels['moveto']   = 'prebaci u...';
$labels['download'] = 'preuzmi (download)';

$labels['filename'] = 'Ime datoteke';
$labels['filesize'] = 'Veličina datoteke';

$labels['preferhtml'] = 'HTML format u prednosti';
$labels['htmlmessage'] = 'HTML format poruke';
$labels['prettydate'] = 'Formatiran datum';

$labels['addtoaddressbook'] = 'Dodaj u imenik';

// weekdays short
$labels['sun'] = 'Ned';
$labels['mon'] = 'Pon';
$labels['tue'] = 'Uto';
$labels['wed'] = 'Sri';
$labels['thu'] = 'Čet';
$labels['fri'] = 'Pet';
$labels['sat'] = 'Sub';

// weekdays long
$labels['sunday']    = 'Nedjelja';
$labels['monday']    = 'Ponedjeljak';
$labels['tuesday']   = 'Utorak';
$labels['wednesday'] = 'Srijeda';
$labels['thursday']  = 'Četvrtak';
$labels['friday']    = 'Petak';
$labels['saturday']  = 'Subota';

$labels['today'] = 'danas';

// toolbar buttons
$labels['checkmail']        = 'Provjera novih poruka';
$labels['writenewmessage']  = 'Nova poruka';
$labels['replytomessage']   = 'Odgovori na poruku';
$labels['replytoallmessage'] = 'Odgovori pošiljaocu i svim primateljima';
$labels['forwardmessage']   = 'Prosljedi poruku';
$labels['deletemessage']    = 'Prebaci poruku u folder (Smeće)';
$labels['printmessage']     = 'Isprintajte poruku';
$labels['previousmessages'] = 'Prijašnje poruke';
$labels['nextmessages']     = 'Sljedeće poruke';
$labels['backtolist']       = 'Povratak na listu poruka';
$labels['viewsource']       = 'Prikaži poruku u izvornom obliku';

$labels['select'] = 'Oznaći';
$labels['all'] = 'Sve';
$labels['none'] = 'Nijednu';
$labels['unread'] = 'Nepročitane';

$labels['compact'] = 'Kompresiranje';
$labels['empty'] = 'Izprazni';
$labels['purge'] = 'Očisti';

$labels['quota'] = 'Kvota';
$labels['unknown']  = 'nepoznato';
$labels['unlimited']  = 'beskonačna';

$labels['quicksearch']  = 'Brza pretraga';
$labels['resetsearch']  = 'Prikaži sve poruke';


// message compose
$labels['compose']  = 'Nova poruka';
$labels['savemessage']  = 'Spremi u \'Predlošci\'';
$labels['sendmessage']  = 'Pošalji poruku';
$labels['addattachment']  = 'Priloži datoteku';
$labels['charset']  = 'Charset';
$labels['returnreceipt'] = 'Potvrda o primitku poruke';

$labels['checkspelling'] = 'Provjera pravopisa';
$labels['resumeediting'] = 'Povratak u pisanje';
$labels['revertto'] = 'Revert to';

$labels['attachments'] = 'Privitci (Attachments)';
$labels['upload'] = 'Dodaj';
$labels['close']  = 'Zatvori';

$labels['low']     = 'Nizak';
$labels['lowest']  = 'Najniži';
$labels['normal']  = 'Srednji';
$labels['high']    = 'Visok';
$labels['highest'] = 'Najviši';

$labels['nosubject']  = '(bez naslova)';
$labels['showimages'] = 'Prikaži slike';


// address boook
$labels['name']      = 'Puno ime';
$labels['firstname'] = 'Ime';
$labels['surname']   = 'Prezime';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Dodaj novi kontakt';
$labels['editcontact'] = 'Izmjeni kontakt';

$labels['edit']   = 'Izmjeni';
$labels['cancel'] = 'Odustani';
$labels['save']   = 'Spremi';
$labels['delete'] = 'Obriši';

$labels['newcontact']     = 'Dodaj novi kontakt';
$labels['deletecontact']  = 'Obriši odabrane kontakte';
$labels['composeto']      = 'Napiši mail...';
$labels['contactsfromto'] = 'Kontakti od $from do $to od ukupno $count';
$labels['print']          = 'Print';
$labels['export']         = 'Export';

$labels['previouspage']   = 'Sljedeća strana';
$labels['nextpage']       = 'Prijašnja strana';


// LDAP search
$labels['ldapsearch'] = 'Pretraga LDAP imenika';

$labels['ldappublicsearchname']    = 'Ime kontakta';
$labels['ldappublicsearchtype'] = 'Potpun termin?';
$labels['ldappublicserverselect'] = 'Odaberi servere';
$labels['ldappublicsearchfield'] = 'Pretraži na';
$labels['ldappublicsearchform'] = 'Pretraži kontakte';
$labels['ldappublicsearch'] = 'Pretraga';


// settings
$labels['settingsfor']  = 'Postavke za';

$labels['preferences']  = 'Postavke';
$labels['userpreferences']  = 'Korisnićke postavke';
$labels['editpreferences']  = 'Izmjena postavki';

$labels['identities']  = 'Identiteti';
$labels['manageidentities']  = 'Podesi identitete za ovaj nalog';
$labels['newidentity']  = 'Dodaj identitet';

$labels['newitem']  = 'New item';
$labels['edititem']  = 'Edit item';

$labels['setdefault']  = 'Set default';
$labels['language']  = 'Jezik';
$labels['timezone']  = 'Vremenska zona';
$labels['pagesize']  = 'Redova po stranici';
$labels['signature'] = 'Potpis';
$labels['dstactive']  = 'Automatska promjena vremena';

$labels['folder']  = 'Folder';
$labels['folders']  = 'Folderi';
$labels['foldername']  = 'Ime foldera';
$labels['subscribed']  = 'Pretplata';
$labels['create']  = 'Napravi';
$labels['createfolder']  = 'Napravi novi folder';
$labels['rename'] = 'Preimenuj';
$labels['renamefolder'] = 'Preimenuj Folder';
$labels['deletefolder']  = 'Obriši Folder';
$labels['managefolders']  = 'Podesi Folder';

$labels['sortby'] = 'Sortiraj po';
$labels['sortasc']  = 'Sortiraj rastućim nizom';
$labels['sortdesc'] = 'Sortiraj opadajućim nizom';

?>
