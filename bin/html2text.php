<?php

define('INSTALL_PATH', realpath('./../') . '/');
require INSTALL_PATH.'program/include/iniset.php';

$converter = new html2text(html_entity_decode($HTTP_RAW_POST_DATA, ENT_COMPAT, 'UTF-8'));

header('Content-Type: text/plain; charset=UTF-8');
print trim($converter->get_text());

?>
