<?php

/*
 +-----------------------------------------------------------------------+
 | language/th_TH/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Translate by: Nantharat Puwarang <sansez@gmail.com>                   |
 +-----------------------------------------------------------------------+

 $Id: labels.inc 296 2006-12-15 $

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
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'ปรับแต่งค่าส่วนตัว';
$labels['addressbook'] = 'สมุดรายชื่อ';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Drafts';
$labels['sent']   = 'Sent';
$labels['trash']  = 'Trash';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Subject';
$labels['from']    = 'Sender';
$labels['to']      = 'Recipient';
$labels['cc']      = 'Copy';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'วันที่';
$labels['size']    = 'ขนาด';
$labels['priority'] = 'ระดับความสำคัญ';
$labels['organization'] = 'องค์กร';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'กล่องจดหมาย';
$labels['messagesfromto'] = 'จดหมายจาก $from ถึง $to จำนวน $count ฉบับ';
$labels['messagenrof'] = 'จดหมายฉบับที่ $nr จาก $count ฉบับ';

$labels['moveto']   = 'ย้ายไป...';
$labels['download'] = 'download';

$labels['filename'] = 'File name';
$labels['filesize'] = 'File size';

$labels['preferhtml'] = 'Prefer HTML';
$labels['htmlmessage'] = 'HTML Message';
$labels['prettydate'] = 'Pretty dates';

$labels['addtoaddressbook'] = 'บันทึกในสมุดรายชื่อ';

// weekdays short
$labels['sun'] = 'Sun';
$labels['mon'] = 'Mon';
$labels['tue'] = 'Tue';
$labels['wed'] = 'Wed';
$labels['thu'] = 'Thu';
$labels['fri'] = 'Fri';
$labels['sat'] = 'Sat';

// weekdays long
$labels['sunday']    = 'วันอาทิตย์';
$labels['monday']    = 'วันจันทร์';
$labels['tuesday']   = 'วันอังคาร';
$labels['wednesday'] = 'วันพุธ';
$labels['thursday']  = 'วันพฤหัสบดี';
$labels['friday']    = 'วันศุกร์';
$labels['saturday']  = 'วันเสาร์';

$labels['today'] = 'วันนี้';

// toolbar buttons
$labels['checkmail']        = 'ตรวจสอบจดหมายใหม่';
$labels['writenewmessage']  = 'สร้างจดหมายใหม่';
$labels['replytomessage']   = 'ตอบกลับจดหมาย';
$labels['replytoallmessage'] = 'ตอบกลับถึงทุกคนในจดหมาย';
$labels['forwardmessage']   = 'ส่งต่อจดหมาย';
$labels['deletemessage']    = 'ลบจดหมาย';
$labels['printmessage']     = 'พิมพ์จดหมาย';
$labels['previousmessages'] = 'จดหมายฉบับก่อนหน้า';
$labels['nextmessages']     = 'จดหมายฉบับถัดไป';
$labels['backtolist']       = 'กลับไปยังรายการจดหมาย';
$labels['viewsource']       = 'Show source';

$labels['select'] = 'เลือก';
$labels['all'] = 'ทั้งหมด';
$labels['none'] = 'ไม่เลือก';
$labels['unread'] = 'จดหมายที่ไม่ได้อ่าน';

$labels['compact'] = 'Compact';
$labels['empty'] = 'Empty';
$labels['purge'] = 'Purge';

$labels['quota'] = 'พื้นที่ใช้งาน';
$labels['unknown']  = 'ไม่ทราบ';
$labels['unlimited']  = 'ไม่จำกัด';

$labels['quicksearch']  = 'Quick search';
$labels['resetsearch']  = 'Reset search';


// message compose
$labels['compose']  = 'เขียนจดหมายใหม่';
$labels['savemessage']  = 'บันทึกฉบับร่าง';
$labels['sendmessage']  = 'ส่งจดหมาย';
$labels['addattachment']  = 'แนบไฟล์';
$labels['charset']  = 'Charset';
$labels['returnreceipt'] = 'Return receipt';

$labels['checkspelling'] = 'ตรวจสอบคำผิด';
$labels['resumeediting'] = 'แก้ไขต่อ';
$labels['revertto'] = 'Revert to';

$labels['attachments'] = 'ไฟล์แนบ';
$labels['upload'] = 'Upload';
$labels['close']  = 'Close';

$labels['low']     = 'ต่ำ';
$labels['lowest']  = 'ต่ำสุด';
$labels['normal']  = 'ปกติ';
$labels['high']    = 'สูง';
$labels['highest'] = 'สูงสุด';

$labels['nosubject']  = '(ไม่มีหัวข้อ)';
$labels['showimages'] = 'แสดงรูป';


// address boook
$labels['name']      = 'ชื่อแสดง';
$labels['firstname'] = 'ชื่อ';
$labels['surname']   = 'สกุล';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'เพิ่มรายชื่อ';
$labels['editcontact'] = 'แก้ไขรายชื่อ';

$labels['edit']   = 'แก้ไข';
$labels['cancel'] = 'ยกเลิก';
$labels['save']   = 'บันทึก';
$labels['delete'] = 'ลบ';

$labels['newcontact']     = 'สร้างรายชื่อใหม่';
$labels['deletecontact']  = 'ลบรายชื่อที่เลือก';
$labels['composeto']      = 'เขียนจดหมายถึง';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'พิมพ์';
$labels['export']         = 'ส่งออก';

$labels['previouspage']   = 'หน้าที่แล้ว';
$labels['nextpage']       = 'หน้าถัดไป';


// LDAP search
$labels['ldapsearch'] = 'LDAP directory search';

$labels['ldappublicsearchname']    = 'Contact name';
$labels['ldappublicsearchtype'] = 'Exact match?';
$labels['ldappublicserverselect'] = 'Select servers';
$labels['ldappublicsearchfield'] = 'Search on';
$labels['ldappublicsearchform'] = 'Look for a contact';
$labels['ldappublicsearch'] = 'ค้นหา';


// settings
$labels['settingsfor']  = 'Settings for';

$labels['preferences']  = 'ปรับแต่ง';
$labels['userpreferences']  = 'ข้อมูลส่วนตัว';
$labels['editpreferences']  = 'แก้ไขข้อมูลส่วนตัว';

$labels['identities']  = 'Identities';
$labels['manageidentities']  = 'Manage identities for this account';
$labels['newidentity']  = 'New identity';

$labels['newitem']  = 'New item';
$labels['edititem']  = 'Edit item';

$labels['setdefault']  = 'ตั้งเป็นค่าใช้งาน';
$labels['language']  = 'ภาษา';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'จำนวนจดหมายต่อหน้า';
$labels['signature'] = 'Signature';
$labels['dstactive']  = 'Daylight savings';

$labels['folder']  = 'กล่องจดหมาย';
$labels['folders']  = 'กล่องจดหมาย';
$labels['foldername']  = 'ชื่อกล่องจดหมาย';
$labels['subscribed']  = 'Subscribed';
$labels['create']  = 'สร้าง';
$labels['createfolder']  = 'สร้างกล่องจดหมาย';
$labels['rename'] = 'เปลื่ยนชื่อ';
$labels['renamefolder'] = 'เปลี่ยนชื่อกล่องจดหมาย';
$labels['deletefolder']  = 'ลบกล่องจดหมาย';
$labels['managefolders']  = 'จัดการกล่องจดหมาย';

$labels['sortby'] = 'เรียงตาม';
$labels['sortasc']  = 'เรียงจากหน้าไปหลัง';
$labels['sortdesc'] = 'เรียงจากหลังไปหน้า';

//New words since 0.1beta2
$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plain text';

$labels['htmleditor'] = 'Compose HTML messages';
$labels['htmlsignature'] = 'HTML signature';

$labels['autosavedraft'] = 'Automatically save draft';
$labels['everynminutes'] = 'every $n minutes';
$labels['never']  = 'never'; 

$labels['previewpane'] = 'Show preview pane';

$labels['previousmessage'] = 'แสดงจดหมายฉบับที่แล้ว';
$labels['firstmessage']    = 'แสดงจดหมายฉบับแรก';
$labels['nextmessage']     = 'แสดงจดหมายฉบับต่อไป';
$labels['lastmessage']     = 'แสดงจดหมายฉบับล่าสุด';

$messages['converting'] = 'Removing formatting from message...';
$messages['deletedsuccessfully'] = 'ลบจดหมายเรียบร้อย';
$messages['messageopenerror'] = 'ไม่สามารถติดต่อกับ Server ได้';

?>
