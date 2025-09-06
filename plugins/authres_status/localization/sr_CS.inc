<?php

$labels = array();
$labels['nosignature'] = 'Нема података о потпису';
$labels['noauthresults'] = 'Потпис нађен али нема заглавља с резултатом аутентификације. Проблем са серверским подешавањима?';
$labels['signaturepass'] = 'Ваљан потпис да домена пошиљаоца. Оверен од стране';
$labels['partialpass'] = 'Неки потписи су неисправни али бар један је исправан за домен пошиљаоца. Проверио ';
$labels['thirdparty'] = 'Потписан од треће стране. Потпис присутан али за различит домен него што је у адреси. Проверен за ';
$labels['temporaryinvalid'] = 'Потпис (привремено) није исправан! Проверио ';
$labels['invalidsignature'] = 'Потпис није исправан! Проверио ';

$labels['for'] = 'за';
$labels['by'] = 'од';

$labels['section_title']  = 'Стање аутентификације';
$labels['column_title']  = 'Резултат аутентификације';

$labels['title_enable_column']  = 'Укључи колону';
$labels['label_enable_column']  = 'Приказује колону с резултатом аутентификације у листи порука';
$labels['label_fallback_verifier'] = 'Користи интерни ДКИМ проверавач (ако нема заглавља аутентификације)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Прикажи само следећа стања';
$labels['label_include_status1']  = 'Није пронађен потпис';
$labels['label_include_status2']  = 'Потпис постоји али нема заглавља аутентификације';
$labels['label_include_status4']  = 'Сви потписи су прошли оверу';
$labels['label_include_status8']  = 'Бар један потпис је прошао оверу';
$labels['label_include_status16']  = 'Потпис треће стране';
$labels['label_include_status32']  = 'Потпис привремено неуспешан';
$labels['label_include_status64']  = 'Потпис трајно неуспешан';

?>
