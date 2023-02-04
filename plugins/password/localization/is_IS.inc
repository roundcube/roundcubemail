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

$labels['changepasswd']  = 'Breyta lykilorði';
$labels['curpasswd']  = 'Núverandi lykilorð:';
$labels['newpasswd']  = 'Nýtt lykilorð:';
$labels['confpasswd']  = 'Staðfesta nýtt lykilorð:';
$messages['nopassword'] = 'Settu inn nýtt lykilorð.';
$messages['nocurpassword'] = 'Settu inn núverandi lykilorð.';
$messages['passwordincorrect'] = 'Núverandi lykilorð er ekki rétt.';
$messages['passwordinconsistency'] = 'Lykilorðin samsvara ekki, reyndu aftur.';
$messages['crypterror'] = 'Gat ekki vistað nýtt lykilorð. Dulritunareiginleika vantar.';
$messages['connecterror'] = 'Gat ekki vistað nýtt lykilorð. Villa í tengingu.';
$messages['internalerror'] = 'Gat ekki vistað nýtt lykilorð.';
$messages['passwordshort'] = 'Lykilorð þarf að vera að minnsta kosti $length stafa langt.';
$messages['passwordweak'] = 'Lykilorð þarf að innihalda a.m.k. einn tölustaf og eitt greinamerki.';
$messages['passwordtooweak'] = 'Lykilorðið er of veikt.';
$messages['passwordnoseq'] = 'Lykilorð ætti ekki að vera runa á borð við 123456 eða QWERTY.';
$messages['passwordnocommon'] = 'Lykilorð ætti ekki að vera algengt orð eða nafn.';
$messages['passwordforbidden'] = 'Lykilorð inniheldur óleyfilega stafi.';
$messages['firstloginchange'] = 'Þetta er í fyrsta skipti sem þú skráir þig inn. Endilega breyttu lykilorðinu þínu.';
$messages['disablednotice'] = 'Kerfið er í viðhaldsfasa og því er ekki hægt að breyta lykilorðum sem stendur. Allt ætti að vera komið í samt lag innan stundar. Við biðjumst afsökunar á þeim óþægindum sem af þessu geta hlotist.';
$messages['passwinhistory'] = 'Þetta lykilorð hefur áður verið notað.';
$messages['samepasswd'] = 'Nýja lykilorðið verður að vera frábrugðið hinu gamla.';
$messages['passwdexpirewarning'] = 'Aðvörun! Lykilorðið þitt rennur út innan skamms, breyttu því fyrir $expirationdatetime.';
$messages['passwdexpired'] = 'Lykilorðið þitt er runnið út, þú verður að breyta því núna!';
$messages['passwdconstraintviolation'] = 'Lykilorðið rakst á skilyrði. Líklega er það of veikt.';
$messages['pwned_mustnotbedisclosed'] = 'Lykilorð má ekki vera <a href="$href" target="_blank">almennt þekkt</a>.';
$messages['pwned_isdisclosed'] = 'Þetta lykilorð er almennt þekkt.';
$messages['pwned_fetcherror'] = 'Mistókst að sannreyna styrk lykilorðsins.';
