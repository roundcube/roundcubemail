<?php

define('INSTALL_PATH', realpath('./../') . '/');
require INSTALL_PATH.'program/include/iniset.php';

$converter = new html2text($HTTP_RAW_POST_DATA);

header('Content-Type: text/plain; charset=UTF-8');
print html_entity_decode($converter->get_text(), ENT_COMPAT, 'UTF-8');

?>
