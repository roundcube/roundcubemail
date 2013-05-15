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
$labels['welcome']   = 'مرحباً بكم في $product';
$labels['username']  = 'اسم المستخدم';
$labels['password']  = 'كلمة المرور';
$labels['server']    = 'الخادم';
$labels['login']     = 'تسجيل الدخول';

// taskbar
$labels['logout']   = 'تسجيل الخروج';
$labels['mail']     = 'البريد';
$labels['settings'] = 'الإعدادات الشخصية';
$labels['addressbook'] = 'دفتر العناوين';

// mailbox names
$labels['inbox']  = 'الوارد';
$labels['drafts'] = 'المسودات';
$labels['sent']   = 'المرسل';
$labels['trash']  = 'المهملات';
$labels['junk']   = 'غير المرغوب';

// message listing
$labels['subject'] = 'الموضوع';
$labels['from']    = 'المرسل';
$labels['sender']  = 'Sender';
$labels['to']      = 'المستقبل';
$labels['cc']      = 'نسخة';
$labels['bcc']     = 'نسخة مخفية';
$labels['replyto'] = 'الرد إلى';
$labels['followupto'] = 'Mail-Followup-To';
$labels['date']    = 'التاريخ';
$labels['size']    = 'الحجم';
$labels['priority'] = 'الأولوية';
$labels['organization'] = 'المنظمة';
$labels['readstatus'] = 'حالة القراءة';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'مجلدات';
$labels['messagesfromto'] = 'الرسالة $from إلى $to من مجموع $count';
$labels['threadsfromto'] = 'جهات الإتصال $from إلى $to من مجموع $count';
$labels['messagenrof'] = 'الرسالة $nr من $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'نسخ';
$labels['move']     = 'نقل';
$labels['moveto']   = 'نقل إلى...';
$labels['download'] = 'تنزيل';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'اسم الملف';
$labels['filesize'] = 'حجم الملف';

$labels['addtoaddressbook'] = 'إضافة إلى دفتر العناوين';

// weekdays short
$labels['sun'] = 'أحد';
$labels['mon'] = 'إثنين';
$labels['tue'] = 'ثلاثاء';
$labels['wed'] = 'أربعاء';
$labels['thu'] = 'خميس';
$labels['fri'] = 'جمعة';
$labels['sat'] = 'سبت';

// weekdays long
$labels['sunday']    = 'الأحد';
$labels['monday']    = 'الإثنين';
$labels['tuesday']   = 'الثلاثاء';
$labels['wednesday'] = 'الأربعاء';
$labels['thursday']  = 'الخميس';
$labels['friday']    = 'الجمعة';
$labels['saturday']  = 'السبت';

// months short
$labels['jan']	= 'يناير';
$labels['feb']	= 'فبراير';
$labels['mar']	= 'مارس';
$labels['apr']	= 'أبريل';
$labels['may']	= 'مايو';
$labels['jun']	= 'يونيو';
$labels['jul'] 	= 'يوليو';
$labels['aug']	= 'أغسطس';
$labels['sep']	= 'سبتمبر';
$labels['oct']	= 'أكتوبر';
$labels['nov']	= 'نوفمبر';
$labels['dec']	= 'ديسمبر';

// months long
$labels['longjan']	= 'يناير';
$labels['longfeb']	= 'فبراير';
$labels['longmar']	= 'مارس';
$labels['longapr']	= 'إبريل';
$labels['longmay']	= 'مايو';
$labels['longjun']	= 'يونيو';
$labels['longjul']	= 'يوليو';
$labels['longaug']	= 'أغسطس';
$labels['longsep']	= 'سبتمبر';
$labels['longoct']	= 'أكتوبر';
$labels['longnov']	= 'نوفمبر';
$labels['longdec']	= 'ديسمبر';

$labels['today'] = 'اليوم';

