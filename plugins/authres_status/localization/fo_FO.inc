<?php

$labels = array();
$labels['nosignature'] = 'Eingin upplýsing um undirskrift ';
$labels['noauthresults'] = 'Undirskriftir funnar, men eingin úrslit fyri samleikis høvdara. Kanska er tað ambætara trupuleikar?';
$labels['signaturepass'] = 'Góðkendar undirskriftir frá sendara økisnavn. Vátta av';
$labels['partialpass'] = 'Onkrar undirskriftir eru ógildigar, men minst ein er góðkend av økisnavninum hjá sendarinum. Góðkent av';
$labels['thirdparty'] = 'Undirskriva av triðaparti. Undirskriftin er tilstaðar, men fyri onnur økisnøvn enn fyri bústaðin. Góðkent av';
$labels['temporaryinvalid'] = 'Undirskrift er (fyribils) ikki gildig. Vátta av ';
$labels['invalidsignature'] = 'Undirskrift er ikki gildig. Vátta av ';

$labels['for'] = 'fyri';
$labels['by'] = 'av';

$labels['section_title']  = 'Samgildis støða';
$labels['column_title']  = 'samgildis úrslit';

$labels['title_enable_column']  = 'Tendra teigar';
$labels['label_enable_column']  = 'Tendra samgildis úrslit teiga í boð lista';
$labels['label_fallback_verifier'] = 'Brúka innanhýsis DKIM váttara (um undirskrivt var funnin men eingin samgildis úrsilt fyri høvd)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Vís einans tær fylgjandi støðurnar';
$labels['label_include_status1']  = 'Eingin undirskrivt funnin';
$labels['label_include_status2']  = 'Undirskrift funnin, men eingin samgildis úrslit';
$labels['label_include_status4']  = 'Allar undirskriftir vóru góðkendar';
$labels['label_include_status8']  = 'Minst ein undirskrift var vátta';
$labels['label_include_status16']  = 'Triðapart undirskrift';
$labels['label_include_status32']  = 'Undirskrift er fyribils miseydna';
$labels['label_include_status64']  = 'Undirskrift er permanent miseydnast ';

?>
