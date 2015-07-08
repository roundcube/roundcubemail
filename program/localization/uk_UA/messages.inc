<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/
$messages['errortitle']  = 'Виникла помилка!';
$messages['loginfailed']  = 'Невдала спроба входу';
$messages['cookiesdisabled'] = 'Ваш переглядач не приймає cookie';
$messages['sessionerror'] = 'Ваша сесія застаріла';
$messages['storageerror'] = 'Невдале з`єднання з IMAP сервером';
$messages['servererror'] = 'Помилка сервера!';
$messages['servererrormsg'] = 'Помилка сервера: $msg';
$messages['dberror'] = 'Помилка бази даних!';
$messages['requesttimedout'] = 'Тайм-аут запиту';
$messages['errorreadonly'] = 'Неможливо виконати операцію. Папка доступна тільки для читання.';
$messages['errornoperm'] = 'Неможливо виконати операцію. Доступ заборонено';
$messages['erroroverquota'] = 'Неможливо виконати операцію. Немає вільного місця на диску.';
$messages['erroroverquotadelete'] = 'Недостатньо вільного місця на диску. Скористайтесь  SHIFT+DEL для видалення повідомлення.';
$messages['invalidrequest'] = 'Невірний запит! Дані не збережено.';
$messages['invalidhost'] = 'Невірне ім\'я серверу.';
$messages['nomessagesfound'] = 'Листів не знайдено';
$messages['loggedout'] = 'Вашу сесію завершено. Всього найкращого!';
$messages['refreshing'] = 'Оновлення...';
$messages['loading'] = 'Завантаження...';
$messages['uploading'] = 'Файл відправляється...';
$messages['uploadingmany'] = 'Завантаження файлів...';
$messages['loadingdata'] = 'Завантаження даних...';
$messages['checkingmail'] = 'Перевірка нових листів...';
$messages['sendingmessage'] = 'Відправка листа...';
$messages['messagesent'] = 'Лист успішно відправлено';
$messages['savingmessage'] = 'Збереження листа...';
$messages['messagesaved'] = 'Збережено в Чернетках';
$messages['successfullysaved'] = 'Збережено';
$messages['savingresponse'] = 'Збереження тексту відповіді...';
$messages['deleteresponseconfirm'] = 'Справді бажаєте видалити цей текст відповіді?';
$messages['addedsuccessfully'] = 'Контакт успішно доданий до списку контактів';
$messages['contactexists'] = 'Контакт з такою електронною адресою вже існує';
$messages['contactnameexists'] = 'Контакт з таким самим іменем вже існує.';
$messages['blockedimages'] = 'З метою безпеки зображення з зовнішніх джерел заблоковано у цьому листі.';
$messages['encryptedmessage'] = 'Лист зашифровано і не може бути відображено.';
$messages['nocontactsfound'] = 'Контакти не знайдені';
$messages['contactnotfound'] = 'Запитаний контакт не знайдений';
$messages['contactsearchonly'] = 'Введіть деякі критерії пошуку, щоб знайти контакти';
$messages['sendingfailed'] = 'Не вдалося відправити листа';
$messages['senttooquickly'] = 'Будь ласка, зачекайте $sec секунд для відправки листа';
$messages['errorsavingsent'] = 'Помилка при збереженні відправленого повідомлення.';
$messages['errorsaving'] = 'Помилка при збереженні.';
$messages['errormoving'] = 'Не вдалося перемістити листи';
$messages['errorcopying'] = 'Не вдалося зкопіювати листи';
$messages['errordeleting'] = 'Не вдалося видалити листи';
$messages['errormarking'] = 'Не вдалося позначити листи';
$messages['deletecontactconfirm']  = 'Ви дійсно бажаєте видалити вибрані контакти?';
$messages['deletegroupconfirm']  = 'Ви дійсно хочете видалити обрану групу?';
$messages['deletemessagesconfirm'] = 'Ви дійсно бажаєте видалити вибрані листи?';
$messages['deletefolderconfirm']  = 'Ви дійсно бажаєте видалити цю папку?';
$messages['purgefolderconfirm']  = 'Ви дійсно бажаєте видалити всі листи у цій папці?';
$messages['contactdeleting'] = 'Видалення контакту(ів)...';
$messages['groupdeleting'] = 'Видалення групи...';
$messages['folderdeleting'] = 'Видалення папки...';
$messages['foldermoving'] = 'Переміщення папки...';
$messages['foldersubscribing'] = 'Підписати папку...';
$messages['folderunsubscribing'] = 'Відписати папку...';
$messages['formincomplete'] = 'Заповнено не всі поля';
$messages['noemailwarning'] = 'Будь ласка, введіть коректну адресу електронної пошти';
$messages['nonamewarning']  = 'Будь ласка, введіть ім`я';
$messages['nopagesizewarning'] = 'Будь ласка, введіть розмір сторінки';
$messages['nosenderwarning'] = 'Будь ласка, введіть адресу електронної пошти відправника';
$messages['norecipientwarning'] = 'Будь ласка, вкажіть принаймні одного отримувача';
$messages['nosubjectwarning']  = 'Не вказано тему листа. Бажаєте вказати її зараз?';
$messages['nobodywarning'] = 'Відправити листа без тексту?';
$messages['notsentwarning'] = 'Лист не було відправлено. Ви бажаєте відхилити відправку?';
$messages['noldapserver'] = 'Будь ласка, виберіть LDAP сервер для пошуку';
$messages['nosearchname'] = 'Будь ласка, введіть ім`я чи електронну адресу';
$messages['notuploadedwarning'] = 'Деякі вкладення не було завантажено. Будь ласка, почекайте або відмініть завантаження.';
$messages['searchsuccessful'] = 'Зайдено $nr листів';
$messages['contactsearchsuccessful'] = '$nr контактів знайдено.';
$messages['searchnomatch'] = 'Листів не знайдено';
$messages['searching'] = 'Пошук...';
$messages['checking'] = 'Перевірка...';
$messages['nospellerrors'] = 'Орфографічних помилок не знайдено';
$messages['folderdeleted'] = 'Папка видалена';
$messages['foldersubscribed'] = 'Папка підписана';
$messages['folderunsubscribed'] = 'Папка відписана';
$messages['folderpurged'] = 'Папка видалена';
$messages['folderexpunged'] = 'Папка очищена';
$messages['deletedsuccessfully'] = 'Видалено успішно';
$messages['converting'] = 'Видалення форматування...';
$messages['messageopenerror'] = 'Не вдалося завантажити повідомлення з сервера';
$messages['fileuploaderror'] = 'Не вдалося вкласти файл';
$messages['filesizeerror'] = 'Розмір вибраного файлу перевищує максимально дозволений ($size)';
$messages['copysuccess'] = 'Успішно скопійовано $nr контактів';
$messages['movesuccess'] = 'Успішно переміщено $nr контактів';
$messages['copyerror'] = 'Не вдалося скопіювати жодного контакту.';
$messages['moveerror'] = 'Невдалось перемістити контакти.';
$messages['sourceisreadonly'] = 'Дане джерело адрес доступне лише для читання';
$messages['errorsavingcontact'] = 'Неможливо зберегти адресу контакту';
$messages['movingmessage'] = 'Переміщення листа...';
$messages['copyingmessage'] = 'Копіювання листа...';
$messages['copyingcontact'] = 'Копіювання контакту(ів)...';
$messages['movingcontact'] = 'Перенесення контакту(ів)...';
$messages['deletingmessage'] = 'Видалення листа (ів)';
$messages['markingmessage'] = 'Позначення листа (ів)';
$messages['addingmember'] = 'Додання контакту(ів) до групи...';
$messages['removingmember'] = 'Видалення контакту(ів) з групи...';
$messages['receiptsent'] = 'Повідомлення про прочитання відправлено';
$messages['errorsendingreceipt'] = 'Не вдалося відправити повідомлення про прочитання';
$messages['deleteidentityconfirm'] = 'Ви дійсно бажаєте видалити цей профіль?';
$messages['nodeletelastidentity'] = 'Ви не можете видалити цей профіль, він у Вас останній.';
$messages['forbiddencharacter'] = 'Ім\'я папки містить заборонені символи';
$messages['selectimportfile'] = 'Виберіть файл для завантаження';
$messages['addresswriterror'] = 'Обрана адресна книга недоступна для запису';
$messages['contactaddedtogroup'] = 'Контакти успішно додано до цієї групи';
$messages['contactremovedfromgroup'] = 'УКонтакти успішно видалено з цієї групи';
$messages['nogroupassignmentschanged'] = 'Розподіл за групами не змінено.';
$messages['importwait'] = 'Імпортування, будь ласка, зачекайте...';
$messages['importformaterror'] = 'Помилка імпорту! Завантажений файл має невідомий формат даних.';
$messages['importconfirm'] = '<b>Успішно імпортовано $inserted контактів, пропущено $skipped існуючих</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Пропущені $skipped наявні записи</b>';
$messages['importmessagesuccess'] = 'Успішно імпортовано $nr повідомлень';
$messages['importmessageerror'] = 'Імпорт не вдався! Завантажений файл не є припустимим повідомлення або файлом поштової скриньки';
$messages['opnotpermitted'] = 'Дія заборонена!';
$messages['nofromaddress'] = 'В обраному профілі не вистачає адреси електронної пошти';
$messages['httpreceivedencrypterror'] = 'Помилка конфігурації. Негайно зверніться до адміністратора. <b>Ваше повідомлення не буде відправлено.</b>';
$messages['smtpconnerror'] = 'Помилка SMTP-сервера ($code): Не вдалося з\'єднатися з сервером';
$messages['smtpautherror'] = 'Помилка SMTP-сервера ($code): Невдала спроба автентифікації';
$messages['smtpfromerror'] = 'Помилка SMTP-сервера ($code): Не вдалося вказати відправника "$from" ($msg)';
$messages['smtptoerror'] = 'Помилка SMTP-сервера ($code): Не вдалося додати отримувача "$to" ($msg)';
$messages['smtprecipientserror'] = 'Помилка SMTP: Не вдалося обробити список отримувачів';
$messages['smtperror'] = 'Помилка SMTP: $msg';
$messages['emailformaterror'] = 'Невірна електронна адреса: $email';
$messages['toomanyrecipients'] = 'Занадто багато отримувачів. Зменшіть їх число до $max.';
$messages['maxgroupmembersreached'] = 'Число адрес у групі перевищило максимум у $max.';
$messages['internalerror'] = 'Виникла внутрішня помилка. Будь ласка, спробуйте ще раз.';
$messages['contactdelerror'] = 'Неможливо видалити контакт(и)';
$messages['contactdeleted'] = 'Контакт(и) видалено успішно';
$messages['contactrestoreerror'] = 'Неможливо відновити видалений(і) контакт(и).';
$messages['contactrestored'] = 'Контакт(и) вдало відновлено.';
$messages['groupdeleted'] = 'Група видалена успішно';
$messages['grouprenamed'] = 'Група перейменована успішно';
$messages['groupcreated'] = 'Група створена успішно';
$messages['savedsearchdeleted'] = 'Збережений пошук вдало видалено.';
$messages['savedsearchdeleteerror'] = 'Неможливо видалити збережений пошук.';
$messages['savedsearchcreated'] = 'Збережений пошук вдало створено.';
$messages['savedsearchcreateerror'] = 'Не вдалось створити збережений пошук.';
$messages['messagedeleted'] = 'Лист(и) видалено успішно';
$messages['messagemoved'] = 'Лист(и) переміщено успішно';
$messages['messagecopied'] = 'Лист(и) скопійовано успішно';
$messages['messagemarked'] = 'Лист(и) помічено успішно';
$messages['autocompletechars'] = 'Введіть щонайменьше $min символів для автозаповнення';
$messages['autocompletemore'] = 'Знайдено багато відповідних записів. Будь ласка введіть більше символів.';
$messages['namecannotbeempty'] = 'Ім’я не може бути пустим';
$messages['nametoolong'] = 'Занадто довге ім’я';
$messages['folderupdated'] = 'Папка відновлена';
$messages['foldercreated'] = 'Папка створена';
$messages['invalidimageformat'] = 'Невірний формат зображення';
$messages['mispellingsfound'] = 'Знайдено орфографічні помилки у повідомленні.';
$messages['parentnotwritable'] = 'Неможливо створити/перемістити папку до обраної батьківської папки. Нема прав доступу.';
$messages['messagetoobig'] = 'Частина повідомлення занадто велика для опрацювання.';
$messages['attachmentvalidationerror'] = 'УВАГА! Це вкладення є підозрілим, тому що його тип не збігається з типом, оголошеним у повідомленні. Якщо ви не довіряєте відправнику, ви не повинні відкривати його в браузері, оскільки його вміст може бути шкідливим.<br/><br/><em>Очікуване: $expected; знайдене: $detected </em>';
$messages['noscriptwarning'] = 'Увага: Даний клієнт електронної пошти потрібує Javascript! Для того, щоб використовувати його необхідно включити підтримку Javascript в налаштуваннях вашого браузера.';
?>
