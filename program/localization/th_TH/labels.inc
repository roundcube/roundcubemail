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
$labels['welcome']   = 'ยินดีต้อนรับสู่ $product';
$labels['username']  = 'บัญชีผู้ใช้';
$labels['password']  = 'รหัสผ่าน';
$labels['server']    = 'เซิร์ฟเวอร์';
$labels['login']     = 'เข้าสู่ระบบ';

// taskbar
$labels['logout']   = 'ออกจากระบบ';
$labels['mail']     = 'อีเมล์';
$labels['settings'] = 'ปรับแต่งค่าส่วนตัว';
$labels['addressbook'] = 'สมุดรายชื่อ';

// mailbox names
$labels['inbox']  = 'กล่องขาเข้า';
$labels['drafts'] = 'กล่องจดหมายร่าง';
$labels['sent']   = 'กล่องขาออก';
$labels['trash']  = 'ถังขยะ';
$labels['junk']   = 'กล่องจดหมายขยะ';

// message listing
$labels['subject'] = 'หัวจดหมาย';
$labels['from']    = 'ผู้ส่ง';
$labels['sender']  = 'ผู้ส่ง';
$labels['to']      = 'ผู้รับ';
$labels['cc']      = 'คัดลอก';
$labels['bcc']     = 'แอบสำเนาถึง';
$labels['replyto'] = 'ตอบกลับ';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'วันที่';
$labels['size']    = 'ขนาด';
$labels['priority'] = 'ระดับความสำคัญ';
$labels['organization'] = 'องค์กร';
$labels['readstatus'] = 'สถานะการเปิดอ่าน';
$labels['listoptions'] = 'ตัวเลือกการแสดงรายการ';

$labels['mailboxlist'] = 'กล่องจดหมาย';
$labels['messagesfromto'] = 'จดหมายจาก $from ถึง $to จำนวน $count ฉบับ';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'จดหมายฉบับที่ $nr จาก $count ฉบับ';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'คัดลอก';
$labels['move']     = 'เลื่อน';
$labels['moveto']   = 'ย้ายไป...';
$labels['download'] = 'ดาวน์โหลด';
$labels['open']     = 'Open';
$labels['showattachment'] = 'แสดง';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ชื่อไฟล์';
$labels['filesize'] = 'ขนาดไฟล์';

$labels['addtoaddressbook'] = 'บันทึกในสมุดรายชื่อ';

// weekdays short
$labels['sun'] = 'อ';
$labels['mon'] = 'จ';
$labels['tue'] = 'อ';
$labels['wed'] = 'พ';
$labels['thu'] = 'พฤ';
$labels['fri'] = 'ศ';
$labels['sat'] = 'ส';

// weekdays long
$labels['sunday']    = 'วันอาทิตย์';
$labels['monday']    = 'วันจันทร์';
$labels['tuesday']   = 'วันอังคาร';
$labels['wednesday'] = 'วันพุธ';
$labels['thursday']  = 'วันพฤหัสบดี';
$labels['friday']    = 'วันศุกร์';
$labels['saturday']  = 'วันเสาร์';

// months short
$labels['jan']	= 'ม.ค.';
$labels['feb']	= 'ก.พ.';
$labels['mar']	= 'มี.ค.';
$labels['apr']	= 'เม.ย.';
$labels['may']	= 'พ.ค.';
$labels['jun']	= 'มิ.ย.';
$labels['jul'] 	= 'ก.ค.';
$labels['aug']	= 'ส.ค.';
$labels['sep']	= 'ก.ย.';
$labels['oct']	= 'ต.ค.';
$labels['nov']	= 'พ.ย.';
$labels['dec']	= 'ธ.ค.';

// months long
$labels['longjan']	= 'มกราคม';
$labels['longfeb']	= 'กุมภาพันธ์';
$labels['longmar']	= 'มีนาคม';
$labels['longapr']	= 'เมษายน';
$labels['longmay']	= 'พ.ค.';
$labels['longjun']	= 'มิถุนายน';
$labels['longjul']	= 'กรกฎาคม';
$labels['longaug']	= 'สิงหาคม';
$labels['longsep']	= 'กันยายน';
$labels['longoct']	= 'ตุลาคม';
$labels['longnov']	= 'พฤศจิกายน';
$labels['longdec']	= 'ธันวาคม';

