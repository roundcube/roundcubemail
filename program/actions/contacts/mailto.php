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
 |   Compose a recipient list with all selected contacts                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_mailto extends rcmail_action_contacts_index
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
        $cids    = self::get_cids();
        $mailto  = [];
        $sources = [];

        foreach ($cids as $source => $cid) {
            $contacts = $rcmail->get_address_book($source);

            if ($contacts->ready) {
                $contacts->set_page(1);
                $contacts->set_pagesize(count($cid) + 2); // +2 to skip counting query
                $sources[] = $contacts->search($contacts->primary_key, $cid, 0, true, true, 'email');
            }
        }

        if (!empty($_REQUEST['_gid']) && isset($_REQUEST['_source'])) {
            $source   = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GP);
            $group_id = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_GP);

            $contacts   = $rcmail->get_address_book($source);
            $group_data = $contacts->get_group($group_id);

            // group has an email address assigned: use that
            if (!empty($group_data['email'])) {
                $mailto[] = format_email_recipient($group_data['email'][0], $group_data['name']);
            }
            else if ($contacts->ready) {
                $maxnum = (int) $rcmail->config->get('max_group_members');

                $contacts->set_group($group_id);
                $contacts->set_page(1);
                $contacts->set_pagesize($maxnum ?: 999);
                $sources[] = $contacts->list_records();
            }
        }

        foreach ($sources as $source) {
            while (is_object($source) && ($rec = $source->iterate())) {
                $emails = rcube_addressbook::get_col_values('email', $rec, true);

                if (!empty($emails)) {
                    $mailto[] = format_email_recipient($emails[0], $rec['name']);
                }
            }
        }

        if (!empty($mailto)) {
            $mailto_str = join(', ', $mailto);
            $mailto_id  = substr(md5($mailto_str), 0, 16);
            $_SESSION['mailto'][$mailto_id] = urlencode($mailto_str);
            $rcmail->output->command('open_compose_step', ['_mailto' => $mailto_id]);
        }
        else {
            $rcmail->output->show_message('nocontactsfound', 'warning');
        }

        // send response
        $rcmail->output->send();
    }
}
