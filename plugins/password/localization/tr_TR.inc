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

$labels['changepasswd']  = 'Parola değiştirme';
$labels['curpasswd']  = 'Geçerli parola:';
$labels['newpasswd']  = 'Yeni parola:';
$labels['confpasswd']  = 'Yeni parola onayı:';
$messages['nopassword'] = 'Yeni parolayı yazın.';
$messages['nocurpassword'] = 'Geçerli parolayı yazın.';
$messages['passwordincorrect'] = 'Geçerli parolayı yanlış yazdınız.';
$messages['passwordinconsistency'] = 'Parola ile onayı aynı değil. Lütfen yeniden deneyin.';
$messages['crypterror'] = 'Yeni parola kaydedilemedi. Şifreleme işlevi bulunamadı.';
$messages['connecterror'] = 'Yeni parola kaydedilemedi. Bağlantı sorunu.';
$messages['internalerror'] = 'Yeni parola kaydedilemedi.';
$messages['passwordshort'] = 'Parola en az $length karakterden oluşmalı.';
$messages['passwordweak'] = 'Parola en az bir sayı ve bir noktalama işareti içermeli.';
$messages['passwordtooweak'] = 'Parola çok kolay.';
$messages['passwordnoseq'] = 'Parola 123456 ya da QWERTY gibi sıralamalardan oluşmamalıdır.';
$messages['passwordnocommon'] = 'Parola iyi bilinen bir sözcük ya da ad olmamalıdır.';
$messages['passwordforbidden'] = 'Parolada izin verilmeyen karakterler var.';
$messages['firstloginchange'] = 'İlk kez oturum açıyorsunuz. Parolanızı değiştirin.';
$messages['disablednotice'] = 'Sistem bakımda olduğundan parola değiştirilemez. Lütfen daha sonra yeniden deneyin. Verdiğimiz rahatsızlıktan dolayı özür dileriz.';
$messages['passwinhistory'] = 'Bu parola daha önce kullanılmış.';
$messages['samepasswd'] = 'Yeni parola eski paroladan farklı olmalıdır.';
$messages['passwdexpirewarning'] = 'Uyarı! Parolanızın kullanma süresi yakında dolacak. $expirationdatetime tarihinden önce değiştirin.';
$messages['passwdexpired'] = 'Parolanızın kullanma süresi dolmuş. Şimdi değiştirmelisiniz!';
$messages['passwdconstraintviolation'] = 'Parola istenilen karmaşıklıkta değil. Büyük olasılıkla oldukça kolay.';
$messages['pwned_mustnotbedisclosed'] = 'Parola <a href="$href" target="_blank">yaygın kullanılmıyor olmalı</a>.';
$messages['pwned_isdisclosed'] = 'Bu parola yaygın olarak kullanılıyor.';
$messages['pwned_fetcherror'] = 'Parola zorluğu denetlenemedi.';

$labels['hestia_err_policy_short'] = 'Parola çok kısa (minimum uzunluk: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia uç noktası yapılandırılmamış veya geçersiz.';
$labels['hestia_err_identity'] = 'Kullanıcı adı local@domain%hint olarak ayrıştırılamıyor.';
$labels['hestia_err_owner_unknown'] = 'Alan adı sahibi %domain için belirlenemiyor.';
$labels['hestia_err_unknown'] = 'Hestia API\'den bilinmeyen hata.';
$labels['hestia_err_default'] = 'Hestia %code hata kodunu döndürdü.';
$labels['hestia_err_1'] = 'Yeterli argüman sunulmamıştır.';
$labels['hestia_err_2'] = 'Geçersiz nesne veya bağımsız değişken.';
$labels['hestia_err_3'] = 'Nesne mevcut değil (posta kutusu veya etki alanı bulunamadı).';
$labels['hestia_err_4'] = 'Nesne zaten mevcut.';
$labels['hestia_err_5'] = 'Nesne zaten askıya alındı.';
$labels['hestia_err_6'] = 'Nesne zaten askıya alınmamış.';
$labels['hestia_err_7'] = 'Nesne kullanımda; değiştirilemez.';
$labels['hestia_err_8'] = 'Paket limitleri nedeniyle işlem engellendi.';
$labels['hestia_err_9'] = 'Yanlış veya geçersiz şifre.';
$labels['hestia_err_10'] = 'İzin Hestia tarafından reddedildi (API kimliğinin hakları yok veya IP\'ye izin verilmiyor).';
$labels['hestia_err_11'] = 'Sistem politikası tarafından devre dışı bırakılan özellik.';
$labels['hestia_err_12'] = 'Ayrıştırma hatası.';
$labels['hestia_err_13'] = 'Disk hatası.';
$labels['hestia_err_14'] = 'Sunucu yükü çok yüksek.';
$labels['hestia_err_15'] = 'Bağlantı hatası.';
$labels['hestia_err_16'] = 'FTP hatası.';
$labels['hestia_err_17'] = 'Veritabanı hatası.';
$labels['hestia_err_18'] = 'RRD hatası.';
$labels['hestia_err_19'] = 'Güncelleme hatası.';
$labels['hestia_err_20'] = 'Yeniden başlatma hatası.';
?>
