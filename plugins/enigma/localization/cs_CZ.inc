<?php

/**
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/

$labels['encryption'] = 'Šifrování';
$labels['enigmacerts'] = 'S/MIME certifikáty';
$labels['enigmakeys'] = 'PGP klíče';
$labels['keysfromto'] = 'Klíče $from až $to z počtu: $count';
$labels['keyname'] = 'Jméno';
$labels['keyid'] = 'ID Klíče';
$labels['keyuserid'] = 'ID uživatele';
$labels['keytype'] = 'Typ klíče';
$labels['fingerprint'] = 'Otisk prstu';
$labels['subkeys'] = 'Podklíče';
$labels['keyprops'] = 'Vlastnosti klíče';
$labels['basicinfo'] = 'Základní informace';
$labels['userids'] = 'Další uživatelé';
$labels['typepublickey'] = 'veřejný klíč';
$labels['typekeypair'] = 'pár klíčů';
$labels['keyattfound'] = 'Tato zpráva má přiložený/é PGP klíče.';
$labels['keyattimport'] = 'Importovat klíče';
$labels['typesign'] = 'Podepsat';
$labels['typeencrypt'] = 'Zašifrovat';
$labels['typecert'] = 'Certifikovat';
$labels['typeauth'] = 'Autentizace';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritmus';
$labels['subkeycreated'] = 'Vytvořený';
$labels['subkeyexpires'] = 'Vyprší';
$labels['subkeyusage'] = 'Použití';
$labels['expiresnever'] = 'nikdy';
$labels['unknown'] = 'neznámý';
$labels['uservalid'] = 'Správný';
$labels['userid'] = 'ID';
$labels['valid'] = 'správný';
$labels['supportencryption'] = 'Povolit podepisování a šifrování zpráv.';
$labels['supportsignatures'] = 'Povolit ověřování podpisů zpráv.';
$labels['supportdecryption'] = 'Povolit dešifrování zpráv.';
$labels['signdefault'] = 'Podepisovat všechny zprávy';
$labels['encryptdefault'] = 'Šifrovat všechny zprávy';
$labels['attachpubkeydefault'] = 'Automaticky připojit svůj veřejný klíč';
$labels['passwordtime'] = 'Uchovat hesla soukromých klíčů pro';
$labels['nminutes'] = '$m minut(a)';
$labels['wholesession'] = 'celá relace';
$labels['createkeys'] = 'Vytvořit nový pár klíčů';
$labels['importkeys'] = 'Importovat klíče';
$labels['exportkeys'] = 'Exportovat klíče';
$labels['keyactions'] = 'Akce s klíčem...';
$labels['keyremove'] = 'Smazat';
$labels['keydisable'] = 'Vypnout';
$labels['keyrevoke'] = 'Revokovat';
$labels['keysend'] = 'Odeslat veřejný klíč ve zprávě';
$labels['keychpass'] = 'Změnit heslo';
$labels['keyadd'] = 'Přidat klíč';
$labels['newkeyident'] = 'Identita';
$labels['newkeypass'] = 'Heslo';
$labels['newkeypassconfirm'] = 'Potvrdit heslo';
$labels['newkeytype'] = 'Typ klíče';
$labels['rsa2048'] = 'RSA - 2048 bitů';
$labels['rsa4096'] = 'RSA - 4096 bitů';
$labels['ecckeypair'] = 'ECC (Eliptická křivka)';
$labels['keygenerating'] = 'Generuji klíče...';
$labels['encryptionoptions'] = 'Možnosti šifrování...';
$labels['encryptmsg'] = 'Zašifrovat tuto zprávu';
$labels['signmsg'] = 'Digitálně podepsat tuto zprávu';
$labels['sendunencrypted'] = 'Poslat nešifrovaně';
$labels['enterkeypasstitle'] = 'Zadejte heslo pro klíč';
$labels['enterkeypass'] = 'Heslo je nutné k odemknutí soukromého klíče ($keyid) pro uživatele: $user.';
$labels['attachpubkeymsg'] = 'Připojit můj veřejný klíč';
$labels['keyexportprompt'] = 'Chcete do uloženého souboru s klíči OpenPGP zahrnout i tajné klíče?';
$labels['onlypubkeys'] = 'Exportovat pouze veřejné klíče';
$labels['withprivkeys'] = 'Exportovat soukromé klíče';
$labels['findkey'] = 'Hledat klíč na keyserveru';
$labels['keyimportlabel'] = 'Importovat ze souboru';
$labels['keyimportsearchlabel'] = 'Hledat klíč na keyserveru';
$labels['managekeys'] = 'Spravovat PGP klíče';
$labels['identitymatchingprivkeys'] = 'V klíčence máte $nr odpovídajících soukromých PGP klíčů:';
$labels['identitynoprivkeys'] = 'Klíčenka neobsahuje soukromý PGP klíč identity odesílatele.';
$labels['arialabelkeyexportoptions'] = 'Možnosti pro export klíčů';
$labels['arialabelkeysearchform'] = 'Formulář pro vyhledávání klíčů';
$labels['arialabelkeyoptions'] = 'Nastavení klíče';
$messages['sigvalid'] = 'Ověřený podpis od $sender.';
$messages['sigvalidpartial'] = 'Ověřený podpis od $sender, ale část těla není podepsána.';
$messages['siginvalid'] = 'Nesprávný podpis od $sender.';
$messages['sigunverified'] = 'Neověřený podpis. Certifikát není ověřen. ID certifikátu: $keyid.';
$messages['signokey'] = 'Neověřený podpis. Veřejný klíč nebyl nalezen. ID klíče: $keyid.';
$messages['sigerror'] = 'Neověřený podpis. Interní chyba.';
$messages['decryptok'] = 'Zpráva byla dešifrována.';
$messages['decrypterror'] = 'Dešifrování selhalo.';
$messages['decryptnokey'] = 'Dešifrování selhalo. Soukromý klíč nebyl nalezen. ID klíče: $keyid.';
$messages['decryptnomdc'] = 'Dešifrování přeskočeno. Integrita zprávy není chráněna.';
$messages['decryptbadpass'] = 'Dešifrování selhalo. Špatné heslo.';
$messages['decryptnopass'] = 'Dešifrování selhalo. Heslo pro klíč je požadováno.';
$messages['decryptpartial'] = 'Zpráva byla dešifrována, ale část jejího těla nebyla zašifrovaná.';
$messages['signerror'] = 'Podepisování selhalo.';
$messages['signnokey'] = 'Podepisování selhalo. Soukromý klíč nebyl nalezen.';
$messages['signbadpass'] = 'Podepisování selhalo. Špatné heslo.';
$messages['signnopass'] = 'Podepisování selhalo. Heslo pro klíč je požadováno.';
$messages['encrypterror'] = 'Šifrování selhalo.';
$messages['encryptnokey'] = 'Šifrování selhalo. Veřejný klíč pro email $email nebyl nalezen.';
$messages['encryptnoprivkey'] = 'Šifrování selhalo. Soukromý klíč nebyl nalezen.';
$messages['nokeysfound'] = 'Nebyly nalezeny žádné klíče.';
$messages['keynotfound'] = 'Klíč nebyl nalezen!';
$messages['keyopenerror'] = 'Nemohu dostat informace o klíči! Interní chyba.';
$messages['keylisterror'] = 'Nemohu získat seznam klíčů! Interní chyba.';
$messages['keysimportfailed'] = 'Nemohu importovat klíče! Interní chyba.';
$messages['keysimportsuccess'] = 'Klíče byly naimportovány. Importovány: $new, nezměněny: $old.';
$messages['keyremoving'] = 'Odstraňuji klíče...';
$messages['keyremoveconfirm'] = 'Opravdu si přejete odstranit vybrané klíče?';
$messages['keyremovesuccess'] = 'Klíče byly odstraněny.';
$messages['keyremoveerror'] = 'Nemohu smazat vybrané klíče.';
$messages['keyimporttext'] = 'Můžete naimportovat soukromé a veřejné klíče nebo podpisy pro revokaci ve formátu ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Veřejné klíče můžete vyhledávat podle identifikátoru klíče, uživatele, nebo e-mailové adresy a potom je přímo importovat.';
$messages['keystoragenotice'] = 'Všechny veřejné a soukromé klíče jsou uloženy na serveru.';
$messages['formerror'] = 'Prosím vyplňte tento formulář. Všechna pole jsou požadována.';
$messages['passwordsdiffer'] = 'Zadaná hesla se neshodují!';
$messages['keygenerateerror'] = 'Selhalo generování páru klíčů';
$messages['keygeneratesuccess'] = 'Pár klíčů byl vygenerován a naimportován.';
$messages['keygennosupport'] = 'Váš webový prohlížeč nepodporuje kryptografii. Nemohu vygenerovat pár klíčů.';
$messages['noidentselected'] = 'Pro tento klíč si musíte vybrat alespoň jednu identitu!';
$messages['nonameident'] = 'Identita musí mít definované uživatelské jméno!';
$labels['newkeysize'] = 'Velikost klíče';
$labels['key2048'] = '2048 bitů - výchozí';
$labels['key4096'] = '4096 bitů - více bezpečné';
