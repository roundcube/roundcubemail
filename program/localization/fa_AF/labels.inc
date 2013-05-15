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
$labels['welcome']   = 'به اين پروژه خوش امديد';
$labels['username']  = 'نام کاربر';
$labels['password']  = 'شفر';
$labels['server']    = 'سرور';
$labels['login']     = 'داخل شدن';

// taskbar
$labels['logout']   = 'خروج';
$labels['mail']     = 'ايميل';
$labels['settings'] = 'تنظیمات شخصی';
$labels['addressbook'] = 'کتاب ادرس';

// mailbox names
$labels['inbox']  = 'نامه های ارسالي';
$labels['drafts'] = 'نامه های ناتکمیل';
$labels['sent']   = 'ارسال شده';
$labels['trash']  = 'اشغال دانی';
$labels['junk']   = 'بیکاره';

// message listing
$labels['subject'] = 'مضمون';
$labels['from']    = 'ارسال کننده';
$labels['sender']  = 'Sender';
$labels['to']      = 'ګيرينده';
$labels['cc']      = 'تکثیر';
$labels['bcc']     = 'تکثیر پنهان';
$labels['replyto'] = 'جواب نامه';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'تاريخ';
$labels['size']    = 'اندازه';
$labels['priority'] = 'اولویت';
$labels['organization'] = 'تنظیمات';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'پوشه ها';
$labels['messagesfromto'] = 'از  مقدار پيغام به مقدار حساب شده';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'از شمار پيغام های حساب شده';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'تکثیر';
$labels['move']     = 'Move';
$labels['moveto']   = 'انتقال بدهید به ...';
$labels['download'] = 'داونلود کردن';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'نام فايل';
$labels['filesize'] = 'اندازه فايل';

$labels['addtoaddressbook'] = 'در کتاب ادرس علاوه نماييد';

// weekdays short
$labels['sun'] = 'يکشنپبه';
$labels['mon'] = 'دوشنبه';
$labels['tue'] = 'سه شنبه';
$labels['wed'] = 'چهارشنبه';
$labels['thu'] = 'پنج شنبه';
$labels['fri'] = 'جمعه';
$labels['sat'] = 'شنبه';

// weekdays long
$labels['sunday']    = 'يکشنبه';
$labels['monday']    = 'دوشنبه';
$labels['tuesday']   = 'سه شنبه';
$labels['wednesday'] = 'چهارشنبه';
$labels['thursday']  = 'پنج شنبه';
$labels['friday']    = 'جمعه';
$labels['saturday']  = 'شنبه';

// months short
$labels['jan']	= 'جنوری';
$labels['feb']	= 'فبروری';
$labels['mar']	= 'مارچ';
$labels['apr']	= 'اپریل';
$labels['may']	= 'می';
$labels['jun']	= 'جون';
$labels['jul'] 	= 'جولایی';
$labels['aug']	= 'آگست';
$labels['sep']	= 'سپتمبر';
$labels['oct']	= 'اکتوبر';
$labels['nov']	= 'نومبر';
$labels['dec']	= 'دسمبر';

// months long
$labels['longjan']	= 'جنوری';
$labels['longfeb']	= 'فبروری';
$labels['longmar']	= 'مارچ';
$labels['longapr']	= 'اپریل';
$labels['longmay']	= 'می';
$labels['longjun']	= 'جون';
$labels['longjul']	= 'جولایی';
$labels['longaug']	= 'آگست';
$labels['longsep']	= 'سپتمبر';
$labels['longoct']	= 'اکتوبر';
$labels['longnov']	= 'نومبر';
$labels['longdec']	= 'دسمبر';

$labels['today'] = 'امروز';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'بررسي کردن پيغام های جديد';
$labels['compose']          = 'نوشتن پیام حدید';
$labels['writenewmessage']  = 'ساختن پيغام جديد';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'جواب دادن به پيغام';
$labels['replytoallmessage'] = 'به فرستنده و تمام گيرينده ها جواب بدهيد';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'فرستادن این پیام به دیگران / فارورد';
$labels['deletemessage']    = 'حذف کردن پيغام';
$labels['movemessagetotrash'] = 'پيغام را به اشيآيي حذف شده منتقل نماييد';
$labels['printmessage']     = 'اين پيغام را چاپ کنيد';
$labels['previousmessage']  = 'پيغام قبلي را نشان دهيد';
$labels['firstmessage']     = 'پيغام اولي را نشان دهيد';
$labels['nextmessage']      = 'پيغام بعدي رانشان دهيد';
$labels['lastmessage']      = 'پيغام اخير را نشان دهيد';
$labels['backtolist']       = 'بازگشتن به لست پيغام';
$labels['viewsource']       = 'منبع / کود را نشان دهيد';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'پيغام ها را نشاني کنيد';
$labels['markread']         = 'مانند خوانده شده';
$labels['markunread']       = 'مانند خوانده نشده';
$labels['markflagged']      = 'مانند نشانی شده';
$labels['markunflagged']    = 'مانند نشانی ناشده';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'انتخاب کردن';
$labels['all'] = 'تمام';
$labels['none'] = 'هيچ کدام';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'ناخوانده';
$labels['flagged'] = 'نشانی شده';
$labels['unanswered'] = 'پیامهای جواب نداده شده';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Deleted';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'تصفیه کردن';
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
$labels['nonesort'] = 'هيچ کدام';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'خلاصه';
$labels['empty'] = 'خالي';

