<?php

/*

+-----------------------------------------------------------------------+
| language/ar_SA/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Ossama Khayat <okhayat@yahoo.com>                             |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'فشل تسجيل الدخول';
$messages['cookiesdisabled'] = 'المتصفح الخاص بك لا يقبل الكوكيز';
$messages['sessionerror'] = 'انتهت صلاحية الجلسة الحالية أو أنها غير صالحة';
$messages['imaperror'] = 'فشل الاتصال بخادم IMAP';
$messages['servererror'] = 'خطأ في الخادم!';
$messages['servererrormsg'] = 'خطأ خادم: $msg';
$messages['errorreadonly'] = 'تعذر تنفيذ العملية. المجلد للقراءة فقط.';
$messages['errornoperm'] = 'تعذر تنفيذ العملية. ليست لديك الصلاحية.';
$messages['invalidrequest'] = 'طلب غير صالح! لم تحفظ أية بيانات.';
$messages['nomessagesfound'] = 'لم يعثر على أية رسائل في صندوق البريد هذا';
$messages['loggedout'] = 'قمت بإنهاء هذه الجلسة بنجاح. إلى اللقاء!';
$messages['mailboxempty'] = 'صندوق البريد فارغ';
$messages['loading'] = 'جاري العمل...';
$messages['uploading'] = 'جاري رفع الملف...';
$messages['loadingdata'] = 'تحميل البيانات...';
$messages['checkingmail'] = 'التحقق من وجود رسائل جديدة...';
$messages['sendingmessage'] = 'إرسال الرسالة...';
$messages['messagesent'] = 'تم إرسال الرسالة بنجاح';
$messages['savingmessage'] = 'حفظ الرسالة...';
$messages['messagesaved'] = 'تم حفظ الرسالة في المسودات';
$messages['successfullysaved'] = 'تم الحفظ بنجاح';
$messages['addedsuccessfully'] = 'تمت إضافة المراسل إلى دفتر العناوين بنجاح';
$messages['contactexists'] = 'هناك مراسل له نفس هذا البريد الالكتروني موجود مسبقاً';
$messages['blockedimages'] = 'لحماية خصوصيتك، تم حجب الصور الغير مضمنة (البعيدة) في هذه الرسالة.';
$messages['encryptedmessage'] = 'هذه الرسالة مشفرة ولا يمكن عرضها. عذراً!';
$messages['nocontactsfound'] = 'لم يعثر على أي مراسل';
$messages['contactnotfound'] = 'تعذر العثور على المراسل المطلوب';
$messages['sendingfailed'] = 'فشل إرسال الرسالة';
$messages['senttooquickly'] = 'رجاء انتظر $sec ثوان قبل إرسال هذه الرسالة';
$messages['errorsavingsent'] = 'حدث خطأ أثناء حفظ الرسالة المُرسلة';
$messages['errorsaving'] = 'حدث خطأ أثناء الحفظ';
$messages['errormoving'] = 'تعذر نقل هذه الرسالة';
$messages['errorcopying'] = 'تعذر نسخ الرسائل';
$messages['errordeleting'] = 'تعذر حذف هذه الرسالة';
$messages['errormarking'] = 'تعذر تحديد الرسائل';
$messages['deletecontactconfirm'] = 'هل تريد حقاً حذف المراسلـ(ين) المحدد(ين)؟';
$messages['deletemessagesconfirm'] = 'هل تريد حذف الرسائل المحددة؟';
$messages['deletefolderconfirm'] = 'هل تريد حقاً حذف هذا المجلد؟';
$messages['purgefolderconfirm'] = 'هل تريد حقاً حذف جميع الرسائل في هذا المجلد؟';
$messages['folderdeleting'] = 'جاري حذف المجلد...';
$messages['foldermoving'] = 'جاري نقل المجلد...';
$messages['foldersubscribing'] = 'الإشتراك في المجلد...';
$messages['folderunsubscribing'] = 'إلغاء الإشتراك في المجد...';
$messages['formincomplete'] = 'لم يتم تعبئة بيانات الاستمارة بالكامل';
$messages['noemailwarning'] = 'الرجاء إدخال عنوان بريد إلكتروني صالح';
$messages['nonamewarning'] = 'الرجاء إدخال اسم';
$messages['nopagesizewarning'] = 'الرجاء إدخال حجم الصفحة';
$messages['nosenderwarning'] = 'رجاء اكتب عنوان البريد الالكتروني للمُرسل';
$messages['norecipientwarning'] = 'الرجاء إدخال اسم مستقبل واحد على الأقل';
$messages['nosubjectwarning'] = 'حقل \"الموضوع\" فارغ. هل تريد كتابة موضوع للرسالة؟';
$messages['nobodywarning'] = 'إرسال هذه الرسالة دون نص؟';
$messages['notsentwarning'] = 'لم يتم إرسال الرسالة. هل تريد تجاهل الرسالة؟';
$messages['noldapserver'] = 'الرجاء اختيار خادم ldap للبحث فيه';
$messages['nocontactsreturned'] = 'لم يعثر على أي مراسلين';
$messages['nosearchname'] = 'الرجاء إدخال اسم مراسل أو عنوان بريد إلكتروني';
$messages['notuploadedwarning'] = 'لم يتم رفع جميع المرفقات بعد. رجاءً الانتظار أو إلغاء عملية الرفع.';
$messages['searchsuccessful'] = 'عثر على $nr رسائل';
$messages['searchnomatch'] = 'لم يعثر على شيء';
$messages['searching'] = 'جاري البحث...';
$messages['checking'] = 'جاري التحقق...';
$messages['nospellerrors'] = 'لم يعثر على أية أخطاء إملائية';
$messages['folderdeleted'] = 'تم حذف المجلد بنجاح';
$messages['foldersubscribed'] = 'تم الإشتراك في المجلد بنجاح';
$messages['folderunsubscribed'] = 'تم إلغاء الإشتراك في المجلد بنجاح';
$messages['folderpurged'] = 'تم تفريغ المجلد بنجاح';
$messages['folderexpunged'] = 'تم إفراغ المجلد بنجاح.';
$messages['deletedsuccessfully'] = 'تم الحذف بنجاح';
$messages['converting'] = 'إزالة التنسيق من الرسالة...';
$messages['messageopenerror'] = 'تعذرت قراءة الرسالة من الخادم';
$messages['fileuploaderror'] = 'فشل رفع الملف';
$messages['filesizeerror'] = 'حجم الملف الذي تحاول رفعه أكبر من الحجم الأقصى $size';
$messages['copysuccess'] = 'تم نسخ $nr رسائل بنجاح';
$messages['copyerror'] = 'لم يمكن نسخ أية عناوين';
$messages['sourceisreadonly'] = 'لا يمكن تعديل مصدر العنوان هذا';
$messages['errorsavingcontact'] = 'تعذر حفظ عنوان المراسل';
$messages['movingmessage'] = 'جاري نقل الرسالة...';
$messages['copyingmessage'] = 'جاري نسخ الرسالة...';
$messages['deletingmessage'] = 'جاري حذف الرسائل...';
$messages['markingmessage'] = 'جاري تحديد الرسائل...';
$messages['receiptsent'] = 'أرسل إيصال الاستلام بنجاح';
$messages['errorsendingreceipt'] = 'تعذر إرسال إيصال الاستلام';
$messages['nodeletelastidentity'] = 'لا يمكن حذف هذا السجل حيث أنه الوحيد المتبقي.';
$messages['forbiddencharacter'] = 'اسم المجلّد يحتوي حروفاً ممنوعة';
$messages['selectimportfile'] = 'رجاء اختر ملفاً لرفعه';
$messages['addresswriterror'] = 'دفتر العناوين المحدد غير قابل للكتابة';
$messages['contactaddedtogroup'] = 'تمت إضافة المراسلين إلى هذه المجموعة بنجاح';
$messages['contactremovedfromgroup'] = 'تمت إزالة المراسلين من هذه المجموعة بنجاح';
$messages['importwait'] = 'جاري الاستيراد، رجاء انتظر...';
$messages['importerror'] = 'فشل الاستيراد! الملف المرفوع ليس ملف vCard صالح.';
$messages['importconfirm'] = '<b>تم استيراد $inserted مراسلين بنجاح، وتجاهل $skipped موجودين مسبقاً</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>تم تجاهل $skipped عناصر موجودة</b>';
$messages['opnotpermitted'] = 'العملية ممنوعة!';
$messages['nofromaddress'] = 'عنوان البريد الالكتروني غير محدد في الهويّة المنتقاة';
$messages['editorwarning'] = 'يتسبب الانتقال إلى محرر النص البسيط بضياع جميع التنسيق. هل تريد الاستمرار؟';
$messages['httpreceivedencrypterror'] = 'حدث خطأ جسيم في التهيئة. رجاءً اتصال بمدير الخادم حالاً. <b>لا يمكن إرسال بريدك.</b>';
$messages['smtpconnerror'] = 'خطأ SMTP ($code): تعذر الاتصال بالخادم';
$messages['smtpautherror'] = 'خطأ SMTP ($code): تعذر التحقق من هويتك';
$messages['smtpfromerror'] = 'خطأ SMTP ($code): تعذر تحديد المرسل "$from" ($msg)';
$messages['smtptoerror'] = 'خطأ SMTP ($code): تعذرت إضافة المستلم "$to" ($msg)';
$messages['smtprecipientserror'] = 'خطأ SMTP: تعذرت قراءة قائمة المرسل إليهم';
$messages['smtpdsnerror'] = 'خطأ SMTP: لا يوجد دعم لتنويهات حالة التوصيل';
$messages['smtperror'] = 'خطأ SMTP: $msg';
$messages['emailformaterror'] = 'عنوان بريد إلكتروني غير صالح: $email';
$messages['toomanyrecipients'] = 'عدد المرسل إليهم كبير. قلص العدد إلى $max.';
$messages['maxgroupmembersreached'] = 'عدد أعضاء المجموعة أكثر من $max';
$messages['internalerror'] = 'حدث خطأ داخلي. الرجاء المحاولة مرة أخرى';
$messages['contactdelerror'] = 'تعذر حذف أسماء جهات الإتصال';
$messages['contactdeleted'] = 'تم حذف أسماء جهات الإتصال بنجاح';
$messages['groupdeleted'] = 'تم حذف المجموعة بنجاح';
$messages['grouprenamed'] = 'تم تغيير إسم المجموعة بنجاح';
$messages['groupcreated'] = 'تم إنشاء المجموعة بنجاح';
$messages['messagedeleted'] = 'تم حذف الرسائل بنجاح';
$messages['messagemoved'] = 'تم نقل الرسائل بنجاح';
$messages['messagecopied'] = 'تم نسخ الرسائل بنجاح';
$messages['messagemarked'] = 'تم تحديد الرسائل بنجاح';
$messages['autocompletechars'] = 'إكتب $min أحرف على الأقل للحصول على التكملة التلقائية';
$messages['namecannotbeempty'] = 'لا يمكن ترك الإسم فارغاً';
$messages['nametoolong'] = 'الإسم طويل جداً';
$messages['folderupdated'] = 'تم تحديث المجلد بنجاح';
$messages['foldercreated'] = 'تم إنشاء المجلد بنجاح';
$messages['invalidimageformat'] = 'ليست صيغة صورة صحيحة';

?>
