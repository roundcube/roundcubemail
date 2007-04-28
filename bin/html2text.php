<?php

require_once('../program/lib/html2text.inc');

$htmlText = $HTTP_RAW_POST_DATA;
$converter = new html2text($htmlText);

header('Content-Type: text/plain; charset=UTF-8');
$plaintext = $converter->get_text();

if (function_exists('html_entity_decode'))
	print html_entity_decode($plaintext, ENT_COMPAT, 'UTF-8');
else
	print $plaintext;

?>