<?php

$labels = array();
$labels['nosignature'] = 'Ingen signatur information';
$labels['noauthresults'] = 'Signature found but no auth. results header. Server configuration problem?';
$labels['signaturepass'] = 'Valid signature(s) from the sender\'s domain. verified by ';
$labels['partialpass'] = 'Some signatures are invalid but at least one is valid for the sender\'s domain. verified by ';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Signature is (temporarily) not valid! verified by ';
$labels['invalidsignature'] = 'Signaturen er ikke gyldig! verificeret af';

$labels['for'] = 'for';
$labels['by'] = 'af';

$labels['section_title']  = 'Autentificering status';
$labels['column_title']  = 'Autentificering resultat';

$labels['title_enable_column']  = 'Aktiver kolonne ';
$labels['label_enable_column']  = 'Enable authentication result column in message list';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Kun vis de følgende statusser';
$labels['label_include_status1']  = 'Ingen signatur fundet';
$labels['label_include_status2']  = 'Signature found but no auth. results header';
$labels['label_include_status4']  = 'Alle signaturer bestod valideringen';
$labels['label_include_status8']  = 'Mindst én signatur bestod validering';
$labels['label_include_status16']  = 'Tredjepart signatur ';
$labels['label_include_status32']  = 'Signatur fejlede midlertidigt ';
$labels['label_include_status64']  = 'Signatur fejlede permenent';

?>
