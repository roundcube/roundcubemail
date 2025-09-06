<?php

$labels = array();
$labels['nosignature'] = 'Nincs aláírásról információ';
$labels['noauthresults'] = 'Az aláírás megvan, de nincs hiteles válasz fejléc. Talán kiszolgáló konfigurációs hiba?';
$labels['signaturepass'] = 'Érvényes aláírás(ok) a küldők domainjából. Ellenőrizve a következő által';
$labels['partialpass'] = 'Néhány aláírás nem valid, de van legalább egy ami valid a küldő tartományához. ellenőrizve a következő által';
$labels['thirdparty'] = 'Harmadik fél által aláírt, az aláírás megtalálható de más domain számára mint a küldő címe. Ellenőrízve a következőre';
$labels['temporaryinvalid'] = 'Az aláírás(átmenetileg) nem érvényes. Ellenőrízve a következő által';
$labels['invalidsignature'] = 'Az aláírás nem érvényes. Ellenörizve a következővel';

$labels['for'] = 'számára';
$labels['by'] = 'általa';

$labels['section_title']  = 'Hitelesítési Állapot';
$labels['column_title']  = 'Hitelesítés. Eredmény';

$labels['title_enable_column']  = 'Oszlop engedélyezése';
$labels['label_enable_column']  = 'A hitelesítés eredménye oszlop engedélyezése az üzenet listában';
$labels['label_fallback_verifier'] = 'A belső DKIM ellenörző használata ( ha van aláírás, de nincs hitelesítési eredmény fejléc )';

$labels['title_trusted_mtas'] = 'Megbizható mta-k';
$labels['label_trusted_mtas'] = 'A megbizható mta-k vesszővel elválasztott  authserv-id listája. Csak a felsorolt mta-któl kapott eredmények lesznek láthatóak.';

$labels['title_include_status'] = 'Csak a következő stászok megjelenítése';
$labels['label_include_status1']  = 'Nem található aláírás';
$labels['label_include_status2']  = 'Aláírás  megtalálva, de nem hiteles. Az eredmények fejléce';
$labels['label_include_status4']  = 'Az összes aláírás megfelelt az ellenörzésen';
$labels['label_include_status8']  = 'Legalább egy aláírás megfelelt az ellenörzésen';
$labels['label_include_status16']  = 'Külsö aláírás';
$labels['label_include_status32']  = 'Átmenetileg az aláírás sikertelen';
$labels['label_include_status64']  = 'Végérvényesn az aláírás sikertelen';

?>
