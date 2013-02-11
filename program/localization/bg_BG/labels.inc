<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
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
$labels['mail']     = 'Кутия';
$labels['settings'] = 'Настройки';
$labels['addressbook'] = 'Адресна книга';

// mailbox names
$labels['inbox']  = 'Входящи';
$labels['drafts'] = 'Чернови';
$labels['sent']   = 'Изпратени';
$labels['trash']  = 'Кошче';
$labels['junk']   = 'Спам';

// message listing
$labels['subject'] = 'Заглавие';
$labels['from']    = 'От';
$labels['sender']  = 'Подател';
$labels['to']      = 'Получател';
$labels['cc']      = 'Копие до';
$labels['bcc']     = 'Скрито копие до';
$labels['replyto'] = 'Отговор до';
$labels['followupto'] = 'Препращане към';
$labels['date']    = 'Дата';
$labels['size']    = 'Размер';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организация';
$labels['readstatus'] = 'Статут (прочетено/ново)';
$labels['listoptions'] = 'Показване на възможни опции';

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Съобщения $from до $to от $count';
$labels['threadsfromto'] = 'Съобщения $from до $to от $count';
$labels['messagenrof'] = 'Съобщение $nr от $count';
$labels['fromtoshort'] = '$from – $to от $count';

$labels['copy']     = 'Копиране';
$labels['move']     = 'Преместване';
$labels['moveto']   = 'Премести в...';
$labels['download'] = 'Изтегли';
$labels['showattachment'] = 'Показване';
$labels['showanyway'] = 'Показване въпреки това';

$labels['filename'] = 'Име на файла';
$labels['filesize'] = 'Размер на файла';

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

// months short
$labels['jan']	= 'Яну';
$labels['feb']	= 'Фев';
$labels['mar']	= 'Мар';
$labels['apr']	= 'Апр';
$labels['may']	= 'Май';
$labels['jun']	= 'Юни';
$labels['jul'] 	= 'Юли';
$labels['aug']	= 'Авг';
$labels['sep']	= 'Сеп';
$labels['oct']	= 'Окт';
$labels['nov']	= 'Ное';
$labels['dec']	= 'Дек';

// months long
$labels['longjan']	= 'Януари';
$labels['longfeb']	= 'Февруари';
$labels['longmar']	= 'Март';
$labels['longapr']	= 'Април';
$labels['longmay']	= 'Май';
$labels['longjun']	= 'Юни';
$labels['longjul']	= 'Юли';
$labels['longaug']	= 'Август';
$labels['longsep']	= 'Септември';
$labels['longoct']	= 'Октомври';
$labels['longnov']	= 'Ноември';
$labels['longdec']	= 'Декември';

$labels['today'] = 'Днес';

// toolbar buttons
$labels['refresh']          = 'Опресняване';
$labels['checkmail']        = 'Провери за нови писма';
$labels['compose']          = 'Ново писмо';
$labels['writenewmessage']  = 'Създай ново писмо';
$labels['reply']            = 'Отговор';
$labels['replytomessage']   = 'Отговори на писмото';
$labels['replytoallmessage'] = 'Отговор до подателя и всички получатели';
$labels['replyall']         = 'Отговор на всички';
$labels['replylist']        = 'Списък за отговор';
$labels['forward']          = 'Препращане';
$labels['forwardinline']    = 'Препрати като част от съобщението';
$labels['forwardattachment'] = 'Препрати като прикачен файл';
$labels['forwardmessage']   = 'Препрати писмото';
$labels['deletemessage']    = 'Изтрий писмото';
$labels['movemessagetotrash'] = 'Премести писмото в кошчето';
$labels['printmessage']     = 'Разпечатай писмото';
$labels['previousmessage']  = 'Предишно писмо';
$labels['firstmessage']     = 'Първо писмо';
$labels['nextmessage']      = 'Следващо писмо';
$labels['lastmessage']      = 'Последно писмо';
$labels['backtolist']       = 'Обратно към списъка';
$labels['viewsource']       = 'Виж като код';
$labels['mark']             = 'Маркирарне';
$labels['markmessages']     = 'Маркирай писмата';
$labels['markread']         = 'Като прочетени';
$labels['markunread']       = 'Като нови';
$labels['markflagged']      = 'Като отбелязани';
$labels['markunflagged']    = 'Като неотбелязани';
$labels['moreactions']      = 'Повече действия...';
$labels['more']             = 'Повече';
$labels['back']             = 'Назад';
$labels['options']          = 'Опции';

