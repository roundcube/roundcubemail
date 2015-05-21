#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/moduserprefs.sh                                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
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

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH.'program/include/clisetup.php';

function print_usage()
{
	print "Usage: moduserprefs.sh [--user=user-id] pref-name [pref-value|--delete]\n";
	print "--user   User ID in local database\n";
	print "--delete Unset the given preference\n";
}


// get arguments
$args = rcube_utils::get_opt(array('u' => 'user', 'd' => 'delete'));

if ($_SERVER['argv'][1] == 'help') {
	print_usage();
	exit;
}
else if (empty($args[0]) || (!isset($args[1]) && !$args['delete'])) {
	print "Missing required parameters.\n";
	print_usage();
	exit;
}

$pref_name  = trim($args[0]);
$pref_value = $args['delete'] ? null : trim($args[1]);

// connect to DB
$rcmail = rcube::get_instance();

$db = $rcmail->get_dbh();
$db->db_connect('w');

if (!$db->is_connected() || $db->is_error())
	die("No DB connection\n" . $db->is_error());

$query = '1=1';

if ($args['user'])
	$query = '`user_id` = ' . intval($args['user']);

// iterate over all users
$sql_result = $db->query("SELECT * FROM " . $db->table_name('users', true) . " WHERE $query");
while ($sql_result && ($sql_arr = $db->fetch_assoc($sql_result))) {
	echo "Updating prefs for user " . $sql_arr['user_id'] . "...";

	$user = new rcube_user($sql_arr['user_id'], $sql_arr);
	$prefs = $old_prefs = $user->get_prefs();

	$prefs[$pref_name] = $pref_value;

	if ($prefs != $old_prefs) {
		$user->save_prefs($prefs, true);
		echo "saved.\n";
	}
	else {
		echo "nothing changed.\n";
	}
}

?>