$labels['quota'] = '‌طريقه استعمال ديسک';
$labels['unknown']  = 'نامعلوم';
$labels['unlimited']  = 'نامعين/ نامحدود';

$labels['quicksearch']  = 'جستجوی سریع';
$labels['resetsearch']  = 'بازنشاندن جستجو';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'در پینجره ای جدید باز کنید';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'پيغام را همين حالا ارسال کنيد';
$labels['savemessage']    = 'این پیش نویس را حفظ نمایید';
$labels['addattachment']  = 'ضميمه نمودن يک فايل';
$labels['charset']        = 'ست کرکتر ها';
$labels['editortype']     = 'روش تصحیح کننده / نوشتاری';
$labels['returnreceipt']  = 'رسید برای مراجعت نامه';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'املا را بررسي کنيد';
$labels['resumeediting'] = 'ادامه / بازگشت به تصحیی کردن / نوشتن';
$labels['revertto']      = 'رجوع کردن به';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'ضميمه ها';
$labels['upload'] = 'اپلود کردن';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'بستن';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'کم';
$labels['lowest']  = 'کمترین';
$labels['normal']  = 'عادی';
$labels['high']    = 'عالي';
$labels['highest'] = 'عاليترين';

$labels['nosubject']  = 'مضمون ندارد';
$labels['showimages'] = 'نمايش دادن تصاوير';
$labels['alwaysshow'] = 'همیشه نشان بده تصاویر را از این فرسیتنده﷼';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'ایچ تی ام ایل';
$labels['plaintoggle'] = 'متن ساده';
$labels['savesentmessagein'] = 'پیام ارسال شده را ذخیره کن در';
$labels['dontsave'] = 'ذخیره نکنید';
$labels['maxuploadsize'] = '$sizeزیاد ترین اندازه اجازه داده شده فایل عبارت است از';

$labels['addcc'] = 'نسخیه دوم  را علاوه کنید';
$labels['addbcc'] = 'نسخه پنهان را علاوه کنید';
$labels['addreplyto'] = 'علاوه کردن';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'فرستانده این پیام خواهش نموده زمانیکه شما نامه را خواندید براش آگاهی داده شود آيا شما ميخواهيد که فرستنده را آګاه سازيد';
$labels['receiptread'] = 'بازگشت رسید (خوانده شده)';
$labels['yourmessage'] = 'برای پيغام شما اين يک رسید بازگشت است';
$labels['receiptnote'] = 'يادداشت : اين رسيد شما را فقط اګاه ميسازد که پيغام به کامپیوتر گیرنده نمایش داده شده است  هیچ گارنتی وجود ندارد که گیرنده پیام این پیام را خوانده و یا مطالب آن را درک کرده باشد.';

// address boook
$labels['name']         = 'نشان دادن نام';
$labels['firstname']    = 'اسم/ نام';
$labels['surname']      = 'اسم فاميلي / تخلص';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'ايميل';
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
$labels['addcontact'] = 'آدرس جديد را علاوه کنيد';
$labels['editcontact'] = 'آدرس جدید را تصحیح کنید';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'تصحيح کردن';
$labels['cancel'] = 'لغو کردن/ حذف کردن';
$labels['save']   = 'حفظ کردن';
$labels['delete'] = 'حذف کردن';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'کارت تماس جديد را بسازيد';
$labels['deletecontact']  = 'آدرس های انتخاب شده را حذف کنيد';
$labels['composeto']      = 'ايمیل را نوشته کنيد به';
$labels['contactsfromto'] = 'از$count$to به $from آدرس ها از';
$labels['print']          = 'چاپ کردن';
$labels['export']         = 'صادر کردن';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'صادر نماییدVcardآدرس ها را به فرمت';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'بسته قبلي را نشان دهيد';
$labels['firstpage']      = 'بسته اولي را نشان دهيد';
$labels['nextpage']       = 'بسته بعدي را نشان دهيد';
$labels['lastpage']       = 'بسته اخير را نشان دهيد';

