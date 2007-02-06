<?php

/*

 +-----------------------------------------------------------------------+
 | language/sk/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Lukas Kraic <lukas.kraic@truni.sk>                            |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome'] = 'Vitajte v $product';
$labels['username']  = 'Prihlasovacie meno';
$labels['password']  = 'Heslo';
$labels['server']    = 'Server';
$labels['login']     = 'Prihlásiť';

// taskbar
$labels['logout']   = 'Odhlásiť';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Osobné nastavenia';
$labels['addressbook'] = 'Adresár';

// mailbox names
$labels['inbox']  = 'Doručená pošta';
$labels['sent']   = 'Odoslané';
$labels['trash']  = 'Kôš';
$labels['drafts'] = 'Nedokončené';
$labels['junk']   = 'Nevyžiadaná pošta';

// message listing
$labels['subject'] = 'Predmet';
$labels['from']    = 'Odosielateľ';
$labels['to']      = 'Adresát';
$labels['cc']      = 'Kópia';
$labels['bcc']     = 'Tajná kópia';
$labels['replyto'] = 'Odpovedať na';
$labels['date']    = 'Dátum';
$labels['size']    = 'Veľkosť';
$labels['priority'] = 'Priorita';
$labels['organization'] = 'Organizácia';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Adresár';
$labels['messagesfromto'] = 'Správy od $from do $to z $count';
$labels['messagenrof'] = 'Správa $nr z $count';

$labels['moveto']   = 'Presunúť do...';
$labels['download'] = 'Stiahnuť';

$labels['filename'] = 'Meno súboru';
$labels['filesize'] = 'Veľkosť súboru';

$labels['preferhtml'] = 'Uprednostniť HTML zobrazenie';
$labels['htmlmessage'] = 'HTML správa';
$labels['prettydate'] = 'Krajší dátum';

$labels['addtoaddressbook'] = 'Pridaj do adresára';

// weekdays short
$labels['sun'] = 'Ne';
$labels['mon'] = 'Po';
$labels['tue'] = 'Ut';
$labels['wed'] = 'St';
$labels['thu'] = 'Štv';
$labels['fri'] = 'Pia';
$labels['sat'] = 'So';

// weekdays long
$labels['sunday']    = 'Nedeľa';
$labels['monday']    = 'Pondelok';
$labels['tuesday']   = 'Utorok';
$labels['wednesday'] = 'Streda';
$labels['thursday']  = 'Štvrtok';
$labels['friday']    = 'Piatok';
$labels['saturday']  = 'Sobota';

$labels['today'] = 'Dnes';

// toolbar buttons
$labels['checkmail'] = 'Skontrolovať nové správy';
$labels['writenewmessage']  = 'Vytvor novú správu';
$labels['replytomessage']   = 'Odpovedať';
$labels['replytoallmessage'] = 'Odpovedať všetkým';
$labels['forwardmessage']   = 'Poslať ďalej';
$labels['deletemessage']    = 'Presuň správu do koša';
$labels['printmessage']     = 'Vytlač správu';
$labels['previousmessages'] = 'Zobraziť predchádzajúcu správu';
$labels['nextmessages']     = 'Zobraziť ďalšiu správu';
$labels['backtolist']       = 'Späť na zoznam správ';
$labels['viewsource']       = 'Ukáž zdroj správy';

$labels['select'] = 'Výber';
$labels['all'] = 'Všetky';
$labels['none'] = 'Žiadnu';
$labels['unread'] = 'Neprečítané';
$labels['compact'] = 'Kompaktné';
$labels['empty'] = 'Prázdne';
$labels['purge'] = 'Vyprázdniť';

$labels['quota'] = 'Využitie schránky';
$labels['unknown']  = 'neznáme';
$labels['unlimited']  = 'neobmedzené';

$labels['quicksearch']  = 'Rýchle vyhľadávanie';
$labels['resetsearch']  = 'Vyčisti vyhľadávanie';

// message compose
$labels['compose']  = 'Vytvoriť správu';
$labels['savemessage']  = 'Uložiť do rozpísaných';
$labels['sendmessage']  = 'Odoslať správu';
$labels['addattachment']  = 'Pridať prílohu';
$labels['charset']  = 'Znaková sada';
$labels['returnreceipt'] = 'Doručenka';

$labels['checkspelling'] = 'Skontroluj pravopis';
$labels['resumeediting'] = 'Pokračovať v úpravách';
$labels['revertto'] = 'Odpovedať na';

$labels['attachments'] = 'Prílohy';
$labels['upload'] = 'Nahrať';
$labels['close']  = 'Zatvor';

$labels['low']     = 'Nízka';
$labels['lowest']  = 'Najnižšia';
$labels['normal']  = 'Normálna';
$labels['high']    = 'Vysoká';
$labels['highest'] = 'Najvyššia';

$labels['nosubject']  = '(bez predmetu)';
$labels['showimages'] = 'Ukáž obrázky';

// address boook
$labels['name']      = 'Celé meno';
$labels['firstname'] = 'Meno';
$labels['surname']   = 'Priezvisko';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Pridaj nový kontakt';
$labels['editcontact'] = 'Uprav kontakt';

$labels['edit']   = 'Uprav';
$labels['cancel'] = 'Zruš';
$labels['save']   = 'Ulož';
$labels['delete'] = 'Zmaž';

$labels['newcontact']     = 'Vytvor nový kontakt';
$labels['deletecontact']  = 'Zmaž zvolené kontakty';
$labels['composeto']      = 'Vytvor správu';
$labels['contactsfromto'] = 'Kontakty od $from do $to z $count';
$labels['print']          = 'Tlač';
$labels['export']         = 'Export';

$labels['previouspage']   = 'Predchádzajúca stránka';
$labels['nextpage']       = 'Nasledujúca stránka';

// LDAP search
$labels['ldapsearch'] = 'LDAP adresárové vyhľadávanie';

$labels['ldappublicsearchname'] = 'Hľadaný kontakt';
$labels['ldappublicsearchtype'] = 'Presná zhoda?';
$labels['ldappublicserverselect'] = 'Zvoľ server';
$labels['ldappublicsearchfield'] = 'Hľadaj podľa';
$labels['ldappublicsearchform'] = 'Vyhľadávanie kontaktov';
$labels['ldappublicsearch'] = 'Hľadaj';

// settings
$labels['settingsfor']  = 'Nastavenia pre';

$labels['preferences']  = 'Vlastnosti';
$labels['userpreferences']  = 'Užívateľské vlastnosti';
$labels['editpreferences']  = 'Uprav užívateľské vlastnosti';

$labels['identities']  = 'Profily';
$labels['manageidentities']  = 'Spravovať profily pri tomto účte';
$labels['newidentity']  = 'Nový profil';

$labels['newitem']  = 'Nová položka';
$labels['edititem']  = 'Uprav položku';

$labels['setdefault']  = 'Obnoviť pôvodné';
$labels['language']  = 'Jazyk';
$labels['timezone']  = 'Časová zóna';
$labels['pagesize']  = 'Riadky na stránku';
$labels['signature'] = 'Podpis';
$labels['dstactive']  = 'Letný čas';

$labels['folder']  = 'Zložka';
$labels['folders']  = 'Zložky';
$labels['foldername']  = 'Meno zložky';
$labels['subscribed']  = 'Podpísaný';
$labels['create']  = 'Vytvoriť';
$labels['createfolder']  = 'Vytvor novú zložku';
$labels['rename'] = 'Premenovať';
$labels['renamefolder'] = 'Premenovať zložku';
$labels['deletefolder']  = 'Zmaž zložku';
$labels['managefolders']  = 'Spravovať zložky';

$labels['sortby'] = 'Triediť podľa';
$labels['sortasc']  = 'Triediť vzostupne';
$labels['sortdesc'] = 'Triediť zostupne';

?>