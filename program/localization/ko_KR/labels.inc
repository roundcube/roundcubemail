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
$labels['password']  = '암호';
$labels['server']    = '서버';
$labels['login']     = '로그인';

// taskbar
$labels['logout']   = '로그아웃';
$labels['mail']     = '메일';
$labels['settings'] = '설정';
$labels['addressbook'] = '주소록';

// mailbox names
$labels['inbox']  = '받은 편지함';
$labels['drafts'] = '임시 보관함';
$labels['sent']   = '보낸 편지함';
$labels['trash']  = '휴지통';
$labels['junk']   = '스팸 편지함';

// message listing
$labels['subject'] = '제목';
$labels['from']    = '보내는 사람';
$labels['sender']  = '발신인';
$labels['to']      = '받는 사람';
$labels['cc']      = '참조';
$labels['bcc']     = '숨은 참조';
$labels['replyto'] = '회신 주소';
$labels['followupto'] = '전달';
$labels['date']    = '날짜';
$labels['size']    = '크기';
$labels['priority'] = '우선순위';
$labels['organization'] = '기관';
$labels['readstatus'] = '읽음 상태';
$labels['listoptions'] = '목록 옵션...';

$labels['mailboxlist'] = '폴더';
$labels['messagesfromto'] = '총 $count개 중 $from 번째부터 $to 번째까지의 메시지';
$labels['threadsfromto'] = '총 $count개 중 $from 번째부터 $to 번째까지의 스레드';
$labels['messagenrof'] = '총 $count개 중  $nr 번째 메시지';
$labels['fromtoshort'] = '총 $count개 중 $from 번째 - $to 번째';

$labels['copy']     = '복사';
$labels['move']     = '이동';
$labels['moveto']   = '이동할 위치...';
$labels['download'] = '다운로드';
$labels['open']     = '열기';
$labels['showattachment'] = '보기';
$labels['showanyway'] = '그래도 항상 보기';

$labels['filename'] = '파일명';
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
$labels['checkmail']        = '새 메시지 확인';
$labels['compose']          = '메시지 작성';
$labels['writenewmessage']  = '새 메시지 생성';
$labels['reply']            = '회신';
$labels['replytomessage']   = '발신인에게 회신';
$labels['replytoallmessage'] = '보낸 사람과 모든 수신인에게 회신';
$labels['replyall']         = '전체 회신';
$labels['replylist']        = '회신 목록';
$labels['forward']          = '전달';
$labels['forwardinline']    = '본문 내용으로 전달';
$labels['forwardattachment'] = '첨부파일로 전달';
$labels['forwardmessage']   = '메시지 전달';
$labels['deletemessage']    = '메시지 삭제';
$labels['movemessagetotrash'] = '메시지를 휴지통으로 이동';
$labels['printmessage']     = '이 메시지 인쇄';
$labels['previousmessage']  = '이전 메시지 보기';
$labels['firstmessage']     = '처음 메시지 표시';
$labels['nextmessage']      = '다음 메시지 표시';
$labels['lastmessage']      = '마지막 메시지 표시';
$labels['backtolist']       = '메일 목록으로 돌아가기';
$labels['viewsource']       = '소스 보기';
$labels['mark']             = '표시';
$labels['markmessages']     = '메시지 표시하기';
$labels['markread']         = '읽음으로 표시';
$labels['markunread']       = '읽지 않음으로 표시';
$labels['markflagged']      = '깃발로 표시됨으로 표시';
$labels['markunflagged']    = '깃발로 표시되지 않음으로 표시';
$labels['moreactions']      = '추가 동작...';
$labels['more']             = '더보기';
$labels['back']             = '뒤로';
$labels['options']          = '옵션';

$labels['select'] = '선택';
$labels['all'] = '모두';
$labels['none'] = '없음';
$labels['currpage'] = '현재 페이지';
$labels['unread'] = '읽지 않음';
$labels['flagged'] = '깃발로 표시됨';
$labels['unanswered'] = '답장하지 않음';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = '삭제됨';
$labels['undeleted'] = '삭제되지 않음';
$labels['invert'] = '반전';
$labels['filter'] = '필터';
$labels['list'] = '목록';
$labels['threads'] = '스레드';
$labels['expand-all'] = '모두 확장';
$labels['expand-unread'] = '읽지 않은 메시지 확장';
$labels['collapse-all'] = '모두 접기';
$labels['threaded'] = '스레드화됨';

