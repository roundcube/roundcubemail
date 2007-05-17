<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/mailto.inc                                  |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Compose a recipient list with all selected contacts                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: copy.inc 471 2007-02-09 21:25:50Z thomasb $

*/

$cid = get_input_value('_cid', RCUBE_INPUT_GET);
$recipients = null;
$mailto = array();

if ($cid && preg_match('/^[a-z0-9\-_=]+(,[a-z0-9\-_=]+)*$/i', $cid) && $CONTACTS->ready)
{
  $recipients = $CONTACTS->search($CONTACTS->primary_key, $cid);

  while (is_object($recipients) && ($rec = $recipients->iterate()))
    $mailto[] = format_email_recipient($rec['email'], $rec['name']);
}

if (!empty($mailto))
{
  $mailto_str = join(', ', $mailto);
  $mailto_id = substr(md5($mailto_str), 0, 16);
  $_SESSION['mailto'][$mailto_id] = $mailto_str;
  $OUTPUT->command('redirect', rcmail_url('compose', array('_mailto' => $mailto_id), 'mail'));
}
else
  $OUTPUT->show_message('nocontactsfound', 'warning');


// send response
$OUTPUT->send();

?>