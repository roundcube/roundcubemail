<?php

/*
 +-----------------------------------------------------------------------+
 | language/hu/messages.inc                                              |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Translator: Ervin Hegedüs <airween@damson.hu>                         |
 +-----------------------------------------------------------------------+
 + Translation updated by rezso <rezso@rezso.net> on 2006-12-18          +
 + Thanks to dblaci <dblaci@dblaci.hu> for help.                         +
 +-----------------------------------------------------------------------+

 $Id$

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
$labels['inbox']  = 'Érkezett';
$labels['sent']   = 'Küldött';
$labels['trash']  = 'Törölt';
$labels['drafts'] = 'Piszkozatok';
$labels['junk']   = 'Szemét';

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
$labels['previousmessage']  = 'Előző üzenet';
$labels['previousmessages'] = 'Előző oldal';
$labels['firstmessage']     = 'Első üzenet';
$labels['firstmessages']    = 'Első oldal';
$labels['nextmessage']      = 'Következő üzenet';
$labels['nextmessages']     = 'Következő oldal';
$labels['lastmessage']      = 'Utolsó üzenet';
$labels['lastmessages']     = 'Utolsó oldal';
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
$labels['unknown'] = 'ismeretlen';
$labels['unlimited'] = 'korlátlan';

$labels['quicksearch'] = 'Gyorskeresés';
$labels['resetsearch'] = 'Keresés törlése';

// message compose
$labels['compose']  = 'Üzenet létrehozása';
$labels['savemessage']  = 'Vázlat mentése';
$labels['sendmessage']  = 'Üzenet azonnali küldése';
$labels['addattachment']  = 'File csatolása';
$labels['charset']  = 'Karakterkészlet';
$labels['editortype']     = 'Szerkesztő típusa';
$labels['returnreceipt']  = 'Értesítés a kézbesítésről';

$labels['checkspelling'] = 'Helyesírásellenõrzés';
$labels['resumeediting'] = 'Szerkesztés folytatása';
$labels['revertto']      = 'Vissza';

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

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Egyszerű szöveg';

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

$labels['previouspage']   = 'Előző oldal';
$labels['firstpage']      = 'Első oldal';
$labels['nextpage']       = 'Következő oldal';
$labels['lastpage']       = 'Utolsó oldal';

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
$labels['dstactive'] = 'Nyári idõszámítás';
$labels['htmleditor'] = 'HTML üzenet szerkesztése';
$labels['htmlsignature'] = 'HTML aláírás';
$labels['previewpane'] = 'Előnézet panel megjelenítése';

$labels['autosavedraft'] = 'Piszkozat automatikus mentése';
$labels['everynminutes'] = 'minden $n percben';
$labels['never'] = 'soha';

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
