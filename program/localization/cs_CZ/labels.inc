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
$labels['welcome']   = 'Vítejte v $product';
$labels['username']  = 'Uživatel';
$labels['password']  = 'Heslo';
$labels['server']    = 'Server';
$labels['login']     = 'Přihlásit';

// taskbar
$labels['logout']   = 'Odhlásit';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Nastavení';
$labels['addressbook'] = 'Adresář';

// mailbox names
$labels['inbox']  = 'Příchozí pošta';
$labels['drafts'] = 'Rozepsané';
$labels['sent']   = 'Odeslané';
$labels['trash']  = 'Koš';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Předmět';
$labels['from']    = 'Odesílatel';
$labels['sender']  = 'Odesílatel';
$labels['to']      = 'Adresát';
$labels['cc']      = 'Kopie';
$labels['bcc']     = 'Skrytá kopie';
$labels['replyto'] = 'Odpověď na';
$labels['followupto'] = 'Následuje-po';
$labels['date']    = 'Datum';
$labels['size']    = 'Velikost';
$labels['priority'] = 'Priorita';
$labels['organization'] = 'Organizace';
$labels['readstatus'] = 'Přečteno';
$labels['listoptions'] = 'Seznam  možností ...';

$labels['mailboxlist'] = 'Složky';
$labels['messagesfromto'] = 'Zprávy $from až $to z celkem $count';
$labels['threadsfromto'] = 'Konverzace $from až $to z celkem $count';
$labels['messagenrof'] = 'Zpráva $nr z $count';
$labels['fromtoshort'] = '$from - $to z $count';

$labels['copy']     = 'Kopírovat';
$labels['move']     = 'Přesunout';
$labels['moveto']   = 'přesunout do...';
$labels['download'] = 'stáhnout';
$labels['open']     = 'Otevřít';
$labels['showattachment'] = 'Zobrazit';
$labels['showanyway'] = 'Přesto zobrazit';

$labels['filename'] = 'Jméno přílohy';
$labels['filesize'] = 'Velikost přílohy';

$labels['addtoaddressbook'] = 'Přidat do adresáře';

// weekdays short
$labels['sun'] = 'Ne';
$labels['mon'] = 'Po';
$labels['tue'] = 'Út';
$labels['wed'] = 'St';
$labels['thu'] = 'Čt';
$labels['fri'] = 'Pá';
$labels['sat'] = 'So';

// weekdays long
$labels['sunday']    = 'Neděle';
$labels['monday']    = 'Pondělí';
$labels['tuesday']   = 'Úterý';
$labels['wednesday'] = 'Středa';
$labels['thursday']  = 'Čtvrtek';
$labels['friday']    = 'Pátek';
$labels['saturday']  = 'Sobota';

// months short
$labels['jan']	= 'Led';
$labels['feb']	= 'Úno';
$labels['mar']	= 'Bře';
$labels['apr']	= 'Dub';
$labels['may']	= 'Květen';
$labels['jun']	= 'Čer';
$labels['jul'] 	= 'Čec';
$labels['aug']	= 'Srp';
$labels['sep']	= 'Zář';
$labels['oct']	= 'Říj';
$labels['nov']	= 'Lis';
$labels['dec']	= 'Pro';

// months long
$labels['longjan']	= 'Leden';
$labels['longfeb']	= 'Únor';
$labels['longmar']	= 'Březen';
$labels['longapr']	= 'Duben';
$labels['longmay']	= 'Květen';
$labels['longjun']	= 'Červen';
$labels['longjul']	= 'Červenec';
$labels['longaug']	= 'Srpen';
$labels['longsep']	= 'Září';
$labels['longoct']	= 'Říjen';
$labels['longnov']	= 'Listopad';
$labels['longdec']	= 'Prosinec';

$labels['today'] = 'Dnes';

