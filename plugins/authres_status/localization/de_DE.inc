<?php

$labels = array();
$labels['nosignature'] = 'Keine Signatur-Informationen';
$labels['noauthresults'] = 'Signatur gefunden aber keinen "auth. results header". Serverkonfiguration korrekt?';
$labels['signaturepass'] = 'Gültige Signatur(en) von der Domain des Absenders. Verifiziert durch';
$labels['partialpass'] = 'Einige Signaturen sind ungültig aber es wurde zumindest eine gültige Signatur für die Domain des Absenders gefunden. Verifiziert durch';
$labels['thirdparty'] = 'Signiert durch einen Dritten. Signatur ist vorhanden aber für eine andere Domain als die der Adresse. Verifiziert für';
$labels['temporaryinvalid'] = 'Signatur ist (vorübergehend) nicht gültig! Verifiziert durch';
$labels['invalidsignature'] = 'Signatur ist nicht gültig! Verifiziert durch';

$labels['for'] = 'für';
$labels['by'] = 'durch';

$labels['section_title']  = 'Status der Authentisierung';
$labels['column_title']  = 'Auth. Ergebnis';

$labels['title_enable_column']  = 'Aktiviere Spalte';
$labels['label_enable_column']  = 'Aktiviere Spalte für das Ergebnis der Authentisierung in der Nachrichtenliste';
$labels['label_fallback_verifier'] = 'Verwende den internen DKIM-Überprüfer (wenn eine Signatur vorhanden ist, aber kein Header mit dem Ergebnis der Authentifizierung)';

$labels['title_trusted_mtas'] = 'Vertraute mta\'s';
$labels['label_trusted_mtas'] = ' Komma separierte authserv-id Liste von mta\'s denen vertraut wird. Nur Ergebnisse dieser mta\'s werden angezeigt';

$labels['title_include_status'] = 'Zeige nur die folgenden Status an';
$labels['label_include_status1']  = 'Keine Signatur gefunden';
$labels['label_include_status2']  = 'Signatur gefunden aber keinen "auth. results header"';
$labels['label_include_status4']  = 'Alle Signaturen haben die Validierung bestanden';
$labels['label_include_status8']  = 'Zumindest eine Signatur hat die Validierung bestanden';
$labels['label_include_status16']  = 'Signatur durch Dritten';
$labels['label_include_status32']  = 'Signatur vorübergehend fehlgeschlagen';
$labels['label_include_status64']  = 'Signatur dauerhaft fehlgeschlagen';

?>
