#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/moduserprefs.sh                                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2012-2015, The Roundcube Dev Team                       |
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

rcmail_utils::mod_pref($pref_name, $pref_value, $args['user']);

?>
