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
	print "Usage:  userexport -f file -u username -h host -l limit -v\n";
	print "--file       Output file\n";
	print "--user       IMAP user name\n";
	print "--host       User IMAP host\n";
	print "--limit      Limit\n";
	print "--verbose    Dump pretty-printed user records \n";
}

function vputs($str)
{
	$out = $GLOBALS['args']['file'] ? STDOUT : STDERR;
	fwrite($out, $str);
}
// get arguments
$opts = array('u' => 'user', 'h' => 'host', 'l' => 'limit', 'f' => 'file', 'v' => 'verbose:');
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

vputs("Connected DB, querying...\n");

// simple query for user
if (!empty($args['user']) && !empty($args['host'])) {
    vputs("Simple querying for ${args['user']}@${args['host']}\n");
    $user = rcube_user::query($args['user'], $args['host']);
    $users = [$user];
} else {
    vputs("Direct querying for " . ((empty($args['user'])) ? '*' : $args['user']) . '@' . ((empty($args['host'])) ? '*' : $args['host']) . "\n");
    $query = "SELECT * FROM " . $db->table_name('users', true);

    if (!empty($args['user'])) {
        $query = $query . " WHERE `username` = ?";
        $qarg = $args['user'];
    } else if (!empty($args['mail_host'])) {
        $query = $query . " WHERE `mail_host` = ?";
        $qarg = $args['host'];
    }

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

if (!isset($users) || empty($users)) {
    vputs("No users found!\n");
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
    vputs("Collected ". count($users_arr) ." users, dumping\n");
    //dump
    if (!empty($args['file'])) {
        $file = fopen($args['file'], 'w');
        if (!$file) {
            print "Could not open ${args['file']} for writing!\n";
            exit;
        }
    } else {
        $file = STDOUT;
    }

    foreach($users_arr as $user_rec) {
        fwrite($file, json_encode($user_rec) . "\n");

        if ($args['verbose']) {
            vputs(print_r($user_rec, true));
        }
    }

    if (!empty($args['file'])) {
        fclose($file);
    }
}

