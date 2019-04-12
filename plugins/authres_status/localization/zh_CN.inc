<?php

$labels = array();
$labels['nosignature'] = '没有签名信息';
$labels['noauthresults'] = '找到签名但没有 auth. results 头。服务器配置存在问题？';
$labels['signaturepass'] = '发件方域名的有效签名。验证者';
$labels['partialpass'] = '部分签名无效，但有至少一个发件方域名的有效签名。验证者';
$labels['thirdparty'] = '已被第三方签名，但与地址的域名不同。验证者';
$labels['temporaryinvalid'] = '签名（暂时）无效！验证者';
$labels['invalidsignature'] = '签名无效！验证者';

$labels['for'] = '对';
$labels['by'] = '由';

$labels['section_title']  = '身份验证状态';
$labels['column_title']  = 'Auth. Result';

$labels['title_enable_column']  = '启用列';
$labels['label_enable_column']  = '启用消息列表中的“身份验证结果”列';
$labels['label_fallback_verifier'] = '使用内部 DKIM 验证器（如果找到签名但没有 auth. results 头）';

$labels['title_trusted_mtas'] = '受信的 ';
$labels['label_trusted_mtas'] = '使用逗号分隔的你信任的 mta authserv-id 列表。只会显示这些 mta 的结果。';

$labels['title_include_status'] = '只显示下列签名';
$labels['label_include_status1']  = '没有找到签名';
$labels['label_include_status2']  = '找到签名但没有 auth. results 头';
$labels['label_include_status4']  = '所有签名通过验证';
$labels['label_include_status8']  = '至少一个签名通过验证';
$labels['label_include_status16']  = '第三方签名';
$labels['label_include_status32']  = '签名暂时失败';
$labels['label_include_status64']  = '签名永久失败';

?>
