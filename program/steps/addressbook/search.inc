<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/addressbook/search.inc                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Search step for address book contacts                               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: search.inc 456 2007-01-10 12:34:33Z thomasb $

*/

$CONTACTS->set_page(1);
$_SESSION['page'] = 1;

// get input
$search = trim(get_input_value('_q', RCUBE_INPUT_GET, true));
$fields = explode(',', get_input_value('_headers', RCUBE_INPUT_GET));

if (empty($fields)) {
    $fields = $SEARCH_MODS_DEFAULT;
}

$search_request = md5('addr'.$search.implode($fields, ','));

// update search_mods setting
$search_mods = array_fill_keys($fields, 1);
$RCMAIL->user->save_prefs(array('addressbook_search_mods' => $search_mods));

if ($fields['all'] || count($fields) == count($SEARCH_MODS_DEFAULT)) {
    $fields = '*';
}

// get contacts for this user
$result = $CONTACTS->search($fields, $search);

// save search settings in session
$_SESSION['search'][$search_request] = $CONTACTS->get_search_set();

if ($result->count > 0) {
    // create javascript list
    rcmail_js_contacts_list($result);
}
else {
  $OUTPUT->show_message('nocontactsfound', 'notice');
}

// update message count display
$OUTPUT->set_env('search_request', $search_request);
$OUTPUT->set_env('pagecount', ceil($result->count / $CONTACTS->page_size));
$OUTPUT->command('set_rowcount', rcmail_get_rowcount_text());

// send response
$OUTPUT->send();
