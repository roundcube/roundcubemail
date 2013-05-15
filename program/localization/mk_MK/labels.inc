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
$labels['welcome']   = 'Добредојдовте во $product';
$labels['username']  = 'Корисничко име';
$labels['password']  = 'Лозинка';
$labels['server']    = 'Сервер';
$labels['login']     = 'Најава';

// taskbar
$labels['logout']   = 'Одјава';
$labels['mail']     = 'Е-пошта';
$labels['settings'] = 'Нагодувања';
$labels['addressbook'] = 'Именик';

// mailbox names
$labels['inbox']  = 'Примени';
$labels['drafts'] = 'Непратени';
$labels['sent']   = 'Пратени';
$labels['trash']  = 'Корпа';
$labels['junk']   = 'Ѓубре';

// message listing
$labels['subject'] = 'Наслов';
$labels['from']    = 'Праќач';
$labels['sender']  = 'Sender';
$labels['to']      = 'Примач';
$labels['cc']      = 'Копирај';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Препрати на';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Датум';
$labels['size']    = 'Големина';
$labels['priority'] = 'Приоритет';
$labels['organization'] = 'Организација';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Папки';
$labels['messagesfromto'] = 'Писма од $from до $to од $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Писмо $nr од $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Копирај';
$labels['move']     = 'Move';
$labels['moveto']   = 'Премести во...';
$labels['download'] = 'Преземи';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Име на податотеката';
$labels['filesize'] = 'Големина на податотеката';

$labels['addtoaddressbook'] = 'Додај во именик';

// weekdays short
$labels['sun'] = 'Нед';
$labels['mon'] = 'Пон';
$labels['tue'] = 'Вто';
$labels['wed'] = 'Сре';
$labels['thu'] = 'Чет';
$labels['fri'] = 'Пет';
$labels['sat'] = 'Саб';

// weekdays long
$labels['sunday']    = 'Недела';
$labels['monday']    = 'Понеделник';
$labels['tuesday']   = 'Вторник';
$labels['wednesday'] = 'Среда';
$labels['thursday']  = 'Четврток';
$labels['friday']    = 'Петок';
$labels['saturday']  = 'Сабота';

// months short
$labels['jan']	= 'Јан';
$labels['feb']	= 'Фев';
$labels['mar']	= 'Мар';
$labels['apr']	= 'Апр';
$labels['may']	= 'Мај';
$labels['jun']	= 'Јун';
$labels['jul'] 	= 'Јул';
$labels['aug']	= 'Авг';
$labels['sep']	= 'Сеп';
$labels['oct']	= 'Окт';
$labels['nov']	= 'Ное';
$labels['dec']	= 'Дек';

// months long
$labels['longjan']	= 'Јануари';
$labels['longfeb']	= 'Февруари';
$labels['longmar']	= 'Март';
$labels['longapr']	= 'Април';
$labels['longmay']	= 'Мај';
$labels['longjun']	= 'Јуни';
$labels['longjul']	= 'Јули';
$labels['longaug']	= 'Август';
$labels['longsep']	= 'Септември';
$labels['longoct']	= 'Октомври';
$labels['longnov']	= 'Ноември';
$labels['longdec']	= 'Декември';

$labels['today'] = 'Денес';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Провери пошта';
$labels['compose']          = 'Пиши ново писмо';
$labels['writenewmessage']  = 'Ново писмо';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'Одговори на испраќачот';
$labels['replytoallmessage'] = 'Одговори на испраќачот и сите примачи';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Препрати писмо';
$labels['deletemessage']    = 'Избриши писмо';
$labels['movemessagetotrash'] = 'Премести ги писмата во Корпа';
$labels['printmessage']     = 'Испечати писмо';
$labels['previousmessage']  = 'Прикажи го претходното писмо';
$labels['firstmessage']     = 'Прикажи го првото писмо';
$labels['nextmessage']      = 'Прикажи го следното писмо';
$labels['lastmessage']      = 'Прикажи го последното писмо';
$labels['backtolist']       = 'Назад кон листа на писма';
$labels['viewsource']       = 'Покажи го изворот на писмото';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'Обележи ги писмата';
$labels['markread']         = 'Како прочитани';
$labels['markunread']       = 'Како непрочитани';
$labels['markflagged']      = 'Како обележано';
$labels['markunflagged']    = 'Како необележано';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'Одбери';
$labels['all'] = 'Сите';
$labels['none'] = 'Ниедно';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Непрочитани';
$labels['flagged'] = 'Обележано';
$labels['unanswered'] = 'Неодговорено';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Избришано';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Обратно';
$labels['filter'] = 'Филтер';
$labels['list'] = 'List';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'Expand All';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'Collapse All';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'From/To';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Attachment';
$labels['nonesort'] = 'Ниедно';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Компактно';
$labels['empty'] = 'Испразни';

