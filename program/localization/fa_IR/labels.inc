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
$labels['welcome']   = 'به $product خوش آمدید';
$labels['username']  = 'نام‌کاربری';
$labels['password']  = 'گذرواژه';
$labels['server']    = 'سرور';
$labels['login']     = 'ورود';

// taskbar
$labels['logout']   = 'برون‌رفت';
$labels['mail']     = 'نامه';
$labels['settings'] = 'تنظیمات';
$labels['addressbook'] = 'دفتر نشانی';

// mailbox names
$labels['inbox']  = 'صندوق ورودی';
$labels['drafts'] = 'پیش‌نویس‌ها';
$labels['sent']   = 'ارسال شده';
$labels['trash']  = 'زباله‌دان';
$labels['junk']   = 'بنجل';

// message listing
$labels['subject'] = 'موضوع';
$labels['from']    = 'از';
$labels['sender']  = 'فرستنده';
$labels['to']      = 'به';
$labels['cc']      = 'رونوشت';
$labels['bcc']     = 'رونوشت مخفی';
$labels['replyto'] = 'پاسخ-به';
$labels['followupto'] = 'پیگیری-در';
$labels['date']    = 'تاریخ';
$labels['size']    = 'اندازه';
$labels['priority'] = 'اولویت';
$labels['organization'] = 'سازمان';
$labels['readstatus'] = 'وضعیت خوانده شدن';
$labels['listoptions'] = 'فهرست گزینه‌ها...';

$labels['mailboxlist'] = 'پوشه‌ها';
$labels['messagesfromto'] = 'پیغام‌ها $from تا $to از $count';
$labels['threadsfromto'] = 'رشته‌ها $from تا $to از $count';
$labels['messagenrof'] = 'پیغام $nr از $count';
$labels['fromtoshort'] = '$from - $to از $count';

$labels['copy']     = 'رونوشت';
$labels['move']     = 'انتقال';
$labels['moveto']   = 'انتقال به...';
$labels['download'] = 'بارگیری';
$labels['showattachment'] = 'نمایش';
$labels['showanyway'] = 'نمایش آن در هر صورت';

$labels['filename'] = 'نام پرونده';
$labels['filesize'] = 'اندازه پرونده';

$labels['addtoaddressbook'] = 'افزودن به دفتر نشانی‌ها';

// weekdays short
$labels['sun'] = '۱ش';
$labels['mon'] = '۲ش';
$labels['tue'] = '۳ش';
$labels['wed'] = '۴ش';
$labels['thu'] = '۵ش';
$labels['fri'] = 'جم';
$labels['sat'] = 'ش';

// weekdays long
$labels['sunday']    = 'یک‌شنبه';
$labels['monday']    = 'دوشنبه';
$labels['tuesday']   = 'سه‌شنبه';
$labels['wednesday'] = 'چهارشنبه';
$labels['thursday']  = 'پنج‌شنبه';
$labels['friday']    = 'جمعه';
$labels['saturday']  = 'شنبه';

// months short
$labels['jan']	= 'ژان';
$labels['feb']	= 'فور';
$labels['mar']	= 'مار';
$labels['apr']	= 'آور';
$labels['may']	= 'مه';
$labels['jun']	= 'ژون';
$labels['jul'] 	= 'ژوی';
$labels['aug']	= 'اوت';
$labels['sep']	= 'سپت';
$labels['oct']	= 'اُکت';
$labels['nov']	= 'نوا';
$labels['dec']	= 'دسا';

// months long
$labels['longjan']	= 'ژانویه';
$labels['longfeb']	= 'فوریه';
$labels['longmar']	= 'مارس';
$labels['longapr']	= 'آوریل';
$labels['longmay']	= 'مه';
$labels['longjun']	= 'ژوئن';
$labels['longjul']	= 'جولای';
$labels['longaug']	= 'آگوست';
$labels['longsep']	= 'سپتامبر';
$labels['longoct']	= 'اکتبر';
$labels['longnov']	= 'نوامبر';
$labels['longdec']	= 'دسامبر';

