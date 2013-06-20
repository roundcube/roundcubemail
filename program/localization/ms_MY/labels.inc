<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Selamat datang ke $product';
$labels['username']  = 'Nama Pengguna';
$labels['password']  = 'Katalaluan';
$labels['server']    = 'Pelayan';
$labels['login']     = 'Log Masuk';

// taskbar
$labels['logout']   = 'Log Keluar';
$labels['mail']     = 'Mel';
$labels['settings'] = 'Tetapan';
$labels['addressbook'] = 'Buku Alamat';

// mailbox names
$labels['inbox']  = 'Peti Masuk';
$labels['drafts'] = 'Draf';
$labels['sent']   = 'Hantar';
$labels['trash']  = 'Tong Sampah';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Subjek';
$labels['from']    = 'Daripada';
$labels['sender']  = 'Sender';
$labels['to']      = 'Kepada';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Balasan Kepada';
$labels['followupto'] = 'Susulan Ke';
$labels['date']    = 'Tarikh';
$labels['size']    = 'Saiz';
$labels['priority'] = 'Prioriti';
$labels['organization'] = 'Organisasi';
$labels['readstatus'] = 'Status baca';
$labels['listoptions'] = 'Senarai pilihan';

$labels['mailboxlist'] = 'Folder';
$labels['messagesfromto'] = 'Mesej $from ke $to dari $count';
$labels['threadsfromto'] = 'Urutan $from ke $to dari $count';
$labels['messagenrof'] = 'Mesej $nr dari $count';
$labels['fromtoshort'] = '$from - $to dari $count';

$labels['copy']     = 'Salin';
$labels['move']     = 'Alih';
$labels['moveto']   = 'Alih ke...';
$labels['download'] = 'Muat Turun';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Nama Fail';
$labels['filesize'] = 'Saiz Fail';

$labels['addtoaddressbook'] = 'Tambah ke buku alamat';

// weekdays short
$labels['sun'] = 'Aha';
$labels['mon'] = 'Isn';
$labels['tue'] = 'Sel';
$labels['wed'] = 'Rab';
$labels['thu'] = 'Kha';
$labels['fri'] = 'Jum';
$labels['sat'] = 'Sab';

// weekdays long
$labels['sunday']    = 'Ahad';
$labels['monday']    = 'Isnin';
$labels['tuesday']   = 'Selasa';
$labels['wednesday'] = 'Rabu';
$labels['thursday']  = 'Khamis';
$labels['friday']    = 'Jumaat';
$labels['saturday']  = 'Sabtu';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mac';
$labels['apr']	= 'Apr';
$labels['may']	= 'Mei';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Ogo';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Okt';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dis';

// months long
$labels['longjan']	= 'Januari';
$labels['longfeb']	= 'Februari';
$labels['longmar']	= 'March';
$labels['longapr']	= 'April';
$labels['longmay']	= 'Mei';
$labels['longjun']	= 'Jun';
$labels['longjul']	= 'Julai';
$labels['longaug']	= 'Ogos';
$labels['longsep']	= 'September';
$labels['longoct']	= 'Oktober';
$labels['longnov']	= 'November';
$labels['longdec']	= 'Disember';

$labels['today'] = 'Hari Ini';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Semak mesej baru';
$labels['compose']          = 'Karang mesej';
$labels['writenewmessage']  = 'Cipta mesej baru';
$labels['reply']            = 'Balas';
$labels['replytomessage']   = 'Balas mesej';
$labels['replytoallmessage'] = 'Balas kepada penghantar dan semua penerima';
$labels['replyall']         = 'Balas semua';
$labels['replylist']        = 'Senarai balas';
$labels['forward']          = 'Majukan';
$labels['forwardinline']    = 'Majukan dalam mesej';
$labels['forwardattachment'] = 'Majukan sebagai lampiran';
$labels['forwardmessage']   = 'Marakan mesej';
$labels['deletemessage']    = 'Pindah mesej ke sampah';
$labels['movemessagetotrash'] = 'Pindah mesej ke Tong Sampah';
$labels['printmessage']     = 'Cetak mesej ini';
$labels['previousmessage']  = 'Papar mesej sebelumnya';
$labels['firstmessage']     = 'Papar mesej pertama';
$labels['nextmessage']      = 'Papat mesej seterusnya';
$labels['lastmessage']      = 'Papat mesej terakhir';
$labels['backtolist']       = 'Kembali ke senarai mesej';
$labels['viewsource']       = 'Papar sumber';
$labels['mark']             = 'Tanda';
$labels['markmessages']     = 'Tanda mesej';
$labels['markread']         = 'Sebagai Dibaca';
$labels['markunread']       = 'Sebagai Tidak dibaca';
$labels['markflagged']      = 'Seperti  ditanda';
$labels['markunflagged']    = 'Seperti tidak ditanda';
$labels['moreactions']      = 'Tindakan lanjut..';
$labels['more']             = 'Seterusnya';
$labels['back']             = 'Kembali';
$labels['options']          = 'Opsyen';