// toolbar buttons
$labels['refresh']          = 'تحديث';
$labels['checkmail']        = 'التحقق من وجود رسائل جديدة';
$labels['compose']          = 'إنشاء رسالة';
$labels['writenewmessage']  = 'إنشاء رسالة جديدة';
$labels['reply']            = 'رد';
$labels['replytomessage']   = 'الرد على الرسالة';
$labels['replytoallmessage'] = 'الرد على المرسل وجميع مستقبلي الرسالة';
$labels['replyall']         = 'الرد على الجميع';
$labels['replylist']        = 'الرد على القائمة';
$labels['forward']          = 'إعادة توجيه';
$labels['forwardinline']    = 'تمرير الرسالة ضمنياً';
$labels['forwardattachment'] = 'تمرير الرسالة كمرفق';
$labels['forwardmessage']   = 'تمرير الرسالة';
$labels['deletemessage']    = 'حذف الرسالة إلى المهملات';
$labels['movemessagetotrash'] = 'انقل الرسالة إلى سلة المهملات';
$labels['printmessage']     = 'طباعة هذه الرسالة';
$labels['previousmessage']  = 'عرض الرسالة السابقة';
$labels['firstmessage']     = 'عرض الرسالة الأولى';
$labels['nextmessage']      = 'عرض الرسالة التالية';
$labels['lastmessage']      = 'عرض الرسالة الأخيرة';
$labels['backtolist']       = 'العودة إلى لائحة الرسائل';
$labels['viewsource']       = 'إظهار المصدر';
$labels['mark']             = 'علامة';
$labels['markmessages']     = 'وسم الرسالة';
$labels['markread']         = 'كمقروءة';
$labels['markunread']       = 'كغير مقروءة';
$labels['markflagged']      = 'كموْسومة';
$labels['markunflagged']    = 'كغير موْسومة';
$labels['moreactions']      = 'إجراءات إضافية...';
$labels['more']             = 'المزيد';
$labels['back']             = 'العودة';
$labels['options']          = 'خيارات';

$labels['select'] = 'تحديد';
$labels['all'] = 'الكل';
$labels['none'] = 'لاشيء';
$labels['currpage'] = 'الصفحة الحالية';
$labels['unread'] = 'غير المقروءة';
$labels['flagged'] = 'موسوم';
$labels['unanswered'] = 'بلا رد';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'محذوف';
$labels['undeleted'] = 'غير محذوفة';
$labels['invert'] = 'عكس';
$labels['filter'] = 'تصفية';
$labels['list'] = 'قائمة';
$labels['threads'] = 'تتابعات';
$labels['expand-all'] = 'إظهار الكل';
$labels['expand-unread'] = 'إظهار الغير مقروءة';
$labels['collapse-all'] = 'ضمّ الكل';
$labels['threaded'] = 'متتابع';

$labels['autoexpand_threads'] = 'إظهار تتابعات الرسائل';
$labels['do_expand'] = 'جميع التتابعات';
$labels['expand_only_unread'] = 'فقط مع الرسائل الغير مقروءة';
$labels['fromto'] = 'المرسل/المستلم';
$labels['flag'] = 'وسم';
$labels['attachment'] = 'مرفق';
$labels['nonesort'] = 'لاشيء';
$labels['sentdate'] = 'تاريخ إرسالها';
$labels['arrival'] = 'تاريخ وصولها';
$labels['asc'] = 'تصاعدي';
$labels['desc'] = 'تنازلي';
$labels['listcolumns'] = 'أعمدة السرد';
$labels['listsorting'] = 'أعمدة الترتيب';
$labels['listorder'] = 'طريقة الترتيب';
$labels['listmode'] = 'وضع طريقة السرد';

$labels['folderactions'] = 'إجراءات المجلد...';
$labels['compact'] = 'ضغط';
$labels['empty'] = 'تفريغ';

$labels['quota'] = 'المساحة المستخدمة';
$labels['unknown']  = 'مجهول';
$labels['unlimited']  = 'غير محدود';

