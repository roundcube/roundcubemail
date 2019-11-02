<?php

/**
 * Archive
 *
 * Plugin that adds a new button to the mailbox toolbar
 * to move messages to a (user selectable) archive folder.
 *
 * @version 3.2
 * @license GNU GPLv3+
 * @author Andre Rodier, Thomas Bruederli, Aleksander Machniak
 */
class archive extends rcube_plugin
{
    public $task = 'settings|mail|login';

    private $archive_folder;
    private $folders;


    /**
     * Plugin initialization.
     */
    function init()
    {
        $rcmail = rcmail::get_instance();

        // register special folder type
        rcube_storage::$folder_types[] = 'archive';

        $this->archive_folder = $rcmail->config->get('archive_mbox');

        if ($rcmail->task == 'mail' && ($rcmail->action == '' || $rcmail->action == 'show') && $this->archive_folder) {
            $this->include_stylesheet($this->local_skin_path() . '/archive.css');
            $this->include_script('archive.js');
            $this->add_texts('localization', true);
            $this->add_button(
                array(
                    'type'     => 'link',
                    'label'    => 'buttontext',
                    'command'  => 'plugin.archive',
                    'class'    => 'button buttonPas archive disabled',
                    'classact' => 'button archive',
                    'width'    => 32,
                    'height'   => 32,
                    'title'    => 'buttontitle',
                    'domain'   => $this->ID,
                    'innerclass' => 'inner',
                ),
                'toolbar');

            // register hook to localize the archive folder
            $this->add_hook('render_mailboxlist', array($this, 'render_mailboxlist'));

            // set env variables for client
            $rcmail->output->set_env('archive_folder', $this->archive_folder);
            $rcmail->output->set_env('archive_type', $rcmail->config->get('archive_type',''));
        }
        else if ($rcmail->task == 'mail') {
            // handler for ajax request
            $this->register_action('plugin.move2archive', array($this, 'move_messages'));
        }
        else if ($rcmail->task == 'settings') {
            $this->add_hook('preferences_list', array($this, 'prefs_table'));
            $this->add_hook('preferences_save', array($this, 'save_prefs'));

            if ($rcmail->action == 'folders' && $this->archive_folder) {
                $this->include_stylesheet($this->local_skin_path() . '/archive.css');
                $this->include_script('archive.js');
                // set env variables for client
                $rcmail->output->set_env('archive_folder', $this->archive_folder);
            }
        }
    }

    /**
     * Hook to give the archive folder a localized name in the mailbox list
     */
    function render_mailboxlist($p)
    {
        // set localized name for the configured archive folder
        if ($this->archive_folder && !rcmail::get_instance()->config->get('show_real_foldernames')) {
            if (isset($p['list'][$this->archive_folder])) {
                $p['list'][$this->archive_folder]['name'] = $this->gettext('archivefolder');
            }
            else {
                // search in subfolders
                $this->_mod_folder_name($p['list'], $this->archive_folder, $this->gettext('archivefolder'));
            }
        }

        return $p;
    }

