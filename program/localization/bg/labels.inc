<?php

/*
 +-----------------------------------------------------------------------+
 | language/bg/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Todor Dragnev <todor.dragnev@gmail.com>                       |
 +-----------------------------------------------------------------------+

 $Id: $

*/

$labels = array();

// login page
$labels['welcome']   = 'Добре дошли в $product';
$labels['username']  = 'Потребител';
$labels['password']  = 'Парола';
$labels['server']    = 'Сървър';
$labels['login']     = 'Вход';

// taskbar
$labels['logout']   = 'Изход';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Настройки';
$labels['addressbook'] = 'Адресна книга';

// mailbox names
$labels['inbox']  = 'Входящи';
$labels['sent']   = 'Изпратени';
$labels['trash']  = 'Кошче';
$labels['drafts'] = 'Чернови';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Заглавие';
$labels['from']    = 'От';
$labels['to']      = 'Получател';
$labels['cc']      = 'Копие до';
$labels['bcc']     = 'Скрито копие до';
$labels['replyto'] = 'Отговор на';
$labels['date']    = 'Дата';
$labels['size']    = 'Размер';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организация';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Съобщения $from до $to от $count';
$labels['messagenrof'] = 'Съобщение $nr от $count';

$labels['moveto']   = 'премести в...';
$labels['download'] = 'изтегли';

$labels['filename'] = 'Име на файла';
$labels['filesize'] = 'Размер на файла';

$labels['preferhtml'] = 'Prefer HTML';
$labels['htmlmessage'] = 'HTML съобщение';
$labels['prettydate'] = 'Pretty dates';

$labels['addtoaddressbook'] = 'Добави в адресната книга';

// weekdays short
$labels['sun'] = 'Нед';
$labels['mon'] = 'Пон';
$labels['tue'] = 'Вто';
$labels['wed'] = 'Сря';
$labels['thu'] = 'Чет';
$labels['fri'] = 'Пет';
$labels['sat'] = 'Съб';

// weekdays long
$labels['sunday']    = 'Неделя';
$labels['monday']    = 'Понеделник';
$labels['tuesday']   = 'Вторник';
$labels['wednesday'] = 'Сряда';
$labels['thursday']  = 'Четвъртък';
$labels['friday']    = 'Петък';
$labels['saturday']  = 'Събота';

$labels['today'] = 'Днес';

// toolbar buttons
$labels['writenewmessage']  = 'Създай ново съобщение';
$labels['replytomessage']   = 'Отговори на съобщението';
$labels['replytoallmessage'] = 'Отговори до всички получатели';
$labels['forwardmessage']   = 'Насочи съобщението';
$labels['deletemessage']    = 'Премести съобщението в кошчето';
$labels['printmessage']     = 'Разпечатай съобщението';
$labels['previousmessages'] = 'Виж предишната страница със съобщения';
$labels['nextmessages']     = 'Виж следващата страница със съобщения';
$labels['backtolist']       = 'Обратно към листата';
$labels['viewsource']       = 'Виж кода';

$labels['select'] = 'Избери';
$labels['all'] = 'Всички';
$labels['none'] = 'None';
$labels['unread'] = 'Нови';

$labels['compact'] = 'Compact';
$labels['empty'] = 'Празни';
$labels['purge'] = 'Purge';

$labels['quota'] = 'Дисково заемане';
$labels['unknown']  = 'неизвестен';
$labels['unlimited']  = 'неограничен';

$labels['quicksearch']  = 'Бързо търсене';
$labels['resetsearch']  = 'Reset search';


// message compose
$labels['compose']  = 'Ново съобщение';
$labels['sendmessage']  = 'Изпрати съобщението';
$labels['addattachment']  = 'Прикачи файл';
$labels['charset']  = 'Charset';
$labels['returnreceipt'] = 'Върни разписка';

$labels['attachments'] = 'Прикачени';
$labels['upload'] = 'Качи';
$labels['close']  = 'Затвори';

$labels['low']     = 'Слаба';
$labels['lowest']  = 'Най-слаба';
$labels['normal']  = 'Нормална';
$labels['high']    = 'Висока';
$labels['highest'] = 'Най-висока';

$labels['nosubject']  = '(няма заглавие)';
$labels['showimages'] = 'виж изображенията';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'тесктов';


// address boook
$labels['name']      = 'Прякор';
$labels['firstname'] = 'Име';
$labels['surname']   = 'Фамилия';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Добави';
$labels['editcontact'] = 'Редактирай';

$labels['edit']   = 'Редактирай';
$labels['cancel'] = 'Отказ';
$labels['save']   = 'Запиши';
$labels['delete'] = 'Изтрии';

$labels['newcontact']     = 'Нов Контакт';
$labels['addcontact']     = 'Добави маркираните контакти в адресната книга';
$labels['deletecontact']  = 'Изтрии маркираните контакти';
$labels['composeto']      = 'Напиши мейл до';
$labels['contactsfromto'] = 'Контакти $from до $to от $count';
$labels['print']          = 'Разпечатай';
$labels['export']         = 'Свали';

$labels['previouspage']   = 'Покажи предни';
$labels['nextpage']       = 'Покажи следващи';


// LDAP search
$labels['ldapsearch'] = 'Търсене на директория';

$labels['ldappublicsearchname']    = 'Име на контакта';
$labels['ldappublicsearchtype'] = 'Точно съвпадение?';
$labels['ldappublicserverselect'] = 'Избери сървъри';
$labels['ldappublicsearchfield'] = 'Търси по';
$labels['ldappublicsearchform'] = 'Търсене за контакт';
$labels['ldappublicsearch'] = 'Търси';


// settings
$labels['settingsfor']  = 'Настройки за';

$labels['preferences']  = 'Настройки';
$labels['userpreferences']  = 'Потребителски настройки';
$labels['editpreferences']  = 'Редактирай потребителските настройки';

$labels['identities']  = 'Самоличности';
$labels['manageidentities']  = 'Маниполиране на самоличностите за този акаунт';
$labels['newidentity']  = 'Нова самоличност';

$labels['newitem']  = 'Нов артикул';
$labels['edititem']  = 'Редактирай артикул';

$labels['setdefault']  = 'По подразбиране';
$labels['language']  = 'Език';
$labels['timezone']  = 'Часова зона';
$labels['pagesize']  = 'Редове на страница';
$labels['signature'] = 'Подпис';
$labels['dstactive']  = 'Daylight savings';
$labels['htmleditor'] = 'Писане на HTML съобщения';
$labels['htmlsignature'] = 'HTML подпис';

$labels['autosavedraft'] = 'Автоматично записвай чернова';
$labels['everynminutes'] = 'всеки $n минути';
$labels['never'] = 'никога';

$labels['folder']  = 'Папка';
$labels['folders']  = 'Папки';
$labels['foldername']  = 'Име на папката';
$labels['subscribed']  = 'Използва се';
$labels['create']  = 'Създай';
$labels['createfolder']  = 'Направи нова папка';
$labels['deletefolder']  = 'Изтрии папка';
$labels['managefolders']  = 'Директории';

$labels['sortby'] = 'Сортирай по';
$labels['sortasc']  = 'Сортирай възходящо';
$labels['sortdesc'] = 'Сортирай низходящо';

?>
