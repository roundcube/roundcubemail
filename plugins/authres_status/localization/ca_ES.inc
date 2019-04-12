<?php

$labels = array();
$labels['nosignature'] = 'No hi ha informació de signatura';
$labels['noauthresults'] = 'La signatura s\'ha trobat però no hi ha capçaleres d\'autenticació. Problema de configuració del servidor?';
$labels['signaturepass'] = 'Signatura(es) vàlida(es) del domini del remitent. Verificat per';
$labels['partialpass'] = 'Algunes de les signatures no són vàlides però almenys una ho és pel domini del remitent. Verificat per';
$labels['thirdparty'] = 'Signada per tercers, la signatura és present però per un domini diferent de l\'adreça. Verificat per';
$labels['temporaryinvalid'] = 'La signatura és (temporalment) no vàlida! Verificat per';
$labels['invalidsignature'] = 'La signatura no és vàlida! Verificat per';

$labels['for'] = 'per a';
$labels['by'] = 'per';

$labels['section_title']  = 'Estat de l\'autenticació';
$labels['column_title']  = 'Resultat de l\'autenticació';

$labels['title_enable_column']  = 'Activa la columna';
$labels['label_enable_column']  = 'Activa la columna de resultats d\'autenticació a la llista de missatges';
$labels['label_fallback_verifier'] = 'Fes servir el verificador intern per DKIM (només si s\'ha trobat la signatura però no hi ha capçalera amb resultats d\'autenticació)';

$labels['title_trusted_mtas'] = 'MTA\'s fiables';
$labels['label_trusted_mtas'] = 'Llista authserv-id d\'MTAs fiables separats per coma. Només es mostraran resultats d\'aquests MTAs.';

$labels['title_include_status'] = 'Mostra només els estats següents';
$labels['label_include_status1']  = 'No s\'ha trobat cap signatura';
$labels['label_include_status2']  = 'La signatura s\'ha trobat però sense cap capçalera d\'autenticació';
$labels['label_include_status4']  = 'Totes les signatures han passat la validació';
$labels['label_include_status8']  = 'Almenys una signatura ha passat la validació';
$labels['label_include_status16']  = 'Signatura de tercers';
$labels['label_include_status32']  = 'La signatura ha fallat temporalment';
$labels['label_include_status64']  = 'La signatura ha fallat de manera permanent';

?>
