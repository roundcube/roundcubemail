<?php

/*

 +-----------------------------------------------------------------------+
 | language/ar/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Ossama Khayat <okhayat@yahoo.com>                             |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
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
$labels['sent']   = 'المرسل';
$labels['trash']  = 'المهملات';
$labels['drafts'] = 'المسودات';
$labels['junk']   = 'غير المرغوب';

// message listing
$labels['subject'] = 'الموضوع';
$labels['from']    = 'المرسل';
$labels['to']      = 'المستقبل';
$labels['cc']      = 'نسخة';
$labels['bcc']     = 'نسخة مخفية';
$labels['replyto'] = 'الرد إلى';
$labels['date']    = 'التاريخ';
$labels['size']    = 'الحجم';
$labels['priority'] = 'الأولوية';
$labels['organization'] = 'المنظمة';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'المجلدات';
$labels['messagesfromto'] = 'الرسالة $from إلى $to من $count';
$labels['messagenrof'] = 'الرسالة $nr من $count';

$labels['moveto']   = 'نقل إلى...';
$labels['download'] = 'تنزيل';

$labels['filename'] = 'اسم الملف';
$labels['filesize'] = 'حجم الملف';

$labels['preferhtml'] = 'تفضيل صيغة HTML';
$labels['htmlmessage'] = 'رسالة بنسق HTML';
$labels['prettydate'] = 'تواريخ منمقة';

$labels['addtoaddressbook'] = 'إضافة إلى دفتر العناوين';

// weekdays short
$labels['sun'] = 'أح';
$labels['mon'] = 'إث';
$labels['tue'] = 'ثل';
$labels['wed'] = 'أر';
$labels['thu'] = 'خم';
$labels['fri'] = 'جم';
$labels['sat'] = 'سب';

// weekdays long
$labels['sunday']    = 'الأحد';
$labels['monday']    = 'الإثنين';
$labels['tuesday']   = 'الثلاثاء';
$labels['wednesday'] = 'الأربعاء';
$labels['thursday']  = 'الخميس';
$labels['friday']    = 'الجمعة';
$labels['saturday']  = 'السبت';

$labels['today'] = 'اليوم';

// toolbar buttons
$labels['writenewmessage']  = 'إنشاء رسالة جديدة';
$labels['replytomessage']   = 'الرد على الرسالة';
$labels['replytoallmessage'] = 'الرد على المرسل وجميع مستقبلي الرسالة';
$labels['forwardmessage']   = 'تمرير الرسالة';
$labels['deletemessage']    = 'حذف الرسالة إلى المهملات';
$labels['printmessage']     = 'طباعة هذه الرسالة';
$labels['previousmessages'] = 'إظهار المجموعة السابقة من الرسائل';
$labels['nextmessages']     = 'إظهار المجموعة التالية من الرسائل';
$labels['backtolist']       = 'العودة إلى لائحة الرسائل';
$labels['viewsource']       = 'إظهار المصدر';

$labels['select'] = 'تحديد';
$labels['all'] = 'الكل';
$labels['none'] = 'لا شيء';
$labels['unread'] = 'غير المقروءة';

$labels['compact'] = 'ضغط';
$labels['empty'] = 'تفريغ';
$labels['purge'] = 'إزالة';

$labels['quota'] = 'المساحة المستخدمة';


// message compose
$labels['compose']  = 'إنشاء رسالة';
$labels['sendmessage']  = 'إرسال الرسالة الآن';
$labels['addattachment']  = 'إرفاق ملف';
$labels['charset']  = 'مجموعة المحارف';

$labels['attachments'] = 'مرفقات';
$labels['upload'] = 'تحميل';
$labels['close']  = 'إغلاق';

$labels['low']     = 'منخض';
$labels['lowest']  = 'الأقل';
$labels['normal']  = 'عادي';
$labels['high']    = 'مرتفع';
$labels['highest'] = 'الأعلى';

$labels['nosubject']  = '(دون موضوع)';
$labels['showimages'] = 'إظهار الصور';


// address boook
$labels['name']      = 'اسم العرض';
$labels['firstname'] = 'الإسم الأول';
$labels['surname']   = 'الإسم الأخير';
$labels['email']     = 'البريد الإلكتروني';

$labels['addcontact'] = 'إضافة مراسل جديد';
$labels['editcontact'] = 'تحرير بيانات المراسل';

$labels['edit']   = 'تحرير';
$labels['cancel'] = 'إلغاء';
$labels['save']   = 'حفظ';
$labels['delete'] = 'حذف';

$labels['newcontact']     = 'إنشاء بطاقة مراسل جديدة';
$labels['addcontact']     = 'إضافة المراسل المحدد إلى دفتر عناويني';
$labels['deletecontact']  = 'حذف المراسلين المحددينDelete selected contacts';
$labels['composeto']      = 'إنشاء رسالة إليه';
$labels['contactsfromto'] = 'المراسلون $from إلى $to من $count';
$labels['print']          = 'طباعة';
$labels['export']         = 'تصدير';

// LDAP search
$labels['ldapsearch'] = 'بحث في دليل LDAP';

$labels['ldappublicsearchname']    = 'إسم المراسل';
$labels['ldappublicsearchtype'] = 'مطابقة الأحرف؟';
$labels['ldappublicserverselect'] = 'اختيار الخوادم';
$labels['ldappublicsearchfield'] = 'بحث في';
$labels['ldappublicsearchform'] = 'البحث عن مراسل';
$labels['ldappublicsearch'] = 'بحث';


// settings
$labels['settingsfor']  = 'إعدادات';

$labels['preferences']  = 'التفضيلات';
$labels['userpreferences']  = 'تفضيلات المستخدم';
$labels['editpreferences']  = 'تحرير تفضيلات المستخدم';

$labels['identities']  = 'الهويات';
$labels['manageidentities']  = 'إدارة هويات هذا الحساب';
$labels['newidentity']  = 'هوية جديدة';

$labels['newitem']  = 'عنصر جديد';
$labels['edititem']  = 'تحرير عنصر';

$labels['setdefault']  = 'تعيين كإفتراضي';
$labels['language']  = 'اللغة';
$labels['timezone']  = 'المنطقة الزمنية';
$labels['pagesize']  = 'أسطر في الصفحة';
$labels['signature'] = 'التوقيع';

$labels['folder']  = 'مجلد';
$labels['folders']  = 'مجلدات';
$labels['foldername']  = 'اسم المجلد';
$labels['subscribed']  = 'مشترك';
$labels['create']  = 'إنشاء';
$labels['createfolder']  = 'إنشاء مجلد جديد';
$labels['deletefolder']  = 'حذف المجلد';
$labels['managefolders']  = 'إدارة المجلدات';

$labels['sortby'] = 'ترتيب بحسب';
$labels['sortasc']  = 'ترتيب تصاعدي';
$labels['sortdesc'] = 'ترتيب تنازلي';

// RC 0.1beta2

$labels['welcome'] = 'مرحباً بكم في $product';

$labels['unknown'] = 'مجهول';
$labels['unlimited'] = 'غير محدود';
$labels['dstactive'] = 'التوقيت الصيفي';

$labels['previouspage'] = 'عرض المجموعة السابقة';
$labels['nextpage']     = 'عرض المجموعة التالية';

$labels['returnreceipt'] = 'ايصال استلام';

$labels['checkmail'] = 'التحقق من وجود رسائل جديدة';
$labels['checkspelling'] = 'التدقيق الإملائي';
$labels['resumeediting'] = 'متابعة التحرير';
$labels['revertto'] = 'استعادة إلى';

$labels['savemessage']  = 'حفظ هذه المسودة';
$labels['rename'] = 'إعادة التسمية';
$labels['renamefolder'] = 'إعادة تسمية المجلد';

$labels['description'] = 'وصف';

?>