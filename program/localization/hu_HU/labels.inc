<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Üdvözli a $product';
$labels['username']  = 'Felhasználónév';
$labels['password']  = 'Jelszó';
$labels['server']    = 'Szerver';
$labels['login']     = 'Bejelentkezés';

// taskbar
$labels['logout']   = 'Kijelentkezés';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Beállítások';
$labels['addressbook'] = 'Címjegyzék';

// mailbox names
$labels['inbox']  = 'Érkezett levelek';
$labels['drafts'] = 'Piszkozatok';
$labels['sent']   = 'Küldött levelek';
$labels['trash']  = 'Törölt elemek';
$labels['junk']   = 'Kéretlen levelek';

// message listing
$labels['subject'] = 'Tárgy';
$labels['from']    = 'Feladó';
$labels['sender']  = 'Feladó';
$labels['to']      = 'Címzett';
$labels['cc']      = 'Másolat';
$labels['bcc']     = 'Titkos másolat';
$labels['replyto'] = 'Válaszcím';
$labels['followupto'] = 'Hírcsoport válasz';
$labels['date']    = 'Dátum';
$labels['size']    = 'Méret';
$labels['priority'] = 'Sürgősség';
$labels['organization'] = 'Szervezet';
$labels['readstatus'] = 'Olvasott';
$labels['listoptions'] = 'Üzenetlista beállítások...';

$labels['mailboxlist'] = 'Mappák';
$labels['messagesfromto'] = 'Üzenetek: $from - $to / $count';
$labels['threadsfromto'] = 'Témák ($from - $to, összesen $count)';
$labels['messagenrof'] = '$nr / $count üzenet';
$labels['fromtoshort'] = '$from – $to / $count';

$labels['copy']     = 'Másolás';
$labels['move']     = 'Áthelyezés';
$labels['moveto']   = 'Áthelyezés...';
$labels['download'] = 'letöltés';
$labels['open']     = 'Megnyítás';
$labels['showattachment'] = 'Megjelenítés';
$labels['showanyway'] = 'Megjelnités mindenképpen';

$labels['filename'] = 'Fájl neve';
$labels['filesize'] = 'Fájl mérete';

$labels['addtoaddressbook'] = 'Hozzáadás a címjegyzékhez';

// weekdays short
$labels['sun'] = 'Vas';
$labels['mon'] = 'Hét';
$labels['tue'] = 'Kedd';
$labels['wed'] = 'Szer';
$labels['thu'] = 'Csüt';
$labels['fri'] = 'Pén';
$labels['sat'] = 'Szom';

// weekdays long
$labels['sunday']    = 'Vasárnap';
$labels['monday']    = 'Hétfő';
$labels['tuesday']   = 'Kedd';
$labels['wednesday'] = 'Szerda';
$labels['thursday']  = 'Csütörtök';
$labels['friday']    = 'Péntek';
$labels['saturday']  = 'Szombat';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Febr';
$labels['mar']	= 'Márc';
$labels['apr']	= 'Ápr';
$labels['may']	= 'Május';
$labels['jun']	= 'Jún';
$labels['jul'] 	= 'Júl';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Szept';
$labels['oct']	= 'Okt';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'Január';
$labels['longfeb']	= 'Február';
$labels['longmar']	= 'Március';
$labels['longapr']	= 'Április';
$labels['longmay']	= 'Május';
$labels['longjun']	= 'Június';
$labels['longjul']	= 'Július';
$labels['longaug']	= 'Augusztus';
$labels['longsep']	= 'Szeptember';
$labels['longoct']	= 'Október';
$labels['longnov']	= 'November';
$labels['longdec']	= 'December';

$labels['today'] = 'Ma';

