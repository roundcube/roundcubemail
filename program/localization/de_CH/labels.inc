<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Willkommen bei $product';
$labels['username']  = 'Benutzername';
$labels['password']  = 'Passwort';
$labels['server']    = 'Server';
$labels['login']     = 'Login';

// taskbar
$labels['logout']   = 'Logout';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Einstellungen';
$labels['addressbook'] = 'Adressbuch';

// mailbox names
$labels['inbox']  = 'Posteingang';
$labels['drafts'] = 'Entwürfe';
$labels['sent']   = 'Gesendet';
$labels['trash']  = 'Gelöscht';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Betreff';
$labels['from']    = 'Absender';
$labels['sender']  = 'Absender';
$labels['to']      = 'Empfänger';
$labels['cc']      = 'Kopie (CC)';
$labels['bcc']     = 'Blind-Kopie';
$labels['replyto'] = 'Antwort an';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Datum';
$labels['size']    = 'Grösse';
$labels['priority'] = 'Priorität';
$labels['organization'] = 'Organisation';
$labels['readstatus'] = 'Gelesen/Ungelesen';
$labels['listoptions'] = 'Listenoptionen...';

$labels['mailboxlist'] = 'Ordner';
$labels['messagesfromto'] = 'Nachrichten $from bis $to von $count';
$labels['threadsfromto'] = 'Konversationen $from bis $to von $count';
$labels['messagenrof'] = 'Nachricht $nr von $count';
$labels['fromtoshort'] = '$from – $to von $count';

$labels['copy']     = 'Kopieren';
$labels['move']     = 'Verschieben';
$labels['moveto']   = 'Verschieben nach...';
$labels['download'] = 'Download';
$labels['showattachment'] = 'Anzeigen';
$labels['showanyway'] = 'Trotzdem anzeigen';

$labels['filename'] = 'Dateiname';
$labels['filesize'] = 'Dateigrösse';

$labels['addtoaddressbook'] = 'Ins Adressbuch übernehmen';

// weekdays short
$labels['sun'] = 'So';
$labels['mon'] = 'Mo';
$labels['tue'] = 'Di';
$labels['wed'] = 'Mi';
$labels['thu'] = 'Do';
$labels['fri'] = 'Fr';
$labels['sat'] = 'Sa';

// weekdays long
$labels['sunday']    = 'Sonntag';
$labels['monday']    = 'Montag';
$labels['tuesday']   = 'Dienstag';
$labels['wednesday'] = 'Mittwoch';
$labels['thursday']  = 'Donnerstag';
$labels['friday']    = 'Freitag';
$labels['saturday']  = 'Samstag';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mär';
$labels['apr']	= 'Apr';
$labels['may']	= 'Mai';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Okt';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dez';

// months long
$labels['longjan']	= 'Januar';
$labels['longfeb']	= 'Februar';
$labels['longmar']	= 'März';
$labels['longapr']	= 'April';
$labels['longmay']	= 'Mai';
$labels['longjun']	= 'Juni';
$labels['longjul']	= 'Juli';
$labels['longaug']	= 'August';
$labels['longsep']	= 'September';
$labels['longoct']	= 'Oktober';
$labels['longnov']	= 'November';
$labels['longdec']	= 'Dezember';

$labels['today'] = 'Heute';

