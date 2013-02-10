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
$labels['welcome']   = 'Добродошли на $product';
$labels['username']  = 'Корисничко име';
$labels['password']  = 'Лозинка';
$labels['server']    = 'Сервер';
$labels['login']     = 'Пријава';

// taskbar
$labels['logout']   = 'Одјава';
$labels['mail']     = 'Сандуче';
$labels['settings'] = 'Подешавања';
$labels['addressbook'] = 'Именик';

// mailbox names
$labels['inbox']  = 'Примљене';
$labels['drafts'] = 'U pripremi';
$labels['sent']   = 'Послате';
$labels['trash']  = 'Канта';
$labels['junk']   = 'Смеће';

// message listing
$labels['subject'] = 'Наслов';
$labels['from']    = 'Од';
$labels['sender']  = 'Sender';
$labels['to']      = 'За';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Одговори';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Датум';
$labels['size']    = 'Величина';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организација';
$labels['readstatus'] = 'Статус поруке';
$labels['listoptions'] = 'Излистај опције...';

$labels['mailboxlist'] = 'Фасцикле';
$labels['messagesfromto'] = 'Поруке $from до $to од укупно $count';
$labels['threadsfromto'] = 'Страница $from до $to од $count';
$labels['messagenrof'] = 'Порука број $nr од $count';
$labels['fromtoshort'] = '$from – $to од $count';

$labels['copy']     = 'Копирај';
$labels['move']     = 'Пребаци';
$labels['moveto']   = 'Пребаци у...';
$labels['download'] = 'Преузми (скидање)';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Име датотекеe';
$labels['filesize'] = 'Величина датотеке';

$labels['addtoaddressbook'] = 'Додај у именик';

// weekdays short
$labels['sun'] = 'Нед';
$labels['mon'] = 'Пон';
$labels['tue'] = 'Уто';
$labels['wed'] = 'Сре';
$labels['thu'] = 'Чет';
$labels['fri'] = 'Пет';
$labels['sat'] = 'Суб';

// weekdays long
$labels['sunday']    = 'Недеља';
$labels['monday']    = 'Понедељак';
$labels['tuesday']   = 'Уторак';
$labels['wednesday'] = 'Среда';
$labels['thursday']  = 'Четвртак';
$labels['friday']    = 'Петак';
$labels['saturday']  = 'Субота';

// months short
$labels['jan']	= 'Јан';
$labels['feb']	= 'Феб';
$labels['mar']	= 'Мар';
$labels['apr']	= 'Апр';
$labels['may']	= 'Мај';
$labels['jun']	= 'Јун';
$labels['jul'] 	= 'Јул';
$labels['aug']	= 'Авг';
$labels['sep']	= 'Сеп';
$labels['oct']	= 'Окт';
$labels['nov']	= 'Нов';
$labels['dec']	= 'Дец';

// months long
$labels['longjan']	= 'Јануар';
$labels['longfeb']	= 'Фебруар';
$labels['longmar']	= 'Март';
$labels['longapr']	= 'Април';
$labels['longmay']	= 'Мај';
$labels['longjun']	= 'Јуни';
$labels['longjul']	= 'Јули';
$labels['longaug']	= 'Август';
$labels['longsep']	= 'Септембар';
$labels['longoct']	= 'Октобар';
$labels['longnov']	= 'Новембар';
$labels['longdec']	= 'Децембар';

$labels['today'] = 'Данас';