// toolbar buttons
$labels['refresh']          = 'Obnovit';
$labels['checkmail']        = 'Zkontrolovat nové zprávy';
$labels['compose']          = 'Napsat zprávu';
$labels['writenewmessage']  = 'Vytvořit novou zprávu';
$labels['reply']            = 'Odpovědět';
$labels['replytomessage']   = 'Odpovědět odesílateli';
$labels['replytoallmessage'] = 'Odpovědět všem';
$labels['replyall']         = 'Odpovědět všem';
$labels['replylist']        = 'Odpovědět do e-mailové konference';
$labels['forward']          = 'Přeposlat';
$labels['forwardinline']    = 'Přeposlat v textu';
$labels['forwardattachment'] = 'Přeposlat jako přílohu';
$labels['forwardmessage']   = 'Předat zprávu';
$labels['deletemessage']    = 'Odstranit zprávu';
$labels['movemessagetotrash'] = 'Přesunout zprávu do koše';
$labels['printmessage']     = 'Vytisknout zprávu';
$labels['previousmessage']  = 'Zobrazit předchozí zprávu';
$labels['firstmessage']     = 'Zobrazit první zprávu';
$labels['nextmessage']      = 'Zobrazit další zprávu';
$labels['lastmessage']      = 'Zobrazit poslední zprávu';
$labels['backtolist']       = 'Zpět do seznamu zpráv';
$labels['viewsource']       = 'Zobrazit zdroj';
$labels['mark']             = 'Označit';
$labels['markmessages']     = 'Označ zprávy';
$labels['markread']         = 'Jako přečtené';
$labels['markunread']       = 'Jako nepřečtené';
$labels['markflagged']      = 'Jako označené';
$labels['markunflagged']    = 'Jako neoznačené';
$labels['moreactions']      = 'Další akce...';
$labels['more']             = 'Více';
$labels['back']             = 'Zpět';
$labels['options']          = 'Možnosti';

$labels['select'] = 'Vybrat';
$labels['all'] = 'Vše';
$labels['none'] = 'Nic';
$labels['currpage'] = 'Aktuální stránka';
$labels['unread'] = 'Nepřečtené';
$labels['flagged'] = 'Označené';
$labels['unanswered'] = 'Neoznačené';
$labels['withattachment'] = 'S přílohou';
$labels['deleted'] = 'Smazané';
$labels['undeleted'] = 'Nesmazáno';
$labels['invert'] = 'Převrátit';
$labels['filter'] = 'Filtr';
$labels['list'] = 'Seznam';
$labels['threads'] = 'Konverzace';
$labels['expand-all'] = 'Rozbalit všechny';
$labels['expand-unread'] = 'Rozbalit nepřečtené';
$labels['collapse-all'] = 'Sbalit všechny';
$labels['threaded'] = 'Spojovat do konverzací';

$labels['autoexpand_threads'] = 'Rozbalit konverzace';
$labels['do_expand'] = 'všechny konverzace';
$labels['expand_only_unread'] = 'pouze s nepřečtenými zprávami';
$labels['fromto'] = 'Odesílatel/Příjemce';
$labels['flag'] = 'Vlajka';
$labels['attachment'] = 'Příloha';
$labels['nonesort'] = 'Nic';
$labels['sentdate'] = 'Datum odeslání';
$labels['arrival'] = 'Datum přijetí';
$labels['asc'] = 'vzestupně';
$labels['desc'] = 'sestupně';
$labels['listcolumns'] = 'Seznam sloupců';
$labels['listsorting'] = 'Třídění sloupců';
$labels['listorder'] = 'Uspořádání';
$labels['listmode'] = 'Režim zobrazení seznamu';

$labels['folderactions'] = 'Akce se složkou...';
$labels['compact'] = 'Zmenšit';
$labels['empty'] = 'Vymazat';

$labels['quota'] = 'Využití schránky';
$labels['unknown']  = 'neznámý';
$labels['unlimited']  = 'neomezený';

$labels['quicksearch']  = 'Rychlé vyhledávání';
$labels['resetsearch']  = 'Zrušit vyhledávání';
$labels['searchmod']  = 'Parametry hledání';
$labels['msgtext']  = 'Celá zpráva';
$labels['body']  = 'Tělo';

$labels['openinextwin'] = 'Otevřít v novém okně';
$labels['emlsave'] = 'Stáhnout (.eml)';
$labels['changeformattext'] = 'Zobrazit v textovém formátu';
$labels['changeformathtml'] = 'Zobrazit v HTML formátu';

