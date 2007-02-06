<?php

/*

 +-----------------------------------------------------------------------+
 | language/hu/messages.inc                                              |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: PtY <peti@tuxwiki.hu> on 2007-01-05                           |
 |         rezso <rezso@rezso.net> on 2006-09-19                         |
 |         Ervin Hegedüs <airween@damson.hu>                             |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Üdvözli a $product';
$labels['username']  = 'Felhasználónév';
$labels['password']  = 'Jelszó';
$labels['server']    = 'Szerver';
$labels['login']     = 'Belépés';

// taskbar
$labels['logout']   = 'Kijelentkezés';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Egyéni beállítások';
$labels['addressbook'] = 'Címjegyzék';

// mailbox names
$labels['inbox']  = 'Érkezett levelek';
$labels['sent']   = 'Küldött levelek';
$labels['trash']  = 'Törölt elemek';
$labels['drafts'] = 'Piszkozatok';
$labels['junk']   = 'Szemetes';
$labels['templates']  = 'Sablonok';

// message listing
$labels['subject'] = 'Tárgy';
$labels['from']    = 'Feladó';
$labels['to']      = 'Címzett';
$labels['cc']      = 'Másolat';
$labels['bcc']     = 'Titkos másolat';
$labels['replyto'] = 'Válaszcím';
$labels['date']    = 'Dátum';
$labels['size']    = 'Méret';
$labels['priority'] = 'Sürgősség';
$labels['organization'] = 'Szervezet';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mappák';
$labels['messagesfromto'] = 'Üzenetek: $from - $to / $count';
$labels['messagenrof'] = '$nr / $count üzenet';

$labels['moveto']   = 'áthelyezés...';
$labels['download'] = 'letöltés';

$labels['filename'] = 'File neve';
$labels['filesize'] = 'File mérete';

$labels['preferhtml'] = 'HTML megjelenítés';
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
$labels['checkmail']        = 'Új üzenetek ellenőrzése';
$labels['writenewmessage']  = 'Új üzenet';
$labels['replytomessage']   = 'Válasz';
$labels['replytoallmessage'] = 'Válasz a feladónak és az összes címzettnek';
$labels['forwardmessage']   = 'Továbbítás';
$labels['deletemessage']    = 'Törlés';
$labels['printmessage']     = 'Nyomtatás';
$labels['previousmessages'] = 'Előző rész mutatása';
$labels['nextmessages']     = 'Következő rész mutatása';
$labels['backtolist']       = 'Vissza az üzenetekhez';
$labels['viewsource']       = 'Forrás megtekintése';

$labels['select'] = 'Kijelölés';
$labels['all'] = 'Összes';
$labels['none'] = 'Nincs';
$labels['unread'] = 'Olvasatlan';

$labels['compact'] = 'Tömörítés';
$labels['empty'] = 'Ürítés';
$labels['purge'] = 'Tisztítás';

$labels['quota'] = 'Diszk használat';
$labels['unknown']  = 'ismeretlen';
$labels['unlimited']  = 'korlátlan';

$labels['quicksearch']  = 'Gyorskeresés';
$labels['resetsearch']  = 'Alapállapot';

// message compose
$labels['compose']  = 'Üzenet létrehozása';
$labels['savemessage']  = 'Vázlat mentése';
$labels['sendmessage']  = 'Üzenet azonnali küldése';
$labels['addattachment']  = 'File csatolása';
$labels['charset']  = 'Karakterkészlet';

$labels['attachments'] = 'Csatolások';
$labels['upload'] = 'Feltöltés';
$labels['close']  = 'Bezárás';

$labels['low']     = 'Alacsony';
$labels['lowest']  = 'Legkisebb';
$labels['normal']  = 'Normál';
$labels['high']    = 'Magas';
$labels['highest'] = 'Legmagasabb';

$labels['nosubject']  = '(nincs tárgy)';
$labels['showimages'] = 'Képek megjelenítése';

$labels['returnreceipt'] = 'Értesítés';

$labels['checkspelling'] = 'Helyesírás-ellenőrzés';
$labels['resumeediting'] = 'Helyesírás kész';
$labels['revertto'] = 'Vissza erre:';

// address book
$labels['name']      = 'Megjelenített név';
$labels['firstname'] = 'Keresztnév';
$labels['surname']   = 'Vezetéknév';
$labels['email']     = 'E-mail cím';

$labels['addcontact'] = 'Új kapcsolat hozzáadása';
$labels['editcontact'] = 'Kapcsolat szerkesztése';

$labels['edit']   = 'Szerkesztés';
$labels['cancel'] = 'Mégsem';
$labels['save']   = 'Mentés';
$labels['delete'] = 'Törlés';

$labels['newcontact']     = 'Új névjegykártya létrehozása';
$labels['deletecontact']  = 'Kijelölt kapcsolatok törlése';
$labels['composeto']      = 'E-mail küldése erre a címre';
$labels['contactsfromto'] = 'Kapcsolatok: $from - $to / $count';
$labels['print']          = 'Nyomtatás';
$labels['export']         = 'Exportálás';

$labels['previouspage']   = 'Előző lap';
$labels['nextpage']       = 'Következő lap';

// LDAP search
$labels['ldapsearch'] = 'Keresés LDAP címtárban';

$labels['ldappublicsearchname']    = 'Kapcsolat neve';
$labels['ldappublicsearchtype'] = 'Pontos egyezés?';
$labels['ldappublicserverselect'] = 'Szerverek kiválasztása';
$labels['ldappublicsearchfield'] = 'Keresés:';
$labels['ldappublicsearchform'] = 'Kapcsolat keresése';
$labels['ldappublicsearch'] = 'Keresés';

// settings
$labels['settingsfor']  = 'Beállítás';

$labels['preferences']  = 'Beállítások';
$labels['userpreferences']  = 'Felhasználói beállítások';
$labels['editpreferences']  = 'Felhasználói beállítások szerkesztése';

$labels['identities']  = 'Azonosítók';
$labels['manageidentities']  = 'Hozzáférés azonosítóinak kezelése';
$labels['newidentity']  = 'Új azonosító';

$labels['newitem']  = 'Új elem';
$labels['edititem']  = 'Elem szerkesztése';

$labels['setdefault']  = 'Beállítás alapértelmezettnek';
$labels['language']  = 'Nyelv';
$labels['timezone']  = 'Időzóna';
$labels['pagesize']  = 'Sorok száma egy oldalon';
$labels['signature'] = 'Aláírás';
$labels['dstactive']  = 'Nyári időszámítás';

$labels['folder']  = 'Mappa';
$labels['folders']  = 'Mappák';
$labels['foldername']  = 'Mappa neve';
$labels['subscribed']  = 'Feliratkozás';
$labels['create']  = 'Létrehozás';
$labels['createfolder']  = 'Új mappa létrehozása';
$labels['rename'] = 'Átnevezés';
$labels['renamefolder'] = 'Mappa átnevezése';
$labels['deletefolder']  = 'Mappa törlése';
$labels['managefolders']  = 'Mappák kezelése';

$labels['sortby'] = 'Rendezés';
$labels['sortasc']  = 'növekvő';
$labels['sortdesc'] = 'csökkenő';

?>