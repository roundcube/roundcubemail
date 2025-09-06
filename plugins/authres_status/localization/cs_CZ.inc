<?php

$labels = array();
$labels['nosignature'] = 'Žádné informace o podpisu';
$labels['noauthresults'] = 'Podpis nalezen, ale chybí hlavička výsledku ověření. Chyba konfigurace serveru?';
$labels['signaturepass'] = 'Platné podpisy z domény odesilatele. ověřeno ';
$labels['partialpass'] = 'Některé podpisy jsou neplatné, ale alespoň jeden je platný pro doménu odesilatele. ověřeno ';
$labels['thirdparty'] = 'Podepsáno třetí stranou, podpis je platný ale pro jinou doménu než má odesilatel. ověřeno pro ';
$labels['temporaryinvalid'] = 'Podpis není (dočasně) platný! ověřeno ';
$labels['invalidsignature'] = 'Podpis není platný! ověřeno ';

$labels['for'] = 'pro';
$labels['by'] = 'od';

$labels['section_title']  = 'Stav ověření';
$labels['column_title']  = 'Ověření';

$labels['title_enable_column']  = 'Zapnout sloupec';
$labels['label_enable_column']  = 'Zapnout sloupec s výsledkem ověření v seznamu zpráv';
$labels['label_fallback_verifier'] = 'Použít vestavěný ověřovač DKIM (pokud je nalezen podpis ale chybí hlavička výsledku ověření)';

$labels['title_trusted_mtas'] = 'Důvěryhodná MTA';
$labels['label_trusted_mtas'] = 'Čárkou oddělený seznam authserv-id MTA, kterým důvěřujete. Zobrazeny budou pouze výsledky pro uvedená MTA   ';

$labels['title_include_status'] = 'Zobrazit pouze následující stavy';
$labels['label_include_status1']  = 'Žádný podpis nenalezen';
$labels['label_include_status2']  = 'Podpis nalezen, ale chybí hlavička výsledku ověření';
$labels['label_include_status4']  = 'Všechny podpisy prošli ověřením';
$labels['label_include_status8']  = 'Alespoň jeden podpis prošel ověřením';
$labels['label_include_status16']  = 'Podpis třetí strany';
$labels['label_include_status32']  = 'Podpis dočasně selhal';
$labels['label_include_status64']  = 'Podpis trvale selhal';

?>