// toolbar buttons
$labels['refresh']          = 'Aktualisieren';
$labels['checkmail']        = 'Auf neue Nachrichten prüfen';
$labels['compose']          = 'Schreiben';
$labels['writenewmessage']  = 'Neue Nachricht schreiben';
$labels['reply']            = 'Antworten';
$labels['replytomessage']   = 'Antwort verfassen';
$labels['replytoallmessage'] = 'Antwort an Absender und alle Empfänger verfassen';
$labels['replyall']         = 'Allen antworten';
$labels['replylist']        = 'Liste antworten';
$labels['forward']          = 'Weiterleiten';
$labels['forwardinline']    = 'Eingebunden weiterleiten';
$labels['forwardattachment'] = 'Als Anhang weiterleiten';
$labels['forwardmessage']   = 'Nachricht weiterleiten';
$labels['deletemessage']    = 'Nachricht löschen';
$labels['movemessagetotrash'] = 'Nachricht in den Papierkorb verschieben';
$labels['printmessage']     = 'Nachricht drucken';
$labels['previousmessage']  = 'Vorherige Nachricht anzeigen';
$labels['firstmessage']     = 'Die erste Nachricht anzeigen';
$labels['nextmessage']      = 'Nächste Nachricht anzeigen';
$labels['lastmessage']      = 'Die letzte Nachricht anzeigen';
$labels['backtolist']       = 'Zurück zur Liste';
$labels['viewsource']       = 'Quelltext anzeigen';
$labels['mark']             = 'Markieren';
$labels['markmessages']     = 'Nachrichten markieren';
$labels['markread']         = 'Als gelesen';
$labels['markunread']       = 'Als ungelesen';
$labels['markflagged']      = 'Markierung hinzufügen';
$labels['markunflagged']    = 'Markierung entfernen';
$labels['moreactions']      = 'Mehr ...';
$labels['more']             = 'Mehr';
$labels['back']             = 'Zurück';
$labels['options']          = 'Optionen';

$labels['select'] = 'Auswählen';
$labels['all'] = 'Alle';
$labels['none'] = 'Keine';
$labels['currpage'] = 'Aktuelle Seite';
$labels['unread'] = 'Ungelesene';
$labels['flagged'] = 'Markierte';
$labels['unanswered'] = 'Unbeantwortete';
$labels['deleted'] = 'Gelöschte';
$labels['undeleted'] = 'Nicht gelöscht';
$labels['invert'] = 'Umkehren';
$labels['filter'] = 'Filter';
$labels['list'] = 'Liste';
$labels['threads'] = 'Konversationen';
$labels['expand-all'] = 'Alle aufklappen';
$labels['expand-unread'] = 'Ungelesene aufklappen';
$labels['collapse-all'] = 'Alle zuklappen';
$labels['threaded'] = 'Gruppiert';

$labels['autoexpand_threads'] = 'Konversationen aufklappen';
$labels['do_expand'] = 'alle';
$labels['expand_only_unread'] = 'nur ungelesene';
$labels['fromto'] = 'Sender/Empfänger';
$labels['flag'] = 'Markierung';
$labels['attachment'] = 'Anhang';
$labels['nonesort'] = 'Keine';
$labels['sentdate'] = 'Sendedatum';
$labels['arrival'] = 'Empfangsdatum';
$labels['asc'] = 'aufsteigend';
$labels['desc'] = 'absteigend';
$labels['listcolumns'] = 'Spalten';
$labels['listsorting'] = 'Sortierung';
$labels['listorder'] = 'Ordnung';
$labels['listmode'] = 'Anzeigemodus';

$labels['folderactions'] = 'Ordneraktionen...';
$labels['compact'] = 'Packen';
$labels['empty'] = 'Leeren';

$labels['quota'] = 'Verwendeter Speicherplatz';
$labels['unknown']  = 'unbekannt';
$labels['unlimited']  = 'unlimitiert';

$labels['quicksearch']  = 'Schnellsuche';
$labels['resetsearch']  = 'Löschen';
$labels['searchmod']  = 'Suchkriterien ändern';
$labels['msgtext']  = 'Ganze Nachricht';
$labels['body']  = 'Inhalt';

$labels['openinextwin'] = 'In neuem Fenster öffnen';
$labels['emlsave'] = 'Herunterladen (.eml)';

// message compose
$labels['editasnew']      = 'Als neue Nachricht öffnen';
$labels['send']           = 'Senden';
$labels['sendmessage']    = 'Nachricht jetzt senden';
$labels['savemessage']    = 'Nachricht speichern';
$labels['addattachment']  = 'Datei anfügen';
$labels['charset']        = 'Zeichensatz';
$labels['editortype']     = 'Editor-Typ';
$labels['returnreceipt']  = 'Empfangsbestätigung (MDN)';
$labels['dsn']            = 'Übermittlungsbestätigung (DSN)';
$labels['mailreplyintro'] = 'Am $date, schrieb $sender:';
$labels['originalmessage'] = 'Originalnachricht';

