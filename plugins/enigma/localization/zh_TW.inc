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
$labels['encryption'] = '加密';
$labels['enigmacerts'] = 'S/MIME 憑證';
$labels['enigmakeys'] = 'PGP 金鑰';
$labels['keysfromto'] = '金鑰：由 $from 至 $to (共 $count 個)';
$labels['keyname'] = '名稱';
$labels['keyid'] = '金鑰 ID';
$labels['keyuserid'] = '使用者 ID';
$labels['keytype'] = '金鑰類型';
$labels['fingerprint'] = '指紋';
$labels['subkeys'] = '子金鑰';
$labels['keyprops'] = '金鑰屬性';
$labels['basicinfo'] = '基本資訊';
$labels['userids'] = '額外使用者';
$labels['typepublickey'] = '公鑰';
$labels['typekeypair'] = '金鑰對';
$labels['keyattfound'] = '本郵件有附帶的 PGP 金鑰';
$labels['keyattimport'] = '匯入金鑰';
$labels['typesign'] = '簽署';
$labels['typeencrypt'] = '加密';
$labels['typecert'] = '認證';
$labels['typeauth'] = '身份核對';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = '演算法';
$labels['subkeycreated'] = '建立時間：';
$labels['subkeyexpires'] = '過期時間：';
$labels['subkeyusage'] = '使用情況';
$labels['expiresnever'] = '從未使用';
$labels['unknown'] = '不明';
$labels['uservalid'] = '有效';
$labels['userid'] = 'ID';
$labels['valid'] = '有效';
$labels['supportencryption'] = '啟用郵件加密及簽署';
$labels['supportsignatures'] = '啟用郵件簽名驗證';
$labels['supportdecryption'] = '啟用郵件解密';
$labels['signdefault'] = '預設簽署所有郵件';
$labels['encryptdefault'] = '預設加密所有郵件';
$labels['attachpubkeydefault'] = '預設附上我的公開 PGP 金鑰';
$labels['passwordtime'] = '為以下項目保留私密金鑰密碼：';
$labels['nminutes'] = '$m 分鐘';
$labels['wholesession'] = '整個工作階段';
$labels['createkeys'] = '建立新的金鑰對';
$labels['importkeys'] = '匯入金鑰';
$labels['exportkeys'] = '匯出金鑰';
$labels['keyactions'] = '金鑰動作...';
$labels['keyremove'] = '移除';
$labels['keydisable'] = '停用';
$labels['keyrevoke'] = '撤銷';
$labels['keysend'] = '在郵件傳送公開金鑰';
$labels['keychpass'] = '更改密碼';
$labels['newkeyident'] = '身分';
$labels['newkeypass'] = '使用者密碼';
$labels['newkeypassconfirm'] = '確認密碼';
$labels['newkeysize'] = '金鑰大小';
$labels['key2048'] = '2048 位元 - 預設';
$labels['key4096'] = '4096 位元 - 較安全';
$labels['keygenerating'] = '正在製作金鑰...';
$labels['encryptionoptions'] = '加密選項...';
$labels['encryptmsg'] = '加密本郵件';
$labels['signmsg'] = '數位簽署本郵件';
$labels['sendunencrypted'] = '以不加密方式傳送';
$labels['enterkeypasstitle'] = '輸入金鑰密語';
$labels['enterkeypass'] = '需要密語才能為使用者 $user 解鎖密鑰 ($keyid)';
$labels['arialabelkeyexportoptions'] = '金鑰匯出選項';
$labels['attachpubkeymsg'] = '附上我的公開金鑰';
$labels['keyexportprompt'] = '是否包括儲存在 OpenPGP 金鑰檔案的密鑰？';
$labels['onlypubkeys'] = '僅匯出公開金鑰';
$labels['withprivkeys'] = '匯出密鑰';
$labels['findkey'] = '在金鑰伺服器搜尋';
$labels['keyimportlabel'] = '從檔案匯入';
$labels['keyimportsearchlabel'] = '在金鑰伺服器搜尋';
$labels['managekeys'] = '管理 PGP 金鑰';
$labels['identitymatchingprivkeys'] = '您的鑰匙圈有 $nr 個符合的 PGP 私鑰：';
$labels['identitynoprivkeys'] = '傳送者的身分不符合您鑰匙圈中 PGP 私鑰的任何一個。';
$messages['sigvalid'] = '來自 $sender 的已驗證簽名';
$messages['sigvalidpartial'] = '來自 $sender 的已驗證簽名，但內文部分並未簽署';
$messages['siginvalid'] = '來自 $sender 的無效簽名';
$messages['sigunverified'] = '未驗證的簽名。憑證無驗證。憑證 ID：$keyid';
$messages['signokey'] = '未驗證的簽名。找不到公開金鑰。金鑰 ID：$keyid';
$messages['sigerror'] = '未驗證的簽名。內部出錯';
$messages['decryptok'] = '郵件已解密';
$messages['decrypterror'] = '解密失敗';
$messages['decryptnokey'] = '解密失敗。找不到私密金鑰。金鑰 ID：$keyid';
$messages['decryptbadpass'] = '解密失敗。密碼無效';
$messages['decryptnopass'] = '解密失敗。必需要金鑰的密碼';
$messages['decryptpartial'] = '郵件已解密，但內文部分未有加密';
$messages['signerror'] = '簽名失敗';
$messages['signnokey'] = '簽名失敗。找不到私密金鑰';
$messages['signbadpass'] = '簽名失敗。密碼無效';
$messages['signnopass'] = '簽名失敗。必須要金鑰密碼';
$messages['encrypterror'] = '加密失敗';
$messages['encryptnokey'] = '加密失敗。找不到用於 $email 的公開金鑰';
$messages['nokeysfound'] = '找不到金鑰';
$messages['keynotfound'] = '找不到金鑰！';
$messages['keyopenerror'] = '無法取得金鑰資訊！內部出錯';
$messages['keylisterror'] = '無法列出金鑰！內部出錯';
$messages['keysimportfailed'] = '無法匯入金鑰！內部出錯';
$messages['keysimportsuccess'] = '成功匯入金鑰。匯入：$new，不變：$old';
$messages['keyremoving'] = '正在移除金鑰...';
$messages['keyremoveconfirm'] = '是否確定要刪除所選的金鑰？';
$messages['keyremovesuccess'] = '成功刪除金鑰';
$messages['keyremoveerror'] = '無法刪除所選的金鑰';
$messages['keyimporttext'] = '可用 ASCII-Armor 格式匯入私密及公開金鑰，或撤銷金鑰';
$messages['keyimportsearchtext'] = '可用金鑰識別碼、使用者名稱或電郵位址搜尋公開金鑰，然後直接匯入';
$messages['formerror'] = '請填好表格，所有欄位都是必須的！';
$messages['passwordsdiffer'] = '密碼不符！';
$messages['keygenerateerror'] = '未能產生金鑰對';
$messages['keygeneratesuccess'] = '成功產生並匯入金鑰對';
$messages['keygennosupport'] = '您的網項瀏覽器不支援加密機制，故此無法產生金鑰對！';
$messages['noidentselected'] = '要為金鑰選取至少一個身份！';
$messages['nonameident'] = '身份必須要有使用者名稱！';
?>
