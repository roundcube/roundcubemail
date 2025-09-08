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

$labels['changepasswd']  = 'Muuda parooli';
$labels['curpasswd']  = 'Vana parool:';
$labels['newpasswd']  = 'Uus parool:';
$labels['confpasswd']  = 'Uus parool uuesti:';
$messages['nopassword'] = 'Palun sisesta uus parool.';
$messages['nocurpassword'] = 'Palun sisesta vana parool.';
$messages['passwordincorrect'] = 'Vana parool on vale.';
$messages['passwordinconsistency'] = 'Paroolid ei kattu, palun proovi uuesti.';
$messages['crypterror'] = 'Serveris ei ole parooli krüpteerimiseks vajalikku funktsiooni.';
$messages['connecterror'] = 'Parooli salvestamine nurjus. Ühenduse tõrge.';
$messages['internalerror'] = 'Uue parooli andmebaasi salvestamine nurjus.';
$messages['passwordshort'] = 'Parool peab olema vähemalt $length märki pikk.';
$messages['passwordweak'] = 'Parool peab sisaldama vähemalt üht numbrit ja märki.';
$messages['passwordtooweak'] = 'Parool on liiga nõrk.';
$messages['passwordforbidden'] = 'Parool sisaldab keelatud märki.';
$messages['firstloginchange'] = 'See on sinu esimene sisselogimine, palun muuda oma parooli.';
$messages['pwned_isdisclosed'] = 'Parool on liiga tavaline.';
$messages['pwned_fetcherror'] = 'Parooli tugevuse kontrollimine ebaõnnestus.';

$labels['hestia_err_policy_short'] = 'Parool liiga lühike (minimaalne pikkus: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia lõpp-punkt ei ole konfigureeritud või on kehtetu.';
$labels['hestia_err_identity'] = 'Ei saa analüüsida kasutajanime local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Ei ole võimalik määrata domeeni omanikku %domeeni jaoks.';
$labels['hestia_err_unknown'] = 'Hestia API tundmatu viga.';
$labels['hestia_err_default'] = 'Hestia tagastas veakoodi %code.';
$labels['hestia_err_1'] = 'Ei ole piisavalt argumente esitatud.';
$labels['hestia_err_2'] = 'Vale objekt või argument.';
$labels['hestia_err_3'] = 'Objekti ei ole olemas (postkasti või domeeni ei leitud).';
$labels['hestia_err_4'] = 'Objekt on juba olemas.';
$labels['hestia_err_5'] = 'Objekt on juba peatatud.';
$labels['hestia_err_6'] = 'Objekt on juba peatamata.';
$labels['hestia_err_7'] = 'Kasutusel olev objekt; ei saa muuta.';
$labels['hestia_err_8'] = 'Operatsioon on blokeeritud paketipiirangute tõttu.';
$labels['hestia_err_9'] = 'Vale või kehtetu parool.';
$labels['hestia_err_10'] = 'Hestia keeldub loa andmisest (API identiteedil puuduvad õigused või IP ei ole lubatud).';
$labels['hestia_err_11'] = 'Funktsioon on süsteemipoliitikaga välja lülitatud.';
$labels['hestia_err_12'] = 'Parsimisviga.';
$labels['hestia_err_13'] = 'Ketta viga.';
$labels['hestia_err_14'] = 'Serveri koormus liiga suur.';
$labels['hestia_err_15'] = 'Ühendusviga.';
$labels['hestia_err_16'] = 'FTP-viga.';
$labels['hestia_err_17'] = 'Andmebaasi viga.';
$labels['hestia_err_18'] = 'RRD viga.';
$labels['hestia_err_19'] = 'Uuendusviga.';
$labels['hestia_err_20'] = 'Taaskäivitusvea.';
?>