$labels['today'] = 'امروز';

// toolbar buttons
$labels['refresh']          = 'نوسازی';
$labels['checkmail']        = 'بررسی پیغام‌های جدید';
$labels['compose']          = 'نوشتن پیغام';
$labels['writenewmessage']  = 'ایجاد پیغام جدید';
$labels['reply']            = 'پاسخ';
$labels['replytomessage']   = 'پاسخ به فرستنده';
$labels['replytoallmessage'] = 'پاسخ به فهرست یا به فرستنده و تمام گیرنده‌ها';
$labels['replyall']         = 'پاسخ به همه';
$labels['replylist']        = 'پاسخ به فهرست';
$labels['forward']          = 'ارجاع';
$labels['forwardinline']    = 'ارجاع خطی';
$labels['forwardattachment'] = 'ارجاع به صورت پیوست';
$labels['forwardmessage']   = 'ارجاع پیغام';
$labels['deletemessage']    = 'حذف پیغام';
$labels['movemessagetotrash'] = 'انتقال پیغام به زباله‌دان';
$labels['printmessage']     = 'چاپ این پیغام';
$labels['previousmessage']  = 'نمایش پیغام قبلی';
$labels['firstmessage']     = 'نمایش اولین  پیغام';
$labels['nextmessage']      = 'نمایش پیغام بعدی';
$labels['lastmessage']      = 'نمایش آخرین پیغام';
$labels['backtolist']       = 'بازگشت به فهرست پیغام';
$labels['viewsource']       = 'نمایش منبع';
$labels['mark']             = 'نشانه‌گذاری';
$labels['markmessages']     = 'نشانه‌گذاری پیغام‌ها';
$labels['markread']         = 'به عنوان خوانده‌شده';
$labels['markunread']       = 'به عنوان خوانده‌نشده';
$labels['markflagged']      = 'به عنوان نشانه‌گذاری شده';
$labels['markunflagged']    = 'به عنوان نشانه‌گذاری نشده';
$labels['moreactions']      = 'کارهای دیگر...';
$labels['more']             = 'بيشتر';
$labels['back']             = 'بازگشت';
$labels['options']          = 'تنظیمات';

$labels['select'] = 'انتخاب';
$labels['all'] = 'همه';
$labels['none'] = 'هیچ‌کدام';
$labels['currpage'] = 'صفحه جاری';
$labels['unread'] = 'خوانده‌نشده';
$labels['flagged'] = 'پرچم‌دار';
$labels['unanswered'] = 'پاسخ داده نشده';
$labels['deleted'] = 'حذف شده';
$labels['undeleted'] = 'حذف نشده';
$labels['invert'] = 'وارونه';
$labels['filter'] = 'صافی';
$labels['list'] = 'فهرست';
$labels['threads'] = 'رشته‌ها';
$labels['expand-all'] = 'باز کردن همه';
$labels['expand-unread'] = 'بازکردن خوانده نشده‌ها';
$labels['collapse-all'] = 'جمع کردن همه';
$labels['threaded'] = 'رشته شده';

$labels['autoexpand_threads'] = 'باز کردن رشته‌های پیغام';
$labels['do_expand'] = 'همه رشته‌ها';
$labels['expand_only_unread'] = 'فقط با پیغام‌های خوانده نشده';
$labels['fromto'] = 'از/به';
$labels['flag'] = 'پرچم';
$labels['attachment'] = 'پیوست';
$labels['nonesort'] = 'هیچ‌کدام';
$labels['sentdate'] = 'تاریخ ارسال';
$labels['arrival'] = 'تاریخ ورود';
$labels['asc'] = 'صعودی';
$labels['desc'] = 'نزولی';
$labels['listcolumns'] = 'ستون‌های فهرست';
$labels['listsorting'] = 'ستون مرتب‌سازی';
$labels['listorder'] = 'ترتیب مرتب‌سازی';
$labels['listmode'] = 'نوع مشاهده فهرست';

$labels['folderactions'] = 'اعمال پوشه...';
$labels['compact'] = 'فشرده';
$labels['empty'] = 'خالی';

