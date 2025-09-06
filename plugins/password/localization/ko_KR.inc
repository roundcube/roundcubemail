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

$labels['changepasswd']  = '암호 변경';
$labels['curpasswd']  = '현재 암호:';
$labels['newpasswd']  = '새로운 암호:';
$labels['confpasswd']  = '새로운 암호 확인:';
$messages['nopassword'] = '새로운 암호를 입력하세요.';
$messages['nocurpassword'] = '현재 사용 중인 암호를 입력해주세요.';
$messages['passwordincorrect'] = '현재 사용 중인 암호가 올바르지 않습니다.';
$messages['passwordinconsistency'] = '암호가 일치하지 않습니다. 다시 시도해주세요.';
$messages['crypterror'] = '새로운 암호를 저장할 수 없습니다. 암호화 기능이 누락되었습니다.';
$messages['connecterror'] = '새로운 암호를 저장할 수 없습니다. 연결 오류입니다.';
$messages['internalerror'] = '새로운 암호를 저장할 수 없습니다.';
$messages['passwordshort'] = '암호는 문자가 $length개 이상이어야 합니다.';
$messages['passwordweak'] = '암호는 숫자 및 특수문자를 각각 한 개 이상 포함해야 합니다.';
$messages['passwordtooweak'] = '암호가 너무 취약합니다.';
$messages['passwordnoseq'] = '암호는 123456 또는 QWERTY 같은 연속된 문구가 아니어야 합니다.';
$messages['passwordnocommon'] = '암호는 자주 쓰이는 단어 또는 이름을 포함할 수 없습니다.';
$messages['passwordforbidden'] = '암호가 금지된 문자을 포함하고 있습니다.';
$messages['firstloginchange'] = '처음 로그인하셨습니다. 암호를 변경해주세요.';
$messages['disablednotice'] = '시스템이 현재 유지보수 중이며 암호 변경이 일시적으로 불가능합니다. 모든 것이 조만간 정상화 될 예정입니다. 불편을 끼쳐드려 죄송합니다.';
$messages['passwinhistory'] = '이 암호는 이미 이전에 사용됐습니다.';
$messages['samepasswd'] = '새로운 암호는 예전 암호와 달라야 합니다.';
$messages['passwdexpirewarning'] = '경고! 암호가 곧 만료됩니다. $expirationdatetime 이전에 변경하십시오.';
$messages['passwdexpired'] = '암호가 만료됐습니다. 지금 변경하셔야 합니다!';
$messages['passwdconstraintviolation'] = '암호 제약 위반. 암호가 취약한 것 같습니다.';
$messages['pwned_mustnotbedisclosed'] = '<a href="$href" target="_blank">흔히 알려지지</a> 않은 암호여야 합니다.';
$messages['pwned_isdisclosed'] = '이것은 흔히 알려진 암호입니다.';
$messages['pwned_fetcherror'] = '암호 복잡성을 검증하는데 실패함.';

$labels['hestia_err_policy_short'] = '비밀번호가 너무 짧습니다(최소 길이: %분).';
$labels['hestia_err_endpoint_invalid'] = '헤스티아 엔드포인트가 구성되지 않았거나 유효하지 않습니다.';
$labels['hestia_err_identity'] = '사용자 아이디를 local@domain%hint로 구문 분석할 수 없습니다.';
$labels['hestia_err_owner_unknown'] = '도메인의 도메인 소유자를 확인할 수 없습니다. %d';
$labels['hestia_err_unknown'] = '헤스티아 API에서 알 수 없는 오류가 발생했습니다.';
$labels['hestia_err_default'] = '헤스티아가 오류 코드 %코드를 반환했습니다.';
$labels['hestia_err_1'] = '인수가 충분하지 않습니다.';
$labels['hestia_err_2'] = '잘못된 객체 또는 인수입니다.';
$labels['hestia_err_3'] = '개체가 존재하지 않습니다(사서함 또는 도메인을 찾을 수 없음).';
$labels['hestia_err_4'] = '개체가 이미 존재합니다.';
$labels['hestia_err_5'] = '개체가 이미 일시 중단되었습니다.';
$labels['hestia_err_6'] = '개체가 이미 일시 중단되었습니다.';
$labels['hestia_err_7'] = '사용 중인 객체, 수정할 수 없습니다.';
$labels['hestia_err_8'] = '패키지 제한으로 인해 작업이 차단되었습니다.';
$labels['hestia_err_9'] = '비밀번호가 잘못되었거나 유효하지 않습니다.';
$labels['hestia_err_10'] = '헤스티아에 의해 권한이 거부되었습니다(API ID에 권한이 없거나 허용되지 않는 IP).';
$labels['hestia_err_11'] = '시스템 정책에 의해 기능이 비활성화되었습니다.';
$labels['hestia_err_12'] = '구문 분석 오류입니다.';
$labels['hestia_err_13'] = '디스크 오류입니다.';
$labels['hestia_err_14'] = '서버 부하가 너무 높습니다.';
$labels['hestia_err_15'] = '연결 오류입니다.';
$labels['hestia_err_16'] = 'FTP 오류.';
$labels['hestia_err_17'] = '데이터베이스 오류입니다.';
$labels['hestia_err_18'] = 'RRD 오류.';
$labels['hestia_err_19'] = '업데이트 오류입니다.';
$labels['hestia_err_20'] = '다시 시작 오류가 발생했습니다.';
?>
