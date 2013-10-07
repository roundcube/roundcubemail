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
$labels['welcome']  = 'Ласкаво просимо!';
$labels['username'] = 'Ім\'я користувача';
$labels['password'] = 'Пароль';
$labels['server']   = 'Сервер';
$labels['login']    = 'Увійти';

// taskbar
$labels['logout']   = 'Вийти';
$labels['mail']     = 'Пошта';
$labels['settings'] = 'Налаштування';
$labels['addressbook'] = 'Контакти';

// mailbox names
$labels['inbox']  = 'Вхідні';
$labels['drafts'] = 'Чернетки';
$labels['sent']   = 'Надіслані';
$labels['trash']  = 'Видалені';
$labels['junk']   = 'Спам';

// message listing
$labels['subject']  = 'Тема';
$labels['from']     = 'Від';
$labels['sender']   = 'Відправник';
$labels['to']       = 'Кому';
$labels['cc']       = 'Копія';
$labels['bcc']      = 'Прихована';
$labels['replyto']  = 'Зворотня адреса';
$labels['followupto'] = 'У відповідь';
$labels['date']     = 'Дата';
$labels['size']     = 'Розмір';
$labels['priority'] = 'Пріоритет';
$labels['organization'] = 'Організація';
$labels['readstatus'] = 'Статус прочитання';
$labels['listoptions'] = 'Налаштування вигляду...';

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Лист з $from по $to із $count';
$labels['threadsfromto'] = 'Обговорення з $from по $to із $count';
$labels['messagenrof'] = 'Лист $nr із $count';
$labels['fromtoshort'] = '$from – $to з $count';

$labels['copy']     = 'Копіювати';
$labels['move']     = 'Перемістити';
$labels['moveto']   = 'Перемістити лист у...';
$labels['download'] = 'Завантажити';
$labels['open']     = 'Відкрити';
$labels['showattachment'] = 'Показати';
$labels['showanyway'] = 'Все одно показати';

$labels['filename'] = 'Ім\'я файлу';
$labels['filesize'] = 'Розмір файлу';

$labels['addtoaddressbook'] = 'Додати до контактів';

// weekdays short
$labels['sun'] = 'Нд';
$labels['mon'] = 'Пн';
$labels['tue'] = 'Вт';
$labels['wed'] = 'Ср';
$labels['thu'] = 'Чт';
$labels['fri'] = 'Пт';
$labels['sat'] = 'Сб';

// weekdays long
$labels['sunday']    = 'Неділя';
$labels['monday']    = 'Понеділок';
$labels['tuesday']   = 'Вівторок';
$labels['wednesday'] = 'Середа';
$labels['thursday']  = 'Четвер';
$labels['friday']    = 'П\'ятниця';
$labels['saturday']  = 'Субота';

// months short
$labels['jan'] = 'січ';
$labels['feb'] = 'лют';
$labels['mar'] = 'бер';
$labels['apr'] = 'кві';
$labels['may'] = 'тра';
$labels['jun'] = 'чер';
$labels['jul'] = 'лип';
$labels['aug'] = 'сер';
$labels['sep'] = 'вер';
$labels['oct'] = 'жов';
$labels['nov'] = 'лис';
$labels['dec'] = 'гру';

// months long
$labels['longjan'] = 'Січень';
$labels['longfeb'] = 'Лютий';
$labels['longmar'] = 'Березень';
$labels['longapr'] = 'Квітень';
$labels['longmay'] = 'Травень';
$labels['longjun'] = 'Червень';
$labels['longjul'] = 'Липень';
$labels['longaug'] = 'Серпень';
$labels['longsep'] = 'Вересень';
$labels['longoct'] = 'Жовтень';
$labels['longnov'] = 'Листопад';
$labels['longdec'] = 'Грудень';

$labels['today'] = 'Сьогодні';

