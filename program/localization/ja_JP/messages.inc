<?php

/*

+-----------------------------------------------------------------------+
| language/ja_JP/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Tadashi Jokagi <elf2000@users.sourceforge.net>                |
|         Takashi Takamatsu <taka717@gmail.com>                         |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'ログインに失敗しました。';
$messages['cookiesdisabled'] = 'ブラウザで Cookie が無効に設定されています。';
$messages['sessionerror'] = 'セッションが正しくないか期限切れです。';
$messages['imaperror'] = 'IMAP サーバへの接続に失敗しました。';
$messages['servererror'] = 'サーバエラーが発生しました。';
$messages['invalidrequest'] = '不正なリクエストです。データは保存されませんでした。';
$messages['nomessagesfound'] = 'メールはありません。';
$messages['loggedout'] = 'ログアウトしました。';
$messages['mailboxempty'] = 'メールボックスは空です。';
$messages['loading'] = '読込中...';
$messages['uploading'] = 'アップロード中...';
$messages['loadingdata'] = 'データを読込中...';
$messages['checkingmail'] = 'メールを確認しています...';
$messages['sendingmessage'] = 'メールを送信しています...';
$messages['messagesent'] = 'メールを送信しました。';
$messages['savingmessage'] = 'メールを保存しています...';
$messages['messagesaved'] = 'メールを下書きに保存しました。';
$messages['successfullysaved'] = '保存しました。';
$messages['addedsuccessfully'] = 'アドレス帳への連絡先の追加しました。';
$messages['contactexists'] = 'このメールアドレスを含む連絡先が既に存在します。';
$messages['blockedimages'] = 'プライバシー保護のため、このメールに含まれるリモート画像をブロックしました。';
$messages['encryptedmessage'] = 'このメールは暗号化されているため表示できません。';
$messages['nocontactsfound'] = '連絡先がありません。';
$messages['contactnotfound'] = '要求された連絡先が見つかりません。';
$messages['sendingfailed'] = 'メール送信に失敗しました。';
$messages['senttooquickly'] = 'このメールを送信するまで $sec 秒 お待ちください。';
$messages['errorsavingsent'] = '送信メールの保存中にエラーが発生しました。';
$messages['errorsaving'] = '保存中にエラーが発生しました。';
$messages['errormoving'] = 'メールを移動できません。';
$messages['errorcopying'] = 'メールをコピーできません。';
$messages['errordeleting'] = 'メールを削除できません。';
$messages['errormarking'] = 'メールをマークできません。';
$messages['deletecontactconfirm'] = '選択した連絡先を本当に削除しますか？';
$messages['deletemessagesconfirm'] = '選択したメールを本当に削除しますか？';
$messages['deletefolderconfirm'] = 'このフォルダを本当に削除しますか？';
$messages['purgefolderconfirm'] = 'このフォルダの全てのメールを本当に削除しますか？';
$messages['foldercreating'] = 'フォルダを作成しています...';
$messages['folderdeleting'] = 'フォルダを削除しています...';
$messages['folderrenaming'] = 'フォルダ名を変更しています...';
$messages['foldermoving'] = 'フォルダを移動しています...';
$messages['formincomplete'] = 'フォームの項目が完全に入力されていません。';
$messages['noemailwarning'] = '有効なメールアドレスを入力して下さい。';
$messages['nonamewarning'] = '名前を入力して下さい。';
$messages['nopagesizewarning'] = 'ページのサイズを入力して下さい。';
$messages['nosenderwarning'] = '差出人のメールアドレスを入力してください。';
$messages['norecipientwarning'] = '宛先を最低 1 人は入力して下さい。';
$messages['nosubjectwarning'] = '件名が空です。今すぐ入力しますか？';
$messages['nobodywarning'] = '本文が無いメールを送信しますか？';
$messages['notsentwarning'] = 'メールは送信されませんでした。破棄しますか？';
$messages['noldapserver'] = '検索する LDAP サーバーを選択して下さい。';
$messages['nocontactsreturned'] = '連絡先がありません。';
$messages['nosearchname'] = '連絡先の名前かメールアドレスを入力して下さい。';
$messages['notuploadedwarning'] = 'アップロードが完了していません。アップロードが完了するまで待つか、キャンセルして下さい。';
$messages['searchsuccessful'] = ' $nr 件のメールが見つかりました。';
$messages['searchnomatch'] = '一致するメールがありません。';
$messages['searching'] = '検索中...';
$messages['checking'] = '確認中...';
$messages['nospellerrors'] = 'スペルミスは見つかりませんでした。';
$messages['folderdeleted'] = 'フォルダを削除しました。';
$messages['deletedsuccessfully'] = '削除しました。';
$messages['converting'] = 'メールから書式を削除しています...';
$messages['messageopenerror'] = 'サーバからメールを取得できません。';
$messages['fileuploaderror'] = 'ファイルのアップロードに失敗しました。';
$messages['filesizeerror'] = 'アップロードするファイルのサイズが上限 $size を超えました。';
$messages['copysuccess'] = 'アドレス $nr 個のコピーに成功しました。';
$messages['copyerror'] = 'アドレスをコピーできません。';
$messages['sourceisreadonly'] = 'このアドレス情報は読取専用です。';
$messages['errorsavingcontact'] = '連絡先のアドレスを保存できません。';
$messages['movingmessage'] = 'メールを移動しています...';
$messages['copyingmessage'] = 'メールをコピーしています...';
$messages['receiptsent'] = '開封確認を送信しました。';
$messages['errorsendingreceipt'] = '開封確認を送信できません。';
$messages['nodeletelastidentity'] = '削除できません。少なくとも1つの個人情報が必要です。';
$messages['addsubfolderhint'] = '現在選択しているフォルダのサブフォルダとして作成されます。';
$messages['forbiddencharacter'] = 'フォルダ名に利用できない文字が含まれています。';
$messages['selectimportfile'] = 'アップロードするファイルを選択してください。';
$messages['addresswriterror'] = '選択したアドレス帳に書き込めません。';
$messages['contactaddedtogroup'] = 'グループに連絡先を追加しました。';
$messages['contactremovedfromgroup'] = 'グループから連絡先を削除しました。';
$messages['importwait'] = 'インポート中です。しばらくお待ちください...';
$messages['importerror'] = 'インポートに失敗しました。アップロードしたファイルは不正な vCard ファイルです。';
$messages['importconfirm'] = '<b>$inserted 件の連絡先をインポートしました。<br />既登録の連絡先 $skipped 件をスキップしました</b>：<p><em>$names</em></p>';
$messages['opnotpermitted'] = '許可されていない操作です。';
$messages['nofromaddress'] = '選択中の個人情報にメールアドレスが含まれていません。';
$messages['editorwarning'] = 'テキストエディタに切り替えるとすべての書式が失われます。よろしいですか？';
$messages['httpreceivedencrypterror'] = '致命的な設定エラーが発生しました。<b>メッセージは送信されませんでした。</b>システム管理者に至急連絡してください。';
$messages['smtpconnerror'] = 'SMTP Error ($code): サーバーへの接続に失敗';
$messages['smtpautherror'] = 'SMTP Error ($code): 認証に失敗';
$messages['smtpfromerror'] = 'SMTP Error ($code): 差出人 "$from" を設定できません';
$messages['smtptoerror'] = 'SMTP Error ($code): 宛先 "$to" を追加できません';
$messages['smtprecipientserror'] = 'SMTP Error: 宛先のリストを解析できません';
$messages['smtperror'] = 'SMTP Error: $msg';
$messages['emailformaterror'] = '不正確なメールアドレスです: $email';
$messages['toomanyrecipients'] = '宛先が多すぎます。$max 件以内にしてください。';
$messages['maxgroupmembersreached'] = 'グループメンバーが多すぎます。$max 人以内にしてください。';

?>
