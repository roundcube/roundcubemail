<?php



$labels['enigmasettings'] = 'Enigma';
$labels['enigmacerts'] = 'Enigma: 証明書 (S/MIME)';
$labels['enigmakeys'] = 'Enigma: 鍵 (PGP)';
$labels['keysfromto'] = '鍵の一覧 $from ～ $to (合計: $count )';
$labels['keyname'] = '名前';
$labels['keyid'] = '鍵 ID';
$labels['keyuserid'] = 'ユーザー ID';
$labels['keytype'] = '鍵の種類';
$labels['fingerprint'] = '指紋';
$labels['subkeys'] = 'Subkeys';
$labels['basicinfo'] = '基本情報';
$labels['userids'] = '追加のユーザー ID';
$labels['typepublickey'] = '公開鍵';
$labels['typekeypair'] = '鍵のペア';
$labels['keyattfound'] = 'このメールは PGP 鍵の添付があります。';
$labels['keyattimport'] = '鍵のインポート';
$labels['createkeys'] = '新しい鍵のペアを作成する';
$labels['importkeys'] = '鍵のインポート';
$labels['exportkeys'] = '鍵のエクスポート';
$labels['deletekeys'] = '鍵の削除';
$labels['keyactions'] = '鍵の操作...';
$labels['keydisable'] = '鍵を無効にする';
$labels['keyrevoke'] = '鍵を取り消す';
$labels['keysend'] = 'メッセージに公開鍵を含んで送信する';
$labels['keychpass'] = 'パスワードの変更';
$labels['securityoptions'] = 'メールのセキュリティ オプション...';
$labels['identdefault'] = '選択した識別子の設定を使う';
$labels['encryptmsg'] = 'このメールの暗号化';
$labels['signmsg'] = 'このメールのデジタル署名';
$messages['sigvalid'] = '$sender からの署名を検証しました。';
$messages['siginvalid'] = '$sender からの署名が正しくありません。';
$messages['signokey'] = '署名は未検証です。公開鍵が見つかりません。鍵 ID: $keyid';
$messages['sigerror'] = '署名は未検証です。内部エラーです。';
$messages['decryptok'] = 'メールを復号しました。';
$messages['decrypterror'] = '復号に失敗しました。';
$messages['decryptnokey'] = '復号に失敗しました。秘密鍵が見つかりません。鍵 ID: $keyid.';
$messages['decryptbadpass'] = '復号に失敗しました。パスワードが正しくありません。';
$messages['nokeysfound'] = '鍵が見つかりません。';
$messages['keyopenerror'] = '鍵情報の取得に失敗しました! 内部エラーです。';
$messages['keylisterror'] = '鍵情報のリストに失敗しました! 内部エラーです。';
$messages['keysimportfailed'] = '鍵のインポートに失敗しました! 内部エラーです。';
$messages['keysimportsuccess'] = '鍵をインポートしました。インポート: $new, 未変更: $old';
$messages['keyconfirmdelete'] = '選択した鍵を本当に削除しますか?';
$messages['keyimporttext'] = '秘密鍵と公開鍵のインポート、または ASCII 形式の署名を無効にできます。';
?>
