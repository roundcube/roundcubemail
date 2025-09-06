<?php

$labels = array();
$labels['nosignature'] = '無簽名資訊';
$labels['noauthresults'] = '找到簽名，但無身份核對結果標頭。是否伺服器設定有誤？';
$labels['signaturepass'] = '有來自傳送者網域的有效簽名。核實簽名的是：';
$labels['partialpass'] = '某些簽名無效，但至至少有一個，對於傳送者的網域是有效的。核實簽名的是：';
$labels['thirdparty'] = '有由第三方簽署的簽名，但對應的網域和傳送者的不符。核實簽名的是：';
$labels['temporaryinvalid'] = '簽名 (暫時) 無效！核實簽名的是：';
$labels['invalidsignature'] = '簽名無效！核實簽名的是：';

$labels['for'] = '用於：';
$labels['by'] = '由：';

$labels['section_title']  = '身份核對狀態';
$labels['column_title']  = '身份核對結果';

$labels['title_enable_column']  = '啟用欄位';
$labels['label_enable_column']  = '於郵件清單，啟用身份核對結果欄位';
$labels['label_fallback_verifier'] = '使用內部 DKIM 核實程式 (若找到簽名，但無身份核對標頭)';

$labels['title_trusted_mtas'] = '信任的 MTA';
$labels['label_trusted_mtas'] = '以逗號分隔的 authserv-id 清單，列出您信任的 MTA。僅顯示來自該等 MTA 的結果。';

$labels['title_include_status'] = '僅顯示以下狀態';
$labels['label_include_status1']  = '找不到簽名';
$labels['label_include_status2']  = '找到簽名，但無身份核對結果標頭';
$labels['label_include_status4']  = '所有簽名均通過驗證';
$labels['label_include_status8']  = '至少一個簽名通過驗證';
$labels['label_include_status16']  = '第三方簽名';
$labels['label_include_status32']  = '簽名暫時不能用';
$labels['label_include_status64']  = '簽名永久不能用';

?>
