<?php

/*
 +-----------------------------------------------------------------------+
 | language/en/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$utf8_decoding = TRUE;

$labels = array();

// login page
$labels['username']  = '使用者名稱';
$labels['password']  = '密碼';
$labels['server']    = '伺服器';
$labels['login']     = '登入';

// taskbar
$labels['logout']   = '登出';
$labels['mail']     = '電子郵件';
$labels['settings'] = '個人設定';
$labels['addressbook'] = '通訊錄';

// mailbox names
$labels['inbox']  = '收件匣';
$labels['sent']   = '已寄郵件';
$labels['trash']  = '垃圾桶';
$labels['drafts'] = '草稿';
$labels['junk']   = '垃圾郵件';

// message listing
$labels['subject'] = '主旨';
$labels['from']    = '寄件者';
$labels['to']      = '收件者';
$labels['cc']      = '副本';
$labels['bcc']     = '密件副本';
$labels['replyto'] = '回信地址 (Reply-To)';
$labels['date']    = '日期';
$labels['size']    = '大小';
$labels['priority'] = '優先順序';
$labels['organization'] = '組織';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = '資料夾';
$labels['messagesfromto'] = '自 $from 至 $to 共 $count 的訊息';
$labels['messagenrof'] = '訊息 $nr 之 $count';

$labels['moveto']   = '移動至...';
$labels['download'] = '下載';

$labels['filename'] = '檔案名稱';
$labels['filesize'] = '檔案大小';

$labels['preferhtml'] = '偏好 HTML';
$labels['htmlmessage'] = 'HTML 訊息';
$labels['prettydate'] = '好看的日期';

$labels['addtoaddressbook'] = '新增至通訊錄';

// weekdays short
$labels['sun'] = '日';
$labels['mon'] = '一';
$labels['tue'] = '二';
$labels['wed'] = '三';
$labels['thu'] = '四';
$labels['fri'] = '五';
$labels['sat'] = '六';

// weekdays long
$labels['sunday']    = '星期日';
$labels['monday']    = '星期一';
$labels['tuesday']   = '星期二';
$labels['wednesday'] = '星期三';
$labels['thursday']  = '星期四';
$labels['friday']    = '星期五';
$labels['saturday']  = '星期六';

$labels['today'] = '今日';

// toolbar buttons
$labels['writenewmessage']  = '建立新的訊息';
$labels['replytomessage']   = '回覆訊息';
$labels['forwardmessage']   = '轉寄訊息';
$labels['deletemessage']    = '移動訊息至垃圾桶';
$labels['printmessage']     = '列印這個訊息';
$labels['previousmessages'] = '顯示前一組的訊息';
$labels['nextmessages']     = '顯示後一組的訊息';
$labels['backtolist']       = '回到訊息清單';

$labels['select'] = '選擇';
$labels['all'] = '全部';
$labels['none'] = '無';
$labels['unread'] = '未閱讀';

// message compose
$labels['compose']  = '撰寫訊息';
$labels['sendmessage']  = '馬上傳送訊息';
$labels['addattachment']  = '附加檔案';

$labels['attachments'] = '附件';
$labels['upload'] = '上傳';
$labels['close']  = '關閉';

$labels['low']     = '低';
$labels['lowest']  = '最低';
$labels['normal']  = '正常';
$labels['high']    = '高';
$labels['highest'] = '最高';

$labels['showimages'] = '顯示影像';


// address boook
$labels['name']      = '顯示名稱';
$labels['firstname'] = '名';
$labels['surname']   = '姓';
$labels['email']     = '電子郵件';

$labels['addcontact'] = '新增新的連絡人';
$labels['editcontact'] = '編輯連絡人';

$labels['edit']   = '編輯';
$labels['cancel'] = '取消';
$labels['save']   = '儲存';
$labels['delete'] = '刪除';

$labels['newcontact']     = '建立新的連絡人卡';
$labels['deletecontact']  = '刪除選取的連絡人';
$labels['composeto']      = '撰寫郵件';
$labels['contactsfromto'] = '自 $from 至 $to 共 $count 的連絡人';


// settings
$labels['settingsfor']  = '下列使用者的設定';

$labels['preferences']  = '偏好設定';
$labels['userpreferences']  = '使用者偏好設定';
$labels['editpreferences']  = '編輯使用者偏好設定';

$labels['identities']  = '身份';
$labels['manageidentities']  = '管理這個帳戶的身份';
$labels['newidentity']  = '新身份';

$labels['newitem']  = '新增項目';
$labels['edititem']  = '編輯項目';

$labels['setdefault']  = '設為預設';
$labels['language']  = '語言';
$labels['timezone']  = '時區';
$labels['pagesize']  = '每頁欄數';


$labels['folders']  = '資料夾';
$labels['foldername']  = '資料夾名稱';
$labels['subscribed']  = '已訂閱';
$labels['create']  = '建立';
$labels['createfolder']  = '建立新資料夾';
$labels['deletefolder']  = '刪除資料夾';
$labels['managefolders']  = '管理資料夾';


?>