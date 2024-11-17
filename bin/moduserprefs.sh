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
 |   Bulk-change settings stored in user preferences                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

require_once INSTALL_PATH . 'program/include/clisetup.php';

function print_usage()
{
    echo "Usage: moduserprefs.sh [options] pref-name [pref-value]\n";
    echo "Options:\n";
    echo "    --user=user-id User ID in local database\n";
    echo "    --config=path  Location of additional configuration file\n";
    echo "    --delete       Unset the given preference\n";
    echo "    --type=type    Pref-value type: int, bool, string\n";
}

// get arguments
$args = rcube_utils::get_opt([
    'u' => 'user',
    'd' => 'delete:bool',
    't' => 'type',
    'c' => 'config',
]);

if (empty($_SERVER['argv'][1]) || $_SERVER['argv'][1] == 'help') {
    print_usage();
    exit;
} elseif (empty($args[0]) || (empty($args[1]) && empty($args['delete']))) {
    echo "Missing required parameters.\n";
    print_usage();
    exit;
}

$pref_name = trim($args[0]);
$pref_value = !empty($args['delete']) ? null : trim($args[1]);

if ($pref_value === null) {
    $args['type'] = null;
}

if (!empty($args['config'])) {
    $rcube = rcube::get_instance();
    $rcube->config->load_from_file($args['config']);
}

$type = $args['type'] ?? null;
$user = $args['user'] ?? null;

rcmail_utils::mod_pref($pref_name, $pref_value, $user, $type);