// toolbar buttons
$labels['refresh']        = 'Оновити';
$labels['checkmail']      = 'Перевірити пошту';
$labels['compose']        = 'Написати листа';
$labels['writenewmessage'] = 'Написати листа';
$labels['reply']          = 'Відповісти';
$labels['replytomessage'] = 'Відповісти відправнику';
$labels['replytoallmessage'] = 'Відповісти відправнику та усім отримувачам';
$labels['replyall']       = 'Відповісти усім';
$labels['replylist']      = 'Відповісти усім отримувачам';
$labels['forward']        = 'Переслати';
$labels['forwardinline']  = 'Переслати у тілі листа';
$labels['forwardattachment'] = 'Переслати як додаток';
$labels['forwardmessage'] = 'Переслати лист';
$labels['deletemessage']  = 'Видалити';
$labels['movemessagetotrash'] = 'Перемістити у Видалені';
$labels['printmessage']   = 'Друкувати';
$labels['previousmessage'] = 'Показати попередній лист';
$labels['firstmessage']   = 'Показати перший лист';
$labels['nextmessage']    = 'Показати наступний лист';
$labels['lastmessage']    = 'Показати останній лист';
$labels['backtolist']     = 'До переліку листів';
$labels['viewsource']     = 'Вихідний текст';
$labels['mark']           = 'Позначити';
$labels['markmessages']   = 'Позначити листи';
$labels['markread']       = 'Позначити як прочитаний';
$labels['markunread']     = 'Позначити як непрочитаний';
$labels['markflagged']    = 'Поставити позначку';
$labels['markunflagged']  = 'Зняти позначку';
$labels['moreactions']    = 'Інші дії...';
$labels['more']           = 'Ще';
$labels['back']           = 'Назад';
$labels['options']        = 'Параметри';

$labels['select']    = 'Вибрати';
$labels['all']       = 'Усі';
$labels['none']      = 'Не сортувати';
$labels['currpage']  = 'Поточна сторінка';
$labels['unread']    = 'Непрочитані';
$labels['flagged']   = 'З позначкою';
$labels['unanswered'] = 'Без відповіді';
$labels['withattachment'] = 'З додатком';
$labels['deleted']   = 'Видалені';
$labels['undeleted'] = 'Не видалені';
$labels['invert']    = 'Інвертувати вибір';
$labels['filter']    = 'Фільтр';
$labels['list']      = 'Список';
$labels['threads']   = 'Гілки';
$labels['expand-all'] = 'Розгорнути усі';
$labels['expand-unread'] = 'Розгорнути непрочитані';
$labels['collapse-all'] = 'Згорнути усі';
$labels['threaded']  = 'Гілками';

$labels['autoexpand_threads'] = 'Автоматично розгортати гілки';
$labels['do_expand'] = 'усі гілки';
$labels['expand_only_unread'] = 'тільки з непрочитаними листами';
$labels['fromto']    = 'Відправник/Одержувач';
$labels['flag']      = 'Позначка';
$labels['attachment'] = 'Додаток';
$labels['nonesort']  = 'Не сортувати';
$labels['sentdate']  = 'Дата відправлення';
$labels['arrival']   = 'Дата отримання';
$labels['asc']       = 'За зростанням';
$labels['desc']      = 'За спаданням';
$labels['listcolumns'] = 'Атрибути для відображення';
$labels['listsorting'] = 'Сортувати за атрибутом';
$labels['listorder'] = 'Порядок сортування';
$labels['listmode']  = 'Режим перегляду';

$labels['folderactions'] = 'Операції з папкою...';
$labels['compact'] = 'Стиснути';
$labels['empty'] = 'Спорожнити';

$labels['quota'] = 'Використано';
$labels['unknown'] = 'невідомо';
$labels['unlimited'] = 'без обмеження';

$labels['quicksearch'] = 'Швидкий пошук';
$labels['resetsearch'] = 'Очистити пошук';
$labels['searchmod'] = 'Де шукати';
$labels['msgtext'] = 'Весь лист';
$labels['body'] = 'Текст листа';

$labels['openinextwin'] = 'Відкрити у новому вікні';
$labels['emlsave'] = 'Зберегти (.eml)';
$labels['changeformattext'] = 'Показати як звичайний текст';
$labels['changeformathtml'] = 'Показати як HTML';

