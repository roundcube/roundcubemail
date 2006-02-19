<?php

/*
 +-----------------------------------------------------------------------+
 | language/ee/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Üllar Pajus <yllar.pajus@gmail.com>                           |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Kasutajanimi';
$labels['password']  = 'Parool';
$labels['server']    = 'Server';
$labels['login']     = 'Logi sisse';

// taskbar
$labels['logout']   = 'Logi välja';
$labels['mail']     = 'Postkast';
$labels['settings'] = 'Seaded';
$labels['addressbook'] = 'Aadressiraamat';

// mailbox names
$labels['inbox']  = 'Sissetulevad';
$labels['sent']   = 'Saadetud';
$labels['trash']  = 'Prügikast';
$labels['drafts'] = 'Ootel';
$labels['junk']   = 'Rämps';

// message listing
$labels['subject'] = 'Pealkiri';
$labels['from']    = 'Saatja';
$labels['to']      = 'Saaja';
$labels['cc']      = 'Koopia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Vastus aadressile';
$labels['date']    = 'Kuupäev';
$labels['size']    = 'Suurus';
$labels['priority'] = 'Tähtsus';
$labels['organization'] = 'Organisatsioon';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Kaustad';
$labels['messagesfromto'] = 'Kirjed $from kuni $to, kokku $count';
$labels['messagenrof'] = 'Kiri $nr, kokku $count';

$labels['moveto']   = 'liiguta kausta...';
$labels['download'] = 'lae arvutisse';

$labels['filename'] = 'Faili nimi';
$labels['filesize'] = 'Faili suurus';

$labels['preferhtml'] = 'Eelista HTMLi';
$labels['htmlmessage'] = 'HTML kirjad';
$labels['prettydate'] = 'Kenad kuupäevad';

$labels['addtoaddressbook'] = 'Lisa aadressiraamatusse';

// weekdays short
$labels['sun'] = 'P';
$labels['mon'] = 'E';
$labels['tue'] = 'T';
$labels['wed'] = 'K';
$labels['thu'] = 'N';
$labels['fri'] = 'R';
$labels['sat'] = 'L';

// weekdays long
$labels['sunday']    = 'Pühapäev';
$labels['monday']    = 'Esmaspäev';
$labels['tuesday']   = 'Teisipäev';
$labels['wednesday'] = 'Kolmapäev';
$labels['thursday']  = 'Neljapäev';
$labels['friday']    = 'Reede';
$labels['saturday']  = 'Laupäev';

$labels['today'] = 'Täna';

// toolbar buttons
$labels['writenewmessage']  = 'Kirjuta uus kiri';
$labels['replytomessage']   = 'Vasta kirjale';
$labels['replytoallmessage'] = 'Vasta saatjale ja teistele kirja saanutele';
$labels['forwardmessage']   = 'Edasta see kiri';
$labels['deletemessage']    = 'Liiguta kiri prügikasti';
$labels['printmessage']     = 'Trüki kiri';
$labels['previousmessages'] = 'Näita eelmisi kirju';
$labels['nextmessages']     = 'Näita järgmisi kirju';
$labels['backtolist']       = 'Tagasi kirjade nimekirja';
$labels['viewsource']       = 'Näita lähtekoodi';

$labels['select'] = 'Vali';
$labels['all'] = 'kõik';
$labels['none'] = 'mitte midagi';
$labels['unread'] = 'mitte loetud';

// message compose
$labels['compose']  = 'Koosta kiri';
$labels['sendmessage']  = 'Saada kiri kohe';
$labels['addattachment']  = 'Lisa fail';

$labels['attachments'] = 'Manused';
$labels['upload'] = 'Kinnita manus';
$labels['close']  = 'Sulge';

$labels['low']     = 'Madal';
$labels['lowest']  = 'Madalaim';
$labels['normal']  = 'Tavaline';
$labels['high']    = 'Kõrge';
$labels['highest'] = 'Kõrgeim';

$labels['nosubject']  = '(teema puudub)';
$labels['showimages'] = 'Näita pilte';
$labels['signature'] = 'Allkiri';
$labels['charset']  = 'Märgistik';

// address boook
$labels['name']      = 'Näidatav nimi';
$labels['firstname'] = 'Eesnimi';
$labels['surname']   = 'Perekonnanimi';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Lisa uus kontakt';
$labels['editcontact'] = 'Muuda kontakti';

$labels['edit']   = 'Muuda';
$labels['cancel'] = 'Katkesta';
$labels['save']   = 'Salvesta';
$labels['delete'] = 'Kustuta';

$labels['newcontact']     = 'Loo uus sissekanne';
$labels['deletecontact']  = 'Kustuta märgistatud kontaktid';
$labels['composeto']      = 'Kirjuta kiri';
$labels['contactsfromto'] = 'Kirjed $from kuni $to, kokku $count';
$labels['print']          = 'Trüki';
$labels['export']         = 'Ekspordi';


// settings
$labels['settingsfor']  = 'Kasutajaeelistused kontole';

$labels['preferences']  = 'Eelistused';
$labels['userpreferences']  = 'Kasutaja eelistused';
$labels['editpreferences']  = 'Muuda kasutaja eelistusi';

$labels['identities']  = 'Identiteedid';
$labels['manageidentities']  = 'Halda selle konto identiteete';
$labels['newidentity']  = 'Uus identiteet';

$labels['newitem']  = 'Uus sissekanne';
$labels['edititem']  = 'Muuda sissekannet';

$labels['setdefault']  = 'Muuda vaikeseadeks';
$labels['language']  = 'Keel';
$labels['timezone']  = 'Ajatsoon';
$labels['pagesize']  = 'Ridu lehe kohta';


$labels['folder']  = 'Kaust';
$labels['folders']  = 'Kaustad';
$labels['foldername']  = 'Kausta nimi';
$labels['subscribed']  = 'Näitan';
$labels['create']  = 'Loo';
$labels['createfolder']  = 'Loo uus kaust';
$labels['deletefolder']  = 'Kustuta kaust';
$labels['managefolders']  = 'Halda kaustu';
$labels['compact'] = 'Tihenda';
$labels['empty'] = 'Tühjenda';
$labels['purge'] = 'Puhasta';
$labels['quota'] = 'Ketta kasutus';

$labels['sortby'] = 'Järjesta';
$labels['sortasc']  = 'Järjesta kasvavalt';
$labels['sortdesc'] = 'Järjesta kahanevalt';

$labels['ldapsearch'] = 'LDAP kataloogi otsing';
$labels['ldappublicsearchname']    = 'Kontakti nimi';
$labels['ldappublicsearchtype'] = 'Täpne vaste ?';
$labels['ldappublicserverselect'] = 'Vali server';
$labels['ldappublicsearchfield'] = 'Otsi kohast';
$labels['ldappublicsearchform'] = 'Otsi kontakti';
$labels['ldappublicsearch'] = 'Otsi';

?>