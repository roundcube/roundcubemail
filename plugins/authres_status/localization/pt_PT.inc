<?php

$labels = array();
$labels['nosignature'] = 'Nenhuma informação de assinatura';
$labels['noauthresults'] = 'Assinatura encontrada, mas sem cabeçalho de autenticação. Problema de configuração do servidor?';
$labels['signaturepass'] = 'Assinatura(s) válida(s) do domínio do remetente. Verificado por';
$labels['partialpass'] = 'Algumas assinaturas são inválidas, mas pelo menos uma é válida para o domínio do remetente. Verificada por';
$labels['thirdparty'] = 'Assinado por terceiros. A assinatura está presente, mas para o domínio é diferente do endereço. Verificado para';
$labels['temporaryinvalid'] = 'A assinatura não é válida (temporariamente)! Verificado por';
$labels['invalidsignature'] = 'A assinatura não é válida! Verificado por ';

$labels['for'] = 'para';
$labels['by'] = 'por';

$labels['section_title']  = 'Estado da autenticação';
$labels['column_title']  = 'Resultado da autenticação';

$labels['title_enable_column']  = 'Ativar Coluna';
$labels['label_enable_column']  = 'Ativar coluna de resultado da autenticação na lista de mensagens';
$labels['label_fallback_verifier'] = 'Usar o verificador DKIM interno (se for encontrada uma assinatura mas, sem nenhum resultado de autenticação no cabeçalho)';

$labels['title_trusted_mtas'] = 'MTA\'s de confiança';
$labels['label_trusted_mtas'] = 'Lista separada por vírgula de authserv-id de MTA\'s da sua confiança. Apenas os resultados desses MTA\'s será apresentado.';

$labels['title_include_status'] = 'Mostrar apenas os seguintes estados';
$labels['label_include_status1']  = 'Nenhuma assinatura encontrada';
$labels['label_include_status2']  = 'Assinatura encontrada mas sem cabeçalho de autenticação';
$labels['label_include_status4']  = 'Todas as assinaturas passaram na validação';
$labels['label_include_status8']  = 'Pelo menos uma assinatura passou na validação';
$labels['label_include_status16']  = 'Assinatura de terceiros';
$labels['label_include_status32']  = 'Assinatura falhou temporariamente';
$labels['label_include_status64']  = 'Assinatura falhou permanentemente';

?>
