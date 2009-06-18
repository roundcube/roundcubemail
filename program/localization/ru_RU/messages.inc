<?php

/*

+-----------------------------------------------------------------------+
| language/ru_RU/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Artur Smolkin <artsmolkin@ya.ru>                              |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Неудачная попытка входа';
$messages['cookiesdisabled'] = 'Ваш броузер не принимает cookie';
$messages['sessionerror'] = 'Ваша сессия устарела';
$messages['imaperror'] = 'Неудачное соединение с IMAP сервером';
$messages['servererror'] = 'Ошибка сервера!';
$messages['nomessagesfound'] = 'Сообщений не найдено';
$messages['loggedout'] = 'Ваша сессия завершена. Всего доброго!';
$messages['mailboxempty'] = 'Почтовый ящик пуст';
$messages['loading'] = 'Загрузка...';
$messages['loadingdata'] = 'Загрузка данных...';
$messages['checkingmail'] = 'Проверка новых сообщений...';
$messages['sendingmessage'] = 'Отправка сообщения...';
$messages['messagesent'] = 'Сообщение отправлено';
$messages['savingmessage'] = 'Сохранение сообщения...';
$messages['messagesaved'] = 'Сохранено в Черновиках';
$messages['successfullysaved'] = 'Сохранено';
$messages['addedsuccessfully'] = 'Контакт добавлен в список контактов';
$messages['contactexists'] = 'Контакт с этим адресом e-mail уже существует';
$messages['blockedimages'] = 'В целях безопасности загрузка изображений заблокирована.';
$messages['encryptedmessage'] = 'Сообщение зашифровано и не может быть показано. Обратитесь к администратору сервера.';
$messages['nocontactsfound'] = 'Контакты не найдены';
$messages['contactnotfound'] = 'Требуемый контакт не найден';
$messages['sendingfailed'] = 'Не удалось отправить сообщение';
$messages['senttooquickly'] = 'Вы должны подождать $sec сек. для отправки сообщения';
$messages['errorsavingsent'] = 'Произошла ошибка при сохранении отправленного сообщения';
$messages['errorsaving'] = 'В процессе сохранения произошла ошибка';
$messages['errormoving'] = 'Не удалось переместить сообщение';
$messages['errordeleting'] = 'Не удалось удалить сообщение';
$messages['errormarking'] = 'Невозможно пометить сообщение';
$messages['deletecontactconfirm'] = 'Вы действительно хотите удалить выделенные контакты?';
$messages['deletemessagesconfirm'] = 'Вы действительно хотите удалить выбранные сообщения?';
$messages['deletefolderconfirm'] = 'Вы действительно хотите удалить эту папку?';
$messages['purgefolderconfirm'] = 'Вы действительно хотите удалить все сообщения в этой папке?';
$messages['foldercreating'] = 'Создание папки...';
$messages['folderdeleting'] = 'Удаление папки...';
$messages['folderrenaming'] = 'Переименование папки...';
$messages['foldermoving'] = 'Перемещение папки...';
$messages['formincomplete'] = 'Заполнены не все поля';
$messages['noemailwarning'] = 'Пожалуйста, введите корректный адрес электронной почты';
$messages['nonamewarning'] = 'Пожалуйста, введите имя';
$messages['nopagesizewarning'] = 'Пожалуйста, введите размер страницы';
$messages['nosenderwarning'] = 'Пожалуйста, введите адрес электронной почты отправителя';
$messages['norecipientwarning'] = 'Пожалуйста, введите имя получателя';
$messages['nosubjectwarning'] = 'Поле Тема не заполнено. Хотите заполнить его сейчас?';
$messages['nobodywarning'] = 'Отправить сообщение без текста?';
$messages['notsentwarning'] = 'Сообщение не было отправлено. Вы хотите отказаться от отправки?';
$messages['noldapserver'] = 'Пожалуйста, выберите LDAP сервер для поиска';
$messages['nocontactsreturned'] = 'Контакты не найдены';
$messages['nosearchname'] = 'Пожалуйста, введите имя или адрес E-Mail';
$messages['searchsuccessful'] = 'Найденных сообщений - $nr';
$messages['searchnomatch'] = 'Сообщений не найдено';
$messages['searching'] = 'Поиск...';
$messages['checking'] = 'Проверка...';
$messages['nospellerrors'] = 'Орфографических ошибок не найдено';
$messages['folderdeleted'] = 'Папка удалена';
$messages['deletedsuccessfully'] = 'Удалено';
$messages['converting'] = 'Удаление форматирования сообщения...';
$messages['messageopenerror'] = 'Невозможно загрузить сообщение с сервера';
$messages['fileuploaderror'] = 'Невозможно загрузить файл';
$messages['filesizeerror'] = 'Загруженный файл больше максимального размера в $size';
$messages['copysuccess'] = 'Скопировано $nr адресов';
$messages['copyerror'] = 'Невозможно скопировать адреса';
$messages['sourceisreadonly'] = 'Данный источник адресов только для чтения';
$messages['errorsavingcontact'] = 'Невозможно сохранить адрес контакта';
$messages['movingmessage'] = 'Перемещение сообщения…';
$messages['receiptsent'] = 'Уведомление о прочтении отправлено';
$messages['errorsendingreceipt'] = 'Уведомление о прочтении не отправлено';
$messages['nodeletelastidentity'] = 'Вы не можете удалить этот профиль, он у вас последний';
$messages['addsubfolderhint'] = 'Эта папка будет создана как подпапка в текущей папке';
$messages['forbiddencharacter'] = 'Имя папки содержит недопустимые символы';
$messages['selectimportfile'] = 'Выберите файл для загрузки';
$messages['addresswriterror'] = 'Выбранная адресная книга недоступна для записи';
$messages['importwait'] = 'Импортирование, пожалуйста, подождите...';
$messages['importerror'] = 'Импорт завершился неудачно! Загруженный файл не является правильным файлом vCard.';
$messages['importconfirm'] = '<b>Успешно импортировано $inserted контакт(ов), пропущено $skipped существующих</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Действие запрещено!';
$messages['nofromaddress'] = 'В выбранном профиле не хватает адреса электронной почты';
$messages['editorwarning'] = 'При переключении в редактор простого текста все форматирование будет потеряно. Продолжить?';
$messages['httpreceivedencrypterror'] = 'На сервере возникла неизбежная ошибка. Срочно свяжитесь с Вашим администратором. <b>Ваше сообщение может быть не отправлено.</b>';

?>
