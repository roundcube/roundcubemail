<?php

/*
 +-----------------------------------------------------------------------+
 | language/da/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
 | Danish translation: Martin Moeller <martin@liga.dk>                   |
 +-----------------------------------------------------------------------+
 
 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Brugernavn';
$labels['password']  = 'Adgangskode';
$labels['server']    = 'Server';
$labels['login']     = 'Log på';

// taskbar
$labels['logout']   = 'Log af';
$labels['mail']     = 'Email';
$labels['settings'] = 'Personlige indstillinger';
$labels['addressbook'] = 'Adressebog';

// mailbox names
$labels['inbox']  = 'Indbakke';
$labels['sent']   = 'Sendt post';
$labels['trash']  = 'Skrald';
$labels['drafts'] = 'Kladder';
$labels['junk']   = 'Ragelse';

// message listing
$labels['subject'] = 'Emne';
$labels['from']    = 'Afsender';
$labels['to']      = 'Modtager';
$labels['cc']      = 'Kopi til';
$labels['bcc']     = 'BCC';
$labels['replyto'] = 'Svar til';
$labels['date']    = 'Dato';
$labels['size']    = 'Størrelse';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisation';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Foldere';
$labels['messagesfromto'] = 'Beskeder $from til $to af $count';
$labels['messagenrof'] = 'Besked $nr af $count';

$labels['moveto']   = 'flyt til...';
$labels['download'] = 'download';

$labels['filename'] = 'Filnavn';
$labels['filesize'] = 'Filstørrelse';

$labels['preferhtml'] = 'Foretræk HTML';
$labels['htmlmessage'] = 'HTML besked';

$labels['addtoaddressbook'] = 'Tilføj til adressebogen';

// weekdays short
$labels['sun'] = 'Søn';
$labels['mon'] = 'Man';
$labels['tue'] = 'Tir';
$labels['wed'] = 'Ons';
$labels['thu'] = 'Tor';
$labels['fri'] = 'Fre';
$labels['sat'] = 'Lør';

// weekdays long
$labels['sunday']    = 'Søndag';
$labels['monday']    = 'Mandag';
$labels['tuesday']   = 'Tirsdag';
$labels['wednesday'] = 'Onsdag';
$labels['thursday']  = 'Torsdag';
$labels['friday']    = 'Fredag';
$labels['saturday']  = 'Lørdag';

$labels['today'] = 'I dag';

// toolbar buttons
$labels['writenewmessage']  = 'Opret en ny besked';
$labels['replytomessage']   = 'Svar på denne besked';
$labels['forwardmessage']   = 'Videresend denne besked';
$labels['deletemessage']    = 'Flyt beskeden til skrald';
$labels['printmessage']     = 'Udskriv denne besked';
$labels['previousmessages'] = 'Vis forrige sæt beskeder';
$labels['nextmessages']     = 'Vis næste sæt beskeder';
$labels['backtolist']       = 'Tilbage til beskedlisten';

$labels['select'] = 'Vælg';
$labels['all'] = 'Alle';
$labels['none'] = 'Ingen';
$labels['unread'] = 'Ulæste';

// message compose
$labels['compose']  = 'Forfat en besked';
$labels['sendmessage']  = 'Send beskeden nu';
$labels['addattachment']  = 'Vedhæft en fil';

$labels['upload'] = 'Upload';
$labels['close']  = 'Luk';

$labels['low']     = 'Lav';
$labels['lowest']  = 'Lavest';
$labels['normal']  = 'Normal';
$labels['high']    = 'Høj';
$labels['highest'] = 'Højest';

$labels['showimages'] = 'Vis billeder';


// address boook
$labels['name']      = 'Vist navn';
$labels['firstname'] = 'Fornavn';
$labels['surname']   = 'Efternavn';
$labels['email']     = 'Email';

$labels['addcontact'] = 'Tilføj en ny kontakt';
$labels['editcontact'] = 'Redigér kontakt';

$labels['edit']   = 'Redigér';
$labels['cancel'] = 'Afbryd';
$labels['save']   = 'Gem';
$labels['delete'] = 'Slet';

$labels['newcontact']     = 'Opret nyt kontaktkort';
$labels['deletecontact']  = 'Slet valgte kontakter';
$labels['composeto']      = 'Skriv brev til';
$labels['contactsfromto'] = 'Kontakter $from til $to af $count';


// settings
$labels['settingsfor']  = 'Indstillinger for';

$labels['preferences']  = 'Præferencer';
$labels['userpreferences']  = 'Brugerpræferencer';
$labels['editpreferences']  = 'Redigér brugerpræferencer';

$labels['identities']  = 'Identiteter';
$labels['manageidentities']  = 'Styr identiteterne for denne konto';
$labels['newidentity']  = 'Ny identitet';

$labels['newitem']  = 'Nyt punkt';
$labels['edititem']  = 'Redigér punkt';

$labels['setdefault']  = 'Sæt standard';
$labels['language']  = 'Sprog';
$labels['timezone']  = 'Tidszone';
$labels['pagesize']  = 'Rækker per side';


$labels['folders']  = 'Foldere';
$labels['foldername']  = 'Foldernavn';
$labels['subscribed']  = 'Abonneret';
$labels['create']  = 'Opret';
$labels['createfolder']  = 'Opret ny folder';
$labels['deletefolder']  = 'Slet folder';
$labels['managefolders']  = 'Styr foldere';

$labels['attachments'] = 'Vedhæftninger';
$labels['prettydate']  = 'Pæn datovisning';
$labels['print']       = 'Print';
$labels['export']      = 'Eksport';
$labels['viewsource']  = 'Vis rå besked';
$labels['replytoallmessage'] = 'Svar til alle modtagere';

$labels['folder']  = 'Folder';
$labels['compact'] = 'Ryd op';
$labels['empty'] = 'Tøm';
$labels['purge'] = 'Tøm';
$labels['quota'] = 'Disk forbrug';

$labels['sortby']   = 'Sortér efter';
$labels['sortdesc'] = 'Nyeste først';
$labels['sortasc']  = 'Ældste først';

$labels['nosubject']  = '(intet emne)';
$labels['signature'] = 'Signatur';
$labels['charset']  = 'Tegnsæt';

$labels['ldapsearch'] = 'LDAP kartotekssøgning';
$labels['ldappublicsearchname']    = 'Kontaktens navn';
$labels['ldappublicsearchtype'] = 'Præcis søgning?';
$labels['ldappublicserverselect'] = 'Vælg servere';
$labels['ldappublicsearchfield'] = 'Søg på';
$labels['ldappublicsearchform'] = 'Søg efter en kontakt';
$labels['ldappublicsearch'] = 'Søg';

$labels['welcome'] = 'Velkommen til $product';

$labels['unknown'] = 'ukendt';
$labels['unlimited'] = 'ubegræset';
$labels['dstactive'] = 'Sommertid';

$labels['previouspage'] = 'Vis forrige sæt';
$labels['nextpage']     = 'Vis næste sæt';

$labels['returnreceipt'] = 'Retur-kvittering';

$labels['checkmail'] = 'Se efter nye beskeder';
$labels['checkspelling'] = 'Tjek stavning';
$labels['resumeediting'] = 'Genoptag redigering';
$labels['revertto'] = 'Vend tilbage til';

$labels['savemessage']  = 'Gem denne klade';
$labels['rename'] = 'Omdøb';
$labels['renamefolder'] = 'Omdøb folder';

# i use an additional description field - this might be used somewhere else
$labels['description'] = 'Beskrivelse';

?>

