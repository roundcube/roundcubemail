<?php

$labels = array();
$labels['nosignature'] = 'Nėra parašo informacijos';
$labels['noauthresults'] = 'Parašas rastas, bet nėra autentifikacijos rezultato antraštės. Serverio konfigūracijos klaida?';
$labels['signaturepass'] = 'Galiojantis parašas(-ai) iš siuntėjo domeno. Patvirtino';
$labels['partialpass'] = 'Ne visi laiške esantys parašai yra galiojantys, bet bent vienas yra galiojantis ir atitinka siuntėjo domeną. Patvirtino';
$labels['thirdparty'] = 'Pasirašyta trečiųjų šalių. Parašas yra skirtas kitam domenui nei pašto adrese. Patvirtintas';
$labels['temporaryinvalid'] = 'Parašas yra (laikinai) negaliojantis! Patvirtino';
$labels['invalidsignature'] = 'Parašas yra negaliojantis! Patvirtino';

$labels['for'] = 'už';
$labels['by'] = 'pagal';

$labels['section_title']  = 'Autentifikacijos būsena';
$labels['column_title']  = 'Autentifikacijos rezultatas';

$labels['title_enable_column']  = 'Įgalinti stulpelį';
$labels['label_enable_column']  = 'Rodyti autentifikacijos rezultato stulepį laiškų sąraše';
$labels['label_fallback_verifier'] = 'Naudokite vidinį, DKIM verifikatorių (jeigu parašas yra, bet nėra autentifikacijos rezultatų antraštės)';

$labels['title_trusted_mtas'] = 'Patikimi mta';
$labels['label_trusted_mtas'] = 'Kableliais atskirtų mta, kuriais pasitikite, authserv-id sąrašas. Bus rodomi rezultatai tik iš šių mta';

$labels['title_include_status'] = 'Rodyti tik šias būsenas';
$labels['label_include_status1']  = 'Parašas nerastas';
$labels['label_include_status2']  = 'Parašas rastas, bet ne autentifikacijos rezultato antraštė';
$labels['label_include_status4']  = 'Visi parašai sėkmingai praėjo patikrą';
$labels['label_include_status8']  = 'Bent vienas parašas praėjo patikrą';
$labels['label_include_status16']  = 'Trečiųjų šalių parašas';
$labels['label_include_status32']  = 'Pasirašymo funkcija laikinai neveikia';
$labels['label_include_status64']  = 'Pasirašymo funkcija yra sugędusi.';

?>
