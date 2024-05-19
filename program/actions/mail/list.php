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
 |   Send message list to client (as remote response)                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_list extends rcmail_action_mail_index
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
        $save_arr      = [];
        $dont_override = (array) $rcmail->config->get('dont_override');

        $sort = rcube_utils::get_input_string('_sort', rcube_utils::INPUT_GET);
        $cols = rcube_utils::get_input_string('_cols', rcube_utils::INPUT_GET);
        $layout = rcube_utils::get_input_string('_layout', rcube_utils::INPUT_GET);

        // is there a sort type for this request?
        if ($sort && preg_match('/^[a-zA-Z_-]+$/', $sort)) {
            // yes, so set the sort vars
            list($sort_col, $sort_order) = explode('_', $sort);

            // set session vars for sort (so next page and task switch know how to sort)
            if (!in_array('message_sort_col', $dont_override)) {
                $_SESSION['sort_col'] = $save_arr['message_sort_col'] = $sort_col;
            }
            if (!in_array('message_sort_order', $dont_override)) {
                $_SESSION['sort_order'] = $save_arr['message_sort_order'] = $sort_order;
            }
        }

        // is there a set of columns for this request?
        if ($cols && preg_match('/^[a-zA-Z_,-]+$/', $cols)) {
            $_SESSION['list_attrib']['columns'] = explode(',', $cols);
            if (!in_array('list_cols', $dont_override)) {
                $save_arr['list_cols'] = explode(',', $cols);
            }
        }

        // register layout change
        if ($layout && preg_match('/^[a-zA-Z_-]+$/', $layout)) {
            $rcmail->output->set_env('layout', $layout);
            $save_arr['layout'] = $layout;
            // force header replace on layout change
            if (!empty($_SESSION['list_attrib']['columns'])) {
                $cols = $_SESSION['list_attrib']['columns'];
            }
        }

        if (!empty($save_arr)) {
            $rcmail->user->save_prefs($save_arr);
        }

        $mbox_name = $rcmail->storage->get_folder();
        $threading = (bool) $rcmail->storage->get_threading();

        // Synchronize mailbox cache, handle flag changes
        $rcmail->storage->folder_sync($mbox_name);

        // fetch message headers
        $a_headers = [];
        if ($count = $rcmail->storage->count($mbox_name, $threading ? 'THREADS' : 'ALL', !empty($_REQUEST['_refresh']))) {
            $a_headers = $rcmail->storage->list_messages($mbox_name, null, self::sort_column(), self::sort_order());
        }

        // update search set (possible change of threading mode)
        if (!empty($_REQUEST['_search']) && isset($_SESSION['search'])
            && $_SESSION['search_request'] == $_REQUEST['_search']
        ) {
            $search_request = $_REQUEST['_search'];
            $_SESSION['search'] = $rcmail->storage->get_search_set();
            $multifolder = !empty($_SESSION['search']) && !empty($_SESSION['search'][1]->multi);
        }
        // remove old search data
        else if (empty($_REQUEST['_search']) && isset($_SESSION['search'])) {
            $rcmail->session->remove('search');
        }

        self::list_pagetitle();

        // update mailboxlist
        if (empty($search_request)) {
            self::send_unread_count($mbox_name, !empty($_REQUEST['_refresh']), empty($a_headers) ? 0 : null);
        }

        // update message count display
        $pages  = ceil($count / $rcmail->storage->get_pagesize());
        $page   = $count ? $rcmail->storage->get_page() : 1;
        $exists = $rcmail->storage->count($mbox_name, 'EXISTS', true);

        $rcmail->output->set_env('messagecount', $count);
        $rcmail->output->set_env('pagecount', $pages);
        $rcmail->output->set_env('threading', $threading);
        $rcmail->output->set_env('current_page', $page);
        $rcmail->output->set_env('exists', $exists);
        $rcmail->output->command('set_rowcount', self::get_messagecount_text($count), $mbox_name);

        // remove old message rows if commanded by the client
        if (!empty($_REQUEST['_clear'])) {
            $rcmail->output->command('clear_message_list');
        }

        // add message rows
        self::js_message_list($a_headers, false, $cols);

        if (!empty($a_headers)) {
            if (!empty($search_request)) {
                $rcmail->output->show_message('searchsuccessful', 'confirmation', ['nr' => $count]);
            }

            // remember last HIGHESTMODSEQ value (if supported)
            // we need it for flag updates in check-recent
            $data = $rcmail->storage->folder_data($mbox_name);
            if (!empty($data['HIGHESTMODSEQ'])) {
                $_SESSION['list_mod_seq'] = $data['HIGHESTMODSEQ'];
            }
        }
        else {
            // handle IMAP errors (e.g. #1486905)
            if ($err_code = $rcmail->storage->get_error_code()) {
                self::display_server_error();
            }
            else if (!empty($search_request)) {
                $rcmail->output->show_message('searchnomatch', 'notice');
            }
            else {
                $rcmail->output->show_message('nomessagesfound', 'notice');
            }
        }

        // set trash folder state
        if ($mbox_name === $rcmail->config->get('trash_mbox')) {
            $rcmail->output->command('set_trash_count', $exists);
        }

        if ($page == 1) {
            $rcmail->output->command('set_quota', self::quota_content(null, !empty($multifolder) ? 'INBOX' : $mbox_name));
        }

        // send response
        $rcmail->output->send();
    }
}
