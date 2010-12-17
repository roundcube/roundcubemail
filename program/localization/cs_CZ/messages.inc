<?php

/*

+-----------------------------------------------------------------------+
| language/cs_CZ/messages.inc                                              |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Martin Mrajca <martin@moonlake.cz>                            |
|        joe <joe@humlak.cz>                                            |
|        Jiri Kaderavek <jiri.kaderavek@webstep.net>                    |
|        Ales Pospichal <ales@pospichalales.info>                       |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Přihlášení selhalo';
$messages['cookiesdisabled'] = 'Váš prohlížeč nepodporuje cookies, které jsou pro přihlášení nezbytné';
$messages['sessionerror'] = 'Vaše přihlášení je neplatné nebo vypršelo';
$messages['imaperror'] = 'Připojení na IMAP server selhalo';
$messages['servererror'] = 'Chyba serveru!';
$messages['servererrormsg'] = 'Chyba serveru: $msg';
$messages['invalidrequest'] = 'Nesprávný požadavek. Data nebyla uložena.';
$messages['nomessagesfound'] = 'Ve schránce nebyla nalezena žádná zpráva';
$messages['loggedout'] = 'Byli jste úspěšně odhlášeni. Nashledanou!';
$messages['mailboxempty'] = 'Schránka je prázdná';
$messages['loading'] = 'Načítám...';
$messages['uploading'] = 'Nahrávám soubor...';
$messages['loadingdata'] = 'Načítám data...';
$messages['checkingmail'] = 'Kontroluji nové zprávy...';
$messages['sendingmessage'] = 'Odesílám zprávu...';
$messages['messagesent'] = 'Zpráva byla odeslána';
$messages['savingmessage'] = 'Ukládám zprávu...';
$messages['messagesaved'] = 'Zpráva uložena do Rozepsané';
$messages['successfullysaved'] = 'Uloženo';
$messages['addedsuccessfully'] = 'Kontakt byl úspěšně přidán do adresáře';
$messages['contactexists'] = 'Kontakt se zadanou e-mailovou adresou již existuje';
$messages['blockedimages'] = 'Z bezpečnostních důvodů byly zablokovány obrázky ve zprávě.';
$messages['encryptedmessage'] = 'Tato zpráva je zašifrovaná a nelze ji zobrazit.';
$messages['nocontactsfound'] = 'Nemáte žádné kontakty';
$messages['contactnotfound'] = 'Požadovaný kontakt nebyl nalezen.';
$messages['sendingfailed'] = 'Odesílání zprávy selhalo';
$messages['senttooquickly'] = 'Prosím počkejte $sec sekund před odesláním zprávy';
$messages['errorsavingsent'] = 'Nastala chyba při ukládání odeslané zprávy';
$messages['errorsaving'] = 'Vyskytla se chyba při ukládání';
$messages['errormoving'] = 'Nemohu přesunout zprávu';
$messages['errorcopying'] = 'Nemohu zkopírovat zprávu';
$messages['errordeleting'] = 'Nemohu smazat zprávu';
$messages['errormarking'] = 'Zprávu nelze označit';
$messages['deletecontactconfirm'] = 'Opravdu chcete smazat označené kontakty?';
$messages['deletemessagesconfirm'] = 'Opravdu chcete smazat označené zprávy?';
$messages['deletefolderconfirm'] = 'Chcete opravdu smazat tento adresář?';
$messages['purgefolderconfirm'] = 'Opravdu chcete smazat všechny zprávy v této složce?';
$messages['folderdeleting'] = 'Odstraňuji složku...';
$messages['foldermoving'] = 'Přesouvám složku...';
$messages['formincomplete'] = 'Formulář nebyl korektně vyplněn';
$messages['noemailwarning'] = 'Zadejte, prosím, platnou e-mailovou adresu';
$messages['nonamewarning'] = 'Zadejte, prosím, jméno';
$messages['nopagesizewarning'] = 'Zadejte, prosím, velikost stránky';
$messages['nosenderwarning'] = 'Zadejte e-mailovou adresu odesílatele';
$messages['norecipientwarning'] = 'Zadejte, prosím, alespoň jednoho příjemce';
$messages['nosubjectwarning'] = 'Předmět nebyl vyplňen. Přejete si jej zadat nyní?';
$messages['nobodywarning'] = 'Opravdu chtete odeslat prázdnou zprávu?';
$messages['notsentwarning'] = 'Zpráva nebyla odeslána. Přejete si zprávu zahodit?';
$messages['noldapserver'] = 'Zvolte, prosím, LDAP server k hledání';
$messages['nocontactsreturned'] = 'Nebyly nalezeny žádné kontakty';
$messages['nosearchname'] = 'Zadejte, prosím, jméno nebo e-mail kontaktu';
$messages['notuploadedwarning'] = 'Ještě nebyly nahrány všechny přílohy. Počkejte prosím nebo nahrávání zrušte.';
$messages['searchsuccessful'] = '$nr zpráv nalezeno';
$messages['searchnomatch'] = 'Nenalezena žádná zpráva';
$messages['searching'] = 'Vyhledávám...';
$messages['checking'] = 'Kontroluji...';
$messages['nospellerrors'] = 'Nebyly nalezeny žádné chyby';
$messages['folderdeleted'] = 'Složka byla úspěšně vymazána';
$messages['deletedsuccessfully'] = 'Úspěšně smazáno';
$messages['converting'] = 'Odstraňuji formátování ze zprávy...';
$messages['messageopenerror'] = 'Nemohu načíst zprávu ze serveru';
$messages['fileuploaderror'] = 'Nahrávání souboru selhalo';
$messages['filesizeerror'] = 'Soubor přesáhl maximální velikost $size';
$messages['copysuccess'] = 'Úspěšně zkopírováno $nr adres';
$messages['copyerror'] = 'Nemohu zkopírovat žádnou adresu';
$messages['sourceisreadonly'] = 'Tento zdroj adres je pouze pro čtení';
$messages['errorsavingcontact'] = 'Nemohu uložit adresu kontaktu';
$messages['movingmessage'] = 'Přesouvám zprávu...';
$messages['copyingmessage'] = 'Kopíruji zprávu...';
$messages['deletingmessage'] = 'Odstraňuji zprávu...';
$messages['markingmessage'] = 'Označuji zprávu...';
$messages['receiptsent'] = 'Potvrzení o přřijetí zprávy odesláno';
$messages['errorsendingreceipt'] = 'Potvrzení o přijetí zprávy nebylo možné odeslat';
$messages['nodeletelastidentity'] = 'Alespoň jedna identita musí být ponechána. Identitu nelze odstranit.';
$messages['forbiddencharacter'] = 'Název složky obsahuje zakázaný znak';
$messages['selectimportfile'] = 'Prosím vyberte soubor k nahrání';
$messages['addresswriterror'] = 'Vybraný seznam kontaktů není zapisovatelný';
$messages['contactaddedtogroup'] = 'Kontakty byly úspěšně přidány do této skupiny';
$messages['contactremovedfromgroup'] = 'Kontakty byly odstraněny z této skupiny';
$messages['importwait'] = 'Importuji, prosím čekejte...';
$messages['importerror'] = 'Během importu nastala chyba! Nahraný soubor není ve formátu vCard.';
$messages['importconfirm'] = 'Úspěšně naimportováno $inserted kontaktů, $skipped existujících záznamů přeskočeno:$names';
$messages['opnotpermitted'] = 'Operace není povolena!';
$messages['nofromaddress'] = 'Chybějící e-mailová adresa v označeném profilu';
$messages['editorwarning'] = 'Přepnutím do režimu prostého textu ztratíte veškeré formátování. Chcete pokračovat?';
$messages['httpreceivedencrypterror'] = 'Vyskytla se vážná chyba v konfiguraci. Kontaktujte neprodleně administátora. Vaše zpráva nemohla být odeslána.';
$messages['smtpconnerror'] = 'Chyba SMTP ($code): Připojení k serveru selhalo';
$messages['smtpautherror'] = 'Chyba SMTP ($code): Chyba přihlášení';
$messages['smtpfromerror'] = 'Chyba SMTP ($code): Nelze nastavit odesílatele "$from" ($msg)';
$messages['smtptoerror'] = 'Chyba SMTP ($code): Nelze přidat příjemce "$to" ($msg)';
$messages['smtprecipientserror'] = 'Chyba SMTP: Nelze zpracovat seznam příjemců';
$messages['smtpdsnerror'] = 'Chyba SMTP: Není podporováno oznamení o stavu doručení (DSN)';
$messages['smtperror'] = 'Chyba SMTP: $msg';
$messages['emailformaterror'] = 'Neplatná e-mailová adresa: $email';
$messages['toomanyrecipients'] = 'Příliš mnoho příjemců. Změňte počet příjemců na $max.';
$messages['maxgroupmembersreached'] = 'Počet členských skupin dosáhl maximum z $max';
$messages['internalerror'] = 'Došlo k interní chybě. Zkuste to znovu';
$messages['contactdelerror'] = 'Kontakty nelze odstranit';
$messages['contactdeleted'] = 'Kontakty byly odstraněny';
$messages['groupdeleted'] = 'Skupina byla odstraněna';
$messages['grouprenamed'] = 'Skupina byla přejmenována';
$messages['groupcreated'] = 'Skupina vytvořena';
$messages['messagedeleted'] = 'Zpráva odstraněna';
$messages['messagemoved'] = 'Zpráva byla přesunuta';
$messages['messagecopied'] = 'Zpráva byla zkopirována';
$messages['messagemarked'] = 'Zpráva označena';
$messages['autocompletechars'] = 'Napište alespoň $min znaků pro automatické doplnění';

?>