$labels['quicksearch']  = 'البحث السريع';
$labels['resetsearch']  = 'استعادة البحث للافتراضي';
$labels['searchmod']  = 'أماكن البحث';
$labels['msgtext']  = 'كامل الرسالة';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'افتح في نافذة جديدة';
$labels['emlsave'] = 'تنزيل (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'تعديل كجديد';
$labels['send']           = 'ارسل';
$labels['sendmessage']    = 'إرسال الرسالة الآن';
$labels['savemessage']    = 'حفظ هذه المسودة';
$labels['addattachment']  = 'إرفاق ملف';
$labels['charset']        = 'مجموعة المحارف';
$labels['editortype']     = 'نوع المُحرّر';
$labels['returnreceipt']  = 'ايصال استلام';
$labels['dsn']            = 'تنويه حالة التوصيل';
$labels['mailreplyintro'] = 'كتب $sender في $date:';
$labels['originalmessage'] = 'الرسالة الأساسية';

$labels['editidents']    = 'تعديل الهويات';
$labels['spellcheck']    = 'التدقيق اﻹملائى';
$labels['checkspelling'] = 'التدقيق الإملائي';
$labels['resumeediting'] = 'متابعة التحرير';
$labels['revertto']      = 'استعادة إلى';

$labels['attach'] = 'إرفاق';
$labels['attachments'] = 'مرفقات';
$labels['upload'] = 'تحميل';
$labels['uploadprogress'] = '$percent ($current من $total)';
$labels['close']  = 'إغلاق';
$labels['messageoptions']  = 'خيارات الرسائل...';

$labels['low']     = 'منخض';
$labels['lowest']  = 'الأقل';
$labels['normal']  = 'عادي';
$labels['high']    = 'مرتفع';
$labels['highest'] = 'الأعلى';

$labels['nosubject']  = '(دون موضوع)';
$labels['showimages'] = 'إظهار الصور';
$labels['alwaysshow'] = 'دائماً أظهر الصّور القادمة من $sender';
$labels['isdraft']    = 'هذه مسودّة رسالة';
$labels['andnmore']   = '$nr المزيد ...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'نص مجرد';
$labels['savesentmessagein'] = 'احفظ الرسالة المُرسلة في';
$labels['dontsave'] = 'لا تحفظها';
$labels['maxuploadsize'] = 'أقصى حجم للملف هو $size';

$labels['addcc'] = 'أضف CC';
$labels['addbcc'] = 'أضف BCC';
$labels['addreplyto'] = 'أضيف Reply-To';
$labels['addfollowupto'] = 'إضافة Mail-Followup-To';

// mdn
$labels['mdnrequest'] = 'طلب المرسل إعلامه عند قراءتك لرسالته. هل تود إعلام المرسل؟';
$labels['receiptread'] = 'إيصال الاستلام )بالقراءة)';
$labels['yourmessage'] = 'هذا إيصال بقراءة رسالتك';
$labels['receiptnote'] = 'ملاحظة: يُعلمك هذا الإيصال بأن رسالتك قد ظهرت على جهاز المستقبِل. ليست هناك أية ضمانات أن المستقبل قد قرأ أو فهم محتويات الرسالة.';

// address boook
$labels['name']         = 'اسم العرض';
$labels['firstname']    = 'الإسم الأول';
$labels['surname']      = 'الإسم الأخير';
$labels['middlename']   = 'الاسم الثاني';
$labels['nameprefix']   = 'السابقة';
$labels['namesuffix']   = 'اللاحقة';
$labels['nickname']     = 'اللقب';
$labels['jobtitle']     = 'المسمى الوظيفي';
$labels['department']   = 'الإدارة';
$labels['gender']       = 'الجنس';
$labels['maidenname']   = 'إسم الزواج';
$labels['email']        = 'البريد الإلكتروني';
$labels['phone']        = 'الهاتف';
$labels['address']      = 'العنوان';
$labels['street']       = 'الشارع';
$labels['locality']     = 'المدينة';
$labels['zipcode']      = 'الرمز البريدي';
$labels['region']       = 'المنطقة';
$labels['country']      = 'الدولة';
$labels['birthday']     = 'تاريخ الميلاد';
$labels['anniversary']  = 'المناسبة السنوية';
$labels['website']      = 'الموقع';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'ملاحظات';
$labels['male']   = 'ذكر';
$labels['female'] = 'أنثى';
$labels['manager'] = 'مدير';
$labels['assistant'] = 'المساعد';
$labels['spouse'] = 'زوجة';
$labels['allfields'] = 'جميع الحقول';
$labels['search'] = 'بحث';
$labels['advsearch'] = 'بحث متقدم';
$labels['advanced'] = 'مُتقدم';
$labels['other'] = 'آخر';

