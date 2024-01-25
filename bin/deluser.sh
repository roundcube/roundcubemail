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
 |   Utility script to remove all data related to a certain user         |
 |   from the local database.                                            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <thomas@roundcube.net>                       |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

require_once INSTALL_PATH . 'program/include/clisetup.php';

function print_usage()
{
    echo "Usage: deluser.sh [--host=HOST][--age=DAYS][--dry-run] [username]\n";
    echo "--host=HOST  The IMAP hostname or IP the given user is related to\n";
    echo "--age=DAYS   Delete all users who have not logged in for more than X days\n";
    echo "--dry-run    List users but do not delete them (for use with --age)\n";
}

function _die($msg, $usage = false)
{
    fwrite(\STDERR, $msg . "\n");
    if ($usage) {
        print_usage();
    }

    exit(1);
}

$rcmail = rcube::get_instance();

// get arguments
$args = rcube_utils::get_opt(['h' => 'host', 'a' => 'age', 'd' => 'dry-run:bool']);

if (!empty($args['age']) && ($age = intval($args['age']))) {
    $db = $rcmail->get_dbh();
    $db->db_connect('r');

    $query = $db->query('SELECT `username`, `mail_host` FROM ' . $db->table_name('users', true)
        . ' WHERE `last_login` < ' . $db->now($age * -1 * 86400)
        . ($args['host'] ? ' AND `mail_host` = ' . $db->quote($args['host']) : '')
    );

    while ($user = $db->fetch_assoc($query)) {
        if (!empty($args['dry-run'])) {
            printf("%s (%s)\n", $user['username'], $user['mail_host']);
            continue;
        }
        system(sprintf('%s/deluser.sh --host=%s %s', INSTALL_PATH . 'bin', escapeshellarg($user['mail_host']), escapeshellarg($user['username'])));
    }

    exit(0);
}

$hostname = rcmail_utils::get_host($args);
$username = isset($args[0]) ? trim($args[0]) : null;

if (empty($username)) {
    _die('Missing required parameters', true);
}

// connect to DB
$db = $rcmail->get_dbh();
$db->db_connect('w');
$transaction = false;

if (!$db->is_connected() || $db->is_error()) {
    _die("No DB connection\n" . $db->is_error());
}

// find user in local database
$user = rcube_user::query($username, $hostname);

if (!$user) {
    exit("User not found.\n");
}

// inform plugins about approaching user deletion
$plugin = $rcmail->plugins->exec_hook('user_delete_prepare', ['user' => $user, 'username' => $username, 'host' => $hostname]);

// let plugins cleanup their own user-related data
if (!$plugin['abort']) {
    $transaction = $db->startTransaction();
    $plugin = $rcmail->plugins->exec_hook('user_delete', $plugin);
}

if ($plugin['abort']) {
    unset($plugin['abort']);
    if ($transaction) {
        $db->rollbackTransaction();
    }
    _die('User deletion aborted by plugin');
}

$db->query('DELETE FROM ' . $db->table_name('users', true) . ' WHERE `user_id` = ?', $user->ID);

if ($db->is_error()) {
    $rcmail->plugins->exec_hook('user_delete_rollback', $plugin);
    _die('DB error occurred: ' . $db->is_error());
} else {
    // inform plugins about executed user deletion
    $plugin = $rcmail->plugins->exec_hook('user_delete_commit', $plugin);

    if ($plugin['abort']) {
        unset($plugin['abort']);
        $db->rollbackTransaction();
        $rcmail->plugins->exec_hook('user_delete_rollback', $plugin);
    } else {
        $db->endTransaction();
        echo "Successfully deleted user {$user->ID}\n";
    }
}
