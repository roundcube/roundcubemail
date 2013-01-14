#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/updatedb.sh                                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2010-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Update database schema                                              |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

// get arguments
$opts = rcube_utils::get_opt(array(
    'v' => 'version',
    'd' => 'dir',
    'l' => 'label',
));

if (empty($opts['dir'])) {
  echo "ERROR: Database schema directory not specified (--dir).\n";
  exit(1);
}
if (empty($opts['label'])) {
  echo "ERROR: Database schema label not specified (--label).\n";
  exit(1);
}

// Check if directory exists
if (!file_exists($opts['dir'])) {
  echo "ERROR: Specified database schema directory doesn't exist.\n";
  exit(1);
}

$RC = rcube::get_instance();
$DB = rcube_db::factory($RC->config->get('db_dsnw'));

// Connect to database
$DB->db_connect('w');
if (!$DB->is_connected()) {
    echo "Error connecting to database: " . $DB->is_error() . ".\n";
    exit(1);
}

// Read DB schema version from database (if system table exists)
if (in_array('system', (array)$DB->list_tables())) {
    $DB->query("SELECT " . $DB->quote_identifier('value')
        ." FROM " . $DB->quote_identifier('system')
        ." WHERE " . $DB->quote_identifier('name') ." = ?",
        $opts['label'] . '-version');

    $row     = $DB->fetch_array();
    $version = $row[0];
}

// DB version not found, but release version is specified
if (!$version && $opts['version']) {
    // Map old release version string to DB schema version
    // Note: This is for backward compat. only, do not need to be updated
    $map = array(
        '0.1-stable' => 1,
        '0.1.1'      => 2008030300,
        '0.2-alpha'  => 2008040500,
        '0.2-beta'   => 2008060900,
        '0.2-stable' => 2008092100,
        '0.3-stable' => 2008092100,
        '0.3.1'      => 2009090400,
        '0.4-beta'   => 2009103100,
        '0.4.2'      => 2010042300,
        '0.5-beta'   => 2010100600,
        '0.5'        => 2010100600,
        '0.5.1'      => 2010100600,
        '0.6-beta'   => 2011011200,
        '0.6'        => 2011011200,
        '0.7-beta'   => 2011092800,
        '0.7'        => 2011111600,
        '0.7.1'      => 2011111600,
        '0.7.2'      => 2011111600,
        '0.7.3'      => 2011111600,
        '0.8-beta'   => 2011121400,
        '0.8-rc'     => 2011121400,
        '0.8.0'      => 2011121400,
        '0.8.1'      => 2011121400,
        '0.8.2'      => 2011121400,
        '0.8.3'      => 2011121400,
        '0.8.4'      => 2011121400,
        '0.9-beta'   => 2012080700,
    );

    $version = $map[$opts['version']];
}

// Assume last version before the system table was added
if (empty($version)) {
    $version = 2012080700;
}

$dir = $opts['dir'] . DIRECTORY_SEPARATOR . $DB->db_provider;
if (!file_exists($dir)) {
    echo "DDL Upgrade files for " . $DB->db_provider . " driver not found.\n";
    exit(1);
}

$dh     = opendir($dir);
$result = array();

while ($file = readdir($dh)) {
    if (preg_match('/^([0-9]+)\.sql$/', $file, $m) && $m[1] > $version) {
        $result[] = $m[1];
    }
}
sort($result, SORT_NUMERIC);

foreach ($result as $v) {
    echo "Updating database schema ($v)... ";
    $error = update_db_schema($opts['label'], $v, $dir . DIRECTORY_SEPARATOR . "$v.sql");

    if ($error) {
        echo "\nError in DDL upgrade $v: $error\n";
        exit(1);
    }
    echo "[OK]\n";
}

exit(0);

function update_db_schema($label, $version, $file)
{
    global $DB;

    // read DDL file
    if ($lines = file($file)) {
        $sql = '';
        foreach ($lines as $line) {
            if (preg_match('/^--/', $line) || trim($line) == '')
                continue;

            $sql .= $line . "\n";
            if (preg_match('/(;|^GO)$/', trim($line))) {
                @$DB->query($sql);
                $sql = '';
                if ($error = $DB->is_error()) {
                    return $error;
                }
            }
        }
    }

    $DB->query("UPDATE " . $DB->quote_identifier('system')
        ." SET " . $DB->quote_identifier('value') . " = ?"
        ." WHERE " . $DB->quote_identifier('name') . " = ?",
        $version, $label . '-version');

    if (!$DB->is_error() && !$DB->affected_rows()) {
        $DB->query("INSERT INTO " . $DB->quote_identifier('system')
            ." (" . $DB->quote_identifier('name') . ", " . $DB->quote_identifier('value') . ")"
            ." VALUES (?, ?)",
            $label . '-version', $version);
    }

    return $DB->is_error();
}

?>
