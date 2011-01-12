<?php

/*

+-----------------------------------------------------------------------+
| language/hu_HU/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Zark Bonfire <ikkhares at gmail dot com>                      |
|         Peter Szabo <pete at teamlupus dot hu>                        |
|         Barnabas Bona <bsi at hosember dot hu>                        |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Sikertelen belépés';
$messages['cookiesdisabled'] = 'A böngésző nem támogatja a sütik használatát';
$messages['sessionerror'] = 'Érvénytelen vagy lejárt munkamenet';
$messages['imaperror'] = 'Nem sikerült a kapcsolódás az IMAP szerverhez';
$messages['servererror'] = 'Szerverhiba!';
$messages['servererrormsg'] = 'Szerverhiba: $msg';
$messages['errorreadonly'] = 'A műveletet nem sikerült végrehajtani. A mappa írásvédett';
$messages['errornoperm'] = 'A műveletet nem sikerült végrehajtani. Hozzáférés megtagadva';
$messages['invalidrequest'] = 'Érvénytelen kérés! Az adatok nem lettek elmentve.';
$messages['nomessagesfound'] = 'A fiók nem tartalmaz leveleket';
$messages['loggedout'] = 'Sikeres kijelentkezés. Viszontlátásra!';
$messages['mailboxempty'] = 'A fiók üres';
$messages['loading'] = 'Betöltés...';
$messages['uploading'] = 'Fájl feltöltése...';
$messages['loadingdata'] = 'Az adatok betöltése...';
$messages['checkingmail'] = 'Új üzenetek keresése...';
$messages['sendingmessage'] = 'Az üzenet küldése...';
$messages['messagesent'] = 'Az üzenet elküldve';
$messages['savingmessage'] = 'Az üzenet mentése...';
$messages['messagesaved'] = 'Az üzenet elmentve a Piszkozatokhoz';
$messages['successfullysaved'] = 'A mentés sikerült';
$messages['addedsuccessfully'] = 'A cím hozzáadása a címjegyzékhez megtörtént';
$messages['contactexists'] = 'A kapcsolat már létezik ezzel az e-mail címmel';
$messages['blockedimages'] = 'Biztonsági okokból a távoli képek letöltése tiltott.';
$messages['encryptedmessage'] = 'Az üzenet titkosított, ezért nem megjeleníteníthető.';
$messages['nocontactsfound'] = 'Nincs kapcsolat';
$messages['contactnotfound'] = 'A kiválasztott kapcsolat nem található';
$messages['sendingfailed'] = 'Az üzenet elküldése nem sikerült';
$messages['senttooquickly'] = 'Kérem várjon még $sec másodpercet az üzenet elküldése előtt';
$messages['errorsavingsent'] = 'Hiba történt az elküldött üzenet mentése közben';
$messages['errorsaving'] = 'A mentés során hiba lépett fel';
$messages['errormoving'] = 'Az üzenetet nem sikerült áthelyezni';
$messages['errorcopying'] = 'Az üzenetet nem sikerült másolni';
$messages['errordeleting'] = 'Az üzenetet nem sikerült törölni';
$messages['errormarking'] = 'Az üzenetet nem sikerült megjelölni';
$messages['deletecontactconfirm'] = 'Biztos, hogy törölni szeretné a kijelölt címe(ke)t?';
$messages['deletemessagesconfirm'] = 'Biztos, hogy törölni szeretné a kijelölt üzenete(ke)t?';
$messages['deletefolderconfirm'] = 'Biztos, hogy törölni szeretné ezt a mappát?';
$messages['purgefolderconfirm'] = 'Biztos benne, hogy az összes üzenet törölhető?';
$messages['folderdeleting'] = 'Mappa törlése...';
$messages['foldermoving'] = 'Mappa mozgatása...';
$messages['foldersubscribing'] = 'Feliratkozás a mappára...';
$messages['folderunsubscribing'] = 'Leiratkozás a mappáról...';
$messages['formincomplete'] = 'Az űrlap hiányosan lett kitöltve';
$messages['noemailwarning'] = 'Adjon meg egy valós e-mail címet';
$messages['nonamewarning'] = 'Adjon meg egy nevet';
$messages['nopagesizewarning'] = 'Adja meg a papír méretét';
$messages['nosenderwarning'] = 'Kérem adja meg a feladó e-mail címét';
$messages['norecipientwarning'] = 'Legalább egy címzettet adjon meg';
$messages['nosubjectwarning'] = 'A Tárgy mező üres. Szeretné most kitölteni?';
$messages['nobodywarning'] = 'Elküldi az üzenetet üresen?';
$messages['notsentwarning'] = 'Az üzenet még nem lett elküldve. Eldobja az üzenetet?';
$messages['noldapserver'] = 'Adjon meg egy LDAP szervert a kereséshez';
$messages['nocontactsreturned'] = 'Nem található kapcsolat';
$messages['nosearchname'] = 'Adja meg a kapcsolat nevét vagy e-mail címét';
$messages['notuploadedwarning'] = 'Még nem került feltöltésre minden csatolmány. Kérem várjon vagy állítsa le a feltöltést!';
$messages['searchsuccessful'] = '$nr üzenet található';
$messages['searchnomatch'] = 'Nincs találat';
$messages['searching'] = 'Keresés...';
$messages['checking'] = 'Ellenőrzés...';
$messages['nospellerrors'] = 'Nem található helyesírási hiba';
$messages['folderdeleted'] = 'A mappa sikeresen törölve';
$messages['foldersubscribed'] = 'A feliratkozás a mappára sikeres';
$messages['folderunsubscribed'] = 'A leiratkozás a mappáról sikeres';
$messages['folderpurged'] = 'Mappa sikeresen törölve';
$messages['folderexpunged'] = 'Mappa sikeresen kiürítve';
$messages['deletedsuccessfully'] = 'Törölve';
$messages['converting'] = 'Formázás eltávolítása az üzenetből...';
$messages['messageopenerror'] = 'A levelek a szerverről nem tölthetők le';
$messages['fileuploaderror'] = 'Feltöltés sikertelen';
$messages['filesizeerror'] = 'A feltöltött fájl mérete meghaladja a maximális méretet $size';
$messages['copysuccess'] = '$nr cím sikeresen másolva';
$messages['copyerror'] = 'Egyetlen cím sem másolható';
$messages['sourceisreadonly'] = 'Ez a címforrás csak olvasható';
$messages['errorsavingcontact'] = 'A kapcsolat címe nem menthető';
$messages['movingmessage'] = 'Üzenet mozgatása...';
$messages['copyingmessage'] = 'Üzenet másolása...';
$messages['deletingmessage'] = 'Üzenet(ek) törlése....';
$messages['markingmessage'] = 'Üzenet(ek) megjelölése...';
$messages['receiptsent'] = 'Az olvasási visszaigazolás el lett küldve';
$messages['errorsendingreceipt'] = 'Az olvasási visszaigazolást nem sikerült elküldeni';
$messages['nodeletelastidentity'] = 'Nem törölheti ezt az azonosítót, ez az egyetlen';
$messages['forbiddencharacter'] = 'A mappa neve tiltott karaktert tartalmaz';
$messages['selectimportfile'] = 'Kérjük válassza ki a feltölteni kívánt fájlt';
$messages['addresswriterror'] = 'A kiválasztott címjegyzék nem írható';
$messages['contactaddedtogroup'] = 'A cím hozzáadása a csoporthoz sikeresen megtörtént';
$messages['contactremovedfromgroup'] = 'A cím törlése a csoportból sikeresen megtörtént';
$messages['importwait'] = 'Importálás folyamatban, kérem várjon...';
$messages['importerror'] = 'Az importálás sikertelen! A feltöltött állomány nem egy érvényes vCard fájl.';
$messages['importconfirm'] = '<b>Sikeresen importálásra került $inserted kapcsolat, kihagyva $skipped már létező bejegyzés</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'A művelet nem megengedett!';
$messages['nofromaddress'] = 'Hiányzó e-mail cím a kiválasztott feladónál';
$messages['editorwarning'] = 'Az egyszerű szöveges formátumra való váltás az összes formázás elvesztésével jár. Biztosan folytatja?';
$messages['httpreceivedencrypterror'] = 'Végzetes konfigurációs hiba történt, azonnal lépjen kapcsolatba az üzemeltetővel. <b>Az üzenet nem küldhető el.</b>';
$messages['smtpconnerror'] = 'SMTP hiba ($code): Sikertelen kapcsolódás a szerverhez';
$messages['smtpautherror'] = 'SMTP hiba ($code): Sikertelen bejelentkezés';
$messages['smtpfromerror'] = 'SMTP hiba ($code): Nem sikerült a feladó beállítása: "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP hiba ($code): Nem sikerült a következő címzett hozzáadása: "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP hiba ($code): A címzettlista feldolgozása sikertelen';
$messages['smtpdsnerror'] = 'SMTP hiba: A kézbesítési visszaigazolásokat a szerver nem támogatja';
$messages['smtperror'] = 'SMTP hiba ($code): $msg';
$messages['emailformaterror'] = 'Helytelen formátumú e-mail cím: $email';
$messages['toomanyrecipients'] = 'Túl sok a címzett. Csökkentse a címzettek számát $max címre.';
$messages['maxgroupmembersreached'] = 'A csoport létszáma meghaladja a maximum $max címet.';
$messages['internalerror'] = 'Belső hiba történt, kérjük próbálja újra!';
$messages['contactdelerror'] = 'Hiba a kapcsolat(ok) törlésekor';
$messages['contactdeleted'] = 'Kapcsolt(ok) sikeresen törölve';
$messages['groupdeleted'] = 'Csoport sikeresen törölve';
$messages['grouprenamed'] = 'Csoport sikeresen átnevezve';
$messages['groupcreated'] = 'Csoport sikeresen létrehozva';
$messages['messagedeleted'] = 'Üzenet(ek) sikeresen törölve';
$messages['messagemoved'] = 'Üzenet(ek) sikeresen átmozgatva';
$messages['messagecopied'] = 'Üzenet(ek) sikeresen másolva';
$messages['messagemarked'] = 'Üzenet(ek) sikeresen megjelölve';
$messages['autocompletechars'] = 'Az automatikus kiegészítéshez legalább $min karakter szükséges';
$messages['namecannotbeempty'] = 'A név nem lehet üres';
$messages['nametoolong'] = 'A név túl hosszú';
$messages['folderupdated'] = 'Mappa sikeresen frissítve';
$messages['foldercreated'] = 'Mappa sikeresen létrehozva';

?>