$labels['editidents']    = 'Absender bearbeiten';
$labels['spellcheck']    = 'Rechtschreibung';
$labels['checkspelling'] = 'Rechtschreibung prüfen';
$labels['resumeediting'] = 'Bearbeitung fortsetzen';
$labels['revertto']      = 'Zurück zu';

$labels['attach'] = 'Anhängen';
$labels['attachments'] = 'Anhänge';
$labels['upload'] = 'Hochladen';
$labels['uploadprogress'] = '$percent ($current von $total)';
$labels['close']  = 'Schliessen';
$labels['messageoptions']  = 'Optionen...';

$labels['low']     = 'Niedrig';
$labels['lowest']  = 'Niedrigste';
$labels['normal']  = 'Normal';
$labels['high']    = 'Hoch';
$labels['highest'] = 'Höchste';

$labels['nosubject']  = '(kein Betreff)';
$labels['showimages'] = 'Bilder anzeigen';
$labels['alwaysshow'] = 'Bilder von $sender immer zeigen';
$labels['isdraft']    = 'Dies ist ein Entwurf';
$labels['andnmore']   = '$nr weitere...';
$labels['togglemoreheaders'] = 'Weitere Kopfzeilen anzeigen';
$labels['togglefullheaders'] = 'Original-Kopfzeilen ein-/ausblenden';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Klartext';
$labels['savesentmessagein'] = 'Nachricht speichern in';
$labels['dontsave'] = 'nicht speichern';
$labels['maxuploadsize'] = 'Maximal erlaubte Dateigrösse ist $size';

$labels['addcc'] = 'Cc hinzufügen';
$labels['addbcc'] = 'Bcc hinzufügen';
$labels['addreplyto'] = 'Antwortadresse hinzufügen';
$labels['addfollowupto'] = 'Followup-To hinzufügen';

// mdn
$labels['mdnrequest'] = 'Der Sender dieser Nachricht hat eine Empfangsbestätigung angefordert. Möchten Sie diese jetzt senden?';
$labels['receiptread'] = 'Empfangsbestätigung (gelesen)';
$labels['yourmessage'] = 'Dieses ist eine Empfangsbestätigung für Ihre Nachricht';
$labels['receiptnote'] = 'Hinweis: Der Empfänger hat den Empfang der Nachricht bestätigt. Dieses ist keine Garantie, dass die Nachricht gelesen und verstanden wurde.';

// address boook
$labels['name']         = 'Anzeigename';
$labels['firstname']    = 'Vorname';
$labels['surname']      = 'Nachname';
$labels['middlename']   = '2. Vorname';
$labels['nameprefix']   = 'Präfix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Spitzname';
$labels['jobtitle']     = 'Titel';
$labels['department']   = 'Abteilung';
$labels['gender']       = 'Geschlecht';
$labels['maidenname']   = 'Mädchenname';
$labels['email']        = 'E-Mail';
$labels['phone']        = 'Telefon';
$labels['address']      = 'Adresse';
$labels['street']       = 'Strasse';
$labels['locality']     = 'Ort';
$labels['zipcode']      = 'PLZ';
$labels['region']       = 'Region';
$labels['country']      = 'Land';
$labels['birthday']     = 'Geburtstag';
$labels['anniversary']  = 'Jahrestag';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notizen';
$labels['male']   = 'männlich';
$labels['female'] = 'weiblich';
$labels['manager'] = 'Vorgesetzte/r';
$labels['assistant'] = 'Assistent';
$labels['spouse'] = 'Partner/in';
$labels['allfields'] = 'Alle Felder';
$labels['search'] = 'Suche';
$labels['advsearch'] = 'Erweiterte Suche';
$labels['advanced'] = 'Erweitert';
$labels['other'] = 'Andere';

$labels['typehome']   = 'Zuhause';
$labels['typework']   = 'Arbeit';
$labels['typeother']  = 'Andere';
$labels['typemobile']  = 'Mobiltelefon';
$labels['typemain']  = 'Hauptnummer';
$labels['typehomefax']  = 'Fax Zuhause';
$labels['typeworkfax']  = 'Fax Arbeit';
$labels['typecar']  = 'Auto';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistent';
$labels['typehomepage']  = 'Homepage';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Feld hinzufügen...';
$labels['addcontact'] = 'Kontakt hinzufügen';
$labels['editcontact'] = 'Kontakt bearbeiten';
$labels['contacts'] = 'Kontakte';
$labels['contactproperties'] = 'Kontaktdaten';
$labels['personalinfo'] = 'Persönliche Informationen';

