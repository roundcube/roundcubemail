<?php

/*

 +-----------------------------------------------------------------------+
 | language/pl_PL/messages.inc                                           |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Tomasz Pajor        <t.pajor@kei.pl>                          |
 | Author: Sławomir Cichoń     <slawek.cichon@gmail.com>                 |
 |         Janusz Zamecki      <janusz@zamecki.eu>                       |
 |         Mariusz Szulerecki  <nob@force.eu.org>                        |
 |         Wojciech Szczepucha <donkoyote@gmail.com>                     |
 |         Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 @version $Id: messages.inc 1245 2008-04-03 13:17:34Z alec $

*/

$messages = array();
$messages['loginfailed'] = 'Błąd logowania!';
$messages['cookiesdisabled'] = 'Twoja przeglądarka nie obsługuje ciasteczek!';
$messages['sessionerror'] = 'Błąd sesji bądź sesja wygasła!';
$messages['imaperror'] = 'Błąd połączenia z serwerem!';
$messages['nomessagesfound'] = 'Brak wiadomości w skrzynce.';
$messages['loggedout'] = 'Użytkownik wylogował się poprawnie.';
$messages['mailboxempty'] = 'Skrzynka jest pusta!';
$messages['loading'] = 'Ładowanie...';
$messages['loadingdata'] = 'Ładowanie danych...';
$messages['checkingmail'] = 'Sprawdzanie nowych wiadomości...';
$messages['sendingmessage'] = 'Wysyłanie wiadomości...';
$messages['messagesent'] = 'Wiadomość została wysłana.';
$messages['savingmessage'] = 'Zapisywanie wiadomości...';
$messages['messagesaved'] = 'Wiadomość została zapisana w folderze Kopie robocze.';
$messages['successfullysaved'] = 'Zapisano.';
$messages['addedsuccessfully'] = 'Kontakt został dodany.';
$messages['contactexists'] = 'Kontakt o podanym adresie e-mail już istnieje!';
$messages['blockedimages'] = 'Ze względów bezpieczeństwa zewnętrzne obrazki zostały zablokowane.';
$messages['encryptedmessage'] = 'Przepraszamy, nie można wyświetlić zaszyfrowanej wiadomości!';
$messages['nocontactsfound'] = 'Nie znaleziono kontaktu!';
$messages['contactnotfound'] = 'Szukany kontakt nie został odnaleziony';
$messages['sendingfailed'] = 'Nie udało się wysłać wiadomości!';
$messages['senttooquickly'] = 'Musisz poczekać $sec sek. aby móc wysłać tą wiadomość!';
$messages['errorsavingsent'] = 'Wystąpił błąd podczas zapisu wysłanej wiadomości!';
$messages['errorsaving'] = 'Wystąpił błąd podczas zapisu!';
$messages['errormoving'] = 'Nie można przenieść wybranej wiadomości!';
$messages['errordeleting'] = 'Nie można usunąć wiadomości!';
$messages['deletecontactconfirm'] = 'Czy na pewno chcesz usunąć wybrane kontakty?';
$messages['deletemessagesconfirm'] = 'Czy na pewno chcesz usunąć wybrane wiadomości?';
$messages['deletefolderconfirm'] = 'Czy na pewno chcesz usunąć wybrany folder?';
$messages['purgefolderconfirm'] = 'Czy na pewno chcesz usunąć wszystkie wiadomości w tym folderze?';
$messages['foldercreating'] = 'Dodawanie folderu...';
$messages['folderdeleting'] = 'Usuwanie folderu...';
$messages['folderrenaming'] = 'Zmiana nazwy folderu...';
$messages['foldermoving'] = 'Przenoszenie folderu...';
$messages['formincomplete'] = 'Uzupełnij formularz!';
$messages['noemailwarning'] = 'Podaj poprawny adres e-mail!';
$messages['nonamewarning'] = 'Podaj nazwę!';
$messages['nopagesizewarning'] = 'Podaj poprawną wartość!';
$messages['nosenderwarning'] = 'Podaj adres nadawcy!'; 
$messages['norecipientwarning'] = 'Podaj co najmniej jednego odbiorcę!';
$messages['nosubjectwarning'] = 'Nie podano tematu wiadomości. Czy chcesz go teraz uzupełnić?';
$messages['nobodywarning'] = 'Ta wiadomość jest pusta, czy mimo to chcesz ją wysłać?';
$messages['notsentwarning'] = 'Wiadomość nie została wysłana. Czy chcesz usunąć tę wiadomość?';
$messages['noldapserver'] = 'Wybierz serwer LDAP!';
$messages['nocontactsreturned'] = 'Nie znaleziono kontaktów!';
$messages['nosearchname'] = 'Podaj nazwę kontaktu lub jego adres e-mail.';
$messages['searchsuccessful'] = 'Liczba znalezionych wiadomości: $nr';
$messages['searchnomatch'] = 'Nic nie znaleziono.';
$messages['searching'] = 'Wyszukiwanie...';
$messages['checking'] = 'Sprawdzanie...';
$messages['nospellerrors'] = 'Nie znaleziono błędów w pisowni.';
$messages['folderdeleted'] = 'Folder został pomyślnie usunięty.';
$messages['deletedsuccessfully'] = 'Usunięto';
$messages['converting'] = 'Konwertowanie tekstu...';
$messages['messageopenerror'] = 'Nie można załadować wiadomości z serwera';
$messages['fileuploaderror'] = 'Załączanie pliku nie powiodło się';
$messages['filesizeerror'] = 'Plik przekracza maksymalną wielkość $size';
$messages['copysuccess'] = 'Pomyślnie skopiowano następującą liczbę adresów: $nr';
$messages['copyerror'] = 'Nie można skopiować żadnego adresu';
$messages['sourceisreadonly'] = 'Źródło adresu jest tylko do odczytu';
$messages['errorsavingcontact'] = 'Nie można było zapisać adresu kontaktu';
$messages['movingmessage'] = 'Przenoszenie wiadomości...';
$messages['receiptsent'] = 'Pomyślnie wysłano potwierdzenie dostarczenia';
$messages['errorsendingreceipt'] = 'Nie można wysłać potwierdzenia';
$messages['nodeletelastidentity'] = 'Nie można skasować tej tożsamości, ponieważ jest ostatnią.';
$messages['addsubfolderhint'] = 'Ten folder zostanie utworzony jako podfolder aktualnie wybranego.';
$messages['forbiddencharacter'] = 'Nazwa folderu zawiera niedozwolony znak';
$messages['selectimportfile'] = 'Proszę wybrać plik do wysłania'; 
$messages['addresswriterror'] = 'Wybrana książka adresowa jest tylko-do-odczytu'; 
$messages['importwait'] = 'Importowanie, proszę czekać...'; 
$messages['importerror'] = 'Błąd! Pobrany plik nie jest poprawnym plikiem vCard.'; 
$messages['importconfirm'] = '<b>Pomyślnie dodano $inserted kontaktów, pominięto $skipped istniejących wpisów</b>:<p><em>$names</em></p>'; 
$messages['opnotpermitted'] = 'Niedozwolona operacja!';
$messages['nofromaddress'] = 'Brak adresu e-mail w wybranej tożsamości';

?>
