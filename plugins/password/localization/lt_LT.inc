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

$labels['changepasswd']  = 'Keisti slaptažodį';
$labels['curpasswd']  = 'Dabartinis slaptažodis:';
$labels['newpasswd']  = 'Naujasis slaptažodis:';
$labels['confpasswd']  = 'Pakartokite naująjį slaptažodį:';
$messages['nopassword'] = 'Prašom įvesti naująjį slaptažodį.';
$messages['nocurpassword'] = 'Prašom įvesti dabartinį slaptažodį.';
$messages['passwordincorrect'] = 'Dabartinis slaptažodis neteisingas.';
$messages['passwordinconsistency'] = 'Slaptažodžiai nesutapo. Bandykite dar kartą.';
$messages['crypterror'] = 'Nepavyko įrašyti naujojo slaptažodžio. Trūksta šifravimo funkcijos.';
$messages['connecterror'] = 'Nepavyko įrašyti naujojo slaptažodžio. Ryšio klaida.';
$messages['internalerror'] = 'Nepavyko įrašyti naujojo slaptažodžio.';
$messages['passwordshort'] = 'Slaptažodis turi būti sudarytas bent iš $length simbolių.';
$messages['passwordweak'] = 'Slaptažodyje turi būti bent vienas skaitmuo ir vienas skyrybos ženklas.';
$messages['passwordtooweak'] = 'Slaptažodis pernelyg silpnas.';
$messages['passwordnoseq'] = 'Slaptažodis neturėtų būti seka, kaip, pavyzdžiui, 123456 ar QWERTY.';
$messages['passwordnocommon'] = 'Slaptažodis neturėtų būti įprastas žodis ar pavadinimas.';
$messages['passwordforbidden'] = 'Slaptažodyje rasta neleistinų simbolių.';
$messages['firstloginchange'] = 'Tai yra pirmasis jūsų prisijungimas. Prašau, pasikeiskite savo slaptažodį.';
$messages['disablednotice'] = 'Sistema šiuo metu tvarkoma ir slaptažodžio pakeitimas negalimas. Netrukus viskas turėtų grįžti į įprastą būseną. Atsiprašome dėl nepatogumų.';
$messages['passwinhistory'] = 'Šis slaptažodis jau buvo naudotas anksčiau.';
$messages['samepasswd'] = 'Naujas slaptažodis turi būti kitoks nei senasis.';
$messages['passwdexpirewarning'] = 'Įspėjimas! Jūsų slaptažodis greitai nustos galioti, pakeiskite jį iki $expirationdatetime.';
$messages['passwdexpired'] = 'Jūsų slaptažodis nebegalioja, jūs turite pakeisti jį dabar!';
$messages['passwdconstraintviolation'] = 'Slaptažodžio apribojimų pažeidimas. Tikriausiai, slaptažodis pernelyg silpnas.';
$messages['pwned_mustnotbedisclosed'] = 'Slaptažodis neturi būti <a href="$href" target="_blank">plačiai žinomas</a>.';
$messages['pwned_isdisclosed'] = 'Šis slaptažodis yra plačiai žinomas.';
$messages['pwned_fetcherror'] = 'Nepavyko patikrinti slaptažodžio stiprumo.';
