<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/
$messages['errortitle']  = 'エラーが発生しました!';
$messages['loginfailed']  = 'ログインできませんでした。';
$messages['cookiesdisabled'] = 'ブラウザーがCookieを受け付けません。';
$messages['sessionerror'] = 'セッションが正しくないか期限が切れています。';
$messages['storageerror'] = 'IMAP サーバーへの接続に失敗しました。';
$messages['servererror'] = 'サーバーでエラーです!';
$messages['servererrormsg'] = 'サーバーエラー: $msg';
$messages['connerror'] = '接続エラー(サーバーに到達できません)!';
$messages['dberror'] = 'データベースのエラーです!';
$messages['windowopenerror'] = 'ポップアップウィンドウがブロックされました!';
$messages['requesttimedout'] = 'リクエストのタイムアウト';
$messages['errorreadonly'] = '操作を実行できませんでした。フォルダーは読み込み専用です。';
$messages['errornoperm'] = '操作を実行できませんでした。権限がありません。';
$messages['erroroverquota'] = '操作を実行できませんでした。空きディスク容量がありません。';
$messages['erroroverquotadelete'] = '空きディスク容量がありません。メッセージを削除するには SHIFT+DEL を使用してください。';
$messages['invalidrequest'] = '不正なリクエストです!  データは保存していません。';
$messages['invalidhost'] = '正しくないサーバー名です。';
$messages['nomessagesfound'] = 'このフォルダーにはメッセージはありません。';
$messages['loggedout'] = 'ログアウトしました。さようなら!';
$messages['mailboxempty'] = 'メールボックスは空です。';
$messages['nomessages'] = 'メッセージなし';
$messages['refreshing'] = '再表示中...';
$messages['loading'] = '読み込み中...';
$messages['uploading'] = 'ファイルをアップロード中...';
$messages['uploadingmany'] = 'ファイルをアップロード中...';
$messages['loadingdata'] = 'データを読み込み中...';
$messages['checkingmail'] = '新しいメッセージを確認中...';
$messages['sendingmessage'] = 'メッセージを送信中...';
$messages['messagesent'] = 'メッセージを送信しました。';
$messages['savingmessage'] = 'メッセージを保存中...';
$messages['messagesaved'] = 'メッセージを下書きに保存しました。';
$messages['successfullysaved'] = '保存しました。';
$messages['savingresponse'] = '回答の文章を保存中...';
$messages['deleteresponseconfirm'] = 'この回答の文章を本当に削除しますか?';
$messages['addedsuccessfully'] = '連絡先をアドレス帳に追加しました。';
$messages['contactexists'] = '同じメールアドレスの連絡先が既に存在します。';
$messages['contactnameexists'] = '同じ名前の連絡先が既に存在します。';
$messages['blockedimages'] = 'あなたのプライバシーを守るため、このメッセージに含まれる外部画像を遮断しました。';
$messages['encryptedmessage'] = 'このメッセージは暗号化されていて表示できません、ごめんなさい!';
$messages['nocontactsfound'] = '連絡先がありません。';
$messages['contactnotfound'] = '連絡先が見つかりません。';
$messages['contactsearchonly'] = '連絡先を探すための検索語を入力してください';
$messages['sendingfailed'] = 'メッセージを送信できませんでした。';
$messages['senttooquickly'] = 'このメッセージを送信するまで、$sec秒お待ちください。';
$messages['errorsavingsent'] = '送信したメッセージの保存中にエラーが発生しました。';
$messages['errorsaving'] = '保存中にエラーが発生しました。';
$messages['errormoving'] = 'メッセージを移動できません。';
$messages['errorcopying'] = 'メッセージをコピーできません。';
$messages['errordeleting'] = 'メッセージを削除できません。';
$messages['errormarking'] = 'メッセージにマークを設定できません。';
$messages['deletecontactconfirm']  = '選択した連絡先を本当に削除しますか?';
$messages['deletegroupconfirm']  = 'このグループを本当に削除しますか?';
$messages['deletemessagesconfirm'] = '選択したメールを本当に削除しますか?';
$messages['deletefolderconfirm']  = 'このフォルダーを本当に削除しますか?';
$messages['purgefolderconfirm']  = 'このフォルダーのすべてのメールを本当に削除しますか?';
$messages['contactdeleting'] = '連絡先を削除中...';
$messages['groupdeleting'] = 'グループを削除中...';
$messages['folderdeleting'] = 'フォルダーを削除中...';
$messages['foldermoving'] = 'フォルダーを移動中...';
$messages['foldersubscribing'] = 'フォルダーを購読中...';
$messages['folderunsubscribing'] = 'フォルダーの購読を解除中...';
$messages['formincomplete'] = 'フォームに未記入の項目があります。';
$messages['noemailwarning'] = '正しい電子メールアドレスを入力してください。';
$messages['nonamewarning']  = '名前を入力してください。';
$messages['nopagesizewarning'] = 'ページのサイズを入力してください。';
$messages['nosenderwarning'] = '送信者の電子メールアドレスを入力してください。';
$messages['norecipientwarning'] = '少なくとも1つ宛先を入力してください。';
$messages['nosubjectwarning']  = '件名(Subject)の欄が空です。今、入力しますか?';
$messages['nobodywarning'] = '本文の無いメッセージを送信しますか?';
$messages['notsentwarning'] = 'メッセージを送信しませんでした。このメッセージを破棄しますか。';
$messages['restoresavedcomposedata'] = '以前に作成して未送信のメッセージがあります。\n\n件名: $subject\n保存日: $date\n\nこのメッセージを回復しますか?';
$messages['noldapserver'] = '検索するLDAPサーバーを選択してください。';
$messages['nosearchname'] = '連絡先の名前か電子メールアドレスを入力してください。';
$messages['notuploadedwarning'] = 'すべての添付ファイルのアップロードを完了していません。今しばらく待つか、取り消ししてください。';
$messages['searchsuccessful'] = '$nr通のメッセージが見つかりました。';
$messages['contactsearchsuccessful'] = '$nr件の連絡先が見つかりました。';
$messages['searchnomatch'] = '一致するものが見つかりませんでした。';
$messages['searching'] = '検索中...';
$messages['checking'] = '確認中...';
$messages['stillsearching'] = 'さらに検索中...';
$messages['nospellerrors'] = 'スペルミスは見つかりませんでした。';
$messages['folderdeleted'] = 'フォルダーを削除しました。';
$messages['foldersubscribed'] = 'フォルダーを購読しました。';
$messages['folderunsubscribed'] = 'フォルダーの購読を解除しました。';
$messages['folderpurged'] = 'フォルダーを空にしました。';
$messages['folderexpunged'] = 'フォルダーを整理しました。';
$messages['deletedsuccessfully'] = '削除しました。';
$messages['converting'] = 'メールから書式を削除中...';
$messages['messageopenerror'] = 'サーバーからメールを読み込めません。';
$messages['fileuploaderror'] = 'ファイルをアップロードできませんでした。';
$messages['filesizeerror'] = 'アップロードするファイルのサイズが上限($size)を超えました。';
$messages['copysuccess'] = 'Successfully copied $nr contacts.';
$messages['movesuccess'] = 'Successfully moved $nr contacts.';
$messages['copyerror'] = 'Could not copy any contacts.';
$messages['moveerror'] = 'Could not move any contacts.';
$messages['sourceisreadonly'] = 'このアドレスソースは読み込み専用です。';
$messages['errorsavingcontact'] = '連絡先のアドレスを保存できません。';
$messages['movingmessage'] = 'メッセージを移動中...';
$messages['copyingmessage'] = 'メッセージをコピー中...';
$messages['copyingcontact'] = '連絡先をコピー中...';
$messages['movingcontact'] = 'Moving contact(s)...';
$messages['deletingmessage'] = 'メッセージを削除中...';
$messages['markingmessage'] = 'メッセージにマークを設定中...';
$messages['addingmember'] = 'グループに連絡先をコピー中...';
$messages['removingmember'] = 'グループから連絡先を削除中...';
$messages['receiptsent'] = '開封通知を送信しました。';
$messages['errorsendingreceipt'] = '開封通知を送信ができませんでした。';
$messages['deleteidentityconfirm'] = 'この識別情報を本当に削除しますか?';
$messages['nodeletelastidentity'] = 'この識別情報は最後の1つなので削除できません。';
$messages['forbiddencharacter'] = 'フォルダー名に使用できない文字が含まれています。';
$messages['selectimportfile'] = 'アップロードするファイルを選択してください。';
$messages['addresswriterror'] = '選択したアドレス帳は書き込みできないアドレス帳です。';
$messages['contactaddedtogroup'] = 'このグループに連絡先を追加しました。';
$messages['contactremovedfromgroup'] = 'このグループから連絡先を削除しました。';
$messages['nogroupassignmentschanged'] = 'グループの割り当てを変更しませんでした。';
$messages['importwait'] = 'インポート中です。しばらくお待ちください...';
$messages['importformaterror'] = 'インポートできませんでした!  アップロードしたファイルは正しいデータをインポートするファイルではありません。';
$messages['importconfirm'] = '<b>$inserted件の連絡先をインポートしました。</b>';
$messages['importconfirmskipped'] = '<b>$skipped件の既存する項目を飛ばした。</b>';
$messages['importmessagesuccess'] = '$nr件のメッセージをインポートしました。';
$messages['importmessageerror'] = 'インポートできませんでした!  アップロードしたファイルは有効なメッセージやメールボックスのファイルではありません。';
$messages['opnotpermitted'] = '許可されていない操作です。';
$messages['nofromaddress'] = '選択している識別情報に電子メールアドレスが抜けています。';
$messages['editorwarning'] = 'エディターの種類を切り替えるとテキストの書式を失うかもしれません。本当に続けますか?';
$messages['httpreceivedencrypterror'] = '致命的な設定エラーが発生しました。システム管理者に至急連絡してください。<b>メッセージを送信できません。</b>';
$messages['smtpconnerror'] = 'SMTP Error ($code): サーバーへの接続に失敗しました。';
$messages['smtpautherror'] = 'SMTP Error ($code): 認証に失敗しました。';
$messages['smtpfromerror'] = 'SMTP Error ($code): 差出人 "$from" を設定できません ($msg)';
$messages['smtptoerror'] = 'SMTP Error ($code): 宛先 "$to" を追加できません ($msg)';
$messages['smtprecipientserror'] = 'SMTP Error: 宛先の一覧を解析できません。';
$messages['smtperror'] = 'SMTP エラー: $msg';
$messages['emailformaterror'] = '電子メールアドレスが正しくありません: $email';
$messages['toomanyrecipients'] = '宛先が多すぎます。$max件以内にしてください。';
$messages['maxgroupmembersreached'] = 'グループのメンバーが多すぎます。$max 件以内にしてください。';
$messages['internalerror'] = '内部エラーが発生しました。もう一度試してください。';
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
$messages['messagedeleted'] = 'メッセージを削除しました。';
$messages['messagemoved'] = 'メッセージを移動しました。';
$messages['messagecopied'] = 'メッセージをコピーしました。';
$messages['messagemarked'] = 'メッセージにマークを設定しました。';
$messages['autocompletechars'] = '自動補完には少なくとも$min文字入力してください。';
$messages['autocompletemore'] = '多くのエントリーが見つかりました。もっと入力してください。';
$messages['namecannotbeempty'] = '名前は空にできません。';
$messages['nametoolong'] = '名前が長すぎます。';
$messages['folderupdated'] = 'フォルダーを更新しました。';
$messages['foldercreated'] = 'フォルダーを作成しました。';
$messages['invalidimageformat'] = '画像の形式が正しくありません。';
$messages['mispellingsfound'] = 'メッセージにスペルミスを見つけました。';
$messages['parentnotwritable'] = '選択した親フォルダーに作成または移動をできませんでした。アクセス権限がありません。';
$messages['messagetoobig'] = 'その処理をするにはメッセージ部分が大きすぎます。';
$messages['attachmentvalidationerror'] = '警告!  この添付ファイルはメッセージの宣言と一致しない種類という理由のため疑わしいです。送信者を信頼できないなら、悪意のある内容を含んでいるかもしれないのでブラウザーで開いてはなりません。<br/><br/><em>予測した種類: $expected; 発見した種類: $found</em>';
$messages['noscriptwarning'] = '警告: このウェブメールサービスにはJavascriptが必要です!  使用するにはJavascriptをブラウザーの設定で有効にしてください。';
?>