$labels['select'] = 'Избери';
$labels['all'] = 'Всички';
$labels['none'] = 'Няма';
$labels['currpage'] = 'Страница';
$labels['unread'] = 'Нови';
$labels['flagged'] = 'Отбелязано';
$labels['unanswered'] = 'Без отговор';
$labels['deleted'] = 'Изтрито';
$labels['undeleted'] = 'Не е изтрит';
$labels['invert'] = 'Инвертирай';
$labels['filter'] = 'Филтър';
$labels['list'] = 'Списък';
$labels['threads'] = 'Съобщения';
$labels['expand-all'] = 'Отваряне на всички';
$labels['expand-unread'] = 'Отваряне на непрочетени';
$labels['collapse-all'] = 'Затваряне на всички';
$labels['threaded'] = 'Съобщения с отговори';

$labels['autoexpand_threads'] = 'Отваряне на съобщения и отговори';
$labels['do_expand'] = 'всички отговори';
$labels['expand_only_unread'] = 'само с непрочетени съобщения';
$labels['fromto'] = 'Изпращащ/Получаващ';
$labels['flag'] = 'Флаг';
$labels['attachment'] = 'Прикачен файл';
$labels['nonesort'] = 'Никакъв';
$labels['sentdate'] = 'Дата на изпращане';
$labels['arrival'] = 'Дата на пристигане';
$labels['asc'] = 'възходящ';
$labels['desc'] = 'низходящ';
$labels['listcolumns'] = 'Колони за списък';
$labels['listsorting'] = 'Колона за сортиране';
$labels['listorder'] = 'Режим на сортиране';
$labels['listmode'] = 'Кратък списък';

$labels['folderactions'] = 'Действия за папки...';
$labels['compact'] = 'Свиване';
$labels['empty'] = 'Изпразни';

$labels['quota'] = 'Използвано място';
$labels['unknown']  = 'няма информация';
$labels['unlimited']  = 'няма ограничение';

$labels['quicksearch']  = 'Бързо търсене';
$labels['resetsearch']  = 'Изчисти търсенето и покажи всички писма';
$labels['searchmod']  = 'Търсене във';
$labels['msgtext']  = 'Цялото съобщение';
$labels['body']  = 'Основен текст';

$labels['openinextwin'] = 'Отвори в нов прозорец';
$labels['emlsave'] = 'Изтегли като .eml';

// message compose
$labels['editasnew']      = 'Промени като ново';
$labels['send']           = 'Изпращане';
$labels['sendmessage']    = 'Изпрати писмото';
$labels['savemessage']    = 'Запиши в Чернови';
$labels['addattachment']  = 'Прикачи файл';
$labels['charset']        = 'Кодировка';
$labels['editortype']     = 'Вид редактор';
$labels['returnreceipt']  = 'Обратна разписка';
$labels['dsn']            = 'Информиране при доставка';
$labels['mailreplyintro'] = 'На $date, $sender написа:';
$labels['originalmessage'] = 'Първоначалното съобщение';

$labels['editidents']    = 'Редактиране на самоличности';
$labels['spellcheck']    = 'Спелуване';
$labels['checkspelling'] = 'Провери правописа';
$labels['resumeediting'] = 'Продължи черновата';
$labels['revertto']      = 'Върни се към';

$labels['attach'] = 'Прикачване';
$labels['attachments'] = 'Прикачени файлове';
$labels['upload'] = 'Качи';
$labels['uploadprogress'] = '$percent ($current от $total)';
$labels['close']  = 'Затвори';
$labels['messageoptions']  = 'Настройки на съобщения...';

