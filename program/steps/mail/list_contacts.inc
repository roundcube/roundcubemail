<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list_contacts.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Send contacts list to client (as remote response)                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$afields       = $RCMAIL->config->get('contactlist_fields');
$addr_sort_col = $RCMAIL->config->get('addressbook_sort_col', 'name');
$page_size     = $RCMAIL->config->get('addressbook_pagesize', $RCMAIL->config->get('pagesize', 50));
$list_page     = max(1, intval($_GET['_page']));

// Use search result
if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']])) {
    $search  = (array)$_SESSION['search'][$_REQUEST['_search']];

    // get records from all sources
    foreach ($search as $s => $set) {
        $CONTACTS = $RCMAIL->get_address_book($s);

        // reset page
        $CONTACTS->set_page(1);
        $CONTACTS->set_pagesize(9999);
        $CONTACTS->set_search_set($set);

        // get records
        $result = $CONTACTS->list_records($afields);

        while ($row = $result->next()) {
            $row['sourceid'] = $s;
            $key = rcube_addressbook::compose_contact_key($row, $addr_sort_col);
            $records[$key] = $row;
        }
        unset($result);
    }

    // sort the records
    ksort($records, SORT_LOCALE_STRING);

    // create resultset object
    $count  = count($records);
    $first  = ($list_page-1) * $page_size;
    $result = new rcube_result_set($count, $first);

    // we need only records for current page
    if ($page_size < $count) {
        $records = array_slice($records, $first, $page_size);
    }

    $result->records = array_values($records);
}
// list contacts from selected source
else {
    $source = get_input_value('_source', RCUBE_INPUT_GPC);
    $CONTACTS = $RCMAIL->get_address_book($source);

    if ($CONTACTS && $CONTACTS->ready) {
        // set list properties
        $CONTACTS->set_pagesize($page_size);
        $CONTACTS->set_page($list_page);

        if ($group_id = get_input_value('_gid', RCUBE_INPUT_GPC)) {
            $CONTACTS->set_group($group_id);
        }
        // list groups of this source (on page one)
        else if ($CONTACTS->groups && $CONTACTS->list_page == 1) {
            foreach ($CONTACTS->list_groups() as $group) {
                $CONTACTS->reset();
                $CONTACTS->set_group($group['ID']);
                $group_prop = $CONTACTS->get_group($group['ID']);

                // group (distribution list) with email address(es)
                if ($group_prop['email']) {
                    foreach ((array)$group_prop['email'] as $email) {
                        $row_id = 'G'.$group['ID'];
                        $jsresult[$row_id] = format_email_recipient($email, $group['name']);
                        $OUTPUT->command('add_contact_row', $row_id, array(
                            'contactgroup' => html::span(array('title' => $email), Q($group['name']))), 'group');
                    }
                }
                // make virtual groups clickable to list their members
                else if ($group_prop['virtual']) {
                    $row_id = 'G'.$group['ID'];
                    $OUTPUT->command('add_contact_row', $row_id, array(
                        'contactgroup' => html::a(array(
                            'href' => '#list',
                            'rel' => $row['ID'],
                            'title' => rcube_label('listgroup'),
                            'onclick' => sprintf("return %s.command('pushgroup',{'source':'%s','id':'%s'},this,event)", JS_OBJECT_NAME, $source, $group['ID']),
                        ), Q($group['name']) . '&nbsp;' . html::span('action', '&raquo;'))),
                        'group',
                        array('ID' => $group['ID'], 'name' => $group['name'], 'virtual' => true));
                }
                // show group with count
                else if (($result = $CONTACTS->count()) && $result->count) {
                    $row_id = 'E'.$group['ID'];
                    $jsresult[$row_id] = $group['name'];
                    $OUTPUT->command('add_contact_row', $row_id, array(
                        'contactgroup' => Q($group['name'] . ' (' . intval($result->count) . ')')), 'group');
                }
            }

            $CONTACTS->reset();
            $CONTACTS->set_group(0);
        }

        // get contacts for this user
        $result = $CONTACTS->list_records($afields);
    }
}

if (!empty($result) && !$result->count && $result->searchonly) {
    $OUTPUT->show_message('contactsearchonly', 'notice');
}
else if (!empty($result) && $result->count > 0) {
    // create javascript list
    while ($row = $result->next()) {
        $name = rcube_addressbook::compose_list_name($row);

        // add record for every email address of the contact
        $emails = $CONTACTS->get_col_values('email', $row, true);
        foreach ($emails as $i => $email) {
            $row_id = $row['ID'].$i;
            $jsresult[$row_id] = format_email_recipient($email, $name);
            $classname = $row['_type'] == 'group' ? 'group' : 'person';
            $keyname = $row['_type'] == 'group' ? 'contactgroup' : 'contact';

            $OUTPUT->command('add_contact_row', $row_id, array(
                $keyname => html::span(array('title' => $email), Q($name ? $name : $email) .
                    ($name && count($emails) > 1 ? '&nbsp;' . html::span('email', Q($email)) : '')
                )), $classname);
        }
    }
}


// update env
$OUTPUT->set_env('contactdata', $jsresult);
$OUTPUT->set_env('pagecount', ceil($result->count / $page_size));
$OUTPUT->command('set_page_buttons');

// send response
$OUTPUT->send();
