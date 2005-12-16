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
 | Author: Melih Taþdizen <melih@marmara.edu.tr>                         |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Kullanýcý Adý';
$labels['password']  = 'Þifre';
$labels['server']    = 'Sunucu';
$labels['login']     = 'Giriþ';

// taskbar
$labels['logout']   = 'Çýkýþ';
$labels['mail']     = 'E-Posta';
$labels['settings'] = 'Kiþisel Ayarlar';
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
$labels['to']      = 'Alýcý';
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
$labels['messagesfromto'] = '$from\' dan/den $to\' e/a $count tane mesaj var.';
$labels['messagenrof'] = '$nr / $count';

$labels['moveto']   = 'Þuraya taþý...';
$labels['download'] = 'Ýndir';

$labels['filename'] = 'Dosya Adý';
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
$labels['tuesday']   = 'Salý';
$labels['wednesday'] = 'Çarþamba';
$labels['thursday']  = 'Perþembe';
$labels['friday']    = 'Cuma';
$labels['saturday']  = 'Cumartesi';

$labels['today'] = 'Bugün';

// toolbar buttons
$labels['writenewmessage']  = 'Yeni Mesaj';
$labels['replytomessage']   = 'Cevapla';
$labels['forwardmessage']   = 'Ýlet';
$labels['deletemessage']    = 'Çöp Klasörüne taþý';
$labels['printmessage']     = 'Mesajý Yazdýr';
$labels['previousmessages'] = 'Sonraki mesajlarý göster';
$labels['nextmessages']     = 'Önceki mesajlarý göster';
$labels['backtolist']       = 'Mesaj Listesi';
$labels['viewsource']       = 'Kaynak Kodu Göster';

$labels['select'] = 'Seç';
$labels['all'] = 'Tümü';
$labels['none'] = 'Hiçbiri';
$labels['unread'] = 'Okunmamýþ';

// message compose
$labels['compose']  = 'Yeni Mesaj';
$labels['sendmessage']  = 'Mesajý gönder';
$labels['addattachment']  = 'Dosya ekle';

$labels['attachments'] = 'Eklenmiþ Dosyalar';
$labels['upload'] = 'Yükle';
$labels['close']  = 'Kapat';

$labels['low']     = 'AzÖnemsiz';
$labels['lowest']  = 'Önemli';
$labels['normal']  = 'Normal';
$labels['high']    = 'Önemli';
$labels['highest'] = 'Çok Önemli';

$labels['showimages'] = 'Resimleri Göster';


// address boook
$labels['name']      = 'Görünen Ýsim';
$labels['firstname'] = 'Ad';
$labels['surname']   = 'Soayad';
$labels['email']     = 'E-Posta';

$labels['addcontact'] = 'Yeni Kontak Ekle';
$labels['editcontact'] = 'Kontak düzenle';

$labels['edit']   = 'Düzen';
$labels['cancel'] = 'Ýptal';
$labels['save']   = 'Kaydet';
$labels['delete'] = 'Sil';

$labels['newcontact']     = 'Yeni kontak ekle';
$labels['deletecontact']  = 'Seçili kontaklarý sil';
$labels['composeto']      = 'Þu kiþiye mesaj yaz:';
$labels['contactsfromto'] = '$from \'den/dan $to \'e/a $count tane mesaj var';
$labels['print']          = 'Yazdýr';
$labels['export']         = 'Farklý kaydet';


// settings
$labels['settingsfor']  = 'Ayarlar:';

$labels['preferences']  = 'Ayarlar';
$labels['userpreferences']  = 'Kullanýcý Ayarlarý';
$labels['editpreferences']  = 'Kullanýcý Ayarlarýný Düzenle';

$labels['identities']  = 'Kimlikler';
$labels['manageidentities']  = 'Bu hesap için kimlikleri düzenle';
$labels['newidentity']  = 'Yeni Kimlik';

$labels['newitem']  = 'Yeni Öðe';
$labels['edititem']  = 'Öðeyi düzenle';

$labels['setdefault']  = 'Varsayýlan olarak ata';
$labels['language']  = 'Dil';
$labels['timezone']  = 'Zaman Dilimi';
$labels['pagesize']  = 'Sayfada Gösterilecek Satýr';


$labels['folders']  = 'Klasörler';
$labels['foldername']  = 'Klasör adý';
$labels['subscribed']  = 'Kayýtlý';
$labels['create']  = 'Oluþtur';
$labels['createfolder']  = 'Yeni klasör oluþtur';
$labels['deletefolder']  = 'Klasör sl';
$labels['managefolders']  = 'Klasörleri düzenle';


?>