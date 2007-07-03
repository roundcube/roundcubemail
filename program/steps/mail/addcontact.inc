<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/addcontact.inc                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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

require_once('include/rcube_contacts.inc');

$done = false;

if (!empty($_POST['_address']))
{
  $CONTACTS = new rcube_contacts($DB, $_SESSION['user_id']);
  $contact_arr = $IMAP->decode_address_list(get_input_value('_address', RCUBE_INPUT_POST, true), 1, false);
  
  if (!empty($contact_arr[1]['mailto']))
  {
    $contact = array(
      'email' => $contact_arr[1]['mailto'],
      'name' => $contact_arr[1]['name']
    );
    
    // use email address part for name
    if (empty($contact['name']) || $contact['name'] == $contact['email'])
      $contact['name'] = ucfirst(preg_replace('/[\.\-]/', ' ', substr($contact['email'], 0, strpos($contact['email'], '@'))));

    // check for existing contacts
    $existing = $CONTACTS->search('email', $contact['email'], true, false);
    if ($done = $existing->count)
      $OUTPUT->show_message('contactexists', 'warning');
    else if ($done = $CONTACTS->insert($contact))
      $OUTPUT->show_message('addedsuccessfully', 'confirmation');
  }
}

if (!$done)
  $OUTPUT->show_message('errorsavingcontact', 'warning');

$OUTPUT->send();
?>