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

$labels['changepasswd']  = 'Промяна на парола';
$labels['curpasswd']  = 'Текуща парола:';
$labels['newpasswd']  = 'Нова парола:';
$labels['confpasswd']  = 'Повторно нова парола:';
$messages['nopassword'] = 'Моля въведете нова парола.';
$messages['nocurpassword'] = 'Моля въведете текущата парола.';
$messages['passwordincorrect'] = 'Невалидна текуща парола.';
$messages['passwordinconsistency'] = 'Паролите не съвпадат, опитайте отново.';
$messages['crypterror'] = 'Невъзможна промяна на паролата. Липсва PHP функция за криптиране.';
$messages['connecterror'] = 'Невъзможна промяна на паролата. Грешка при свързване със сървър.';
$messages['internalerror'] = 'Паролата не може да бъде променена.';
$messages['passwordshort'] = 'Паролата трябва да е дълга поне $length знака.';
$messages['passwordweak'] = 'Паролата трябва да включва поне една цифра и един препинателен знак.';
$messages['passwordtooweak'] = 'Паролата е твърде лесна.';
$messages['passwordnoseq'] = 'Паролата не трябва да бъде последователност като 123456 или QWERTY.';
$messages['passwordnocommon'] = 'Паролата не трябва да е често срещна дума или име.';
$messages['passwordforbidden'] = 'Паролата съдържа непозволени символи.';
$messages['firstloginchange'] = 'Влизате за първи път. Моля променете Вашата парола.';
$messages['disablednotice'] = 'Системата е в режим на поддръжка и не е възможна промяна на паролата. Всичко би трябвало да се нормализира съвсем скоро. Извиняваме са за причиненото неудобство.';
$messages['passwinhistory'] = 'Тази парола вече е била използвана.';
$messages['samepasswd'] = 'Новата парола трябва да е различна от старата парола.';
$messages['passwdexpirewarning'] = 'Внимание! Срокът на Вашата парола изтича съвсем скоро и е препоръчително да я смените преди $expirationdatetime.';
$messages['passwdexpired'] = 'Срокът на Вашата парола е изтекъл и трябва да я смените точно сега!';
$messages['passwdconstraintviolation'] = 'Нарушение на изискване за паролата. Вероятно паролата е твърде лесна.';
$messages['pwned_mustnotbedisclosed'] = 'Паролата не трябва да бъде <a href="$href" target="_blank">общоизвестна</a>.';
$messages['pwned_isdisclosed'] = 'Тази парола е общоизвестна.';
$messages['pwned_fetcherror'] = 'Неуспешна проверка на силата на паролата.';
