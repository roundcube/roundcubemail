<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/delete_identity.inc                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete the submitted identities (IIDs) from the database            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$REMOTE_REQUEST = $_GET['_remote'] ? TRUE : FALSE;

if ($_GET['_iid'])
  {
  $DB->query("UPDATE ".get_table_name('identities')."
              SET    del=1
              WHERE  user_id=?
              AND    identity_id IN (".$_GET['_iid'].")",
              $_SESSION['user_id']);

  $count = $DB->affected_rows();
  if ($count)
    {
    $commands = show_message('deletedsuccessfully', 'confirmation');
    }

  // send response
  if ($REMOTE_REQUEST)
    rcube_remote_response($commands);
  }


if ($REMOTE_REQUEST)
  exit;


// go to identities page
$_action = 'identities';

// overwrite action variable  
$OUTPUT->add_script(sprintf("\n%s.set_env('action', '%s');", $JS_OBJECT_NAME, $_action));
?>