$labels['today'] = 'วันนี้';

// toolbar buttons
$labels['refresh']          = 'รีเฟรช';
$labels['checkmail']        = 'ตรวจสอบจดหมายใหม่';
$labels['compose']          = 'เขียนจดหมายใหม่';
$labels['writenewmessage']  = 'สร้างจดหมายใหม่';
$labels['reply']            = 'ตอบกลับ';
$labels['replytomessage']   = 'ตอบกลับจดหมาย';
$labels['replytoallmessage'] = 'ตอบกลับถึงทุกคนในจดหมาย';
$labels['replyall']         = 'ตอบกลับทั้งหมด';
$labels['replylist']        = 'รายชื่อตอบกลับ';
$labels['forward']          = 'ส่งต่อ';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'ส่งต่อเป็นไฟล์แนบ';
$labels['forwardmessage']   = 'ส่งต่อจดหมาย';
$labels['deletemessage']    = 'ลบจดหมาย';
$labels['movemessagetotrash'] = 'ย้ายข้อความไปไว้ในถังขยะ';
$labels['printmessage']     = 'พิมพ์จดหมาย';
$labels['previousmessage']  = 'แสดงจดหมายฉบับที่แล้ว';
$labels['firstmessage']     = 'แสดงจดหมายฉบับแรก';
$labels['nextmessage']      = 'แสดงจดหมายฉบับต่อไป';
$labels['lastmessage']      = 'แสดงจดหมายฉบับล่าสุด';
$labels['backtolist']       = 'กลับไปยังรายการจดหมาย';
$labels['viewsource']       = 'แสดง source';
$labels['mark']             = 'ทำเครื่องหมาย';
$labels['markmessages']     = 'ทำเครื่องหมายข้อความ';
$labels['markread']         = 'ว่าอ่านแล้ว';
$labels['markunread']       = 'ว่ายังไม่ได้อ่าน';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'การกระทำเพิ่มเติม...';
$labels['more']             = 'เพิ่มเติม';
$labels['back']             = 'ย้อนกลับ';
$labels['options']          = 'ตัวเลือก';

$labels['select'] = 'เลือก';
$labels['all'] = 'ทั้งหมด';
$labels['none'] = 'ไม่เลือก';
$labels['currpage'] = 'หน้าปัจจุบัน';
$labels['unread'] = 'จดหมายที่ไม่ได้อ่าน';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'ยังไม่ได้ตอบ';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'ลบแล้ว';
$labels['undeleted'] = 'ยังไม่ได้ลบ';
$labels['invert'] = 'Invert';
$labels['filter'] = 'ตัวกรองข้อมูล';
$labels['list'] = 'รายการ';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'แสดงทั้งหมด';
$labels['expand-unread'] = 'แสดงเฉพาะที่ยังไม่ได้อ่าน';
$labels['collapse-all'] = 'ปิดทั้งหมด';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'จาก/ถึง';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'ไฟล์แนบ';
$labels['nonesort'] = 'ไม่เลือก';
$labels['sentdate'] = 'วันที่ส่ง';
$labels['arrival'] = 'วันที่มาถึง';
$labels['asc'] = 'เรียงจากน้อยมามาก';
$labels['desc'] = 'เรีียงจากมากไปน้อย';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'จัดเรียงคอลัมน์';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'แบบย่อ';
$labels['empty'] = 'ว่างเปล่า';

$labels['quota'] = 'พื้นที่ใช้งาน';
$labels['unknown']  = 'ไม่ทราบ';
$labels['unlimited']  = 'ไม่จำกัด';

