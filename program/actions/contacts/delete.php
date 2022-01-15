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
 |   Delete the submitted contacts (CIDs) from the users address book    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_delete extends rcmail_action_contacts_index
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
        $cids   = self::get_cids(null, rcube_utils::INPUT_POST);
        $delcnt = 0;

        // remove previous deletes
        $undo_time = $rcmail->config->get('undo_timeout', 0);
        $rcmail->session->remove('contact_undo');

        foreach ($cids as $source => $cid) {
            $CONTACTS = self::contact_source($source);

            if ($CONTACTS->readonly && empty($CONTACTS->deletable)) {
                // more sources? do nothing, probably we have search results from
                // more than one source, some of these sources can be readonly
                if (count($cids) == 1) {
                    $rcmail->output->show_message('contactdelerror', 'error');
                    $rcmail->output->command('list_contacts');
                    $rcmail->output->send();
                }
                continue;
            }

            $plugin = $rcmail->plugins->exec_hook('contact_delete', [
                    'id'     => $cid,
                    'source' => $source
            ]);

            $deleted = !$plugin['abort'] ? $CONTACTS->delete($cid, $undo_time < 1) : $plugin['result'];

            if (!$deleted) {
                if (!empty($plugin['message'])) {
                    $error = $plugin['message'];
                }
                else if (($error = $CONTACTS->get_error()) && !empty($error['message'])) {
                    $error = $error['message'];
                }
                else {
                    $error = 'contactdelerror';
                }

                $source = rcube_utils::get_input_string('_source', rcube_utils::INPUT_GP);
                $group  = rcube_utils::get_input_string('_gid', rcube_utils::INPUT_GP);

                $rcmail->output->show_message($error, 'error');
                $rcmail->output->command('list_contacts', $source, $group);
                $rcmail->output->send();
            }
            else {
                $delcnt += $deleted;

                // store deleted contacts IDs in session for undo action
                if ($undo_time > 0 && $CONTACTS->undelete) {
                    $_SESSION['contact_undo']['data'][$source] = $cid;
                }
            }
        }

        if (!empty($_SESSION['contact_undo'])) {
            $_SESSION['contact_undo']['ts'] = time();
            $msg = html::span(null, $rcmail->gettext('contactdeleted'))
                . ' ' . html::a(
                    ['onclick' => rcmail_output::JS_OBJECT_NAME.".command('undo', '', this)"],
                    $rcmail->gettext('undo')
                );

            $rcmail->output->show_message($msg, 'confirmation', null, true, $undo_time);
        }
        else {
            $rcmail->output->show_message('contactdeleted', 'confirmation');
        }

        $page_size = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $page      = $_SESSION['page'] ?? 1;

        // update saved search after data changed
        if (($records = self::search_update(true)) !== false) {
            // create resultset object
            $count  = count($records);
            $first  = ($page-1) * $page_size;
            $result = new rcube_result_set($count, $first);
            $pages  = ceil((count($records) + $delcnt) / $page_size);

            // last page and it's empty, display previous one
            if ($result->count && $result->count <= ($page_size * ($page - 1))) {
                $rcmail->output->command('list_page', 'prev');
                $rowcount = $rcmail->gettext('loading');
            }
            // get records from the next page to add to the list
            else if ($pages > 1 && $page < $pages) {
                // sort the records
                ksort($records, SORT_LOCALE_STRING);

                $first += $page_size;
                // create resultset object
                $res = new rcube_result_set($count, $first - $delcnt);

                if ($page_size < $count) {
                    $records = array_slice($records, $first - $delcnt, $delcnt);
                }

                $res->records = array_values($records);
                $records = $res;
            }
            else {
                unset($records);
            }
        }
        else if (isset($CONTACTS)) {
            // count contacts for this user
            $result = $CONTACTS->count();
            $pages  = ceil(($result->count + $delcnt) / $page_size);

            // last page and it's empty, display previous one
            if ($result->count && $result->count <= ($page_size * ($page - 1))) {
                $rcmail->output->command('list_page', 'prev');
                $rowcount = $rcmail->gettext('loading');
            }
            // get records from the next page to add to the list
            else if ($pages > 1 && $page < $pages) {
                $CONTACTS->set_page($page);
                $records = $CONTACTS->list_records(null, -$delcnt);
            }
        }

        if (!isset($rowcount)) {
            $rowcount = isset($result) ? self::get_rowcount_text($result) : '';
        }

        // update message count display
        $rcmail->output->set_env('pagecount', isset($result) ? ceil($result->count / $page_size) : 0);
        $rcmail->output->command('set_rowcount', $rowcount);

        // add new rows from next page (if any)
        if (!empty($records)) {
            self::js_contacts_list($records);
        }

        // send response
        $rcmail->output->send();
    }
}