$labels['low']     = 'Нисък';
$labels['lowest']  = 'Най-нисък';
$labels['normal']  = 'Нормален';
$labels['high']    = 'Висок';
$labels['highest'] = 'Най-висок';

$labels['nosubject']  = '(няма заглавие)';
$labels['showimages'] = 'Показвай изображения';
$labels['alwaysshow'] = 'Винаги показвай изображения от $';
$labels['isdraft']    = 'Това съобщение е чернова';
$labels['andnmore']   = '$nr повече...';
$labels['togglemoreheaders'] = 'Показва още съобщения';
$labels['togglefullheaders'] = 'Само новите съобщения';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Текстово';
$labels['savesentmessagein'] = 'Запази съобщението в';
$labels['dontsave'] = 'Не съхранявай';
$labels['maxuploadsize'] = 'Максимално позволен размер $size';

$labels['addcc'] = 'Копие до';
$labels['addbcc'] = 'Скрито копие до';
$labels['addreplyto'] = 'Отговор на';
$labels['addfollowupto'] = 'Добавяне на препращане към';

// mdn
$labels['mdnrequest'] = 'Подателят на писмото е пожелал да бъде уведомен, че сте го прочели. Желаете ли да изпратите обратна разписка?';
$labels['receiptread'] = 'Обратна разписка (прочетено)';
$labels['yourmessage'] = 'Това е обратна разписка за писмото Ви';
$labels['receiptnote'] = 'Забележка: Тази разписка потвърждава само, че писмото е било показано на компютъра на получателят. Няма никаква гаранция, че получателят е прочел и/или разбрал съдържанието на писмото.';

// address boook
$labels['name']         = 'Екранно име';
$labels['firstname']    = 'Име';
$labels['surname']      = 'Фамилия';
$labels['middlename']   = 'Презиме';
$labels['nameprefix']   = 'Представка (Титла)';
$labels['namesuffix']   = 'Наставка (Титла)';
$labels['nickname']     = 'Псевдоним';
$labels['jobtitle']     = 'Длъжност';
$labels['department']   = 'Отдел';
$labels['gender']       = 'Пол';
$labels['maidenname']   = 'Бащино фамилия';
$labels['email']        = 'E-Mail';
$labels['phone']        = 'Телефон';
$labels['address']      = 'Адрес';
$labels['street']       = 'Улица';
$labels['locality']     = 'Град';
$labels['zipcode']      = 'Пощенски код';
$labels['region']       = 'Област';
$labels['country']      = 'Държава';
$labels['birthday']     = 'Рожден ден';
$labels['anniversary']  = 'Годишнина';
$labels['website']      = 'Сайт';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Бележки';
$labels['male']   = 'Мъж';
$labels['female'] = 'Жена';
$labels['manager'] = 'Мениджър';
$labels['assistant'] = 'Асистент';
$labels['spouse'] = 'Съпруг(а)';
$labels['allfields'] = 'Всички полета';
$labels['search'] = 'Търсене';
$labels['advsearch'] = 'Разширено търсене';
$labels['advanced'] = 'Разширени';
$labels['other'] = 'Други';

$labels['typehome']   = 'Начало';
$labels['typework']   = 'Работа';
$labels['typeother']  = 'Други';
$labels['typemobile']  = 'Мобилни';
$labels['typemain']  = 'Главни';
$labels['typehomefax']  = 'Домашен факс';
$labels['typeworkfax']  = 'Служебен факс';
$labels['typecar']  = 'Автомобил';
$labels['typepager']  = 'Пейджър';
$labels['typevideo']  = 'Видео';
$labels['typeassistant']  = 'Асистент';
$labels['typehomepage']  = 'Начална страница';
$labels['typeblog'] = 'Блог';
$labels['typeprofile'] = 'Профил';

