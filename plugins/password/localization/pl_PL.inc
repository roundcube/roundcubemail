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

$labels['changepasswd']  = 'Zmiana hasła';
$labels['curpasswd']  = 'Aktualne hasło:';
$labels['newpasswd']  = 'Nowe hasło:';
$labels['confpasswd']  = 'Potwierdź hasło:';
$messages['nopassword'] = 'Wprowadź nowe hasło.';
$messages['nocurpassword'] = 'Wprowadź aktualne hasło.';
$messages['passwordincorrect'] = 'Błędne aktualne hasło, spróbuj ponownie.';
$messages['passwordinconsistency'] = 'Hasła nie pasują, spróbuj ponownie.';
$messages['crypterror'] = 'Nie udało się zapisać nowego hasła. Brak funkcji kodującej.';
$messages['connecterror'] = 'Nie udało się zapisać nowego hasła. Błąd połączenia.';
$messages['internalerror'] = 'Nie udało się zapisać nowego hasła.';
$messages['passwordshort'] = 'Hasło musi posiadać co najmniej $length znaków.';
$messages['passwordweak'] = 'Hasło musi zawierać co najmniej jedną cyfrę i znak interpunkcyjny.';
$messages['passwordtooweak'] = 'Hasło jest zbyt słabe.';
$messages['passwordnoseq'] = 'Hasło nie powinno być sekwencją typu 123456 albo QWERTY.';
$messages['passwordnocommon'] = 'Hasło nie może być zwykłym słowem lub nazwą.';
$messages['passwordforbidden'] = 'Hasło zawiera niedozwolone znaki.';
$messages['firstloginchange'] = 'To jest twoje pierwsze logowanie. Proszę zmień hasło.';
$messages['disablednotice'] = 'System jest w trakcie konserwacji i zmiana hasła w tym momencie nie jest możliwa. Wszystko powinno wrócić do normy w niedługim czasie. Przepraszamy za wszelkie niedogodności';
$messages['passwinhistory'] = 'To hasło było już wcześniej użyte';
$messages['samepasswd'] = 'Nowe hasło musi być różne od poprzednich.';
$messages['passwdexpirewarning'] = 'Uwaga! Twoje hasło wkrótce wygaśnie, zmień je przed $expirationdatetime.';
$messages['passwdexpired'] = 'Twoje hasło wygasło, musisz je zmienić.';
$messages['passwdconstraintviolation'] = 'Naruszenie ograniczeń hasła. Prawdopodobnie jest zbyt słabe.';
$messages['pwned_mustnotbedisclosed'] = 'Hasło nie może być <a href="$href" target="_blank">powszechnie znane</a>.';
$messages['pwned_isdisclosed'] = 'To hasło jest powszechnie znane.';
$messages['pwned_fetcherror'] = 'Nie udało się zweryfikować siły hasła.';

$labels['hestia_err_policy_short'] = 'Hasło jest zbyt krótkie (minimalna długość: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Punkt końcowy Hestia nie jest skonfigurowany lub jest nieprawidłowy.';
$labels['hestia_err_identity'] = 'Nie można przeanalizować nazwy użytkownika w local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Nie można określić właściciela domeny dla %domain.';
$labels['hestia_err_unknown'] = 'Nieznany błąd interfejsu API Hestia.';
$labels['hestia_err_default'] = 'Hestia zwróciła kod błędu %code.';
$labels['hestia_err_1'] = 'Nie podano wystarczających argumentów.';
$labels['hestia_err_2'] = 'Nieprawidłowy obiekt lub argument.';
$labels['hestia_err_3'] = 'Obiekt nie istnieje (nie znaleziono skrzynki pocztowej lub domeny).';
$labels['hestia_err_4'] = 'Obiekt już istnieje.';
$labels['hestia_err_5'] = 'Obiekt jest już zawieszony.';
$labels['hestia_err_6'] = 'Obiekt nie jest już zawieszony.';
$labels['hestia_err_7'] = 'Obiekt w użyciu; nie można modyfikować.';
$labels['hestia_err_8'] = 'Działanie zablokowane przez limity pakietów.';
$labels['hestia_err_9'] = 'Błędne lub nieprawidłowe hasło.';
$labels['hestia_err_10'] = 'Odmowa pozwolenia przez Hestia (tożsamość API nie ma uprawnień lub adres IP nie jest dozwolony).';
$labels['hestia_err_11'] = 'Funkcja wyłączona przez zasady systemowe.';
$labels['hestia_err_12'] = 'Błąd parsowania.';
$labels['hestia_err_13'] = 'Błąd dysku.';
$labels['hestia_err_14'] = 'Zbyt duże obciążenie serwera.';
$labels['hestia_err_15'] = 'Błąd połączenia.';
$labels['hestia_err_16'] = 'Błąd FTP.';
$labels['hestia_err_17'] = 'Błąd bazy danych.';
$labels['hestia_err_18'] = 'Błąd RRD.';
$labels['hestia_err_19'] = 'Błąd aktualizacji.';
$labels['hestia_err_20'] = 'Błąd ponownego uruchomienia.';
?>
