<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/copy.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2007, The Roundcube Dev Team                            |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Copy a contact record from one direcotry to another                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: copy.inc 471 2007-02-09 21:25:50Z thomasb $

*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;


$cids         = rcmail_get_cids();
$target       = get_input_value('_to', RCUBE_INPUT_POST);
$target_group = get_input_value('_togid', RCUBE_INPUT_POST);

$success  = 0;
$errormsg = 'copyerror';
$maxnum   = $RCMAIL->config->get('max_group_members', 0);

foreach ($cids as $source => $cid)
{
    // Something wrong, target not specified
    if (!strlen($target)) {
        break;
    }

    // It maight happen when copying records from search result
    // Do nothing, go to next source
    if ((string)$target == (string)$source) {
        continue;
    }

    $CONTACTS = $RCMAIL->get_address_book($source);
    $TARGET   = $RCMAIL->get_address_book($target);

    if (!$TARGET || !$TARGET->ready || $TARGET->readonly) {
        break;
    }

    $ids = array();

    foreach ($cid as $cid) {
        $a_record = $CONTACTS->get_record($cid, true);

        // Check if contact exists, if so, we'll need it's ID
        // Note: Some addressbooks allows empty email address field
        if (!empty($a_record['email']))
            $result = $TARGET->search('email', $a_record['email'], true, true, true);
        else if (!empty($a_record['name']))
            $result = $TARGET->search('name', $a_record['name'], true, true, true);
        else
            $result = new rcube_result_set();

        // insert contact record
        if (!$result->count) {
            $plugin = $RCMAIL->plugins->exec_hook('contact_create', array(
                'record' => $a_record, 'source' => $target, 'group' => $target_group));

            if (!$plugin['abort']) {
                if ($insert_id = $TARGET->insert($plugin['record'], false)) {
                    $ids[] = $insert_id;
                    $success++;
                }
            }
            else if ($plugin['result']) {
                $ids = array_merge($ids, $plugin['result']);
                $success++;
            }
        }
        else {
            $record = $result->first();
            $ids[] = $record['ID'];
            $errormsg = empty($a_record['email']) ? 'contactnameexists' : 'contactexists';
        }
    }

    // assign to group
    if ($target_group && $TARGET->groups && !empty($ids)) {
        $plugin = $RCMAIL->plugins->exec_hook('group_addmembers', array(
            'group_id' => $target_group, 'ids' => $ids, 'source' => $target));

        if (!$plugin['abort']) {
            $TARGET->reset();
            $TARGET->set_group($target_group);

            if ($maxnum && ($TARGET->count()->count + count($plugin['ids']) > $maxnum)) {
                $OUTPUT->show_message('maxgroupmembersreached', 'warning', array('max' => $maxnum));
                $OUTPUT->send();
            }

            if (($cnt = $TARGET->add_to_group($target_group, $plugin['ids'])) && $cnt > $success)
                $success = $cnt;
        }
        else if ($plugin['result']) {
            $success = $plugin['result'];
        }

        $errormsg = $plugin['message'] ? $plugin['message'] : 'copyerror';
    }
}

if ($success == 0)
    $OUTPUT->show_message($errormsg, 'error');
else
    $OUTPUT->show_message('copysuccess', 'notice', array('nr' => $success));

// send response
$OUTPUT->send();
