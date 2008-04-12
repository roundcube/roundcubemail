<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/sendmdn.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send a message disposition notification for a specific mail         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: sendmail.inc 930 2007-11-25 17:34:19Z thomasb $

*/


if (!empty($_POST['_uid'])) {
  $sent = rcmail_send_mdn(get_input_value('_uid', RCUBE_INPUT_POST));
}

// show either confirm or error message
if ($sent) {
  $OUTPUT->set_env('mdn_request', false);
  $OUTPUT->show_message('receiptsent', 'confirmation');
}
else {
  $OUTPUT->show_message('errorsendingreceipt', 'error');
}

$OUTPUT->send();

?>