$labels['select'] = 'Pilih';
$labels['all'] = 'Semua';
$labels['none'] = 'Tiada';
$labels['currpage'] = 'Muka terkini';
$labels['unread'] = 'Belum dibaca';
$labels['flagged'] = 'Ditanda';
$labels['unanswered'] = 'Belum dijawab';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Telah dipadam';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Songsangkan';
$labels['filter'] = 'Tapis';
$labels['list'] = 'Senarai';
$labels['threads'] = 'Urutan';
$labels['expand-all'] = 'Buka Semua';
$labels['expand-unread'] = 'Buka yang belum dibaca';
$labels['collapse-all'] = 'Tutup Semua';
$labels['threaded'] = 'Telah urutan';

$labels['autoexpand_threads'] = 'Buka mesej mengikut urutan';
$labels['do_expand'] = 'Kesemua urutan';
$labels['expand_only_unread'] = 'hanya dengan mesej yang belum dibaca';
$labels['fromto'] = 'Daripada/Kepada';
$labels['flag'] = 'Tanda';
$labels['attachment'] = 'Lampiran';
$labels['nonesort'] = 'Tiada';
$labels['sentdate'] = 'Tarikh dihantar';
$labels['arrival'] = 'Tarikh terima';
$labels['asc'] = 'menaik';
$labels['desc'] = 'menurun';
$labels['listcolumns'] = 'Senarai ruang';
$labels['listsorting'] = 'pengisihan ruang';
$labels['listorder'] = 'Urutan pengisihan';
$labels['listmode'] = 'Mod senarai paparan';

$labels['folderactions'] = 'Aksi folder';
$labels['compact'] = 'Kompak';
$labels['empty'] = 'Kosong';

$labels['quota'] = 'Penggunaan cakera';
$labels['unknown']  = 'tidak diketahui';
$labels['unlimited']  = 'Tidak terhad';

$labels['quicksearch']  = 'Carian pantas';
$labels['resetsearch']  = 'Set semula carian';
$labels['searchmod']  = 'Pengubah carian';
$labels['msgtext']  = 'Keseluruhan mesej';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Buka dalam tetingkap baru';
$labels['emlsave'] = 'Muat-turun (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Ubah seperti baharu';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Hantar mesej';
$labels['savemessage']    = 'Simapan sebagai draf';
$labels['addattachment']  = 'Lampir fail';
$labels['charset']        = 'Set aksara';
$labels['editortype']     = 'Jenis Editor';
$labels['returnreceipt']  = 'Penerimaan semula';
$labels['dsn']            = 'Status pemberitahuan penghantaran';
$labels['mailreplyintro'] = 'Pada $date, $sender menulis';
$labels['originalmessage'] = 'Mesej Asal';

$labels['editidents']    = 'Ubah identiti';
$labels['spellcheck']    = 'Eja';
$labels['checkspelling'] = 'Semak ejaan';
$labels['resumeediting'] = 'Sambung pengeditan';
$labels['revertto']      = 'Kembali kepada';

$labels['attach'] = 'Lampirkan';
$labels['attachments'] = 'Lampiran';
$labels['upload'] = 'Muat naik';
$labels['uploadprogress'] = '$percent ($current dari $total)';
$labels['close']  = 'Tutup';
$labels['messageoptions']  = 'Opsyen mesej';

$labels['low']     = 'Rendah';
$labels['lowest']  = 'Paling Rendah';
$labels['normal']  = 'Normal';
$labels['high']    = 'Tinggi';
$labels['highest'] = 'Paling Tinggi';

$labels['nosubject']  = '(tiada subjek)';
$labels['showimages'] = 'Papar imej';
$labels['alwaysshow'] = 'selalu paparkan imej dari $sender';
$labels['isdraft']    = 'Ini adalah draf mesej';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Teks kosong';
$labels['savesentmessagein'] = 'Simpan mesej yang telah dihantar dalam';
$labels['dontsave'] = 'Jangan simpan';
$labels['maxuploadsize'] = 'maksimum saiz fail yang dibenarkan ialah $size';

$labels['addcc'] = 'Tambah Cc';
$labels['addbcc'] = 'Tambah Bcc';
$labels['addreplyto'] = 'Tambah Reply-To';
$labels['addfollowupto'] = 'Tambah Followup-To';

// mdn
$labels['mdnrequest'] = 'Pengirim mesej ini mahu diberitahu apabila anda membaca mesej ini. Adakah anda ingin memberitahu pengirim?';
$labels['receiptread'] = 'Balas Resip (dibaca)';
$labels['yourmessage'] = 'Ini adalah Resip Balasan untuk mesej anda';
$labels['receiptnote'] = 'Perhatian: Resip ini hanya untuk memberitahu yang mesej ini telah dipaparkan di komputer penerima. Tidak ada jaminan yang penerima telah membaca atau memahami kandungan mesej.';

