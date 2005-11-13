<?php

/*
 +-----------------------------------------------------------------------+
 | language/nn/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Per-Stian Vatne <psv@orsta.org>                               |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Brukarnamn';
$labels['password']  = 'Passord';
$labels['server']    = 'Tenar';
$labels['login']     = 'Logg inn';

// taskbar
$labels['logout']   = 'Logg ut';
$labels['mail']     = 'E-post';
$labels['settings'] = 'Personlege innstillingar';
$labels['addressbook'] = 'Adressebok';

// mailbox names
$labels['inbox']  = 'Innboks';
$labels['sent']   = 'Sende element';
$labels['trash']  = 'Sletta element';
$labels['drafts'] = 'Kladd';
$labels['junk']   = 'Søppel';

// message listing
$labels['subject'] = 'Emne';
$labels['from']    = 'Sendar';
$labels['to']      = 'Mottakar';
$labels['cc']      = 'Kopi';
$labels['bcc']     = 'Blindkopi';
$labels['replyto'] = 'Svar-til';
$labels['date']    = 'Dato';
$labels['size']    = 'Storleik';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisasjon';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Katalogar';
$labels['messagesfromto'] = 'Meldingar $from til $to av $count';
$labels['messagenrof'] = 'Melding $nr av $count';

$labels['moveto']   = 'flytt til...';
$labels['download'] = 'last ned';

$labels['filename'] = 'Filnamn';
$labels['filesize'] = 'Filstorleik';

$labels['preferhtml'] = 'Føretrekk HTML';
$labels['htmlmessage'] = 'HTML-melding';
$labels['prettydate'] = 'Pene datoar';

$labels['addtoaddressbook'] = 'Legg til i adressebok';

// weekdays short
$labels['sun'] = 'Sun';
$labels['mon'] = 'Mån';
$labels['tue'] = 'Tys';
$labels['wed'] = 'Ons';
$labels['thu'] = 'Tor';
$labels['fri'] = 'Fre';
$labels['sat'] = 'Lau';

// weekdays long
$labels['sunday']    = 'Sundag';
$labels['monday']    = 'Måndag';
$labels['tuesday']   = 'Tysdag';
$labels['wednesday'] = 'Onsdag';
$labels['thursday']  = 'Torsdag';
$labels['friday']    = 'Fredag';
$labels['saturday']  = 'Laurdag';

$labels['today'] = 'I dag';

// toolbar buttons
$labels['writenewmessage']  = 'Skriv ei ny melding';
$labels['replytomessage']   = 'Svar på meldinga';
$labels['forwardmessage']   = 'Vidaresend meldinga';
$labels['deletemessage']    = 'Flytt melding til Sletta element';
$labels['printmessage']     = 'Skriv ut meldinga';
$labels['previousmessages'] = 'Vis føregåande side med meldingar';
$labels['nextmessages']     = 'Vis neste side med med meldingar';
$labels['backtolist']       = 'Tilbake til meldingslista';
$labels['viewsource']       = 'Vis kjelde';

$labels['select'] = 'Vel';
$labels['all'] = 'Alle';
$labels['none'] = 'Ingen';
$labels['unread'] = 'Uleste';

// message compose
$labels['compose']  = 'Skriv ei melding';
$labels['sendmessage']  = 'Send meldinga no';
$labels['addattachment']  = 'Legg ved ei fil';

$labels['attachments'] = 'Vedlegg';
$labels['upload'] = 'Last opp';
$labels['close']  = 'Steng';

$labels['low']     = 'Låg';
$labels['lowest']  = 'Lågast';
$labels['normal']  = 'Normal';
$labels['high']    = 'Høg';
$labels['highest'] = 'Høgast';

$labels['showimages'] = 'Vis bilete';


// address book
$labels['name']      = 'Visingsnamn';
$labels['firstname'] = 'Førenamn';
$labels['surname']   = 'Etternamn';
$labels['email']     = 'E-post';

$labels['addcontact'] = 'Legg til ny kontakt';
$labels['editcontact'] = 'Redigere kontakt';

$labels['edit']   = 'Redigere';
$labels['cancel'] = 'Avbryt';
$labels['save']   = 'Lagre';
$labels['delete'] = 'Slette';

$labels['newcontact']     = 'Opprett nytt kontaktkort';
$labels['deletecontact']  = 'Slett valde kontaktar';
$labels['composeto']      = 'Ny melding til';
$labels['contactsfromto'] = 'Kontaktar $from til $to av $count';
$labels['print']          = 'Skriv ut';
$labels['export']         = 'Eksport';


// settings
$labels['settingsfor']  = 'Innstillingar for';

$labels['preferences']  = 'Innstillingar';
$labels['userpreferences']  = 'Brukarinnstillingar';
$labels['editpreferences']  = 'Redigere brukarinnstillingar';

$labels['identities']  = 'Identitetar';
$labels['manageidentities']  = 'Administrere identitetar for denne kontoen';
$labels['newidentity']  = 'Ny identitet';

$labels['newitem']  = 'Nytt element';
$labels['edititem']  = 'Redigere element';

$labels['setdefault']  = 'Set som standard';
$labels['language']  = 'Språk';
$labels['timezone']  = 'Tidssone';
$labels['pagesize']  = 'Rader pr side';


$labels['folders']  = 'Mapper';
$labels['foldername']  = 'Mappenamn';
$labels['subscribed']  = 'Abonnert';
$labels['create']  = 'Opprette';
$labels['createfolder']  = 'Opprett ny mappe';
$labels['deletefolder']  = 'Slett mappe';
$labels['managefolders']  = 'Administrere mapper';


?>
