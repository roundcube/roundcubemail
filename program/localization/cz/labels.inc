<?php

/*

 +-----------------------------------------------------------------------+
 | language/cz/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | All rights reserved.                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Martin Mrajca <martin@moonlake.cz>                            |
 |        joe <joe@humlak.cz>                                            |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Uživatel';
$labels['password']  = 'Heslo';
$labels['server']    = 'Server';
$labels['login']     = 'Přihlásit';

// taskbar
$labels['logout']   = 'Odhlásit';
$labels['mail']     = 'E-mail';
$labels['settings'] = 'Osobní nastavení';
$labels['addressbook'] = 'Adresář';

// mailbox names
$labels['inbox']  = 'Příchozí pošta';
$labels['sent']   = 'Odeslané';
$labels['trash']  = 'Koš';
$labels['drafts'] = 'Rozepsané';
$labels['junk']   = 'Nevyžádaná pošta';

// message listing
$labels['subject'] = 'Předmět';
$labels['from']    = 'Odesílatel';
$labels['to']      = 'Adresát';
$labels['cc']      = 'Kopie';
$labels['bcc']     = 'Slepá';
$labels['replyto'] = 'Odpověď na';
$labels['date']    = 'Datum';
$labels['size']    = 'Velikost';
$labels['priority'] = 'Priorita';
$labels['organization'] = 'Organizace';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Složky';
$labels['messagesfromto'] = 'Zprávy $from až $to z celkem $count';
$labels['messagenrof'] = 'Zpráva $nr z $count';

$labels['moveto']   = 'přesunout do...';
$labels['download'] = 'stáhnout';

$labels['filename'] = 'Jméno přílohy';
$labels['filesize'] = 'Velikost přílohy';

$labels['preferhtml'] = 'Upřednostňovat HTML zobrazení';
$labels['htmlmessage'] = 'HTML zpráva';
$labels['prettydate'] = 'Hezčí datum';

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

$labels['today'] = 'Dnes';

// toolbar buttons
$labels['writenewmessage']  = 'Vytvořit novou zprávu';
$labels['replytomessage']   = 'Odpovědět odesílateli';
$labels['replytoallmessage'] = 'Odpovědět všem';
$labels['forwardmessage']   = 'Předat zprávu';
$labels['deletemessage']    = 'Přesunout do koše';
$labels['printmessage']     = 'Vytisknout zprávu';
$labels['previousmessages'] = 'Zobrazit předchozí zprávy';
$labels['nextmessages']     = 'Zobrazit další zprávy';
$labels['backtolist']       = 'Zpět do seznamu zpráv';
$labels['viewsource']       = 'Zobrazit zdroj';

$labels['select'] = 'Vybrat';
$labels['all'] = 'Vše';
$labels['none'] = 'Nic';
$labels['unread'] = 'Nepřečtené';

// message compose
$labels['compose']  = 'Napsat zprávu';
$labels['sendmessage']  = 'Odeslat zprávu nyní';
$labels['addattachment']  = 'Přidat přílohu';

$labels['attachments'] = 'Přílohy';
$labels['upload'] = 'Nahrát';
$labels['close']  = 'Zavřít';

$labels['low']     = 'Nízká';
$labels['lowest']  = 'Nejnižší';
$labels['normal']  = 'Normální';
$labels['high']    = 'Vysoká';
$labels['highest'] = 'Nejvyšší';

$labels['showimages'] = 'Zobrazit obrázky';

// address boook
$labels['name']      = 'Zobrazit jméno';
$labels['firstname'] = 'Jméno';
$labels['surname']   = 'Příjmení';
$labels['email']     = 'E-mail';

$labels['addcontact'] = 'Přidat kontakt';
$labels['editcontact'] = 'Upravit kontakt';

$labels['edit']   = 'Upravit';
$labels['cancel'] = 'Konec';
$labels['save']   = 'Uložit';
$labels['delete'] = 'Smazat';

$labels['newcontact']     = 'Vytvořit nový kontakt';
$labels['deletecontact']  = 'Smazat vybrané kontakty';
$labels['composeto']      = 'Poslat e-mail';
$labels['contactsfromto'] = 'Kontakty $from až $to z celkem $count';
$labels['print']          = 'Tisk';
$labels['export']         = 'Export';

// settings
$labels['settingsfor']  = 'Nastavení pro';

$labels['preferences']  = 'Vlastnosti';
$labels['userpreferences']  = 'Vlastnosti uživatele';
$labels['editpreferences']  = 'Upravit vlastnosti uživatele';

$labels['identities']  = 'Profily';
$labels['manageidentities']  = 'Spravovat profily u tohoto účtu';
$labels['newidentity']  = 'Nový profil';

$labels['newitem']  = 'Nová položka';
$labels['edititem']  = 'Upravit položku';

$labels['setdefault']  = 'Nastavit výchozí';
$labels['language']  = 'Jazyk';
$labels['timezone']  = 'Časová zóna';
$labels['pagesize']  = 'Řádků na stránku';

$labels['folders']  = 'Složky';
$labels['folder']  = 'Složka';
$labels['foldername']  = 'Jméno složky';
$labels['subscribed']  = 'Vybráno';
$labels['create']  = 'Vytvořit';
$labels['createfolder']  = 'Vytvořit novou složku';
$labels['deletefolder']  = 'Smazat složku';
$labels['managefolders']  = 'Spravovat složky';

$labels['compact'] = 'Kompaktní';
$labels['empty'] = 'Prázdný';
$labels['purge'] = 'Vyprázdnit';
$labels['quota'] = 'Využití schránky';

$labels['sortby']   = 'Seřadit podle';
$labels['sortdesc'] = 'Seřadit sestupně';
$labels['sortasc']  = 'Seřadit vzestupně';

$labels['nosubject']  = '(bez predmetu)';
$labels['signature'] = 'Podpis';
$labels['charset']  = 'Znaková sada';

$labels['ldapsearch'] = 'Hledat v LDAP adresáři';
$labels['ldappublicsearchname']    = 'Jméno kontaktu';
$labels['ldappublicsearchtype'] = 'Doslovné znění?';
$labels['ldappublicserverselect'] = 'Zvolit servery';
$labels['ldappublicsearchfield'] = 'Hledat na';
$labels['ldappublicsearchform'] = 'Hledat kontakt';
$labels['ldappublicsearch'] = 'Hledat';

$labels['welcome'] = 'Vítejte v $product';

$labels['unknown'] = 'neznámý';
$labels['unlimited'] = 'neomezený';
$labels['dstactive'] = 'Letní čas';

$labels['previouspage'] = 'Předchozí';
$labels['nextpage']     = 'Další';

$labels['returnreceipt'] = 'Doručenka';

$labels['checkmail'] = 'Zkontrolovat nové zprávy';
$labels['checkspelling'] = 'Zkontrolovat pravopis';
$labels['resumeediting'] = 'Pokračovat v úpravách';
$labels['revertto'] = 'Přejít na';

$labels['savemessage']  = 'Uložit do rozepsaných';
$labels['rename'] = 'Přejmenovat';
$labels['renamefolder'] = 'Přejmenovat složku';

$labels['description'] = 'Popis';

$labels['previousmessage']  = 'Zobrazit předchozí zprávu';
$labels['firstmessage']     = 'Zobrazit první zprávu';
$labels['firstmessages']    = 'Zobrazit první zprávy';
$labels['nextmessage']      = 'Zobrazit další zprávu';
$labels['lastmessage']      = 'Zobrazit poslední zprávu';
$labels['lastmessages']     = 'Zobrazit poslední zprávy';
$labels['quicksearch']  = 'Rychlé vyhledávání';
$labels['resetsearch']  = 'Zrušit vyhledávání';
$labels['editortype'] = 'Typ editoru zpráv';
$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Prostý text';
$labels['firstpage']  = 'Zobrazit první zprávy';
$labels['lastpage']   = 'Zobrazit poslední zprávy';
$labels['groups'] = 'Skupiny';
$labels['personaladrbook'] = 'Osobní kontakty';
$labels['htmleditor'] = 'Vytvářet HTML zprávy';
$labels['htmlsignature'] = 'HTML podpis';
$labels['previewpane'] = 'Zobrazit panel náhledu';
$labels['autosavedraft']  = 'Automaticky uložit rozepsané zprávy';
$labels['everynminutes']  = 'každých $n minut';
$labels['never']  = 'nikdy';

?>