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

//  EN-Revision: 5490

*/

$messages = array();
$messages['loginfailed']  = 'ログインに失敗しました。';
$messages['cookiesdisabled'] = 'ブラウザーで Cookie が無効に設定されています。';
$messages['sessionerror'] = 'セッションが正しくないか期限切れです。';
$messages['imaperror'] = 'IMAP サーバーへの接続に失敗しました。';
$messages['servererror'] = 'サーバーでエラーが発生しました。';
$messages['servererrormsg'] = 'サーバー エラー: $msg';
$messages['dberror'] = 'データベースのエラーです!';
$messages['errorreadonly'] = '実行に失敗しました。フォルダーは読み込み専用です。';
$messages['errornoperm'] = '実行に失敗しました。権限がありません。';
$messages['invalidrequest'] = '不正なリクエストです。データは保存されませんでした。';
$messages['nomessagesfound'] = 'このフォルダーにはメールはありません。';
$messages['loggedout'] = 'ログアウトしました。';
$messages['mailboxempty'] = 'フォルダーは空です。';
$messages['loading'] = '読み込んでいます...';
$messages['uploading'] = 'ファイルをアップロードしています...';
$messages['uploadingmany'] = 'ファイルをアップロードしています...';
$messages['loadingdata'] = 'データを読み込んでいます...';
$messages['checkingmail'] = 'メールを確認しています...';
$messages['sendingmessage'] = 'メールを送信しています...';
$messages['messagesent'] = 'メールを送信しました。';
$messages['savingmessage'] = 'メールを保存しています...';
$messages['messagesaved'] = 'メールを下書きに保存しました。';
$messages['successfullysaved'] = '保存しました。';
$messages['addedsuccessfully'] = 'アドレス帳への連絡先の追加しました。';
$messages['contactexists'] = 'このメールアドレスを含む連絡先が既に存在します。';
$messages['messagesaved'] = 'メールを下書きに保存しました。';
$messages['successfullysaved'] = '保存しました。';
$messages['addedsuccessfully'] = '連絡先をアドレス帳に追加しました。';
$messages['contactexists'] = '同じメールアドレスの連絡先がすでに存在します。';
$messages['contactnameexists'] = '同じ名前の連絡先がすでに存在します。';
$messages['blockedimages'] = 'プライバシー保護のため、このメールに含まれるリモート画像をブロックしました。';
$messages['encryptedmessage'] = 'このメールは暗号化されているため表示できません。';
$messages['nocontactsfound'] = '連絡先がありません。';
$messages['contactnotfound'] = '連絡先が見つかりません。';
$messages['contactsearchonly'] = '連絡先の検索にはいくつかの単語を入力します';
$messages['sendingfailed'] = 'メールの送信に失敗しました。';
$messages['senttooquickly'] = 'このメールを送信するまで $sec 秒 お待ちください。';
$messages['errorsavingsent'] = '送信メールの保存中にエラーが発生しました。';
$messages['errorsaving'] = '保存中にエラーが発生しました。';
$messages['errormoving'] = 'メールを移動できません。';
$messages['errorcopying'] = 'メールをコピーできません。';
$messages['errordeleting'] = 'メールを削除できません。';
$messages['errormarking'] = 'メールのマークを設定できません。';
$messages['deletecontactconfirm']  = '選択した連絡先を本当に削除しますか?';
$messages['deletegroupconfirm']  = 'このグループを本当に削除しますか?';
$messages['deletemessagesconfirm'] = '選択したメールを本当に削除しますか?';
$messages['deletefolderconfirm'] = 'このフォルダーを本当に削除しますか?';
$messages['purgefolderconfirm'] = 'このフォルダーの全メールを本当に削除しますか?';
$messages['contactdeleting'] = '連絡先を削除しています...';
$messages['groupdeleting'] = 'グループを削除しています...';
$messages['folderdeleting'] = 'フォルダーを削除しています...';
$messages['foldermoving'] = 'フォルダーを移動しています...';
$messages['foldersubscribing'] = 'フォルダーを購読しています...';
$messages['folderunsubscribing'] = 'フォルダーの購読をやめています...';
$messages['formincomplete'] = 'フォームの全項目を入力していません。';
$messages['noemailwarning'] = '正しいメールアドレスを入力してください。';
$messages['nonamewarning']  = '名前を入力してください。';
$messages['nopagesizewarning'] = 'ページのサイズを入力してください。';
$messages['nosenderwarning'] = '差出人のメールアドレスを入力してください。';
$messages['norecipientwarning'] = '宛先は少なくともひとり分は入力してください。';
$messages['nosubjectwarning'] = '件名が空です。今すぐ入力しますか?';
$messages['nobodywarning'] = '本文が無いメールを送信しますか?';
$messages['notsentwarning'] = 'メールは送信されませんでした。破棄しますか?';
$messages['noldapserver'] = '検索する LDAP サーバーを選択してください。';
$messages['nosearchname'] = '連絡先の名前かメールアドレスを入力してください。';
$messages['notuploadedwarning'] = 'アップロードが完了していません。アップロードが完了するまで待つか、キャンセルしてください。';
$messages['searchsuccessful'] = '$nr 件のメールが見つかりました。';
$messages['contactsearchsuccessful'] = '$nr 件の連絡先が見つかりました。';
$messages['searchnomatch'] = '一致するものが見つかりませんでした。';
$messages['searching'] = '検索しています...';
$messages['checking'] = '確認しています...';
$messages['nospellerrors'] = 'スペル ミスは見つかりませんでした。';
$messages['folderdeleted'] = 'フォルダーを削除しました。';
$messages['foldersubscribed'] = 'フォルダーを購読しました。';
$messages['folderunsubscribed'] = 'フォルダーの購読をやめました。';
$messages['folderpurged'] = 'フォルダーを空にしました。';
$messages['folderexpunged'] = 'フォルダーを整理しました。';
$messages['deletedsuccessfully'] = '削除しました。';
$messages['converting'] = 'メールから書式を削除しています...';
$messages['messageopenerror'] = 'サーバーからメールを取得できません。';
$messages['fileuploaderror'] = 'ファイルのアップロードに失敗しました。';
$messages['filesizeerror'] = 'アップロードするファイルのサイズが上限 ($size) を超えました。';
$messages['copysuccess'] = '$nr 件のアドレスのコピーしました。';
$messages['copyerror'] = 'いくつかのアドレスがコピーできません。';
$messages['sourceisreadonly'] = 'この元アドレスは読み込み専用です。';
$messages['errorsavingcontact'] = '連絡先のアドレスを保存できません。';
$messages['movingmessage'] = 'メールを移動しています...';
$messages['copyingmessage'] = 'メールをコピーしています...';
$messages['copyingcontact'] = '連絡先をコピーしています...';
$messages['deletingmessage'] = 'メールを削除しています...';
$messages['markingmessage'] = 'メールのマークを設定しています...';
$messages['addingmember'] = 'グループへ連絡先をコピーしています...';
$messages['removingmember'] = 'グループから連絡先を削除しています...';
$messages['receiptsent'] = '開封通知を送信しました。';
$messages['errorsendingreceipt'] = '開封通知の送信ができません。';
$messages['nodeletelastidentity'] = '削除できません。少なくともひとつの個人情報が必要です。';
$messages['forbiddencharacter'] = 'フォルダー名に利用できない文字が含まれています。';
$messages['selectimportfile'] = 'アップロードするファイルを選択してください。';
$messages['addresswriterror'] = '選択したアドレス帳に書き込めません。';
$messages['contactaddedtogroup'] = 'このグループへ連絡先を追加しました。';
$messages['contactremovedfromgroup'] = 'このグループから連絡先を削除しました。';
$messages['importwait'] = 'インポート中です。しばらくお待ちください...';
$messages['importerror'] = 'インポートに失敗しました。アップロードしたファイルは不正な vCard ファイルです。';
$messages['importconfirm'] = '<b>$inserted 件の連絡先をインポートしました。</b>';
$messages['importconfirmskipped'] = '<b>$skipped 拳の既存エントリーをスキップしました</b>';

