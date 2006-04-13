<?php

/*
 +-----------------------------------------------------------------------+
 | language/si/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Bostjan Oblak <bostjan@muha.cc>, Gregor Kavcic <grega@muha.cc>|
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Uporabniško ime';
$labels['password']  = 'Geslo';
$labels['server']    = 'Strežnik';
$labels['login']     = 'Prijava';

// taskbar
$labels['logout']   = 'Odjava';
$labels['mail']     = 'E-Pošta';
$labels['settings'] = 'Osebne nastavitve';
$labels['addressbook'] = 'Stiki';

// mailbox names
$labels['inbox']  = 'Prejeto';
$labels['sent']   = 'Poslano';
$labels['trash']  = 'Smeti';
$labels['drafts'] = 'Osnutki';
$labels['junk']   = 'Nezaželena  sporočila';

// message listing
$labels['subject'] = 'Zadeva';
$labels['from']    = 'Pošiljatelj';
$labels['to']      = 'Prejemnik';
$labels['cc']      = 'Kopija';
$labels['bcc']     = 'Skp';
$labels['replyto'] = 'Odgovor na';
$labels['date']    = 'Datum';
$labels['size']    = 'Velikost';
$labels['priority'] = 'Prioriteta';
$labels['organization'] = 'Organizacija';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mape';
$labels['messagesfromto'] = 'Sporočila $from do $to od $count';
$labels['messagenrof'] = 'Sporočilo $nr od $count';

$labels['moveto']   = 'premakni v...';
$labels['download'] = 'prenesi';
	
$labels['filename'] = 'Ime datoteke';
$labels['filesize'] = 'Velikost datoteke';

$labels['preferhtml'] = 'Prednostno HTML';
$labels['htmlmessage'] = 'HTML sporočilo';
$labels['prettydate'] = 'Lepi datumi';

$labels['addtoaddressbook'] = 'Dodaj v stike';

// weekdays short
$labels['sun'] = 'Ned';
$labels['mon'] = 'Pon';
$labels['tue'] = 'Tor';
$labels['wed'] = 'Sre';
$labels['thu'] = 'Čet';
$labels['fri'] = 'Pet';
$labels['sat'] = 'Sob';

// weekdays long
$labels['sunday']    = 'Nedelja';
$labels['monday']    = 'Ponedeljek';
$labels['tuesday']   = 'Torek';
$labels['wednesday'] = 'Sreda';
$labels['thursday']  = 'Četrtek';
$labels['friday']    = 'Petek';
$labels['saturday']  = 'Sobota';

$labels['today'] = 'Danes';

// toolbar buttons
$labels['writenewmessage']  = 'Novo sporočilo';
$labels['replytomessage']   = 'Odgovori na sporočilo';
$labels['forwardmessage']   = 'Posreduj sporočilo';
$labels['deletemessage']    = 'Premakni sporočilo v smeti';
$labels['printmessage']     = 'Tiskaj to sporočilo';
$labels['previousmessages'] = 'Prikaži predhodni set sporočil';
$labels['nextmessages']     = 'Prikaži naslednji set sporočil';
$labels['backtolist']       = 'Nazaj na seznam sporočil';

$labels['select'] = 'Izberi';
$labels['all'] = 'Vse';
$labels['none'] = 'Nobenih';
$labels['unread'] = 'Neprebrano';

$labels['compact'] = 'Stistni';
$labels['empty'] = 'Izprazni';
$labels['purge'] = 'Počisti';

$labels['quota'] = 'Poraba diska';


// message compose
$labels['compose']  = 'Sestavi sporočilo';
$labels['sendmessage']  = 'Pošlji sporočilo zdaj';
$labels['addattachment']  = 'Pripni datoteko';
$labels['charset']  = 'Nabor znakov';

$labels['attachments'] = "Priponke";
$labels['upload'] = 'Naloži';
$labels['close']  = 'Zapri';

$labels['low']     = 'Nizka';
$labels['lowest']  = 'Najnižja';
$labels['normal']  = 'Normalna';
$labels['high']    = 'Visoka';
$labels['highest'] = 'Najvišja';

$labels['nosubject']  = '(brez naslova)';
$labels['showimages'] = 'Prikaži slike';


// address boook
$labels['name']      = 'Ime za prikaz';
$labels['firstname'] = 'Ime';
$labels['surname']   = 'Priimek';
$labels['email']     = $labels['mail'];

$labels['addcontact'] = 'Dodaj nov stik';
$labels['editcontact'] = 'Uredi stik';

$labels['edit']   = 'Uredi';
$labels['cancel'] = 'Prekliči';
$labels['save']   = 'Shrani';
$labels['delete'] = 'Briši';

$labels['newcontact']     = 'Sestavi novo karto za stik';
$labels['addcontact']     = 'Dodaj izbrane stike v imenik';
$labels['deletecontact']  = 'Izbriši izbrane kontakte';
$labels['composeto']      = 'Sestavi sporočilo za';
$labels['contactsfromto'] = 'Stiki $from od $to do $count';
$labels['print']          = 'Natisni';
$labels['export']         = 'Izvozi';

// LDAP search
$labels['ldapsearch'] = 'LDAP iskanje';

$labels['ldappublicsearchname']    = 'Ime kontakta';
$labels['ldappublicsearchtype'] = 'Natančni zadetki?';
$labels['ldappublicserverselect'] = 'Izberi strežnike';
$labels['ldappublicsearchfield'] = 'Išči po';
$labels['ldappublicsearchform'] = 'Išči kontakt';
$labels['ldappublicsearch'] = 'Išči';


// settings
$labels['settingsfor']  = 'Nastavitve za';

$labels['preferences']  = 'Nastavitve';
$labels['userpreferences']  = 'Uporabniške nastavitve';
$labels['editpreferences']  = 'Uredi uporabniške nastavitve';

$labels['identities']  = 'Identitete';
$labels['manageidentities']  = 'Upravljaj identitete za ta račun';
$labels['newidentity']  = 'Nova identiteta';

$labels['newitem']  = 'Nov predmet';
$labels['edititem']  = 'Uredi predmet';

$labels['setdefault']  = 'Nastavi privzet';
$labels['language']  = 'Jezik';
$labels['timezone']  = 'Časovni pas';
$labels['pagesize']  = 'Vrstic na stran';
$labels['signature'] = 'Podpis';

$labels['folder']  = 'Mapa';
$labels['folders']  = 'Mape';
$labels['foldername']  = 'Ime mape';
$labels['subscribed']  = 'Vpisan';
$labels['create']  = 'Ustvari';
$labels['createfolder']  = 'Ustvari novo mapo';
$labels['deletefolder']  = 'Izbrisi mapo';
$labels['managefolders']  = 'Urejaj mape';

$labels['sortby'] = 'Uredi po';
$labels['sortasc']  = 'Uredi naraščajoče';
$labels['sortdesc'] = 'Uredi padajoče';

?>