$labels['edit']   = 'Bearbeiten';
$labels['cancel'] = 'Abbrechen';
$labels['save']   = 'Speichern';
$labels['delete'] = 'Löschen';
$labels['rename'] = 'Umbenennen';
$labels['addphoto'] = 'Hinzufügen';
$labels['replacephoto'] = 'Ersetzen';
$labels['uploadphoto'] = 'Bild hochladen';

$labels['newcontact']     = 'Neuen Kontakt erfassen';
$labels['deletecontact']  = 'Gewählte Kontakte löschen';
$labels['composeto']      = 'Nachricht verfassen';
$labels['contactsfromto'] = 'Kontakte $from bis $to von $count';
$labels['print']          = 'Drucken';
$labels['export']         = 'Exportieren';
$labels['exportall']      = 'Alle exportieren';
$labels['exportsel']      = 'Ausgewählte exportieren';
$labels['exportvcards']   = 'Kontakte im vCard-Format exportieren';
$labels['newcontactgroup'] = 'Neue Adressgruppe erstellen';
$labels['grouprename']    = 'Gruppe umbenennen';
$labels['groupdelete']    = 'Gruppe löschen';
$labels['groupremoveselected'] = 'Gewählte Kontakte aus Gruppe entfernen';

$labels['previouspage']   = 'Eine Seite zurück';
$labels['firstpage']      = 'Erste Seite';
$labels['nextpage']       = 'Nächste Seite';
$labels['lastpage']       = 'Letzte Seite';

$labels['group'] = 'Gruppe';
$labels['groups'] = 'Gruppen';
$labels['personaladrbook'] = 'Persönliches Adressbuch';

$labels['searchsave'] = 'Suche speichern';
$labels['searchdelete'] = 'Suche löschen';

$labels['import'] = 'Importieren';
$labels['importcontacts'] = 'Adressen importieren';
$labels['importfromfile'] = 'Import aus Datei:';
$labels['importtarget'] = 'Neue Kontakte speichern in:';
$labels['importreplace'] = 'Bestehendes Adressbuch komplett ersetzen';
$labels['importdesc'] = 'Sie können Kontakte aus einem bestehenden Adressbuch hochladen.rnEs können Adressbücher im <a href="http://de.wikipedia.org/wiki/VCard">vCard</a>- oder CSV-Format importiert werden.';
$labels['done'] = 'Fertig';

// settings
$labels['settingsfor'] = 'Einstellungen für';
$labels['about'] = 'Über';
$labels['preferences'] = 'Einstellungen';
$labels['userpreferences'] = 'Benutzereinstellungen';
$labels['editpreferences'] = 'Einstellungen bearbeiten';

$labels['identities'] = 'Absender';
$labels['manageidentities'] = 'Absender für dieses Konto verwalten';
$labels['newidentity'] = 'Neuer Absender';

$labels['newitem'] = 'Neuer Eintrag';
$labels['edititem'] = 'Eintrag bearbeiten';

