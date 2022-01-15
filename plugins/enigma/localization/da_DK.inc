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

$labels['encryption'] = 'Kryptering';
$labels['enigmacerts'] = 'S/MIME Certifikater';
$labels['enigmakeys'] = 'PGP Nøgler';
$labels['keysfromto'] = 'Nøgler $from $to af $count';
$labels['keyname'] = 'Navn';
$labels['keyid'] = 'NøgleID';
$labels['keyuserid'] = 'Bruger ID';
$labels['keytype'] = 'Nøgletype';
$labels['fingerprint'] = 'Fingeravtryk';
$labels['subkeys'] = 'Undernøgler';
$labels['keyprops'] = 'Nøgleegenskaber';
$labels['basicinfo'] = 'Grundlæggende Oplysninger';
$labels['userids'] = 'Yderligere Brugere';
$labels['typepublickey'] = 'offentlig nøgle';
$labels['typekeypair'] = 'nøglepar';
$labels['keyattfound'] = 'Denne meddelelse indeholder vedhæftede PGP nøgl(er).';
$labels['keyattimport'] = 'Importer nøgl(er)';
$labels['typeencrypt'] = 'Krypter';
$labels['typeauth'] = 'Godkendelse';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritme';
$labels['subkeycreated'] = 'Oprettet';
$labels['subkeyexpires'] = 'Udløber';
$labels['subkeyusage'] = 'Anvendelse';
$labels['expiresnever'] = 'aldrig';
$labels['unknown'] = 'ukendt';
$labels['uservalid'] = 'Gyldig';
$labels['userid'] = 'ID';
$labels['valid'] = 'gyldig';
$labels['supportencryption'] = 'Aktiver beskedkryptering og meddelelsessignatur';
$labels['supportsignatures'] = 'Aktiver verificering af meddelelsessignaturer';
$labels['supportdecryption'] = 'Aktiver beskeddekryptering';
$labels['signdefault'] = 'Underskriv alle meddelelser som standard';
$labels['encryptdefault'] = 'Krypter alle meddelelser som standard';
$labels['attachpubkeydefault'] = 'Vedhæft min offentlige PGP nøgle som standard';
$labels['passwordtime'] = ' 
Behold adgangskoder til private nøgler for';
$labels['nminutes'] = '$m minut(er)';
$labels['wholesession'] = 'hele sessionen';
$labels['createkeys'] = 'Opret et nyt nøglepar';
$labels['importkeys'] = 'Importer nøgl(er)';
$labels['exportkeys'] = 'Eksporter nøgl(er)';
$labels['keyactions'] = 'Nøglehandlinger...';
$labels['keyremove'] = 'Fjern';
$labels['keydisable'] = 'Deaktiver';
$labels['keyrevoke'] = 'Tilbagekald';
$labels['keysend'] = 'Send offentlig nøgle i en besked';
$labels['keychpass'] = 'Skift kodeord:';
$labels['keyadd'] = 'Tilføj nøgle';
$labels['newkeyident'] = 'Identificer';
$labels['newkeypass'] = 'Adgangskode';
$labels['newkeypassconfirm'] = 'Bekræft password';
$labels['newkeytype'] = 'Nøgletype';
$labels['rsa2048'] = 'RSA - 2048 bits';
$labels['rsa4096'] = 'RSA - 4096 bits';
$labels['ecckeypair'] = 'ECC (Elliptic Curve)';
$labels['keygenerating'] = 'Genererer nøgler...';
$labels['encryptionoptions'] = 'Krypteringsmuligheder...';
$labels['encryptmsg'] = 'Krypter denne meddelelse';
$labels['signmsg'] = 'Underskriv denne meddelelse digitalt';
$labels['sendunencrypted'] = 'Send ukrypteret';
$labels['enterkeypasstitle'] = 'Indtast nøgleordet';
$labels['enterkeypass'] = 'En adgangssætning er nødvendig for at låse den hemmelige nøgle op ($keyid) for bruger: $user.';
$labels['attachpubkeymsg'] = 'Vedhæft min offentlige nøgle';
$labels['keyexportprompt'] = 'Vil du inkludere hemmelige nøgler i den gemte OpenPGP nøglefil?';
$labels['onlypubkeys'] = 'Eksporter kun offentlige nøgler';
$labels['withprivkeys'] = 'Eksporter hemmelige nøgler';
$labels['findkey'] = 'Søg på nøgleserv(er)';
$labels['keyimportlabel'] = 'Importer fra fil';
$labels['keyimportsearchlabel'] = 'Søg på nøgleserv(er)';
$labels['managekeys'] = 'Administrer PGP nøgler';
$labels['identitymatchingprivkeys'] = 'Du har $nr matchende PGP private nøgler gemt i din nøglering:';
$labels['identitynoprivkeys'] = 'Denne afsenderidentitet har endnu ikke en privat PGP nøgle gemt i din nøglering.';
$labels['arialabelkeyexportoptions'] = 'Nøgler eksport muligheder';
$labels['arialabelkeysearchform'] = 'Nøglesøgningsformular';
$labels['arialabelkeyoptions'] = 'Nøglemuligheder';
$messages['sigvalid'] = 'Verificeret signatur fra $sender.';
$messages['siginvalid'] = ' 
Ugyldig signatur fra $sender.';
$messages['sigunverified'] = 'Ubekræftet signatur. Certifikat ikke verificeret. Certifikat ID: $keyid.';
$messages['signokey'] = 'Ubekræftet signatur. Offentlig nøgle blev ikke fundet. Nøgle ID: $keyid.';
$messages['sigerror'] = 'Ubekræftet signatur. Intern fejl.';
$messages['decryptok'] = 'Meddelelse dekrypteret.';
$messages['decrypterror'] = 'Dekryptering mislykkedes.';
$messages['decryptnokey'] = 'Dekryptering mislykkedes. Privat nøgle ikke fundet. Nøgle ID: $keyid.';
$messages['decryptnomdc'] = 'Dekryptering sprunget over. Beskeden er ikke integritetsbeskyttet.';
$messages['decryptbadpass'] = 'Dekryptering mislykkedes. Forkert kodeord.';
$messages['decryptnopass'] = 'Dekryptering mislykkedes. Nøgleadgangskode påkrævet.';
$messages['signerror'] = 'Signering mislykkedes.';
$messages['signnokey'] = 'Signering mislykkedes. Privat nøgle ikke fundet.';
$messages['signbadpass'] = 'Signering mislykkedes. Forkert kodeord.';
$messages['signnopass'] = 'Signering mislykkedes. Nøgleadgangskode påkrævet.';
$messages['encrypterror'] = 'Kryptering mislykkedes.';
$messages['encryptnokey'] = 'Kryptering mislykkedes. Offentlig nøgle ikke fundet til $email.';
$messages['encryptnoprivkey'] = 'Kryptering mislykkedes. Privat nøgle ikke fundet.';
$messages['nokeysfound'] = 'Ingen nøgler fundet';
$messages['keynotfound'] = 'Nøglen blev ikke fundet!';
$messages['keyopenerror'] = 'Kan ikke få nøgle oplysninger! Intern fejl.';
$messages['keylisterror'] = 'Kan ikke angive nøgler! Intern fejl.';
$messages['keysimportfailed'] = 'Kan ikke importere nøgl(er)! Intern fejl.';
$messages['keysimportsuccess'] = 'Nøgl(er) blev succesfuldt Importeret: $new, uændret: $old.';
$messages['keyremoving'] = 'Slettet nøgl(er)...';
$messages['keyremoveconfirm'] = 'Er du sikker på, at du vil slette valgte nøgl(er)?';
$messages['keyremovesuccess'] = 'Nøgl(er) blev slettet';
$messages['keyremoveerror'] = 'Kunne ikke slette de valgte nøgl(er).';
$messages['keyimportsearchtext'] = 'Du kan søge efter offentlige nøgler efter nøgle id, brugernavn eller e -mail adresse og derefter importere dem direkte.';
$messages['keystoragenotice'] = 'Alle offentlige og private nøgler gemmes på serveren';
$messages['formerror'] = 'Venligst udfyld formularen. Alle felter skal udfyldes!';
$messages['passwordsdiffer'] = 'Adgangskoderne stemmer ikke overens!';
$messages['keygenerateerror'] = 'Det lykkedes ikke at generere et nøglepar';
$messages['keygeneratesuccess'] = 'Et nøglepar blev genereret og importeret.';
$messages['keygennosupport'] = 'Din webbrowser understøtter ikke kryptografi. Kan ikke generere et nøglepar!';
$messages['noidentselected'] = 'Du skal vælge mindst én identitet til nøglen!';
$messages['nonameident'] = 'Identitet skal have et brugernavn defineret!';
$labels['newkeysize'] = 'Nøglestørrelse';
$labels['key2048'] = '2048 bits - standard';
$labels['key4096'] = '4096 bits - mere sikker';
