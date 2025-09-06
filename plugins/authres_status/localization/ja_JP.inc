<?php

$labels = array();
$labels['nosignature'] = '署名の情報がありません。';
$labels['noauthresults'] = '署名がありますが、認証情報の結果のヘッダーがありません。サーバーの構成の問題かもしれません?';
$labels['signaturepass'] = '送信者のドメインからの有効な(1つ以上の)署名です。次で検証: ';
$labels['partialpass'] = '正しくない署名もありますが、少なくとも1つは送信者のドメインに有効な署名です。次で検証: ';
$labels['thirdparty'] = '第三者による署名で、発信者のアドレスと異なるドメインの署名が存在します。次を検証:';
$labels['temporaryinvalid'] = '署名は(一時的に)次で検証できません: ';
$labels['invalidsignature'] = '署名は次で検証できません: ';

$labels['for'] = '対象: ';
$labels['by'] = '署名者: ';

$labels['section_title']  = '認証の状態';
$labels['column_title']  = '認証結果';

$labels['title_enable_column']  = '列を有効';
$labels['label_enable_column']  = 'メッセージの一覧で認証結果の列を有効にします。';
$labels['label_fallback_verifier'] = '内蔵のDKIMの検証を使用(署名があり、認証結果のヘッダーがない場合)';

$labels['title_trusted_mtas'] = '信頼するMTA';
$labels['label_trusted_mtas'] = '信頼するMTAのauthserv-idをコンマで区切ったリストです。これらのMTAからの結果だけ表示します。';

$labels['title_include_status'] = '以下の署名だけ表示';
$labels['label_include_status1']  = '署名がありません。';
$labels['label_include_status2']  = '署名がありますが、認証情報の結果のヘッダーがありません。';
$labels['label_include_status4']  = 'すべて検証した署名';
$labels['label_include_status8']  = '少なくとも1つは検証した署名';
$labels['label_include_status16']  = '第三者の署名';
$labels['label_include_status32']  = '署名を一時的に検証不可';
$labels['label_include_status64']  = '署名は永続的に検証不可';

?>