$labels['preferhtml'] = 'HTML bevorzugen';
$labels['defaultcharset'] = 'Standard-Zeichensatz';
$labels['htmlmessage'] = 'HTML Nachricht';
$labels['messagepart'] = 'Abschnitt';
$labels['digitalsig'] = 'Digitale Signatur';
$labels['dateformat'] = 'Datumsformat';
$labels['timeformat'] = 'Zeitformat';
$labels['prettydate'] = 'Kurze Datumsanzeige';
$labels['setdefault']  = 'Als Standard';
$labels['autodetect']  = 'Automatisch';
$labels['language']  = 'Sprache';
$labels['timezone']  = 'Zeitzone';
$labels['pagesize']  = 'Einträge pro Seite';
$labels['signature'] = 'Signatur';
$labels['dstactive']  = 'Sommerzeit';
$labels['showinextwin'] = 'Nachrichten in neuem Fenster öffnen';
$labels['composeextwin'] = 'Nachrichten in neuem Fenster verfassen';
$labels['htmleditor'] = 'HTML-Nachrichten verfassen';
$labels['htmlonreply'] = 'nur Antworten auf HTML-Nachrichten';
$labels['htmlonreplyandforward'] = 'Antworten oder Weiterleiten einer HTML-Nachricht';
$labels['htmlsignature'] = 'HTML-Signatur';
$labels['previewpane'] = 'Nachrichtenvorschau anzeigen';
$labels['skin'] = 'Oberflächendesign';
$labels['logoutclear'] = 'Papierkorb beim Abmelden leeren';
$labels['logoutcompact'] = 'Posteingang beim Abmelden packen';
$labels['uisettings'] = 'Benutzeroberfläche';
$labels['serversettings'] = 'Server-Einstellungen';
$labels['mailboxview'] = 'Mailbox-Ansicht';
$labels['mdnrequests'] = 'Empfangsbestätigung senden';
$labels['askuser'] = 'immer fragen';
$labels['autosend'] = 'automatisch senden';
$labels['autosendknown'] = 'für bekannte Absender, sonst fragen';
$labels['autosendknownignore'] = 'für bekannte Absender, sonst ignorieren';
$labels['ignore'] = 'ignorieren';
$labels['readwhendeleted'] = 'Beim Löschen als gelesen markieren';
$labels['flagfordeletion'] = 'Als gelöscht markieren anstatt in den Papierkorb verschieben';
$labels['skipdeleted'] = 'Zeige keine gelöschten Nachrichten an';
$labels['deletealways'] = 'Löschen wenn Verschieben in den Papierkorb nicht möglich';
$labels['deletejunk'] = 'Nachrichten in Spam direkt löschen';
$labels['showremoteimages'] = 'Eingebettete Bilder vom Internet laden';
$labels['fromknownsenders'] = 'bei bekannten Absendern';
$labels['always'] = 'immer';
$labels['showinlineimages'] = 'Angehängte Bilder unter der Nachricht anzeigen';
$labels['autosavedraft']  = 'Entwurf autom. speichern';
$labels['everynminutes']  = '$n Minute(n)';
$labels['refreshinterval']  = 'Refresh (auf neue Nachrichten prüfen, etc.)';
$labels['never']  = 'nie';
$labels['immediately']  = 'sofort';
$labels['messagesdisplaying'] = 'Nachrichtendarstellung';
$labels['messagescomposition'] = 'Nachrichtenerstellung';
$labels['mimeparamfolding'] = 'Namen der Dateianhänge';
$labels['2231folding'] = 'Vollständig RFC 2231 kompatibel (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 kompatibel (Microsoft Outlook)';
$labels['2047folding'] = 'Vollständig RFC 2047 kompatibel (andere)';
$labels['force7bit'] = 'MIME-Kodierung für 8-bit Zeichen';
$labels['advancedoptions'] = 'Erweiterte Einstellungen';
$labels['focusonnewmessage'] = 'Fokussiere Browserfenster bei neuen Nachrichten';
$labels['checkallfolders'] = 'Alle Ordner auf neue Nachrichten prüfen';
$labels['displaynext'] = 'Zeige nächste Nachricht nach verschieben/löschen';
$labels['defaultfont'] = 'Standardschrift in HTML-Nachrichten';
$labels['mainoptions'] = 'Allgemein';
$labels['browseroptions'] = 'Browsereinstellungen';
$labels['section'] = 'Bereich';
$labels['maintenance'] = 'Wartung';
$labels['newmessage'] = 'Neue Nachrichten';
$labels['signatureoptions'] = 'Signatur';
$labels['whenreplying'] = 'Beantworten einer Nachricht';
$labels['replyempty'] = 'Antwort ohne Zitat der Originalnachricht';
$labels['replytopposting'] = 'Antwort über der Originalnachricht';
$labels['replybottomposting'] = 'Antwort unter der Originalnachricht';
$labels['replyremovesignature'] = 'Beim Antworten die Signatur der Originalnachricht entfernen';
$labels['autoaddsignature'] = 'Signatur automatisch einfügen';
$labels['newmessageonly'] = 'nur bei neuen Nachrichten';
$labels['replyandforwardonly'] = 'nur bei Antworten und Weiterleitungen';
$labels['replysignaturepos'] = 'Beim Antworten oder Weiterleiten die Signatur';
$labels['belowquote'] = 'unter der Originalnachricht einfügen';
$labels['abovequote'] = 'über der Originalnachricht einfügen';
$labels['insertsignature'] = 'Signatur einfügen';
$labels['previewpanemarkread']  = 'Nachricht in Vorschau als gelesen markieren';
$labels['afternseconds']  = 'nach $n Sekunden';
$labels['reqmdn'] = 'Empfangsbestätigung (MSN) immer anfordern';
$labels['reqdsn'] = 'Übermittlungsbestätigung (DSN) immer anfordern';
$labels['replysamefolder'] = 'Antworten im selben Ordner wie Original speichern';
$labels['defaultabook'] = 'Standard-Adressbuch';
$labels['autocompletesingle'] = 'Keine alternativen E-Mail-Adressen in Autovervollständigung';
$labels['listnamedisplay'] = 'Kontakte auflisten als';
$labels['spellcheckbeforesend'] = 'Rechtscheibung vor dem Senden prüfen';
$labels['spellcheckoptions'] = 'Rechtschreibprüfungsoptionen';
$labels['spellcheckignoresyms'] = 'Wörter mit Symbolen überspringen';
$labels['spellcheckignorenums'] = 'Wörter mit Ziffern überspringen';
$labels['spellcheckignorecaps'] = 'Wörter überspringen, die nur aus Grossbuchstaben bestehen';
$labels['addtodict'] = 'Zum Wörterbuch hinzufügen';
$labels['mailtoprotohandler'] = 'Als Empfänger für mailto: Links einrichten';
$labels['forwardmode'] = 'Weiterleiten einer Nachricht';
$labels['inline'] = 'eingebettet';
$labels['asattachment'] = 'als Anhang';

