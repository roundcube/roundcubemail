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

$labels['changepasswd']  = 'Vaihda salasana';
$labels['curpasswd']  = 'Nykyinen salasana:';
$labels['newpasswd']  = 'Uusi salasana:';
$labels['confpasswd']  = 'Vahvista uusi salasana:';
$messages['nopassword'] = 'Syötä uusi salasana.';
$messages['nocurpassword'] = 'Syötä nykyinen salasana.';
$messages['passwordincorrect'] = 'Nykyinen salasana on väärin.';
$messages['passwordinconsistency'] = 'Salasanat eivät täsmää, yritä uudelleen.';
$messages['crypterror'] = 'Uuden salasanan tallennus epäonnistui. Kryptausfunktio puuttuu.';
$messages['connecterror'] = 'Uuden salasanan tallennus epäonnistui. Yhteysongelma.';
$messages['internalerror'] = 'Uuden salasanan tallennus epäonnistui.';
$messages['passwordshort'] = 'Salasanassa täytyy olla vähintään $length merkkiä.';
$messages['passwordweak'] = 'Salasanan täytyy sisältää vähintään yksi numero ja yksi välimerkki.';
$messages['passwordtooweak'] = 'Salasana on liian heikko.';
$messages['passwordnoseq'] = 'Salasana ei saa olla yleinen ketju kuten 123456 tai QWERTY.';
$messages['passwordnocommon'] = 'Salasana ei saa olla yleinen sana tai nimi.';
$messages['passwordforbidden'] = 'Salasana sisältää virheellisiä merkkejä.';
$messages['firstloginchange'] = 'Tämä on ensimmäinen kirjautumiskertasi. Vaihda salasanasi.';
$messages['disablednotice'] = 'Järjestelmä on parhaillaan huoltotilassa, joten salasanaa ei voi vaihtaa juuri nyt. Pahoittelemme aiheutunutta vaivaa. Kaiken pitäisi olla pian käytettävissä normaaliin tapaan.';
$messages['passwinhistory'] = 'Tämä salasana on jo ollut käytössä aikaisemmin.';
$messages['samepasswd'] = 'Uuden salasanan pitää olla eri kuin vanha salasana.';
$messages['passwdexpirewarning'] = 'Varoitus! Salasanasi vanhenee pian, vaihda se ennen $expirationdatetime.';
$messages['passwdexpired'] = 'Salasanasi on vanhentunut ja se pitää vaihtaa nyt!';
$messages['passwdconstraintviolation'] = 'Salasanan rajoitevirhe. Salasana on todennäköisesti liian heikko.';
$messages['pwned_mustnotbedisclosed'] = 'Salasana ei saa olla <a href="$href" target="_blank">yleisesti tunnettu</a>.';
$messages['pwned_isdisclosed'] = 'Salasana on yleisesti tunnettu.';
$messages['pwned_fetcherror'] = 'Salasanan vahvuutta ei voitu varmistaa.';
