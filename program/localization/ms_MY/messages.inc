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
$messages['errortitle']  = 'An error occurred!';
$messages['loginfailed']  = 'Log masuk gagal';
$messages['cookiesdisabled'] = 'Pelayar anda tidak menerima cookies';
$messages['sessionerror'] = 'Sesi anda tidak sah atau telah tamat';
$messages['storageerror'] = 'Sambungan ke pelayan IMAP gagal';
$messages['servererror'] = 'Server Error!';
$messages['servererrormsg'] = 'Server Error: $msg';
$messages['dberror'] = 'Database Error!';
$messages['requesttimedout'] = 'Request timed out';
$messages['errorreadonly'] = 'Unable to perform operation. Folder is read-only.';
$messages['errornoperm'] = 'Unable to perform operation. Permission denied.';
$messages['invalidrequest'] = 'Invalid request! No data was saved.';
$messages['invalidhost'] = 'Invalid server name.';
$messages['nomessagesfound'] = 'Tiada mesej dijumpai dalam kotak mel ini';
$messages['loggedout'] = 'Sesi anda telah tamat. Selamat tinggal!';
$messages['mailboxempty'] = 'Kotak mail kosong';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'Memuat...';
$messages['uploading'] = 'Uploading file...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'Memuat data...';
$messages['checkingmail'] = 'Menyemak mesej baru...';
$messages['sendingmessage'] = 'Menghantar mesej...';
$messages['messagesent'] = 'Mesej berjaya dihantar';
$messages['savingmessage'] = 'Menyimpan mesej...';
$messages['messagesaved'] = 'Mesej disimpan dalam Draf';
$messages['successfullysaved'] = 'Berjaya disimpan';
$messages['addedsuccessfully'] = 'Kontek berjaya disimpan ke dalam buku alamat';
$messages['contactexists'] = 'Kontek dengan alamat emel ini sudah wujud';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'Untuk melindungi kebesasan peribadi anda, imej jauh dihalang dalam mesej ini.';
$messages['encryptedmessage'] = 'Ini adalah mesej yang di\'encrypted\' dan tidak boleh dipamerkan. Maaf!';
$messages['nocontactsfound'] = 'Tiada kontek dijumpai';
$messages['contactnotfound'] = 'Kontek yang diminta tidak dijumpai';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'Gagal menghantar mesej';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'Terdapat ralat ketika menyimpan';
$messages['errormoving'] = 'Tidak boleh pindah mesej';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'Tidak boleh hapus mesej';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'Adakah anda ingin hapuskan kontek yang dipilih?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'Adakah anda ingin hapuskan mesej yang dipilih?';
$messages['deletefolderconfirm']  = 'Adakah anda ingin hapuskan kabinet fail ini?';
$messages['purgefolderconfirm']  = 'Adakah anda ingin hapuskan semua mesej dalam kabinet fail ini?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'Borang tidak diisi dengan lengkap';
$messages['noemailwarning'] = 'Sila masukkan alamat emel yang sah';
$messages['nonamewarning']  = 'Sila masukkan nama';
$messages['nopagesizewarning'] = 'Sila masukkan saiz halaman';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'Sila masukkan sekurang-kurangnya satu penerima';
$messages['nosubjectwarning']  = 'Ruang "Subjek" kosong. Adakah anda ingin masukkannya sekarang?';
$messages['nobodywarning'] = 'Hantar mesej ini tanpa teks?';
$messages['notsentwarning'] = 'Mesej tidak dihantar. Adakah anda ingin menyingkir mesej anda?';
$messages['noldapserver'] = 'Sila pilih satu pelayan ldap untuk carian';
$messages['nosearchname'] = 'Sila masukkan nama kontek atau alamat emel';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = '$nr mesej dijumpai';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'Carian tiada padanan';
$messages['searching'] = 'Menggelintar...';
$messages['checking'] = 'Menyemak...';
$messages['nospellerrors'] = 'Tiada kesalah ejaan dijumpai';
$messages['folderdeleted'] = 'Kabinet fail berjaya dihapuskan';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'Berjaya dihapuskan';
$messages['converting'] = 'Singkir format dari mesej...';
$messages['messageopenerror'] = 'Tidak boleh muat mesej dari server';
$messages['fileuploaderror'] = 'Muatnaik fail gagal';
$messages['filesizeerror'] = 'Fail yang dimuatnaik melampaui saiz maksima $size';
$messages['copysuccess'] = 'Berjaya salin alamat-alamat $nr';
$messages['copyerror'] = 'Tidak boleh salin apa-apa alamat';
$messages['sourceisreadonly'] = 'Sumber alamat ini adalah untuk bacaan sahaja';
$messages['errorsavingcontact'] = 'Tidak boleh simmpan alamat kontek';
$messages['movingmessage'] = 'Memindah mesej...';
$messages['copyingmessage'] = 'Copying message(s)...';
$messages['copyingcontact'] = 'Copying contact(s)...';
$messages['deletingmessage'] = 'Deleting message(s)...';
$messages['markingmessage'] = 'Marking message(s)...';
$messages['addingmember'] = 'Adding contact(s) to the group...';
$messages['removingmember'] = 'Removing contact(s) from the group...';
$messages['receiptsent'] = 'Berjaya menghantar resip bacaan';
$messages['errorsendingreceipt'] = 'Tidak dapat menghantar resip';
$messages['deleteidentityconfirm'] = 'Do you really want to delete this identity?';
$messages['nodeletelastidentity'] = 'Anda tidak boleh membuang identiti ini, ia milik anda yang terakhir.';
$messages['forbiddencharacter'] = 'Folder name contains a forbidden character.';
$messages['selectimportfile'] = 'Please select a file to upload.';
$messages['addresswriterror'] = 'The selected address book is not writeable.';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group.';
$messages['contactremovedfromgroup'] = 'Successfully removed contacts from this group.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'Importing, please wait...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>Successfully imported $inserted contacts</b>';
$messages['importconfirmskipped'] = '<b>Skipped $skipped existing entries</b>';
$messages['opnotpermitted'] = 'Operation not permitted!';
$messages['nofromaddress'] = 'Missing e-mail address in selected identity.';
$messages['editorwarning'] = 'Switching to the plain text editor will cause all text formatting to be lost. Do you wish to continue?';
$messages['httpreceivedencrypterror'] = 'A fatal configuration error occurred. Contact your administrator immediately. <b>Your message can not be sent.</b>';
$messages['smtpconnerror'] = 'SMTP Error ($code): Connection to server failed.';
$messages['smtpautherror'] = 'SMTP Error ($code): Authentication failed.';
$messages['smtpfromerror'] = 'SMTP Error ($code): Failed to set sender "$from" ($msg).';
$messages['smtptoerror'] = 'SMTP Error ($code): Failed to add recipient "$to" ($msg).';
$messages['smtprecipientserror'] = 'SMTP Error: Unable to parse recipients list.';
$messages['smtperror'] = 'SMTP Error: $msg';
$messages['emailformaterror'] = 'Invalid e-mail address: $email';
$messages['toomanyrecipients'] = 'Too many recipients. Reduce the number of recipients to $max.';
$messages['maxgroupmembersreached'] = 'The number of group members exceeds the maximum of $max.';
$messages['internalerror'] = 'An internal error occured. Please try again.';
$messages['contactdelerror'] = 'Could not delete contact(s).';
$messages['contactdeleted'] = 'Contact(s) deleted successfully.';
$messages['contactrestoreerror'] = 'Could not restore deleted contact(s).';
$messages['contactrestored'] = 'Contact(s) restored successfully.';
$messages['groupdeleted'] = 'Group deleted successfully.';
$messages['grouprenamed'] = 'Group renamed successfully.';
$messages['groupcreated'] = 'Group created successfully.';
$messages['savedsearchdeleted'] = 'Saved search deleted successfully.';
$messages['savedsearchdeleteerror'] = 'Could not delete saved search.';
$messages['savedsearchcreated'] = 'Saved search created successfully.';
$messages['savedsearchcreateerror'] = 'Could not create saved search.';
$messages['messagedeleted'] = 'Message(s) deleted successfully.';
$messages['messagemoved'] = 'Message(s) moved successfully.';
$messages['messagecopied'] = 'Message(s) copied successfully.';
$messages['messagemarked'] = 'Message(s) marked successfully.';
$messages['autocompletechars'] = 'Enter at least $min characters for autocompletion.';
$messages['autocompletemore'] = 'More matching entries found. Please type more characters.';
$messages['namecannotbeempty'] = 'Name cannot be empty.';
$messages['nametoolong'] = 'Name is too long.';
$messages['folderupdated'] = 'Folder updated successfully.';
$messages['foldercreated'] = 'Folder created successfully.';
$messages['invalidimageformat'] = 'Not a valid image format.';
$messages['mispellingsfound'] = 'Spelling errors detected in the message.';
$messages['parentnotwritable'] = 'Unable to create/move folder into selected parent folder. No access rights.';
$messages['messagetoobig'] = 'The message part is too big to process it.';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = 'Warning: This webmail service requires Javascript! In order to use it please enable Javascript in your browser\'s settings.';

?>
