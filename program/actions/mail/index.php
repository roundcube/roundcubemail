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
 |   Provide webmail functionality and GUI objects                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_index extends rcmail_action
{
    public static $aliases = [
        'refresh'            => 'check-recent',
        'preview'            => 'show',
        'print'              => 'show',
        'expunge'            => 'folder-expunge',
        'purge'              => 'folder-purge',
        'remove-attachment'  => 'attachment-delete',
        'rename-attachment'  => 'attachment-rename',
        'display-attachment' => 'attachment-display',
        'upload'             => 'attachment-upload',
    ];

    protected static $PRINT_MODE = false;
    protected static $REMOTE_OBJECTS;
    protected static $SUSPICIOUS_EMAIL = false;
    protected static $wash_html_body_attrs = [];

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // always instantiate storage object (but not connect to server yet)
        $rcmail->storage_init();

        // init environment - set current folder, page, list mode
        self::init_env();

        // set message set for search result
        if (
            !empty($_REQUEST['_search'])
            && isset($_SESSION['search'])
            && isset($_SESSION['search_request'])
            && $_SESSION['search_request'] == $_REQUEST['_search']
        ) {
            $rcmail->storage->set_search_set($_SESSION['search']);

            $rcmail->output->set_env('search_request', $_REQUEST['_search']);
            $rcmail->output->set_env('search_text', $_SESSION['last_text_search']);
        }

        // remove mbox part from _uid
        $uid = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GPC);
        if ($uid && preg_match('/^\d+-.+/', $uid)) {
            list($uid, $mbox) = explode('-', $uid, 2);
            if (isset($_GET['_uid'])) {
                $_GET['_uid'] = $uid;
            }
            if (isset($_POST['_uid'])) {
                $_POST['_uid'] = $uid;
            }
            $_REQUEST['_uid'] = $uid;

            // override mbox
            if (!empty($mbox)) {
                $_GET['_mbox']  = $mbox;
                $_POST['_mbox'] = $mbox;
                $rcmail->storage->set_folder($_SESSION['mbox'] = $mbox);
            }
        }

        if (!empty($_SESSION['browser_caps']) && !$rcmail->output->ajax_call) {
            $rcmail->output->set_env('browser_capabilities', $_SESSION['browser_caps']);
        }

        // set main env variables, labels and page title
        if (empty($rcmail->action) || $rcmail->action == 'list') {
            // connect to storage server and trigger error on failure
            $rcmail->storage_connect();

            $mbox_name = $rcmail->storage->get_folder();

            if (empty($rcmail->action)) {
                $rcmail->output->set_env('search_mods', self::search_mods());

                $scope = rcube_utils::get_input_string('_scope', rcube_utils::INPUT_GET);
                if (!$scope && isset($_SESSION['search_scope']) && $rcmail->output->get_env('search_request')) {
                    $scope = $_SESSION['search_scope'];
                }

                if ($scope && preg_match('/^(all|sub)$/i', $scope)) {
                    $rcmail->output->set_env('search_scope', strtolower($scope));
                }

                self::list_pagetitle();
            }

            $threading = (bool) $rcmail->storage->get_threading();
            $delimiter = $rcmail->storage->get_hierarchy_delimiter();

            // set current mailbox and some other vars in client environment
            $rcmail->output->set_env('mailbox', $mbox_name);
            $rcmail->output->set_env('pagesize', $rcmail->storage->get_pagesize());
            $rcmail->output->set_env('current_page', max(1, $_SESSION['page'] ?? 1));
            $rcmail->output->set_env('delimiter', $delimiter);
            $rcmail->output->set_env('threading', $threading);
            $rcmail->output->set_env('threads', $threading || $rcmail->storage->get_capability('THREAD'));
            $rcmail->output->set_env('reply_all_mode', (int) $rcmail->config->get('reply_all_mode'));
            $rcmail->output->set_env('layout', $rcmail->config->get('layout') ?: 'widescreen');
            $rcmail->output->set_env('quota', $rcmail->storage->get_capability('QUOTA'));

            // set special folders
            foreach (['drafts', 'trash', 'junk'] as $mbox) {
                if ($folder = $rcmail->config->get($mbox . '_mbox')) {
                    $rcmail->output->set_env($mbox . '_mailbox', $folder);
                }
            }

            if (!empty($_GET['_uid'])) {
                $rcmail->output->set_env('list_uid', $_GET['_uid']);
            }

            // set configuration
            self::set_env_config(['delete_junk', 'flag_for_deletion', 'read_when_deleted',
                'skip_deleted', 'display_next', 'message_extwin', 'forward_attachment']);

            if (!$rcmail->output->ajax_call) {
                $rcmail->output->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
                    'movingmessage', 'copyingmessage', 'deletingmessage', 'markingmessage',
                    'copy', 'move', 'quota', 'replyall', 'replylist', 'stillsearching',
                    'flagged', 'unflagged', 'unread', 'deleted', 'replied', 'forwarded',
                    'priority', 'withattachment', 'fileuploaderror', 'mark', 'markallread',
                    'folders-cur', 'folders-sub', 'folders-all', 'cancel', 'bounce', 'bouncemsg',
                    'sendingmessage');
            }
        }

        // register UI objects
        $rcmail->output->add_handlers([
            'mailboxlist'         => [$rcmail, 'folder_list'],
            'quotadisplay'        => [$this, 'quota_display'],
            'messages'            => [$this, 'message_list'],
            'messagecountdisplay' => [$this, 'messagecount_display'],
            'listmenulink'        => [$this, 'options_menu_link'],
            'mailboxname'         => [$this, 'mailbox_name_display'],
            'messageimportform'   => [$this, 'message_import_form'],
            'searchfilter'        => [$this, 'search_filter'],
            'searchinterval'      => [$this, 'search_interval'],
            'searchform'          => [$rcmail->output, 'search_form'],
        ]);
    }

    /**
     * Sets storage properties and session
     */
    public static function init_env()
    {
        $rcmail = rcmail::get_instance();

        $default_threading  = $rcmail->config->get('default_list_mode', 'list') == 'threads';
        $a_threading        = $rcmail->config->get('message_threading', []);
        $message_sort_col   = $rcmail->config->get('message_sort_col');
        $message_sort_order = $rcmail->config->get('message_sort_order');

        $mbox = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GPC, true);

        // set imap properties and session vars
        if (!strlen($mbox)) {
            $mbox = isset($_SESSION['mbox']) && strlen($_SESSION['mbox']) ? $_SESSION['mbox'] : 'INBOX';
        }

        // We handle 'page' argument on 'list' and 'getunread' to prevent from
        // race condition and unintentional page overwrite in session.
        // Also, when entering the Mail UI (#7932)
        if (empty($rcmail->action) || $rcmail->action == 'list' || $rcmail->action == 'getunread') {
            $page = isset($_GET['_page']) ? intval($_GET['_page']) : 0;
            if (!$page) {
                $page = !empty($_SESSION['page']) ? $_SESSION['page'] : 1;
            }

            $_SESSION['page'] = $page;
        }

        $rcmail->storage->set_folder($_SESSION['mbox'] = $mbox);
        $rcmail->storage->set_page($_SESSION['page'] ?? 1);

        // set default sort col/order to session
        if (!isset($_SESSION['sort_col'])) {
            $_SESSION['sort_col'] = $message_sort_col ?: '';
        }
        if (!isset($_SESSION['sort_order'])) {
            $_SESSION['sort_order'] = strtoupper($message_sort_order) == 'ASC' ? 'ASC' : 'DESC';
        }

        // set threads mode
        if (isset($_GET['_threads'])) {
            if ($_GET['_threads']) {
                // re-set current page number when listing mode changes
                if (empty($a_threading[$_SESSION['mbox']])) {
                    $rcmail->storage->set_page($_SESSION['page'] = 1);
                }

                $a_threading[$_SESSION['mbox']] = true;
            }
            else {
                // re-set current page number when listing mode changes
                if (!empty($a_threading[$_SESSION['mbox']])) {
                    $rcmail->storage->set_page($_SESSION['page'] = 1);
                }

                $a_threading[$_SESSION['mbox']] = false;
            }

            $rcmail->user->save_prefs(['message_threading' => $a_threading]);
        }

        $threading = $a_threading[$_SESSION['mbox']] ?? $default_threading;

        $rcmail->storage->set_threading($threading);
    }

    /**
     * Sets page title
     */
    public static function list_pagetitle()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->output->get_env('search_request')) {
            $pagetitle = $rcmail->gettext('searchresult');
        }
        else {
            $mbox_name = $rcmail->output->get_env('mailbox') ?: $rcmail->storage->get_folder();
            $delimiter = $rcmail->storage->get_hierarchy_delimiter();
            $pagetitle = self::localize_foldername($mbox_name, true);
            $pagetitle = str_replace($delimiter, " \xC2\xBB ", $pagetitle);
        }

        $rcmail->output->set_pagetitle($pagetitle);
    }

    /**
     * Returns default search mods
     */
    public static function search_mods()
    {
        $rcmail = rcmail::get_instance();
        $mods   = $rcmail->config->get('search_mods');

        if (empty($mods)) {
            $mods = ['*' => ['subject' => 1, 'from' => 1]];

            foreach (['sent', 'drafts'] as $mbox) {
                if ($mbox = $rcmail->config->get($mbox . '_mbox')) {
                    $mods[$mbox] = ['subject' => 1, 'to' => 1];
                }
            }
        }

        return $mods;
    }

    /**
     * Returns 'to' if current folder is configured Sent or Drafts
     * or their subfolders, otherwise returns 'from'.
     *
     * @return string Column name
     */
    public static function message_list_smart_column_name()
    {
        $rcmail      = rcmail::get_instance();
        $delim       = $rcmail->storage->get_hierarchy_delimiter();
        $sent_mbox   = $rcmail->config->get('sent_mbox');
        $drafts_mbox = $rcmail->config->get('drafts_mbox');
        $mbox        = $rcmail->output->get_env('mailbox');

        if (!is_string($mbox) || !strlen($mbox)) {
            $mbox = $rcmail->storage->get_folder();
        }

        if ((strpos($mbox.$delim, $sent_mbox.$delim) === 0 || strpos($mbox.$delim, $drafts_mbox.$delim) === 0)
            && strtoupper($mbox) != 'INBOX'
        ) {
            return 'to';
        }

        return 'from';
    }

    /**
     * Returns configured messages list sorting column name
     * The name is context-sensitive, which means if sorting is set to 'fromto'
     * it will return 'from' or 'to' according to current folder type.
     *
     * @return string Column name
     */
    public static function sort_column()
    {
        $rcmail = rcmail::get_instance();

        if (isset($_SESSION['sort_col'])) {
            $column = $_SESSION['sort_col'];
        }
        else {
            $column = $rcmail->config->get('message_sort_col');
        }

        // get name of smart From/To column in folder context
        if ($column == 'fromto') {
            $column = self::message_list_smart_column_name();
        }

        return $column;
    }

    /**
     * Returns configured message list sorting order
     *
     * @return string Sorting order (ASC|DESC)
     */
    public static function sort_order()
    {
        if (isset($_SESSION['sort_order'])) {
            return $_SESSION['sort_order'];
        }

        return rcmail::get_instance()->config->get('message_sort_order');
    }

    /**
     * return the message list as HTML table
     */
    function message_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        // add some labels to client
        $rcmail->output->add_label('from', 'to');

        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcubemessagelist';
        }

        // define list of cols to be displayed based on parameter or config
        if (empty($attrib['columns'])) {
            $list_cols   = $rcmail->config->get('list_cols');
            $a_show_cols = !empty($list_cols) && is_array($list_cols) ? $list_cols : ['subject'];

            $rcmail->output->set_env('col_movable', !in_array('list_cols', (array) $rcmail->config->get('dont_override')));
        }
        else {
            $a_show_cols = preg_split('/[\s,;]+/', str_replace(["'", '"'], '', $attrib['columns']));
            $attrib['columns'] = $a_show_cols;
        }

        // save some variables for use in ajax list
        $_SESSION['list_attrib'] = $attrib;

        // make sure 'threads' and 'subject' columns are present
        if (!in_array('subject', $a_show_cols)) {
            array_unshift($a_show_cols, 'subject');
        }
        if (!in_array('threads', $a_show_cols)) {
            array_unshift($a_show_cols, 'threads');
        }

        $listcols = $a_show_cols;

        // set client env
        $rcmail->output->add_gui_object('messagelist', $attrib['id']);
        $rcmail->output->set_env('autoexpand_threads', intval($rcmail->config->get('autoexpand_threads')));
        $rcmail->output->set_env('sort_col', $_SESSION['sort_col']);
        $rcmail->output->set_env('sort_order', $_SESSION['sort_order']);
        $rcmail->output->set_env('messages', []);
        $rcmail->output->set_env('listcols', $listcols);
        $rcmail->output->set_env('listcols_widescreen', ['threads', 'subject', 'fromto', 'date', 'size', 'flag', 'attachment']);

        $rcmail->output->include_script('list.js');

        $table = new html_table($attrib);

        if (empty($attrib['noheader'])) {
            $allcols = array_merge($listcols, ['threads', 'subject', 'fromto', 'date', 'size', 'flag', 'attachment']);
            $allcols = array_unique($allcols);

            foreach (self::message_list_head($attrib, $allcols) as $col => $cell) {
                if (in_array($col, $listcols)) {
                    $table->add_header(['class' => $cell['className'], 'id' => $cell['id']], $cell['html']);
                }
            }
        }

        return $table->show();
    }

    /**
     * return javascript commands to add rows to the message list
     */
    public static function js_message_list($a_headers, $insert_top = false, $a_show_cols = null)
    {
        $rcmail = rcmail::get_instance();

        if (empty($a_show_cols)) {
            if (!empty($_SESSION['list_attrib']['columns'])) {
                $a_show_cols = $_SESSION['list_attrib']['columns'];
            }
            else {
                $list_cols   = $rcmail->config->get('list_cols');
                $a_show_cols = !empty($list_cols) && is_array($list_cols) ? $list_cols : ['subject'];
            }
        }
        else {
            if (!is_array($a_show_cols)) {
                $a_show_cols = preg_split('/[\s,;]+/', str_replace(["'", '"'], '', $a_show_cols));
            }
            $head_replace = true;
        }

        $delimiter   = $rcmail->storage->get_hierarchy_delimiter();
        $search_set  = $rcmail->storage->get_search_set();
        $multifolder = $search_set && !empty($search_set[1]->multi);

        // add/remove 'folder' column to the list on multi-folder searches
        if ($multifolder && !in_array('folder', $a_show_cols)) {
            $a_show_cols[] = 'folder';
            $head_replace  = true;
        }
        else if (!$multifolder && ($found = array_search('folder', $a_show_cols)) !== false) {
            unset($a_show_cols[$found]);
            $head_replace = true;
        }

        $mbox = $rcmail->output->get_env('mailbox');
        if (!is_string($mbox) || !strlen($mbox)) {
            $mbox = $rcmail->storage->get_folder();
        }

        // make sure 'threads' and 'subject' columns are present
        if (!in_array('subject', $a_show_cols)) {
            array_unshift($a_show_cols, 'subject');
        }
        if (!in_array('threads', $a_show_cols)) {
            array_unshift($a_show_cols, 'threads');
        }

        // Make sure there are no duplicated columns (#1486999)
        $a_show_cols = array_unique($a_show_cols);
        $_SESSION['list_attrib']['columns'] = $a_show_cols;

        // Plugins may set header's list_cols/list_flags and other rcube_message_header variables
        // and list columns
        $plugin = $rcmail->plugins->exec_hook('messages_list', ['messages' => $a_headers, 'cols' => $a_show_cols]);

        $a_show_cols = $plugin['cols'];
        $a_headers   = $plugin['messages'];

        // make sure minimum required columns are present (needed for widescreen layout)
        $allcols = array_merge($a_show_cols, ['threads', 'subject', 'fromto', 'date', 'size', 'flag', 'attachment']);
        $allcols = array_unique($allcols);

        $thead = !empty($head_replace) ? self::message_list_head($_SESSION['list_attrib'], $allcols) : null;

        // get name of smart From/To column in folder context
        $smart_col = self::message_list_smart_column_name();
        $rcmail->output->command('set_message_coltypes', array_values($a_show_cols), $thead, $smart_col);

        if ($multifolder && $_SESSION['search_scope'] == 'all') {
            $rcmail->output->command('select_folder', '');
        }

        $rcmail->output->set_env('multifolder_listing', $multifolder);

        if (empty($a_headers)) {
            return;
        }

        // remove 'threads', 'attachment', 'flag', 'status' columns, we don't need them here
        foreach (['threads', 'attachment', 'flag', 'status', 'priority'] as $col) {
            if (($key = array_search($col, $allcols)) !== false) {
                unset($allcols[$key]);
            }
        }

        $sort_col = $_SESSION['sort_col'];

        // loop through message headers
        foreach ($a_headers as $header) {
            if (empty($header) || empty($header->size)) {
                continue;
            }

            // make message UIDs unique by appending the folder name
            if ($multifolder) {
                $header->uid .= '-' . $header->folder;
                $header->flags['skip_mbox_check'] = true;
                if (!empty($header->parent_uid)) {
                    $header->parent_uid .= '-' . $header->folder;
                }
            }

            $a_msg_cols  = [];
            $a_msg_flags = [];

            // format each col; similar as in self::message_list()
            foreach ($allcols as $col) {
                $col_name = $col == 'fromto' ? $smart_col : $col;

                if (in_array($col_name, ['from', 'to', 'cc', 'replyto'])) {
                    $cont = self::address_string($header->$col_name, 3, false, null, $header->charset, null, false);
                    if (empty($cont)) {
                        $cont = '&nbsp;'; // for widescreen mode
                    }
                }
                else if ($col == 'subject') {
                    $cont = trim(rcube_mime::decode_header($header->subject, $header->charset));
                    if (!$cont) {
                        $cont = $rcmail->gettext('nosubject');
                    }
                    $cont = rcube::SQ($cont);
                }
                else if ($col == 'size') {
                    $cont = self::show_bytes($header->size);
                }
                else if ($col == 'date') {
                    $cont = $rcmail->format_date($sort_col == 'arrival' ? $header->internaldate : $header->date);
                }
                else if ($col == 'folder') {
                    if (!isset($last_folder) || !isset($last_folder_name) || $last_folder !== $header->folder) {
                        $last_folder      = $header->folder;
                        $last_folder_name = self::localize_foldername($last_folder, true);
                        $last_folder_name = str_replace($delimiter, " \xC2\xBB ", $last_folder_name);
                    }

                    $cont = rcube::SQ($last_folder_name);
                }
                else if (isset($header->$col)) {
                    $cont = rcube::SQ($header->$col);
                }
                else {
                    $cont = '';
                }

                $a_msg_cols[$col] = $cont;
            }

            $a_msg_flags = array_change_key_case(array_map('intval', (array) $header->flags));

            if (!empty($header->depth)) {
                $a_msg_flags['depth'] = $header->depth;
            }
            else if (!empty($header->has_children)) {
                $roots[] = $header->uid;
            }
            if (!empty($header->parent_uid)) {
                $a_msg_flags['parent_uid'] = $header->parent_uid;
            }
            if (!empty($header->has_children)) {
                $a_msg_flags['has_children'] = $header->has_children;
            }
            if (!empty($header->unread_children)) {
                $a_msg_flags['unread_children'] = $header->unread_children;
            }
            if (!empty($header->flagged_children)) {
                $a_msg_flags['flagged_children'] = $header->flagged_children;
            }
            if (!empty($header->others['list-post'])) {
                $a_msg_flags['ml'] = 1;
            }
            if (!empty($header->priority)) {
                $a_msg_flags['prio'] = (int) $header->priority;
            }

            $a_msg_flags['ctype'] = rcube::Q($header->ctype);
            $a_msg_flags['mbox']  = $header->folder;

            // merge with plugin result (Deprecated, use $header->flags)
            if (!empty($header->list_flags) && is_array($header->list_flags)) {
                $a_msg_flags = array_merge($a_msg_flags, $header->list_flags);
            }
            if (!empty($header->list_cols) && is_array($header->list_cols)) {
                $a_msg_cols = array_merge($a_msg_cols, $header->list_cols);
            }

            $rcmail->output->command('add_message_row', $header->uid, $a_msg_cols, $a_msg_flags, $insert_top);
        }

        if ($rcmail->storage->get_threading()) {
            $roots = isset($roots) ? (array) $roots : [];
            $rcmail->output->command('init_threads', $roots, $mbox);
        }
    }

    /*
     * Creates <THEAD> for message list table
     */
    public static function message_list_head($attrib, $a_show_cols)
    {
        $rcmail = rcmail::get_instance();

        // check to see if we have some settings for sorting
        $sort_col   = $_SESSION['sort_col'];
        $sort_order = $_SESSION['sort_order'];

        $dont_override  = (array) $rcmail->config->get('dont_override');
        $disabled_sort  = in_array('message_sort_col', $dont_override);
        $disabled_order = in_array('message_sort_order', $dont_override);

        $rcmail->output->set_env('disabled_sort_col', $disabled_sort);
        $rcmail->output->set_env('disabled_sort_order', $disabled_order);

        // define sortable columns
        if ($disabled_sort) {
            $a_sort_cols = $sort_col && !$disabled_order ? [$sort_col] : [];
        }
        else {
            $a_sort_cols = ['subject', 'date', 'from', 'to', 'fromto', 'size', 'cc'];
        }

        if (!empty($attrib['optionsmenuicon'])) {
            $params = [];
            foreach ($attrib as $key => $val) {
                if (preg_match('/^optionsmenu(.+)$/', $key, $matches)) {
                    $params[$matches[1]] = $val;
                }
            }

            $list_menu = self::options_menu_link($params);
        }

        $cells = $coltypes = [];

        // get name of smart From/To column in folder context
        $smart_col = null;
        if (array_search('fromto', $a_show_cols) !== false) {
            $smart_col = self::message_list_smart_column_name();
        }

        foreach ($a_show_cols as $col) {
            // sanity check
            if (!preg_match('/^[a-zA-Z_-]+$/', $col)) {
                continue;
            }

            $label    = '';
            $sortable = false;
            $rel_col  = $col == 'date' && $sort_col == 'arrival' ? 'arrival' : $col;

            // get column name
            switch ($col) {
            case 'flag':
                $col_name = html::span('flagged', $rcmail->gettext('flagged'));
                break;
            case 'attachment':
            case 'priority':
                $col_name = html::span($col, $rcmail->gettext($col));
                break;
            case 'status':
                $col_name = html::span($col, $rcmail->gettext('readstatus'));
                break;
            case 'threads':
                $col_name = !empty($list_menu) ? $list_menu : '';
                break;
            case 'fromto':
                $label    = $rcmail->gettext($smart_col);
                $col_name = rcube::Q($label);
                break;
            default:
                $label    = $rcmail->gettext($col);
                $col_name = rcube::Q($label);
            }

            // make sort links
            if (in_array($col, $a_sort_cols)) {
                $sortable = true;
                $col_name = html::a([
                        'href'  => "./#sort",
                        'class' => 'sortcol',
                        'rel'   => $rel_col,
                        'title' => $rcmail->gettext('sortby')
                    ], $col_name);
            }
            else if (empty($col_name) || $col_name[0] != '<') {
                $col_name = '<span class="' . $col .'">' . $col_name . '</span>';
            }

            $sort_class = $rel_col == $sort_col && !$disabled_order ? " sorted$sort_order" : '';
            $class_name = $col.$sort_class;

            // put it all together
            $cells[$col]    = ['className' => $class_name, 'id' => "rcm$col", 'html' => $col_name];
            $coltypes[$col] = ['className' => $class_name, 'id' => "rcm$col", 'label' => $label, 'sortable' => $sortable];
        }

        $rcmail->output->set_env('coltypes', $coltypes);

        return $cells;
    }

    public static function options_menu_link($attrib = [])
    {
        $rcmail  = rcmail::get_instance();
        $title   = $rcmail->gettext(!empty($attrib['label']) ? $attrib['label'] : 'listoptions');
        $inner   = $title;
        $onclick = sprintf(
            "return %s.command('menu-open', '%s', this, event)",
            rcmail_output::JS_OBJECT_NAME,
            !empty($attrib['ref']) ? $attrib['ref'] : 'messagelistmenu'
        );

        // Backwards compatibility, attribute renamed in v1.5
        if (isset($attrib['optionsmenuicon'])) {
            $attrib['icon'] = $attrib['optionsmenuicon'];
        }

        if (!empty($attrib['icon']) && $attrib['icon'] != 'true') {
            $inner = html::img(['src' => $rcmail->output->asset_url($attrib['icon'], true), 'alt' => $title]);
        }
        else if (!empty($attrib['innerclass'])) {
            $inner = html::span($attrib['innerclass'], $inner);
        }

        return html::a([
                'href'     => '#list-options',
                'onclick'  => $onclick,
                'class'    => $attrib['class'] ?? 'listmenu',
                'id'       => $attrib['id'] ?? 'listmenulink',
                'title'    => $title,
                'tabindex' => '0',
            ], $inner
        );
    }

    public static function messagecount_display($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmcountdisplay';
        }

        $rcmail->output->add_gui_object('countdisplay', $attrib['id']);

        $content =  $rcmail->action != 'show' ? self::get_messagecount_text() : $rcmail->gettext('loading');

        return html::span($attrib, $content);
    }

    public static function get_messagecount_text($count = null, $page = null)
    {
        $rcmail = rcmail::get_instance();

        if ($page === null) {
            $page = $rcmail->storage->get_page();
        }

        $page_size = $rcmail->storage->get_pagesize();
        $start_msg = ($page-1) * $page_size + 1;
        $max       = $count;

        if ($max === null && $rcmail->action) {
            $max = $rcmail->storage->count(null, $rcmail->storage->get_threading() ? 'THREADS' : 'ALL');
        }

        if (!$max) {
            $out = $rcmail->storage->get_search_set() ? $rcmail->gettext('nomessages') : $rcmail->gettext('mailboxempty');
        }
        else {
            $out = $rcmail->gettext([
                    'name' => $rcmail->storage->get_threading() ? 'threadsfromto' : 'messagesfromto',
                    'vars' => [
                        'from'  => $start_msg,
                        'to'    => min($max, $start_msg + $page_size - 1),
                        'count' => $max
                    ]
            ]);
        }

        return rcube::Q($out);
    }

    public static function mailbox_name_display($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmmailboxname';
        }

        $rcmail->output->add_gui_object('mailboxname', $attrib['id']);

        return html::span($attrib, self::get_mailbox_name_text());
    }

    public static function get_mailbox_name_text()
    {
        $rcmail = rcmail::get_instance();
        $mbox   = $rcmail->output->get_env('mailbox');

        if (!is_string($mbox) || !strlen($mbox)) {
            $mbox = $rcmail->storage->get_folder();
        }

        return self::localize_foldername($mbox);
    }

    public static function send_unread_count($mbox_name, $force = false, $count = null, $mark = '')
    {
        $rcmail     = rcmail::get_instance();
        $old_unseen = self::get_unseen_count($mbox_name);
        $unseen     = $count;

        if ($unseen === null) {
            $unseen = $rcmail->storage->count($mbox_name, 'UNSEEN', $force);
        }

        if ($unseen !== $old_unseen || ($mbox_name == 'INBOX')) {
            $rcmail->output->command('set_unread_count', $mbox_name, $unseen,
                ($mbox_name == 'INBOX'), $unseen && $mark ? $mark : '');
        }

        self::set_unseen_count($mbox_name, $unseen);

        return $unseen;
    }

    public static function set_unseen_count($mbox_name, $count)
    {
        // @TODO: this data is doubled (session and cache tables) if caching is enabled

        // Make sure we have an array here (#1487066)
        if (!isset($_SESSION['unseen_count']) || !is_array($_SESSION['unseen_count'])) {
            $_SESSION['unseen_count'] = [];
        }

        $_SESSION['unseen_count'][$mbox_name] = $count;
    }

    public static function get_unseen_count($mbox_name)
    {
        if (!empty($_SESSION['unseen_count']) && array_key_exists($mbox_name, $_SESSION['unseen_count'])) {
            return $_SESSION['unseen_count'][$mbox_name];
        }
    }

    /**
     * Sets message is_safe flag according to 'show_images' option value
     *
     * @param rcube_message $message Mail message object
     */
    public static function check_safe($message)
    {
        $rcmail = rcmail::get_instance();

        if (empty($message->is_safe)
            && ($show_images = $rcmail->config->get('show_images'))
            && $message->has_html_part()
        ) {
            switch ($show_images) {
            case 3: // trusted senders only
            case 1: // all my contacts
                if (!empty($message->sender['mailto'])) {
                    $type = rcube_addressbook::TYPE_TRUSTED_SENDER;

                    if ($show_images == 1) {
                        $type |= rcube_addressbook::TYPE_RECIPIENT | rcube_addressbook::TYPE_WRITEABLE;
                    }

                    if ($rcmail->contact_exists($message->sender['mailto'], $type)) {
                        $message->set_safe(true);
                    }
                }

                $rcmail->plugins->exec_hook('message_check_safe', ['message' => $message]);
                break;

            case 2: // always
                $message->set_safe(true);
                break;
            }
        }

        return !empty($message->is_safe);
    }

    /**
     * Cleans up the given message HTML Body (for displaying)
     *
     * @param string $html         HTML
     * @param array  $p            Display parameters
     * @param array  $cid_replaces CID map replaces (inline images)
     *
     * @return string Clean HTML
     */
    public static function wash_html($html, $p, $cid_replaces = [])
    {
        $rcmail = rcmail::get_instance();

        $p += ['safe' => false, 'inline_html' => true, 'css_prefix' => null, 'container_id' => null];

        // charset was converted to UTF-8 in rcube_storage::get_message_part(),
        // change/add charset specification in HTML accordingly,
        // washtml's DOMDocument methods cannot work without that
        $meta = '<meta charset="' . RCUBE_CHARSET . '" />';

        // remove old meta tag and add the new one, making sure that it is placed in the head (#3510, #7116)
        $html = preg_replace('/<meta[^>]+charset=[a-z0-9_"-]+[^>]*>/Ui', '', $html);
        $html = preg_replace('/(<head[^>]*>)/Ui', '\\1'.$meta, $html, -1, $rcount);

        if (!$rcount) {
            // Note: HTML without <html> tag may still be a valid input (#6713)
            if (($pos = stripos($html, '<html')) === false) {
                $html = '<html><head>' . $meta . '</head>' . $html;
            }
            else {
                $pos  = strpos($html, '>', $pos);
                $html = substr_replace($html, '<head>' . $meta . '</head>', $pos + 1, 0);
            }
        }

        // clean HTML with washtml by Frederic Motte
        $wash_opts = [
            'show_washed'   => false,
            'add_comments'  => $p['add_comments'] ?? true,
            'allow_remote'  => $p['safe'],
            'blocked_src'   => $rcmail->output->asset_url('program/resources/blocked.gif'),
            'charset'       => RCUBE_CHARSET,
            'cid_map'       => $cid_replaces,
            'html_elements' => ['body'],
            'css_prefix'    => $p['css_prefix'],
            'ignore_elements' => $p['ignore_elements'] ?? [],
            // internal configuration
            'container_id'  => $p['container_id'],
            'body_class'    => $p['body_class'] ?? '',
        ];

        if (empty($p['inline_html'])) {
            $wash_opts['html_elements'] = ['html','head','title','body','link'];
        }
        if (!empty($p['safe'])) {
            $wash_opts['html_attribs'] = ['rel','type'];
        }

        // overwrite washer options with options from plugins
        if (isset($p['html_elements'])) {
            $wash_opts['html_elements'] = $p['html_elements'];
        }
        if (isset($p['html_attribs'])) {
            $wash_opts['html_attribs'] = $p['html_attribs'];
        }

        // initialize HTML washer
        $washer = new rcube_washtml($wash_opts);

        self::$wash_html_body_attrs = [];

        if (!empty($p['inline_html'])) {
            $washer->add_callback('body', 'rcmail_action_mail_index::washtml_callback');

            if ($wash_opts['body_class']) {
                self::$wash_html_body_attrs['class'] = $wash_opts['body_class'];
            }

            if ($wash_opts['container_id']) {
                self::$wash_html_body_attrs['id'] = $wash_opts['container_id'];
            }
        }

        if (empty($p['skip_washer_form_callback'])) {
            $washer->add_callback('form', 'rcmail_action_mail_index::washtml_callback');
        }

        // allow CSS styles, will be sanitized by self::washtml_callback()
        if (empty($p['skip_washer_style_callback'])) {
            $washer->add_callback('style', 'rcmail_action_mail_index::washtml_callback');
        }

        // modify HTML links to open a new window if clicked
        if (empty($p['skip_washer_link_callback'])) {
            $washer->add_callback('a', 'rcmail_action_mail_index::washtml_link_callback');
            $washer->add_callback('area', 'rcmail_action_mail_index::washtml_link_callback');
            $washer->add_callback('link', 'rcmail_action_mail_index::washtml_link_callback');
        }

        // Remove non-UTF8 characters (#1487813)
        $html = rcube_charset::clean($html);

        $html = $washer->wash($html);
        self::$REMOTE_OBJECTS = $washer->extlinks;

        // There was no <body>, but a wrapper element is required
        if (!empty($p['inline_html']) && !empty(self::$wash_html_body_attrs)) {
            $html = html::tag('div', self::$wash_html_body_attrs, $html);
        }

        return $html;
    }

    /**
     * Convert the given message part to proper HTML
     * which can be displayed the message view
     *
     * @param string             $body Message part body
     * @param rcube_message_part $part Message part
     * @param array              $p    Display parameters array
     *
     * @return string Formatted HTML string
     */
    public static function print_body($body, $part, $p = [])
    {
        $rcmail = rcmail::get_instance();

        // trigger plugin hook
        $data = $rcmail->plugins->exec_hook('message_part_before',
            [
                'type' => $part->ctype_secondary,
                'body' => $body,
                'id'   => $part->mime_id
            ] + $p + [
                'safe'  => false,
                'plain' => false,
                'inline_html' => true
            ]
        );

        // convert html to text/plain
        if ($data['plain'] && ($data['type'] == 'html' || $data['type'] == 'enriched')) {
            if ($data['type'] == 'enriched') {
                $data['body'] = rcube_enriched::to_html($data['body']);
            }

            $body = $rcmail->html2text($data['body']);
            $part->ctype_secondary = 'plain';
        }
        // text/html
        else if ($data['type'] == 'html') {
            $body = self::wash_html($data['body'], $data, $part->replaces);
            $part->ctype_secondary = $data['type'];
        }
        // text/enriched
        else if ($data['type'] == 'enriched') {
            $body = rcube_enriched::to_html($data['body']);
            $body = self::wash_html($body, $data, $part->replaces);
            $part->ctype_secondary = 'html';
        }
        else {
            // assert plaintext
            $body = $data['body'];
            $part->ctype_secondary = $data['type'] = 'plain';
        }

        // free some memory (hopefully)
        unset($data['body']);

        // plaintext postprocessing
        if ($part->ctype_secondary == 'plain') {
            $flowed = isset($part->ctype_parameters['format']) && $part->ctype_parameters['format'] == 'flowed';
            $delsp  = isset($part->ctype_parameters['delsp']) && $part->ctype_parameters['delsp'] == 'yes';
            $body   = self::plain_body($body, $flowed, $delsp);
        }

        // allow post-processing of the message body
        $data = $rcmail->plugins->exec_hook('message_part_after', [
                'type' => $part->ctype_secondary,
                'body' => $body,
                'id'   => $part->mime_id
            ] + $data);

        return $data['body'];
    }

    /**
     * Handle links and citation marks in plain text message
     *
     * @param string $body   Plain text string
     * @param bool   $flowed Set to True if the source text is in format=flowed
     * @param bool   $delsp  Enable 'delsp' option of format=flowed text
     *
     * @return string Formatted HTML string
     */
    public static function plain_body($body, $flowed = false, $delsp = false)
    {
        $options = [
            'flowed'   => $flowed,
            'replacer' => 'rcmail_string_replacer',
            'delsp'    => $delsp
        ];

        $text2html = new rcube_text2html($body, false, $options);
        $body      = $text2html->get_html();

        return $body;
    }

    /**
     * Callback function for washtml cleaning class
     */
    public static function washtml_callback($tagname, $attrib, $content, $washtml)
    {
        $out = '';

        switch ($tagname) {
        case 'form':
            $out = html::div('form', $content);
            break;

        case 'style':
            // Crazy big styles may freeze the browser (#1490539)
            // remove content with more than 5k lines
            if (substr_count($content, "\n") > 5000) {
                break;
            }

            // decode all escaped entities and reduce to ascii strings
            $decoded  = rcube_utils::xss_entity_decode($content);
            $stripped = preg_replace('/[^a-zA-Z\(:;]/', '', $decoded);

            // now check for evil strings like expression, behavior or url()
            if (!preg_match('/expression|behavior|javascript:|import[^a]/i', $stripped)) {
                if (!$washtml->get_config('allow_remote') && preg_match('/url\((?!data:image)/', $stripped)) {
                    $washtml->extlinks = true;
                }
                else {
                    $out = $decoded;
                }
            }

            if (strlen($out)) {
                $css_prefix = $washtml->get_config('css_prefix');
                $is_safe = $washtml->get_config('allow_remote');
                $body_class = $washtml->get_config('body_class') ?: '';
                $cont_id = $washtml->get_config('container_id') ?: '';
                $cont_id = trim($cont_id . ($body_class ? " div.{$body_class}" : ''));

                $out = rcube_utils::mod_css_styles($out, $cont_id, $is_safe, $css_prefix);

                $out = html::tag('style', ['type' => 'text/css'], $out);
            }

            break;

        case 'body':
            $style = [];
            $attrs = self::$wash_html_body_attrs;

            foreach (html::parse_attrib_string($attrib) as $attr_name => $value) {
                switch (strtolower($attr_name)) {
                    case 'bgcolor':
                        // Get bgcolor, we'll set it as background-color of the message container
                        if (preg_match('/^([a-z0-9#]+)$/i', $value, $m)) {
                            $style['background-color'] = $value;
                        }
                        break;
                    case 'text':
                        // Get text color, we'll set it as font color of the message container
                        if (preg_match('/^([a-z0-9#]+)$/i', $value, $m)) {
                            $style['color'] = $value;
                        }
                        break;
                    case 'background':
                        // Get background, we'll set it as background-image of the message container
                        if (preg_match('/^([^\s]+)$/', $value, $m)) {
                            $style['background-image'] = "url({$value})";
                        }
                        break;
                    default:
                        $attrs[$attr_name] = $value;
                }
            }

            if (!empty($style)) {
                foreach ($style as $idx => $val) {
                    $style[$idx] = $idx . ': ' . $val;
                }

                if (isset($attrs['style'])) {
                    $attrs['style'] = trim($attrs['style'], '; ') . '; ' . implode('; ', $style);
                } else {
                    $attrs['style'] = implode('; ', $style);
                }
            }

            $out = html::tag('div', $attrs, $content);
            self::$wash_html_body_attrs = [];
            break;
        }

        return $out;
    }

    /**
     * Detect if a message attachment is an image (that can be displayed in the browser).
     *
     * @param rcube_message_part $part Message part - attachment
     *
     * @return string|null Image MIME type
     */
    public static function part_image_type($part)
    {
        $mimetype = strtolower($part->mimetype);

        // Skip TIFF/WEBP images if browser doesn't support this format
        // ...until we can convert them to JPEG
        $tiff_support = !empty($_SESSION['browser_caps']) && !empty($_SESSION['browser_caps']['tiff']);
        $tiff_support = $tiff_support || rcube_image::is_convertable('image/tiff');
        $webp_support = !empty($_SESSION['browser_caps']) && !empty($_SESSION['browser_caps']['webp']);
        $webp_support = $webp_support || rcube_image::is_convertable('image/webp');

        if ((!$tiff_support && $mimetype == 'image/tiff') || (!$webp_support && $mimetype == 'image/webp')) {
            return;
        }

        // Content-Type: image/*...
        if (strpos($mimetype, 'image/') === 0) {
            return $mimetype;
        }

        // Many clients use application/octet-stream, we'll detect mimetype
        // by checking filename extension

        // Supported image filename extensions to image type map
        $types = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
        ];

        if ($tiff_support) {
            $types['tif']  = 'image/tiff';
            $types['tiff'] = 'image/tiff';
        }

        if ($webp_support) {
            $types['webp'] = 'image/webp';
        }

        if ($part->filename
            && $mimetype == 'application/octet-stream'
            && preg_match('/\.([^.]+)$/i', $part->filename, $m)
            && ($extension = strtolower($m[1]))
            && isset($types[$extension])
        ) {
            return $types[$extension];
        }
    }

    /**
     * Parse link (a, link, area) attributes and set correct target
     */
    public static function washtml_link_callback($tag, $attribs, $content, $washtml)
    {
        $rcmail = rcmail::get_instance();
        $attrib = html::parse_attrib_string($attribs);

        // Remove non-printable characters in URL (#1487805)
        if (isset($attrib['href'])) {
            $attrib['href'] = preg_replace('/[\x00-\x1F]/', '', $attrib['href']);

            if ($tag == 'link' && preg_match('/^https?:\/\//i', $attrib['href'])) {
                $tempurl = 'tmp-' . md5($attrib['href']) . '.css';
                $_SESSION['modcssurls'][$tempurl] = $attrib['href'];
                $attrib['href'] = $rcmail->url([
                        'task'   => 'utils',
                        'action' => 'modcss',
                        'u'      => $tempurl,
                        'c'      => $washtml->get_config('container_id'),
                        'p'      => $washtml->get_config('css_prefix'),
                ]);
                $content = null;
            }
            else if (preg_match('/^mailto:(.+)/i', $attrib['href'], $mailto)) {
                $url_parts = explode('?', html_entity_decode($mailto[1], ENT_QUOTES, 'UTF-8'), 2);
                $mailto    = $url_parts[0];
                $url       = $url_parts[1] ?? '';

                // #6020: use raw encoding for correct "+" character handling as specified in RFC6068
                $url       = rawurldecode($url);
                $mailto    = rawurldecode($mailto);
                $addresses = rcube_mime::decode_address_list($mailto, null, true);
                $mailto    = [];

                // do sanity checks on recipients
                foreach ($addresses as $idx => $addr) {
                    if (rcube_utils::check_email($addr['mailto'], false)) {
                        $addresses[$idx] = $addr['mailto'];
                        $mailto[]        = $addr['string'];
                    }
                    else {
                        unset($addresses[$idx]);
                    }
                }

                if (!empty($addresses)) {
                    $attrib['href']    = 'mailto:' . implode(',', $addresses);
                    $attrib['onclick'] = sprintf(
                        "return %s.command('compose','%s',this)",
                        rcmail_output::JS_OBJECT_NAME,
                        rcube::JQ(implode(',', $mailto) . ($url ? "?$url" : '')));
                }
                else {
                    $attrib['href']    = '#NOP';
                    $attrib['onclick'] = '';
                }
            }
            else if (!empty($attrib['href']) && $attrib['href'][0] != '#') {
                $attrib['target'] = '_blank';
            }

            // Better security by adding rel="noreferrer" (#1484686)
            if (($tag == 'a' || $tag == 'area') && $attrib['href'] && $attrib['href'][0] != '#') {
                $attrib['rel'] = 'noreferrer';
            }
        }

        // allowed attributes for a|link|area tags
        $allow = ['href','name','target','onclick','id','class','style','title',
            'rel','type','media','alt','coords','nohref','hreflang','shape'];

        return html::tag($tag, $attrib, $content, $allow);
    }

    /**
     * Decode address string and re-format it as HTML links
     */
    public static function address_string($input, $max = null, $linked = false, $addicon = null,
        $default_charset = null, $title = null, $spoofcheck = true)
    {
        $a_parts = rcube_mime::decode_address_list($input, null, true, $default_charset);

        if (!count($a_parts)) {
            return null;
        }

        $rcmail  = rcmail::get_instance();
        $c       = count($a_parts);
        $j       = 0;
        $out     = '';
        $allvalues       = [];
        $shown_addresses = [];
        $show_email      = $rcmail->config->get('message_show_email');

        if ($addicon && !isset($_SESSION['writeable_abook'])) {
            $_SESSION['writeable_abook'] = $rcmail->get_address_sources(true) ? true : false;
        }

        foreach ($a_parts as $part) {
            $j++;

            $name   = $part['name'];
            $mailto = $part['mailto'];
            $string = $part['string'];
            $valid  = rcube_utils::check_email($mailto, false);

            // phishing email prevention (#1488981), e.g. "valid@email.addr <phishing@email.addr>"
            if (!$show_email && $valid && $name && $name != $mailto && preg_match('/@|＠|﹫/', $name)) {
                $name = '';
            }

            // IDNA ASCII to Unicode
            if ($name == $mailto) {
                $name = rcube_utils::idn_to_utf8($name);
            }
            if ($string == $mailto) {
                $string = rcube_utils::idn_to_utf8($string);
            }
            $mailto = rcube_utils::idn_to_utf8($mailto);

            // Homograph attack detection (#6891)
            if ($spoofcheck && !self::$SUSPICIOUS_EMAIL) {
                self::$SUSPICIOUS_EMAIL = rcube_spoofchecker::check($mailto);
            }

            if (self::$PRINT_MODE) {
                $address = '&lt;' . rcube::Q($mailto) . '&gt;';
                if ($name) {
                    $address = rcube::SQ($name) . ' ' . $address;
                }
            }
            else if ($valid) {
                if ($linked) {
                    $attrs = [
                        'href'    => 'mailto:' . $mailto,
                        'class'   => 'rcmContactAddress',
                        'onclick' => sprintf("return %s.command('compose','%s',this)",
                            rcmail_output::JS_OBJECT_NAME, rcube::JQ(format_email_recipient($mailto, $name))),
                    ];

                    if ($show_email && $name && $mailto) {
                        $content = rcube::SQ($name ? sprintf('%s <%s>', $name, $mailto) : $mailto);
                    }
                    else {
                        $content = rcube::SQ($name ?: $mailto);
                        $attrs['title'] = $mailto;
                    }

                    $address = html::a($attrs, $content);
                }
                else {
                    $address = html::span(['title' => $mailto, 'class' => "rcmContactAddress"],
                        rcube::SQ($name ?: $mailto));
                }

                if ($addicon && $_SESSION['writeable_abook']) {
                    $label = $rcmail->gettext('addtoaddressbook');
                    $icon = html::img([
                            'src'   => $rcmail->output->asset_url($addicon, true),
                            'alt'   => $label,
                            'class' => 'noselect',
                    ]);
                    $address .= html::a([
                            'href'    => "#add",
                            'title'   => $label,
                            'class'   => 'rcmaddcontact',
                            'onclick' => sprintf("return %s.command('add-contact','%s',this)",
                                rcmail_output::JS_OBJECT_NAME, rcube::JQ($string)),
                        ],
                        $addicon == 'virtual' ? '' : $icon
                    );
                }
            }
            else {
                $address = $name ? rcube::Q($name) : '';
                if ($mailto) {
                    $address = trim($address . ' ' . rcube::Q($name ? sprintf('<%s>', $mailto) : $mailto));
                }
            }

            $address = html::span('adr', $address);
            $allvalues[] = $address;

            if (empty($moreadrs)) {
                $out .= ($out ? ', ' : '') . $address;
                $shown_addresses[] = $address;
            }

            if ($max && $j == $max && $c > $j) {
                if ($linked) {
                    $moreadrs = $c - $j;
                }
                else {
                    $out .= '...';
                    break;
                }
            }
        }

        if (!empty($moreadrs)) {
            $label = rcube::Q($rcmail->gettext(['name' => 'andnmore', 'vars' => ['nr' => $moreadrs]]));

            if (self::$PRINT_MODE) {
                $out .= ', ' . html::a([
                        'href'    => '#more',
                        'class'   => 'morelink',
                        'onclick' => '$(this).hide().next().show()',
                    ], $label)
                    . html::span(['style' => 'display:none'], join(', ', array_diff($allvalues, $shown_addresses)));
            }
            else {
                $out .= ', ' . html::a([
                        'href'    => '#more',
                        'class'   => 'morelink',
                        'onclick' => sprintf("return %s.simple_dialog('%s','%s',null,{cancel_button:'close'})",
                            rcmail_output::JS_OBJECT_NAME,
                            rcube::JQ(join(', ', $allvalues)),
                            rcube::JQ($title))
                    ], $label);
            }
        }

        return $out;
    }
    /**
     * Return attachment filename, handle empty filename case
     *
     * @param rcube_message_part $attachment Message part
     * @param bool               $display    Convert to a description text for "special" types
     *
     * @return string Filename
     */
    public static function attachment_name($attachment, $display = false)
    {
        $rcmail = rcmail::get_instance();

        $filename = (string) $attachment->filename;
        $filename = str_replace(["\r", "\n"], '', $filename);

        if ($filename === '') {
            if ($attachment->mimetype == 'text/html') {
                $filename = $rcmail->gettext('htmlmessage');
            }
            else {
                $ext      = array_first((array) rcube_mime::get_mime_extensions($attachment->mimetype));
                $filename = $rcmail->gettext('messagepart') . ' ' . $attachment->mime_id;
                if ($ext) {
                    $filename .= '.' . $ext;
                }
            }
        }

        // Display smart names for some known mimetypes
        if ($display) {
            if (preg_match('/application\/(pgp|pkcs7)-signature/i', $attachment->mimetype)) {
                $filename = $rcmail->gettext('digitalsig');
            }
        }

        return $filename;
    }

    public static function search_filter($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmlistfilter';
        }

        if (!self::get_bool_attr($attrib, 'noevent')) {
            $attrib['onchange'] = rcmail_output::JS_OBJECT_NAME . '.filter_mailbox(this.value)';
        }

        // Content-Type values of messages with attachments
        // the same as in app.js:add_message_row()
        $ctypes = ['application/', 'multipart/mixed', 'multipart/signed', 'multipart/report'];

        // Build search string of "with attachment" filter
        $attachment = trim(str_repeat(' OR', count($ctypes)-1));
        foreach ($ctypes as $type) {
            $attachment .= ' HEADER Content-Type ' . rcube_imap_generic::escape($type);
        }

        $select = new html_select($attrib);
        $select->add($rcmail->gettext('all'), 'ALL');
        $select->add($rcmail->gettext('unread'), 'UNSEEN');
        $select->add($rcmail->gettext('flagged'), 'FLAGGED');
        $select->add($rcmail->gettext('unanswered'), 'UNANSWERED');
        if (!$rcmail->config->get('skip_deleted')) {
            $select->add($rcmail->gettext('deleted'), 'DELETED');
            $select->add($rcmail->gettext('undeleted'), 'UNDELETED');
        }
        $select->add($rcmail->gettext('withattachment'), $attachment);
        $select->add($rcmail->gettext('priority').': '.$rcmail->gettext('highest'), 'HEADER X-PRIORITY 1');
        $select->add($rcmail->gettext('priority').': '.$rcmail->gettext('high'), 'HEADER X-PRIORITY 2');
        $select->add($rcmail->gettext('priority').': '.$rcmail->gettext('normal'), 'NOT HEADER X-PRIORITY 1 NOT HEADER X-PRIORITY 2 NOT HEADER X-PRIORITY 4 NOT HEADER X-PRIORITY 5');
        $select->add($rcmail->gettext('priority').': '.$rcmail->gettext('low'), 'HEADER X-PRIORITY 4');
        $select->add($rcmail->gettext('priority').': '.$rcmail->gettext('lowest'), 'HEADER X-PRIORITY 5');

        $rcmail->output->add_gui_object('search_filter', $attrib['id']);

        $selected = rcube_utils::get_input_string('_filter', rcube_utils::INPUT_GET);

        if (!$selected && !empty($_REQUEST['_search'])) {
            $selected = $_SESSION['search_filter'];
        }

        return $select->show($selected ?: 'ALL');
    }

    public static function search_interval($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmsearchinterval';
        }

        $select = new html_select($attrib);
        $select->add('', '');

        foreach (['1W', '1M', '1Y', '-1W', '-1M', '-1Y'] as $value) {
            $select->add($rcmail->gettext('searchinterval' . $value), $value);
        }

        $rcmail->output->add_gui_object('search_interval', $attrib['id']);

        return $select->show(!empty($_REQUEST['_search']) ? $_SESSION['search_interval'] : '');
    }

    public static function message_error()
    {
        $rcmail = rcmail::get_instance();

        // ... display message error page
        if ($rcmail->output->template_exists('messageerror')) {
            // Set env variables for messageerror.html template
            if ($rcmail->action == 'show') {
                $mbox_name = $rcmail->storage->get_folder();

                $rcmail->output->set_env('mailbox', $mbox_name);
                $rcmail->output->set_env('uid', null);
            }

            $rcmail->output->show_message('messageopenerror', 'error');
            $rcmail->output->send('messageerror');
        }
        else {
            $rcmail->raise_error(['code' => 410], false, true);
        }
    }

    public static function message_import_form($attrib = [])
    {
        $rcmail = rcmail::get_instance();

        $rcmail->output->add_label('selectimportfile', 'importwait', 'importmessages', 'import');

        $description = $rcmail->gettext('mailimportdesc');
        $input_attr  = [
            'multiple' => true,
            'name'     => '_file[]',
            'accept'   => '.eml,.mbox,.msg,message/rfc822,text/*',
        ];

        if (class_exists('ZipArchive', false)) {
            $input_attr['accept'] .= '.zip,application/zip,application/x-zip';
            $description          .= ' ' . $rcmail->gettext('mailimportzip');
        }

        $attrib['prefix'] = html::tag('input', ['type' => 'hidden', 'name' => '_unlock', 'value' => ''])
            . html::tag('input', ['type' => 'hidden', 'name' => '_framed', 'value' => '1'])
            . html::p(null, $description);

        return self::upload_form($attrib, 'importform', 'import-messages', $input_attr);
    }

    // Return mimetypes supported by the browser
    public static function supported_mimetypes()
    {
        $rcmail = rcube::get_instance();

        // mimetypes supported by the browser (default settings)
        $mimetypes = (array) $rcmail->config->get('client_mimetypes');

        // Remove unsupported types, which makes that attachment which cannot be
        // displayed in a browser will be downloaded directly without displaying an overlay page
        if (empty($_SESSION['browser_caps']['pdf']) && ($key = array_search('application/pdf', $mimetypes)) !== false) {
            unset($mimetypes[$key]);
        }

        if (empty($_SESSION['browser_caps']['flash']) && ($key = array_search('application/x-shockwave-flash', $mimetypes)) !== false) {
            unset($mimetypes[$key]);
        }

        // We cannot securely preview XML files as we do not have a proper parser
        if (($key = array_search('text/xml', $mimetypes)) !== false) {
            unset($mimetypes[$key]);
        }

        foreach (['tiff', 'webp'] as $type) {
            if (empty($_SESSION['browser_caps'][$type]) && ($key = array_search('image/' . $type, $mimetypes)) !== false) {
                // can we convert it to jpeg?
                if (!rcube_image::is_convertable('image/' . $type)) {
                    unset($mimetypes[$key]);
                }
            }
        }

        // @TODO: support mail preview for compose attachments
        if ($rcmail->action != 'compose' && !in_array('message/rfc822', $mimetypes)) {
            $mimetypes[] = 'message/rfc822';
        }

        return array_values($mimetypes);
    }
}
