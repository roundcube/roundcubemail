<?php
/*

 +-----------------------------------------------------------------------+
 | bin/html2text.php                                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Convert HTML message to plain text                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/');
require INSTALL_PATH . 'program/include/iniset.php';

$RCMAIL = rcmail::get_instance();

if (!empty($RCMAIL->user->ID)) {
  $converter = new html2text($HTTP_RAW_POST_DATA);

  header('Content-Type: text/plain; charset=UTF-8');
  print trim($converter->get_text());
}
else {
  header("HTTP/1.0 403 Forbidden");
  echo "Requires a valid user session";
}

?>
