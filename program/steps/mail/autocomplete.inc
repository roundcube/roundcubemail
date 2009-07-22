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

$MAXNUM = 15;
$contacts = array();
$book_types = (array) $RCMAIL->config->get('autocomplete_addressbooks', 'sql');

if ($book_types && $search = get_input_value('_search', RCUBE_INPUT_POST, true)) {

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
  }
  
  sort($contacts);
}

$OUTPUT->command('ksearch_query_results', $contacts, $search);
$OUTPUT->send();

?>