// toolbar buttons
$labels['refresh']          = 'Frissítés';
$labels['checkmail']        = 'Új üzenetek ellenőrzése';
$labels['compose']          = 'Üzenet létrehozása';
$labels['writenewmessage']  = 'Új üzenet';
$labels['reply']            = 'Válasz';
$labels['replytomessage']   = 'Válasz';
$labels['replytoallmessage'] = 'Válasz a listának vagy a feladónak és az összes címzettnek';
$labels['replyall']         = 'Válasz mindenkinek';
$labels['replylist']        = 'Válasz a listának';
$labels['forward']          = 'Továbbítás';
$labels['forwardinline']    = 'Továbbítás beágyazva';
$labels['forwardattachment'] = 'Továbbítás csatolmányként';
$labels['forwardmessage']   = 'Továbbítás';
$labels['deletemessage']    = 'Törlés';
$labels['movemessagetotrash'] = 'Üzenet törlése';
$labels['printmessage']     = 'Nyomtatás';
$labels['previousmessage']  = 'Előző levél mutatása';
$labels['firstmessage']     = 'Első levél mutatása';
$labels['nextmessage']      = 'Következő levél mutatása';
$labels['lastmessage']      = 'Utolsó levél mutatása';
$labels['backtolist']       = 'Vissza az üzenetekhez';
$labels['viewsource']       = 'Forrás megtekintése';
$labels['mark']             = 'Jelölés';
$labels['markmessages']     = 'Üzenetek megjelölése';
$labels['markread']         = 'Olvasottként';
$labels['markunread']       = 'Olvasatlanként';
$labels['markflagged']      = 'Jelöltként';
$labels['markunflagged']    = 'Jelöletlenként';
$labels['moreactions']      = 'További műveletek...';
$labels['more']             = 'Még egy';
$labels['back']             = 'Vissza';
$labels['options']          = 'Beállítások';

$labels['select'] = 'Kijelölés';
$labels['all'] = 'Összes';
$labels['none'] = 'Nincs';
$labels['currpage'] = 'Aktuális oldal';
$labels['unread'] = 'Olvasatlan';
$labels['flagged'] = 'Megjelölt';
$labels['unanswered'] = 'Megválaszolatlan';
$labels['withattachment'] = 'Csatolmánnyal';
$labels['deleted'] = 'Törölt';
$labels['undeleted'] = 'Nem lett törölve';
$labels['invert'] = 'Invertálás';
$labels['filter'] = 'Szűrés';
$labels['list'] = 'Lista';
$labels['threads'] = 'Témák';
$labels['expand-all'] = 'Összes kibontása';
$labels['expand-unread'] = 'Olvasatlanok kibontása';
$labels['collapse-all'] = 'Összes összecsukása';
$labels['threaded'] = 'Téma nézet';

$labels['autoexpand_threads'] = 'Témák kibontása';
$labels['do_expand'] = 'minden téma';
$labels['expand_only_unread'] = 'csak ahol van olvasatlan üzenet';
$labels['fromto'] = 'Feladó/Címzett';
$labels['flag'] = 'Megjelölés';
$labels['attachment'] = 'Csatolmány';
$labels['nonesort'] = 'Nincs';
$labels['sentdate'] = 'Feladva';
$labels['arrival'] = 'Kézbesítve';
$labels['asc'] = 'Emelkedő';
$labels['desc'] = 'Csökkenő';
$labels['listcolumns'] = 'Oszlopok listája';
$labels['listsorting'] = 'Rendezés oszlopa';
$labels['listorder'] = 'Rendezés sorrendje';
$labels['listmode'] = 'Megjelenítési mód';

$labels['folderactions'] = 'Mappaműveletek...';
$labels['compact'] = 'Tömörítés';
$labels['empty'] = 'Kiürítés';

$labels['quota'] = 'Helyfoglalás';
$labels['unknown']  = 'ismeretlen';
$labels['unlimited']  = 'korlátlan';

$labels['quicksearch']  = 'Gyorskeresés';
$labels['resetsearch']  = 'Alapállapot';
$labels['searchmod']  = 'Keresési opciók';
$labels['msgtext']  = 'Teljes üzenet';
$labels['body']  = 'Törzs';

$labels['openinextwin'] = 'Megnyitás új ablakban';
$labels['emlsave'] = 'Letöltés (.eml)';
$labels['changeformattext'] = 'Megjelenítés sima szöveges formátumban';
$labels['changeformathtml'] = 'Megjelenítés HTML formátumban';

// message compose
$labels['editasnew']      = 'Szerkesztés újként';
$labels['send']           = 'Küldés';
$labels['sendmessage']    = 'Üzenet küldése';
$labels['savemessage']    = 'Vázlat mentése';
$labels['addattachment']  = 'Fájl csatolása';
$labels['charset']        = 'Karakterkészlet';
$labels['editortype']     = 'Szerkesztő típusa';
$labels['returnreceipt']  = 'Olvasási visszaigazolás';
$labels['dsn']            = 'Kézbesítési visszaigazolás';
$labels['mailreplyintro'] = '$date időpontban $sender ezt írta:';
$labels['originalmessage'] = 'Eredeti üzenet';

