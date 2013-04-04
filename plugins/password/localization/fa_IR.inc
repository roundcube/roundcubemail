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

$labels = array();
$labels['changepasswd']  = 'تغییر گذرواژه';
$labels['curpasswd']  = 'گذرواژه فعلی';
$labels['newpasswd']  = 'گذرواژه جدید';
$labels['confpasswd']  = 'تایید گذرواژه جدید';

$messages = array();
$messages['nopassword'] = 'گذرواژه جدید را وارد نمایید';
$messages['nocurpassword'] = 'گذرواژه فعلی را وارد نمایید';
$messages['passwordincorrect'] = 'گذرواژه فعلی اشتباه است';
$messages['passwordinconsistency'] = 'گذرواژه‌ها با هم مطابقت ندارند، دوباره سعی نمایید.';
$messages['crypterror'] = 'گذرواژه جدید نمی‌توانست ذخیره شود. نبودن تابع رمزگذاری.';
$messages['connecterror'] = 'گذرواژه جدید نمی‌توانست ذخیره شود. خطای ارتباط.';
$messages['internalerror'] = 'گذرواژه جدید ذخیره نشد';
$messages['passwordshort'] = 'گذرواژه باید حداقل $length کاراکتر طول داشته باشد.';
$messages['passwordweak'] = 'گذرواژه باید شامل حداقل یک عدد و یک کاراکتر نشانه‌ای باشد.';
$messages['passwordforbidden'] = 'گذرواژه شما کاراکترهای غیرمجاز است.';

?>
