<?php

/*

 +-----------------------------------------------------------------------+
 | language/ja_JP/messages.inc                                           |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Tadashi Jokagi <elf2000@users.sourceforge.net>                |
 |         Takashi Takamatsu <taka717@gmail.com>                         |
 +-----------------------------------------------------------------------+

@version $Id$

//  EN-Revision: 4313

*/

$messages = array();
$messages['loginfailed'] = 'ログインに失敗しました。';
$messages['cookiesdisabled'] = 'ブラウザで Cookie が無効に設定されています。';
$messages['sessionerror'] = 'セッションが正しくないか期限切れです。';
$messages['imaperror'] = 'IMAP サーバーへの接続に失敗しました。';
$messages['servererror'] = 'サーバーでエラーが発生しました。';
$messages['servererrormsg'] = 'サーバー エラー: $msg';
$messages['invalidrequest'] = '不正なリクエストです。データは保存されませんでした。';
$messages['nomessagesfound'] = 'メールはありません。';
$messages['loggedout'] = 'ログアウトしました。';
$messages['mailboxempty'] = 'メール ボックスは空です。';
$messages['loading'] = '読み込んでいます...';
$messages['uploading'] = 'アップロードしています...';
$messages['loadingdata'] = 'データを読み込んでいます...';
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
$messages['sendingfailed'] = 'メールの送信に失敗しました。';
$messages['senttooquickly'] = 'このメールを送信するまで $sec 秒 お待ちください。';
$messages['errorsavingsent'] = '送信メールの保存中にエラーが発生しました。';
$messages['errorsaving'] = '保存中にエラーが発生しました。';
$messages['errormoving'] = 'メールを移動できません。';
$messages['errorcopying'] = 'メールをコピーできません。';
$messages['errordeleting'] = 'メールを削除できません。';
$messages['errormarking'] = 'メールのマークを設定できません。';
$messages['deletecontactconfirm'] = '選択した連絡先を本当に削除しますか?';
$messages['deletemessagesconfirm'] = '選択したメールを本当に削除しますか?';
$messages['deletefolderconfirm'] = 'このフォルダーを本当に削除しますか?';
$messages['purgefolderconfirm'] = 'このフォルダーの全メールを本当に削除しますか?';
$messages['folderdeleting'] = 'フォルダーを削除しています...';
$messages['foldermoving'] = 'フォルダーを移動しています...';
$messages['foldersubscribing'] = 'フォルダーを購読しています...';
$messages['folderunsubscribing'] = 'フォルダーの購読をやめています...';
$messages['formincomplete'] = 'すべてのフォームの項目を入力していません。';
$messages['noemailwarning'] = '有効なメールアドレスを入力してください。';
$messages['nonamewarning'] = '名前を入力してください。';
$messages['nopagesizewarning'] = 'ページのサイズを入力してください。';
$messages['nosenderwarning'] = '差出人のメールアドレスを入力してください。';
$messages['norecipientwarning'] = '宛先を最低 1 人は入力してください。';
$messages['nosubjectwarning'] = '件名が空です。今すぐ入力しますか?';
$messages['nobodywarning'] = '本文が無いメールを送信しますか?';
$messages['notsentwarning'] = 'メールは送信されませんでした。破棄しますか?';
$messages['noldapserver'] = '検索する LDAP サーバーを選択してください。';
$messages['nocontactsreturned'] = '連絡先がありません。';
$messages['nosearchname'] = '連絡先の名前かメールアドレスを入力してください。';
$messages['notuploadedwarning'] = 'アップロードが完了していません。アップロードが完了するまで待つか、キャンセルしてください。';
$messages['searchsuccessful'] = '$nr 件のメールが見つかりました。';
$messages['searchnomatch'] = '一致するメールがありません。';
$messages['searching'] = '検索しています...';
$messages['checking'] = '確認しています...';
$messages['nospellerrors'] = 'スペル ミスは見つかりませんでした。';
$messages['folderdeleted'] = 'フォルダーを削除しました。';
$messages['foldersubscribed'] = 'フォルダーの購読に成功しました。';
$messages['folderunsubscribed'] = 'フォルダーの購読をやめました。';
$messages['folderpurged'] = 'フォルダーの整理に成功しました。';
$messages['deletedsuccessfully'] = '削除しました。';
$messages['converting'] = 'メールから書式を削除しています...';
$messages['messageopenerror'] = 'サーバーからメールを取得できません。';
$messages['fileuploaderror'] = 'ファイルのアップロードに失敗しました。';
$messages['filesizeerror'] = 'アップロードするファイルのサイズが上限 ($size) を超えました。';
$messages['copysuccess'] = ' $nr 個のアドレスのコピーに成功しました。';
$messages['copyerror'] = 'アドレスをコピーできません。';
$messages['sourceisreadonly'] = 'このアドレス情報は読み取り専用です。';
$messages['errorsavingcontact'] = '連絡先のアドレスを保存できません。';
$messages['movingmessage'] = 'メールを移動しています...';
$messages['copyingmessage'] = 'メールをコピーしています...';
$messages['deletingmessage'] = 'メールを削除しています...';
$messages['markingmessage'] = 'メールのマークを設定しています...';
$messages['receiptsent'] = '開封確認を送信しました。';
$messages['errorsendingreceipt'] = '開封確認を送信できません。';
$messages['nodeletelastidentity'] = '削除できません。少なくとも 1 つの個人情報が必要です。';
$messages['forbiddencharacter'] = 'フォルダー名に利用できない文字が含まれています。';
$messages['selectimportfile'] = 'アップロードするファイルを選択してください。';
$messages['addresswriterror'] = '選択したアドレス帳に書き込めません。';
$messages['contactaddedtogroup'] = 'グループに連絡先を追加しました。';
$messages['contactremovedfromgroup'] = 'グループから連絡先を削除しました。';
$messages['importwait'] = 'インポート中です。しばらくお待ちください...';
$messages['importerror'] = 'インポートに失敗しました。アップロードしたファイルは不正な vCard ファイルです。';
$messages['importconfirm'] = '<b>$inserted 件の連絡先をインポートしました。<br />既登録の連絡先 $skipped 件をスキップしました</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = '許可されていない操作です。';
$messages['nofromaddress'] = '選択中の個人情報にメールアドレスが含まれていません。';
$messages['editorwarning'] = 'テキスト エディターに切り替えるとすべての書式が失われます。よろしいですか?';
$messages['httpreceivedencrypterror'] = '致命的な設定エラーが発生しました。<b>メッセージは送信されませんでした。</b>システム管理者に至急連絡してください。';
$messages['smtpconnerror'] = 'SMTP Error ($code): サーバーへの接続に失敗しました。';
$messages['smtpautherror'] = 'SMTP Error ($code): 認証に失敗しました。';
$messages['smtpfromerror'] = 'SMTP Error ($code): 差出人 "$from" を設定できません ($msg)';
$messages['smtptoerror'] = 'SMTP Error ($code): 宛先 "$to" を追加できません ($msg)';
$messages['smtprecipientserror'] = 'SMTP Error: 宛先の一覧を解析できません。';
$messages['smtpdsnerror'] = 'SMTP エラー: 配送状況通知をサポートしていません。';
$messages['smtperror'] = 'SMTP エラー: $msg';
$messages['emailformaterror'] = 'メールアドレスが正しくありません: $email';
$messages['toomanyrecipients'] = '宛先が多すぎます。$max 件以内にしてください。';
$messages['maxgroupmembersreached'] = 'グループのメンバーが多すぎます。$max 件以内にしてください。';
$messages['internalerror'] = '内部エラーが発生しました。もう一度やり直してください。';
$messages['contactdelerror'] = '連絡先を削除できませんでした。';
$messages['contactdeleted'] = '連絡先を削除しました。';
$messages['groupdeleted'] = 'グループを削除しました。';
$messages['grouprenamed'] = 'グループ名を変更しました．';
$messages['groupcreated'] = 'グループを作成しました。';
$messages['messagedeleted'] = 'メールを削除しました。';
$messages['messagemoved'] = 'メールを移動しました。';
$messages['messagecopied'] = 'メールをコピーしました。';
$messages['messagemarked'] = 'メールのマークを設定しました。';
$messages['autocompletechars'] = '自動補完には少なくとも $min 文字入力してください。';
$messages['namecannotbeempty'] = '名前は空にできません。';
$messages['nametoolong'] = '名前が長すぎます。';
$messages['folderupdated'] = 'フォルダーの更新に成功しました。';
$messages['foldercreated'] = 'フォルダーの作成に成功しました。';

?>
