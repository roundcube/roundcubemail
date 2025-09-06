<?php

$labels = array();
$labels['nosignature'] = 'Ni podatkov za podpis';
$labels['noauthresults'] = 'Podpis je bil najden, vendar nobenih rezultatov preverjanja pristonosti v glavi sporočila. Morda gre za težavo v konfiguraciji strežnika?';
$labels['signaturepass'] = 'Veljavni podpis(i) iz naslova domene pošiljatelja. Preverjeno iz strani';
$labels['partialpass'] = 'Nekateri podpisi niso veljavni, vsaj en podpis z domeno pošiljatelja je veljaven. Preverjeno iz strani ';
$labels['thirdparty'] = 'Podpisano iz neodvisnega naslova. Podpis je dodan, vendar za drugo domeno, kot je navedena v elektronskem naslovu. Preverjeno za';
$labels['temporaryinvalid'] = 'Podpis je (trenutno) neveljaven. Preverjeno iz strani';
$labels['invalidsignature'] = 'Podpis ni veljaven. Preverjeno iz strani';

$labels['for'] = 'za';
$labels['by'] = 'od';

$labels['section_title']  = 'Status avtentikacije';
$labels['column_title']  = 'Rezultat avtentikacije';

$labels['title_enable_column']  = 'Omogoči stolpec';
$labels['label_enable_column']  = 'Omogoči preverjanje pristnosti rezultatov v seznamu sporočil';
$labels['label_fallback_verifier'] = 'Za preverjanje uporabite interni pregledovalnik DKIM (v primeru da bo podpis najden, v glavi sporočila pa ne bo nobenih rezultatov preverjanja pristnosti)';

$labels['title_trusted_mtas'] = 'Zaupanja vredni MTA';
$labels['label_trusted_mtas'] = 'Z vejico ločen seznam authserv-id od zaupanja vrednih MTA. Prikazani bodo samo rezultati iz naslova teh MTA.-jev';

$labels['title_include_status'] = 'Prikaži samo naslednje statuse';
$labels['label_include_status1']  = 'Podpisa ni bilo moogoče najti';
$labels['label_include_status2']  = 'Podpis je bil najden, a v glavi sporočila ni nobenih rezultatov preverjanja pristnosti';
$labels['label_include_status4']  = 'Vsi podpisi so bili preverjeni';
$labels['label_include_status8']  = 'Vsaj en podpis je bil uspešno preverjen';
$labels['label_include_status16']  = 'Podpisi neodvisnih naslovov';
$labels['label_include_status32']  = 'Podpisovanje je začasno spodletelo';
$labels['label_include_status64']  = 'Podpisovanje je spodletelo';

?>
