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

$labels['changepasswd']  = 'Изменить пароль';
$labels['curpasswd']  = 'Текущий пароль:';
$labels['newpasswd']  = 'Новый пароль:';
$labels['confpasswd']  = 'Подтвердите новый пароль:';
$messages['nopassword'] = 'Пожалуйста, введите новый пароль.';
$messages['nocurpassword'] = 'Пожалуйста, введите текущий пароль.';
$messages['passwordincorrect'] = 'Текущий пароль неверен.';
$messages['passwordinconsistency'] = 'Пароли не совпадают, попробуйте ещё раз, пожалуйста.';
$messages['crypterror'] = 'Не могу сохранить новый пароль. Отсутствует криптографическая функция.';
$messages['connecterror'] = 'Не могу сохранить новый пароль. Ошибка соединения.';
$messages['internalerror'] = 'Не могу сохранить новый пароль.';
$messages['passwordshort'] = 'Длина пароля должна быть как минимум $length символов.';
$messages['passwordweak'] = 'Пароль должен включать в себя как минимум одну цифру и один знак пунктуации.';
$messages['passwordtooweak'] = 'Пароль слишком простой.';
$messages['passwordnoseq'] = 'Пароль НЕ должен быть последовательностью, например,123456 или QWERTY.';
$messages['passwordnocommon'] = 'Пароль не должен быть простым словом или именем.';
$messages['passwordforbidden'] = 'Пароль содержит недопустимые символы.';
$messages['firstloginchange'] = 'Вы выполнили вход впервые. Измените ваш пароль.';
$messages['disablednotice'] = 'В настоящее время проводится обслуживание системы и изменение пароля не возможно в данный момент. Все должно вернуться в нормальное состояние в ближайшее время. Приносим свои извинения за причиненные неудобства.';
$messages['passwinhistory'] = 'Этот пароль уже использовался ранее.';
$messages['samepasswd'] = 'Новый пароль должен отличаться от старого.';
$messages['passwdexpirewarning'] = 'Внимание! Ваш пароль истекает в ближайшее время, измените его до $expirationdatetime.';
$messages['passwdexpired'] = 'Срок действия вашего пароля истек, вы должны изменить его сейчас!';
$messages['passwdconstraintviolation'] = 'Нарушение ограничения пароля. Вероятно, слишком слабый пароль.';
$messages['pwned_mustnotbedisclosed'] = 'Пароль не должен быть <a href="$href" target="_blank">commonly known</a>.';
$messages['pwned_isdisclosed'] = 'Этот пароль общеизвестен.';
$messages['pwned_fetcherror'] = 'Не удалось проверить стойкость пароля.';

$labels['hestia_err_policy_short'] = 'Слишком короткий пароль (минимальная длина: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Конечная точка Hestia не настроена или недействительна.';
$labels['hestia_err_identity'] = 'Невозможно разобрать имя пользователя в local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Невозможно определить владельца домена для %domain.';
$labels['hestia_err_unknown'] = 'Неизвестная ошибка от Hestia API.';
$labels['hestia_err_default'] = 'Гестия вернула код ошибки %code.';
$labels['hestia_err_1'] = 'Недостаточно аргументов.';
$labels['hestia_err_2'] = 'Недопустимый объект или аргумент.';
$labels['hestia_err_3'] = 'Объект не существует (почтовый ящик или домен не найдены).';
$labels['hestia_err_4'] = 'Объект уже существует.';
$labels['hestia_err_5'] = 'Объект уже приостановлен.';
$labels['hestia_err_6'] = 'Объект уже не подвешен.';
$labels['hestia_err_7'] = 'Объект в использовании; не может быть изменен.';
$labels['hestia_err_8'] = 'Работа заблокирована из-за ограничений пакета.';
$labels['hestia_err_9'] = 'Неправильный или недействительный пароль.';
$labels['hestia_err_10'] = 'Разрешение отклонено Hestia (идентификатор API не имеет прав или IP не разрешен).';
$labels['hestia_err_11'] = 'Функция отключена системной политикой.';
$labels['hestia_err_12'] = 'Ошибка разбора.';
$labels['hestia_err_13'] = 'Ошибка диска.';
$labels['hestia_err_14'] = 'Слишком высокая загрузка сервера.';
$labels['hestia_err_15'] = 'Ошибка подключения.';
$labels['hestia_err_16'] = 'Ошибка FTP.';
$labels['hestia_err_17'] = 'Ошибка базы данных.';
$labels['hestia_err_18'] = 'Ошибка RRD.';
$labels['hestia_err_19'] = 'Ошибка обновления.';
$labels['hestia_err_20'] = 'Ошибка перезапуска.';
?>
