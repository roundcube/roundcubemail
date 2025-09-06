<?php

$labels = array();
$labels['nosignature'] = 'Няма дадзеных аб подпісе';
$labels['noauthresults'] = 'Подпіс знойдзены, але няма загалоўка вынікаў праверкі сапраўднасці. Праблема канфігурацыі сервера?';
$labels['signaturepass'] = 'Сапраўдны подпіс(ы) з дамена адпраўніка. праверана';
$labels['partialpass'] = 'Некаторыя подпісы з\'яўляюцца несапраўднымі, але па меншай меры адзін сапраўдны для дамена адпраўніка. праверана';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Подпіс (часова) не дзейнічае! праверана';
$labels['invalidsignature'] = 'Подпіс не зьяўляецца сапраўдным! праверана';

$labels['for'] = 'для';
$labels['by'] = 'па';

$labels['section_title']  = 'Статус ідэнтыфікацыі';
$labels['column_title']  = 'Вынікі праверкі сапраўднасці';

$labels['title_enable_column']  = 'Уключыць калонку';
$labels['label_enable_column']  = 'Уключэнне ідэнтыфікацыі выніку калонкі ў спісе паведамленняў';
$labels['label_fallback_verifier'] = 'Выкарыстоўвайце ўнутраную DKIM верыфікацыю (калі подпіс знойдзены, але не ідэнтыфікаваны вынік загалоўка)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Паказваць толькі наступныя статусы';
$labels['label_include_status1']  = 'Няма подпісу';
$labels['label_include_status2']  = 'Signature found but no auth. results header';
$labels['label_include_status4']  = 'All signatures passed validation';
$labels['label_include_status8']  = 'At least one signature passed validation';
$labels['label_include_status16']  = 'Third party signature';
$labels['label_include_status32']  = 'Signature temporary failed';
$labels['label_include_status64']  = 'Signature permanently failed';

?>
