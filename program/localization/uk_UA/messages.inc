<?php

/*

+-----------------------------------------------------------------------+
| language/uk/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Natali Stasyuk <natali@onlineua.net>                          |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Невдала спроба входу';
$messages['cookiesdisabled'] = 'Ваш переглядач не приймає cookie';
$messages['sessionerror'] = 'Ваша сесія застаріла';
$messages['imaperror'] = 'Невдале з`єднання з IMAP сервером';
$messages['nomessagesfound'] = 'Повідомлень не знайдено';
$messages['loggedout'] = 'Ваша сесія завершена. Всього найкращого!';
$messages['mailboxempty'] = 'Поштова скринька порожня';
$messages['loading'] = 'Завантаження...';
$messages['loadingdata'] = 'Завантаження даних...';
$messages['checkingmail'] = 'Перевірка нових повідомлень...';
$messages['sendingmessage'] = 'Відправка повідомлень...';
$messages['messagesent'] = 'Повідомлення відправлено';
$messages['savingmessage'] = 'Збереження повідомлення...';
$messages['messagesaved'] = 'Збережено в Чернетках';
$messages['successfullysaved'] = 'Збережено';
$messages['addedsuccessfully'] = 'Контакт успішно доданий до списку контактів';
$messages['contactexists'] = 'Контакт з цією адресою e-mail вже існує';
$messages['blockedimages'] = 'З метою безпеки завантаження зображень заблоковано';
$messages['encryptedmessage'] = 'Повідомлення зашифровано і не може бути показано. Зверніться до адміністратора серверу.';
$messages['nocontactsfound'] = 'Контакти не знайдені';
$messages['contactnotfound'] = 'Запитаний контакт не знайдений';
$messages['sendingfailed'] = 'Не вдалося відправити повідомлення';
$messages['senttooquickly'] = 'Ви повинні почекати $sec сек. для відправлення повідомлення';
$messages['errorsavingsent'] = 'Відбулася помилка при збереженні відправленого повідомлення';
$messages['errorsaving'] = 'У процесі збереження трапилася помилка';
$messages['errormoving'] = 'Не вдалося перемістити повідомлення';
$messages['errordeleting'] = 'Не вдалося видалити повідомлення';
$messages['deletecontactconfirm'] = 'Ви дійстно бажаєте видалити вибрані контакти?';
$messages['deletemessagesconfirm'] = 'Ви дійстно бажаєте видалити вибрані повідомлення?';
$messages['deletefolderconfirm'] = 'Ви дійстно бажаєте видалити цю теку?';
$messages['purgefolderconfirm'] = 'Ви дійстно бажаєте видалити всі повідомлення у цій теці?';
$messages['foldercreating'] = 'Створення папки';
$messages['folderdeleting'] = 'Видалити папку';
$messages['folderrenaming'] = 'Перейменування папки';
$messages['foldermoving'] = 'Переміщення папки';
$messages['formincomplete'] = 'Заповнені не усі поля';
$messages['noemailwarning'] = 'Будь ласка, введіть коректну адресу електронної пошти';
$messages['nonamewarning'] = 'Будь ласка, введіть ім`я';
$messages['nopagesizewarning'] = 'Будь ласка, введіть розмір сторінки';
$messages['nosenderwarning'] = 'Будь ласка, уведіть адресу електронної пошти відправника';
$messages['norecipientwarning'] = 'Будь ласка, введіть ім`я отримувача';
$messages['nosubjectwarning'] = 'Поле "Тема" не заповнено. Бажаєте заповнити його зараз?';
$messages['nobodywarning'] = 'Відправити повідомлення без тексту?';
$messages['notsentwarning'] = 'Повідомлення не було відправлено. Ви бажаєте відмовитись від відправки?';
$messages['noldapserver'] = 'Будь ласка, виберіть LDAP сервер для пошука';
$messages['nocontactsreturned'] = 'Контакти не знайдені';
$messages['nosearchname'] = 'Будь ласка, введіть ім`я чи адресу E-Mail';
$messages['searchsuccessful'] = 'Зайдено $nr повідомлень';
$messages['searchnomatch'] = 'Повідомлень не знайдено';
$messages['searching'] = 'Пошук...';
$messages['checking'] = 'Перевірка...';
$messages['nospellerrors'] = 'Орфографічних помилок не знайдено';
$messages['folderdeleted'] = 'Тека видалена';
$messages['deletedsuccessfully'] = 'Видалено успішно';
$messages['converting'] = 'Видалення форматованого повідомлення...';
$messages['messageopenerror'] = 'Неможливо завантажити повідомлення з серверу';
$messages['fileuploaderror'] = 'Неможливо завантажити файл';
$messages['filesizeerror'] = 'Завантажений файл більший за максимальний розмір у $size';
$messages['copysuccess'] = 'Скопійовано $nr адрес';
$messages['copyerror'] = 'Неможливо скопіювати адресу';
$messages['sourceisreadonly'] = 'Дане джерело адрес лише для читання';
$messages['errorsavingcontact'] = 'Неможливо зберегти адресу контакта';
$messages['movingmessage'] = 'Переміщую повідомлення...';
$messages['receiptsent'] = 'Повідомлення про прочитання відправлене';
$messages['errorsendingreceipt'] = 'Повідомлення про прочитання не відправлене';
$messages['nodeletelastidentity'] = 'ви не можете вилучити цей профіль, він у вас останній';
$messages['addsubfolderhint'] = 'Ця папка буде створена як підпапка в поточній папці';
$messages['forbiddencharacter'] = 'Ім\'я папки містить неприпустимі символи';
$messages['selectimportfile'] = 'Виберіть файл для завантаження';
$messages['addresswriterror'] = 'Обрана адресна книга недоступна для запису';
$messages['importwait'] = 'Імпортування, будь ласка, почекайте';
$messages['importerror'] = 'Імпорт завершився невдало! Завантажений файл не є правильним файлом vcard';
$messages['importconfirm'] = 'Успішно імпортованих $inserted контактів, пропущене $skipped існуючих';
$messages['opnotpermitted'] = 'Дія заборонена!';
$messages['nofromaddress'] = 'В обраному профілі не вистачає адреси електронної пошти';
$messages['editorwarning'] = 'При перемиканні в редактор простого тексту все форматування буде загублено. Продовжити?';

?>
