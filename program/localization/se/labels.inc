<?php

/*
 +-----------------------------------------------------------------------+
 | language/en/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Användarnamn';
$labels['password']  = 'Lösenord';
$labels['server']    = 'Server';
$labels['login']     = 'Logga in';

// taskbar
$labels['logout']   = 'Logga ut';
$labels['mail']     = 'E-post';
$labels['settings'] = 'Personliga inställningar';
$labels['addressbook'] = 'Adressbok';

// mailbox names
$labels['inbox']  = 'Inkorg';
$labels['sent']   = 'Skickat';
$labels['trash']  = 'Papperskorg';
$labels['drafts'] = 'Utkast';
$labels['junk']   = 'Skräp';

// message listing
$labels['subject'] = 'Ämne';
$labels['from']    = 'Avsändare';
$labels['to']      = 'Mottagare';
$labels['cc']      = 'Kopia';
$labels['bcc']     = 'Hemlig kopia';
$labels['replyto'] = 'Svar till';
$labels['date']    = 'Datum';
$labels['size']    = 'Storlek';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisation';

// aliases
$labels['reply-to'] = $labels['svartill'];

$labels['mailboxlist'] = 'Mappar';
$labels['messagesfromto'] = 'Meddelanden $from till $to av $count';
$labels['messagenrof'] = 'Meddelande $nr av $count';

$labels['moveto']   = 'flytta till...';
$labels['download'] = 'ladda ner';

$labels['filename'] = 'Filnamn';
$labels['filesize'] = 'Filstorlek';

$labels['preferhtml'] = 'Föredra HTML';
$labels['htmlmessage'] = 'HTML-meddelande';
$labels['prettydate'] = 'Fina datum';

$labels['addtoaddressbook'] = 'Lägg till adressbok';

// weekdays short
$labels['sun'] = 'Sön';
$labels['mon'] = 'Mån';
$labels['tue'] = 'Tis';
$labels['wed'] = 'Ons';
$labels['thu'] = 'Tor';
$labels['fri'] = 'Fre';
$labels['sat'] = 'Lör';

// weekdays long
$labels['sunday']    = 'Söndag';
$labels['monday']    = 'Måndag';
$labels['tuesday']   = 'Tisdag';
$labels['wednesday'] = 'Onsdag';
$labels['thursday']  = 'Torsdag';
$labels['friday']    = 'Fredag';
$labels['saturday']  = 'Lördag';

$labels['today'] = 'Idag';

// toolbar buttons
$labels['writenewmessage']  = 'Skapa nytt meddelande';
$labels['replytomessage']   = 'Svar på meddelande';
$labels['replytoallmessage'] = 'Svar till avsändare och alla mottagare';
$labels['forwardmessage']   = 'Skicka vidare meddelande';
$labels['deletemessage']    = 'Flytta meddelande till papperskorgen';
$labels['printmessage']     = 'Skriv ut';
$labels['previousmessages'] = 'Visa tidigare';
$labels['nextmessages']     = 'Visa nästa';
$labels['backtolist']       = 'Tillbaka till meddelandelistan';
$labels['viewsource']       = 'Visa källa';

$labels['select'] = 'Välj';
$labels['all'] = 'Alla';
$labels['none'] = 'Ingen';
$labels['unread'] = 'Oläst';

$labels['compact'] = 'Packa';
$labels['empty'] = 'Tom';
$labels['purge'] = 'Rensa';

$labels['quota'] = 'Diskutrymme';


// message compose
$labels['compose']  = 'Skapa nytt meddelande';
$labels['sendmessage']  = 'Skicka meddelande nu';
$labels['addattachment']  = 'Bifoga en fil';
$labels['charset']  = 'Teckenkodning';

$labels['attachments'] = 'Bilagor';
$labels['upload'] = 'Uppladdning';
$labels['close']  = 'Stäng';

$labels['low']     = 'Låg';
$labels['lowest']  = 'Lägst';
$labels['normal']  = 'Normal';
$labels['high']    = 'Hög';
$labels['highest'] = 'Högst';

$labels['nosubject']  = '(inget ämne)';
$labels['showimages'] = 'Visa bilder';


// address boook
$labels['name']      = 'Visa namn';
$labels['firstname'] = 'Förnamn';
$labels['surname']   = 'Efternamn';
$labels['email']     = 'E-post';

$labels['addcontact'] = 'Lägg till ny kontakt';
$labels['editcontact'] = 'Redigera kontakt';

$labels['edit']   = 'Redigera';
$labels['cancel'] = 'Avbryt';
$labels['save']   = 'Spara';
$labels['delete'] = 'Radera';

$labels['newcontact']     = 'Skapa nytt kontaktkort';
$labels['addcontact']     = 'Lägg till vald kotakt till adressboken';
$labels['deletecontact']  = 'Radera valda kontakter';
$labels['composeto']      = 'Skriv e-post till';
$labels['contactsfromto'] = 'Kontakter $from till $to av $count';
$labels['print']          = 'Skriv ut';
$labels['export']         = 'Exportera';

// LDAP search
$labels['ldapsearch'] = 'LDAP katalogsök';

$labels['ldappublicsearchname']    = 'Kontaktnamn';
$labels['ldappublicsearchtype'] = 'Exakt matchning?';
$labels['ldappublicserverselect'] = 'Valda servrar';
$labels['ldappublicsearchfield'] = 'Sök på';
$labels['ldappublicsearchform'] = 'Sök kontakt';
$labels['ldappublicsearch'] = 'Sök';


// settings
$labels['settingsfor']  = 'Inställningar för';

$labels['preferences']  = 'Inställningar';
$labels['userpreferences']  = 'Användarinställningar';
$labels['editpreferences']  = 'Ändra användarinställningar';

$labels['identities']  = 'Profiler';
$labels['manageidentities']  = 'Hantera profiler för detta konto';
$labels['newidentity']  = 'Ny profil';

$labels['newitem']  = 'Ny post';
$labels['edititem']  = 'Ändra post';

$labels['setdefault']  = 'Sätt som standard';
$labels['language']  = 'Språk';
$labels['timezone']  = 'Tidszon';
$labels['pagesize']  = 'Rader per sida';
$labels['signature'] = 'Signatur';

$labels['folder']  = 'Mapp';
$labels['folders']  = 'Mappar';
$labels['foldername']  = 'Mappnamn';
$labels['subscribed']  = 'Ansluten';
$labels['create']  = 'Skapa';
$labels['createfolder']  = 'Skapa ny mapp';
$labels['deletefolder']  = 'Radera mapp';
$labels['managefolders']  = 'Hantera mappar';

$labels['sortby'] = 'Sortera på';
$labels['sortasc']  = 'Sortera stigande';
$labels['sortdesc'] = 'Sortera fallande';

?>
