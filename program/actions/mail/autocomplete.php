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
 |   Perform a search on configured address books for the email          |
 |   address autocompletion                                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_autocomplete extends rcmail_action
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
        $MAXNUM = (int) $rcmail->config->get('autocomplete_max', 15);
        $mode   = (int) $rcmail->config->get('addressbook_search_mode');
        $single = (bool) $rcmail->config->get('autocomplete_single');
        $search = rcube_utils::get_input_string('_search', rcube_utils::INPUT_GPC, true);
        $reqid  = rcube_utils::get_input_string('_reqid', rcube_utils::INPUT_GPC);

        $contacts = [];

        if (strlen($search) && ($book_types = self::autocomplete_addressbooks())) {
            $sort_keys = [];
            $books_num = count($book_types);
            $search_lc = mb_strtolower($search);
            $mode     |= rcube_addressbook::SEARCH_GROUPS;
            $fields    = $rcmail->config->get('contactlist_fields');

            foreach ($book_types as $abook_id) {
                $abook = $rcmail->get_address_book($abook_id);
                $abook->set_pagesize($MAXNUM);

                if ($result = $abook->search($fields, $search, $mode, true, true, 'email')) {
                    while ($record = $result->iterate()) {
                        // Contact can have more than one e-mail address
                        $email_arr = (array) $abook->get_col_values('email', $record, true);
                        $email_cnt = count($email_arr);
                        $idx       = 0;

                        foreach ($email_arr as $email) {
                            if (empty($email)) {
                                continue;
                            }

                            $name    = rcube_addressbook::compose_list_name($record);
                            $contact = format_email_recipient($email, $name);

                            // skip entries that don't match
                            if ($email_cnt > 1 && strpos(mb_strtolower($contact), $search_lc) === false) {
                                continue;
                            }

                            $index = $contact;

                            // skip duplicates
                            if (empty($contacts[$index])) {
                                $contact = [
                                    'name'   => $contact,
                                    'type'   => $record['_type'] ?? null,
                                    'id'     => $record['ID'],
                                    'source' => $abook_id,
                                ];

                                $display = rcube_addressbook::compose_search_name($record, $email, $name);

                                if ($display && $display != $contact['name']) {
                                    $contact['display'] = $display;
                                }

                                // groups with defined email address will not be expanded to its members' addresses
                                if ($contact['type'] == 'group') {
                                    $contact['email'] = $email;
                                }

                                $name              = !empty($contact['display']) ? $contact['display'] : $name;
                                $contacts[$index]  = $contact;
                                $sort_keys[$index] = sprintf('%s %03d', $name, $idx++);

                                if (count($contacts) >= $MAXNUM) {
                                    break 2;
                                }
                            }

                            // skip redundant entries (show only first email address)
                            if ($single) {
                                break;
                            }
                        }
                    }
                }

                // also list matching contact groups
                if ($abook->groups && count($contacts) < $MAXNUM) {
                    foreach ($abook->list_groups($search, $mode) as $group) {
                        $abook->reset();
                        $abook->set_group($group['ID']);

                        $group_prop = $abook->get_group($group['ID']);

                        // group (distribution list) with email address(es)
                        if (!empty($group_prop['email'])) {
                            $idx = 0;
                            foreach ((array) $group_prop['email'] as $email) {
                                $index = format_email_recipient($email, $group['name']);

                                if (empty($contacts[$index])) {
                                    $sort_keys[$index] = sprintf('%s %03d', $group['name'] , $idx++);
                                    $contacts[$index]  = [
                                        'name'   => $index,
                                        'email'  => $email,
                                        'type'   => 'group',
                                        'id'     => $group['ID'],
                                        'source' => $abook_id,
                                    ];

                                    if (count($contacts) >= $MAXNUM) {
                                        break 3;
                                    }
                                }
                            }
                        }
                        // show group with count
                        else if (($result = $abook->count()) && $result->count) {
                            if (empty($contacts[$group['name']])) {
                                $sort_keys[$group['name']] = $group['name'];
                                $contacts[$group['name']]  = [
                                    'name'   => $group['name'] . ' (' . intval($result->count) . ')',
                                    'type'   => 'group',
                                    'id'     => $group['ID'],
                                    'source' => $abook_id,
                                ];

                                if (count($contacts) >= $MAXNUM) {
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            if (count($contacts)) {
                // sort contacts index
                asort($sort_keys, SORT_LOCALE_STRING);
                // re-sort contacts according to index
                foreach ($sort_keys as $idx => $val) {
                    $sort_keys[$idx] = $contacts[$idx];
                }
                $contacts = array_values($sort_keys);
            }
        }

        // Allow autocomplete result optimization via plugin
        $plugin = $rcmail->plugins->exec_hook('contacts_autocomplete_after', [
                'search'   => $search,
                // Provide already-found contacts to plugin if they are required
                'contacts' => $contacts,
        ]);

        $contacts = $plugin['contacts'];

        $rcmail->output->command('ksearch_query_results', $contacts, $search, $reqid);
        $rcmail->output->send();
    }

    /**
     * Collect addressbook sources used for autocompletion
     */
    public static function autocomplete_addressbooks()
    {
        $rcmail = rcmail::get_instance();
        $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GPC);

        if (strlen($source)) {
            $book_types = [$source];
        }
        else {
            $book_types = (array) $rcmail->config->get('autocomplete_addressbooks', 'sql');
        }

        $collected_recipients = $rcmail->config->get('collected_recipients');
        $collected_senders    = $rcmail->config->get('collected_senders');

        if (strlen($collected_recipients) && !in_array($collected_recipients, $book_types)) {
            $book_types[] = $collected_recipients;
        }

        if (strlen($collected_senders) && !in_array($collected_senders, $book_types)) {
            $book_types[] = $collected_senders;
        }

        return !empty($book_types) ? $book_types : null;
    }
}
