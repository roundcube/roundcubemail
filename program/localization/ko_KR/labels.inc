<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = '$product에 오신 것을 환영합니다';
$labels['username']  = '사용자명';
$labels['password']  = '비밀번호';
$labels['server']    = '서버';
$labels['login']     = '로그인';

// taskbar
$labels['logout']   = '로그아웃';
$labels['mail']     = '이메일';
$labels['settings'] = '개인 설정';
$labels['addressbook'] = '주소록';

// mailbox names
$labels['inbox']  = '받은편지함';
$labels['drafts'] = '임시저장';
$labels['sent']   = '보낸편지함';
$labels['trash']  = '휴지통';
$labels['junk']   = '정크메일';

// message listing
$labels['subject'] = '제목';
$labels['from']    = '보내는 사람';
$labels['sender']  = '보낸 사람';
$labels['to']      = '받는 사람';
$labels['cc']      = '참조';
$labels['bcc']     = '숨은 참조';
$labels['replyto'] = '회신 주소';
$labels['followupto'] = '추가 답장';
$labels['date']    = '날짜';
$labels['size']    = '크기';
$labels['priority'] = '우선순위';
$labels['organization'] = '기관';
$labels['readstatus'] = '보기 상태';
$labels['listoptions'] = '목록 옵션...';

$labels['mailboxlist'] = '폴더';
$labels['messagesfromto'] = '$count 개 중 $from 에서 $to 번 메시지';
$labels['threadsfromto'] = '$from 에서 $to 까지 $count 개의 쓰레드';
$labels['messagenrof'] = '$count 개 중 $nr 번 메시지';
$labels['fromtoshort'] = '$from 에서 $to 까지 $count 개';

$labels['copy']     = '참조';
$labels['move']     = '이동';
$labels['moveto']   = '이동...';
$labels['download'] = '다운로드';
$labels['showattachment'] = '표시';
$labels['showanyway'] = '항상 표시';

$labels['filename'] = '파일 이름';
$labels['filesize'] = '파일 크기';

$labels['addtoaddressbook'] = '주소록에 추가';

// weekdays short
$labels['sun'] = '일';
$labels['mon'] = '월';
$labels['tue'] = '화';
$labels['wed'] = '수';
$labels['thu'] = '목';
$labels['fri'] = '금';
$labels['sat'] = '토';

// weekdays long
$labels['sunday']    = '일요일';
$labels['monday']    = '월요일';
$labels['tuesday']   = '화요일';
$labels['wednesday'] = '수요일';
$labels['thursday']  = '목요일';
$labels['friday']    = '금요일';
$labels['saturday']  = '토요일';

// months short
$labels['jan']	= '1월';
$labels['feb']	= '2월';
$labels['mar']	= '3월';
$labels['apr']	= '4월';
$labels['may']	= '5월';
$labels['jun']	= '6월';
$labels['jul'] 	= '7월';
$labels['aug']	= '8월';
$labels['sep']	= '9월';
$labels['oct']	= '10월';
$labels['nov']	= '11월';
$labels['dec']	= '12월';

// months long
$labels['longjan']	= '1월';
$labels['longfeb']	= '2월';
$labels['longmar']	= '3월';
$labels['longapr']	= '4월';
$labels['longmay']	= '5월';
$labels['longjun']	= '6월';
$labels['longjul']	= '7월';
$labels['longaug']	= '8월';
$labels['longsep']	= '9월';
$labels['longoct']	= '10월';
$labels['longnov']	= '11월';
$labels['longdec']	= '12월';

$labels['today'] = '오늘';

