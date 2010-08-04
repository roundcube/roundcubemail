<?php

/*

+-----------------------------------------------------------------------+
| language/tr/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Hasan Cansız <hasancansiz@yahoo.com>                          |
| Author: Gökdeniz Karadağ <gokdenizk@gmail.com>                        |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Giriş Başarısız';
$messages['cookiesdisabled'] = 'Tarayıcınız çerezleri kabul etmiyor';
$messages['sessionerror'] = 'Oturumunuz sona ermiş veya hatalı';
$messages['imaperror'] = 'IMAP sunucusuna bağlanılamadı';
$messages['servererror'] = 'Sunucu Hatası!';
$messages['invalidrequest'] = 'Geçersiz İstek! Veri kaydedilmedi';
$messages['nomessagesfound'] = 'Posta kutusunda ileti bulunamadı';
$messages['loggedout'] = 'Oturumu başarılı bir şekilde kapattınız. Güle güle!';
$messages['mailboxempty'] = 'Posta kutusu boş';
$messages['loading'] = 'Yükleniyor...';
$messages['uploading'] = 'Dosya yükleniyor...';
$messages['loadingdata'] = 'Veriler yükleniyor...';
$messages['checkingmail'] = 'Yeni postalara bakılıyor...';
$messages['sendingmessage'] = 'Posta gönderiliyor...';
$messages['messagesent'] = 'Posta gönderildi';
$messages['savingmessage'] = 'Posta kaydediliyor...';
$messages['messagesaved'] = 'Posta taslaklara kaydedildi';
$messages['successfullysaved'] = 'Kaydedildi';
$messages['addedsuccessfully'] = 'Kişi adres defterine eklendi';
$messages['contactexists'] = 'Rehberde bu e-posta adresine sahip biri zaten var';
$messages['blockedimages'] = 'Kişisel gizliliğinizi korumak amacıyla postadaki resimler engellendi';
$messages['encryptedmessage'] = 'Bu şifrelenmiş bir posta olduğu için gösterilemiyor. Özür dilerim!';
$messages['nocontactsfound'] = 'Kişi bulunamadı';
$messages['contactnotfound'] = 'İstenen kişi bulunamadı';
$messages['sendingfailed'] = 'Posta gönderilemedi';
$messages['senttooquickly'] = 'Lütfen bu postayı göndermeden önce $sec saniye bekleyin';
$messages['errorsavingsent'] = 'Gönderilen postayı kaydederken hata oluştu';
$messages['errorsaving'] = 'Kaydederken bir hata oluştu';
$messages['errormoving'] = 'Posta taşınamadı';
$messages['errorcopying'] = 'Posta kopyalanamadı';
$messages['errordeleting'] = 'Posta silinemedi';
$messages['errormarking'] = 'Posta işaretlenemedi';
$messages['deletecontactconfirm'] = 'Seçili kişileri silmek istediğinizden emin misiniz?';
$messages['deletemessagesconfirm'] = 'Seçili postaları silmek istediğinizden emin misiniz?';
$messages['deletefolderconfirm'] = 'Bu klasörü silmek istediğinizden emin misiniz?';
$messages['purgefolderconfirm'] = 'Bu klasördeki tüm postaları silmek istediğinizden emin misiniz?';
$messages['foldercreating'] = 'Klasör oluştuluyor...';
$messages['folderdeleting'] = 'Klasör siliniyor...';
$messages['folderrenaming'] = 'Klasör yeniden adlandırılıyor...';
$messages['foldermoving'] = 'Klasör taşınıyor...';
$messages['formincomplete'] = 'Form tam olarak doldurulmadı';
$messages['noemailwarning'] = 'Lütfen geçerli bir e-posta adresi girin';
$messages['nonamewarning'] = 'Lütfen bir isim girin';
$messages['nopagesizewarning'] = 'Lütfen bir sayfa boyutu girin';
$messages['nosenderwarning'] = 'Lütfen gönderici e-posta adresini girin';
$messages['norecipientwarning'] = 'Lütfen en az bir alıcı belirtin';
$messages['nosubjectwarning'] = '"Konu" kutusu boş bırakılmış. Şimdi bir konu belirtmek ister misiniz?';
$messages['nobodywarning'] = 'Postayı boş olarak gönder?';
$messages['notsentwarning'] = 'Posta gönderilmedi. Postanızı iptal etmek istiyor musunuz?';
$messages['noldapserver'] = 'Lütfen arama için bir LDAP sunucu seçin';
$messages['nocontactsreturned'] = 'Kişi bulunamadı';
$messages['nosearchname'] = 'Lütfen bir kişi ismi veya e-posta adresi girin';
$messages['notuploadedwarning'] = 'Henüz tüm ekli dosyalar yüklenmedi. Lütfen bekleyin ya da yüklemeyi iptal edin.';
$messages['searchsuccessful'] = '$nr posta bulundu';
$messages['searchnomatch'] = 'Aramanıza uygun hiçbir sonuç bulunamadı';
$messages['searching'] = 'Aranıyor...';
$messages['checking'] = 'Denetleniyor...';
$messages['nospellerrors'] = 'Yazım hatası bulunamadı';
$messages['folderdeleted'] = 'Klasör silindi';
$messages['deletedsuccessfully'] = 'Silindi';
$messages['converting'] = 'Postanın biçimlendirmesi kaldırılıyor...';
$messages['messageopenerror'] = 'Sunucudan posta yüklenemedi';
$messages['fileuploaderror'] = 'Dosya yükleme başarısız';
$messages['filesizeerror'] = 'Yüklenen dosya en büyük dosya boyunu ($size) aşıyor';
$messages['copysuccess'] = '$nr adres kopyalandı';
$messages['copyerror'] = 'Hiçbir adres kopyalanamadı';
$messages['sourceisreadonly'] = 'Adres kaynağı salt okunur durumda';
$messages['errorsavingcontact'] = 'Kişinin adresi kaydedilemedi';
$messages['movingmessage'] = 'Posta taşınıyor...';
$messages['copyingmessage'] = 'Posta kopyalanıyor...';
$messages['receiptsent'] = 'Okundu onayı gönderildi';
$messages['errorsendingreceipt'] = 'Okundu onayı gönderilemedi';
$messages['nodeletelastidentity'] = 'Son kimliğiniz olduğu için bu kimliği silemezsiniz';
$messages['addsubfolderhint'] = 'Bu klasör, şu an seçili olanın alt klasörü olarak oluşturulacak';
$messages['forbiddencharacter'] = 'Klasör ismi yasaklanmış bir karakter içeriyor';
$messages['selectimportfile'] = 'Lütfen yüklenecek dosyayı seçin';
$messages['addresswriterror'] = 'Seçili adres defterine yazılamaz';
$messages['contactaddedtogroup'] = 'Kişiler bu gruba eklendi';
$messages['contactremovedfromgroup'] = 'Kişiler bu gruptan çıkarıldı';
$messages['importwait'] = 'Aktarılıyor, lütfen bekleyin...';
$messages['importerror'] = 'İçe aktarma başarısız! Yüklenen dosya geçerli bir vCard dosyası değil';
$messages['importconfirm'] = '<b>$inserted kişi başarıyla aktarıldı, halihazırda var olan $skipped kişi atlandı</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Bu işleme izin verilmedi!';
$messages['nofromaddress'] = 'Seçili kimlikte e-posta adresi yok';
$messages['editorwarning'] = 'Düz metin düzenleyiciye geçmek, metin üzerindeki bütün biçimlendirmeleri kaldıracak. Devam etmek istiyor musunuz?';
$messages['httpreceivedencrypterror'] = 'Ayarlarda bir hata oluştu. Sistem yöneticinize haber verin. <b>Postanız gönderilemedi</b>';
$messages['smtpconnerror'] = 'SMTP Hatası ($code): Sunucuya bağlanılamadı';
$messages['smtpautherror'] = 'SMTP Hatası ($code): Kimlik doğrulama başarısız oldu';
$messages['smtpfromerror'] = 'SMTP Hatası ($code): Gönderen adresi "$from" olarak ayarlanamadı ($msg)';
$messages['smtptoerror'] = 'SMTP Hatası ($code): "$to" alıcısı eklenemedi ($msg)';
$messages['smtprecipientserror'] = 'SMTP Hatası: Posta alıcılarının listesi ayrıştırılamadı';
$messages['smtperror'] = 'SMTP Hatası: $msg';
$messages['emailformaterror'] = 'Hatalı e-posta adresi: $email';
$messages['toomanyrecipients'] = 'Çok fazla alıcı. En fazla $max alıcı girebilirsiniz.';
$messages['maxgroupmembersreached'] = 'Grup üyelerinin sayısı $max sınırını aşıyor.';
$messages['internalerror'] = 'Bir hata oluştu. Lütfen tekrar deneyin.';

?>
