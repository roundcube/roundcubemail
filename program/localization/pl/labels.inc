<?php

/*
 +-----------------------------------------------------------------------+
 | language/pl/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Sławomir Cichoń  <slawek.cichon@gmail.com>                    |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to Roundcube|Mail';
$labels['username']  = 'E-mail';
$labels['password']  = 'Hasło';
$labels['server']    = 'Serwer';
$labels['login']     = 'Zaloguj';

// taskbar
$labels['logout']   = 'Wyloguj';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Ustawienia';
$labels['addressbook'] = 'Książka Adresowa';

// mailbox names
$labels['inbox']  = 'Odebrane';
$labels['sent']   = 'Wysłane';
$labels['trash']  = 'Kosz';
$labels['drafts'] = 'Kopie robocze';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Temat';
$labels['from']    = 'Nadawca';
$labels['to']      = 'Odbiorca';
$labels['cc']      = 'Kopia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Odpowiedz do';
$labels['date']    = 'Data';
$labels['size']    = 'Rozmiar';
$labels['priority'] = 'Priorytet';
$labels['organization'] = 'Organizacja';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Foldery';
$labels['messagesfromto'] = 'Wiadmość od $from do $to z $count';
$labels['messagenrof'] = 'Wiadmość $nr z $count';

$labels['moveto']   = 'Przenieś do...';
$labels['download'] = 'pobierz';

$labels['filename'] = 'Nazwa pliku';
$labels['filesize'] = 'Rozmiar pliku';

$labels['preferhtml'] = 'Domyślny HTML';
$labels['htmlmessage'] = 'Widomość HTML';
$labels['prettydate'] = 'Ładne daty';

$labels['addtoaddressbook'] = 'Dodaj do książki adresowej ';

// weekdays short
$labels['sun'] = 'Nd';
$labels['mon'] = 'Pn';
$labels['tue'] = 'Wt';
$labels['wed'] = 'Śr';
$labels['thu'] = 'Czw';
$labels['fri'] = 'Pt';
$labels['sat'] = 'Sb';

// weekdays long
$labels['sunday']    = 'Niedziela';
$labels['monday']    = 'Poniedziałek';
$labels['tuesday']   = 'Wtorek';
$labels['wednesday'] = 'Środa';
$labels['thursday']  = 'Czwartek';
$labels['friday']    = 'Piątek';
$labels['saturday']  = 'Sobota';

$labels['today'] = 'Dzisiaj';

// toolbar buttons
$labels['writenewmessage']  = 'Utwórz nową wiadmomość';
$labels['replytomessage']   = 'Odpowiedz nadawcy';
$labels['replytoallmessage'] = 'Odpowiedz wszystkim';
$labels['forwardmessage']   = 'Prześlij dalej';
$labels['deletemessage']    = 'Usuń';
$labels['printmessage']     = 'Drukuj';
$labels['previousmessages'] = 'Pokaż poprzednią';
$labels['nextmessages']     = 'Pokaż następną';
$labels['backtolist']       = 'Pokaż listę widomości';
$labels['viewsource']       = 'Pokaż źródło';

$labels['select'] = 'Wybierz';
$labels['all'] = 'Zaznacz wszystkie';
$labels['none'] = 'Odznacz';
$labels['unread'] = 'Zaznacz nieprzeczytane';

$labels['compact'] = 'Kompaktuj';
$labels['empty'] = 'Pusty';
$labels['purge'] = 'Oczyść';

$labels['quota'] = 'Użyta Przestrzeń';

// message compose
$labels['compose']  = 'Utwórz wiadomość';
$labels['sendmessage']  = 'Wyślij teraz';
$labels['addattachment']  = 'Dołącz plik';
$labels['charset']  = 'Kodowanie znaków';

$labels['attachments'] = 'Załączniki';
$labels['upload'] = 'Wgraj';
$labels['close']  = 'Zamknij';

$labels['low']     = 'Bardzo niski';
$labels['lowest']  = 'Niski';
$labels['normal']  = 'Normalny';
$labels['high']    = 'Wysoki';
$labels['highest'] = 'Bardzo wysoki';

$labels['nosubject']  = '(brak tematu)';
$labels['showimages'] = 'Wyświetl obrazki';

// address boook
$labels['name']      = 'Nazwa';
$labels['firstname'] = 'Imię';
$labels['surname']   = 'Nazwisko';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Dodaj nowy kontakt';
$labels['editcontact'] = 'Edytuj kontakt';

$labels['edit']   = 'Edytuj';
$labels['cancel'] = 'Anuluj';
$labels['save']   = 'Zapisz';
$labels['delete'] = 'Kasuj';

$labels['newcontact']     = 'Dodaj nowy kontakt';
$labels['addcontact']     = 'Dodaj znaznaczony kontakt do książki adresowej';
$labels['deletecontact']  = 'Usuń zaznaczone kontakty';
$labels['composeto']      = 'Stwórz wiadomość dla';
$labels['contactsfromto'] = 'Kontakt od $from do $to z $count';
$labels['print']          = 'Drukuj';
$labels['export']         = 'Eksportuj';

// LDAP search
$labels['ldapsearch'] = 'Wyszukiwanie w katalogu LDAP';

$labels['ldappublicsearchname']    = 'Nazwa kontaktu';
$labels['ldappublicsearchtype'] = 'Dokładnie pasujący?';
$labels['ldappublicserverselect'] = 'Wybierz serwery';
$labels['ldappublicsearchfield'] = 'Szukaj w ';
$labels['ldappublicsearchform'] = 'Szukaj kontaktu';
$labels['ldappublicsearch'] = 'Szukaj';

// settings
$labels['settingsfor']  = 'Ustawienia dla';

$labels['preferences']  = 'Ustawienia';
$labels['userpreferences']  = 'Preferencje';
$labels['editpreferences']  = 'Edytuj preferencję';

$labels['identities']  = 'Tożsamości';
$labels['manageidentities']  = 'Zarządzaj tożsamościami';
$labels['newidentity']  = 'Nowa tożsamość';

$labels['newitem']  = 'Nowy';
$labels['edititem']  = 'Edytuj';

$labels['setdefault']  = 'Ustaw domyślne';
$labels['language']  = 'Język';
$labels['timezone']  = 'Strefy czasu';
$labels['pagesize']  = 'wierszy na stronie';
$labels['signature'] = 'Podpis';

$labels['folders']  = 'Foldery';
$labels['foldername']  = 'Nazwa folderu';
$labels['subscribed']  = 'Zapisany';
$labels['create']  = 'Utwórz';
$labels['createfolder']  = 'Utwórz nowy folder';
$labels['deletefolder']  = 'Usuń folder';
$labels['managefolders']  = 'Zarządzaj folderami';

$labels['sortby'] = 'Sortuj wg.';
$labels['sortasc']  = 'Rosnąco';
$labels['sortdesc'] = 'Malejąco';

?>
