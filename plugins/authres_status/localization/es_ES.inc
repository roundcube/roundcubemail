<?php

$labels = array();
$labels['nosignature'] = 'No hay información de firma';
$labels['noauthresults'] = 'Encontrada firma pero no hay cabecera de resultados de autenticación. ¿Problema de configuración del servidor?';
$labels['signaturepass'] = 'Firma(s) válida(s) del dominio del remitente. Verificadas por';
$labels['partialpass'] = 'Algunas firmas no son válidas pero al menos una es válida para el dominio del remitente. Verificada por';
$labels['thirdparty'] = 'Firmado por terceros, la firma está presente pero para un dominio diferente de la dirección remitente. Verificada por';
$labels['temporaryinvalid'] = '¡La firma no es válida (temporalmente)! Verificada por';
$labels['invalidsignature'] = '¡La firma no es válida! Verificada por';

$labels['for'] = 'para';
$labels['by'] = 'por';

$labels['section_title']  = 'Estado de Autenticación';
$labels['column_title']  = 'Resultado de Autenticación';

$labels['title_enable_column']  = 'Habilitar Columna';
$labels['label_enable_column']  = 'Habilitar columna de resultado de autenticación en la lista de mensajes';
$labels['label_fallback_verifier'] = 'Usar verificador interno DKIM (si se encuentra una firma, pero no un encabezamiento con los resultados de autor)';

$labels['title_trusted_mtas'] = 'Servidores de correo de confianza';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Mostrar sólo los siguientes estados';
$labels['label_include_status1']  = 'Firma no encontrada';
$labels['label_include_status2']  = 'Firma encontrada pero no la cabecera de resultados de autenticación';
$labels['label_include_status4']  = 'Todas las firmas han pasado la validación';
$labels['label_include_status8']  = 'Al menos una firma ha pasado la validación';
$labels['label_include_status16']  = 'Firma de terceros';
$labels['label_include_status32']  = 'Error temporal de la firma';
$labels['label_include_status64']  = 'Error permanente de la firma';

?>
