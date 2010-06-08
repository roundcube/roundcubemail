<?php

/*

+-----------------------------------------------------------------------+
| language/uk/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Volodymyr M. Kononenko aka kvm <vmkononenko@gmail.com>        |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Невдала спроба входу';
$messages['cookiesdisabled'] = 'Ваш переглядач не приймає cookie';
$messages['sessionerror'] = 'Ваша сесія застаріла';
$messages['imaperror'] = 'Невдале з`єднання з IMAP сервером';
$messages['servererror'] = 'Помилка сервера!';
$messages['invalidrequest'] = 'Невірний запит! Дані не збережено.';
$messages['nomessagesfound'] = 'Листів не знайдено';
$messages['loggedout'] = 'Вашу сесію завершено. Всього найкращого!';
$messages['mailboxempty'] = 'Поштова скринька порожня';
$messages['loading'] = 'Завантаження...';
$messages['uploading'] = 'Файл відправляється...';
$messages['loadingdata'] = 'Завантаження даних...';
$messages['checkingmail'] = 'Перевірка нових листів...';
$messages['sendingmessage'] = 'Відправка листа...';
$messages['messagesent'] = 'Лист успішно відправлено';
$messages['savingmessage'] = 'Збереження листа...';
$messages['messagesaved'] = 'Збережено в Чернетках';
$messages['successfullysaved'] = 'Збережено';
$messages['addedsuccessfully'] = 'Контакт успішно доданий до списку контактів';
$messages['contactexists'] = 'Контакт з такою електронною адресою вже існує';
$messages['blockedimages'] = 'З метою безпеки зображення з зовнішніх джерел заблоковано у цьому листі.';
$messages['encryptedmessage'] = 'Лист зашифровано і не може бути відображено.';
$messages['nocontactsfound'] = 'Контакти не знайдені';
$messages['contactnotfound'] = 'Запитаний контакт не знайдений';
$messages['sendingfailed'] = 'Не вдалося відправити листа';
$messages['senttooquickly'] = 'Будь ласка, зачекайте $sec секунд для відправки листа';
$messages['errorsavingsent'] = 'Помилка при збереженні відправленого листа';
$messages['errorsaving'] = 'Помилка при збереженні';
$messages['errormoving'] = 'Не вдалося перемістити листи';
$messages['errorcopying'] = 'Не вдалося зкопіювати листи';
$messages['errordeleting'] = 'Не вдалося видалити листи';
$messages['errormarking'] = 'Не вдалося позначити листи';
$messages['deletecontactconfirm'] = 'Ви дійсно бажаєте видалити вибрані контакти?';
$messages['deletemessagesconfirm'] = 'Ви дійсно бажаєте видалити вибрані листи?';
$messages['deletefolderconfirm'] = 'Ви дійсно бажаєте видалити цю папку?';
$messages['purgefolderconfirm'] = 'Ви дійсно бажаєте видалити всі листи у цій папці?';
$messages['foldercreating'] = 'Створення папки...';
$messages['folderdeleting'] = 'Видалення папки...';
$messages['folderrenaming'] = 'Перейменування папки...';
$messages['foldermoving'] = 'Переміщення папки...';
$messages['formincomplete'] = 'Заповнено не всі поля';
$messages['noemailwarning'] = 'Будь ласка, введіть коректну адресу електронної пошти';
$messages['nonamewarning'] = 'Будь ласка, введіть ім`я';
$messages['nopagesizewarning'] = 'Будь ласка, введіть розмір сторінки';
$messages['nosenderwarning'] = 'Будь ласка, введіть адресу електронної пошти відправника';
$messages['norecipientwarning'] = 'Будь ласка, вкажіть принаймні одного отримувача';
$messages['nosubjectwarning'] = 'Не вказано тему листа. Бажаєте вказати її зараз?';
$messages['nobodywarning'] = 'Відправити листа без тексту?';
$messages['notsentwarning'] = 'Лист не було відправлено. Ви бажаєте відхилити відправку?';
$messages['noldapserver'] = 'Будь ласка, виберіть LDAP сервер для пошуку';
$messages['nocontactsreturned'] = 'Контакти не знайдені';
$messages['nosearchname'] = 'Будь ласка, введіть ім`я чи електронну адресу';
$messages['notuploadedwarning'] = 'Деякі вкладення не було завантажено. Будь ласка, почекайте або відмініть завантаження.';
$messages['searchsuccessful'] = 'Зайдено $nr листів';
$messages['searchnomatch'] = 'Листів не знайдено';
$messages['searching'] = 'Пошук...';
$messages['checking'] = 'Перевірка...';
$messages['nospellerrors'] = 'Орфографічних помилок не знайдено';
$messages['folderdeleted'] = 'Папку успішно видалено';
$messages['deletedsuccessfully'] = 'Видалено успішно';
$messages['converting'] = 'Видалення форматування...';
$messages['messageopenerror'] = 'Не вдалося завантажити повідомлення з сервера';
$messages['fileuploaderror'] = 'Не вдалося вкласти файл';
$messages['filesizeerror'] = 'Розмір вибраного файлу перевищує максимально дозволений ($size)';
$messages['copysuccess'] = 'Скопійовано $nr адрес';
$messages['copyerror'] = 'Не вдалося скопіювати жодну адресу';
$messages['sourceisreadonly'] = 'Дане джерело адрес доступне лише для читання';
$messages['errorsavingcontact'] = 'Неможливо зберегти адресу контакту';
$messages['movingmessage'] = 'Переміщення листа...';
$messages['copyingmessage'] = 'Копіювання листа...';
$messages['receiptsent'] = 'Повідомлення про прочитання відправлено';
$messages['errorsendingreceipt'] = 'Не вдалося відправити повідомлення про прочитання';
$messages['nodeletelastidentity'] = 'Ви не можете видалити цей профіль, він у Вас останній.';
$messages['addsubfolderhint'] = 'Цю папку буде створено як підпапку в поточній.';
$messages['forbiddencharacter'] = 'Ім\'я папки містить заборонені символи';
$messages['selectimportfile'] = 'Виберіть файл для завантаження';
$messages['addresswriterror'] = 'Обрана адресна книга недоступна для запису';
$messages['contactaddedtogroup'] = 'Контакти успішно додано до цієї групи';
$messages['contactremovedfromgroup'] = 'УКонтакти успішно видалено з цієї групи';
$messages['importwait'] = 'Імпортування, будь ласка, зачекайте...';
$messages['importerror'] = 'Імпорт завершився невдало! Завантажений файл не є правильним файлом vCard';
$messages['importconfirm'] = '<b>Успішно імпортовано $inserted контактів, пропущено $skipped існуючих</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Дія заборонена!';
$messages['nofromaddress'] = 'В обраному профілі не вистачає адреси електронної пошти';
$messages['editorwarning'] = 'Перемикання в режим звичайного тексту спричинить втрату всього форматування. Продовжити?';
$messages['httpreceivedencrypterror'] = 'Помилка конфігурації. Негайно зверніться до адміністратора. <b>Ваше повідомлення не буде відправлено.</b>';
$messages['smtpconnerror'] = 'Помилка SMTP-сервера ($code): Не вдалося з\'єднатися з сервером';
$messages['smtpautherror'] = 'Помилка SMTP-сервера ($code): Невдала спроба автентифікації';
$messages['smtpfromerror'] = 'Помилка SMTP-сервера ($code): Не вдалося вказати відправника "$from" ($msg)';
$messages['smtptoerror'] = 'Помилка SMTP-сервера ($code): Не вдалося додати отримувача "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP Error: Не вдалося обробити список отримувачів';
$messages['smtperror'] = 'Помилка SMTP-сервера: $msg';
$messages['emailformaterror'] = 'Невірна електронна адреса: $email';
$messages['toomanyrecipients'] = 'Занадто багато отримувачів. Зменшіть їх число до $max.';
$messages['maxgroupmembersreached'] = 'Число адрес у групі перевищило максимум у $max.';

?>