$labels['autoexpand_threads'] = '메시지 스레드 확장';
$labels['do_expand'] = '모든 스레드';
$labels['expand_only_unread'] = '읽지 않은 메시지에만 적용';
$labels['fromto'] = '보내는 사람/받는 사람';
$labels['flag'] = '깃발로 표시';
$labels['attachment'] = '첨부파일';
$labels['nonesort'] = '없음';
$labels['sentdate'] = '발신일';
$labels['arrival'] = '도착일';
$labels['asc'] = '오름차순';
$labels['desc'] = '내림차순';
$labels['listcolumns'] = '열 목록 보기';
$labels['listsorting'] = '열 정렬';
$labels['listorder'] = '정렬 순서';
$labels['listmode'] = '화면 모드 목록 보기';

$labels['folderactions'] = '폴더 명령';
$labels['compact'] = '간단하게';
$labels['empty'] = '비어 있음';

$labels['quota'] = '디스크 사용량';
$labels['unknown']  = '알 수 없음';
$labels['unlimited']  = '제한 없음';

$labels['quicksearch']  = '빠른 검색';
$labels['resetsearch']  = '검색 재설정';
$labels['searchmod']  = '수정자 검색';
$labels['msgtext']  = '전체 메시지';
$labels['body']  = '본문';

$labels['openinextwin'] = '새 창에서 열기';
$labels['emlsave'] = '다운로드(.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = '새 메시지로 편집';
$labels['send']           = '보내기';
$labels['sendmessage']    = '메일 보내기';
$labels['savemessage']    = '임시 보관함에 저장';
$labels['addattachment']  = '파일 첨부';
$labels['charset']        = '인코딩';
$labels['editortype']     = '편집기 유형';
$labels['returnreceipt']  = '수신 확인';
$labels['dsn']            = '전송 상태 알림';
$labels['mailreplyintro'] = '$date에 $sender이(가) 작성한 내용:';
$labels['originalmessage'] = '원본 메세지';

$labels['editidents']    = '신원 편집';
$labels['spellcheck']    = '맞춤법';
$labels['checkspelling'] = '맞춤법 검사하기';
$labels['resumeediting'] = '편집 계속하기';
$labels['revertto']      = '돌아가기';

$labels['attach'] = '첨부';
$labels['attachments'] = '첨부파일';
$labels['upload'] = '업로드';
$labels['uploadprogress'] = '$percent ($total개 중 $current개)';
$labels['close']  = '닫기';
$labels['messageoptions']  = '메시지 옵션...';

$labels['low']     = '낮음';
$labels['lowest']  = '가장 낮음';
$labels['normal']  = '보통';
$labels['high']    = '높음';
$labels['highest'] = '가장 높음';

$labels['nosubject']  = '(제목 없음)';
$labels['showimages'] = '이미지 보기';
$labels['alwaysshow'] = '$sender에서 수신된 이미지 항상 보기';
$labels['isdraft']    = '이것은 작성 중인 메시지입니다.';
$labels['andnmore']   = '$nr 더 보기...';
$labels['togglemoreheaders'] = '메시지 헤더 더 보기';
$labels['togglefullheaders'] = '원 메시지 헤더 표시/숨기기';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = '일반 텍스트';
$labels['savesentmessagein'] = '보낸 메시지를 다음 위치에 저장';
$labels['dontsave'] = '저장하지 않음';
$labels['maxuploadsize'] = '최대 허용 파일 크기는 $size 입니다';

$labels['addcc'] = '참조 추가';
$labels['addbcc'] = '숨은 참조 추가';
$labels['addreplyto'] = '회신 주소 추가';
$labels['addfollowupto'] = '전달 추가';

// mdn
$labels['mdnrequest'] = '이 메시지를 보낸 사람이 사용자가 이 메시지를 읽었을 때 읽음 확인을 요청했습니다. 보낸 사람에게 알리시겠습니까?';
$labels['receiptread'] = '수신 확인 (읽음)';
$labels['yourmessage'] = '이것은 사용자의 메시지에 대한 수신 확인입니다.';
$labels['receiptnote'] = '참고: 이 수신 확인은 이 메시지가 받는 사람의 컴퓨터에 표시되었다는 것만을 의미합니다. 받는 사람이 메시지 내용을 읽거나 이해했다는 보장은 없습니다.';