    /**
     * Helper method to find the archive folder in the mailbox tree
     */
    private function _mod_folder_name(&$list, $folder, $new_name)
    {
        foreach ($list as $idx => $item) {
            if ($item['id'] == $folder) {
                $list[$idx]['name'] = $new_name;
                return true;
            }
            else if (!empty($item['folders'])) {
                if ($this->_mod_folder_name($list[$idx]['folders'], $folder, $new_name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Plugin action to move the submitted list of messages to the archive subfolders
     * according to the user settings and their headers.
     */
    function move_messages()
    {
        $rcmail = rcmail::get_instance();

        // only process ajax requests
        if (!$rcmail->output->ajax_call) {
            return;
        }

        $this->add_texts('localization');

        $storage        = $rcmail->get_storage();
        $delimiter      = $storage->get_hierarchy_delimiter();
        $read_on_move   = (bool) $rcmail->config->get('read_on_archive');
        $archive_type   = $rcmail->config->get('archive_type', '');
        $archive_prefix = $this->archive_folder . $delimiter;
        $search_request = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC);

        // count messages before changing anything
        if ($_POST['_from'] != 'show') {
            $threading = (bool) $storage->get_threading();
            $old_count = $storage->count(null, $threading ? 'THREADS' : 'ALL');
        }

        $count = 0;

        // this way response handler for 'move' action will be executed
        $rcmail->action = 'move';
        $this->result   = array(
            'reload'       => false,
            'error'        => false,
            'sources'      => array(),
            'destinations' => array(),
        );

        foreach (rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST) as $mbox => $uids) {
            if (!$this->archive_folder || $mbox === $this->archive_folder || strpos($mbox, $archive_prefix) === 0) {
                $count = count($uids);
                continue;
            }
            else if (!$archive_type || $archive_type == 'folder') {
                $folder = $this->archive_folder;

                if ($archive_type == 'folder') {
                    // compose full folder path
                    $folder .= $delimiter . $mbox;
                }

                // create archive subfolder if it doesn't yet exist
                $this->subfolder_worker($folder);

                $count += $this->move_messages_worker($uids, $mbox, $folder, $read_on_move);
            }
            else {
                if ($uids == '*') {
                    $index = $storage->index(null, rcmail_sort_column(), rcmail_sort_order());
                    $uids  = $index->get();
                }

                $messages = $storage->fetch_headers($mbox, $uids);
                $execute  = array();

                foreach ($messages as $message) {
                    $subfolder = null;
                    switch ($archive_type) {
                    case 'year':
                        $subfolder = $rcmail->format_date($message->timestamp, 'Y');
                        break;

                    case 'month':
                        $subfolder = $rcmail->format_date($message->timestamp, 'Y')
                            . $delimiter . $rcmail->format_date($message->timestamp, 'm');
                        break;

                    case 'tbmonth':
                        $subfolder = $rcmail->format_date($message->timestamp, 'Y')
                            . $delimiter . $rcmail->format_date($message->timestamp, 'Y')
                            . '-' . $rcmail->format_date($message->timestamp, 'm');
                        break;

                    case 'sender':
                        $subfolder = $this->sender_subfolder($message->get('from'));
                        break;
                    }

                    // compose full folder path
                    $folder = $this->archive_folder . ($subfolder ? $delimiter . $subfolder : '');

                    $execute[$folder][] = $message->uid;
                }

                foreach ($execute as $folder => $uids) {
                    // create archive subfolder if it doesn't yet exist
                    $this->subfolder_worker($folder);

                    $count += $this->move_messages_worker($uids, $mbox, $folder, $read_on_move);
                }
            }
        }

        if ($this->result['error']) {
            if ($_POST['_from'] != 'show') {
                $rcmail->output->command('list_mailbox');
            }

            $rcmail->output->show_message($this->gettext('archiveerror'), 'warning');
            $rcmail->output->send();
        }

        if (!empty($_POST['_refresh'])) {
            // FIXME: send updated message rows instead of reloading the entire list
            $rcmail->output->command('refresh_list');
        }
        else {
            $addrows = true;
        }

        // refresh saved search set after moving some messages
        if ($search_request && $rcmail->storage->get_search_set()) {
            $_SESSION['search'] = $rcmail->storage->refresh_search();
        }

        if ($_POST['_from'] == 'show') {
            if ($next = rcube_utils::get_input_value('_next_uid', rcube_utils::INPUT_GPC)) {
                $rcmail->output->command('show_message', $next);
            }
            else {
                $rcmail->output->command('command', 'list');
            }

            $rcmail->output->send();
        }

        $mbox           = $storage->get_folder();
        $msg_count      = $storage->count(null, $threading ? 'THREADS' : 'ALL');
        $exists         = $storage->count($mbox, 'EXISTS', true);
        $page_size      = $storage->get_pagesize();
        $page           = $storage->get_page();
        $pages          = ceil($msg_count / $page_size);
        $nextpage_count = $old_count - $page_size * $page;
        $remaining      = $msg_count - $page_size * ($page - 1);
        $quota_root     = $multifolder ? $this->result['sources'][0] : 'INBOX';

        // jump back one page (user removed the whole last page)
        if ($page > 1 && $remaining == 0) {
            $page -= 1;
            $storage->set_page($page);
            $_SESSION['page'] = $page;
            $jump_back = true;
        }

        // update unread messages counts for all involved folders
        foreach ($this->result['sources'] as $folder) {
            rcmail_send_unread_count($folder, true);
        }

        // update message count display
        $rcmail->output->set_env('messagecount', $msg_count);
        $rcmail->output->set_env('current_page', $page);
        $rcmail->output->set_env('pagecount', $pages);
        $rcmail->output->set_env('exists', $exists);
        $rcmail->output->command('set_quota', $rcmail->quota_content(null, $quota_root));
        $rcmail->output->command('set_rowcount', rcmail_get_messagecount_text($msg_count), $mbox);

        if ($threading) {
            $count = rcube_utils::get_input_value('_count', rcube_utils::INPUT_POST);
        }

        // add new rows from next page (if any)
        if ($addrows && $count && $uids != '*' && ($jump_back || $nextpage_count > 0)) {
            // #5862: Don't add more rows than it was on the next page
            $count = $jump_back ? null : min($nextpage_count, $count);

            $a_headers = $storage->list_messages($mbox, null,
                rcmail_sort_column(), rcmail_sort_order(), $count);

            rcmail_js_message_list($a_headers, false);
        }

        if ($this->result['reload']) {
            $rcmail->output->show_message($this->gettext('archivedreload'), 'confirmation');
        }
        else {
            $rcmail->output->show_message($this->gettext('archived'), 'confirmation');

            if (!$read_on_move) {
                foreach ($this->result['destinations'] as $folder) {
                    rcmail_send_unread_count($folder, true);
                }
            }
        }

        // send response
        $rcmail->output->send();
    }

    /**
     * Move messages from one folder to another and mark as read if needed
     */
    private function move_messages_worker($uids, $from_mbox, $to_mbox, $read_on_move)
    {
        $storage = rcmail::get_instance()->get_storage();

        if ($read_on_move) {
            // don't flush cache (4th argument)
            $storage->set_flag($uids, 'SEEN', $from_mbox, true);
        }

        // move message to target folder
        if ($storage->move_message($uids, $to_mbox, $from_mbox)) {
            if (!in_array($from_mbox, $this->result['sources'])) {
                $this->result['sources'][] = $from_mbox;
            }
            if (!in_array($to_mbox, $this->result['destinations'])) {
                $this->result['destinations'][] = $to_mbox;
            }

            return count($uids);
        }

        $this->result['error'] = true;
    }

    /**
     * Create archive subfolder if it doesn't yet exist
     */
    private function subfolder_worker($folder)
    {
        $storage   = rcmail::get_instance()->get_storage();
        $delimiter = $storage->get_hierarchy_delimiter();

        if ($this->folders === null) {
            $this->folders = $storage->list_folders('', $this->archive_folder . '*', 'mail', null, true);
        }

        if (!in_array($folder, $this->folders)) {
            $path = explode($delimiter, $folder);

            // we'll create all folders in the path
            for ($i=0; $i<count($path); $i++) {
                $_folder = implode($delimiter, array_slice($path, 0, $i+1));
                if (!in_array($_folder, $this->folders)) {
                    if ($storage->create_folder($_folder, true)) {
                        $this->result['reload'] = true;
                        $this->folders[] = $_folder;
                    }
                }
            }
        }
    }

    /**
     * Hook to inject plugin-specific user settings
     */
    function prefs_table($args)
    {
        global $CURR_SECTION;

        $this->add_texts('localization');

        $rcmail        = rcmail::get_instance();
        $dont_override = $rcmail->config->get('dont_override', array());

        if ($args['section'] == 'folders' && !in_array('archive_mbox', $dont_override)) {
            $mbox = $rcmail->config->get('archive_mbox');
            $type = $rcmail->config->get('archive_type');

            // load folders list when needed
            if ($CURR_SECTION) {
                $select = $rcmail->folder_selector(array(
                        'noselection'   => '---',
                        'realnames'     => true,
                        'maxlength'     => 30,
                        'folder_filter' => 'mail',
                        'folder_rights' => 'w',
                        'onchange'      => "if ($(this).val() == 'INBOX') $(this).val('')",
                ));
            }
            else {
                $select = new html_select();
            }

            $args['blocks']['main']['options']['archive_mbox'] = array(
                'title'   => html::label('_archive_mbox', rcube::Q($this->gettext('archivefolder'))),
                'content' => $select->show($mbox, array('id' => '_archive_mbox', 'name' => '_archive_mbox'))
            );

            // If the server supports only either messages or folders in a folder
            // we do not allow archive splitting, for simplicity (#5057)
            if ($rcmail->get_storage()->get_capability(rcube_storage::DUAL_USE_FOLDERS)) {
                // add option for structuring the archive folder
                $archive_type = new html_select(array('name' => '_archive_type', 'id' => 'ff_archive_type'));
                $archive_type->add($this->gettext('none'), '');
                $archive_type->add($this->gettext('archivetypeyear'), 'year');
                $archive_type->add($this->gettext('archivetypemonth'), 'month');
                $archive_type->add($this->gettext('archivetypetbmonth'), 'tbmonth');
                $archive_type->add($this->gettext('archivetypesender'), 'sender');
                $archive_type->add($this->gettext('archivetypefolder'), 'folder');

                $args['blocks']['archive'] = array(
                    'name'    => rcube::Q($this->gettext('settingstitle')),
                    'options' => array('archive_type' => array(
                            'title'   => html::label('ff_archive_type', rcube::Q($this->gettext('archivetype'))),
                            'content' => $archive_type->show($type)
                        )
                    )
                );
            }
        }
        else if ($args['section'] == 'server' && !in_array('read_on_archive', $dont_override)) {
            $chbox = new html_checkbox(array('name' => '_read_on_archive', 'id' => 'ff_read_on_archive', 'value' => 1));
            $args['blocks']['main']['options']['read_on_archive'] = array(
                'title'   => html::label('ff_read_on_archive', rcube::Q($this->gettext('readonarchive'))),
                'content' => $chbox->show($rcmail->config->get('read_on_archive') ? 1 : 0)
            );
        }

        return $args;
    }

    /**
     * Hook to save plugin-specific user settings
     */
    function save_prefs($args)
    {
        $rcmail        = rcmail::get_instance();
        $dont_override = $rcmail->config->get('dont_override', array());

        if ($args['section'] == 'folders' && !in_array('archive_mbox', $dont_override)) {
            $args['prefs']['archive_type'] = rcube_utils::get_input_value('_archive_type', rcube_utils::INPUT_POST);
        }
        else if ($args['section'] == 'server' && !in_array('read_on_archive', $dont_override)) {
            $args['prefs']['read_on_archive'] = (bool) rcube_utils::get_input_value('_read_on_archive', rcube_utils::INPUT_POST);
        }

        return $args;
    }

    /**
     * Create folder name from the message sender address
     */
    protected function sender_subfolder($from)
    {
        static $delim;
        static $vendor;
        static $skip_hidden;

        preg_match('/[\b<](.+@.+)[\b>]/i', $from, $m);

        if (empty($m[1])) {
            return $this->gettext('unkownsender');
        }

        if ($delim === null) {
            $rcmail      = rcmail::get_instance();
            $storage     = $rcmail->get_storage();
            $delim       = $storage->get_hierarchy_delimiter();
            $vendor      = $storage->get_vendor();
            $skip_hidden = $rcmail->config->get('imap_skip_hidden_folders');
        }

        // Remove some forbidden characters
        $regexp = '\\x00-\\x1F\\x7F%*';

        if ($vendor == 'cyrus') {
            // List based on testing Kolab's Cyrus-IMAP 2.5
            $regexp .= '!`(){}|\\?<;"';
        }

        $folder_name = preg_replace("/[$regexp]/", '', $m[1]);

        if ($skip_hidden && $folder_name[0] == '.') {
            $folder_name = substr($folder_name, 1);
        }

        $replace = $delim == '-' ? '_' : '-';
        $replacements[$delim] = $replace;

        // Cyrus-IMAP does not allow @ character in folder name
        if ($vendor == 'cyrus') {
            $replacements['@'] = $replace;
        }

        // replace reserved characters in folder name
        return strtr($folder_name, $replacements);
    }
}
