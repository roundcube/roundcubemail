<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcmail.php                                            |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2013, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2013, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Application class providing core functions and holding              |
 |   instances of all 'global' objects like db- and imap-connections     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Application class of Roundcube Webmail
 * implemented as singleton
 *
 * @package Core
 */
class rcmail extends rcube
{
    /**
     * Main tasks.
     *
     * @var array
     */
    static public $main_tasks = array('mail','settings','addressbook','login','logout','utils','dummy');

    /**
     * Current task.
     *
     * @var string
     */
    public $task;

    /**
     * Current action.
     *
     * @var string
     */
    public $action    = '';
    public $comm_path = './';
    public $filename  = '';

    private $address_books = array();
    private $action_map    = array();


    const ERROR_STORAGE          = -2;
    const ERROR_INVALID_REQUEST  = 1;
    const ERROR_INVALID_HOST     = 2;
    const ERROR_COOKIES_DISABLED = 3;


    /**
     * This implements the 'singleton' design pattern
     *
     * @param string Environment name to run (e.g. live, dev, test)
     *
     * @return rcmail The one and only instance
     */
    static function get_instance($env = '')
    {
        if (!self::$instance || !is_a(self::$instance, 'rcmail')) {
            self::$instance = new rcmail($env);
            // init AFTER object was linked with self::$instance
            self::$instance->startup();
        }

        return self::$instance;
    }

    /**
     * Initial startup function
     * to register session, create database and imap connections
     */
    protected function startup()
    {
        $this->init(self::INIT_WITH_DB | self::INIT_WITH_PLUGINS);

        // set filename if not index.php
        if (($basename = basename($_SERVER['SCRIPT_FILENAME'])) && $basename != 'index.php') {
            $this->filename = $basename;
        }

        // start session
        $this->session_init();

        // create user object
        $this->set_user(new rcube_user($_SESSION['user_id']));

        // set task and action properties
        $this->set_task(rcube_utils::get_input_value('_task', rcube_utils::INPUT_GPC));
        $this->action = asciiwords(rcube_utils::get_input_value('_action', rcube_utils::INPUT_GPC));

        // reset some session parameters when changing task
        if ($this->task != 'utils') {
            // we reset list page when switching to another task
            // but only to the main task interface - empty action (#1489076)
            // this will prevent from unintentional page reset on cross-task requests
            if ($this->session && $_SESSION['task'] != $this->task && empty($this->action)) {
                $this->session->remove('page');
            }

            // set current task to session
            $_SESSION['task'] = $this->task;
        }

        // init output class
        if (!empty($_REQUEST['_remote']))
            $GLOBALS['OUTPUT'] = $this->json_init();
        else
            $GLOBALS['OUTPUT'] = $this->load_gui(!empty($_REQUEST['_framed']));

        // load plugins
        $this->plugins->init($this, $this->task);
        $this->plugins->load_plugins((array)$this->config->get('plugins', array()),
            array('filesystem_attachments', 'jqueryui'));
    }

    /**
     * Setter for application task
     *
     * @param string Task to set
     */
    public function set_task($task)
    {
        $task = asciiwords($task, true);

        if ($this->user && $this->user->ID)
            $task = !$task ? 'mail' : $task;
        else
            $task = 'login';

        $this->task      = $task;
        $this->comm_path = $this->url(array('task' => $this->task));

        if (!empty($_REQUEST['_framed'])) {
            $this->comm_path .= '&_framed=1';
        }

        if ($this->output) {
            $this->output->set_env('task', $this->task);
            $this->output->set_env('comm_path', $this->comm_path);
        }
    }

    /**
     * Setter for system user object
     *
     * @param rcube_user Current user instance
     */
    public function set_user($user)
    {
        if (is_object($user)) {
            $this->user = $user;

            // overwrite config with user preferences
            $this->config->set_user_prefs((array)$this->user->get_prefs());
        }

        $lang = $this->language_prop($this->config->get('language', $_SESSION['language']));
        $_SESSION['language'] = $this->user->language = $lang;

        // set localization
        setlocale(LC_ALL, $lang . '.utf8', $lang . '.UTF-8', 'en_US.utf8', 'en_US.UTF-8');

        // workaround for http://bugs.php.net/bug.php?id=18556
        if (version_compare(PHP_VERSION, '5.5.0', '<') && in_array($lang, array('tr_TR', 'ku', 'az_AZ'))) {
            setlocale(LC_CTYPE, 'en_US.utf8', 'en_US.UTF-8');
        }
    }

    /**
     * Return instance of the internal address book class
     *
     * @param string  Address book identifier (-1 for default addressbook)
     * @param boolean True if the address book needs to be writeable
     *
     * @return rcube_contacts Address book object
     */
    public function get_address_book($id, $writeable = false)
    {
        $contacts    = null;
        $ldap_config = (array)$this->config->get('ldap_public');

        // 'sql' is the alias for '0' used by autocomplete
        if ($id == 'sql')
            $id = '0';
        else if ($id == -1) {
            $id = $this->config->get('default_addressbook');
            $default = true;
        }

        // use existing instance
        if (isset($this->address_books[$id]) && ($this->address_books[$id] instanceof rcube_addressbook)) {
            $contacts = $this->address_books[$id];
        }
        else if ($id && $ldap_config[$id]) {
            $domain   = $this->config->mail_domain($_SESSION['storage_host']);
            $contacts = new rcube_ldap($ldap_config[$id], $this->config->get('ldap_debug'), $domain);
        }
        else if ($id === '0') {
            $contacts = new rcube_contacts($this->db, $this->get_user_id());
        }
        else {
            $plugin = $this->plugins->exec_hook('addressbook_get', array('id' => $id, 'writeable' => $writeable));

            // plugin returned instance of a rcube_addressbook
            if ($plugin['instance'] instanceof rcube_addressbook) {
                $contacts = $plugin['instance'];
            }
        }

        // when user requested default writeable addressbook
        // we need to check if default is writeable, if not we
        // will return first writeable book (if any exist)
        if ($contacts && $default && $contacts->readonly && $writeable) {
            $contacts = null;
        }

        // Get first addressbook from the list if configured default doesn't exist
        // This can happen when user deleted the addressbook (e.g. Kolab folder)
        if (!$contacts && (!$id || $default)) {
            $source = reset($this->get_address_sources($writeable, !$default));
            if (!empty($source)) {
                $contacts = $this->get_address_book($source['id']);
                if ($contacts) {
                    $id = $source['id'];
                }
            }
        }

        if (!$contacts) {
            // there's no default, just return
            if ($default) {
                return null;
            }

            self::raise_error(array(
                    'code'    => 700,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Addressbook source ($id) not found!"
                ),
                true, true);
        }

        // add to the 'books' array for shutdown function
        $this->address_books[$id] = $contacts;

        if ($writeable && $contacts->readonly) {
            return null;
        }

        // set configured sort order
        if ($sort_col = $this->config->get('addressbook_sort_col')) {
            $contacts->set_sort_order($sort_col);
        }

        return $contacts;
    }

    /**
     * Return identifier of the address book object
     *
     * @param rcube_addressbook Addressbook source object
     *
     * @return string Source identifier
     */
    public function get_address_book_id($object)
    {
        foreach ($this->address_books as $index => $book) {
            if ($book === $object) {
                return $index;
            }
        }
    }

