<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/
$labels['changepasswd']  = 'Schimbă parola';
$labels['curpasswd']  = 'Parola curentă:';
$labels['newpasswd']  = 'Parola nouă:';
$labels['confpasswd']  = 'Confirmare parola nouă:';
$messages['nopassword'] = 'Te rog să introduci noua parolă.';
$messages['nocurpassword'] = 'Te rog să introduci parola curentă';
$messages['passwordincorrect'] = 'Parola curentă este incorectă.';
$messages['passwordinconsistency'] = 'Parolele nu se potrivesc, te rog să mai încerci';
$messages['crypterror'] = 'Nu am reușit să salvez noua parolă. Funcția de criptare lipsește.';
$messages['connecterror'] = 'Nu am reușit să salvez noua parolă. Eroare connexiune.';
$messages['internalerror'] = 'Nu am reușit să salvez noua parolă.';
$messages['passwordshort'] = 'Parola trebuie să aibă minim $length caractere.';
$messages['passwordweak'] = 'Parola trebuie să conțina cel puțin un număr si un semn de punctuație.';
$messages['passwordforbidden'] = 'Parola conține caractere nepermise.';
$messages['firstloginchange'] = 'Aceasta este prima autentificare. Te rugăm să schimbi parola.';
$messages['disablednotice'] = 'Sistemul este în prezent în curs de întreținere, iar schimbarea parolei nu este posibilă în acest moment. Totul ar trebui să revină la normal în curând. Ne cerem scuze pentru orice inconveniență.';
$messages['passwinhistory'] = 'Această parolă a fost folosită anterior.';
$messages['samepasswd'] = 'Parola nouă trebuie să fie diferită față de cea veche.';
$messages['passwdexpirewarning'] = 'Atenție! Parola dvs. va expira în curând, modificați-o înainte de $expirationdatetime.';
$messages['passwdexpired'] = 'Parola dvs. a expirat, trebuie să o schimbați acum!';
$messages['passwdconstraintviolation'] = 'Încălcare constrângere parolă. Parola probabil prea slabă.';
