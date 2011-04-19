<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/delete_identity.inc                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
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
  $plugin = $RCMAIL->plugins->exec_hook('identity_delete', array('id' => $iid));
  
  $deleted = !$plugin['abort'] ? $USER->delete_identity($iid) : $plugin['result'];

  if ($deleted > 0 && $deleted !== false)
    $OUTPUT->show_message('deletedsuccessfully', 'confirmation', null, false);
  else
    $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : ($deleted < 0 ? 'nodeletelastidentity' : 'errorsaving'), 'error', null, false);

  // send response
  if ($OUTPUT->ajax_call)
    $OUTPUT->send();
}

if ($OUTPUT->ajax_call)
  exit;

// go to identities page
rcmail_overwrite_action('identities');
