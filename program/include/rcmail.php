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
 * @package Webmail
 */
class rcmail extends rcube
{
    /**
     * Main tasks.
     *
     * @var array
     */
    public static $main_tasks = ['mail','settings','addressbook','login','logout','utils','oauth','dummy'];

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
    public $default_skin;
    public $login_error;
    public $oauth;

    /** @var ?string Temporary user email (set on user creation only) */
    public $user_email;

    /** @var ?string Temporary user password (set on user creation only) */
    public $password;

    private $address_books = [];
    private $action_map    = [];
    private $action_args   = [];

    const ERROR_STORAGE          = -2;
    const ERROR_INVALID_REQUEST  = 1;
    const ERROR_INVALID_HOST     = 2;
    const ERROR_COOKIES_DISABLED = 3;
    const ERROR_RATE_LIMIT       = 4;


    /**
     * This implements the 'singleton' design pattern
     *
     * @param int    $mode Ignored rcube::get_instance() argument
     * @param string $env  Environment name to run (e.g. live, dev, test)
     *
     * @return rcmail The one and only instance
     */
    static function get_instance($mode = 0, $env = '')
    {
        if (!self::$instance || !is_a(self::$instance, 'rcmail')) {
            // In cli-server mode env=test
            if ($env === null && php_sapi_name() == 'cli-server') {
                $env = 'test';
            }

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

        // load all configured plugins
        $plugins          = (array) $this->config->get('plugins', []);
        $required_plugins = ['filesystem_attachments', 'jqueryui'];
        $this->plugins->load_plugins($plugins, $required_plugins);

        // start session
        $this->session_init();

        // Remember default skin, before it's replaced by user prefs
        $this->default_skin = $this->config->get('skin');

        // create user object
        $this->set_user(new rcube_user($_SESSION['user_id'] ?? null));

        // set task and action properties
        $this->set_task(rcube_utils::get_input_string('_task', rcube_utils::INPUT_GPC));
        $this->action = asciiwords(rcube_utils::get_input_string('_action', rcube_utils::INPUT_GPC));

        // reset some session parameters when changing task
        if ($this->task != 'utils') {
            // we reset list page when switching to another task
            // but only to the main task interface - empty action (#1489076, #1490116)
            // this will prevent from unintentional page reset on cross-task requests
            if ($this->session && empty($this->action)
                && (empty($_SESSION['task']) || $_SESSION['task'] != $this->task)
            ) {
                $this->session->remove('page');

                // set current task to session
                $_SESSION['task'] = $this->task;
            }
        }

        // init output class
        if (php_sapi_name() == 'cli') {
            $this->output = new rcmail_output_cli();
        }
        else if (!empty($_REQUEST['_remote'])) {
            $this->json_init();
        }
        else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $this->load_gui(!empty($_REQUEST['_framed']));
        }

        // load oauth manager
        $this->oauth = rcmail_oauth::get_instance();

        // run init method on all the plugins
        $this->plugins->init($this, $this->task);
    }