// toolbar buttons
$labels['refresh']          = 'Освежи';
$labels['checkmail']        = 'Провера нових порука';
$labels['compose']          = 'Нова порука';
$labels['writenewmessage']  = 'Нова порука';
$labels['reply']            = 'Одговори';
$labels['replytomessage']   = 'Одговори на поруку';
$labels['replytoallmessage'] = 'Одговори пошиљаоцу и свим примаоцима';
$labels['replyall']         = 'Одговори свима';
$labels['replylist']        = 'Листа за одговоре';
$labels['forward']          = 'Проследи';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Проследи као прилог';
$labels['forwardmessage']   = 'Проследи поруку';
$labels['deletemessage']    = 'Избриши поруку';
$labels['movemessagetotrash'] = 'Пребаци поруку у канту';
$labels['printmessage']     = 'Одштампај поруку';
$labels['previousmessage']  = 'Прикажи претходну поруку';
$labels['firstmessage']     = 'Прикажи прву поруку';
$labels['nextmessage']      = 'Прикажи следећу поруку';
$labels['lastmessage']      = 'Прикажи последњу поруку';
$labels['backtolist']       = 'Повратак на листу порука';
$labels['viewsource']       = 'Прикажи поруку у изворном облику';
$labels['mark']             = 'Означи';
$labels['markmessages']     = 'Обележи поруке';
$labels['markread']         = 'Као прочитано';
$labels['markunread']       = 'Као непрочитано';
$labels['markflagged']      = 'Као означено';
$labels['markunflagged']    = 'Као неозначено';
$labels['moreactions']      = 'Остале радње...';
$labels['more']             = 'Више';
$labels['back']             = 'Назад';
$labels['options']          = 'Опције';

$labels['select'] = 'Одабери';
$labels['all'] = 'Све';
$labels['none'] = 'Ништа';
$labels['currpage'] = 'Тренутна страница';
$labels['unread'] = 'Непрочитане';
$labels['flagged'] = 'Означено';
$labels['unanswered'] = 'Неодговорено';
$labels['deleted'] = 'Обрисано';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Уведи';
$labels['filter'] = 'Филтер';
$labels['list'] = 'Листа';
$labels['threads'] = 'Нити';
$labels['expand-all'] = 'Прошири све';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'Сажми све';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'само са непрочитаним порукама';
$labels['fromto'] = 'Од/За';
$labels['flag'] = 'Застава';
$labels['attachment'] = 'Прилог';
$labels['nonesort'] = 'Ништа';
$labels['sentdate'] = 'Датум послато';
$labels['arrival'] = 'Датум стигло';
$labels['asc'] = 'растући';
$labels['desc'] = 'опадајући';
$labels['listcolumns'] = 'Лист колона';
$labels['listsorting'] = 'Сортирање колонe';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'Лист режим приказивања';

$labels['folderactions'] = 'Поставке фасцикле';
$labels['compact'] = 'Компакција';
$labels['empty'] = 'Испразни';

$labels['quota'] = 'Квота';
$labels['unknown']  = 'непозната';
$labels['unlimited']  = 'бесконачна';

$labels['quicksearch']  = 'Брза претрага';
$labels['resetsearch']  = 'Прикажи све поруке';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Цела порука';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Отвори у новом прозору';
$labels['emlsave'] = 'Довнлоад (.eml)';

// message compose
$labels['editasnew']      = 'Модификуј као ново';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Пошаљи поруку';
$labels['savemessage']    = 'Сачувај у Снимљене';
$labels['addattachment']  = 'Приложи датотеку';
$labels['charset']        = 'Скуп карактера';
$labels['editortype']     = 'Врста едитора';
$labels['returnreceipt']  = 'Потврда о приспећу';
$labels['dsn']            = 'Обавештење о статусу слања поруке';
$labels['mailreplyintro'] = 'Дана $date, $sender је написао:';
$labels['originalmessage'] = 'Оригинална порука';

$labels['editidents']    = 'Модификуј псеудониме';
$labels['spellcheck']    = 'Провера правописа';
$labels['checkspelling'] = 'Провера правописа';
$labels['resumeediting'] = 'Повратак у режим писања';
$labels['revertto']      = 'Врати се на';

$labels['attach'] = 'Приложи';
$labels['attachments'] = 'Прилози';
$labels['upload'] = 'Додај';
$labels['uploadprogress'] = '$percent ($current од $total)';
$labels['close']  = 'Затвори';
$labels['messageoptions']  = 'Опције поруке';

$labels['low']     = 'Низак';
$labels['lowest']  = 'Најнижи';
$labels['normal']  = 'Средњи';
$labels['high']    = 'Висок';
$labels['highest'] = 'Највиши';