// toolbar buttons
$labels['refresh']          = '새로 고침';
$labels['checkmail']        = '새 메일 확인';
$labels['compose']          = '메일 쓰기';
$labels['writenewmessage']  = '새 메일 작성';
$labels['reply']            = '회신';
$labels['replytomessage']   = '회신 작성';
$labels['replytoallmessage'] = '보낸이와 모든 수신자에게 회신';
$labels['replyall']         = '전체 회신';
$labels['replylist']        = '회신 목록';
$labels['forward']          = '전달';
$labels['forwardinline']    = '전달';
$labels['forwardattachment'] = '메일을 첨부파일로 전달';
$labels['forwardmessage']   = '메일 전달';
$labels['deletemessage']    = '메일 삭제';
$labels['movemessagetotrash'] = '메일 휴지통으로 이동';
$labels['printmessage']     = '메일 인쇄';
$labels['previousmessage']  = '이전 메일 보기';
$labels['firstmessage']     = '처음 메일 보기';
$labels['nextmessage']      = '다음 메일 보기';
$labels['lastmessage']      = '마지막 메일 보기';
$labels['backtolist']       = '메일 목록으로';
$labels['viewsource']       = '소스 보기';
$labels['mark']             = '표시';
$labels['markmessages']     = '메시지 표시';
$labels['markread']         = '읽은 것으로 표시';
$labels['markunread']       = '읽지 않은 것으로 표시';
$labels['markflagged']      = '깃발 달기';
$labels['markunflagged']    = '깃발 내리기';
$labels['moreactions']      = '이외의 동작...';
$labels['more']             = '더보기';
$labels['back']             = '뒤로';
$labels['options']          = '선택 사항';

$labels['select'] = '선택';
$labels['all'] = '모두';
$labels['none'] = '없음';
$labels['currpage'] = '현재 페이지';
$labels['unread'] = '읽지 않음';
$labels['flagged'] = '깃발';
$labels['unanswered'] = '답장하지 않음';
$labels['deleted'] = '삭제됨';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = '반전';
$labels['filter'] = '필터';
$labels['list'] = '리스트';
$labels['threads'] = '쓰레드';
$labels['expand-all'] = '모두 확장';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = '모두 접기';
$labels['threaded'] = '그룹화됨';

$labels['autoexpand_threads'] = '모든 메시지 확장';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'From/To';
$labels['flag'] = '표시';
$labels['attachment'] = '첨부';
$labels['nonesort'] = '없음';
$labels['sentdate'] = '보낸 날짜';
$labels['arrival'] = '수신 날짜';
$labels['asc'] = '오름차순';
$labels['desc'] = '내림차순';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = '정렬';
$labels['listorder'] = '정렬 순서';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = '폴더 명령';
$labels['compact'] = '간단하게';
$labels['empty'] = '비우기';

$labels['quota'] = '디스크 사용';
$labels['unknown']  = '알수 없음';
$labels['unlimited']  = '제한 없음';

$labels['quicksearch']  = '빨리 찾기';
$labels['resetsearch']  = '찾기 리셋';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = '전체 메시지';
$labels['body']  = 'Body';

$labels['openinextwin'] = '새 창에서 열기';
$labels['emlsave'] = '다운로드 (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = '메일 지금 보냄';
$labels['savemessage']    = '드래프트 저장';
$labels['addattachment']  = '파일 첨부';
$labels['charset']        = '문자셋';
$labels['editortype']     = '편집기 형태';
$labels['returnreceipt']  = '수신 확인 요청하기';
$labels['dsn']            = '전송상태 메일 받기';
$labels['mailreplyintro'] = '$date에, $sender가 작성:';
$labels['originalmessage'] = '원본 메세지';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = '맞춤법';
$labels['checkspelling'] = '스펠링 체크';
$labels['resumeediting'] = '편집 계속';
$labels['revertto']      = '돌아감';

$labels['attach'] = '첨부';
$labels['attachments'] = '첨부 파일';
$labels['upload'] = '업로드';
$labels['uploadprogress'] = '$percent ($total 에서 $current)';
$labels['close']  = '닫기';
$labels['messageoptions']  = '메시지 옵션(들)...';

$labels['low']     = '낮음';
$labels['lowest']  = '가장 낮음';
$labels['normal']  = '보통';
$labels['high']    = '높음';
$labels['highest'] = '가장 높음';