$labels['editidents']    = 'Azonosítók szerkesztése';
$labels['spellcheck']    = 'Helyesírás ellenőrzés';
$labels['checkspelling'] = 'Helyesírás-ellenőrzés';
$labels['resumeediting'] = 'Helyesírás-ellenőrzés vége';
$labels['revertto']      = 'Visszaállítás erre';

$labels['attach'] = 'Csatolás';
$labels['attachments'] = 'Csatolmányok';
$labels['upload'] = 'Feltöltés';
$labels['uploadprogress'] = '$percent ($current / $total)';
$labels['close']  = 'Bezárás';
$labels['messageoptions']  = 'Üzenetbeállítások...';

$labels['low']     = 'Alacsony';
$labels['lowest']  = 'Legkisebb';
$labels['normal']  = 'Normál';
$labels['high']    = 'Magas';
$labels['highest'] = 'Legmagasabb';

$labels['nosubject']  = '(nincs tárgy)';
$labels['showimages'] = 'Képek megjelenítése';
$labels['alwaysshow'] = 'Képek megjelenítése mindig, ha a feladó $sender';
$labels['isdraft']    = 'Ez egy piszkozat.';
$labels['andnmore']   = 'további $nr...';
$labels['togglemoreheaders'] = 'Több üzenet fejrész(header) megjelenítése';
$labels['togglefullheaders'] = 'Nyers üzenet fejléc megjelenítés- elrejtés';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Egyszerű szöveg';
$labels['savesentmessagein'] = 'Elküldött üzenet mentése ide';
$labels['dontsave'] = 'nincs mentés';
$labels['maxuploadsize'] = 'A maximális feltölthető fájl mérete $size';

$labels['addcc'] = 'Cc (Másolati cím) hozzáadása';
$labels['addbcc'] = 'Bcc (titkos másolati cím) hozzáadása';
$labels['addreplyto'] = 'Válaszcím hozzáadása';
$labels['addfollowupto'] = 'Hírcsoport válasz hozzáadása';

// mdn
$labels['mdnrequest'] = 'Az üzenet küldője értesítést kér arról, hogy elolvasta a levelet. El legyen küldve az olvasási visszaigazolás?';
$labels['receiptread'] = 'Olvasási visszaigazolás (olvasott)';
$labels['yourmessage'] = 'Ez az üzenete olvasási visszaigazolása';
$labels['receiptnote'] = 'Megjegyzés: Ez az olvasási visszaigazolás csak azt igazolja, hogy az üzenet megjelenítésre került a címzett számítógépén. Nincs rá garancia, hogy a címzett elolvasta volna az üzenetet illetve megértette volna annak tartalmát.';

// address boook
$labels['name']         = 'Megjelenített név';
$labels['firstname']    = 'Keresztnév';
$labels['surname']      = 'Vezetéknév';
$labels['middlename']   = 'Második keresztnév';
$labels['nameprefix']   = 'Név-előtag';
$labels['namesuffix']   = 'Név-utótag';
$labels['nickname']     = 'Becenév';
$labels['jobtitle']     = 'Beosztás';
$labels['department']   = 'Részleg';
$labels['gender']       = 'Nem';
$labels['maidenname']   = 'Leánykori név';
$labels['email']        = 'E-mail cím';
$labels['phone']        = 'Telefonszám';
$labels['address']      = 'Lakcím';
$labels['street']       = 'Utca';
$labels['locality']     = 'Város';
$labels['zipcode']      = 'Irányítószám';
$labels['region']       = 'Régió';
$labels['country']      = 'Ország';
$labels['birthday']     = 'Születésnap';
$labels['anniversary']  = 'Évforduló';
$labels['website']      = 'Weboldal';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Megjegyzések';
$labels['male']   = 'férfi';
$labels['female'] = 'nő';
$labels['manager'] = 'Menedzser';
$labels['assistant'] = 'Asszisztens';
$labels['spouse'] = 'Házastárs';
$labels['allfields'] = 'Összes mező';
$labels['search'] = 'Keresés';
$labels['advsearch'] = 'Részletes keresés';
$labels['advanced'] = 'Haladó';
$labels['other'] = 'Egyéb';

