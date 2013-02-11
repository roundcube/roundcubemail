<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = '$productにようこそ';
$labels['username']  = 'ユーザー名';
$labels['password']  = 'パスワード';
$labels['server']    = 'サーバー';
$labels['login']     = 'ログイン';

// taskbar
$labels['logout']   = 'ログアウト';
$labels['mail']     = '電子メール';
$labels['settings'] = '設定';
$labels['addressbook'] = 'アドレス帳';

// mailbox names
$labels['inbox']  = '受信箱';
$labels['drafts'] = '下書き';
$labels['sent']   = '送信済み';
$labels['trash']  = 'ごみ箱';
$labels['junk']   = '迷惑メール';

// message listing
$labels['subject'] = '件名';
$labels['from']    = '発信者';
$labels['sender']  = '送信者';
$labels['to']      = '宛先';
$labels['cc']      = '写し(Cc)';
$labels['bcc']     = '隠した写し(Bcc)';
$labels['replyto'] = '返信先';
$labels['followupto'] = 'Followup-To';
$labels['date']    = '日付';
$labels['size']    = '大きさ';
$labels['priority'] = '優先度';
$labels['organization'] = '企業名';
$labels['readstatus'] = '閲覧の状態';
$labels['listoptions'] = '一覧のオプション...';

$labels['mailboxlist'] = 'フォルダー';
$labels['messagesfromto'] = '$count通の$from通目から$to通目のメッセージ';
$labels['threadsfromto'] = '$count通の$from通目から$to通目のスレッド';
$labels['messagenrof'] = '$count通の$nr通目のメッセージ';
$labels['fromtoshort'] = '$count通の$from通目から$to通目';

$labels['copy']     = 'コピー';
$labels['move']     = '移動';
$labels['moveto']   = 'フォルダーに移動';
$labels['download'] = 'ダウンロード';
$labels['showattachment'] = '表示';
$labels['showanyway'] = 'とにかく表示';

$labels['filename'] = 'ファイル名';
$labels['filesize'] = 'ファイルの大きさ';

$labels['addtoaddressbook'] = 'アドレス帳に追加';

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

// months short
$labels['jan']	= '1月';
$labels['feb']	= '2月';
$labels['mar']	= '3月';
$labels['apr']	= '4月';
$labels['may']	= '5月';
$labels['jun']	= '6月';
$labels['jul'] 	= '7月';
$labels['aug']	= '8月';
$labels['sep']	= '9月';
$labels['oct']	= '10月';
$labels['nov']	= '11月';
$labels['dec']	= '12月';

// months long
$labels['longjan']	= '1月';
$labels['longfeb']	= '2月';
$labels['longmar']	= '3月';
$labels['longapr']	= '4月';
$labels['longmay']	= '5月';
$labels['longjun']	= '6月';
$labels['longjul']	= '7月';
$labels['longaug']	= '8月';
$labels['longsep']	= '9月';
$labels['longoct']	= '10月';
$labels['longnov']	= '11月';
$labels['longdec']	= '12月';

$labels['today'] = '今日';

// toolbar buttons
$labels['refresh']          = '再読込み';
$labels['checkmail']        = '新しく届いたメッセージを確認';
$labels['compose']          = 'メッセージの作成';
$labels['writenewmessage']  = '新しいメッセージを作成';
$labels['reply']            = '返信';
$labels['replytomessage']   = '送信者に返信';
$labels['replytoallmessage'] = 'メーリングリスト、または送信者とすべての宛先に返信';
$labels['replyall']         = '全員に返信';
$labels['replylist']        = 'メーリングリストに返信';
$labels['forward']          = '転送';
$labels['forwardinline']    = '本文に挿入して転送';
$labels['forwardattachment'] = '添付ファイルとして転送';
$labels['forwardmessage']   = 'メッセージを転送';
$labels['deletemessage']    = 'メッセージを削除';
$labels['movemessagetotrash'] = 'ごみ箱に移動';
$labels['printmessage']     = 'メッセージを印刷';
$labels['previousmessage']  = '前のメッセージを表示';
$labels['firstmessage']     = '最初のメッセージを表示';
$labels['nextmessage']      = '次のメッセージを表示';
$labels['lastmessage']      = '最後のメッセージを表示';
$labels['backtolist']       = 'メッセージの一覧に戻る';
$labels['viewsource']       = 'ソースを表示';
$labels['mark']             = 'マーク';
$labels['markmessages']     = 'マークを付ける';
$labels['markread']         = '既読に設定';
$labels['markunread']       = '未読に設定';
$labels['markflagged']      = 'フラグを設定';
$labels['markunflagged']    = 'フラグを解除';
$labels['moreactions']      = 'その他の操作...';
$labels['more']             = '続く';
$labels['back']             = '戻る';
$labels['options']          = 'オプション';

