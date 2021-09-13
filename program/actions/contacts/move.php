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
 |   Move a contact record from one directory to another                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_contacts_move extends rcmail_action_contacts_index
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
        $cids         = self::get_cids();
        $target       = rcube_utils::get_input_string('_to', rcube_utils::INPUT_POST);
        $target_group = rcube_utils::get_input_string('_togid', rcube_utils::INPUT_POST);

        $rcmail    = rcmail::get_instance();
        $all       = 0;
        $deleted   = 0;
        $success   = 0;
        $errormsg  = 'moveerror';
        $maxnum    = $rcmail->config->get('max_group_members', 0);
        $page_size = $rcmail->config->get('addressbook_pagesize', $rcmail->config->get('pagesize', 50));
        $page      = !empty($_SESSION['page']) ? $_SESSION['page'] : 1;

        foreach ($cids as $source => $source_cids) {
            // Something wrong, target not specified
            if (!strlen($target)) {
                break;
            }

            // It might happen when moving records from search result
            // Do nothing, go to the next source
            if ((string) $target === (string) $source) {
                continue;
            }

            $CONTACTS = $rcmail->get_address_book($source);
            $TARGET   = $rcmail->get_address_book($target);

            if (!$TARGET || !$TARGET->ready || $TARGET->readonly) {
                break;
            }

            if (!$CONTACTS || !$CONTACTS->ready || ($CONTACTS->readonly && empty($CONTACTS->deletable))) {
                continue;
            }

            $ids = [];

            foreach ($source_cids as $idx => $cid) {
                $record = $CONTACTS->get_record($cid, true);

                // avoid moving groups
                if (isset($record['_type']) && $record['_type'] == 'group') {
                    unset($source_cids[$idx]);
                    continue;
                }

                // Check if contact exists, if so, we'll need it's ID
                // Note: Some addressbooks allows empty email address field
                // @TODO: should we check all email addresses?
                $email = $CONTACTS->get_col_values('email', $record, true);

                if (!empty($email)) {
                    $result = $TARGET->search('email', $email[0], 1, true, true);
                }
                else if (!empty($record['name'])) {
                    $result = $TARGET->search('name', $record['name'], 1, true, true);
                }
                else {
                    $result = new rcube_result_set();
                }

                // insert contact record
                if (!$result->count) {
                    $plugin = $rcmail->plugins->exec_hook('contact_create', [
                            'record' => $record,
                            'source' => $target,
                            'group'  => $target_group
                    ]);

                    if (empty($plugin['abort'])) {
                        if ($insert_id = $TARGET->insert($plugin['record'], false)) {
                            $ids[] = $insert_id;
                            $success++;
                        }
                    }
                    else if (!empty($plugin['result'])) {
                        $ids = array_merge($ids, $plugin['result']);
                        $success++;
                    }
                }
                else {
                    $record   = $result->first();
                    $ids[]    = $record['ID'];
                    $errormsg = empty($email) ? 'contactnameexists' : 'contactexists';
                }
            }

            // remove source contacts
            if ($success && !empty($source_cids)) {
                $all   += count($source_cids);
                $plugin = $rcmail->plugins->exec_hook('contact_delete', [
                        'id'     => $source_cids,
                        'source' => $source
                ]);

                $del_status = !$plugin['abort'] ? $CONTACTS->delete($source_cids) : $plugin['result'];

                if ($del_status) {
                    $deleted += $del_status;
                }
            }

            // assign to group
            if ($target_group && $TARGET->groups && !empty($ids)) {
                $plugin = $rcmail->plugins->exec_hook('group_addmembers', [
                        'group_id' => $target_group,
                        'ids'      => $ids,
                        'source'   => $target
                ]);

                if (empty($plugin['abort'])) {
                    $TARGET->reset();
                    $TARGET->set_group($target_group);

                    if ($maxnum && ($TARGET->count()->count + count($plugin['ids']) > $maxnum)) {
                        $rcmail->output->show_message('maxgroupmembersreached', 'warning', ['max' => $maxnum]);
                        $rcmail->output->send();
                    }

                    if (($cnt = $TARGET->add_to_group($target_group, $plugin['ids'])) && $cnt > $success) {
                        $success = $cnt;
                    }
                }
                else if ($plugin['result']) {
                    $success = $plugin['result'];
                }

                $errormsg = !empty($plugin['message']) ? $plugin['message'] : 'moveerror';
            }
        }

        if (!$deleted || $deleted != $all) {
            $rcmail->output->command('list_contacts');
        }
        else {
            // update saved search after data changed
            if (($records = self::search_update(true)) !== false) {
                // create resultset object
                $count  = count($records);
                $first  = ($page-1) * $page_size;
                $result = new rcube_result_set($count, $first);
                $pages  = ceil((count($records) + $deleted) / $page_size);

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
                    $res = new rcube_result_set($count, $first - $deleted);

                    if ($page_size < $count) {
                        $records = array_slice($records, $first - $deleted, $deleted);
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
                $pages  = ceil(($result->count + $deleted) / $page_size);

                // last page and it's empty, display previous one
                if ($result->count && $result->count <= ($page_size * ($page - 1))) {
                    $rcmail->output->command('list_page', 'prev');
                    $rowcount = $rcmail->gettext('loading');
                }
                // get records from the next page to add to the list
                else if ($pages > 1 && $page < $pages) {
                    $CONTACTS->set_page($page);
                    $records = $CONTACTS->list_records(null, -$deleted);
                }
            }

            if (!isset($rowcount)) {
                $rowcount = isset($result) ? self::get_rowcount_text($result) : 0;
            }

            // update message count display
            $rcmail->output->set_env('pagecount', isset($result) ? ceil($result->count / $page_size) : 0);
            $rcmail->output->command('set_rowcount', $rowcount);

            // add new rows from next page (if any)
            if (!empty($records)) {
                self::js_contacts_list($records);
            }
        }

        if (!$success) {
            $rcmail->output->show_message($errormsg, 'error');
        }
        else {
            $rcmail->output->show_message('movesuccess', 'confirmation', ['nr' => $success]);
        }

        // send response
        $rcmail->output->send();
    }
}
