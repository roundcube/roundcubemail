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
$labels['changepasswd']  = '암호 변경';
$labels['curpasswd']  = '현재 암호:';
$labels['newpasswd']  = '새 암호:';
$labels['confpasswd']  = '새로운 비밀번호 확인 :';
$messages['nopassword'] = '새 암호를 입력하시오.';
$messages['nocurpassword'] = '현재 사용중인 암호를 입력하세요.';
$messages['passwordincorrect'] = '현재 사용중인 암호가 올바르지 않습니다.';
$messages['passwordinconsistency'] = '암호가 일치하지 않습니다. 다시 시도하기 바랍니다.';
$messages['crypterror'] = '새로운 암호를 저장할 수 없습니다. 암호화 실패.';
$messages['connecterror'] = '새로운 암호를 저장할 수 없습니다. 연결 오류.';
$messages['internalerror'] = '새로운 암호를 저장할 수 없습니다.';
$messages['passwordshort'] = '암호는 적어도 $length 글자 이상이어야 합니다.';
$messages['passwordweak'] = '암호는 적어도 숫자 하나와 특수 문자 하나를 포함하여야 합니다.';
$messages['passwordforbidden'] = '암호가 허락되지 않은 문자들을 포함하고 있습니다.';
?>
