<?php
/**
 * Processing an advanced search over an E-Mail Account
 *
 * @version 2.1.5
 * @licence GNU GPLv3+
 * @author  Wilwert Claude
 * @author  Ludovicy Steve
 * @author  Chris Moules
 * @website http://www.gms.lu
 */

class advanced_search extends rcube_plugin
{

    /**
     * Instance of rcmail
     *
     * @var object
     * @access private
     */
    private $rc;

    public $task = 'mail|settings';

    /**
     * Plugin config
     *
     * @var array
     * @access private
     */
    private $config;

    /**
     * Localization strings
     *
     * @var array
     * @access private
     */
    private $i18n_strings = array();

    /**
     * Initialisation of the plugin
     *
     * @access public
     * @return null
     */
    public function init()
    {
        $this->rc = rcmail::get_instance();
        $this->load_config("config-default.inc.php");
        $this->load_config();
        $this->config = $this->rc->config->get('advanced_search_plugin');
        $this->register_action('plugin.display_advanced_search', array($this, 'display_advanced_search'));
        $this->register_action('plugin.trigger_search', array($this, 'trigger_search'));
        $this->register_action('plugin.trigger_search_pagination', array($this, 'trigger_search_pagination'));
        $this->register_action('plugin.save_search', array($this, 'save_search'));
        $this->register_action('plugin.delete_search', array($this, 'delete_search'));
        $this->register_action('plugin.get_saved_search', array($this, 'get_saved_search'));

        $this->skin = $this->rc->config->get('skin');
        $this->add_texts('localization', true);
        $this->populate_i18n();
        if(isset($this->config['criteria'])) {
            foreach($this->config['criteria'] as $key => $translation) {
                $this->config['criteria'][$key] = $this->gettext($key);
            }
        }
        $this->include_script('advanced_search.js');

        if ($this->rc->task == 'mail') {
            $file = 'skins/' . $this->skin . '/advanced_search.css';

            if (file_exists($this->home . '/' . $file)) {
                $this->include_stylesheet($file);
            } else {
                $this->include_stylesheet('skins/default/advanced_search.css');
            }

            if (empty($this->rc->action)) {
                $this->add_menu_entry();
            }
        } elseif ($this->rc->task == 'settings') {
            $this->add_hook('preferences_list', array($this, 'preferences_list'));
            $this->add_hook('preferences_save', array($this, 'preferences_save'));
            $this->add_hook('preferences_sections_list', array($this, 'preferences_section'));
            $file = 'skins/' . $this->skin . '/advanced_search.css';
            if (file_exists($this->home . '/' . $file)) {
                $this->include_stylesheet($file);
            } else {
                $this->include_stylesheet('skins/default/advanced_search.css');
            }
        }

        $this->add_hook('startup', array($this, 'startup'));
    }


