<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['encryption'] = 'Šifriranje';
$labels['enigmacerts'] = 'S/MIME certifikati';
$labels['enigmakeys'] = 'PGP ključi';
$labels['keysfromto'] = 'Ključi od $from do $to od skupno $count';
$labels['keyname'] = 'Ime';
$labels['keyid'] = 'ID ključa';
$labels['keyuserid'] = 'ID uporabnika';
$labels['keytype'] = 'Vrsta ključa';
$labels['fingerprint'] = 'Prstni odtis';
$labels['subkeys'] = 'Podključi';
$labels['keyprops'] = 'Lastnosti ključa';
$labels['basicinfo'] = 'Osnovne informacije';
$labels['userids'] = 'Dodatni uporabniki';
$labels['typepublickey'] = 'javni ključ';
$labels['typekeypair'] = 'par ključev';
$labels['keyattfound'] = 'To sporočilo vsebuje priložen(e) PGP ključ(e).';
$labels['keyattimport'] = 'Uvozi ključ(e)';
$labels['typesign'] = ' Podpiši';
$labels['typeencrypt'] = 'Šifriraj';
$labels['typecert'] = 'Potrdi';
$labels['typeauth'] = 'Avtentikacija';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritem';
$labels['subkeycreated'] = 'Ustvarjen';
$labels['subkeyexpires'] = 'Poteče';
$labels['subkeyusage'] = 'Uporaba';
$labels['expiresnever'] = 'nikoli';
$labels['unknown'] = 'neznano';
$labels['uservalid'] = 'Veljavno';
$labels['userid'] = 'ID';
$labels['valid'] = 'veljavno';
$labels['supportencryption'] = 'Omogoči šifriranje in podpisovanje sporočil';
$labels['supportsignatures'] = 'Omogoči preverjanje podpisov sporočil';
$labels['supportdecryption'] = 'Omogoči dešifriranje sporočil';
$labels['signdefault'] = 'Privzeto podpiši vsa sporočila';
$labels['encryptdefault'] = 'Privzeto šifriraj vsa sporočila';
$labels['attachpubkeydefault'] = 'Privzeto priloži moj javni PGP ključ';
$labels['passwordtime'] = 'Shrani gesla zasebnih ključev za';
$labels['nminutes'] = '$m minut';
$labels['wholesession'] = 'celotno sejo';
$labels['createkeys'] = 'Ustvari nov par ključev';
$labels['importkeys'] = 'Uvozi ključ(e)';
$labels['exportkeys'] = ' Izvozi ključ(e)';
$labels['keyactions'] = 'Dejanja ključev...';
$labels['keyremove'] = 'Odstrani';
$labels['keydisable'] = 'Onemogoči';
$labels['keyrevoke'] = 'Prekliči';
$labels['keysend'] = 'Pošlji javni ključ v sporočilu';
$labels['keychpass'] = 'Spremeni geslo';
$labels['keyadd'] = 'Dodaj ključ';
$labels['newkeyident'] = 'Identiteta';
$labels['newkeypass'] = 'Geslo';
$labels['newkeypassconfirm'] = 'Potrdi geslo';
$labels['newkeytype'] = 'Vrsta ključa';
$labels['rsa2048'] = 'RSA - 2048 bitov';
$labels['rsa4096'] = 'RSA - 4096 bitov';
$labels['ecckeypair'] = 'ECC (Eliptična krivulja)';
$labels['keygenerating'] = 'Ustvarjanje ključev...';
$labels['encryptionoptions'] = 'Možnosti šifriranja...';
$labels['encryptmsg'] = 'Šifriraj to sporočilo';
$labels['signmsg'] = 'Digitalno podpiši to sporočilo';
$labels['sendunencrypted'] = 'Pošlji nešifrirano';
$labels['enterkeypasstitle'] = 'Vnesite geslo ključa';
$labels['enterkeypass'] = 'Potrebno je geslo za odklepanje tajnega ključa ($keyid) za uporabnika: $user.';
$labels['attachpubkeymsg'] = 'Priloži moj javni ključ';
$labels['keyexportprompt'] = 'Ali želite vključiti tajne ključe v shranjeno datoteko OpenPGP ključev?';
$labels['onlypubkeys'] = 'Izvozi samo javne ključe';
$labels['withprivkeys'] = 'Izvozi tajne ključe';
$labels['findkey'] = 'Išči na strežnikih ključev';
$labels['keyimportlabel'] = 'Uvozi iz dokumenta';
$labels['keyimportsearchlabel'] = 'Išči na strežnikih ključev';
$labels['managekeys'] = 'Upravljaj PGP ključe';
$labels['identitymatchingprivkeys'] = 'V svoji zbirki ključev imate $nr ujemajočih se zasebnih PGP ključev:';
$labels['identitynoprivkeys'] = 'Ta identiteta pošiljatelja še nima zasebnega PGP ključa v vaši zbirki ključev.';
$labels['arialabelkeyexportoptions'] = 'Možnosti izvoza ključev';
$labels['arialabelkeysearchform'] = 'Obrazec za iskanje ključev';
$labels['arialabelkeyoptions'] = 'Možnosti ključa';
$messages['sigvalid'] = 'Preverjen podpis od $sender.';
$messages['sigvalidpartial'] = 'Preverjen podpis od $sender, vendar del vsebine ni bil podpisan.';
$messages['siginvalid'] = 'Neveljaven podpis od $sender.';
$messages['sigunverified'] = 'Nepreverjen podpis. Certifikat ni preverjen. ID certifikata: $keyid.';
$messages['signokey'] = 'Nepreverjen podpis. Javni ključ ni najden. ID ključa: $keyid.';
$messages['sigerror'] = 'Nepreverjen podpis. Notranja napaka.';
$messages['decryptok'] = 'Sporočilo dešifrirano.';
$messages['decrypterror'] = 'Dešifriranje ni uspelo.';
$messages['decryptnokey'] = 'Dešifriranje ni uspelo. Zasebni ključ ni najden. ID ključa: $keyid.';
$messages['decryptnomdc'] = 'Dešifriranje preskočeno. Sporočilo nima zaščite celovitosti.';
$messages['decryptbadpass'] = 'Dešifriranje ni uspelo. Neveljavno geslo.';
$messages['decryptnopass'] = 'Dešifriranje ni uspelo. Zahtevano je geslo ključa.';
$messages['decryptpartial'] = 'Sporočilo dešifrirano, vendar del vsebine ni bil šifriran.';
$messages['signerror'] = 'Podpisovanje ni uspelo.';
$messages['signnokey'] = 'Podpisovanje ni uspelo. Zasebni ključ ni najden.';
$messages['signbadpass'] = 'Podpisovanje ni uspelo. Neveljavno geslo.';
$messages['signnopass'] = 'Podpisovanje ni uspelo. Zahtevano je geslo ključa.';
$messages['encrypterror'] = 'Šifriranje ni uspelo.';
$messages['encryptnokey'] = 'Šifriranje ni uspelo. Javni ključ za $email ni najden.';
$messages['encryptnoprivkey'] = 'Šifriranje ni uspelo. Zasebni ključ ni najden.';
$messages['nokeysfound'] = 'Ni najdenih ključev';
$messages['keynotfound'] = 'Ključ ni najden!';
$messages['keyopenerror'] = 'Pridobivanje podatkov o ključu ni mogoče! Notranja napaka.';
$messages['keylisterror'] = 'Izpis seznama ključev ni mogoč! Notranja napaka.';
$messages['keysimportfailed'] = 'Uvoz ključev ni mogoč! Notranja napaka.';
$messages['keysimportsuccess'] = 'Ključi uspešno uvoženi. Uvoženih: $new, nespremenjenih: $old.';
$messages['keyremoving'] = 'Odstranjevanje ključev...';
$messages['keyremoveconfirm'] = 'Ste prepričani, da želite izbrisati izbrane ključe?';
$messages['keyremovesuccess'] = ' Ključ(i) uspešno izbrisani';
$messages['keyremoveerror'] = 'Izbranih ključev ni mogoče izbrisati.';
$messages['keyimporttext'] = 'Uvozite lahko zasebne in javne ključe ali preklicanе podpise v ASCII-Armor formatu.';
$messages['keyimportsearchtext'] = 'Javne ključe lahko iščete po identifikatorju ključa, uporabniškem imenu ali e-poštnem naslovu in jih nato neposredno uvozite.';
$messages['keystoragenotice'] = 'Vsi javni in zasebni ključi so shranjeni na strežniku.';
$messages['formerror'] = 'Prosimo, izpolnite obrazec. Vsa polja so obvezna!';
$messages['passwordsdiffer'] = 'Gesli se ne ujemata!';
$messages['keygenerateerror'] = 'Generiranje para ključev ni uspelo';
$messages['keygeneratesuccess'] = 'Par ključev je bil uspešno generiran in uvožen.';
$messages['keygennosupport'] = 'Vaš spletni brskalnik ne podpira kriptografije. Generiranje para ključev ni mogoče!';
$messages['noidentselected'] = 'Za ključ morate izbrati vsaj eno identiteto!';
$messages['nonameident'] = 'Identiteta mora imeti definirano uporabniško ime!';
$labels['newkeysize'] = ' Velikost ključa';
$labels['key2048'] = '2048 bitov - privzeto';
$labels['key4096'] = '4096 bitov - bolj varno';