    /**
     * Return address books list
     *
     * @param boolean True if the address book needs to be writeable
     * @param boolean True if the address book needs to be not hidden
     *
     * @return array  Address books array
     */
    public function get_address_sources($writeable = false, $skip_hidden = false)
    {
        $abook_type   = (string) $this->config->get('address_book_type');
        $ldap_config  = (array) $this->config->get('ldap_public');
        $autocomplete = (array) $this->config->get('autocomplete_addressbooks');
        $list         = array();

        // We are using the DB address book or a plugin address book
        if (!empty($abook_type) && strtolower($abook_type) != 'ldap') {
            if (!isset($this->address_books['0'])) {
                $this->address_books['0'] = new rcube_contacts($this->db, $this->get_user_id());
            }

            $list['0'] = array(
                'id'       => '0',
                'name'     => $this->gettext('personaladrbook'),
                'groups'   => $this->address_books['0']->groups,
                'readonly' => $this->address_books['0']->readonly,
                'undelete' => $this->address_books['0']->undelete && $this->config->get('undo_timeout'),
                'autocomplete' => in_array('sql', $autocomplete),
            );
        }

        if (!empty($ldap_config)) {
            foreach ($ldap_config as $id => $prop) {
                // handle misconfiguration
                if (empty($prop) || !is_array($prop)) {
                    continue;
                }

                $list[$id] = array(
                    'id'       => $id,
                    'name'     => html::quote($prop['name']),
                    'groups'   => !empty($prop['groups']) || !empty($prop['group_filters']),
                    'readonly' => !$prop['writable'],
                    'hidden'   => $prop['hidden'],
                    'autocomplete' => in_array($id, $autocomplete)
                );
            }
        }

        $plugin = $this->plugins->exec_hook('addressbooks_list', array('sources' => $list));
        $list   = $plugin['sources'];

        foreach ($list as $idx => $item) {
            // register source for shutdown function
            if (!is_object($this->address_books[$item['id']])) {
                $this->address_books[$item['id']] = $item;
            }
            // remove from list if not writeable as requested
            if ($writeable && $item['readonly']) {
                unset($list[$idx]);
            }
            // remove from list if hidden as requested
            else if ($skip_hidden && $item['hidden']) {
                unset($list[$idx]);
            }
        }

        return $list;
    }

    /**
     * Getter for compose responses.
     * These are stored in local config and user preferences.
     *
     * @param boolean True to sort the list alphabetically
     * @param boolean True if only this user's responses shall be listed
     *
     * @return array List of the current user's stored responses
     */
    public function get_compose_responses($sorted = false, $user_only = false)
    {
        $responses = array();

        if (!$user_only) {
            foreach ($this->config->get('compose_responses_static', array()) as $response) {
                if (empty($response['key'])) {
                    $response['key']    = substr(md5($response['name']), 0, 16);
                }

                $response['static'] = true;
                $response['class']  = 'readonly';

                $k = $sorted ? '0000-' . strtolower($response['name']) : $response['key'];
                $responses[$k] = $response;
            }
        }

        foreach ($this->config->get('compose_responses', array()) as $response) {
            if (empty($response['key'])) {
                $response['key'] = substr(md5($response['name']), 0, 16);
            }

            $k = $sorted ? strtolower($response['name']) : $response['key'];
            $responses[$k] = $response;
        }

        // sort list by name
        if ($sorted) {
            ksort($responses, SORT_LOCALE_STRING);
        }

        return array_values($responses);
    }

    /**
     * Init output object for GUI and add common scripts.
     * This will instantiate a rcmail_output_html object and set
     * environment vars according to the current session and configuration
     *
     * @param boolean True if this request is loaded in a (i)frame
     *
     * @return rcube_output Reference to HTML output object
     */
    public function load_gui($framed = false)
    {
        // init output page
        if (!($this->output instanceof rcmail_output_html)) {
            $this->output = new rcmail_output_html($this->task, $framed);
        }

        // set refresh interval
        $this->output->set_env('refresh_interval', $this->config->get('refresh_interval', 0));
        $this->output->set_env('session_lifetime', $this->config->get('session_lifetime', 0) * 60);

        if ($framed) {
            $this->comm_path .= '&_framed=1';
            $this->output->set_env('framed', true);
        }

        $this->output->set_env('task', $this->task);
        $this->output->set_env('action', $this->action);
        $this->output->set_env('comm_path', $this->comm_path);
        $this->output->set_charset(RCUBE_CHARSET);

        if ($this->user && $this->user->ID) {
            $this->output->set_env('user_id', $this->user->get_hash());
        }

        // set compose mode for all tasks (message compose step can be triggered from everywhere)
        $this->output->set_env('compose_extwin', $this->config->get('compose_extwin',false));

        // add some basic labels to client
        $this->output->add_label('loading', 'servererror', 'connerror', 'requesttimedout', 'refreshing');

        return $this->output;
    }

    /**
     * Create an output object for JSON responses
     *
     * @return rcube_output Reference to JSON output object
     */
    public function json_init()
    {
        if (!($this->output instanceof rcmail_output_json)) {
            $this->output = new rcmail_output_json($this->task);
        }

        return $this->output;
    }

    /**
     * Create session object and start the session.
     */
    public function session_init()
    {
        parent::session_init();

        // set initial session vars
        if (!$_SESSION['user_id']) {
            $_SESSION['temp'] = true;
        }

        // restore skin selection after logout
        if ($_SESSION['temp'] && !empty($_SESSION['skin'])) {
            $this->config->set('skin', $_SESSION['skin']);
        }
    }

