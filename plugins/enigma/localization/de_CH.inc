<?php

/**
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/

$labels['encryption'] = 'Verschlüsselung';
$labels['enigmacerts'] = 'S/MIME Zertifikate';
$labels['enigmakeys'] = 'PGP Schlüssel';
$labels['keysfromto'] = 'Schlüssel $from bis $to von $count';
$labels['keyname'] = 'Schlüssel';
$labels['keyid'] = 'Schlüssel ID';
$labels['keyuserid'] = 'Benutzer ID';
$labels['keytype'] = 'Schlüsseltyp';
$labels['fingerprint'] = 'Fingerprint';
$labels['subkeys'] = 'Sub-Schlüssel';
$labels['keyprops'] = 'Schlüsseleigenschaften';
$labels['basicinfo'] = 'Allgemeine Informationen';
$labels['userids'] = 'Zusätzliche Benutzer';
$labels['typepublickey'] = 'Öffentlicher Schlüssel';
$labels['typekeypair'] = 'Schlüsselpaar';
$labels['keyattfound'] = 'Diese Nachricht enthält angehängte PGP Schlüssel.';
$labels['keyattimport'] = 'Schlüssel importieren';
$labels['typesign'] = 'Signieren';
$labels['typeencrypt'] = 'Verschlüsseln';
$labels['typecert'] = 'Bestätigen';
$labels['typeauth'] = 'Authentifizierung';
$labels['subkeyid'] = 'Sub-Schlüssel ID';
$labels['subkeyalgo'] = 'Algorithmus';
$labels['subkeycreated'] = 'Erstellt am';
$labels['subkeyexpires'] = 'Läuft aus';
$labels['subkeyusage'] = 'Verwendet für';
$labels['expiresnever'] = 'nie';
$labels['unknown'] = 'unbekannt';
$labels['uservalid'] = 'Gültigkeit';
$labels['userid'] = 'ID';
$labels['valid'] = 'gültig';
$labels['supportencryption'] = 'Nachrichtenverschlüsselung und Signierung aktivieren';
$labels['supportsignatures'] = 'Verifizierung der Nachrichtensignatur aktivieren';
$labels['supportdecryption'] = 'Entschlüsselung von Nachrichten aktivieren';
$labels['signdefault'] = 'Nachrichten immer signieren';
$labels['encryptdefault'] = 'Nachrichten immer verschlüsseln';
$labels['attachpubkeydefault'] = 'Öffentlichen Schlüssel in der Nachricht anfügen';
$labels['passwordtime'] = 'Private Schlüssel behalten für';
$labels['nminutes'] = '$m Minuten';
$labels['wholesession'] = 'die gesamte Sitzung';
$labels['createkeys'] = 'Neues Schlüsselpaar erstellen';
$labels['importkeys'] = 'Schlüssel importieren';
$labels['exportkeys'] = 'Schlüssel exportieren';
$labels['keyactions'] = 'Aktionen...';
$labels['keyremove'] = 'Entfernen';
$labels['keydisable'] = 'Deaktivieren';
$labels['keyrevoke'] = 'Widerrufen';
$labels['keysend'] = 'Öffentlichen Schlüssel versenden';
$labels['keychpass'] = 'Passwort ändern';
$labels['keyadd'] = 'Schlüssel hinzufügen';
$labels['newkeyident'] = 'Identität';
$labels['newkeypass'] = 'Passwort';
$labels['newkeypassconfirm'] = 'Passwort bestätigen';
$labels['newkeytype'] = 'Schlüsseltyp';
$labels['rsa2048'] = 'RSA - 2048 Bit';
$labels['rsa4096'] = 'RSA - 4096 Bit';
$labels['ecckeypair'] = 'ECC (Elliptische Kurve)';
$labels['keygenerating'] = 'Schlüssel generieren...';
$labels['encryptionoptions'] = 'Verschlüsselungsoptionen';
$labels['encryptmsg'] = 'Diese Nachricht verschlüsseln';
$labels['signmsg'] = 'Diese Nachricht digital signieren';
$labels['sendunencrypted'] = 'Unverschlüsselt senden';
$labels['enterkeypasstitle'] = 'Bitte Schlüssel Passwort eingeben';
$labels['enterkeypass'] = 'Ein Passwort wird benötigt um den privaten Schlüssel ($keyid) für Benutzer: $user zu entsperren';
$labels['attachpubkeymsg'] = 'Meinen öffentlichen Schlüssel anfügen';
$labels['keyexportprompt'] = 'Möchten Sie geheime Schlüssel in die gespeicherte OpenPGP-Schlüsseldatei mit aufnehmen?';
$labels['onlypubkeys'] = 'Nur öffentliche Schlüssel exportieren';
$labels['withprivkeys'] = 'Geheime Schlüssel exportieren';
$labels['findkey'] = 'Auf Schlüsselserver(n) suchen';
$labels['keyimportlabel'] = 'Aus Datei importieren';
$labels['keyimportsearchlabel'] = 'Auf Schlüsselserver(n) suchen';
$labels['managekeys'] = 'PGP-Schlüssel verwalten';
$labels['identitymatchingprivkeys'] = 'Für diesen Absender sind $nr private Schlüssel in Ihrem Schlüsselbund gespeichert:';
$labels['identitynoprivkeys'] = 'Für diesen Absender existiert noch kein PGP Schlüsselpaar in Ihrem  Schlüsselbund.';
$labels['arialabelkeyexportoptions'] = 'Optionen für Schlüsselexport';
$labels['arialabelkeysearchform'] = 'Schlüsselsuche';
$labels['arialabelkeyoptions'] = 'Schlüssel-Optionen';
$messages['sigvalid'] = 'Signatur von $sender wurde erfolgreich überprüft.';
$messages['sigvalidpartial'] = 'Signatur von $sender wurde überprüft, aber ein Teil der Nachricht wurde nicht signiert.';
$messages['siginvalid'] = 'Ungültige Signatur von $sender.';
$messages['sigunverified'] = 'Unbestätigte Signatur. Zertifikat nicht überprüft. Zertifikat-ID: $keyid. ';
$messages['signokey'] = 'Unbestätigte Signatur. Öffentlicher Schlüssel wurde nicht gefunden. Schlüssel-ID: $keyid.';
$messages['sigerror'] = 'Unbestätigte Signatur. Interner Fehler.';
$messages['decryptok'] = 'Nachricht entschlüsselt.';
$messages['decrypterror'] = 'Entschlüsselung fehlgeschlagen.';
$messages['decryptnokey'] = 'Entschlüsselung fehlgeschlagen. Privater Schlüssel wurde nicht gefunden. Schlüssel-ID: $keyid.';
$messages['decryptnomdc'] = 'Entschlüsselung übersprungen. Nachricht ist nicht integritätsgeschützt.';
$messages['decryptbadpass'] = 'Entschlüsselung fehlgeschlagen. Passwort ungültig.';
$messages['decryptnopass'] = 'Entschlüsselung fehlgeschlagen. Schlüsselpasswort wird benötigt.';
$messages['decryptpartial'] = 'Nachricht entschlüsselt, aber ein Teil der Nachricht wurde nicht verschlüsselt.';
$messages['signerror'] = 'Signierung fehlgeschlagen.';
$messages['signnokey'] = 'Signierung fehlgeschlagen. Privater Schlüssel wurde nicht gefunden.';
$messages['signbadpass'] = 'Signierung fehlgeschlagen. Passwort ungültig.';
$messages['signnopass'] = 'Signierung fehlgeschlagen. Schlüsselpasswort wird benötigt.';
$messages['encrypterror'] = 'Verschlüsselung fehlgeschlagen.';
$messages['encryptnokey'] = 'Verschlüsselung fehlgeschlagen. Öffentlicher Schlüssel für $email nicht gefunden';
$messages['encryptnoprivkey'] = 'Verschlüsselung fehlgeschlagen. Privater Schlüssel nicht gefunden.';
$messages['nokeysfound'] = 'Keine Schlüssel gefunden';
$messages['keynotfound'] = 'Schlüssel nicht gefunden!';
$messages['keyopenerror'] = 'Abrufen der Schlüsselinformationen nicht möglich. Interner Fehler.';
$messages['keylisterror'] = 'Auflisten der Schlüsselinformationen nicht möglich! Interner Fehler.';
$messages['keysimportfailed'] = 'Schlüsselimport fehlgeschlagen! Interner Fehler.';
$messages['keysimportsuccess'] = 'Schlüsselimport erfolgreich. Importiert: $new, unverändert: $old.';
$messages['keyremoving'] = 'Schlüssel entfernen...';
$messages['keyremoveconfirm'] = 'Bist du dir sicher, dass du die ausgewählten Schlüssel entfernen willst?';
$messages['keyremovesuccess'] = 'Schlüssel erfolgreich gelöscht.';
$messages['keyremoveerror'] = 'Löschung der Schlüssel nicht möglich.';
$messages['keyimporttext'] = 'Private und öffentliche Schlüssel sowie zurückgerufene Signaturen können im ASCII-Armor Format importiert werden.';
$messages['keyimportsearchtext'] = 'Sie können für öffentliche Schlüssel nach dem Schlüsselbezeichner, Benutzername oder der E-Mail-Adresse suchen und diese dann direkt importieren.';
$messages['keystoragenotice'] = 'Alle öffentlichen und privaten Schlüssel werden auf dem Server gespeichert.';
$messages['formerror'] = 'Bitte, alle Eingabefelder ausfüllen. Alle Eingabefelder werden benötigt!';
$messages['passwordsdiffer'] = 'Passwörter stimmen nicht überein.';
$messages['keygenerateerror'] = 'Schlüsselgenerierung fehlgeschlagen.';
$messages['keygeneratesuccess'] = 'Schlüsselpaar erfolgreich generiert und importiert.';
$messages['keygennosupport'] = 'Dein Browser unterstützt keine Kryptopgraphiefunktionen. Konnte Schlüsselpaar nicht generieren!';
$messages['noidentselected'] = 'Sie müssen mindestens eine Identität für den Schlüssel auswählen!';
$messages['nonameident'] = 'Identität muss einen Benutzernamen definiert haben.';
$labels['newkeysize'] = 'Schlüssellänge';
$labels['key2048'] = '2048 bits - standard';
$labels['key4096'] = '4096 bits - sicher';
