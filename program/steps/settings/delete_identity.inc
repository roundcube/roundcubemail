<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/delete_identity.inc                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

if (($ids = get_input_value('_iid', RCUBE_INPUT_GET)) && preg_match('/^[0-9]+(,[0-9]+)*$/', $ids))
{

  if ($USER->delete_identity($ids))
    $OUTPUT->show_message('deletedsuccessfully', 'confirmation');

  // send response
  if ($OUTPUT->ajax_call)
    $OUTPUT->send();
}


if ($OUTPUT->ajax_call)
  exit;


// go to identities page
rcmail_overwrite_action('identities');

?>
