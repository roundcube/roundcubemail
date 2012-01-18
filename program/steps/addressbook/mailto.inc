<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/mailto.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2007, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
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
$recipients = null;

foreach ($cids as $source => $cid)
{
    $CONTACTS = $RCMAIL->get_address_book($source);

    if ($CONTACTS->ready)
    {
        $CONTACTS->set_page(1);
        $CONTACTS->set_pagesize(count($cid) + 2); // +2 to skip counting query
        $recipients = $CONTACTS->search($CONTACTS->primary_key, $cid, 0, true, true, 'email');
    }
}

if (!empty($_REQUEST['_gid']) && isset($_REQUEST['_source']))
{
    $source = get_input_value('_source', RCUBE_INPUT_GPC);
    $CONTACTS = $RCMAIL->get_address_book($source);
    
    $group_id = get_input_value('_gid', RCUBE_INPUT_GPC);
    $group_data = $CONTACTS->get_group($group_id);
    
    // group has an email address assigned: use that
    if ($group_data['email']) {
        $mailto[] = format_email_recipient($group_data['email'][0], $group_data['name']);
    }
    else if ($CONTACTS->ready) {
        $CONTACTS->set_group($group_id);
        $CONTACTS->set_page(1);
        $CONTACTS->set_pagesize(200); // limit somehow
        $recipients = $CONTACTS->list_records();
    }
}

if ($recipients)
{
    while (is_object($recipients) && ($rec = $recipients->iterate())) {
        $emails = $CONTACTS->get_col_values('email', $rec, true);
        $mailto[] = format_email_recipient($emails[0], $rec['name']);
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
