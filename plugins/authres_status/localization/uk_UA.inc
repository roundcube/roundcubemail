<?php

$labels = array();
$labels['nosignature'] = 'Немає інформації підпису';
$labels['noauthresults'] = 'Підпис знайдено, однак без заголовка результатів автентифікації. Проблема конфігурації сервера?';
$labels['signaturepass'] = 'Дійсний підпис(-и) з домена відправника. Перевірено ';
$labels['partialpass'] = 'Деякі підписи недійсні, але дійсний принаймні один підпис на домен відправника. Перевірено ';
$labels['thirdparty'] = 'Підписано третьою стороною, підпис наявний, але з іншого домена, ніж адреса. Перевірено для ';
$labels['temporaryinvalid'] = 'Підпис (тимчасово) не дійсний! Перевірено ';
$labels['invalidsignature'] = 'Підпис не дійсний! Перевірено ';

$labels['for'] = 'для';
$labels['by'] = ' ';

$labels['section_title']  = 'Статус автентифікації';
$labels['column_title']  = 'Результат автентифікації';

$labels['title_enable_column']  = 'Увімкнути колонку';
$labels['label_enable_column']  = 'Увімкнути колонку результатів автентифікації у списку повідомлень';
$labels['label_fallback_verifier'] = 'Використати внутрішній DKIM-перевірник (якщо підпис знайдено, але без заголовка результатів автентифікації)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Показувати лише вказані статуси';
$labels['label_include_status1']  = 'Підпису не знайдено';
$labels['label_include_status2']  = 'Підпис знайдено, однак без заголовка результатів автентифікації.';
$labels['label_include_status4']  = 'Усі підписи пройшли перевірку достовірності';
$labels['label_include_status8']  = 'Принаймні один підпис пройшов перевірку достовірності';
$labels['label_include_status16']  = 'Підпис третьої сторони';
$labels['label_include_status32']  = 'Підпис тимчасово не вдався';
$labels['label_include_status64']  = 'Підпис не вдається';

?>
