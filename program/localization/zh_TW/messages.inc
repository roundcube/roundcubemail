<?php

/*

+-----------------------------------------------------------------------+
| localization/zh_TW/messages.inc                                       |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: WayneSan <waynesan@zerozone.tw>                               |
|         kourge <kourge@gmail.com>                                     |
|         Ting-Hung Chiu <earrl@mmlab.cse.yzu.edu.tw>                   |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = '登入失敗';
$messages['cookiesdisabled'] = '你的瀏覽器不接受 cookies';
$messages['sessionerror'] = '你的工作階段已失效或過期';
$messages['imaperror'] = '連線到 IMAP 伺服器失敗';
$messages['servererror'] = '伺服器發生錯誤!';
$messages['invalidrequest'] = '無效的請求! 沒有資料被儲存';
$messages['nomessagesfound'] = '此郵件匣沒有任何郵件';
$messages['loggedout'] = '你已成功地登出並結束工作階段了。再見！';
$messages['mailboxempty'] = '郵件匣是空的';
$messages['loading'] = '載入中...';
$messages['uploading'] = '上傳檔案中...';
$messages['loadingdata'] = '載入資料中...';
$messages['checkingmail'] = '檢查新郵件...';
$messages['sendingmessage'] = '寄出郵件中...';
$messages['messagesent'] = '郵件寄出成功';
$messages['savingmessage'] = '儲存郵件中...';
$messages['messagesaved'] = '訊息已經存至';
$messages['successfullysaved'] = '儲存成功';
$messages['addedsuccessfully'] = '聯絡人已經成功地新增至通訊錄';
$messages['contactexists'] = '此聯絡人的電子郵件位址已存在';
$messages['blockedimages'] = '為了保護你的隱私，此郵件中的遠端圖片已經被阻擋。';
$messages['encryptedmessage'] = '此郵件經過加密，因此無法顯示。抱歉！';
$messages['nocontactsfound'] = '沒有找到任何聯絡人';
$messages['contactnotfound'] = '找不到要求的聯絡人';
$messages['sendingfailed'] = '郵件寄出失敗';
$messages['senttooquickly'] = '你寄出的郵件太過於頻繁，請稍候 $sec 秒後再試一次。';
$messages['errorsavingsent'] = '儲存寄件備份時發生錯誤';
$messages['errorsaving'] = '儲存時發生錯誤';
$messages['errormoving'] = '無法移動此郵件';
$messages['errordeleting'] = '無法刪除此郵件';
$messages['errormarking'] = '無法標記此郵件';
$messages['deletecontactconfirm'] = '你確定要刪除已選擇的聯絡人？';
$messages['deletemessagesconfirm'] = '你確定要刪除已選擇的郵件？';
$messages['deletefolderconfirm'] = '你確定要刪除這個資料夾嗎？';
$messages['purgefolderconfirm'] = '你確定要刪除這個資料夾裡面所有的郵件嗎？';
$messages['foldercreating'] = '建立資料夾中...';
$messages['folderdeleting'] = '刪除資料夾中...';
$messages['folderrenaming'] = '重新命名資料夾中...';
$messages['foldermoving'] = '移動資料夾中...';
$messages['formincomplete'] = '這個表單有部分未填，請完整填寫';
$messages['noemailwarning'] = '請輸入一個有效的電子郵件位址';
$messages['nonamewarning'] = '請輸入名字';
$messages['nopagesizewarning'] = '請輸入一頁顯示多少郵件';
$messages['nosenderwarning'] = '請輸入寄件者的電子郵件位址';
$messages['norecipientwarning'] = '請輸入至少一位收信者';
$messages['nosubjectwarning'] = '「主旨」欄是空的。你要輸入一個主旨嗎？';
$messages['nobodywarning'] = '要傳送沒有內文的郵件嗎？';
$messages['notsentwarning'] = '郵件尚未寄出。你確定要離開並且捨棄此郵件？';
$messages['noldapserver'] = '請選擇一個 LDAP 伺服器進行搜尋';
$messages['nocontactsreturned'] = '沒有找到任何聯絡人';
$messages['nosearchname'] = '請輸入一個聯絡人姓名或電子郵件位址';
$messages['notuploadedwarning'] = '附件尚未上傳完畢。請稍等或取消上傳。';
$messages['searchsuccessful'] = '共找到 $nr 封郵件';
$messages['searchnomatch'] = '找不到任何符合的郵件';
$messages['searching'] = '搜尋中...';
$messages['checking'] = '檢查中...';
$messages['nospellerrors'] = '沒有找到任何拼字錯誤';
$messages['folderdeleted'] = '資料夾已經成功地被刪除';
$messages['deletedsuccessfully'] = '刪除成功';
$messages['converting'] = '移除郵件格式中...';
$messages['messageopenerror'] = '無法從伺服器載入郵件';
$messages['fileuploaderror'] = '檔案上傳失敗';
$messages['filesizeerror'] = '上傳的檔案超過了 $size 的大小限制';
$messages['copysuccess'] = '複製 $nr 個位址成功';
$messages['copyerror'] = '無法複製任何位址';
$messages['sourceisreadonly'] = '此來源位址是唯讀的';
$messages['errorsavingcontact'] = '無法儲存連絡人的位址';
$messages['movingmessage'] = '移動郵件中...';
$messages['receiptsent'] = '閱讀回條傳送成功';
$messages['errorsendingreceipt'] = '無法傳送回條';
$messages['nodeletelastidentity'] = '你不能刪除此身份，因為只剩一個身份。';
$messages['addsubfolderhint'] = '此資料夾會成為目前所選資料夾的子資料夾';
$messages['forbiddencharacter'] = '資料夾名稱中包含非法的字元';
$messages['selectimportfile'] = '請選擇一個上傳的檔案';
$messages['addresswriterror'] = '無法寫入選擇的通訊錄';
$messages['importwait'] = '匯入中，請稍候...';
$messages['importerror'] = '匯入失敗！上傳的檔案並非支援的 vCard 格式。';
$messages['importconfirm'] = '<b>成功匯入 $inserted 筆資料，略過 $skipped 筆已存在的資料</b>：<p><em>$names</em></p>';
$messages['opnotpermitted'] = '不允許的操作';
$messages['nofromaddress'] = '在選擇的身分中遺失了電子郵件位址';
$messages['editorwarning'] = '切換到純文字編輯模式將會遺失所有設定的樣式。您確定要繼續嗎？';
$messages['smtpconnerror'] = 'SMTP 發生錯誤 ($code): 連結到伺服器失敗';
$messages['smtpautherror'] = 'SMTP 發生錯誤 ($code): 認證失敗';
$messages['smtpfromerror'] = 'SMTP 發生錯誤 ($code): 設定寄件者失敗 "$from"';
$messages['smtptoerror'] = 'SMTP 發生錯誤 ($code): 增加收件者失敗 "$from"';
$messages['smtprecipientserror'] = 'SMTP 發生錯誤 ($code): 無法分析收件者清單
';
$messages['smtperror'] = 'SMTP 發生錯誤: $msg';
$messages['emailformaterror'] = '不正確的e-mail位址: $email';

?>
