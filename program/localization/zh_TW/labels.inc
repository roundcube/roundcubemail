<?php

/*

+-----------------------------------------------------------------------+
| localization/zh_TW/labels.inc                                         |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: WayneSan <waynesan@zerozone.tw>                               |
|         kourge <kourge@gmail.com>                                     |
|         Ting-Hung Chiu <earrl@mmlab.cse.yzu.edu.tw>                   |
+-----------------------------------------------------------------------+

@version $Id$

*/

$labels = array();

// login page
$labels['welcome'] = '歡迎使用 $product';
$labels['username'] = '使用者名稱';
$labels['password'] = '使用者密碼';
$labels['server'] = '伺服器';
$labels['login'] = '登入';

// taskbar
$labels['logout'] = '登出';
$labels['mail'] = '電子郵件';
$labels['settings'] = '個人設定';
$labels['addressbook'] = '通訊錄';

// mailbox names
$labels['inbox'] = '收件匣';
$labels['drafts'] = '草稿匣';
$labels['sent'] = '寄件備份';
$labels['trash'] = '垃圾桶';
$labels['junk'] = '垃圾郵件';

// message listing
$labels['subject'] = '主旨';
$labels['from'] = '寄件者';
$labels['to'] = '收件者';
$labels['cc'] = '副本';
$labels['bcc'] = '密件副本';
$labels['replyto'] = '回覆至';
$labels['date'] = '日期';
$labels['size'] = '大小';
$labels['priority'] = '優先順序';
$labels['organization'] = '組織';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = '資料夾';
$labels['messagesfromto'] = '郵件 $from 至 $to，共有 $count 封郵件';
$labels['messagenrof'] = '第 $nr 封郵件，共有 $count 封';

$labels['moveto'] = '移至...';
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
$labels['sunday'] = '星期日';
$labels['monday'] = '星期一';
$labels['tuesday'] = '星期二';
$labels['wednesday'] = '星期三';
$labels['thursday'] = '星期四';
$labels['friday'] = '星期五';
$labels['saturday'] = '星期六';

// months short
$labels['jan'] = '一';
$labels['feb'] = '二';
$labels['mar'] = '三';
$labels['apr'] = '四';
$labels['may'] = '五';
$labels['jun'] = '六';
$labels['jul'] = '七';
$labels['aug'] = '八';
$labels['sep'] = '九';
$labels['oct'] = '十';
$labels['nov'] = '十一';
$labels['dec'] = '十二';

// months long
$labels['longjan'] = '一月';
$labels['longfeb'] = '二月';
$labels['longmar'] = '三月';
$labels['longapr'] = '四月';
$labels['longmay'] = '五月';
$labels['longjun'] = '六月';
$labels['longjul'] = '七月';
$labels['longaug'] = '八月';
$labels['longsep'] = '九月';
$labels['longoct'] = '十月';
$labels['longnov'] = '十一月';
$labels['longdec'] = '十二月';

$labels['today'] = '今日';

// toolbar buttons
$labels['checkmail'] = '檢查新郵件';
$labels['writenewmessage'] = '撰寫新郵件';
$labels['replytomessage'] = '回覆';
$labels['replytoallmessage'] = '全部回覆';
$labels['forwardmessage'] = '轉寄';
$labels['deletemessage'] = '刪除郵件';
$labels['movemessagetotrash'] = '移動郵件至' . $labels['trash'];
$labels['printmessage'] = '列印此郵件';
$labels['previousmessage'] = '顯示上一封郵件';
$labels['previousmessages'] = '顯示上一頁';
$labels['firstmessage'] = '顯示第一封郵件';
$labels['firstmessages'] = '顯示第一頁';
$labels['nextmessage'] = '顯示下一封郵件';
$labels['nextmessages'] = '顯示下一頁';
$labels['lastmessage'] = '顯示最後一封郵件';
$labels['lastmessages'] = '顯示最後一頁';
$labels['backtolist'] = '回到郵件清單';
$labels['viewsource'] = '顯示原始郵件';
$labels['markmessages'] = '標記選取的郵件為';
$labels['markread'] = '標示為已讀取';
$labels['markunread'] = '標示為未讀取';
$labels['markflagged'] = '標上旗標';
$labels['markunflagged'] = '移除旗標';

$labels['select'] = '選取';
$labels['all'] = '全部';
$labels['none'] = '無';
$labels['unread'] = '未讀取';

$labels['compact'] = '壓縮';
$labels['empty'] = '清空';
$labels['purge'] = '清除';

$labels['quota'] = '磁碟使用量';
$labels['unknown'] = '未知';
$labels['unlimited'] = '無限制';

$labels['quicksearch'] = '快速搜尋';
$labels['resetsearch'] = '重設搜尋';

$labels['openinextwin'] = '在新視窗開啟';

// message compose
$labels['compose'] = '撰寫新郵件';
$labels['savemessage'] = '儲存成草稿';
$labels['sendmessage'] = '立即寄出';
$labels['addattachment'] = '增加附件檔案';
$labels['charset'] = '郵件編碼';
$labels['editortype'] = '編輯器類型';
$labels['returnreceipt'] = '要求讀取回條';

$labels['checkspelling'] = '拼字檢查';
$labels['resumeediting'] = '繼續編輯';
$labels['revertto'] = '恢復至';

$labels['attachments'] = '附加檔案';
$labels['upload'] = '上傳';
$labels['close'] = '關閉';

$labels['low'] = '低';
$labels['lowest'] = '最低';
$labels['normal'] = '普通';
$labels['high'] = '高';
$labels['highest'] = '最高';

