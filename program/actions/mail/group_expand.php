<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Expand addressbook group into list of email addresses               |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_group_expand extends rcmail_action
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $gid    = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_GET);
        $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        $abook  = $rcmail->get_address_book($source);

        if ($gid && $abook) {
            $abook->set_group($gid);
            $abook->set_pagesize(9999);  // TODO: limit number of group members by config?

            $result  = $abook->list_records($rcmail->config->get('contactlist_fields'));
            $members = [];

            while ($result && ($record = $result->iterate())) {
                $email = array_first((array) $abook->get_col_values('email', $record, true));
                if (!empty($email)) {
                    $members[] = format_email_recipient($email, rcube_addressbook::compose_list_name($record));
                }
            }

            $rcmail->output->command('replace_group_recipients', $gid, join(', ', array_unique($members)));
        }

        $rcmail->output->send();
    }
}
