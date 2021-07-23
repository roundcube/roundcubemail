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

$labels['encryption'] = 'Šifriranje';
$labels['enigmacerts'] = 'S/MIME certifikati';
$labels['enigmakeys'] = 'PGP ključevi';
$labels['keysfromto'] = 'Ključevi $from do $to od $count';
$labels['keyname'] = 'Naziv';
$labels['keyid'] = 'ID ključa';
$labels['keyuserid'] = 'Korisnički ID';
$labels['keytype'] = 'Vrsta ključa';
$labels['fingerprint'] = 'Otisak prsta';
$labels['subkeys'] = 'Podključevi';
$labels['keyprops'] = 'Svojstva ključa';
$labels['basicinfo'] = 'Osnovne informacije';
$labels['userids'] = 'Dodatni korisnici';
$labels['typepublickey'] = 'javni ključ';
$labels['keyattfound'] = 'Ova poruka sadrži priložen(e) PGP ključ(eve).';
$labels['keyattimport'] = 'Uvezi ključ(eve)';
$labels['typeencrypt'] = 'Šifriraj';
$labels['typeauth'] = 'Utvrđivanje identiteta';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritam';
$labels['subkeycreated'] = 'Stvoreno';
$labels['subkeyexpires'] = 'Istječe';
$labels['subkeyusage'] = 'Uporaba';
$labels['expiresnever'] = 'nikad';
$labels['unknown'] = 'nepoznato';
$labels['uservalid'] = 'Vrijedi';
$labels['userid'] = 'ID';
$labels['valid'] = 'vrijedni';
$labels['supportencryption'] = 'Omogući šifriranje i potpisivanje poruke';
$labels['supportsignatures'] = 'Omogući potvrđivanje potpisa poruke';
$labels['supportdecryption'] = 'Omogući dešifriranje poruke';
$labels['signdefault'] = 'Zadano potpisuj sve poruke';
$labels['encryptdefault'] = 'Zadano šifriraj sve poruke';
$labels['attachpubkeydefault'] = 'Zadano priloži moj javni PGP ključ';
$labels['nminutes'] = '$m minut(a)';
$labels['wholesession'] = 'cijela sesija';
$labels['importkeys'] = 'Uvezi ključ(eve)';
$labels['exportkeys'] = 'Izvezi ključ(eve)';
$labels['keyremove'] = 'Ukloni';
$labels['keydisable'] = 'Onemogući';
$labels['keyrevoke'] = 'Opozovi';
$labels['keysend'] = 'Pošalji u poruci javni ključ';
$labels['keychpass'] = 'Promjena zaporke';
$labels['newkeyident'] = 'Identitet';
$labels['newkeypass'] = 'Lozinka';
$labels['newkeypassconfirm'] = 'Potvrdi lozinku';
$labels['newkeytype'] = 'Vrsta ključa';
$labels['keygenerating'] = 'Stvaranje ključeva...';
$labels['encryptionoptions'] = 'Mogućnosti šifriranja...';
$labels['encryptmsg'] = 'Šifriraj ovu poruku';
$labels['signmsg'] = 'Digitalno potpiši ovu poruku';
$labels['sendunencrypted'] = 'Pošalji nešifrirano';
$labels['attachpubkeymsg'] = 'Priloži moj javni ključ';
$labels['keyexportprompt'] = 'Želite li uključiti tajne ključeve iz datoteke OpenPGP ključeva?';
$labels['onlypubkeys'] = 'Izvezi samo javne ključeve';
$labels['withprivkeys'] = 'Izvezi tajne ključeve';
$labels['keyimportlabel'] = 'Uvezi iz datoteke';
$labels['managekeys'] = 'Upravljaj PGP ključevima';
$labels['arialabelkeyexportoptions'] = 'Mogućnosti izvoza ključeva';
$messages['decryptok'] = 'Poruka dešifrirana.';
$messages['decrypterror'] = 'Dešifriranje neuspješno.';
$messages['decryptnokey'] = 'Dešifriranje neuspješno. Nije pronađen privatni ključ. ID ključa: $keyid.';
$messages['decryptbadpass'] = 'Dešifriranje neuspješno. Netočna lozinka.';
$messages['signerror'] = 'Potpisivanje neuspješno.';
$messages['signnokey'] = 'Potpisivanje neuspješno. Privatni ključ nije pronađen.';
$messages['signbadpass'] = 'Potpisivanje neuspješno. Netočna lozinka.';
$messages['encrypterror'] = 'Šifriranje neuspješno.';
$messages['nokeysfound'] = 'Ključevi nisu pronađeni';
$messages['keynotfound'] = 'Ključ nije pronađen!';
$messages['keyremoveconfirm'] = 'Jeste li sigurni da želite obrisati označeni(e) ključ(eve)?';
$messages['keyremovesuccess'] = 'Ključ(evi) uspješno obrisani';
$messages['keyremoveerror'] = 'Označeni(e) ključ(eve) nije moguće obrisati.';
$messages['formerror'] = 'Molimo popunite obrazac. Sva su polja obvezna!';
$messages['passwordsdiffer'] = 'Lozinke se ne podudaraju!';
$labels['newkeysize'] = 'Veličina ključa';
$labels['key2048'] = '2048 bitova - zadano';
$labels['key4096'] = '4096 bita - sigurnije';
