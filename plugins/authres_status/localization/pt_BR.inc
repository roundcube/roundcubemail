<?php

$labels = array();
$labels['nosignature'] = 'Sem informação de assinatura';
$labels['noauthresults'] = 'Assinatura encontrada, mas não há resultado da autenticação no cabeçalho. Problema de configuração no servidor?';
$labels['signaturepass'] = 'Assinatura(s) válida(s) do domínio do remetente. Verificado por ';
$labels['partialpass'] = 'Algumas assinaturas são inválidas, mas ao menos uma é válida para o domínio do remetente. Verificado por ';
$labels['thirdparty'] = 'Assinado por um terceiro. Assinatura presente, mas para um domínio diferente do remetente. Verificado por ';
$labels['temporaryinvalid'] = 'Assinatura (temporariamente) inválida. Verificado por ';
$labels['invalidsignature'] = 'Assinatura inválida! Verificado por ';

$labels['for'] = 'para';
$labels['by'] = 'por';

$labels['section_title']  = 'Status da Autenticação';
$labels['column_title']  = 'Resultado da Autenticação';

$labels['title_enable_column']  = 'Habilitar Coluna';
$labels['label_enable_column']  = 'Habilitar coluna com resultado da autenticação na lista de mensagens';
$labels['label_fallback_verifier'] = 'Utilizar verificador DKIM interno (se assinatura for localizada, mas não houver resultado da autenticação no cabeçalho)';

$labels['title_trusted_mtas'] = 'MTA confiável';
$labels['label_trusted_mtas'] = 'Lista de authserv-id separada por vírgulas de MTAs confiáveis. Apenas resultados destes MTAs serão exibidos';

$labels['title_include_status'] = 'Exibir somente os seguintes status';
$labels['label_include_status1']  = 'Nenhuma assinatura encontrada';
$labels['label_include_status2']  = 'Assinatura encontrada, mas não há resultado da autenticação no cabeçalho';
$labels['label_include_status4']  = 'Todas assinaturas são válidas';
$labels['label_include_status8']  = 'Ao menos uma assinatura é válida';
$labels['label_include_status16']  = 'Assinatura por terceiros';
$labels['label_include_status32']  = 'Falha temporária na assinatura';
$labels['label_include_status64']  = 'Falha permanente na assinatura';

?>