$labels['nosubject']  = '(без наслова)';
$labels['showimages'] = 'Прикажи слике';
$labels['alwaysshow'] = 'Увек покажи слике од $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Обичан текст';
$labels['savesentmessagein'] = 'Послате поруке сачувај у';
$labels['dontsave'] = 'Не сачувај';
$labels['maxuploadsize'] = 'Максимална величина податка је $size';

$labels['addcc'] = 'Додај Cc';
$labels['addbcc'] = 'Додај Bcc';
$labels['addreplyto'] = 'Додај Одговор-за';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'Пошиљалац ове поруке је замолио да буде обавештен када прочитате ову поруку. Да ли желите да обавестите пошиљаоца?';
$labels['receiptread'] = 'Обавештење о приспећу (прочитана порука)';
$labels['yourmessage'] = 'Ово је обавештење о приспећу за вашу поруку';
$labels['receiptnote'] = 'Пажња: Ово обавештење само потврђује да је порука приказана на рачунару примаоца. Не постоји гаранција да је прималац прочитао или разумео садржај поруке.';

// address boook
$labels['name']         = 'Пуно име';
$labels['firstname']    = 'Име';
$labels['surname']      = 'Презиме';
$labels['middlename']   = 'Средње име';
$labels['nameprefix']   = 'Префикс';
$labels['namesuffix']   = 'Суфикс';
$labels['nickname']     = 'Надимак';
$labels['jobtitle']     = 'Пословна титула';
$labels['department']   = 'Оделење';
$labels['gender']       = 'Пол';
$labels['maidenname']   = 'Девојачко име';
$labels['email']        = 'Имејл';
$labels['phone']        = 'Телефон';
$labels['address']      = 'Адреса';
$labels['street']       = 'Улица';
$labels['locality']     = 'Град';
$labels['zipcode']      = 'Поштански број';
$labels['region']       = 'република/провинција';
$labels['country']      = 'Држава';
$labels['birthday']     = 'Датум рођења';
$labels['anniversary']  = 'Годишњица';
$labels['website']      = 'Вебсајт';
$labels['instantmessenger'] = 'ИМ';
$labels['notes'] = 'Белешке';
$labels['male']   = 'мушко';
$labels['female'] = 'женско';
$labels['manager'] = 'Менаџер';
$labels['assistant'] = 'Асистент';
$labels['spouse'] = 'Супружник';
$labels['allfields'] = 'Сва поља';
$labels['search'] = 'Тражи';
$labels['advsearch'] = 'Напредна претрага';
$labels['advanced'] = 'Напредно';
$labels['other'] = 'Остало';

$labels['typehome']   = 'Кућа';
$labels['typework']   = 'Посао';
$labels['typeother']  = 'Остало';
$labels['typemobile']  = 'Мобилни Телефон';
$labels['typemain']  = 'Главна';
$labels['typehomefax']  = 'Кућни факс';
$labels['typeworkfax']  = 'Пословни факс';
$labels['typecar']  = 'Ауто';
$labels['typepager']  = 'Пејџер';
$labels['typevideo']  = 'Видео';
$labels['typeassistant']  = 'Асистент';
$labels['typehomepage']  = 'Почетна страница';
$labels['typeblog'] = 'Блог';
$labels['typeprofile'] = 'Профил';

$labels['addfield'] = 'Додај поље';
$labels['addcontact'] = 'Додај нови контакт';
$labels['editcontact'] = 'Измени контакт';
$labels['contacts'] = 'Конакти';
$labels['contactproperties'] = 'Својства контакта';
$labels['personalinfo'] = 'Личне информације';

$labels['edit']   = 'Измени';
$labels['cancel'] = 'Откажи';
$labels['save']   = 'Сачувај';
$labels['delete'] = 'Обриши';
$labels['rename'] = 'Преименуј';
$labels['addphoto'] = 'Додај';
$labels['replacephoto'] = 'Измени';
$labels['uploadphoto'] = 'Постави фотографију';

