#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Create database schema                                              |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

// get arguments
$opts = rcube_utils::get_opt([
    'd' => 'dir',
    'u' => 'update'
]);

if (empty($opts['dir'])) {
    rcube::raise_error("Database schema directory not specified (--dir).", false, true);
}

// Check if directory exists
if (!file_exists($opts['dir'])) {
    rcube::raise_error("Specified database schema directory doesn't exist.", false, true);
}

$db = rcmail_utils::db();

if (!empty($opts['update']) && in_array($db->table_name('system'), (array)$db->list_tables())) {
    echo "Checking for database schema updates..." . PHP_EOL;
    rcmail_utils::db_update($opts['dir'], 'roundcube', null, ['errors' => true]);
} else {
    rcmail_utils::db_init($opts['dir']);
}
