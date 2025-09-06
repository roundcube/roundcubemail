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

$labels['hestia_err_policy_short'] = 'Parolă prea scurtă (lungime minimă: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Punctul final Hestia nu este configurat sau este invalid.';
$labels['hestia_err_identity'] = 'Nu se poate analiza numele de utilizator în local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Imposibilitatea de a determina proprietarul domeniului pentru %domain.';
$labels['hestia_err_unknown'] = 'Eroare necunoscută de la Hestia API.';
$labels['hestia_err_default'] = 'Hestia a returnat codul de eroare %code.';
$labels['hestia_err_1'] = 'Nu au fost furnizate suficiente argumente.';
$labels['hestia_err_2'] = 'Obiect sau argument invalid.';
$labels['hestia_err_3'] = 'Obiectul nu există (căsuța poștală sau domeniul nu sunt găsite).';
$labels['hestia_err_4'] = 'Obiectul există deja.';
$labels['hestia_err_5'] = 'Obiectul este deja suspendat.';
$labels['hestia_err_6'] = 'Obiectul este deja nesuspendat.';
$labels['hestia_err_7'] = 'Obiect în uz; nu poate fi modificat.';
$labels['hestia_err_8'] = 'Funcționare blocată de limitele pachetului.';
$labels['hestia_err_9'] = 'Parolă greșită sau invalidă.';
$labels['hestia_err_10'] = 'Permisiune refuzată de Hestia (identitatea API nu are drepturi sau IP-ul nu este permis).';
$labels['hestia_err_11'] = 'Funcție dezactivată de politica sistemului.';
$labels['hestia_err_12'] = 'Eroare de parsare.';
$labels['hestia_err_13'] = 'Eroare de disc.';
$labels['hestia_err_14'] = 'Sarcina serverului este prea mare.';
$labels['hestia_err_15'] = 'Eroare de conectare.';
$labels['hestia_err_16'] = 'Eroare FTP.';
$labels['hestia_err_17'] = 'Eroare în baza de date.';
$labels['hestia_err_18'] = 'Eroare RRD.';
$labels['hestia_err_19'] = 'Eroare de actualizare.';
$labels['hestia_err_20'] = 'Eroare de repornire.';
?>
