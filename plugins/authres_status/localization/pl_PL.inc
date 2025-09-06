<?php

$labels = array();
$labels['nosignature'] = 'Brak informacji o podpisie';
$labels['noauthresults'] = 'Znaleziono podpis, jednak bez autoryzacji. Możliwe, że problem z konfiguracją serwera?';
$labels['signaturepass'] = 'Aktualne sygnatury domeny nadawcy. Zweryfikowane przez';
$labels['partialpass'] = 'Niektóre sygnatury są nieaktualne, lecz przynajmniej jedna z nich jest aktualna dla domeny nadawcy. Zweryfikowane przez';
$labels['thirdparty'] = 'Podpisane przez podmiot trzeci, sygnatura jest dostępna, jednak przeznaczona jest dla innej domeny niż tej obecnej w adresie. Zweryfikowane dla';
$labels['temporaryinvalid'] = 'Podpis jest (tymczasowo) niepoprawny! sprawdzone przez';
$labels['invalidsignature'] = 'Podpis jest niepoprawny! sprawdzone przez';

$labels['for'] = 'dla';
$labels['by'] = 'przez';

$labels['section_title']  = 'Status autoryzacji';
$labels['column_title']  = 'Autoryzacja. Wynik';

$labels['title_enable_column']  = 'Włącz kolumnę';
$labels['label_enable_column']  = 'Włącz kolumnę wyniku autoryzacji na liście wiadomości';
$labels['label_fallback_verifier'] = 'Używaj wewnętrznego weryfikatora DKIM (jeżeli sygnatura istnieje, lecz brak nagłówka wyników autoryzacji)';

$labels['title_trusted_mtas'] = 'Zaufane MTA';
$labels['label_trusted_mtas'] = 'Rozdzielona przecinkiem lista authserv-id zaufanych MTA. Tylko wyniki z tych MTA będą pokazane.';

$labels['title_include_status'] = 'Pokaż tylko następujące statusy';
$labels['label_include_status1']  = 'Nie znaleziono podpisu';
$labels['label_include_status2']  = 'Znaleziono podpis, jednak bez autoryzacji.';
$labels['label_include_status4']  = 'Wszystkie podpisy przeszły sprawdzenie';
$labels['label_include_status8']  = 'Co najmniej jeden podpis przeszedł sprawdzenie';
$labels['label_include_status16']  = 'Podpis osoby trzeciej';
$labels['label_include_status32']  = 'Sygnatura tymczasowo nieprawidłowa';
$labels['label_include_status64']  = 'Sygnatura permanentnie nieprawidłowa';

?>
