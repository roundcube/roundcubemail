<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2015, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/enigma/
*/

$labels = array();
$labels['encryption'] = 'Verschlüsselung';
$labels['enigmacerts'] = 'S/MIME Zertifikate';
$labels['enigmakeys'] = 'PGP Schlüssel';
$labels['keysfromto'] = 'Schlüssel $from bis $to von $count';
$labels['keyname'] = 'Name';
$labels['keyid'] = 'Schlüssel-ID';
$labels['keyuserid'] = 'Benutzer-ID';
$labels['keytype'] = 'Schlüsselart';
$labels['fingerprint'] = 'Fingerprint';
$labels['subkeys'] = 'Untergeordnete Schlüssel';
$labels['basicinfo'] = 'Grundlegende Informationen';
$labels['userids'] = 'Zusätzliche Benutzer';
$labels['typepublickey'] = 'Öffentlicher Schlüssel';
$labels['typekeypair'] = 'Schlüsselpaar';
$labels['keyattfound'] = 'Diese Nachricht enthält angehängte PGP-Schlüssel.';
$labels['keyattimport'] = 'Importiere Schlüssel';
$labels['typesign'] = 'Unterschrift';
$labels['typeencrypt'] = 'Verschlüsselung';
$labels['typecert'] = 'Bestätigung';
$labels['typeauth'] = 'Beglaubigung';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algorithmus';
$labels['subkeycreated'] = 'Erstellt';
$labels['subkeyexpires'] = 'Verfällt';
$labels['subkeyusage'] = 'Nutzung';
$labels['expiresnever'] = 'niemals';
$labels['unknown'] = 'unbekannt';
$labels['uservalid'] = 'gültig';
$labels['userid'] = 'ID';
$labels['valid'] = 'gültig';

$labels['supportencryption'] = 'Verschlüsselung und Unterschreiben von Nachrichten erlauben';
$labels['supportsignatures'] = 'Überpüfung von Unterschriften in Nachrichten erlauben';
$labels['supportdecryption'] = 'Entschlüsselung von Nachrichten erlauben';
$labels['signdefault'] = 'Nachrichten immer unterschreiben';
$labels['encryptdefault'] = 'Nachrichten immer verschlüsseln';
$labels['attachpubkeydefault'] = 'Meinen öffentlichen PGP-Schlüssel immer anhängen';
$labels['passwordtime'] = 'Passwort für geheimen Schlüssel merken für';
$labels['nminutes'] = '$m Minuten';
$labels['wholesession'] = 'gesamte Sitzung';

$labels['createkeys'] = 'Erstelle ein neues Schlüsselpaar';
$labels['importkeys'] = 'Importiere Schlüssel';
$labels['exportkeys'] = 'Exportiere Schlüssel';
$labels['keyactions'] = 'Schlüsselaktionen...';
$labels['keyremove'] = 'Entfernen';
$labels['keydisable'] = 'Deaktivieren';
$labels['keyrevoke'] = 'Widerrufen';
$labels['keysend'] = 'Öffentlichen Schlüssel in einer Nachricht senden';
$labels['keychpass'] = 'Passwort ändern';

$labels['newkeyident'] = 'Identität:';
$labels['newkeypass'] = 'Passwort:';
$labels['newkeypassconfirm'] = 'Passwort bestätigen:';
$labels['newkeysize'] = 'Schlüsselgröße:';
$labels['key2048'] = '2048 bits - Standard';
$labels['key4096'] = '4096 bits - sicherer';
$labels['keygenerating'] = 'Schlüssel werden erstellt...';

$labels['encryptionoptions'] = 'Verschlüsselungsoptionen...';
$labels['encryptmsg'] = 'Diese Nachricht verschlüsseln';
$labels['signmsg'] = 'Diese Nachricht digital unterschreiben';

$labels['enterkeypasstitle'] = 'Passwort für Schlüssel eingeben';
$labels['enterkeypass'] = 'Es wird ein Passwort benötigt, um den geheimen Schlüssel ($keyid) von Benutzer $user zu entsperren.';
$labels['arialabelkeyexportoptions'] = 'Optionen für Schlüsselexport';
$labels['attachpubkeymsg'] = 'Meinen öffentlichen Schlüssel anhängen';