$labels['quota'] = 'فضای استفاده شده';
$labels['unknown']  = 'ناشناخته';
$labels['unlimited']  = 'نامحدود';

$labels['quicksearch']  = 'جستجوی سریع';
$labels['resetsearch']  = 'جستجوی دوباره';
$labels['searchmod']  = 'اصلاحات جستجو';
$labels['msgtext']  = 'کل پیغام';
$labels['body']  = 'بدنه';

$labels['openinextwin'] = 'باز کردن در پنجره‌ی جدید';
$labels['emlsave'] = 'بارگیری (.eml)';

// message compose
$labels['editasnew']      = 'ویرایش به عنوان جدید';
$labels['send']           = 'ارسال';
$labels['sendmessage']    = 'ارسال پیغام';
$labels['savemessage']    = 'ذخیره به عنوان پیش‌نویس';
$labels['addattachment']  = 'پیوست کردن یک پرونده';
$labels['charset']        = 'مجموعه‌نمایه';
$labels['editortype']     = 'نوع ویرایش‌گر';
$labels['returnreceipt']  = 'رسید دریافت';
$labels['dsn']            = 'اعلام وضعیت دریافت';
$labels['mailreplyintro'] = 'در $date، $sender نوشته است:';
$labels['originalmessage'] = 'پیغام اصلی';

$labels['editidents']    = 'ویرایش شناسه‌ها';
$labels['spellcheck']    = 'املاء';
$labels['checkspelling'] = 'بررسی املایی';
$labels['resumeediting'] = 'ادامه‌ی ویرایش';
$labels['revertto']      = 'برگرداندن به';

$labels['attach'] = 'پیوست کردن';
$labels['attachments'] = 'پیوست‌ها';
$labels['upload'] = 'بارگذاری';
$labels['uploadprogress'] = '$percent ($current از$total)';
$labels['close']  = 'بستن';
$labels['messageoptions']  = 'گزینه‌های پیغام...';

$labels['low']     = 'کم';
$labels['lowest']  = 'کم‌ترین';
$labels['normal']  = 'معمولی';
$labels['high']    = 'زیاد';
$labels['highest'] = 'بیش‌ترین';

$labels['nosubject']  = '(بدون موضوع)';
$labels['showimages'] = 'نمایش عکس‌ها';
$labels['alwaysshow'] = 'همیشه عکس‌های دریافتی از $sender را نمایش داده شود';
$labels['isdraft']    = 'این یک پیغام پیش‌نویس است.';
$labels['andnmore']   = '$nr بیشتر...';
$labels['togglemoreheaders'] = 'نمایش سربرگ‌های بیشتر پیغام';
$labels['togglefullheaders'] = 'تعویض سربرگ‌های پیغام خام';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'متن ساده';
$labels['savesentmessagein'] = 'ذخیره پیغام ارسالی در';
$labels['dontsave'] = 'ذخیره نکن';
$labels['maxuploadsize'] = 'حجم پرونده حداکثر تا میزان $size می‌باشد';

$labels['addcc'] = 'افزودن رونوشت';
$labels['addbcc'] = 'افزودن رونوشت مخفی';
$labels['addreplyto'] = 'افزودن پاسخ-به';
$labels['addfollowupto'] = 'افزودن پیگیری-به';

// mdn
$labels['mdnrequest'] = 'فرستنده‌ی این پیغام خواسته است که وقتی شما این پیغام را می‌خوانید، آگاه شود. آیا مایلید فرستنده را آگاه سازید؟';
$labels['receiptread'] = 'رسید دریافت (خواندن)';
$labels['yourmessage'] = 'این یک رسید دریافت برای پیغام شماست';
$labels['receiptnote'] = 'تذکر: این رسید فقط تصدیق می‌کند که پیغام، روی رایانه گیرنده نمایش داده شده است. هیچ تضمینی نیست که گیرنده، محتوای پیغام را خوانده یا فهمیده باشد.';