// address boook
$labels['name']         = 'Nama paparan';
$labels['firstname']    = 'Nama pertama';
$labels['surname']      = 'Nama akhir';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'E-Mel';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'State/Province';
$labels['country']      = 'Country';
$labels['birthday']     = 'Birthday';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'male';
$labels['female'] = 'female';
$labels['manager'] = 'Manager';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'Other';

$labels['typehome']   = 'Home';
$labels['typework']   = 'Work';
$labels['typeother']  = 'Other';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'Home Fax';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'Tambah kontek baru';
$labels['editcontact'] = 'Ubahsuai Kontek';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Sunting';
$labels['cancel'] = 'Batal';
$labels['save']   = 'Simpan';
$labels['delete'] = 'Padam';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Cipta kad kontek baru';
$labels['deletecontact']  = 'Hapus kontek yang dipilih';
$labels['composeto']      = 'Karang mel kepada';
$labels['contactsfromto'] = 'Kontek $from ke $to dari $count';
$labels['print']          = 'Cetak';
$labels['export']         = 'Eksport';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Papar set sebelumnya';
$labels['firstpage']      = 'Papar set pertama';
$labels['nextpage']       = 'Papar set seterusnya';
$labels['lastpage']       = 'Papar set akhir';

$labels['group'] = 'Group';
$labels['groups'] = 'Kumpulan';
$labels['personaladrbook'] = 'Alamat-alamat Peribadi';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'Import';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Done';

// settings
$labels['settingsfor'] = 'Tetapan bagi';
$labels['about'] = 'About';
$labels['preferences'] = 'Keutamaan';
$labels['userpreferences'] = 'Keutamaan pengguna';
$labels['editpreferences'] = 'Sunting keutamaan pengguna';

$labels['identities'] = 'Identiti';
$labels['manageidentities'] = 'Urus identiti bagi akaun ini';
$labels['newidentity'] = 'Identiti baru';

$labels['newitem'] = 'Butiran baru';
$labels['edititem'] = 'Ubahsuai butiran';

$labels['preferhtml'] = 'Papar HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Mesej HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Tarikh-tarikh cantik';
$labels['setdefault']  = 'Tetap asal';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Bahasa';
$labels['timezone']  = 'Zon Masa';
$labels['pagesize']  = 'Baris setiap muka';
$labels['signature'] = 'Tandatangan';
$labels['dstactive']  = 'Simpanan siang hari';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Karang mesej HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Tandatangan HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Pamer ruang previu';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Kosongkan Tong sampah ketika keluar';
$labels['logoutcompact'] = 'Padatkan Inbox ketika keluar';
$labels['uisettings'] = 'Antaramuka Pengguna';
$labels['serversettings'] = 'Konfigurasi Pelayan';
$labels['mailboxview'] = 'Pandangan kotak Mel';
$labels['mdnrequests'] = 'Pemberitahuan Pengirim';
$labels['askuser'] = 'Tanya pengguna';
$labels['autosend'] = 'Hantar automatik';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'Biarkan';
$labels['readwhendeleted'] = 'Tanda mesej sebagai dibaca ketika hapus';
$labels['flagfordeletion'] = 'Tanda mesej untuk pembuangan (tidak delete terus)';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'Simpan draf dengan automatik';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'tidak pernah';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Advanced options';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'New Message';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automatically add signature';
$labels['newmessageonly'] = 'new message only';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['insertsignature'] = 'Insert signature';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'after $n seconds';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Add to dictionary';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Kabinet Fail';
$labels['folders']  = 'Folder';
$labels['foldername']  = 'Nama Kabinet Fail';
$labels['subscribed']  = 'Dilanggan';
$labels['messagecount'] = 'Mesej-mesej';
$labels['create']  = 'Cipta';
$labels['createfolder']  = 'Cipta kabinet fail baru';
$labels['managefolders']  = 'Urus kabinet fail';
$labels['specialfolders'] = 'Special Folders';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'Susun dengan';
$labels['sortasc']  = 'Susun menaik';
$labels['sortdesc'] = 'Susun menurun';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'English';
$labels['westerneuropean'] = 'Western European';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'Baltic';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'Arabic';
$labels['greek'] = 'Greek';
$labels['hebrew'] = 'Hebrew';
$labels['turkish'] = 'Turkish';
$labels['nordic'] = 'Nordic';
$labels['thai'] = 'Thai';
$labels['celtic'] = 'Celtic';
$labels['vietnamese'] = 'Vietnamese';
$labels['japanese'] = 'Japanese';
$labels['korean'] = 'Korean';
$labels['chinese'] = 'Chinese';

?>
