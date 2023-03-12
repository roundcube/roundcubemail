<?php

/**
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/

$labels['encryption'] = 'Salaus';
$labels['enigmacerts'] = 'S/MIME-varmenteet';
$labels['enigmakeys'] = 'PGP-avaimet';
$labels['keysfromto'] = 'Avaimet $from-$to/$count';
$labels['keyname'] = 'Nimi';
$labels['keyid'] = 'Avaintunniste';
$labels['keyuserid'] = 'Käyttäjätunniste';
$labels['keytype'] = 'Avaimen tyyppi';
$labels['fingerprint'] = 'Sormenjälki';
$labels['subkeys'] = 'Aliavaimet';
$labels['keyprops'] = 'Avaimen asetukset';
$labels['basicinfo'] = 'Perustiedot';
$labels['userids'] = 'Lisäkäyttäjät';
$labels['typepublickey'] = 'julkinen avain';
$labels['typekeypair'] = 'avainpari';
$labels['keyattfound'] = 'Tämä viesti sisältää yhden tai useamman PGP-avaimen.';
$labels['keyattimport'] = 'Tuo avaimia';
$labels['typesign'] = 'Allekirjoitus';
$labels['typeencrypt'] = 'Salaus';
$labels['typecert'] = 'Todistus';
$labels['typeauth'] = 'Tunnistautuminen';
$labels['subkeyid'] = 'Tunniste';
$labels['subkeyalgo'] = 'Algoritmi';
$labels['subkeycreated'] = 'Luotu';
$labels['subkeyexpires'] = 'Vanhenee';
$labels['subkeyusage'] = 'Käyttö';
$labels['expiresnever'] = 'ei koskaan';
$labels['unknown'] = 'tuntematon';
$labels['uservalid'] = 'Kelvollinen';
$labels['userid'] = 'Tunniste';
$labels['valid'] = 'kelvollinen';
$labels['supportencryption'] = 'Käytä viestien salausta ja allekirjoitusta';
$labels['supportsignatures'] = 'Käytä viestien allekirjoitusten vahvistusta';
$labels['supportdecryption'] = 'Ota käyttöön viestien salauksen purkaminen';
$labels['signdefault'] = 'Allekirjoita kaikki viestit oletuksena';
$labels['encryptdefault'] = 'Salaa kaikki viestit oletuksena';
$labels['attachpubkeydefault'] = 'Liitä julkinen PGP-avaimeni oletuksena';
$labels['passwordtime'] = 'Säilytä yksityisen avaimen salasanat';
$labels['nminutes'] = '$m minuutin ajan';
$labels['wholesession'] = 'koko istunnon ajan';
$labels['createkeys'] = 'Luo uusi avainpari';
$labels['importkeys'] = 'Tuo avaimia';
$labels['exportkeys'] = 'Vie avaimia';
$labels['keyactions'] = 'Avaintoiminnot...';
$labels['keyremove'] = 'Poista';
$labels['keydisable'] = 'Poista käytöstä';
$labels['keyrevoke'] = 'Kumoa';
$labels['keysend'] = 'Lähetä julkinen avain viestissä';
$labels['keychpass'] = 'Vaihda salasana';
$labels['keyadd'] = 'Lisää avain';
$labels['newkeyident'] = 'Identiteetti';
$labels['newkeypass'] = 'Salasana';
$labels['newkeypassconfirm'] = 'Vahvista salasana';
$labels['newkeytype'] = 'Avaimen tyyppi';
$labels['rsa2048'] = 'RSA – 2048 bittiä';
$labels['rsa4096'] = 'RSA – 4096 bittiä';
$labels['ecckeypair'] = 'ECC (elliptinen käyrä)';
$labels['keygenerating'] = 'Luodaan avaimia...';
$labels['encryptionoptions'] = 'Salausvalinnat...';
$labels['encryptmsg'] = 'Salaa tämä viesti';
$labels['signmsg'] = 'Lisää digitaalinen allekirjoitus tähän viestiin';
$labels['sendunencrypted'] = 'Lähetä salaamaton';
$labels['enterkeypasstitle'] = 'Anna tunnuslause';
$labels['enterkeypass'] = 'Käyttäjän $user salaisen avaimen ($keyid) avaamiseen tarvitaan salasana.';
$labels['attachpubkeymsg'] = 'Liitä julkinen avaimeni';
$labels['onlypubkeys'] = 'Vie vain julkiset avaimet';
$labels['withprivkeys'] = 'Vie salaiset avaimet';
$labels['findkey'] = 'Etsi avainpalvelimilta';
$labels['keyimportlabel'] = 'Tuo tiedostosta';
$labels['keyimportsearchlabel'] = 'Etsi avainpalvelimilta';
$labels['managekeys'] = 'Hallinnoi PGP-avaimia';
$labels['arialabelkeyexportoptions'] = 'Avaimien vientivalinnat';
$labels['arialabelkeysearchform'] = 'Avaimien hakulomake';
$labels['arialabelkeyoptions'] = 'Avaimen valinnat';
$messages['sigvalid'] = 'Vahvistettu allekirjoitus lähettäjältä $sender.';
$messages['sigvalidpartial'] = 'Vahvistettu allekirjoitus lähettäjältä $sender, mutta viestisisällön osaa ei allekirjoitettu. ';
$messages['siginvalid'] = 'Virheellinen allekirjoitus lähettäjältä $sender.';
$messages['sigunverified'] = 'Vahvistamaton allekirjoitus. Varmennetta ei ole vahvistettu. Varmenteen tunniste: $keyid.';
$messages['signokey'] = 'Vahvistamaton allekirjoitus. Julkista avainta ei löydy. Avaimen tunniste: $keyid.';
$messages['sigerror'] = 'Vahvistamaton allekirjoitus. Sisäinen virhe.';
$messages['decryptok'] = 'Viestin salaus purettu.';
$messages['decrypterror'] = 'Salauksen purkaminen epäonnistui.';
$messages['decryptnokey'] = 'Salauksen purkaminen epäonnistui. Yksityistä avainta ei löytynyt. Avaimen tunniste: $keyid.';
$messages['decryptnomdc'] = 'Purku ohitettu. Viestin eheyttä ei ole suojattu.';
$messages['decryptbadpass'] = 'Salauksen purkaminen epäonnistui. Väärä salasana.';
$messages['decryptnopass'] = 'Salauksen purkaminen epäonnistui. Avaimen salasana vaaditaan.';
$messages['decryptpartial'] = 'Viestin salaus purettiin, mutta osa viestisisällöstä ei ollut salattu.';
$messages['signerror'] = 'Allekirjoittaminen epäonnistui.';
$messages['signnokey'] = 'Allekirjoittaminen epäonnistui. Yksityistä avainta ei löytynyt.';
$messages['signbadpass'] = 'Allekirjoitus epäonnistui. Virheellinen salasana.';
$messages['signnopass'] = 'Allekirjoitus epäonnistui. Avaimen salasana vaaditaan.';
$messages['encrypterror'] = 'Salaaminen epäonnistui.';
$messages['encryptnokey'] = 'Salaaminen epäonnistui. Osoitteelle $email ei löytynyt julkista avainta.';
$messages['encryptnoprivkey'] = 'Salaus epäonnistui. Yksityistä avainta ei löytynyt.';
$messages['nokeysfound'] = 'Avaimia ei löytynyt';
$messages['keynotfound'] = 'Avainta ei löytynyt!';
$messages['keyopenerror'] = 'Avaintietojen noutaminen ei onnistu! Sisäinen virhe.';
$messages['keylisterror'] = 'Avainten listaaminen ei onnistu! Sisäinen virhe.';
$messages['keysimportfailed'] = 'Avaimen tuominen epäonnistui! Sisäinen virhe.';
$messages['keysimportsuccess'] = 'Avain tai avaimet tuotiin onnistuneesti. Tuotiin: $new, muuttumaton: $old.';
$messages['keyremoving'] = 'Poistetaan avaimia...';
$messages['keyremoveconfirm'] = 'Oletko varma että haluat poistaa valitut avaimet?';
$messages['keyremovesuccess'] = 'Avaimet poistettu onnistuneesti';
$messages['keyremoveerror'] = 'Avaimien poisto epäonnistui.';
$messages['formerror'] = 'Täytä lomake. Kaikki kentät ovat pakollisia!';
$messages['passwordsdiffer'] = 'Salasanat eivät täsmää!';
$messages['keygenerateerror'] = 'Avainparin luominen epäonnistui';
$messages['keygeneratesuccess'] = 'Avainpari luotiin ja tuotiin onnistuneesti.';
$messages['keygennosupport'] = 'Selaimesi ei tue salaustekniikkaa. Avainparin luominen ei onnistu!';
$messages['noidentselected'] = 'Sinun täytyy valita vähintään yksi identiteetti avaimelle!';
$messages['nonameident'] = 'Identiteetillä tulee olla käyttäjänimi määritettynä!';
$labels['newkeysize'] = 'Avaimen koko';
$labels['key2048'] = '2048 bittiä - oletus';
$labels['key4096'] = '4096 bittiä - turvallisempi';
