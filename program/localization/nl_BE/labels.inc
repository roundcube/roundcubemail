<?php

/*

 +-----------------------------------------------------------------------+
 | language/nl_BE/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Dennis Heeren <dennis.heeren@gmail.com>                       |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Onthaal aan $product';
$labels['username']  = 'Gebruikersnaam';
$labels['password']  = 'Wachtwoord';
$labels['server']    = 'Server';
$labels['login']     = 'Aanmelden';

// taskbar
$labels['logout']   = 'Afmelden';
$labels['mail']     = 'Berichten';
$labels['settings'] = 'Instellingen';
$labels['addressbook'] = 'Contactpersonen';

// mailbox names
$labels['inbox']  = 'Postvak IN';
$labels['sent']   = 'Verzonden berichten';
$labels['trash']  = 'Prullenbak';
$labels['drafts'] = 'Concepten';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Onderwerp';
$labels['from']    = 'Van';
$labels['to']      = 'Aan';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Antwoorden aan';
$labels['date']    = 'Datum';
$labels['size']    = 'Grootte';
$labels['priority'] = 'Prioriteit';
$labels['organization'] = 'Organisatie';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Mappen';
$labels['messagesfromto'] = 'Berichten $from - $to van $count';
$labels['messagenrof'] = 'Bericht $nr van $count';

$labels['moveto']   = 'verplaats naar...';
$labels['download'] = 'downloaden';

$labels['filename'] = 'Bestandsnaam';
$labels['filesize'] = 'Bestandsgrootte';

$labels['preferhtml'] = 'Gebruik HTML-opmaak';
$labels['htmlmessage'] = 'HTML-bericht';
$labels['prettydate'] = 'Uitgebreide datumweergave';

$labels['addtoaddressbook'] = 'Voeg toe aan lijst met contactpersonen';

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
$labels['replytomessage']   = 'Beantwoorden';
$labels['replytoallmessage'] = 'Allen beantwoorden';
$labels['forwardmessage']   = 'Doorsturen';
$labels['deletemessage']    = 'Dit bericht verwijderen';
$labels['printmessage']     = 'Afdrukken';
$labels['previousmessages'] = 'Vorige berichten';
$labels['nextmessages']     = 'Volgende berichten';
$labels['backtolist']       = 'Terug naar berichtenlijst';
$labels['viewsource']       = 'Broncode bericht weergeven';

$labels['select'] = 'Selecteren';
$labels['all'] = 'Alles';
$labels['none'] = 'Geen';
$labels['unread'] = 'Ongelezen';

$labels['compact'] = 'Comprimeren';
$labels['empty'] = 'Legen';
$labels['purge'] = 'Opruimen';

$labels['quota'] = 'Gebruikte schijfruimte';

// message compose
$labels['compose']  = 'Nieuw bericht opstellen';
$labels['sendmessage']  = 'Verzenden';
$labels['addattachment']  = 'Bestand toevoegen als bijlage';
$labels['charset']  = 'Karakterset';

$labels['attachments'] = 'Bijlagen';
$labels['upload'] = 'Toevoegen';
$labels['close']  = 'Sluiten';

$labels['low']     = 'Laag';
$labels['lowest']  = 'Laagste';
$labels['normal']  = 'Normaal';
$labels['high']    = 'Hoog';
$labels['highest'] = 'Hoogste';

$labels['nosubject']  = '(geen onderwerp)';
$labels['showimages'] = 'Toon afbeeldingen';

// address boook
$labels['name']      = 'Naam';
$labels['firstname'] = 'Voornaam';
$labels['surname']   = 'Achternaam';
$labels['email']     = 'E-mailadres';

$labels['addcontact'] = 'Contactpersoon toevoegen';
$labels['editcontact'] = 'Contactpersoon bewerken';

$labels['edit']   = 'Bewerken';
$labels['cancel'] = 'Annuleren';
$labels['save']   = 'Opslaan';
$labels['delete'] = 'Verwijderen';

$labels['newcontact']     = 'Contactpersoon toevoegen';
$labels['addcontact']     = 'Geselecteerde contactpersoon toevoegen';
$labels['deletecontact']  = 'Verwijder geselecteerde contactpersonen';
$labels['composeto']      = 'Mail sturen aan';
$labels['contactsfromto'] = 'Contactpersonen $from - $to van $count';
$labels['print']          = 'Afdrukken';
$labels['export']         = 'Exporteren';

// LDAP search
$labels['ldapsearch'] = 'LDAP opzoeking';

$labels['ldappublicsearchname'] = 'Naam contactpersoon';
$labels['ldappublicsearchtype'] = 'Exact zoeken?';
$labels['ldappublicserverselect'] = 'Kies een server';
$labels['ldappublicsearchfield'] = 'Zoeken op';
$labels['ldappublicsearchform'] = 'Zoek een contactpersoon';
$labels['ldappublicsearch'] = 'Zoeken';

// settings
$labels['settingsfor']  = 'Instellingen voor';

$labels['preferences']  = 'Voorkeuren';
$labels['userpreferences']  = 'Gebruikersvoorkeuren';
$labels['editpreferences']  = 'Gebruikersvoorkeuren bewerken';

$labels['identities']  = 'Identiteiten';
$labels['manageidentities']  = 'Beheer de identiteiten voor deze gebruiker';
$labels['newidentity']  = 'Nieuwe identiteit';

$labels['newitem']  = 'Nieuw';
$labels['edititem']  = 'Bewerken';

$labels['setdefault']  = 'Als standaard instellen';
$labels['language']  = 'Taal';
$labels['timezone']  = 'Tijdzone';
$labels['pagesize']  = 'Berichten per pagina';
$labels['signature'] = 'Handtekening';

$labels['folder']  = 'Map';
$labels['folders']  = 'Mappen';
$labels['foldername']  = 'Naam';
$labels['subscribed']  = 'Gebruiken';
$labels['create']  = 'Aanmaken';
$labels['createfolder']  = 'Nieuwe map aanmaken';
$labels['deletefolder']  = 'Map verwijderen';
$labels['managefolders']  = 'Mappen beheren';

$labels['sortby']   = 'Sorteren op';
$labels['sortdesc'] = 'Aflopend sorteren';
$labels['sortasc']  = 'Oplopend sorteren';

?>