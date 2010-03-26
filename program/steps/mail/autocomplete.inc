<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/autocomplete.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008-2010, RoundCube Dev Team                           |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Perform a search on configured address books for the address        |
 |   autocompletion of the message compose screen                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

$MAXNUM = 15;
$contacts = array();
$book_types = (array) $RCMAIL->config->get('autocomplete_addressbooks', 'sql');

if ($book_types && $search = get_input_value('_search', RCUBE_INPUT_GPC, true)) {

  foreach ($book_types as $id) {
    $abook = $RCMAIL->get_address_book($id);
    $abook->set_pagesize($MAXNUM);

    if ($result = $abook->search(array('email','name'), $search)) {
      while ($sql_arr = $result->iterate()) {
          $contacts[] = format_email_recipient($sql_arr['email'], $sql_arr['name']);
          if (count($contacts) >= $MAXNUM)
            break 2;
      }
    }
    
    // also list matching contact groups
    if ($abook->groups) {
      foreach ($abook->list_groups($search) as $group) {
        $members = array();
        $abook->reset();
        $abook->set_group($group['ID']);
        $result = $abook->list_records(array('email','name'));
        while ($result && ($sql_arr = $result->iterate()))
          $members[] = format_email_recipient($sql_arr['email'], $sql_arr['name']);
        
        if (count($members)) {
          $contacts[] = array('name' => $group['name'] . ' (' . rcube_label('group') . ')', 'members' => $members);
          if (count($contacts) >= $MAXNUM)
            break;
        }
      }
    }
  }
  
  usort($contacts, 'contact_results_sort');
}

$OUTPUT->command('ksearch_query_results', $contacts, $search);
$OUTPUT->send();


function contact_results_sort($a, $b)
{
  $name_a = is_array($a) ? $a['name'] : $a;
  $name_b = is_array($b) ? $b['name'] : $b;
  return strcmp(trim($name_a, '" '), trim($name_b, '" '));
}

?>
