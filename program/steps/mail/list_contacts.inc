<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/list_contacts.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2012, The Roundcube Dev Team                            |
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

 $Id$

*/

$jsenv = array();
$source = get_input_value('_source', RCUBE_INPUT_GPC);
$CONTACTS = $RCMAIL->get_address_book($source);
$PAGE_SIZE = $RCMAIL->config->get('addressbook_pagesize', $RCMAIL->config->get('pagesize', 50));

if ($CONTACTS && $CONTACTS->ready) {
    // set list properties
    $CONTACTS->set_pagesize($PAGE_SIZE);
    $CONTACTS->set_page(max(1, intval($_GET['_page'])));

    // list groups of this source (on page one)
    if ($CONTACTS->groups && $CONTACTS->list_page == 1) {
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
                        'contactgroup' => html::span(array('title' => $email), Q($group['name']))));
                }
            }
            // show group with count
            else if (($result = $CONTACTS->count()) && $result->count) {
                $row_id = 'E'.$group['ID'];
                $jsresult[$row_id] = $group['name'];
                $OUTPUT->command('add_contact_row', $row_id, array(
                    'contactgroup' => Q($group['name'] . ' (' . intval($result->count) . ')')));
            }
        }
    }

    // get contacts for this user
    $CONTACTS->set_group(0);
    $result = $CONTACTS->list_records(array('name', 'email'));

    if (!$result->count && $result->searchonly) {
        $OUTPUT->show_message('contactsearchonly', 'notice');
    }
    else if (!empty($result) && $result->count > 0) {
        // create javascript list
        while ($row = $result->next()) {
            $name = rcube_addressbook::compose_display_name($row, true);

            // add record for every email address of the contact
            foreach ($CONTACTS->get_col_values('email', $row, true) as $i => $email) {
                $row_id = $row['ID'].$i;
                $jsresult[$row_id] = format_email_recipient($email, $name);
                $OUTPUT->command('add_contact_row', $row_id, array(
                    'contact' => html::span(array('title' => $email), Q($name ? $name : $email))));
            }
        }
    }
}

// update env
$OUTPUT->set_env('contactdata', $jsresult);
$OUTPUT->set_env('pagecount', ceil($result->count / $PAGE_SIZE));
$OUTPUT->command('set_page_buttons');

// send response
$OUTPUT->send();
