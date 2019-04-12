<?php

// Plugin: contextmenu_folder
// Roundcube Context Menu Folder Manager
// Adds context menus with mailbox operations
class contextmenu_folder extends rcube_plugin {

    // supported filters
    private static $filter_type_list = array(
        'unread',
        'special',
        'selected',
        'transient',
        'predefined',
    );

    // client environment variables, push/pull
    private static $allowed_names = array(
        'show_mode',
        'collect_special',
        'collect_selected',
        'collect_transient',
        'collect_predefined',
        'memento_current_mailbox',
        'memento_current_message',
        'memento_folder_locate_text',
        'memento_contact_parent_item',
        'memento_contact_header_item',
        'memento_contact_format_item',
    );

    // client environment variables, push only
    private static $environ_names = array(
        'activate_plugin',
        'enable_logging',
        'enable_refresh',
        'enable_folder_list_context_menu',
        'enable_folder_list_control_menu',
        'enable_message_list_context_menu',
        'filter_active',
        'filter_favorite',
        'contact_folder_format_list',
        'feature_choice',
        'icon_mapa',
        'allow_purge_regex',
        'hide_ctrl_menu_list',
        'hide_mbox_menu_list',
        'hide_mesg_menu_list',
        'transient_expire_mins',
    );

    // plugin ajax registered actions
    private static $action_names = array(
        'header_list',
        'folder_list',
        'folder_create',
        'folder_delete',
        'folder_rename',
        'folder_purge',
        'folder_scan_tree',
    );

    const ROOT = ''; // root of mailbox hierarchy

    public $task = 'mail|settings'; // supported tasks regex filter
    public $allowed_prefs = array(); // see: rcube_plugin->$allowed_prefs

    private $config_default = array(); // default plugin configuration
    private $rc; // controller singleton

    // early instace init
    function onload() {
        $this->provide_allowed_prefs();
    }

    // final instace init
    function init() {
        $this->rc = rcmail::get_instance();
        $this->require_plugin('jqueryui');
        $this->require_plugin('contextmenu');
        $task = $this->rc->task; $action = $this->rc->action;
        if ($task == 'mail' && $this->is_root_request()) {
            // periodic client pull
            if($action == 'refresh') {
                $this->init_config();
                $this->log('client pull');
                $this->init_refresh_hook();
                return;
            }
            // plugin ajax action post
            if (strpos($action, $this->key('')) === 0) {
                $this->init_config();
                $this->log('action post: ' . $action);
                $this->init_mail_action();
                return;
            }
            // application window load
            if ( $action == '' && $this->is_html_request()) {
                $this->init_config();
                $this->log('window load');
                $this->init_mail_hook();
                $this->init_mail_html_page();
                return;
            }
            return;
        }
        if ($task == 'settings') {
            $this->init_config();
            $this->log('settings');
            $this->init_settings_hook();
            $this->init_settings_html_page();
            return;
        }
    }

    ////////////////////////////

    // plugin name space
    function key($name) {
        return 'plugin.contextmenu_folder.' . $name; // keep in sync with *.js
    }

    // plugin server logger
    function log($line, $force = false) {
        if($this->config_get('enable_logging') || $force){
        	$head = $this->key('');
        	$file = $this->key('log');
        	$func = debug_backtrace()[1]['function'];
            $text = $head . $func . ' : ' . $line;
            rcube::write_log($file, $text);
        }
    }

    // localized quoted text
    function quoted($name) {
        return rcube::Q($this->gettext($name));
    }

    // load plugin preferences
    function config_get($name) {
        $key = $this->key($name);
        return $this->rc->config->get($key);
    }

    // save plugin preferences
    function config_put($name, $value) {
        $key = $this->key($name);
        $this->rc->user->save_prefs(array($key => $value));
    }

    // read client post result
    function input_value($name) {
        $name = str_replace('.', '_', $name); // PHP convention
        return rcube_utils::get_input_value($name, rcube_utils::INPUT_POST);
    }

    // imap backend storage mailbox separator
    function hierarchy_delimiter() {
        return $this->rc->storage->get_hierarchy_delimiter();
    }
    
    // verify if file is present at path
    function has_file($path) {
    	 return $path && is_file($path) && is_readable($path);
    }

