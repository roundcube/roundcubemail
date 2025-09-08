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
$messages['pwned_isdisclosed'] = 'Šis slaptažodis yra plačiai žinomas.';
$messages['pwned_fetcherror'] = 'Nepavyko patikrinti slaptažodžio stiprumo.';

$labels['hestia_err_policy_short'] = 'Per trumpas slaptažodis (mažiausias ilgis: %min).';
$labels['hestia_err_endpoint_invalid'] = '"Hestia" galinis taškas nesukonfigūruotas arba negalioja.';
$labels['hestia_err_identity'] = 'Negalima analizuoti vartotojo vardo į local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Nepavyksta nustatyti domeno %domeno savininko.';
$labels['hestia_err_unknown'] = 'Nežinoma "Hestia API" klaida.';
$labels['hestia_err_default'] = '"Hestia" grąžino klaidos kodą %kodas.';
$labels['hestia_err_1'] = 'Pateikta nepakankamai argumentų.';
$labels['hestia_err_2'] = 'Netinkamas objektas arba argumentas.';
$labels['hestia_err_3'] = 'Objektas neegzistuoja (pašto dėžutė arba domenas nerastas).';
$labels['hestia_err_4'] = 'Objektas jau egzistuoja.';
$labels['hestia_err_5'] = 'Objektas jau sustabdytas.';
$labels['hestia_err_6'] = 'Objektas jau nėra sustabdytas.';
$labels['hestia_err_7'] = 'Naudojamas objektas; negalima keisti.';
$labels['hestia_err_8'] = 'Operacija blokuojama dėl pakuotės apribojimų.';
$labels['hestia_err_9'] = 'Neteisingas arba negaliojantis slaptažodis.';
$labels['hestia_err_10'] = '"Hestia" atsisakė suteikti leidimą (API tapatybė neturi teisių arba IP neleidžiamas).';
$labels['hestia_err_11'] = 'Funkcija išjungta pagal sistemos politiką.';
$labels['hestia_err_12'] = 'Parsavimo klaida.';
$labels['hestia_err_13'] = 'Disko klaida.';
$labels['hestia_err_14'] = 'Per didelė serverio apkrova.';
$labels['hestia_err_15'] = 'Ryšio klaida.';
$labels['hestia_err_16'] = 'FTP klaida.';
$labels['hestia_err_17'] = 'Duomenų bazės klaida.';
$labels['hestia_err_18'] = 'RRD klaida.';
$labels['hestia_err_19'] = 'Atnaujinimo klaida.';
$labels['hestia_err_20'] = 'Paleidimo iš naujo klaida.';
?>