// message compose
$labels['editasnew']      = 'Upravit jako novou';
$labels['send']           = 'Odeslat';
$labels['sendmessage']    = 'Odeslat zprávu nyní';
$labels['savemessage']    = 'Uložit do rozepsaných';
$labels['addattachment']  = 'Přidat přílohu';
$labels['charset']        = 'Znaková sada';
$labels['editortype']     = 'Typ editoru zpráv';
$labels['returnreceipt']  = 'Doručenka';
$labels['dsn']            = 'Stav doručení';
$labels['mailreplyintro'] = 'Dne $date, $sender napsal:';
$labels['originalmessage'] = 'Původní zpráva';

$labels['editidents']    = 'Editovat identity';
$labels['spellcheck']    = 'Pravopis';
$labels['checkspelling'] = 'Zkontrolovat pravopis';
$labels['resumeediting'] = 'Pokračovat v úpravách';
$labels['revertto']      = 'Přejít na';

$labels['attach'] = 'Přiložit';
$labels['attachments'] = 'Přílohy';
$labels['upload'] = 'Nahrát';
$labels['uploadprogress'] = '$percent ($current z $total)';
$labels['close']  = 'Zavřít';
$labels['messageoptions']  = 'Nastavení zprávy...';

$labels['low']     = 'Nízká';
$labels['lowest']  = 'Nejnižší';
$labels['normal']  = 'Normální';
$labels['high']    = 'Vysoká';
$labels['highest'] = 'Nejvyšší';

$labels['nosubject']  = '(bez predmetu)';
$labels['showimages'] = 'Zobrazit obrázky';
$labels['alwaysshow'] = 'Vždy ukazovat obrázky od $sender';
$labels['isdraft']    = 'Toto je šablona zprávy.';
$labels['andnmore']   = '$nr dalších...';
$labels['togglemoreheaders'] = 'Zobrazit více hlaviček zprávy';
$labels['togglefullheaders'] = 'Přepnout skutečné hlavičky zprávy';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Prostý text';
$labels['savesentmessagein'] = 'Ukládat odeslané zprávy v';
$labels['dontsave'] = 'neukládat';
$labels['maxuploadsize'] = 'Maximální povolená velikost souboru je $size';

$labels['addcc'] = 'Přidat pole "Kopie';
$labels['addbcc'] = 'Přidat pole "Skrytá kopie';
$labels['addreplyto'] = 'Přidat pole "Odpověď';
$labels['addfollowupto'] = 'Přidat pole "Followup-To';

// mdn
$labels['mdnrequest'] = 'Odesílatel této zprávy si přeje být upozorněn na to, že jste zprávu obdrželi. Chcete přijetí zprávy potvrdit?';
$labels['receiptread'] = 'Potvrzení o přijetí zprávy';
$labels['yourmessage'] = 'Toto je potvrzení o přijetí Vaší zprávy';
$labels['receiptnote'] = 'Poznámka: Toto potvrzení negarantuje, že zpráva byla příjemce přečtena a že porozuměl jejímu obsahu.';

// address boook
$labels['name']         = 'Zobrazit jméno';
$labels['firstname']    = 'Jméno';
$labels['surname']      = 'Příjmení';
$labels['middlename']   = 'Rodné příjmení';
$labels['nameprefix']   = 'Titul před';
$labels['namesuffix']   = 'Titul za';
$labels['nickname']     = 'Přezdívka';
$labels['jobtitle']     = 'Pracovní pozice';
$labels['department']   = 'Oddělení';
$labels['gender']       = 'Pohlaví';
$labels['maidenname']   = 'Rodné příjmení';
$labels['email']        = 'E-mail';
$labels['phone']        = 'Telefon';
$labels['address']      = 'Adresa';
$labels['street']       = 'Ulice';
$labels['locality']     = 'Město';
$labels['zipcode']      = 'PSČ';
$labels['region']       = 'Kraj';
$labels['country']      = 'Země';
$labels['birthday']     = 'Datum narození';
$labels['anniversary']  = 'Výročí';
$labels['website']      = 'WWW';
$labels['instantmessenger'] = 'Komunikace (ICQ, messenger...)';
$labels['notes'] = 'Poznámky';
$labels['male']   = 'Muž';
$labels['female'] = 'Žena';
$labels['manager'] = 'Manažer';
$labels['assistant'] = 'Asistent';
$labels['spouse'] = 'Manžel/ka';
$labels['allfields'] = 'Všechny položky';
$labels['search'] = 'Hledat';
$labels['advsearch'] = 'Pokročilé vyhledávání';
$labels['advanced'] = 'Pokročilé';
$labels['other'] = 'Ostatní';

