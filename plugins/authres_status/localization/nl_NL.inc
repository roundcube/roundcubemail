<?php

$labels = array();
$labels['nosignature'] = 'Geen digitale handtekening';
$labels['noauthresults'] = 'Digitale handtekening gevonden maar geen auth.-header. Probleem met serverconfiguratie?';
$labels['signaturepass'] = 'Geldige digitale handtekening(en) voor het domein van de afzender. Gecontroleerd door ';
$labels['partialpass'] = 'Sommige handtekeningen zijn ongeldig maar tenminste één is geldig voor het domein van de afzender. Gecontroleerd door ';
$labels['thirdparty'] = 'Ondertekend door een derde partij, er is een geldige digitale handtekening maar voor een ander domein dan van de afzender. Gecontroleerd voor ';
$labels['temporaryinvalid'] = 'Digitale handtekening is (tijdelijk) ongeldig! Gecontroleerd door ';
$labels['invalidsignature'] = 'Digitale handtekening is ongeldig! Gecontroleerd door ';

$labels['for'] = 'voor';
$labels['by'] = 'door';

$labels['section_title']  = 'Authenticatiestatus';
$labels['column_title']  = 'Auth.-status';

$labels['title_enable_column']  = 'Activeer kolom';
$labels['label_enable_column']  = 'Voeg kolom met authenticatiestatus aan de berichtenlijst';
$labels['label_fallback_verifier'] = 'Gebruik interne DKIM-verificatie (wanneer handtekening gevonden is, maar geen auth.-header)';

$labels['title_trusted_mtas'] = 'Vertrouwde mta\'s';
$labels['label_trusted_mtas'] = 'Komma gescheiden authserv-id lijst van vertrouwde mta\'s. Alleen resultaten van deze mta\'s zullen worden weergegeven';

$labels['title_include_status'] = 'Geef alleen de volgende statussen weer';
$labels['label_include_status1']  = 'Geen digitale handtekening gevonden';
$labels['label_include_status2']  = 'Digitale handtekening gevonden maar geen auth.-header';
$labels['label_include_status4']  = 'Alle digitale handtekeningen zijn geldig';
$labels['label_include_status8']  = 'Tenminste één digitale handtekening is geldig';
$labels['label_include_status16']  = 'Handtekening van derde partij';
$labels['label_include_status32']  = 'Digitale handtekening tijdelijk ongeldig';
$labels['label_include_status64']  = 'Digitale handtekening permanent ongeldig';

?>
