<?php

$labels = array();
$labels['nosignature'] = 'Nu există informații semnătură';
$labels['noauthresults'] = 'Semnătură găsită, dar nu există rezultate autorizație în antet. Problema de configurare a serverului?';
$labels['signaturepass'] = 'Semnătură validă de la domeniul expeditorului. verificat de';
$labels['partialpass'] = 'Some signatures are invalid but at least one is valid for the sender\'s domain. verified by ';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Signature is (temporarily) not valid! verified by ';
$labels['invalidsignature'] = 'Semnătura nu este validă! verificat de';

$labels['for'] = 'pentru';
$labels['by'] = 'de';

$labels['section_title']  = 'Stare autentificare';
$labels['column_title']  = 'Auth. Result';

$labels['title_enable_column']  = 'Activați coloană';
$labels['label_enable_column']  = 'Enable authentication result column in message list';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Mta-uri de incredere';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Afișați doar următoarele stări';
$labels['label_include_status1']  = 'Nu sa găsit nici o semnătură';
$labels['label_include_status2']  = 'Signature found but no auth. results header';
$labels['label_include_status4']  = 'Toate semnăturile au trecut validarea';
$labels['label_include_status8']  = 'Cel puțin o semnătură a trecut validarea';
$labels['label_include_status16']  = 'Semnătură terță parte';
$labels['label_include_status32']  = 'Semnătura a eșuat temporar';
$labels['label_include_status64']  = 'Semnătura a eșuat definitiv';

?>