$labels['typehome']   = 'Domací';
$labels['typework']   = 'Pracovní';
$labels['typeother']  = 'Ostatní';
$labels['typemobile']  = 'Mobil';
$labels['typemain']  = 'Osobní';
$labels['typehomefax']  = 'Domácí FAX';
$labels['typeworkfax']  = 'Pracovní FAX';
$labels['typecar']  = 'Automobil';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Asistent';
$labels['typehomepage']  = 'Domácí stránka';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Přidat položku';
$labels['addcontact'] = 'Přidat kontakt';
$labels['editcontact'] = 'Upravit kontakt';
$labels['contacts'] = 'Kontakty';
$labels['contactproperties'] = 'Vlastnosti kontaktu';
$labels['personalinfo'] = 'Osobní informace';

$labels['edit']   = 'Upravit';
$labels['cancel'] = 'Konec';
$labels['save']   = 'Uložit';
$labels['delete'] = 'Smazat';
$labels['rename'] = 'Přejmenovat';
$labels['addphoto'] = 'Přidat';
$labels['replacephoto'] = 'Změnit';
$labels['uploadphoto'] = 'Nahrát fotku';

$labels['newcontact']     = 'Vytvořit nový kontakt';
$labels['deletecontact']  = 'Smazat vybrané kontakty';
$labels['composeto']      = 'Poslat e-mail';
$labels['contactsfromto'] = 'Kontakty $from až $to z celkem $count';
$labels['print']          = 'Tisk';
$labels['export']         = 'Export';
$labels['exportall']      = 'Export všech';
$labels['exportsel']      = 'Export vybraných';
$labels['exportvcards']   = 'Exportovat kontakty ve formátu vCard';
$labels['newcontactgroup'] = 'Vytvořit novou skupinu kontaktů';
$labels['grouprename']    = 'Přejmenovat skupinu';
$labels['groupdelete']    = 'Smazat skupinu';
$labels['groupremoveselected'] = 'Odstranit označené kontakty ze skupiny';

$labels['previouspage']   = 'Předchozí';
$labels['firstpage']      = 'Zobrazit první zprávy';
$labels['nextpage']       = 'Další';
$labels['lastpage']       = 'Zobrazit poslední zprávy';

$labels['group'] = 'Skupina';
$labels['groups'] = 'Skupiny';
$labels['personaladrbook'] = 'Osobní kontakty';

$labels['searchsave'] = 'Uložit hledání';
$labels['searchdelete'] = 'Smazat hledání';

$labels['import'] = 'Import';
$labels['importcontacts'] = 'Importovat kontakty';
$labels['importfromfile'] = 'Importovat ze souboru';
$labels['importtarget'] = 'Přidat nové kontakty do seznamu kontaktů:';
$labels['importreplace'] = 'Nahradit celý seznam kontaktů';
$labels['importdesc'] = 'Můžete nahrát kontakty z existujícího adresáře kontaktů.<br/>Momentálně podporujeme kontakty ve formátu <a href="http://cs.wikipedia.org/wiki/VCard">vCard</a> nebo CSV (comma-separated).';
$labels['done'] = 'Hotovo';

// settings
$labels['settingsfor'] = 'Nastavení pro';
$labels['about'] = 'O programu';
$labels['preferences'] = 'Vlastnosti';
$labels['userpreferences'] = 'Vlastnosti uživatele';
$labels['editpreferences'] = 'Upravit vlastnosti uživatele';

$labels['identities'] = 'Profily';
$labels['manageidentities'] = 'Spravovat profily u tohoto účtu';
$labels['newidentity'] = 'Nový profil';

$labels['newitem'] = 'Nová položka';
$labels['edititem'] = 'Upravit položku';

