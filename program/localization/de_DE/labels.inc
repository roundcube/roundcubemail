<?php

/*
 +-----------------------------------------------------------------------+
 | language/de_DE/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author:      Thomas Bruederli <roundcube@gmail.com>                   |
 +-----------------------------------------------------------------------+
 | de_DE translation: Stephan Diehl <info@sd-edv.de>                     |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page // Login-Seite
$labels['welcome']   = 'Willkommen bei $product';
$labels['username']  = 'Benutzername';
$labels['password']  = 'Passwort';
$labels['server']    = 'Server';
$labels['login']     = 'Anmelden';

// taskbar // Aktionsleiste
$labels['logout']   = 'Abmelden';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Einstellungen';
$labels['addressbook'] = 'Adressbuch';

// mailbox names // E-Mail-Ordnernamen
$labels['inbox']  = 'Posteingang';
$labels['sent']   = 'Gesendet';
$labels['trash']  = 'Gelöscht';
$labels['drafts'] = 'Entwürfe';
$labels['junk']   = 'Spam';

// message listing // Nachrichtenliste
$labels['subject'] = 'Betreff';
$labels['from']    = 'Absender';
$labels['to']      = 'Empfänger';
$labels['cc']      = 'Kopie (CC)';
$labels['bcc']     = 'Blind-Kopie';
$labels['replyto'] = 'Antwort an';
$labels['date']    = 'Datum';
$labels['size']    = 'Größe';
$labels['priority'] = 'Priorität';
$labels['organization'] = 'Organisation';

// aliases // [Platzhalter]
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Ordner';
$labels['messagesfromto'] = 'Nachrichten $from bis $to von $count';
$labels['messagenrof'] = 'Nachricht $nr von $count';

$labels['moveto']   = 'Verschieben nach...';
$labels['download'] = 'Download';

$labels['filename'] = 'Dateiname';
$labels['filesize'] = 'Dateigröße';

$labels['preferhtml'] = 'HTML bevorzugen';
$labels['htmlmessage'] = 'HTML Nachricht';
$labels['prettydate'] = 'Kurze Datumsanzeige';

$labels['addtoaddressbook'] = 'Ins Adressbuch übernehmen';

// weekdays short // Wochentage (Abkürzungen) 
$labels['sun'] = 'So';
$labels['mon'] = 'Mo';
$labels['tue'] = 'Di';
$labels['wed'] = 'Mi';
$labels['thu'] = 'Do';
$labels['fri'] = 'Fr';
$labels['sat'] = 'Sa';

// weekdays long // Wochentage (normal)
$labels['sunday']    = 'Sonntag';
$labels['monday']    = 'Montag';
$labels['tuesday']   = 'Dienstag';
$labels['wednesday'] = 'Mittwoch';
$labels['thursday']  = 'Donnerstag';
$labels['friday']    = 'Freitag';
$labels['saturday']  = 'Samstag';

$labels['today'] = 'Heute';

// toolbar buttons // Symbolleisten-Tipps
$labels['writenewmessage']  = 'Neue Nachricht schreiben';
$labels['replytomessage']   = 'Antwort verfassen';
$labels['replytoallmessage'] = 'Antwort an Absender und alle Empfänger';
$labels['forwardmessage']   = 'Nachricht weiterleiten';
$labels['deletemessage']    = 'In den Papierkorb verschieben';
$labels['printmessage']     = 'Diese Nachricht drucken';
$labels['previousmessages'] = 'Vorherige Nachrichten anzeigen';
$labels['nextmessages']     = 'Weitere Nachrichten anzeigen';
$labels['backtolist']       = 'Zurück zur Liste';

$labels['select'] = 'Auswählen';
$labels['all']    = 'Alle';
$labels['none']   = 'Keine';
$labels['unread'] = 'Ungelesene';

$labels['compact'] = 'Packen';
$labels['empty'] = 'Leeren';
$labels['purge'] = 'Bereinigen';

$labels['quota'] = 'Verwendeter Speicherplatz';
$labels['unknown']  = 'unbekannt';
$labels['unlimited']  = 'unlimitiert';

$labels['quicksearch']  = 'Schnellsuche';
$labels['resetsearch']  = 'Löschen';


// message compose // Nachrichten erstellen
$labels['compose']  = 'Neue Nachricht verfassen';
$labels['sendmessage']  = 'Nachricht jetzt senden';
$labels['addattachment']  = 'Datei anfügen';
$labels['charset']  = 'Zeichensatz';
$labels['returnreceipt'] = 'Empfangsbestätigung';

$labels['attachments'] = 'Anhänge';
$labels['upload'] = 'Hochladen';
$labels['close']  = 'Schließen';

$labels['low']     = 'Niedrig';
$labels['lowest']  = 'Niedrigste';
$labels['normal']  = 'Normal';
$labels['high']    = 'Hoch';
$labels['highest'] = 'Höchste';

$labels['nosubject']  = '(kein Betreff)';
$labels['showimages'] = 'Bilder anzeigen';


// address book // Adressbuch
$labels['name']      = 'Anzeigename';
$labels['firstname'] = 'Vorname';
$labels['surname']   = 'Nachname';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Kontakt hinzufügen';
$labels['editcontact'] = 'Kontakt bearbeiten';

$labels['edit']   = 'Bearbeiten';
$labels['cancel'] = 'Abbrechen';
$labels['save']   = 'Speichern';
$labels['delete'] = 'Löschen';

$labels['newcontact']     = 'Neuen Kontakt erfassen';
$labels['deletecontact']  = 'Gewählte Kontakte löschen';
$labels['composeto']      = 'Nachricht verfassen';
$labels['contactsfromto'] = 'Kontakte $from bis $to von $count';
$labels['print']          = 'Drucken';
$labels['export']         = 'Exportieren';

$labels['previouspage']   = 'Eine Seite zurück';
$labels['nextpage']       = 'Nächste Seite';

// LDAP search
$labels['ldapsearch'] = 'LDAP Verzeichnis-Suche';

$labels['ldappublicsearchname']   = 'Kontakt-Name';
$labels['ldappublicsearchtype']   = 'Genaue Übereinstimmung';
$labels['ldappublicserverselect'] = 'Server-Auswahl';
$labels['ldappublicsearchfield']  = 'Suche in';
$labels['ldappublicsearchform']   = 'Adressen suchen';
$labels['ldappublicsearch'] = 'Suchen';

// settings // Einstellungen
$labels['settingsfor']  = 'Einstellungen für';

$labels['preferences']  = 'Einstellungen';
$labels['userpreferences']  = 'Benutzereinstellungen';
$labels['editpreferences']  = 'Einstellungen bearbeiten';

$labels['identities']  = 'Absender';
$labels['manageidentities']  = 'Absender für dieses Konto verwalten';
$labels['newidentity']  = 'Neuer Absender';

$labels['newitem']  = 'Neuer Eintrag';
$labels['edititem']  = 'Eintrag bearbeiten';

$labels['setdefault']  = 'Als Standard';
$labels['language']  = 'Sprache';
$labels['timezone']  = 'Zeitzone';
$labels['pagesize']  = 'Einträge pro Seite';
$labels['signature'] = 'Signatur';
$labels['dstactive']  = 'Sommerzeit';

$labels['folder']  = 'Ordner';
$labels['folders']  = 'Ordner';
$labels['foldername']  = 'Ordnername';
$labels['subscribed']  = 'Abonniert';
$labels['create']      = 'Erstellen';
$labels['createfolder']  = 'Neuen Ordner erstellen';
$labels['deletefolder']  = 'Ordner löschen';
$labels['managefolders']  = 'Ordner verwalten';

$labels['sortby'] = 'Sortieren nach';
$labels['sortasc']  = 'aufsteigend sortieren';
$labels['sortdesc'] = 'absteigend sortieren';

?>
