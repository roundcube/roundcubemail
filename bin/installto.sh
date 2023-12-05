#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
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

$target_dir = unslashify(end($_SERVER['argv']));
$accept = in_array('-y', $_SERVER['argv']) ? 'y' : null;

if (empty($target_dir) || !is_dir(realpath($target_dir))) {
    rcube::raise_error("Invalid target: not a directory\nUsage: installto.sh [-y] <TARGET>", false, true);
}

// read version from iniset.php
$iniset = @file_get_contents($target_dir . '/program/include/iniset.php');
if (!preg_match('/define\(.RCMAIL_VERSION.,\s*.([0-9.]+[a-z0-9-]*)/', $iniset, $m)) {
    rcube::raise_error("No valid Roundcube installation found at $target_dir", false, true);
}

$oldversion = $m[1];

if (version_compare(version_parse($oldversion), version_parse(RCMAIL_VERSION), '>')) {
    rcube::raise_error("Target installation already in version $oldversion.", false, true);
}

if (version_compare(version_parse($oldversion), version_parse(RCMAIL_VERSION), '==')) {
    echo "Target installation already in version $oldversion. Do you want to update again? (y/N)\n";
}
else {
    echo "Upgrading from $oldversion. Do you want to continue? (y/N)\n";
}

$input = $accept ?: trim(fgets(STDIN));

if (strtolower($input) == 'y') {
    echo "Copying files to target location...";

    $adds = [];
    $dirs = ['bin','SQL','plugins','skins','program'];

    if (is_dir(INSTALL_PATH . 'vendor') && (!is_file("$target_dir/composer.json") || rcmail_install::vendor_dir_untouched($target_dir))) {
        $dirs[] = 'vendor';
    }
    if (file_exists("$target_dir/installer")) {
        $dirs[] = 'installer';
    }

    foreach ($dirs as $dir) {
        // @FIXME: should we use --delete for all directories?
        $delete  = in_array($dir, ['program', 'vendor', 'installer']) ? '--delete ' : '';
        $command = "rsync -aC --out-format=%n " . $delete . INSTALL_PATH . "$dir/ $target_dir/$dir/";

        if (system($command, $ret) === false || $ret > 0) {
            rcube::raise_error("Failed to execute command: $command", false, true);
        }
    }

    foreach (['index.php','config/defaults.inc.php','composer.json-dist','jsdeps.json','CHANGELOG.md','README.md','UPGRADING','LICENSE','INSTALL'] as $file) {
        $command = "rsync -a --out-format=%n " . INSTALL_PATH . "$file $target_dir/$file";

        if (file_exists(INSTALL_PATH . $file) && (system($command, $ret) === false || $ret > 0)) {
            rcube::raise_error("Failed to execute command: $command", false, true);
        }
    }

    // Copy .htaccess or .user.ini if needed
    foreach (['.htaccess','.user.ini'] as $file) {
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
            if (is_dir("$target_dir/$plugin_skin_dir/classic")) {
                system("rm -rf $target_dir/$plugin_skin_dir/default");
            }
        }
        echo "done.\n\n";
    }

    // Warn about situation when using "complete" package to update "custom" installation (#7087)
    // Note: "Complete" package do not include jsdeps.json nor install-jsdeps.sh
    if (file_exists("$target_dir/jsdeps.json") && !file_exists(INSTALL_PATH . "jsdeps.json")) {
        $adds[] = "WARNING: JavaScript dependencies update skipped. New jsdeps.json file not found.";
    }
    // check if js-deps are up-to-date
    else if (file_exists("$target_dir/jsdeps.json") && file_exists("$target_dir/bin/install-jsdeps.sh")) {
        $jsdeps    = json_decode(file_get_contents("$target_dir/jsdeps.json"));
        $package   = $jsdeps->dependencies[0];
        $dest_file = $target_dir . '/' . $package->dest;

        if (!file_exists($dest_file) || sha1_file($dest_file) !== $package->sha1) {
            echo "Installing JavaScript dependencies...";
            system("cd $target_dir && bin/install-jsdeps.sh");
            echo "done.\n\n";
        }
    }

    if (file_exists("$target_dir/installer")) {
        $adds[] = "NOTICE: The 'installer' directory still exists. You should remove it after the upgrade.";
    }

    if (!empty($adds)) {
        echo implode("\n", $adds) . "\n\n";
    }

    echo "Running update script at target...\n";
    system("cd $target_dir && bin/update.sh --version=$oldversion" . ($accept ? ' -y' : ''));
    echo "All done.\n";
}
else {
    echo "Update cancelled. See ya!\n";
}