$labels['typehome']   = 'المنزل';
$labels['typework']   = 'العمل';
$labels['typeother']  = 'آخر';
$labels['typemobile']  = 'المحمول';
$labels['typemain']  = 'الرئيسي';
$labels['typehomefax']  = 'فاكس المنزل';
$labels['typeworkfax']  = 'فاكس العمل';
$labels['typecar']  = 'السيارة';
$labels['typepager']  = 'الرنان';
$labels['typevideo']  = 'الفيديو';
$labels['typeassistant']  = 'المساعد';
$labels['typehomepage']  = 'الرئيسية';
$labels['typeblog'] = 'المدوّنة';
$labels['typeprofile'] = 'الملف الشخصي';

$labels['addfield'] = 'إضافة حقل...';
$labels['addcontact'] = 'إضافة المراسل المحدد إلى دفتر عناويني';
$labels['editcontact'] = 'تحرير بيانات المراسل';
$labels['contacts'] = 'المراسلين';
$labels['contactproperties'] = 'خواص جهة الإتصال';
$labels['personalinfo'] = 'المعلومات الشخصية';

$labels['edit']   = 'تحرير';
$labels['cancel'] = 'إلغاء';
$labels['save']   = 'حفظ';
$labels['delete'] = 'حذف';
$labels['rename'] = 'إعادة التسمية';
$labels['addphoto'] = 'إضافة';
$labels['replacephoto'] = 'استبدال';
$labels['uploadphoto'] = 'ارفع صورة';

$labels['newcontact']     = 'إنشاء بطاقة مراسل جديدة';
$labels['deletecontact']  = 'حذف المراسلين المحددين';
$labels['composeto']      = 'إنشاء رسالة إليه';
$labels['contactsfromto'] = 'المراسلون $from إلى $to من $count';
$labels['print']          = 'طباعة';
$labels['export']         = 'تصدير';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'صدّر المراسلين بنسق vCard';
$labels['newcontactgroup'] = 'إنشاء مجموعة مراسلين جديدة';
$labels['grouprename']    = 'تغيير اسم المجموعة';
$labels['groupdelete']    = 'حذف المجموعة';
$labels['groupremoveselected'] = 'حذف العنوان المختار من المجموعة';

$labels['previouspage']   = 'عرض المجموعة السابقة';
$labels['firstpage']      = 'عرض المجموعة الأولى';
$labels['nextpage']       = 'عرض المجموعة التالية';
$labels['lastpage']       = 'عرض المجموعة الأخيرة';

$labels['group'] = 'مجموعة';
$labels['groups'] = 'المجموعات';
$labels['personaladrbook'] = 'العناوين الشخصية';

$labels['searchsave'] = 'حفظ البحث';
$labels['searchdelete'] = 'حذف البحث';

$labels['import'] = 'استورد';
$labels['importcontacts'] = 'استورد المراسلين';
$labels['importfromfile'] = 'استورد من ملف:';
$labels['importtarget'] = 'أضف المراسلين الجدد إلى دفتر العناوين:';
$labels['importreplace'] = 'استبدل دفتر العناوين بأكمله';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'تم';

// settings
$labels['settingsfor'] = 'إعدادات';
$labels['about'] = 'نبذة';
$labels['preferences'] = 'التفضيلات';
$labels['userpreferences'] = 'تفضيلات المستخدم';
$labels['editpreferences'] = 'تحرير تفضيلات المستخدم';

$labels['identities'] = 'الهويات';
$labels['manageidentities'] = 'إدارة هويات هذا الحساب';
$labels['newidentity'] = 'هوية جديدة';

$labels['newitem'] = 'عنصر جديد';
$labels['edititem'] = 'تحرير عنصر';

