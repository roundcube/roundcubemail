<?php

$labels = array();
$labels['nosignature'] = '서명 정보가 없음';
$labels['noauthresults'] = '서명을 찾았으나 인증 결과 머리글이 없습니다. 서버 구성 문제입니까?';
$labels['signaturepass'] = '발송자 도메인에서 유효한 서명입니다. 검증자:';
$labels['partialpass'] = '일부 서명이 유효하지 않으나 최소 한 개는 발송자의 도메인에 대해 유효합니다. 검증자:';
$labels['thirdparty'] = '제3자가 서명한 서명이 존재하나 보내는 주소와 다른 도메인의 것입니다. 다음에 대한 검증:';
$labels['temporaryinvalid'] = '서명이 (일시적으로) 유효하지 않습니다! 검증자:';
$labels['invalidsignature'] = '서명이 유효하지 않습니다! 검증자:';

$labels['for'] = '대상';
$labels['by'] = '검증자:';

$labels['section_title']  = '인증 상태';
$labels['column_title']  = '인증 결과';

$labels['title_enable_column']  = '열 활성화';
$labels['label_enable_column']  = '메시지 목록에 인증 결과 열을 활성화';
$labels['label_fallback_verifier'] = '내장 DKIM 검증자 사용 (서명을 찾았으나 인증 결과 머리글이 없을 경우)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = '다음 상태만 보기';
$labels['label_include_status1']  = '서명을 찾을 수 없음';
$labels['label_include_status2']  = '서명을 찾았으나 인증 결과 머리글이 없음';
$labels['label_include_status4']  = '모든 서명이 검증을 통과함';
$labels['label_include_status8']  = '최소 한 개의 서명이 검증을 통과함';
$labels['label_include_status16']  = '제3자 서명';
$labels['label_include_status32']  = '서명이 일시적으로 실패함';
$labels['label_include_status64']  = '서명이 영구적으로 실패함';

?>
