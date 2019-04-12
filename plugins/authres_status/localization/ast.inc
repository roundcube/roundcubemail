<?php

$labels = array();
$labels['nosignature'] = 'Nun hai información de robla';
$labels['noauthresults'] = 'Atopóse una robla pero nun hai testera de resultaos d\'autenticación. ¿Problema de configuración del sirvidor?';
$labels['signaturepass'] = 'Robla(es) válides del dominiu del remitente. Verificaes por';
$labels['partialpass'] = 'Dalgunes robles nun son válides pero al menos una ye válida pal dominiu del remitente. Verificada por';
$labels['thirdparty'] = 'Robláu por terceros, la robla ta presente pero pa un dominiu distintu de la direición remitente. Verificada por';
$labels['temporaryinvalid'] = 'La robla nun ye válida (temporalmente) ! Verificada por';
$labels['invalidsignature'] = 'La robla nun ye válida! Verificada por';

$labels['for'] = 'pa';
$labels['by'] = 'por';

$labels['section_title']  = 'Estáu d\'autencación';
$labels['column_title']  = 'Resultáu autenticación';

$labels['title_enable_column']  = 'Habilitar columna';
$labels['label_enable_column']  = 'Habilitar columna de resultáu d\'autenticación na llista de mensaxes';
$labels['label_fallback_verifier'] = 'Use internal DKIM verifier (if signature found but no auth. results header)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Namái pa los siguientes estaos';
$labels['label_include_status1']  = 'Nun s\'atopó robla';
$labels['label_include_status2']  = 'Atopóse la robla, pero non la testera de resultaos d\'autenticación';
$labels['label_include_status4']  = 'Toles robles pasaron l\'autenticación';
$labels['label_include_status8']  = 'Al menos una robla pasó l\'autenticación';
$labels['label_include_status16']  = 'Robla de terceros';
$labels['label_include_status32']  = 'Falló la robla temporalmente';
$labels['label_include_status64']  = 'Falló la robla dafechu';

?>