$labels['preferhtml'] = 'تفضيل صيغة HTML';
$labels['defaultcharset'] = 'ترميز المحارف الافتراضي';
$labels['htmlmessage'] = 'رسالة بنسق HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'نسق التاريخ';
$labels['timeformat'] = 'نسق الوقت';
$labels['prettydate'] = 'تواريخ منمقة';
$labels['setdefault']  = 'تعيين كإفتراضي';
$labels['autodetect']  = 'آلي';
$labels['language']  = 'اللغة';
$labels['timezone']  = 'المنطقة الزمنية';
$labels['pagesize']  = 'عدد الرسائل في الصفحة';
$labels['signature'] = 'التوقيع';
$labels['dstactive']  = 'التوقيت الصيفي';
$labels['showinextwin'] = 'فتح الرسالة فى نافذة جديدة';
$labels['composeextwin'] = 'أرسال عن طريق نافذة جديدة';
$labels['htmleditor'] = 'إنشاء رسائل بنسق HTML';
$labels['htmlonreply'] = 'حال الرد على الرسائل بصيغة HTML فقط';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'توقيع HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'إظهار قسم المعاينة';
$labels['skin'] = 'شكل الواجهة';
$labels['logoutclear'] = 'إفراغ سلة المحذوفات عند الخروج';
$labels['logoutcompact'] = 'ضغط صندوق الوارد عن الخروج';
$labels['uisettings'] = 'واجهة المستخدم';
$labels['serversettings'] = 'إعدادات الخادم';
$labels['mailboxview'] = 'مظهر صندوق البريد';
$labels['mdnrequests'] = 'عند طلب إشعار استلام';
$labels['askuser'] = 'اسأل المستخدم';
$labels['autosend'] = 'أرسلها تلقائياً';
$labels['autosendknown'] = 'أرسل إشعار استلام إلى جهات اتصالي، وإلااسألني';
$labels['autosendknownignore'] = 'أرسل إشعار استلام إلى جهات اتصالي، وإلاتجاهل الأمر';
$labels['ignore'] = 'تجاهلها';
$labels['readwhendeleted'] = 'تحديد الرسالة كمقروءة عند حذفها';
$labels['flagfordeletion'] = 'وضع علامة على الرسالة لحذفها بدلاً منحذفها';
$labels['skipdeleted'] = 'لا تظهر الرسائل المحذوفة';
$labels['deletealways'] = 'حذف الرسائل مباشرة، إن تعذر نقلها إلىالمهملات';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'إظهار الصور الخارجية المدمجة';
$labels['fromknownsenders'] = 'للمرسلين المعلومين';
$labels['always'] = 'دائماً';
$labels['showinlineimages'] = 'إظهار الصّور المرفقة أسفل الرسالة';
$labels['autosavedraft']  = 'حفظ المسودة تلقائياً';
$labels['everynminutes']  = 'كل $n دقيقة';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'أبداً';
$labels['immediately']  = 'حالاً';
$labels['messagesdisplaying'] = 'إظهار الرسائل';
$labels['messagescomposition'] = 'إنشاء الرسائل';
$labels['mimeparamfolding'] = 'أسماء الملفات المرفقة';
$labels['2231folding'] = 'RFC 2231 بأكملها (ثندربيرد)';
$labels['miscfolding'] = 'RFC 2047/2231 (ميكروسوفت آوتلوك)';
$labels['2047folding'] = 'RFC 2047 بأكملها (غيرها)';
$labels['force7bit'] = 'استخدام ترميز MIME للحروف ذات الثمانيةبتّات';
$labels['advancedoptions'] = 'خيارات متقدّمة';
$labels['focusonnewmessage'] = 'التركيز على نافذة المتصفح عند وجود رسالةجديدة';
$labels['checkallfolders'] = 'التحقق من وجود رسائل جديدة في جميعالمجلدات';
$labels['displaynext'] = 'إظهار الرسالة التالية بعد حذف/نقلالرسالة الحالية';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'الخيارات الأساسية';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'قسم';
$labels['maintenance'] = 'الصيانة';
$labels['newmessage'] = 'رسالة جديدة';
$labels['signatureoptions'] = 'خيارات التوقيع';
$labels['whenreplying'] = 'عند الرد';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'بدء رسالة جديدة ووضع النص أعلى الأصلية';
$labels['replybottomposting'] = 'بدء رسالة جديدة ووضع النص أسفل الأصلية';
$labels['replyremovesignature'] = 'إزالة التوقيع من الرسالة عند الرد';
$labels['autoaddsignature'] = 'إضافة التوقيع آلياً';
$labels['newmessageonly'] = 'الرسالة الجديدة فقط';
$labels['replyandforwardonly'] = 'الردود والتمريرات فقط';
$labels['insertsignature'] = 'إضافة التوقيع';
$labels['previewpanemarkread']  = 'تحديد الرسائل المُعاينة كمقروءة';
$labels['afternseconds']  = 'بعد $n ثواني';
$labels['reqmdn'] = 'طلب إشعار استلام دائماً';
$labels['reqdsn'] = 'طلب تنويه لحالة التوصيل دائماً';
$labels['replysamefolder'] = 'وضع الردود في المجلد الذي يحوي الرسالةالمردود عليها';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'تجاهل عنوان البريد الإلكتروني البديل فيالتكملة التلقائية';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'تحقق من الإملاء قبل إرسال الرسائل';
$labels['spellcheckoptions'] = 'خيارات التدقيق الإملاءي';
$labels['spellcheckignoresyms'] = 'تجاهل الكلمات التي تحتوي رموز';
$labels['spellcheckignorenums'] = 'تجاهل الكلمات التي تحتوي أرقام';
$labels['spellcheckignorecaps'] = 'تجاهل الكلمات التي بها أحرف كبيرة';
$labels['addtodict'] = 'إضافة إلى القامس';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'مجلد';
$labels['folders']  = 'مجلدات';
$labels['foldername']  = 'اسم المجلد';
$labels['subscribed']  = 'مشترك';
$labels['messagecount'] = 'الرسائل';
$labels['create']  = 'أنشئ';
$labels['createfolder']  = 'أنشئ مجلد جديد';
$labels['managefolders']  = 'إدارة المجلدات';
$labels['specialfolders'] = 'المجلّدات المُميّزة';
$labels['properties'] = 'الخواص';
$labels['folderproperties'] = 'خواص المجلد';
$labels['parentfolder'] = 'المجلد الحاوي';
$labels['location'] = 'الموضع';
$labels['info'] = 'معلومات';
$labels['getfoldersize'] = 'اضغط لمعرفة حجم المجلد';
$labels['changesubscription'] = 'اضغط لتغيير حالة الإشتراك';
$labels['foldertype'] = 'نوع المجلد';
$labels['personalfolder']  = 'مجلد خاص';
$labels['otherfolder']  = 'مجلد مستخدم آخر';
$labels['sharedfolder']  = 'مجلد عام';

