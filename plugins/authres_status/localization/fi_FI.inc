<?php

$labels = array();
$labels['nosignature'] = 'Ei allekirjoitustietoja';
$labels['noauthresults'] = 'Allekirjoitus löytyi, mutta tunnistautumistieto-otsaketta ei. Mahdollinen ongelma palvelimen asetuksissa.';
$labels['signaturepass'] = 'Valid signature(s) from the sender\'s domain. verified by ';
$labels['partialpass'] = 'Some signatures are invalid but at least one is valid for the sender\'s domain. verified by ';
$labels['thirdparty'] = 'Signed by third party, signature is present but for different domain than from address. verified for ';
$labels['temporaryinvalid'] = 'Signature is (temporarily) not valid! verified by ';
$labels['invalidsignature'] = 'Allekirjoitus ei ole kelvollinen! vahvistanut';

$labels['for'] = 'for';
$labels['by'] = 'by';

$labels['section_title']  = 'Tunnistautumisen tila';
$labels['column_title']  = 'Tunnistautumisen tulos';

$labels['title_enable_column']  = 'Enable Column';
$labels['label_enable_column']  = 'Enable authentication result column in message list';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Näytä vain seuraavat tilat';
$labels['label_include_status1']  = 'Allekirjoitusta ei löytynyt';
$labels['label_include_status2']  = 'Allekirjoitus löytyi, mutta tunnistautumistieto-otsaketta ei';
$labels['label_include_status4']  = 'Kaikki allekirjoitukset läpäisivät tarkistuksen';
$labels['label_include_status8']  = 'Ainakin yksi allekirjoitus läpäisi tarkistuksen';
$labels['label_include_status16']  = 'Kolmannen osapuolen allekirjoitus';
$labels['label_include_status32']  = 'Allekirjoitus epäonnistui väliaikaisesti';
$labels['label_include_status64']  = 'Allekirjoitus epäonnistui pysyvästi';

?>
