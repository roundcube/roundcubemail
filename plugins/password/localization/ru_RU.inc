<?php
/*

+-----------------------------------------------------------------------+
| plugins/password/localization/ru_RU.inc                               |
|                                                                       |
| Language file of the Roundcube help plugin                            |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Sergey Dukachev <iam@dukess.ru>                               |
+-----------------------------------------------------------------------+

*/

$labels = array();
$labels['changepasswd']  = 'Изменить пароль';
$labels['curpasswd']  = 'Текущий пароль:';
$labels['newpasswd']  = 'Новый пароль:';
$labels['confpasswd']  = 'Подтвердите новый пароль:';

$messages = array();
$messages['nopassword'] = 'Пожалуйста, введите новый пароль.';
$messages['nocurpassword'] = 'Пожалуйста, введите текущий пароль.';
$messages['passwordincorrect'] = 'Текущий пароль неверен.';
$messages['passwordinconsistency'] = 'Пароли не совпадают, попробуйте, пожалуйста, ещё.';
$messages['crypterror'] = 'Не могу сохранить новый пароль. Отсутствует криптографическая функция.';
$messages['connecterror'] = 'Не могу сохранить новый пароль. Ошибка соединения.';
$messages['internalerror'] = 'Не могу сохранить новый пароль.';
$messages['passwordshort'] = 'Пароль должен быть длиной как минимум $length символов.';
$messages['passwordweak'] = 'Пароль должен включать в себя как минимум одну цифру и один знак пунктуации.';
$messages['passwordforbidden'] = 'Пароль содержит недопустимые символы.';

?>
