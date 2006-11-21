<?php

/*
 +-----------------------------------------------------------------------+
 | language/ru_RU/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Maxim Zenin <maxx@webmechanics.ru>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Добро пожаловать в $product!';
$labels['username']  = 'Имя пользователя';
$labels['password']  = 'Пароль';
$labels['server']    = 'Сервер';
$labels['login']     = 'Войти';

// taskbar
$labels['logout']   = 'Выход';
$labels['mail']     = 'Почта';
$labels['settings'] = 'Настройки';
$labels['addressbook'] = 'Контакты';

// mailbox names
$labels['inbox']  = 'Входящие';
$labels['sent']   = 'Отправленные';
$labels['trash']  = 'Корзина';
$labels['drafts'] = 'Черновики';
$labels['junk']   = 'СПАМ';

// message listing
$labels['subject'] = 'Тема';
$labels['from']    = 'От';
$labels['to']      = 'Кому';
$labels['cc']      = 'Копия';
$labels['bcc']     = 'Скрытая';
$labels['replyto'] = 'Обратный адрес';
$labels['date']    = 'Дата';
$labels['size']    = 'Размер';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организация';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Сообщения с $from по $to из $count';
$labels['messagenrof'] = 'Сообщение $nr из $count';

$labels['moveto']   = 'Переместить в...';
$labels['download'] = 'Загрузить';

$labels['filename'] = 'Имя файла';
$labels['filesize'] = 'Размер файла';

$labels['preferhtml'] = 'Предпочитать HTML';
$labels['htmlmessage'] = 'Сообщение HTML';
$labels['prettydate'] = 'Красивые даты';

$labels['addtoaddressbook'] = 'Добавить в контакты';

// weekdays short
$labels['sun'] = 'Вс';
$labels['mon'] = 'Пн';
$labels['tue'] = 'Вт';
$labels['wed'] = 'Ср';
$labels['thu'] = 'Чт';
$labels['fri'] = 'Пт';
$labels['sat'] = 'Сб';

// weekdays long
$labels['sunday']    = 'Воскресенье';
$labels['monday']    = 'Понедельник';
$labels['tuesday']   = 'Вторник';
$labels['wednesday'] = 'Среда';
$labels['thursday']  = 'Четверг';
$labels['friday']    = 'Пятница';
$labels['saturday']  = 'Суббота';

$labels['today'] = 'Сегодня';

// toolbar buttons
$labels['checkmail']        = 'Доставить почту';
$labels['writenewmessage']  = 'Новое сообщение';
$labels['replytomessage']   = 'Ответить';
$labels['replytoallmessage'] = 'Ответить всем';
$labels['forwardmessage']   = 'Переслать';
$labels['deletemessage']    = 'В корзину';
$labels['printmessage']     = 'Печать';
$labels['previousmessages'] = 'Предыдущее';
$labels['firstmessages']    = 'Первое сообщение';
$labels['nextmessages']     = 'Следующее';
$labels['lastmessages']     = 'Последнее сообщение';
$labels['backtolist']       = 'К списку сообщений';
$labels['viewsource']       = 'Исходный текст';

$labels['select'] = 'Выбрать';
$labels['all'] = 'Все';
$labels['none'] = 'Ничего';
$labels['unread'] = 'Непрочитанные';

$labels['compact'] = 'Сжать';
$labels['empty'] = 'Опустошить';
$labels['purge'] = 'Очистить';

$labels['quota'] = 'Квота';
$labels['unknown']  = 'неизвестно';
$labels['unlimited']  = 'без ограничений';

$labels['quicksearch']  = 'Быстрый поиск';
$labels['resetsearch']  = 'Сброс';


// message compose
$labels['compose']  = 'Написать сообщение';
$labels['savemessage']  = 'Сохранить черновик';
$labels['sendmessage']  = 'Отправить сейчас';
$labels['addattachment']  = 'Добавить вложение';
$labels['charset']  = 'Кодировка';
$labels['returnreceipt'] = 'Запрос ответа';

$labels['rename'] = 'Переименовать';
$labels['renamefolder'] = 'Переименовать папку';

$labels['checkspelling'] = 'Проверить орфографию';
$labels['resumeediting'] = 'Продолжить редактирование';
$labels['revertto'] = 'Отменить правки';

$labels['attachments'] = 'Вложения';
$labels['upload'] = 'Загрузить';
$labels['close']  = 'Закрыть';

$labels['low']     = 'Низкий';
$labels['lowest']  = 'Нижайш.';
$labels['normal']  = 'Норм.';
$labels['high']    = 'Высокий';
$labels['highest'] = 'Высоч.';

$labels['nosubject']  = '(без темы)';
$labels['showimages'] = 'Показать изображения';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Обычный текст';

// address boook
$labels['name']      = 'Отображаемое имя';
$labels['firstname'] = 'Имя';
$labels['surname']   = 'Фамилия';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Добавить контакт';
$labels['editcontact'] = 'Редактировать контакт';

$labels['edit']   = 'Правка';
$labels['cancel'] = 'Отмена';
$labels['save']   = 'Сохранить';
$labels['delete'] = 'Удалить';

$labels['newcontact']     = 'Создать новый контакт';
$labels['addcontact']     = 'Добавить выбранные контакты в список контактов';
$labels['deletecontact']  = 'Удалить выбранные контакты';
$labels['composeto']      = 'Создать сообщение для выбранных контактов';
$labels['contactsfromto'] = 'Контакты с $from по $to из $count';
$labels['print']          = 'Печать';
$labels['export']         = 'Экспорт';

$labels['previouspage']   = 'Показать предыдущий';
$labels['firstpage']   		= 'Показать первый';
$labels['nextpage']       = 'Показать следующий';
$labels['lastpage']   		= 'Показать последний';

// LDAP search
$labels['ldapsearch'] = 'Поиск в каталоге LDAP';

$labels['ldappublicsearchname']    = 'Имя';
$labels['ldappublicsearchtype'] = 'Точное совпадение?';
$labels['ldappublicserverselect'] = 'Выбрать сервер';
$labels['ldappublicsearchfield'] = 'искать на';
$labels['ldappublicsearchform'] = 'Искать контакт';
$labels['ldappublicsearch'] = 'Искать';


// settings
$labels['settingsfor']  = 'Настройки для';

$labels['preferences']  = 'Настройки';
$labels['userpreferences']  = 'Настройки пользователя';
$labels['editpreferences']  = 'Редактировать настройки пользователя';

$labels['identities']  = 'Профили';
$labels['manageidentities']  = 'Управление профилями';
$labels['newidentity']  = 'Новый профиль';

$labels['newitem']  = 'Новый';
$labels['edititem']  = 'Правка';

$labels['setdefault']  = 'Использовать по умолчанию';
$labels['language']  = 'Язык';
$labels['timezone']  = 'Часовой пояс';
$labels['pagesize']  = 'Строк на странице';
$labels['signature'] = 'Подпись';
$labels['dstactive']  = 'Перевод часов';

$labels['folder']  = 'Папка';
$labels['folders']  = 'Папки';
$labels['foldername']  = 'Имя папки';
$labels['subscribed']  = 'Подписан';
$labels['create']  = 'Создать';
$labels['createfolder']  = 'Создать новую папку';
$labels['deletefolder']  = 'Удалить папку';
$labels['managefolders']  = 'Управление папками';

$labels['sortby'] = 'Сортировать по';
$labels['sortasc']  = 'Возрастанию';
$labels['sortdesc'] = 'Убыванию';

$labels['description'] = 'Описание';
?>