<?php
/*

 +-----------------------------------------------------------------------+
 | program/steps/utils/killcache.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete rows from cache and messages tables                          |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Dennis P. Nikolaenko <dennis@nikolaenko.ru>                   |
 +-----------------------------------------------------------------------+

 $Id$

*/

// don't allow public access if not in devel_mode
if (!$RCMAIL->config->get('devel_mode')) {
    header("HTTP/1.0 401 Access denied");
    die("Access denied!");
}

$options = array(
    'use_transactions' => false,
    'log_line_break' => "\n",
    'idxname_format' => '%s',
    'debug' => false,
    'quote_identifier' => true,
    'force_defaults' => false,
    'portability' => true
);

// @TODO: transaction here (if supported by DB) would be a good thing
$res = $RCMAIL->db->query("DELETE FROM cache");
if (PEAR::isError($res)) {
    exit($res->getMessage());
}

$res = $RCMAIL->db->query("DELETE FROM messages");
if (PEAR::isError($res)) {
    exit($res->getMessage());
}

echo "Cache cleared\n";
exit;