$labels['newcontact']     = 'Додај нови контакт';
$labels['deletecontact']  = 'Обриши одабране контакте';
$labels['composeto']      = 'Напиши поруку...';
$labels['contactsfromto'] = 'Контакти $from до $to од укупно $count';
$labels['print']          = 'Штампај';
$labels['export']         = 'Извези';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Извези конакте у vCard формату';
$labels['newcontactgroup'] = 'Креирај нову контакт групу';
$labels['grouprename']    = 'Преименуј групу';
$labels['groupdelete']    = 'Обриши групу';
$labels['groupremoveselected'] = 'Избриши изабране контакте из групе';

$labels['previouspage']   = 'Прикажи претходну страну';
$labels['firstpage']      = 'Прикажи први скуп';
$labels['nextpage']       = 'Прикажи следећу страну';
$labels['lastpage']       = 'Прикажи последњи скуп';

$labels['group'] = 'Група';
$labels['groups'] = 'Групе';
$labels['personaladrbook'] = 'Личне адресе';

$labels['searchsave'] = 'Сачувај претрагу';
$labels['searchdelete'] = 'Избриши претрагу';

$labels['import'] = 'Увези';
$labels['importcontacts'] = 'Увези контакте';
$labels['importfromfile'] = 'Увези из датотеке:';
$labels['importtarget'] = 'Додај нове контакте у адресар';
$labels['importreplace'] = 'Измени цели адресар';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Готово';

// settings
$labels['settingsfor'] = 'Подешавања за';
$labels['about'] = 'Инфо';
$labels['preferences'] = 'Подешавања';
$labels['userpreferences'] = 'Општа подешавања';
$labels['editpreferences'] = 'Измена општих подешавања';

$labels['identities'] = 'Идентитети';
$labels['manageidentities'] = 'Подеси идентитете за овај налог';
$labels['newidentity'] = 'Додај идентитет';

$labels['newitem'] = 'Нова ставка';
$labels['edititem'] = 'Измена ставке';

