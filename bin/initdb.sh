#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/initdb.sh                                                         |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010-2015, The Roundcube Dev Team                       |
 | Copyright (C) 2010-2015, Kolab Systems AG                             |
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
$opts = rcube_utils::get_opt(array(
    'd' => 'dir',
));

if (empty($opts['dir'])) {
    rcube::raise_error("Database schema directory not specified (--dir).", false, true);
}

// Check if directory exists
if (!file_exists($opts['dir'])) {
    rcube::raise_error("Specified database schema directory doesn't exist.", false, true);
}

$RC = rcube::get_instance();
$DB = rcube_db::factory($RC->config->get('db_dsnw'));

$DB->set_debug((bool)$RC->config->get('sql_debug'));

// Connect to database
$DB->db_connect('w');
if (!$DB->is_connected()) {
    rcube::raise_error("Error connecting to database: " . $DB->is_error(), false, true);
}

$file = $opts['dir'] . '/' . $DB->db_provider . '.initial.sql';
if (!file_exists($file)) {
    rcube::raise_error("DDL file $file not found", false, true);
}

echo "Creating database schema... ";

if ($sql = file_get_contents($file)) {
    if (!$DB->exec_script($sql)) {
        $error = $DB->is_error();
    }
}
else {
    $error = "Unable to read file $file or it is empty";
}

if ($error) {
    echo "[FAILED]\n";
    rcube::raise_error($error, false, true);
}
else {
    echo "[OK]\n";
}

?>
