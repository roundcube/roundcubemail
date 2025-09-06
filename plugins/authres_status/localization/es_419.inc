<?php

$labels = array();
$labels['nosignature'] = 'No hay información de firma';
$labels['noauthresults'] = 'Firma encontrada pero no autorizada. ¿Problema en configuración del servidor?';
$labels['signaturepass'] = 'Firma(s) válida desde el dominio del remitente. verificado por';
$labels['partialpass'] = 'Algunas firmas no son válidas pero al menos una es válida para el dominio del remitente. verificado por';
$labels['thirdparty'] = 'Firmado por una tercera parte, la firma está peresente pero para diferentes dominios de las direcciones. verificado por';
$labels['temporaryinvalid'] = '¡La firma es (temporalmente) no válida! verificado por';
$labels['invalidsignature'] = '¡La firma no es válida! verificado por';

$labels['for'] = 'para';
$labels['by'] = 'por';

$labels['section_title']  = 'Estado de la autenticación';
$labels['column_title']  = 'Autenticación. Resultado';

$labels['title_enable_column']  = 'Habilitar columna';
$labels['label_enable_column']  = 'Habilitar columna de resultado de autenticación en el listado de mensajes';
$labels['label_fallback_verifier'] = 'Usa verificador interno de DKIM (si se encuentra una firma pero no autorizada. resultados de encabezado)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Sólo mostrar los siguientes estados';
$labels['label_include_status1']  = 'No se ha encontrado una firma';
$labels['label_include_status2']  = 'Firma encontrada pero no autorizada. resultados de encabezado';
$labels['label_include_status4']  = 'Todos las firmas aprobadas la validación';
$labels['label_include_status8']  = 'Al menos una firma aprueba la validación';
$labels['label_include_status16']  = 'Firma de tercera parte';
$labels['label_include_status32']  = 'Error temporal en firma';
$labels['label_include_status64']  = 'Error permanente en firma';

?>
