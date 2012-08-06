<?php

/*
 +-----------------------------------------------------------------------+
 | localization/ko_KR/messages.inc                                       |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Kim, Woohyun <woohyun.kim@gmail.com>                          |
 +-----------------------------------------------------------------------+
*/

$messages = array();
$messages['errortitle'] = '에러가 발생!';
$messages['loginfailed'] = '로그인이 실패하였습니다.';
$messages['cookiesdisabled'] = '브라우저가 쿠키를 지원하지 않습니다';
$messages['sessionerror'] = '세션이 유효하지 않거나 종료되었습니다';
$messages['storageerror'] = 'IMAP 서버 연결 실패';
$messages['servererror'] = '서버 에러!';
$messages['servererrormsg'] = '서버 에러: $msg';
$messages['dberror'] = '데이터베이스 에러!';
$messages['requesttimedout'] = '요청 시간 초과';
$messages['errorreadonly'] = '명령을 수행할 수 없습니다. 읽기전용 폴더.';
$messages['errornoperm'] = '명령을 수행할 수 없습니다. 권한이 거부 됨.';
$messages['invalidrequest'] = '잘못된 요청! 데이터는 저장되지 않았음.';
$messages['nomessagesfound'] = '이 메일함에 메일이 없습니다';
$messages['loggedout'] = '세션을 성공적으로 종료하였습니다. 안녕히 가십시오!';
$messages['mailboxempty'] = '메일함이 비어있습니다';
$messages['loading'] = '불러오는 중...';
$messages['uploading'] = '하나의 파일 올리기...';
$messages['uploadingmany'] = '여러 파일 올리기...';
$messages['loadingdata'] = '데이터 불러오는 중...';
$messages['checkingmail'] = '새 메일 확인 중...';
$messages['sendingmessage'] = '메일 보내는 중...';
$messages['messagesent'] = '메일을 성공적으로 보냈습니다';
$messages['savingmessage'] = '메일 저장 중...';
$messages['messagesaved'] = '메일이 드래프트 폴더에 저장되었습니다';
$messages['successfullysaved'] = '성공적으로 저장';
$messages['addedsuccessfully'] = '연락처가 주소록에 성공적으로 추가되었습니다';
$messages['contactexists'] = '이 메일로 지정된 연락처가 이미 있습니다';
$messages['contactnameexists'] = '이미 존재하는 것과 동일한 이름의 연락처.';
$messages['blockedimages'] = '보안 유지를 위해, 이 메일의 외부 이미지를 차단하였습니다';
$messages['encryptedmessage'] = '이 메일은 암호화되어 볼 수 없습니다. 죄송합니다!';
$messages['nocontactsfound'] = '연락처 없음';
$messages['contactnotfound'] = '요청한 연락처가 없습니다';
$messages['contactsearchonly'] = '연락처를 찾기 위해 검색 항목을 입력하세요.';
$messages['sendingfailed'] = '메일을 보내지 못했습니다';
$messages['senttooquickly'] = '이 메시지를 보내기 전에 $sec 초 동안 기다리십시오.';
$messages['errorsavingsent'] = '보낸 메시지를 저장할 때 에러가 발생했습니다.';
$messages['errorsaving'] = '저장 중 에러가 발생했습니다';
$messages['errormoving'] = '메일을 옮길 수 없습니다';
$messages['errorcopying'] = '메시지(들)를 복사할 수 없습니다.';
$messages['errordeleting'] = '메일을 지울 수 없습니다';
$messages['errormarking'] = '메시지(들)에 표시할 수 없습니다.';
$messages['deletecontactconfirm'] = '정말 선택한 연락처들을 삭제하시겠습니까?';
$messages['deletegroupconfirm'] = '정말로 선택한 그룹을 삭제하는 것을 원하십니까?';
$messages['deletemessagesconfirm'] = '정말 선택한 메일들을 삭제하시겠습니까?';
$messages['deletefolderconfirm'] = '정말 이 폴더를 삭제하시겠습니까?';
$messages['purgefolderconfirm'] = '정말 이 폴더의 모든 메일을 삭제하시겠습니까?';
$messages['contactdeleting'] = '연락처(들) 삭제 중...';
$messages['groupdeleting'] = '그룹 삭제 중...';
$messages['folderdeleting'] = '폴더 지우는 중...';
$messages['foldermoving'] = '폴더 옮기는 중...';
$messages['foldersubscribing'] = '폴더의 구독을 활성화 하는 중...';
$messages['folderunsubscribing'] = '폴더의 구독을 취소하는 중...';
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
$messages['nosearchname'] = '연락처 이름이나 메일 주소를 넣으세요';
$messages['notuploadedwarning'] = '아직 모든 첨부가 업로드되지 않았습니다. 기다리거나 업로드를 취소하기 바랍니다.';
$messages['searchsuccessful'] = '$nr개의 메일 발견';
$messages['contactsearchsuccessful'] = '$nr 연락처 발견됨.';
$messages['searchnomatch'] = '검색 결과가 없습니다';
$messages['searching'] = '검색 중...';
$messages['checking'] = '체크 중...';
$messages['nospellerrors'] = '스펠링 에러가 없습니다';
$messages['folderdeleted'] = '폴더를 성공적으로 삭제하였습니다';
$messages['foldersubscribed'] = '폴더가 성공적으로 구독 됨.';
$messages['folderunsubscribed'] = '폴더가 성공적으로 구독 취소 됨.';
$messages['folderpurged'] = '폴더가 성공적으로 비워졌습니다.';
$messages['folderexpunged'] = '폴더가 성공적으로 압축 되었습니다.';
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
$messages['copyingmessage'] = '메시지(들) 복사 중...';
$messages['copyingcontact'] = '연락처(들) 복사 중...';
$messages['deletingmessage'] = '메시지(들) 삭제 중...';
$messages['markingmessage'] = '메시지(들)에 표시 중...';
$messages['addingmember'] = '그룹에 연락처(들)을 추가 중...';
$messages['removingmember'] = '그룹으로 부터 연락처를 삭제 중...';
$messages['receiptsent'] = '메일 읽음 확인을 보냈습니다';
$messages['errorsendingreceipt'] = '읽음 확인을 보낼 수 없습니다';
$messages['nodeletelastidentity'] = '이 정보는 마지막 남은 것이므로 지울 수 없습니다';
$messages['forbiddencharacter'] = '폴더 이름에 쓸 수 없는 문자가 포함되어 있습니다';
$messages['selectimportfile'] = '업로드할 파일을 선택하세요';
$messages['addresswriterror'] = '선택한 주소록은 쓸 수 없습니다';
$messages['contactaddedtogroup'] = '이 그룹에 연락처를 성공적으로 추가 함.';
$messages['contactremovedfromgroup'] = '이 그룹으로 부터 연락처를 성공적으로 제거 함.';
$messages['importwait'] = '가져오는 중, 기다리세요...';
$messages['importerror'] = '가져오기 실패! 업로드한 파일이 유효한 vCard 파일이 아닙니다.';
$messages['importconfirm'] = '<b>$inserted 연락처를 성공적으로 가져왔고, $skipped 존재하는 항목은 건너뛰었습니다.</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = '작업이 허가되지 않았습니다!';
$messages['nofromaddress'] = '선택한 신원에 이메일 주소가 없음';
$messages['editorwarning'] = '일반 텍스트 편집기로 바꾸면 모든 서식이 사라집니다. 계속할까요?';
$messages['httpreceivedencrypterror'] = '치명적인 설정 오류가 발생 되었음. 관리자에게 바로 연락을 취하세요.<b>메시지를 전송할 수 없습니다.</b>';
$messages['smtpconnerror'] = 'SMTP 에러 ($code): 서버로의 연결이 실패 됨.';
$messages['smtpautherror'] = 'SMTP 에러 ($code): 인증이 실패 됨.';
$messages['smtpfromerror'] = 'SMTP 에러 ($code): 보낸 사람을 "$from"로 설정하는 것이 실패 됨 ($msg).';
$messages['smtptoerror'] = 'SMTP 에러 ($code): 수신자 "$to"를 추가하는 데 실패 함 ($msg).';
$messages['smtprecipientserror'] = 'SMTP 에러: 수신자 목록을 분석(parse)할 수 없음.';
$messages['smtperror'] = 'SMTP 에러: $msg';
$messages['emailformaterror'] = '잘못된 e-mail 주소: $email';
$messages['toomanyrecipients'] = '수신자가 너무 많습니다. 수신자의 수를 $max로 줄이세요.';
$messages['maxgroupmembersreached'] = '그룹 구성원의 수가 최대 인원 $max를 초과하였습니다.';
$messages['internalerror'] = '내부 오류가 발생하였습니다. 다시 시도하기 바랍니다.';
$messages['contactdelerror'] = '연락처(들)을 지울 수 없습니다.';
$messages['contactdeleted'] = '연락처(들)이 성공적으로 제거 됨.';
$messages['contactrestoreerror'] = '삭제된 연락처(들)을 복구할 수 없음.';
$messages['contactrestored'] = '연락처(들)이 성공적으로 복구 됨.';
$messages['groupdeleted'] = '그룹이 성공적으로 삭제 됨.';
$messages['grouprenamed'] = '그룹명이 성공적으로 변경 됨.';
$messages['groupcreated'] = '그룹이 성공적으로 생성 됨.';
$messages['messagedeleted'] = '메시지(들)이 성공적으로 삭제 됨.';
$messages['messagemoved'] = '메시지(들)을 성공적으로 이동 함.';
$messages['messagecopied'] = '메시지(들)을 성공적으로 복사 함.';
$messages['messagemarked'] = '메시지(들)에 성공적으로 표시 함.';
$messages['autocompletechars'] = '자동 완성을 위해서 적어도 $min 글자를 입력하세요.';
$messages['namecannotbeempty'] = '이름이 비워져서는 안됩니다.';
$messages['nametoolong'] = '이름이 너무 깁니다.';
$messages['folderupdated'] = '폴더를 성공적으로 갱신 함.';
$messages['foldercreated'] = '폴더를 성공적으로 생성 함.';
$messages['invalidimageformat'] = '올바른 이미지 포멧이 아님.';
$messages['mispellingsfound'] = '메시지에서 맞춤법 오류를 감지 하였음.';
$messages['parentnotwritable'] = '선택한 부모 폴더에 폴더를 생성하거나 이동할 수 없음. 접근 권한이 없음.';