// address boook
$labels['name']         = 'نمایش نام';
$labels['firstname']    = 'نام';
$labels['surname']      = 'نام خانوادگی';
$labels['middlename']   = 'نام وسط';
$labels['nameprefix']   = 'پیشوند';
$labels['namesuffix']   = 'پسوند';
$labels['nickname']     = 'نام مستعار';
$labels['jobtitle']     = 'عنوان شغل';
$labels['department']   = 'بخش';
$labels['gender']       = 'جنسیت';
$labels['maidenname']   = 'نام خانوادگی زن قبل از ازدواج';
$labels['email']        = 'پست‌الکترونیکی';
$labels['phone']        = 'تلفن';
$labels['address']      = 'نشانی';
$labels['street']       = 'خیابان';
$labels['locality']     = 'شهر';
$labels['zipcode']      = 'کدپستی';
$labels['region']       = 'ایالت/استان';
$labels['country']      = 'کشور';
$labels['birthday']     = 'زادروز';
$labels['anniversary']  = 'سالگرد';
$labels['website']      = 'وب‌سایت';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'یادداشت‌ها';
$labels['male']   = 'مرد';
$labels['female'] = 'زن';
$labels['manager'] = 'مدیر';
$labels['assistant'] = 'دستیار';
$labels['spouse'] = 'همسر';
$labels['allfields'] = 'همه زمینه‌ها';
$labels['search'] = 'جستجو';
$labels['advsearch'] = 'جستجوی پیشرفته';
$labels['advanced'] = 'پیشرفته';
$labels['other'] = 'سایر';

$labels['typehome']   = 'منزل';
$labels['typework']   = 'کار';
$labels['typeother']  = 'سایر';
$labels['typemobile']  = 'تلفن همراه';
$labels['typemain']  = 'اصلی';
$labels['typehomefax']  = 'فاکس خانگی';
$labels['typeworkfax']  = 'فاکس کاری';
$labels['typecar']  = 'اتومبیل';
$labels['typepager']  = 'پیجر';
$labels['typevideo']  = 'ویدئو';
$labels['typeassistant']  = 'دستیار';
$labels['typehomepage']  = 'صفحه خانگی';
$labels['typeblog'] = 'بلاگ';
$labels['typeprofile'] = 'نمایه';

$labels['addfield'] = 'افزودن زمینه...';
$labels['addcontact'] = 'افزودن تماس جدید';
$labels['editcontact'] = 'ویرایش تماس';
$labels['contacts'] = 'تماس‌ها';
$labels['contactproperties'] = 'خصوصیات تماس';
$labels['personalinfo'] = 'اطلاعات شخصی';

$labels['edit']   = 'ویرایش';
$labels['cancel'] = 'لغو';
$labels['save']   = 'ذخیره';
$labels['delete'] = 'حذف';
$labels['rename'] = 'تغییر نام';
$labels['addphoto'] = 'افزودن';
$labels['replacephoto'] = 'جایگزینی';
$labels['uploadphoto'] = 'بارگذاری تصویر';

$labels['newcontact']     = 'ایجاد کارت تماس جدید';
$labels['deletecontact']  = 'حذف تماس‌های انتخاب شده';
$labels['composeto']      = 'نوشتن پیغام به';
$labels['contactsfromto'] = 'تماس‌های $from تا $to از $count';
$labels['print']          = 'چاپ';
$labels['export']         = 'صدور';
$labels['exportall']      = 'خارج کردن همه';
$labels['exportsel']      = 'خارج کردن انتخاب شده‌ها';
$labels['exportvcards']   = 'صدور اطلاعات تماس‌ها با فرمت vCard';
$labels['newcontactgroup'] = 'افزودن گروه تماس جدید';
$labels['grouprename']    = 'تغییر نام گروه';
$labels['groupdelete']    = 'حذف گروه';
$labels['groupremoveselected'] = 'حذف نشانی‌های انتخاب شده از گروه';

$labels['previouspage']   = 'نمایش صفحه قبلی';
$labels['firstpage']      = 'نمایش صفحه اول';
$labels['nextpage']       = 'نمایش صفحه بعدی';
$labels['lastpage']       = 'نمایش صفحه آخر';