$labels['typehome']   = 'Otthon';
$labels['typework']   = 'Munkahely';
$labels['typeother']  = 'Egyéb';
$labels['typemobile']  = 'Mobil';
$labels['typemain']  = 'Vezetékes';
$labels['typehomefax']  = 'Otthoni fax';
$labels['typeworkfax']  = 'Munkahelyi fax';
$labels['typecar']  = 'Gépkocsi';
$labels['typepager']  = 'Személyhívó';
$labels['typevideo']  = 'Videó';
$labels['typeassistant']  = 'Asszisztens';
$labels['typehomepage']  = 'Weboldal';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Új mező hozzáadása...';
$labels['addcontact'] = 'Új kapcsolat hozzáadása';
$labels['editcontact'] = 'Kapcsolat szerkesztése';
$labels['contacts'] = 'Kapcsolatok';
$labels['contactproperties'] = 'Kapcsolat tulajdonságai';
$labels['personalinfo'] = 'Személyes adatok';

$labels['edit']   = 'Szerkesztés';
$labels['cancel'] = 'Mégsem';
$labels['save']   = 'Mentés';
$labels['delete'] = 'Törlés';
$labels['rename'] = 'Átnevezés';
$labels['addphoto'] = 'Kép hozzáadása';
$labels['replacephoto'] = 'Kép cseréje';
$labels['uploadphoto'] = 'Fotó feltöltése';

$labels['newcontact']     = 'Új kapcsolat létrehozása';
$labels['deletecontact']  = 'Kijelölt kapcsolatok törlése';
$labels['composeto']      = 'E-mail küldése erre a címre';
$labels['contactsfromto'] = 'Kapcsolatok: $from - $to / $count';
$labels['print']          = 'Nyomtatás';
$labels['export']         = 'Exportálás';
$labels['exportall']      = 'Összes exportálása';
$labels['exportsel']      = 'Kijelöltek exportálása';
$labels['exportvcards']   = 'Kapcsolatok exportálása vCard formátumban';
$labels['newcontactgroup'] = 'Új csoport';
$labels['grouprename']    = 'Csoport átnevezése';
$labels['groupdelete']    = 'Csoport törlése';
$labels['groupremoveselected'] = 'A kijelölt partnerek törlése a csopotból';

$labels['previouspage']   = 'Előző oldal';
$labels['firstpage']      = 'Első oldal';
$labels['nextpage']       = 'Következő oldal';
$labels['lastpage']       = 'Utolsó oldal';

$labels['group'] = 'Csoport';
$labels['groups'] = 'Csoportok';
$labels['personaladrbook'] = 'Személyes címjegyzék';

$labels['searchsave'] = 'Keresés mentése';
$labels['searchdelete'] = 'Keresés törlése';

$labels['import'] = 'Importálás';
$labels['importcontacts'] = 'Kapcsolatok importálása';
$labels['importfromfile'] = 'Importálás fájlból:';
$labels['importtarget'] = 'Kapcsolatok hozzáadása a címjegyzékhez:';
$labels['importreplace'] = 'A teljes címjegyzék cseréje';
$labels['importdesc'] = 'Feltölthetsz partnereket egyébb már létező címjegyzékekből. <br/>Jelenleg a következő két tipusú címjegyzékek importálása lehetséges  <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> és a CSV (comma-separated/vesszővel tagolt) formátum.';
$labels['done'] = 'Kész';

// settings
$labels['settingsfor'] = 'Beállítás';
$labels['about'] = 'Névjegy';
$labels['preferences'] = 'Beállítások';
$labels['userpreferences'] = 'Felhasználói beállítások';
$labels['editpreferences'] = 'Felhasználói beállítások szerkesztése';

$labels['identities'] = 'Azonosítók';
$labels['manageidentities'] = 'Hozzáférés azonosítóinak kezelése';
$labels['newidentity'] = 'Új azonosító';

$labels['newitem'] = 'Új elem';
$labels['edititem'] = 'Elem szerkesztése';

