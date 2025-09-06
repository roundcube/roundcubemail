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

$labels['changepasswd']  = 'Zmeniť heslo';
$labels['curpasswd']  = 'Aktuálne heslo:';
$labels['newpasswd']  = 'Nové heslo:';
$labels['confpasswd']  = 'Potvrďte nové heslo:';
$messages['nopassword'] = 'Prosím zadajte nové heslo.';
$messages['nocurpassword'] = 'Prosím zadajte aktuálne heslo.';
$messages['passwordincorrect'] = 'Aktuálne heslo je nesprávne.';
$messages['passwordinconsistency'] = 'Heslá nie sú rovnaké, skúste to znova.';
$messages['crypterror'] = 'Nové heslo nemožno uložiť. Chýba šifrovacia funkcia.';
$messages['connecterror'] = 'Nové heslo nemožno uložiť. Chyba spojenia.';
$messages['internalerror'] = 'Nové heslo nemožno uložiť.';
$messages['passwordshort'] = 'Heslo musí mať najmenej $length znakov.';
$messages['passwordweak'] = 'Heslo musí obsahovať aspoň jedno číslo a jedno interpunkčné znamienko.';
$messages['passwordtooweak'] = 'Heslo je príliš slabé.';
$messages['passwordnoseq'] = 'Heslo by nemalo byť takou sekvenciou, ako napr. 123456 alebo QWERTY.';
$messages['passwordnocommon'] = 'Bežné slovo alebo meno by nemalo byť heslom.';
$messages['passwordforbidden'] = 'Heslo obsahuje nepovolené znaky.';
$messages['firstloginchange'] = 'Prihlásili ste sa po prvýkrát. Prosím zmeňte si prístupové heslo.';
$messages['disablednotice'] = 'V systéme momentálne prebieha údržba a preto nie je teraz možné zmeniť heslo. Čoskoro by sa však situácia mala vrátiť do normálu. Ospravedlňujeme sa Vám za spôsobené nepríjemnosti.';
$messages['passwinhistory'] = 'Toto heslo už bolo predtým použité.';
$messages['samepasswd'] = 'Nové heslo by sa malo odlišovať od pôvodného hesla.';
$messages['passwdexpirewarning'] = 'Pozor! Platnosť Vášho hesla čoskoro vyprší, zmeňte ho pred $expirationdatetime.';
$messages['passwdexpired'] = 'Platnosť Vášho hesla vypršala, teraz ho musíte zmeniť!';
$messages['passwdconstraintviolation'] = 'Výnimka obmedzenia hesla. Heslo je pravdepodobne príliš ľahké.';
$messages['pwned_mustnotbedisclosed'] = 'Heslo nesmie byť <a href="$href" target="_blank">všeobecne a bežne známe</a>.';
$messages['pwned_isdisclosed'] = 'Toto heslo je všeobecne známe.';
$messages['pwned_fetcherror'] = 'Nepodarilo sa overiť silu hesla.';

$labels['hestia_err_policy_short'] = 'Príliš krátke heslo (minimálna dĺžka: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Koncový bod Hestia nie je nakonfigurovaný alebo je neplatný.';
$labels['hestia_err_identity'] = 'Nie je možné analyzovať používateľské meno do local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Nie je možné určiť vlastníka domény pre %doménu.';
$labels['hestia_err_unknown'] = 'Neznáma chyba z rozhrania Hestia API.';
$labels['hestia_err_default'] = 'Hestia vrátila chybový kód %code.';
$labels['hestia_err_1'] = 'Nie je k dispozícii dostatok argumentov.';
$labels['hestia_err_2'] = 'Neplatný objekt alebo argument.';
$labels['hestia_err_3'] = 'Objekt neexistuje (poštová schránka alebo doména nebola nájdená).';
$labels['hestia_err_4'] = 'Objekt už existuje.';
$labels['hestia_err_5'] = 'Objekt je už pozastavený.';
$labels['hestia_err_6'] = 'Objekt už nie je zavesený.';
$labels['hestia_err_7'] = 'Objekt v používaní; nemožno upravovať.';
$labels['hestia_err_8'] = 'Operácia je blokovaná obmedzeniami balíka.';
$labels['hestia_err_9'] = 'Nesprávne alebo neplatné heslo.';
$labels['hestia_err_10'] = 'Hestia zamietla povolenie (identita API nemá práva alebo IP nie je povolená).';
$labels['hestia_err_11'] = 'Funkcia zakázaná systémovou politikou.';
$labels['hestia_err_12'] = 'Chyba pri analyzovaní.';
$labels['hestia_err_13'] = 'Chyba disku.';
$labels['hestia_err_14'] = 'Príliš vysoké zaťaženie servera.';
$labels['hestia_err_15'] = 'Chyba pripojenia.';
$labels['hestia_err_16'] = 'Chyba FTP.';
$labels['hestia_err_17'] = 'Chyba databázy.';
$labels['hestia_err_18'] = 'Chyba RRD.';
$labels['hestia_err_19'] = 'Chyba aktualizácie.';
$labels['hestia_err_20'] = 'Chyba pri reštarte.';
?>