    public function startup($args)
    {
        $search = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GET);
        if (!isset($search)) {
            $search = rcube_utils::get_input_value('_search', rcube_utils::INPUT_POST);
        }
        $rsearch = $search == 'advanced_search_active';
        $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GET);
        $draft_uid = rcube_utils::get_input_value('_draft_uid', rcube_utils::INPUT_GET);
        $mbox = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_GET);
        $page = rcube_utils::get_input_value('_page', rcube_utils::INPUT_GET);
        $sort = rcube_utils::get_input_value('_sort', rcube_utils::INPUT_GET);

        if (!empty($uid)) {
            $parts = explode('__MB__', $uid);
            if (count($parts) == 2) {
                $search = 'advanced_search_active';
            }
        }
        if (!empty($draft_uid)) {
            $parts = explode('__MB__', $draft_uid);
            if (count($parts) == 2) {
                $search = 'advanced_search_active';
            }
        }

        if ($search == 'advanced_search_active') {
            if ($args['action'] == 'show' && !empty($uid)) {
                $parts = explode('__MB__', $uid);
                $uid = $parts[0];
                $this->rc->output->redirect(array('_task' => 'mail', '_action' => $args['action'], '_mbox' => $mbox, '_uid' => $uid));
            }
            if ($args['action'] == 'compose') {
                $draft_uid = rcube_utils::get_input_value('_draft_uid', rcube_utils::INPUT_GET);
                $parts = explode('__MB__', $draft_uid);
                $draft_uid = $parts[0];
                if (!empty($draft_uid)) {
                    $this->rc->output->redirect(array('_task' => 'mail', '_action' => $args['action'], '_mbox' => $mbox, '_draft_uid' => $draft_uid));
                }
            }
            if ($args['action'] == 'list' && $rsearch) {
                $this->rc->output->command('advanced_search_active', '_page=' . $page . '&_sort=' . $sort);
                $this->rc->output->send();
                $args['abort'] = true;
            }
            if ($args['action'] == 'mark') {
                $flag = rcube_utils::get_input_value('_flag', rcube_utils::INPUT_POST);
                $uid = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);

                $post_str = '_flag=' . $flag . '&_uid=' . $uid;
                if ($quiet = rcube_utils::get_input_value('_quiet', rcube_utils::INPUT_POST)) {
                    $post_str .= '&_quiet=' . $quiet;
                }
                if ($from = rcube_utils::get_input_value('_from', rcube_utils::INPUT_POST)) {
                    $post_str .= '&_from=' . $from;
                }
                if ($count = rcube_utils::get_input_value('_count', rcube_utils::INPUT_POST)) {
                    $post_str .= '&_count=' . $count;
                }
                if ($ruid = rcube_utils::get_input_value('_ruid', rcube_utils::INPUT_POST)) {
                    $post_str .= '&_ruid=' . $ruid;
                }
                $this->rc->output->command('label_mark', $post_str);
                $this->rc->output->send();
                $args['abort'] = true;
            }

        } else {
            if ($args['action'] != 'plugin.get_saved_search' && $args['action'] != 'plugin.save_search' && $args['action'] != 'plugin.delete_search') {
                $this->rc->output->command('plugin.advanced_search_del_header', array());
            }
        }
    }

    /**
     * This function populates an array with localization texts.
     * This is needed as ew are using a lot of localizations from core.
     * The core localizations are not avalable directly in JS
     *
     * @access private
     * @return null
     */
    private function populate_i18n()
    {
        $core = array('advsearch', 'search', 'resetsearch', 'addfield', 'delete', 'cancel');

        foreach ($core as $label) {
            $this->i18n_strings[$label] = $this->rc->gettext($label);
        }

        $local = array('in', 'and', 'or', 'not', 'where', 'exclude', 'andsubfolders', 'allfolders', 'save_the_search', 'has_been_saved', 'deletesearch', 'has_been_deleted');

        foreach ($local as $label) {
            $this->i18n_strings[$label] = $this->gettext($label);
        }
    }

    /**
     * This adds a button into the configured menu to use the advanced search
     *
     * @access public
     * @return null
     */
    public function add_menu_entry()
    {
        $displayOptions = $this->rc->config->get('advanced_search_display_options', array());
        $target_menu = (isset($displayOptions['target_menu']) && !empty($displayOptions['target_menu'])) ? $displayOptions['target_menu'] : $this->config['target_menu'];
        if ($target_menu != 'toolbar') {
            $this->api->add_content(
                html::tag(
                    'li',
                    null,
                    $this->api->output->button(
                        array(
                            'command'    => 'plugin.advanced_search',
                            'label'      => 'advsearch',
                            'type'       => 'link',
                            'classact'   => 'icon advanced-search active',
                            'class'      => 'icon advanced-search',
                            'innerclass' => 'icon advanced-search'
                        )
                    )
                ),
                $target_menu
            );
        } else {
            $this->api->add_content(
                $this->api->output->button(
                    array(
                        'command'    => 'plugin.advanced_search',
                        'title'      => 'advsearch',
                        'label'      => 'search',
                        'type'       => 'link',
                        'classact'   => 'button advanced-search active',
                        'class'      => 'button advanced-search',
                        'innerclass' => 'button advanced-search',
                    )
                ),
                $target_menu
            );
        }
    }

    /**
     * This function quotes some specific values based on their data type
     *
     * @param mixed $input The value to get quoted
     * @access public
     * @return The quoted value
     */
    public function quote($value)
    {
        if (getType($value) == 'string') {
            if (!preg_match('/"/', $value)) {
                $value = preg_replace('/^"/', '', $value);
                $value = preg_replace('/"$/', '', $value);
                $value = preg_replace('/"/', '\\"', $value);
            }

            $value = '"' . $value . '"';
        }

        return $value;
    }

    /**
     * This function generates the IMAP compatible search query based on the request data (by javascript)
     *
     * @param array $input The raw criteria data sent by javascript
     * @access private
     * @return string or int
     */
    private function process_search_part($search_part)
    {
        $command_str = '';
        $flag = false;

        // Check for valid input
        if (!array_key_exists($search_part['filter'], $this->config['criteria'])) {
            $this->rc->output->show_message($this->gettext('internalerror'), 'error');

            return 0;
        }
        if (in_array($search_part['filter'], $this->config['flag_criteria'])) {
            $flag = true;
        }
        if (!$flag && !(isset($search_part['filter-val']) && $search_part['filter-val'] != '')) {
            return 1;
        }

        // Negate part
        if ($search_part['not'] == 'true') {
            $command_str .= 'NOT ';
        }

        $command_str .= $search_part['filter'];

        if (!$flag) {
            if (in_array($search_part['filter'], $this->config['date_criteria'])) {
                // Take date format from user environment
                $date_format = $this->rc->config->get('date_format', 'Y-m-d');
                // Try to use PHP5.2+ DateTime but fallback to ugly old method
                if (class_exists('DateTime')) {
                    $date = DateTime::createFromFormat($date_format, $search_part['filter-val']);
                    $command_str .= ' ' . $this->quote($date->format("d-M-Y"));
                } else {
                    $date_format = preg_replace('/(\w)/', '%$1', $date_format);
                    $date_array = strptime($search_part['filter-val'], $date_format);
                    $unix_ts = mktime($date_array['tm_hour'], $date_array['tm_min'], $date_array['tm_sec'], $date_array['tm_mon']+1, $date_array['tm_mday'], $date_array['tm_year']+1900);
                    $command_str .= ' ' . $this->quote(date("d-M-Y", $unix_ts));
                }
            } elseif (in_array($search_part['filter'], $this->config['email_criteria'])) {
                // Strip possible ',' added by autocomplete
                $command_str .= ' ' . $this->quote(trim($search_part['filter-val'], " \t,"));
            } else {
                // Don't try to use a value for a binary flag object
                $command_str .= ' ' . $this->quote($search_part['filter-val']);
            }
        }

        return $command_str;
    }

    /**
     * This function generates the IMAP compatible search query based on the request data (by javascript)
     *
     * @param array $input The raw criteria data sent by javascript
     * @access public
     * @return The final search command
     */
    public function get_search_query($input)
    {
        $command = array();

        foreach ($input as $search_part) {
            // Skip excluded parts
            if ($search_part['excluded'] == 'true') {
                continue;
            }
            if (! $part_command = $this->process_search_part($search_part)) {
                return 0;
            }
            // Skip invalid parts
            if ($part_command === 1) {
                continue;
            }

            $command[] = array('method' => isset($search_part['method']) ? $search_part['method'] : 'and',
                               'command' => $part_command);
        }

        $command_string = $this->build_search_string($command);

        return $command_string;
    }

    /**
     * This function converts the preconfigured query parts (as array) into an IMAP compatible string
     *
     * @param array $command_array An array containing the advanced search criteria
     * @access public
     * @return The command string
     */
    private function build_search_string($command_array)
    {
        $command = array();
        $paranthesis = 0;
        $prev_method = null;
        $next_method = null;
        $cnt = count($command_array);

        foreach ($command_array as $k => $v) {
            $part = '';
            $next_method = 'unknown';

            // Lookup next method
            if ($k < $cnt-1) {
                $next_method = $command_array[$k+1]['method'];
            }

            // If previous option was OR, close any open brakets
            if ($paranthesis > 0 && $prev_method == 'or' && $v['method'] != 'or') {
                for (; $paranthesis > 0; $paranthesis--) {
                    $part .= ')';
                }
            }

            // If there are two consecutive ORs, add brakets
            // If the next option is a new OR, add the prefix here
            // If the next option is _not_ an OR, and the current option is AND, prefix ALL
            if ($next_method == 'or') {
                if ($v['method'] == 'or') {
                    $part .= ' (';
                    $paranthesis++;
                }
                $part .= 'OR ';
            } elseif ($v['method'] == 'and') {
                $part .= 'ALL ';
            }

            $part .= $v['command'];

            // If this is the end of the query, and we have open brakets, close them
            if ($k == $cnt-1 && $paranthesis > 0) {
                for (; $paranthesis > 0; $paranthesis--) {
                    $part .= ')';
                }
            }

            $prev_method = $v['method'];
            $command[] = $part;
        }

        $command = implode(' ', $command);

        return $command;
    }

    /**
     * This functions sends the initial data to the client side where a form (in dialog) is built for the advanced search
     *
     * @access public
     * @return null
     */
    public function display_advanced_search()
    {
        $ret = array('html' => $this->generate_searchbox(),
                     'row' => $this->add_row(),
                     'saved_searches' => $this->get_saved_search_names(),
                     'title' => $this->i18n_strings['advsearch'],
                     'date_criteria' => $this->config['date_criteria'],
                     'flag_criteria' => $this->config['flag_criteria'],
                     'email_criteria' => $this->config['email_criteria']);

        $this->rc->output->command('plugin.show', $ret);
    }

    public function generate_searchbox()
    {
        $search_button = new html_inputfield(array('type' => 'submit', 'name' => 'search', 'class' => 'button mainaction', 'value' => $this->i18n_strings['search']));
        $reset_button = new html_inputfield(array('type' => 'reset', 'name' => 'reset', 'class' => 'button reset', 'value' => $this->i18n_strings['resetsearch']));
        $save_button = html::tag('input', array('type' => 'submit', 'name' => 'save_the_search', 'id'=> 'save_the_search', 'class' => 'button save_search', 'value' => $this->i18n_strings['save_the_search']));
        $delete_button = new html_inputfield(array('type' => 'submit', 'name' => 'delete', 'style' => 'display: none;', 'class' => 'button delete_search', 'value' => $this->i18n_strings['deletesearch']));

        $layout_table = new html_table();
        $layout_table->add(null, $search_button->show());
        $folderConfig = array('name' => 'folder');
        $layout_table->add(
            null,
            $this->i18n_strings['in'] . ': ' .
            $this->folder_selector($folderConfig)->show($this->rc->storage->get_folder()) .
            html::span(
                array('class' => 'sub-folders'),
                $this->i18n_strings['andsubfolders'] . ': ' .
                html::tag(
                    'input',
                    array('type' => 'checkbox', 'name' => 'subfolder'),
                    null
                )
            ) .
            $this->i19n_strings['where']
        );
        $first_row = $this->add_row(true);
        $layout_table->add_row();
        $layout_table->add(array('class' => 'adv-search-and-or'), null);
        $layout_table->add(null, $first_row);
        $layout_table->add_row();
        $layout_table->add(null, $search_button->show());
        $layout_table->add(null, $save_button . ' ' . $reset_button->show() . ' ' . $delete_button->show());

        return html::tag(
            'div',
            array('id' => 'adsearch-popup'),
            html::tag(
                'form',
                array('method' => 'post', 'action' => '#'),
                $layout_table->show()
            )
        );
    }

    /**
     * This function is used to render the html of the advanced search form and also
     * the later following rows are created by this function
     *
     * @param array   $folders Array of folders
     * @param boolean $first   True if form gets created, False to create a new row
     * @access public
     * @return string The final html
     */
    public function add_row($first = false)
    {
        $row_html = '';
        $optgroups = '';

        $criteria = $this->config['criteria'];
        $all_criteria = array(
            $this->gettext('Common') => $this->config['prefered_criteria'],
            $this->gettext('Addresses') => $this->config['email_criteria'],
            $this->gettext('Dates') => $this->config['date_criteria'],
            $this->gettext('Flags') => $this->config['flag_criteria'],
            $this->gettext('Other') => $this->config['other_criteria'],
        );

        foreach ($all_criteria as $label => $specific_criteria) {
            $options = '';

            foreach ($specific_criteria as $value) {
                $options .= html::tag('option', array('value' => $value), $criteria[$value]);
            }

            $optgroups .= html::tag('optgroup', array('label' => $label), $options);
        }

        $tmp = html::tag('select', array('name' => 'filter'), $optgroups);
        $tmp .= $this->i18n_strings['not'] . ':' . html::tag('input', array('type' => 'checkbox', 'name' => 'not'), null);
        $tmp .= html::tag('input', array('type' => 'text', 'name' => 'filter-val'));
        $tmp .= $this->i18n_strings['exclude'] . ':' . html::tag('input', array('type' => 'checkbox', 'name' => 'filter-exclude'), null);
        $tmp .= html::tag('button', array('name' => 'add', 'class' => 'add'), $this->i18n_strings['addfield']);

        if ($first) {
            $row_html = $tmp;
        } else {
            $and_or_select = new html_select(array('name' => 'method'));
            $and_or_select->add($this->i18n_strings['and'], 'and');
            $and_or_select->add($this->i18n_strings['or'], 'or');
            $tmp .= html::tag('button', array('name' => 'delete', 'class' => 'delete'), $this->i18n_strings['delete']);
            $row_html = html::tag(
                'tr',
                null,
                html::tag(
                    'td',
                    array('class' => 'adv-search-and-or'),
                    $and_or_select->show()
                ) .
                html::tag(
                    'td',
                    null,
                    $tmp
                )
            );
        }

        return $row_html;
    }

    /**
     * Return folders list as html_select object
     *
     * This is a copy of the core function and adapted to fit
     * the needs of the advanced_search function
     *
     * @param array $p Named parameters
     *
     * @return html_select HTML drop-down object
     */
    public function folder_selector($p = array())
    {
        $p += array('maxlength' => 100, 'realnames' => false, 'is_escaped' => true);
        $a_mailboxes = array();
        $storage = $this->rc->get_storage();

        $list = $storage->list_folders_subscribed();
        $delimiter = $storage->get_hierarchy_delimiter();

        foreach ($list as $folder) {
            $this->rc->build_folder_tree($a_mailboxes, $folder, $delimiter);
        }

        $select = new html_select($p);
        $select->add($this->i18n_strings['allfolders'], "all");

        $this->rc->render_folder_tree_select($a_mailboxes, $mbox, $p['maxlength'], $select, $p['realnames'], 0, $p);

        return $select;
    }

    public function trigger_search_pagination($param)
    {
        $_GET['search'] = $_SESSION['av_search'];
        $_GET['folder'] = $_SESSION['av_folder'];
        $_GET['sub_folders'] = $_SESSION['av_sub_folders'];
        $this->trigger_search(true);
    }

    /**
     * Here is where the actual query is fired to the imap server and the result is evaluated and sent back to the client side
     *
     * @access public
     * @return null
     */
    public function trigger_search($inPagination = false)
    {
        $search = rcube_utils::get_input_value('search', rcube_utils::INPUT_GPC);
        // reset list_page and old search results
        $this->rc->storage->set_page(1);
        $this->rc->storage->set_search_set(null);
        $page = rcube_utils::get_input_value('_page', rcube_utils::INPUT_GPC);
        $page = $page ? $page : 1;
        $pagesize = $this->rc->storage->get_pagesize();

        if (!empty($search)) {
            $mbox = rcube_utils::get_input_value('folder', rcube_utils::INPUT_GPC);
            $imap_charset = RCUBE_CHARSET;
            $sort_column = rcmail_sort_column();
            $search_str = $this->get_search_query($search);
            $sub_folders = rcube_utils::get_input_value('sub_folders', rcube_utils::INPUT_GPC) == 'true';
            $folders = array();
            $result_h = array();
            $count = 0;
            $new_id = 1;
            $current_mbox = $this->rc->storage->get_folder();
            $uid_list = array();
            //Store information in session for pagination
            $_SESSION['av_search'] = $search;
            $_SESSION['av_folder'] = $mbox;
            $_SESSION['av_sub_folders'] = rcube_utils::get_input_value('sub_folders', rcube_utils::INPUT_GPC);
            $nosub = $sub_folders;
            $folders = $this->rc->get_storage()->list_folders_subscribed();
            if (empty($folders) || ($sub_folders === false && $mbox !== 'all')) {
                $folders = array($mbox);
            } elseif ($mbox !== 'all') {
                if ($sub_folders === false) {
                    $folders = array($mbox);
                } else {
                    $folders = $this->rc->get_storage()->list_folders_subscribed_direct($mbox);
                }
            }
            $md5folders = array();
            foreach ($folders as $folder) {
                $md5folders[md5($folder)] = $folder;
            }
            $this->rc->output->set_env('as_md5_folders', $md5folders);

            if ($search_str) {
                $res = $this->perform_search($search_str, $folders, $page);
                $count = $res['count'];
            }


            if ($count > 0) {
                $_SESSION['advanced_search']['uid_list'] = $uid_list;
                if ($search_str && $inPagination == false) {
                    $this->rc->output->show_message('searchsuccessful', 'confirmation', array('nr' => $count));
                }
            } elseif ($err_code = $this->rc->storage->get_error_code()) {
                rcmail_display_server_error();
            } else {
                $this->rc->output->show_message('searchnomatch', 'notice');
            }

            $current_folder = rcube_utils::get_input_value('current_folder', rcube_utils::INPUT_GPC);

            $this->rc->output->set_env('search_request', 'advanced_search_active');
            $this->rc->output->set_env('messagecount', $count);
            $this->rc->output->set_env('pagecount', ceil($count / $pagesize));
            $this->rc->output->set_env('exists', $this->rc->storage->count($current_folder, 'EXISTS'));
            $this->rc->output->command('set_rowcount', rcmail_get_messagecount_text($count, $page));
            $this->rc->output->command('plugin.search_complete');
            $this->rc->output->send();
        }
    }

    /**
     * return javascript commands to add rows to the message list
     */
    public function rcmail_js_message_list($a_headers, $insert_top = false, $a_show_cols = null, $avmbox = false, $avcols = array(), $showMboxColumn = false)
    {
        global $CONFIG, $RCMAIL, $OUTPUT;
        $uid_mboxes = array();

        if (empty($a_show_cols)) {
            if (!empty($_SESSION['list_attrib']['columns'])) {
                $a_show_cols = $_SESSION['list_attrib']['columns'];
            } else {
                $a_show_cols = is_array($CONFIG['list_cols']) ? $CONFIG['list_cols'] : array('subject');
            }
        } else {
            if (!is_array($a_show_cols)) {
                $a_show_cols = preg_split('/[\s,;]+/', strip_quotes($a_show_cols));
            }
            $head_replace = true;
        }

        $mbox = $RCMAIL->storage->get_folder();

        // make sure 'threads' and 'subject' columns are present
        if (!in_array('subject', $a_show_cols)) {
            array_unshift($a_show_cols, 'subject');
        }
        if (!in_array('threads', $a_show_cols)) {
            array_unshift($a_show_cols, 'threads');
        }
        $_SESSION['list_attrib']['columns'] = $a_show_cols;

        // Make sure there are no duplicated columns (#1486999)
        $a_show_cols = array_merge($a_show_cols, $avcols);
        $a_show_cols = array_unique($a_show_cols);

        // Plugins may set header's list_cols/list_flags and other rcube_message_header variables
        // and list columns
        $plugin = $RCMAIL->plugins->exec_hook(
            'messages_list',
            array('messages' => $a_headers, 'cols' => $a_show_cols)
        );
        $a_show_cols = $plugin['cols'];
        $a_headers   = $plugin['messages'];
        $thead = $head_replace ? rcmail_message_list_head($_SESSION['list_attrib'], $a_show_cols) : null;

        // get name of smart From/To column in folder context
        if (($f = array_search('fromto', $a_show_cols)) !== false) {
            $smart_col = rcmail_message_list_smart_column_name();
        }
        if ($this->coltypesSet == false) {
            $OUTPUT->command('set_message_coltypes', $a_show_cols, $thead, $smart_col);
            if ($showMboxColumn === true) {
                $OUTPUT->command('plugin.advanced_search_add_header', array());
            }
            $this->coltypesSet = true;
        }

        if (empty($a_headers)) {
            return;
        }

        // remove 'threads', 'attachment', 'flag', 'status' columns, we don't need them here
        foreach (array('threads', 'attachment', 'flag', 'status', 'priority') as $col) {
            if (($key = array_search($col, $a_show_cols)) !== false) {
                unset($a_show_cols[$key]);
            }
        }

        // loop through message headers
        foreach ($a_headers as $n => $header) {
            if (empty($header)) {
                continue;
            }
            $a_msg_cols = array();
            $a_msg_flags = array();
            // format each col; similar as in rcmail_message_list()
            foreach ($a_show_cols as $col) {
                $col_name = $col == 'fromto' ? $smart_col : $col;

                if (in_array($col_name, array('from', 'to', 'cc', 'replyto'))) {
                    $cont = rcmail_address_string($header->$col_name, 3, false, null, $header->charset);
                } elseif ($col == 'subject') {
                    $cont = trim(rcube_mime::decode_header($header->$col, $header->charset));
                    if (!$cont) {
                        $cont = rcube_label('nosubject');
                    }
                    $cont = rcube::Q($cont);
                } elseif ($col == 'size') {
                    $cont = rcmail::get_instance()->show_bytes($header->$col);
                } elseif ($col == 'date') {
                    $cont = rcmail::get_instance()->format_date($header->date);
                } else {
                    $cont = rcube::Q($header->$col);
                }
                $a_msg_cols[$col] = $cont;
            }

            $a_msg_flags = array_change_key_case(array_map('intval', (array) $header->flags));
            if ($header->depth) {
                $a_msg_flags['depth'] = $header->depth;
            } elseif ($header->has_children) {
                $roots[] = $header->uid;
            }
            if ($header->parent_uid) {
                $a_msg_flags['parent_uid'] = $header->parent_uid;
            }
            if ($header->has_children) {
                $a_msg_flags['has_children'] = $header->has_children;
            }
            if ($header->unread_children) {
                $a_msg_flags['unread_children'] = $header->unread_children;
            }
            if ($header->others['list-post']) {
                $a_msg_flags['ml'] = 1;
            }
            if ($header->priority) {
                $a_msg_flags['prio'] = (int) $header->priority;
            }
            $a_msg_flags['ctype'] = rcube::Q($header->ctype);
            $a_msg_flags['mbox'] = $mbox;
            if (!empty($header->list_flags) && is_array($header->list_flags)) {
                $a_msg_flags = array_merge($a_msg_flags, $header->list_flags);
            }
            if (!empty($header->list_cols) && is_array($header->list_cols)) {
                $a_msg_cols = array_merge($a_msg_cols, $header->list_cols);
            }
            if ($showMboxColumn === true) {
                $a_msg_flags['avmbox'] = $avmbox;
            }

            $OUTPUT->command(
                'add_message_row',
                $header->uid . '__MB__' . md5($mbox),
                $a_msg_cols,
                $a_msg_flags,
                $insert_top
            );
            $id = $header->uid . '__MB__' . md5($mbox);
            $uid_mboxes[$id] = array('uid' => $header->uid, 'mbox' => $mbox, 'md5mbox' => md5($mbox));
        }

        if ($RCMAIL->storage->get_threading()) {
            $OUTPUT->command('init_threads', (array) $roots, $mbox);
        }

        return $uid_mboxes;
    }


    private function do_pagination($folders, $onPage)
    {
        $perPage = $this->rc->storage->get_pagesize();
        $from = $perPage * $onPage - $perPage + 1;
        $to = $from + $perPage - 1;
        $got = 0;
        $pos = 0;
        $cbox = "";
        $boxStart = 0;
        $boxStop = 0;
        $fetch = array();
        foreach ($folders as $box => $num) {
            $i = $num;
            if ($box != $cbox) {
                $boxStart = 0;
                $boxStop = 0;
                $cbox = $box;
            }
            while ($i--) {
                $pos++;
                $boxStart++;
                if ($pos >= $from && $pos <= $to) {
                    if (!isset($fetch[$box])) {
                        $fetch[$box] = array("from" => $boxStart);
                    }
                    $fetch[$box]['to'] = $boxStart;
                    $got++;
                }
            }
            if ($got >= $perPage) {
                break;
            }
        }

        return $fetch;
    }

    /**
     * Save advanced search preferences
     *
     * @access  public
     */
    public function preferences_save($args)
    {
        if ($args['section'] != 'advancedsearch') {
            return;
        }
        $rcmail = rcmail::get_instance();

        $displayOptions = array();
        $displayOptions['_show_message_label_header'] = rcube_utils::get_input_value('_show_message_label_header', rcube_utils::INPUT_POST) == 1 ? true : false;
        $displayOptions['_show_message_mbox_info'] = rcube_utils::get_input_value('_show_message_mbox_info', rcube_utils::INPUT_POST) == 1 ? true : false;
        $displayOptions['target_menu'] = rcube_utils::get_input_value('button_display_option', rcube_utils::INPUT_POST);

        $args['prefs']['advanced_search_display_options'] = $displayOptions;

        return($args);
    }

    /**
     * Add a section advanced search to the preferences section list
     *
     * @access  public
     */
    public function preferences_section($args)
    {
        $args['list']['advancedsearch'] = array(
            'id' => 'advancedsearch',
            'section' => rcube::Q($this->gettext('advancedsearch'))
        );

        return($args);
    }

    /**
     * Display advanced search configuration in user preferences tab
     *
     * @access  public
     */
    public function preferences_list($args)
    {
        if ($args['section'] == 'advancedsearch') {

            $this->rc = rcmail::get_instance();
            $args['blocks']['label_display_options'] = array('options' => array(), 'name' => rcube::Q($this->gettext('label_display_options')));

            $displayOptions = $this->rc->config->get('advanced_search_display_options', array());
            $target_menu = (isset($displayOptions['target_menu']) && !empty($displayOptions['target_menu'])) ? $displayOptions['target_menu'] : $this->config['target_menu'];
            $options = '';
            $optarg = array('value' => 'messagemenu');
            if ($target_menu == 'messagemenu') {
                $optarg['selected'] = 'selected';
                $target_image = 'menu_location_a.jpg';
            }
            $options .= html::tag('option', $optarg, rcube::Q($this->gettext('display_in_messagemenu')));
            $optarg = array('value' => 'toolbar');
            if ($target_menu == 'toolbar') {
                $optarg['selected'] = 'selected';
                $target_image = 'menu_location_b.jpg';
            }
            $options .= html::tag('option', $optarg, rcube::Q($this->gettext('display_in_toolbar')));
            $select = html::tag('select', array('name' => 'button_display_option', 'id' => 'button_display_option'), $options);

            $label1 = html::label('_show_message_label_header', rcube::Q($this->gettext('mailbox_headers_in_results')));
            $label2 = html::label('_show_message_mbox_info', rcube::Q($this->gettext('mailbox_info_in_results')));
            $label3 = html::label('button_display_option', rcube::Q($this->gettext('show_advanced_search')));

            $arg1 = array('name' => '_show_message_label_header', 'id' => '_show_message_label_header', 'type' => 'checkbox', 'title' => "", 'class' => 'watermark linput', 'value' => 1);
            if (isset($displayOptions['_show_message_label_header']) && $displayOptions['_show_message_label_header'] === true) {
                $arg1['checked'] = 'checked';
                $img1class = 'enabled';
            } else {
                $img1class = 'disabled';            
            }
            $check1 = html::tag('input', $arg1);
            $arg2 = array('name' => '_show_message_mbox_info', 'id' => '_show_message_mbox_info', 'type' => 'checkbox', 'title' => "", 'class' => 'watermark linput', 'value' => 1);
            if (isset($displayOptions['_show_message_mbox_info']) && $displayOptions['_show_message_mbox_info'] === true) {
                $arg2['checked'] = 'checked';
                $img2class = 'enabled';
            } else {
                $img2class = 'disabled';
            }

            $img1 = html::img(array('src' => $this->url('skins/larry/images/show_mbox_row.jpg'), 'class' => $img1class));
            $img2 = html::img(array('src' => $this->url('skins/larry/images/show_mbox_col.jpg'), 'class' => $img2class));
            $img3 = html::img(array('src' => $this->url('skins/larry/images/' . $target_image)));

            $check2 = html::tag('input', $arg2);
            $args['blocks']['label_display_options']['options'][0] = array('title' => '', 'content' => '<p class="avsearchpref"><span>' . $check1 . ' ' . $label1 . '</span> ' . $img1 . '</p>');
            $args['blocks']['label_display_options']['options'][1] = array('title' => '', 'content' => '<p class="avsearchpref"><span>' . $check2 . ' ' . $label2 . '</span> ' . $img2 . '</p>');
            $args['blocks']['label_display_options']['options'][2] = array('title' => '', 'content' => '<p class="avsearchpref"><span>' . $label3 . ' ' . $select . '</span> ' . $img3 . '</p>');
        }

        return($args);
    }

    private function perform_search($search_string, $folders, $page = 1)
    {
        // Search all folders and build a final set
        if ($folders[0] == 'all' || empty($folders)) {
            $folders_search = $this->rc->imap->list_folders_subscribed();
        } else {
            $folders_search = $folders;
        }
        $count = 0;
        $folder_count = array();
        foreach ($folders_search as $mbox) {
            $this->rc->storage->set_folder($mbox);
            $this->rc->storage->search($mbox, $search_string, RCUBE_CHARSET, $_SESSION['sort_col']);
            $result = array();
            $fcount = $this->rc->storage->count($mbox, 'ALL', !empty($_REQUEST['_refresh']));
            $count += $fcount;
            $folder_count[$mbox] = $fcount;
        }
        foreach ($folder_count as $k => $v) {
            if ($v == 0) {
                unset($folder_count[$k]);
            }
        }
        $fetch = $this->do_pagination($folder_count, $page);
        $mails = array();
        $currentMailbox = "";
        $displayOptions = $this->rc->config->get('advanced_search_display_options', array());
        $showMboxColumn = isset($displayOptions['_show_message_mbox_info']) && $displayOptions['_show_message_mbox_info'] ? true : false;
        $uid_mboxes = array();
        foreach ($fetch as $mailbox => $data) {
            if ($currentMailbox != $mailbox) {
                $currentMailbox = $mailbox;
                if (isset($displayOptions['_show_message_label_header']) && $displayOptions['_show_message_label_header'] === true) {
                    $this->rc->output->command('advanced_search_add_mbox', $mailbox, $folder_count[$mailbox], $showMboxColumn);
                }
            }
            $uid_mboxes = array_merge($uid_mboxes, $this->getMails($mailbox, $data, $search_string, $showMboxColumn));
        }

        return array('result' => array(), 'count' => $count, 'uid_mboxes' => $uid_mboxes);
    }

    private function getMails($mailbox, $data, $search_string, $showMboxColumn)
    {
        $pageSize = $this->rc->storage->get_pagesize();
        $msgNum = $data['from'];
        $startPage =  ceil($msgNum/$pageSize);
        $msgMod = $msgNum % $pageSize;
        $multiPage = "false";
        $firstArrayElement = $msgMod == 0 ? ($pageSize-1) : ($msgMod-1);
        $quantity = $data['to'] - $data['from'];
        if ($data['from'] + $quantity > $pageSize) {
            $multiPage = "true";
        }
        $this->rc->storage->set_folder($mailbox);
        $this->rc->storage->search($mailbox, $search_string, RCUBE_CHARSET, $_SESSION['sort_col']);
        $messages = $this->rc->storage->list_messages('', $startPage);
        if ($multiPage) {
            $messages = array_merge($messages, $this->rc->storage->list_messages('', $startPage+1));
        }
        //FIRST: 0 QUANTITY: 2
        $sliceTo = $quantity + 1;
        $mslice = array_slice($messages, $firstArrayElement, $sliceTo, true);
        $messages = $mslice;
        $avbox = array();
        $showAvmbox = false;
        foreach ($messages as $set_flag) {
            $set_flag->flags['skip_mbox_check'] = true;
            if ($showMboxColumn === true) {
                $set_flag->avmbox = $mailbox;
                $avbox[] = 'avmbox';
                $showAvmbox = true;
            }
        }
        $uid_mboxes = $this->rcmail_js_message_list($messages, false, null, $showAvmbox, $avbox, $showMboxColumn);

        return $uid_mboxes;
    }

    public function save_search()
    {
        $search_name = rcube_utils::get_input_value('search_name', rcube_utils::INPUT_GPC);
        if ($search_name) {
            $search = array();
            $search['search'] = rcube_utils::get_input_value('search', rcube_utils::INPUT_GPC);
            $search['search_name'] = $search_name;
            $search['folder'] = rcube_utils::get_input_value('folder', rcube_utils::INPUT_GPC);
            $search['sub_folders'] = rcube_utils::get_input_value('sub_folders', rcube_utils::INPUT_GPC);
            $prefs = (array)$this->rc->user->get_prefs();
            if (!isset($prefs['advanced_search'])) {
                $prefs['advanced_search'] = array();
            }
            $prefs['advanced_search'][$search_name] = $search;
            $this->rc->user->save_prefs(array('advanced_search' => $prefs['advanced_search']));
            $this->rc->output->show_message('"<i>' . $search_name . '</i>" ' . $this->i18n_strings['has_been_saved'], 'confirmation');
        }
    }

    public function delete_search()
    {
        $search_name = rcube_utils::get_input_value('search_name', rcube_utils::INPUT_GPC);
        if ($search_name) {
            $prefs = (array)$this->rc->user->get_prefs();
            unset($prefs['advanced_search'][$search_name]);
            $this->rc->user->save_prefs(array('advanced_search' => $prefs['advanced_search']));
            $this->rc->output->show_message('"<i>' . $search_name . '</i>" ' . $this->i18n_strings['has_been_deleted'], 'notice');
        }
    }

    public function get_saved_search()
    {
        $search_name = rcube_utils::get_input_value('search_name', rcube_utils::INPUT_GPC);
        $prefs = (array)$this->rc->user->get_prefs();
        if (!isset($prefs['advanced_search'])) {
            $prefs['advanced_search'] = array();
        }

        $search = isset($prefs['advanced_search'][$search_name]) ? $prefs['advanced_search'][$search_name] : false;
        $this->rc->output->command('plugin.load_saved_search', $search);
        $this->rc->output->send();
    }

    private function get_saved_search_names()
    {
        $prefs = (array)$this->rc->user->get_prefs();
        if (!isset($prefs['advanced_search'])) {
            $prefs['advanced_search'] = array();
        }
        $names = array();
        foreach($prefs['advanced_search'] as $name => $search) {
            $names[] = $name;
        }

        return $names;
    }
}
