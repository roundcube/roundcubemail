<?php

/*
 +-----------------------------------------------------------------------+
 | language/ru/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Maxim Zenin <maxx@webmechanics.ru>, www.webmechanics.ru       |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Имя пользователя';
$labels['password']  = 'Пароль';
$labels['server']    = 'Сервер';
$labels['login']     = 'Логин';

// taskbar
$labels['logout']   = 'Выход';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Настройки';
$labels['addressbook'] = 'Адресная книга';

// mailbox names
$labels['inbox']  = 'Входящие';
$labels['sent']   = 'Отправленные';
$labels['trash']  = 'Корзина';
$labels['drafts'] = 'Черновики';
$labels['junk']   = 'СПАМ';

// message listing
$labels['subject'] = 'Тема';
$labels['from']    = 'отправитель';
$labels['to']      = 'Получатель';
$labels['cc']      = 'Копия';
$labels['bcc']     = 'Скрытая';
$labels['replyto'] = 'Отвечать на';
$labels['date']    = 'Дата';
$labels['size']    = 'Размер';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организация';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Сообщения  с $from по $to из $count';
$labels['messagenrof'] = 'Сообщение $nr из $count';

$labels['moveto']   = 'переместить в...';
$labels['download'] = 'загрузить';

$labels['filename'] = 'Имя файла';
$labels['filesize'] = 'Размер файла';

$labels['preferhtml'] = 'предпочитать HTML';
$labels['htmlmessage'] = 'Сообщение HTML';
$labels['prettydate'] = 'Красивые даты';

$labels['addtoaddressbook'] = 'Добавить в адресную книгу';

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
$labels['writenewmessage']  = 'Новое сообщение';
$labels['replytomessage']   = 'Ответить';
$labels['forwardmessage']   = 'Переслать';
$labels['deletemessage']    = 'В корзину';
$labels['printmessage']     = 'Печать';
$labels['previousmessages'] = 'Показать предыдущее';
$labels['nextmessages']     = 'Показать следующее';
$labels['backtolist']       = 'К списку сообщений';
$labels['viewsource']       = 'Показать источник';

$labels['select'] = 'Выбрать';
$labels['all'] = 'Все';
$labels['none'] = 'Ничего';
$labels['unread'] = 'Непрочитанные';

// message compose
$labels['compose']  = 'Написать';
$labels['sendmessage']  = 'Отправить сейчас';
$labels['addattachment']  = 'Вложение';

$labels['attachments'] = 'Вложения';
$labels['upload'] = 'Загрузить';
$labels['close']  = 'Закрыть';

$labels['low']     = 'Низкий';
$labels['lowest']  = 'Самый низкий';
$labels['normal']  = 'Нормальный';
$labels['high']    = 'Высокий';
$labels['highest'] = 'Самый высокий';

$labels['showimages'] = 'Показать картинки';


// address boook
$labels['name']      = 'Псевдоним';
$labels['firstname'] = 'Имя';
$labels['surname']   = 'Фамилия';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Добавть контакт';
$labels['editcontact'] = 'Редактировать контакт';

$labels['edit']   = 'Правка';
$labels['cancel'] = 'Отмена';
$labels['save']   = 'Сохранить';
$labels['delete'] = 'Отмена';

$labels['newcontact']     = 'Создать карточку контакта';
$labels['deletecontact']  = 'Удалить выбранные контакты';
$labels['composeto']      = 'Новое сообщение контакту';
$labels['contactsfromto'] = 'Контакты с $from по $to из $count';
$labels['print']          = 'Печать';
$labels['export']         = 'Экспорт';


// settings
$labels['settingsfor']  = 'Настройки для';

$labels['preferences']  = 'Настройки';
$labels['userpreferences']  = 'Настройки пользователя';
$labels['editpreferences']  = 'Редактировать настройки пользователя';

$labels['identities']  = 'Профили';
$labels['manageidentities']  = 'Управление профилями';
$labels['newidentity']  = 'новый профиль';

$labels['newitem']  = 'Новый элемент';
$labels['edititem']  = 'Редактировать элемент';

$labels['setdefault']  = 'Сделать основным';
$labels['language']  = 'Язык';
$labels['timezone']  = 'Часовой пояс';
$labels['pagesize']  = 'Строк на странице';


$labels['folders']  = 'Папки';
$labels['foldername']  = 'Имя папки';
$labels['subscribed']  = 'Подписан';
$labels['create']  = 'Создать';
$labels['createfolder']  = 'Создать новую папку';
$labels['deletefolder']  = 'Удалить папку';
$labels['managefolders']  = 'Настройка папок';


?>