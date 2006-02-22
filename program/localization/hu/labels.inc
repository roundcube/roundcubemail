<?php

/*
 +-----------------------------------------------------------------------+
 | language/hu/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Translator: Ervin Hegedüs <airween@damson.hu>                         |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Felhasználónév';
$labels['password']  = 'Jelszó';
$labels['server']    = 'Kiszolgáló';
$labels['login']     = 'Belépés';

// taskbar
$labels['logout']   = 'Kijelentkezés';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Egyéni beállítások';
$labels['addressbook'] = 'Címjegyzék';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['sent']   = 'Küldött';
$labels['trash']  = 'Törölt';
$labels['drafts'] = 'Piszkozatok';
$labels['junk']   = 'Szemét';

// message listing
$labels['subject'] = 'Tárgy';
$labels['from']    = 'Küldő';
$labels['to']      = 'Címzett';
$labels['cc']      = 'Másolat';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Válaszcím';
$labels['date']    = 'Dátum';
$labels['size']    = 'Méret';
$labels['priority'] = 'Sűrgősség';
$labels['organization'] = 'Szervezet';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mappák';
$labels['messagesfromto'] = 'Üzenetek: $from - $to, össz.: $count';
$labels['messagenrof'] = '$nr. üzenet, összesen $count';

$labels['moveto']   = 'mozgatás...';
$labels['download'] = 'letöltés';

$labels['filename'] = 'Fájl neve';
$labels['filesize'] = 'Fájl mérete';

$labels['preferhtml'] = 'HTML megj.';
$labels['htmlmessage'] = 'HTML üzenet';
$labels['prettydate'] = 'Rövid dátumok';

$labels['addtoaddressbook'] = 'Hozzáadás a címjegyzékhez';

// weekdays short
$labels['sun'] = 'Vas';
$labels['mon'] = 'Hét';
$labels['tue'] = 'Kedd';
$labels['wed'] = 'Szer';
$labels['thu'] = 'Csüt';
$labels['fri'] = 'Pén';
$labels['sat'] = 'Szom';

// weekdays long
$labels['sunday']    = 'Vasárnap';
$labels['monday']    = 'Hétfő';
$labels['tuesday']   = 'Kedd';
$labels['wednesday'] = 'Szerda';
$labels['thursday']  = 'Csütörtök';
$labels['friday']    = 'Péntek';
$labels['saturday']  = 'Szombat';

$labels['today'] = 'Ma';

// toolbar buttons
$labels['writenewmessage']  = 'Új üzenet létrehozása';
$labels['replytomessage']   = 'Válasz az üzenetre';
$labels['replytoallmessage'] = 'Válasz a feladónak és az összes címzettnek';
$labels['forwardmessage']   = 'Üzenet továbbítása';
$labels['deletemessage']    = 'Mozgatás a lomtárba';
$labels['printmessage']     = 'Üzenet nyomtatása';
$labels['previousmessages'] = 'Előző rész mutatása';
$labels['nextmessages']     = 'Következő rész mutatása';
$labels['backtolist']       = 'Vissza az üzenetekhez';
$labels['viewsource']       = 'Forrás megtekintése';

$labels['select'] = 'Kiválasztás';
$labels['all'] = 'Mind';
$labels['none'] = 'Nincs';
$labels['unread'] = 'Olvasatlan';

$labels['compact'] = 'Tömörített';
$labels['empty'] = 'Üres';
$labels['purge'] = 'Tisztítás';

$labels['quota'] = 'Diszk használat';


// message compose
$labels['compose']  = 'Üzenet létrehozása';
$labels['sendmessage']  = 'Üzenet azonnali küldése';
$labels['addattachment']  = 'Fájlmelléklet hozzáadása';
$labels['charset']  = 'Karakterkészlet';

$labels['attachments'] = 'Mellékletek';
$labels['upload'] = 'Feltöltés';
$labels['close']  = 'Bezárás';

$labels['low']     = 'Alacsony';
$labels['lowest']  = 'Legkissebb';
$labels['normal']  = 'Normál';
$labels['high']    = 'Magas';
$labels['highest'] = 'Legmagasabb';

$labels['nosubject']  = '(nincs tárgy)';
$labels['showimages'] = 'Képek megjelenítése';


// address boook
$labels['name']      = 'Megjelenített név';
$labels['firstname'] = 'Keresztnév';
$labels['surname']   = 'Vezetéknév';
$labels['email']     = 'E-Mail cím';

$labels['addcontact'] = 'Új kapcsolat hozzáadása';
$labels['editcontact'] = 'Kapcsolat szerkesztése';

$labels['edit']   = 'Szerkesztés';
$labels['cancel'] = 'Mégsem';
$labels['save']   = 'Mentés';
$labels['delete'] = 'Törlés';

$labels['newcontact']     = 'Új névjegykártya létrehozása';
$labels['addcontact']     = 'Kiválasztott névjegy hozzáadása a címjegyzékhez';
$labels['deletecontact']  = 'Kijelölt kapcsolatok törlése';
$labels['composeto']      = 'Mail létrehozása erre a címre';
$labels['contactsfromto'] = 'Kapcsolatok: $from - $to, össz.: $count';
$labels['print']          = 'Nyomtatás';
$labels['export']         = 'Export';

// LDAP search
$labels['ldapsearch'] = 'Keresés LDAP címtárban';

$labels['ldappublicsearchname']    = 'Név';
$labels['ldappublicsearchtype'] = 'Teljes egyezés?';
$labels['ldappublicserverselect'] = 'Kiszolgáló választás';
$labels['ldappublicsearchfield'] = 'Keresés';
$labels['ldappublicsearchform'] = 'Kapcsolat keresése';
$labels['ldappublicsearch'] = 'Keresés';


// settings
$labels['settingsfor']  = 'Beállítás';

$labels['preferences']  = 'Tulajdonságok';
$labels['userpreferences']  = 'Felhasználó tulajdonságai';
$labels['editpreferences']  = 'Felhasználói tulajdonságok szerkesztése';

$labels['identities']  = 'Azonosítók';
$labels['manageidentities']  = 'Azonosítók kezelése';
$labels['newidentity']  = 'Új azonosító';

$labels['newitem']  = 'Új elem';
$labels['edititem']  = 'Elem szerkesztése';

$labels['setdefault']  = 'Beállítás alapértelmezettnek';
$labels['language']  = 'Nyelv';
$labels['timezone']  = 'Időzóna';
$labels['pagesize']  = 'Sorok száma egy oldalon';
$labels['signature'] = 'Aláírás';

$labels['folder']  = 'Mappa';
$labels['folders']  = 'Mappák';
$labels['foldername']  = 'Mappa neve';
$labels['subscribed']  = 'Feliratkozás';
$labels['create']  = 'Létrehozás';
$labels['createfolder']  = 'Új mappa létrehozása';
$labels['deletefolder']  = 'Mappa törlése';
$labels['managefolders']  = 'Mappák kezelése';

$labels['sortby'] = 'Rendezés';
$labels['sortasc']  = 'növekvő';
$labels['sortdesc'] = 'csökkenő';

?>
