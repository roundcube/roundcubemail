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
 |   Send contacts list to client (as remote response)                   |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_list_contacts extends rcmail_action_mail_index
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail        = rcmail::get_instance();
        $source        = rcube_utils::get_input_value('_source', rcube_utils::INPUT_GPC);
        $afields       = $rcmail->config->get('contactlist_fields');
        $addr_sort_col = $rcmail->config->get('addressbook_sort_col', 'name');
        $page_size     = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $list_page     = max(1, isset($_GET['_page']) ? intval($_GET['_page']) : 0);
        $jsresult      = [];

        // Use search result
        if (!empty($_REQUEST['_search']) && isset($_SESSION['search'][$_REQUEST['_search']])) {
            $search  = (array) $_SESSION['search'][$_REQUEST['_search']];
            $sparam  = $_SESSION['search_params']['id'] == $_REQUEST['_search'] ? $_SESSION['search_params']['data'] : [];
            $mode    = (int) $rcmail->config->get('addressbook_search_mode');
            $records = [];

            // get records from all sources
            foreach ($search as $s => $set) {
                $CONTACTS = $rcmail->get_address_book($s);

                // list matching groups of this source (on page one)
                if ($sparam[1] && $CONTACTS->groups && $list_page == 1) {
                    $jsresult += self::compose_contact_groups($CONTACTS, $s, $sparam[1], $mode);
                }

                // reset page
                $CONTACTS->set_page(1);
                $CONTACTS->set_pagesize(9999);
                $CONTACTS->set_search_set($set);

                // get records
                $result = $CONTACTS->list_records($afields);

                while ($row = $result->next()) {
                    $row['sourceid'] = $s;
                    $key = rcube_addressbook::compose_contact_key($row, $addr_sort_col);
                    $records[$key] = $row;
                }
                unset($result);
            }

            // sort the records
            ksort($records, SORT_LOCALE_STRING);

            // create resultset object
            $count  = count($records);
            $first  = ($list_page-1) * $page_size;
            $result = new rcube_result_set($count, $first);

            // we need only records for current page
            if ($page_size < $count) {
                $records = array_slice($records, $first, $page_size);
            }

            $result->records = array_values($records);
        }
        // list contacts from selected source
        else {
            $CONTACTS = $rcmail->get_address_book($source);

            if ($CONTACTS && $CONTACTS->ready) {
                // set list properties
                $CONTACTS->set_pagesize($page_size);
                $CONTACTS->set_page($list_page);

                if ($group_id = rcube_utils::get_input_value('_gid', rcube_utils::INPUT_GET)) {
                    $CONTACTS->set_group($group_id);
                }
                // list groups of this source (on page one)
                else if ($CONTACTS->groups && $CONTACTS->list_page == 1) {
                    $jsresult = self::compose_contact_groups($CONTACTS, $source);
                }

                // get contacts for this user
                $result = $CONTACTS->list_records($afields);
            }
        }

        if (!empty($result) && !$result->count && $result->searchonly) {
            $rcmail->output->show_message('contactsearchonly', 'notice');
        }
        else if (!empty($result) && $result->count > 0) {
            // create javascript list
            while ($row = $result->next()) {
                $name = rcube_addressbook::compose_list_name($row);

                // add record for every email address of the contact
                $emails = rcube_addressbook::get_col_values('email', $row, true);
                foreach ($emails as $i => $email) {
                    $source = $row['sourceid'] ?: $source;
                    $row_id = $source.'-'.$row['ID'].'-'.$i;

                    $jsresult[$row_id] = format_email_recipient($email, $name);

                    $classname = $row['_type'] == 'group' ? 'group' : 'person';
                    $keyname   = $row['_type'] == 'group' ? 'contactgroup' : 'contact';

                    $rcmail->output->command('add_contact_row', $row_id, [
                            $keyname => html::a(
                                ['title' => $email],
                                rcube::Q($name ?: $email)
                                . ($name && count($emails) > 1 ? '&nbsp;' . html::span('email', rcube::Q($email)) : '')
                            )
                        ],
                        $classname
                    );
                }
            }
        }

        // update env
        $rcmail->output->set_env('contactdata', $jsresult);
        $rcmail->output->set_env('pagecount', isset($result) ? ceil($result->count / $page_size) : 1);
        $rcmail->output->command('set_page_buttons');

        // send response
        $rcmail->output->send();
    }
}
