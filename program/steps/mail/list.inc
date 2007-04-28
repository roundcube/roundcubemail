<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list.inc                                           |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send message list to client (as remote response)                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$OUTPUT_TYPE = 'js';
// is there a sort type for this request?
if ($sort = get_input_value('_sort', RCUBE_INPUT_GET))
{
  // yes, so set the sort vars
  list($sort_col, $sort_order) = explode('_', $sort);

  // set session vars for sort (so next page and task switch know how to sort)
  $_SESSION['sort_col'] = $sort_col;
  $_SESSION['sort_order'] = $sort_order;
}
else
{
  // use session settings if set, defaults if not
  $sort_col   = isset($_SESSION['sort_col'])   ? $_SESSION['sort_col']   : $CONFIG['message_sort_col'];
  $sort_order = isset($_SESSION['sort_order']) ? $_SESSION['sort_order'] : $CONFIG['message_sort_order'];
}


// fetch message headers
if ($count = $IMAP->messagecount())
  $a_headers = $IMAP->list_headers($mbox_name, NULL, $sort_col, $sort_order);

$unseen = $IMAP->messagecount($mbox_name, 'UNSEEN', !empty($_GET['_refresh']) ? TRUE : FALSE);

// update message count display
$pages = ceil($count/$IMAP->page_size);
$OUTPUT->set_env('messagecount', $count);
$OUTPUT->set_env('pagecount', $pages);
$OUTPUT->command('set_rowcount', rcmail_get_messagecount_text($count));

// update mailboxlist
$OUTPUT->command('set_unread_count', $IMAP->get_mailbox_name(), $unseen);


// add message rows
if (isset($a_headers) && count($a_headers))
  rcmail_js_message_list($a_headers);

  
// send response
$OUTPUT->send();

?>