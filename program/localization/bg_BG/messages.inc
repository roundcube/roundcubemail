<?php

/*

+-----------------------------------------------------------------------+
| language/bg/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Todor Dragnev <todor.dragnev@gmail.com>                       |
|	  Nickolay Bunev <just4nick@gmail.com>				|
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Вход неуспешен';
$messages['cookiesdisabled'] = 'Вашият браузър не приема cookies';
$messages['sessionerror'] = 'Невалидна или изтекла сесия';
$messages['imaperror'] = 'Неуспешно свързване към IMAP сървъра';
$messages['nomessagesfound'] = 'Няма съобщения';
$messages['loggedout'] = 'Довиждане!';
$messages['mailboxempty'] = 'Кутията е празна';
$messages['loading'] = 'Зареждане...';
$messages['loadingdata'] = 'Зареждане на данни...';
$messages['checkingmail'] = 'Проверка за нови писма...';
$messages['sendingmessage'] = 'Изпращане на писмото...';
$messages['messagesent'] = 'Писмото е изпратено успешно';
$messages['savingmessage'] = 'Записване на писмото...';
$messages['messagesaved'] = 'Писмото е записано в Чернови';
$messages['successfullysaved'] = 'Записано';
$messages['addedsuccessfully'] = 'Контакта е добавен в адресната книга';
$messages['contactexists'] = 'Контакт с този e-mail адрес вече същестува';
$messages['blockedimages'] = 'С оглед на Вашата сигурност, изображенията в това писмо са блокирани.';
$messages['encryptedmessage'] = 'Това е кодирано писмо и не може да бъде показано. Съжаляваме!';
$messages['nocontactsfound'] = 'Няма контакти';
$messages['contactnotfound'] = 'Исканият контакт не е намерен';
$messages['sendingfailed'] = 'Изпращането неуспешно';
$messages['senttooquickly'] = 'Моля, изчакайте $sec секунда(и) преди да изпратите съобщението';
$messages['errorsavingsent'] = 'Възникна грешка при записването на съобщението';
$messages['errorsaving'] = 'Възникна грешка при записването';
$messages['errormoving'] = 'Писмото не може да бъде преместено';
$messages['errordeleting'] = 'Писмото не може да бъде изтрито';
$messages['deletecontactconfirm'] = 'Искате ли да изтриете маркираните контакти?';
$messages['deletemessagesconfirm'] = 'Искате ли да изтриете маркираните съобщения?';
$messages['deletefolderconfirm'] = 'Искате ли да изтриете тази папка?';
$messages['purgefolderconfirm'] = 'Искате ли да изтриете всички писма в тази папка?';
$messages['foldercreating'] = 'Създаване на папка...';
$messages['folderdeleting'] = 'Изтриване на папка...';
$messages['folderrenaming'] = 'Преименуване на папка...';
$messages['foldermoving'] = 'Преместване на папка...';
$messages['formincomplete'] = 'Не сте попълнили всички полета';
$messages['noemailwarning'] = 'Моля, въведете валиден e-mail адрес';
$messages['nonamewarning'] = 'Моля, въведете име';
$messages['nopagesizewarning'] = 'Въведете брой редове на страница';
$messages['nosenderwarning'] = 'Моля въведете валиден e-mail адрес на изпращача';
$messages['norecipientwarning'] = 'Въведете поне един получател';
$messages['nosubjectwarning'] = 'Полето "Заглавие" е празно. Искате ли да въведете заглавие?';
$messages['nobodywarning'] = 'Изпрати това писмо без текст?';
$messages['notsentwarning'] = 'Писмото не е изпратено. Искате ли да бъде унищожено?';
$messages['noldapserver'] = 'Изберете LDAP сървър за търсене';
$messages['nocontactsreturned'] = 'Не са намерени контакти';
$messages['nosearchname'] = 'Моля, въведете Име на контакта или e-mail адрес';
$messages['searchsuccessful'] = '$nr намерени писма';
$messages['searchnomatch'] = 'Търсенето не откри съвпадения';
$messages['searching'] = 'Търсене...';
$messages['checking'] = 'Проверка...';
$messages['nospellerrors'] = 'Не са открити правописни грешки';
$messages['folderdeleted'] = 'Папката е изтрита';
$messages['deletedsuccessfully'] = 'Изтриването е успешно';
$messages['converting'] = 'Премахване форматирането на писмото...';
$messages['messageopenerror'] = 'Писмото не може да бъде заредено от сървъра';
$messages['fileuploaderror'] = 'Грешка при прикачването на файла';
$messages['filesizeerror'] = 'Прикаченият файл надвишава лимита от $size';
$messages['copysuccess'] = 'Успешно копирани $nr адреса';
$messages['copyerror'] = 'Грешка при копирането на адресите';
$messages['sourceisreadonly'] = 'Този източник на адреси е само за четене';
$messages['errorsavingcontact'] = 'Грешка при записването на адреса';
$messages['movingmessage'] = 'Преместване на писмото...';
$messages['receiptsent'] = 'Обратната разписка е изпратена.';
$messages['errorsendingreceipt'] = 'Грешка при изпращането на обратна разписка.';
$messages['nodeletelastidentity'] = 'Не можете да изтриете тази самоличност, трябва да имате поне една.';
$messages['addsubfolderhint'] = 'Тази папка ще бъде създадена като подпапка на текущо избраната';
$messages['forbiddencharacter'] = 'Името на папката съдържа непозволени символи';
$messages['selectimportfile'] = 'Моля изберете файл за качване';
$messages['addresswriterror'] = 'Избраната адресна книга не може да бъде записвана';
$messages['importwait'] = 'Внасяне, моля изчакайте...';
$messages['importerror'] = 'Внасянето неуспешно! Каченият файл не е във валиден vCard формат.';
$messages['importconfirm'] = '<b>Успешно са внесени следните контакти $inserted contacts, вече съществуващите $skipped контакти са пропуснати</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Операцията не е позволена!';
$messages['nofromaddress'] = 'Липсва e-mail адрес за избраната самоличност';
$messages['editorwarning'] = 'Превключването на редактора в текстов режим ще доведе до загуба на форматирането на текса. Сигурни ли сте, че искате да продължите?';

?>
