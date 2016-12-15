#!/usr/bin/env php
<?php

/*
 +-----------------------------------------------------------------------+
 | bin/userexport.sh                                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2016, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Utility script to export user data to a database-independent        |
 |   format for roundcube migrations.                                    |
 +-----------------------------------------------------------------------+
 | Author: Lukas Erlacher <luke@lerlacher.de>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );
ini_set('memory_limit', -1);

require_once INSTALL_PATH.'program/include/clisetup.php';

function print_usage()
{
	print "Usage:  userexport -u username -h host -l limit\n";
	print "--user   IMAP user name\n";
	print "--host   User IMAP host\n";
	print "--limit  Limit\n";
}

// get arguments
$opts = array('u' => 'user', 'h' => 'host', 'l' => 'limit');
$args = rcube_utils::get_opt($opts);

if ($_SERVER['argv'][1] == 'help')
{
	print_usage();
	exit;
}

$rcmail = rcube::get_instance();

// connect to DB
$db = $rcmail->get_dbh();
$db->db_connect('w');
$transaction = false;

if (!$db->is_connected() || $db->is_error()) {
    _die("No DB connection\n" . $db->is_error());
}

print "Connected DB, querying...\n";

// simple query for user
if (!empty($args['user']) && !empty($args['host'])) {
    print "Simple querying for ${args['user']}@${args['host']}\n";
    $user = rcube_user::query($args['user'], $args['host']);
    $users = [$user];
} else {
    print "Direct querying for ${args['user']}@${args['host']}\n";
    $query = "SELECT * FROM " . $db->table_name('users', true);

    print "Query:" . $query . "\n";
    if (!empty($args['user'])) {
        $query = $query . " WHERE `username` = ?";
        $qarg = $args['user'];
    } else if (!empty($args['mail_host'])) {
        $query = $query . " WHERE `mail_host` = ?";
        $qarg = $args['host'];
    }
    print "Query:" . $query . "\n";

    if (isset($qarg)) {
        if (!empty($args['limit'])) {
            $sql_result = $db->limitquery($query, 0, (int)$args['limit'], $qarg);
        } else {
            $sql_result = $db->query($query, $qarg);
        }
    } else {
        if (!empty($args['limit'])) {
            $sql_result = $db->limitquery($query, 0, (int)$args['limit']);
        } else {
            $sql_result = $db->query($query);
        }
    }

    $users = [];
    while ($sql_arr = $db->fetch_assoc($sql_result)) {
        $users[] = new rcube_user($sql_arr['user_id'], $sql_arr);
    }
}

print "\n";

if (!isset($users) || empty($users)) {
    print "No users found!\n";
} else {
    $users_arr = [];
    foreach ($users as $user) {
        $user_d = [];
        // user table
        $user_d['user'] = array(
            'user_id' => $user->ID,
            'data' => $user->data,
            'language' => $user->language,
        );
        // user identities
        $user_d['identities'] = $user->list_identities();
        // user search
        $user_d['searches'] = $user->list_searches();

        // dictionary - loaded directly from db
        $query = 'SELECT * FROM ' . $db->table_name('dictionary', true) . "WHERE `user_id` = ?";
        $sql_result = $db->query($query, $user->ID);
        $user_d['dictionary'] = array();
        while ( $dict = $db->fetch_assoc($sql_result)) {
            $user_d['dictionary'][] = $dict;
        }

        // contact groups
        $contacts = new rcube_contacts($db, $user->ID);
        $groups = $contacts->list_groups();
        $user_d['groups'] = $groups;

        $c_records = $contacts->list_records();
        foreach ($c_records as $record) {
            $record_groups = $contacts->get_record_groups($record['ID']);
            $user_d['contacts'][] = array(
                'contact' => $record,
                'groups' => $record_groups
            );
        }

        $users_arr[] = $user_d;

    }
    //dump
    print_r($users_arr);
}

