<?php

/*
 +-----------------------------------------------------------------------+
 | language/lv/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Rudolfs Osins <dev.random@gmail.com>                          |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Lietotājvārds';
$labels['password']  = 'Parole';
$labels['server']    = 'Serveris';
$labels['login']     = 'Pieslēgties';

// taskbar
$labels['logout']   = 'Atslēgties';
$labels['mail']     = 'E-pasts';
$labels['settings'] = 'Personīgie iestatījumi';
$labels['addressbook'] = 'Adrešu grāmata';

// mailbox names
$labels['inbox']  = 'Ienākošās';
$labels['sent']   = 'Nosūtītās';
$labels['trash']  = 'Miskaste';
$labels['drafts'] = 'Uzmetumi';
$labels['junk']   = 'Mēstules';

// message listing
$labels['subject'] = 'Temats';
$labels['from']    = 'No';
$labels['to']      = 'Kam';
$labels['cc']      = 'Kopija';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Atbildēt uz';
$labels['date']    = 'Datums';
$labels['size']    = 'Izmērs';
$labels['priority'] = 'Prioritāte';
$labels['organization'] = 'Organizācija';

// aliases
$labels['reply-to'] = $labels['atbildēt uz'];

$labels['mailboxlist'] = 'Mapes';
$labels['messagesfromto'] = 'Vēstules $from līdz $to no $count';
$labels['messagenrof'] = '$nr. vēstule  no $count';

$labels['moveto']   = 'pārvietot uz...';
$labels['download'] = 'lejupielādēt';

$labels['filename'] = 'Faila nosaukums';
$labels['filesize'] = 'Faila izmērs';

$labels['preferhtml'] = 'Dot priekšroku HTML';
$labels['htmlmessage'] = 'HTML vēstule';
$labels['prettydate'] = 'Skaists datums';

$labels['addtoaddressbook'] = 'Pievienot adrešu grāmatai';

// weekdays short
$labels['sun'] = 'Sv';
$labels['mon'] = 'P';
$labels['tue'] = 'O';
$labels['wed'] = 'T';
$labels['thu'] = 'C';
$labels['fri'] = 'P';
$labels['sat'] = 'Se';

// weekdays long
$labels['sunday']    = 'Svētdiena';
$labels['monday']    = 'Pirmdiena';
$labels['tuesday']   = 'Otrdiena';
$labels['wednesday'] = 'Trešdiena';
$labels['thursday']  = 'Ceturtdiena';
$labels['friday']    = 'Piektdiena';
$labels['saturday']  = 'Sestdiena';

$labels['today'] = 'Šodien';

// toolbar buttons
$labels['writenewmessage']  = 'Rakstīt jaunu vēstuli';
$labels['replytomessage']   = 'Atbildēt uz vēstuli';
$labels['forwardmessage']   = 'Pārsūtīt vēstuli';
$labels['deletemessage']    = 'Pārvietot vēstuli uz miskasti';
$labels['printmessage']     = 'Izdrukāt šo vēstuli';
$labels['previousmessages'] = 'Parādīt iepriekšējo vēstuļu kopu';
$labels['nextmessages']     = 'Parādīt nākamo vēstuļu kopu';
$labels['backtolist']       = 'Atpakaļ uz vēstuļu sarakstu';

$labels['select'] = 'Iezīmēt';
$labels['all'] = 'Visas';
$labels['none'] = 'Nevienu';
$labels['unread'] = 'Nelasītās';

// message compose
$labels['compose']  = 'Rakstīt vēstuli';
$labels['sendmessage']  = 'Sūtīt vēstuli';
$labels['addattachment']  = 'Pievienot failu';

$labels['attachments'] = 'Pielikumi';
$labels['upload'] = 'Augšupielādēt';
$labels['close']  = 'Aizvērt';

$labels['low']     = 'Zema';
$labels['lowest']  = 'Zemākā';
$labels['normal']  = 'Normāla';
$labels['high']    = 'Augsta';
$labels['highest'] = 'Augstākā';

$labels['showimages'] = 'Rādīt attēlus';


// address boook
$labels['name']      = 'Uzrādītais vārds';
$labels['firstname'] = 'Vārds';
$labels['surname']   = 'Uzvārds';
$labels['email']     = 'E-pasts';

$labels['addcontact'] = 'Pievienot ierakstu adrešu grāmatā';
$labels['editcontact'] = 'Rediģēt adrešu grāmatas ierakstu';

$labels['edit']   = 'Rediģēt';
$labels['cancel'] = 'Atcelt';
$labels['save']   = 'Saglabāt';
$labels['delete'] = 'Dzēst';

$labels['newcontact']     = 'Izveidot jaunu ierakstu adrešu grāmatā';
$labels['deletecontact']  = 'Dzēst iezīmētos ierakstus';
$labels['composeto']      = 'Rakstīt vēstuli';
$labels['contactsfromto'] = 'Ieraksti $from līdz $to no $count';


// settings
$labels['settingsfor']  = 'Iestatījumi';

$labels['preferences']  = 'Preferences';
$labels['userpreferences']  = 'Lietotāja preferences';
$labels['editpreferences']  = 'Rediģēt lietotāja preferences';

$labels['identities']  = 'Identitātes';
$labels['manageidentities']  = 'Rediģēt šī konta identitātes';
$labels['newidentity']  = 'Jauna identitāte';

$labels['newitem']  = 'Jauns';
$labels['edititem']  = 'Rediģēt';

$labels['setdefault']  = 'Uzlikt kā noklusēto';
$labels['language']  = 'Valoda';
$labels['timezone']  = 'Laika zona';
$labels['pagesize']  = 'Rindas lapā';


$labels['folders']  = 'Mapes';
$labels['foldername']  = 'Mapes nosaukums';
$labels['subscribed']  = 'Abonētās';
$labels['create']  = 'Izveidot';
$labels['createfolder']  = 'Izveidot jaunu mapi';
$labels['deletefolder']  = 'Dzēst mapi';
$labels['managefolders']  = 'Rediģēt mapes';


?>