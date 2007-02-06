<?php

/*

 +-----------------------------------------------------------------------+
 | language/nb_NO/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | All rights reserved.                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Olav Elstad <olav@elstad.org>                                 |
 +-----------------------------------------------------------------------+
 
 @version $Id$
 
*/

$labels = array();

// login page
$labels['username']  = 'Brukernavn:';
$labels['password']  = 'Passord:';
$labels['server']    = 'Server';
$labels['login']     = 'Logg på';

// taskbar
$labels['logout']   = 'Logg ut';
$labels['mail']     = 'Epost';
$labels['settings'] = 'Personlige innstillinger';
$labels['addressbook'] = 'Adressebok';

// mailbox names
$labels['inbox']  = 'Innboks';
$labels['sent']   = 'Sendt post';
$labels['trash']  = 'Søppel';
$labels['drafts'] = 'Kladder';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Emne';
$labels['from']    = 'Avsender';
$labels['to']      = 'Mottaker';
$labels['cc']      = 'Kopi til';
$labels['bcc']     = 'BCC';
$labels['replyto'] = 'Svar til';
$labels['date']    = 'Dato';
$labels['size']    = 'Størrelse';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisasjon';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mapper';
$labels['messagesfromto'] = 'Meldinger $from til $to av $count';
$labels['messagenrof'] = 'Meldinger $nr av $count';

$labels['moveto']   = 'flytt til...';
$labels['download'] = 'last ned';

$labels['filename'] = 'Filnavn';
$labels['filesize'] = 'Filstørrelse';

$labels['preferhtml'] = 'Foretrekk HTML';
$labels['htmlmessage'] = 'HTML besked';

$labels['addtoaddressbook'] = 'Tilføy til adresseboken';

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
$labels['writenewmessage']  = 'Opprett en ny e-post';
$labels['replytomessage']   = 'Svar på denne e-post';
$labels['forwardmessage']   = 'Videresend denne e-post';
$labels['deletemessage']    = 'Flytt e-posten til søppel';
$labels['printmessage']     = 'Skriv ut denne e-post';
$labels['previousmessages'] = 'Vis forrige side';
$labels['nextmessages']     = 'Vis neste side';
$labels['backtolist']       = 'Tilbake til e-postlisten';

$labels['select'] = 'Velg';
$labels['all'] = 'Alle';
$labels['none'] = 'Ingen';
$labels['unread'] = 'Uleste';

// message compose
$labels['compose']  = 'Lag ny e-post';
$labels['sendmessage']  = 'Send e-posten nu';
$labels['addattachment']  = 'Vedlegg';

$labels['upload'] = 'Last opp';
$labels['close']  = 'Lukk';

$labels['low']     = 'Lav';
$labels['lowest']  = 'Lavest';
$labels['normal']  = 'Normal';
$labels['high']    = 'Høy';
$labels['highest'] = 'Høyest';

$labels['showimages'] = 'Vis bilder';

// address boook
$labels['name']      = 'Vist navn';
$labels['firstname'] = 'Fornavn';
$labels['surname']   = 'Etternavn';
$labels['email']     = 'E-post';

$labels['addcontact'] = 'Legg til en ny kontakt';
$labels['editcontact'] = 'Rediger kontakt';

$labels['edit']   = 'Rediger';
$labels['cancel'] = 'Avbryt';
$labels['save']   = 'Lagre';
$labels['delete'] = 'Slett';

$labels['newcontact']     = 'Opprett ny kontakt';
$labels['deletecontact']  = 'Slett valgte kontakter';
$labels['composeto']      = 'Skriv e-post til';
$labels['contactsfromto'] = 'Kontakter $from til $to av $count';

// settings
$labels['settingsfor']  = 'Innstillinger for';

$labels['preferences']  = 'Oppsett';
$labels['userpreferences']  = 'Brukeroppsett';
$labels['editpreferences']  = 'Rediger brukeroppsett';

$labels['identities']  = 'Identiteter';
$labels['manageidentities']  = 'Styr identitetene for denne kontoen';
$labels['newidentity']  = 'Ny identitet';

$labels['newitem']  = 'Nytt punkt';
$labels['edititem']  = 'Rediger punkt';

$labels['setdefault']  = 'Sett standard';
$labels['language']  = 'Språk';
$labels['timezone']  = 'Tidssone';
$labels['pagesize']  = 'Linjer per side';

$labels['folders']  = 'Mapper';
$labels['foldername']  = 'Mappenavn';
$labels['subscribed']  = 'Abonnere';
$labels['create']  = 'Opprett';
$labels['createfolder']  = 'Lag ny mappe';
$labels['deletefolder']  = 'Slett mappe';
$labels['managefolders']  = 'Rediger mapper';

$labels['attachments'] = 'Vedlegg';
$labels['prettydate']  = 'Pen datovisning';
$labels['print']       = 'Skriv ut';
$labels['export']      = 'Eksportere';
$labels['viewsource']  = 'Vis rå besked';
$labels['replytoallmessage'] = 'Svar til alle mottakere';

$labels['folder']  = 'Mappe';
$labels['compact'] = 'Rydd opp';
$labels['empty'] = 'Tøm';
$labels['purge'] = 'Tøm';
$labels['quota'] = 'Disk forbruk';

$labels['sortby']   = 'Sorter etter';
$labels['sortdesc'] = 'Nyeste først';
$labels['sortasc']  = 'Eldste først';

$labels['nosubject']  = '(intet emne)';
$labels['signature'] = 'Signatur';
$labels['charset']  = 'Tegnsett';

$labels['ldapsearch'] = 'LDAP kartotekssøking';
$labels['ldappublicsearchname']    = 'Kontaktens navn';
$labels['ldappublicsearchtype'] = 'Presis søkning?';
$labels['ldappublicserverselect'] = 'Velg servere';
$labels['ldappublicsearchfield'] = 'Søk på';
$labels['ldappublicsearchform'] = 'Søk etter en kontakt';
$labels['ldappublicsearch'] = 'Søk';

$labels['welcome'] = 'Velkommen til $product';

$labels['unknown'] = 'ukjent';
$labels['unlimited'] = 'ubegrenset';
$labels['dstactive'] = 'Sommertid';

$labels['previouspage'] = 'Vis forrige sett';
$labels['nextpage']     = 'Vis neste sett';

$labels['returnreceipt'] = 'Be om kvittering';

$labels['checkmail'] = 'Sjekk for nye beskjeder';
$labels['checkspelling'] = 'Stavekontroll';
$labels['resumeediting'] = 'Fortsett redigering';
$labels['revertto'] = 'Tilbake til';

$labels['savemessage']  = 'Lagre kladd';
$labels['rename'] = 'Døkp om';
$labels['renamefolder'] = 'Døkp om mappe';

# i use an additional description field - this might be used somewhere else
$labels['description'] = 'Beskrivelse';

?>