<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/undo.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Undelete contacts (CIDs) from last delete action                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+

 $Id$

*/

// process ajax requests only
if (!$OUTPUT->ajax_call)
    return;

$undo   = $_SESSION['contact_undo'];
$delcnt = 0;

foreach ((array)$undo['data'] as $source => $cid)
{
    $CONTACTS = rcmail_contact_source($source);

    $plugin = $RCMAIL->plugins->exec_hook('contact_undelete', array(
        'id' => $cid, 'source' => $source));

    $restored = !$plugin['abort'] ? $CONTACTS->undelete($cid) : $plugin['result'];

    if (!$restored) {
        $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'contactrestoreerror', 'error');
        $OUTPUT->command('list_contacts');
        $OUTPUT->send();
    }
    else {
        $delcnt += $restored;
    }
}

// update saved search after data changed
if ($delcnt && ($search_request = $_REQUEST['_search']) && isset($_SESSION['search'][$search_request])) {
    $search  = (array)$_SESSION['search'][$search_request];

    foreach ($search as $s => $set) {
        $source = $RCMAIL->get_address_book($s);

        // reset page
        $source->set_page(1);
        $source->set_pagesize(9999);
        $source->set_search_set($set);

        // get records
        $result = $source->list_records(array('name', 'email'));

        if (!$result->count) {
            unset($search[$s]);
            continue;
        }

        $search[$s] = $source->get_search_set();
    }

    $_SESSION['search'][$search_request] = $search;
}

$RCMAIL->session->remove('contact_undo');

$OUTPUT->show_message('contactrestored', 'confirmation');
$OUTPUT->command('list_contacts');

// send response
$OUTPUT->send();
