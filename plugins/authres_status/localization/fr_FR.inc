<?php

$labels = array();
$labels['nosignature'] = 'Aucune information de signature';
$labels['noauthresults'] = 'Une signature a été trouvée, mais aucun en-tête de résultat d’authentification. Il y a-t-il un problème de configuration du serveur ?';
$labels['signaturepass'] = 'Signature(s) valide(s) du domaine de l’expéditeur. Vérifié par';
$labels['partialpass'] = 'Certaines signatures sont invalides mais au moins une est valide pour le domaine de l’expéditeur. Vérifié par';
$labels['thirdparty'] = 'Signé par un tiers, une signature est présente mais pour un domaine différent que l’adresse. Vérifié par';
$labels['temporaryinvalid'] = 'La signature est (temporairement) invalide ! Vérifié par';
$labels['invalidsignature'] = 'La signature est invalide ! Vérifié par';

$labels['for'] = 'pour';
$labels['by'] = 'par';

$labels['section_title']  = 'État d’authentification';
$labels['column_title']  = 'Résultat d’authentification';

$labels['title_enable_column']  = 'Activer la colonne';
$labels['label_enable_column']  = 'Activer la colonne de résultat d’authentification dans la liste des courriels';
$labels['label_fallback_verifier'] = 'Utiliser le vérificateur DKIM interne (si une signature est trouvée mais aucun en-tête de résultat d’authentification)';

$labels['title_trusted_mtas'] = 'Mta de confiance';
$labels['label_trusted_mtas'] = 'Liste séparée par des virgules des authserv-id des mta en qui vous avez confiance. Seuls les résultats de ces mta seront affichés';

$labels['title_include_status'] = 'Montrer seulement les états suivants';
$labels['label_include_status1']  = 'Aucune signature trouvée';
$labels['label_include_status2']  = 'Une signature a été trouvée, mais aucun en-tête de résultat d’authentification.';
$labels['label_include_status4']  = 'Toutes les signatures ont été validées';
$labels['label_include_status8']  = 'Au moins une signature a été validée';
$labels['label_include_status16']  = 'Signature d’un tiers';
$labels['label_include_status32']  = 'Échec temporaire de la signature';
$labels['label_include_status64']  = 'Échec permanent de la signature';

?>
