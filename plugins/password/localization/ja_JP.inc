<?php

//  EN-Revision: 3891

$labels = array();
$labels['changepasswd']  = 'パスワードの変更';
$labels['curpasswd']  = '現在のパスワード:';
$labels['newpasswd']  = '新しいパスワード:';
$labels['confpasswd']  = '新しいパスワード (確認):';

$messages = array();
$messages['nopassword'] = '新しいパスワードを入力してください。';
$messages['nocurpassword'] = '現在のパスワードを入力してください。';
$messages['passwordincorrect'] = '現在のパスワードが間違っています。';
$messages['passwordinconsistency'] = 'パスワードが一致しません。もう一度やり直してください。';
$messages['crypterror'] = 'パスワードを保存できませんでした。暗号化関数がみあたりません。';
$messages['connecterror'] = '新しいパスワードを保存できませんでした。接続エラーです。';
$messages['internalerror'] = '新しいパスワードを保存できませんでした。';
$messages['passwordshort'] = 'パスワードは少なくとも $length 文字の長さが必要です。';
$messages['passwordweak'] = 'パスワードは少なくとも数字を 1 文字と記号が 1 文字含んでなければなりません。';
$messages['passwordforbidden'] = 'パスワードに禁止された文字が含まれています。';

?>