$labels['folder']  = 'Ordner';
$labels['folders']  = 'Ordner';
$labels['foldername']  = 'Ordnername';
$labels['subscribed']  = 'Abonniert';
$labels['messagecount'] = 'Nachrichten';
$labels['create']  = 'Erstellen';
$labels['createfolder']  = 'Neuen Ordner erstellen';
$labels['managefolders']  = 'Ordner verwalten';
$labels['specialfolders'] = 'Spezialordner';
$labels['properties'] = 'Eigenschaften';
$labels['folderproperties'] = 'Ordnereigenschaften';
$labels['parentfolder'] = 'Eltern';
$labels['location'] = 'Speicherort';
$labels['info'] = 'Informationen';
$labels['getfoldersize'] = 'Ordnergrösse anzeigen';
$labels['changesubscription'] = 'Abonnieren';
$labels['foldertype'] = 'Ordnertyp';
$labels['personalfolder']  = 'Privater Ordner';
$labels['otherfolder']  = 'Order eines anderen Benutzers';
$labels['sharedfolder']  = 'Öffentlicher Ordner';

$labels['sortby'] = 'Sortieren nach';
$labels['sortasc']  = 'aufsteigend sortieren';
$labels['sortdesc'] = 'absteigend sortieren';
$labels['undo'] = 'Rückgängig';

$labels['installedplugins'] = 'Installierte Plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Quellcode';
$labels['license'] = 'Lizenz';
$labels['support'] = 'Support';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Englisch';
$labels['westerneuropean'] = 'Westeuropäisch';
$labels['easterneuropean'] = 'Osteuropäisch';
$labels['southeasterneuropean'] = 'Südosteuropäisch';
$labels['baltic'] = 'Baltisch';
$labels['cyrillic'] = 'Kyrillisch';
$labels['arabic'] = 'Arabisch';
$labels['greek'] = 'Griechisch';
$labels['hebrew'] = 'Hebräisch';
$labels['turkish'] = 'Türkisch';
$labels['nordic'] = 'Skandinavisch';
$labels['thai'] = 'Thailändisch';
$labels['celtic'] = 'Keltisch';
$labels['vietnamese'] = 'Vietnamesisch';
$labels['japanese'] = 'Japanisch';
$labels['korean'] = 'Koreanisch';
$labels['chinese'] = 'Chinesisch';

?>
