<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/addcontact.inc                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Add the submitted contact to the users address book                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = TRUE;

if ($_GET['_address'])
  {
  $contact_arr = $IMAP->decode_address_list($_GET['_address']);
  if (sizeof($contact_arr))
    {
    $contact = $contact_arr[1];

    if ($contact['mailto'])
      $sql_result = $DB->query(sprintf("SELECT 1 FROM %s
                                        WHERE  user_id=%d
                                        AND    email='%s'
                                        AND    del!='1'",
                                       get_table_name('contacts'),
                                       $_SESSION['user_id'],
                                       $contact['mailto']));

    // contact entry with this mail address exists
    if ($sql_result && $DB->num_rows($sql_result))
      $existing_contact = TRUE;

    else if ($contact['mailto'])
      {
      $DB->query(sprintf("INSERT INTO %s
                          (user_id, name, email)
                          VALUES (%d, '%s', '%s')",
                         get_table_name('contacts'),
                         $_SESSION['user_id'],
                         $contact['name'],
                         $contact['mailto']));

      $added = $DB->insert_id();
      }
    }

  if ($added)
    $commands = show_message('addedsuccessfully', 'confirmation');
  else if ($existing_contact)
    $commands = show_message('contactexists', 'warning');
  }


if (!$commands)
  $commands = show_message('errorsavingcontact', 'warning');

rcube_remote_response($commands);  
exit;
?>