$labels['group'] = 'گروه';
$labels['groups'] = 'گروه‌ها';
$labels['personaladrbook'] = 'نشانی‌های شخصی';

$labels['searchsave'] = 'ذخیره جستجو';
$labels['searchdelete'] = 'حذف جستجو';

$labels['import'] = 'وارد کردن';
$labels['importcontacts'] = 'وارد کردن تماس‌ها';
$labels['importfromfile'] = 'وارد کردن از پرونده‌ی:';
$labels['importtarget'] = 'افزودن تماس‌های جدید به دفتر نشانی:';
$labels['importreplace'] = 'جایگیزینی کامل دفتر نشانی';
$labels['importdesc'] = 'شما می‌توانید تماس‌ها را از یک دفتر نشانی موجود بارگذاری نمایید.<br/> هم اکنون ما وارد کردن آدرس‌ها را از <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> یا قالب داده CVS (جدا شده با کاما) پشتیبانی می‌کنیم.';
$labels['done'] = 'انجام شد';

// settings
$labels['settingsfor'] = 'تنظیمات برای';
$labels['about'] = 'درباره';
$labels['preferences'] = 'ترجیحات';
$labels['userpreferences'] = 'ترجیحات کاربر';
$labels['editpreferences'] = 'ویرایش ترجیحات کاربر';

$labels['identities'] = 'شناسه‌ها';
$labels['manageidentities'] = 'مدیریت شناسه‌ها برای این حساب';
$labels['newidentity'] = 'شناسه جدید';

$labels['newitem'] = 'مورد جدید';
$labels['edititem'] = 'ویرایش مورد';

