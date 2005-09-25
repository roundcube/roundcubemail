<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/delete.inc                                  |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete the submitted contacts (CIDs) from the users address book    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;

if ($_GET['_cid'])
  {
  $DB->query(sprintf("UPDATE %s
                      SET    del='1'
                      WHERE  user_id=%d
                      AND    contact_id IN (%s)",
                     get_table_name('contacts'),
                     $_SESSION['user_id'],
                     $_GET['_cid']));
                     
  $count = $DB->affected_rows();
  if (!$count)
    {
    // send error message
    exit;
    }


  // count contacts for this user
  $sql_result = $DB->query(sprintf("SELECT COUNT(contact_id) AS rows
                                    FROM   %s
                                    WHERE  del!='1'
                                    AND    user_id=%d",
                                 get_table_name('contacts'),
                                 $_SESSION['user_id']));
                                   
  $sql_arr = $DB->fetch_assoc($sql_result);
  $rowcount = $sql_arr['rows'];    

  // update message count display
  $pages = ceil($rowcount/$CONFIG['pagesize']);
  $commands = sprintf("this.set_rowcount('%s');\n", rcmail_get_rowcount_text($rowcount));
  $commands .= sprintf("this.set_env('pagecount', %d);\n", $pages);


  // add new rows from next page (if any)
  if ($_GET['_from']!='show' && $pages>1 && $_SESSION['page'] < $pages)
    {
    $start_row = ($_SESSION['page'] * $CONFIG['pagesize']) - $count;

    // get contacts from DB
    $sql_result = $DB->query(sprintf("SELECT * FROM %s
                                      WHERE  del!='1'
                                      AND    user_id=%d
                                      ORDER BY name
                                      LIMIT %d, %d",
                                     get_table_name('contacts'),
                                     $_SESSION['user_id'],
                                     $start_row,
                                     $count));
                                     
    $commands .= rcmail_js_contacts_list($sql_result);

/*
    // define list of cols to be displayed
    $a_show_cols = array('name', 'email');
    
    while ($sql_arr = $DB->fetch_assoc($sql_result))
      {
      $a_row_cols = array();
            
      // format each col
      foreach ($a_show_cols as $col)
        {
        $cont = rep_specialchars_output($sql_arr[$col]);
        $a_row_cols[$col] = $cont;
        }
  
      $commands .= sprintf("this.add_contact_row(%s, %s);\n",
                           $sql_arr['contact_id'],
                           array2js($a_row_cols));
      }
*/
    }

  // send response
  rcube_remote_response($commands);
  }

exit;
?>