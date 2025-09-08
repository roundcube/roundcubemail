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

$labels['changepasswd']  = '修改密码';
$labels['curpasswd']  = '当前密码：';
$labels['newpasswd']  = '新密码：';
$labels['confpasswd']  = '确认新密码：';
$messages['nopassword'] = '请输入新密码。';
$messages['nocurpassword'] = '请输入当前的密码。';
$messages['passwordincorrect'] = '当前密码不正确。';
$messages['passwordinconsistency'] = '两次输入的密码不一致，请重试。';
$messages['crypterror'] = '无法保存新密码，缺少加密功能。';
$messages['connecterror'] = '无法保存新密码，连接出错。';
$messages['internalerror'] = '无法保存新密码。';
$messages['passwordshort'] = '密码至少为 $length 位。';
$messages['passwordweak'] = '密码必须至少包含一个数字和一个标点符号。';
$messages['passwordforbidden'] = '密码包含禁止使用的字符。';
$messages['firstloginchange'] = '这是你第一次登录邮箱，请修改邮箱密码。';
$messages['disablednotice'] = '系统目前正在维护，密码修改功能无法使用。我们会很快回来，给您带来不便深表歉意。';
$messages['passwinhistory'] = '此密码以前使用过。';
$messages['samepasswd'] = '新密码不能与旧密码相同。';
$messages['passwdexpirewarning'] = '警告！您的密码即将过期，请在 $expirationdatetime 前更改。';
$messages['passwdexpired'] = '您的密码已过期，您现在必须更改！';
$messages['passwdconstraintviolation'] = '密码约束违规。密码可能太弱了。';

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
