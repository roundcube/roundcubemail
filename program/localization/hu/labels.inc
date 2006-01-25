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
$labels['logout']   = 'Kilépés';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Személyes beállítások';
$labels['addressbook'] = 'Címjegyzék';

// mailbox names
$labels['inbox']  = 'Beérkezett üzenetek';
$labels['sent']   = 'Küldött üzenetek';
$labels['trash']  = 'Törölt elemek';
$labels['drafts'] = 'Sémák';
$labels['junk']   = 'Szemét';

// message listing
$labels['subject'] = 'Tárgy';
$labels['from']    = 'Feladó';
$labels['to']      = 'Címzett';
$labels['cc']      = 'Másolat';
$labels['bcc']     = 'Titkos másolat';
$labels['replyto'] = 'Válasz cím';
$labels['date']    = 'Dátum';
$labels['size']    = 'Méret';
$labels['priority'] = 'Elsőbbségi';
$labels['organization'] = 'Szervezet';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mappák';
$labels['messagesfromto'] = 'üzenetek: $from - $to, össz.: $count';
$labels['messagenrof'] = '$nr. üzenet, összes: $count';

$labels['moveto']   = 'mozgatás ide...';
$labels['download'] = 'letöltés';

$labels['filename'] = 'Fájl neve';
$labels['filesize'] = 'Fájl mérete';

$labels['preferhtml'] = 'HTML mail';
$labels['htmlmessage'] = 'HTML üzenet';
$labels['prettydate'] = 'Rövid dátumok';

$labels['addtoaddressbook'] = 'Hozzáadás a címjegyzékhez';

// weekdays short
$labels['sun'] = 'Va';
$labels['mon'] = 'Hé';
$labels['tue'] = 'Ke';
$labels['wed'] = 'Sze';
$labels['thu'] = 'Csü';
$labels['fri'] = 'Pé';
$labels['sat'] = 'Szo';

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
$labels['writenewmessage']  = 'Új levél létrehozása';
$labels['replytomessage']   = 'Válasz a levélre';
$labels['replytoallmessage'] = 'Válasz mindenkinek';
$labels['forwardmessage']   = 'Levél továbbítása';
$labels['deletemessage']    = 'Levél törlése';
$labels['printmessage']     = 'Levél nyomtatása';
$labels['previousmessages'] = 'Levél előző részének mutatása';
$labels['nextmessages']     = 'Levél következő részének mutatása';
$labels['backtolist']       = 'Vissza az üzenetek listájához';
$labels['viewsource']       = 'Forrás megtekintése';

$labels['select'] = 'Kijelölés';
$labels['all'] = 'Mind';
$labels['none'] = 'Nincs';
$labels['unread'] = 'Olvasatlan';

// message compose
$labels['compose']  = 'üzenet létrehozása';
$labels['sendmessage']  = 'Levél küldése';
$labels['addattachment']  = 'Fájl melléklet csatolása';
$labels['charset']  = 'Karakter kódolás';

$labels['attachments'] = 'Csatolmányok';
$labels['upload'] = 'Feltöltés';
$labels['close']  = 'Bezárás';

$labels['low']     = 'Alacsony';
$labels['lowest']  = 'Legalacsonyabb';
$labels['normal']  = 'Normál';
$labels['high']    = 'Magas';
$labels['highest'] = 'Legmagasabb';

$labels['nosubject'] = '(nincs tárgy)';
$labels['showimages'] = 'Képek megjelenítése';


// address boook
$labels['name']      = 'Megjelenítendő név';
$labels['firstname'] = 'Keresztnév';
$labels['surname']   = 'Vezetéknév';
$labels['email']     = 'E-Mail cím';

$labels['addcontact'] = 'Új cím hozzáadása';
$labels['editcontact'] = 'Cím szerkesztése';

$labels['edit']   = 'Szerkesztés';
$labels['cancel'] = 'Mégsem';
$labels['save']   = 'Mentés';
$labels['delete'] = 'Törlés';

$labels['newcontact']     = 'Új névjegykártya hozzáadása';
$labels['deletecontact']  = 'A kijelölt névjegyek törlése';
$labels['composeto']      = 'Leél küldése a kijelölt címre';
$labels['contactsfromto'] = 'Névjegyek: $from - $to, össz.: $count';
$labels['print']          = 'Nyomtatás';
$labels['export']         = 'Export';


// settings
$labels['settingsfor']  = 'Settings for';

$labels['preferences']  = 'Tulajdonságok';
$labels['userpreferences']  = 'Felhasználó tulajdonságai';
$labels['editpreferences']  = 'Felhasználói tulajdonságok szerkesztése';

$labels['identities']  = 'Azonosítók';
$labels['manageidentities']  = 'A fiókhoz tartozó azonosítók szerkesztése';
$labels['newidentity']  = 'Új azonosító';

$labels['newitem']  = 'Új elem';
$labels['edititem']  = 'Elem szerkesztése';

$labels['setdefault']  = 'Alapértelmezés beállítása';
$labels['language']  = 'Nyelv';
$labels['timezone']  = 'Időzóna';
$labels['pagesize']  = 'Sorok száma egy oldalon';
$labels['signature']  = 'Aláírás';

$labels['folders']  = 'Mappák';
$labels['foldername']  = 'Mappa neve';
$labels['subscribed']  = 'Megjelenítendő';
$labels['create']  = 'Létrehozás';
$labels['createfolder']  = 'Új mappa létrehozása';
$labels['deletefolder']  = 'Mappa törlése';
$labels['managefolders']  = 'Mappák kezelése';

$labels['sortby']  = 'Rendezés';
$labels['sortasc']  = 'Növekvő';
$labels['sortdesc']  = 'Csökkenő';

?>