$labels['select'] = '選択';
$labels['all'] = 'すべて';
$labels['none'] = 'なし';
$labels['currpage'] = '現在のページ';
$labels['unread'] = '未読';
$labels['flagged'] = 'フラグ付き';
$labels['unanswered'] = '未返信';
$labels['deleted'] = '削除済み';
$labels['undeleted'] = '削除済みでない';
$labels['invert'] = '反転';
$labels['filter'] = 'フィルター';
$labels['list'] = '一覧';
$labels['threads'] = 'スレッド';
$labels['expand-all'] = 'すべて展開';
$labels['expand-unread'] = '未開封のメッセージを展開';
$labels['collapse-all'] = 'すべて折りたたむ';
$labels['threaded'] = 'スレッド化';

$labels['autoexpand_threads'] = 'メッセージのスレッドを展開';
$labels['do_expand'] = 'すべてのスレッド';
$labels['expand_only_unread'] = '未読のメッセージだけ';
$labels['fromto'] = '発信者/宛先';
$labels['flag'] = 'フラグ';
$labels['attachment'] = '添付ファイル';
$labels['nonesort'] = 'なし';
$labels['sentdate'] = '送信日';
$labels['arrival'] = '受信日';
$labels['asc'] = '昇順';
$labels['desc'] = '降順';
$labels['listcolumns'] = '列を一覧';
$labels['listsorting'] = '並べ替える列';
$labels['listorder'] = '並べ替える順序';
$labels['listmode'] = '一覧表示モード';

$labels['folderactions'] = 'フォルダーの操作...';
$labels['compact'] = '圧縮';
$labels['empty'] = '空';

$labels['quota'] = 'ディスクの使用状況';
$labels['unknown']  = '不明';
$labels['unlimited']  = '無制限';

$labels['quicksearch']  = '高速検索';
$labels['resetsearch']  = '検索を解除';
$labels['searchmod']  = '検索の条件';
$labels['msgtext']  = 'メッセージ全体';
$labels['body']  = '本文';

$labels['openinextwin'] = '新しいウィンドウで開く';
$labels['emlsave'] = 'ダウンロード(.eml形式)';

// message compose
$labels['editasnew']      = '新しいメッセージとして編集';
$labels['send']           = '送信';
$labels['sendmessage']    = 'メッセージを送信';
$labels['savemessage']    = '下書きとして保存';
$labels['addattachment']  = 'ファイルを添付';
$labels['charset']        = '文字セット';
$labels['editortype']     = 'エディターの種類';
$labels['returnreceipt']  = '開封確認';
$labels['dsn']            = '配送状態通知';
$labels['mailreplyintro'] = '$date に $sender さんは書きました:';
$labels['originalmessage'] = '元のメッセージ';

$labels['editidents']    = '識別情報を編集';
$labels['spellcheck']    = 'スペル';
$labels['checkspelling'] = 'スペルチェック';
$labels['resumeediting'] = '編集を再開';
$labels['revertto']      = '元に戻す';

