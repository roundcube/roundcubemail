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

$labels['changepasswd']  = 'Змінити пароль';
$labels['curpasswd']  = 'Поточний пароль:';
$labels['newpasswd']  = 'Новий пароль:';
$labels['confpasswd']  = 'Підтвердіть новий пароль:';
$messages['nopassword'] = 'Будь ласка, введіть новий пароль.';
$messages['nocurpassword'] = 'Будь ласка, введіть поточний пароль.';
$messages['passwordincorrect'] = 'Поточний пароль неправильний.';
$messages['passwordinconsistency'] = 'Паролі не збігаються, спробуйте ще раз.';
$messages['crypterror'] = 'Не вдалося зберегти новий пароль. Функція шифрування відсутня.';
$messages['connecterror'] = 'Не вдалося зберегти новий пароль. Помилка з\'єднання.';
$messages['internalerror'] = 'Не вдалося зберегти новий пароль.';
$messages['passwordshort'] = 'Пароль повинен бути не менше $length символів.';
$messages['passwordweak'] = 'Пароль повинен містити як мінімум одну цифру і один розділовий знак.';
$messages['passwordforbidden'] = 'Пароль містить заборонені символи.';
$messages['firstloginchange'] = 'Це Ваше перше входження в систему. Змініть, будь ласка, свій пароль.';
$messages['disablednotice'] = 'Система зараз в режимі обслуговування і змінити пароль наразі неможливо. Невдовзі все має вернутися в норму. Просимо вибачення за будь-які незручності.';
$messages['passwinhistory'] = 'Цей пароль вже використовувався раніше.';
$messages['samepasswd'] = 'Новий пароль повинен відрізнятися від старого.';
$messages['passwdexpirewarning'] = 'УВАГА! У вашого пароля закінчується термін, змінити його як омога скоріше до $expirationdatetime.';
$messages['passwdexpired'] = 'Ваш пароль закінчився, ви повинні змінити це зараз!';

$labels['hestia_err_policy_short'] = 'Занадто короткий пароль (мінімальна довжина: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Кінцева точка Hestia не налаштована або недійсна.';
$labels['hestia_err_identity'] = 'Не вдається розібрати ім\'я користувача в local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Не вдалося визначити власника домену для %domain.';
$labels['hestia_err_unknown'] = 'Невідома помилка з API Hestia.';
$labels['hestia_err_default'] = 'Hestia повернула код помилки %code.';
$labels['hestia_err_1'] = 'Надано недостатньо аргументів.';
$labels['hestia_err_2'] = 'Неправильний об\'єкт або аргумент.';
$labels['hestia_err_3'] = 'Об\'єкт не існує (поштову скриньку або домен не знайдено).';
$labels['hestia_err_4'] = 'Об\'єкт вже існує.';
$labels['hestia_err_5'] = 'Об\'єкт вже призупинено.';
$labels['hestia_err_6'] = 'Об\'єкт вже розконсервовано.';
$labels['hestia_err_7'] = 'Об\'єкт у використанні; не може бути змінений.';
$labels['hestia_err_8'] = 'Операція заблокована через ліміт пакетів.';
$labels['hestia_err_9'] = 'Неправильний або недійсний пароль.';
$labels['hestia_err_10'] = 'Дозвіл відхилено Hestia (ідентифікатор API не має прав або IP не дозволено).';
$labels['hestia_err_11'] = 'Функція вимкнена системною політикою.';
$labels['hestia_err_12'] = 'Помилка синтаксичного аналізу.';
$labels['hestia_err_13'] = 'Помилка диска.';
$labels['hestia_err_14'] = 'Занадто велике навантаження на сервер.';
$labels['hestia_err_15'] = 'Помилка з\'єднання.';
$labels['hestia_err_16'] = 'Помилка FTP.';
$labels['hestia_err_17'] = 'Помилка в базі даних.';
$labels['hestia_err_18'] = 'Помилка RRD.';
$labels['hestia_err_19'] = 'Помилка оновлення.';
$labels['hestia_err_20'] = 'Помилка перезавантаження.';
?>
