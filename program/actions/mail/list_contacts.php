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
        $source        = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);
        $afields       = $rcmail->config->get('contactlist_fields');
        $addr_sort_col = $rcmail->config->get('addressbook_sort_col', 'name');
        $page_size     = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $list_page     = max(1, $_GET['_page'] ?? 0);
        $jsresult      = [];

        // Use search result
        if (!empty($_REQUEST['_search']) && isset($_SESSION['contact_search'][$_REQUEST['_search']])) {
            $search  = (array) $_SESSION['contact_search'][$_REQUEST['_search']];
            $sparam  = $_SESSION['contact_search_params']['id'] == $_REQUEST['_search'] ? $_SESSION['contact_search_params']['data'] : [];
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

                if ($group_id = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_GET)) {
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
                    $source    = !empty($row['sourceid']) ? $row['sourceid'] : $source;
                    $row_id    = $source.'-'.$row['ID'].'-'.$i;
                    $is_group  = isset($row['_type']) && $row['_type'] == 'group';
                    $classname = $is_group ? 'group' : 'person';
                    $keyname   = $is_group ? 'contactgroup' : 'contact';

                    $jsresult[$row_id] = format_email_recipient($email, $name);

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

    /**
     * Add groups from the given address source to the address book widget
     */
    public static function compose_contact_groups($abook, $source_id, $search = null, $search_mode = 0)
    {
        $rcmail   = rcmail::get_instance();
        $jsresult = [];

        foreach ($abook->list_groups($search, $search_mode) as $group) {
            $abook->reset();
            $abook->set_group($group['ID']);

            // group (distribution list) with email address(es)
            if (!empty($group['email'])) {
                foreach ((array) $group['email'] as $email) {
                    $row_id = 'G'.$group['ID'];
                    $jsresult[$row_id] = format_email_recipient($email, $group['name']);
                    $rcmail->output->command('add_contact_row', $row_id, [
                            'contactgroup' => html::span(['title' => $email], rcube::Q($group['name']))
                        ], 'group');
                }
            }
            // make virtual groups clickable to list their members
            else if (!empty($group['virtual'])) {
                $row_id = 'G'.$group['ID'];
                $rcmail->output->command('add_contact_row', $row_id, [
                        'contactgroup' => html::a([
                                'href' => '#list',
                                'rel' => $group['ID'],
                                'title' => $rcmail->gettext('listgroup'),
                                'onclick' => sprintf("return %s.command('pushgroup',{'source':'%s','id':'%s'},this,event)",
                                    rcmail_output::JS_OBJECT_NAME, $source_id, $group['ID']),
                            ],
                            rcube::Q($group['name']) . '&nbsp;' . html::span('action', '&raquo;')
                    )],
                    'group',
                    ['ID' => $group['ID'], 'name' => $group['name'], 'virtual' => true]
                );
            }
            // show group with count
            else if (($result = $abook->count()) && $result->count) {
                $row_id = 'E'.$group['ID'];
                $jsresult[$row_id] = ['name' => $group['name'], 'source' => $source_id];
                $rcmail->output->command('add_contact_row', $row_id, [
                        'contactgroup' => rcube::Q($group['name'] . ' (' . intval($result->count) . ')')
                    ], 'group');
            }
        }

        $abook->reset();
        $abook->set_group(0);

        return $jsresult;
    }

}