$labels['addfield'] = 'Добави поле';
$labels['addcontact'] = 'Добавяне';
$labels['editcontact'] = 'Промяна';
$labels['contacts'] = 'Контакти';
$labels['contactproperties'] = 'Свойства на контакт';
$labels['personalinfo'] = 'Лична информация';

$labels['edit']   = 'Редактирай';
$labels['cancel'] = 'Отказ';
$labels['save']   = 'Запиши';
$labels['delete'] = 'Изтрий';
$labels['rename'] = 'Преименувай';
$labels['addphoto'] = 'Добави';
$labels['replacephoto'] = 'Замени';
$labels['uploadphoto'] = 'Качване на снимка';

$labels['newcontact']     = 'Нов контакт';
$labels['deletecontact']  = 'Изтрий маркираните контакти';
$labels['composeto']      = 'Напиши писмо до';
$labels['contactsfromto'] = 'Контакти $from до $to от $count';
$labels['print']          = 'Разпечатай';
$labels['export']         = 'Изнасяне';
$labels['exportall']      = 'Експортиране';
$labels['exportsel']      = 'Експортиране на избраното';
$labels['exportvcards']   = 'Изнасяне във vCard формат';
$labels['newcontactgroup'] = 'Създаване на нова група';
$labels['grouprename']    = 'Преименувай група';
$labels['groupdelete']    = 'Изтриване на група';
$labels['groupremoveselected'] = 'Премахване на избраните контакти от група';

$labels['previouspage']   = 'Предна страница';
$labels['firstpage']      = 'Първа страница';
$labels['nextpage']       = 'Следваща страница';
$labels['lastpage']       = 'Последна страница';

$labels['group'] = 'Група';
$labels['groups'] = 'Групи';
$labels['personaladrbook'] = 'Лични адреси';

$labels['searchsave'] = 'Запази търсенето';
$labels['searchdelete'] = 'Изтрий търсенето';

$labels['import'] = 'Внасяне';
$labels['importcontacts'] = 'Внасяне';
$labels['importfromfile'] = 'Внасяне от файл';
$labels['importtarget'] = 'Добави нови контакти в книгата с адреси';
$labels['importreplace'] = 'Замени цялата книга с адреси';
$labels['importdesc'] = 'Можете да качвате контакти от съществуваща адресна книга.<br/>В момента поддържаме внасяне на адреси от файловите формати <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> или CSV (comma-separated).';
$labels['done'] = 'Извършено';

// settings
$labels['settingsfor'] = 'Настройки за';
$labels['about'] = 'Относно';
$labels['preferences'] = 'Настройки';
$labels['userpreferences'] = 'Потребителски настройки';
$labels['editpreferences'] = 'Редактирай потребителските настройки';

$labels['identities'] = 'Самоличност';
$labels['manageidentities'] = 'Промяна на самоличностите за този акаунт';
$labels['newidentity'] = 'Нова самоличност';

$labels['newitem'] = 'Нова самоличност';
$labels['edititem'] = 'Редактиране на самоличност';

