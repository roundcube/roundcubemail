<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/
$labels['changepasswd']  = 'تغيير كلمة المرور';
$labels['curpasswd']  = 'كلمة المرور الحالية:';
$labels['newpasswd']  = 'كلمة المرور الجديدة:';
$labels['confpasswd']  = 'تأكيد كلمة المرور الجديدة:';
$messages['nopassword'] = 'من فضلك أدخل كلمة المرور الجديدة.';
$messages['nocurpassword'] = 'من فضلك أدخل كلمة المرور الحالية.';
$messages['passwordincorrect'] = 'كلمة المرور الحالية غير صحيحة.';
$messages['passwordinconsistency'] = 'كلمة المرور غير مطابقة حاول مجددا';
$messages['crypterror'] = 'تعذر حفظ كلمة المرور الجديدة. وظيفة التشفير مفقودة.';
$messages['connecterror'] = 'تعذر حفظ كلمة المرور الجديدة. خطأ بالإتصال.';
$messages['internalerror'] = 'تعذر حفظ كلمة المرور الجديدة.';
$messages['passwordshort'] = 'كلمة المرور يجب على الأقل $length أحرف';
$messages['passwordweak'] = ' كلمة المرور يجب أن تتضمن رقم واحد على الأقل وحرف ترقيم واحد.';
$messages['passwordforbidden'] = 'كلمة المرور تحتوى على أحرف ممنوعة';
$messages['firstloginchange'] = 'لقد تم تسجيل دخولك لأول مرة، يُرجى تغيير كلمتك السرية.';
$messages['passwinhistory'] = 'لقد قُمت باستخدام هذه الكلمة السرية مِن قبل.';
$messages['samepasswd'] = 'يجب أن تكون كلمة السر الجديدة لا تُشابِه الكلمة القديمة.';
$messages['passwdexpirewarning'] = 'تنبيه ! إنّ مدة صلاحية كلمتك السرية سوف تنقضي قريبا، يُرجى تغييرها قبل $expirationdatetime.';
$messages['passwdexpired'] = 'لقد إنتهت مدة صلاحية كلمتك السرية، يجب عليك تغييرها الآن !';
$messages['passwdconstraintviolation'] = 'تحتوي كلمة المرور على خروقات. ربما كلمة المرور ضعيفة جدا.';
