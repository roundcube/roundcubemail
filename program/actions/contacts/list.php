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

class rcmail_action_contacts_list extends rcmail_action_contacts_index
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
        $rcmail = rcmail::get_instance();

        if (!empty($_GET['_page'])) {
            $page = intval($_GET['_page']);
        }
        else {
            $page = !empty($_SESSION['page']) ? $_SESSION['page'] : 1;
        }

        $_SESSION['page'] = $page;

        $page_size  = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $group_data = null;

        // Use search result
        if (($records = self::search_update(true)) !== false) {
            // sort the records
            ksort($records, SORT_LOCALE_STRING);

            // create resultset object
            $count  = count($records);
            $first  = ($page-1) * $page_size;
            $result = new rcube_result_set($count, $first);

            // we need only records for current page
            if ($page_size < $count) {
                $records = array_slice($records, $first, $page_size);
            }

            $result->records = array_values($records);
        }
        // List selected directory
        else {
            $afields  = $rcmail->config->get('contactlist_fields');
            $contacts = self::contact_source(null, true);

            // get contacts for this user
            $result = $contacts->list_records($afields);

            if (!$result->count && $result->searchonly) {
                $rcmail->output->show_message('contactsearchonly', 'notice');
                // Don't invoke advanced search dialog automatically from here (#6679)
            }

            if (!empty($contacts->group_id)) {
                $group_data = ['ID' => $contacts->group_id]
                    + array_intersect_key((array) $contacts->get_group($contacts->group_id), ['name' => 1,'email' => 1]);
            }
        }

        $rcmail->output->command('set_group_prop', $group_data);

        // update message count display
        $rcmail->output->set_env('pagecount', ceil($result->count / $page_size));
        $rcmail->output->command('set_rowcount', self::get_rowcount_text($result));

        // create javascript list
        self::js_contacts_list($result);

        // send response
        $rcmail->output->send();
    }
}
