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
$labels['curpasswd']  = 'Aktuelles Passwort:';
$labels['newpasswd']  = 'Neues Passwort:';
$labels['confpasswd']  = 'Neues Passwort bestätigen:';
$messages['nopassword'] = 'Bitte geben Sie ein neues Passwort ein.';
$messages['nocurpassword'] = 'Bitte geben Sie Ihr aktuelles Passwort ein.';
$messages['passwordincorrect'] = 'Aktuelles Passwort ist falsch.';
$messages['passwordinconsistency'] = 'Passwörter stimmen nicht überein, bitte versuchen Sie es erneut.';
$messages['crypterror'] = 'Neues Passwort konnte nicht gespeichert werden. Verschlüsselungsfunktion fehlt.';
$messages['connecterror'] = 'Neues Passwort konnte nicht gespeichert werden. Verbindungsfehler.';
$messages['internalerror'] = 'Neues Passwort konnte nicht gespeichert werden.';
$messages['passwordshort'] = 'Passwort muss mindestens $length Zeichen lang sein.';
$messages['passwordweak'] = 'Passwort muss mindestens eine Zahl und ein Sonderzeichen enthalten.';
$messages['passwordtooweak'] = 'Das Passwort ist zu schwach.';
$messages['passwordnoseq'] = 'Das Passwort sollte keine Sequenz wie 123456 oder QWERTY sein.';
$messages['passwordnocommon'] = 'Passwort sollte kein allgemeines Wort oder Name sein.';
$messages['passwordforbidden'] = 'Passwort enthält unzulässige Zeichen.';
$messages['firstloginchange'] = 'Das ist Ihre erste Anmeldung. Bitte ändern Sie Ihr Passwort.';
$messages['disablednotice'] = 'Das System befindet sich derzeit im Wartungszustand und eine Passwortänderung ist im Moment nicht möglich. Alles sollte bald wieder normal sein. Wir bitten um Entschuldigung für die Unannehmlichkeiten.';
$messages['passwinhistory'] = 'Dieses Passwort wurde bereits zu einem früheren Zeitpunkt verwendet.';
$messages['samepasswd'] = 'Das neue Passwort muss sich von dem Alten unterscheiden.';
$messages['passwdexpirewarning'] = 'Achtung! Ihr Passwort läuft am $expirationdatetime ab. Ändern Sie es rechtzeitig.';
$messages['passwdexpired'] = 'Ihr Passwort ist abgelaufen, ändern Sie es jetzt!';
$messages['passwdconstraintviolation'] = 'Passwortbeschränkungsverletzung. Passwort wahrscheinlich zu schwach.';
$messages['pwned_isdisclosed'] = 'Dieses Passwort ist allgemein bekannt.';
$messages['pwned_fetcherror'] = 'Die Überprüfung der Passwortstärke ist fehlgeschlagen.';
