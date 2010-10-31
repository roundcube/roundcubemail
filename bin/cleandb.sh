#!/usr/bin/env php
<?php
/*

 +-----------------------------------------------------------------------+
 | bin/cleandb.sh                                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010, Roundcube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Finally remove all db records marked as deleted some time ago       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (php_sapi_name() != 'cli') {
    die('Not on the "shell" (php-cli).');
}

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );
require INSTALL_PATH.'program/include/iniset.php';

// mapping for table name => primary key
$primary_keys = array(
    'contacts' => "contact_id",
    'contactgroups' => "contactgroup_id",
);

// connect to DB
$RCMAIL = rcmail::get_instance();
$db = $RCMAIL->get_dbh();
$db->db_connect('w');

if (!$db->is_connected() || $db->is_error())
    die("No DB connection\n");

if (!empty($_SERVER['argv'][1]))
    $days = intval($_SERVER['argv'][1]);
else
    $days = 7;

// remove all deleted records older than two days
$threshold = date('Y-m-d 00:00:00', time() - $days * 86400);

foreach (array('contacts','contactgroups','identities') as $table) {

    $sqltable = get_table_name($table);

    // also delete linked records
    // could be skipped for databases which respect foreign key constraints
    if ($db->db_provider == 'sqlite'
        && ($table == 'contacts' || $table == 'contactgroups')
    ) {
        $pk = $primary_keys[$table];
        $memberstable = get_table_name('contactgroupmembers');

        $db->query(
            "DELETE FROM $memberstable".
            " WHERE $pk IN (".
                "SELECT $pk FROM $sqltable".
                " WHERE del=1 AND changed < ?".
            ")",
            $threshold);

        echo $db->affected_rows() . " records deleted from '$memberstable'\n";
    }

    // delete outdated records
    $db->query("DELETE FROM $sqltable WHERE del=1 AND changed < ?", $threshold);

    echo $db->affected_rows() . " records deleted from '$table'\n";
}

?>