$labels['preferhtml'] = 'HTML نمایش';
$labels['defaultcharset'] = 'مجموعه نمایه پیش‌فرض';
$labels['htmlmessage'] = 'HTML پیغام';
$labels['messagepart'] = 'قسمت';
$labels['digitalsig'] = 'امضای دیجیتال';
$labels['dateformat'] = 'قالب تاریخ';
$labels['timeformat'] = 'قالب زمان';
$labels['prettydate'] = 'تاریخ‌های زیبا';
$labels['setdefault']  = 'پیش‌فرض کن';
$labels['autodetect']  = 'خودکار';
$labels['language']  = 'زبان';
$labels['timezone']  = 'محدوده‌ی زمانی';
$labels['pagesize']  = 'سطر در صفحه';
$labels['signature'] = 'امضاء';
$labels['dstactive']  = 'ساعت تابستانی';
$labels['showinextwin'] = 'نمایش پیغام در پنجره جدید';
$labels['composeextwin'] = 'نوشتن پیغام  در پنجره جدید';
$labels['htmleditor'] = 'HTML ‌نوشتن پیغام‌های';
$labels['htmlonreply'] = 'فقط در پاسخ به پیغام HTML';
$labels['htmlonreplyandforward'] = 'در بازگردانی یا پاسخ به پیغام‌های HTML';
$labels['htmlsignature'] = 'HTML امضای';
$labels['previewpane'] = 'نمایش قاب پیش‌نمایش';
$labels['skin'] = 'پوسته ظاهری';
$labels['logoutclear'] = 'تمیز کردن زباله دان هنگام برون‌رفت';
$labels['logoutcompact'] = 'فشرده‌سازی صندوق در هنگام خروچ';
$labels['uisettings'] = 'محیط کاربری';
$labels['serversettings'] = 'تنظیمات سرور';
$labels['mailboxview'] = 'نمایش صندوق‌پستی';
$labels['mdnrequests'] = 'تاییدهای فرستنده';
$labels['askuser'] = 'پرسش از کاربر';
$labels['autosend'] = 'ارسال رسید';
$labels['autosendknown'] = 'ارسال رسید به تماس‌های من، در غیر این صورت سوال کن';
$labels['autosendknownignore'] = 'ارسال رسید به تماس‌های من، درغیر این صورت چشم‌پوشی کن';
$labels['ignore'] = 'نادیده‌گیری';
$labels['readwhendeleted'] = 'در هنگام پاک کردن، پیغام را به عنوان خوانده شده علامت بزن';
$labels['flagfordeletion'] = 'پیغام را به جای پاک کردن، با پرچم نشانه‌گذاری کن';
$labels['skipdeleted'] = 'پیغام‌های حذف شده را نمایش نده';
$labels['deletealways'] = 'اگر انتقال پیغام به زباله‌دان ناموفق بود، آن‌ها را حذف کن';
$labels['deletejunk'] = 'حذف مستقیم پیغام‌های زباله';
$labels['showremoteimages'] = 'تصاویر پیوند شده را نمایش بده';
$labels['fromknownsenders'] = 'از فرستنده شناس';
$labels['always'] = 'همواره';
$labels['showinlineimages'] = 'نمایش عکس‌های پیوست شده در پایین پیغام';
$labels['autosavedraft']  = 'ذخیره‌ی خودکار پیش‌نویس';
$labels['everynminutes']  = 'در هر $n دقیقه';
$labels['refreshinterval']  = 'تازه کردن (بررسی برای پیغام‌های جدید، مثلا)';
$labels['never']  = 'هرگز';
$labels['immediately']  = 'بلافاصله';
$labels['messagesdisplaying'] = 'نمایش دادن پیغام‌ها';
$labels['messagescomposition'] = 'نوشتن پیغام‌ها';
$labels['mimeparamfolding'] = 'عناوین پیوستی';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (سایر)';
$labels['force7bit'] = 'از MIME encoding برای کارکترهای ۸-بیتی استفاده کن';
$labels['advancedoptions'] = 'گزینه‌های پیشرفته';
$labels['focusonnewmessage'] = 'مشخص نمودن پیغام جدید';
$labels['checkallfolders'] = 'همه پوشه‌ها را برای مشاهده پیغام‌های جدید بررسی کن';
$labels['displaynext'] = 'بعد از انتقال/حذف پیغام، پیغام بعدی نمایش داده شود';
$labels['defaultfont'] = 'فونت پیش فرض برای پیغام HTML';
$labels['mainoptions'] = 'گزینه‌های اصلی';
$labels['browseroptions'] = 'گزینه‌‌های مرورگر';
$labels['section'] = 'بخش';
$labels['maintenance'] = 'نگهداشت';
$labels['newmessage'] = 'پیغام جدید';
$labels['signatureoptions'] = 'گزینه‌های امضاء';
$labels['whenreplying'] = 'هنگام پاسخ';
$labels['replyempty'] = 'نقل قول نکردن پیغام اصلی';
$labels['replytopposting'] = 'شروع پیغام جدید بالای اصلی';
$labels['replybottomposting'] = 'شروع پیغام جدید پایین اصلی';
$labels['replyremovesignature'] = 'هنگام پاسخ امضاء اصلی را از پیغام پاک کن';
$labels['autoaddsignature'] = 'اضافه کردن خودکار امضاء';
$labels['newmessageonly'] = 'فقط پیغام جدید';
$labels['replyandforwardonly'] = 'فقط پاسخ‌ها و ارجاع‌ها';
$labels['replysignaturepos'] = 'هنگام پاسخ یا ارجاع امضاء را قرار داده شود';
$labels['belowquote'] = 'زیر نقل‌قول';
$labels['abovequote'] = 'بالای نقل‌قول';
$labels['insertsignature'] = 'درج امضاء';
$labels['previewpanemarkread']  = 'نشانه‌گذاری پیغام‌های پیش مرور شده به عنوان خوانده شده';
$labels['afternseconds']  = 'بعد از $s ثانیه';
$labels['reqmdn'] = 'درخواست همیشگی رسید برگشت';
$labels['reqdsn'] = 'درخواست همیشگی تذکر وضعیت تحویل';
$labels['replysamefolder'] = 'پاسخ‌ها را در پوشه‌ای پیغام‌هایی که باید پاسخ داده شود گرفته بده.';
$labels['defaultabook'] = 'دفترچه آدرس پیش‌فرض';
$labels['autocompletesingle'] = 'رد کردن نشانی‌های پست الکترونیک دیگر در تکمیل خودکار';
$labels['listnamedisplay'] = 'فهرست تماس‌ها به عنوان';
$labels['spellcheckbeforesend'] = 'بررسی املایی قبل از ارسال پیغام';
$labels['spellcheckoptions'] = 'گزینه‌های بررسی املاء';
$labels['spellcheckignoresyms'] = 'نادیده گرفتن کلمات دارای نماد';
$labels['spellcheckignorenums'] = 'نادیده گرفتن کلمات دارای عدد';
$labels['spellcheckignorecaps'] = 'نادیده گرفتن کلمات با حروف بزرگ';
$labels['addtodict'] = 'اضافه کردن به واژه‌نامه';
$labels['mailtoprotohandler'] = 'ثبت نگه‌دارنده پروتوکل برای mailto: پیوندها';
$labels['forwardmode'] = 'بازگردانی پیغام';
$labels['inline'] = 'خطی';
$labels['asattachment'] = 'به عنوان پیوست';

