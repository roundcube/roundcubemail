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

$labels = array();
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


$labels['hestia_err_policy_short'] = 'كلمة المرور قصيرة جداً (الحد الأدنى للطول: %دقيقة).';
$labels['hestia_err_endpoint_invalid'] = 'نقطة نهاية Hestia غير مهيأة أو غير صالحة.';
$labels['hestia_err_identity'] = 'لا يمكن تحليل اسم المستخدم إلى محلي@المجال المحلي%تلميح.';
$labels['hestia_err_owner_unknown'] = 'غير قادر على تحديد مالك النطاق لـ %domain.';
$labels['hestia_err_unknown'] = 'خطأ غير معروف من Hestia API.';
$labels['hestia_err_default'] = 'قام Hestia بإرجاع رمز الخطأ %الرمز %.';
$labels['hestia_err_1'] = 'لم يتم تقديم حجج كافية.';
$labels['hestia_err_2'] = 'كائن أو حجة غير صالحة.';
$labels['hestia_err_3'] = 'الكائن غير موجود (صندوق البريد أو المجال غير موجود).';
$labels['hestia_err_4'] = 'الكائن موجود بالفعل.';
$labels['hestia_err_5'] = 'الكائن معلق بالفعل.';
$labels['hestia_err_6'] = 'الكائن غير معلق بالفعل.';
$labels['hestia_err_7'] = 'كائن قيد الاستخدام؛ لا يمكن تعديله.';
$labels['hestia_err_8'] = 'العملية محظورة بسبب حدود الحزمة.';
$labels['hestia_err_9'] = 'كلمة مرور خاطئة أو غير صالحة.';
$labels['hestia_err_10'] = 'تم رفض الإذن من قبل Hestia (هوية واجهة برمجة التطبيقات تفتقر إلى الحقوق أو عنوان IP غير مسموح به).';
$labels['hestia_err_11'] = 'تم تعطيل الميزة بواسطة نهج النظام.';
$labels['hestia_err_12'] = 'خطأ في الإعراب.';
$labels['hestia_err_13'] = 'خطأ في القرص.';
$labels['hestia_err_14'] = 'تحميل الخادم مرتفع جداً';
$labels['hestia_err_15'] = 'خطأ في الاتصال.';
$labels['hestia_err_16'] = 'خطأ في FTP.';
$labels['hestia_err_17'] = 'خطأ في قاعدة البيانات.';
$labels['hestia_err_18'] = 'خطأ في RRD';
$labels['hestia_err_19'] = 'خطأ في التحديث.';
$labels['hestia_err_20'] = 'خطأ في إعادة التشغيل.';
?>
