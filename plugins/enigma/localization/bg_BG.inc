<?php

/**
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/

$labels['encryption'] = 'Криптиране';
$labels['enigmacerts'] = 'S/MIME сертификати';
$labels['enigmakeys'] = 'PGP ключове';
$labels['keysfromto'] = 'Ключове $from до $to от $count';
$labels['keyname'] = 'Име';
$labels['keyid'] = 'ИН на ключ';
$labels['keyuserid'] = 'ИН на потребител';
$labels['keytype'] = 'Тип на ключ';
$labels['fingerprint'] = 'Пръстов отпечатък';
$labels['subkeys'] = 'Подключове';
$labels['keyprops'] = 'Свойства на ключа';
$labels['basicinfo'] = 'Основна информация';
$labels['userids'] = 'Допълнителни потребители';
$labels['typepublickey'] = 'публичен ключ';
$labels['typekeypair'] = 'ключова двойка';
$labels['keyattfound'] = 'Това писмо съдържа прикачен(и) PGP ключ(ове)';
$labels['keyattimport'] = 'Импорт на ключ(ове)';
$labels['typesign'] = 'Подписване';
$labels['typeencrypt'] = 'Криптиране';
$labels['typecert'] = 'Удостоверяване';
$labels['typeauth'] = 'Идентификация';
$labels['subkeyid'] = 'ИН';
$labels['subkeyalgo'] = 'Алгоритъм';
$labels['subkeycreated'] = 'Създаден';
$labels['subkeyexpires'] = 'Изтича';
$labels['subkeyusage'] = 'Употреба';
$labels['expiresnever'] = 'никога';
$labels['unknown'] = 'неизвестно';
$labels['uservalid'] = 'Валиден';
$labels['userid'] = 'ИН';
$labels['valid'] = 'валиден';
$labels['supportencryption'] = 'Включи криптиране и подписване на писма';
$labels['supportsignatures'] = 'Включи верификация на подписи в писма';
$labels['supportdecryption'] = 'Включи декриптиране на писма';
$labels['signdefault'] = 'Подписвай всички писма по подразбиране';
$labels['encryptdefault'] = 'Криптирай всички писма по подразбиране';
$labels['attachpubkeydefault'] = 'Прикачи по подразбирне моя публичен PGP ключ';
$labels['passwordtime'] = 'Запомни паролата на частния ключ за';
$labels['nminutes'] = '$m минути';
$labels['wholesession'] = 'цялата сесия';
$labels['createkeys'] = 'Създай нова ключова двойка';
$labels['importkeys'] = 'Импорт на ключ(ове)';
$labels['exportkeys'] = 'Експорт на ключ(ове)';
$labels['keyactions'] = 'Действия...';
$labels['keyremove'] = 'Изтриване';
$labels['keydisable'] = 'Деактивиране';
$labels['keyrevoke'] = 'Отмяна';
$labels['keysend'] = 'Изпрати писмо с публичен ключ';
$labels['keychpass'] = 'Промени паролата';
$labels['keyadd'] = 'Добавяне на ключ';
$labels['newkeyident'] = 'Самоличност';
$labels['newkeypass'] = 'Парола';
$labels['newkeypassconfirm'] = 'Потвърди паролата';
$labels['newkeytype'] = 'Тип на ключ';
$labels['rsa2048'] = 'RSA - 2048 бита';
$labels['rsa4096'] = 'RSA - 4096 бита';
$labels['ecckeypair'] = 'ECC (Elliptic Curve)';
$labels['keygenerating'] = 'Генериране на ключове...';
$labels['encryptionoptions'] = 'Опции за криптиране...';
$labels['encryptmsg'] = 'Криптирай това писмо';
$labels['signmsg'] = 'Подпиши цифрово това писмо';
$labels['sendunencrypted'] = 'Изпрати некриптирано';
$labels['enterkeypasstitle'] = 'Въведете парола на ключа';
$labels['enterkeypass'] = 'Нужна е парола за отваряне на ключ ($keyid) за потребител $user.';
$labels['attachpubkeymsg'] = 'Прикачи моя публичен ключ';
$labels['keyexportprompt'] = 'Желаете ли да включите секретните ключове в записания OpenPGP файл?';
$labels['onlypubkeys'] = 'Експорт само на Публичните ключове';
$labels['withprivkeys'] = 'Експорт на Секретни ключове';
$labels['findkey'] = 'Търси на сървъри за ключове';
$labels['keyimportlabel'] = 'Импорт от файл';
$labels['keyimportsearchlabel'] = 'Търси на сървъри за ключове';
$labels['managekeys'] = 'Управление на PGP ключовете';
$labels['identitymatchingprivkeys'] = 'Имате $nr съвпадащи PGP частни ключа, съхранени във вашия ключодържател:';
$labels['identitynoprivkeys'] = 'Тази самоличност на подателя все още няма частен PGP ключ, съхраняван във вашия ключодържател.';
$labels['arialabelkeyexportoptions'] = 'Опции за експорт на ключ';
$labels['arialabelkeysearchform'] = 'Форма за търсене на ключове';
$labels['arialabelkeyoptions'] = 'Настройки на ключа';
$messages['sigvalid'] = 'Верифициран подпис от $sender.';
$messages['sigvalidpartial'] = 'Верифициран подпис от $sender, но част от съдържанието на писмото не е било подписано';
$messages['siginvalid'] = 'Невалиден подпис от $sender.';
$messages['sigunverified'] = 'Неверифициран подпис. Сертификатът не е верифициран. Сертификат с ИН $keyid.';
$messages['signokey'] = 'Неверифициран подпис. Публичния ключ не е намерен. Ключ с ИН $keyid.';
$messages['sigerror'] = 'Неверифициран подпис. Вътрешна грешка.';
$messages['decryptok'] = 'Писмото е декриптирано.';
$messages['decrypterror'] = 'Невъзможно декриптиране.';
$messages['decryptnokey'] = 'Невъзможно декриптиране. Частният ключ не е намерен. Ключ с ИН $keyid.';
$messages['decryptnomdc'] = 'Декриптирането е пропуснато. Целостта писмото е нарушена.';
$messages['decryptbadpass'] = 'Невъзможно декриптиране. Невалидна парола.';
$messages['decryptnopass'] = 'Невъзможно декриптиране. Паролата на частния ключ е задължителна.';
$messages['decryptpartial'] = 'Писмото е декриптирано, но част от съдържанието му не е било защитено с криптиране.';
$messages['signerror'] = 'Невъзможно подписване.';
$messages['signnokey'] = 'Невъзможно подписване. Частният ключ не е намерен.';
$messages['signbadpass'] = 'Невъзможно подписване. Невалидна парола.';
$messages['signnopass'] = 'Невъзможно подписване. Паролата на частния ключ е задължителна.';
$messages['encrypterror'] = 'Невъзможно криптиране.';
$messages['encryptnokey'] = 'Невъзможно криптиране. Публичният ключ не е намерен за $email.';
$messages['encryptnoprivkey'] = 'Невъзможно криптиране. Частният ключ не е намерен.';
$messages['nokeysfound'] = 'Не са намерени ключове';
$messages['keynotfound'] = 'Ключът не е намерен!';
$messages['keyopenerror'] = 'Невъзможно получаване на информация за ключа! Вътрешна грешка.';
$messages['keylisterror'] = 'Невъзможно получаване на ключове! Вътрешна грешка.';
$messages['keysimportfailed'] = 'Невъзможно импортиране на ключове! Вътрешна грешка.';
$messages['keysimportsuccess'] = 'Ключовете са импортирани успешно. Импортирани $new, непроменени $old.';
$messages['keyremoving'] = 'Изтриване на ключове...';
$messages['keyremoveconfirm'] = 'Желаете ли да изтриете избраните ключове?';
$messages['keyremovesuccess'] = 'Ключовете са изтрити успешно.';
$messages['keyremoveerror'] = 'Невъзможно изтриване на ключове.';
$messages['keyimporttext'] = 'Можете да импортирате частни и публични ключове или сигнатури за отмяна в ASCII-Armor формат.';
$messages['keyimportsearchtext'] = 'Можете да търсите публични ключове по идентификатор, потребителско име или адрес на ел. поща, след което директно да ги импортирате.';
$messages['keystoragenotice'] = 'Всички публични и частни ключове се съхраняват на сървъра.';
$messages['formerror'] = 'Моля попълнете формата. Всички полета са задължителни!';
$messages['passwordsdiffer'] = 'Паролите не съвпадат!';
$messages['keygenerateerror'] = 'Невъзможно генериране на ключова двойка';
$messages['keygeneratesuccess'] = 'Ключовата двойка е генерирана и импортирана успешно.';
$messages['keygennosupport'] = 'Вашият браузър не поддържа нужните криптографски методи. Невъзможно генериране на ключова двойка.';
$messages['noidentselected'] = 'Трябва да избере поне една самоличност за този ключ!';
$messages['nonameident'] = 'Самоличността трябва да има дефинирано потребителско име!';
$labels['newkeysize'] = 'Размер на ключа';
$labels['key2048'] = '2048 бита (по подразбиране)';
$labels['key4096'] = '4096 бита (по-сигурен)';
