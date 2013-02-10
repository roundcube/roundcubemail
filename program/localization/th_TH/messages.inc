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
$messages['errortitle']  = 'เกิดข้อผิดพลาดบางประการ!';
$messages['loginfailed']  = 'ไม่สามารถเข้าสู่ระบบได้';
$messages['cookiesdisabled'] = 'Browser ของคุณไม่ได้เปิดการใช้งาน Cookie ไว้';
$messages['sessionerror'] = 'Your session is invalid or expired';
$messages['storageerror'] = 'ไม่สามารถติดต่อ IMAP Server ได้';
$messages['servererror'] = 'เกิดข้อผิดพลาดเกี่ยวกับเซิร์ฟเวอร์';
$messages['servererrormsg'] = 'Server Error: $msg';
$messages['dberror'] = 'เกิดข้อผิดพลาดเกี่ยวกับฐานข้อมูล';
$messages['requesttimedout'] = 'Request timed out';
$messages['errorreadonly'] = 'Unable to perform operation. Folder is read-only.';
$messages['errornoperm'] = 'Unable to perform operation. Permission denied.';
$messages['invalidrequest'] = 'Invalid request! No data was saved.';
$messages['invalidhost'] = 'ชื่อเซิร์ฟเวอร์ไม่ถูกต้อง';
$messages['nomessagesfound'] = 'ไม่มีจดหมายในกล่องจดหมายนี้';
$messages['loggedout'] = 'ทำการออกจากระบบเรียบร้อย';
$messages['mailboxempty'] = 'กล่องจดหมายนี้ว่างเปล่า';
$messages['refreshing'] = 'กำลังรีเฟรช...';
$messages['loading'] = 'Loading...';
$messages['uploading'] = 'กำลังอัพโหลดไฟล์...';
$messages['uploadingmany'] = 'กำลังอัพโหลดไฟล์...';
$messages['loadingdata'] = 'กำลังโหลดข้อมูล...';
$messages['checkingmail'] = 'กำลังเช็คจดหมายใหม่...';
$messages['sendingmessage'] = 'กำลังส่งจดหมาย...';
$messages['messagesent'] = 'จดหมายถูกส่งเรียบร้อยแล้ว';
$messages['savingmessage'] = 'กำลังบันทึกจดหมาย...';
$messages['messagesaved'] = 'จดหมายถูกบันทึกเป็นจดหมายร่าง';
$messages['successfullysaved'] = 'บันทึกเรียบร้อยแล้ว';
$messages['addedsuccessfully'] = 'รายชื่อถูกบันทึกเรียบร้อยแล้ว';
$messages['contactexists'] = 'อีเมล์นี้มีอยู่แล้ว';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'เพื่อปกป้องความส่วนตัว รูปจากภายนอกในจดหมายนี้จะถูกปิดบังไว้';
$messages['encryptedmessage'] = 'จดหมายถูกเข้ารหัสไม่สามารถแสดงผลได้';
$messages['nocontactsfound'] = 'ไม่พบรายชื่อที่ค้นหา';
$messages['contactnotfound'] = 'ไม่พบรายชื่อที่ค้นหา';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'การส่งจดหมายล้มเหลว';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'เกิดข้อผิดพลาดระหว่างการบันทึก';
$messages['errormoving'] = 'ไม่สามารถย้ายจดหมายได้';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'ไม่สามารถลบจดหมายได้';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'คุณมั่นใจที่ต้องการลบรายชื่อที่เลือกใช่หรือไม่?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'คุณมั่นใจที่ต้องการลบจดหมายที่เลือกใช่หรือไม่?';
$messages['deletefolderconfirm']  = 'คุณมั่นใจที่ต้องการลบแฟ้มนี้หรือไม่?';
$messages['purgefolderconfirm']  = 'คุณมั่นใจที่ต้องการลบจดหมายทั้งหมดในแฟ้มนี้ใช่หรือไม่?';
$messages['contactdeleting'] = 'กำลังลบรายชื่อผู้ติดต่อ...';
$messages['groupdeleting'] = 'กำลังลบกลุ่ม...';
$messages['folderdeleting'] = 'กำลังลบโฟลเดอร์...';
$messages['foldermoving'] = 'กำลังย้ายโฟลเดอร์...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'ฟอร์มยังไม่ครบถ้วนสมบูรณ์';
$messages['noemailwarning'] = 'โปรดใส่อีเมล์ให้ถูกต้อง';
$messages['nonamewarning']  = 'โปรดใส่ชื่อ';
$messages['nopagesizewarning'] = 'โปรดใส่จำนวนหน้า';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'โปรดใส่ผู้รับอย่างน้อย 1 ชื่อ';
$messages['nosubjectwarning']  = 'หัวจดหมายว่างเปล่า คุณต้องการใส่หัวจดหมายเดี๋ยวนี้หรือไม่?';
$messages['nobodywarning'] = 'คุณต้องการส่งจดหมายโดยไม่มีข้อความใช่หรือไม่?';
$messages['notsentwarning'] = 'จดหมายยังไม่ถูกส่ง คุณต้องการยกเลิกหรือไม่?';
$messages['noldapserver'] = 'โปรดเลือก LDAP Server เพื่อค้นหา';
$messages['nosearchname'] = 'โปรดใสชื่อหรืออีเมล์';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = 'พบจดหมายจำนวน $nr ฉบับ';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'การค้นหาไม่พบ';
$messages['searching'] = 'กำลังค้นหา...';
$messages['checking'] = 'กำลังตรวจสอบ...';
$messages['nospellerrors'] = 'ไม่พบคำที่สะกดผิด';
$messages['folderdeleted'] = 'ลบแฟ้มเสร็จสมบูรณ์';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'ลบเสร็จสมบูรณ์';
$messages['converting'] = 'นำเอาหารจัดรูปแบบในจดหมายออก...';
$messages['messageopenerror'] = 'ไม่สามารถอ่านจดหมายจาก Server ได้';
$messages['fileuploaderror'] = 'การอัพโหลดล้มเหลว';
$messages['filesizeerror'] = 'ไฟล์มีขนาดใหญ่เกิน $size';
$messages['copysuccess'] = 'คัดลอกจดหมายจำนวน $nr ฉบับเรียบร้อย';
$messages['copyerror'] = 'ไม่สามารถคัดลอกได้';
$messages['sourceisreadonly'] = 'จดหมายนี้เป็นแบบอ่านอย่างเดียว';
$messages['errorsavingcontact'] = 'ไม่สามารถบันทึกรายชื่อได้';
$messages['movingmessage'] = 'กำลังย้ายข้อความ...';
$messages['copyingmessage'] = 'กำลังคัดลอกข้อความ...';
$messages['copyingcontact'] = 'กำลังคัดลอกรายชื่อผู้ติดต่อ...';
$messages['deletingmessage'] = 'กำลังลบข้อความ...';
$messages['markingmessage'] = 'Marking message(s)...';
$messages['addingmember'] = 'Adding contact(s) to the group...';
$messages['removingmember'] = 'Removing contact(s) from the group...';
$messages['receiptsent'] = 'Successfully sent a read receipt.';
$messages['errorsendingreceipt'] = 'Could not send the receipt.';
$messages['deleteidentityconfirm'] = 'Do you really want to delete this identity?';
$messages['nodeletelastidentity'] = 'You cannot delete this identity, it\'s your last one.';
$messages['forbiddencharacter'] = 'Folder name contains a forbidden character.';
$messages['selectimportfile'] = 'Please select a file to upload.';
$messages['addresswriterror'] = 'The selected address book is not writeable.';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group.';
$messages['contactremovedfromgroup'] = 'Successfully removed contacts from this group.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'กำลังนำเข้าข้อมูล, โปรดรอสักครู่';
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
