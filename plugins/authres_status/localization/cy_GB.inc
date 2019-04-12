<?php

$labels = array();
$labels['nosignature'] = 'Dim gwybodaeth llofnod';
$labels['noauthresults'] = 'Llofnod wedi ei ganfod ond dim pennawd dilysu. Problem gyda cyfluniad y gweinydd?';
$labels['signaturepass'] = 'Llofnod(au) dilys o barth yr anfonwr, dilyswyd gan';
$labels['partialpass'] = 'Mae rhai llofnodion yn annilys ond mae o leia un yn ddilys ar gyfer parth yr anfonwr. gwiriwyd gan';
$labels['thirdparty'] = 'Llofnodwyd gan trydydd parti, mae llofnod yn bresennol ond ar gyfer parth gwahanol i\'r cyfeiriad. gwiriwyd gan';
$labels['temporaryinvalid'] = 'Llofnod yn annilys (dros dro)! gwiriwyd gan';
$labels['invalidsignature'] = 'Llofnod yn annilys! gwiriwyd gan';

$labels['for'] = 'i';
$labels['by'] = 'gan';

$labels['section_title']  = 'Statws Dilysiad';
$labels['column_title']  = 'Canlyniad Dilysu';

$labels['title_enable_column']  = 'Galluogi Colofn';
$labels['label_enable_column']  = 'Galluogi colofn canlyniad dilysu yn y rhestr negeseuon';
$labels['label_fallback_verifier'] = 'Defnyddio gwirydd DKIM mewnol (os gwelwyd llofnod ond dim pennawd canlyniad dilysu)';

$labels['title_trusted_mtas'] = 'MTA\'s ymddiriedig';
$labels['label_trusted_mtas'] = 'Rhestr wedi ei wahanu a coma o authserv-id yr mtas rydych yn ymddiried ynddo. Dim ond canlyniadau o\'r mtas yma bydd yn cael eu dangos';

$labels['title_include_status'] = 'Dangos y statysau canlynol yn unig';
$labels['label_include_status1']  = 'Dim llofnod wedi ei ganfod';
$labels['label_include_status2']  = 'Llofnod wedi ei ganfod ond dim pennawd canlyniad dilysu';
$labels['label_include_status4']  = 'Pob llofnod wedi pasio dilysiad';
$labels['label_include_status8']  = 'O leia un llofnod wedi pasio dilysiad';
$labels['label_include_status16']  = 'Llofnod trydydd parti';
$labels['label_include_status32']  = 'Llofnod wedi methu dros dro';
$labels['label_include_status64']  = 'Llofnod wedi methu yn barhaol';

?>
