<?php

$labels = array();
$labels['nosignature'] = 'Puudub info signatuuride kohta';
$labels['noauthresults'] = 'Signatuur leitud aga puudub valideerimise vastuse päis. Probleem serveri seadistusega?';
$labels['signaturepass'] = 'Kontrollitud signatuur(id) saatja domeenilt. valideeritud';
$labels['partialpass'] = 'Mõned signatuurid ei valideeru, aga vähemalt üks on valiidne. verifitseeritud ';
$labels['thirdparty'] = 'Signeeritud kolmanda osapoole poolt, signatuur on olemas aga teisele domeenile kui saatja aadress. verifitseeritud ';
$labels['temporaryinvalid'] = 'Signatuur ei ole (ajutiselt) valiidne. verifitseeritud ';
$labels['invalidsignature'] = 'Signatuur ei ole valiidne, verifitseeritud ';

$labels['for'] = 'kellele';
$labels['by'] = 'poolt';

$labels['section_title']  = 'Tuvastamise staatus';
$labels['column_title']  = 'Tuvastamise tulemus';

$labels['title_enable_column']  = 'Luba veerg';
$labels['label_enable_column']  = 'Luba tuvastamise tulemuse veerg nimekirjas';
$labels['label_fallback_verifier'] = 'Kasuta sisemist DKIM verifitseeriat (kui signatuur on leitud aga puudub tuvastamise vastuse päis)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Näita ainult antud staatuseid';
$labels['label_include_status1']  = 'Signatuuri ei leitud';
$labels['label_include_status2']  = 'Signatuur leiti aga puudub tuvastamise vastuse päis';
$labels['label_include_status4']  = 'Kõik signatuurid läbisid valideerimise';
$labels['label_include_status8']  = 'Vähemalt üks signatuuridest läbis valideerimise';
$labels['label_include_status16']  = 'Kolmanda osapoole signatuur';
$labels['label_include_status32']  = 'Signatuuri tuvastamisel ajutine viga';
$labels['label_include_status64']  = 'Signatuuri tuvastamisel lõpplik viga';

?>
