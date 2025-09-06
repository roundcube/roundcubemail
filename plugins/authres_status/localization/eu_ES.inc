<?php

$labels = array();
$labels['nosignature'] = 'Ez dago sinadura informaziorik';
$labels['noauthresults'] = 'Sinadura topatu da baina ez baimenen emaitzen goiburua. Zerbitzarian ezarpen arazorik?';
$labels['signaturepass'] = 'Bidaltzailearen domeinuko sinadura baliozkoa(k), honek egiaztatua';
$labels['partialpass'] = 'Bidaltzailearen domeinuko sinadura batzuk ez dira baliozkoak baina bat bederen bai, honek egiaztatua';
$labels['thirdparty'] = 'Hirugarren batek sinatua, sinadura badago baina bidaltzailearen helbidekoarena ez den domeinu batentzat, honek egiaztatua';
$labels['temporaryinvalid'] = 'Sinadura ez da (behin behinean) baliozkoa, honek egiaztatua';
$labels['invalidsignature'] = 'Sinadura ez da baliozkoa, honen egiaztatua';

$labels['for'] = 'honentzat';
$labels['by'] = 'honengandik';

$labels['section_title']  = 'Egiaztapen egoera';
$labels['column_title']  = 'Egiaztapenaren emaitza';

$labels['title_enable_column']  = 'Gaitu zutabea';
$labels['label_enable_column']  = 'Gaitu emaitzen egiaztapen zutabea mezuen zerrendan';
$labels['label_fallback_verifier'] = 'Erabili barneko DKIM egiaztatzailea (baldin eta sindura topatu bada baina ez egiaztapenen emaitzen goiburua)';

$labels['title_trusted_mtas'] = 'MTA fidagarriak';
$labels['label_trusted_mtas'] = 'Zure konfidantzako MTA-en  autoserv-ld komaz bereizitako zerrenda. Bakarrik bistaratuko dira MTA hauen emaitzak.';

$labels['title_include_status'] = 'Erakutsi bakarrik ondoko egoerak';
$labels['label_include_status1']  = 'Ez da sinadurarik topatu';
$labels['label_include_status2']  = 'Sinadura topatu da baina ez egiaztapenen emaitzen goiburua';
$labels['label_include_status4']  = 'Sinadura guztiek pasa dute egiaztapena';
$labels['label_include_status8']  = 'Gutxienez sinadura batek pasa du egiaztapena';
$labels['label_include_status16']  = 'Hirugarren baten sinadura';
$labels['label_include_status32']  = 'Sinadurak behin behinean huts egin du';
$labels['label_include_status64']  = 'Sinadura behin betiko huts egin du';

?>
