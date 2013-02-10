<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'Bir hata oluştu!';
$messages['loginfailed']  = 'Giriş Başarısız';
$messages['cookiesdisabled'] = 'Tarayıcınız çerezleri kabul etmiyor';
$messages['sessionerror'] = 'Oturumunuz sona ermiş veya hatalı';
$messages['storageerror'] = 'IMAP sunucusuna bağlanılamadı';
$messages['servererror'] = 'Sunucu Hatası!';
$messages['servererrormsg'] = 'Sunucu hatası: $msg';
$messages['dberror'] = 'Veritabanı Hatası!';
$messages['requesttimedout'] = 'İstek zaman aşımına uğradı';
$messages['errorreadonly'] = 'İşlem gerçekleştirilemedi. Dizin salt okunabilir';
$messages['errornoperm'] = 'İşlem gerçekleştirilemedi. İzin verilmedi.';
$messages['invalidrequest'] = 'Geçersiz İstek! Veri kaydedilmedi';
$messages['invalidhost'] = 'Geçersiz sunucu adı.';
$messages['nomessagesfound'] = 'Posta kutusunda ileti bulunamadı';
$messages['loggedout'] = 'Oturumu başarılı bir şekilde kapattınız. Güle güle!';
$messages['mailboxempty'] = 'Posta kutusu boş';
$messages['refreshing'] = 'Yenileniyor...';
$messages['loading'] = 'Yükleniyor...';
$messages['uploading'] = 'Dosya yükleniyor...';
$messages['uploadingmany'] = 'Dosyalar yükleniyor...';
$messages['loadingdata'] = 'Veriler yükleniyor...';
$messages['checkingmail'] = 'Yeni postalara bakılıyor...';
$messages['sendingmessage'] = 'Posta gönderiliyor...';
$messages['messagesent'] = 'Posta gönderildi';
$messages['savingmessage'] = 'Posta kaydediliyor...';
$messages['messagesaved'] = 'Posta taslaklara kaydedildi';
$messages['successfullysaved'] = 'Kaydedildi';
$messages['addedsuccessfully'] = 'Kişi adres defterine eklendi';
$messages['contactexists'] = 'Rehberde bu e-posta adresine sahip biri zaten var';
$messages['contactnameexists'] = 'Rehberde bu ada sahip biri zaten var.';
$messages['blockedimages'] = 'Kişisel gizliliğinizi korumak amacıyla postadaki resimler engellendi';
$messages['encryptedmessage'] = 'Bu şifrelenmiş bir posta olduğu için gösterilemiyor. Özür dilerim!';
$messages['nocontactsfound'] = 'Kişi bulunamadı';
$messages['contactnotfound'] = 'İstenen kişi bulunamadı';
$messages['contactsearchonly'] = 'Kişi aramak için arama terimleri giriniz';
$messages['sendingfailed'] = 'Posta gönderilemedi';
$messages['senttooquickly'] = 'Lütfen bu postayı göndermeden önce $sec saniye bekleyin';
$messages['errorsavingsent'] = 'Gönderilen postayı kaydederken hata oluştu';
$messages['errorsaving'] = 'Kaydederken bir hata oluştu';
$messages['errormoving'] = 'Posta taşınamadı';
$messages['errorcopying'] = 'Posta kopyalanamadı';
$messages['errordeleting'] = 'Posta silinemedi';
$messages['errormarking'] = 'Posta işaretlenemedi';
$messages['deletecontactconfirm']  = 'Seçili kişileri silmek istediğinizden emin misiniz?';
$messages['deletegroupconfirm']  = 'Seçili grupları silmek istediğinizden emin misiniz?';
$messages['deletemessagesconfirm'] = 'Seçili postaları silmek istediğinizden emin misiniz?';
$messages['deletefolderconfirm']  = 'Bu klasörü silmek istediğinizden emin misiniz?';
$messages['purgefolderconfirm']  = 'Bu klasördeki tüm postaları silmek istediğinizden emin misiniz?';
$messages['contactdeleting'] = 'Kişi(er) siliniyor...';
$messages['groupdeleting'] = 'Grup siliniyor...';
$messages['folderdeleting'] = 'Klasör siliniyor...';
$messages['foldermoving'] = 'Klasör taşınıyor...';
$messages['foldersubscribing'] = 'Klasöre abone olunuyor...';
$messages['folderunsubscribing'] = 'Klasör aboneliği kaldırılıyor...';
$messages['formincomplete'] = 'Form tam olarak doldurulmadı';
$messages['noemailwarning'] = 'Lütfen geçerli bir e-posta adresi girin';
$messages['nonamewarning']  = 'Lütfen bir isim girin';
$messages['nopagesizewarning'] = 'Lütfen bir sayfa boyutu girin';
$messages['nosenderwarning'] = 'Lütfen gönderici e-posta adresini girin';
$messages['norecipientwarning'] = 'Lütfen en az bir alıcı belirtin';
$messages['nosubjectwarning']  = '"Konu" kutusu boş bırakılmış. Şimdi bir konu belirtmek ister misiniz?';
$messages['nobodywarning'] = 'Postayı boş olarak gönder?';
$messages['notsentwarning'] = 'Posta gönderilmedi. Postanızı iptal etmek istiyor musunuz?';
$messages['noldapserver'] = 'Lütfen arama için bir LDAP sunucu seçin';
$messages['nosearchname'] = 'Lütfen bir kişi ismi veya e-posta adresi girin';
$messages['notuploadedwarning'] = 'Henüz tüm ekli dosyalar yüklenmedi. Lütfen bekleyin ya da yüklemeyi iptal edin.';
$messages['searchsuccessful'] = '$nr posta bulundu';
$messages['contactsearchsuccessful'] = '$nr kişi bulundu';
$messages['searchnomatch'] = 'Aramanıza uygun hiçbir sonuç bulunamadı';
$messages['searching'] = 'Aranıyor...';
$messages['checking'] = 'Denetleniyor...';
$messages['nospellerrors'] = 'Yazım hatası bulunamadı';
$messages['folderdeleted'] = 'Klasör silindi';
$messages['foldersubscribed'] = 'Klasöre abone olundu';
$messages['folderunsubscribed'] = 'Klasör aboneliği kaldırıldı';
$messages['folderpurged'] = 'Klasör sıklaştırıldı';
$messages['folderexpunged'] = 'Klasör boşaltıldı';
$messages['deletedsuccessfully'] = 'Silindi';
$messages['converting'] = 'Postanın biçimlendirmesi kaldırılıyor...';
$messages['messageopenerror'] = 'Sunucudan posta yüklenemedi';
$messages['fileuploaderror'] = 'Dosya yükleme başarısız';
$messages['filesizeerror'] = 'Yüklenen dosya en büyük dosya boyunu ($size) aşıyor';
$messages['copysuccess'] = '$nr adres kopyalandı';
$messages['copyerror'] = 'Hiçbir adres kopyalanamadı';
$messages['sourceisreadonly'] = 'Adres kaynağı salt okunur durumda';
$messages['errorsavingcontact'] = 'Kişinin adresi kaydedilemedi';
$messages['movingmessage'] = 'Posta(lar) taşınıyor...';
$messages['copyingmessage'] = 'Posta(lar) kopyalanıyor...';
$messages['copyingcontact'] = 'Kişile(ler) kopyalanıyor...';
$messages['deletingmessage'] = 'Posta(lar) siliniyor...';
$messages['markingmessage'] = 'Posta(lar) işaretleniyor...';
$messages['addingmember'] = 'Gruba kişi(ler) ekleniyor...';
$messages['removingmember'] = 'Gruptan kişi(ler) siliniyor...';
$messages['receiptsent'] = 'Okundu onayı gönderildi';
$messages['errorsendingreceipt'] = 'Okundu onayı gönderilemedi';
$messages['deleteidentityconfirm'] = 'Bu kimliği silmek istediğinizden emin misiniz?';
$messages['nodeletelastidentity'] = 'Son kimliğiniz olduğu için bu kimliği silemezsiniz';
$messages['forbiddencharacter'] = 'Klasör ismi yasaklanmış bir karakter içeriyor';
$messages['selectimportfile'] = 'Lütfen yüklenecek dosyayı seçin';
$messages['addresswriterror'] = 'Seçili adres defterine yazılamaz';
$messages['contactaddedtogroup'] = 'Kişiler bu gruba eklendi';
$messages['contactremovedfromgroup'] = 'Kişiler bu gruptan çıkarıldı';
$messages['nogroupassignmentschanged'] = 'Grup atamalarında bir değişiklik yapılmadı';
$messages['importwait'] = 'Aktarılıyor, lütfen bekleyin...';
$messages['importformaterror'] = 'İçe aktarım başarısız. Yüklenen dosya geçerli bir içe aktarım dosyası değil.';
$messages['importconfirm'] = '<b>$inserted kişi başarıyla aktarıldı</b>';
$messages['importconfirmskipped'] = '<b>Var olan $skipped girdi atlandı</b>';
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
$messages['contactdelerror'] = 'Kişi(ler) silinemedi';
$messages['contactdeleted'] = 'Kişi(ler) silindi';
$messages['contactrestoreerror'] = 'Silinen kişi(ler) geri getirilemiyor.';
$messages['contactrestored'] = 'Kişi(ler) başarıyla geri getirildi.';
$messages['groupdeleted'] = 'Grup silindi';
$messages['grouprenamed'] = 'Grup ismi değiştirildi';
$messages['groupcreated'] = 'Grup oluşturuldu';
$messages['savedsearchdeleted'] = 'Kayıtlı arama silindi.';
$messages['savedsearchdeleteerror'] = 'Kayıtlı arama silinemedi.';
$messages['savedsearchcreated'] = 'Kayıtlı arama oluşturuldu.';
$messages['savedsearchcreateerror'] = 'Kayıtlı arama oluşturulamadı.';
$messages['messagedeleted'] = 'Posta(lar) silindi';
$messages['messagemoved'] = 'Posta(lar) taşındı';
$messages['messagecopied'] = 'Posta(lar) kopyalandı';
$messages['messagemarked'] = 'Posta(lar) işaretlendi';
$messages['autocompletechars'] = 'otomatik tamamlama için en az $min karakter girin';
$messages['autocompletemore'] = 'Fazla sayıda eşleşen öğe bulundu. Lütfen daha fazla karakter yazın.';
$messages['namecannotbeempty'] = 'İsim alanı boş olamaz';
$messages['nametoolong'] = 'İsim çok uzun';
$messages['folderupdated'] = 'Klasör güncellendi';
$messages['foldercreated'] = 'Klasör oluşturuldu';
$messages['invalidimageformat'] = 'Geçerli bir resim biçimi değil.';
$messages['mispellingsfound'] = 'Postada yazım hataları tespit edildi.';
$messages['parentnotwritable'] = 'Klasör, seçilen üst klasöre taşınamıyor. Erişim izni yok.';
$messages['messagetoobig'] = 'Bu ileti parçası işlenemeyecek kadar büyük.';
$messages['attachmentvalidationerror'] = 'UYARI! Bu eklenti mesajda belirtilenden farklı olduğu için şüpheli bulundu. Eğer göndericiye güvenmiyorsanız eki web tarayıcısında açmayın. Şüpheli içerik bulunabilir.';
$messages['noscriptwarning'] = 'Uyarı: Bu e-posta servisi Javascript gerektirmektedir! Servisi kullanabilmek için lütfen web tarayıcınızın ayarlarından Javascript\'i etkinleştiriniz.';

?>
