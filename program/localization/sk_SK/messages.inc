<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'Vyskytla sa chyba!';
$messages['loginfailed']  = 'Chybné prihlásenie';
$messages['cookiesdisabled'] = 'Váš internetový prehliadač nepodporuje cookies, ktoré sú potrebné pre prihlásenie';
$messages['sessionerror'] = 'Vaše prihlásenie je neplatné alebo vypršala jeho platnosť';
$messages['storageerror'] = 'Nepodarilo sa spojiť s IMAP serverom';
$messages['servererror'] = 'Chyba servera!';
$messages['servererrormsg'] = 'Chyba servera: $msg';
$messages['dberror'] = 'Databázová chyba!';
$messages['requesttimedout'] = 'Čas požiadavky vypršal';
$messages['errorreadonly'] = 'Nemožno vykonať operáciu. Adresár je len na čítanie';
$messages['errornoperm'] = 'Nemožno vykonať operáciu. Prístup odmietnutý';
$messages['invalidrequest'] = 'Chybný požiadavek. Žiadne údaje neboli uložené.';
$messages['invalidhost'] = 'Neplatný názov servera';
$messages['nomessagesfound'] = 'Vo Vašej schránke nie je žiadna správa';
$messages['loggedout'] = 'Odhlásenie prebehlo úspešne. Dovidenia.';
$messages['mailboxempty'] = 'Schránka je prázdna';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'Načítava sa...';
$messages['uploading'] = 'Nahrávám súbor...';
$messages['uploadingmany'] = 'Nahrávam súbory...';
$messages['loadingdata'] = 'Načítavajú sa údaje...';
$messages['checkingmail'] = 'Kontrolujú sa nové správy...';
$messages['sendingmessage'] = 'Správa sa odosiela...';
$messages['messagesent'] = 'Správa bola úspešne odoslaná';
$messages['savingmessage'] = 'Správa sa ukladá...';
$messages['messagesaved'] = 'Správa bola uložená medzi Rozpísané správy';
$messages['successfullysaved'] = 'Úspešne uložená';
$messages['addedsuccessfully'] = 'Kontakt bol pridaný do adresára';
$messages['contactexists'] = 'Kontakt s touto e-mailovou adresou už existuje';
$messages['contactnameexists'] = 'Kontakt s rovnakým menom už existuje.';
$messages['blockedimages'] = 'Z bezpečnostných dôvodov sú v správe zablokované obrázky.';
$messages['encryptedmessage'] = 'Táto správa je zašifrovaná, a nie je možné ju zobraziť!';
$messages['nocontactsfound'] = 'Nemáte žiadne kontakty';
$messages['contactnotfound'] = 'Požadovaný kontakt nebol nájdený';
$messages['contactsearchonly'] = 'Zadaj nejaký vyhľadávací výraz pre hľadanie kontaktov.';
$messages['sendingfailed'] = 'Odosielanie správy zlyhalo';
$messages['senttooquickly'] = 'Počkajte $sec sekúnd pred odoslaním tejto správy';
$messages['errorsavingsent'] = 'Pri ukladaní odoslanej správy nastala chyba';
$messages['errorsaving'] = 'Vyskytla sa chyba pri ukladaní';
$messages['errormoving'] = 'Správa sa nedá presunúť';
$messages['errorcopying'] = 'Správa sa nedá skopírovať';
$messages['errordeleting'] = 'Správa sa nedá zmazať';
$messages['errormarking'] = 'Správa sa nedá označiť';
$messages['deletecontactconfirm']  = 'Ste si istý, že chcete zmazať zvolený(é) kontakt(y)?';
$messages['deletegroupconfirm']  = 'Skutočne chceš zmazať vybranú skupinu?';
$messages['deletemessagesconfirm'] = 'Ste si istý, že chcete zmazať zvolenú(é) správu(y)?';
$messages['deletefolderconfirm']  = 'Ste si istý, že chcete zmazať tento priečinok?';
$messages['purgefolderconfirm']  = 'Ste si istý, že chcete zmazať všetky správy v tomto priečinku?';
$messages['contactdeleting'] = 'Mažem kontakt(y)...';
$messages['groupdeleting'] = 'Mažem skupinu...';
$messages['folderdeleting'] = 'Odstraňuje sa priečinok ...';
$messages['foldermoving'] = 'Prečinok sa presúva ...';
$messages['foldersubscribing'] = 'Prihlasujem sa k adresáru...';
$messages['folderunsubscribing'] = 'Odhlasujem sa z adresára...';
$messages['formincomplete'] = 'Formulár nie je kompletne vyplnený';
$messages['noemailwarning'] = 'Prosím, vložte platnú emailovú adresu';
$messages['nonamewarning']  = 'Prosím, zadajte meno';
$messages['nopagesizewarning'] = 'Prosím, zadajte veľkosť strany';
$messages['nosenderwarning'] = 'Prosím, zadajte adresu odosielateľa';
$messages['norecipientwarning'] = 'Prosím, vložte aspoň jedného príjemcu';
$messages['nosubjectwarning']  = 'Predmet správy je prázdny. Chcete ho teraz zadať?';
$messages['nobodywarning'] = 'Chcete odoslať správu bez textu?';
$messages['notsentwarning'] = 'Správa nebola odoslaná, chcete ju zrušiť?';
$messages['noldapserver'] = 'Prosím, zvoľte LDAP server na vyhľadávanie';
$messages['nosearchname'] = 'Prosím vložte meno alebo emailovú adresu';
$messages['notuploadedwarning'] = 'Ešte neboli nahrané všetky prílohy. Počkajte prosím alebo nahrávanie zrušte.';
$messages['searchsuccessful'] = 'nájdených $nr správ';
$messages['contactsearchsuccessful'] = 'Bolo nájdených $nr kontaktov.';
$messages['searchnomatch'] = 'Hľadaný výraz nebol nájdený';
$messages['searching'] = 'Vyhľadáva sa...';
$messages['checking'] = 'Kontroluje sa...';
$messages['nospellerrors'] = 'Pri kontrole pravopisu neboli nájdené chyby';
$messages['folderdeleted'] = 'Priečinok bol zmazaný';
$messages['foldersubscribed'] = 'Úspešne prihlásený k adresáru';
$messages['folderunsubscribed'] = 'Úspešne odhlásený z adresára';
$messages['folderpurged'] = 'Adresár bol vyprázdnený';
$messages['folderexpunged'] = 'Adresár bol zhustený';
$messages['deletedsuccessfully'] = 'Úspešne zmazané';
$messages['converting'] = 'Odstraňuje sa formátovanie správy...';
$messages['messageopenerror'] = 'Nedá sa načítať správa zo servera';
$messages['fileuploaderror'] = 'Načítanie súboru nebolo úspešné';
$messages['filesizeerror'] = 'Načítavaný súbor prekročil maximálnu veľkosť $size';
$messages['copysuccess'] = 'Úspešne sa skopírovalo $nr adries';
$messages['copyerror'] = 'Nedá sa kopírovať žiadna adresa';
$messages['sourceisreadonly'] = 'Tento zdroj adries je len na čítanie';
$messages['errorsavingcontact'] = 'Nedá sa uložiť adresa kontaktu';
$messages['movingmessage'] = 'Správa sa presúva...';
$messages['copyingmessage'] = 'Správa sa kopíruje...';
$messages['copyingcontact'] = 'Kopírujem kontakt(y)';
$messages['deletingmessage'] = 'Mažem správu(y)...';
$messages['markingmessage'] = 'Označujem správu(y)...';
$messages['addingmember'] = 'Pridávam kontakt(y) do skupiny...';
$messages['removingmember'] = 'Odoberám kontakt(y) zo skupiny';
$messages['receiptsent'] = 'Potvrdenie o prijatí správy bolo odoslané';
$messages['errorsendingreceipt'] = 'Potvrdenie o prijatí správy sa nedalo odoslať';
$messages['deleteidentityconfirm'] = 'Skutočne chcete zmazať túto identitu?';
$messages['nodeletelastidentity'] = 'Identita sa nedá odstrániť, je posledná a musí zostať.';
$messages['forbiddencharacter'] = 'Názov priečinka obsahuje nepovolený znak';
$messages['selectimportfile'] = 'Zvoľte súbor, ktorý chcete načítať';
$messages['addresswriterror'] = 'Zvolený adresár kontaktov je iba na čítanie';
$messages['contactaddedtogroup'] = 'Kontakty boli úspešne presunuty do tejto skupiny';
$messages['contactremovedfromgroup'] = 'Kontakty boli úspešne odstráneny z tejto skupiny';
$messages['nogroupassignmentschanged'] = 'Priradenia do skupín neboli zmenené.';
$messages['importwait'] = 'Prebieha import, počkajte ...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>Úspešne sa načítalo $inserted kontaktov, preskočilo sa $skipped existujúcich záznamov</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Preskočených $skipped existujúcich záznamov</b>';
$messages['opnotpermitted'] = 'Operácia nie je povolená!';
$messages['nofromaddress'] = 'Zvolená identita neobsahuje e-mailovú adresu';
$messages['editorwarning'] = 'Prepnutie na editor obyčajného textu spôsobí stratu formátovania. Chcete napriek tomu pokračovať?';
$messages['httpreceivedencrypterror'] = 'Vyskytla sa vážna chyba v konfigurácii. Kontaktujte bezodkladne administrátora. <b>Vaša správa nemohla byť odoslaná.</b>';
$messages['smtpconnerror'] = 'Chyba SMTP: Pripojenie na server zlyhalo';
$messages['smtpautherror'] = 'Chyba SMTP: Autorizácie zlyhala';
$messages['smtpfromerror'] = 'Chyba SMTP: Nemožno nastaviť odosielateľa ($msg)';
$messages['smtptoerror'] = 'Chyba SMTP: Nemožno pridať príjemca ($msg)';
$messages['smtprecipientserror'] = 'Chyba SMTP: Nemožno spracovať zoznam príjemcov';
$messages['smtperror'] = 'Chyba SMTP: $msg';
$messages['emailformaterror'] = 'Neplatná e-mailová adresa: $email';
$messages['toomanyrecipients'] = 'Príliš veľa príjemcov. Zmenšite počet príjemcov na $max.';
$messages['maxgroupmembersreached'] = 'Počet členov skupiny dosiahol maxima z $max';
$messages['internalerror'] = 'Došlo k internej chybe. Skúste to znova';
$messages['contactdelerror'] = 'Nemôžem vymazať kontakt(y)';
$messages['contactdeleted'] = 'Kontakt(y) bol vymazaný';
$messages['contactrestoreerror'] = 'Nemôžem obnoviť zmazané kontakty';
$messages['contactrestored'] = 'Kontakty boli úspešne obnovené';
$messages['groupdeleted'] = 'Skupina bola vymazaná';
$messages['grouprenamed'] = 'Skupina bola premenovaná';
$messages['groupcreated'] = 'Skupina bola vytvorená';
$messages['savedsearchdeleted'] = 'Uložené vyhľadávanie bolo vymazané.';
$messages['savedsearchdeleteerror'] = 'Nemôžem zmazať uložené vyhľadávanie.';
$messages['savedsearchcreated'] = 'Uložené vyhľadávanie bolo vytvorené.';
$messages['savedsearchcreateerror'] = 'Nemôžem vytvoriť uložené vyhľadávanie';
$messages['messagedeleted'] = 'Správa(y) bola vymazaná';
$messages['messagemoved'] = 'Správa(y) bola presunutá';
$messages['messagecopied'] = 'Správa(y) bola skopírovaná';
$messages['messagemarked'] = 'Správa(y) bola označená';
$messages['autocompletechars'] = 'Zadajte najmenej $min znamkov pre automatické dopĺňanie';
$messages['autocompletemore'] = 'Bolo nájdených viac záznamov. Napíšte prosím viac znakov.';
$messages['namecannotbeempty'] = 'Meno nemôže byť prázdne';
$messages['nametoolong'] = 'Meno je príliš dlhé';
$messages['folderupdated'] = 'Adresár bol aktualizovaný';
$messages['foldercreated'] = 'Adresár bol vytvorený';
$messages['invalidimageformat'] = 'Zlý formát obrázku';
$messages['mispellingsfound'] = 'V správe boli nájdené pravopisné chyby';
$messages['parentnotwritable'] = 'Nemôžem vytvoriť/presunúť adresár do zvoleného nadradeného adresára. Nemáte oprávnenia na zmenu.';
$messages['messagetoobig'] = 'Časť správy je príliš veľká na spracovanie.';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = 'Warning: This webmail service requires Javascript! In order to use it please enable Javascript in your browser\'s settings.';

?>
