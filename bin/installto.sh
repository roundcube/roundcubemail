#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/installto.sh                                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Update an existing Roundcube installation with files from           |
 |   this version                                                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

$target_dir = unslashify($_SERVER['argv'][1]);

if (empty($target_dir) || !is_dir(realpath($target_dir)))
  rcube::raise_error("Invalid target: not a directory\nUsage: installto.sh <TARGET>", false, true);

// read version from iniset.php
$iniset = @file_get_contents($target_dir . '/program/include/iniset.php');
if (!preg_match('/define\(.RCMAIL_VERSION.,\s*.([0-9.]+[a-z-]*)/', $iniset, $m))
  rcube::raise_error("No valid Roundcube installation found at $target_dir", false, true);

$oldversion = $m[1];

if (version_compare(version_parse($oldversion), version_parse(RCMAIL_VERSION), '>='))
  rcube::raise_error("Installation at target location is up-to-date!", false, true);

echo "Upgrading from $oldversion. Do you want to continue? (y/N)\n";
$input = trim(fgets(STDIN));

if (strtolower($input) == 'y') {
  $err = false;
  echo "Copying files to target location...";
  foreach (array('program','installer','bin','SQL','plugins','skins') as $dir) {
    if (!system("rsync -avC " . INSTALL_PATH . "$dir/* $target_dir/$dir/")) {
      $err = true;
      break;
    }
  }
  foreach (array('index.php','.htaccess','config/defaults.inc.php','CHANGELOG','README.md','UPGRADING','LICENSE') as $file) {
    if (!system("rsync -av " . INSTALL_PATH . "$file $target_dir/$file")) {
      $err = true;
      break;
    }
  }
  echo "done.\n\n";

  if (is_dir("$target_dir/skins/default")) {
      echo "Removing old default skin...";
      system("rm -rf $target_dir/skins/default $target_dir/plugins/jqueryui/themes/default");
      foreach (glob(INSTALL_PATH . "plugins/*/skins") as $plugin_skin_dir) {
          $plugin_skin_dir = preg_replace('!^.*' . INSTALL_PATH . '!', '', $plugin_skin_dir);
          if (is_dir("$target_dir/$plugin_skin_dir/classic"))
            system("rm -rf $target_dir/$plugin_skin_dir/default");
      }
      echo "done.\n\n";
  }

  if (!$err) {
    echo "Running update script at target...\n";
    system("cd $target_dir && php bin/update.sh --version=$oldversion");
    echo "All done.\n";
  }
}
else
  echo "Update cancelled. See ya!\n";

?>
