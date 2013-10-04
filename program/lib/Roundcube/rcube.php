<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Framework base class providing core functions and holding           |
 |   instances of all 'global' objects like db- and storage-connections  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


/**
 * Base class of the Roundcube Framework
 * implemented as singleton
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube
{
    const INIT_WITH_DB = 1;
    const INIT_WITH_PLUGINS = 2;

    /**
     * Singleton instace of rcube
     *
     * @var rcube
     */
    static protected $instance;

    /**
     * Stores instance of rcube_config.
     *
     * @var rcube_config
     */
    public $config;

    /**
     * Instace of database class.
     *
     * @var rcube_db
     */
    public $db;

    /**
     * Instace of Memcache class.
     *
     * @var Memcache
     */
    public $memcache;

   /**
     * Instace of rcube_session class.
     *
     * @var rcube_session
     */
    public $session;

    /**
     * Instance of rcube_smtp class.
     *
     * @var rcube_smtp
     */
    public $smtp;

    /**
     * Instance of rcube_storage class.
     *
     * @var rcube_storage
     */
    public $storage;

    /**
     * Instance of rcube_output class.
     *
     * @var rcube_output
     */
    public $output;

    /**
     * Instance of rcube_plugin_api.
     *
     * @var rcube_plugin_api
     */
    public $plugins;


    /* private/protected vars */
    protected $texts;
    protected $caches = array();
    protected $shutdown_functions = array();
    protected $expunge_cache = false;


    /**
     * This implements the 'singleton' design pattern
     *
     * @param integer Options to initialize with this instance. See rcube::INIT_WITH_* constants
     *
     * @return rcube The one and only instance
     */
    static function get_instance($mode = 0)
    {
        if (!self::$instance) {
            self::$instance = new rcube();
            self::$instance->init($mode);
        }

        return self::$instance;
    }


    /**
     * Private constructor
     */
    protected function __construct()
    {
        // load configuration
        $this->config  = new rcube_config;
        $this->plugins = new rcube_dummy_plugin_api;

        register_shutdown_function(array($this, 'shutdown'));
    }


    /**
     * Initial startup function
     */
    protected function init($mode = 0)
    {
        // initialize syslog
        if ($this->config->get('log_driver') == 'syslog') {
            $syslog_id       = $this->config->get('syslog_id', 'roundcube');
            $syslog_facility = $this->config->get('syslog_facility', LOG_USER);
            openlog($syslog_id, LOG_ODELAY, $syslog_facility);
        }

        // connect to database
        if ($mode & self::INIT_WITH_DB) {
            $this->get_dbh();
        }

        // create plugin API and load plugins
        if ($mode & self::INIT_WITH_PLUGINS) {
            $this->plugins = rcube_plugin_api::get_instance();
        }
    }


    /**
     * Get the current database connection
     *
     * @return rcube_db Database object
     */
    public function get_dbh()
    {
        if (!$this->db) {
            $config_all = $this->config->all();
            $this->db = rcube_db::factory($config_all['db_dsnw'], $config_all['db_dsnr'], $config_all['db_persistent']);
            $this->db->set_debug((bool)$config_all['sql_debug']);
        }

        return $this->db;
    }


    /**
     * Get global handle for memcache access
     *
     * @return object Memcache
     */
    public function get_memcache()
    {
        if (!isset($this->memcache)) {
            // no memcache support in PHP
            if (!class_exists('Memcache')) {
                $this->memcache = false;
                return false;
            }

            $this->memcache     = new Memcache;
            $this->mc_available = 0;

            // add all configured hosts to pool
            $pconnect = $this->config->get('memcache_pconnect', true);
            foreach ($this->config->get('memcache_hosts', array()) as $host) {
                if (substr($host, 0, 7) != 'unix://') {
                    list($host, $port) = explode(':', $host);
                    if (!$port) $port = 11211;
                }
                else {
                    $port = 0;
                }

                $this->mc_available += intval($this->memcache->addServer(
                    $host, $port, $pconnect, 1, 1, 15, false, array($this, 'memcache_failure')));
            }

            // test connection and failover (will result in $this->mc_available == 0 on complete failure)
            $this->memcache->increment('__CONNECTIONTEST__', 1);  // NOP if key doesn't exist

            if (!$this->mc_available) {
                $this->memcache = false;
            }
        }

        return $this->memcache;
    }


    /**
     * Callback for memcache failure
     */
    public function memcache_failure($host, $port)
    {
        static $seen = array();

        // only report once
        if (!$seen["$host:$port"]++) {
            $this->mc_available--;
            self::raise_error(array(
                'code' => 604, 'type' => 'db',
                'line' => __LINE__, 'file' => __FILE__,
                'message' => "Memcache failure on host $host:$port"),
                true, false);
        }
    }


    /**
     * Initialize and get cache object
     *
     * @param string $name   Cache identifier
     * @param string $type   Cache type ('db', 'apc' or 'memcache')
     * @param string $ttl    Expiration time for cache items
     * @param bool   $packed Enables/disables data serialization
     *
     * @return rcube_cache Cache object
     */
    public function get_cache($name, $type='db', $ttl=0, $packed=true)
    {
        if (!isset($this->caches[$name]) && ($userid = $this->get_user_id())) {
            $this->caches[$name] = new rcube_cache($type, $userid, $name, $ttl, $packed);
        }

        return $this->caches[$name];
    }


    /**
     * Create SMTP object and connect to server
     *
     * @param boolean True if connection should be established
     */
    public function smtp_init($connect = false)
    {
        $this->smtp = new rcube_smtp();

        if ($connect) {
            $this->smtp->connect();
        }
    }


    /**
     * Initialize and get storage object
     *
     * @return rcube_storage Storage object
     */
    public function get_storage()
    {
        // already initialized
        if (!is_object($this->storage)) {
            $this->storage_init();
        }

        return $this->storage;
    }


    /**
     * Initialize storage object
     */
    public function storage_init()
    {
        // already initialized
        if (is_object($this->storage)) {
            return;
        }

        $driver       = $this->config->get('storage_driver', 'imap');
        $driver_class = "rcube_{$driver}";

        if (!class_exists($driver_class)) {
            self::raise_error(array(
                'code' => 700, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Storage driver class ($driver) not found!"),
                true, true);
        }

        // Initialize storage object
        $this->storage = new $driver_class;

        // for backward compat. (deprecated, will be removed)
        $this->imap = $this->storage;

        // enable caching of mail data
        $storage_cache  = $this->config->get("{$driver}_cache");
        $messages_cache = $this->config->get('messages_cache');
        // for backward compatybility
        if ($storage_cache === null && $messages_cache === null && $this->config->get('enable_caching')) {
            $storage_cache  = 'db';
            $messages_cache = true;
        }

        if ($storage_cache) {
            $this->storage->set_caching($storage_cache);
        }
        if ($messages_cache) {
            $this->storage->set_messages_caching(true);
        }

        // set pagesize from config
        $pagesize = $this->config->get('mail_pagesize');
        if (!$pagesize) {
            $pagesize = $this->config->get('pagesize', 50);
        }
        $this->storage->set_pagesize($pagesize);

        // set class options
        $options = array(
            'auth_type'   => $this->config->get("{$driver}_auth_type", 'check'),
            'auth_cid'    => $this->config->get("{$driver}_auth_cid"),
            'auth_pw'     => $this->config->get("{$driver}_auth_pw"),
            'debug'       => (bool) $this->config->get("{$driver}_debug"),
            'force_caps'  => (bool) $this->config->get("{$driver}_force_caps"),
            'timeout'     => (int) $this->config->get("{$driver}_timeout"),
            'skip_deleted' => (bool) $this->config->get('skip_deleted'),
            'driver'      => $driver,
        );

        if (!empty($_SESSION['storage_host'])) {
            $options['host']     = $_SESSION['storage_host'];
            $options['user']     = $_SESSION['username'];
            $options['port']     = $_SESSION['storage_port'];
            $options['ssl']      = $_SESSION['storage_ssl'];
            $options['password'] = $this->decrypt($_SESSION['password']);
            $_SESSION[$driver.'_host'] = $_SESSION['storage_host'];
        }

        $options = $this->plugins->exec_hook("storage_init", $options);

        // for backward compat. (deprecated, to be removed)
        $options = $this->plugins->exec_hook("imap_init", $options);

        $this->storage->set_options($options);
        $this->set_storage_prop();
    }


    /**
     * Set storage parameters.
     * This must be done AFTER connecting to the server!
     */
    protected function set_storage_prop()
    {
        $storage = $this->get_storage();

        $storage->set_charset($this->config->get('default_charset', RCUBE_CHARSET));

        if ($default_folders = $this->config->get('default_folders')) {
            $storage->set_default_folders($default_folders);
        }
        if (isset($_SESSION['mbox'])) {
            $storage->set_folder($_SESSION['mbox']);
        }
        if (isset($_SESSION['page'])) {
            $storage->set_page($_SESSION['page']);
        }
    }


    /**
     * Create session object and start the session.
     */
    public function session_init()
    {
        // session started (Installer?)
        if (session_id()) {
            return;
        }

        $sess_name   = $this->config->get('session_name');
        $sess_domain = $this->config->get('session_domain');
        $sess_path   = $this->config->get('session_path');
        $lifetime    = $this->config->get('session_lifetime', 0) * 60;
        $is_secure   = $this->config->get('use_https') || rcube_utils::https_check();

        // set session domain
        if ($sess_domain) {
            ini_set('session.cookie_domain', $sess_domain);
        }
        // set session path
        if ($sess_path) {
            ini_set('session.cookie_path', $sess_path);
        }
        // set session garbage collecting time according to session_lifetime
        if ($lifetime) {
            ini_set('session.gc_maxlifetime', $lifetime * 2);
        }

        ini_set('session.cookie_secure', $is_secure);
        ini_set('session.name', $sess_name ? $sess_name : 'roundcube_sessid');
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.serialize_handler', 'php');
        ini_set('session.cookie_httponly', 1);

        // use database for storing session data
        $this->session = new rcube_session($this->get_dbh(), $this->config);

        $this->session->register_gc_handler(array($this, 'temp_gc'));
        $this->session->register_gc_handler(array($this, 'cache_gc'));

        $this->session->set_secret($this->config->get('des_key') . dirname($_SERVER['SCRIPT_NAME']));
        $this->session->set_ip_check($this->config->get('ip_check'));

        if ($this->config->get('session_auth_name')) {
            $this->session->set_cookiename($this->config->get('session_auth_name'));
        }

        // start PHP session (if not in CLI mode)
        if ($_SERVER['REMOTE_ADDR']) {
            session_start();
        }
    }


    /**
     * Garbage collector function for temp files.
     * Remove temp files older than two days
     */
    public function temp_gc()
    {
        $tmp = unslashify($this->config->get('temp_dir'));
        $expire = time() - 172800;  // expire in 48 hours

        if ($tmp && ($dir = opendir($tmp))) {
            while (($fname = readdir($dir)) !== false) {
                if ($fname{0} == '.') {
                    continue;
                }

                if (filemtime($tmp.'/'.$fname) < $expire) {
                    @unlink($tmp.'/'.$fname);
                }
            }

            closedir($dir);
        }
    }


    /**
     * Garbage collector for cache entries.
     * Set flag to expunge caches on shutdown
     */
    public function cache_gc()
    {
        // because this gc function is called before storage is initialized,
        // we just set a flag to expunge storage cache on shutdown.
        $this->expunge_cache = true;
    }


    /**
     * Get localized text in the desired language
     *
     * @param mixed   $attrib  Named parameters array or label name
     * @param string  $domain  Label domain (plugin) name
     *
     * @return string Localized text
     */
    public function gettext($attrib, $domain=null)
    {
        // load localization files if not done yet
        if (empty($this->texts)) {
            $this->load_language();
        }

        // extract attributes
        if (is_string($attrib)) {
            $attrib = array('name' => $attrib);
        }

        $name = $attrib['name'] ? $attrib['name'] : '';

        // attrib contain text values: use them from now
        if (($setval = $attrib[strtolower($_SESSION['language'])]) || ($setval = $attrib['en_us'])) {
            $this->texts[$name] = $setval;
        }

        // check for text with domain
        if ($domain && ($text = $this->texts[$domain.'.'.$name])) {
        }
        // text does not exist
        else if (!($text = $this->texts[$name])) {
            return "[$name]";
        }

        // replace vars in text
        if (is_array($attrib['vars'])) {
            foreach ($attrib['vars'] as $var_key => $var_value) {
                $text = str_replace($var_key[0]!='$' ? '$'.$var_key : $var_key, $var_value, $text);
            }
        }

        // format output
        if (($attrib['uppercase'] && strtolower($attrib['uppercase'] == 'first')) || $attrib['ucfirst']) {
            return ucfirst($text);
        }
        else if ($attrib['uppercase']) {
            return mb_strtoupper($text);
        }
        else if ($attrib['lowercase']) {
            return mb_strtolower($text);
        }

        return strtr($text, array('\n' => "\n"));
    }


    /**
     * Check if the given text label exists
     *
     * @param string  $name       Label name
     * @param string  $domain     Label domain (plugin) name or '*' for all domains
     * @param string  $ref_domain Sets domain name if label is found
     *
     * @return boolean True if text exists (either in the current language or in en_US)
     */
    public function text_exists($name, $domain = null, &$ref_domain = null)
    {
        // load localization files if not done yet
        if (empty($this->texts)) {
            $this->load_language();
        }

        if (isset($this->texts[$name])) {
            $ref_domain = '';
            return true;
        }

        // any of loaded domains (plugins)
        if ($domain == '*') {
            foreach ($this->plugins->loaded_plugins() as $domain) {
                if (isset($this->texts[$domain.'.'.$name])) {
                    $ref_domain = $domain;
                    return true;
                }
            }
        }
        // specified domain
        else if ($domain) {
            $ref_domain = $domain;
            return isset($this->texts[$domain.'.'.$name]);
        }

        return false;
    }


    /**
     * Load a localization package
     *
     * @param string Language ID
     * @param array  Additional text labels/messages
     */
    public function load_language($lang = null, $add = array())
    {
        $lang = $this->language_prop(($lang ? $lang : $_SESSION['language']));

        // load localized texts
        if (empty($this->texts) || $lang != $_SESSION['language']) {
            $this->texts = array();

            // handle empty lines after closing PHP tag in localization files
            ob_start();

            // get english labels (these should be complete)
            @include(RCUBE_LOCALIZATION_DIR . 'en_US/labels.inc');
            @include(RCUBE_LOCALIZATION_DIR . 'en_US/messages.inc');

            if (is_array($labels))
                $this->texts = $labels;
            if (is_array($messages))
                $this->texts = array_merge($this->texts, $messages);

            // include user language files
            if ($lang != 'en' && $lang != 'en_US' && is_dir(RCUBE_LOCALIZATION_DIR . $lang)) {
                include_once(RCUBE_LOCALIZATION_DIR . $lang . '/labels.inc');
                include_once(RCUBE_LOCALIZATION_DIR . $lang . '/messages.inc');

                if (is_array($labels))
                    $this->texts = array_merge($this->texts, $labels);
                if (is_array($messages))
                    $this->texts = array_merge($this->texts, $messages);
            }

            ob_end_clean();

            $_SESSION['language'] = $lang;
        }

        // append additional texts (from plugin)
        if (is_array($add) && !empty($add)) {
            $this->texts += $add;
        }
    }


    /**
     * Check the given string and return a valid language code
     *
     * @param string Language code
     *
     * @return string Valid language code
     */
    protected function language_prop($lang)
    {
        static $rcube_languages, $rcube_language_aliases;

        // user HTTP_ACCEPT_LANGUAGE if no language is specified
        if (empty($lang) || $lang == 'auto') {
            $accept_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang         = str_replace('-', '_', $accept_langs[0]);
        }

        if (empty($rcube_languages)) {
            @include(RCUBE_LOCALIZATION_DIR . 'index.inc');
        }

        // check if we have an alias for that language
        if (!isset($rcube_languages[$lang]) && isset($rcube_language_aliases[$lang])) {
            $lang = $rcube_language_aliases[$lang];
        }
        // try the first two chars
        else if (!isset($rcube_languages[$lang])) {
            $short = substr($lang, 0, 2);

            // check if we have an alias for the short language code
            if (!isset($rcube_languages[$short]) && isset($rcube_language_aliases[$short])) {
                $lang = $rcube_language_aliases[$short];
            }
            // expand 'nn' to 'nn_NN'
            else if (!isset($rcube_languages[$short])) {
                $lang = $short.'_'.strtoupper($short);
            }
        }

        if (!isset($rcube_languages[$lang]) || !is_dir(RCUBE_LOCALIZATION_DIR . $lang)) {
            $lang = 'en_US';
        }

        return $lang;
    }


    /**
     * Read directory program/localization and return a list of available languages
     *
     * @return array List of available localizations
     */
    public function list_languages()
    {
        static $sa_languages = array();

        if (!sizeof($sa_languages)) {
            @include(RCUBE_LOCALIZATION_DIR . 'index.inc');

            if ($dh = @opendir(RCUBE_LOCALIZATION_DIR)) {
                while (($name = readdir($dh)) !== false) {
                    if ($name[0] == '.' || !is_dir(RCUBE_LOCALIZATION_DIR . $name)) {
                        continue;
                    }

                    if ($label = $rcube_languages[$name]) {
                        $sa_languages[$name] = $label;
                    }
                }
                closedir($dh);
            }
        }

        return $sa_languages;
    }


    /**
     * Encrypt using 3DES
     *
     * @param string $clear clear text input
     * @param string $key encryption key to retrieve from the configuration, defaults to 'des_key'
     * @param boolean $base64 whether or not to base64_encode() the result before returning
     *
     * @return string encrypted text
     */
    public function encrypt($clear, $key = 'des_key', $base64 = true)
    {
        if (!$clear) {
            return '';
        }

        /*-
         * Add a single canary byte to the end of the clear text, which
         * will help find out how much of padding will need to be removed
         * upon decryption; see http://php.net/mcrypt_generic#68082
         */
        $clear = pack("a*H2", $clear, "80");

        if (function_exists('mcrypt_module_open') &&
            ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, ""))
        ) {
            $iv = $this->create_iv(mcrypt_enc_get_iv_size($td));
            mcrypt_generic_init($td, $this->config->get_crypto_key($key), $iv);
            $cipher = $iv . mcrypt_generic($td, $clear);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        }
        else {
            @include_once 'des.inc';

            if (function_exists('des')) {
                $des_iv_size = 8;
                $iv = $this->create_iv($des_iv_size);
                $cipher = $iv . des($this->config->get_crypto_key($key), $clear, 1, 1, $iv);
            }
            else {
                self::raise_error(array(
                    'code' => 500, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not perform encryption; make sure Mcrypt is installed or lib/des.inc is available"
                    ), true, true);
            }
        }

        return $base64 ? base64_encode($cipher) : $cipher;
    }


    /**
     * Decrypt 3DES-encrypted string
     *
     * @param string $cipher encrypted text
     * @param string $key encryption key to retrieve from the configuration, defaults to 'des_key'
     * @param boolean $base64 whether or not input is base64-encoded
     *
     * @return string decrypted text
     */
    public function decrypt($cipher, $key = 'des_key', $base64 = true)
    {
        if (!$cipher) {
            return '';
        }

        $cipher = $base64 ? base64_decode($cipher) : $cipher;

        if (function_exists('mcrypt_module_open') &&
            ($td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_CBC, ""))
        ) {
            $iv_size = mcrypt_enc_get_iv_size($td);
            $iv = substr($cipher, 0, $iv_size);

            // session corruption? (#1485970)
            if (strlen($iv) < $iv_size) {
                return '';
            }

            $cipher = substr($cipher, $iv_size);
            mcrypt_generic_init($td, $this->config->get_crypto_key($key), $iv);
            $clear = mdecrypt_generic($td, $cipher);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        }
        else {
            @include_once 'des.inc';

            if (function_exists('des')) {
                $des_iv_size = 8;
                $iv = substr($cipher, 0, $des_iv_size);
                $cipher = substr($cipher, $des_iv_size);
                $clear = des($this->config->get_crypto_key($key), $cipher, 0, 1, $iv);
            }
            else {
                self::raise_error(array(
                    'code' => 500, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not perform decryption; make sure Mcrypt is installed or lib/des.inc is available"
                    ), true, true);
            }
        }

        /*-
         * Trim PHP's padding and the canary byte; see note in
         * rcube::encrypt() and http://php.net/mcrypt_generic#68082
         */
        $clear = substr(rtrim($clear, "\0"), 0, -1);

        return $clear;
    }


    /**
     * Generates encryption initialization vector (IV)
     *
     * @param int Vector size
     *
     * @return string Vector string
     */
    private function create_iv($size)
    {
        // mcrypt_create_iv() can be slow when system lacks entrophy
        // we'll generate IV vector manually
        $iv = '';
        for ($i = 0; $i < $size; $i++) {
            $iv .= chr(mt_rand(0, 255));
        }

        return $iv;
    }


    /**
     * Build a valid URL to this instance of Roundcube
     *
     * @param mixed Either a string with the action or url parameters as key-value pairs
     * @return string Valid application URL
     */
    public function url($p)
    {
        // STUB: should be overloaded by the application
        return '';
    }


    /**
     * Function to be executed in script shutdown
     * Registered with register_shutdown_function()
     */
    public function shutdown()
    {
        foreach ($this->shutdown_functions as $function) {
            call_user_func($function);
        }

        if (is_object($this->smtp)) {
            $this->smtp->disconnect();
        }

        foreach ($this->caches as $cache) {
            if (is_object($cache)) {
                $cache->close();
            }
        }

        if (is_object($this->storage)) {
            if ($this->expunge_cache) {
                $this->storage->expunge_cache();
            }
            $this->storage->close();
        }
    }


    /**
     * Registers shutdown function to be executed on shutdown.
     * The functions will be executed before destroying any
     * objects like smtp, imap, session, etc.
     *
     * @param callback Function callback
     */
    public function add_shutdown_function($function)
    {
        $this->shutdown_functions[] = $function;
    }


    /**
     * Quote a given string.
     * Shortcut function for rcube_utils::rep_specialchars_output()
     *
     * @return string HTML-quoted string
     */
    public static function Q($str, $mode = 'strict', $newlines = true)
    {
        return rcube_utils::rep_specialchars_output($str, 'html', $mode, $newlines);
    }


    /**
     * Quote a given string for javascript output.
     * Shortcut function for rcube_utils::rep_specialchars_output()
     *
     * @return string JS-quoted string
     */
    public static function JQ($str)
    {
        return rcube_utils::rep_specialchars_output($str, 'js');
    }


    /**
     * Construct shell command, execute it and return output as string.
     * Keywords {keyword} are replaced with arguments
     *
     * @param $cmd Format string with {keywords} to be replaced
     * @param $values (zero, one or more arrays can be passed)
     *
     * @return output of command. shell errors not detectable
     */
    public static function exec(/* $cmd, $values1 = array(), ... */)
    {
        $args   = func_get_args();
        $cmd    = array_shift($args);
        $values = $replacements = array();

        // merge values into one array
        foreach ($args as $arg) {
            $values += (array)$arg;
        }

        preg_match_all('/({(-?)([a-z]\w*)})/', $cmd, $matches, PREG_SET_ORDER);
        foreach ($matches as $tags) {
            list(, $tag, $option, $key) = $tags;
            $parts = array();

            if ($option) {
                foreach ((array)$values["-$key"] as $key => $value) {
                    if ($value === true || $value === false || $value === null) {
                        $parts[] = $value ? $key : "";
                    }
                    else {
                        foreach ((array)$value as $val) {
                            $parts[] = "$key " . escapeshellarg($val);
                        }
                    }
                }
            }
            else {
                foreach ((array)$values[$key] as $value) {
                    $parts[] = escapeshellarg($value);
                }
            }

            $replacements[$tag] = join(" ", $parts);
        }

        // use strtr behaviour of going through source string once
        $cmd = strtr($cmd, $replacements);

        return (string)shell_exec($cmd);
    }


    /**
     * Print or write debug messages
     *
     * @param mixed Debug message or data
     */
    public static function console()
    {
        $args = func_get_args();

        if (class_exists('rcube', false)) {
            $rcube = self::get_instance();
            $plugin = $rcube->plugins->exec_hook('console', array('args' => $args));
            if ($plugin['abort']) {
                return;
            }
           $args = $plugin['args'];
        }

        $msg = array();
        foreach ($args as $arg) {
            $msg[] = !is_string($arg) ? var_export($arg, true) : $arg;
        }

        self::write_log('console', join(";\n", $msg));
    }


    /**
     * Append a line to a logfile in the logs directory.
     * Date will be added automatically to the line.
     *
     * @param $name name of log file
     * @param line Line to append
     */
    public static function write_log($name, $line)
    {
        if (!is_string($line)) {
            $line = var_export($line, true);
        }

        $date_format = self::$instance ? self::$instance->config->get('log_date_format') : null;
        $log_driver  = self::$instance ? self::$instance->config->get('log_driver') : null;

        if (empty($date_format)) {
            $date_format = 'd-M-Y H:i:s O';
        }

        $date = date($date_format);

        // trigger logging hook
        if (is_object(self::$instance) && is_object(self::$instance->plugins)) {
            $log  = self::$instance->plugins->exec_hook('write_log', array('name' => $name, 'date' => $date, 'line' => $line));
            $name = $log['name'];
            $line = $log['line'];
            $date = $log['date'];
            if ($log['abort'])
                return true;
        }

        if ($log_driver == 'syslog') {
            $prio = $name == 'errors' ? LOG_ERR : LOG_INFO;
            syslog($prio, $line);
            return true;
        }

        // log_driver == 'file' is assumed here

        $line = sprintf("[%s]: %s\n", $date, $line);
        $log_dir  = self::$instance ? self::$instance->config->get('log_dir') : null;

        if (empty($log_dir)) {
            $log_dir = RCUBE_INSTALL_PATH . 'logs';
        }

        // try to open specific log file for writing
        $logfile = $log_dir.'/'.$name;

        if ($fp = @fopen($logfile, 'a')) {
            fwrite($fp, $line);
            fflush($fp);
            fclose($fp);
            return true;
        }

        trigger_error("Error writing to log file $logfile; Please check permissions", E_USER_WARNING);
        return false;
    }


    /**
     * Throw system error (and show error page).
     *
     * @param array Named parameters
     *      - code:    Error code (required)
     *      - type:    Error type [php|db|imap|javascript] (required)
     *      - message: Error message
     *      - file:    File where error occurred
     *      - line:    Line where error occurred
     * @param boolean True to log the error
     * @param boolean Terminate script execution
     */
    public static function raise_error($arg = array(), $log = false, $terminate = false)
    {
        // handle PHP exceptions
        if (is_object($arg) && is_a($arg, 'Exception')) {
            $arg = array(
                'type' => 'php',
                'code' => $arg->getCode(),
                'line' => $arg->getLine(),
                'file' => $arg->getFile(),
                'message' => $arg->getMessage(),
            );
        }
        else if (is_string($arg)) {
            $arg = array('message' => $arg, 'type' => 'php');
        }

        if (empty($arg['code'])) {
            $arg['code'] = 500;
        }

        // installer
        if (class_exists('rcube_install', false)) {
            $rci = rcube_install::get_instance();
            $rci->raise_error($arg);
            return;
        }

        $cli = php_sapi_name() == 'cli';

        if (($log || $terminate) && !$cli && $arg['type'] && $arg['message']) {
            $arg['fatal'] = $terminate;
            self::log_bug($arg);
        }

        // terminate script
        if ($terminate) {
            // display error page
            if (is_object(self::$instance->output)) {
                self::$instance->output->raise_error($arg['code'], $arg['message']);
            }
            else if ($cli) {
                fwrite(STDERR, 'ERROR: ' . $arg['message']);
            }

            exit(1);
        }
    }


    /**
     * Report error according to configured debug_level
     *
     * @param array Named parameters
     * @see self::raise_error()
     */
    public static function log_bug($arg_arr)
    {
        $program = strtoupper($arg_arr['type']);
        $level   = self::get_instance()->config->get('debug_level');

        // disable errors for ajax requests, write to log instead (#1487831)
        if (($level & 4) && !empty($_REQUEST['_remote'])) {
            $level = ($level ^ 4) | 1;
        }

        // write error to local log file
        if (($level & 1) || !empty($arg_arr['fatal'])) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $post_query = '?_task='.urlencode($_POST['_task']).'&_action='.urlencode($_POST['_action']);
            }
            else {
                $post_query = '';
            }

            $log_entry = sprintf("%s Error: %s%s (%s %s)",
                $program,
                $arg_arr['message'],
                $arg_arr['file'] ? sprintf(' in %s on line %d', $arg_arr['file'], $arg_arr['line']) : '',
                $_SERVER['REQUEST_METHOD'],
                $_SERVER['REQUEST_URI'] . $post_query);

            if (!self::write_log('errors', $log_entry)) {
                // send error to PHPs error handler if write_log didn't succeed
                trigger_error($arg_arr['message'], E_USER_WARNING);
            }
        }

        // report the bug to the global bug reporting system
        if ($level & 2) {
            // TODO: Send error via HTTP
        }

        // show error if debug_mode is on
        if ($level & 4) {
            print "<b>$program Error";

            if (!empty($arg_arr['file']) && !empty($arg_arr['line'])) {
                print " in $arg_arr[file] ($arg_arr[line])";
            }

            print ':</b>&nbsp;';
            print nl2br($arg_arr['message']);
            print '<br />';
            flush();
        }
    }


    /**
     * Returns current time (with microseconds).
     *
     * @return float Current time in seconds since the Unix
     */
    public static function timer()
    {
        return microtime(true);
    }


    /**
     * Logs time difference according to provided timer
     *
     * @param float  $timer  Timer (self::timer() result)
     * @param string $label  Log line prefix
     * @param string $dest   Log file name
     *
     * @see self::timer()
     */
    public static function print_timer($timer, $label = 'Timer', $dest = 'console')
    {
        static $print_count = 0;

        $print_count++;
        $now  = self::timer();
        $diff = $now - $timer;

        if (empty($label)) {
            $label = 'Timer '.$print_count;
        }

        self::write_log($dest, sprintf("%s: %0.4f sec", $label, $diff));
    }


    /**
     * Getter for logged user ID.
     *
     * @return mixed User identifier
     */
    public function get_user_id()
    {
        if (is_object($this->user)) {
            return $this->user->ID;
        }
        else if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        return null;
    }


    /**
     * Getter for logged user name.
     *
     * @return string User name
     */
    public function get_user_name()
    {
        if (is_object($this->user)) {
            return $this->user->get_username();
        }
        else if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }
    }


    /**
     * Getter for logged user email (derived from user name not identity).
     *
     * @return string User email address
     */
    public function get_user_email()
    {
        if (is_object($this->user)) {
            return $this->user->get_username('mail');
        }
    }


    /**
     * Getter for logged user password.
     *
     * @return string User password
     */
    public function get_user_password()
    {
        if ($this->password) {
            return $this->password;
        }
        else if ($_SESSION['password']) {
            return $this->decrypt($_SESSION['password']);
        }
    }


    /**
     * Getter for logged user language code.
     *
     * @return string User language code
     */
    public function get_user_language()
    {
        if (is_object($this->user)) {
            return $this->user->language;
        }
        else if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
    }

}


/**
 * Lightweight plugin API class serving as a dummy if plugins are not enabled
 *
 * @package Framework
 * @subpackage Core
 */
class rcube_dummy_plugin_api
{
    /**
     * Triggers a plugin hook.
     * @see rcube_plugin_api::exec_hook()
     */
    public function exec_hook($hook, $args = array())
    {
        return $args;
    }
}
