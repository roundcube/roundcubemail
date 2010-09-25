<?php

/*

+-----------------------------------------------------------------------+
| language/sk/messages.inc                                              |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Marek Kutnar <marek19@zmail.sk>                               |
|         Lukas Kraic  <lukas.kraic@truni.sk>                           |
|         Peter Mann   <peter.mann@tuke.sk>                             |
|         Ales Pospichal   <ales@pospichalales.info>                    |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Chybné prihlásenie';
$messages['cookiesdisabled'] = 'Váš internetový prehliadač nepodporuje cookies, ktoré sú potrebné pre prihlásenie';
$messages['sessionerror'] = 'Vaše prihlásenie je neplatné alebo vypršala jeho platnosť';
$messages['imaperror'] = 'Nepodarilo sa spojiť s IMAP serverom';
$messages['servererror'] = 'Chyba servera!';
$messages['invalidrequest'] = 'Chybný požiadavek. Žiadne údaje neboli uložené.';
$messages['nomessagesfound'] = 'Vo Vašej schránke nie je žiadna správa';
$messages['loggedout'] = 'Odhlásenie prebehlo úspešne. Dovidenia.';
$messages['mailboxempty'] = 'Schránka je prázdna';
$messages['loading'] = 'Načítava sa...';
$messages['uploading'] = 'Nahrávám súbor...';
$messages['loadingdata'] = 'Načítavajú sa údaje...';
$messages['checkingmail'] = 'Kontrolujú sa nové správy...';
$messages['sendingmessage'] = 'Správa sa odosiela...';
$messages['messagesent'] = 'Správa bola úspešne odoslaná';
$messages['savingmessage'] = 'Správa sa ukladá...';
$messages['messagesaved'] = 'Správa bola uložená medzi Rozpísané správy';
$messages['successfullysaved'] = 'Úspešne uložená';
$messages['addedsuccessfully'] = 'Kontakt bol pridaný do adresára';
$messages['contactexists'] = 'Kontakt s touto e-mailovou adresou už existuje';
$messages['blockedimages'] = 'Z bezpečnostných dôvodov sú v správe zablokované obrázky.';
$messages['encryptedmessage'] = 'Táto správa je zašifrovaná, a nie je možné ju zobraziť!';
$messages['nocontactsfound'] = 'Nemáte žiadne kontakty';
$messages['contactnotfound'] = 'Požadovaný kontakt nebol nájdený';
$messages['sendingfailed'] = 'Odosielanie správy zlyhalo';
$messages['senttooquickly'] = 'Počkajte $sec sekúnd pred odoslaním tejto správy';
$messages['errorsavingsent'] = 'Pri ukladaní odoslanej správy nastala chyba';
$messages['errorsaving'] = 'Vyskytla sa chyba pri ukladaní';
$messages['errormoving'] = 'Správa sa nedá presunúť';
$messages['errorcopying'] = 'Správa sa nedá skopírovať';
$messages['errordeleting'] = 'Správa sa nedá zmazať';
$messages['errormarking'] = 'Správa sa nedá označiť';
$messages['deletecontactconfirm'] = 'Ste si istý, že chcete zmazať zvolený(é) kontakt(y)?';
$messages['deletemessagesconfirm'] = 'Ste si istý, že chcete zmazať zvolenú(é) správu(y)?';
$messages['deletefolderconfirm'] = 'Ste si istý, že chcete zmazať tento priečinok?';
$messages['purgefolderconfirm'] = 'Ste si istý, že chcete zmazať všetky správy v tomto priečinku?';
$messages['foldercreating'] = 'Vytvára sa priečinok ...';
$messages['folderdeleting'] = 'Odstraňuje sa priečinok ...';
$messages['folderrenaming'] = 'Mení sa názov priečinka ...';
$messages['foldermoving'] = 'Prečinok sa presúva ...';
$messages['formincomplete'] = 'Formulár nie je kompletne vyplnený';
$messages['noemailwarning'] = 'Prosím, vložte platnú emailovú adresu';
$messages['nonamewarning'] = 'Prosím, zadajte meno';
$messages['nopagesizewarning'] = 'Prosím, zadajte veľkosť strany';
$messages['nosenderwarning'] = 'Prosím, zadajte adresu odosielateľa';
$messages['norecipientwarning'] = 'Prosím, vložte aspoň jedného príjemcu';
$messages['nosubjectwarning'] = 'Predmet správy je prázdny. Chcete ho teraz zadať?';
$messages['nobodywarning'] = 'Chcete odoslať správu bez textu?';
$messages['notsentwarning'] = 'Správa nebola odoslaná, chcete ju zrušiť?';
$messages['noldapserver'] = 'Prosím, zvoľte LDAP server na vyhľadávanie';
$messages['nocontactsreturned'] = 'Neboli nájdené žiadne kontakty';
$messages['nosearchname'] = 'Prosím vložte meno alebo emailovú adresu';
$messages['notuploadedwarning'] = 'Ešte neboli nahrané všetky prílohy. Počkajte prosím alebo nahrávanie zrušte.';
$messages['searchsuccessful'] = 'nájdených $nr správ';
$messages['searchnomatch'] = 'Hľadaný výraz nebol nájdený';
$messages['searching'] = 'Vyhľadáva sa...';
$messages['checking'] = 'Kontroluje sa...';
$messages['nospellerrors'] = 'Pri kontrole pravopisu neboli nájdené chyby';
$messages['folderdeleted'] = 'Priečinok bol zmazaný';
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
$messages['receiptsent'] = 'Potvrdenie o prijatí správy bolo odoslané';
$messages['errorsendingreceipt'] = 'Potvrdenie o prijatí správy sa nedalo odoslať';
$messages['nodeletelastidentity'] = 'Identita sa nedá odstrániť, je posledná a musí zostať.';
$messages['addsubfolderhint'] = 'Tento priečinok bude vytvorený ako podpriečinok aktuálne zvoleného priečinka';
$messages['forbiddencharacter'] = 'Názov priečinka obsahuje nepovolený znak';
$messages['selectimportfile'] = 'Zvoľte súbor, ktorý chcete načítať';
$messages['addresswriterror'] = 'Zvolený adresár kontaktov je iba na čítanie';
$messages['contactaddedtogroup'] = 'Kontakty boli úspešne presunuty do tejto skupiny';
$messages['contactremovedfromgroup'] = 'Kontakty boli úspešne odstráneny z tejto skupiny';
$messages['importwait'] = 'Prebieha import, počkajte ...';
$messages['importerror'] = 'Import zlyhal. Načítavaný súbor nie je platným vCard súborom.';
$messages['importconfirm'] = '<b>Úspešne sa načítalo $inserted kontaktov, preskočilo sa $skipped existujúcich záznamov</b>:<p><em>$names</em></p>';
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

?>
