<?php

$labels = array();
$labels['nosignature'] = 'Nessuna firma diponibile';
$labels['noauthresults'] = 'Alcune firme sono state trovate, ma nessu auth. header. C\'è un problema di configurazione?';
$labels['signaturepass'] = 'Firme valide dal dominio del mittente. Verificate da';
$labels['partialpass'] = 'Alcune firme non sono valide, ma almeno una risulta valida per il dominio del mittente. Verificato da';
$labels['thirdparty'] = 'Firmato da terzi, la firma è presente ma per il dominio diverso da quello dell\'indirizzo. verificate per';
$labels['temporaryinvalid'] = 'La firma non è valida (temporaneamente)! verificato da';
$labels['invalidsignature'] = 'La firma non è valida! verificato da';

$labels['for'] = 'per';
$labels['by'] = 'da';

$labels['section_title']  = 'Stato autenticazione';
$labels['column_title']  = 'Risultato autent.';

$labels['title_enable_column']  = 'Abilita colonna';
$labels['label_enable_column']  = 'Abilitare la colonna risultato autenticazione nell\'elenco dei messaggi';
$labels['label_fallback_verifier'] = 'Usa verifica interna DKIM (se la firma è stata trovata ma senza intestazione con il risultato dell\'autenticazione)';

$labels['title_trusted_mtas'] = 'MTA affidabile';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Mostra soltanto i seguenti stati';
$labels['label_include_status1']  = 'Nessuna firma trovata';
$labels['label_include_status2']  = 'Firma trovata ma non presenta alcuna intestazione con il risultato dell\'autenticazione';
$labels['label_include_status4']  = 'Tutte le firme hanno superato la convalida';
$labels['label_include_status8']  = 'Almeno una firma ha superato la convalida';
$labels['label_include_status16']  = 'Firma da terzi';
$labels['label_include_status32']  = 'Firma temporaneamente non riuscita';
$labels['label_include_status64']  = 'Firma non riuscita in modo permanente';

?>
