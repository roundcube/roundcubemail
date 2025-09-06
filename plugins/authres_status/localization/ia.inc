<?php

$labels = array();
$labels['nosignature'] = 'Nulle information de signatura';
$labels['noauthresults'] = 'Signatura trovate ma nulle capite de resultato de authentication. Problema de configuration del servitor?';
$labels['signaturepass'] = 'Signatura(s) valide ab le dominio del expeditor, verificate per';
$labels['partialpass'] = 'Alcun signaturas non es valide ma al minus un es valide pro le dominio del expeditor. Verificate per';
$labels['thirdparty'] = 'Signate per un tertio. Le signatura es presente ma es pro un altere dominio que illo del adresse "De:". Verificate pro';
$labels['temporaryinvalid'] = 'Le signatura (temporarimente) non es valide. Verificate per';
$labels['invalidsignature'] = 'Le signatura non es valide. Verificate per';

$labels['for'] = 'pro';
$labels['by'] = 'per';

$labels['section_title']  = 'Stato de authentication';
$labels['column_title']  = 'Resultato de authentication';

$labels['title_enable_column']  = 'Activar columna';
$labels['label_enable_column']  = 'Activar le columna de resultato de authentication in le lista de messages';
$labels['label_fallback_verifier'] = 'Usar le verificator DKIM interne (si le signatura es trovate ma il manca un capite de resultato de authentication)';

$labels['title_trusted_mtas'] = 'Servitores e-mail digne de fide';
$labels['label_trusted_mtas'] = 'Lista de identificatores "authserv", separate per commas, de agentes de transporto de e-mail (MTA) in que tu ha confidentia. Solmente resultatos de iste MTAs essera monstrate';

$labels['title_include_status'] = 'Monstrar solmente le sequente statos';
$labels['label_include_status1']  = 'Nulle signatura trovate';
$labels['label_include_status2']  = 'Signatura trovate ma il manca un capite de resultato de authentication';
$labels['label_include_status4']  = 'Tote le signaturas ha essite validate correctemente';
$labels['label_include_status8']  = 'Al minus un signatura ha essite validate correctemente';
$labels['label_include_status16']  = 'Signatura de tertio';
$labels['label_include_status32']  = 'Le signatura ha temporarimente fallite';
$labels['label_include_status64']  = 'Le signatura ha permanentemente fallite';

?>