$labels['preferhtml'] = 'Upřednostňovat HTML zobrazení';
$labels['defaultcharset'] = 'Výchozí kódování';
$labels['htmlmessage'] = 'HTML zpráva';
$labels['messagepart'] = 'Část';
$labels['digitalsig'] = 'Digitální podpis';
$labels['dateformat'] = 'Formát data';
$labels['timeformat'] = 'Formát času';
$labels['prettydate'] = 'Hezčí datum';
$labels['setdefault']  = 'Nastavit výchozí';
$labels['autodetect']  = 'Automaticky';
$labels['language']  = 'Jazyk';
$labels['timezone']  = 'Časová zóna';
$labels['pagesize']  = 'Řádků na stránku';
$labels['signature'] = 'Podpis';
$labels['dstactive']  = 'Letní čas';
$labels['showinextwin'] = 'Otevřít zprávu v novém okně';
$labels['composeextwin'] = 'Vytvořit v novém okně';
$labels['htmleditor'] = 'Vytvářet HTML zprávy';
$labels['htmlonreply'] = 'jen při odpovědi na HTML zprávy';
$labels['htmlonreplyandforward'] = 'při přeposlání nebo odpovědi na HTML zprávu';
$labels['htmlsignature'] = 'HTML podpis';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Zobrazit panel náhledu';
$labels['skin'] = 'Vzhled';
$labels['logoutclear'] = 'Vyprázdnit koš při odhlášení';
$labels['logoutcompact'] = 'Provést údržbu schránky při odhlášení';
$labels['uisettings'] = 'Uživatelské rozhraní';
$labels['serversettings'] = 'Nastavení serveru';
$labels['mailboxview'] = 'Zobrazení schránky';
$labels['mdnrequests'] = 'Oznámení o doručení';
$labels['askuser'] = 'zeptat se';
$labels['autosend'] = 'poslat potvrzení automaticky';
$labels['autosendknown'] = 'poslat potvrzení pouze mým kontaktům, jinak se zeptat';
$labels['autosendknownignore'] = 'poslat potvrzení pouze mým kontaktům, jinak ignorovat';
$labels['ignore'] = 'ignorovat';
$labels['readwhendeleted'] = 'Při odstranění označit zprávu jako přečtenou';
$labels['flagfordeletion'] = 'Zprávu nemazat, pouze označit pro odstranění';
$labels['skipdeleted'] = 'Nezobrazovat smazané zprávy';
$labels['deletealways'] = 'Odstranit zprávy, pokud selže jejich přesun do koše';
$labels['deletejunk'] = 'Přímo mazat zprávy ze složky Nevyžádaná pošta';
$labels['showremoteimages'] = 'Zobrazovat obrázky uložené mimo mail';
$labels['fromknownsenders'] = 'od známých uživatelů';
$labels['always'] = 'vždy';
$labels['showinlineimages'] = 'Zobrazovat připojené obrázky pod textem';
$labels['autosavedraft']  = 'Automaticky uložit rozepsané zprávy';
$labels['everynminutes']  = 'každých $n minut';
$labels['refreshinterval']  = 'Obnovit (zkontroluj nové zprávy, atd)';
$labels['never']  = 'nikdy';
$labels['immediately']  = 'ihned';
$labels['messagesdisplaying'] = 'Zobrazování zpráv';
$labels['messagescomposition'] = 'Psaní zpráv';
$labels['mimeparamfolding'] = 'Jména příloh';
$labels['2231folding'] = 'Podle RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Podle RFC 2047 (ostatní)';
$labels['force7bit'] = 'Použít kódování MIME pro 8-bitové znaky';
$labels['advancedoptions'] = 'Pokročilá nastavení';
$labels['focusonnewmessage'] = 'Aktivovat okno prohlížeče při příchozí zprávě';
$labels['checkallfolders'] = 'Kontrolovat nové zprávy ve všech složkách';
$labels['displaynext'] = 'Zobrazit další zprávu po smazání/přesunu zprávy';
$labels['defaultfont'] = 'Výchozí písmo zprávy HTML';
$labels['mainoptions'] = 'Hlavní nastavení';
$labels['browseroptions'] = 'Možnosti prohlížeče';
$labels['section'] = 'Sekce';
$labels['maintenance'] = 'Údržba';
$labels['newmessage'] = 'Nová zpráva';
$labels['signatureoptions'] = 'Nastavení podpisu';
$labels['whenreplying'] = 'Při odpovídání';
$labels['replyempty'] = 'necitovat původní zprávu';
$labels['replytopposting'] = 'začít novou zprávu nad původní';
$labels['replybottomposting'] = 'začít novou zprávu pod původní';
$labels['replyremovesignature'] = 'Při odpovídání odstranit ze zprávy původní podpis';
$labels['autoaddsignature'] = 'Automaticky přidat podpis';
$labels['newmessageonly'] = 'pouze k novým zprávám';
$labels['replyandforwardonly'] = 'jen k odpovědi a přeposílané zprávě';
$labels['insertsignature'] = 'Vložit podpis';
$labels['previewpanemarkread']  = 'Označit zobrazené zprávy jako přečtené';
$labels['afternseconds']  = 'po $n sekundách';
$labels['reqmdn'] = 'Vždy požadovat doručenku';
$labels['reqdsn'] = 'Vždy požádat o oznámení o stavu doručení';
$labels['replysamefolder'] = 'Ukládat odpovědi ve stejné složce jako je zodpovězená zpráva';
$labels['defaultabook'] = 'Výchozí adresář kontaktů';
$labels['autocompletesingle'] = 'Při doplňování adres přeskočit alternativní email.';
$labels['listnamedisplay'] = 'Vypsat kontakty jako';
$labels['spellcheckbeforesend'] = 'Před odesláním zkontrolovat pravopis';
$labels['spellcheckoptions'] = 'Nastavení pravopisu';
$labels['spellcheckignoresyms'] = 'Ignorovat slova obsahující symboly';
$labels['spellcheckignorenums'] = 'Ignorovat slova obsahující čísla';
$labels['spellcheckignorecaps'] = 'Ignorovat slova psaná velkými písmeny';
$labels['addtodict'] = 'Přidat do slovníku';
$labels['mailtoprotohandler'] = 'Zaregistrovat handler pro odkazy „mailto:“';
$labels['forwardmode'] = 'Přeposlat zprávu';
$labels['inline'] = 'vloženě';
$labels['asattachment'] = 'jako přílohu';

