<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'Passwort ändern';
$labels['curpasswd']  = 'Aktuelles Passwort';
$labels['newpasswd']  = 'Neues Passwort';
$labels['confpasswd']  = 'Passwort Wiederholung';
$messages['nopassword'] = 'Bitte geben Sie ein neues Passwort ein';
$messages['nocurpassword'] = 'Bitte geben Sie Ihr aktuelles Passwort an';
$messages['passwordincorrect'] = 'Das aktuelle Passwort ist nicht korrekt';
$messages['passwordinconsistency'] = 'Das neue Passwort und dessen Wiederholung stimmen nicht überein';
$messages['crypterror'] = 'Neues Passwort nicht gespeichert: Verschlüsselungsfunktion fehlt';
$messages['connecterror'] = 'Neues Passwort nicht gespeichert: Verbindungsfehler';
$messages['internalerror'] = 'Neues Passwort nicht gespeichert';
$messages['passwordshort'] = 'Passwort muss mindestens $length Zeichen lang sein.';
$messages['passwordweak'] = 'Passwort muss mindestens eine Zahl und ein Sonderzeichen enthalten.';
$messages['passwordtooweak'] = 'Das Passwort ist zu schwach.';
$messages['passwordnoseq'] = 'Das Passwort sollte keine Sequenz wie 123456 oder QWERTZ sein.';
$messages['passwordnocommon'] = 'Passwort sollte kein allgemeines Wort oder Name sein.';
$messages['passwordforbidden'] = 'Passwort enthält unzulässige Zeichen.';
$messages['firstloginchange'] = 'Dies ist Ihre erste Anmeldung. Bitte ändern Sie Ihr Passwort.';
$messages['disablednotice'] = 'Das System befindet sich derzeit im Wartungszustand und eine Passwortänderung ist im Moment nicht möglich. Der normale Betrieb sollte bald wieder hergestellt sein. Wir bitten um Entschuldigung für die Unannehmlichkeiten.';
$messages['passwinhistory'] = 'Dieses Passwort wurde bereits einmal verwendet.';
$messages['samepasswd'] = 'Das neue Passwort muss sich von dem Alten unterscheiden.';
$messages['passwdexpirewarning'] = 'Achtung! Ihr Passwort läuft am $expirationdatetime ab. Ändern Sie es rechtzeitig.';
$messages['passwdexpired'] = 'Ihr Passwort ist abgelaufen, bitte ändern Sie es jetzt!';
$messages['passwdconstraintviolation'] = 'Passwortrichtlinien nicht erfüllt. Das Passwort ist wahrscheinlich zu schwach.';
$messages['pwned_mustnotbedisclosed'] = 'Das Passwort darf nicht <a href="$href" target="_blank">allgemein bekannt sein</a>.';
$messages['pwned_isdisclosed'] = 'Dieses Passwort ist allgemein bekannt.';
$messages['pwned_fetcherror'] = 'Die Überprüfung der Passwortstärke ist fehlgeschlagen.';

$labels['hestia_err_policy_short'] = 'Passwort zu kurz (Mindestlänge: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Der Hestia-Endpunkt ist nicht konfiguriert oder ungültig.';
$labels['hestia_err_identity'] = 'Kann den Benutzernamen nicht in local@domain%hint parsen.';
$labels['hestia_err_owner_unknown'] = 'Der Eigentümer der Domäne %domain kann nicht ermittelt werden.';
$labels['hestia_err_unknown'] = 'Unbekannter Fehler von Hestia API.';
$labels['hestia_err_default'] = 'Hestia hat den Fehlercode %code zurückgegeben.';
$labels['hestia_err_1'] = 'Es wurden nicht genügend Argumente geliefert.';
$labels['hestia_err_2'] = 'Ungültiges Objekt oder Argument.';
$labels['hestia_err_3'] = 'Das Objekt existiert nicht (Postfach oder Domäne nicht gefunden).';
$labels['hestia_err_4'] = 'Das Objekt existiert bereits.';
$labels['hestia_err_5'] = 'Das Objekt ist bereits ausgesetzt.';
$labels['hestia_err_6'] = 'Das Objekt ist bereits unsuspendiert.';
$labels['hestia_err_7'] = 'Objekt in Gebrauch; kann nicht geändert werden.';
$labels['hestia_err_8'] = 'Der Betrieb ist durch Paketgrenzen blockiert.';
$labels['hestia_err_9'] = 'Falsches oder ungültiges Passwort.';
$labels['hestia_err_10'] = 'Erlaubnis von Hestia verweigert (API-Identität hat keine Rechte oder IP nicht erlaubt).';
$labels['hestia_err_11'] = 'Funktion durch Systemrichtlinie deaktiviert.';
$labels['hestia_err_12'] = 'Parsing-Fehler.';
$labels['hestia_err_13'] = 'Festplattenfehler.';
$labels['hestia_err_14'] = 'Die Serverlast ist zu hoch.';
$labels['hestia_err_15'] = 'Verbindungsfehler.';
$labels['hestia_err_16'] = 'FTP-Fehler.';
$labels['hestia_err_17'] = 'Datenbank-Fehler.';
$labels['hestia_err_18'] = 'RRD-Fehler.';
$labels['hestia_err_19'] = 'Aktualisierungsfehler.';
$labels['hestia_err_20'] = 'Fehler beim Neustart.';
?>