// message compose
$labels['editasnew']     = 'Редагувати як новий';
$labels['send']          = 'Відправлено';
$labels['sendmessage']   = 'Надіслати зараз';
$labels['savemessage']   = 'Зберегти чернетку';
$labels['addattachment'] = 'Додати файл';
$labels['charset']       = 'Кодування';
$labels['editortype']    = 'Редактор';
$labels['returnreceipt'] = 'Повідомити про прочитання';
$labels['dsn']           = 'Повідомити про доставку';
$labels['mailreplyintro'] = '$date, $sender написав:';
$labels['originalmessage'] = 'Оригінальний лист';

$labels['editidents'] = 'Змінити дані';
$labels['spellcheck'] = 'Орфографія';
$labels['checkspelling'] = 'Перевірити орфографію';
$labels['resumeediting'] = 'Продовжити редагування';
$labels['revertto'] = 'Відмінити редагування';

$labels['attach'] = 'Додати';
$labels['attachments'] = 'Додані файли';
$labels['upload'] = 'Завантажити';
$labels['uploadprogress'] = '$percent ($current з $total)';
$labels['close'] = 'Закрити';
$labels['messageoptions'] = 'Налаштування листа';

$labels['low']     = 'низький';
$labels['lowest']  = 'найнижчий';
$labels['normal']  = 'нормальний';
$labels['high']    = 'високий';
$labels['highest'] = 'найвищий';

$labels['nosubject']  = '(без теми)';
$labels['showimages'] = 'Показувати зображення';
$labels['alwaysshow'] = 'Завжди показувати зображення від $sender';
$labels['isdraft']    = 'Це чернетка.';
$labels['andnmore']   = 'ще $nr...';
$labels['togglemoreheaders'] = 'Показати більше заголовків листа';
$labels['togglefullheaders'] = 'Показати усі заголовки листа';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Звичайний текст';
$labels['savesentmessagein'] = 'Зберегти надісланого листа у';
$labels['dontsave'] = 'не зберігати';
$labels['maxuploadsize'] = 'Максимальний розмір файлу $size';

$labels['addcc'] = 'Додати копію';
$labels['addbcc'] = 'Додати приховану копію';
$labels['addreplyto'] = 'Додати зворотню адресу';
$labels['addfollowupto'] = 'Додати у відповідь';

// mdn
$labels['mdnrequest'] = 'Відправник цього листа запитує про його прочитання. Повідомити відправника?';
$labels['receiptread'] = 'Повідомлення про прочитання';
$labels['yourmessage'] = 'Повідомлення про прочитання Вашого листа';
$labels['receiptnote'] = 'Зауваження: Це повідомлення означає лише те, що листа було відкрито одержувачем, і не гарантує того, що його було прочитано.';

// address boook
$labels['name']       = 'Ім\'я для відображення';
$labels['firstname']  = 'Ім\'я';
$labels['surname']    = 'Прізвище';
$labels['middlename'] = 'По-батькові';
$labels['nameprefix'] = 'Префікс';
$labels['namesuffix'] = 'Суфікс';
$labels['nickname']   = 'Прізвисько';
$labels['jobtitle']   = 'Посада';
$labels['department'] = 'Відділ';
$labels['gender']     = 'Стать';
$labels['maidenname'] = 'Дівоче прізвище';
$labels['email']      = 'Електронна адреса';
$labels['phone']      = 'Телефон';
$labels['address']    = 'Адреса';
$labels['street']     = 'Вулиця';
$labels['locality']   = 'Місто';
$labels['zipcode']    = 'Індекс';
$labels['region']     = 'Область';
$labels['country']    = 'Країна';
$labels['birthday']   = 'День народження';
$labels['anniversary'] = 'Ювілей';
$labels['website']    = 'Веб-сайт';
$labels['instantmessenger'] = 'IM';
$labels['notes']      = 'Примітки';
$labels['male']       = 'чоловіча';
$labels['female']     = 'жіноча';
$labels['manager']    = 'Менеджер';
$labels['assistant']  = 'Помічник';
$labels['spouse']     = 'У шлюбі з';
$labels['allfields']  = 'Усі поля';
$labels['search']     = 'Пошук';
$labels['advsearch']  = 'Розширений пошук';
$labels['advanced']   = 'Додатково';
$labels['other']      = 'Інше';