$labels['quota'] = 'Искористен простор';
$labels['unknown']  = 'непознато';
$labels['unlimited']  = 'неограничено';

$labels['quicksearch']  = 'Брзо пребарување';
$labels['resetsearch']  = 'Ново пребарување';
$labels['searchmod']  = 'Модификатори на пребарувањето';
$labels['msgtext']  = 'Цело писмо';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Отвори во нов прозорец';
$labels['emlsave'] = 'Преземи (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Уреди како ново';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Испрати сега';
$labels['savemessage']    = 'Зачувај го писмото во Непратени';
$labels['addattachment']  = 'Приложи податотека';
$labels['charset']        = 'Збир од знаци';
$labels['editortype']     = 'Тип на уредник';
$labels['returnreceipt']  = 'Врати';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Провери правопис';
$labels['resumeediting'] = 'Продолжи со уредување';
$labels['revertto']      = 'Врати на';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Приложено';
$labels['upload'] = 'Подигни';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Затвори';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Низок';
$labels['lowest']  = 'Најнизок';
$labels['normal']  = 'Нормален';
$labels['high']    = 'Висок';
$labels['highest'] = 'Највисок';

$labels['nosubject']  = '(без наслов)';
$labels['showimages'] = 'Прикажи слики';
$labels['alwaysshow'] = 'Секогаш прикажувај ги сликите од $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Обичен текст';
$labels['savesentmessagein'] = 'Сними го пратеното писмо во';
$labels['dontsave'] = 'не зачувувај';
$labels['maxuploadsize'] = 'Максималната дозволена големина за податотеката е $size';

$labels['addcc'] = 'Додај Cc';
$labels['addbcc'] = 'Додај Bcc';
$labels['addreplyto'] = 'Додај Одговори-на';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'Праќачот на оваа порака сака да биде известен кога ќе го прочитате писмото. Дали сакате да го известите?';
$labels['receiptread'] = 'Извести (прочитано)';
$labels['yourmessage'] = 'Ова е известување за вашата порака';
$labels['receiptnote'] = 'Напомена: Ова известување само потврдува дека писмото кое сте го пратиле е отворено. Не е гаранција дека примачот го прочитал или разбрал истото.';

// address boook
$labels['name']         = 'Име за приказ';
$labels['firstname']    = 'Име';
$labels['surname']      = 'Презиме';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Е-пошта';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'State/Province';
$labels['country']      = 'Country';
$labels['birthday']     = 'Birthday';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'male';
$labels['female'] = 'female';
$labels['manager'] = 'Manager';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'Other';

$labels['typehome']   = 'Home';
$labels['typework']   = 'Work';
$labels['typeother']  = 'Other';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'Home Fax';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'Додај нов контакт';
$labels['editcontact'] = 'Уреди контакт';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Уреди';
$labels['cancel'] = 'Откажи';
$labels['save']   = 'Зачувај';
$labels['delete'] = 'Избриши';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Додај ново име';
$labels['deletecontact']  = 'Избриши ги следните имиња';
$labels['composeto']      = 'Напиши порака за';
$labels['contactsfromto'] = 'Имиња од $from до $to од $count';
$labels['print']          = 'Испечати';
$labels['export']         = 'Извези како';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Извези ги контактите во vCard формат';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Прикажи претходна страна';
$labels['firstpage']      = 'Прикажи ја првата страна';
$labels['nextpage']       = 'Прикажи ја следната страна';
$labels['lastpage']       = 'Прикажи ја последната страна';

$labels['group'] = 'Group';
$labels['groups'] = 'Групи';
$labels['personaladrbook'] = 'Лични адреси';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'Увези';
$labels['importcontacts'] = 'Увези контакти';
$labels['importfromfile'] = 'Увези од податотека:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Замени го целиот именик';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Завршено';

// settings
$labels['settingsfor'] = 'Нагодувања за';
$labels['about'] = 'About';
$labels['preferences'] = 'Нагодувања';
$labels['userpreferences'] = 'Кориснички нагодувања';
$labels['editpreferences'] = 'Промени кориснички нагодувања';

