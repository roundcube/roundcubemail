<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'تغيير كلمة المرور';
$labels['curpasswd']  = 'كلمة المرور الحالية';
$labels['newpasswd']  = 'كلمة المرور الجديدة';
$labels['confpasswd']  = 'تأكيد كلمة المرور الجديدة';
$messages['nopassword'] = 'من فضلك أدخل كلمة مرور جديدة';
$messages['nocurpassword'] = 'من فضلك أدخل كلمة المرور الحالية';
$messages['passwordincorrect'] = 'كلمة المرور الحالية غير صحيحة';
$messages['passwordinconsistency'] = 'كلمة المرور غير مطابقة, أعد المحاولة';
$messages['crypterror'] = 'تعذر حفظ كلمة المرور الجديدة. دالة التشفير مفقودة.';
$messages['connecterror'] = 'تعذر حفظ كلمة المرور الجديدة. خطأ في الإتصال';
$messages['internalerror'] = 'تعذر حفظ كلمة المرور الجديدة';
$messages['passwordshort'] = 'كلمة المرور يجب ان تحتوي على الاقل $length احرف.';
$messages['passwordweak'] = 'كلمة المرور يجب ان تتضمن على الاقل رقم و علامة ترميز.';
$messages['passwordtooweak'] = 'كلمة المرور ضعيفة';
$messages['passwordnoseq'] = 'يجب ألا تكون كلمة المرور تسلسلًا مثل 123456 أو QWERTY.';
$messages['passwordnocommon'] = 'يجب ألا تكون كلمة المرور كلمة أو اسمًا شائعًا.';
$messages['passwordforbidden'] = 'كلمة المرور تحتوي حروفاً ممنوعة';
$messages['firstloginchange'] = 'هذا هو تسجيل الدخول الأول الخاص بك. الرجاء تغيير كلمة المرور الخاصة بك.';
$messages['disablednotice'] = 'النظام قيد الصيانة حاليًا ولا يمكن تغيير كلمة المرور في الوقت الحالي. يجب أن يعود كل شيء إلى طبيعته قريبًا. نحن نعتذر عن أي شيء غير مناسب.';
$messages['passwinhistory'] = 'سبق استخدام كلمة المرور هذه من قبل.';
$messages['samepasswd'] = 'يجب أن تكون كلمة المرور الجديدة مختلفة عن القديمة.';
$messages['passwdexpirewarning'] = 'تحذير! ستنتهي صلاحية كلمة مرورك قريبًا ، قم بتغييرها قبل $expirationdatetime .';
$messages['passwdexpired'] = 'لقد انتهت صلاحية كلمة المرور الخاصة بك ، يجب عليك تغييرها الآن!';
$messages['passwdconstraintviolation'] = 'انتهاك قيود كلمة المرور. ربما تكون كلمة المرور ضعيفة للغاية.';
$messages['pwned_mustnotbedisclosed'] = 'يجب ألا تكون كلمة المرور &nbsp; <a href="https://haveibeenpwned.com/Passwords" target="_blank">معروفة بشكل عام</a>.';
$messages['pwned_isdisclosed'] = 'كلمة المرور هذه معروفة بشكل عام.';
$messages['pwned_fetcherror'] = 'فشل التحقق من قوة كلمة المرور.';
