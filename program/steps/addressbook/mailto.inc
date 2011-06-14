<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/mailto.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2007, The Roundcube Dev Team                            |
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

$cids   = rcmail_get_cids();
$mailto = array();

foreach ($cids as $source => $cid)
{
    $CONTACTS = $RCMAIL->get_address_book($source);

    if ($CONTACTS->ready)
    {
        $CONTACTS->set_page(1);
        $CONTACTS->set_pagesize(count($cid) + 2); // +2 to skip counting query
        $recipients = $CONTACTS->search($CONTACTS->primary_key, $cid, false, true, true, 'email');

        while (is_object($recipients) && ($rec = $recipients->iterate())) {
            $emails = $CONTACTS->get_col_values('email', $rec, true);
            $mailto[] = format_email_recipient($emails[0], $rec['name']);
        }
    }
}

if (!empty($mailto))
{
    $mailto_str = join(', ', $mailto);
    $mailto_id = substr(md5($mailto_str), 0, 16);
    $_SESSION['mailto'][$mailto_id] = urlencode($mailto_str);
    $OUTPUT->redirect(array('task' => 'mail', '_action' => 'compose', '_mailto' => $mailto_id));
}
else {
    $OUTPUT->show_message('nocontactsfound', 'warning');
}

// send response
$OUTPUT->send();
