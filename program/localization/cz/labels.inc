<?php

/*
 +-----------------------------------------------------------------------+
 | language/cz/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Martin Mrajca <martin@moonlake.cz>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to Roundcube|Mail';
$labels['username']  = 'Uživatel';
$labels['password']  = 'Heslo';
$labels['server']    = 'Server';
$labels['login']     = 'Přihlásit';

// taskbar
$labels['logout']   = 'Odhlásit';
$labels['mail']     = 'E-Mail';
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
$labels['from']    = 'Odesilatel';
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
$labels['messagesfromto'] = 'Zprávy od $from do $to z celkem $count';
$labels['messagenrof'] = 'Zpráva $nr z $count';

$labels['moveto']   = 'přesunout do...';
$labels['download'] = 'stáhnout';

$labels['filename'] = 'Jméno přílohy';
$labels['filesize'] = 'Velikost přílohy';

$labels['preferhtml'] = 'Upřednostňovat HTML zobrazení';
$labels['htmlmessage'] = 'HTML zpráva';
$labels['prettydate'] = 'Hezčí data';

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
$labels['replytomessage']   = 'Odpovědět odesilateli';
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
$labels['normal']  = 'Normalní';
$labels['high']    = 'Vysoká';
$labels['highest'] = 'Nejvyšší';

$labels['showimages'] = 'Zobrazit obrázky';


// address boook
$labels['name']      = 'Zobrazit jméno';
$labels['firstname'] = 'Jméno';
$labels['surname']   = 'Příjmení';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Přidat kontakt';
$labels['editcontact'] = 'Upravit kontakt';

$labels['edit']   = 'Upravit';
$labels['cancel'] = 'Konec';
$labels['save']   = 'Uložit';
$labels['delete'] = 'Smazat';

$labels['newcontact']     = 'Vytvořit nový kontakt';
$labels['deletecontact']  = 'Smazat vybrané kontakty';
$labels['composeto']      = 'Poslat mail';
$labels['contactsfromto'] = 'Kontakty od $from do $to z celkem $count';
$labels['print']          = 'Tisk';
$labels['export']         = 'Export';


// settings
$labels['settingsfor']  = 'Nastavení pro';

$labels['preferences']  = 'Vlastnosti';
$labels['userpreferences']  = 'Vlastnosti uživatele';
$labels['editpreferences']  = 'Upravit vlastnosti uživatele';

$labels['identities']  = 'Profily';
$labels['manageidentities']  = 'Spravovat profily u tohoto účtu';
$labels['newidentity']  = 'Nová profil';

$labels['newitem']  = 'Nová položka';
$labels['edititem']  = 'Upravit položku';

$labels['setdefault']  = 'Nastavit výchozí';
$labels['language']  = 'Jazyk';
$labels['timezone']  = 'Časová zóna';
$labels['pagesize']  = 'Řádků na stránku';


$labels['folders']  = 'Složky';
$labels['folder']  = 'Složka';
$labels['foldername']  = 'Jméno složky';
$labels['subscribed']  = 'Podepsané';
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

$labels['nosubject']  = '(bez předmětu)';
$labels['signature'] = 'Podpis';
$labels['charset']  = 'Znaková sada';

$labels['ldapsearch'] = 'Hledat v LDAP adresáři';
$labels['ldappublicsearchname']    = 'Jméno kontaktu';
$labels['ldappublicsearchtype'] = 'Doslovné znění?';
$labels['ldappublicserverselect'] = 'Zvolit servery';
$labels['ldappublicsearchfield'] = 'Hledat na';
$labels['ldappublicsearchform'] = 'Hledat kontakt';
$labels['ldappublicsearch'] = 'Hledat';

?>