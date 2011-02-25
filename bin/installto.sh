#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/installto.sh                                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Update an existing Roundcube installation with files from           |
 |   this version                                                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (php_sapi_name() != 'cli') {
    die('Not on the "shell" (php-cli).');
}
define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/iniset.php';

$target_dir = unslashify($_SERVER['argv'][1]);

if (empty($target_dir) || !is_dir(realpath($target_dir)))
  die("Invalid target: not a directory\nUsage: installto.sh <TARGET>\n");

// read version from iniset.php
$iniset = @file_get_contents($target_dir . '/program/include/iniset.php');
if (!preg_match('/define\(.RCMAIL_VERSION.,\s*.([0-9.]+[a-z-]*)/', $iniset, $m))
  die("No valid Roundcube installation found at $target_dir\n");

$oldversion = $m[1];

if (version_compare($oldversion, RCMAIL_VERSION, '>='))
  die("Installation at target location is up-to-date!\n");

echo "Upgrading from $oldversion. Do you want to continue? (y/N)\n";
$input = trim(fgets(STDIN));

if (strtolower($input) == 'y') {
  $err = false;
  echo "Copying files to target location...";
  foreach (array('program','installer','bin','SQL','plugins','skins/default') as $dir) {
    if (!system("rsync -avuC " . INSTALL_PATH . "$dir/* $target_dir/$dir/")) {
      $err = true;
      break;
    }
  }
  foreach (array('index.php','.htaccess','config/main.inc.php.dist','config/db.inc.php.dist','CHANGELOG','README','UPGRADING') as $file) {
    if (!system("rsync -avu " . INSTALL_PATH . "$file $target_dir/$file")) {
      $err = true;
      break;
    }
  }
  echo "done.\n\n";
  
  if (!$err) {
    echo "Running update script at target...\n";
    system("cd $target_dir && bin/update.sh --version=$oldversion");
    echo "All done.\n";
  }
}
else
  echo "Update cancelled. See ya!\n";

?>
