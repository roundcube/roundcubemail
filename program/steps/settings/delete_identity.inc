<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/delete_identity.inc                            |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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

$iid = get_input_value('_iid', RCUBE_INPUT_GPC);

// check request token
if (!$OUTPUT->ajax_call && !$RCMAIL->check_request(RCUBE_INPUT_GPC)) {
  $OUTPUT->show_message('invalidrequest', 'error');
  rcmail_overwrite_action('identities');
  return;
}

if ($iid && preg_match('/^[0-9]+(,[0-9]+)*$/', $iid))
{
  $plugin = $RCMAIL->plugins->exec_hook('delete_identity', array('id' => $iid));
  
  if (!$plugin['abort'] && $USER->delete_identity($iid)) {
    $OUTPUT->show_message('deletedsuccessfully', 'confirmation', null, false);
  }
  else {
    $OUTPUT->show_message('nodeletelastidentity', 'error', null, false);
  }
  // send response
  if ($OUTPUT->ajax_call)
    $OUTPUT->send();
}

if ($OUTPUT->ajax_call)
  exit;

// go to identities page
rcmail_overwrite_action('identities');

?>