$labels['typehome']   = 'Дім';
$labels['typework']   = 'Робота';
$labels['typeother']  = 'Інше';
$labels['typemobile'] = 'Мобільний';
$labels['typemain']   = 'Основний';
$labels['typehomefax'] = 'Домашній факс';
$labels['typeworkfax'] = 'Робочий факс';
$labels['typecar']    = 'Автомобіль';
$labels['typepager']  = 'Пейджер';
$labels['typevideo']  = 'Відео';
$labels['typeassistant'] = 'Помічник';
$labels['typehomepage'] = 'Домашня сторінка';
$labels['typeblog']   = 'Блог';
$labels['typeprofile'] = 'Профіль';

$labels['addfield'] = 'Додати поле...';
$labels['addcontact'] = 'Додати обрані контакти до списку контактів';
$labels['editcontact'] = 'Редагувати контакт';
$labels['contacts'] = 'Контакти';
$labels['contactproperties'] = 'Властивості контакту';
$labels['personalinfo'] = 'Особова інформація';

$labels['edit']   = 'Правка';
$labels['cancel'] = 'Скасувати';
$labels['save']   = 'Зберегти';
$labels['delete'] = 'Видалити';
$labels['rename'] = 'Перейменувати';
$labels['addphoto'] = 'Додати';
$labels['replacephoto'] = 'Замінити';
$labels['uploadphoto'] = 'Завантажити фотографію';

$labels['newcontact']    = 'Створити новий контакт';
$labels['deletecontact'] = 'Видалити обрані контакти';
$labels['composeto']     = 'Написати листа до обраних контактів';
$labels['contactsfromto'] = 'Контакти  $from - $to / $count';
$labels['print']         = 'Друкувати';
$labels['export']        = 'Експортувати';
$labels['exportall']     = 'Експортувати все';
$labels['exportsel']     = 'Експортувати обрані';
$labels['exportvcards']  = 'Експортувати контакти у формат vCard';
$labels['newcontactgroup'] = 'Створити нову групу контактів';
$labels['grouprename']   = 'Перейменувати групу';
$labels['groupdelete']   = 'Видалити групу';
$labels['groupremoveselected'] = 'Видалити обрані контакти з групи';

$labels['previouspage'] = 'Попередня сторінка';
$labels['firstpage'] = 'Перша сторінка';
$labels['nextpage'] = 'Наступна сторінка';
$labels['lastpage'] = 'Остання сторінка';

$labels['group'] = 'Група';
$labels['groups'] = 'Групи';
$labels['personaladrbook'] = 'Персональні адреси';

$labels['searchsave'] = 'Зберегти пошуковий запит';
$labels['searchdelete'] = 'Видалити пошуковий запит';

$labels['import'] = 'Імпорт';
$labels['importcontacts'] = 'Імпортувати контакти';
$labels['importfromfile'] = 'Імпортувати з файлу:';
$labels['importtarget'] = 'Додати нові контакти до адресної книги';
$labels['importreplace'] = 'Замінити всю адресну книгу';
$labels['importdesc'] = 'Ви можете завантажити контакти з існуючої адресної книги.<br/>В даний час ми підтримуємо імпорт адрес у форматі візитної картки <a href="http://en.wikipedia.org/wiki/VCard"> vCard</ a> або CSV (дані розділені комами).';
$labels['done'] = 'Готово';

// settings
$labels['settingsfor'] = 'Налаштування для';
$labels['about'] = 'Про програму';
$labels['preferences'] = 'Параметри';
$labels['userpreferences'] = 'Налаштування користувача';
$labels['editpreferences'] = 'Редагувати налаштування користувача';

$labels['identities'] = 'Профілі';
$labels['manageidentities'] = 'Керування профілями';
$labels['newidentity'] = 'Новий профіль';

$labels['newitem'] = 'Новий';
$labels['edititem'] = 'Редагувати';