$labels['nosubject']  = '(제목 없음)';
$labels['showimages'] = '이미지 표시';
$labels['alwaysshow'] = '$sender 에서 온 이미지 항상 보기';
$labels['isdraft']    = '이 메시지는 임시 보관 메시지 입니다.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = '일반 텍스트';
$labels['savesentmessagein'] = '보낸 메시지를 다음에 저장';
$labels['dontsave'] = '저장하지 않음';
$labels['maxuploadsize'] = '최대 파일 크기는 $size 입니다';

$labels['addcc'] = '참조 넣기';
$labels['addbcc'] = '숨은참조 넣기';
$labels['addreplyto'] = '회신주소 넣기';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = '이 메시지를 보낸 분이 읽음 확인 요청을 했습니다. 확인할까요?';
$labels['receiptread'] = '읽음 확인 보내기';
$labels['yourmessage'] = '당신의 메시지를 읽었습니다.';
$labels['receiptnote'] = '주의: 이 확인은 이 메시지가 수신자의 컴퓨터에 표시된 것만을 의미합니다. 수신자가 반드시 이 메일을 읽거나 이해했다는 것은 아닙니다.';

// address boook
$labels['name']         = '표시 이름';
$labels['firstname']    = '이름';
$labels['surname']      = '성';
$labels['middlename']   = '가운데 이름';
$labels['nameprefix']   = '접두사';
$labels['namesuffix']   = '접미사';
$labels['nickname']     = '별명';
$labels['jobtitle']     = '직위';
$labels['department']   = '부서';
$labels['gender']       = '성별';
$labels['maidenname']   = '결혼 전 이름';
$labels['email']        = '이메일';
$labels['phone']        = '전화';
$labels['address']      = '주소';
$labels['street']       = '세부주소';
$labels['locality']     = '시/구/군';
$labels['zipcode']      = '우편번호';
$labels['region']       = '도';
$labels['country']      = '국가';
$labels['birthday']     = '생년월일';
$labels['anniversary']  = '기념일';
$labels['website']      = '웹사이트';
$labels['instantmessenger'] = '메신져';
$labels['notes'] = '노트';
$labels['male']   = '남성';
$labels['female'] = '여성';
$labels['manager'] = '관리자';
$labels['assistant'] = '도우미';
$labels['spouse'] = '배우자';
$labels['allfields'] = '친구 전체';
$labels['search'] = '검색';
$labels['advsearch'] = '상세 검색';
$labels['advanced'] = '고급 설정';
$labels['other'] = '그 외';

$labels['typehome']   = '홈';
$labels['typework']   = '직장';
$labels['typeother']  = '그 외';
$labels['typemobile']  = '휴대폰';
$labels['typemain']  = '주';
$labels['typehomefax']  = '팩스(집)';
$labels['typeworkfax']  = '팩스(직장)';
$labels['typecar']  = '자동차';
$labels['typepager']  = '호출기';
$labels['typevideo']  = '동영상';
$labels['typeassistant']  = '도우미';
$labels['typehomepage']  = '홈 페이지';
$labels['typeblog'] = '블로그';
$labels['typeprofile'] = '자기소개';

$labels['addfield'] = '항목 추가..';
$labels['addcontact'] = '새 연락처 추가';
$labels['editcontact'] = '연락처 편집';
$labels['contacts'] = '연락처';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = '개인 정보';

$labels['edit']   = '편집';
$labels['cancel'] = '취소';
$labels['save']   = '저장';
$labels['delete'] = '삭제';
$labels['rename'] = '이름 변경';
$labels['addphoto'] = '추가';
$labels['replacephoto'] = '바꾸기';
$labels['uploadphoto'] = '사진 올리기';

$labels['newcontact']     = '새 연락처 카드 만들기';
$labels['deletecontact']  = '선택한 연락처 삭제';
$labels['composeto']      = '새 메일 작성';
$labels['contactsfromto'] = '$from 에서 $to 로의 연락처 $count 개';
$labels['print']          = '인쇄';
$labels['export']         = '내보내기';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = '연락처를 vCard 포맷으로 내보내기';
$labels['newcontactgroup'] = '새로운 연락처 그룹 생성';
$labels['grouprename']    = '그룹 이름 바꾸기';
$labels['groupdelete']    = '그룹 삭제';
$labels['groupremoveselected'] = '그룹에서 선택한 연락처 제거';

