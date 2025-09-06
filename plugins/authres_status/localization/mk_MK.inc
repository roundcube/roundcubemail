<?php

$labels = array();
$labels['nosignature'] = 'Нема информации за потпис';
$labels['noauthresults'] = 'Пронајден е потпис но не е потврден. резултирај заглавје. Проблем со конфигурација на серверот?';
$labels['signaturepass'] = 'Валиден потпис (потписи) од доменот на испраќачот потврдени од ';
$labels['partialpass'] = 'Некој потписи не се валидни но барем еден е валиден од доменот на испрќачот. потврдено од ';
$labels['thirdparty'] = 'Потпишано од трети лица, потписот е присутен но од различен домен за разлика од адресата. потврдено за ';
$labels['temporaryinvalid'] = 'Потписот е (привремено) невалиден! потврдено од ';
$labels['invalidsignature'] = 'Потписот е невалиден! потврдено од ';

$labels['for'] = 'за ';
$labels['by'] = 'од';

$labels['section_title']  = 'Проверка на статус';
$labels['column_title']  = 'проверка. Исход';

$labels['title_enable_column']  = 'Овозможи колона';
$labels['label_enable_column']  = 'Овозможи колона за исход од проверка во листата со пораки ';
$labels['label_fallback_verifier'] = 'Користи внатрешен DKIM верификатор (ако е пронајен потпис не не е потврден резултирај поглавје)';

$labels['title_trusted_mtas'] = 'Доверливи агенти за трансфер на пораки ';
$labels['label_trusted_mtas'] = 'Аuthserv-id листа оделена со запирка на агенти за трансфер на пораки на кој ти им веруваш, Ќе бидат прикажани резултати само од овие агенти ';

$labels['title_include_status'] = 'Покажи ги само следните статуси';
$labels['label_include_status1']  = 'Не е пронајден потпис';
$labels['label_include_status2']  = 'Пронајден е потпис но не е потврден. резултирај заглавје ';
$labels['label_include_status4']  = 'Сите потписи ја поминаа проверката';
$labels['label_include_status8']  = 'Барем еден потпис ја помина проверката ';
$labels['label_include_status16']  = 'Потпис од трето лице ';
$labels['label_include_status32']  = 'Потписот привремено падна ';
$labels['label_include_status64']  = 'Потписот падна трајно ';

?>