// address boook
$labels['name']         = '표시명';
$labels['firstname']    = '이름';
$labels['surname']      = '성';
$labels['middlename']   = '가운데 이름';
$labels['nameprefix']   = '접두사';
$labels['namesuffix']   = '접미사';
$labels['nickname']     = '별칭';
$labels['jobtitle']     = '직위';
$labels['department']   = '부서';
$labels['gender']       = '성별';
$labels['maidenname']   = '결혼 전 성';
$labels['email']        = '이메일';
$labels['phone']        = '전화';
$labels['address']      = '주소';
$labels['street']       = '나머지 주소';
$labels['locality']     = '시/구/군';
$labels['zipcode']      = '우편번호';
$labels['region']       = '시/도';
$labels['country']      = '국가';
$labels['birthday']     = '생년월일';
$labels['anniversary']  = '기념일';
$labels['website']      = '웹사이트';
$labels['instantmessenger'] = '인스턴트 메신저';
$labels['notes'] = '메모';
$labels['male']   = '남성';
$labels['female'] = '여성';
$labels['manager'] = '상사';
$labels['assistant'] = '비서';
$labels['spouse'] = '배우자';
$labels['allfields'] = '모든 항목';
$labels['search'] = '검색';
$labels['advsearch'] = '고급 검색';
$labels['advanced'] = '고급';
$labels['other'] = '기타';

$labels['typehome']   = '자택';
$labels['typework']   = '직장';
$labels['typeother']  = '기타';
$labels['typemobile']  = '휴대전화';
$labels['typemain']  = '대표';
$labels['typehomefax']  = '자택 팩스';
$labels['typeworkfax']  = '직장 팩스';
$labels['typecar']  = '자동차';
$labels['typepager']  = '호출기';
$labels['typevideo']  = '동영상';
$labels['typeassistant']  = '비서';
$labels['typehomepage']  = '홈페이지';
$labels['typeblog'] = '블로그';
$labels['typeprofile'] = '자기소개';

$labels['addfield'] = '필드 추가..';
$labels['addcontact'] = '새 연락처 추가';
$labels['editcontact'] = '연락처 편집';
$labels['contacts'] = '연락처';
$labels['contactproperties'] = '연락처 속성';
$labels['personalinfo'] = '개인 정보';

$labels['edit']   = '편집';
$labels['cancel'] = '취소';
$labels['save']   = '저장';
$labels['delete'] = '삭제';
$labels['rename'] = '이름 변경';
$labels['addphoto'] = '추가';
$labels['replacephoto'] = '바꾸기';
$labels['uploadphoto'] = '사진 업로드';

$labels['newcontact']     = '새 연락처 카드 생성';
$labels['deletecontact']  = '선택한 연락처 삭제';
$labels['composeto']      = '메일 작성 대상';
$labels['contactsfromto'] = '총 $count개의 연락처 중 $from 번째부터 $to 번째 까지';
$labels['print']          = '인쇄';
$labels['export']         = '내보내기';
$labels['exportall']      = '모두 내보내기';
$labels['exportsel']      = '선택한 항목 내보내기';
$labels['exportvcards']   = '연락처를 vCard 형식으로 내보내기';
$labels['newcontactgroup'] = '새 연락처 그룹 생성';
$labels['grouprename']    = '그룹명 변경';
$labels['groupdelete']    = '그룹 삭제';
$labels['groupremoveselected'] = '그룹에서 선택한 연락처 제거';

$labels['previouspage']   = '이전 페이지 보기';
$labels['firstpage']      = '처음 페이지 보기';
$labels['nextpage']       = '다음 페이지 보기';
$labels['lastpage']       = '마지막 페이지 보기';

$labels['group'] = '그룹';
$labels['groups'] = '그룹';
$labels['personaladrbook'] = '개인 주소';

$labels['searchsave'] = '검색 저장';
$labels['searchdelete'] = '검색 삭제';

$labels['import'] = '가져오기';
$labels['importcontacts'] = '연락처 가져오기';
$labels['importfromfile'] = '파일에서 가져오기:';
$labels['importtarget'] = '새 연락처를 주소록에 추가:';
$labels['importreplace'] = '전체 주소록을 바꾸기';
$labels['importdesc'] = '기존 주소록에서 연락처를 가져올 수 있습니다.<br/> 현재 <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> 데이터 형식에서 주소 가져오기를 지원합니다.\'';
$labels['done'] = '완료';

// settings
$labels['settingsfor'] = '설정';
$labels['about'] = '정보...';
$labels['preferences'] = '환경설정';
$labels['userpreferences'] = '사용자 환경설정';
$labels['editpreferences'] = '사용자 환경설정 편집';

