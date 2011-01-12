<?php
/*

+-----------------------------------------------------------------------+
| plugins/enigma/localization/ru_RU.inc                                 |
|                                                                       |
| Russian translation for roundcube/enigma plugin                       |
| Copyright (C) 2010                                                    |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Sergey Dukachev <iam@dukess.ru>                               |
| Updates:                                                              |
+-----------------------------------------------------------------------+

@version 2010-12-23

*/

$labels = array();
$labels['enigmasettings'] = 'Enigma: Настройки';
$labels['enigmacerts'] = 'Enigma: Сертификаты (S/MIME)';
$labels['enigmakeys'] = 'Enigma: Ключи (PGP)';
$labels['keysfromto'] = 'Ключи от $from к $to в количестве $count';
$labels['keyname'] = 'Имя';
$labels['keyid'] = 'Идентификатор ключа';
$labels['keyuserid'] = 'Идентификатор пользователя';
$labels['keytype'] = 'Тип ключа';
$labels['fingerprint'] = 'Отпечаток (хэш) ключа';
$labels['subkeys'] = 'Подразделы';
$labels['basicinfo'] = 'Основные сведения';
$labels['userids'] = 'Дополнительные идентификаторы пользователя';
$labels['typepublickey'] = 'Открытый ключ';
$labels['typekeypair'] = 'пара ключей';
$labels['keyattfound'] = 'Это сообщение содержит один или несколько ключей PGP.';
$labels['keyattimport'] = 'Импортировать ключи';

$labels['createkeys'] = 'Создать новую пару ключей';
$labels['importkeys'] = 'Импортировать ключ(и)';
$labels['exportkeys'] = 'Экспортировать ключ(и)';
$labels['deletekeys'] = 'Удалить ключ(и)';
$labels['keyactions'] = 'Действия с ключами...';
$labels['keydisable'] = 'Отключить ключ';
$labels['keyrevoke'] = 'Отозвать ключ';
$labels['keysend'] = 'Отправить публичный ключ в собщении';
$labels['keychpass'] = 'Изменить пароль';

$messages = array();
$messages['sigvalid'] = 'Проверенная подпись у $sender.';
$messages['siginvalid'] = 'Неверная подпись у $sender.';
$messages['signokey'] = 'Непроверяемая подпись. Открытый ключ не найден. Идентификатор ключа: $keyid.';
$messages['sigerror'] = 'Непроверяемая подпись. Внутренняя ошибка.';
$messages['decryptok'] = 'Сообщение расшифровано.';
$messages['decrypterror'] = 'Расшифровка не удалась.';
$messages['decryptnokey'] = 'Расшифровка не удалась. Секретный ключ не найден. Идентификатор ключа: $keyid.';
$messages['decryptbadpass'] = 'Расшифровка не удалась. Неправильный пароль.';
$messages['nokeysfound'] = 'Ключи не найдены';
$messages['keyopenerror'] = 'Невозможно получить информацию о ключе! Внутренняя ошибка.';
$messages['keylisterror'] = 'Невозможно сделать список ключей! Внутренняя ошибка.';
$messages['keysimportfailed'] = 'Невозможно импортировать ключ(и)! Внутренняя ошибка.';
$messages['keysimportsuccess'] = 'Ключи успешно импортированы. Импортировано: $new, без изменений: $old.';
$messages['keyconfirmdelete'] = 'Вы точно хотите удалить выбранные ключи?';
$messages['keyimporttext'] = 'Вы можете импортировать открытые и секретные ключи или сообщения об отзыве ключей в формате ASCII-Armor.';

?>
