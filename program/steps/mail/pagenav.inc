<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/pagenav.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Updates message page navigation controls                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: show.inc 4176 2010-11-04 09:59:55Z alec $

*/

$uid = get_input_value('_uid', RCUBE_INPUT_GET);
$cnt  = $IMAP->messagecount(NULL, 'ALL'); // Only messages, no threads here

if ($_SESSION['sort_col'] == 'date' && $_SESSION['sort_order'] != 'DESC'
    && empty($_REQUEST['_search']) && !$CONFIG['skip_deleted'] && !$IMAP->threading
) {
    // this assumes that we are sorted by date_DESC
    $seq   = $IMAP->get_id($uid);
    $index = $cnt - $seq;

    $prev  = $IMAP->get_uid($seq + 1);
    $first = $IMAP->get_uid($cnt);
    $next  = $IMAP->get_uid($seq - 1);
    $last  = $IMAP->get_uid(1);
}
else {
    // Only if we use custom sorting
    $a_msg_index = $IMAP->message_index(NULL, $_SESSION['sort_col'], $_SESSION['sort_order']);

    $index = array_search($IMAP->get_id($uid), $a_msg_index);

    $count = count($a_msg_index);
    $prev  = isset($a_msg_index[$index-1]) ? $IMAP->get_uid($a_msg_index[$index-1]) : -1;
    $first = $count > 1 ? $IMAP->get_uid($a_msg_index[0]) : -1;
    $next  = isset($a_msg_index[$index+1]) ? $IMAP->get_uid($a_msg_index[$index+1]) : -1;
    $last  = $count > 1 ? $IMAP->get_uid($a_msg_index[$count-1]) : -1;
}

// Set UIDs and activate navigation buttons
if ($prev > 0) {
    $OUTPUT->set_env('prev_uid', $prev);
    $OUTPUT->command('enable_command', 'previousmessage', 'firstmessage', true);
}
if ($next > 0) {
    $OUTPUT->set_env('next_uid', $next);
    $OUTPUT->command('enable_command', 'nextmessage', 'lastmessage', true);
}
if ($first > 0)
    $OUTPUT->set_env('first_uid', $first);
if ($last > 0)
    $OUTPUT->set_env('last_uid', $last);

// Don't need a real messages count value
$OUTPUT->set_env('messagecount', 1);

// Set rowcount text
$OUTPUT->command('set_rowcount', rcube_label(array(
    'name' => 'messagenrof',
    'vars' => array('nr'  => $index+1, 'count' => $cnt)
)));

$OUTPUT->send();

