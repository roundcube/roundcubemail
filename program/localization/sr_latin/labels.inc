<?php

/*

 +-----------------------------------------------------------------------+
 | language/sr_latin/labels.inc                                          |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Viktor Varga <vile@neobee.net>                                |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Dobrodošli na $product';
$labels['username']  = 'Korisničko ime';
$labels['password']  = 'Lozinka';
$labels['server']    = 'Server';
$labels['login']     = 'Prijava';

// taskbar
$labels['logout']   = 'Odjava';
$labels['mail']     = 'Sanduče';
$labels['settings'] = 'Podešavanja';
$labels['addressbook'] = 'Imenik';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Drafts';
$labels['sent']   = 'Sent';
$labels['trash']  = 'Trash';
$labels['junk']   = 'Junk';

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

$labels['mailboxlist'] = 'Fascikle';
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
$labels['wed'] = 'Sre';
$labels['thu'] = 'Čet';
$labels['fri'] = 'Pet';
$labels['sat'] = 'Sub';

// weekdays long
$labels['sunday']    = 'Nedelja';
$labels['monday']    = 'Ponedeljak';
$labels['tuesday']   = 'Utorak';
$labels['wednesday'] = 'Sreda';
$labels['thursday']  = 'Četvrtak';
$labels['friday']    = 'Petak';
$labels['saturday']  = 'Subota';

$labels['today'] = 'danas';

// toolbar buttons
$labels['checkmail']        = 'Provera novih poruka';
$labels['writenewmessage']  = 'Nova poruka';
$labels['replytomessage']   = 'Odgovori na poruku';
$labels['replytoallmessage'] = 'Odgovori pošiljaocu i svim primaocima';
$labels['forwardmessage']   = 'Prosledi poruku';
$labels['deletemessage']    = 'Prebaci poruku u korpu (Trash)';
$labels['printmessage']     = 'Odštampaj poruku';
$labels['previousmessages'] = 'Prikaži prethodni skup poruka';
$labels['nextmessages']     = 'Prikaži sledeći skup poruka';
$labels['backtolist']       = 'Povratak na listu poruka';
$labels['viewsource']       = 'Prikaži poruku u izvornom obliku';

$labels['select'] = 'Obeleži';
$labels['all'] = 'Sve';
$labels['none'] = 'Nijednu';
$labels['unread'] = 'Nepročitane';

$labels['compact'] = 'Kompakcija';
$labels['empty'] = 'Izprazni';
$labels['purge'] = 'Očisti';

$labels['quota'] = 'Kvota';
$labels['unknown']  = 'nepoznato';
$labels['unlimited']  = 'beskonačna';

$labels['quicksearch']  = 'Brza pretraga';
$labels['resetsearch']  = 'Prikaži sve poruke';

// message compose
$labels['compose']  = 'Nova poruka';
$labels['savemessage']  = 'Sačuvaj u \'Drafts\'';
$labels['sendmessage']  = 'Pošalji poruku';
$labels['addattachment']  = 'Priloži datoteku';
$labels['charset']  = 'Karakter-set';
$labels['returnreceipt'] = 'Potvrda o prispeću poruke';

$labels['checkspelling'] = 'Provera pravopisa';
$labels['resumeediting'] = 'Povratak u režim pisanja';
$labels['revertto'] = 'Revert to';

$labels['attachments'] = 'Prilozi (Attachments)';
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
$labels['editcontact'] = 'Izmeni kontakt';

$labels['edit']   = 'Izmeni';
$labels['cancel'] = 'Otkaži';
$labels['save']   = 'Sačuvaj';
$labels['delete'] = 'Obriši';

$labels['newcontact']     = 'Dodaj novi kontakt';
$labels['deletecontact']  = 'Obriši odabrane kontakte';
$labels['composeto']      = 'Napiši mail...';
$labels['contactsfromto'] = 'Kontakti od $from do $to od ukupno $count';
$labels['print']          = 'Štampaj';
$labels['export']         = 'Izvezi';

$labels['previouspage']   = 'Prikaži predhodni skup';
$labels['nextpage']       = 'Prikaži sledeći skup';

// LDAP search
$labels['ldapsearch'] = 'Pretraga LDAP imenika';

$labels['ldappublicsearchname']    = 'Ime kontakta';
$labels['ldappublicsearchtype'] = 'Potpuna fraza?';
$labels['ldappublicserverselect'] = 'Odaberi servere';
$labels['ldappublicsearchfield'] = 'Pretraži na';
$labels['ldappublicsearchform'] = 'Pretraži kontakte';
$labels['ldappublicsearch'] = 'Pretraga';

// settings
$labels['settingsfor']  = 'Podešavanja za';

$labels['preferences']  = 'Podešavanja';
$labels['userpreferences']  = 'Opšta podešavanja';
$labels['editpreferences']  = 'Izmena opštih podešavanja';

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
$labels['dstactive']  = 'Letnje pomeranje vremena';

$labels['folder']  = 'Fascikla';
$labels['folders']  = 'Fascikle';
$labels['foldername']  = 'Ime fascikle';
$labels['subscribed']  = 'Subscribed';
$labels['create']  = 'Kreiraj';
$labels['createfolder']  = 'Kreiraj novu fasciklu';
$labels['rename'] = 'Preimenuj';
$labels['renamefolder'] = 'Preimenuj Fasciklu';
$labels['deletefolder']  = 'Obriši Fasciklu';
$labels['managefolders']  = 'Podesi Fasciklu';

$labels['sortby'] = 'Sortiraj po';
$labels['sortasc']  = 'Sortiraj rastućim nizom';
$labels['sortdesc'] = 'Sortiraj opadajućim nizom';

?>