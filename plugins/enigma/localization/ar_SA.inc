<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['encryption'] = 'التشفير';
$labels['enigmacerts'] = 'شهادات S / MIME';
$labels['enigmakeys'] = 'مفاتيح PGP';
$labels['keysfromto'] = 'مفاتيح $from إلى $to من $count';
$labels['keyname'] = 'الاسم';
$labels['keyid'] = 'مفتاح المعرّف';
$labels['keyuserid'] = 'معرّف المستخدم';
$labels['keytype'] = 'نوع المفتاح';
$labels['fingerprint'] = 'بصمة';
$labels['subkeys'] = 'مفاتيح فرعية';
$labels['keyprops'] = 'الخصائص الرئيسية';
$labels['basicinfo'] = 'المعلومات العامة';
$labels['userids'] = 'مستخدمون إضافيون';
$labels['typepublickey'] = 'المفتاح العام';
$labels['typekeypair'] = 'اقتران المفتاح';
$labels['keyattfound'] = 'تحتوي هذه الرسالة على مفتاح (مفاتيح) PGP مرفق.';
$labels['keyattimport'] = 'استيراد مفتاح (مفاتيح)';
$labels['typesign'] = 'علامة';
$labels['typeencrypt'] = 'تشفير';
$labels['typecert'] = 'تصديق';
$labels['typeauth'] = 'المصادقة';
$labels['subkeyid'] = 'المُعَرّف';
$labels['subkeyalgo'] = 'الخوارزمية';
$labels['subkeycreated'] = 'أُنشئ في';
$labels['subkeyexpires'] = 'تنتهي';
$labels['subkeyusage'] = 'استعمال';
$labels['expiresnever'] = 'أبداً';
$labels['unknown'] = 'مجهول';
$labels['uservalid'] = 'صالح';
$labels['userid'] = 'المُعَرّف';
$labels['valid'] = 'صالح';
$labels['supportencryption'] = 'تفعيل تشفير الرسائل والتوقيع';
$labels['supportsignatures'] = 'تفعيل التحقق من تواقيع الرسائل';
$labels['supportdecryption'] = 'تفعيل فك تشفير الرسالة';
$labels['signdefault'] = 'قم بتسجيل جميع الرسائل بشكل افتراضي';
$labels['encryptdefault'] = 'تشفير جميع الرسائل افتراضيًا';
$labels['attachpubkeydefault'] = 'قم بإرفاق مفتاح PGP العام الخاص بي افتراضيًا';
$labels['passwordtime'] = 'احتفظ بكلمات سر المفاتيح الخاصة';
$labels['nminutes'] = '$m دقيقة/دقائق';
$labels['wholesession'] = 'الجلسة بأكملها';
$labels['createkeys'] = 'إنشاء زوج مفاتيح جديد';
$labels['importkeys'] = 'استيراد مفتاح (مفاتيح)';
$labels['exportkeys'] = 'تصدير مفتاح(مفاتيح) ';
$labels['keyactions'] = 'إجراءات المفاتيح...';
$labels['keyremove'] = 'إزالة';
$labels['keydisable'] = 'إبطال';
$labels['keyrevoke'] = 'إلغاء';
$labels['keysend'] = 'إرسال المفتاح العام في الرسالة';
$labels['keychpass'] = 'تغيير كلمة المرور';
$labels['keyadd'] = 'إضافة مفتاح';
$labels['newkeyident'] = 'الهوية';
$labels['newkeypass'] = 'كلمة المرور';
$labels['newkeypassconfirm'] = 'تأكيد كلمة السر';
$labels['newkeytype'] = 'نوع المفتاح';
$labels['rsa2048'] = 'خوارزمية آر إس إيه - 2048 بت';
$labels['rsa4096'] = 'خوارزمية آر إس إيه - 4096 بت';
$labels['ecckeypair'] = 'خوارزمية تشفير بالمنحنيات الإهليلجية -ECC-';
$labels['keygenerating'] = 'جاري إنشاء المفاتيح...';
$labels['encryptionoptions'] = 'خيارات التشفير...';
$labels['encryptmsg'] = 'تشفير هذه الرسالة';
$labels['signmsg'] = 'التوقيع رقميا على هذه الرسالة';
$labels['sendunencrypted'] = 'إرسالها غير مشفره';
$labels['enterkeypasstitle'] = 'أدخل عبارة السر الرئيسية';
$labels['enterkeypass'] = 'يجب إدخال عبارة السر لفتح المفتاح السري ($keyid) للمستخدم: $user.';
$labels['attachpubkeymsg'] = 'إرفاق مفتاحي العام';
$labels['keyexportprompt'] = 'هل تريد تضمين المفاتيح السرية في ملف مفاتيح بي جي بي المفتوحة -OpenPGP- المحفوظ؟';
$labels['onlypubkeys'] = 'تصدير المفاتيح العامة فقط';
$labels['withprivkeys'] = 'تصدير المفاتيح السرية';
$labels['findkey'] = 'البحث في خادم(خوادم) المفاتيح';
$labels['keyimportlabel'] = 'الاستيراد من ملف';
$labels['keyimportsearchlabel'] = 'البحث في خادم(خوادم) المفاتيح';
$labels['managekeys'] = 'إدارة مفاتيح بي جي بي';
$labels['identitymatchingprivkeys'] = 'لقد قمت بتخزين $nr مفاتيح بي جي بي الخاصة المطابقة في حلقة المفاتيح الخاصة بك:';
$labels['identitynoprivkeys'] = 'لا يوجد حتى الآن مفتاح خاص بي جي بي مخزّن في سلسلة المفاتيح الخاصة بك لهوية المرسل هذه.';
$labels['arialabelkeyexportoptions'] = 'خيارات تصدير المفاتيح';
$labels['arialabelkeysearchform'] = 'نموذج البحث عن المفاتيح';
$labels['arialabelkeyoptions'] = 'خيارات المفاتيح';
$messages['sigvalid'] = 'تم التحقق من توقيع $sender.';
$messages['sigvalidpartial'] = 'تم التحقق من توقيع $sender.ولكن جزء من نص الرسالة لم يتم توقيعه.';
$messages['siginvalid'] = 'توقيع غير صالح من $sender.';
$messages['sigunverified'] = 'توقيع غير مُتحقق. لم يتم التحقق من الشهادة. مُعرّف الشهادة: $keyid.';
$messages['signokey'] = 'توقيع غير مُتحقق. المفتاح العام غير موجود. مُعرّف المفتاح: $keyid.';
$messages['sigerror'] = 'توقيع غير مُتحقق منه. خطأ داخلي.';
$messages['decryptok'] = 'تم فك تشفير الرسالة.';
$messages['decrypterror'] = 'فشل فك التشفير.';
$messages['decryptnokey'] = 'فشل فك التشفير. لم يتم العثور على المفتاح الخاص. معرف المفتاح: $keyid.';
$messages['decryptnomdc'] = 'تم تخطي فك التشفير. الرسالة غير محمية.';
$messages['decryptbadpass'] = 'فشل فك التشفير. رمز مرور خاطئ.';
$messages['decryptnopass'] = 'فشل فك التشفير. مطلوب كلمة مرور رئيسية.';
$messages['decryptpartial'] = 'تم فك تشفير الرسالة، ولكن جزء من النص لم يتم تشفيره.';
$messages['signerror'] = 'فشل التوقيع.';
$messages['signnokey'] = 'فشل التوقيع. لم يتم العثور على المفتاح الخاص.';
$messages['signbadpass'] = 'فشل تسجيل الدخول. كلمة المرور خاطئة.';
$messages['signnopass'] = 'فشل التوقيع. مطلوب كلمة السر الرئيسية.';
$messages['encrypterror'] = 'فشل التشفير.';
$messages['encryptnokey'] = 'فشل التشفير. لم يتم العثور على المفتاح العام لـ $email.';
$messages['encryptnoprivkey'] = 'فشل التشفير. لم يتم العثور على المفتاح الخاص.';
$messages['nokeysfound'] = 'لم يتم العثور على مفاتيح';
$messages['keynotfound'] = 'لم يتم العثور على المفتاح!';
$messages['keyopenerror'] = 'تعذر الحصول على المعلومات الأساسية! خطأ داخلي.';
$messages['keylisterror'] = 'تعذر إدراج المفاتيح! خطأ داخلي.';
$messages['keysimportfailed'] = 'تعذر استيراد المفاتيح! خطأ داخلي.';
$messages['keysimportsuccess'] = 'تم استيراد المفتاح(المفاتيح) بنجاح. تم الاستيراد: $new، لم يتم تغييره: $old.';
$messages['keyremoving'] = 'إزالة المفتاح(المفاتيح)...';
$messages['keyremoveconfirm'] = 'هل أنت متأكد أنك تريد حذف المفاتيح المحددة؟';
$messages['keyremovesuccess'] = 'تم حذف المفتاح(المفاتيح) بنجاح';
$messages['keyremoveerror'] = 'غير قادر على حذف المفاتيح المحددة.';
$messages['keyimporttext'] = 'يمكنك استيراد مفاتيح خاصة وعامة أو توقيعات الإلغاء بصيغة ASCII-Armor.';
$messages['keyimportsearchtext'] = 'يمكنك البحث عن المفاتيح العامة عن طريق معرف المفتاح أو اسم المستخدم أو عنوان البريد الإلكتروني ثم استيرادها مباشرة.';
$messages['keystoragenotice'] = 'يتم تخزين كافة المفاتيح العامة والخاصة على الخادم.';
$messages['formerror'] = 'يرجى تعبئة النموذج. جميع الحقول مطلوبة!';
$messages['passwordsdiffer'] = 'كلمات المرور غير متطابقة!';
$messages['keygenerateerror'] = 'فشل في إنشاء زوج المفاتيح';
$messages['keygeneratesuccess'] = 'تم إنشاء زوج المفاتيح واستيراده بنجاح.';
$messages['keygennosupport'] = 'متصفح الويب الخاص بك لا يدعم التشفير. تعذر إنشاء زوج مفاتيح!';
$messages['noidentselected'] = 'يتعين عليك تحديد هوية واحدة على الأقل للمفتاح!';
$messages['nonameident'] = 'يجب أن يكون للهوية اسم مستخدم محدد!';
$labels['newkeysize'] = 'حجم المفتاح';
$labels['key2048'] = '2048 بت - الافتراضي';
$labels['key4096'] = '4096 بت - أكثر أمانًا';