$labels['identities'] = 'Идентитети';
$labels['manageidentities'] = 'Организирај ги идентитетите за овој корисник';
$labels['newidentity'] = 'Нов идентитет';

$labels['newitem'] = 'Нов објект';
$labels['edititem'] = 'Промени објект';

$labels['preferhtml'] = 'Прикажи во HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML писмо';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Убави датуми';
$labels['setdefault']  = 'Постави за основно';
$labels['autodetect']  = 'Автоматски';
$labels['language']  = 'Јазик';
$labels['timezone']  = 'Часовна зона';
$labels['pagesize']  = 'Редови по страна';
$labels['signature'] = 'Потпис';
$labels['dstactive']  = 'Промени на времето';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Пиши HTML порака';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML потпис';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Прикажи прозорец за преглед';
$labels['skin'] = 'Модел на изгледот';
$labels['logoutclear'] = 'Исчисти ја Кантата при излез';
$labels['logoutcompact'] = 'Подреди ги примените при излез';
$labels['uisettings'] = 'Кориснички изглед';
$labels['serversettings'] = 'Нагодувања на серверот';
$labels['mailboxview'] = 'Приказ на поштенското сандаче';
$labels['mdnrequests'] = 'Известувања за праќачот';
$labels['askuser'] = 'прашај го корисникот';
$labels['autosend'] = 'испрати автоматски';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'игнорирај';
$labels['readwhendeleted'] = 'Обележи го писмото како прочитано при бришење';
$labels['flagfordeletion'] = 'Обележи го писмото за бришење наместо да го избришеш';
$labels['skipdeleted'] = 'Не ги прикажувај избришаните писма';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Прикажи вметнати слики';
$labels['fromknownsenders'] = 'од познати праќачи';
$labels['always'] = 'секогаш';
$labels['showinlineimages'] = 'Прикажи приложени слики под писмото';
$labels['autosavedraft']  = 'Авоматски зачувувај во Непратени';
$labels['everynminutes']  = 'секои $n минути';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'никогаш';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Прикажување на писма';
$labels['messagescomposition'] = 'Пишување на писма';
$labels['mimeparamfolding'] = 'Име на прикачените податотеки';
$labels['2231folding'] = 'Полн RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Полн RFC 2047 (друго)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Напредни можности';
$labels['focusonnewmessage'] = 'Фокусирај го прозорецот на прелистувачот на новото писмо';
$labels['checkallfolders'] = 'Провери ги сите папки за нови писма';
$labels['displaynext'] = 'По бришење/преместување на писмото прикажи го следното писмо';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Главни можности';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Заглавие';
$labels['maintenance'] = 'Одржување';
$labels['newmessage'] = 'Ново писмо';
$labels['signatureoptions'] = 'Можности за потписот';
$labels['whenreplying'] = 'При одговарање';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'новото писмо започни го над оригиналот';
$labels['replybottomposting'] = 'новото писмо започни го под оригиналот';
$labels['replyremovesignature'] = 'При одговарање отстрани го изворниот потпис од писмото';
$labels['autoaddsignature'] = 'Автоматски додавај потпис';
$labels['newmessageonly'] = 'само нови писма';
$labels['replyandforwardonly'] = 'само одгорови и препраќања';
$labels['insertsignature'] = 'Вметни потпис';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'after $n seconds';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Add to dictionary';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Папка';
$labels['folders']  = 'Папки';
$labels['foldername']  = 'Име на папката';
$labels['subscribed']  = 'Претплатен';
$labels['messagecount'] = 'Пораки';
$labels['create']  = 'Создај';
$labels['createfolder']  = 'Создај нова папка';
$labels['managefolders']  = 'Организирање на папки';
$labels['specialfolders'] = 'Специјални папки';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'Сортирај по';
$labels['sortasc']  = 'Сортирај нагорно';
$labels['sortdesc'] = 'Сортирај надолно';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'Б';
$labels['KB'] = 'КБ';
$labels['MB'] = 'МБ';
$labels['GB'] = 'ГБ';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'English';
$labels['westerneuropean'] = 'Western European';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'Baltic';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'Arabic';
$labels['greek'] = 'Greek';
$labels['hebrew'] = 'Hebrew';
$labels['turkish'] = 'Turkish';
$labels['nordic'] = 'Nordic';
$labels['thai'] = 'Thai';
$labels['celtic'] = 'Celtic';
$labels['vietnamese'] = 'Vietnamese';
$labels['japanese'] = 'Japanese';
$labels['korean'] = 'Korean';
$labels['chinese'] = 'Chinese';

?>
