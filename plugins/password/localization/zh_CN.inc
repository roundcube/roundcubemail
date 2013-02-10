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

$labels = array();
$labels['changepasswd']  = '修改密码';
$labels['curpasswd']  = '当前密码：';
$labels['newpasswd']  = '新密码：';
$labels['confpasswd']  = '确认新密码：';

$messages = array();
$messages['nopassword'] = '请输入新密码。';
$messages['nocurpassword'] = '请输入正确的密码。';
$messages['passwordincorrect'] = '当前密码不正确。';
$messages['passwordinconsistency'] = '两次输入的密码不一致，请重试。';
$messages['crypterror'] = '无法保存新密码，因为加密功能不可用。';
$messages['connecterror'] = '无法保存新密码，因为连接出错。';
$messages['internalerror'] = '无法保存新密码。';
$messages['passwordshort'] = '密码必须至少为 $length 位。';
$messages['passwordweak'] = '密码必须至少包含一个数字和一个标点符号。';
$messages['passwordforbidden'] = '密码包含禁止使用的字符。';

?>
