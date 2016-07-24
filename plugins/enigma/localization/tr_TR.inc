<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2016, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/
$labels['encryption'] = 'Şifreleme';
$labels['enigmacerts'] = 'S/MIME Sertifilara';
$labels['enigmakeys'] = 'PGP Anahtarları';
$labels['keysfromto'] = 'Anahtarlar $form dan $to  için $count';
$labels['keyname'] = 'Ad';
$labels['keyid'] = 'Anahtar ID';
$labels['keyuserid'] = 'Kullanıcı ID';
$labels['keytype'] = 'Anahtar tipi';
$labels['fingerprint'] = 'parmakizi';
$labels['subkeys'] = 'Altanahtarlar';
$labels['basicinfo'] = 'Temel Bilgiler';
$labels['userids'] = 'Ek Kullanıcılar';
$labels['typepublickey'] = 'Genel Anahtar';
$labels['typekeypair'] = 'Anahtar çifti';
$labels['keyattfound'] = 'Mesaj ekiniz PGP anahtar(lar)ı içeriyor';
$labels['keyattimport'] = 'Anahtar(lar)ı içeri al.';
$labels['typesign'] = 'İmza';
$labels['typeencrypt'] = 'Şifreleme';
$labels['typecert'] = 'Belgele';
$labels['typeauth'] = 'Doğrulama';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'algoritma';
$labels['subkeycreated'] = 'Oluşturuldu';
$labels['subkeyexpires'] = 'Geçerlilik süresi';
$labels['subkeyusage'] = 'Kullanım';
$labels['expiresnever'] = 'asla';
$labels['unknown'] = 'bilinmeyen';
$labels['uservalid'] = 'Geçerli';
$labels['userid'] = 'ID';
$labels['valid'] = 'geçerli';
$labels['supportencryption'] = 'İletileri şifrele ve imzala';
$labels['supportsignatures'] = 'İletilerin imzalarını doğrula';
$labels['supportdecryption'] = 'İletilerin şifresini çöz';
$labels['signdefault'] = 'Tüm iletileri imzala-varsayılan';
$labels['encryptdefault'] = 'Tüm iletileri şifrele-varsayılan-';
$labels['attachpubkeydefault'] = 'PGP anahtarını varsayılan olarak ekle';
$labels['passwordtime'] = 'Parolalar için özel anahtarları tut';
$labels['nminutes'] = '$m dakika(lar)';
$labels['wholesession'] = 'bütün oturum';
$labels['createkeys'] = 'Yeni anahtar çifti oluşturun';
$labels['importkeys'] = 'Anahtar(lar)ı içeri al.';
$labels['exportkeys'] = 'Anahtar(lar)ı dışarı ver';
$labels['keyactions'] = 'Anahtar eylemleri';
$labels['keyremove'] = 'Kaldır';
$labels['keydisable'] = 'Devredışı';
$labels['keyrevoke'] = 'Geri alın';
$labels['keysend'] = 'Ortak anahtarı mesajla gönder';
$labels['keychpass'] = 'Parolayı değiştirin';
$labels['newkeyident'] = 'Kimlik:';
$labels['newkeypass'] = 'Parola:';
$labels['newkeypassconfirm'] = 'Porolayı doğrulayın:';
$labels['newkeysize'] = 'Anahtar uzunluğu:';
$labels['key2048'] = '2048 bit-Varsayılan';
$labels['key4096'] = '4096 bit- daha güvenli';
$labels['keygenerating'] = 'Anahtarlar oluştur...';
$labels['encryptionoptions'] = 'Şifreleme seçenekleri...';
$labels['encryptmsg'] = 'Bu iletiyi şifrele';
$labels['signmsg'] = 'İletiyi dijital imzala';
$labels['enterkeypasstitle'] = 'Anahtar kelimenizi girin';
$labels['enterkeypass'] = '$user Kullanıcısının anahtar kelimesi gizli anahtarı ($keyid) açmak için gerekli.';
$labels['arialabelkeyexportoptions'] = 'Anahtarları dışa aktarma seçenekleri';
$labels['attachpubkeymsg'] = 'Genel anahtarımı ekle';
$labels['keyexportprompt'] = 'OpenPGP anahtar dosyasına gizli anahtarları da  kaydetmek istiyor musunuz?';
$labels['onlypubkeys'] = 'Yalnız genel anahtarları dışarı aktar';
$labels['withprivkeys'] = 'Gizli anahtarı dışarı aktar';
$labels['findkey'] = 'Anahtarı sunucu(lar)da ara';
$labels['keyimportlabel'] = 'Dosyadan al';
$labels['keyimportsearchlabel'] = 'Anahtarı sunucu(lar)da ara';
$messages['sigvalid'] = 'Geçerli imza $sender den.';
$messages['sigvalidpartial'] = '$sender imzası doğrulandı, fakat mesajın tümü imzalanmamış.';
$messages['siginvalid'] = 'Geçersiz imza $sender den.';
$messages['sigunverified'] = 'İmza doğrulanmamış. Sertifika doğrulanmamış. Sertifika ID: $keyid.';
$messages['signokey'] = 'İmza doğrulanmamış. Genel anahtar bulunamadı. Anahtar ID: $keyid.';
$messages['sigerror'] = 'Hata!... İmza doğrulanmamış.';
$messages['decryptok'] = 'İleti şifresini çöz.';
$messages['decrypterror'] = 'Şifre çözülemedi.';
$messages['decryptnokey'] = 'Şifre çözülemedi. Özel anahtar bulunamadı. Anahtar ID: $keyid.';
$messages['decryptbadpass'] = 'Şifre çözülemedi. Parola yanlış.';
$messages['decryptnopass'] = 'Şifre çözülemedi. Anahtar parola gerekli.';
$messages['decryptpartial'] = 'Mesaj çözüldü, fakat mesajın tümü şifrelenmemiş.';
$messages['signerror'] = 'Geçersiz imza.';
$messages['signnokey'] = 'Geçersiz imza. Özel anahtar bulunamadı.';
$messages['signbadpass'] = 'Geçersiz imza. Parola yanlış.';
$messages['signnopass'] = 'Geçersiz imza. Anahtar parola gerekli.';
$messages['encrypterror'] = 'Şifreleme başarısız.';
$messages['encryptnokey'] = 'Şifreleme başarısız. Genel anahtar $email içinde bulunamadı.';
$messages['nokeysfound'] = 'Anahtarlar bulunamadı';
$messages['keynotfound'] = 'Anahtar buunamadı!';
$messages['keyopenerror'] = 'Hata!.. Anahtar bilgileri alınamıyor.';
$messages['keylisterror'] = 'Hata!... Anahtar listesi yok.';
$messages['keysimportfailed'] = 'Hata!... Anahtar(lar) aktarılamıyor.';
$messages['keysimportsuccess'] = 'Anahtar(lar) aktarıldı. Aktarıldı: $new, başarısız: $old.';
$messages['keyremoving'] = 'Anahtar(lar)ı kaldırın...';
$messages['keyremoveconfirm'] = 'Seçili anahtar(lar)ı silmek istiyormusunuz?';
$messages['keyremovesuccess'] = 'Anahtar(lar) silindi';
$messages['keyremoveerror'] = 'Anahtar(lar) silinemedi.';
$messages['keyimporttext'] = 'Özel ve genel anahtar(lar)i aktarabilir veya ASCII-Armor formatlı imzanızı geri alabilirsiniz.';
$messages['keyimportsearchtext'] = 'Genel anahtarlar ile tanımlayıcı anahtarları  email veya kullanıcı adı ile arayıp, doğrudan aktarabilirsiniz.';
$messages['formerror'] = 'Formun doldurunuz. -Tüm alanlar zorunlu-';
$messages['passwordsdiffer'] = 'Parolalar eşleşmiyor';
$messages['nonameident'] = 'Kimlik için kullanıcı adı tanımlamalısınız!';
$messages['keygenerateerror'] = 'Anahtar çifti oluşturulamadı.';
$messages['keygeneratesuccess'] = 'Anahtar çiftiniz oluşturuldu ve aktarıldı.';
$messages['keygennosupport'] = 'Tarayıcınız şifrelemeyi desteklemiyor. Anahtar çifti oluşturulamıyor.';
?>
