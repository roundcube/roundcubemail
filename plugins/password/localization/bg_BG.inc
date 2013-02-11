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
$labels['changepasswd']  = 'Промяна на парола';
$labels['curpasswd']  = 'Текуща парола:';
$labels['newpasswd']  = 'Нова парола:';
$labels['confpasswd']  = 'Повторете:';

$messages = array();
$messages['nopassword'] = 'Моля въведете нова парола.';
$messages['nocurpassword'] = 'Моля въведете текущата.';
$messages['passwordincorrect'] = 'Невалидна текуща парола.';
$messages['passwordinconsistency'] = 'Паролите не съвпадат, опитайте пак.';
$messages['crypterror'] = 'Паролата не може да бъде сменена. Грешка в криптирането.';
$messages['connecterror'] = 'Паролата не може да бъде сменена. Грешка в свързването.';
$messages['internalerror'] = 'Паролата не може да бъде сменена.';
$messages['passwordshort'] = 'Паролата трябва да е дълга поне $length знака.';
$messages['passwordweak'] = 'Паролата трябва да включва поне един азбучен символ и една пунктуация.';
$messages['passwordforbidden'] = 'Паролата съдържа невалидни знаци.';

?>