$labels['identities'] = '신원';
$labels['manageidentities'] = '이 계정의 신원 관리';
$labels['newidentity'] = '새 신원';

$labels['newitem'] = '새 항목';
$labels['edititem'] = '항목 편집';

$labels['preferhtml'] = 'HTML 표시';
$labels['defaultcharset'] = '기본 인코딩';
$labels['htmlmessage'] = 'HTML 메시지';
$labels['messagepart'] = '부분';
$labels['digitalsig'] = '디지털 서명';
$labels['dateformat'] = '날짜 형식';
$labels['timeformat'] = '시간 형식';
$labels['prettydate'] = '간결한 날짜';
$labels['setdefault']  = '기본 설정';
$labels['autodetect']  = '자동';
$labels['language']  = '언어';
$labels['timezone']  = '시간대';
$labels['pagesize']  = '페이지당 행 수';
$labels['signature'] = '서명';
$labels['dstactive']  = '일광 절약 시간';
$labels['showinextwin'] = '메시지를 새 창에서 열기';
$labels['composeextwin'] = '새 창에서 작성하기';
$labels['htmleditor'] = 'HTML 메시지 작성';
$labels['htmlonreply'] = 'HTML 메시지 회신 시';
$labels['htmlonreplyandforward'] = '전달 또는 HTML 메시지 회신 시';
$labels['htmlsignature'] = 'HTML 서명';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = '미리보기 창 보기';
$labels['skin'] = '인터페이스 스킨';
$labels['logoutclear'] = '로그아웃할 때 휴지통 비우기';
$labels['logoutcompact'] = '로그아웃할 때 받은 편지함 압축하기';
$labels['uisettings'] = '사용자 인터페이스';
$labels['serversettings'] = '서버 설정';
$labels['mailboxview'] = '메일함 화면';
$labels['mdnrequests'] = '수신 확인 요청 시';
$labels['askuser'] = '사용자에게 물어봄';
$labels['autosend'] = '수신 확인 보내기';
$labels['autosendknown'] = '내 연락처에 수신 확인 요청을 보냄, 아닐 경우 물어봄';
$labels['autosendknownignore'] = '내 연락처에 수신 확인 요청을 보냄, 아닐 경우 무시함';
$labels['ignore'] = '무시함';
$labels['readwhendeleted'] = '삭제 시 메시지를 읽음으로 표시';
$labels['flagfordeletion'] = '삭제 대신 삭제 대상 메시지를 깃발로 표시';
$labels['skipdeleted'] = '삭제된 메시지 보지 않기';
$labels['deletealways'] = '메시지를 휴지통으로 이동하는 작업에 실패할 경우 삭제';
$labels['deletejunk'] = '스팸 메일함에 있는 메시지를 바로 삭제';
$labels['showremoteimages'] = '본문 내용의 원격 이미지 표시';
$labels['fromknownsenders'] = '알려진 발신인으로부터 받은 메시지만';
$labels['always'] = '항상';
$labels['showinlineimages'] = '메시지 하단에 첨부 이미지 표시';
$labels['autosavedraft']  = '작성 중인 메시지를 자동으로 저장';
$labels['everynminutes']  = '매 $n분 마다';
$labels['refreshinterval']  = '새로 고침 (새 메시지 확인, 기타 등등)';
$labels['never']  = '하지 않음';
$labels['immediately']  = '즉시';
$labels['messagesdisplaying'] = '메시지 보기';
$labels['messagescomposition'] = '메시지 작성';
$labels['mimeparamfolding'] = '첨부파일명';
$labels['2231folding'] = 'Full RFC 2231 (선더버드)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS 아웃룩)';
$labels['2047folding'] = 'Full RFC 2047 (기타)';
$labels['force7bit'] = '8-bit 문자에 MIME 인코딩 사용';
$labels['advancedoptions'] = '고급 옵션';
$labels['focusonnewmessage'] = '새 메시지가 있을 경우 브라우저 창으로 포커스 이동';
$labels['checkallfolders'] = '모든 폴더에서 새 메시지 확인';
$labels['displaynext'] = '메시지 삭제/이동 후 다음 메시지 표시';
$labels['defaultfont'] = 'HTML 메시지의 기본 글꼴';
$labels['mainoptions'] = '주요 옵션';
$labels['browseroptions'] = '브라우저 옵션';
$labels['section'] = '섹션';
$labels['maintenance'] = '유지관리';
$labels['newmessage'] = '새 메시지';
$labels['signatureoptions'] = '서명 옵션';
$labels['whenreplying'] = '회신 시';
$labels['replyempty'] = '원문 메시지를 인용하지 않음';
$labels['replytopposting'] = '새 메시지를 인용문 상단에서 시작';
$labels['replybottomposting'] = '새 메시지를 인용문 하단에서 시작';
$labels['replyremovesignature'] = '회신 시 메시지에서 원문 서명을 제거';
$labels['autoaddsignature'] = '서명 자동으로 추가';
$labels['newmessageonly'] = '새로운 메시지에만';
$labels['replyandforwardonly'] = '회신 및 전달 시에만';
$labels['insertsignature'] = '서명 삽입';
$labels['previewpanemarkread']  = '미리 본 메시지를 읽음으로 표시';
$labels['afternseconds']  = '$n초 후';
$labels['reqmdn'] = '수신 확인을 항상 요청';
$labels['reqdsn'] = '전송 상태 알림을 항상 요청';
$labels['replysamefolder'] = ' 회신 메시지를 회신 대상 메시지의 폴더로 이동';
$labels['defaultabook'] = '기본 주소록';
$labels['autocompletesingle'] = '자동완성에서 대체 이메일 주소를 건너뜀';
$labels['listnamedisplay'] = '연락처 정렬 방식';
$labels['spellcheckbeforesend'] = '메시지를 보내기 전에 맞춤법 검사';
$labels['spellcheckoptions'] = '맞춤법 검사 옵션';
$labels['spellcheckignoresyms'] = '기호가 포함된 단어 무시';
$labels['spellcheckignorenums'] = '숫자가 포함된 단어 무시';
$labels['spellcheckignorecaps'] = '모두 대문자로 표기된 단어 무시';
$labels['addtodict'] = '사전에 추가';
$labels['mailtoprotohandler'] = 'mailto: 링크의 프로토콜 처리자 등록';
$labels['forwardmode'] = '메시지 전달';
$labels['inline'] = '본문 내용으로';
$labels['asattachment'] = '첨부파일로';