    /**
     * Perfom login to the mail server and to the webmail service.
     * This will also create a new user entry if auto_create_user is configured.
     *
     * @param string Mail storage (IMAP) user name
     * @param string Mail storage (IMAP) password
     * @param string Mail storage (IMAP) host
     * @param bool   Enables cookie check
     *
     * @return boolean True on success, False on failure
     */
    function login($username, $pass, $host = null, $cookiecheck = false)
    {
        $this->login_error = null;

        if (empty($username)) {
            return false;
        }

        if ($cookiecheck && empty($_COOKIE)) {
            $this->login_error = self::ERROR_COOKIES_DISABLED;
            return false;
        }

        $default_host    = $this->config->get('default_host');
        $default_port    = $this->config->get('default_port');
        $username_domain = $this->config->get('username_domain');
        $login_lc        = $this->config->get('login_lc', 2);

        // host is validated in rcmail::autoselect_host(), so here
        // we'll only handle unset host (if possible)
        if (!$host && !empty($default_host)) {
            if (is_array($default_host)) {
                list($key, $val) = each($default_host);
                $host = is_numeric($key) ? $val : $key;
            }
            else {
                $host = $default_host;
            }

            $host = rcube_utils::parse_host($host);
        }

        if (!$host) {
            $this->login_error = self::ERROR_INVALID_HOST;
            return false;
        }

        // parse $host URL
        $a_host = parse_url($host);
        if ($a_host['host']) {
            $host = $a_host['host'];
            $ssl  = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? $a_host['scheme'] : null;

            if (!empty($a_host['port']))
                $port = $a_host['port'];
            else if ($ssl && $ssl != 'tls' && (!$default_port || $default_port == 143))
                $port = 993;
        }

        if (!$port) {
            $port = $default_port;
        }

        // Check if we need to add/force domain to username
        if (!empty($username_domain)) {
            $domain = is_array($username_domain) ? $username_domain[$host] : $username_domain;

            if ($domain = rcube_utils::parse_host((string)$domain, $host)) {
                $pos = strpos($username, '@');

                // force configured domains
                if ($pos !== false && $this->config->get('username_domain_forced')) {
                    $username = substr($username, 0, $pos) . '@' . $domain;
                }
                // just add domain if not specified
                else if ($pos === false) {
                    $username .= '@' . $domain;
                }
            }
        }

        // Convert username to lowercase. If storage backend
        // is case-insensitive we need to store always the same username (#1487113)
        if ($login_lc) {
            if ($login_lc == 2 || $login_lc === true) {
                $username = mb_strtolower($username);
            }
            else if (strpos($username, '@')) {
                // lowercase domain name
                list($local, $domain) = explode('@', $username);
                $username = $local . '@' . mb_strtolower($domain);
            }
        }

        // try to resolve email address from virtuser table
        if (strpos($username, '@') && ($virtuser = rcube_user::email2user($username))) {
            $username = $virtuser;
        }

        // Here we need IDNA ASCII
        // Only rcube_contacts class is using domain names in Unicode
        $host     = rcube_utils::idn_to_ascii($host);
        $username = rcube_utils::idn_to_ascii($username);

        // user already registered -> overwrite username
        if ($user = rcube_user::query($username, $host)) {
            $username = $user->data['username'];
        }

        $storage = $this->get_storage();

        // try to log in
        if (!$storage->connect($host, $username, $pass, $port, $ssl)) {
            return false;
        }

        // user already registered -> update user's record
        if (is_object($user)) {
            // update last login timestamp
            $user->touch();
        }
        // create new system user
        else if ($this->config->get('auto_create_user')) {
            if ($created = rcube_user::create($username, $host)) {
                $user = $created;
            }
            else {
                self::raise_error(array(
                        'code'    => 620,
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => "Failed to create a user record. Maybe aborted by a plugin?"
                    ),
                    true, false);
            }
        }
        else {
            self::raise_error(array(
                    'code'    => 621,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Access denied for new user $username. 'auto_create_user' is disabled"
                ),
                true, false);
        }

        // login succeeded
        if (is_object($user) && $user->ID) {
            // Configure environment
            $this->set_user($user);
            $this->set_storage_prop();

            // set session vars
            $_SESSION['user_id']      = $user->ID;
            $_SESSION['username']     = $user->data['username'];
            $_SESSION['storage_host'] = $host;
            $_SESSION['storage_port'] = $port;
            $_SESSION['storage_ssl']  = $ssl;
            $_SESSION['password']     = $this->encrypt($pass);
            $_SESSION['login_time']   = time();

            if (isset($_REQUEST['_timezone']) && $_REQUEST['_timezone'] != '_default_') {
                $_SESSION['timezone'] = rcube_utils::get_input_value('_timezone', rcube_utils::INPUT_GPC);
            }

            // fix some old settings according to namespace prefix
            $this->fix_namespace_settings($user);

            // create default folders on login
            if ($this->config->get('create_default_folders')) {
                $storage->create_default_folders();
            }

            // clear all mailboxes related cache(s)
            $storage->clear_cache('mailboxes', true);

            return true;
        }

        return false;
    }

    /**
     * Returns error code of last login operation
     *
     * @return int Error code
     */
    public function login_error()
    {
        if ($this->login_error) {
            return $this->login_error;
        }

        if ($this->storage && $this->storage->get_error_code() < -1) {
            return self::ERROR_STORAGE;
        }
    }

    /**
     * Auto-select IMAP host based on the posted login information
     *
     * @return string Selected IMAP host
     */
    public function autoselect_host()
    {
        $default_host = $this->config->get('default_host');
        $host         = null;

        if (is_array($default_host)) {
            $post_host = rcube_utils::get_input_value('_host', rcube_utils::INPUT_POST);
            $post_user = rcube_utils::get_input_value('_user', rcube_utils::INPUT_POST);

            list(, $domain) = explode('@', $post_user);

            // direct match in default_host array
            if ($default_host[$post_host] || in_array($post_host, array_values($default_host))) {
                $host = $post_host;
            }
            // try to select host by mail domain
            else if (!empty($domain)) {
                foreach ($default_host as $storage_host => $mail_domains) {
                    if (is_array($mail_domains) && in_array_nocase($domain, $mail_domains)) {
                        $host = $storage_host;
                        break;
                    }
                    else if (stripos($storage_host, $domain) !== false || stripos(strval($mail_domains), $domain) !== false) {
                        $host = is_numeric($storage_host) ? $mail_domains : $storage_host;
                        break;
                    }
                }
            }

            // take the first entry if $host is still not set
            if (empty($host)) {
                list($key, $val) = each($default_host);
                $host = is_numeric($key) ? $val : $key;
            }
        }
        else if (empty($default_host)) {
            $host = rcube_utils::get_input_value('_host', rcube_utils::INPUT_POST);
        }
        else {
            $host = rcube_utils::parse_host($default_host);
        }

        return $host;
    }

    /**
     * Destroy session data and remove cookie
     */
    public function kill_session()
    {
        $this->plugins->exec_hook('session_destroy');

        $this->session->kill();
        $_SESSION = array('language' => $this->user->language, 'temp' => true, 'skin' => $this->config->get('skin'));
        $this->user->reset();
    }

    /**
     * Do server side actions on logout
     */
    public function logout_actions()
    {
        $config  = $this->config->all();
        $storage = $this->get_storage();

        if ($config['logout_purge'] && !empty($config['trash_mbox'])) {
            $storage->clear_folder($config['trash_mbox']);
        }

        if ($config['logout_expunge']) {
            $storage->expunge_folder('INBOX');
        }

        // Try to save unsaved user preferences
        if (!empty($_SESSION['preferences'])) {
            $this->user->save_prefs(unserialize($_SESSION['preferences']));
        }
    }

    /**
     * Generate a unique token to be used in a form request
     *
     * @return string The request token
     */
    public function get_request_token()
    {
        $sess_id = $_COOKIE[ini_get('session.name')];

        if (!$sess_id) {
            $sess_id = session_id();
        }

        $plugin = $this->plugins->exec_hook('request_token', array(
            'value' => md5('RT' . $this->get_user_id() . $this->config->get('des_key') . $sess_id)));

        return $plugin['value'];
    }

    /**
     * Check if the current request contains a valid token
     *
     * @param int Request method
     *
     * @return boolean True if request token is valid false if not
     */
    public function check_request($mode = rcube_utils::INPUT_POST)
    {
        $token   = rcube_utils::get_input_value('_token', $mode);
        $sess_id = $_COOKIE[ini_get('session.name')];

        return !empty($sess_id) && $token == $this->get_request_token();
    }

    /**
     * Build a valid URL to this instance of Roundcube
     *
     * @param mixed Either a string with the action or url parameters as key-value pairs
     *
     * @return string Valid application URL
     */
    public function url($p)
    {
        if (!is_array($p)) {
            if (strpos($p, 'http') === 0) {
                return $p;
            }

            $p = array('_action' => @func_get_arg(0));
        }

        $task = $p['_task'] ? $p['_task'] : ($p['task'] ? $p['task'] : $this->task);
        $p['_task'] = $task;
        unset($p['task']);

        $url  = './' . $this->filename;
        $delm = '?';

        foreach (array_reverse($p) as $key => $val) {
            if ($val !== '' && $val !== null) {
                $par  = $key[0] == '_' ? $key : '_'.$key;
                $url .= $delm.urlencode($par).'='.urlencode($val);
                $delm = '&';
            }
        }

        return $url;
    }

