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

$labels['encryption'] = 'Criptare';
$labels['enigmacerts'] = 'Certificate S/MIME';
$labels['enigmakeys'] = 'Chei PGP';
$labels['keysfromto'] = 'Cheile de la $from la $to din $count';
$labels['keyname'] = 'Nume';
$labels['keyid'] = 'ID Cheie';
$labels['keyuserid'] = 'ID Utilizator';
$labels['keytype'] = 'Tip cheie';
$labels['fingerprint'] = 'Amprentă';
$labels['subkeys'] = 'Subchei';
$labels['basicinfo'] = 'Informații de bază';
$labels['userids'] = 'Utilizatori adiționali';
$labels['typepublickey'] = 'cheie publică';
$labels['typekeypair'] = 'cheie pereche';
$labels['keyattfound'] = 'Acest mesaj conține chei PGP  atașate.';
$labels['keyattimport'] = 'Importă chei';
$labels['typesign'] = 'Semnează';
$labels['typeencrypt'] = 'Criptează';
$labels['typecert'] = 'Certifică';
$labels['typeauth'] = 'Autentificare';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritm';
$labels['subkeycreated'] = 'Creat la';
$labels['subkeyexpires'] = 'Expiră la';
$labels['subkeyusage'] = 'Folosire';
$labels['expiresnever'] = 'niciodată';
$labels['unknown'] = 'necunoscut';
$labels['uservalid'] = 'Valid';
$labels['userid'] = 'ID';
$labels['valid'] = 'valid';
$labels['supportencryption'] = 'Activează criptare și semnare mesaj';
$labels['supportsignatures'] = 'Activează verificare semnături mesaj';
$labels['supportdecryption'] = 'Activează decriptare mesaj';
$labels['signdefault'] = 'Semnează toate mesajele în mod prestabilit';
$labels['encryptdefault'] = 'Criptează toate mesajele în mod prestabilit';
$labels['attachpubkeydefault'] = 'Atașează cheia mea publică PGP implicit';
$labels['passwordtime'] = 'Păstrează parola cheii private pentru';
$labels['nminutes'] = '$m minut(e)';
$labels['wholesession'] = 'toată sesiunea';
$labels['createkeys'] = 'Creează cheie pereche nouă';
$labels['importkeys'] = 'Importă chei(e)';
$labels['exportkeys'] = 'Expertă chei(e)';
$labels['keyactions'] = 'Acțiuni cheie';
$labels['keyremove'] = 'Elimină';
$labels['keydisable'] = 'Dezactivează';
$labels['keyrevoke'] = 'Revocă';
$labels['keysend'] = 'Trimite cheie publică în mesaj';
$labels['keychpass'] = 'Schimbă parola';
$labels['newkeypass'] = 'Parolă';
$labels['newkeytype'] = 'Tip cheie';
$labels['keygenerating'] = 'Se generează cheile...';
$labels['encryptionoptions'] = 'Opțiuni criptare';
$labels['encryptmsg'] = 'Criptează acest mesaj';
$labels['signmsg'] = 'Semnează digital acest mesaj';
$labels['enterkeypasstitle'] = 'Introdu fraza de acces cheie';
$labels['enterkeypass'] = 'O frază de acces este necesară pentru a debloca cheia secretă ($keyid) pentru utilizatorul: $user.';
$labels['attachpubkeymsg'] = 'Atașează cheia mea publică';
$labels['keyexportprompt'] = 'Vrei să incluzi cheile secrete în fișierul de chei OpenPGP salvat?';
$labels['onlypubkeys'] = 'Exportă doar Cheile Publice';
$labels['withprivkeys'] = 'Exportă Cheile Secrete';
$labels['findkey'] = 'Caută în serverele de chei';
$labels['keyimportlabel'] = 'Importă din fișier';
$labels['keyimportsearchlabel'] = 'Caută în serverele de chei';
$labels['arialabelkeyexportoptions'] = 'Opțiuni export chei';
$messages['sigvalid'] = 'Semnătură $sender verificată.';
$messages['sigvalidpartial'] = 'Semnătură $sender verificată, dar parte a mesajului nu a fost semnat.';
$messages['siginvalid'] = 'Semnătură nevalidă de la $sender.';
$messages['sigunverified'] = 'Semnătură neverificată. Certificat neverificat. ID Certificat: $keyid.';
$messages['signokey'] = 'Semnătură neverificată. Cheie publică negăsită. ID Cheie: $keyid.';
$messages['sigerror'] = 'Semnătura neconfirmată. Eroare internă.';
$messages['decryptok'] = 'Mesajul a fost decriptat.';
$messages['decrypterror'] = 'Decriptarea a eșuat.';
$messages['decryptnokey'] = 'Decriptarea a eșuat. Cheie privată negăsită. ID Cheie: $keyid.';
$messages['decryptbadpass'] = 'Decriptarea a eșuat. Parolă nevalidă.';
$messages['decryptnopass'] = 'Decriptarea a eșuat. Parola este obligatorie.';
$messages['decryptpartial'] = 'Mesaj decriptat, dar parte a mesajului nu a fost criptat.';
$messages['signerror'] = 'Semnarea a eșuat.';
$messages['signnokey'] = 'Semnarea a eșuat. Cheia privată nu a fost găsită.';
$messages['signbadpass'] = 'Semnarea a eșuat. Parolă nevalidă.';
$messages['signnopass'] = 'Semnarea a eșuat. Parolă cheie necesară.';
$messages['encrypterror'] = 'Criptarea a eșuat.';
$messages['encryptnokey'] = 'Criptarea a eșuat. Cheia publică nu a fost găsită pentru $email.';
$messages['nokeysfound'] = 'Nici o cheie găsită';
$messages['keynotfound'] = 'Cheia nu a fost găsită!';
$messages['keyopenerror'] = 'Imposibil de preluat informații cheie! Eroare internă.';
$messages['keylisterror'] = 'Imposibil de afișat chei! Eroare internă.';
$messages['keysimportfailed'] = 'Imposibil de importat chei(e)! Eroare internă.';
$messages['keysimportsuccess'] = 'Chei importate cu succes. Importate: $new, neschimbate: $old.';
$messages['keyremoving'] = 'Se elimină cheile...';
$messages['keyremoveconfirm'] = 'Ești sigur că vrei să ștergi cheile selectate?';
$messages['keyremovesuccess'] = 'Chei eliminate cu succes';
$messages['keyremoveerror'] = 'Imposibil de șters cheile selectate.';
$messages['keyimporttext'] = 'Poți importa chei private și publice sau semnături de revocare în format ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Poți căuta chei publice după identificatorul de cheie, numele de utilizator sau adresa de e-mail și apoi le poți importa direct.';
$messages['formerror'] = 'Te rog completează formularul. Toate câmpurile sunt necesare!';
$messages['passwordsdiffer'] = 'Parolele nu se potrivesc!';
$messages['keygenerateerror'] = 'Nu s-a putut genera cheia pereche';
$messages['keygeneratesuccess'] = 'Cheia pereche a fost generată și importată cu succes.';
$messages['keygennosupport'] = 'Browserul dvs. web nu acceptă criptografia. Imposibil de generat cheia pereche!';
$messages['noidentselected'] = 'Trebuie sa alegi cel puțin o identitate pentru cheie!';
$messages['nonameident'] = 'Identitatea trebuie să aibă definit un nume de utilizator!';
$labels['key2048'] = '2048 biți - implicit';
$labels['key4096'] = '4096 biți - mult mai sigur';
