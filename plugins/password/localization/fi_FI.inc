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

$labels['hestia_err_policy_short'] = 'Salasana liian lyhyt (vähimmäispituus: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia-päätepistettä ei ole määritetty tai se on virheellinen.';
$labels['hestia_err_identity'] = 'Käyttäjätunnusta ei voida jäsentää muotoon local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Verkkotunnuksen %domainin omistajaa ei pystytä määrittämään.';
$labels['hestia_err_unknown'] = 'Tuntematon virhe Hestia API:sta.';
$labels['hestia_err_default'] = 'Hestia palautti virhekoodin %code.';
$labels['hestia_err_1'] = 'Ei riittävästi perusteluja.';
$labels['hestia_err_2'] = 'Virheellinen objekti tai argumentti.';
$labels['hestia_err_3'] = 'Objektia ei ole olemassa (postilaatikkoa tai toimialuetta ei löydy).';
$labels['hestia_err_4'] = 'Kohde on jo olemassa.';
$labels['hestia_err_5'] = 'Kohde on jo keskeytetty.';
$labels['hestia_err_6'] = 'Kohde ei ole jo keskeytetty.';
$labels['hestia_err_7'] = 'Käytössä oleva kohde; ei voi muuttaa.';
$labels['hestia_err_8'] = 'Pakettirajoitukset estävät toiminnan.';
$labels['hestia_err_9'] = 'Väärä tai virheellinen salasana.';
$labels['hestia_err_10'] = 'Hestia on evännyt luvan (API-tunnuksella ei ole oikeuksia tai IP ei ole sallittu).';
$labels['hestia_err_11'] = 'Järjestelmäkäytäntö poistaa ominaisuuden käytöstä.';
$labels['hestia_err_12'] = 'Analysointivirhe.';
$labels['hestia_err_13'] = 'Levyvirhe.';
$labels['hestia_err_14'] = 'Palvelimen kuormitus liian suuri.';
$labels['hestia_err_15'] = 'Yhteysvirhe.';
$labels['hestia_err_16'] = 'FTP-virhe.';
$labels['hestia_err_17'] = 'Tietokantavirhe.';
$labels['hestia_err_18'] = 'RRD-virhe.';
$labels['hestia_err_19'] = 'Päivitysvirhe.';
$labels['hestia_err_20'] = 'Uudelleenkäynnistysvirhe.';
?>
