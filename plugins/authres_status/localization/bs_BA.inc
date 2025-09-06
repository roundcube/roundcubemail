<?php

$labels = array();
$labels['nosignature'] = 'Nema informacija o potpisu';
$labels['noauthresults'] = 'Potpis je pronađen ali nema rezultata za zaglavlje autentifikacije. Problem u konfiguraciji servera?';
$labels['signaturepass'] = 'Ispravan potpis sa domene pošiljaoca. verificirano od strane';
$labels['partialpass'] = 'Neki potpisi su netačni ali je barem jedan ispravan sa domene pošiljaoca. verificirano od strane';
$labels['thirdparty'] = 'Potpisano od strane trećeg lica, potpis je pristupan, ali za drugu adresu. verificirano od strane';
$labels['temporaryinvalid'] = 'Potpis (trenutno) nije ispravan! verificirano od strane';
$labels['invalidsignature'] = 'Potpis nije ispravan! verificirano od strane';

$labels['for'] = 'za';
$labels['by'] = 'od';

$labels['section_title']  = 'Status autentifikacije';
$labels['column_title']  = 'Rezultat autent.';

$labels['title_enable_column']  = 'Uključi kolonu';
$labels['label_enable_column']  = 'Omogući kolonu za autentifikacijske rezultate u listi poruka';
$labels['label_fallback_verifier'] = 'Koristi interni DKIM verifikator (ako je potpis pronađen, ali nema zaglavlja rezultata autentifikacije)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Samo prikaži sljedeće statuse';
$labels['label_include_status1']  = 'Nema pronađenih potpisa';
$labels['label_include_status2']  = 'Potpis pronađen, ali nema zaglavlja autentifikacijskih rezultata';
$labels['label_include_status4']  = 'Svi potpisi su prošli validaciju';
$labels['label_include_status8']  = 'Barem jedan potpis je prošao validaciju';
$labels['label_include_status16']  = 'Potpis trećeg lica';
$labels['label_include_status32']  = 'Potpis privremeno netačan';
$labels['label_include_status64']  = 'Potpis trajno netačan';

?>
