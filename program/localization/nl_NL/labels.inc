<?php

/*
 +-----------------------------------------------------------------------+
 | language/nl_NL/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Mark Moses <mark@katmoda.com>                                 |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welkom bij $product';
$labels['username']  = 'Gebruikersnaam';
$labels['password']  = 'Wachtwoord';
$labels['server']    = 'Server';
$labels['login']     = 'Login';

// taskbar
$labels['logout']   = 'Uitloggen';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Persoonlijke Instellingen';
$labels['addressbook'] = 'Adresboek';

// mailbox names
$labels['inbox']  = 'Postvak IN';
$labels['sent']   = 'Verzonden';
$labels['trash']  = 'Prullenbak';
$labels['drafts'] = 'Concepten';
$labels['junk']   = 'SPAM';

// message listing
$labels['subject'] = 'Onderwerp';
$labels['from']    = 'Afzender';
$labels['to']      = 'Ontvanger';
$labels['cc']      = 'kopie';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Antwoord-aan';
$labels['date']    = 'Datum';
$labels['size']    = 'Grootte';
$labels['priority'] = 'Prioriteit';
$labels['organization'] = 'Organisatie';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mappen';
$labels['messagesfromto'] = 'Bericht $from t/m $to van $count';
$labels['messagenrof'] = 'Bericht $nr van $count';

$labels['moveto']   = 'verplaats naar...';
$labels['download'] = 'download';

$labels['filename'] = 'Bestandsnaam';
$labels['filesize'] = 'Bestandsgrootte';

$labels['preferhtml'] = 'Prefereer HTML';
$labels['htmlmessage'] = 'HTML Bericht';
$labels['prettydate'] = 'Opgemaakte data';

$labels['addtoaddressbook'] = 'Toevoegen aan adresboek';

// weekdays short
$labels['sun'] = 'Zo';
$labels['mon'] = 'Ma';
$labels['tue'] = 'Di';
$labels['wed'] = 'Wo';
$labels['thu'] = 'Do';
$labels['fri'] = 'Vr';
$labels['sat'] = 'Za';

// weekdays long
$labels['sunday']    = 'Zondag';
$labels['monday']    = 'Maandag';
$labels['tuesday']   = 'Dinsdag';
$labels['wednesday'] = 'Woensdag';
$labels['thursday']  = 'Donderdag';
$labels['friday']    = 'Vrijdag';
$labels['saturday']  = 'Zaterdag';

$labels['today'] = 'Vandaag';

// toolbar buttons
$labels['writenewmessage']  = 'Nieuw bericht';
$labels['replytomessage']   = 'Beantwoord het bericht';
$labels['replytoallmessage'] = 'Beantwoord alle ontvangers';
$labels['forwardmessage']   = 'Bericht doorsturen';
$labels['deletemessage']    = 'Verplaats het bericht naar de prullenbak';
$labels['printmessage']     = 'Dit bericht afdrukken';
$labels['previousmessages'] = 'Toon vorige lijst met berichten';
$labels['nextmessages']     = 'Toon volgende lijst met berichten';
$labels['backtolist']       = 'Terug naar berichtenoverzicht';
$labels['viewsource']       = 'Toon bron';

$labels['select'] = 'Selecteer';
$labels['all'] = 'Allemaal';
$labels['none'] = 'Geen';
$labels['unread'] = 'Ongelezen';

$labels['compact'] = 'Compact';
$labels['empty'] = 'Legen';
$labels['purge'] = 'Purge';

$labels['quota'] = 'Schijfruimte gebruik';
$labels['unknown']  = 'onbekend';
$labels['unlimited']  = 'onbeperkt';

$labels['quicksearch']  = 'Snel zoeken';
$labels['resetsearch']  = 'Reset zoekopdracht';


// message compose
$labels['compose']  = 'Maak een bericht';
$labels['sendmessage']  = 'Verstuur het bericht nu';
$labels['addattachment']  = 'Voeg een bijlage toe';
$labels['charset']  = 'karakterset';
$labels['returnreceipt'] = 'Ontvangstbevestiging';

$labels['attachments'] = 'Bijlages';
$labels['upload'] = 'Toevoegen';
$labels['close']  = 'Sluit';

$labels['low']     = 'Laag';
$labels['lowest']  = 'Laagste';
$labels['normal']  = 'Normaal';
$labels['high']    = 'Hoog';
$labels['highest'] = 'Hoogste';

$labels['nosubject']  = '(geen onderwerp)';
$labels['showimages'] = 'Toon afbeeldingen';


// address boook
$labels['name']      = 'Tonen als';
$labels['firstname'] = 'Voornaam';
$labels['surname']   = 'Achternaam';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Nieuw contactpersoon toevoegen';
$labels['editcontact'] = 'Contactpersoon wijzigen';

$labels['edit']   = 'Wijzig';
$labels['cancel'] = 'Annuleer';
$labels['save']   = 'Opslaan';
$labels['delete'] = 'Verwijder';

$labels['newcontact']     = 'Voeg een nieuwe contactpersoon toe';
$labels['addcontact']     = 'Voeg geselecteerde contacten toe aan het adresboek';
$labels['deletecontact']  = 'Verwijder geselecteerde contacten';
$labels['composeto']      = 'Stuur een bericht naar';
$labels['contactsfromto'] = 'Contacten $from t/m $to van $count';
$labels['print']          = 'Afdrukken';
$labels['export']         = 'Exporteren';

$labels['previouspage']   = 'Vorige pagina';
$labels['nextpage']       = 'Volgende pagina';


// LDAP search
$labels['ldapsearch'] = 'LDAP adresboek zoeken';

$labels['ldappublicsearchname']    = 'Naam van contactpersoon';
$labels['ldappublicsearchtype'] = 'Precies matchen?';
$labels['ldappublicserverselect'] = 'Selecteer servers';
$labels['ldappublicsearchfield'] = 'Zoek op';
$labels['ldappublicsearchform'] = 'Zoek een contactpersoon';
$labels['ldappublicsearch'] = 'Zoek';


// settings
$labels['settingsfor']  = 'Instellingen voor';

$labels['preferences']  = 'Instellingen';
$labels['userpreferences']  = 'Gebruikers instellingen';
$labels['editpreferences']  = 'Wijzig gebruikers instellingen';

$labels['identities']  = 'Identititeiten';
$labels['manageidentities']  = 'Beheer identiteiten voor dit account';
$labels['newidentity']  = 'Identiteit toevoegen';

$labels['newitem']  = 'Nieuw item';
$labels['edititem']  = 'Wijzig item';

$labels['setdefault']  = 'Stel als standaard in';
$labels['language']  = 'Taal';
$labels['timezone']  = 'Tijdszone';
$labels['pagesize']  = 'Rijen per pagina';
$labels['signature'] = 'Onderschrift';
$labels['dstactive']  = 'Zomertijd';

$labels['folder']  = 'Map';
$labels['folders']  = 'Mappen';
$labels['foldername']  = 'Mapnaam';
$labels['subscribed']  = 'Geabonneerd';
$labels['create']  = 'Nieuw';
$labels['createfolder']  = 'Maak nieuwe map';
$labels['deletefolder']  = 'Verwijder map';
$labels['managefolders']  = 'Beheer mappen';

$labels['sortby'] = 'Sorteer op';
$labels['sortasc']  = 'Sorteer oplopend';
$labels['sortdesc'] = 'Sorteer aflopend';

?>
