<?php

$labels = array();
$labels['nosignature'] = 'Žiadna informácia o podpise';
$labels['noauthresults'] = 'Podpis sa našiel, ale nenašla sa hlavička pre autentifikáciu výsledkov. Problém s konfiguráciou servera?';
$labels['signaturepass'] = 'Platný podpis (platné podpisy) z domény odosielateľa. Overené cez';
$labels['partialpass'] = 'Niektoré podpisy nie sú platné, ale aspoň jeden podpis je platný pre doménu odosielateľa. Overené cez:';
$labels['thirdparty'] = 'Podpísané treťou stranou, podpis je prítomný, ale je určený pre inú doménu ako je tá, ktorá je uvedená v adrese. Overené cez:';
$labels['temporaryinvalid'] = 'Podpis je (dočasne) neplatný! Overené cez:';
$labels['invalidsignature'] = 'Podpis je neplatný! Overené cez:';

$labels['for'] = 'pre';
$labels['by'] = 'od';

$labels['section_title']  = 'Stav autentifikácie';
$labels['column_title']  = 'Výsledok autentifikácie';

$labels['title_enable_column']  = 'Zapnúť stĺpec';
$labels['label_enable_column']  = 'Zapnúť stĺpec s výsledkom autentifikácie v zozname správ';
$labels['label_fallback_verifier'] = 'Použiť interný overovač DKIM (ak sa našiel podpis, ale žiadna hlavička s výsledkami autentifikácie)';

$labels['title_trusted_mtas'] = 'Dôveryhodní agenti na prenos správ (MTA)';
$labels['label_trusted_mtas'] = 'Čiarkou oddeľovaný zoznam ID autentif. serverov služby MTA, ktorým dôverujete. Výsledky budú obmedzené iba na takéto MTA.';

$labels['title_include_status'] = 'Zobraziť len s nasledujúcim stavom:';
$labels['label_include_status1']  = 'Nenašiel sa žiadny podpis';
$labels['label_include_status2']  = 'Našiel sa podpis, nie však hlavička s výsledkom autentifikácie';
$labels['label_include_status4']  = 'Všetky podpisy prešli overením';
$labels['label_include_status8']  = 'Najmenej jeden podpis prešiel overením';
$labels['label_include_status16']  = 'Podpis tretej strany';
$labels['label_include_status32']  = 'Podpis dočasne zlyhal';
$labels['label_include_status64']  = 'Podpis permanentne zlyhal';

?>
