#!/usr/bin/env php
<?php
/*

 +-----------------------------------------------------------------------+
 | bin/cleandb.php                                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2010, RoundCube Dev. - Switzerland                      |
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

if (!$db->is_connected() || $db->is_error)
  die("No DB connection");

// remove all deleted records older than two days
$threshold = date('Y-m-d 00:00:00', time() - 2 * 86400);

foreach (array('contacts','contactgroups','identities') as $table) {
  // also delete linked records
  // could be skipped for databases which respect foreign key constraints
  if ($table == 'contacts' || $table == 'contactgroups') {
    $ids = array();
    $pk = $primary_keys[$table];

    $result = $db->query(
      "SELECT $pk FROM ".get_table_name($table)."
       WHERE del=1 AND changed < ".$db->quote($threshold));

    while ($result && ($sql_arr = $db->fetch_assoc($result)))
      $ids[] = $sql_arr[$pk];

    if (count($ids)) {
      $db->query(
        "DELETE FROM ".get_table_name('contactgroupmembers')."
         WHERE $pk IN (".join(',', $ids).")");

      echo $db->affected_rows() . " records deleted from '".get_table_name('contactgroupmembers')."'\n";
    }
  }

  // delete outdated records
  $db->query(
    "DELETE FROM ".get_table_name($table)."
     WHERE del=1 AND changed < ".$db->quote($threshold));

  echo $db->affected_rows() . " records deleted from '$table'\n";
}

?>
