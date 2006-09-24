<?php

/*
 +-----------------------------------------------------------------------+
 | localization/tw/labels.inc                                            |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Ting-Hung Chiu <earrl@mmlab.cse.yzu.edu.tw>                   |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = '歡迎使用 $product';
$labels['username']  = '使用者名稱';
$labels['password']  = '使用者密碼';
$labels['server']    = '伺服器';
$labels['login']     = '登入';

// taskbar
$labels['logout']   = '登出';
$labels['mail']     = '電子郵件';
$labels['settings'] = '個人設定';
$labels['addressbook'] = '通訊錄';

// mailbox names
$labels['inbox']  = '信件匣';
$labels['drafts'] = '草稿匣';
$labels['sent']   = '寄件備份';
$labels['trash']  = '垃圾郵件';
$labels['junk']   = '垃圾桶';

// message listing
$labels['subject'] = '標題';
$labels['from']    = '寄件者';
$labels['to']      = '收件者';
$labels['cc']      = '副本';
$labels['bcc']     = '秘件副本';
$labels['replyto'] = '回覆至';
$labels['date']    = '日期';
$labels['size']    = '大小';
$labels['priority'] = '優先度';
$labels['organization'] = '組織';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = '資料匣';
$labels['messagesfromto'] = '郵件 $from 至 $to，共有 $count 郵件';
$labels['messagenrof'] = '第 $nr 封郵件，共有 $count 封';

$labels['moveto']   = '移至...';
$labels['download'] = '下載';

$labels['filename'] = '檔案名稱';
$labels['filesize'] = '檔案大小';

$labels['preferhtml'] = '使用 HTML 顯示';
$labels['htmlmessage'] = 'HTML 郵件';
$labels['prettydate'] = '簡短日期顯示';

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
$labels['checkmail']        = '檢查新郵件';
$labels['writenewmessage']  = '建立新郵件';
$labels['replytomessage']   = '回覆';
$labels['replytoallmessage'] = '回覆所有';
$labels['forwardmessage']   = '轉寄';
$labels['deletemessage']    = '移至垃圾郵件';
$labels['printmessage']     = '列印此郵件';
$labels['previousmessages'] = '上一頁';
$labels['nextmessages']     = '下一頁';
$labels['backtolist']       = '回到郵件清單';
$labels['viewsource']       = '顯示原始郵件';

$labels['select'] = '選擇';
$labels['all'] = '全部';
$labels['none'] = '無';
$labels['unread'] = '未讀';

$labels['compact'] = 'Compact';
$labels['empty'] = 'Empty';
$labels['purge'] = 'Purge';

$labels['quota'] = '磁碟使用量';
$labels['unknown']  = '未知';
$labels['unlimited']  = '沒有限制';

$labels['quicksearch']  = '快速搜尋';
$labels['resetsearch']  = '重置搜尋';


// message compose
$labels['compose']  = '撰寫新郵件';
$labels['savemessage']  = '存至草稿匣';
$labels['sendmessage']  = '立即寄出';
$labels['addattachment']  = '增加檔案至附件';
$labels['charset']  = '字元集';
$labels['returnreceipt'] = '郵件回條';

$labels['checkspelling'] = '拼字檢查';
$labels['resumeediting'] = '繼續編輯';
$labels['revertto'] = '恢復至';

$labels['attachments'] = '附件';
$labels['upload'] = '上傳';
$labels['close']  = '關閉';

$labels['low']     = '低';
$labels['lowest']  = '最低';
$labels['normal']  = '普通';
$labels['high']    = '高';
$labels['highest'] = '最高';

$labels['nosubject']  = '（沒有標題）';
$labels['showimages'] = '顯示圖片';


// address boook
$labels['name']      = '顯示名稱';
$labels['firstname'] = '名稱';
$labels['surname']   = '姓氏';
$labels['email']     = '電子郵件';

$labels['addcontact'] = '新增聯絡人';
$labels['editcontact'] = '編輯聯絡人';

$labels['edit']   = '編輯';
$labels['cancel'] = '取消';
$labels['save']   = '儲存';
$labels['delete'] = '刪除';

$labels['newcontact']     = '建立新聯絡人卡片';
$labels['deletecontact']  = '刪除所選擇之聯絡人';
$labels['composeto']      = '寄信至';
$labels['contactsfromto'] = '聯絡人 $from 至 $to，共有 $count 人';
$labels['print']          = '列印';
$labels['export']         = '匯出';

$labels['previouspage']   = '上一頁';
$labels['nextpage']       = '下一頁';


// LDAP search
$labels['ldapsearch'] = 'LDAP 目錄搜尋';

$labels['ldappublicsearchname']    = '聯絡名稱';
$labels['ldappublicsearchtype'] = '精確符合';
$labels['ldappublicserverselect'] = '選擇伺服器';
$labels['ldappublicsearchfield'] = '搜尋於此';
$labels['ldappublicsearchform'] = '尋找聯絡人';
$labels['ldappublicsearch'] = '搜尋';


// settings
$labels['settingsfor']  = '設定';

$labels['preferences']  = '設定';
$labels['userpreferences']  = '個人設定';
$labels['editpreferences']  = '編輯個人設定';

$labels['identities']  = '身份資訊';
$labels['manageidentities']  = '管理此帳號之身分資訊';
$labels['newidentity']  = '新增身份資訊';

$labels['newitem']  = '新的項目';
$labels['edititem']  = '編輯項目';

$labels['setdefault']  = '設成預設值';
$labels['language']  = '語言';
$labels['timezone']  = '時區';
$labels['pagesize']  = '頁郵件數';
$labels['signature'] = '簽名';
$labels['dstactive']  = '日光節約';

$labels['folder']  = '目錄';
$labels['folders']  = '目錄';
$labels['foldername']  = '目錄名稱';
$labels['subscribed']  = '標記';
$labels['create']  = '建立';
$labels['createfolder']  = '建立新目錄';
$labels['rename'] = '更改名稱';
$labels['renamefolder'] = '更改目錄名稱';
$labels['deletefolder']  = '刪除目錄';
$labels['managefolders']  = '管理目錄';

$labels['sortby'] = '排序';
$labels['sortasc']  = '遞增排序';
$labels['sortdesc'] = '遞減排序';

?>
