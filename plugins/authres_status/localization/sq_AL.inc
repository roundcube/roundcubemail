<?php

$labels = array();
$labels['nosignature'] = 'Pa të dhëna nënshkrimi';
$labels['noauthresults'] = 'U gjet nënshkrim, pro jo krye auth. results. Problem me formësimin e shërbyesit?';
$labels['signaturepass'] = 'Nënshkrim(e) i vlefshëm prej përkatësisë së dërguesit. Verifikuar nga';
$labels['partialpass'] = 'Disa nga nënshkrimet janë të pavlefshme, por të paktën njëra është e vlefshme për përkatësinë e dërguesit. Verifikuar nga';
$labels['thirdparty'] = 'Nënshkruar nga palë e tretë, nënshkrimi është i pranishëm, por për një përkatësi tjetër nga ajo e adresës. Verifikuar nga';
$labels['temporaryinvalid'] = 'Nënshkrimi është (përkohësisht) i pavlefshëm! Verifikuar nga';
$labels['invalidsignature'] = 'Nënshkrimi është i pavlefshëm! Verifikuar nga';

$labels['for'] = 'për';
$labels['by'] = 'nga';

$labels['section_title']  = 'Gjendje Mirëfilltësimi';
$labels['column_title']  = 'Përfundim Mirëfilltësimi';

$labels['title_enable_column']  = 'Aktivizoje Shtyllën';
$labels['label_enable_column']  = 'Aktivizoni shtyllë përfundimesh mirëfilltësimi te lista e mesazheve';
$labels['label_fallback_verifier'] = 'Përdor verifikues të brendshëm DKIM (nëse gjendet nënshkrim, por jo krye auth. results)';

$labels['title_trusted_mtas'] = 'mta të besuara';
$labels['label_trusted_mtas'] = 'Listë identitetesh authserv-id e ndarë me presje mta-sh që u besoni. Do të shfaqen vetëm përfundime prej këtyre mta-ve';

$labels['title_include_status'] = 'Shfaq vetëm gjendjet vijuese';
$labels['label_include_status1']  = 'S’u gjet nënshkrim';
$labels['label_include_status2']  = 'U gjet nënshkrim, pro jo krye auth. results';
$labels['label_include_status4']  = 'Krejt nënshkrimet e kaluan vleftësimin';
$labels['label_include_status8']  = 'Të paktën një nënshkrim e kaloi vleftësimin';
$labels['label_include_status16']  = 'Nënshkrim nga palë e tretë';
$labels['label_include_status32']  = 'Nënshkrimi dështoi përkohësisht';
$labels['label_include_status64']  = 'Nënshkrimi dështoi përgjithmonë';

?>