$labels['preferhtml'] = 'Показувати у HTML';
$labels['defaultcharset'] = 'Кодування за замовчуванням';
$labels['htmlmessage'] = 'Лист у HTML';
$labels['messagepart'] = 'Частина';
$labels['digitalsig'] = 'Цифровий підпис';
$labels['dateformat'] = 'Формат дати';
$labels['timeformat'] = 'Формат часу';
$labels['prettydate'] = 'Дати у зручному форматі';
$labels['setdefault'] = 'Встановити за замовчуванням';
$labels['autodetect'] = 'визначити автоматично';
$labels['language'] = 'Мова';
$labels['timezone'] = 'Часовий пояс';
$labels['pagesize'] = 'Рядків на сторінці';
$labels['signature'] = 'Підпис';
$labels['dstactive'] = 'Літній/зимовий час';
$labels['showinextwin'] = 'Відкрити лист у новому вікні';
$labels['composeextwin'] = 'Створити у новому вікні';
$labels['htmleditor'] = 'Створювати листи у HTML';
$labels['htmlonreply'] = 'тільки у відповідь на лист у HTML';
$labels['htmlonreplyandforward'] = 'під час пересилання або відповіді на лист у HTML';
$labels['htmlsignature'] = 'Підпис у HTML';
$labels['showemail'] = 'Показати адресу з ім\'ям для відображення';
$labels['previewpane'] = 'Показати панель перегляду';
$labels['skin'] = 'Тема';
$labels['logoutclear'] = 'Очищати папку Видалені при виході';
$labels['logoutcompact'] = 'Стискати папку Вхідні при завершенні';
$labels['uisettings'] = 'Інтерфейс користувача';
$labels['serversettings'] = 'Налаштування сервера';
$labels['mailboxview'] = 'Вид інтерфейсу';
$labels['mdnrequests'] = 'Повідомлення про прочитання';
$labels['askuser'] = 'запитати користувача';
$labels['autosend'] = 'відправляти автоматично';
$labels['autosendknown'] = 'відправити лист моїм контактам, у іншому випадку запитати мене';
$labels['autosendknownignore'] = 'відправити лист моїм контактам, у іншому випадку ігнорувати';
$labels['ignore'] = 'ігнорувати';
$labels['readwhendeleted'] = 'Позначати як прочитане при видаленні';
$labels['flagfordeletion'] = 'Позначати для видалення замість видалення';
$labels['skipdeleted'] = 'Не показувати видалені листи';
$labels['deletealways'] = 'Видаляти листи при невдалому переміщенні у Видалені';
$labels['deletejunk'] = 'Видаляти листи з папки СПАМ минаючи Видалені';
$labels['showremoteimages'] = 'Показувати віддалені зображення';
$labels['fromknownsenders'] = 'від відомих відправників';
$labels['always'] = 'завжди';
$labels['showinlineimages'] = 'Показувати додані зображення внизу листа';
$labels['autosavedraft'] = 'Автозбереження чернетки';
$labels['everynminutes'] = 'кожні $n хвилин';
$labels['refreshinterval'] = 'Оновити (перевірити нові листи, тощо)';
$labels['never'] = 'ніколи';
$labels['immediately'] = 'одразу';
$labels['messagesdisplaying'] = 'Відображення листів';
$labels['messagescomposition'] = 'Створення листів';
$labels['mimeparamfolding'] = 'Імена додатків';
$labels['2231folding'] = 'повний RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'RFC 2047 (інші поштові клієнти)';
$labels['force7bit'] = 'Використовувати MIME кодування для 8-бітних символів';
$labels['advancedoptions'] = 'Додаткові параметри';
$labels['focusonnewmessage'] = 'Фокусувати вікно переглядача при новому листі';
$labels['checkallfolders'] = 'Перевіряти нові листи у всіх папках';
$labels['displaynext'] = 'Після видалення/переміщення листа відображати наступний';
$labels['defaultfont'] = 'Шрифт для HTML за замовчуванням';
$labels['mainoptions'] = 'Основні налаштування';
$labels['browseroptions'] = 'Налаштування переглядача';
$labels['section'] = 'Розділ';
$labels['maintenance'] = 'Додатково';
$labels['newmessage'] = 'Новий лист';
$labels['signatureoptions'] = 'Налаштування підпису';
$labels['whenreplying'] = 'При відповіді';
$labels['replyempty'] = 'не цитувати оригінальний лист';
$labels['replytopposting'] = 'починати новий лист перед цитованим';
$labels['replybottomposting'] = 'починати новий лист після цитованого';
$labels['replyremovesignature'] = 'При відповіді видаляти первинний підпис з листа';
$labels['autoaddsignature'] = 'Автоматично додавати підпис';
$labels['newmessageonly'] = 'тільки до нових листів';
$labels['replyandforwardonly'] = 'тільки при відповідях та пересилках';
$labels['insertsignature'] = 'Додати підпис';
$labels['previewpanemarkread'] = 'Позначити переглянуті листи як прочитані';
$labels['afternseconds'] = 'через $n секунд';
$labels['reqmdn'] = 'Завжди повідомляти про прочитання';
$labels['reqdsn'] = 'Завжди повідомляти про доставку';
$labels['replysamefolder'] = 'Розміщувати відповіді у папці вихідного листа';
$labels['defaultabook'] = 'Адресна книга за замовчуванням';
$labels['autocompletesingle'] = 'Пропускати додаткові адреси у автозавершенні';
$labels['listnamedisplay'] = 'Виводити список контактів як';
$labels['spellcheckbeforesend'] = 'Перевіряти орфографію перед відправкою';
$labels['spellcheckoptions'] = 'Налаштування перевірки орфографії';
$labels['spellcheckignoresyms'] = 'Ігнорувати слова з символами';
$labels['spellcheckignorenums'] = 'Ігнорувати слова з числами';
$labels['spellcheckignorecaps'] = 'Ігнорувати слова з великими літерами';
$labels['addtodict'] = 'Додати до словника';
$labels['mailtoprotohandler'] = 'Зареєструвати цей сервер як обробник посилань mailto:';
$labels['forwardmode'] = 'Пересилання листів';
$labels['inline'] = 'у тексті';
$labels['asattachment'] = 'як додаток';