$labels['nosubject'] = '（沒有主旨）';
$labels['showimages'] = '顯示圖片';
$labels['alwaysshow'] = '永遠顯示來自 $sender 的圖片';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = '純文字';
$labels['savesentmessagein'] = '儲存寄件備份至';
$labels['dontsave'] = '不要儲存';
$labels['maxuploadsize'] = '上傳檔案大小限制為 $size';

$labels['addcc'] = '新增副本';
$labels['addbcc'] = '新增密件副本';
$labels['addreplyto'] = '新增回覆地址';

// mdn
$labels['mdnrequest'] = '此郵件的寄件者希望在你閱讀此郵件時受到通知。你想要通知寄件者嗎？';
$labels['receiptread'] = '郵件回條（已閱讀）';
$labels['yourmessage'] = '這是你郵件的郵件回條';
$labels['receiptnote'] = '注意：此回條只能確認收件者的電腦顯示了郵件，並不能保證收件者已閱讀或了解郵件的內容。';

// address book
$labels['name'] = '顯示名稱';
$labels['firstname'] = '名稱';
$labels['surname'] = '姓氏';
$labels['email'] = '電子郵件';

$labels['addcontact'] = '新增聯絡人';
$labels['editcontact'] = '編輯聯絡人';

$labels['edit'] = '編輯';
$labels['cancel'] = '取消';
$labels['save'] = '儲存';
$labels['delete'] = '刪除';

$labels['newcontact'] = '建立新聯絡人資料';
$labels['deletecontact'] = '刪除所選擇的聯絡人';
$labels['composeto'] = '寄信至所選擇的聯絡人';
$labels['contactsfromto'] = '聯絡人 $from 至 $to，共有 $count 人';
$labels['print'] = '列印';
$labels['export'] = '匯出通訊錄';
$labels['exportvcards'] = '匯出通訊錄為 vCard 格式';

$labels['previouspage'] = '顯示上一頁';
$labels['firstpage'] = '顯示第一頁';
$labels['nextpage'] = '顯示下一頁';
$labels['lastpage'] = '顯示最後一頁';

$labels['groups'] = '群組';
$labels['personaladrbook'] = '個人住址';

$labels['import'] = '匯入通訊錄';
$labels['importcontacts'] = '由檔案匯入通訊錄';
$labels['importfromfile'] = '選擇你要匯入的檔案:';
$labels['importreplace'] = '以匯入的資料取代已存在的重複資料';
$labels['importtext'] = '你可以由已存在的通訊錄上傳資料，<br/>目前系統僅支援匯入 <a href="http://zh.wikipedia.org/wiki/VCard" target="_blank">vCard</a> 格式的資料。';
$labels['done'] = '完成';

// settings
$labels['settingsfor'] = '設定';

$labels['preferences'] = '設定';
$labels['userpreferences'] = '個人設定';
$labels['editpreferences'] = '編輯個人設定';

$labels['identities'] = '身份資訊';
$labels['manageidentities'] = '管理此帳號的身份資訊';
$labels['newidentity'] = '新增身份資訊';

$labels['newitem'] = '新增項目';
$labels['edititem'] = '編輯項目';

$labels['setdefault'] = '設成預設i值';
$labels['autodetect'] = '自動選擇';
$labels['language'] = '語言';
$labels['timezone'] = '時區';
$labels['pagesize'] = '每頁郵件數';
$labels['signature'] = '簽名檔';
$labels['dstactive'] = '日光節約時間';
$labels['htmleditor'] = '使用 HTML 撰寫郵件';
$labels['htmlsignature'] = 'HTML 簽名檔';
$labels['previewpane'] = '顯示預覽窗格';
$labels['skin'] = '版面風格';
$labels['logoutclear'] = '登出時清空' . $labels['trash'];
$labels['logoutcompact'] = '登出時壓縮' . $labels['inbox'];
$labels['uisettings'] = '使用介面設定';
$labels['serversettings'] = '伺服器設定';
$labels['mailboxview'] = '信箱顯示設定';
$labels['mdnrequests'] = '讀取回條回應';
$labels['askuser'] = '詢問使用者';
$labels['autosend'] = '自動送出';
$labels['ignore'] = '完全忽略';
$labels['readwhendeleted'] = '將刪除的信件標示為已讀取';
$labels['flagfordeletion'] = '刪除信件時將原始信件標示為已刪除';
$labels['showinlineimages'] = '將附加檔案的圖片顯示於郵件最後';
$labels['autosavedraft'] = '自動儲存草稿';
$labels['everynminutes'] = '每 $n 分鐘';
$labels['never'] = '絕不';
$labels['messagesdisplaying'] = '郵件顯示設定';
$labels['messagescomposition'] = '撰寫郵件設定';

$labels['folder'] = '資料夾';
$labels['folders'] = '資料夾';
$labels['foldername'] = '資料夾名稱';
$labels['subscribed'] = '顯示';
$labels['messagecount'] = '郵件數目';
$labels['create'] = '建立';
$labels['createfolder'] = '建立新資料夾';
$labels['rename'] = '重新命名';
$labels['renamefolder'] = '重新命名資料夾';
$labels['deletefolder'] = '刪除資料夾';
$labels['managefolders'] = '管理資料夾';
$labels['specialfolders'] = '預設資料夾設定';

$labels['sortby'] = '排序';
$labels['sortasc'] = '遞增排序';
$labels['sortdesc'] = '遞減排序';

?>