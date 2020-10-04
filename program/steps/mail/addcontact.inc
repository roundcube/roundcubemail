<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Add the submitted contact to the users address book                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

// only process ajax requests
if (!$OUTPUT->ajax_call) {
    return;
}

$address = rcube_utils::get_input_value('_address', rcube_utils::INPUT_POST, true);
$source  = rcube_utils::get_input_value('_source', rcube_utils::INPUT_POST);

// Get the default addressbook
$CONTACTS = null;
$SENDERS  = null;
$type     = 0;

if ($source != rcube_addressbook::TYPE_TRUSTED_SENDER) {
    $CONTACTS = $RCMAIL->get_address_book(rcube_addressbook::TYPE_DEFAULT, true);
    $type     = rcube_addressbook::TYPE_DEFAULT;
}

// Get the trusted senders addressbook
if (!empty($_POST['_reload']) || $source == rcube_addressbook::TYPE_TRUSTED_SENDER) {
    $collected_senders = $RCMAIL->config->get('collected_senders');

    if (strlen($collected_senders)) {
        $type |= rcube_addressbook::TYPE_TRUSTED_SENDER;
        $SENDERS = $RCMAIL->get_address_book($collected_senders);
        if ($CONTACTS == $SENDERS) {
            $SENDERS = null;
        }
    }
}

$contact = rcube_mime::decode_address_list($address, 1, false);

if (empty($contact[1]['mailto'])) {
    $OUTPUT->show_message('errorsavingcontact', 'error', null, false);
    $OUTPUT->send();
}

$contact = array(
    'email' => $contact[1]['mailto'],
    'name'  => $contact[1]['name'],
);

$email = rcube_utils::idn_to_ascii($contact['email']);

if (!rcube_utils::check_email($email, false)) {
    $OUTPUT->show_message('emailformaterror', 'error', array('email' => $contact['email']), false);
    $OUTPUT->send();
}

if ($RCMAIL->contact_exists($contact['email'], $type)) {
    $OUTPUT->show_message('contactexists', 'warning');
    $OUTPUT->send();
}

$done = $RCMAIL->contact_create($contact, $SENDERS ?: $CONTACTS, $error);

if ($done) {
    $OUTPUT->show_message('addedsuccessfully', 'confirmation');

    if (!empty($_POST['_reload'])) {
        $OUTPUT->command('command', 'load-remote');
    }
}
else {
    $OUTPUT->show_message($error ?: 'errorsavingcontact', 'error', null, false);
}

$OUTPUT->send();