    // load plugin default configuration file(s)
    function provide_default() {
        $config = null;
        $path = $this->home . '/' . 'config.inc.php.dist';
        if ($this->has_file($path)) {
        	ob_start();
        	include($path);
        	ob_end_clean();
        }
        $path = $this->home . '/' . 'config.inc.php';
        if ($this->has_file($path)) {
        	ob_start();
        	include($path);
        	ob_end_clean();
        }
        if (is_array($config)) {
            $this->config_default = $config;
        }
    }

    ////////////////////////////

    // setup config with default override
    function init_config() {
        $this->add_hook('config_get', array($this, 'hook_config_get'));
        $this->provide_default();
        $this->provide_collect_special();
        $this->provide_collect_predefined();
    }

    // mail
    function init_mail_hook() {
        $this->add_hook('refresh', array($this, 'hook_refresh'));
        $this->add_hook('preferences_update', array($this, 'hook_preferences_update'));
    }

    // mail
    function init_mail_action() {
        foreach(self::$action_names as $name) {
            $this->register_action($this->key($name), array($this, 'action_' . $name));
        }
    }

    // mail
    function init_mail_html_page() {
        $this->add_texts('localization', true);
        $this->include_script('contextmenu_folder.js');
        $this->include_stylesheet( 'assets/fontello/css/folder.css');
        $this->include_stylesheet( 'skins' . '/style.css');
        $this->include_stylesheet($this->local_skin_path() . '/style.css');
        $this->provide_client_env_var();
    }

    // refresh
    function init_refresh_hook() {
        $this->add_hook('new_messages', array($this, 'hook_new_messages'));
    }

    // client environment variables
    function set_env($name, $value = null) {
        $key = $this->key($name);
        if(! isset($value)) {
            $value = $this->config_get($name);
        }
        $this->rc->output->set_env($key, $value);
    }

    // allow to save these prefs on demand
    function provide_allowed_prefs() {
        $allowed_prefs = array();
        foreach(self::$allowed_names as $name) {
            $allowed_prefs[] = $this->key($name);
        }
        $this->allowed_prefs = $allowed_prefs;
    }

    // client environment variables
    function provide_client_env_var() {
        $name_list = array_merge(
            self::$allowed_names, self::$environ_names
        );
        foreach($name_list as $name) {
           $this->set_env($name);
        }
    }

    // root vs frame window request
    function is_root_request() {
        return empty($_REQUEST['_framed']);
    }

    // html vs ajax request
    function is_html_request() {
        return $this->rc->output->type == 'html';
    }

    // root vs frame window request
    function is_frame_request() {
        return isset($_REQUEST['_framed']);
    }

    // build list of imap special mailbox names
    function special_folder_list() {
        $storage = $this->rc->get_storage();
        $special_folders = $storage->get_special_folders();
        $folder_list = array_merge(
            array('inbox' => 'INBOX'), $special_folders
        );
        return $folder_list;
    }

    // mailbox descriptor in collection
    function mbox_meta($mbox = 'default', $action = 'default', $created_msec = 0) {
        $meta_info = array(
            'mbox' => $mbox,
            'action' => $action,
            'created_msec' => $created_msec,
        );
        return $meta_info;
    }

    // rebuild 'special' mailbox collection from the configuration
    function provide_collect_special() {
        $collect_special = array();
        $mbox_list = $this->special_folder_list();
        foreach($mbox_list as $mbox) {
            $collect_special[$mbox] = $this->mbox_meta($mbox);
        }
        $this->config_put('collect_special', $collect_special);
    }

    // rebuild 'predefined' mailbox collection from the configuration
    function provide_collect_predefined() {
        $collect_predefined = array();
        $mbox_list = $this->config_get('predefined_list');
        foreach($mbox_list as $mbox) {
            $collect_predefined[$mbox] = $this->mbox_meta($mbox);
        }
        $this->config_put('collect_predefined', $collect_predefined);
    }

    // navigate mailbox hierarchy
    function parent_mbox($mbox) {
        $delimiter = $this->hierarchy_delimiter();
        if (strpos($mbox, $delimiter) === false) {
              return self::ROOT;
        } else {
              return substr($mbox, 0, strrpos($mbox, $delimiter));
        }
    }

    // detect imap special mailbox name
    function is_folder_special($name) {
        return $this->rc->storage->is_special_folder($name);
    }