$messages['opnotpermitted'] = '許可されていない操作です。';
$messages['nofromaddress'] = '選択中の個人情報にメールアドレスが含まれていません。';
$messages['editorwarning'] = 'テキスト エディターに切り替えるとすべての書式が失われます。よろしいですか?';
$messages['httpreceivedencrypterror'] = '致命的な設定エラーが発生しました。<b>メッセージは送信されませんでした。</b>システム管理者に至急連絡してください。';
$messages['smtpconnerror'] = 'SMTP Error ($code): サーバーへの接続に失敗しました。';
$messages['smtpautherror'] = 'SMTP Error ($code): 認証に失敗しました。';
$messages['smtpfromerror'] = 'SMTP Error ($code): 差出人 "$from" を設定できません ($msg)';
$messages['smtptoerror'] = 'SMTP Error ($code): 宛先 "$to" を追加できません ($msg)';
$messages['smtprecipientserror'] = 'SMTP Error: 宛先の一覧を解析できません。';
$messages['smtperror'] = 'SMTP エラー: $msg';
$messages['emailformaterror'] = 'メールアドレスが正しくありません: $email';
$messages['toomanyrecipients'] = '宛先が多すぎます。$max 件以内にしてください。';
$messages['maxgroupmembersreached'] = 'グループのメンバーが多すぎます。$max 件以内にしてください。';
$messages['internalerror'] = '内部エラーが発生しました。もう一度やり直してください。';
$messages['contactdelerror'] = '連絡先を削除できませんでした。';
$messages['contactdeleted'] = '連絡先を削除しました。';
$messages['contactrestoreerror'] = '削除した連絡先を復元できませんでした。';
$messages['contactrestored'] = '連絡先を復元しました。';
$messages['groupdeleted'] = 'グループを削除しました。';
$messages['grouprenamed'] = 'グループ名を変更しました．';
$messages['groupcreated'] = 'グループを作成しました。';
$messages['savedsearchdeleted'] = '保存した検索情報を削除しました。';
$messages['savedsearchdeleteerror'] = '保存した検索情報を削除できません。';
$messages['savedsearchcreated'] = '検索情報を作成しました。';
$messages['savedsearchcreateerror'] = '検索情報を削除できませんでした。';
$messages['messagedeleted'] = 'メールを削除しました。';
$messages['messagemoved'] = 'メールを移動しました。';
$messages['messagecopied'] = 'メールをコピーしました。';
$messages['messagemarked'] = 'メールのマークを設定しました。';
$messages['autocompletechars'] = '自動補完には少なくとも $min 文字入力してください。';
$messages['autocompletemore'] = '多くのエントリーが見つかりました。もっと入力してください。';
$messages['namecannotbeempty'] = '名前を空欄にできません。';
$messages['nametoolong'] = '名前が長すぎます。';
$messages['folderupdated'] = 'フォルダーを更新しました。';
$messages['foldercreated'] = 'フォルダーを作成しました。';
$messages['invalidimageformat'] = '画像の形式が正しくありません。';
$messages['mispellingsfound'] = 'メッセージにスペル エラーを見つけました。';
$messages['parentnotwritable'] = '選択した親フォルダーへの作成、移動に失敗しました。アクセス権限がありません。';
$messages['messagetoobig'] = 'その処理をするにはメッセージ部分が大きすぎます。';

?>
