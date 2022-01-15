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
 |   Add the submitted contact to the user's address book                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_addcontact extends rcmail_action
{
    // only process ajax requests
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail  = rcmail::get_instance();
        $address = rcube_utils::get_input_string('_address', rcube_utils::INPUT_POST, true);
        $source  = rcube_utils::get_input_string('_source', rcube_utils::INPUT_POST);

        // Get the default addressbook
        $CONTACTS = null;
        $SENDERS  = null;
        $type     = 0;

        if ($source != rcube_addressbook::TYPE_TRUSTED_SENDER) {
            $CONTACTS = $rcmail->get_address_book(rcube_addressbook::TYPE_DEFAULT, true);
            $type     = rcube_addressbook::TYPE_DEFAULT;
        }

        // Get the trusted senders addressbook
        if (!empty($_POST['_reload']) || $source == rcube_addressbook::TYPE_TRUSTED_SENDER) {
            $collected_senders = $rcmail->config->get('collected_senders');

            if (strlen($collected_senders)) {
                $type |= rcube_addressbook::TYPE_TRUSTED_SENDER;
                $SENDERS = $rcmail->get_address_book($collected_senders);
                if ($CONTACTS == $SENDERS) {
                    $SENDERS = null;
                }
            }
        }

        $contact = rcube_mime::decode_address_list($address, 1, false);

        if (empty($contact[1]['mailto'])) {
            $rcmail->output->show_message('errorsavingcontact', 'error', null, false);
            $rcmail->output->send();
        }

        $contact = [
            'email' => $contact[1]['mailto'],
            'name'  => $contact[1]['name'],
        ];

        $email = rcube_utils::idn_to_ascii($contact['email']);

        if (!rcube_utils::check_email($email, false)) {
            $rcmail->output->show_message('emailformaterror', 'error', ['email' => $contact['email']], false);
            $rcmail->output->send();
        }

        if ($rcmail->contact_exists($contact['email'], $type)) {
            $rcmail->output->show_message('contactexists', 'warning');
            $rcmail->output->send();
        }

        $done = $rcmail->contact_create($contact, $SENDERS ?: $CONTACTS, $error);

        if ($done) {
            $rcmail->output->show_message('addedsuccessfully', 'confirmation');

            if (!empty($_POST['_reload'])) {
                $rcmail->output->command('command', 'load-remote');
            }
        }
        else {
            $rcmail->output->show_message($error ?: 'errorsavingcontact', 'error', null, false);
        }

        $rcmail->output->send();
    }
}