$labels['attach'] = '添付';
$labels['attachments'] = '添付ファイル';
$labels['upload'] = 'アップロード';
$labels['uploadprogress'] = '$percent ($current / $total)';
$labels['close']  = '閉じる';
$labels['messageoptions']  = 'メッセージのオプション...';

$labels['low']     = '低';
$labels['lowest']  = '最低';
$labels['normal']  = '通常';
$labels['high']    = '高';
$labels['highest'] = '最高';

$labels['nosubject']  = '(件名なし)';
$labels['showimages'] = '画像を表示';
$labels['alwaysshow'] = '$sender から届いた画像は常に表示';
$labels['isdraft']    = 'これは下書きのメッセージです。';
$labels['andnmore']   = 'さらに$nr件...';
$labels['togglemoreheaders'] = 'さらにメッセージのヘッダーを表示';
$labels['togglefullheaders'] = 'メッセージヘッダーの生の表示と切り替え';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'テキスト';
$labels['savesentmessagein'] = '送信したメッセージの保存先';
$labels['dontsave'] = '保存しない';
$labels['maxuploadsize'] = '添付可能なファイルは最大で$sizeです。';

$labels['addcc'] = '写し(Cc)を追加';
$labels['addbcc'] = '隠した写し(Bcc)を追加';
$labels['addreplyto'] = '返信先(Reply-To)を追加';
$labels['addfollowupto'] = 'フォロー先(Followup-Toを追加';

// mdn
$labels['mdnrequest'] = 'このメッセージを読んだときに、送信者が通知を受けたいと依頼しています。開封確認の通知を送信しますか?';
$labels['receiptread'] = '開封確認(表示済み)';
$labels['yourmessage'] = 'これはあなたが送信したメッセージに対する開封確認です。';
$labels['receiptnote'] = '注意: この開封確認は、メッセージが宛先のコンピューターで表示されたことだけを知らせるものです。受信者がメッセージを読んだり、内容を理解したと保証するものではありません。';

// address boook
$labels['name']         = '表示名';
$labels['firstname']    = '名';
$labels['surname']      = '姓';
$labels['middlename']   = 'ミドルネーム';
$labels['nameprefix']   = '敬称(名前の前)';
$labels['namesuffix']   = '敬称(名前の後)';
$labels['nickname']     = 'ニックネーム';
$labels['jobtitle']     = '職名';
$labels['department']   = '部署名';
$labels['gender']       = '性別';
$labels['maidenname']   = '旧姓';
$labels['email']        = '電子メール';
$labels['phone']        = '電話番号';
$labels['address']      = '住所';
$labels['street']       = '住所';
$labels['locality']     = '都市';
$labels['zipcode']      = '郵便番号';
$labels['region']       = '都道府県';
$labels['country']      = '国';
$labels['birthday']     = '誕生日';
$labels['anniversary']  = '記念日';
$labels['website']      = 'ウェブサイト';
$labels['instantmessenger'] = 'インスタントメッセージ';
$labels['notes'] = '注釈';
$labels['male']   = '男性';
$labels['female'] = '女性';
$labels['manager'] = '管理者';
$labels['assistant'] = 'アシスタント';
$labels['spouse'] = '配偶者';
$labels['allfields'] = 'すべての項目';
$labels['search'] = '検索';
$labels['advsearch'] = '高度な検索';
$labels['advanced'] = '高度';
$labels['other'] = 'その他';

$labels['typehome']   = '自宅';
$labels['typework']   = '職場';
$labels['typeother']  = 'その他';
$labels['typemobile']  = 'モバイル';
$labels['typemain']  = '主回線';
$labels['typehomefax']  = '自宅ファクス';
$labels['typeworkfax']  = '職場のFax';
$labels['typecar']  = '車';
$labels['typepager']  = 'ページャー(ポケットベル)';
$labels['typevideo']  = '動画';
$labels['typeassistant']  = 'アシスタント';
$labels['typehomepage']  = 'ホームページ';
$labels['typeblog'] = 'ブログ';
$labels['typeprofile'] = 'プロフィール';

