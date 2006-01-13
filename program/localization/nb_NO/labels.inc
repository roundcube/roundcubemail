<?php

/*
 +-----------------------------------------------------------------------+
 | language/nb/labels.inc                                                |
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
$labels['username']  = 'Brukernavn';
$labels['password']  = 'Passord';
$labels['server']    = 'Tjener';
$labels['login']     = 'Logg inn';

// taskbar
$labels['logout']   = 'Logg ut';
$labels['mail']     = 'E-post';
$labels['settings'] = 'Personlige innstillinger';
$labels['addressbook'] = 'Adressebok';

// mailbox names
$labels['inbox']  = 'Innboks';
$labels['sent']   = 'Sendte elementer';
$labels['trash']  = 'Slettede elementer';
$labels['drafts'] = 'Kladd';
$labels['junk']   = 'Søppel';

// message listing
$labels['subject'] = 'Emne';
$labels['from']    = 'Avsender';
$labels['to']      = 'Mottaker';
$labels['cc']      = 'Kopi';
$labels['bcc']     = 'Blindkopi';
$labels['replyto'] = 'Svar-til';
$labels['date']    = 'Dato';
$labels['size']    = 'Størrelse';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisasjon';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Kataloger';
$labels['messagesfromto'] = 'Meldinger $from til $to av $count';
$labels['messagenrof'] = 'Melding $nr av $count';

$labels['moveto']   = 'flytt til...';
$labels['download'] = 'last ned';

$labels['filename'] = 'Filnavn';
$labels['filesize'] = 'Filestørrelse';

$labels['preferhtml'] = 'Foretrekk HTML';
$labels['htmlmessage'] = 'HTML-melding';
$labels['prettydate'] = 'Pene datoer';

$labels['addtoaddressbook'] = 'Legg til adressebok';

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
$labels['writenewmessage']  = 'Skriv en ny melding';
$labels['replytomessage']   = 'Svar på meldingen';
$labels['forwardmessage']   = 'Videresend meldingen';
$labels['deletemessage']    = 'Flytt melding til Slettede elementer';
$labels['printmessage']     = 'Skriv ut meldingen';
$labels['previousmessages'] = 'Vis foregående side med meldinger';
$labels['nextmessages']     = 'Vis neste side med med meldinger';
$labels['backtolist']       = 'Tilbake til meldingslisten';
$labels['viewsource']       = 'Vis kilde';

$labels['select'] = 'Velg';
$labels['all'] = 'Alle';
$labels['none'] = 'Ingen';
$labels['unread'] = 'Uleste';

// message compose
$labels['compose']  = 'Skriv en melding';
$labels['sendmessage']  = 'Send meldingen nå';
$labels['addattachment']  = 'Legg ved en fil';

$labels['attachments'] = 'Vedlegg';
$labels['upload'] = 'Last opp';
$labels['close']  = 'Lukk';

$labels['low']     = 'Lav';
$labels['lowest']  = 'Lavest';
$labels['normal']  = 'Normal';
$labels['high']    = 'Høy';
$labels['highest'] = 'Høyest';

$labels['showimages'] = 'Vis bilder';


// address book
$labels['name']      = 'Visningsnavn';
$labels['firstname'] = 'Fornavn';
$labels['surname']   = 'Etternavn';
$labels['email']     = 'E-post';

$labels['addcontact'] = 'Legg til ny kontakt';
$labels['editcontact'] = 'Redigere kontakt';

$labels['edit']   = 'Redigere';
$labels['cancel'] = 'Avbryt';
$labels['save']   = 'Lagre';
$labels['delete'] = 'Slette';

$labels['newcontact']     = 'Opprett nytt kontaktkort';
$labels['deletecontact']  = 'Slett valgte kontakter';
$labels['composeto']      = 'Ny melding til';
$labels['contactsfromto'] = 'Kontakter $from til $to av $count';
$labels['print']          = 'Skriv ut';
$labels['export']         = 'Eksport';


// settings
$labels['settingsfor']  = 'Innstillinger for';

$labels['preferences']  = 'Valg';
$labels['userpreferences']  = 'User preferences';
$labels['editpreferences']  = 'Edit user preferences';

$labels['identities']  = 'Identiteter';
$labels['manageidentities']  = 'Behandle identiteter for denne kontoen';
$labels['newidentity']  = 'Ny identitet';

$labels['newitem']  = 'Nytt element';
$labels['edititem']  = 'Redigere element';

$labels['setdefault']  = 'Sett som standard';
$labels['language']  = 'Språk';
$labels['timezone']  = 'Tidssone';
$labels['pagesize']  = 'Rader pr side';


$labels['folders']  = 'Mapper';
$labels['foldername']  = 'Mappenavn';
$labels['subscribed']  = 'Abonnert';
$labels['create']  = 'Opprette';
$labels['createfolder']  = 'Opprett ny mappe';
$labels['deletefolder']  = 'Slett mappe';
$labels['managefolders']  = 'Behandle mapper';


?>