$labels['folder']  = 'پوشه';
$labels['folders']  = 'پوشه‌ها';
$labels['foldername']  = 'نام پوشه';
$labels['subscribed']  = 'مشترک شد';
$labels['messagecount'] = 'پیغام‌ها';
$labels['create']  = 'ایجاد';
$labels['createfolder']  = 'ایجاد پوشه‌ی جدید';
$labels['managefolders']  = 'مدیریت پوشه‌ها';
$labels['specialfolders'] = 'پوشه‌های ویژه';
$labels['properties'] = 'خصوصیات';
$labels['folderproperties'] = 'خصوصیات پوشه';
$labels['parentfolder'] = 'پوشه والد';
$labels['location'] = 'موقعیت';
$labels['info'] = 'اطلاعات';
$labels['getfoldersize'] = 'برای فهمیدن حجم پوشه کلیک کنید';
$labels['changesubscription'] = 'برای تغییر اشتراک کلیک کنید';
$labels['foldertype'] = 'نوع پوشه';
$labels['personalfolder']  = 'پوشه خصوصی';
$labels['otherfolder']  = 'پوشه کاربران دیگر';
$labels['sharedfolder']  = 'پوشه عمومی';

$labels['sortby'] = 'مرتب‌سازی بر اساس';
$labels['sortasc']  = 'مرتب‌سازی صعودی';
$labels['sortdesc'] = 'مرتب‌سازی نزولی';
$labels['undo'] = 'برگردان';

$labels['installedplugins'] = 'افزونه‌های نصب شده';
$labels['plugin'] = 'افزونه';
$labels['version'] = 'نسخه';
$labels['source'] = 'منبع';
$labels['license'] = 'گواهینامه';
$labels['support'] = 'پشتیبانی';

// units
$labels['B'] = 'بایت';
$labels['KB'] = 'کیلوبایت';
$labels['MB'] = 'مگابایت';
$labels['GB'] = 'گیگابایت';

// character sets
$labels['unicode'] = 'یونیکد';
$labels['english'] = 'انگلیسی';
$labels['westerneuropean'] = 'اروپای غربی';
$labels['easterneuropean'] = 'اروپای شرقی';
$labels['southeasterneuropean'] = 'اروپای جنوب شرقی';
$labels['baltic'] = 'بالتیک';
$labels['cyrillic'] = 'سیریلیک';
$labels['arabic'] = 'عربی';
$labels['greek'] = 'یونانی';
$labels['hebrew'] = 'عبری';
$labels['turkish'] = 'ترکی استانبولی';
$labels['nordic'] = 'شمالی';
$labels['thai'] = 'تایلندی';
$labels['celtic'] = 'سلتی';
$labels['vietnamese'] = 'ویتنامی';
$labels['japanese'] = 'ژاپنی';
$labels['korean'] = 'کره‌ای';
$labels['chinese'] = 'چینی';

?>