    /**
     * Function to be executed in script shutdown
     */
    public function shutdown()
    {
        parent::shutdown();

        foreach ($this->address_books as $book) {
            if (is_object($book) && is_a($book, 'rcube_addressbook'))
                $book->close();
        }

        // write performance stats to logs/console
        if ($this->config->get('devel_mode')) {
            // make sure logged numbers use unified format
            setlocale(LC_NUMERIC, 'en_US.utf8', 'en_US.UTF-8', 'en_US', 'C');

            if (function_exists('memory_get_usage'))
                $mem = $this->show_bytes(memory_get_usage());
            if (function_exists('memory_get_peak_usage'))
                $mem .= '/'.$this->show_bytes(memory_get_peak_usage());

            $log = $this->task . ($this->action ? '/'.$this->action : '') . ($mem ? " [$mem]" : '');

            if (defined('RCMAIL_START'))
                self::print_timer(RCMAIL_START, $log);
            else
                self::console($log);
        }
    }

    /**
     * Registers action aliases for current task
     *
     * @param array $map Alias-to-filename hash array
     */
    public function register_action_map($map)
    {
        if (is_array($map)) {
            foreach ($map as $idx => $val) {
                $this->action_map[$idx] = $val;
            }
        }
    }

    /**
     * Returns current action filename
     *
     * @param array $map Alias-to-filename hash array
     */
    public function get_action_file()
    {
        if (!empty($this->action_map[$this->action])) {
            return $this->action_map[$this->action];
        }

        return strtr($this->action, '-', '_') . '.inc';
    }

    /**
     * Fixes some user preferences according to namespace handling change.
     * Old Roundcube versions were using folder names with removed namespace prefix.
     * Now we need to add the prefix on servers where personal namespace has prefix.
     *
     * @param rcube_user $user User object
     */
    private function fix_namespace_settings($user)
    {
        $prefix     = $this->storage->get_namespace('prefix');
        $prefix_len = strlen($prefix);

        if (!$prefix_len)
            return;

        $prefs = $this->config->all();
        if (!empty($prefs['namespace_fixed']))
            return;

        // Build namespace prefix regexp
        $ns     = $this->storage->get_namespace();
        $regexp = array();

        foreach ($ns as $entry) {
            if (!empty($entry)) {
                foreach ($entry as $item) {
                    if (strlen($item[0])) {
                        $regexp[] = preg_quote($item[0], '/');
                    }
                }
            }
        }
        $regexp = '/^('. implode('|', $regexp).')/';

        // Fix preferences
        $opts = array('drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox', 'archive_mbox');
        foreach ($opts as $opt) {
            if ($value = $prefs[$opt]) {
                if ($value != 'INBOX' && !preg_match($regexp, $value)) {
                    $prefs[$opt] = $prefix.$value;
                }
            }
        }

        if (!empty($prefs['default_folders'])) {
            foreach ($prefs['default_folders'] as $idx => $name) {
                if ($name != 'INBOX' && !preg_match($regexp, $name)) {
                    $prefs['default_folders'][$idx] = $prefix.$name;
                }
            }
        }

        if (!empty($prefs['search_mods'])) {
            $folders = array();
            foreach ($prefs['search_mods'] as $idx => $value) {
                if ($idx != 'INBOX' && $idx != '*' && !preg_match($regexp, $idx)) {
                    $idx = $prefix.$idx;
                }
                $folders[$idx] = $value;
            }

            $prefs['search_mods'] = $folders;
        }

        if (!empty($prefs['message_threading'])) {
            $folders = array();
            foreach ($prefs['message_threading'] as $idx => $value) {
                if ($idx != 'INBOX' && !preg_match($regexp, $idx)) {
                    $idx = $prefix.$idx;
                }
                $folders[$prefix.$idx] = $value;
            }

            $prefs['message_threading'] = $folders;
        }

        if (!empty($prefs['collapsed_folders'])) {
            $folders     = explode('&&', $prefs['collapsed_folders']);
            $count       = count($folders);
            $folders_str = '';

            if ($count) {
                $folders[0]        = substr($folders[0], 1);
                $folders[$count-1] = substr($folders[$count-1], 0, -1);
            }

            foreach ($folders as $value) {
                if ($value != 'INBOX' && !preg_match($regexp, $value)) {
                    $value = $prefix.$value;
                }
                $folders_str .= '&'.$value.'&';
            }

            $prefs['collapsed_folders'] = $folders_str;
        }

        $prefs['namespace_fixed'] = true;

        // save updated preferences and reset imap settings (default folders)
        $user->save_prefs($prefs);
        $this->set_storage_prop();
    }

    /**
     * Overwrite action variable
     *
     * @param string New action value
     */
    public function overwrite_action($action)
    {
        $this->action = $action;
        $this->output->set_env('action', $action);
    }

    /**
     * Set environment variables for specified config options
     */
    public function set_env_config($options)
    {
        foreach ((array) $options as $option) {
            if ($this->config->get($option)) {
                $this->output->set_env($option, true);
            }
        }
    }

    /**
     * Returns RFC2822 formatted current date in user's timezone
     *
     * @return string Date
     */
    public function user_date()
    {
        // get user's timezone
        try {
            $tz   = new DateTimeZone($this->config->get('timezone'));
            $date = new DateTime('now', $tz);
        }
        catch (Exception $e) {
            $date = new DateTime();
        }

        return $date->format('r');
    }

    /**
     * Write login data (name, ID, IP address) to the 'userlogins' log file.
     */
    public function log_login($user = null, $failed_login = false, $error_code = 0)
    {
        if (!$this->config->get('log_logins')) {
            return;
        }

        // failed login
        if ($failed_login) {
            $message = sprintf('Failed login for %s from %s in session %s (error: %d)',
                $user, rcube_utils::remote_ip(), session_id(), $error_code);
        }
        // successful login
        else {
            $user_name = $this->get_user_name();
            $user_id   = $this->get_user_id();

            if (!$user_id) {
                return;
            }

            $message = sprintf('Successful login for %s (ID: %d) from %s in session %s',
                $user_name, $user_id, rcube_utils::remote_ip(), session_id());
        }

        // log login
        self::write_log('userlogins', $message);
    }

    /**
     * Create a HTML table based on the given data
     *
     * @param  array  Named table attributes
     * @param  mixed  Table row data. Either a two-dimensional array or a valid SQL result set
     * @param  array  List of cols to show
     * @param  string Name of the identifier col
     *
     * @return string HTML table code
     */
    public function table_output($attrib, $table_data, $a_show_cols, $id_col)
    {
        $table = new html_table($attrib);

        // add table header
        if (!$attrib['noheader']) {
            foreach ($a_show_cols as $col) {
                $table->add_header($col, $this->Q($this->gettext($col)));
            }
        }

        if (!is_array($table_data)) {
            $db = $this->get_dbh();
            while ($table_data && ($sql_arr = $db->fetch_assoc($table_data))) {
                $table->add_row(array('id' => 'rcmrow' . rcube_utils::html_identifier($sql_arr[$id_col])));

                // format each col
                foreach ($a_show_cols as $col) {
                    $table->add($col, $this->Q($sql_arr[$col]));
                }
            }
        }
        else {
            foreach ($table_data as $row_data) {
                $class = !empty($row_data['class']) ? $row_data['class'] : '';
                $rowid = 'rcmrow' . rcube_utils::html_identifier($row_data[$id_col]);

                $table->add_row(array('id' => $rowid, 'class' => $class));

                // format each col
                foreach ($a_show_cols as $col) {
                    $table->add($col, $this->Q(is_array($row_data[$col]) ? $row_data[$col][0] : $row_data[$col]));
                }
            }
        }

        return $table->show($attrib);
    }

