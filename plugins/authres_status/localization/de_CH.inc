<?php

$labels = array();
$labels['nosignature'] = 'Keine Informationen zur Signatur';
$labels['noauthresults'] = 'Signatur gefunden aber keine Auth-Results Kopfzeilen. Server-Konfigurations-Problem?';
$labels['signaturepass'] = 'Gültige Signatur(en) der Absenderdomäne. Geprüft von';
$labels['partialpass'] = 'Einige Signaturen sind ungültig aber mind. eine ist gültig für die Absenderdomäne. Geprüft von';
$labels['thirdparty'] = 'Signiert von einer Drittpartei! Signatur liegt vor, ist jedoch von einer anderen Domäne als die Absenderadresse. Geprüft für';
$labels['temporaryinvalid'] = 'Signatur ist (temporär) ungültig! Geprüft von';
$labels['invalidsignature'] = 'Ungültige Signatur! Verifiziert durch';

$labels['for'] = 'für';
$labels['by'] = 'von';

$labels['section_title']  = 'Authenifizierungsstatus';
$labels['column_title']  = 'Auth. Resultat';

$labels['title_enable_column']  = 'Spalte anzeigen';
$labels['label_enable_column']  = 'Authenifizierungsresultat in der Nachrichtenliste anzeigen';
$labels['label_fallback_verifier'] = 'Interne DKIM-Prüfung verwenden (falls Signatur vorhanden aber keine Auth-Results Kopfzeilen)';

$labels['title_trusted_mtas'] = 'Vertraute MTAs';
$labels['label_trusted_mtas'] = 'Komma-separierte authserv-id Liste von MTAs, denen Sie vertrauen. Nur Resultate von diesen MTAs werden angezeigt.';

$labels['title_include_status'] = 'Nur die folgenden Status anzeigen';
$labels['label_include_status1']  = 'Keine Signatur gefunden';
$labels['label_include_status2']  = 'Signatur gefunden aber keine Auth-Results Kopfzeilen';
$labels['label_include_status4']  = 'Alle Signaturen haben die Prüfung bestanden';
$labels['label_include_status8']  = 'Mind. eine Signature hat die Prüfung bestanden';
$labels['label_include_status16']  = 'Drittpartei-Signatur';
$labels['label_include_status32']  = 'Signatur temporär fehlgeschlagen';
$labels['label_include_status64']  = 'Signatur permanent fehlgeschlagen';

?>