$labels['sortby'] = 'ترتيب بحسب';
$labels['sortasc']  = 'ترتيب تصاعدي';
$labels['sortdesc'] = 'ترتيب تنازلي';
$labels['undo'] = 'تراجع';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'إضافة';
$labels['version'] = 'النسخة';
$labels['source'] = 'المصدر';
$labels['license'] = 'الترخيص';
$labels['support'] = 'احصل على الدعم الفني';

// units
$labels['B'] = 'ب';
$labels['KB'] = 'ك.ب';
$labels['MB'] = 'م.ب';
$labels['GB'] = 'ج.ب';

// character sets
$labels['unicode'] = 'يونيكود';
$labels['english'] = 'الإنجليزية';
$labels['westerneuropean'] = 'الغرب أوروبية';
$labels['easterneuropean'] = 'الشرق أوروبية';
$labels['southeasterneuropean'] = 'الجنوب شرق أوروبية';
$labels['baltic'] = 'البلطيقية';
$labels['cyrillic'] = 'الكيريلية';
$labels['arabic'] = 'العربية';
$labels['greek'] = 'اليونانية';
$labels['hebrew'] = 'العبرية';
$labels['turkish'] = 'التركية';
$labels['nordic'] = 'النرويجية';
$labels['thai'] = 'التايلندية';
$labels['celtic'] = 'الكلتية';
$labels['vietnamese'] = 'الفيتنامية';
$labels['japanese'] = 'اليابانية';
$labels['korean'] = 'الكورية';
$labels['chinese'] = 'الصينية';

?>
