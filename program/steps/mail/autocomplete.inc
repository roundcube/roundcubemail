<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/autocomplete.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008-2009, RoundCube Dev Team                           |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Perform a search on configured address books for the address        |
 |   autocompletion of the message compose screen                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: $

*/

$MAXNUM = 15;  // same limit as in app.js
$contacts = array();

if ($search = get_input_value('_search', RCUBE_INPUT_POST)) {

  foreach ($RCMAIL->config->get('autocomplete_addressbooks', array('sql')) as $id) {
    $abook = $RCMAIL->get_address_book($id);
    $abook->set_pagesize($MAXNUM);

    if ($result = $abook->search(array('email','name'), $search)) {
      while ($sql_arr = $result->iterate()) {
        if (stripos((string)$sql_arr['email'], $search) !== false || stripos((string)$sql_arr['name'], $search) !== false) {
          $contacts[] = format_email_recipient($sql_arr['email'], $sql_arr['name']);
        }
      }
    }
    
    if (count($contacts) >= $MAXNUM)
      break;
  }
  
  sort($contacts);
}

$OUTPUT->command('ksearch_query_results', $contacts);
$OUTPUT->send();

?>