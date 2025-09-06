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
$messages['passwordtooweak'] = '密碼太差';
$messages['passwordnoseq'] = '密碼不應是連續的字串，例如 123456 或 QWERTY';
$messages['passwordnocommon'] = '密碼不應是常見的字詞或名稱';
$messages['passwordforbidden'] = '密碼含有禁用字元';
$messages['firstloginchange'] = '這是你第一次登入。請更改密碼。';
$messages['disablednotice'] = '系統正在進行保養工作，故此無法更改密碼。系統很快會回復正常。抱歉！';
$messages['passwinhistory'] = '此密碼之前已用過';
$messages['samepasswd'] = '新密碼要和舊的不同';
$messages['passwdexpirewarning'] = '警告！您的密碼很快會到期，請在 $expirationdatetime 之前更改';
$messages['passwdexpired'] = '您的密碼已到期，請馬上更改！';
$messages['passwdconstraintviolation'] = '違反密碼規限。密碼可能太弱了。';
$messages['pwned_isdisclosed'] = '此密碼為人所共知。';
$messages['pwned_fetcherror'] = '驗證密碼強度失敗。';

$labels['hestia_err_policy_short'] = '密码太短（最小长度：%min）。';
$labels['hestia_err_endpoint_invalid'] = 'Hestia 端点未配置或无效。';
$labels['hestia_err_identity'] = '无法将用户名解析为 local@domain%hint.';
$labels['hestia_err_owner_unknown'] = '无法确定 %domain 的域名所有者。';
$labels['hestia_err_unknown'] = '来自 Hestia API 的未知错误。';
$labels['hestia_err_default'] = 'Hestia 返回错误代码 %code。';
$labels['hestia_err_1'] = '提供的论据不足。';
$labels['hestia_err_2'] = '无效对象或参数。';
$labels['hestia_err_3'] = '对象不存在（未找到邮箱或域）。';
$labels['hestia_err_4'] = '对象已存在。';
$labels['hestia_err_5'] = '对象已暂停。';
$labels['hestia_err_6'] = '对象已经未悬挂。';
$labels['hestia_err_7'] = '使用中的对象；不能修改。';
$labels['hestia_err_8'] = '由于软件包限制，操作受阻。';
$labels['hestia_err_9'] = '密码错误或无效。';
$labels['hestia_err_10'] = 'Hestia 拒绝授予权限（API 身份缺乏权限或 IP 不允许）。';
$labels['hestia_err_11'] = '系统策略禁用功能。';
$labels['hestia_err_12'] = '解析错误。';
$labels['hestia_err_13'] = '磁盘错误。';
$labels['hestia_err_14'] = '服务器负载过高。';
$labels['hestia_err_15'] = '连接错误。';
$labels['hestia_err_16'] = 'FTP 错误。';
$labels['hestia_err_17'] = '数据库错误。';
$labels['hestia_err_18'] = 'RRD 错误。';
$labels['hestia_err_19'] = '更新错误。';
$labels['hestia_err_20'] = '重启错误。';
?>