    // find nubmer of unread messages in the mailbox
    function folder_count_unread($mbox) {
        $mode = 'UNSEEN'; $force = true; $status = true;
        return $this->rc->storage->count($mbox, $mode, $force, $status);
    }

    // build mailbox filter flags from configuration
    function provide_filter($name) {
        $filter = array(); // $filter[type] = true|false
        $filter_keys = $this->config_get($name);
        foreach(self::$filter_type_list as $type) {
              $filter[$type] = in_array($type, $filter_keys);
        }
        return $filter;
    }

    // TODO periodic folder tree refresh
    function hook_refresh($args) {
        if($this->config_get('enable_refresh')) {
            $this->log('TODO ');
            // $this->action_folder_list();
        }
        return $args;
    }

    // inject plugin default configuration
    function hook_config_get($args){
        $name = $args['name'];
        $result = $args['result'];
        $default = $this->config_default[$name];
        if(! isset($result) && isset($default)) {
            $args['result'] = $default;
        }
        return $args;
    }

    // inspect prefs values, see rcube_user.php
    function hook_preferences_update($args){
        // $this->log(print_r($args, true));
        return $args;
    }

    // notification for auto show 'unread' mailboxes
    function hook_new_messages($args){
        $output = $this->rc->output;
        $output->command($this->key('folder_notify'), array('folder' => $args['mailbox']));
        // $output->send(); // executed elsewhere
        return $args;
    }

    // report back to client the change
    function folder_update($action, $source, $target) {
        $output = $this->rc->output;
        $status = array(
            'action' => $action,
            'source' => $source,
            'target' => $target,
        );
        $this->log(print_r($status, true));
        $output->command($this->key('folder_update'), $status);
        $output->send();
    }

    // create complete folder tree, bottom up
    function folder_ensure_tree($target) {
        $storage = $this->rc->storage;
        if($storage->folder_exists($target)) {
            return true;
        }
        $parent = $this->parent_mbox($target);
        if($parent == self::ROOT || $storage->folder_exists($parent)) {
            return $storage->create_folder($target, true);
        }
        return $this->folder_ensure_tree($parent) && $storage->create_folder($target, true); // recurse
    }