$labels['addfield'] = '項目を追加...';
$labels['addcontact'] = '新しい連絡先を追加';
$labels['editcontact'] = '連絡先を編集';
$labels['contacts'] = '連絡先';
$labels['contactproperties'] = '連絡先の属性';
$labels['personalinfo'] = '個人情報';

$labels['edit']   = '編集';
$labels['cancel'] = '取り消し';
$labels['save']   = '保存';
$labels['delete'] = '削除';
$labels['rename'] = '名前を変更';
$labels['addphoto'] = '追加';
$labels['replacephoto'] = '置き換え';
$labels['uploadphoto'] = '写真をアップロード';

$labels['newcontact']     = '新しい連絡先カードを作成';
$labels['deletecontact']  = '選択した連絡先を削除';
$labels['composeto']      = 'この連絡先宛にメッセージを作成';
$labels['contactsfromto'] = '$count件の連絡先の$from件目から$to件目';
$labels['print']          = '印刷';
$labels['export']         = 'エクスポート';
$labels['exportall']      = 'すべてエクスポート';
$labels['exportsel']      = '選択した連絡先をエクスポート';
$labels['exportvcards']   = 'vCard形式でエクスポート';
$labels['newcontactgroup'] = '連絡先グループを新しく作成';
$labels['grouprename']    = 'グループ名を変更';
$labels['groupdelete']    = 'グループを削除';
$labels['groupremoveselected'] = '選択した連絡先をグループから削除';

$labels['previouspage']   = '前のページを表示';
$labels['firstpage']      = '最初のページを表示';
$labels['nextpage']       = '次のページを表示';
$labels['lastpage']       = '最後のページを表示';

$labels['group'] = 'グループ';
$labels['groups'] = 'グループ';
$labels['personaladrbook'] = '個人の住所';

$labels['searchsave'] = '検索情報を保存';
$labels['searchdelete'] = '検索情報を削除';

$labels['import'] = 'インポート';
$labels['importcontacts'] = '連絡先をインポート';
$labels['importfromfile'] = 'ファイルからインポート';
$labels['importtarget'] = 'アドレス帳に新しい連絡先の追加:';
$labels['importreplace'] = 'アドレス帳全体と置き換え';
$labels['importdesc'] = '連絡先を既存のアドレス帳からアップロードできます。<br />現在、<a href="http://ja.wikipedia.org/wiki/VCard">vCard</a>またはCSV(コンマ区切り)のデータ形式からアドレスのインポートをサポートしています。';
$labels['done'] = '完了';

// settings
$labels['settingsfor'] = '次の設定:';
$labels['about'] = 'このプログラムについて';
$labels['preferences'] = '設定';
$labels['userpreferences'] = 'ユーザー設定';
$labels['editpreferences'] = 'ユーザー設定を変更';

$labels['identities'] = '識別情報';
$labels['manageidentities'] = 'このアカウントの識別情報を管理';
$labels['newidentity'] = '新しい識別情報';

$labels['newitem'] = '新しい項目';
$labels['edititem'] = '項目の編集';

