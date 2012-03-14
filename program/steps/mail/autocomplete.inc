<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/autocomplete.inc                                   |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2011, Roundcube Dev Team                           |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Perform a search on configured address books for the address        |
 |   autocompletion of the message compose screen                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if ($RCMAIL->action == 'group-expand') {
  $abook = $RCMAIL->get_address_book(get_input_value('_source', RCUBE_INPUT_GPC));
  if ($gid = get_input_value('_gid', RCUBE_INPUT_GPC)) {
    $members = array();
    $abook->set_group($gid);
    $abook->set_pagesize(1000);  // TODO: limit number of group members by config
    $result = $abook->list_records(array('name', 'firstname', 'surname', 'email'));
    while ($result && ($sql_arr = $result->iterate())) {
      foreach ((array)$sql_arr['email'] as $email) {
        $members[] = format_email_recipient($email, rcube_addressbook::compose_list_name($sql_arr));
        break;  // only expand one email per contact
      }
    }

    $separator = trim($RCMAIL->config->get('recipients_separator', ',')) . ' ';
    $OUTPUT->command('replace_group_recipients', $gid, join($separator, array_unique($members)));
  }

  $OUTPUT->send();
}


$MAXNUM = (int) $RCMAIL->config->get('autocomplete_max', 15);
$mode   = (int) $RCMAIL->config->get('addressbook_search_mode');
$single = (bool) $RCMAIL->config->get('autocomplete_single');
$search = get_input_value('_search', RCUBE_INPUT_GPC, true);
$source = get_input_value('_source', RCUBE_INPUT_GPC);
$sid    = get_input_value('_id', RCUBE_INPUT_GPC);

if (strlen($source))
  $book_types = array($source);
else
  $book_types = (array) $RCMAIL->config->get('autocomplete_addressbooks', 'sql');

if (!empty($book_types) && strlen($search)) {
  $contacts  = array();
  $sort_keys = array();
  $books_num = count($book_types);
  $search_lc = mb_strtolower($search);

  foreach ($book_types as $id) {
    $abook = $RCMAIL->get_address_book($id);
    $abook->set_pagesize($MAXNUM);

    if ($result = $abook->search(array('name', 'firstname', 'surname', 'email'), $search, $mode, true, true, 'email')) {
      while ($sql_arr = $result->iterate()) {
        // Contact can have more than one e-mail address
        $email_arr = (array)$abook->get_col_values('email', $sql_arr, true);
        $email_cnt = count($email_arr);
        $idx = 0;
        foreach ($email_arr as $email) {
          if (empty($email)) {
            continue;
          }

          $sql_arr['name'] = rcube_addressbook::compose_list_name($sql_arr);
          $contact = format_email_recipient($email, $sql_arr['name']);

          // skip entries that don't match
          if ($email_cnt > 1 && strpos(mb_strtolower($contact), $search_lc) === false) {
            continue;
          }

          // skip duplicates
          if (!in_array($contact, $contacts)) {
            $contacts[]  = $contact;
            $sort_keys[] = sprintf('%s %03d', $sql_arr['name'] , $idx++);

            if (count($contacts) >= $MAXNUM)
              break 2;
          }

          // skip redundant entries (show only first email address)
          if ($single) {
            break;
          }
        }
      }
    }

    // also list matching contact groups
    if ($abook->groups && count($contacts) < $MAXNUM) {
      foreach ($abook->list_groups($search) as $group) {
        $abook->reset();
        $abook->set_group($group['ID']);
        $group_prop = $abook->get_group($group['ID']);

        // group (distribution list) with email address(es)
        if ($group_prop['email']) {
            $idx = 0;
            foreach ((array)$group_prop['email'] as $email) {
                $contacts[]  = format_email_recipient($email, $group['name']);
                $sort_keys[] = sprintf('%s %03d', $group['name'] , $idx++);

                if (count($contacts) >= $MAXNUM)
                  break 2;
            }
        }
        // show group with count
        else if (($result = $abook->count()) && $result->count) {
          $contacts[]  = array('name' => $group['name'] . ' (' . intval($result->count) . ')', 'id' => $group['ID'], 'source' => $id);
          $sort_keys[] = $group['name'];

          if (count($contacts) >= $MAXNUM)
            break;
        }
      }
    }
  }

  if (count($contacts)) {
    // sort contacts index
    asort($sort_keys, SORT_LOCALE_STRING);
    // re-sort contacts according to index
    foreach ($sort_keys as $idx => $val) {
      $sort_keys[$idx] = $contacts[$idx];
    }
    $contacts = array_values($sort_keys);
  }
}

$OUTPUT->command('ksearch_query_results', $contacts, $search, $sid);
$OUTPUT->send();