    /**
     * Setter for application task
     *
     * @param string $task Task to set
     */
    public function set_task($task)
    {
        if (php_sapi_name() == 'cli') {
            $task = 'cli';
        }
        else if (!$this->user || !$this->user->ID) {
            $task = 'login';
        }
        else {
            $task = asciiwords($task, true) ?: 'mail';
        }

        // Re-initialize plugins if task is changing
        if (!empty($this->task) && $this->task != $task) {
            $this->plugins->init($this, $task);
        }

        $this->task      = $task;
        $this->comm_path = $this->url(['task' => $this->task]);

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
     * @param rcube_user $user Current user instance
     */
    public function set_user($user)
    {
        parent::set_user($user);

        $session_lang = $_SESSION['language'] ?? null;
        $lang = $this->language_prop($this->config->get('language', $session_lang));
        $_SESSION['language'] = $this->user->language = $lang;

        // set localization
        setlocale(LC_ALL, $lang . '.utf8', $lang . '.UTF-8', 'en_US.utf8', 'en_US.UTF-8');

        // Workaround for https://bugs.php.net/bug.php?id=18556
        // Also strtoupper/strtolower and other methods are locale-aware
        // for these locales it is problematic (#1490519)
        if (in_array($lang, ['tr_TR', 'ku', 'az_AZ'])) {
            setlocale(LC_CTYPE, 'en_US.utf8', 'en_US.UTF-8', 'C');
        }
    }

    /**
     * Handle the request. All request pre-checks are NOT done here.
     */
    public function action_handler()
    {
        // we're ready, user is authenticated and the request is safe
        $plugin = $this->plugins->exec_hook('ready', ['task' => $this->task, 'action' => $this->action]);

        $this->set_task($plugin['task']);
        $this->action = $plugin['action'];

        // handle special actions
        if ($this->action == 'keep-alive') {
            $this->output->reset();
            $this->plugins->exec_hook('keep_alive', []);
            $this->output->send();
        }

        $task       = $this->action == 'save-pref' ? 'utils' : $this->task;
        $task       = $task == 'addressbook' ? 'contacts' : $task;
        $task_class = "rcmail_action_{$task}_index";

        // execute the action index handler
        if (class_exists($task_class)) {
            $task_handler = new $task_class;
            $task_handler->run();
        }

        // allow 5 "redirects" to another action
        $redirects = 0;
        while ($redirects < 5) {
            // execute a plugin action
            if (preg_match('/^plugin\./', $this->action)) {
                $this->plugins->exec_action($this->action);
                break;
            }

            // execute action registered to a plugin task
            if ($this->plugins->is_plugin_task($task)) {
                if (!$this->action) $this->action = 'index';
                $this->plugins->exec_action("{$task}.{$this->action}");
                break;
            }

            $action = !empty($this->action) ? $this->action : 'index';

            // handle deprecated action names
            if (!empty($task_handler) && !empty($task_handler::$aliases[$action])) {
                $action = $task_handler::$aliases[$action];
            }

            $action = str_replace('-', '_', $action);
            $class  = "rcmail_action_{$task}_{$action}";

            // Run the action (except the index)
            if ($class != $task_class && class_exists($class)) {
                $handler = new $class;
                if (!$handler->checks()) {
                    break;
                }
                $handler->run($this->action_args);
                $redirects++;
            }
            else {
                break;
            }
        }

        if ($this->action == 'refresh') {
            $last = intval(rcube_utils::get_input_value('_last', rcube_utils::INPUT_GPC));
            $this->plugins->exec_hook('refresh', ['last' => $last]);
        }

        // parse main template (default)
        $this->output->send($this->task);

        // if we arrive here, something went wrong
        $error = ['code' => 404, 'line' => __LINE__, 'file' => __FILE__, 'message' => "Invalid request"];
        rcmail::raise_error($error, true, true);
    }

    /**
     * Return instance of the internal address book class
     *
     * @param string $id        Address book identifier. It accepts also special values:
     *                          - rcube_addressbook::TYPE_CONTACT (or 'sql') for the SQL addressbook
     *                          - rcube_addressbook::TYPE_DEFAULT for the default addressbook
     * @param bool   $writeable True if the address book needs to be writeable
     * @param bool   $fallback  Fallback to the first existing source, if the configured default wasn't found
     *
     * @return rcube_contacts|null Address book object
     */
    public function get_address_book($id, $writeable = false, $fallback = true)
    {
        $contacts    = null;
        $ldap_config = (array) $this->config->get('ldap_public');
        $default     = false;

        $id = (string) $id;

        // 'sql' is the alias for '0' used by autocomplete
        if ($id == 'sql') {
            $id = (string) rcube_addressbook::TYPE_CONTACT;
        }
        else if ($id === strval(rcube_addressbook::TYPE_DEFAULT) || $id === '-1') { // -1 for BC
            $id = $this->config->get('default_addressbook');
            $default = true;
        }

        // use existing instance
        if (isset($this->address_books[$id]) && ($this->address_books[$id] instanceof rcube_addressbook)) {
            $contacts = $this->address_books[$id];
        }
        else if ($id && !empty($ldap_config[$id])) {
            $domain   = $this->config->mail_domain($_SESSION['storage_host']);
            $contacts = new rcube_ldap($ldap_config[$id], $this->config->get('ldap_debug'), $domain);
        }
        else if ($id === (string) rcube_addressbook::TYPE_CONTACT) {
            $contacts = new rcube_contacts($this->db, $this->get_user_id());
        }
        else if ($id === (string) rcube_addressbook::TYPE_RECIPIENT || $id === (string) rcube_addressbook::TYPE_TRUSTED_SENDER) {
            $contacts = new rcube_addresses($this->db, $this->get_user_id(), (int) $id);
        }
        else {
            $plugin = $this->plugins->exec_hook('addressbook_get', ['id' => $id, 'writeable' => $writeable]);

            // plugin returned instance of a rcube_addressbook
            if (!empty($plugin['instance']) && $plugin['instance'] instanceof rcube_addressbook) {
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
        if ($fallback && !$contacts && (!$id || $default)) {
            $source = $this->get_address_sources($writeable, !$default);
            $source = reset($source);

            if (!empty($source)) {
                // Note: No fallback here to prevent from an infinite loop
                $contacts = $this->get_address_book($source['id'], false, false);
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

            self::raise_error([
                    'code'    => 700,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Addressbook source ($id) not found!"
                ],
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
     * @param rcube_addressbook $object Addressbook source object
     *
     * @return string|null Source identifier
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
     * @param bool $writeable   True if the address book needs to be writeable
     * @param bool $skip_hidden True if the address book needs to be not hidden
     *
     * @return array Address books array
     */
    public function get_address_sources($writeable = false, $skip_hidden = false)
    {
        $abook_type   = strtolower((string) $this->config->get('address_book_type', 'sql'));
        $ldap_config  = (array) $this->config->get('ldap_public');
        $list         = [];

        // SQL-based (built-in) address book
        if ($abook_type === 'sql') {
            $list[rcube_addressbook::TYPE_CONTACT] = [
                'id'       => (string) rcube_addressbook::TYPE_CONTACT,
                'name'     => $this->gettext('personaladrbook'),
                'groups'   => true,
                'readonly' => false,
                'undelete' => $this->config->get('undo_timeout') > 0,
            ];
        }

        // LDAP address book(s)
        if (!empty($ldap_config)) {
            foreach ($ldap_config as $id => $prop) {
                // handle misconfiguration
                if (empty($prop) || !is_array($prop)) {
                    continue;
                }

                $list[$id] = [
                    'id'       => $id,
                    'name'     => html::quote($prop['name']),
                    'groups'   => !empty($prop['groups']) || !empty($prop['group_filters']),
                    'readonly' => empty($prop['writable']),
                    'hidden'   => !empty($prop['hidden']),
                ];
            }
        }

        $collected_recipients = $this->config->get('collected_recipients');
        $collected_senders    = $this->config->get('collected_senders');

        if ($collected_recipients === (string) rcube_addressbook::TYPE_RECIPIENT) {
            $list[rcube_addressbook::TYPE_RECIPIENT] = [
                'id'       => (string) rcube_addressbook::TYPE_RECIPIENT,
                'name'     => $this->gettext('collectedrecipients'),
                'groups'   => false,
                'readonly' => true,
                'undelete' => false,
                'deletable' => true,
            ];
        }

        if ($collected_senders === (string) rcube_addressbook::TYPE_TRUSTED_SENDER) {
            $list[rcube_addressbook::TYPE_TRUSTED_SENDER] = [
                'id'       => (string) rcube_addressbook::TYPE_TRUSTED_SENDER,
                'name'     => $this->gettext('trustedsenders'),
                'groups'   => false,
                'readonly' => true,
                'undelete' => false,
                'deletable' => true,
            ];
        }

        // Plugins can also add address books, or re-order the list
        $plugin = $this->plugins->exec_hook('addressbooks_list', ['sources' => $list]);
        $list   = $plugin['sources'];

        foreach ($list as $idx => $item) {
            // remove from list if not writeable as requested
            if ($writeable && $item['readonly']) {
                unset($list[$idx]);
            }
            // remove from list if hidden as requested
            else if ($skip_hidden && !empty($item['hidden'])) {
                unset($list[$idx]);
            }
        }

        return $list;
    }

    /**
     * Getter for compose responses.
     *
     * @param bool $user_only True to exclude additional static responses
     *
     * @return array List of the current user's stored responses
     */
    public function get_compose_responses($user_only = false)
    {
        $responses = $this->user->list_responses();

        if (!$user_only) {
            $additional = [];
            foreach ($this->config->get('compose_responses_static', []) as $response) {
                $additional[$response['name']] = [
                    'id'      => 'static-' . substr(md5($response['name']), 0, 16),
                    'name'    => $response['name'],
                    'static'  => true,
                ];
            }

            if (!empty($additional)) {
                ksort($additional, SORT_LOCALE_STRING);
                $responses = array_merge(array_values($additional), $responses);
            }
        }

        $hook = $this->plugins->exec_hook('get_compose_responses', [
                'list'      => $responses,
                'user_only' => $user_only,
        ]);

        return $hook['list'];
    }

    /**
     * Getter for compose response data.
     *
     * @param int|string $id Response ID
     *
     * @return array|null Response data, Null if not found
     */
    public function get_compose_response($id)
    {
        $record = null;

        // Static response
        if (strpos((string) $id, 'static-') === 0) {
            foreach ($this->config->get('compose_responses_static', []) as $response) {
                $rid = 'static-' . substr(md5($response['name']), 0, 16);
                if ($id === $rid) {
                    $record = [
                        'id'      => $rid,
                        'name'    => $response['name'],
                        'data'    => !empty($response['html']) ? $response['html'] : $response['text'],
                        'is_html' => !empty($response['html']),
                        'static'  => true,
                    ];
                    break;
                }
            }
        }

        // User owned response
        if (empty($record) && is_numeric($id)) {
            $record = $this->user->get_response($id);
        }

        // Plugin-provided response or other modifications
        $hook = $this->plugins->exec_hook('get_compose_response', [
                'id'     => $id,
                'record' => $record,
        ]);

        return $hook['record'];
    }

    /**
     * Init output object for GUI and add common scripts.
     * This will instantiate a rcmail_output_html object and set
     * environment vars according to the current session and configuration
     *
     * @param bool $framed True if this request is loaded in a (i)frame
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
        $this->output->add_label('loading', 'servererror', 'connerror', 'requesttimedout',
            'refreshing', 'windowopenerror', 'uploadingmany', 'uploading', 'close', 'save', 'cancel',
            'alerttitle', 'confirmationtitle', 'delete', 'continue', 'ok');

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
            $this->output = new rcmail_output_json();
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
        if (empty($_SESSION['user_id'])) {
            $_SESSION['temp'] = true;
        }
    }

    /**
     * Perform login to the mail server and to the webmail service.
     * This will also create a new user entry if auto_create_user is configured.
     *
     * @param string $username    Mail storage (IMAP) user name
     * @param string $password    Mail storage (IMAP) password
     * @param string $host        Mail storage (IMAP) host
     * @param bool   $cookiecheck Enables cookie check
     *
     * @return bool True on success, False on failure
     */
    function login($username, $password, $host = null, $cookiecheck = false)
    {
        $this->login_error = null;

        if (empty($username)) {
            return false;
        }

        if ($cookiecheck && empty($_COOKIE)) {
            $this->login_error = self::ERROR_COOKIES_DISABLED;
            return false;
        }

        $imap_host       = $this->config->get('imap_host', 'localhost:143');
        $username_domain = $this->config->get('username_domain');
        $login_lc        = $this->config->get('login_lc', 2);

        // check username input validity
        if (!$this->login_input_checks($username, $password)) {
            $this->login_error = self::ERROR_INVALID_REQUEST;
            return false;
        }

        // host is validated in rcmail::autoselect_host(), so here
        // we'll only handle unset host (if possible)
        if (!$host && !empty($imap_host)) {
            if (is_array($imap_host)) {
                $key  = key($imap_host);
                $host = is_numeric($key) ? $imap_host[$key] : $key;
            }
            else {
                $host = $imap_host;
            }
        }

        $host = rcube_utils::parse_host($host);

        if (!$host) {
            $this->login_error = self::ERROR_INVALID_HOST;
            return false;
        }

        // parse $host URL
        list($host, $scheme, $port) = rcube_utils::parse_host_uri($host, 143, 993);

        $ssl = in_array($scheme, ['ssl', 'imaps', 'tls']) ? $scheme : false;

        // Check if we need to add/force domain to username
        if (!empty($username_domain)) {
            $domain = '';
            if (is_array($username_domain)) {
                if (!empty($username_domain[$host])) {
                    $domain = $username_domain[$host];
                }
            }
            else {
                $domain = $username_domain;
            }

            if ($domain = rcube_utils::parse_host((string) $domain, $host)) {
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
                list($local, $domain) = rcube_utils::explode('@', $username);
                $username = $local . '@' . mb_strtolower($domain);
            }
        }

        // try to resolve email address from virtuser table
        if (strpos($username, '@') && ($virtuser = rcube_user::email2user($username))) {
            $username = $virtuser;
        }

        // Here we need IDNA ASCII
        // Only rcube_contacts class is using domain names in Unicode
        $host = rcube_utils::idn_to_ascii($host);
        if (strpos($username, '@')) {
            $username = rcube_utils::idn_to_ascii($username);
        }

        // user already registered -> overwrite username
        if ($user = rcube_user::query($username, $host)) {
            $username = $user->data['username'];

            // Brute-force prevention
            if ($user->is_locked()) {
                $this->login_error = self::ERROR_RATE_LIMIT;
                return false;
            }
        }

        $storage = $this->get_storage();

        // try to log in
        if (!$storage->connect($host, $username, $password, $port, $ssl)) {
            if ($user) {
                $user->failed_login();
            }

            // Wait a second to slow down brute-force attacks (#1490549)
            sleep(1);
            return false;
        }

        // user already registered -> update user's record
        if (is_object($user)) {
            // update last login timestamp
            $user->touch();
        }
        // create new system user
        else if ($this->config->get('auto_create_user')) {
            // Temporarily set user email and password, so plugins can use it
            // this way until we set it in session later. This is required e.g.
            // by the user-specific LDAP operations from new_user_identity plugin.
            $domain = $this->config->mail_domain($host);
            $this->user_email = strpos($username, '@') ? $username : sprintf('%s@%s', $username, $domain);
            $this->password   = $password;

            $user = rcube_user::create($username, $host);

            $this->user_email = null;
            $this->password   = null;

            if (!$user) {
                self::raise_error([
                        'code'    => 620,
                        'file'    => __FILE__,
                        'line'    => __LINE__,
                        'message' => "Failed to create a user record. Maybe aborted by a plugin?"
                    ],
                    true, false
                );
            }
        }
        else {
            self::raise_error([
                    'code'    => 621,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Access denied for new user $username. 'auto_create_user' is disabled"
                ],
                true, false
            );
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
            $_SESSION['password']     = $this->encrypt($password);
            $_SESSION['login_time']   = time();

            $timezone = rcube_utils::get_input_string('_timezone', rcube_utils::INPUT_GPC);
            if ($timezone && $timezone != '_default_') {
                $_SESSION['timezone'] = $timezone;
            }

            // fix some old settings according to namespace prefix
            $this->fix_namespace_settings($user);

            // set/create special folders
            $this->set_special_folders();

            // clear all mailboxes related cache(s)
            $storage->clear_cache('mailboxes', true);

            return true;
        }

        return false;
    }

    /**
     * Returns error code of last login operation
     *
     * @return int|null Error code
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
     * Validate username input
     *
     * @param string $username User name
     * @param string $password User password
     *
     * @return bool True if valid, False otherwise
     */
    private function login_input_checks($username, $password)
    {
        $username_filter = $this->config->get('login_username_filter');
        $username_maxlen = $this->config->get('login_username_maxlen', 1024);
        $password_maxlen = $this->config->get('login_password_maxlen', 1024);

        if ($username_maxlen && strlen($username) > $username_maxlen) {
            return false;
        }

        if ($password_maxlen && strlen($password) > $password_maxlen) {
            return false;
        }

        if ($username_filter) {
            $is_email = strtolower($username_filter) == 'email';

            if ($is_email && !rcube_utils::check_email($username, false)) {
                return false;
            }

            if (!$is_email && !preg_match($username_filter, $username)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Detects session errors
     *
     * @return string|null Error label
     */
    public function session_error()
    {
        // log session failures
        $task = rcube_utils::get_input_string('_task', rcube_utils::INPUT_GPC);

        if ($task && !in_array($task, ['login', 'logout']) && !empty($_COOKIE[ini_get('session.name')])) {
            $sess_id = $_COOKIE[ini_get('session.name')];
            $log     = "Aborted session $sess_id; no valid session data found";
            $error   = 'sessionerror';

            // In rare cases web browser might end up with multiple cookies of the same name
            // but different params, e.g. domain (webmail.domain.tld and .webmail.domain.tld).
            // In such case browser will send both cookies in the request header
            // problem is that PHP session handler can use only one and if that one session
            // does not exist we'll end up here
            $cookie          = rcube_utils::request_header('Cookie');
            $cookie_sessid   = $this->config->get('session_name') ?: 'roundcube_sessid';
            $cookie_sessauth = $this->config->get('session_auth_name') ?: 'roundcube_sessauth';

            if (substr_count($cookie, $cookie_sessid.'=') > 1 || substr_count($cookie, $cookie_sessauth.'=') > 1) {
                $log .= ". Cookies mismatch";
                $error = 'cookiesmismatch';
            }

            $this->session->log($log);

            return $error;
        }
    }

    /**
     * Auto-select IMAP host based on the posted login information
     *
     * @return string Selected IMAP host
     */
    public function autoselect_host()
    {
        $default_host = $this->config->get('imap_host');
        $host         = null;

        if (is_array($default_host)) {
            $post_host = rcube_utils::get_input_string('_host', rcube_utils::INPUT_POST);
            $post_user = rcube_utils::get_input_string('_user', rcube_utils::INPUT_POST);

            list(, $domain) = rcube_utils::explode('@', $post_user);

            // direct match in default_host array
            if (!empty($default_host[$post_host]) || in_array($post_host, array_values($default_host))) {
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
                $key  = key($default_host);
                $host = is_numeric($key) ? $default_host[$key] : $key;
            }
        }
        else if (empty($default_host)) {
            $host = rcube_utils::get_input_string('_host', rcube_utils::INPUT_POST);
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
        $_SESSION = ['language' => $this->user->language, 'temp' => true];
        $this->user->reset();

        if ($this->config->get('skin') != $this->default_skin && method_exists($this->output, 'set_skin')) {
            $this->output->set_skin($this->default_skin);
        }
    }

    /**
     * Do server side actions on logout
     */
    public function logout_actions()
    {
        $storage        = $this->get_storage();
        $logout_expunge = $this->config->get('logout_expunge');
        $logout_purge   = $this->config->get('logout_purge');
        $trash_mbox     = $this->config->get('trash_mbox');

        if ($logout_purge && !empty($trash_mbox)) {
            $getMessages = function ($folder) use ($logout_purge, $storage) {
                if (is_numeric($logout_purge)) {
                    $now      = new DateTime('now');
                    $interval = new DateInterval('P' . intval($logout_purge) . 'D');

                    return $storage->search_once($folder, 'BEFORE ' . $now->sub($interval)->format('j-M-Y'));
                }

                return '*';
            };

            $storage->delete_message($getMessages($trash_mbox), $trash_mbox);

            // Trash subfolders
            $delimiter  = $storage->get_hierarchy_delimiter();
            $subfolders = array_reverse($storage->list_folders($trash_mbox . $delimiter, '*'));
            $last       = '';

            foreach ($subfolders as $folder) {
                $messages = $getMessages($folder);

                // Delete the folder if in all-messages mode, or all existing messages are to-be-removed,
                // but not if there's a subfolder
                if (
                    ($messages === '*' || $messages->count() == $storage->count($folder, 'ALL', false, false))
                    && strpos($last, $folder . $delimiter) !== 0
                ) {
                    $storage->delete_folder($folder);
                }
                else {
                    $storage->delete_message($messages, $folder);
                    $last = $folder;
                }
            }
        }

        if ($logout_expunge) {
            $storage->expunge_folder('INBOX');
        }

        // Try to save unsaved user preferences
        if (!empty($_SESSION['preferences'])) {
            $this->user->save_prefs(unserialize($_SESSION['preferences']));
        }
    }

    /**
     * Build a valid URL to this instance of Roundcube
     *
     * @param mixed $p        Either a string with the action or
     *                        url parameters as key-value pairs
     * @param bool  $absolute Build a URL absolute to document root
     * @param bool  $full     Create fully qualified URL including http(s):// and hostname
     * @param bool  $secure   Return absolute URL in secure location
     *
     * @return string Valid application URL
     */
    public function url($p, $absolute = false, $full = false, $secure = false)
    {
        if (!is_array($p)) {
            if (preg_match('#^https?://#', $p)) {
                return $p;
            }

            $p = ['_action' => $p];
        }

        $task = $this->task;

        if (!empty($p['_task'])) {
            $task = $p['_task'];
        }
        else if (!empty($p['task'])) {
            $task = $p['task'];
        }

        unset($p['task'], $p['_task']);

        $pre  = ['_task' => $task];
        $url  = $this->filename;
        $delm = '?';

        foreach (array_merge($pre, $p) as $key => $val) {
            if ($val !== '' && $val !== null) {
                $par  = $key[0] == '_' ? $key : ('_' . $key);
                $url .= $delm . urlencode($par) . '=' . urlencode($val);
                $delm = '&';
            }
        }

        $base_path = $this->get_request_path();

        if ($secure && ($token = $this->get_secure_url_token(true))) {
            // add token to the url
            $url = $token . '/' . $url;

            // remove old token from the path
            $base_path = rtrim($base_path, '/');
            $base_path = preg_replace('/\/[a-zA-Z0-9]{' . strlen($token) . '}$/', '', $base_path);

            // this need to be full url to make redirects work
            $absolute = true;
        }
        else if ($secure && ($token = $this->get_request_token())) {
            $url .= $delm . '_token=' . urlencode($token);
        }

        if ($absolute || $full) {
            // add base path to this Roundcube installation
            $prefix = $base_path ?: '/';

            // prepend protocol://hostname:port
            if ($full) {
                $prefix = rcube_utils::resolve_url($prefix);
            }

            $prefix = rtrim($prefix, '/') . '/';
        }
        else {
            $prefix = $base_path ?: './';
        }

        return $prefix . $url;
    }

    /**
     * Get the the request path
     */
    protected function get_request_path()
    {
        $path = $this->config->get('request_path');

        if ($path && isset($_SERVER[$path])) {
            // HTTP headers need to come from a trusted proxy host
            if (strpos($path, 'HTTP_') === 0 && !rcube_utils::check_proxy_whitelist_ip()) {
                return '/';
            }

            $path = $_SERVER[$path];
        }
        else if (empty($path)) {
            foreach (['REQUEST_URI', 'REDIRECT_SCRIPT_URL', 'SCRIPT_NAME'] as $name) {
                if (!empty($_SERVER[$name])) {
                    $path = $_SERVER[$name];
                    break;
                }
            }
        }
        else {
            return rtrim($path, '/') . '/';
        }

        $path = preg_replace('/index\.php.*$/', '', (string) $path);
        $path = preg_replace('/[?&].*$/', '', $path);
        $path = preg_replace('![^/]+$!', '', $path);

        return rtrim($path, '/') . '/';
    }

    /**
     * Function to be executed in script shutdown
     */
    public function shutdown()
    {
        parent::shutdown();

        foreach ($this->address_books as $book) {
            if (is_a($book, 'rcube_addressbook')) {
                $book->close();
            }
        }

        $this->address_books = [];

        // In CLI stop here, prevent from errors when the console.log might exist,
        // but be not accessible
        if (php_sapi_name() == 'cli') {
            return;
        }

        // write performance stats to logs/console
        if ($this->config->get('devel_mode') || $this->config->get('performance_stats')) {
            // we have to disable per_user_logging to make sure stats end up in the main console log
            $this->config->set('per_user_logging', false);

            // make sure logged numbers use unified format
            setlocale(LC_NUMERIC, 'en_US.utf8', 'en_US.UTF-8', 'en_US', 'C');

            if (function_exists('memory_get_usage')) {
                $mem = round(memory_get_usage() / 1024 /1024, 1);

                if (function_exists('memory_get_peak_usage')) {
                    $mem .= '/'. round(memory_get_peak_usage() / 1024 / 1024, 1);
                }
            }

            $log = $this->task . ($this->action ? '/'.$this->action : '') . (isset($mem) ? " [$mem]" : '');

            if (defined('RCMAIL_START')) {
                self::print_timer(RCMAIL_START, $log);
            }
            else {
                self::console($log);
            }
        }
    }

    /**
     * CSRF attack prevention code. Raises error when check fails.
     *
     * @param int $mode Request mode
     */
    public function request_security_check($mode = rcube_utils::INPUT_POST)
    {
        // check request token
        if (!$this->check_request($mode)) {
            $error = ['code' => 403, 'message' => "Request security check failed"];
            self::raise_error($error, false, true);
        }
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
        $prefix     = (string) $this->storage->get_namespace('prefix');
        $prefix_len = strlen($prefix);

        if (!$prefix_len) {
            return;
        }

        if ($this->config->get('namespace_fixed')) {
            return;
        }

        $prefs = [];

        // Build namespace prefix regexp
        $ns     = $this->storage->get_namespace();
        $regexp = [];

        foreach ($ns as $entry) {
            if (!empty($entry)) {
                foreach ($entry as $item) {
                    if (isset($item[0]) && strlen($item[0])) {
                        $regexp[] = preg_quote($item[0], '/');
                    }
                }
            }
        }
        $regexp = '/^(' . implode('|', $regexp) . ')/';

        // Fix preferences
        $opts = ['drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox', 'archive_mbox'];
        foreach ($opts as $opt) {
            if ($value = $this->config->get($opt)) {
                if ($value != 'INBOX' && !preg_match($regexp, $value)) {
                    $prefs[$opt] = $prefix . $value;
                }
            }
        }

        if (($search_mods = $this->config->get('search_mods')) && !empty($search_mods)) {
            $folders = [];
            foreach ($search_mods as $idx => $value) {
                if ($idx != 'INBOX' && $idx != '*' && !preg_match($regexp, $idx)) {
                    $idx = $prefix . $idx;
                }
                $folders[$idx] = $value;
            }

            $prefs['search_mods'] = $folders;
        }

        if (($threading = $this->config->get('message_threading')) && !empty($threading)) {
            $folders = [];
            foreach ($threading as $idx => $value) {
                if ($idx != 'INBOX' && !preg_match($regexp, $idx)) {
                    $idx = $prefix . $idx;
                }
                $folders[$idx] = $value;
            }

            $prefs['message_threading'] = $folders;
        }

        if ($collapsed = $this->config->get('collapsed_folders')) {
            $folders     = explode('&&', $collapsed);
            $count       = count($folders);
            $folders_str = '';

            if ($count) {
                $folders[0]        = substr($folders[0], 1);
                $folders[$count-1] = substr($folders[$count-1], 0, -1);
            }

            foreach ($folders as $value) {
                if ($value != 'INBOX' && !preg_match($regexp, $value)) {
                    $value = $prefix . $value;
                }
                $folders_str .= '&' . $value . '&';
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
     * @param string $action New action value
     * @param array  $args   Arguments to be passed to the next action
     */
    public function overwrite_action($action, $args = [])
    {
        $this->action      = $action;
        $this->action_args = array_merge($this->action_args, $args);
        $this->output->set_env('action', $action);
    }

    /**
     * Set environment variables for specified config options
     *
     * @param array $options List of configuration option names
     *
     * @deprecated since 1.5-beta, use rcmail_action::set_env_config()
     */
    public function set_env_config($options)
    {
        rcmail_action::set_env_config($options);
    }

    /**
     * Insert a contact to specified addressbook.
     *
     * @param array             $contact Contact data
     * @param rcube_addressbook $source  The addressbook object
     * @param string            $error   Filled with an error message/label on error
     *
     * @return int|string|bool Contact ID on success, False otherwise
     */
    public function contact_create($contact, $source, &$error = null)
    {
        $contact['email'] = rcube_utils::idn_to_utf8($contact['email']);

        $contact = $this->plugins->exec_hook('contact_displayname', $contact);

        if (empty($contact['name'])) {
            $contact['name'] = rcube_addressbook::compose_display_name($contact);
        }

        // validate the contact
        if (!$source->validate($contact, true)) {
            if ($error = $source->get_error()) {
                $error = $error['message'];
            }

            return false;
        }

        $plugin = $this->plugins->exec_hook('contact_create', [
                'record' => $contact,
                'source' => $this->get_address_book_id($source),
        ]);

        $contact = $plugin['record'];

        if (!empty($plugin['abort'])) {
            if (!empty($plugin['message'])) {
                $error = $plugin['message'];
            }

            return $plugin['result'];
        }

        return $source->insert($contact);
    }

    /**
     * Find an email address in user addressbook(s)
     *
     * @param string $email Email address
     * @param int    $type  Addressbook type (see rcube_addressbook::TYPE_* constants)
     *
     * @return bool True if the address exists in specified addressbook(s), False otherwise
     */
    public function contact_exists($email, $type)
    {
        if (empty($email) || !is_string($email) || !strpos($email, '@')) {
            return false;
        }

        $email = rcube_utils::idn_to_utf8($email);

        // TODO: Support TYPE_READONLY filter
        $sources = [];

        if ($type & rcube_addressbook::TYPE_WRITEABLE) {
            foreach ($this->get_address_sources(true, true) as $book) {
                $sources[] = $book['id'];
            }
        }

        if ($type & rcube_addressbook::TYPE_DEFAULT) {
            if ($default = $this->get_address_book(rcube_addressbook::TYPE_DEFAULT, true)) {
                $book_id = $this->get_address_book_id($default);
                if (!in_array($book_id, $sources)) {
                    $sources[] = $book_id;
                }
            }
        }

        if ($type & rcube_addressbook::TYPE_RECIPIENT) {
            $collected_recipients = $this->config->get('collected_recipients');
            if (strlen($collected_recipients) && !in_array($collected_recipients, $sources)) {
                array_unshift($sources, $collected_recipients);
            }
        }

        if ($type & rcube_addressbook::TYPE_TRUSTED_SENDER) {
            $collected_senders = $this->config->get('collected_senders');
            if (strlen($collected_senders) && !in_array($collected_senders, $sources)) {
                array_unshift($sources, $collected_senders);
            }
        }

        $plugin = $this->plugins->exec_hook('contact_exists', [
                'email'   => $email,
                'type'    => $type,
                'sources' => $sources,
        ]);

        if (!empty($plugin['abort'])) {
            return $plugin['result'];
        }

        foreach ($plugin['sources'] as $source) {
            $contacts = $this->get_address_book($source);

            if (!$contacts) {
                continue;
            }

            $result = $contacts->search('email', $email, rcube_addressbook::SEARCH_STRICT, false);

            if ($result->count) {
                return true;
            }
        }

        return false;
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

        // don't log full session id for security reasons
        $session_id = session_id();
        $session_id = $session_id ? substr($session_id, 0, 16) : 'no-session';

        // failed login
        if ($failed_login) {
            // don't fill the log with complete input, which could
            // have been prepared by a hacker
            if (strlen($user) > 256) {
                $user = substr($user, 0, 256) . '...';
            }

            $message = sprintf('Failed login for %s from %s in session %s (error: %d)',
                $user, rcube_utils::remote_ip(), $session_id, $error_code);
        }
        // successful login
        else {
            $user_name = $this->get_user_name();
            $user_id   = $this->get_user_id();

            if (!$user_id) {
                return;
            }

            $message = sprintf('Successful login for %s (ID: %d) from %s in session %s',
                $user_name, $user_id, rcube_utils::remote_ip(), $session_id);
        }

        // log login
        self::write_log('userlogins', $message);
    }

    /**
     * Check if specified asset file exists
     *
     * @param string $path     Asset path
     * @param bool   $minified Fallback to minified version of the file
     *
     * @return string|null Asset path if found (modified if minified file found)
     */
    public function find_asset($path, $minified = true)
    {
        if (empty($path)) {
            return;
        }

        $assets_dir = $this->config->get('assets_dir');
        $root_path  = unslashify($assets_dir ?: INSTALL_PATH) . '/';
        $full_path  = $root_path . trim($path, '/');

        if (file_exists($full_path)) {
            return $path;
        }

        if ($minified && preg_match('/(?<!\.min)\.(js|css)$/', $path)) {
            $path      = preg_replace('/\.(js|css)$/', '.min.\\1', $path);
            $full_path = $root_path . trim($path, '/');

            if (file_exists($full_path)) {
                return $path;
            }
        }
    }

    /**
     * Create a HTML table based on the given data
     *
     * @param array  $attrib     Named table attributes
     * @param mixed  $table_data Table row data. Either a two-dimensional array
     *                           or a valid SQL result set
     * @param array  $show_cols  List of cols to show
     * @param string $id_col     Name of the identifier col
     *
     * @return string HTML table code
     * @deprecated since 1.5-beta, use rcmail_action::table_output()
     */
    public function table_output($attrib, $table_data, $show_cols, $id_col)
    {
        return rcmail_action::table_output($attrib, $table_data, $show_cols, $id_col);
    }

    /**
     * Convert the given date to a human readable form
     * This uses the date formatting properties from config
     *
     * @param mixed  $date    Date representation (string, timestamp or DateTimeInterface)
     * @param string $format  Date format to use
     * @param bool   $convert Enables date conversion according to user timezone
     *
     * @return string Formatted date string
     */
    public function format_date($date, $format = null, $convert = true)
    {
        if ($date instanceof DateTimeInterface) {
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

            if ($pretty_date && $timestamp > $today_limit && $timestamp <= $now) {
                $format = $this->config->get('date_today', $this->config->get('time_format', 'H:i'));
                $today  = true;
            }
            else if ($pretty_date && $timestamp > $week_limit && $timestamp <= $now) {
                $format = $this->config->get('date_short', 'D H:i');
            }
            else {
                $format = $this->config->get('date_long', 'Y-m-d H:i');
            }
        }

        // parse format string manually in order to provide localized weekday and month names
        $out = '';
        for ($i = 0; $i < strlen($format); $i++) {
            if ($format[$i] == "\\") {  // skip escape chars
                continue;
            }

            // write char "as-is"
            if ($format[$i] == ' ' || ($i > 0 && $format[$i-1] == "\\")) {
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
                $formatter = new IntlDateFormatter(setlocale(LC_ALL, '0'), IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
                $out .= $formatter->format($timestamp);
            }
            else {
                $out .= date($format[$i], $timestamp);
            }
        }

        if (!empty($today)) {
            $label = $this->gettext('today');
            // replace $ character with "Today" label (#1486120)
            if (strpos($out, '$') !== false) {
                $out = preg_replace('/\$/', $label, $out, 1);
            }
            else {
                $out = $label . ' ' . $out;
            }
        }

        if (isset($stz)) {
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
     * @deprecated since 1.5-beta, use rcmail_action::folder_list()
     */
    public function folder_list($attrib)
    {
        return rcmail_action::folder_list($attrib);
    }

    /**
     * Return folders list as html_select object
     *
     * @param array $p Named parameters
     *
     * @return html_select HTML drop-down object
     * @deprecated since 1.5-beta, use rcmail_action::folder_selector()
     */
    public function folder_selector($p = [])
    {
        return rcmail_action::folder_selector($p);
    }

    /**
     * Returns class name for the given folder if it is a special folder
     * (including shared/other users namespace roots).
     *
     * @param string $folder_id IMAP Folder name
     *
     * @return string|null CSS class name
     * @deprecated since 1.5-beta, use rcmail_action::folder_classname()
     */
    public function folder_classname($folder_id)
    {
        return rcmail_action::folder_classname($folder_id);
    }

    /**
     * Try to localize the given IMAP folder name.
     * UTF-7 decode it in case no localized text was found
     *
     * @param string $name        Folder name
     * @param bool   $with_path   Enable path localization
     * @param bool   $path_remove Remove the path
     *
     * @return string Localized folder name in UTF-8 encoding
     * @deprecated since 1.5-beta, use rcmail_action::localize_foldername()
     */
    public function localize_foldername($name, $with_path = false, $path_remove = false)
    {
        return rcmail_action::localize_foldername($name, $with_path, $path_remove);
    }

    /**
     * Localize folder path
     *
     * @deprecated since 1.5-beta, use rcmail_action::localize_folderpath()
     */
    public function localize_folderpath($path)
    {
        return rcmail_action::localize_folderpath($path);
    }

    /**
     * Return HTML for quota indicator object
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the quota indicator object
     * @deprecated since 1.5-beta, use rcmail_action::quota_display()
     */
    public static function quota_display($attrib)
    {
        return rcmail_action::quota_display($attrib);
    }

    /**
     * Return (parsed) quota information
     *
     * @param array $attrib Named parameters
     * @param array $folder Current folder
     *
     * @return array Quota information
     * @deprecated since 1.5-beta, use rcmail_action::quota_content()
     */
    public function quota_content($attrib = null, $folder = null)
    {
        return rcmail_action::quota_content($attrib, $folder);
    }

    /**
     * Outputs error message according to server error/response codes
     *
     * @param string $fallback      Fallback message label
     * @param array  $fallback_args Fallback message label arguments
     * @param string $suffix        Message label suffix
     * @param array  $params        Additional parameters (type, prefix)
     *
     * @deprecated since 1.5-beta, use rcmail_action::display_server_error()
     */
    public function display_server_error($fallback = null, $fallback_args = null, $suffix = '', $params = [])
    {
        rcmail_action::display_server_error($fallback, $fallback_args, $suffix, $params);
    }

    /**
     * Displays an error message on storage fatal errors
     *
     * @deprecated since 1.5-beta, use rcmail_action::storage_fatal_error()
     */
    public function storage_fatal_error()
    {
        rcmail_action::storage_fatal_error();
    }

    /**
     * Output HTML editor scripts
     *
     * @param string $mode Editor mode
     *
     * @deprecated since 1.5-beta, use rcmail_action::html_editor()
     */
    public function html_editor($mode = '')
    {
        rcmail_action::html_editor($mode);
    }

    /**
     * File upload progress handler.
     *
     * @deprecated We're using HTML5 upload progress
     */
    public function upload_progress()
    {
        // NOOP
        $this->output->send();
    }

    /**
     * Initializes file uploading interface.
     *
     * @param int $max_size Optional maximum file size in bytes
     *
     * @return string Human-readable file size limit
     * @deprecated since 1.5-beta, use rcmail_action::upload_init()
     */
    public function upload_init($max_size = null)
    {
        return rcmail_action::upload_init($max_size);
    }

    /**
     * Upload form object
     *
     * @param array  $attrib     Object attributes
     * @param string $name       Form object name
     * @param string $action     Form action name
     * @param array  $input_attr File input attributes
     * @param int    $max_size   Maximum upload size
     *
     * @return string HTML output
     * @deprecated since 1.5-beta, use rcmail_action::upload_form()
     */
    public function upload_form($attrib, $name, $action, $input_attr = [], $max_size = null)
    {
        return rcmail_action::upload_form($attrib, $name, $action, $input_attr, $max_size);
    }

    /**
     * Outputs uploaded file content (with image thumbnails support
     *
     * @param array $file Upload file data
     *
     * @deprecated since 1.5-beta, use rcmail_action::display_uploaded_file()
     */
    public function display_uploaded_file($file)
    {
        rcmail_action::display_uploaded_file($file);
    }

    /**
     * Initializes client-side autocompletion.
     *
     * @deprecated since 1.5-beta, use rcmail_action::autocomplete_init()
     */
    public function autocomplete_init()
    {
        rcmail_action::autocomplete_init();
    }

    /**
     * Returns supported font-family specifications
     *
     * @param string $font Font name
     *
     * @return string|array Font-family specification array or string (if $font is used)
     * @deprecated since 1.5-beta, use rcmail_action::autocomplete_init()
     */
    public static function font_defs($font = null)
    {
        return rcmail_action::font_defs($font);
    }

    /**
     * Create a human readable string for a number of bytes
     *
     * @param int    $bytes Number of bytes
     * @param string &$unit Size unit
     *
     * @return string Byte string
     * @deprecated since 1.5-beta, use rcmail_action::show_bytes()
     */
    public function show_bytes($bytes, &$unit = null)
    {
        return rcmail_action::show_bytes($bytes, $unit);
    }

    /**
     * Returns real size (calculated) of the message part
     *
     * @param rcube_message_part $part Message part
     *
     * @return string Part size (and unit)
     * @deprecated since 1.5-beta, use rcmail_action::message_part_size()
     */
    public function message_part_size($part)
    {
        return rcmail_action::message_part_size($part);
    }

    /**
     * Returns message UID(s) and IMAP folder(s) from GET/POST data
     *
     * @param string $uids           UID value to decode
     * @param string $mbox           Default mailbox value (if not encoded in UIDs)
     * @param bool   $is_multifolder Will be set to True if multi-folder request
     * @param int    $mode           Request mode. Default: rcube_utils::INPUT_GPC.
     *
     * @return array  List of message UIDs per folder
     * @deprecated since 1.5-beta, use rcmail_action::get_uids()
     */
    public static function get_uids($uids = null, $mbox = null, &$is_multifolder = false, $mode = null)
    {
        return rcmail_action::get_uids($uids, $mbox, $is_multifolder, $mode);
    }

    /**
     * Get resource file content (with assets_dir support)
     *
     * @param string $name File name
     *
     * @return string File content
     * @deprecated since 1.5-beta, use rcmail_action::get_resource_content()
     */
    public function get_resource_content($name)
    {
        return rcmail_action::get_resource_content($name);
    }

    /**
     * Converts HTML content into plain text
     *
     * @param string $html    HTML content
     * @param array  $options Conversion parameters (width, links, charset)
     *
     * @return string Plain text
     */
    public function html2text($html, $options = [])
    {
        $default_options = [
            'links'   => $this->config->get('html2text_links', rcube_html2text::LINKS_DEFAULT),
            'width'   => $this->config->get('html2text_width') ?: 75,
            'body'    => $html,
            'charset' => RCUBE_CHARSET,
        ];

        $options = array_merge($default_options, (array) $options);

        // Plugins may want to modify HTML in another/additional way
        $options = $this->plugins->exec_hook('html2text', $options);

        // Convert to text
        if (empty($options['abort'])) {
            $converter = new rcube_html2text($options['body'],
                false, $options['links'], $options['width'], $options['charset']);

            $options['body'] = rtrim($converter->get_text());
        }

        return $options['body'];
    }

    /**
     * Connect to the mail storage server with stored session data
     *
     * @return bool True on success, False on error
     */
    public function storage_connect()
    {
        $storage = $this->get_storage();

        if (!empty($_SESSION['storage_host']) && !$storage->is_connected()) {
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