$labels['preferhtml'] = 'HTMLを表示';
$labels['defaultcharset'] = '初期設定の文字セット';
$labels['htmlmessage'] = 'HTMLメッセージ';
$labels['messagepart'] = 'パート';
$labels['digitalsig'] = 'デジタル署名';
$labels['dateformat'] = '日付の書式';
$labels['timeformat'] = '時刻の書式';
$labels['prettydate'] = '短い日付で表示';
$labels['setdefault']  = '初期値を設定';
$labels['autodetect']  = '自動識別';
$labels['language']  = '言語';
$labels['timezone']  = 'タイムゾーン';
$labels['pagesize']  = '1ページの表示件数';
$labels['signature'] = '署名';
$labels['dstactive']  = 'サマータイムを適用';
$labels['showinextwin'] = '新しいウィンドウでメッセージを表示';
$labels['composeextwin'] = '新しいウィンドウでメッセージを作成';
$labels['htmleditor'] = '作成時にHTMLメッセージを使用';
$labels['htmlonreply'] = '返信時にHTMLメッセージを使用';
$labels['htmlonreplyandforward'] = '転送またはHTMLメッセージへの返信';
$labels['htmlsignature'] = 'HTMLの署名';
$labels['previewpane'] = 'プレビュー枠の表示';
$labels['skin'] = 'インターフェイスのスキン';
$labels['logoutclear'] = 'ログアウト時にごみ箱の内容を消去';
$labels['logoutcompact'] = 'ログアウト時にフォルダーを整理';
$labels['uisettings'] = 'ユーザーインターフェイス';
$labels['serversettings'] = 'サーバーの設定';
$labels['mailboxview'] = '受信箱';
$labels['mdnrequests'] = '開封確認の要求の処理';
$labels['askuser'] = '開封確認の送信を確認';
$labels['autosend'] = '開封確認を送信';
$labels['autosendknown'] = '送信者が連絡先に登録されていれば送信、それ以外は確認';
$labels['autosendknownignore'] = '送信者が連絡先に登録されていれば送信、それ以外は無視';
$labels['ignore'] = '無視';
$labels['readwhendeleted'] = '削除したメッセージを既読に設定';
$labels['flagfordeletion'] = '削除する代わりに削除済みフラグを設定';
$labels['skipdeleted'] = '削除済みのメッセージを表示しない';
$labels['deletealways'] = 'ごみ箱への移動できなかったメッセージは削除';
$labels['deletejunk'] = '迷惑メールのメッセージを直接削除';
$labels['showremoteimages'] = '外部のインライン画像を表示';
$labels['fromknownsenders'] = '送信者が知人の場合';
$labels['always'] = '常時';
$labels['showinlineimages'] = 'メッセージに添付された画像を下に表示';
$labels['autosavedraft']  = '自動的に下書きを保存';
$labels['everynminutes']  = '$n分毎';
$labels['refreshinterval']  = '再表示 (新しいメッセージの確認等)';
$labels['never']  = 'しない';
$labels['immediately']  = '即時';
$labels['messagesdisplaying'] = 'メッセージの表示';
$labels['messagescomposition'] = 'メッセージの作成';
$labels['mimeparamfolding'] = '添付ファイルの名前';
$labels['2231folding'] = 'RFC 2231に完全準拠(Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231(MS Outlook)';
$labels['2047folding'] = 'RFC 2047に完全準拠(その他)';
$labels['force7bit'] = '8ビット文字列にMIMEエンコードを使用';
$labels['advancedoptions'] = '高度な設定';
$labels['focusonnewmessage'] = '新しいメッセージが届いたらブラウザーをアクティブ';
$labels['checkallfolders'] = 'すべてのフォルダーで新着メールを表示';
$labels['displaynext'] = 'メッセージを削除や移動した後で次のメッセージを表示';
$labels['defaultfont'] = 'HTMLメッセージの初期フォント';
$labels['mainoptions'] = '基本的な設定';
$labels['browseroptions'] = 'ブラウザーのオプション';
$labels['section'] = '設定項目';
$labels['maintenance'] = '保守';
$labels['newmessage'] = '新しいメッセージ';
$labels['signatureoptions'] = '署名の設定';
$labels['whenreplying'] = '返信時の本文';
$labels['replyempty'] = '元のメッセージを引用しない';
$labels['replytopposting'] = '元のメッセージを引用した前に本文を作成';
$labels['replybottomposting'] = '元のメッセージを引用した後に本文を作成';
$labels['replyremovesignature'] = '返信時に元の署名をメッセージから削除';
$labels['autoaddsignature'] = '自動的に署名を付加';
$labels['newmessageonly'] = '新しいメッセージだけ';
$labels['replyandforwardonly'] = '返信と転送だけ';
$labels['replysignaturepos'] = '返信や転送で署名を挿入する位置';
$labels['belowquote'] = '引用の後';
$labels['abovequote'] = '引用の前';
$labels['insertsignature'] = '署名を挿入';
$labels['previewpanemarkread']  = 'プレビューしたメッセージを既読に設定';
$labels['afternseconds']  = '$n秒後';
$labels['reqmdn'] = '常に開封確認通知を要求';
$labels['reqdsn'] = '常に配送状態通知を要求';
$labels['replysamefolder'] = '返信したメッセージを元のメールと同じフォルダに保存';
$labels['defaultabook'] = '初期状態で使用するアドレス帳';
$labels['autocompletesingle'] = '自動補完で代替のメールアドレスを飛ばして進める';
$labels['listnamedisplay'] = '連絡先の一覧表示';
$labels['spellcheckbeforesend'] = 'メッセージを送信する前にスペルチェック';
$labels['spellcheckoptions'] = 'スペルチェックのオプション';
$labels['spellcheckignoresyms'] = '記号を含む単語を無視';
$labels['spellcheckignorenums'] = '数字を含む単語を無視';
$labels['spellcheckignorecaps'] = 'すべて大文字の単語を無視';
$labels['addtodict'] = '辞書に追加';
$labels['mailtoprotohandler'] = 'mailto: のリンクを扱うプロトコル処理の登録';
$labels['forwardmode'] = 'メッセージの転送形式';
$labels['inline'] = 'インライン';
$labels['asattachment'] = '添付ファイル';