    /**
     * Convert the given date to a human readable form
     * This uses the date formatting properties from config
     *
     * @param mixed  Date representation (string, timestamp or DateTime object)
     * @param string Date format to use
     * @param bool   Enables date convertion according to user timezone
     *
     * @return string Formatted date string
     */
    public function format_date($date, $format = null, $convert = true)
    {
        if (is_object($date) && is_a($date, 'DateTime')) {
            $timestamp = $date->format('U');
        }
        else {
            if (!empty($date)) {
                $timestamp = rcube_utils::strtotime($date);
            }

            if (empty($timestamp)) {
                return '';
            }

            try {
                $date = new DateTime("@".$timestamp);
            }
            catch (Exception $e) {
                return '';
            }
        }

        if ($convert) {
            try {
                // convert to the right timezone
                $stz = date_default_timezone_get();
                $tz = new DateTimeZone($this->config->get('timezone'));
                $date->setTimezone($tz);
                date_default_timezone_set($tz->getName());

                $timestamp = $date->format('U');
            }
            catch (Exception $e) {
            }
        }

        // define date format depending on current time
        if (!$format) {
            $now         = time();
            $now_date    = getdate($now);
            $today_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year']);
            $week_limit  = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);
            $pretty_date = $this->config->get('prettydate');

            if ($pretty_date && $timestamp > $today_limit && $timestamp < $now) {
                $format = $this->config->get('date_today', $this->config->get('time_format', 'H:i'));
                $today  = true;
            }
            else if ($pretty_date && $timestamp > $week_limit && $timestamp < $now) {
                $format = $this->config->get('date_short', 'D H:i');
            }
            else {
                $format = $this->config->get('date_long', 'Y-m-d H:i');
            }
        }

        // strftime() format
        if (preg_match('/%[a-z]+/i', $format)) {
            $format = strftime($format, $timestamp);
            if ($stz) {
                date_default_timezone_set($stz);
            }
            return $today ? ($this->gettext('today') . ' ' . $format) : $format;
        }

        // parse format string manually in order to provide localized weekday and month names
        // an alternative would be to convert the date() format string to fit with strftime()
        $out = '';
        for ($i=0; $i<strlen($format); $i++) {
            if ($format[$i] == "\\") {  // skip escape chars
                continue;
            }

            // write char "as-is"
            if ($format[$i] == ' ' || $format[$i-1] == "\\") {
                $out .= $format[$i];
            }
            // weekday (short)
            else if ($format[$i] == 'D') {
                $out .= $this->gettext(strtolower(date('D', $timestamp)));
            }
            // weekday long
            else if ($format[$i] == 'l') {
                $out .= $this->gettext(strtolower(date('l', $timestamp)));
            }
            // month name (short)
            else if ($format[$i] == 'M') {
                $out .= $this->gettext(strtolower(date('M', $timestamp)));
            }
            // month name (long)
            else if ($format[$i] == 'F') {
                $out .= $this->gettext('long'.strtolower(date('M', $timestamp)));
            }
            else if ($format[$i] == 'x') {
                $out .= strftime('%x %X', $timestamp);
            }
            else {
                $out .= date($format[$i], $timestamp);
            }
        }

        if ($today) {
            $label = $this->gettext('today');
            // replcae $ character with "Today" label (#1486120)
            if (strpos($out, '$') !== false) {
                $out = preg_replace('/\$/', $label, $out, 1);
            }
            else {
                $out = $label . ' ' . $out;
            }
        }

        if ($stz) {
            date_default_timezone_set($stz);
        }

