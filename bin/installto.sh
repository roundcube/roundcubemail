#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/installto.sh                                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2014-2016, The Roundcube Dev Team                       |
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

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

if (!function_exists('system')) {
  rcube::raise_error("PHP system() function is required. Check disable_functions in php.ini.", false, true);
}

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
  echo "Copying files to target location...";

  $adds = array();
  $dirs = array('program','bin','SQL','plugins','skins');

  if (is_dir(INSTALL_PATH . 'vendor') && !is_file("$target_dir/composer.json")) {
    $dirs[] = 'vendor';
  }
  if (file_exists("$target_dir/installer")) {
    $dirs[] = 'installer';
  }

  foreach ($dirs as $dir) {
    // @FIXME: should we use --delete for all directories?
    $delete  = in_array($dir, array('program', 'vendor', 'installer')) ? '--delete ' : '';
    $command = "rsync -aC --out-format=%n " . $delete . INSTALL_PATH . "$dir/ $target_dir/$dir/";
    if (system($command, $ret) === false || $ret > 0) {
      rcube::raise_error("Failed to execute command: $command", false, true);
    }
  }

  foreach (array('index.php','config/defaults.inc.php','composer.json-dist','jsdeps.json','CHANGELOG','README.md','UPGRADING','LICENSE','INSTALL') as $file) {
    $command = "rsync -a --out-format=%n " . INSTALL_PATH . "$file $target_dir/$file";
    if (file_exists(INSTALL_PATH . $file) && (system($command, $ret) === false || $ret > 0)) {
      rcube::raise_error("Failed to execute command: $command", false, true);
    }
  }

  // Copy .htaccess or .user.ini if needed
  foreach (array('.htaccess','.user.ini') as $file) {
    if (file_exists(INSTALL_PATH . $file)) {
      if (!file_exists("$target_dir/$file") || file_get_contents(INSTALL_PATH . $file) != file_get_contents("$target_dir/$file")) {
        if (copy(INSTALL_PATH . $file, "$target_dir/$file.new")) {
          echo "$file.new\n";
          $adds[] = "NOTICE: New $file file saved as $file.new.";
        }
      }
    }
  }

  // remove old (<1.0) .htaccess file
  @unlink("$target_dir/program/.htaccess");
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

  // check if js-deps are up-to-date
  if (file_exists("$target_dir/jsdeps.json") && file_exists("$target_dir/bin/install-jsdeps.sh")) {
    $jsdeps = json_decode(file_get_contents("$target_dir/jsdeps.json"));
    $package = $jsdeps->dependencies[0];
    $dest_file = $target_dir . '/' . $package->dest;
    if (!file_exists($dest_file) || sha1_file($dest_file) !== $package->sha1) {
        echo "Installing JavaScript dependencies...";
        system("cd $target_dir && bin/install-jsdeps.sh");
        echo "done.\n\n";
    }
  }
  else {
    $adds[] = "NOTICE: JavaScript dependencies installation skipped...";
  }

  if (file_exists("$target_dir/installer")) {
    $adds[] = "NOTICE: The 'installer' directory still exists. You should remove it after the upgrade.";
  }

  if (!empty($adds)) {
    echo implode($adds, "\n") . "\n\n";
  }

  echo "Running update script at target...\n";
  system("cd $target_dir && php bin/update.sh --version=$oldversion");
  echo "All done.\n";
}
else {
  echo "Update cancelled. See ya!\n";
}

?>
