<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/addcontact.inc                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Add the submitted contact to the users address book                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// only process ajax requests
if (!$OUTPUT->ajax_call)
  return;

$done = false;
$CONTACTS = $RCMAIL->get_address_book(null, true);

if (!empty($_POST['_address']) && is_object($CONTACTS))
{
  $contact_arr = $IMAP->decode_address_list(get_input_value('_address', RCUBE_INPUT_POST, true), 1, false);

  if (!empty($contact_arr[1]['mailto'])) {
    $contact = array(
      'email' => $contact_arr[1]['mailto'],
      'name' => $contact_arr[1]['name']
    );

    // Validity checks
    if (empty($contact['email'])) {
      $OUTPUT->show_message('errorsavingcontact', 'error');
      $OUTPUT->send();
    }
    else if (!check_email($contact['email'], false)) {
      $OUTPUT->show_message('emailformaterror', 'error', array('email' => $contact['email']));
      $OUTPUT->send();
    }

    $contact['email'] = idn_to_utf8($contact['email']);

    // use email address part for name
    if (empty($contact['name']) || $contact['name'] == $contact['email'])
      $contact['name'] = ucfirst(preg_replace('/[\.\-]/', ' ', substr($contact['email'], 0, strpos($contact['email'], '@'))));

    // check for existing contacts
    $existing = $CONTACTS->search('email', $contact['email'], true, false);

    if ($done = $existing->count)
      $OUTPUT->show_message('contactexists', 'warning');
    else {
      $plugin = $RCMAIL->plugins->exec_hook('contact_create', array('record' => $contact, 'source' => null));
      $contact = $plugin['record'];

      $done = !$plugin['abort'] ? $CONTACTS->insert($contact) : $plugin['result'];

      if ($done)
        $OUTPUT->show_message('addedsuccessfully', 'confirmation');
    }
  }
}

if (!$done)
  $OUTPUT->show_message($plugin['message'] ? $plugin['message'] : 'errorsavingcontact', 'error');

$OUTPUT->send();

