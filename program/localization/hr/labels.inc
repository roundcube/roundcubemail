<?php

/*
 +-----------------------------------------------------------------------+
 | language/hr/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author:        Robi Markovic <robiNOSPAM.markovic@gmail.com>          |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page // Login-Seite
$labels['username']  = 'E-mail Korisnika';
$labels['password']  = 'Lozinka';
$labels['server']    = 'Server';
$labels['login']     = 'Prijava';

// taskbar // Aktionsleiste
$labels['logout']   = 'Odjava';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Osobne Postavke';
$labels['addressbook'] = 'Adresar';

// mailbox names // E-Mail-Ordnernamen
$labels['inbox']  = 'Primljene';
$labels['sent']   = 'Poslate';
$labels['trash']  = 'Izbrisane(Smeće)';
$labels['drafts'] = 'Draft';
$labels['junk']   = 'Spam(Junk)';

// message listing // Nachrichtenliste
$labels['subject'] = 'Naslov';
$labels['from']    = 'Pošiljaoc';
$labels['to']      = 'Primaoc';
$labels['cc']      = 'Kopija (CC)';
$labels['bcc']     = 'Slijepa kopija (BCC)';
$labels['replyto'] = 'Odgovori na';
$labels['date']    = 'Datum';
$labels['size']    = 'Veličina';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organizacija';

// aliases // [Platzhalter]
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mape';
$labels['messagesfromto'] = 'Poruke $from do $to od $count';
$labels['messagenrof'] = 'Poruke $nr od $count';

$labels['moveto']   = 'Prebaci u...';
$labels['download'] = 'Snimi(Download)';

$labels['filename'] = 'Ime datoteke';
$labels['filesize'] = 'Veličina datoteke';

$labels['preferhtml'] = 'Sa HTML';
$labels['htmlmessage'] = 'HTML Poruka';
$labels['prettydate'] = 'Kratak prikaz datuma';

$labels['addtoaddressbook'] = 'Dodajte u adresar';

// weekdays short // Wochentage (Abkürzungen) 
$labels['sun'] = 'Ne';
$labels['mon'] = 'Po';
$labels['tue'] = 'Ut';
$labels['wed'] = 'Sr';
$labels['thu'] = 'Če';
$labels['fri'] = 'Pe';
$labels['sat'] = 'Su';

// weekdays long // Wochentage (normal)
$labels['sunday']    = 'Nedjelja';
$labels['monday']    = 'Ponedjeljak';
$labels['tuesday']   = 'Utorak';
$labels['wednesday'] = 'Srijeda';
$labels['thursday']  = 'Četvrtak';
$labels['friday']    = 'Petak';
$labels['saturday']  = 'Subota';

$labels['today'] = 'Danas';

// toolbar buttons // Symbolleisten-Tipps
$labels['writenewmessage']  = 'Napisati novu poruku';
$labels['replytomessage']   = 'Odogovoriti na poruku';
$labels['replytoallmessage'] = 'Odgovoriti pošiljaocu i svim primateljima';
$labels['forwardmessage']   = 'Proslijediti poruku';
$labels['deletemessage']    = 'Prebaciti poruku u "Izbrisane Poruke"';
$labels['printmessage']     = 'Odštampati poruku';
$labels['previousmessages'] = 'Pokazati prethodni set poruka';
$labels['nextmessages']     = 'Pokazati naredni set poruka';
$labels['backtolist']       = 'Povratak na listu poruka';

$labels['select'] = 'Odabrati';
$labels['all']    = 'Sve';
$labels['none']   = 'Nijednu';
$labels['unread'] = 'Nepročitane';

$labels['compact'] = 'Kompaktirati';


// message compose // Nachrichten erstellen
$labels['compose']  = 'Napisati poruku';
$labels['sendmessage']  = 'Poslati poruku sada';
$labels['addattachment']  = 'Dodati datoteku';
$labels['charset']  = 'Standard';

$labels['attachments'] = 'Dodatci';
$labels['upload'] = 'Ubaciti';
$labels['close']  = 'Zatvoriti';

$labels['low']     = 'Nisko';
$labels['lowest']  = 'Najniže';
$labels['normal']  = 'Normalno';
$labels['high']    = 'Visoko';
$labels['highest'] = 'Najviše';

$labels['nosubject']  = '(nema naslova)';
$labels['showimages'] = 'Prikazati slike';


// address book // Adressbuch
$labels['name']      = 'Prikazano ime';
$labels['firstname'] = 'Ime';
$labels['surname']   = 'Prezime';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Dodati novi kontakt';
$labels['editcontact'] = 'Izmjeniti kontakt';

$labels['edit']   = 'Izmjeniti';
$labels['cancel'] = 'Otkazati';
$labels['save']   = 'Snimiti';
$labels['delete'] = 'Obrisati';

$labels['newcontact']     = 'Napraviti novu kontakt karticu';
$labels['deletecontact']  = 'Obrisati odabrane kontakte';
$labels['composeto']      = 'Napisati mail na';
$labels['contactsfromto'] = 'Kontakti $from do $to od $count';
$labels['print']          = 'Odštampati';
$labels['export']         = 'Izvesti(Export)';

// LDAP search
$labels['ldapsearch'] = 'LDAP mapu pretraži';

$labels['ldappublicsearchname']   = 'Ime kontakta';
$labels['ldappublicsearchtype']   = 'Točan tip pretrage';
$labels['ldappublicserverselect'] = 'Odaberi server';
$labels['ldappublicsearchfield']  = 'Traži u';
$labels['ldappublicsearchform']   = 'Adresu pretraži';
$labels['ldappublicsearch'] = 'Pretraži';

// settings // Einstellungen
$labels['settingsfor']  = 'Podešavanje za';

$labels['preferences']  = 'Postavke';
$labels['userpreferences']  = 'Korisničke postavke';
$labels['editpreferences']  = 'Izmjeniti korisničke postavke';

$labels['identities']  = 'Identitet';
$labels['manageidentities']  = 'Uredi identitete za ovog korisnika';
$labels['newidentity']  = 'Novi Identitet';

$labels['newitem']  = 'Novi unos';
$labels['edititem']  = 'Izmjeni unos';

$labels['setdefault']  = 'Postavi standardno';
$labels['language']  = 'Jezik';
$labels['timezone']  = 'Vremenska zona';
$labels['pagesize']  = 'Poruka po stranici';
$labels['signature'] = 'Potpis';

$labels['folder']  = 'Mapa';
$labels['folders']  = 'Mape';
$labels['foldername']  = 'Ime mape';
$labels['subscribed']  = 'Pretplatiti';
$labels['create']      = 'Napraviti';
$labels['createfolder']  = 'Napraviti novi mapu';
$labels['deletefolder']  = 'Izbrisati mapu';
$labels['managefolders']  = 'Uredi mape';

$labels['sortby'] = 'Sortiraj po';
$labels['sortasc']  = 'Sortiraj opadajuće';
$labels['sortdesc'] = 'Sortiraj rastuće';

?>