$labels['quicksearch']  = 'ค้นหาแบบด่วน';
$labels['resetsearch']  = 'ล้างการค้นหา';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'เนื้อหา';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'ส่งจดหมาย';
$labels['savemessage']    = 'บันทึกฉบับร่าง';
$labels['addattachment']  = 'แนบไฟล์';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Editor type';
$labels['returnreceipt']  = 'ตอบกลับผู้รับ';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'ตรวจสอบคำผิด';
$labels['resumeediting'] = 'แก้ไขต่อ';
$labels['revertto']      = 'ย้นกลับไปยัง';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'ไฟล์แนบ';
$labels['upload'] = 'อัพโหลด';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'ปิด';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'ต่ำ';
$labels['lowest']  = 'ต่ำสุด';
$labels['normal']  = 'ปกติ';
$labels['high']    = 'สูง';
$labels['highest'] = 'สูงสุด';

$labels['nosubject']  = '(ไม่มีหัวข้อ)';
$labels['showimages'] = 'แสดงรูป';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plain text';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Add Cc';
$labels['addbcc'] = 'Add Bcc';
$labels['addreplyto'] = 'Add Reply-To';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'Return Receipt (read)';
$labels['yourmessage'] = 'This is a Return Receipt for your message';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']         = 'ชื่อแสดง';
$labels['firstname']    = 'ชื่อ';
$labels['surname']      = 'สกุล';
$labels['middlename']   = 'ชื่อกลาง';
$labels['nameprefix']   = 'คำนำหน้า';
$labels['namesuffix']   = 'คำต่อท้ายชื่อ';
$labels['nickname']     = 'ชื่อเล่น';
$labels['jobtitle']     = 'ตำแหน่งงาน';
$labels['department']   = 'แผนก';
$labels['gender']       = 'เพศ';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'อีเมล์';
$labels['phone']        = 'โทรศัพท์';
$labels['address']      = 'ที่อยู่';
$labels['street']       = 'ถนน';
$labels['locality']     = 'เมือง';
$labels['zipcode']      = 'รหัสไปรษณีย์';
$labels['region']       = 'รัฐ/จังหวัด';
$labels['country']      = 'ประเทศ';
$labels['birthday']     = 'วันเกิด';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'เว็บไซต์';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'ชาย';
$labels['female'] = 'หญิง';
$labels['manager'] = 'ผู้จัดการ';
$labels['assistant'] = 'ผู้ช่วย';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'ช่องข้อมูลทั้่งหมด';
$labels['search'] = 'ค้นหา';
$labels['advsearch'] = 'ค้นหาขั้นสูง';
$labels['advanced'] = 'ขั้นสูง';
$labels['other'] = 'อื่นๆ';

$labels['typehome']   = 'บ้าน';
$labels['typework']   = 'สถานที่ทำงาน';
$labels['typeother']  = 'อื่นๆ';
$labels['typemobile']  = 'มือถือ';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'โทรสารที่บ้าน';
$labels['typeworkfax']  = 'โทรสารที่ทำงาน';
$labels['typecar']  = 'รถ';
$labels['typepager']  = 'เพจเจอร์';
$labels['typevideo']  = 'วีดีโอ';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'โฮมเพจ';
$labels['typeblog'] = 'บล็อก';
$labels['typeprofile'] = 'โปรไฟล์';

$labels['addfield'] = 'เพิ่มช่องข้อมูล...';
$labels['addcontact'] = 'เพิ่มรายชื่อ';
$labels['editcontact'] = 'แก้ไขรายชื่อ';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'แก้ไข';
$labels['cancel'] = 'ยกเลิก';
$labels['save']   = 'บันทึก';
$labels['delete'] = 'ลบ';
$labels['rename'] = 'เปลี่ยนชื่อ';
$labels['addphoto'] = 'เพิ่ม';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'สร้างรายชื่อใหม่';
$labels['deletecontact']  = 'ลบรายชื่อที่เลือก';
$labels['composeto']      = 'เขียนจดหมายถึง';
$labels['contactsfromto'] = 'รายชื่อ $from ถึง $to จำนวน $count';
$labels['print']          = 'พิมพ์';
$labels['export']         = 'ส่งออก';
$labels['exportall']      = 'ส่งออกทั้งหมด';
$labels['exportsel']      = 'ส่งออกเฉพาะที่เลือก';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'หน้าที่แล้ว';
$labels['firstpage']      = 'Show first page';
$labels['nextpage']       = 'หน้าถัดไป';
$labels['lastpage']       = 'Show last page';