$labels['preferhtml'] = 'Показвай първо HTML версия';
$labels['defaultcharset'] = 'Подразбиращо се кодиране';
$labels['htmlmessage'] = 'HTML съобщение';
$labels['messagepart'] = 'Част';
$labels['digitalsig'] = 'Показва подпис';
$labels['dateformat'] = 'Формат на датата';
$labels['timeformat'] = 'Формат на времето';
$labels['prettydate'] = 'Кратки дати';
$labels['setdefault']  = 'По подразбиране';
$labels['autodetect']  = 'Автоматично';
$labels['language']  = 'Език';
$labels['timezone']  = 'Часова зона';
$labels['pagesize']  = 'Редове на страница';
$labels['signature'] = 'Подпис';
$labels['dstactive']  = 'Маркирай при лятно време';
$labels['showinextwin'] = 'Отваряне на съобщението в нов прозорец';
$labels['composeextwin'] = 'Ново писмо в прозорец';
$labels['htmleditor'] = 'Писане на съобщениe като HTML';
$labels['htmlonreply'] = 'само при отговор на HTML събощения';
$labels['htmlonreplyandforward'] = 'само при препращане или отговор на HTML съобщение';
$labels['htmlsignature'] = 'HTML подпис';
$labels['previewpane'] = 'Панел за преглед';
$labels['skin'] = 'Смяна изгледа на интерфейса';
$labels['logoutclear'] = 'Изчисти кошчето при изход';
$labels['logoutcompact'] = 'Упллътни Входящи при изход';
$labels['uisettings'] = 'Потребителски интерфейс';
$labels['serversettings'] = 'Настройки на сървъра';
$labels['mailboxview'] = 'Преглед на Кутия';
$labels['mdnrequests'] = 'Уведомление на подателя';
$labels['askuser'] = 'Задай въпрос';
$labels['autosend'] = 'Изпрати автоматично';
$labels['autosendknown'] = 'да се изпрати обратна разписка на моите контакти и да се пита при външни контакти';
$labels['autosendknownignore'] = 'да се изпрати обратна разписка на моите контакти, а на външни не';
$labels['ignore'] = 'Отхвърли';
$labels['readwhendeleted'] = 'Отбележи като прочетено при изтриване';
$labels['flagfordeletion'] = 'Отбележи съобщението за изтриване';
$labels['skipdeleted'] = 'Не показвай изтритите съобщения';
$labels['deletealways'] = 'При неуспешно преместване на съобщение в Кошчето, да се изтрие';
$labels['deletejunk'] = 'Местене на изтритите съобщения в кошчето';
$labels['showremoteimages'] = 'Покажи блокираните изображения';
$labels['fromknownsenders'] = 'Oт познати податели';
$labels['always'] = 'Винаги';
$labels['showinlineimages'] = 'Покажи прикачените изображения след съобщението';
$labels['autosavedraft']  = 'Автоматично записвай чернова';
$labels['everynminutes']  = 'всеки $n минути';
$labels['refreshinterval']  = 'Опресняване (проверка за нови съобщения и т.н.)';
$labels['never']  = 'никога';
$labels['immediately']  = 'Веднага';
$labels['messagesdisplaying'] = 'Показване на събщенията';
$labels['messagescomposition'] = 'Писане на съобщения';
$labels['mimeparamfolding'] = 'Добавяне име на прикрепените файлове';
$labels['2231folding'] = 'Според RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Според RFC 2047 (други)';
$labels['force7bit'] = 'Използване на MIME кодиране за 8-битови символи';
$labels['advancedoptions'] = 'Настройки за напреднали';
$labels['focusonnewmessage'] = 'Премигване на прозореца при ново писмо';
$labels['checkallfolders'] = 'Провери всички папки за нови писма';
$labels['displaynext'] = 'След изтриване / премини към следващото писмо';
$labels['defaultfont'] = 'Шрифт по подразбиране за HTML съобщенията';
$labels['mainoptions'] = 'Основни настройки';
$labels['browseroptions'] = 'Настройки на четеца';
$labels['section'] = 'Секция';
$labels['maintenance'] = 'Поддръжка';
$labels['newmessage'] = 'Ново съобщение';
$labels['signatureoptions'] = 'Настройки на подпис';
$labels['whenreplying'] = 'При отговор';
$labels['replyempty'] = 'да не се цитира оригиналното съобщение';
$labels['replytopposting'] = 'започване на ново съобщение преди получения текст';
$labels['replybottomposting'] = 'започване на ново съобщение след получения текст';
$labels['replyremovesignature'] = 'Премахване на предишния подпис от съобщението при отговор';
$labels['autoaddsignature'] = 'Автоматично добавяне на подпис';
$labels['newmessageonly'] = 'само на нови съобщения';
$labels['replyandforwardonly'] = 'само на отговори и препратени';
$labels['replysignaturepos'] = 'Поставяне на подпис при отговор или препращане';
$labels['belowquote'] = 'над цитатът';
$labels['abovequote'] = 'под цитатът';
$labels['insertsignature'] = 'Вмъкване на подпис';
$labels['previewpanemarkread']  = 'Маркиране на прегледаните съобщения като прочетени';
$labels['afternseconds']  = 'след $n секунди';
$labels['reqmdn'] = 'Винаги връщай обратна разписка';
$labels['reqdsn'] = 'Винаги изисквай отговор при доставка на съобщение';
$labels['replysamefolder'] = 'Постави отговора в папката на съобщението, на което се отговаря';
$labels['defaultabook'] = 'Адресна книга по подразбиране';
$labels['autocompletesingle'] = 'Пропусни алтернативните email адреси при автоматично попълване';
$labels['listnamedisplay'] = 'Показване на контактите като';
$labels['spellcheckbeforesend'] = 'Провери за правописни грешки преди изпращане на съобщението';
$labels['spellcheckoptions'] = 'Настройки на проверката за правопис';
$labels['spellcheckignoresyms'] = 'Игнорирай думи съдържащи символи';
$labels['spellcheckignorenums'] = 'Игнорирай думи съдържащи числа';
$labels['spellcheckignorecaps'] = 'Игнорирай думи съдържащи единствено главни букви';
$labels['addtodict'] = 'Добави в речника';
$labels['mailtoprotohandler'] = 'Регистриран протокол за mailto: връзките';
$labels['forwardmode'] = 'Препращане на съобщения';
$labels['inline'] = 'вградено';
$labels['asattachment'] = 'като прикачен файл';

