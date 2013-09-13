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
    'p' => 'package',
));

if (empty($opts['dir'])) {
    rcube::raise_error("Database schema directory not specified (--dir).", false, true);
}
if (empty($opts['package'])) {
    rcube::raise_error("Database schema package name not specified (--package).", false, true);
}

// Check if directory exists
if (!file_exists($opts['dir'])) {
    rcube::raise_error("Specified database schema directory doesn't exist.", false, true);
}

$RC = rcube::get_instance();
$DB = rcube_db::factory($RC->config->get('db_dsnw'));

// Connect to database
$DB->db_connect('w');
if (!$DB->is_connected()) {
    rcube::raise_error("Error connecting to database: " . $DB->is_error(), false, true);
}

// Read DB schema version from database (if 'system' table exists)
if (in_array($DB->table_name('system'), (array)$DB->list_tables())) {
    $DB->query("SELECT " . $DB->quote_identifier('value')
        ." FROM " . $DB->quote_identifier($DB->table_name('system'))
        ." WHERE " . $DB->quote_identifier('name') ." = ?",
        $opts['package'] . '-version');

    $row     = $DB->fetch_array();
    $version = preg_replace('/[^0-9]/', '', $row[0]);
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
        '0.2.1'      => 2008092100,
        '0.2.2'      => 2008092100,
        '0.3-stable' => 2008092100,
        '0.3.1'      => 2009090400,
        '0.4-beta'   => 2009103100,
        '0.4'        => 2010042300,
        '0.4.1'      => 2010042300,
        '0.4.2'      => 2010042300,
        '0.5-beta'   => 2010100600,
        '0.5'        => 2010100600,
        '0.5.1'      => 2010100600,
        '0.5.2'      => 2010100600,
        '0.5.3'      => 2010100600,
        '0.5.4'      => 2010100600,
        '0.6-beta'   => 2011011200,
        '0.6'        => 2011011200,
        '0.7-beta'   => 2011092800,
        '0.7'        => 2011111600,
        '0.7.1'      => 2011111600,
        '0.7.2'      => 2011111600,
        '0.7.3'      => 2011111600,
        '0.7.4'      => 2011111600,
        '0.8-beta'   => 2011121400,
        '0.8-rc'     => 2011121400,
        '0.8.0'      => 2011121400,
        '0.8.1'      => 2011121400,
        '0.8.2'      => 2011121400,
        '0.8.3'      => 2011121400,
        '0.8.4'      => 2011121400,
        '0.8.5'      => 2011121400,
        '0.8.6'      => 2011121400,
        '0.9-beta'   => 2012080700,
    );

    $version = $map[$opts['version']];
}

// Assume last version before the 'system' table was added
if (empty($version)) {
    $version = 2012080700;
}

$dir = $opts['dir'] . DIRECTORY_SEPARATOR . $DB->db_provider;
if (!file_exists($dir)) {
    rcube::raise_error("DDL Upgrade files for " . $DB->db_provider . " driver not found.", false, true);
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
    $error = update_db_schema($opts['package'], $v, $dir . DIRECTORY_SEPARATOR . "$v.sql");

    if ($error) {
        echo "[FAILED]\n";
        rcube::raise_error("Error in DDL upgrade $v: $error", false, true);
    }
    echo "[OK]\n";
}


function update_db_schema($package, $version, $file)
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
                @$DB->query(fix_table_names($sql));
                $sql = '';
                if ($error = $DB->is_error()) {
                    return $error;
                }
            }
        }
    }

    // escape if 'system' table does not exist
    if ($version < 2013011000) {
        return;
    }

    $system_table = $DB->quote_identifier($DB->table_name('system'));

    $DB->query("UPDATE " . $system_table
        ." SET " . $DB->quote_identifier('value') . " = ?"
        ." WHERE " . $DB->quote_identifier('name') . " = ?",
        $version, $package . '-version');

    if (!$DB->is_error() && !$DB->affected_rows()) {
        $DB->query("INSERT INTO " . $system_table
            ." (" . $DB->quote_identifier('name') . ", " . $DB->quote_identifier('value') . ")"
            ." VALUES (?, ?)",
            $package . '-version', $version);
    }

    return $DB->is_error();
}

function fix_table_names($sql)
{
    global $DB;

    foreach (array('users','identities','contacts','contactgroups','contactgroupmembers','session','cache','cache_index','cache_index','cache_messages','dictionary','searches','system') as $table) {
        $real_table = $DB->table_name($table);
        if ($real_table != $table) {
            $sql = preg_replace("/([^a-z0-9_])$table([^a-z0-9_])/i", "\\1$real_table\\2", $sql);
        }
    }

    return $sql;
}

?>