$labels['preferhtml'] = 'HTML megjelenítés';
$labels['defaultcharset'] = 'Alapértelmezett karakterkódolás';
$labels['htmlmessage'] = 'HTML üzenet';
$labels['messagepart'] = 'Rész';
$labels['digitalsig'] = 'Digitális aláírás';
$labels['dateformat'] = 'Dátumformátum';
$labels['timeformat'] = 'Időformátum';
$labels['prettydate'] = 'Rövid dátumok';
$labels['setdefault']  = 'Beállítás alapértelmezettnek';
$labels['autodetect']  = 'Automatikus';
$labels['language']  = 'Nyelv';
$labels['timezone']  = 'Időzóna';
$labels['pagesize']  = 'Sorok száma egy oldalon';
$labels['signature'] = 'Aláírás';
$labels['dstactive']  = 'Nyári időszámítás';
$labels['showinextwin'] = 'Az üzenet megnyitása egy új ablakban';
$labels['composeextwin'] = 'Űzenet írása új ablakban';
$labels['htmleditor'] = 'HTML üzenet írása';
$labels['htmlonreply'] = 'csak HTML üzenetre válaszolva';
$labels['htmlonreplyandforward'] = 'HTML üzenet továbbításakor vagy az arra való válaszoláskor';
$labels['htmlsignature'] = 'HTML aláírás';
$labels['showemail'] = 'Az email címek megjelenítése a partner nevével';
$labels['previewpane'] = 'Előnézeti kép';
$labels['skin'] = 'Kinézet';
$labels['logoutclear'] = 'Törölt elemek mappa ürítése kilépéskor';
$labels['logoutcompact'] = 'Beérkezett üzenetek mappa tömörítése kilépéskor';
$labels['uisettings'] = 'Felhasználói felület';
$labels['serversettings'] = 'Szerverbeállítások';
$labels['mailboxview'] = 'Postafiók nézet';
$labels['mdnrequests'] = 'Olvasási visszaigazolás küldése';
$labels['askuser'] = 'kérdezzen rá';
$labels['autosend'] = 'automatikus küldés';
$labels['autosendknown'] = 'visszaigazolás küldése csak a kapcsolataimnak, egyéb esetben kérdezzen rá';
$labels['autosendknownignore'] = 'visszaigazolás küldése csak a kapcsolataimnak, egyéb esetben hagyja figyelmen kívül';
$labels['ignore'] = 'hagyja figyelmen kívül';
$labels['readwhendeleted'] = 'Üzenet olvasottként jelölése törléskor';
$labels['flagfordeletion'] = 'Üzenet törlendőnek jelölése törlés helyett';
$labels['skipdeleted'] = 'Törölt üzenetek ne legyenek megjelenítve';
$labels['deletealways'] = 'Üzenet törlése, ha nem sikerül áthelyezni a törölt elemek mappába';
$labels['deletejunk'] = 'Üzenetek közvetlen törlése a levélszemét mappából';
$labels['showremoteimages'] = 'Távoli beágyazott képek megjelenítése';
$labels['fromknownsenders'] = 'csak ismert feladóktól';
$labels['always'] = 'mindig';
$labels['showinlineimages'] = 'Csatolt képek megjelenítése az üzenet alatt';
$labels['autosavedraft']  = 'Piszkozat automatikus mentése';
$labels['everynminutes']  = 'minden $n percben';
$labels['refreshinterval']  = 'Frissítés ( új üzenetek keresése, stb )';
$labels['never']  = 'soha';
$labels['immediately']  = 'azonnal';
$labels['messagesdisplaying'] = 'Üzenetek megjelenítése';
$labels['messagescomposition'] = 'Üzenetek írása';
$labels['mimeparamfolding'] = 'Csatolt állományok nevei';
$labels['2231folding'] = 'Teljes RFC 2231 ( pl. Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (egyéb)';
$labels['force7bit'] = 'MIME kódolás használata 8 bites karakterekhez';
$labels['advancedoptions'] = 'Haladó beállítások';
$labels['focusonnewmessage'] = 'Beérkező új üzenet ablakának fókuszba hozása';
$labels['checkallfolders'] = 'Új üzenetek ellenőrzése minden mappában';
$labels['displaynext'] = 'Levél törlése/mozgatása után jelenjen meg a következő üzenet';
$labels['defaultfont'] = 'HTML üzenet alap karakterkészlete';
$labels['mainoptions'] = 'Általános beállítások';
$labels['browseroptions'] = 'Böngésző beállításai';
$labels['section'] = 'Beállítások';
$labels['maintenance'] = 'Karbantartás';
$labels['newmessage'] = 'Új üzenet';
$labels['signatureoptions'] = 'Aláírás beállításai';
$labels['whenreplying'] = 'Válasznál';
$labels['replyempty'] = 'ne idézze az eredeti üzenetet';
$labels['replytopposting'] = 'üzenet írása az eredeti felett';
$labels['replybottomposting'] = 'üzenet írása az eredeti alatt';
$labels['replyremovesignature'] = 'Válasznál az eredeti aláírás eltávolítása';
$labels['autoaddsignature'] = 'Aláírás automatikus hozzáadása';
$labels['newmessageonly'] = 'csak új üzenetnél';
$labels['replyandforwardonly'] = 'válasznál és továbbításnál';
$labels['insertsignature'] = 'Aláírás beillesztése';
$labels['previewpanemarkread']  = 'Előnézetben megjelent üzenetek megjelölése olvasottként';
$labels['afternseconds']  = '$n másodperc elteltével';
$labels['reqmdn'] = 'Olvasási visszaigazolás megkövetelése';
$labels['reqdsn'] = 'Kézbesítési visszaigazolás megkövetelése';
$labels['replysamefolder'] = 'Válaszok azonos mappába helyezése';
$labels['defaultabook'] = 'Alapértelmezett címjegyzék';
$labels['autocompletesingle'] = 'Alternatív e-mail címek kihagyása automatikus kiegészítéskor';
$labels['listnamedisplay'] = 'Névjegyek listázása';
$labels['spellcheckbeforesend'] = 'Helyesírás-ellenőrzés az üzenet elküldése előtt';
$labels['spellcheckoptions'] = 'Helyesírás-ellenőrzés opciók';
$labels['spellcheckignoresyms'] = 'Speciális karaktereket tartalmazó szavak kihagyása';
$labels['spellcheckignorenums'] = 'Számot tartalmazó szavak kihagyása';
$labels['spellcheckignorecaps'] = 'Csak nagybetűt tartalmazó szavak kihagyása';
$labels['addtodict'] = 'Hozzáadás a szótárhoz';
$labels['mailtoprotohandler'] = 'Beállítás a mailto: linkek kezelőjeként';
$labels['forwardmode'] = 'Üzenet továbbítás';
$labels['inline'] = 'beágyazott';
$labels['asattachment'] = 'csatolmányként';

