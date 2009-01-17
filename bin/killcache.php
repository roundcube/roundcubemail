<?php
/*

 +-----------------------------------------------------------------------+
 | bin/killcache.php                                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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

define('INSTALL_PATH', realpath(dirname(__FILE__).'/..') . '/');
require INSTALL_PATH.'program/include/iniset.php';

$config = new rcube_config();

// don't allow public access if not in devel_mode
if (!$config->get('devel_mode') && $_SERVER['REMOTE_ADDR']) {
	header("HTTP/1.0 401 Access denied");
	die("Access denied!");
}


$dbh =& MDB2::factory($config->get('db_dsnw'), $options);
if (PEAR::isError($dbh)) {
        exit($mdb2->getMessage());
}

//TODO: transaction here (if supported by DB) would be a good thing
$res =& $dbh->exec("DELETE FROM cache");
if (PEAR::isError($res)) {
  $dbh->disconnect();
  exit($res->getMessage());
};

$res =& $dbh->exec("DELETE FROM messages");
if (PEAR::isError($res)) {
  $dbh->disconnect();
  exit($res->getMessage());
};

echo "Cache cleared\n";

$dbh->disconnect();

?>