$labels['preferhtml'] = 'Предност дај ХТМЛ формату';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'ХТМЛ формат поруке';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Формат датума';
$labels['timeformat'] = 'Формат времена';
$labels['prettydate'] = 'Форматирани датум';
$labels['setdefault']  = 'Подеси подразумевано';
$labels['autodetect']  = 'Аутоматски';
$labels['language']  = 'Језик';
$labels['timezone']  = 'Временска зона';
$labels['pagesize']  = 'Редова по страни';
$labels['signature'] = 'Потпис';
$labels['dstactive']  = 'Летње померање времена';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Састави HTML поруке';
$labels['htmlonreply'] = 'само при одговору на HTML поруку';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML потпис';
$labels['previewpane'] = 'Прикажи панел за преглед';
$labels['skin'] = 'Интерфејс';
$labels['logoutclear'] = 'Избриши смеће при логовању';
$labels['logoutcompact'] = 'Компактно сандуче при логовању';
$labels['uisettings'] = 'Кориснички интерфејс';
$labels['serversettings'] = 'Поставке сервера';
$labels['mailboxview'] = 'Преглед сандучета';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'питај ме';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'игнорисати';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Не приказуј избрисане поруке';
$labels['deletealways'] = 'Ако слање поруке у отпад не успе, обришите их';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'увек';
$labels['showinlineimages'] = 'Покажи приложене слике испод поруке';
$labels['autosavedraft']  = 'Аутоматски чувај у Снимљене';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'никад';
$labels['immediately']  = 'одмах';
$labels['messagesdisplaying'] = 'прикажи поруке';
$labels['messagescomposition'] = 'састави поруке';
$labels['mimeparamfolding'] = 'имена прилога';
$labels['2231folding'] = 'Full RFC 2231 (тхундербирд )';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (други)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Напредне опције';
$labels['focusonnewmessage'] = 'Фокусирати прозор претраживача на новој поруци';
$labels['checkallfolders'] = 'Проверите све фасцикле за нове поруке';
$labels['displaynext'] = 'Након Избрисања/Премјештења поруке приказати следећу поруку';
$labels['defaultfont'] = 'Подразумевани фонт за ХТМЛ поруке';
$labels['mainoptions'] = 'Главне поставке';
$labels['browseroptions'] = 'Опције претраживача';
$labels['section'] = 'Секција';
$labels['maintenance'] = 'Поправке';
$labels['newmessage'] = 'Нова порука';
$labels['signatureoptions'] = 'Опције потписа';
$labels['whenreplying'] = 'При одговору';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'Почни нову поруку преко оригинала';
$labels['replybottomposting'] = 'Почни нову поруку испод оригинала';
$labels['replyremovesignature'] = 'При одговору одстрани оригинални потпис из поруке';
$labels['autoaddsignature'] = 'Аутоматски додај потпис';
$labels['newmessageonly'] = 'само нове поруке';
$labels['replyandforwardonly'] = 'само одговори и прослеђивања';
$labels['replysignaturepos'] = 'При одговору или прослеђивању додај потпис';
$labels['belowquote'] = 'испод квоте';
$labels['abovequote'] = 'изнад квоте';
$labels['insertsignature'] = 'Убаци потпис';
$labels['previewpanemarkread']  = 'Прегледану поруку означи као прочитану';
$labels['afternseconds']  = 'након $n секунди';
$labels['reqmdn'] = 'Увек затражи повратну скрипту';
$labels['reqdsn'] = 'Увек затражи обавештење о статусу слања поруке';
$labels['replysamefolder'] = 'Сачувај одговоре у фасциклу поруке на коју се одговара';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'Листи контакате као';
$labels['spellcheckbeforesend'] = 'Проверите правопис пре слања поруке';
$labels['spellcheckoptions'] = 'Опције  Провере  правописа';
$labels['spellcheckignoresyms'] = 'игнориши  речи са симболима';
$labels['spellcheckignorenums'] = 'игнориши  речи са бројевима';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Додај у речник';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Фасцикла';
$labels['folders']  = 'Фасцикле';
$labels['foldername']  = 'Име фасцикле';
$labels['subscribed']  = 'Потписана';
$labels['messagecount'] = 'Поруке';
$labels['create']  = 'Креирај';
$labels['createfolder']  = 'Креирај нову фасциклу';
$labels['managefolders']  = 'Подеси фасциклу';
$labels['specialfolders'] = 'Посебне фасцикле';
$labels['properties'] = 'Својства';
$labels['folderproperties'] = 'Својства фасцикле';
$labels['parentfolder'] = 'Главна фасцикла';
$labels['location'] = 'Локација';
$labels['info'] = 'Информација';
$labels['getfoldersize'] = 'Кликните за приказ величине фасцикле';
$labels['changesubscription'] = 'Кликните да бисте променили опис';
$labels['foldertype'] = 'типа фасцикле';
$labels['personalfolder']  = 'Приватна фасцикла';
$labels['otherfolder']  = 'Фасцикла Других Корисникa';
$labels['sharedfolder']  = 'Јавна фасцикла';

$labels['sortby'] = 'Сортирај по';
$labels['sortasc']  = 'Сортирај растуће';
$labels['sortdesc'] = 'Сортирај опадајуће';
$labels['undo'] = 'Унатраг';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Додаци';
$labels['version'] = 'Верзија';
$labels['source'] = 'Извор';
$labels['license'] = 'Лиценца';
$labels['support'] = 'подршка';

// units
$labels['B'] = 'Б';
$labels['KB'] = 'КБ';
$labels['MB'] = 'МБ';
$labels['GB'] = 'ГБ';

// character sets
$labels['unicode'] = 'Универзални код';
$labels['english'] = 'Енглески';
$labels['westerneuropean'] = 'Западна Европа';
$labels['easterneuropean'] = 'Источна Европа';
$labels['southeasterneuropean'] = 'Јужно-источна Европа';
$labels['baltic'] = 'Балтички';
$labels['cyrillic'] = 'Ћирилица';
$labels['arabic'] = 'Арапски';
$labels['greek'] = 'Грчки';
$labels['hebrew'] = 'Хебрејски';
$labels['turkish'] = 'Турски';
$labels['nordic'] = 'Нордички';
$labels['thai'] = 'Тајландски';
$labels['celtic'] = 'келтски';
$labels['vietnamese'] = 'Вијетнамски';
$labels['japanese'] = 'Јапански';
$labels['korean'] = 'Кореански';
$labels['chinese'] = 'Кинески';

?>