$labels['folder']  = 'Složka';
$labels['folders']  = 'Složky';
$labels['foldername']  = 'Jméno složky';
$labels['subscribed']  = 'Vybráno';
$labels['messagecount'] = 'Počet zpráv';
$labels['create']  = 'Vytvořit';
$labels['createfolder']  = 'Vytvořit novou složku';
$labels['managefolders']  = 'Spravovat složky';
$labels['specialfolders'] = 'Speciální složky';
$labels['properties'] = 'Vlastnosti';
$labels['folderproperties'] = 'Vlastnosti složky';
$labels['parentfolder'] = 'Nadřazená složka';
$labels['location'] = 'Umístění';
$labels['info'] = 'Informace';
$labels['getfoldersize'] = 'Kliknutím zjistíte velikost složky';
$labels['changesubscription'] = 'Kliknutím změníte přednastavení';
$labels['foldertype'] = 'Typ složky';
$labels['personalfolder']  = 'Soukromá složka';
$labels['otherfolder']  = 'Složky ostatních uživatelů';
$labels['sharedfolder']  = 'Sdílená složka';

$labels['sortby'] = 'Seřadit podle';
$labels['sortasc']  = 'Seřadit vzestupně';
$labels['sortdesc'] = 'Seřadit sestupně';
$labels['undo'] = 'Vrátit zpět';

$labels['installedplugins'] = 'Nainstalované pluginy';
$labels['plugin'] = 'Zásuvný modul';
$labels['version'] = 'Verze';
$labels['source'] = 'Zdroj';
$labels['license'] = 'Licence';
$labels['support'] = 'Podpora';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Angličtina';
$labels['westerneuropean'] = 'Západní Evropa';
$labels['easterneuropean'] = 'Východní Evropa';
$labels['southeasterneuropean'] = 'Jiho-východní Evropa';
$labels['baltic'] = 'Baltština';
$labels['cyrillic'] = 'Cyrilice';
$labels['arabic'] = 'Arabština';
$labels['greek'] = 'Řečtina';
$labels['hebrew'] = 'Hebrejština';
$labels['turkish'] = 'Turečtina';
$labels['nordic'] = 'Nordština';
$labels['thai'] = 'Thajština';
$labels['celtic'] = 'Keltština';
$labels['vietnamese'] = 'Vietnamština';
$labels['japanese'] = 'Japonština';
$labels['korean'] = 'Korejština';
$labels['chinese'] = 'Čínština';

?>
