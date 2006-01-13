<?php

/*
 +-----------------------------------------------------------------------+
 | language/tr/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Melih Taşdizen <melih@marmara.edu.tr>                         |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Kullanıcı Adı';
$labels['password']  = 'Şifre';
$labels['server']    = 'Sunucu';
$labels['login']     = 'Giriş';

// taskbar
$labels['logout']   = 'Çıkış';
$labels['mail']     = 'E-Posta';
$labels['settings'] = 'Kişisel Ayarlar';
$labels['addressbook'] = 'Adres Defteri';

// mailbox names
$labels['inbox']  = 'Gelen Kutusu';
$labels['sent']   = 'Gönderilenler';
$labels['trash']  = 'Çöp';
$labels['drafts'] = 'Taslaklar';
$labels['junk']   = 'Gereksiz(spam)';

// message listing
$labels['subject'] = 'Konu';
$labels['from']    = 'Gönderen';
$labels['to']      = 'Alıcı';
$labels['cc']      = 'Kopya';
$labels['bcc']     = 'Gizli Kopya';
$labels['replyto'] = 'Cevapla';
$labels['date']    = 'Tarih';
$labels['size']    = 'Boyut';
$labels['priority'] = 'Öncelik';
$labels['organization'] = 'Organizasyon';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Klasörler';
$labels['messagesfromto'] = '$from - $to arası, Toplam $count mesaj';
$labels['messagenrof'] = '$nr / $count';

$labels['moveto']   = 'Şuraya taşı...';
$labels['download'] = 'İndir';

$labels['filename'] = 'Dosya Adı';
$labels['filesize'] = 'Dosya Boyutu';

$labels['preferhtml'] = 'HTML Tercih et';
$labels['htmlmessage'] = 'HTML Mesaj';
$labels['prettydate'] = 'Tarihleri Formatla';

$labels['addtoaddressbook'] = 'Add to address book';

// weekdays short
$labels['sun'] = 'Paz';
$labels['mon'] = 'Pts';
$labels['tue'] = 'Sal';
$labels['wed'] = 'Çar';
$labels['thu'] = 'Per';
$labels['fri'] = 'Cum';
$labels['sat'] = 'Cts';

// weekdays long
$labels['sunday']    = 'Pazar';
$labels['monday']    = 'Pazartesi';
$labels['tuesday']   = 'Salı';
$labels['wednesday'] = 'Çarşamba';
$labels['thursday']  = 'Perşembe';
$labels['friday']    = 'Cuma';
$labels['saturday']  = 'Cumartesi';

$labels['today'] = 'Bugün';

// toolbar buttons
$labels['writenewmessage']  = 'Yeni Mesaj';
$labels['replytomessage']   = 'Cevapla';
$labels['forwardmessage']   = 'İlet';
$labels['deletemessage']    = 'Çöp Klasörüne taşı';
$labels['printmessage']     = 'Mesajı Yazdır';
$labels['previousmessages'] = 'Sonraki mesajları göster';
$labels['nextmessages']     = 'Önceki mesajları göster';
$labels['backtolist']       = 'Mesaj Listesi';
$labels['viewsource']       = 'Kaynak Kodu Göster';

$labels['select'] = 'Seç';
$labels['all'] = 'Tümü';
$labels['none'] = 'Hiçbiri';
$labels['unread'] = 'Okunmamış';

// message compose
$labels['compose']  = 'Yeni Mesaj';
$labels['sendmessage']  = 'Mesajı gönder';
$labels['addattachment']  = 'Dosya ekle';

$labels['attachments'] = 'Eklenmiş Dosyalar';
$labels['upload'] = 'Yükle';
$labels['close']  = 'Kapat';

$labels['low']     = 'AzÖnemsiz';
$labels['lowest']  = 'Önemli';
$labels['normal']  = 'Normal';
$labels['high']    = 'Önemli';
$labels['highest'] = 'Çok Önemli';

$labels['showimages'] = 'Resimleri Göster';


// address boook
$labels['name']      = 'Görünen İsim';
$labels['firstname'] = 'Ad';
$labels['surname']   = 'Soayad';
$labels['email']     = 'E-Posta';

$labels['addcontact'] = 'Yeni Kontak Ekle';
$labels['editcontact'] = 'Kontak düzenle';

$labels['edit']   = 'Düzen';
$labels['cancel'] = 'İptal';
$labels['save']   = 'Kaydet';
$labels['delete'] = 'Sil';

$labels['newcontact']     = 'Yeni kontak ekle';
$labels['deletecontact']  = 'Seçili kontakları sil';
$labels['composeto']      = 'Şu kişiye mesaj yaz:';
$labels['contactsfromto'] = '$from - $to arası, Toplam $count kontak';
$labels['print']          = 'Yazdır';
$labels['export']         = 'Farklı kaydet';


// settings
$labels['settingsfor']  = 'Ayarlar:';

$labels['preferences']  = 'Ayarlar';
$labels['userpreferences']  = 'Kullanıcı Ayarları';
$labels['editpreferences']  = 'Kullanıcı Ayarlarını Düzenle';

$labels['identities']  = 'Kimlikler';
$labels['manageidentities']  = 'Bu hesap için kimlikleri düzenle';
$labels['newidentity']  = 'Yeni Kimlik';

$labels['newitem']  = 'Yeni Öğe';
$labels['edititem']  = 'Öğeyi düzenle';

$labels['setdefault']  = 'Varsayılan olarak ata';
$labels['language']  = 'Dil';
$labels['timezone']  = 'Zaman Dilimi';
$labels['pagesize']  = 'Sayfada Gösterilecek Satır';


$labels['folders']  = 'Klasörler';
$labels['foldername']  = 'Klasör adı';
$labels['subscribed']  = 'Kayıtlı';
$labels['create']  = 'Oluştur';
$labels['createfolder']  = 'Yeni klasör oluştur';
$labels['deletefolder']  = 'Klasör sl';
$labels['managefolders']  = 'Klasörleri düzenle';


?>