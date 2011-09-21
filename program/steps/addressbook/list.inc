<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/list.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send contacts list to client (as remote response)                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// Use search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']]))
{
    $search  = (array)$_SESSION['search'][$_REQUEST['_search']];
    $records = array();

    if (!empty($_GET['_page']))
        $page = intval($_GET['_page']);
    else
        $page = isset($_SESSION['page']) ? $_SESSION['page'] : 1;

    $_SESSION['page'] = $page;

    // Get records from all sources
    foreach ($search as $s => $set) {
        $source = $RCMAIL->get_address_book($s);

        // reset page
        $source->set_page(1);
        $source->set_pagesize(9999);
        $source->set_search_set($set);

        // get records
        $result = $source->list_records(array('name', 'email'));

        while ($row = $result->next()) {
            $row['sourceid'] = $s;
            $key = $row['name'] . ':' . $row['sourceid'];
            $records[$key] = $row;
        }
        unset($result);
    }

    // sort the records
    ksort($records, SORT_LOCALE_STRING);

    // create resultset object
    $count  = count($records);
    $first  = ($page-1) * $CONFIG['pagesize'];
    $result = new rcube_result_set($count, $first);

    // we need only records for current page
    if ($CONFIG['pagesize'] < $count) {
        $records = array_slice($records, $first, $CONFIG['pagesize']);
    }

    $result->records = array_values($records);
}
// List selected directory
else {
    $CONTACTS = rcmail_contact_source(null, true);

    // get contacts for this user
    $result = $CONTACTS->list_records(array('name'));
    
    if (!$result->count && $result->searchonly)
        $OUTPUT->show_message('contactsearchonly', 'notice');
}

// update message count display
$OUTPUT->set_env('pagecount', ceil($result->count / $CONFIG['pagesize']));
$OUTPUT->command('set_rowcount', rcmail_get_rowcount_text($result));

// create javascript list
rcmail_js_contacts_list($result);

// send response
$OUTPUT->send();
