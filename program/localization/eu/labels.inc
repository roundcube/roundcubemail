<?php

/*

 +-----------------------------------------------------------------------+
 | language/eu/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: pi <pi@beobide.net>                                           |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to Roundcube|Mail';
$labels['username']  = 'Erabiltzailea';
$labels['password']  = 'Pasahitza';
$labels['server']    = 'Zerbitzaria';
$labels['login']     = 'Saio hasiera';

// taskbar
$labels['logout']   = 'Irten';
$labels['mail']     = 'ePosta';
$labels['settings'] = 'Ezarpen Pertsonalak';
$labels['addressbook'] = 'Helbide Liburua';

// mailbox names
$labels['inbox']  = 'Sarrera';
$labels['sent']   = 'Bidalitakoak';
$labels['trash']  = 'Zakarrontzia';
$labels['drafts'] = 'Zirriborroak';
$labels['junk']   = 'Zabor-posta';

// message listing
$labels['subject'] = 'Gaia';
$labels['from']    = 'Nork';
$labels['to']      = 'Nori';
$labels['cc']      = 'Kopia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Erantzun-Hona';
$labels['date']    = 'Data';
$labels['size']    = 'Tamaina';
$labels['priority'] = 'Lehentasuna';
$labels['organization'] = 'Erakundea';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Karpetak';
$labels['messagesfromto'] = '$from -tik $to -ra $count mezuetatik';
$labels['messagenrof'] = '$nr mezua $count -tik';

$labels['moveto']   = 'mugitu hona...';
$labels['download'] = 'deskargatu';

$labels['filename'] = 'Fitxategi Izena';
$labels['filesize'] = 'Fitxategi Tamaiana';

$labels['preferhtml'] = 'HTML nahiago';
$labels['htmlmessage'] = 'HTML Mezua';
$labels['prettydate'] = 'Data politak';

$labels['addtoaddressbook'] = 'Helbide liburura gehitu';

// weekdays short
$labels['sun'] = 'Iga';
$labels['mon'] = 'Al';
$labels['tue'] = 'As';
$labels['wed'] = 'Azt';
$labels['thu'] = 'Og';
$labels['fri'] = 'Or';
$labels['sat'] = 'Lar';

// weekdays long
$labels['sunday']    = 'Igandea';
$labels['monday']    = 'Astelehena';
$labels['tuesday']   = 'Asteartea';
$labels['wednesday'] = 'Asteazkena';
$labels['thursday']  = 'Osteguna';
$labels['friday']    = 'Ostirala';
$labels['saturday']  = 'Larunbata';

$labels['today'] = 'Gaur';

// toolbar buttons
$labels['writenewmessage']  = 'Mezu berri bat sortu';
$labels['replytomessage']   = 'Mezuari erantzun';
$labels['forwardmessage']   = 'Mezua Birbidali';
$labels['deletemessage']    = 'Mezua zakarrontzira mugitu';
$labels['printmessage']     = 'Mezu hau inprimatu';
$labels['previousmessages'] = 'Aurreko mezuak bistarazi';
$labels['nextmessages']     = 'Hurrengo mezuak bistarazi';
$labels['backtolist']       = 'Mezu zerrendara itzuli';
$labels['viewsource']       = 'Jatorria bistarazi';

$labels['select'] = 'Hautatu';
$labels['all'] = 'Denak';
$labels['none'] = 'Batez';
$labels['unread'] = 'Irakurri gabeak';

// message compose
$labels['compose']  = 'Mezu bat sortu';
$labels['sendmessage']  = 'Mezua orain bidali';
$labels['addattachment']  = 'Fitxategia erantsi';

$labels['attachments'] = 'Eranskinak';
$labels['upload'] = 'Igo';
$labels['close']  = 'Itxi';

$labels['low']     = 'Baxua';
$labels['lowest']  = 'Baxuena';
$labels['normal']  = 'Normala';
$labels['high']    = 'Altua';
$labels['highest'] = 'Altuena';

$labels['showimages'] = 'Irudiak bistarazi';

// address boook
$labels['name']      = 'Izena bistarazi';
$labels['firstname'] = 'Izena';
$labels['surname']   = 'Abizena';
$labels['email']     = 'ePosta';

$labels['addcontact'] = 'Txartel berria gehitu';
$labels['editcontact'] = 'Txartela editatu';

$labels['edit']   = 'Editatu';
$labels['cancel'] = 'Utzi';
$labels['save']   = 'Gorde';
$labels['delete'] = 'Ezabatu';

$labels['newcontact']     = 'Helbide liburu txartel berria sortu';
$labels['deletecontact']  = 'Aukeraturiko txartelak ezabatu';
$labels['composeto']      = 'Bidali mezua honi: ';
$labels['contactsfromto'] = '$from -tik $to -ra $count txarteletatik';
$labels['print']          = 'Inprimatu';
$labels['export']         = 'Esportatu';

// settings
$labels['settingsfor']  = 'Honen ezarpenak:';

$labels['preferences']  = 'Hobespenak';
$labels['userpreferences']  = 'Erabiltzaile hobespenak';
$labels['editpreferences']  = 'Erabiltzaile hobespenak editatu';

$labels['identities']  = 'Identitateak';
$labels['manageidentities']  = 'Kontu honetako identitateak kudeatu';
$labels['newidentity']  = 'Identitate berria';

$labels['newitem']  = 'Elementu berria';
$labels['edititem']  = 'Elementua Editatu';

$labels['setdefault']  = 'Lehenespenak ezarri';
$labels['language']  = 'Hizkuntza';
$labels['timezone']  = 'Ordu zonaldea';
$labels['pagesize']  = 'Lerro orrialdeko';

$labels['folders']  = 'Karpetak';
$labels['foldername']  = 'Karpeta izena';
$labels['subscribed']  = 'Harpideturik';
$labels['create']  = 'Sortu';
$labels['createfolder']  = 'Karpeta berria sortu';
$labels['deletefolder']  = 'Karpeta ezabatu';
$labels['managefolders']  = 'Karpeta kudeaketa';

?>