$labels['folder']  = 'フォルダー';
$labels['folders']  = 'フォルダー';
$labels['foldername']  = 'フォルダー名';
$labels['subscribed']  = '購読済み';
$labels['messagecount'] = 'メッセージ';
$labels['create']  = '作成';
$labels['createfolder']  = '新しいフォルダーを作成';
$labels['managefolders']  = 'フォルダーを管理';
$labels['specialfolders'] = '特殊なフォルダー';
$labels['properties'] = '属性';
$labels['folderproperties'] = 'フォルダーの属性';
$labels['parentfolder'] = '親のフォルダー';
$labels['location'] = '場所';
$labels['info'] = '情報';
$labels['getfoldersize'] = 'クリックしてフォルダーの大きさを取得';
$labels['changesubscription'] = 'クリックして購読状態を変更';
$labels['foldertype'] = 'フォルダーの種類';
$labels['personalfolder']  = '個人フォルダー';
$labels['otherfolder']  = '他のユーザーのフォルダー';
$labels['sharedfolder']  = '公開フォルダー';

$labels['sortby'] = '並べ替え';
$labels['sortasc']  = '昇順で並べ替え';
$labels['sortdesc'] = '降順で並べ替え';
$labels['undo'] = '取り消し';

$labels['installedplugins'] = 'インストールしているプラグイン';
$labels['plugin'] = 'プラグイン';
$labels['version'] = 'バージョン';
$labels['source'] = 'ソース';
$labels['license'] = 'ライセンス';
$labels['support'] = 'サポートを得る';

// units
$labels['B'] = 'バイト';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = '英語';
$labels['westerneuropean'] = '西ヨーロッパ言語';
$labels['easterneuropean'] = '東ヨーロッパ言語';
$labels['southeasterneuropean'] = '南東ヨーロッパ言語';
$labels['baltic'] = 'バルト諸語';
$labels['cyrillic'] = 'キリル文字';
$labels['arabic'] = 'アラビア語';
$labels['greek'] = 'ギリシャ語';
$labels['hebrew'] = 'ヘブライ語';
$labels['turkish'] = 'トルコ語';
$labels['nordic'] = '北欧言語';
$labels['thai'] = 'タイ語';
$labels['celtic'] = 'ケルト語';
$labels['vietnamese'] = 'ベトナム語';
$labels['japanese'] = '日本語';
$labels['korean'] = '韓国語';
$labels['chinese'] = '中国語';

?>
