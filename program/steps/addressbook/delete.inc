<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/delete.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Delete the submitted contacts (CIDs) from the users address book    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// process ajax requests only
if (!$OUTPUT->ajax_call)
    return;

$cids   = rcmail_get_cids();
$delcnt = 0;

// remove previous deletes
$undo_time = $RCMAIL->config->get('undo_timeout', 0);
$RCMAIL->session->remove('contact_undo');

foreach ($cids as $source => $cid)
{
    $CONTACTS = rcmail_contact_source($source);

    if ($CONTACTS->readonly) {
        // more sources? do nothing, probably we have search results from
        // more than one source, some of these sources can be readonly
        if (count($cids) == 1) {
            $OUTPUT->show_message('contactdelerror', 'error');
            $OUTPUT->command('list_contacts');
            $OUTPUT->send();
        }
        continue;
    }

    $plugin = $RCMAIL->plugins->exec_hook('contact_delete', array(
        'id' => $cid, 'source' => $source));

    $deleted = !$plugin['abort'] ? $CONTACTS->delete($cid, $undo_time < 1) : $plugin['result'];

    if (!$deleted) {
        $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'contactdelerror', 'error');
        $OUTPUT->command('list_contacts');
        $OUTPUT->send();
    }
    else {
        $delcnt += $deleted;

        // store deleted contacts IDs in session for undo action
        if ($undo_time > 0 && $CONTACTS->undelete) {
            $_SESSION['contact_undo']['data'][$source] = $cid;
        }
    }
}

$page = isset($_SESSION['page']) ? $_SESSION['page'] : 1;

// update saved search after data changed
if (($search_request = $_REQUEST['_search']) && isset($_SESSION['search'][$search_request])) {
    $search  = (array)$_SESSION['search'][$search_request];
    $records = array();

    // Get records from all sources (refresh search)
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

        while ($row = $result->next()) {
            $row['sourceid'] = $s;
            $key = $row['name'] . ':' . $row['sourceid'];
            $records[$key] = $row;
        }
        unset($result);

        $search[$s] = $source->get_search_set();
    }

    $_SESSION['search'][$search_request] = $search;

    // create resultset object
    $count  = count($records);
    $first  = ($page-1) * $CONFIG['pagesize'];
    $result = new rcube_result_set($count, $first);

    // get records from the next page to add to the list
    $pages = ceil((count($records) + $delcnt) / $CONFIG['pagesize']);
    if ($_GET['_from'] != 'show' && $pages > 1 && $page < $pages) {
        // sort the records
        ksort($records, SORT_LOCALE_STRING);

        $first += $CONFIG['pagesize'];
        // create resultset object
        $res = new rcube_result_set($count, $first - $delcnt);

        if ($CONFIG['pagesize'] < $count) {
            $records = array_slice($records, $first - $delcnt, $delcnt);
        }

        $res->records = array_values($records);
        $records = $res;
    }
    else {
        unset($records);
    }
}
else {
    // count contacts for this user
    $result = $CONTACTS->count();

    // get records from the next page to add to the list
    $pages = ceil(($result->count + $delcnt) / $CONFIG['pagesize']);
    if ($_GET['_from'] != 'show' && $pages > 1 && $page < $pages) {
        $CONTACTS->set_page($page);
        $records = $CONTACTS->list_records(null, -$delcnt);
    }
}

// update message count display
$OUTPUT->set_env('pagecount', ceil($result->count / $CONFIG['pagesize']));
$OUTPUT->command('set_rowcount', rcmail_get_rowcount_text($result));

if (!empty($_SESSION['contact_undo'])) {
    $_SESSION['contact_undo']['ts'] = time();
    $msg = html::span(null, rcube_label(array('name' => 'itemsdeleted', 'vars' => array('num' => $deleted))))
        . ' ' . html::a(array('onclick' => JS_OBJECT_NAME.".command('undo', '', this)"), rcube_label('undo'));

    $OUTPUT->show_message($msg, 'confirmation', null, true, $undo_time);
}
else {
    $OUTPUT->show_message('contactdeleted', 'confirmation');
}

// add new rows from next page (if any)
if (!empty($records)) {
    rcmail_js_contacts_list($records);
}

// send response
$OUTPUT->send();