$labels['group'] = 'Group';
$labels['groups'] = 'Groups';
$labels['personaladrbook'] = 'Personal Addresses';

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
$labels['settingsfor'] = 'ตั้งค่าสำหรับ';
$labels['about'] = 'About';
$labels['preferences'] = 'ปรับแต่ง';
$labels['userpreferences'] = 'ข้อมูลส่วนตัว';
$labels['editpreferences'] = 'แก้ไขข้อมูลส่วนตัว';

$labels['identities'] = 'ชื่อแสดงตัว';
$labels['manageidentities'] = 'จัดการชื่อแสดงตัวสำหรับรายชื่อนี้';
$labels['newidentity'] = 'ชื่อแสดงตัวใหม่';

$labels['newitem'] = 'New item';
$labels['edititem'] = 'Edit item';

$labels['preferhtml'] = 'อ่านแบบ HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'จดหมาย HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'แสดงวันที่แบบย่อ';
$labels['setdefault']  = 'ตั้งเป็นค่าใช้งาน';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'ภาษา';
$labels['timezone']  = 'เขตเวลา';
$labels['pagesize']  = 'จำนวนจดหมายต่อหน้า';
$labels['signature'] = 'ลายมือชื่อ';
$labels['dstactive']  = 'Daylight savings';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'เขียนจดหมายแบบ HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'ลายมือชื่อแบบ HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'แสดงหน้าต่างตัวอย่างแสดงผล';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'User Interface';
$labels['serversettings'] = 'Server Settings';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'ask me';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ignore';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'บันทึกเป็นจดหมายร่างอัตโนมัติ';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'ไม่ใช้';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'ตัวเลือกขั้นสูง';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'ข้อความใหม่';
$labels['signatureoptions'] = 'ตัวเลือกสำหรับลายเซ็็นต์';
$labels['whenreplying'] = 'เมื่อตอบกลับ';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'เพิ่มลายเซ็นต์เข้าไปอัตโนมัติ';
$labels['newmessageonly'] = 'เฉพาะข้อความใหม่เท่านั้น';
$labels['replyandforwardonly'] = 'ตอบกลับและส่งต่อเท่านั้น';
$labels['insertsignature'] = 'แทรกลายเซ็นต์';
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
$labels['asattachment'] = 'เป็นไฟล์แนบ';

$labels['folder']  = 'กล่องจดหมาย';
$labels['folders']  = 'กล่องจดหมาย';
$labels['foldername']  = 'ชื่อกล่องจดหมาย';
$labels['subscribed']  = 'ลงชื่อ';
$labels['messagecount'] = 'ข้อความ';
$labels['create']  = 'สร้าง';
$labels['createfolder']  = 'สร้างกล่องจดหมาย';
$labels['managefolders']  = 'จัดการกล่องจดหมาย';
$labels['specialfolders'] = 'โฟลเดอร์พิเศษ';
$labels['properties'] = 'คุณสมบัติ';
$labels['folderproperties'] = 'คุณสมบัติเกี่ยวกับโฟลเดอร์';
$labels['parentfolder'] = 'โฟลเดอร์แม่';
$labels['location'] = 'Location';
$labels['info'] = 'ข้อมูล';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'ประเภทโฟลเดอร์';
$labels['personalfolder']  = 'โฟลเดอร์ส่วนตัว';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'โฟลเดอร์สาธารณะ';

$labels['sortby'] = 'เรียงตาม';
$labels['sortasc']  = 'เรียงจากหน้าไปหลัง';
$labels['sortdesc'] = 'เรียงจากหลังไปหน้า';
$labels['undo'] = 'เลิกทำ';

$labels['installedplugins'] = 'ปลั๊กอินที่ติดตั้ง';
$labels['plugin'] = 'ปลั๊กอิน';
$labels['version'] = 'รุ่น';
$labels['source'] = 'Source';
$labels['license'] = 'สัญญาอนุญาต';
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
