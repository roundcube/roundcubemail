<?php

$labels = array();
$labels['nosignature'] = 'Engar upplýsingar um undirritun';
$labels['noauthresults'] = 'Signature found but no auth. results header. Server configuration problem?';
$labels['signaturepass'] = 'Valid signature(s) from the sender\'s domain. verified by ';
$labels['partialpass'] = 'Some signatures are invalid but at least one is valid for the sender\'s domain. verified by ';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Signature is (temporarily) not valid! verified by ';
$labels['invalidsignature'] = 'Signature is not valid! verified by ';

$labels['for'] = 'fyrir';
$labels['by'] = 'af';

$labels['section_title']  = 'Staða auðkenningar';
$labels['column_title']  = 'Auth. Result';

$labels['title_enable_column']  = 'Virkja dálk';
$labels['label_enable_column']  = 'Enable authentication result column in message list';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Only show the following statuses';
$labels['label_include_status1']  = 'Engin undirritun fannst';
$labels['label_include_status2']  = 'Signature found but no auth. results header';
$labels['label_include_status4']  = 'Allar undirritanir stóðust sannvottun';
$labels['label_include_status8']  = 'Að minnsta kosti ein undirritun stóðst sannvottun';
$labels['label_include_status16']  = 'Undirritun frá utanaðkomandi aðila';
$labels['label_include_status32']  = 'Undirritun brást tímabundið';
$labels['label_include_status64']  = 'Undirritun brást endanlega';

?>
