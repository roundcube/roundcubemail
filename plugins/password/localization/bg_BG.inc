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

$labels['hestia_err_policy_short'] = 'Паролата е твърде кратка (минимална дължина: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Крайната точка на Hestia не е конфигурирана или е невалидна.';
$labels['hestia_err_identity'] = 'Не може да се анализира потребителското име в local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Не е възможно да се определи собственикът на домейна за %домейн. %d';
$labels['hestia_err_unknown'] = 'Неизвестна грешка от API на Hestia.';
$labels['hestia_err_default'] = 'Hestia върна код за грешка %code.';
$labels['hestia_err_1'] = 'Не са предоставени достатъчно аргументи.';
$labels['hestia_err_2'] = 'Невалиден обект или аргумент.';
$labels['hestia_err_3'] = 'Обектът не съществува (не е намерена пощенска кутия или домейн).';
$labels['hestia_err_4'] = 'Обектът вече съществува.';
$labels['hestia_err_5'] = 'Обектът вече е спрян.';
$labels['hestia_err_6'] = 'Обектът вече не е спрян.';
$labels['hestia_err_7'] = 'Обект в употреба; не може да се модифицира.';
$labels['hestia_err_8'] = 'Операцията е блокирана от ограниченията на пакета.';
$labels['hestia_err_9'] = 'Грешна или невалидна парола.';
$labels['hestia_err_10'] = 'Разрешението е отказано от Hestia (API идентичността няма права или IP не е разрешен).';
$labels['hestia_err_11'] = 'Функцията е деактивирана от системната политика.';
$labels['hestia_err_12'] = 'Грешка при парсирането.';
$labels['hestia_err_13'] = 'Дискова грешка.';
$labels['hestia_err_14'] = 'Натоварването на сървъра е твърде голямо.';
$labels['hestia_err_15'] = 'Грешка при свързване.';
$labels['hestia_err_16'] = 'Грешка в FTP.';
$labels['hestia_err_17'] = 'Грешка в базата данни.';
$labels['hestia_err_18'] = 'Грешка на RRD.';
$labels['hestia_err_19'] = 'Грешка при актуализиране.';
$labels['hestia_err_20'] = 'Грешка при рестартиране.';
?>
