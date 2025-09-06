<?php

$labels = array();
$labels['nosignature'] = 'Sin información de firma';
$labels['noauthresults'] = 'Firma encontrada pero no hay encabezado de autorización. ¿Problema en configuración del servidor?';
$labels['signaturepass'] = 'Firma(s) válidas desde el dominio remitente. verificado por';
$labels['partialpass'] = 'Algunas firmas no son válidas pero al menos una es válida para el dominio del remitente. verificado por';
$labels['thirdparty'] = 'Firmado por una tercera parte, la firma está presente pero para diferentes dominios que el de la dirección. verificado por';
$labels['temporaryinvalid'] = '¡La firma está (temporalmente) no válida! verificado por';
$labels['invalidsignature'] = '¡La firma no es válida! verificado por';

$labels['for'] = 'para';
$labels['by'] = 'por';

$labels['section_title']  = 'Estado de la autenticación';
$labels['column_title']  = 'Autenticación. Resultado';

$labels['title_enable_column']  = 'Habilitar columna';
$labels['label_enable_column']  = 'Habilitar la columna de resultado de la autenticación en la lista de mensajes';
$labels['label_fallback_verifier'] = 'Usa verificador DKIM interno (si se encuentra la firma pero no está autorizada. resultados del encabezado)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Sólo mostrar los siguientes estados';
$labels['label_include_status1']  = 'No se encontró una firma';
$labels['label_include_status2']  = 'Firma encontrada pero no autorizada. resultados del encabezado';
$labels['label_include_status4']  = 'Todas las firmas han pasado la validación';
$labels['label_include_status8']  = 'Al menos una firma ha pasado la validación';
$labels['label_include_status16']  = 'Firma de tercera parte';
$labels['label_include_status32']  = 'Fallo temporario de la firma';
$labels['label_include_status64']  = 'Fallo permanente de la firma';

?>
