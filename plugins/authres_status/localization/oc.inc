<?php

$labels = array();
$labels['nosignature'] = 'No signature information';
$labels['noauthresults'] = 'Signature found but no auth. results header. Server configuration problem?';
$labels['signaturepass'] = 'Valid signature(s) from the sender\'s domain. verified by ';
$labels['partialpass'] = 'Some signatures are invalid but at least one is valid for the sender\'s domain. verified by ';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Signature is (temporarily) not valid! verified by ';
$labels['invalidsignature'] = 'Signature is not valid! verified by ';

$labels['for'] = 'for';
$labels['by'] = 'by';

$labels['section_title']  = 'Authenticaton Status';
$labels['column_title']  = 'Auth. Result';

$labels['title_enable_column']  = 'Enable Column';
$labels['label_enable_column']  = 'Enable authentication result column in message list';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Only show the following statuses';
$labels['label_include_status1']  = 'No signature found';
$labels['label_include_status2']  = 'Signature found but no auth. results header';
$labels['label_include_status4']  = 'All signatures passed validation';
$labels['label_include_status8']  = 'At least one signature passed validation';
$labels['label_include_status16']  = 'Third party signature';
$labels['label_include_status32']  = 'Signature temporary failed';
$labels['label_include_status64']  = 'Signature permanently failed';

?>