$labels['folder']  = 'Папка';
$labels['folders']  = 'Папки';
$labels['foldername']  = 'Име на папката';
$labels['subscribed']  = 'Използвай';
$labels['messagecount'] = 'Писма';
$labels['create']  = 'Създай';
$labels['createfolder']  = 'Направи нова папка';
$labels['managefolders']  = 'Настройки на папките';
$labels['specialfolders'] = 'Служебни папки';
$labels['properties'] = 'Свойства';
$labels['folderproperties'] = 'Свойства на папката';
$labels['parentfolder'] = 'Родителска папка';
$labels['location'] = 'Местоположение';
$labels['info'] = 'Информация';
$labels['getfoldersize'] = 'Щракни за големина на папката';
$labels['changesubscription'] = 'Щракни за промяна на абонамент';
$labels['foldertype'] = 'Вид на папката';
$labels['personalfolder']  = 'Лична папка';
$labels['otherfolder']  = 'Папка на друг потребител';
$labels['sharedfolder']  = 'Публична папка';

$labels['sortby'] = 'Сортирай по';
$labels['sortasc']  = 'Сортирай възходящо';
$labels['sortdesc'] = 'Сортирай низходящо';
$labels['undo'] = 'Отмени';

$labels['installedplugins'] = 'Инсталирани добавки';
$labels['plugin'] = 'Добавки';
$labels['version'] = 'Версия';
$labels['source'] = 'Източник';
$labels['license'] = 'Лиценз';
$labels['support'] = 'Поддръжка';

// units
$labels['B'] = 'Б';
$labels['KB'] = 'КБ';
$labels['MB'] = 'МБ';
$labels['GB'] = 'ГБ';

// character sets
$labels['unicode'] = 'Уникод';
$labels['english'] = 'Английски';
$labels['westerneuropean'] = 'Западна Европа';
$labels['easterneuropean'] = 'Източна Европа';
$labels['southeasterneuropean'] = 'Югоизточна Европа';
$labels['baltic'] = 'Балтийски';
$labels['cyrillic'] = 'Кирилица';
$labels['arabic'] = 'Арабски';
$labels['greek'] = 'Гръцки';
$labels['hebrew'] = 'Иврит';
$labels['turkish'] = 'Турски';
$labels['nordic'] = 'Скандинавски';
$labels['thai'] = 'Тайландски';
$labels['celtic'] = 'Келтски';
$labels['vietnamese'] = 'Виетнамски';
$labels['japanese'] = 'Японски';
$labels['korean'] = 'Корейски';
$labels['chinese'] = 'Китайски';

?>