$labels['folder'] = 'Папка';
$labels['folders'] = 'Папки';
$labels['foldername'] = 'Назва папки';
$labels['subscribed'] = 'Підписані';
$labels['messagecount'] = 'Листи';
$labels['create'] = 'Створити';
$labels['createfolder'] = 'Створити нову папку';
$labels['managefolders'] = 'Керування папками';
$labels['specialfolders'] = 'Особливі папки';
$labels['properties'] = 'Властивості';
$labels['folderproperties'] = 'Властивості папки';
$labels['parentfolder'] = 'Батьківська папка';
$labels['location'] = 'Розташування';
$labels['info'] = 'Інформація';
$labels['getfoldersize'] = 'Показати розмір папки';
$labels['changesubscription'] = 'Змінити підписку';
$labels['foldertype'] = 'Тип каталогу';
$labels['personalfolder'] = 'Приватний каталог';
$labels['otherfolder'] = 'Каталог іншого користувача';
$labels['sharedfolder'] = 'Публічний каталог';

$labels['sortby'] = 'Відсортувати за';
$labels['sortasc'] = 'Сортувати за зростанням';
$labels['sortdesc'] = 'Сортувати за спаданням';
$labels['undo'] = 'Відмінити';

$labels['installedplugins'] = 'Встановлені плагіни';
$labels['plugin'] = 'Доповнення';
$labels['version'] = 'Версія';
$labels['source'] = 'Джерело';
$labels['license'] = 'Ліцензія';
$labels['support'] = 'Отримати підтримку';

// units
$labels['B'] = 'Б';
$labels['KB'] = 'кБ';
$labels['MB'] = 'МБ';
$labels['GB'] = 'ГБ';

// character sets
$labels['unicode'] = 'Юнікод';
$labels['english'] = 'Англійське';
$labels['westerneuropean'] = 'Західноєвропейське';
$labels['easterneuropean'] = 'Східноєвропейське';
$labels['southeasterneuropean'] = 'Південно-Західна Європа';
$labels['baltic'] = 'Балтійське';
$labels['cyrillic'] = 'Кирилиця';
$labels['arabic'] = 'Арабське';
$labels['greek'] = 'Грецьке';
$labels['hebrew'] = 'Іврит';
$labels['turkish'] = 'Турецьке';
$labels['nordic'] = 'Скандинавське';
$labels['thai'] = 'Тайське';
$labels['celtic'] = 'Кельтське';
$labels['vietnamese'] = 'В\'єтнамське';
$labels['japanese'] = 'Японське';
$labels['korean'] = 'Корейське';
$labels['chinese'] = 'Китайське';

?>
