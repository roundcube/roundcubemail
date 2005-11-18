<?php

/*
 +-----------------------------------------------------------------------+
 | language/ja/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Tadashi Jokagi <elf2000@users.sourceforge.net>                |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'ユーザー名';
$labels['password']  = 'パスワード';
$labels['server']    = 'サーバー';
$labels['login']     = 'ログイン';

// taskbar
$labels['logout']   = 'ログアウト';
$labels['mail']     = '電子メール';
$labels['settings'] = '個人設定';
$labels['addressbook'] = 'アドレス帳';

// mailbox names
$labels['inbox']  = '受信箱';
$labels['sent']   = '送信箱';
$labels['trash']  = 'ごみ箱';
$labels['drafts'] = '草稿';
$labels['junk']   = 'ジャンク';

// message listing
$labels['subject'] = '件名';
$labels['from']    = '送信者';
$labels['to']      = '受信者';
$labels['cc']      = 'コピー';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = '返信先';
$labels['date']    = '日付';
$labels['size']    = '容量';
$labels['priority'] = '優先度';
$labels['organization'] = '所属';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Folders';
$labels['messagesfromto'] = '表示中のメッセージ: $from から $to(全メッセージ: $count)';
$labels['messagenrof'] = '全メッセージ: $count 現在のメッセージ:$nr';

$labels['moveto']   = '選択したフォルダーに移動する...';
$labels['download'] = 'ダウンロード';

$labels['filename'] = 'ファイル名';
$labels['filesize'] = 'ファイルサイズ';

$labels['preferhtml'] = 'Prefer HTML';
$labels['htmlmessage'] = 'HTML メッセージ';
$labels['prettydate'] = '簡潔な日付';

$labels['addtoaddressbook'] = 'アドレス帳に追加する';

// weekdays short
$labels['sun'] = '日';
$labels['mon'] = '月';
$labels['tue'] = '火';
$labels['wed'] = '水';
$labels['thu'] = '木';
$labels['fri'] = '金';
$labels['sat'] = '土';

// weekdays long
$labels['sunday']    = '日曜日';
$labels['monday']    = '月曜日';
$labels['tuesday']   = '火曜日';
$labels['wednesday'] = '水曜日';
$labels['thursday']  = '木曜日';
$labels['friday']    = '金曜日';
$labels['saturday']  = '土曜日';

$labels['today'] = '今日';

// toolbar buttons
$labels['writenewmessage']  = '新規メッセージを作成する';
$labels['replytomessage']   = 'メッセージを返信する';
$labels['forwardmessage']   = 'メッセージを転送する';
$labels['deletemessage']    = 'ごみ箱にメッセージを移動する';
$labels['printmessage']     = 'このメッセージを印刷する';
$labels['previousmessages'] = '前のメッセージセットを表示する';
$labels['nextmessages']     = '次のメッセージセットを表示する';
$labels['backtolist']       = 'メッセージ一覧に戻る';
$labels['viewsource']       = 'ソースを表示する';

$labels['select'] = '選択';
$labels['all'] = 'すべて';
$labels['none'] = 'なし';
$labels['unread'] = '未読';

// message compose
$labels['compose']  = 'メッセージを作成する';
$labels['sendmessage']  = 'メッセージを今すぐ送信する';
$labels['addattachment']  = 'ファイルを添付する';

$labels['attachments'] = '添付ファイル';
$labels['upload'] = 'アップロード';
$labels['close']  = '閉じる';

$labels['low']     = '低い';
$labels['lowest']  = '最低';
$labels['normal']  = '通常';
$labels['high']    = '高い';
$labels['highest'] = '最高';

$labels['showimages'] = '画像を表示する';


// address boook
$labels['name']      = '表示名';
$labels['firstname'] = '名前';
$labels['surname']   = '苗字';
$labels['email']     = '電子メールアドレス';

$labels['addcontact'] = '新規連絡簿を追加する';
$labels['editcontact'] = '連絡簿を編集する';

$labels['edit']   = '編集する';
$labels['cancel'] = '取り消し';
$labels['save']   = '保存する';
$labels['delete'] = '削除する';

$labels['newcontact']     = '新規連絡カードを作成する';
$labels['deletecontact']  = '選択した連絡簿を削除する';
$labels['composeto']      = '次にメールを作成する:';
$labels['contactsfromto'] = '連絡簿 $from から $to (合計: $count)';
$labels['print']          = '印刷する';
$labels['export']         = 'エクスポートする';


// settings
$labels['settingsfor']  = '次の設定:';

$labels['preferences']  = 'プリファレンス';
$labels['userpreferences']  = 'ユーザープリファレンス';
$labels['editpreferences']  = 'ユーザープリファレンスを編集する';

$labels['identities']  = '個人情報';
$labels['manageidentities']  = 'このアカウントの個人情報管理';
$labels['newidentity']  = '新規個人情報';

$labels['newitem']  = '新規項目';
$labels['edititem']  = '項目を編集する';

$labels['setdefault']  = 'デフォルトの設定';
$labels['language']  = '言語';
$labels['timezone']  = 'タイムゾーン';
$labels['pagesize']  = 'ページ毎の行数';


$labels['folders']  = 'フォルダー一覧';
$labels['foldername']  = 'フォルダー名';
$labels['subscribed']  = '購読済';
$labels['create']  = '作成';
$labels['createfolder']  = '新規フォルダーを作成する';
$labels['deletefolder']  = 'フォルダーを削除する';
$labels['managefolders']  = 'フォルダー管理';

$labels['sortby'] = 'ソート基準';
$labels['sortasc']  = '昇順ソート';
$labels['sortdesc'] = '降順ソート';

?>