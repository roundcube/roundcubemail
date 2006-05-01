<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/spell.inc                                          |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Submit request to Google's spell checking engine                    |
 |                                                                       |
 | CREDITS:                                                              |
 |   Script from GoogieSpell by amix.dk                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;

$google = "www.google.com";
$lang = $_GET['lang'];
$path = "/tbproxy/spell?lang=$lang";
$data = file_get_contents('php://input');
$store = "";

if ($fp = fsockopen($google, 80, $errno, $errstr, 30))
  {
  $out = "POST $path HTTP/1.0\r\n";
  $out .= "Host: $google\r\n";
  $out .= "Content-Length: " . strlen($data) . "\r\n";
  $out .= "Content-type: application/x-www-form-urlencoded\r\n";
  $out .= "Connection: Close\r\n\r\n";
  $out .= $data;
  fwrite($fp, $out);
  while (!feof($fp))
    $store .= fgets($fp, 128);
  fclose($fp);
  }

print $store;  
exit;

?>