<?php

/*
 +-----------------------------------------------------------------------+
 | localization/sr_CS/messages.inc                                       |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas <Unknown>                                              |
 +-----------------------------------------------------------------------+
 @version $Id$
*/

$messages = array();
$messages['loginfailed'] = 'Неуспешно пријављивање';
$messages['cookiesdisabled'] = 'Ваш интернет прегледач не подржава колачиће';
$messages['sessionerror'] = 'Сесија није валидна или је истекла';
$messages['storageerror'] = 'Неуспешна конекција на IMAP сервер';
$messages['servererror'] = 'Грешка на серверу!';
$messages['servererrormsg'] = 'Грешка на серверу: $msg';
$messages['errorreadonly'] = 'Операција неуспела. Фасицкло је';
$messages['errornoperm'] = 'Извршење операције није могуће. Одбијен захтев за дозволу..';
$messages['invalidrequest'] = 'Погрешан захтев! Подаци нису сачувани.';
$messages['nomessagesfound'] = 'Немате порука у сандучету';
$messages['loggedout'] = 'Успешно сте се одјавили.';
$messages['mailboxempty'] = 'Сандуче је празно';
$messages['loading'] = 'Учитавање...';
$messages['uploading'] = 'Оптремање датотеке...';
$messages['loadingdata'] = 'Учитавање података...';
$messages['checkingmail'] = 'Провера нових порука у току...';
$messages['sendingmessage'] = 'Порука се шаље...';
$messages['messagesent'] = 'Порука је успешно послата';
$messages['savingmessage'] = 'Порука се снима...';
$messages['messagesaved'] = 'Порука успешно сачувана у Снимљене';
$messages['successfullysaved'] = 'Снимање успешно обављено';
$messages['addedsuccessfully'] = 'Контакт успешно додат у именик';
$messages['contactexists'] = 'Контакт са овом е-мејл адресом већ постоји у именику';
$messages['blockedimages'] = 'Због заштите приватности, слике са удаљених сервера су блокиране';
$messages['encryptedmessage'] = 'Ова порука је шифрована, па се не може приказати';
$messages['nocontactsfound'] = 'Није пронађен ниједан контакт';
$messages['contactnotfound'] = 'Жељени контакт није нађен';
$messages['sendingfailed'] = 'Грешка при слању поруке';
$messages['senttooquickly'] = 'Молимо Вас да сачекате $sec секунд(и) пре него пошаљете ову поруку';
$messages['errorsavingsent'] = 'Приликом похрањивања послате поруке десила се грешка';
$messages['errorsaving'] = 'Грешка при снимању поруке';
$messages['errormoving'] = 'Грешка при премештању поруке';
$messages['errorcopying'] = 'Копирање поруке није било успешно';
$messages['errordeleting'] = 'Грешка при брисању поруке';
$messages['errormarking'] = 'Маркирање поруке неуспешно';
$messages['deletecontactconfirm'] = 'Да ли стварно желите да обришете изабране контакте?';
$messages['deletemessagesconfirm'] = 'Да ли стварно желите да обришете изабране поруке?';
$messages['deletefolderconfirm'] = 'Да ли заиста желите да обришете изабрану фасциклу?';
$messages['purgefolderconfirm'] = 'Да ли заиста желите да обришете све поруке у фасцикли?';
$messages['folderdeleting'] = 'Брисање фасцикле...';
$messages['foldermoving'] = 'премештање фасцикле...';
$messages['foldersubscribing'] = 'претплаћивање фасцикле...';
$messages['folderunsubscribing'] = 'одјављивање претплате на фасциклу...';
$messages['formincomplete'] = 'Формулар није у целости попуњен';
$messages['noemailwarning'] = 'Унесите валидну е-мејл адресу';
$messages['nonamewarning'] = 'Унесите име';
$messages['nopagesizewarning'] = 'Унесите величину странице';
$messages['nosenderwarning'] = 'Унесите имејл адресу пошиљаоца';
$messages['norecipientwarning'] = 'Унесите примаоца/примаоце (За)';
$messages['nosubjectwarning'] = 'Поље \'Наслов\' је празно. Желите ли да унесете наслов?';
$messages['nobodywarning'] = 'Да ли желите да пошаљете поруку без текста?';
$messages['notsentwarning'] = 'Порука није послата. Да ли желите да одбаците ову поруку?';
$messages['noldapserver'] = 'Унесите LDAP сервер за претрагу';
$messages['nosearchname'] = 'Унесите име или е-мејл адресу';
$messages['notuploadedwarning'] = 'Нису сви прилози отпремљени. Молимо сачекајте или откажите отпремање.';
$messages['searchsuccessful'] = 'Број пронађених порука: $nr';
$messages['searchnomatch'] = 'Тражена фраза није пронађена ни у једној поруци';
$messages['searching'] = 'Претрага у току...';
$messages['checking'] = 'Провера у току...';
$messages['nospellerrors'] = 'Није пронађена ниједна правописна грешка';
$messages['folderdeleted'] = 'Фасцикла успешно обрисана';
$messages['foldersubscribed'] = 'Фасцикла успешно претплаћена';
$messages['folderunsubscribed'] = 'Фасцикла успешно одјављена од претплате';
$messages['folderpurged'] = 'Фасцикла успешно испражњена';
$messages['folderexpunged'] = 'Фасцикла успешнпо компактирана';
$messages['deletedsuccessfully'] = 'Брисање успешно';
$messages['converting'] = 'Уклањање форматирања из поруке...';
$messages['messageopenerror'] = 'Порука се не може учитати са сервера';
$messages['fileuploaderror'] = 'Слање датотеке неуспешно';
$messages['filesizeerror'] = 'Послата датотека не може да прекорачи величину од $size';
$messages['copysuccess'] = 'Успешно копирано $nr адреса';
$messages['copyerror'] = 'Немогуће је ископирати иједну адресу';
$messages['sourceisreadonly'] = 'Овај извор адресе је само за читање';
$messages['errorsavingcontact'] = 'Неуспело снимање адресе контакта';
$messages['movingmessage'] = 'Премештам поруку...';
$messages['copyingmessage'] = 'Копирам поруке...';
$messages['deletingmessage'] = 'бришем поруке...';
$messages['markingmessage'] = 'Означавам поруке...';
$messages['receiptsent'] = 'Успешно послато обавештење о приспећу';
$messages['errorsendingreceipt'] = 'Неуспело слање обавештења о приспећу';
$messages['nodeletelastidentity'] = 'Не можете обрисати овај идентитет, он Вам је последњи.';
$messages['forbiddencharacter'] = 'Име фасцикле садржи недозвољено слово';
$messages['selectimportfile'] = 'Молимо Вас да изаберете датотеку за отпремање';
$messages['addresswriterror'] = 'У изабраном адресару нису дозвољене промене';
$messages['contactaddedtogroup'] = 'Контакти успешно додати групи';
$messages['contactremovedfromgroup'] = 'Контакти успешно одстрањени из групе';
$messages['importwait'] = 'Увоз података, молимо сачекајте...';
$messages['importerror'] = 'Увоз неуспео! Отпремљена датотека није валидан vCard формат.';
$messages['importconfirm'] = '<b>Успешно увезжени $inserted контакти </b>';
$messages['importconfirmskipped'] = '<b>Прескочени $skipped постојећи уноси </b>';
$messages['opnotpermitted'] = 'Операција није дозвољена!';
$messages['nofromaddress'] = 'Недостаје имејл адреса у изабраном налогу';
$messages['editorwarning'] = 'Пребацивање на обичан текст едитор ће изазвати губитак целог форматирања. Да ли желите да наставите?';
$messages['httpreceivedencrypterror'] = 'Фатална грешка у конфигурацији. Контактирајте одмах Вашег администратора. <b>Ваша порука не може бити послата.</b>';
$messages['smtpconnerror'] = 'SMTP Error ($code): Конекција на сервер није успела';
$messages['smtpautherror'] = 'SMTP Error ($code): Аутентификација неуспешна';
$messages['smtpfromerror'] = 'SMTP Error ($code): Неуспешно додавање пошиљаоца "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP Error ($code): Неуспешно додавање примаоца "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP Error ($code): Није могуће расчланити листу прималаца';
$messages['smtperror'] = 'SMTP Error ($code): $msg';
$messages['emailformaterror'] = 'Имејл адреса није валидна: $email';
$messages['toomanyrecipients'] = 'Превише прималаца. Смањите број прималаца на $max.';
$messages['maxgroupmembersreached'] = 'Број чланова гупе прелази максимум од $max';
$messages['internalerror'] = 'Десила се интерна грешка. Молимо покушајте поново';
$messages['contactdelerror'] = 'Брисање контакта неуспешно';
$messages['contactdeleted'] = 'Брисање контакта успешно';
$messages['groupdeleted'] = 'Група успешно обрисана';
$messages['grouprenamed'] = 'Група успешно преименована';
$messages['groupcreated'] = 'Група успешно креирана';
$messages['messagedeleted'] = 'Поруке успешно обрисане';
$messages['messagemoved'] = 'Поруке успешно премештене';
$messages['messagecopied'] = 'Поруке успешно копиране';
$messages['messagemarked'] = 'Поруке успешно означене';
$messages['autocompletechars'] = 'За аутоматско комплетирање унесите најмање $min карактера';
$messages['namecannotbeempty'] = 'Поље за име не сме бити празно';
$messages['nametoolong'] = 'Предугачко име';
$messages['folderupdated'] = 'фасцикла успешно ажурирана';
$messages['foldercreated'] = 'Фасцикла успешно креирана';
$messages['invalidimageformat'] = 'Формат слике није валидан';

