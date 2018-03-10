<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2016, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/
$labels['encryption'] = 'Zifraketa';
$labels['enigmacerts'] = 'S/MIME ziurtagiriak';
$labels['enigmakeys'] = 'PGP gakoak';
$labels['keysfromto'] = '$count-eko $from-etik $to-rako gakoak';
$labels['keyname'] = 'Izena';
$labels['keyid'] = 'Gakoaren IDa';
$labels['keyuserid'] = 'Erabiltzailearen IDa';
$labels['keytype'] = 'Gako mota';
$labels['fingerprint'] = 'Hatz-marka';
$labels['subkeys'] = 'Azpigakoak';
$labels['keyprops'] = 'Gakoaren propietateak';
$labels['basicinfo'] = 'Oinarrizko informazioa';
$labels['userids'] = 'Erabiltzaile gehigarriak';
$labels['typepublickey'] = 'gako publikoa';
$labels['typekeypair'] = 'gako bikotea';
$labels['keyattfound'] = 'Mezu honek erantsita PGP gakoa(k) d(it)u';
$labels['keyattimport'] = 'Inportatu gakoa(k)';
$labels['typesign'] = 'Sinatu';
$labels['typeencrypt'] = 'Zifratu';
$labels['typecert'] = 'Ziurtatu';
$labels['typeauth'] = 'Egiaztapena';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritmoa';
$labels['subkeycreated'] = 'Sortuta';
$labels['subkeyexpires'] = 'Iraungitze-data';
$labels['subkeyusage'] = 'Erabilera';
$labels['expiresnever'] = 'inoiz ez';
$labels['unknown'] = 'ezezaguna';
$labels['uservalid'] = 'Baliozkoa';
$labels['userid'] = 'ID';
$labels['valid'] = 'baliozkoa';
$labels['supportencryption'] = 'Gaitu mezuen zifratzea eta sinadura ';
$labels['supportsignatures'] = 'Gaitu mezuen sinaduren egiaztapena';
$labels['supportdecryption'] = 'Gaitu mezuen deszifratzea';
$labels['signdefault'] = 'Mezuen sinatzea lehenetsita';
$labels['encryptdefault'] = 'Mezuen zifratzea lehenetsita';
$labels['attachpubkeydefault'] = 'Nire PGP gako publikoa eranstea lehenetsita';
$labels['passwordtime'] = 'Mantendu gako pribatuen pasahitzak';
$labels['nminutes'] = '$m minutu';
$labels['wholesession'] = 'Saio osoa';
$labels['createkeys'] = 'Sortu gako pare berria';
$labels['importkeys'] = 'Inportatu gakoa(k)';
$labels['exportkeys'] = 'Esportatu gakoa(k)';
$labels['keyactions'] = 'Gakoaren ekintzak...';
$labels['keyremove'] = 'Kendu';
$labels['keydisable'] = 'Desgaitu';
$labels['keyrevoke'] = 'Errebokatu';
$labels['keysend'] = 'Bidali gako publikoa mezu batean';
$labels['keychpass'] = 'Aldatu pasahitza';
$labels['newkeyident'] = 'Identitatea';
$labels['newkeypass'] = 'Pasahitza';
$labels['newkeypassconfirm'] = 'Baieztatu pasahitza';
$labels['newkeysize'] = 'Gakoaren tamaina';
$labels['key2048'] = '2048 bit - lehenetsia';
$labels['key4096'] = '4096 bit - seguruagoa';
$labels['keygenerating'] = 'Gakoak sortzen...';
$labels['encryptionoptions'] = 'Zifratze aukerak...';
$labels['encryptmsg'] = 'Zifratu mezu hau';
$labels['signmsg'] = 'Digitalki sinatu mezu hau';
$labels['sendunencrypted'] = 'Bidali zifratu gabe';
$labels['enterkeypasstitle'] = 'Sartu gakoaren pasaesaldia';
$labels['enterkeypass'] = 'Pasaesaldi bat behar da erabiltzaile honen gako sekretua ($keyid) desblokeatzeko: $user';
$labels['arialabelkeyexportoptions'] = 'Gakoen esportazio aukerak';
$labels['attachpubkeymsg'] = 'Erantsi nire gako publikoa';
$labels['keyexportprompt'] = 'Nahi al duzu gako sekretuak sartzea gordetako OpenPGP gakoen fitxategian?';
$labels['onlypubkeys'] = 'Esportatu bakarrik gako publikoak';
$labels['withprivkeys'] = 'Esportatu gako sekretuak';
$labels['findkey'] = 'Bilatu gako zerbitzari(et)an';
$labels['keyimportlabel'] = 'Inportatu fitxategitik';
$labels['keyimportsearchlabel'] = 'Bilatu gako zerbitzari(et)an';
$messages['sigvalid'] = '$sender-en sinadura egiaztatuta';
$messages['sigvalidpartial'] = '$sender-en sinadura egiaztatuta, baina testu-gorputzaren zati bat ez dago sinatua.';
$messages['siginvalid'] = '$sender-en sinadura baliogabea.';
$messages['sigunverified'] = 'Egiaztatu gabeko sinadura. Ziurtagiria ez dago egiaztatuta. Ziurtagiriaren IDa: $Keyid.';
$messages['signokey'] = 'Egiaztatu gabeko sinadura. Ez da aurkitu gako publikoa. Gakoaren IDa: $keyid.';
$messages['sigerror'] = 'Egiaztatu gabeko sinadura. Barne errorea.';
$messages['decryptok'] = 'Mezua deszifratua.';
$messages['decrypterror'] = 'Deszifratzeak huts egin du.';
$messages['decryptnokey'] = 'Deszifratzeak  huts egin du. Ez da aurkitu gako pribatua. Gakoaren IDa:  $keyid.';
$messages['decryptbadpass'] = 'Deszifratzeak huts egin du. Baliogabeko pasahitza.';
$messages['decryptnopass'] = 'Deszifratzeak huts egin du. Pasahitz gakoa behar da.';
$messages['decryptpartial'] = 'Mezua deszifratua, baina testu gorputzaren zati bat ez zegoen zifratua.';
$messages['signerror'] = 'Sinadurak huts egin du.';
$messages['signnokey'] = 'Sinadurak huts egin du. Ez da aurkitu gako pribatua.';
$messages['signbadpass'] = 'Sinadurak huts egin du. Baliogabeko pasahitza.';
$messages['signnopass'] = 'Sinadurak huts egin du. Gako pasahitza behar da.';
$messages['encrypterror'] = 'Zifratzeak huts egin du.';
$messages['encryptnokey'] = 'Zifratzeak huts egin du. Ez da $email -ren gako publikoa aurkitu.';
$messages['nokeysfound'] = 'Ez dira gakoak aurkitu';
$messages['keynotfound'] = 'Gakoa ez da aurkitu!';
$messages['keyopenerror'] = 'Ezin izan da gakoaren informazioa eskuratu! Barne errorea.';
$messages['keylisterror'] = 'Ezin izan dira gakoak zerrendatu! Barne errorea.';
$messages['keysimportfailed'] = 'Ezin izan d(ir)a gakoa(k) inportatu! Barne errorea.';
$messages['keysimportsuccess'] = 'Gakoa(k) ongi inportatu d(ir)a. Inportatua: $new, aldatu gabe: $old';
$messages['keyremoving'] = 'Gakoa(k) kentzen...';
$messages['keyremoveconfirm'] = 'Ziur hautatutako gakoa(k) ezabatu nahi d(it)uzula?';
$messages['keyremovesuccess'] = 'Gakoa(k) ongi ezabatu d(ir)a';
$messages['keyremoveerror'] = 'Ezin izan d(ir)a hautatutako gakoa(k) ezabatu.';
$messages['keyimporttext'] = 'Gako pribatu eta publikoa(k) edo errebokazio sinadurak ASCII-Armor formatuan inportatu ahal dituzu.';
$messages['keyimportsearchtext'] = 'Gako publikoak bilatzen ahal dituzu gako identifikatzaile, erabiltzaile-izena edo posta helbidearen bidez eta orduan zuzenean inportatu.';
$messages['formerror'] = 'Bete formularioa. Eremu guztiak bete behar dira!';
$messages['passwordsdiffer'] = 'Pasahitzak ez dira berdinak!';
$messages['keygenerateerror'] = 'Huts egin du gako bikotea sortzea.';
$messages['keygeneratesuccess'] = 'Gako bikotea ongi sortu eta inportatu da.';
$messages['keygennosupport'] = 'Zure nabigatzaileak ez du kriptografia onartzen. Ezin da gako bikotea sortu!';
$messages['noidentselected'] = 'Gutxienez identitate bat hautatu behar duzu gakoarentzat!';
$messages['nonameident'] = 'Identitateak erabiltzaile-izen bat definitua izan behar du!';
?>
