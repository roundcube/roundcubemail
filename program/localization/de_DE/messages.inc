<?php

/*

+-----------------------------------------------------------------------+
| language/de_DE/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Marcel Schlesinger <info@marcel-schlesinger.de>               |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Anmeldung fehlgeschlagen';
$messages['cookiesdisabled'] = 'Ihr Browser akzeptiert keine Cookies';
$messages['sessionerror'] = 'Ihre Session ist ungültig oder abgelaufen';
$messages['imaperror'] = 'Keine Verbindung zum IMAP-Server';
$messages['servererror'] = 'Serverfehler!';
$messages['servererrormsg'] = 'Serverfehler: $msg';
$messages['errorreadonly'] = 'Die Aktion nicht ausgeführt werden. Der Ordner ist schreibgeschützt.';
$messages['errornoperm'] = 'Die Aktion nicht ausgeführt werden. Zugriff verweigert.';
$messages['invalidrequest'] = 'Ungültige Anfrage! Es wurden keine Daten gespeichert.';
$messages['nomessagesfound'] = 'Keine Nachrichten in diesem Ordner';
$messages['loggedout'] = 'Sie haben Ihre Session erfolgreich beendet. Auf Wiedersehen!';
$messages['mailboxempty'] = 'Ordner ist leer';
$messages['loading'] = 'Lade...';
$messages['uploading'] = 'Datei wird hochgeladen...';
$messages['loadingdata'] = 'Daten werden geladen...';
$messages['checkingmail'] = 'Überprüfung auf neue Nachrichten...';
$messages['sendingmessage'] = 'Nachricht wird gesendet...';
$messages['messagesent'] = 'Nachricht erfolgreich gesendet';
$messages['savingmessage'] = 'Nachricht wird gespeichert...';
$messages['messagesaved'] = 'Nachricht als Entwurf gespeichert';
$messages['successfullysaved'] = 'Erfolgreich gespeichert';
$messages['addedsuccessfully'] = 'Kontakt zum Adressbuch hinzugefügt';
$messages['contactexists'] = 'Es existiert bereits ein Eintrag mit dieser E-Mail-Adresse';
$messages['blockedimages'] = 'Um Ihre Privatsphäre zur schützen, wurden externe Bilder blockiert.';
$messages['encryptedmessage'] = 'Dies ist eine verschlüsselte Nachricht und kann leider nicht angezeigt werden. Sorry!';
$messages['nocontactsfound'] = 'Keine Kontakte gefunden';
$messages['contactnotfound'] = 'Der angeforderte Kontakt wurde nicht gefunden';
$messages['sendingfailed'] = 'Versenden der Nachricht fehlgeschlagen';
$messages['senttooquickly'] = 'Bitte warten Sie $sec Sekunde(n) vor dem Senden dieser Nachricht';
$messages['errorsavingsent'] = 'Ein Fehler ist beim Speichern der gesendeten Nachricht aufgetreten';
$messages['errorsaving'] = 'Beim Speichern ist ein Fehler aufgetreten';
$messages['errormoving'] = 'Nachricht konnte nicht verschoben werden';
$messages['errorcopying'] = 'Konnte Nachrichten nicht kopieren';
$messages['errordeleting'] = 'Nachricht konnte nicht gelöscht werden';
$messages['errormarking'] = 'Nachricht konnte nicht markiert werden';
$messages['deletecontactconfirm'] = 'Wollen Sie die ausgewählten Kontakte wirklich löschen?';
$messages['deletemessagesconfirm'] = 'Wollen Sie die ausgewählten Nachrichten wirklich löschen?';
$messages['deletefolderconfirm'] = 'Wollen Sie diesen Ordner wirklich löschen?';
$messages['purgefolderconfirm'] = 'Wollen Sie diesen Ordner wirklich leeren?';
$messages['folderdeleting'] = 'Lösche Ordner...';
$messages['foldermoving'] = 'Verschiebe Ordner...';
$messages['foldersubscribing'] = 'Abonniere Ordner...';
$messages['folderunsubscribing'] = 'Ordner abbstellen...';
$messages['formincomplete'] = 'Das Formular wurde nicht vollständig ausgefüllt';
$messages['noemailwarning'] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein';
$messages['nonamewarning'] = 'Bitte geben Sie einen Namen ein';
$messages['nopagesizewarning'] = 'Bitte geben Sie die Einträge pro Seite an';
$messages['nosenderwarning'] = 'Bitte geben Sie die E-Mail-Adresse des Senders an';
$messages['norecipientwarning'] = 'Bitte geben Sie mindestens einen Empfänger an';
$messages['nosubjectwarning'] = 'Die Betreffzeile ist leer. Möchten Sie jetzt einen Betreff eingeben?';
$messages['nobodywarning'] = 'Diese Nachricht ohne Inhalt senden?';
$messages['notsentwarning'] = 'Ihre Nachricht wurde nicht gesendet. Wollen Sie die Nachricht verwerfen?';
$messages['noldapserver'] = 'Bitte wählen Sie einen LDAP-Server aus';
$messages['nocontactsreturned'] = 'Es wurden keine Kontakte gefunden';
$messages['nosearchname'] = 'Bitte geben Sie einen Namen oder eine E-Mail-Adresse ein';
$messages['notuploadedwarning'] = 'Es wurden noch nicht alle Dateien hochgeladen. Bitte warten oder Upload abbrechen.';
$messages['searchsuccessful'] = '$nr Nachrichten gefunden';
$messages['searchnomatch'] = 'Die Suche lieferte keine Treffer';
$messages['searching'] = 'Suche...';
$messages['checking'] = 'Prüfe...';
$messages['nospellerrors'] = 'Keine Rechtschreibfehler gefunden';
$messages['folderdeleted'] = 'Ordner erfolgreich gelöscht';
$messages['foldersubscribed'] = 'Der Ordner wurde erfolgreich abonniert';
$messages['folderunsubscribed'] = 'Der Ordner wurde erfolgreich abbestellt';
$messages['folderpurged'] = 'Der Ordner wurde erfolgreich gelöscht';
$messages['folderexpunged'] = 'Der Ordner wurde erfolgreich gepackt';
$messages['deletedsuccessfully'] = 'Erfolgreich gelöscht';
$messages['converting'] = 'Entferne Formatierungen der Nachricht...';
$messages['messageopenerror'] = 'Die Nachricht konnte nicht vom Server geladen werden';
$messages['fileuploaderror'] = 'Hochladen der Datei fehlgeschlagen';
$messages['filesizeerror'] = 'Die hochzuladende Datei überschreitet die Maximalgröße von $size';
$messages['copysuccess'] = '$nr Adressen erfolgreich kopiert';
$messages['copyerror'] = 'Die Adressen konnten nicht kopiert werden';
$messages['sourceisreadonly'] = 'Das Adressverzeichnis kann nicht verändert werden';
$messages['errorsavingcontact'] = 'Die Änderungen konnten nicht gespeichert werden';
$messages['movingmessage'] = 'Die E-Mail wird verschoben...';
$messages['copyingmessage'] = 'Kopiere Nachrichten...';
$messages['deletingmessage'] = 'Lösche Nachricht(en)...';
$messages['markingmessage'] = 'Markiere Nachricht(en)...';
$messages['receiptsent'] = 'Lesebestätigung erfolgreich gesendet';
$messages['errorsendingreceipt'] = 'Bestätigung konnte nicht gesendet werden';
$messages['nodeletelastidentity'] = 'Diese Identität kann nicht gelöscht werden, da dies die letzte ist.';
$messages['forbiddencharacter'] = 'Der Ordnername enthält ein ungültiges Zeichen';
$messages['selectimportfile'] = 'Bitte wählen Sie eine Datei zum Importieren aus';
$messages['addresswriterror'] = 'Das ausgewählte Adressbuch kann nicht verändert werden';
$messages['contactaddedtogroup'] = 'Kontakte wurden dieser Gruppe hinzugefügt';
$messages['contactremovedfromgroup'] = 'Kontakte wurden aus dieser Gruppe entfernt';
$messages['importwait'] = 'Daten werden importiert, bitte warten...';
$messages['importerror'] = 'Import fehlgeschlagen! Die hochgeladene Datei ist nicht im vCard-Format.';
$messages['importconfirm'] = '<b>Es wurden $inserted Adressen erfolgreich importiert und $skipped bestehende Einträge übersprungen</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operation nicht erlaubt!';
$messages['nofromaddress'] = 'Fehlende E-Mail-Adresse in ausgewählter Identität';
$messages['editorwarning'] = 'Beim Wechseln in den Texteditor gehen alle Textformatierungen verloren. Möchten Sie fortfahren?';
$messages['httpreceivedencrypterror'] = 'Ein fataler Konfigurationsfehler ist aufgetreten. Kontaktieren Sie bitte sofort Ihren Administrator. <b>Ihre Nachricht konnte nicht versendet werden.</b>';
$messages['smtpconnerror'] = 'SMTP Fehler ($code): Die Verbindung ist fehlgeschlagen';
$messages['smtpautherror'] = 'SMTP Fehler ($code): Die Authentisierung ist fehlgeschlagen';
$messages['smtpfromerror'] = 'SMTP Fehler ($code): Der Absender "$from" konnte nicht gesetzt werden ($msg)';
$messages['smtptoerror'] = 'SMTP Fehler ($code): Der Empfänger "$to" konnte nicht gesetzt werden ($msg)';
$messages['smtprecipientserror'] = 'SMTP Fehler: Die Empfängerliste konnte nicht verarbeitet werden';
$messages['smtpdsnerror'] = 'SMTP-Fehler: Empfangsbestätigung werden nicht unterstützt';
$messages['smtperror'] = 'SMTP Fehler: $msg';
$messages['emailformaterror'] = 'Ungültige E-Mail-Adresse: $email';
$messages['toomanyrecipients'] = 'Zuviele Empfänger. Reduzieren Sie die Anzahl Empfängeradressen auf $max.';
$messages['maxgroupmembersreached'] = 'Die Anzahl Adressen in dieser Gruppe überschreitet das Maximum von $max';
$messages['internalerror'] = 'Ein interner Fehler ist aufgetreten. Bitte versuchen Sie den Vorgang erneut.';
$messages['contactdelerror'] = 'Fehler beim Löschen';
$messages['contactdeleted'] = 'Kontakt(e) erfolgreich gelöscht';
$messages['groupdeleted'] = 'Gruppe erfolgreich gelöscht';
$messages['grouprenamed'] = 'Gruppe erlogreich umbenannt';
$messages['groupcreated'] = 'Gruppe erlogreich erstellt';
$messages['messagedeleted'] = 'Nachricht(en) erfolgreich gelöscht';
$messages['messagemoved'] = 'Nachricht(en) erfolgreich verschoben';
$messages['messagecopied'] = 'Nachricht(en) erfolgreich kopiert';
$messages['messagemarked'] = 'Nachricht(en) erfolgreich markiert';
$messages['autocompletechars'] = 'Geben Sie mind. $min Zeichen für die Auto-Vervollständigung ein';
$messages['namecannotbeempty'] = 'Der Name darf nicht leer sein';
$messages['nametoolong'] = 'Der Name ist zu lang';
$messages['folderupdated'] = 'Der Ordner wurde erfolgreich aktualisiert';
$messages['foldercreated'] = 'Der Ordner wurde erfolgreich erstellt';
$messages['invalidimageformat'] = 'Kein gültiges Bildformat';

?>
