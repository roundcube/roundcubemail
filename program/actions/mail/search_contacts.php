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
 |   Search contacts from the address book widget                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_search_contacts extends rcmail_action_mail_list_contacts
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
        $search        = rcube_utils::get_input_string('_q', rcube_utils::INPUT_GPC, true);
        $sources       = $rcmail->get_address_sources();
        $search_mode   = (int) $rcmail->config->get('addressbook_search_mode');
        $addr_sort_col = $rcmail->config->get('addressbook_sort_col', 'name');
        $afields       = $rcmail->config->get('contactlist_fields');
        $page_size     = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $records       = [];
        $search_set    = [];
        $jsresult      = [];
        $search_mode  |= rcube_addressbook::SEARCH_GROUPS;

        foreach ($sources as $s) {
            $source = $rcmail->get_address_book($s['id']);
            $source->set_page(1);
            $source->set_pagesize(9999);

            // list matching groups of this source
            if ($source->groups) {
                $jsresult += self::compose_contact_groups($source, $s['id'], $search, $search_mode);
            }

            // get contacts count
            $result = $source->search($afields, $search, $search_mode, true, true, 'email');

            if (!$result->count) {
                continue;
            }

            while ($row = $result->next()) {
                $row['sourceid'] = $s['id'];
                $key = rcube_addressbook::compose_contact_key($row, $addr_sort_col);
                $records[$key] = $row;
            }

            $search_set[$s['id']] = $source->get_search_set();
            unset($result);
        }

        $group_count = count($jsresult);

        // sort the records
        ksort($records, SORT_LOCALE_STRING);

        // create resultset object
        $count  = count($records);
        $result = new rcube_result_set($count);

        // select the requested page
        if ($page_size < $count) {
            $records = array_slice($records, $result->first, $page_size);
        }

        $result->records = array_values($records);

        if (!empty($result) && $result->count > 0) {
            // create javascript list
            while ($row = $result->next()) {
                $name      = rcube_addressbook::compose_list_name($row);
                $is_group  = isset($row['_type']) && $row['_type'] == 'group';
                $classname = $is_group ? 'group' : 'person';
                $keyname   = $is_group ? 'contactgroup' : 'contact';

                // add record for every email address of the contact
                // (same as in list_contacts.inc)
                $emails = rcube_addressbook::get_col_values('email', $row, true);

                foreach ($emails as $i => $email) {
                    $row_id = $row['sourceid'].'-'.$row['ID'].'-'.$i;

                    $jsresult[$row_id] = format_email_recipient($email, $name);

                    $title = rcube_addressbook::compose_search_name($row, $email, $name);
                    $link_content = rcube::Q($name ?: $email);
                    if ($name && count($emails) > 1) {
                        $link_content .= '&nbsp;' . html::span('email', rcube::Q($email));
                    }
                    $link = html::a(['title' => $title], $link_content);

                    $rcmail->output->command('add_contact_row', $row_id, [$keyname => $link], $classname);
                }
            }

            // search request ID
            $search_request = md5('composeaddr' . $search);

            // save search settings in session
            $_SESSION['contact_search'][$search_request] = $search_set;
            $_SESSION['contact_search_params'] = ['id' => $search_request, 'data' => [$afields, $search]];

            $rcmail->output->show_message('contactsearchsuccessful', 'confirmation', ['nr' => $result->count]);

            $rcmail->output->set_env('search_request', $search_request);
            $rcmail->output->set_env('source', '');
            $rcmail->output->command('unselect_directory');
        }
        else if (!$group_count) {
            $rcmail->output->show_message('nocontactsfound', 'notice');
        }

        // update env
        $rcmail->output->set_env('contactdata', $jsresult);
        $rcmail->output->set_env('pagecount', ceil($result->count / $page_size));
        $rcmail->output->command('set_page_buttons');

        // send response
        $rcmail->output->send();
    }
}