$labels['folder']  = 'Mappa';
$labels['folders']  = 'Mappák';
$labels['foldername']  = 'Mappa neve';
$labels['subscribed']  = 'Feliratkozva';
$labels['messagecount'] = 'Üzenet';
$labels['create']  = 'Létrehozás';
$labels['createfolder']  = 'Új mappa létrehozása';
$labels['managefolders']  = 'Mappák kezelése';
$labels['specialfolders'] = 'Különleges mappák';
$labels['properties'] = 'Tulajdonságok';
$labels['folderproperties'] = 'Mappa tulajdonságai';
$labels['parentfolder'] = 'Szülőmappa';
$labels['location'] = 'Hely';
$labels['info'] = 'Információk';
$labels['getfoldersize'] = 'Mappa mérete';
$labels['changesubscription'] = 'Feliratkozás megváltoztatása';
$labels['foldertype'] = 'Mappa típusa';
$labels['personalfolder']  = 'Privát mappa';
$labels['otherfolder']  = 'Más felhasználó(k) mappája';
$labels['sharedfolder']  = 'Megosztott mappa';

$labels['sortby'] = 'Rendezés';
$labels['sortasc']  = 'növekvő';
$labels['sortdesc'] = 'csökkenő';
$labels['undo'] = 'Visszavonás';

$labels['installedplugins'] = 'Telepített bővítmények';
$labels['plugin'] = 'Bővítmény';
$labels['version'] = 'Verzió';
$labels['source'] = 'Forrás';
$labels['license'] = 'Licensz';
$labels['support'] = 'Támogatás';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Angol';
$labels['westerneuropean'] = 'Nyugat-európai';
$labels['easterneuropean'] = 'Kelet-európai';
$labels['southeasterneuropean'] = 'Délkelet-európai';
$labels['baltic'] = 'Balti';
$labels['cyrillic'] = 'Cirill';
$labels['arabic'] = 'Arab';
$labels['greek'] = 'Görög';
$labels['hebrew'] = 'Héber';
$labels['turkish'] = 'Török';
$labels['nordic'] = 'Skandináv';
$labels['thai'] = 'Thai';
$labels['celtic'] = 'Kelta';
$labels['vietnamese'] = 'Vietnámi';
$labels['japanese'] = 'Japán';
$labels['korean'] = 'Koreai';
$labels['chinese'] = 'Kínai';

?>