    // create imap mailbox
    public function action_folder_create() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $source = '';
        $target = $this->input_value('target');
        $result = $this->folder_ensure_tree($target);
        if ($result) {
            $this->folder_update('create', $source, $target);
        } else {
            $this->rc->display_server_error('error folder_create');
        }
    }

    // recursive delete tree bottom up
    // https://github.com/roundcube/roundcubemail/issues/5466
    function folder_delete($target) {
        $storage = $this->rc->storage;
        $delimiter = $this->hierarchy_delimiter();
        $pattern = $target . $delimiter . '%'; // own leaf only
        $folder_list = $storage->list_folders(self::ROOT, $pattern, 'mail', null, false);
        foreach($folder_list as $folder) {
            if($this->folder_delete($folder)) {
                continue;
            } else {
                return false;
            }
        }
        return $storage->delete_folder($target);
    }

    // delete imap mailbox
    public function action_folder_delete() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $source = '';
        $target = $this->input_value('target');
        $result = $this->folder_delete($target);
        // $result = $storage->delete_folder($target);
        if ($result) {
            $parent = $this->parent_mbox($target);
            if( $parent == self::ROOT ){
                $parent = 'INBOX';
            }
            $this->folder_update('delete', $source, $target);
        } else {
            $this->rc->display_server_error('error folder_delete');
        }
    }

    // rename imap mailbox
    public function action_folder_rename() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $source = $this->input_value('source');
        $target = $this->input_value('target');
        $result = $storage->rename_folder($source, $target);
        if ($result) {
            $this->folder_update('rename', $source, $target);
        } else {
            $this->rc->display_server_error('error folder_rename');
        }
    }

    // empty imap folder of all messages
    public function action_folder_purge() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $source = $this->input_value('source');
        $target = $this->input_value('target');
        $result = $storage->clear_folder($target);
        if ($result) {
            $output->show_message('folderpurged', 'confirmation');
        } else {
            $this->rc->display_server_error('error folder_purge');
        }

        $output->command($this->key('folder_purge'), array());
        $output->send();
    }

    // produce flat, sorted, unfiltered, mailbox list
    public function action_folder_list() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $folder_list = $storage->list_folders();
        $output->command($this->key('folder_list'), array('folder_list' => $folder_list));
        $output->send();
    }

    // mark all un-seen messages in the mailbox as read
    function folder_mark_read($target) {
        $this->log($target);

        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $search = $storage->search_once($target, 'ALL UNSEEN', true);
        if ($search->is_empty()){
            return;
        }
        $message_list = $search->get();
        $storage->set_flag($message_list, 'SEEN', $target);
        $output->command('toggle_read_status', 'read', $message_list);
        rcmail_send_unread_count($target, true);
    }

    // recursively navigate mailbox and its descendants and mark all as read
    public function action_folder_scan_tree() {
        $output = $this->rc->output;
        $storage = $this->rc->storage;

        $target = $this->input_value('target');
        $scan_mode = $this->input_value('scan_mode');

        switch($scan_mode) {
        case 'read_this':
            $this->folder_mark_read($target);
            break;
        case 'read_tree':
            $this->folder_mark_read($target);
            $delimiter = $this->hierarchy_delimiter();
            $pattern = $target . $delimiter . '*';
            $folder_list = $storage->list_folders(self::ROOT, $pattern, 'mail', null, false);
            foreach($folder_list as $folder) {
                $this->folder_mark_read($folder);
            }
            break;
        default:
            $this->log('invalid $scan_mode: ' . $scan_mode, true);
            break;
        }

        // confirm to client when done
        $output->command($this->key('folder_scan_tree'), array(
            'target' => $target,
            'scan_mode' => $scan_mode,
        ));

        $output->send();
    }

    // guess business name from email domain
    function company_name($domain) {
        $generic_list = $this->config_get('domain_generic_list');
        $country_list = $this->config_get('domain_country_list');
        $domain = strtolower($domain);
        $company = explode(".", $domain);
        if (in_array(end($company), $generic_list)) {
            array_pop($company);
            $company = end($company);
        } else if (in_array(end($company), $country_list)) {
            array_pop($company);
            if(in_array(end($company), $generic_list)) {
                array_pop($company);
            }
            $company = end($company);
        } else {
            $company = implode(" ", $company);
        }
        $company = ucwords($company);
        return $company;
    }

    // provide structured message address headers
    public function action_header_list() {
        $uid = $this->input_value('uid');
        $mbox = $this->input_value('mbox');
        $message = new rcube_message($uid, $mbox);
        $header_list = array();
        foreach(array('from', 'to', 'cc') as $type) {
            $header = $message->get_header($type);
            $subject = $message->get_header('subject');
            $address_list = rcube_mime::decode_address_list($header);
            foreach($address_list as $address) {
                $name = trim($address['name']);
                if (strpos($name, ",") === false) {
                    $full_part = explode(" ", $name);
                } else { // reverse names order
                    $temp = explode(",", $name);
                    $full_part = array(trim(end($temp)), trim(reset($temp)));
                }
                $full = implode(" ", $full_part); $full = ucwords($full); $full = trim($full);
                $mailto = strtolower($address['mailto']); $mailto_part = explode("@", $mailto);
                $prefix = reset($mailto_part); $domain = end($mailto_part);
                $company = $this->company_name($domain);
                $header_list[] = array(
                    'type' => $type, 'name' => $name, 'string' => $address['string'],
                    'full_name' => $full, 'full_head' => reset($full_part), 'full_tail' => end($full_part),
                    'mail_addr' => $mailto, 'prefix' => $prefix, 'domain' => $domain, 'company' => $company,
                    'subject' => $subject,
                );
            }
        }
        // $this->log(print_r($header_list, true));
        $output = $this->rc->output;
        $output->command($this->key('header_list'), array('header_list' => $header_list));
        $output->send('plugin');
    }

    ////////////////////////////

    // settings
    function init_settings_hook() {
        $this->add_hook('preferences_list', array($this, 'hook_preferences_list'));
        $this->add_hook('preferences_save', array($this, 'hook_preferences_save'));
    }

    // settings
    function init_settings_html_page() {
        $output = $this->rc->output;
        if ($output->type == 'html') {
            $this->add_texts('localization', true);
        }
    }

    // plugin settings section
    function is_plugin_section($args) {
        return $args['section'] == 'mailbox';
    }

    // settings exposed to user
    function settings_checkbox_list() {
        return $this->config_get('settings_checkbox_list');
    }

    // settings exposed to user
    function settings_select_list() {
        return $this->config_get('settings_select_list');
    }

    // settings exposed to user
    function settings_area_list() {
        return $this->config_get('settings_area_list');
    }

    // settings exposed to user
    function settings_text_list() {
        return $this->config_get('settings_text_list');
    }

    // settings checkbox
    function build_checkbox(& $entry, $name) {
        $key = $this->key($name);
        $checkbox = new html_checkbox(array(
             'id' => $key, 'name' => $key, 'value' => 1,
        ));
        $entry['options'][$name] = array(
            'title' => html::label($key, $this->quoted($name)),
            'content' => $checkbox->show($this->config_get($name)),
        );
    }

    // settings multi select
    function build_select(& $entry, $name, $option_list = null) {
        if(! $option_list) { // list name convention
            $option_list = $this->config_get($name . '.' . 'list');
        }
        $key = $this->key($name);
        $select = new html_select(array(
             'id' => $key, 'name' => $key . '[]', // use array
             'multiple' => true, 'size' => 5,
        ));
        $select->add($option_list, $option_list); // value => content
        $entry['options'][$name] = array(
            'title' => html::label($key, $this->quoted($name)),
            'content' => $select->show($this->config_get($name)),
        );
    }

    // settings multi line text area
    function build_textarea(& $entry, $name) {
        $key = $this->key($name);
        $textarea = new html_textarea(array(
             'id' => $key, 'name' => $key, 'rows' => 5, 'cols' => 65,
        ));
        $entry['options'][$name] = array(
            'title' => html::label($key, $this->quoted($name)),
            'content' => $textarea->show(implode(PHP_EOL, $this->config_get($name))),
        );
    }

    // settings single line text input
    function build_text(& $entry, $name) {
        $key = $this->key($name);
        $input = new html_inputfield(array(
             'id' => $key, 'name' => $key, 'value' => 1,
        ));
        $entry['options'][$name] = array(
            'title' => html::label($key, $this->quoted($name)),
            'content' => $input->show($this->config_get($name)),
        );
    }

    // build settings ui
    function hook_preferences_list($args) {
        if ($this->is_plugin_section($args)) {
            $blocks = & $args['blocks'];
            $section = $this->key('section');
            $blocks[$section] = array(); $entry = & $blocks[$section];
            $entry['name'] = $this->quoted('plugin_folder_menu');
            foreach($this->settings_checkbox_list() as $name) {
                $this->build_checkbox($entry, $name);
            }
            foreach($this->settings_select_list() as $name) {
                $this->build_select($entry, $name);
            }
            foreach($this->settings_area_list() as $name) {
                $this->build_textarea($entry, $name);
            }
            foreach($this->settings_text_list() as $name) {
                $this->build_text($entry, $name);
            }
        }
        return $args;
    }

    // settings checkbox
    function persist_checkbox(& $prefs, $name) {
        $key = $this->key($name); $value = $this->input_value($key);
        $prefs[$key] =  $value ? true : false;
    }

    // settings multi select
    function persist_select(& $prefs, $name) {
        $key = $this->key($name); $value = $this->input_value($key);
        $prefs[$key] = $value;
    }

    // settings multi line text area
    function persist_textarea(& $prefs, $name) {
        $key = $this->key($name); $value = $this->input_value($key);
        $value = explode(PHP_EOL, $value); // array from text
        $value = array_map('trim', $value); // no spaces
        $value = array_filter($value); // no empty lines
        // sort($value); // alpha sorted
        $prefs[$key] = $value;
    }

    // settings single line text input
    function persist_text(& $prefs, $name) {
        $key = $this->key($name); $value = $this->input_value($key);
        $prefs[$key] = trim($value);
    }

    // persist user settings
    function hook_preferences_save($args) {
        if ($this->is_plugin_section($args)) {
            $prefs = & $args['prefs'];
            // $this->log('post: ' . print_r($_POST, true));
            foreach($this->settings_checkbox_list() as $name) {
                $this->persist_checkbox($prefs, $name);
            }
            foreach($this->settings_select_list() as $name) {
                $this->persist_select($prefs, $name);
            }
            foreach($this->settings_area_list() as $name) {
                $this->persist_textarea($prefs, $name);
            }
            foreach($this->settings_text_list() as $name) {
                $this->persist_text($prefs, $name);
            }
        }
        return $args;
    }

}

?>
