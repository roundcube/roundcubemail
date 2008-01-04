<?php

/*

 +-----------------------------------------------------------------------+
 | language/sr_cyrillic/labels.inc                                       |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Branko Kokanovic <branko.kokanovic@gmail.com                  |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Добродошли на $product';
$labels['username']  = 'Корисничко име';
$labels['password']  = 'Лозинка';
$labels['server']    = 'Сервер';
$labels['login']     = 'Пријава';

// taskbar
$labels['logout']   = 'Одјава';
$labels['mail']     = 'Сандуче';
$labels['settings'] = 'Подешавања';
$labels['addressbook'] = 'Именик';

// mailbox names
$labels['inbox']  = 'Примљене';
$labels['drafts'] = 'Снимљене';
$labels['sent']   = 'Послате';
$labels['trash']  = 'Канта';
$labels['junk']   = 'Смеће';

// message listing
$labels['subject'] = 'Наслов';
$labels['from']    = 'Од';
$labels['to']      = 'За';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'Датум';
$labels['size']    = 'Величина';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организација';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Фасцикле';
$labels['messagesfromto'] = 'Поруке од $from за $to од укупно $count';
$labels['messagenrof'] = 'Порука број $nr од $count';

$labels['moveto']   = 'пребаци у...';
$labels['download'] = 'преузми (скидање)';

$labels['filename'] = 'Име датотекеe';
$labels['filesize'] = 'Величина датотеке';

$labels['preferhtml'] = 'Предност дај ХТМЛ формату';
$labels['htmlmessage'] = 'ХТМЛ формат поруке';
$labels['prettydate'] = 'Форматирани датум';

$labels['addtoaddressbook'] = 'Додај у именик';

// weekdays short
$labels['sun'] = 'Нед';
$labels['mon'] = 'Пон';
$labels['tue'] = 'Уто';
$labels['wed'] = 'Сре';
$labels['thu'] = 'Чет';
$labels['fri'] = 'Пет';
$labels['sat'] = 'Суб';

// weekdays long
$labels['sunday']    = 'Недеља';
$labels['monday']    = 'Понедељак';
$labels['tuesday']   = 'Уторак';
$labels['wednesday'] = 'Среда';
$labels['thursday']  = 'Четвртак';
$labels['friday']    = 'Петак';
$labels['saturday']  = 'Субота';

$labels['today'] = 'данас';

// toolbar buttons
$labels['checkmail']        = 'Провера нових порука';
$labels['writenewmessage']  = 'Нова порука';
$labels['replytomessage']   = 'Одговори на поруку';
$labels['replytoallmessage'] = 'Одговори пошиљаоцу и свим примаоцима';
$labels['forwardmessage']   = 'Проследи поруку';
$labels['deletemessage']    = 'Пребаци поруку у канту';
$labels['printmessage']     = 'Одштампај поруку';
$labels['previousmessages'] = 'Прикажи претходни скуп порука';
$labels['nextmessages']     = 'Прикажи следећи скуп порука';
$labels['backtolist']       = 'Повратак на листу порука';
$labels['viewsource']       = 'Прикажи поруку у изворном облику';

$labels['select'] = 'Означи';
$labels['all'] = 'Све';
$labels['none'] = 'Ниједну';
$labels['unread'] = 'Непрочитане';

$labels['compact'] = 'Компакција';
$labels['empty'] = 'Испразни';
$labels['purge'] = 'Oчисти';

$labels['quota'] = 'Квота';
$labels['unknown']  = 'непозната';
$labels['unlimited']  = 'бесконачна';

$labels['quicksearch']  = 'Брза претрага';
$labels['resetsearch']  = 'Прикажи све поруке';

// message compose
$labels['compose']  = 'Нова порука';
$labels['savemessage']  = 'Сачувај у Снимљене';
$labels['sendmessage']  = 'Пошаљи поруку';
$labels['addattachment']  = 'Приложи датотеку';
$labels['charset']  = 'Скуп карактера';
$labels['returnreceipt'] = 'Потврда о приспећу';

$labels['checkspelling'] = 'Провера правописа';
$labels['resumeediting'] = 'Повратак у режим писања';
$labels['revertto'] = 'Врати се на';

$labels['attachments'] = 'Прилози ';
$labels['upload'] = 'Додај';
$labels['close']  = 'Затвори';

$labels['low']     = 'Низак';
$labels['lowest']  = 'Најнижи';
$labels['normal']  = 'Средњи';
$labels['high']    = 'Висок';
$labels['highest'] = 'Највиши';

$labels['nosubject']  = '(без наслова)';
$labels['showimages'] = 'Прикажи слике';

// address boook
$labels['name']      = 'Пуно име';
$labels['firstname'] = 'Име';
$labels['surname']   = 'Презиме';
$labels['email']     = 'Е-мејл';

$labels['addcontact'] = 'Додај нови контакт';
$labels['editcontact'] = 'Измени контакт';

$labels['edit']   = 'Измени';
$labels['cancel'] = 'Поништи';
$labels['save']   = 'Сачувај';
$labels['delete'] = 'Обриши';

$labels['newcontact']     = 'Додај нови контакт';
$labels['deletecontact']  = 'Обриши одабране контакте';
$labels['composeto']      = 'Напиши поруку...';
$labels['contactsfromto'] = 'Контакти од $from до $to од укупно $count';
$labels['print']          = 'Штампај';
$labels['export']         = 'Извези';

$labels['previouspage']   = 'Прикажи претходну страну';
$labels['nextpage']       = 'Прикажи следећу страну';

// LDAP search
$labels['ldapsearch'] = 'Претрага LDAP именика';
$labels['ldappublicsearchname']    = 'Име контакта';
$labels['ldappublicsearchtype'] = 'Потпуно поклапање?';
$labels['ldappublicserverselect'] = 'Одабери сервере';
$labels['ldappublicsearchfield'] = 'Претражи на';
$labels['ldappublicsearchform'] = 'Претражи контакте';
$labels['ldappublicsearch'] = 'Претрага';

// settings
$labels['settingsfor']  = 'Подешавања за';
$labels['preferences']  = 'Подешавања';
$labels['userpreferences']  = 'Општа подешавања';
$labels['editpreferences']  = 'Измена општих подешавања';

$labels['identities']  = 'Идентитети';
$labels['manageidentities']  = 'Подеси идентитете за овај налог';
$labels['newidentity']  = 'Додај идентитет';

$labels['newitem']  = 'Нова ставка';
$labels['edititem']  = 'Измена ставке';

$labels['setdefault']  = 'Подеси подразумевано';
$labels['language']  = 'Језик';
$labels['timezone']  = 'Временска зона';
$labels['pagesize']  = 'Редова по страни';
$labels['signature'] = 'Потпис';
$labels['dstactive']  = 'Летње померање времена';

$labels['folder']  = 'Фасцикла';
$labels['folders']  = 'Фасцикле';
$labels['foldername']  = 'Име фасцикле';
$labels['subscribed']  = 'Потписана';
$labels['create']  = 'Креирај';
$labels['createfolder']  = 'Креирај нову фасциклу';
$labels['rename'] = 'Преименуј';
$labels['renamefolder'] = 'Преименуј фасциклу';
$labels['deletefolder']  = 'Обриши фасциклу';
$labels['managefolders']  = 'Подеси фасциклу';

$labels['sortby'] = 'Сортирај по';
$labels['sortasc']  = 'Сортирај растуће';
$labels['sortdesc'] = 'Сортирај опадајуће';

?>