$labels['folder']  = '폴더';
$labels['folders']  = '폴더';
$labels['foldername']  = '폴더명';
$labels['subscribed']  = '구독함';
$labels['messagecount'] = '메시지';
$labels['create']  = '생성';
$labels['createfolder']  = '새 폴더 생성';
$labels['managefolders']  = '폴더 관리';
$labels['specialfolders'] = '특수 폴더';
$labels['properties'] = '속성';
$labels['folderproperties'] = '폴더 속성';
$labels['parentfolder'] = '상위 폴더';
$labels['location'] = '위치';
$labels['info'] = '정보';
$labels['getfoldersize'] = '폴더 크기를 확인하려면 클릭하세요';
$labels['changesubscription'] = '구독을 변경하려면 클릭하세요';
$labels['foldertype'] = '폴더 유형';
$labels['personalfolder']  = '개인 폴더';
$labels['otherfolder']  = '다른 사용자의 폴더';
$labels['sharedfolder']  = '공유 폴더';

$labels['sortby'] = '정렬 방식';
$labels['sortasc']  = '오름차순으로 정렬';
$labels['sortdesc'] = '내림차순으로 정렬';
$labels['undo'] = '실행 취소';

$labels['installedplugins'] = '설치된 플러그인';
$labels['plugin'] = '플러그인';
$labels['version'] = '버전';
$labels['source'] = '소스';
$labels['license'] = '라이선스';
$labels['support'] = '지원 요청';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = '유니코드';
$labels['english'] = '영어';
$labels['westerneuropean'] = '서유럽어';
$labels['easterneuropean'] = '동유럽어';
$labels['southeasterneuropean'] = '남동유럽어';
$labels['baltic'] = '발트어';
$labels['cyrillic'] = '키릴어';
$labels['arabic'] = '아랍어';
$labels['greek'] = '그리스어';
$labels['hebrew'] = '히브리어';
$labels['turkish'] = '터키어';
$labels['nordic'] = '북유럽어';
$labels['thai'] = '태국어';
$labels['celtic'] = '켈트어';
$labels['vietnamese'] = '베트남어';
$labels['japanese'] = '일본어';
$labels['korean'] = '한국어';
$labels['chinese'] = '중국어';

?>
