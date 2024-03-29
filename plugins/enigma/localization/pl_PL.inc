<?php

/*
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

$labels['encryption'] = 'Szyfrowanie';
$labels['enigmacerts'] = 'Certyfikaty S/MIME';
$labels['enigmakeys'] = 'Klucze PGP';
$labels['keysfromto'] = 'Klucze od $from do $to z $count';
$labels['keyname'] = 'Nazwa';
$labels['keyid'] = 'Id klucza';
$labels['keyuserid'] = 'Id użytkownika';
$labels['keytype'] = 'Typ klucza';
$labels['fingerprint'] = 'Odcisk palca';
$labels['subkeys'] = 'Podklucze';
$labels['keyprops'] = 'Właściwości klucza';
$labels['basicinfo'] = 'Informacje podstawowe';
$labels['userids'] = 'Dodatkowi użytkownicy';
$labels['typepublickey'] = 'klucz publiczny';
$labels['typekeypair'] = 'para kluczy';
$labels['keyattfound'] = 'Ta wiadomość zawiera załączone klucze PGP.';
$labels['keyattimport'] = 'Importuj klucz(e)';
$labels['typesign'] = 'Podpisz';
$labels['typeencrypt'] = 'Szyfruj';
$labels['typecert'] = 'Certyfikuj';
$labels['typeauth'] = 'Uwierzytelnianie';
$labels['subkeyid'] = 'Id';
$labels['subkeyalgo'] = 'Algorytm';
$labels['subkeycreated'] = 'Utworzony';
$labels['subkeyexpires'] = 'Wygasa';
$labels['subkeyusage'] = 'Zastosowanie';
$labels['expiresnever'] = 'nigdy';
$labels['unknown'] = 'nieznany';
$labels['uservalid'] = 'Ważny';
$labels['userid'] = 'Id';
$labels['valid'] = 'ważny';
$labels['supportencryption'] = 'Włącz szyfrowanie i podpisywanie wiadomości';
$labels['supportsignatures'] = 'Włącz weryfikację podpisów wiadomości';
$labels['supportdecryption'] = 'Włącz rozszyfrowywanie wiadomości';
$labels['signdefault'] = 'Domyślnie podpisuj wiadomości';
$labels['encryptdefault'] = 'Domyślnie szyfruj wiadomości';
$labels['attachpubkeydefault'] = 'Zawsze załączaj mój klucz publiczny PGP';
$labels['passwordtime'] = 'Przechowuj hasła kluczy prywatnych przez';
$labels['nminutes'] = '$m minut';
$labels['wholesession'] = 'cała sesja';
$labels['createkeys'] = 'Utwórz nową parę kluczy';
$labels['importkeys'] = 'Importuj klucz(e)';
$labels['exportkeys'] = 'Eksportuj klucz(e)';
$labels['keyactions'] = 'Akcje kluczy...';
$labels['keyremove'] = 'Usuń';
$labels['keydisable'] = 'Wyłącz';
$labels['keyrevoke'] = 'Unieważnij';
$labels['keysend'] = 'Wyślij klucz publiczny w wiadomości';
$labels['keychpass'] = 'Zmień hasło';
$labels['keyadd'] = 'Dodaj klucz';
$labels['newkeyident'] = 'Tożsamość';
$labels['newkeypass'] = 'Hasło';
$labels['newkeypassconfirm'] = 'Potwierdź hasło';
$labels['newkeytype'] = 'Typ klucza';
$labels['rsa2048'] = 'RSA - 2048 bitów';
$labels['rsa4096'] = 'RSA - 4096 bitów';
$labels['ecckeypair'] = 'ECC (krzywe eliptyczne)';
$labels['keygenerating'] = 'Generowanie kluczy...';
$labels['encryptionoptions'] = 'Opcje szyfrowania...';
$labels['encryptmsg'] = 'Szyfruj wiadomość';
$labels['signmsg'] = 'Podpisz wiadomość';
$labels['sendunencrypted'] = 'Wyślij niezaszyfrowaną';
$labels['enterkeypasstitle'] = 'Wprowadź hasło klucza';
$labels['enterkeypass'] = 'Wymagane jest hasło do klucza ($keyid) użytkownika: $user.';
$labels['attachpubkeymsg'] = 'Załącz mój klucz publiczny';
$labels['keyexportprompt'] = 'Czy chcesz dołączyć klucze prywatne do utworzonego pliku kluczy PGP?';
$labels['onlypubkeys'] = 'Eksportuj tylko klucze publiczne';
$labels['withprivkeys'] = 'Eksportuj klucze prywatne';
$labels['findkey'] = 'Wyszukaj na serwerach kluczy';
$labels['keyimportlabel'] = 'Import z pliku';
$labels['keyimportsearchlabel'] = 'Wyszukiwanie na serwerach kluczy';
$labels['managekeys'] = 'Zarządzaj kluczami PGP';
$labels['identitymatchingprivkeys'] = 'Masz $nr pasujących kluczy prywatnych PGP w bazie kluczy.';
$labels['identitynoprivkeys'] = 'Brak klucza prywatnego PGP dla wybranej tożsamości nadawcy.';
$labels['arialabelkeyexportoptions'] = 'Opcje eksportu kluczy';
$labels['arialabelkeysearchform'] = 'Formularz wyszukiwania kluczy';
$labels['arialabelkeyoptions'] = 'Opcje kluczy';
$messages['sigvalid'] = 'Zweryfikowany podpis nadawcy $sender.';
$messages['sigvalidpartial'] = 'Zweryfikowany podpis od $sender, ale część wiadomości nie została podpisana.';
$messages['siginvalid'] = 'Błędny podpis nadawcy $sender.';
$messages['sigunverified'] = 'Podpis niezweryfikowany. Certyfikat niezweryfikowany. ID certyfikatu: $keyid.';
$messages['signokey'] = 'Podpis niezweryfikowany. Brak klucza publicznego. ID klucza: $keyid.';
$messages['sigerror'] = 'Podpis niezweryfikowany. Błąd wewnętrzny.';
$messages['decryptok'] = 'Wiadomość odszyfrowana.';
$messages['decrypterror'] = 'Nie udało się odszyfrować wiadomości.';
$messages['decryptnokey'] = 'Odszyfrowanie nie powiodło się. Brak klucza prywatnego. ID klucza: $keyid.';
$messages['decryptnomdc'] = 'Pominięto szyfrowanie. Wiadomość nie posiada ochrony integralności.';
$messages['decryptbadpass'] = 'Odszyfrowanie nie powiodło się. Hasło jest nieprawidłowe.';
$messages['decryptnopass'] = 'Odszyfrowanie nie powiodło się. Hasło jest wymagane.';
$messages['decryptpartial'] = 'Wiadomość odszyfrowana, ale jej część nie była zaszyfrowana.';
$messages['signerror'] = 'Błąd podpisywania.';
$messages['signnokey'] = 'Błąd podpisywania. Nie znaleziono klucza prywatnego.';
$messages['signbadpass'] = 'Błąd podpisywania. Hasło jest nieprawidłowe.';
$messages['signnopass'] = 'Błąd podpisywania. Wymagane hasło klucza.';
$messages['encrypterror'] = 'Błąd szyfrowania.';
$messages['encryptnokey'] = 'Błąd szyfrowania. Nie znaleziono klucza publicznego dla $email.';
$messages['encryptnoprivkey'] = 'Błąd szyfrowania. Nie znaleziono klucza prywatnego.';
$messages['nokeysfound'] = 'Nie znaleziono kluczy';
$messages['keynotfound'] = 'Nie znaleziono klucza!';
$messages['keyopenerror'] = 'Nie można odczytać informacji o kluczu! Błąd wewnętrzny.';
$messages['keylisterror'] = 'Nie można odczytać listy kluczy! Błąd wewnętrzny.';
$messages['keysimportfailed'] = 'Nie można zapisać kluczy! Błąd wewnętrzny.';
$messages['keysimportsuccess'] = 'Klucze zostały zapisane. Dodane: $new, bez zmian: $old.';
$messages['keyremoving'] = 'Usuwanie kluczy...';
$messages['keyremoveconfirm'] = 'Czy na pewno chcesz usunąć wybrane klucze?';
$messages['keyremovesuccess'] = 'Klucze zostały usunięte.';
$messages['keyremoveerror'] = 'Nie można usunąć wybranych kluczy.';
$messages['keyimporttext'] = 'Możesz zaimportować klucze prywatne i publiczne oraz sygnatury unieważniające w formacie ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Możesz wyszukiwać przy użyciu identyfikatora klucza, nazwy użytkownika lub adresu email, a następnie bezpośrednio zaimportować wyszukane klucze.';
$messages['keystoragenotice'] = 'Wszystkie klucze publiczne i prywatne są przechowywane na serwerze.';
$messages['formerror'] = 'Proszę wypełnić formularz. Wszystkie pola są wymagane!';
$messages['passwordsdiffer'] = 'Hasła nie pasują!';
$messages['keygenerateerror'] = 'Generowanie pary kluczy nie powiodło się';
$messages['keygeneratesuccess'] = 'Para kluczy została zapisana.';
$messages['keygennosupport'] = 'Twoja przeglądarka nie obsługuje kryptografii. Nie można wygenerować kluczy!';
$messages['noidentselected'] = 'Należy wybrać przynajmniej jedną tożsamość!';
$messages['nonameident'] = 'Tożsamość musi zawierać nazwę użytkownika!';
$labels['newkeysize'] = 'Rozmiar klucza';
$labels['key2048'] = '2048 bitów - domyślnie';
$labels['key4096'] = '4096 bitów - bardziej bezpiecznie';
