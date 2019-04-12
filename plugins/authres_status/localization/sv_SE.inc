<?php

$labels = array();
$labels['nosignature'] = 'Ingen signaturinformation';
$labels['noauthresults'] = 'Signatur hittades men utan autentisering. Konfigurationsproblem i server?';
$labels['signaturepass'] = 'Giltig signatur (giltiga signaturer) för avsändarens domän. Verifierad av';
$labels['partialpass'] = 'Några signaturer är ogiltiga men minst en signatur är giltig för avsändarens domän. Verifierad av';
$labels['thirdparty'] = 'Signerad av oberoende part, signatur finns men för annan domän än i avsändaradressen. Verifierad av';
$labels['temporaryinvalid'] = 'Signaturen är (tillfälligt) ogiltig! Verifierad av';
$labels['invalidsignature'] = 'Signaturen är ogiltig! Verifierad av';

$labels['for'] = 'till';
$labels['by'] = 'av';

$labels['section_title']  = 'Autentiseringsstatus';
$labels['column_title']  = 'Autentiseringsresultat';

$labels['title_enable_column']  = 'Aktivera kolumn';
$labels['label_enable_column']  = 'Aktivera kolumn för autentiseringsresultat i meddelandelistan';
$labels['label_fallback_verifier'] = 'Använd intern verifiering med DKIM (om signatur hittades men utan autentisering)';

$labels['title_trusted_mtas'] = 'Betrodda mailservrar';
$labels['label_trusted_mtas'] = 'Lista på betrodda mailservrar (kommaseparerade authserv-id). Endast resultat från dessa kommer att visas.';

$labels['title_include_status'] = 'Visa endast följande status';
$labels['label_include_status1']  = 'Ingen signatur hittades';
$labels['label_include_status2']  = 'Signatur hittades men utan autentisering';
$labels['label_include_status4']  = 'Alla signaturer är godkända';
$labels['label_include_status8']  = 'Minst en signatur är godkänd';
$labels['label_include_status16']  = 'Signatur av oberoende part';
$labels['label_include_status32']  = 'Signatur tillfälligt ur funktion';
$labels['label_include_status64']  = 'Signatur permanent ur funktion';

?>
