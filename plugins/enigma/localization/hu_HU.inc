<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2015, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/enigma/
*/
$labels['encryption'] = 'Titkosítás';
$labels['enigmacerts'] = 'S/MIMI Tanusitványok';
$labels['enigmakeys'] = 'PGP Kulcsok';
$labels['keysfromto'] = 'Kulcsok $from - $to / $count ';
$labels['keyname'] = 'Név';
$labels['keyid'] = 'Kulcs ID';
$labels['keyuserid'] = 'Felhasználó ID';
$labels['keytype'] = 'Kulcs tipus';
$labels['fingerprint'] = 'Ujjlenyomat';
$labels['subkeys'] = 'Alkulcsok';
$labels['basicinfo'] = 'Alap információk';
$labels['typepublickey'] = 'nyilvános kulcs';
$labels['typekeypair'] = 'kulcspár';
$labels['keyattfound'] = 'Ez az űzenet tartalmaz csatolt PGP kulcso(ka)t';
$labels['keyattimport'] = 'Kulcs(ok) importálása';
$labels['typesign'] = 'Aláírás';
$labels['typeencrypt'] = 'Titkosít';
$labels['typeauth'] = 'Hitelesítés';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritmus';
$labels['subkeycreated'] = 'Létrehozva';
$labels['subkeyexpires'] = 'Lejár';
$labels['subkeyusage'] = 'Felhasználás';
$labels['expiresnever'] = 'soha';
$labels['unknown'] = 'ismeretlen';
$labels['uservalid'] = 'Érvényes';
$labels['userid'] = 'ID';
$labels['valid'] = 'érvényes';
$labels['supportencryption'] = 'Az üzenet titkosításának és aláíásának engedélyezése';
$labels['supportsignatures'] = 'Az űzenet álírásainak ellenörzésének engedélyezése';
$labels['signdefault'] = 'Alapértelemzetten írja alá az üzeneteket';
$labels['encryptdefault'] = 'Titkosítsa az összes üzenetet alapértelemzetten';
$labels['nminutes'] = '$m perc';
$labels['wholesession'] = 'az egész munkamenet';
$labels['createkeys'] = 'Új kulcs pár létrehozása';
$labels['importkeys'] = 'Kulcsok importálása';
$labels['exportkeys'] = 'Kulcs(ok) exportálása';
$labels['keyactions'] = 'Kulcs műveletek';
$labels['keyremove'] = 'Eltávolítás';
$labels['keydisable'] = 'Tilt';
$labels['keyrevoke'] = 'Visszavon';
$labels['keysend'] = 'Kűld el a nyilvános kulcsot az üzenetben';
$labels['keychpass'] = 'Jelszó módosítás';
$labels['newkeyident'] = 'Azonosító:';
$labels['newkeypass'] = 'Jelszó:';
$labels['newkeypassconfirm'] = 'Jelszó megerősítése';
$labels['newkeysize'] = 'Kulcs méret:';
$labels['key2048'] = '2048 bit - alapértelemzett';
$labels['key4096'] = '4096 bit - biztonságosabb';
$labels['keygenerating'] = 'Kulcsok generálása…';
$labels['encryptionoptions'] = 'Titkosítási beállítások...';
$labels['encryptmsg'] = 'Titkosítsa ezt az üzenetet';
$labels['signmsg'] = 'Ennek az üzenetnek  a digtális aláírása';
$labels['arialabelkeyexportoptions'] = 'Kulcsok exportálásához opciók';
$messages['keyremoving'] = 'Kulcs(ok) eltávolítása...';
$messages['keyremoveconfirm'] = 'Biztosan törölni szeretnéd a kiválasztott kulcso(ka)t?';
$messages['keyremovesuccess'] = 'A kulcs(ok) sikeresen törölve';
$messages['keyremoveerror'] = 'Nem sikerült a kiválasztott kulcso(ka)t törölni.';
$messages['formerror'] = 'Tőltsd ki az ürlapot. Minden mező kitöltése kötelező!';
$messages['passwordsdiffer'] = 'A jelszavak nem egyeznek!';
$messages['keygenerateerror'] = 'Nem sikerült a kulcs-pár létrehozása';
$messages['keygeneratesuccess'] = 'A kulcs-pár létrejött és sikeresen beimportálódott.';
$messages['keygennosupport'] = 'A böngésződ nem támogatja a titkosítást. A kulcs-pár nem hozható létre!';
?>
