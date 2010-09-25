<?php

/*

+-----------------------------------------------------------------------+
| localization/kr/messages.inc                                            |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: lordmiss <lordmiss@lordmiss.com>                              |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages['loginfailed'] = '로그인 실패';
$messages['loginfailed'] = '로그인 실패';
$messages['cookiesdisabled'] = '브라우저가 쿠키를 지원하지 않습니다';
$messages['sessionerror'] = '세션이 유효하지 않거나 종료되었습니다';
$messages['imaperror'] = 'IMAP 서버 연결 실패';
$messages['nomessagesfound'] = '이 메일함에 메일이 없습니다';
$messages['loggedout'] = '세션을 성공적으로 종료하였습니다. 안녕히 가십시오!';
$messages['mailboxempty'] = '메일함이 비어있습니다';
$messages['loading'] = '불러오는 중...';
$messages['loadingdata'] = '데이터 불러오는 중...';
$messages['checkingmail'] = '새 메일 확인 중...';
$messages['sendingmessage'] = '메일 보내는 중...';
$messages['messagesent'] = '메일을 성공적으로 보냈습니다';
$messages['savingmessage'] = '메일 저장 중...';
$messages['messagesaved'] = '메일이 드래프트 폴더에 저장되었습니다';
$messages['successfullysaved'] = '성공적으로 저장';
$messages['addedsuccessfully'] = '연락처가 주소록에 성공적으로 추가되었습니다';
$messages['contactexists'] = '이 메일로 지정된 연락처가 이미 있습니다';
$messages['blockedimages'] = '보안 유지를 위해, 이 메일의 외부 이미지를 차단하였습니다';
$messages['encryptedmessage'] = '이 메일은 암호화되어 볼 수 없습니다. 죄송합니다!';
$messages['nocontactsfound'] = '연락처 없음';
$messages['contactnotfound'] = '요청한 연락처가 없습니다';
$messages['sendingfailed'] = '메일을 보내지 못했습니다';
$messages['senttooquickly'] = '이 메시지를 보내기 전에 $sec 초 동안 기다리십시오.';
$messages['errorsavingsent'] = '보낸 메시지를 저장할 때 에러가 발생했습니다.';
$messages['errorsaving'] = '저장 중 에러가 발생했습니다';
$messages['errormoving'] = '메일을 옮길 수 없습니다';
$messages['errordeleting'] = '메일을 지울 수 없습니다';
$messages['deletecontactconfirm'] = '정말 선택한 연락처들을 삭제하시겠습니까?';
$messages['deletemessagesconfirm'] = '정말 선택한 메일들을 삭제하시겠습니까?';
$messages['deletefolderconfirm'] = '정말 이 폴더를 삭제하시겠습니까?';
$messages['purgefolderconfirm'] = '정말 이 폴더의 모든 메일을 삭제하시겠습니까?';
$messages['foldercreating'] = '폴더 만드는 중...';
$messages['folderdeleting'] = '폴더 지우는 중...';
$messages['folderrenaming'] = '폴더 이름 바꾸는 중...';
$messages['foldermoving'] = '폴더 옮기는 중...';
$messages['formincomplete'] = '양식을 완전히 채우지 않았습니다';
$messages['noemailwarning'] = '유효한 이메일 주소를 넣어주십시오';
$messages['nonamewarning'] = '이름을 넣어주십시오';
$messages['nopagesizewarning'] = '페이지 크기를 넣어주십시오';
$messages['nosenderwarning'] = '발신자 이메일 주소를 넣어주십시오';
$messages['norecipientwarning'] = '적어도 한 개 이상의 수신 주소를 넣어주십시오';
$messages['nosubjectwarning'] = '"제목"이 없습니다. 제목을 넣으시겠습니까?';
$messages['nobodywarning'] = '텍스트 없이 메일을 보낼까요?';
$messages['notsentwarning'] = '메일을 보내지 않았습니다. 메일을 버리시겠습니까?';
$messages['noldapserver'] = '검색할 LDAP 서버를 선택하세요';
$messages['nocontactsreturned'] = '연락처가 없습니다';
$messages['nosearchname'] = '연락처 이름이나 메일 주소를 넣으세요';
$messages['searchsuccessful'] = '$nr개의 메일 발견';
$messages['searchnomatch'] = '검색 결과가 없습니다';
$messages['searching'] = '검색 중...';
$messages['checking'] = '체크 중...';
$messages['nospellerrors'] = '스펠링 에러가 없습니다';
$messages['folderdeleted'] = '폴더를 성공적으로 삭제하였습니다';
$messages['deletedsuccessfully'] = '성공적으로 삭제하였습니다';
$messages['converting'] = '메일의 포맷을 지우는 중...';
$messages['messageopenerror'] = '서버에서 메일을 가져올 수 없습니다';
$messages['fileuploaderror'] = '파일 업로드 실패';
$messages['filesizeerror'] = '올린 파일이 $size의 최대 크기를 초과했습니다';
$messages['copysuccess'] = '$nr개의 주소를 성공적으로 복사했습니다';
$messages['copyerror'] = '주소를 복사할 수 없습니다';
$messages['sourceisreadonly'] = '이 주소 소스는 읽기 전용입니다';
$messages['errorsavingcontact'] = '연락처 주소를 저장할 수 없습니다';
$messages['movingmessage'] = '메시지 이동 중...';
$messages['receiptsent'] = '메일 읽음 확인을 보냈습니다';
$messages['errorsendingreceipt'] = '읽음 확인을 보낼 수 없습니다';
$messages['nodeletelastidentity'] = '이 정보는 마지막 남은 것이므로 지울 수 없습니다';
$messages['addsubfolderhint'] = '이 폴더는 현재 선택된 폴더의 서브 폴더로 만들어질 것입니다';
$messages['forbiddencharacter'] = '폴더 이름에 쓸 수 없는 문자가 포함되어 있습니다';
$messages['selectimportfile'] = '업로드할 파일을 선택하세요';
$messages['addresswriterror'] = '선택한 주소록은 쓸 수 없습니다';
$messages['importwait'] = '가져오는 중, 기다리세요...';
$messages['importerror'] = '가져오기 실패! 업로드한 파일이 유효한 vCard 파일이 아닙니다.';
$messages['importconfirm'] = '<b>$inserted 연락처를 성공적으로 가져왔고, $skipped 존재하는 항목은 건너뛰었습니다.</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = '작업이 허가되지 않았습니다!';
$messages['nofromaddress'] = '선택한 신원에 이메일 주소가 없음';
$messages['editorwarning'] = '일반 텍스트 편집기로 바꾸면 모든 서식이 사라집니다. 계속할까요?';

?>