$labels['group'] = 'Group';
$labels['groups'] = 'گروه ها';
$labels['personaladrbook'] = 'ادرس های شخصي';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'وارد کردن';
$labels['importcontacts'] = 'آدرس ها را وارد نمایید';
$labels['importfromfile'] = 'از یک فایل وارد نمایید';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'تمام کتاب آدرس ها را عوض نمایید';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'اجرا شد';

// settings
$labels['settingsfor'] = 'تنظیمات برای';
$labels['about'] = 'About';
$labels['preferences'] = 'ترجيحات';
$labels['userpreferences'] = 'ترجيحات کاربر';
$labels['editpreferences'] = 'تصحيح کردن ترجيحات  کاربر';

$labels['identities'] = 'شناخت ها';
$labels['manageidentities'] = 'اداره کردن شناسای ها برای اين آدرس';
$labels['newidentity'] = 'شناخت جديد';

$labels['newitem'] = 'فقره جديد';
$labels['edititem'] = 'تصحيح کردن فقره';

$labels['preferhtml'] = 'نشان دادن ايچ ټي ام ايل';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'پيغام ايچ ټي‌ام ايل';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'تاريخ های مهم';
$labels['setdefault']  = 'بطور همیشگی انتخاب نمایید';
$labels['autodetect']  = 'اتومات';
$labels['language']  = 'لسان';
$labels['timezone']  = 'ناحيه زمانی';
$labels['pagesize']  = 'تعداد سطر ها در صفحه';
$labels['signature'] = 'امضا';
$labels['dstactive']  = 'Daylight saving time';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'پيغام ايچ ټي ام ايل رانوشته کنيد';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'امضای حساب ایمیل بشکل ایچ تی ام ال';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'چوکات پیشنما را نشان دهید';
$labels['skin'] = 'تمپلیت محیط کاربری';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'محیط کاربر';
$labels['serversettings'] = 'تنظیمات سرور';
$labels['mailboxview'] = 'طرز نمایش صندوق پستی';
$labels['mdnrequests'] = 'آگاهی های ارسال کننده';
$labels['askuser'] = 'از کاربر بپرسید';
$labels['autosend'] = 'پیام را بطور خودکار روان کنید';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'رد کردن';
$labels['readwhendeleted'] = 'در هنگام حفظ پیام را خوانده شده نشانی کنید';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'پیام های حذف شده را نمایش ندهید';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'نامه های از آدرس های شناخته شده';
$labels['always'] = 'همیشه';
$labels['showinlineimages'] = 'تصاویر ضمیمه شده را در پایین پیام نشان دهید.';
$labels['autosavedraft']  = 'بصورت خودکار پیش نويس را حفظ کنيد';
$labels['everynminutes']  = 'دقیفه$n هر';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'هیچگاه';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'نمایش دادن پیام ها';
$labels['messagescomposition'] = 'ایجاد کردن پیام جدید';
$labels['mimeparamfolding'] = 'نام های ضمیمه ها';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (دیگر ها)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'انتخاب های پیشرفته';
$labels['focusonnewmessage'] = 'فوکس دادن پینجره مرورگر به پیام جدید';
$labels['checkallfolders'] = 'تمام پوشه ها را بخاطر پیام های جدید بررسی کنید';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'New Message';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automatically add signature';
$labels['newmessageonly'] = 'new message only';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['insertsignature'] = 'Insert signature';
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

$labels['folder']  = 'پوشه';
$labels['folders']  = 'پوشه ها';
$labels['foldername']  = 'نام پوشه';
$labels['subscribed']  = 'مشترک';
$labels['messagecount'] = 'پيغام ها';
$labels['create']  = 'ایجاد کردن';
$labels['createfolder']  = 'ایجاد پوشه جدید';
$labels['managefolders']  = 'اداره کردن پوشه ها';
$labels['specialfolders'] = 'پوشه های مخصوص';
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

$labels['sortby'] = 'فهرست کردن توسط';
$labels['sortasc']  = 'فهرست کردن از طرف بالا به پايين';
$labels['sortdesc'] = 'فهرست کردن از طرف پايين به بالا';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'بایت';
$labels['KB'] = 'کیلو بایت';
$labels['MB'] = 'میگا بایت';
$labels['GB'] = 'گیگا بایت';

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
