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
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Translation / Tercüme: Hasan Cansız <hasancansiz@yahoo.com>           | 
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Kullanıcı Adı';
$labels['password']  = 'Şifre';
$labels['server']    = 'Sunucu';
$labels['login']     = 'Oturum Aç';

// taskbar
$labels['logout']   = 'Oturumu Kapat';
$labels['mail']     = 'E-Posta';
$labels['settings'] = 'Kişisel Ayarlar';
$labels['addressbook'] = 'Rehber';

// mailbox names
$labels['inbox']  = 'Gelenler';
$labels['sent']   = 'Gönderilenler';
$labels['trash']  = 'Çöp Kutusu';
$labels['drafts'] = 'Taslaklar';
$labels['junk']   = 'Gereksiz(spam)';

// message listing
$labels['subject'] = 'Konu';
$labels['from']    = 'Gönderen';
$labels['to']      = 'Alıcı';
$labels['cc']      = 'Karbon Kopya';
$labels['bcc']     = 'Gizli Karbon Kopya';
$labels['replyto'] = 'Yanıtların Gönderileceği Adres';
$labels['date']    = 'Tarih';
$labels['size']    = 'Boyut';
$labels['priority'] = 'Öncelik';
$labels['organization'] = 'Kuruluş';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Klasörler';
$labels['messagesfromto'] = '$count Mesajın $from - $to Arasındaki Mesajlar';
$labels['messagenrof'] = '$count Mesajın $nr .';

$labels['moveto']   = 'Şuraya taşı...';
$labels['download'] = 'İndir';

$labels['filename'] = 'Dosya Adı';
$labels['filesize'] = 'Dosya Boyutu';

$labels['preferhtml'] = 'HTML görüntülemeyi destekle';
$labels['htmlmessage'] = 'HTML Mesaj';
$labels['prettydate'] = 'Tarihi kısaltarak göster';

$labels['addtoaddressbook'] = 'Rehbere Ekle';

// weekdays short
$labels['sun'] = 'Pzr.';
$labels['mon'] = 'Pts.';
$labels['tue'] = 'Salı';
$labels['wed'] = 'Çarş.';
$labels['thu'] = 'Perş.';
$labels['fri'] = 'Cuma';
$labels['sat'] = 'C.tesi';

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
$labels['writenewmessage']  = 'Yeni posta oluştur';
$labels['replytomessage']   = 'Postayı yanıtla';
$labels['replytoallmessage'] = 'Bu postanın gönderildiği herkesi yanıtla';
$labels['forwardmessage']   = 'Postayı ilet';
$labels['deletemessage']    = 'Çöp Kutusuna At';
$labels['printmessage']     = 'Yazdır';
$labels['previousmessages'] = 'Önceki postaları göster';
$labels['nextmessages']     = 'Sonraki postaları göster';
$labels['backtolist']       = 'Posta kutusuna dön';
$labels['viewsource']       = 'Kaynağı göster';

$labels['select'] = 'Seç';
$labels['all'] = 'Hepsi';
$labels['none'] = 'Hiçbiri';
$labels['unread'] = 'Okunmamış';

$labels['compact'] = 'Kompakt';
$labels['empty'] = 'Boşalt';
$labels['purge'] = 'Sil';

$labels['quota'] = 'Disk kullanımı';


// message compose
$labels['compose']  = 'Yeni posta oluştur';
$labels['sendmessage']  = 'Postayı gönder';
$labels['addattachment']  = 'Dosya ekle';
$labels['charset']  = 'Karakter seti';

$labels['attachments'] = 'Ekler';
$labels['upload'] = 'Yükle';
$labels['close']  = 'Kapat';

$labels['low']     = 'Düşük';
$labels['lowest']  = 'Çok düşük';
$labels['normal']  = 'Normal';
$labels['high']    = 'Yüksek';
$labels['highest'] = 'Çok yüksek';

$labels['nosubject']  = '(Konu Belirtilmemiş)';
$labels['showimages'] = 'Grafikleri görüntüle';


// address boook
$labels['name']      = 'İsmi görüntüle';
$labels['firstname'] = 'İsim';
$labels['surname']   = 'Soy isim';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Yeni kişi ekle';
$labels['editcontact'] = 'Kişiyi düzenle';

$labels['edit']   = 'Düzenle';
$labels['cancel'] = 'İptal';
$labels['save']   = 'Kaydet';
$labels['delete'] = 'Sil';

$labels['newcontact']     = 'Yeni kişi Kartı Ekle';
$labels['addcontact']     = 'Seçili kişiyi rehbere ekle';
$labels['deletecontact']  = 'Seçili kişileri sil';
$labels['composeto']      = 'Seçili kişiye posta gönder';
$labels['contactsfromto'] = '$count Kişinin $from - $to arası ';
$labels['print']          = 'Yazdır';
$labels['export']         = 'Export';

// LDAP search
$labels['ldapsearch'] = 'LDAP directory araması';

$labels['ldappublicsearchname']    = 'Kişi adı';
$labels['ldappublicsearchtype'] = 'Tam olarak uysun?';
$labels['ldappublicserverselect'] = 'Serverleri seç';
$labels['ldappublicsearchfield'] = 'Arama açık';
$labels['ldappublicsearchform'] = 'Kişi ara';
$labels['ldappublicsearch'] = 'Ara';


// settings
$labels['settingsfor']  = 'Ayarlar';

$labels['preferences']  = 'Tercihler';
$labels['userpreferences']  = 'Kullanıcı tercihleri';
$labels['editpreferences']  = 'Kullanıcı tercihlerini düzenle';

$labels['identities']  = 'Kimlikler';
$labels['manageidentities']  = 'Bu hesap için kimlikleri düzenle';
$labels['newidentity']  = 'Yeni kimlik';

$labels['newitem']  = 'Yeni etiket';
$labels['edititem']  = 'Etiket düzenle';

$labels['setdefault']  = 'Varsayılan olarak ayarla';
$labels['language']  = 'Dil';
$labels['timezone']  = 'Saat dilimi';
$labels['pagesize']  = 'Bir sayfada kaç posta gösterilsin';
$labels['signature'] = 'İmza';

$labels['folder']  = 'Klasör';
$labels['folders']  = 'Klasörler';
$labels['foldername']  = 'Klasör ismi';
$labels['subscribed']  = 'Görülebilir';
$labels['create']  = 'Yeni Oluştur';
$labels['createfolder']  = 'Yeni klasör oluştur';
$labels['deletefolder']  = 'Klasörü sil';
$labels['managefolders']  = 'Klasörleri düzenle';

$labels['sortby'] = 'Sırala';
$labels['sortasc']  = 'Azdan çoğa';
$labels['sortdesc'] = 'Çoktan aza';

?>