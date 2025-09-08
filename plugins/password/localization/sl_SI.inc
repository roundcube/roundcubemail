<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'Spremenjeno geslo';
$labels['curpasswd']  = 'Obstoječe geslo:';
$labels['newpasswd']  = 'Novo geslo:';
$labels['confpasswd']  = 'Potrdi novo geslo:';
$messages['nopassword'] = 'Vnesite novo geslo.';
$messages['nocurpassword'] = 'Vnesite obstoječe geslo.';
$messages['passwordincorrect'] = 'Obstoječe geslo ni veljavno.';
$messages['passwordinconsistency'] = 'Gesli se ne ujemata, poskusite znova.';
$messages['crypterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake pri šifriranju.';
$messages['connecterror'] = 'Novega gesla ni bilo mogoče shraniti. Prišlo je do napake v povezavi.';
$messages['internalerror'] = 'Novega gesla ni bilo mogoče shraniti.';
$messages['passwordshort'] = 'Geslo mora vsebovati vsaj $length znakov';
$messages['passwordweak'] = 'Geslo mora vključevati vsaj eno številko in ločilo.';
$messages['passwordforbidden'] = 'Geslo vsebuje neveljavne znake.';
$messages['firstloginchange'] = 'To je vaša prva prijava. Spremenite začetno geslo.';
$messages['disablednotice'] = 'Sistem se trenutno posodablja, zato sprememba gesla v tem trenutku ni mogoča. Postopek bo mogoče opraviti v kratkem. Za nevšečnosti se opravičujemo.';
$messages['passwinhistory'] = 'To geslo ste v preteklosti že uporabili.';
$messages['samepasswd'] = 'Novo geslo se mora razlikovati od prejšnjega.';
$messages['passwdexpirewarning'] = 'Opozorilo! Vaše geslo bo kmalu poteklo, spremenite ga pred $expirationdatetime.';
$messages['passwdexpired'] = 'Vaše geslo je poteklo, spremenite ga!';
$messages['passwdconstraintviolation'] = 'Geslo ne ustreza zahtevam. Verjetno je prešibko.';

$labels['hestia_err_policy_short'] = 'Prekratko geslo (najmanjša dolžina: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Končna točka Hestia ni konfigurirana ali je neveljavna.';
$labels['hestia_err_identity'] = 'Uporabniškega imena ni mogoče razčleniti v local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Ni mogoče določiti lastnika domene za %domeno.';
$labels['hestia_err_unknown'] = 'Neznana napaka iz vmesnika Hestia API.';
$labels['hestia_err_default'] = 'Hestia je vrnila kodo napake %koda.';
$labels['hestia_err_1'] = 'Premalo argumentov.';
$labels['hestia_err_2'] = 'Nepravilen predmet ali argument.';
$labels['hestia_err_3'] = 'Objekt ne obstaja (poštni predal ali domena nista bila najdena).';
$labels['hestia_err_4'] = 'Objekt že obstaja.';
$labels['hestia_err_5'] = 'Objekt je že suspendiran.';
$labels['hestia_err_6'] = 'Objekt je že odvzet iz suspenza.';
$labels['hestia_err_7'] = 'Predmet v uporabi; ni mogoče spreminjati.';
$labels['hestia_err_8'] = 'Delovanje blokirajo omejitve paketa.';
$labels['hestia_err_9'] = 'Napačno ali neveljavno geslo.';
$labels['hestia_err_10'] = 'Hestia je zavrnila dovoljenje (identiteta API nima pravic ali IP ni dovoljen).';
$labels['hestia_err_11'] = 'Funkcija je onemogočena s sistemsko politiko.';
$labels['hestia_err_12'] = 'Napaka pri razčlenjevanju.';
$labels['hestia_err_13'] = 'Napaka diska.';
$labels['hestia_err_14'] = 'Prevelika obremenitev strežnika.';
$labels['hestia_err_15'] = 'Napaka povezave.';
$labels['hestia_err_16'] = 'Napaka FTP.';
$labels['hestia_err_17'] = 'Napaka v podatkovni bazi.';
$labels['hestia_err_18'] = 'Napaka RRD.';
$labels['hestia_err_19'] = 'Napaka pri posodabljanju.';
$labels['hestia_err_20'] = 'Napaka ponovnega zagona.';
?>
