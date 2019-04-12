<?php

$labels = array();
$labels['nosignature'] = 'Non hai información sobre a sinatura';
$labels['noauthresults'] = 'Atopouse unha sinatura, pero non a cabeceria de resultados da autenticación. Poida que haxa un problema de configuración no servidor.';
$labels['signaturepass'] = 'Sinatura(s) válidas para o dominio do remitente. Verificado por';
$labels['partialpass'] = 'Algunha das sinaturas é invalida, pero polo menos unha é válida para o dominio do remitente. Verificado por';
$labels['thirdparty'] = 'Asinado por unha terceira parte. A sinatura está presente, pero para un dominio diferente ao do remitente. Verificado por';
$labels['temporaryinvalid'] = 'A sinatura non é momentaneamente válida! Comprobado por';
$labels['invalidsignature'] = 'A sinatura non é valida! Comprobado por';

$labels['for'] = 'para';
$labels['by'] = 'de';

$labels['section_title']  = 'Estado de autenticación';
$labels['column_title']  = 'Resultado da autenticación';

$labels['title_enable_column']  = 'Activar columna';
$labels['label_enable_column']  = 'Amosar columna co resultado da autenticación na lista de mensaxes';
$labels['label_fallback_verifier'] = 'Usar o verificador DKIM interno (si se atopou a sinatura pero non a cabeceira de resultados de autenticación)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Amosar solo as que estean no seguinte estado';
$labels['label_include_status1']  = 'Non se atopou a sinatura';
$labels['label_include_status2']  = 'Atopouse a sinatura, pero non a cabeceria de resultados de autenticación.';
$labels['label_include_status4']  = 'Tódalas firmas validaron correctamente';
$labels['label_include_status8']  = 'Ao menos unha sinatura validou';
$labels['label_include_status16']  = 'Sinatura de terceira parte';
$labels['label_include_status32']  = 'A sinatura fallou temporalmente';
$labels['label_include_status64']  = 'A sinatura fallou permanentemente';

?>
