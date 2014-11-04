#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/indexcontacts.sh                                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Update the fulltext index for all contacts of the internal          |
 |   address book.                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH.'program/include/clisetup.php';
ini_set('memory_limit', -1);

// connect to DB
$RCMAIL = rcube::get_instance();

$db = $RCMAIL->get_dbh();
$db->db_connect('w');

if (!$db->is_connected() || $db->is_error()) {
    rcube::raise_error("No DB connection", false, true);
}

// iterate over all users
$sql_result = $db->query("SELECT `user_id` FROM " . $db->table_name('users', true) . " ORDER BY `user_id`");
while ($sql_result && ($sql_arr = $db->fetch_assoc($sql_result))) {
    echo "Indexing contacts for user " . $sql_arr['user_id'] . "...";

    $contacts = new rcube_contacts($db, $sql_arr['user_id']);
    $contacts->set_pagesize(9999);

    $result = $contacts->list_records();
    while ($result->count && ($row = $result->next())) {
        unset($row['words']);
        $contacts->update($row['ID'], $row);
    }

    echo "done.\n";
}

?>
