<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = '오류 발생!';
$messages['loginfailed']  = '로그인이 실패하였습니다.';
$messages['cookiesdisabled'] = '브라우저가 쿠키를 지원하지 않습니다.';
$messages['sessionerror'] = '세션이 유효하지 않거나 종료되었습니다.';
$messages['storageerror'] = 'IMAP 서버 연결 실패';
$messages['servererror'] = '서버 오류!';
$messages['servererrormsg'] = '서버 오류: $msg';
$messages['dberror'] = '데이터베이스 오류!';
$messages['requesttimedout'] = '요청 시간 초과';
$messages['errorreadonly'] = '명령을 수행할 수 없습니다. 읽기 전용 폴더임.';
$messages['errornoperm'] = '명령을 수행할 수 없습니다. 권한이 거부됨.';
$messages['erroroverquota'] = '작업을 수행할 수 없음. 여유 공간이 없음.';
$messages['erroroverquotadelete'] = '여유 공간이 없음. SHIFT-DEL을 이용하여 메시지를 삭제하세요.';
$messages['invalidrequest'] = '잘못된 요청! 데이터가 저장되지 않았음.';
$messages['invalidhost'] = '잘못된 서버 이름입니다.';
$messages['nomessagesfound'] = '이 메일함에 메일이 없습니다.';
$messages['loggedout'] = '세션을 성공적으로 종료하였습니다. 안녕히 가세요!';
$messages['mailboxempty'] = '메일함이 비어있습니다.';
$messages['refreshing'] = '새로 고치는 중 ...';
$messages['loading'] = '불러오는 중...';
$messages['uploading'] = '파일을 업로드하는 중...';
$messages['uploadingmany'] = '파일을 업로드하는 중...';
$messages['loadingdata'] = '데이터를 불러오는 중...';
$messages['checkingmail'] = '새 메시지를 확인하는 중...';
$messages['sendingmessage'] = '메시지를 보내는 중...';
$messages['messagesent'] = '메시지를 성공적으로 보냈습니다.';
$messages['savingmessage'] = '메시지를 저장하는 중...';
$messages['messagesaved'] = '메시지가 임시 보관함에 저장되었습니다.';
$messages['successfullysaved'] = '성공적으로 저장됨.';
$messages['addedsuccessfully'] = '연락처가 주소록에 성공적으로 추가되었습니다.';
$messages['contactexists'] = '연락처에 동일한 이메일 주소가 이미 존재합니다.';
$messages['contactnameexists'] = '연락처에 동일한 이름이 이미 존재합니다.';
$messages['blockedimages'] = '보안을 위해, 이 메시지의 외부 이미지를 차단하였습니다.';
$messages['encryptedmessage'] = '이것은 암호화된 메시지며 표시할 수 없습니다. 죄송합니다!';
$messages['nocontactsfound'] = '연락처를 찾을 수 없음.';
$messages['contactnotfound'] = '요청한 연락처를 찾을 수 없었습니다.';
$messages['contactsearchonly'] = '연락처를 찾기 위해 일부 검색용어를 입력하세요.';
$messages['sendingfailed'] = '메시지 보내기를 실패하였음.';
$messages['senttooquickly'] = '이 메시지를 보내기 전에 $sec초 동안 기다리세요.';
$messages['errorsavingsent'] = '보낸 메시지를 저장하는 동안 오류가 발생했습니다.';
$messages['errorsaving'] = '저장 중 오류가 발생했습니다.';
$messages['errormoving'] = '메시지를 이동할 수 없었음.';
$messages['errorcopying'] = '메시지를 복사할 수 없었음.';
$messages['errordeleting'] = '메시지를 삭제할 수 없었음.';
$messages['errormarking'] = '메시지를 표시할 수 없었음.';
$messages['deletecontactconfirm']  = '정말 선택한 연락처를 삭제하시겠습니까?';
$messages['deletegroupconfirm']  = '정말로 선택한 그룹을 삭제하시겠습니까?';
$messages['deletemessagesconfirm'] = '정말로 선택한 메시지를 삭제하시겠습니까?';
$messages['deletefolderconfirm']  = '정말로 이 폴더를 삭제하시겠습니까?';
$messages['purgefolderconfirm']  = '정말로 이 폴더의 모든 메시지를 삭제하시겠습니까?';
$messages['contactdeleting'] = '연락처 삭제 중...';
$messages['groupdeleting'] = '그룹 삭제 중...';
$messages['folderdeleting'] = '폴더를 삭제하는 중...';
$messages['foldermoving'] = '폴더를 이동하는 중...';
$messages['foldersubscribing'] = '폴더를 구독하는 중...';
$messages['folderunsubscribing'] = '폴더의 구독을 취소하는 중...';
$messages['formincomplete'] = '양식을 완전히 작성되지 않았습니다.';
$messages['noemailwarning'] = '유효한 이메일 주소를 입력하시기 바랍니다.';
$messages['nonamewarning']  = '이름을 입력하시기 바랍니다.';
$messages['nopagesizewarning'] = '페이지 크기를 입력하시기 바랍니다.';
$messages['nosenderwarning'] = '발신인 이메일 주소를 입력하시기 바랍니다.';
$messages['norecipientwarning'] = '적어도 한 개 이상의 수신인 주소를 입력하시기 바랍니다.';
$messages['nosubjectwarning']  = '"제목" 필드가 비어있습니다. 지금 입력하시겠습니까?';
$messages['nobodywarning'] = '텍스트가 없는 메시지를 보내시겠습니까?';
$messages['notsentwarning'] = '메시지를 보내지 못했습니다. 메시지를 폐기하시겠습니까?';
$messages['noldapserver'] = '검색할 LDAP 서버를 선택하시기 바랍니다.';
$messages['nosearchname'] = '연락처명이나 이메일 주소를 입력하시기 바랍니다.';
$messages['notuploadedwarning'] = '아직 모든 첨부가 업로드되지 않았습니다. 기다리거나 업로드를 취소하시기 바랍니다.';
$messages['searchsuccessful'] = '$nr개의 메시지를 찾음.';
$messages['contactsearchsuccessful'] = '$nr개의 연락처를 찾음.';
$messages['searchnomatch'] = '검색이 일치 항목을 찾지 못함.';
$messages['searching'] = '검색 중...';
$messages['checking'] = '확인 중...';
$messages['nospellerrors'] = '맞춤법 오류를 찾지 못함.';
$messages['folderdeleted'] = '폴더를 성공적으로 삭제함.';
$messages['foldersubscribed'] = '폴더를 성공적으로 구독함.';
$messages['folderunsubscribed'] = '폴더를 성공적으로 구독 취소함.';
$messages['folderpurged'] = '폴더를 성공적으로 비웠습니다.';
$messages['folderexpunged'] = '폴더를 성공적으로 압축하였습니다.';
$messages['deletedsuccessfully'] = '성공적으로 삭제함.';
$messages['converting'] = '서식 설정 삭제 중...';
$messages['messageopenerror'] = '서버에서 메시지를 불러올 수 없음.';
$messages['fileuploaderror'] = '파일 업로드를 실패함.';
$messages['filesizeerror'] = '업로드된 파일이 최대 크기인 $size를 초과하였습니다.';
$messages['copysuccess'] = '$nr개의 주소를 성공적으로 복사함.';
$messages['copyerror'] = '모든 주소를 복사할 수 없음.';
$messages['sourceisreadonly'] = '이 주소의 소스는 읽기 전용입니다.';
$messages['errorsavingcontact'] = '연락처의 주소를 저장할 수 없음.';
$messages['movingmessage'] = '메시지를 이동하는 중...';
$messages['copyingmessage'] = '메시지 복사하는 중...';
$messages['copyingcontact'] = '연락처 복사하는 중...';
$messages['deletingmessage'] = '메시지 삭제하는 중...';
$messages['markingmessage'] = '메시지에 표시하는 중...';
$messages['addingmember'] = '그룹에 연락처를 추가하는 중...';
$messages['removingmember'] = '그룹에서 연락처를 삭제하는 중...';
$messages['receiptsent'] = '읽음 확인을 보냄.';
$messages['errorsendingreceipt'] = '읽음 확인을 보낼 수 없음.';
$messages['deleteidentityconfirm'] = '이 항목을 정말로 삭제 하시겠습니까?';
$messages['nodeletelastidentity'] = '이 신원은 마지막 하나이므로 삭제할 수 없습니다.';
$messages['forbiddencharacter'] = '폴더명에 금지된 문자가 포함되어 있습니다.';
$messages['selectimportfile'] = '업로드할 파일을 선택해주시기 바랍니다.';
$messages['addresswriterror'] = '선택된 주소록은 쓰기가 불가능합니다.';
$messages['contactaddedtogroup'] = '이 그룹에 연락처를 성공적으로 추가함.';
$messages['contactremovedfromgroup'] = '이 그룹에서 연락처를 성공적으로 제거함.';
$messages['nogroupassignmentschanged'] = '그룹 할당이 변경되지 않음.';
$messages['importwait'] = '가져오는 중, 기다려주시기 바랍니다...';
$messages['importformaterror'] = '가져오기를 실패함! 업로드된 파일은 유효하지 않은 가져오기 데이터 파일입니다.';
$messages['importconfirm'] = '<b>$inserted 연락처를 성공적으로 가져옴<b>';
$messages['importconfirmskipped'] = '<b>기존 기재사항인 $skipped을(를) 건너뜀</b>';
$messages['opnotpermitted'] = '작업이 허가되지 않음!';
$messages['nofromaddress'] = '선택된 신원에 이메일 주소 가 누락됨.';
$messages['editorwarning'] = '일반 텍스트 편집기로 바꾸면 모든 텍스트 서식이 사라집니다. 계속하시겠습니까?';
$messages['httpreceivedencrypterror'] = '치명적인 구성 오류가 발생하였습니다. 즉시 관리자에게 연락하세요.
<b>메시지를 전송할 수 없습니다.</b>';
$messages['smtpconnerror'] = 'SMTP 오류 ($code): 서버와의 연결을 실패함.';
$messages['smtpautherror'] = 'SMTP 에러 ($code): 인증을 실패함.';
$messages['smtpfromerror'] = 'SMTP 오류 ($code): 발신인을 "$from" ($msg)로 설정하는데 실패함.';
$messages['smtptoerror'] = 'SMTP 오류 ($code): 수신인을 "$to" ($msg)에 추가하는데 실패함.';
$messages['smtprecipientserror'] = 'SMTP 오류: 수신인 목록을 분석(parse)할 수 없음.';
$messages['smtperror'] = 'SMTP 오류: $msg';
$messages['emailformaterror'] = '유효하지 않은 이메일 주소: $email';
$messages['toomanyrecipients'] = '수신인이 너무 많음. 수신인 수를 $max명으로 줄이세요.';
$messages['maxgroupmembersreached'] = '그룹 구성원의 수가 최대 인원인 $max명을 초과합니다.';
$messages['internalerror'] = '내부 오류가 발생함. 재시도 해주시기 바랍니다.';
$messages['contactdelerror'] = '연락처를 삭제할 수 없음.';
$messages['contactdeleted'] = '연락처가 성공적으로 삭제됨.';
$messages['contactrestoreerror'] = '삭제된 연락처를 복구할 수 없음.';
$messages['contactrestored'] = '연락처가 성공적으로 복구됨.';
$messages['groupdeleted'] = '그룹이 성공적으로 삭제됨.';
$messages['grouprenamed'] = '그룹명이 성공적으로 변경됨.';
$messages['groupcreated'] = '그룹이 성공적으로 생성됨.';
$messages['savedsearchdeleted'] = '저장된 검색이 성공적으로 삭제됨.';
$messages['savedsearchdeleteerror'] = '저장된 정보를 삭제할 수 없었음.';
$messages['savedsearchcreated'] = '저장된 검색이 성공적으로 생성됨.';
$messages['savedsearchcreateerror'] = '저장된 검색을 생성할 수 없었음.';
$messages['messagedeleted'] = '메시지가 성공적으로 삭제됨.';
$messages['messagemoved'] = '메시지가 성공적으로 이동됨.';
$messages['messagecopied'] = '메시지가 성공적으로 복사됨.';
$messages['messagemarked'] = '메시지에 성공적으로 표시됨.';
$messages['autocompletechars'] = '자동 완성을 위해서 적어도 $min개의 문자를 입력하세요.';
$messages['autocompletemore'] = '더 많은 일치 항목이 발견됨. 더 많은 문자를 입력해주시기 바랍니다.';
$messages['namecannotbeempty'] = '이름을 비워둘 수 없습니다.';
$messages['nametoolong'] = '이름이 너무 깁니다.';
$messages['folderupdated'] = '폴더가 성공적으로 업데이트됨.';
$messages['foldercreated'] = '폴더가 성공적으로 생성됨.';
$messages['invalidimageformat'] = '유효한 이미지 형식이 아님.';
$messages['mispellingsfound'] = '메시지에서 맞춤법 오류가 감지되었음.';
$messages['parentnotwritable'] = '선택한 상위 폴더에 폴더를 생성/이동할 수 없음. 접근 권한이 없음.';
$messages['messagetoobig'] = '메시지 부분이 처리하기에는 너무 큽니다.';
$messages['attachmentvalidationerror'] = '경고! 이 첨부 파일의 유형이 메시지에서 선언된 유형과 일치하지 않기 때문에 의심됩니다. 발신인을 신뢰할 수 없을 경우, 악성 코드를 포함할 수도 있기 때문에 브라우저에서 열지 않는 것이 좋습니다. <br/><br/><em> 예상한 유형: $expected; 발견된 유형: $found</ em>';
$messages['noscriptwarning'] = '경고: 이 웹 메일 서비스는 자바스크립트가 필요합니다! 이 기능을 사용하시려면 자바스크립트를 브라우저 설정에서 활성화하시기 바랍니다.';

?>