$labels['previouspage']   = '이전 세트 보기';
$labels['firstpage']      = '처음 세트 보기';
$labels['nextpage']       = '다음 세트 보기';
$labels['lastpage']       = '마지막 세트 보기';

$labels['group'] = '그룹';
$labels['groups'] = '그룹';
$labels['personaladrbook'] = '개인 주소';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = '가져오기';
$labels['importcontacts'] = '연락처 가져오기';
$labels['importfromfile'] = '파일에서 가져오기';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = '전체 주소록을 바꿈';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = '완료';

// settings
$labels['settingsfor'] = '설정';
$labels['about'] = '이것은...';
$labels['preferences'] = '환경 설정';
$labels['userpreferences'] = '사용자 환경';
$labels['editpreferences'] = '사용자 환경 편집';

$labels['identities'] = '신원';
$labels['manageidentities'] = '이 계정의 신원 관리';
$labels['newidentity'] = '새 신원';

$labels['newitem'] = '새 아이템';
$labels['edititem'] = '아이템 편집';

$labels['preferhtml'] = 'HTML 보이기';
$labels['defaultcharset'] = '기본 문자 세트';
$labels['htmlmessage'] = 'HTML 메시지';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = '날짜 형식';
$labels['timeformat'] = '시간 형식';
$labels['prettydate'] = '날짜 형식';
$labels['setdefault']  = '기본으로 설정';
$labels['autodetect']  = '자동';
$labels['language']  = '언어';
$labels['timezone']  = '시간대';
$labels['pagesize']  = '페이지당 줄 수';
$labels['signature'] = '서명';
$labels['dstactive']  = '서머타임';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML 메일 쓰기';
$labels['htmlonreply'] = 'HTML 메시지에 회신할 때에 만';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML 서명';
$labels['previewpane'] = '미리 보기 창 보기';
$labels['skin'] = '인터페이스 스킨';
$labels['logoutclear'] = '로그아웃할 때 휴지통 비우기';
$labels['logoutcompact'] = '로그아웃할 때 받은편지함 압축하기';
$labels['uisettings'] = '사용자 인터페이스';
$labels['serversettings'] = '서버 설정';
$labels['mailboxview'] = '메일 상자 보기';
$labels['mdnrequests'] = '수신 확인';
$labels['askuser'] = '사용자에게 묻기';
$labels['autosend'] = '자동으로 보내기';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = '무시';
$labels['readwhendeleted'] = '삭제할 때 메시지를 읽은 것으로 표시';
$labels['flagfordeletion'] = '지우는 대신 지울 메시지에 깃발 달기';
$labels['skipdeleted'] = '지운 메시지 보이지 않기';
$labels['deletealways'] = '휴지통으로 메시지 이동을 실패하면, 삭제';
$labels['deletejunk'] = '정크 메일함에 있는 메시지들을 바로 삭제';
$labels['showremoteimages'] = '원격 이미지 보이기';
$labels['fromknownsenders'] = '아는 발신인에게서만';
$labels['always'] = '항상';
$labels['showinlineimages'] = '메시지 아래에 첨부 이미지 보이기';
$labels['autosavedraft']  = '드래프트 자동 저장';
$labels['everynminutes']  = '$n 분마다';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = '하지 않음';
$labels['immediately']  = '즉시';
$labels['messagesdisplaying'] = '메시지 표시 중';
$labels['messagescomposition'] = '메시지 작성 중';
$labels['mimeparamfolding'] = '첨부 파일 이름';
$labels['2231folding'] = 'Full RFC 2231 (선더버드)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS 아웃룻)';
$labels['2047folding'] = 'Full RFC 2047 (다른 프로그램들)';
$labels['force7bit'] = '8-bit 문자들을 위해서 MIME 인코딩을 사용';
$labels['advancedoptions'] = '상세 설정';
$labels['focusonnewmessage'] = '새 메시지가 있으면 브라우저 윈도우로 포커스 이동';
$labels['checkallfolders'] = '새 메시지를 위해 모든 폴더 체크';
$labels['displaynext'] = '메시지 삭제/이동한 후에는 다음 메시지를 표시';
$labels['defaultfont'] = 'HTML 메시지의 기본 폰트';
$labels['mainoptions'] = '주 옵션';
$labels['browseroptions'] = '브라우저 옵션';
$labels['section'] = '섹션';
$labels['maintenance'] = '유지 관리';
$labels['newmessage'] = '새 메시지';
$labels['signatureoptions'] = '서명 옵션';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = '자동으로 서명 추가';
$labels['newmessageonly'] = '새로운 메시지만';
$labels['replyandforwardonly'] = '회신이나 전달 메시지 만';
$labels['replysignaturepos'] = 'When replying or forwarding place signature';
$labels['belowquote'] = 'below the quote';
$labels['abovequote'] = 'above the quote';
$labels['insertsignature'] = '서명 삽입';
$labels['previewpanemarkread']  = '미리 읽힘 메시지를 읽음으로 표시';
$labels['afternseconds']  = '$n 초 이후';
$labels['reqmdn'] = '항상 수신확인 요청 하기';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = '메시지를 보내기 전에 맞춤법 확인';
$labels['spellcheckoptions'] = '맞춤법 확인 옵션';
$labels['spellcheckignoresyms'] = '심볼이 포함된 단어 무시하기';
$labels['spellcheckignorenums'] = '숫자가 포함된 단어 무시하기';
$labels['spellcheckignorecaps'] = '대문자가 포함된 단어 무시하기';
$labels['addtodict'] = '사전에 추가';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = '폴더';
$labels['folders']  = '폴더';
$labels['foldername']  = '폴더 이름';
$labels['subscribed']  = '가입됨';
$labels['messagecount'] = '메시지';
$labels['create']  = '만들기';
$labels['createfolder']  = '새 폴더 만들기';
$labels['managefolders']  = '폴더 관리';
$labels['specialfolders'] = '특수 폴더';
$labels['properties'] = '등록 정보';
$labels['folderproperties'] = '폴더 등록 정보';
$labels['parentfolder'] = '상위 폴더';
$labels['location'] = 'Location';
$labels['info'] = '정보';
$labels['getfoldersize'] = '폴더의 사이즈를 알아보려면 클릭';
$labels['changesubscription'] = '구독을 변경하려면 클릭';
$labels['foldertype'] = '폴더 타입';
$labels['personalfolder']  = '개인 폴더';
$labels['otherfolder']  = '다른 사용자의 폴더';
$labels['sharedfolder']  = '공개 폴더';

$labels['sortby'] = '정렬';
$labels['sortasc']  = '오름차순으로 정렬';
$labels['sortdesc'] = '내림차순으로 정렬';
$labels['undo'] = '실행 취소';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = '플러그인';
$labels['version'] = '버전';
$labels['source'] = 'Source';
$labels['license'] = '라이센스';
$labels['support'] = '지원 받기';

// units
$labels['B'] = '바이트';
$labels['KB'] = '킬로바이트';
$labels['MB'] = '메가바이트';
$labels['GB'] = '기가바이트';

// character sets
$labels['unicode'] = '유니코드(단일 코드,다국적 지원코드)';
$labels['english'] = '영어';
$labels['westerneuropean'] = 'Western European';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'Baltic';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'Arabic';
$labels['greek'] = 'Greek';
$labels['hebrew'] = 'Hebrew';
$labels['turkish'] = '터키어';
$labels['nordic'] = 'Nordic';
$labels['thai'] = '태국어';
$labels['celtic'] = 'Celtic';
$labels['vietnamese'] = '베트남어';
$labels['japanese'] = '일본어';
$labels['korean'] = '한국어';
$labels['chinese'] = '중국어';

?>
