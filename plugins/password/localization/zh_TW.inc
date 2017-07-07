<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/
$labels['changepasswd']  = '更改密碼';
$labels['curpasswd']  = '目前的密碼：';
$labels['newpasswd']  = '新密碼';
$labels['confpasswd']  = '確認新密碼';
$messages['nopassword'] = '請輸入新密碼';
$messages['nocurpassword'] = '請輸入目前的密碼';
$messages['passwordincorrect'] = '目前的密碼錯誤';
$messages['passwordinconsistency'] = '密碼不相符，請重新輸入';
$messages['crypterror'] = '無法更新密碼：無加密機制';
$messages['connecterror'] = '無法更新密碼：連線失敗';
$messages['internalerror'] = '無法更新密碼';
$messages['passwordshort'] = '密碼至少需 $length 個字元';
$messages['passwordweak'] = '新密碼至少需含有一個數字與一個標點符號';
$messages['passwordforbidden'] = '密碼含有禁用字元';
$messages['firstloginchange'] = '這是你第一次登入。請更改密碼。';
$messages['disablednotice'] = '系統正在進行保養工作，故此無法更改密碼。系統很快會回復正常。抱歉！';
$messages['passwinhistory'] = '此密碼之前已用過';
$messages['samepasswd'] = '新密碼要和舊的不同';
$messages['passwdexpirewarning'] = '警告！您的密碼很快會到期，請在 $expirationdatetime 之前更改';
$messages['passwdexpired'] = '您的密碼已到期，請馬上更改！';