$messages = array();
$messages['sigvalid'] = 'Geprüfte Unterschrift von $sender.';
$messages['sigvalidpartial'] = 'Geprüfte Unterschrift von $sender, aber ein Teil der Nachricht war nicht unterzeichnet';
$messages['siginvalid'] = 'Ungültige Unterschrift von $sender.';
$messages['sigunverified'] = 'Ungeprüfte Unterschrift. Zertifikat nicht geprüft. ID des Zertifikats: $keyid.';
$messages['signokey'] = 'Ungeprüfte Unterschrift. Öffentlicher Schlüssel nicht gefunden. ID des Schlüssels: $keyid.';
$messages['sigerror'] = 'Ungeprüfte Unterschrift. Interner Fehler.';
$messages['decryptok'] = 'Nachricht entschlüsselt.';
$messages['decrypterror'] = 'Entschlüsselung fehlgeschlagen.';
$messages['decryptnokey'] = 'Entschlüsselung fehlgeschlagen. Privaten Schlüssel nicht gefunden. ID des Schlüssels: $keyid.';
$messages['decryptbadpass'] = 'Entschlüsselung fehlgeschlagen. Falsches Passwort.';
$messages['decryptnopass'] = 'Entschlüsselung fehlgeschlagen. Passwort des Schlüssels benötigt.';
$messages['decryptpartial'] = 'Nachricht entschlüsselt, aber ein Teil der Nachricht war nicht verschlüsselt.';
$messages['signerror'] = 'Unterzeichnung fehlgeschlagen.';
$messages['signnokey'] = 'Unterzeichnung fehlgeschlagen. Privaten Schlüssel nicht gefunden.';
$messages['signbadpass'] = 'Unterzeichnung fehlgeschlagen. Falsches Passwort.';
$messages['signnopass'] = 'Unterzeichnung fehlgeschlagen. Passwort des Schlüssels benötigt.';
$messages['encrypterror'] = 'Verschlüsselung fehlgeschlagen.';
$messages['encryptnokey'] = 'Verschlüsselung fehlgeschlagen. Öffentlichen Schlüssel nicht gefunden for $email.';
$messages['nokeysfound'] = 'Keine Schlüssel gefunden';
$messages['keyopenerror'] = 'Schlüsselinformationen nicht erhalten! Interner Fehler.';
$messages['keylisterror'] = 'Schlüssel können nicht angezeigt werden! Interner Fehler.';
$messages['keysimportfailed'] = 'Schlüssel können nicht importiert werden! Interner Fehler.';
$messages['keysimportsuccess'] = 'Schlüssel erfolgreich importiert. Importiert: $new, unverändert: $old.';
$messages['keyremoving'] = 'Entferne Schlüssel...';
$messages['keyremoveconfirm'] = 'Wollen Sie die markierten Schlüssel wirklich löschen?';
$messages['keyremovesuccess'] = 'Schlüssel erfolgreich gelöscht';
$messages['keyremoveerror'] = 'Ausgewählte Schlüssel können nicht gelöscht werden.';
$messages['keyimporttext'] = 'Sie können private und öffentliche Schlüssel im ASCII-Armor-Format importieren oder Unterschriften widerrufen.';
$messages['formerror'] = 'Bitte füllen Sie das Formular aus. Alle Felder werden benötigt!';
$messages['passwordsdiffer'] = 'Passwörter stimmen nicht überein!';
$messages['nonameident'] = 'Identität muss einen Benutzernamen definiert haben!';
$messages['keygenerateerror'] = 'Generierung von Schlüsselpaar fehlgeschlagen';
$messages['keygeneratesuccess'] = 'Schlüsselpaar erfolgreich erzeugt und importiert.';
$messages['keygennosupport'] = 'Ihr Webbrowser unterstützt keine Kryptographie. Schlüsselpaar kann nicht erzeugt werden!';

?>