        return $out;
    }

    /**
     * Return folders list in HTML
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the gui object
     */
    public function folder_list($attrib)
    {
        static $a_mailboxes;

        $attrib += array('maxlength' => 100, 'realnames' => false, 'unreadwrap' => ' (%s)');

        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();

        // add some labels to client
        $rcmail->output->add_label('purgefolderconfirm', 'deletemessagesconfirm');

        $type = $attrib['type'] ? $attrib['type'] : 'ul';
        unset($attrib['type']);

        if ($type == 'ul' && !$attrib['id']) {
            $attrib['id'] = 'rcmboxlist';
        }

        if (empty($attrib['folder_name'])) {
            $attrib['folder_name'] = '*';
        }

        // get current folder
        $mbox_name = $storage->get_folder();

        // build the folders tree
        if (empty($a_mailboxes)) {
            // get mailbox list
            $a_folders = $storage->list_folders_subscribed(
                '', $attrib['folder_name'], $attrib['folder_filter']);
            $delimiter = $storage->get_hierarchy_delimiter();
            $a_mailboxes = array();

            foreach ($a_folders as $folder) {
                $rcmail->build_folder_tree($a_mailboxes, $folder, $delimiter);
            }
        }

        // allow plugins to alter the folder tree or to localize folder names
        $hook = $rcmail->plugins->exec_hook('render_mailboxlist', array(
            'list'      => $a_mailboxes,
            'delimiter' => $delimiter,
            'type'      => $type,
            'attribs'   => $attrib,
        ));

        $a_mailboxes = $hook['list'];
        $attrib      = $hook['attribs'];

        if ($type == 'select') {
            $attrib['is_escaped'] = true;
            $select = new html_select($attrib);

            // add no-selection option
            if ($attrib['noselection']) {
                $select->add(html::quote($rcmail->gettext($attrib['noselection'])), '');
            }

            $rcmail->render_folder_tree_select($a_mailboxes, $mbox_name, $attrib['maxlength'], $select, $attrib['realnames']);
            $out = $select->show($attrib['default']);
        }
        else {
            $js_mailboxlist = array();
            $tree = $rcmail->render_folder_tree_html($a_mailboxes, $mbox_name, $js_mailboxlist, $attrib);

            if ($type != 'js') {
                $out = html::tag('ul', $attrib, $tree, html::$common_attrib);

                $rcmail->output->include_script('treelist.js');
                $rcmail->output->add_gui_object('mailboxlist', $attrib['id']);
                $rcmail->output->set_env('unreadwrap', $attrib['unreadwrap']);
                $rcmail->output->set_env('collapsed_folders', (string)$rcmail->config->get('collapsed_folders'));
            }

            $rcmail->output->set_env('mailboxes', $js_mailboxlist);

            // we can't use object keys in javascript because they are unordered
            // we need sorted folders list for folder-selector widget
            $rcmail->output->set_env('mailboxes_list', array_keys($js_mailboxlist));
        }

        return $out;
    }

    /**
     * Return folders list as html_select object
     *
     * @param array $p  Named parameters
     *
     * @return html_select HTML drop-down object
     */
    public function folder_selector($p = array())
    {
        $realnames = $this->config->get('show_real_foldernames');
        $p += array('maxlength' => 100, 'realnames' => $realnames, 'is_escaped' => true);
        $a_mailboxes = array();
        $storage = $this->get_storage();

        if (empty($p['folder_name'])) {
            $p['folder_name'] = '*';
        }

        if ($p['unsubscribed']) {
            $list = $storage->list_folders('', $p['folder_name'], $p['folder_filter'], $p['folder_rights']);
        }
        else {
            $list = $storage->list_folders_subscribed('', $p['folder_name'], $p['folder_filter'], $p['folder_rights']);
        }

        $delimiter = $storage->get_hierarchy_delimiter();

        if (!empty($p['exceptions'])) {
            $list = array_diff($list, (array) $p['exceptions']);
        }

        if (!empty($p['additional'])) {
            foreach ($p['additional'] as $add_folder) {
                $add_items = explode($delimiter, $add_folder);
                $folder    = '';
                while (count($add_items)) {
                    $folder .= array_shift($add_items);

                    // @TODO: sorting
                    if (!in_array($folder, $list)) {
                        $list[] = $folder;
                    }

                    $folder .= $delimiter;
                }
            }
        }

        foreach ($list as $folder) {
            $this->build_folder_tree($a_mailboxes, $folder, $delimiter);
        }

        $select = new html_select($p);

        if ($p['noselection']) {
            $select->add(html::quote($p['noselection']), '');
        }

        $this->render_folder_tree_select($a_mailboxes, $mbox, $p['maxlength'], $select, $p['realnames'], 0, $p);

        return $select;
    }

    /**
     * Create a hierarchical array of the mailbox list
     */
    public function build_folder_tree(&$arrFolders, $folder, $delm = '/', $path = '')
    {
        // Handle namespace prefix
        $prefix = '';
        if (!$path) {
            $n_folder = $folder;
            $folder = $this->storage->mod_folder($folder);

            if ($n_folder != $folder) {
                $prefix = substr($n_folder, 0, -strlen($folder));
            }
        }

        $pos = strpos($folder, $delm);

        if ($pos !== false) {
            $subFolders    = substr($folder, $pos+1);
            $currentFolder = substr($folder, 0, $pos);

            // sometimes folder has a delimiter as the last character
            if (!strlen($subFolders)) {
                $virtual = false;
            }
            else if (!isset($arrFolders[$currentFolder])) {
                $virtual = true;
            }
            else {
                $virtual = $arrFolders[$currentFolder]['virtual'];
            }
        }
        else {
            $subFolders    = false;
            $currentFolder = $folder;
            $virtual       = false;
        }

        $path .= $prefix . $currentFolder;

        if (!isset($arrFolders[$currentFolder])) {
            $arrFolders[$currentFolder] = array(
                'id' => $path,
                'name' => rcube_charset::convert($currentFolder, 'UTF7-IMAP'),
                'virtual' => $virtual,
                'folders' => array());
        }
        else {
            $arrFolders[$currentFolder]['virtual'] = $virtual;
        }

        if (strlen($subFolders)) {
            $this->build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
        }
    }

    /**
     * Return html for a structured list &lt;ul&gt; for the mailbox tree
     */
    public function render_folder_tree_html(&$arrFolders, &$mbox_name, &$jslist, $attrib, $nestLevel = 0)
    {
        $maxlength = intval($attrib['maxlength']);
        $realnames = (bool)$attrib['realnames'];
        $msgcounts = $this->storage->get_cache('messagecount');
        $collapsed = $this->config->get('collapsed_folders');
        $realnames = $this->config->get('show_real_foldernames');

        $out = '';
        foreach ($arrFolders as $folder) {
            $title        = null;
            $folder_class = $this->folder_classname($folder['id']);
            $is_collapsed = strpos($collapsed, '&'.rawurlencode($folder['id']).'&') !== false;
            $unread       = $msgcounts ? intval($msgcounts[$folder['id']]['UNSEEN']) : 0;

            if ($folder_class && !$realnames) {
                $foldername = $this->gettext($folder_class);
            }
            else {
                $foldername = $folder['name'];

                // shorten the folder name to a given length
                if ($maxlength && $maxlength > 1) {
                    $fname = abbreviate_string($foldername, $maxlength);
                    if ($fname != $foldername) {
                        $title = $foldername;
                    }
                    $foldername = $fname;
                }
            }

            // make folder name safe for ids and class names
            $folder_id = rcube_utils::html_identifier($folder['id'], true);
            $classes   = array('mailbox');

            // set special class for Sent, Drafts, Trash and Junk
            if ($folder_class) {
                $classes[] = $folder_class;
            }

            if ($folder['id'] == $mbox_name) {
                $classes[] = 'selected';
            }

            if ($folder['virtual']) {
                $classes[] = 'virtual';
            }
            else if ($unread) {
                $classes[] = 'unread';
            }

            $js_name = $this->JQ($folder['id']);
            $html_name = $this->Q($foldername) . ($unread ? html::span('unreadcount', sprintf($attrib['unreadwrap'], $unread)) : '');
            $link_attrib = $folder['virtual'] ? array() : array(
                'href' => $this->url(array('_mbox' => $folder['id'])),
                'onclick' => sprintf("return %s.command('list','%s',this)", rcmail_output::JS_OBJECT_NAME, $js_name),
                'rel' => $folder['id'],
                'title' => $title,
            );

            $out .= html::tag('li', array(
                'id' => "rcmli".$folder_id,
                'class' => join(' ', $classes),
                'noclose' => true),
                html::a($link_attrib, $html_name));

            if (!empty($folder['folders'])) {
                $out .= html::div('treetoggle ' . ($is_collapsed ? 'collapsed' : 'expanded'), '&nbsp;');
            }

            $jslist[$folder['id']] = array(
                'id'      => $folder['id'],
                'name'    => $foldername,
                'virtual' => $folder['virtual'],
            );

            if (!empty($folder_class)) {
                $jslist[$folder['id']]['class'] = $folder_class;
            }

            if (!empty($folder['folders'])) {
                $out .= html::tag('ul', array('style' => ($is_collapsed ? "display:none;" : null)),
                    $this->render_folder_tree_html($folder['folders'], $mbox_name, $jslist, $attrib, $nestLevel+1));
            }

            $out .= "</li>\n";
        }

        return $out;
    }

    /**
     * Return html for a flat list <select> for the mailbox tree
     */
    public function render_folder_tree_select(&$arrFolders, &$mbox_name, $maxlength, &$select, $realnames = false, $nestLevel = 0, $opts = array())
    {
        $out = '';

        foreach ($arrFolders as $folder) {
            // skip exceptions (and its subfolders)
            if (!empty($opts['exceptions']) && in_array($folder['id'], $opts['exceptions'])) {
                continue;
            }

            // skip folders in which it isn't possible to create subfolders
            if (!empty($opts['skip_noinferiors'])) {
                $attrs = $this->storage->folder_attributes($folder['id']);
                if ($attrs && in_array('\\Noinferiors', $attrs)) {
                    continue;
                }
            }

            if (!$realnames && ($folder_class = $this->folder_classname($folder['id']))) {
                $foldername = $this->gettext($folder_class);
            }
            else {
                $foldername = $folder['name'];

                // shorten the folder name to a given length
                if ($maxlength && $maxlength > 1) {
                    $foldername = abbreviate_string($foldername, $maxlength);
                }
            }

            $select->add(str_repeat('&nbsp;', $nestLevel*4) . html::quote($foldername), $folder['id']);

            if (!empty($folder['folders'])) {
                $out .= $this->render_folder_tree_select($folder['folders'], $mbox_name, $maxlength,
                    $select, $realnames, $nestLevel+1, $opts);
            }
        }

        return $out;
    }

    /**
     * Return internal name for the given folder if it matches the configured special folders
     */
    public function folder_classname($folder_id)
    {
        if ($folder_id == 'INBOX') {
            return 'inbox';
        }

        // for these mailboxes we have localized labels and css classes
        foreach (array('sent', 'drafts', 'trash', 'junk') as $smbx)
        {
            if ($folder_id === $this->config->get($smbx.'_mbox')) {
                return $smbx;
            }
        }
    }

    /**
     * Try to localize the given IMAP folder name.
     * UTF-7 decode it in case no localized text was found
     *
     * @param string $name      Folder name
     * @param bool   $with_path Enable path localization
     *
     * @return string Localized folder name in UTF-8 encoding
     */
    public function localize_foldername($name, $with_path = false)
    {
        $realnames = $this->config->get('show_real_foldernames');

        if (!$realnames && ($folder_class = $this->folder_classname($name))) {
            return $this->gettext($folder_class);
        }

        // try to localize path of the folder
        if ($with_path && !$realnames) {
            $storage   = $this->get_storage();
            $delimiter = $storage->get_hierarchy_delimiter();
            $path      = explode($delimiter, $name);
            $count     = count($path);

            if ($count > 1) {
                for ($i = 1; $i < $count; $i++) {
                    $folder = implode($delimiter, array_slice($path, 0, -$i));
                    if ($folder_class = $this->folder_classname($folder)) {
                        $name = implode($delimiter, array_slice($path, $count - $i));
                        return $this->gettext($folder_class) . $delimiter . rcube_charset::convert($name, 'UTF7-IMAP');
                    }
                }
            }
        }

        return rcube_charset::convert($name, 'UTF7-IMAP');
    }


    public function localize_folderpath($path)
    {
        $protect_folders = $this->config->get('protect_default_folders');
        $default_folders = (array) $this->config->get('default_folders');
        $delimiter       = $this->storage->get_hierarchy_delimiter();
        $path            = explode($delimiter, $path);
        $result          = array();

        foreach ($path as $idx => $dir) {
            $directory = implode($delimiter, array_slice($path, 0, $idx+1));
            if ($protect_folders && in_array($directory, $default_folders)) {
                unset($result);
                $result[] = $this->localize_foldername($directory);
            }
            else {
                $result[] = rcube_charset::convert($dir, 'UTF7-IMAP');
            }
        }

        return implode($delimiter, $result);
    }


    public static function quota_display($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (!$attrib['id']) {
            $attrib['id'] = 'rcmquotadisplay';
        }

        $_SESSION['quota_display'] = !empty($attrib['display']) ? $attrib['display'] : 'text';

        $rcmail->output->add_gui_object('quotadisplay', $attrib['id']);

        $quota = $rcmail->quota_content($attrib);

        $rcmail->output->add_script('rcmail.set_quota('.rcube_output::json_serialize($quota).');', 'docready');

        return html::span($attrib, '&nbsp;');
    }


    public function quota_content($attrib = null)
    {
        $quota = $this->storage->get_quota();
        $quota = $this->plugins->exec_hook('quota', $quota);

        $quota_result = (array) $quota;
        $quota_result['type'] = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';

        if ($quota['total'] > 0) {
            if (!isset($quota['percent'])) {
                $quota_result['percent'] = min(100, round(($quota['used']/max(1,$quota['total']))*100));
            }

            $title = sprintf('%s / %s (%.0f%%)',
                $this->show_bytes($quota['used'] * 1024), $this->show_bytes($quota['total'] * 1024),
                $quota_result['percent']);

            $quota_result['title'] = $title;

            if ($attrib['width']) {
                $quota_result['width'] = $attrib['width'];
            }
            if ($attrib['height']) {
                $quota_result['height']	= $attrib['height'];
            }
        }
        else {
            $unlimited               = $this->config->get('quota_zero_as_unlimited');
            $quota_result['title']   = $this->gettext($unlimited ? 'unlimited' : 'unknown');
            $quota_result['percent'] = 0;
        }

        return $quota_result;
    }

    /**
     * Outputs error message according to server error/response codes
     *
     * @param string $fallback       Fallback message label
     * @param array  $fallback_args  Fallback message label arguments
     * @param string $suffix         Message label suffix
     */
    public function display_server_error($fallback = null, $fallback_args = null, $suffix = '')
    {
        $err_code = $this->storage->get_error_code();
        $res_code = $this->storage->get_response_code();
        $args     = array();

        if ($res_code == rcube_storage::NOPERM) {
            $error = 'errornoperm';
        }
        else if ($res_code == rcube_storage::READONLY) {
            $error = 'errorreadonly';
        }
        else if ($res_code == rcube_storage::OVERQUOTA) {
            $error = 'errorroverquota';
        }
        else if ($err_code && ($err_str = $this->storage->get_error_str())) {
            // try to detect access rights problem and display appropriate message
            if (stripos($err_str, 'Permission denied') !== false) {
                $error = 'errornoperm';
            }
            // try to detect full mailbox problem and display appropriate message
            // there can be e.g. "Quota exceeded" or "quotum would exceed"
            else if (stripos($err_str, 'quot') !== false && stripos($err_str, 'exceed') !== false) {
                $error = 'erroroverquota';
            }
            else {
                $error = 'servererrormsg';
                $args  = array('msg' => $err_str);
            }
        }
        else if ($err_code < 0) {
            $error = 'storageerror';
        }
        else if ($fallback) {
            $error = $fallback;
            $args  = $fallback_args;
        }

        if ($error) {
            if ($suffix && $this->text_exists($error . $suffix)) {
                $error .= $suffix;
            }
            $this->output->show_message($error, 'error', $args);
        }
    }

    /**
     * Output HTML editor scripts
     *
     * @param string $mode  Editor mode
     */
    public function html_editor($mode = '')
    {
        $hook = $this->plugins->exec_hook('html_editor', array('mode' => $mode));

        if ($hook['abort']) {
            return;
        }

        $lang = strtolower($_SESSION['language']);

        // TinyMCE uses two-letter lang codes, with exception of Chinese
        if (strpos($lang, 'zh_') === 0) {
            $lang = str_replace('_', '-', $lang);
        }
        else {
            $lang = substr($lang, 0, 2);
        }

        if (!file_exists(INSTALL_PATH . 'program/js/tiny_mce/langs/'.$lang.'.js')) {
            $lang = 'en';
        }

        $script = array(
            'mode'       => $mode,
            'lang'       => $lang,
            'skin_path'  => $this->output->get_skin_path(),
            'spellcheck' => intval($this->config->get('enable_spellcheck')),
            'spelldict'  => intval($this->config->get('spellcheck_dictionary'))
        );

        $this->output->include_script('tiny_mce/tiny_mce.js');
        $this->output->include_script('editor.js');
        $this->output->set_env('html_editor_init', $script);
    }

    /**
     * Replaces TinyMCE's emoticon images with plain-text representation
     *
     * @param string $html  HTML content
     *
     * @return string HTML content
     */
    public static function replace_emoticons($html)
    {
        $emoticons = array(
            '8-)' => 'smiley-cool',
            ':-#' => 'smiley-foot-in-mouth',
            ':-*' => 'smiley-kiss',
            ':-X' => 'smiley-sealed',
            ':-P' => 'smiley-tongue-out',
            ':-@' => 'smiley-yell',
            ":'(" => 'smiley-cry',
            ':-(' => 'smiley-frown',
            ':-D' => 'smiley-laughing',
            ':-)' => 'smiley-smile',
            ':-S' => 'smiley-undecided',
            ':-$' => 'smiley-embarassed',
            'O:-)' => 'smiley-innocent',
            ':-|' => 'smiley-money-mouth',
            ':-O' => 'smiley-surprised',
            ';-)' => 'smiley-wink',
        );

        foreach ($emoticons as $idx => $file) {
            // <img title="Cry" src="http://.../program/js/tiny_mce/plugins/emotions/img/smiley-cry.gif" border="0" alt="Cry" />
            $search[]  = '/<img title="[a-z ]+" src="https?:\/\/[a-z0-9_.\/-]+\/tiny_mce\/plugins\/emotions\/img\/'.$file.'.gif"[^>]+\/>/i';
            $replace[] = $idx;
        }

        return preg_replace($search, $replace, $html);
    }

    /**
     * File upload progress handler.
     */
    public function upload_progress()
    {
        $prefix = ini_get('apc.rfc1867_prefix');
        $params = array(
            'action' => $this->action,
            'name' => rcube_utils::get_input_value('_progress', rcube_utils::INPUT_GET),
        );

        if (function_exists('apc_fetch')) {
            $status = apc_fetch($prefix . $params['name']);

            if (!empty($status)) {
                $status['percent'] = round($status['current']/$status['total']*100);
                $params = array_merge($status, $params);
            }
        }

        if (isset($params['percent']))
            $params['text'] = $this->gettext(array('name' => 'uploadprogress', 'vars' => array(
                'percent' => $params['percent'] . '%',
                'current' => $this->show_bytes($params['current']),
                'total'   => $this->show_bytes($params['total'])
        )));

        $this->output->command('upload_progress_update', $params);
        $this->output->send();
    }

    /**
     * Initializes file uploading interface.
     */
    public function upload_init()
    {
        // Enable upload progress bar
        $rfc1867 = filter_var(ini_get('apc.rfc1867'), FILTER_VALIDATE_BOOLEAN);
        if ($rfc1867 && ($seconds = $this->config->get('upload_progress'))) {
            if ($field_name = ini_get('apc.rfc1867_name')) {
                $this->output->set_env('upload_progress_name', $field_name);
                $this->output->set_env('upload_progress_time', (int) $seconds);
            }
        }

        // find max filesize value
        $max_filesize = parse_bytes(ini_get('upload_max_filesize'));
        $max_postsize = parse_bytes(ini_get('post_max_size'));

        if ($max_postsize && $max_postsize < $max_filesize) {
            $max_filesize = $max_postsize;
        }

        $this->output->set_env('max_filesize', $max_filesize);
        $max_filesize = $this->show_bytes($max_filesize);
        $this->output->set_env('filesizeerror', $this->gettext(array(
            'name' => 'filesizeerror', 'vars' => array('size' => $max_filesize))));

        return $max_filesize;
    }

    /**
     * Initializes client-side autocompletion.
     */
    public function autocomplete_init()
    {
        static $init;

        if ($init) {
            return;
        }

        $init = 1;

        if (($threads = (int)$this->config->get('autocomplete_threads')) > 0) {
            $book_types = (array) $this->config->get('autocomplete_addressbooks', 'sql');
            if (count($book_types) > 1) {
                $this->output->set_env('autocomplete_threads', $threads);
                $this->output->set_env('autocomplete_sources', $book_types);
            }
        }

        $this->output->set_env('autocomplete_max', (int)$this->config->get('autocomplete_max', 15));
        $this->output->set_env('autocomplete_min_length', $this->config->get('autocomplete_min_length'));
        $this->output->add_label('autocompletechars', 'autocompletemore');
    }

    /**
     * Returns supported font-family specifications
     *
     * @param string $font  Font name
     *
     * @param string|array Font-family specification array or string (if $font is used)
     */
    public static function font_defs($font = null)
    {
        $fonts = array(
            'Andale Mono'   => '"Andale Mono",Times,monospace',
            'Arial'         => 'Arial,Helvetica,sans-serif',
            'Arial Black'   => '"Arial Black","Avant Garde",sans-serif',
            'Book Antiqua'  => '"Book Antiqua",Palatino,serif',
            'Courier New'   => '"Courier New",Courier,monospace',
            'Georgia'       => 'Georgia,Palatino,serif',
            'Helvetica'     => 'Helvetica,Arial,sans-serif',
            'Impact'        => 'Impact,Chicago,sans-serif',
            'Tahoma'        => 'Tahoma,Arial,Helvetica,sans-serif',
            'Terminal'      => 'Terminal,Monaco,monospace',
            'Times New Roman' => '"Times New Roman",Times,serif',
            'Trebuchet MS'  => '"Trebuchet MS",Geneva,sans-serif',
            'Verdana'       => 'Verdana,Geneva,sans-serif',
        );

        if ($font) {
            return $fonts[$font];
        }

        return $fonts;
    }

    /**
     * Create a human readable string for a number of bytes
     *
     * @param int Number of bytes
     *
     * @return string Byte string
     */
    public function show_bytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $gb  = $bytes/1073741824;
            $str = sprintf($gb>=10 ? "%d " : "%.1f ", $gb) . $this->gettext('GB');
        }
        else if ($bytes >= 1048576) {
            $mb  = $bytes/1048576;
            $str = sprintf($mb>=10 ? "%d " : "%.1f ", $mb) . $this->gettext('MB');
        }
        else if ($bytes >= 1024) {
            $str = sprintf("%d ",  round($bytes/1024)) . $this->gettext('KB');
        }
        else {
            $str = sprintf('%d ', $bytes) . $this->gettext('B');
        }

        return $str;
    }

    /**
     * Returns real size (calculated) of the message part
     *
     * @param rcube_message_part  Message part
     *
     * @return string Part size (and unit)
     */
    public function message_part_size($part)
    {
        if (isset($part->d_parameters['size'])) {
            $size = $this->show_bytes((int)$part->d_parameters['size']);
        }
        else {
          $size = $part->size;
          if ($part->encoding == 'base64') {
            $size = $size / 1.33;
          }

          $size = '~' . $this->show_bytes($size);
        }

        return $size;
    }


    /************************************************************************
     *********          Deprecated methods (to be removed)          *********
     ***********************************************************************/

    public static function setcookie($name, $value, $exp = 0)
    {
        rcube_utils::setcookie($name, $value, $exp);
    }

    public function imap_connect()
    {
        return $this->storage_connect();
    }

    public function imap_init()
    {
        return $this->storage_init();
    }

    /**
     * Connect to the mail storage server with stored session data
     *
     * @return bool True on success, False on error
     */
    public function storage_connect()
    {
        $storage = $this->get_storage();

        if ($_SESSION['storage_host'] && !$storage->is_connected()) {
            $host = $_SESSION['storage_host'];
            $user = $_SESSION['username'];
            $port = $_SESSION['storage_port'];
            $ssl  = $_SESSION['storage_ssl'];
            $pass = $this->decrypt($_SESSION['password']);

            if (!$storage->connect($host, $user, $pass, $port, $ssl)) {
                if (is_object($this->output)) {
                    $this->output->show_message('storageerror', 'error');
                }
            }
            else {
                $this->set_storage_prop();
            }
        }

        return $storage->is_connected();
    }
}
