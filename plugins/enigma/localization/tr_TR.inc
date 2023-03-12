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

$labels['encryption'] = 'Şifreleme';
$labels['enigmacerts'] = 'S/MIME sertifikaları';
$labels['enigmakeys'] = 'PGP anahtarları';
$labels['keysfromto'] = 'Anahtarlar $from - $to / $count';
$labels['keyname'] = 'Ad';
$labels['keyid'] = 'Anahtar kodu';
$labels['keyuserid'] = 'Kullanıcı kodu';
$labels['keytype'] = 'Anahtar türü';
$labels['fingerprint'] = 'Parmak izi';
$labels['subkeys'] = 'Alt anahtarlar';
$labels['keyprops'] = 'Anahtar özellikleri';
$labels['basicinfo'] = 'Temel bilgiler';
$labels['userids'] = 'Ek kullanıcılar';
$labels['typepublickey'] = 'Herkese açık anahtar';
$labels['typekeypair'] = 'Anahtar çifti';
$labels['keyattfound'] = 'İleti ekinde PGP anahtarları var.';
$labels['keyattimport'] = 'Anahtar(lar)ı içe aktar.';
$labels['typesign'] = 'İmza';
$labels['typeencrypt'] = 'Şifrele';
$labels['typecert'] = 'Belgele';
$labels['typeauth'] = 'Kimlik doğrulaması';
$labels['subkeyid'] = 'Kod';
$labels['subkeyalgo'] = 'Algoritma';
$labels['subkeycreated'] = 'Eklendi';
$labels['subkeyexpires'] = 'Geçerlilik süresi';
$labels['subkeyusage'] = 'Kullanım';
$labels['expiresnever'] = 'asla';
$labels['unknown'] = 'bilinmeyen';
$labels['uservalid'] = 'Geçerli';
$labels['userid'] = 'Kod';
$labels['valid'] = 'geçerli';
$labels['supportencryption'] = 'İletiler şifrelensin ve imzalansın';
$labels['supportsignatures'] = 'İleti imzaları doğrulansın';
$labels['supportdecryption'] = 'İleti şifreleri çözülsün';
$labels['signdefault'] = 'Varsayılan olarak tüm iletiler imzalansın';
$labels['encryptdefault'] = 'Varsayılan olarak tüm iletiler şifrelensin';
$labels['attachpubkeydefault'] = 'Varsayılan olarak herkese açık PGP anahtarım eklensin';
$labels['passwordtime'] = 'Parolalar için kişisel anahtarları tut';
$labels['nminutes'] = '$m dakika';
$labels['wholesession'] = 'tüm oturum';
$labels['createkeys'] = 'Yeni anahtar çifti oluştur';
$labels['importkeys'] = 'Anahtar(lar)ı içe aktar';
$labels['exportkeys'] = 'Anahtar(lar)ı dışa aktar';
$labels['keyactions'] = 'Anahtar işlemleri...';
$labels['keyremove'] = 'Kaldır';
$labels['keydisable'] = 'Devre dışı';
$labels['keyrevoke'] = 'Geri al';
$labels['keysend'] = 'İletilerde herkese açık anahtar gönderilsin';
$labels['keychpass'] = 'Parolayı değiştir';
$labels['keyadd'] = 'Anahtar ekle';
$labels['newkeyident'] = 'Kimlik';
$labels['newkeypass'] = 'Parola';
$labels['newkeypassconfirm'] = 'Parola Onayı';
$labels['newkeytype'] = 'Anahtar türü';
$labels['rsa2048'] = 'RSA - 2048 bit';
$labels['rsa4096'] = 'RSA - 4096 bit';
$labels['ecckeypair'] = 'ECC (eliptik eğri)';
$labels['keygenerating'] = 'Anahtarlar oluşturuluyor...';
$labels['encryptionoptions'] = 'Şifreleme ayarları...';
$labels['encryptmsg'] = 'Bu iletiyi şifrele';
$labels['signmsg'] = 'Bu iletiyi sayısal olarak imzala';
$labels['sendunencrypted'] = 'Şifrelenmemiş olarak gönder';
$labels['enterkeypasstitle'] = 'Anahtar parolasını yazın';
$labels['enterkeypass'] = 'Gizli anahtarı ($keyid) açmak için $user kullanıcısının parolası gerekli.';
$labels['attachpubkeymsg'] = 'Herkese açık anahtarımı ekle';
$labels['keyexportprompt'] = 'OpenPGP anahtar dosyasına gizli anahtarları da  kaydetmek istiyor musunuz?';
$labels['onlypubkeys'] = 'Yalnızca herkese açık anahtarları dışa aktar';
$labels['withprivkeys'] = 'Gizli anahtarları dışa aktar';
$labels['findkey'] = 'Anahtarı sunucu(lar)da ara';
$labels['keyimportlabel'] = 'Dosyadan içe aktar';
$labels['keyimportsearchlabel'] = 'Anahtarı sunucu(lar)da ara';
$labels['managekeys'] = 'PGP anahtarları yönetimi';
$labels['identitymatchingprivkeys'] = 'Anahtarlığınızda eşleşen $nr PGP kişisel anahtarı var:';
$labels['identitynoprivkeys'] = 'Henüz bu gönderici kimliği için PGP anahtarlığınızda bir kişisel anahtar bulunmuyor.';
$labels['arialabelkeyexportoptions'] = 'Anahtar dışa aktarma ayarları';
$labels['arialabelkeysearchform'] = 'Anahtar arama formu';
$labels['arialabelkeyoptions'] = 'Anahtar seçenekleri';
$messages['sigvalid'] = '$sender imzası geçerli.';
$messages['sigvalidpartial'] = '$sender imzası doğrulandı, ancak metnin tümü imzalanmamış.';
$messages['siginvalid'] = '$sender imzası geçersiz.';
$messages['sigunverified'] = 'İmza doğrulanmamış. Sertifika doğrulanmamış. Sertifika Kodu: $keyid.';
$messages['signokey'] = 'İmza doğrulanmamış. Herkese açık anahtar bulunamadı. Anahtar kodu: $keyid.';
$messages['sigerror'] = 'Sorun var! İmza doğrulanmamış.';
$messages['decryptok'] = 'İleti şifresi çözüldü.';
$messages['decrypterror'] = 'Şifre çözülemedi.';
$messages['decryptnokey'] = 'Şifre çözülemedi. Kişisel anahtar bulunamadı. Anahtar kodu: $keyid.';
$messages['decryptnomdc'] = 'Şifre çözme işlemi atlandı. İletinin bütünlüğü korunmamış.';
$messages['decryptbadpass'] = 'Şifre çözülemedi. Parola geçersiz.';
$messages['decryptnopass'] = 'Şifre çözülemedi. Anahtar parolası gerekli.';
$messages['decryptpartial'] = 'İletinin şifresi çözüldü, fakat metnin tümü şifrelenmemiş.';
$messages['signerror'] = 'İmzalama geçersiz.';
$messages['signnokey'] = 'İmzalanamadı. Kişisel anahtar bulunamadı.';
$messages['signbadpass'] = 'İmzalanamadı. Parola geçersiz.';
$messages['signnopass'] = 'İmzalanamadı. Anahtar parolası gerekli.';
$messages['encrypterror'] = 'Şifrelenemedi.';
$messages['encryptnokey'] = 'Şifrelenemedi. Herkese açık anahtar $email içinde bulunamadı.';
$messages['encryptnoprivkey'] = 'Şifrelenemedi. Kişisel anahtar bulunamadı.';
$messages['nokeysfound'] = 'Anahtarlar bulunamadı';
$messages['keynotfound'] = 'Anahtar buunamadı!';
$messages['keyopenerror'] = 'Sorun var! Anahtar bilgileri alınamıyor.';
$messages['keylisterror'] = 'Sorun var! Anahtar listesi yok.';
$messages['keysimportfailed'] = 'Sorun var! Anahtar(lar) aktarılamıyor.';
$messages['keysimportsuccess'] = 'Anahtar(lar) içe aktarıldı. İçe Aktarılan: $new, Değişmeyen: $old.';
$messages['keyremoving'] = 'Anahtar(lar) kaldırılıyor...';
$messages['keyremoveconfirm'] = 'Seçilmiş anahtar(lar)ı silmek istediğinize emin misiniz?';
$messages['keyremovesuccess'] = 'Anahtar(lar) silindi';
$messages['keyremoveerror'] = 'Anahtar(lar) silinemedi.';
$messages['keyimporttext'] = 'Kişisel ve herkese açık anahtar(lar)ı içe aktarabilir ya da ASCII-Armor biçimindeki imzanızı geri çekebilirsiniz.';
$messages['keyimportsearchtext'] = 'Herkese açık anahtarları, anahtar tanımlayıcı, e-posta ya da kullanıcı adına göre arayarak doğrudan içe aktarabilirsiniz.';
$messages['keystoragenotice'] = 'Herkese açık ve kişisel tüm anahtarlar sunucu üzerinde depolanıyor.';
$messages['formerror'] = 'Formun doldurun. Tüm alanlar zorunludur!';
$messages['passwordsdiffer'] = 'Parola ile onayı aynı değil!';
$messages['keygenerateerror'] = 'Anahtar çifti oluşturulamadı.';
$messages['keygeneratesuccess'] = 'Anahtar çiftiniz oluşturuldu ve içe aktarıldı.';
$messages['keygennosupport'] = 'Tarayıcınız şifrelemeyi desteklemiyor. Anahtar çifti oluşturulamıyor.';
$messages['noidentselected'] = 'Anahtar için en az bir kimlik seçmelisiniz!';
$messages['nonameident'] = 'Kimlik için bir kullanıcı adı belirtmelisiniz!';
$labels['newkeysize'] = 'Anahtar boyutu';
$labels['key2048'] = '2048 bit - varsayılan';
$labels['key4096'] = '4096 bit - daha güvenli';
