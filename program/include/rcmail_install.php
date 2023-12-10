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
 |   Roundcube Installer                                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to control the installation process of the Roundcube Webmail package
 *
 * @category Install
 * @package  Webmail
 */
class rcmail_install
{
    public $step;
    public $last_error;
    public $is_post           = false;
    public $failures          = 0;
    public $config            = [];
    public $defaults          = [];
    public $comments          = [];
    public $configured        = false;
    public $legacy_config     = false;
    public $email_pattern     = '([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9])';

    public $bool_config_props = ['ip_check', 'enable_spellcheck', 'auto_create_user', 'smtp_log', 'prefer_html'];
    public $local_config    = ['db_dsnw', 'imap_host', 'support_url', 'des_key', 'plugins'];
    public $obsolete_config = ['db_backend', 'db_max_length', 'double_auth', 'preview_pane', 'debug_level', 'referer_check'];
    public $replaced_config = [
        'skin_path'            => 'skin',
        'locale_string'        => 'language',
        'multiple_identities'  => 'identities_level',
        'addrbook_show_images' => 'show_images',
        'imap_root'            => 'imap_ns_personal',
        'pagesize'             => 'mail_pagesize',
        'top_posting'          => 'reply_mode',
        'keep_alive'           => 'refresh_interval',
        'min_keep_alive'       => 'min_refresh_interval',
        'default_host'         => 'imap_host',
        'smtp_server'          => 'smtp_host',
    ];

    // List of configuration options supported by the Installer
    public $supported_config = [
        'product_name', 'support_url', 'temp_dir', 'des_key', 'ip_check', 'enable_spellcheck',
        'spellcheck_engine', 'identities_level', 'log_driver', 'log_dir', 'syslog_id',
        'syslog_facility', 'db_dsnw', 'db_prefix', 'imap_host', 'username_domain',
        'auto_create_user', 'sent_mbox', 'trash_mbox', 'drafts_mbox', 'junk_mbox',
        'smtp_host', 'smtp_user', 'smtp_pass', 'smtp_log', 'language', 'skin', 'mail_pagesize',
        'addressbook_pagesize', 'prefer_html', 'htmleditor', 'draft_autosave', 'mdn_requests',
        'mime_param_folding', 'plugins',
    ];

    // list of supported database drivers
    public $supported_dbs = [
        'MySQL'               => 'pdo_mysql',
        'PostgreSQL'          => 'pdo_pgsql',
        'SQLite'              => 'pdo_sqlite',
        'SQLite (v2)'         => 'pdo_sqlite2',
        'SQL Server (SQLSRV)' => 'pdo_sqlsrv',
        'SQL Server (DBLIB)'  => 'pdo_dblib',
        'Oracle'              => 'oci8',
    ];

    /** @var array List of config options with default value change per-release */
    public $defaults_changes = [
        '1.4.0' => ['skin', 'smtp_port', 'smtp_user', 'smtp_pass'],
        '1.4.1' => ['jquery_ui_skin_map'],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->step    = isset($_REQUEST['_step']) ? intval($_REQUEST['_step']) : 0;
        $this->is_post = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Singleton getter
     */
    public static function get_instance()
    {
        static $inst;

        if (!$inst) {
            $inst = new rcmail_install();
        }

        return $inst;
    }

    /**
     * Read the local config files and store properties
     */
    public function load_config()
    {
        if ($this->configured) {
            return;
        }

        // defaults
        if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'defaults.inc.php')) {
            $this->config   = (array) $config;
            $this->defaults = $this->config;
        }

        $config = null;

        // config
        if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'config.inc.php')) {
            $this->config = array_merge($this->config, $config);
        }
        else {
            if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'main.inc.php')) {
                $this->config        = array_merge($this->config, $config);
                $this->legacy_config = true;
            }

            if ($config = $this->load_config_file(RCUBE_CONFIG_DIR . 'db.inc.php')) {
                $this->config        = array_merge($this->config, $config);
                $this->legacy_config = true;
            }
        }

        $this->configured = !empty($config);
    }

    /**
     * Read the default config file and store properties
     *
     * @param string $file File name with path
     */
    public function load_config_file($file)
    {
        if (!is_readable($file)) {
            return;
        }

        $config        = [];
        $rcmail_config = []; // deprecated var name

        include $file;

        // read comments from config file
        if (function_exists('token_get_all')) {
            $tokens    = token_get_all(file_get_contents($file));
            $in_config = false;
            $buffer    = '';

            for ($i = 0; $i < count($tokens); $i++) {
                $token = $tokens[$i];
                if ($token[0] == T_VARIABLE && ($token[1] == '$config' || $token[1] == '$rcmail_config')) {
                    $in_config = true;
                    if ($buffer && $tokens[$i+1] == '[' && $tokens[$i+2][0] == T_CONSTANT_ENCAPSED_STRING) {
                        $propname = trim($tokens[$i+2][1], "'\"");
                        $this->comments[$propname] = preg_replace('/\n\n/', "\n", $buffer);
                        $buffer = '';
                        $i += 3;
                    }
                }
                else if ($in_config && $token[0] == T_COMMENT) {
                    $buffer .= strtr($token[1], ['\n' => "\n"]) . "\n";
                }
            }
        }

        return array_merge((array) $rcmail_config, (array) $config);
    }

    /**
     * Getter for a certain config property
     *
     * @param string $name    Property name
     * @param string $default Default value
     *
     * @return mixed The property value
     */
    public function getprop($name, $default = '')
    {
        $value = $this->config[$name] ?? null;

        if ($name == 'des_key' && !$this->configured && !isset($_REQUEST["_$name"])) {
            $value = rcube_utils::random_bytes(24);
        }

        return $value !== null && $value !== '' ? $value : $default;
    }

    /**
     * Create configuration file that contains parameters
     * that differ from default values.
     *
     * @param bool $use_post Use POSTed configuration values (of supported options)
     *
     * @return string The complete config file content
     */
    public function create_config($use_post = true)
    {
        $config = [];

        foreach ($this->config as $prop => $default) {
            $post_value = $_POST["_$prop"] ?? null;
            $value      = $default;

            if ($use_post && in_array($prop, $this->supported_config)
                && ($post_value !== null || in_array($prop, $this->bool_config_props))
            ) {
                $value = $post_value;
            }

            // always disable installer
            if ($prop == 'enable_installer') {
                $value = false;
            }

            // generate new encryption key, never use the default value
            if ($prop == 'des_key' && $value == $this->defaults[$prop]) {
                $value = rcube_utils::random_bytes(24);
            }

            // convert some form data
            if ($prop == 'db_dsnw' && !empty($_POST['_dbtype'])) {
                if ($_POST['_dbtype'] == 'sqlite') {
                    $value = sprintf('%s://%s?mode=0646', $_POST['_dbtype'],
                        $_POST['_dbname'][0] == '/' ? '/' . $_POST['_dbname'] : $_POST['_dbname']);
                }
                else if ($_POST['_dbtype']) {
                    $value = sprintf('%s://%s:%s@%s/%s', $_POST['_dbtype'],
                        rawurlencode($_POST['_dbuser']), rawurlencode($_POST['_dbpass']), $_POST['_dbhost'], $_POST['_dbname']);
                }
            }
            else if ($prop == 'imap_host' && is_array($value)) {
                $value = self::_clean_array($value);
                if (count($value) <= 1) {
                    $value = $value[0];
                }
            }
            else if ($prop == 'mail_pagesize' || $prop == 'addressbook_pagesize') {
                $value = max(2, intval($value));
            }
            else if ($prop == 'smtp_user' && !empty($_POST['_smtp_user_u'])) {
                $value = '%u';
            }
            else if ($prop == 'smtp_pass' && !empty($_POST['_smtp_user_u'])) {
                $value = '%p';
            }
            else if (is_bool($default)) {
                $value = (bool) $value;
            }
            else if (is_numeric($value)) {
                $value = intval($value);
            }
            else if ($prop == 'plugins' && !empty($_POST['submit'])) {
                $value = [];
                foreach (array_keys($_POST) as $key) {
                    if (preg_match('/^_plugins_*/', $key)) {
                        array_push($value, $_POST[$key]);
                    }
                }
            }

            // skip this property
            if ($value == ($this->defaults[$prop] ?? null)
                && (!in_array($prop, $this->local_config)
                    || in_array($prop, array_merge($this->obsolete_config, array_keys($this->replaced_config)))
                    || preg_match('/^db_(table|sequence)_/', $prop)
                )
            ) {
                continue;
            }

            // save change
            $this->config[$prop] = $value;
            $config[$prop]       = $value;
        }

        $out = "<?php\n\n";
        $out .= "/* Local configuration for Roundcube Webmail */\n\n";

        foreach ($config as $prop => $value) {
            // copy option descriptions from existing config or defaults.inc.php
            $out .= $this->comments[$prop] ?? '';
            $out .= "\$config['$prop'] = " . self::_dump_var($value, $prop) . ";\n\n";
        }

        return $out;
    }

    /**
     * save generated config file in RCUBE_CONFIG_DIR
     *
     * @return bool True if the file was saved successfully, false if not
     */
    public function save_configfile($config)
    {
        if (is_writable(RCUBE_CONFIG_DIR)) {
            return file_put_contents(RCUBE_CONFIG_DIR . 'config.inc.php', $config);
        }

        return false;
    }

    /**
     * Check the current configuration for missing properties
     * and deprecated or obsolete settings
     *
     * @param string $version Previous version on upgrade
     *
     * @return array List with problems detected
     */
    public function check_config($version = null)
    {
        $this->load_config();

        if (!$this->configured) {
            return;
        }

        $seen = [];
        $out = ['defaults' => [], 'obsolete' => [], 'replaced' => [], 'dependencies' => [], 'missing' => []];

        // iterate over the current configuration
        foreach (array_keys($this->config) as $prop) {
            if (!empty($this->replaced_config[$prop])) {
                $replacement = $this->replaced_config[$prop];
                $out['replaced'][]  = ['prop' => $prop, 'replacement' => $replacement];
                $seen[$replacement] = true;
            }
            else if (empty($seen[$prop]) && in_array($prop, $this->obsolete_config)) {
                $out['obsolete'][] = ['prop' => $prop];
                $seen[$prop]       = true;
            }
        }

        // the old default mime_magic reference is obsolete
        if ($this->config['mime_magic'] == '/usr/share/misc/magic') {
            $out['obsolete'][] = [
                'prop'    => 'mime_magic',
                'explain' => "Set value to null in order to use system default"
            ];
        }

        // check config dependencies and contradictions
        if (!empty($this->config['enable_spellcheck']) && $this->config['spellcheck_engine'] == 'pspell') {
            if (!extension_loaded('pspell')) {
                $out['dependencies'][] = [
                    'prop'    => 'spellcheck_engine',
                    'explain' => "This requires the <tt>pspell</tt> extension which could not be loaded."
                ];
            }
            else if (!empty($this->config['spellcheck_languages'])) {
                foreach ($this->config['spellcheck_languages'] as $lang => $descr) {
                    if (!@pspell_new($lang)) {
                        $out['dependencies'][] = [
                            'prop'    => 'spellcheck_languages',
                            'explain' => "You are missing pspell support for language $lang ($descr)"
                        ];
                    }
                }
            }
        }

        if ($this->config['log_driver'] == 'syslog') {
            if (!function_exists('openlog')) {
                $out['dependencies'][] = [
                    'prop'    => 'log_driver',
                    'explain' => "This requires the <tt>syslog</tt> extension which could not be loaded."
                ];
            }

            if (empty($this->config['syslog_id'])) {
                $out['dependencies'][] = [
                    'prop'    => 'syslog_id',
                    'explain' => "Using <tt>syslog</tt> for logging requires a syslog ID to be configured"
                ];
            }
        }

        // check ldap_public sources having global_search enabled
        if (is_array($this->config['ldap_public']) && !is_array($this->config['autocomplete_addressbooks'])) {
            foreach ($this->config['ldap_public'] as $ldap_public) {
                if ($ldap_public['global_search']) {
                    $out['replaced'][] = [
                        'prop'        => 'ldap_public::global_search',
                        'replacement' => 'autocomplete_addressbooks'
                    ];
                    break;
                }
            }
        }

        if ($version) {
            foreach ($this->defaults_changes as $v => $opts) {
                if (version_compare($v, $version, '>')) {
                    $out['defaults'] = array_merge($out['defaults'], $opts);
                }
            }

            $out['defaults'] = array_unique($out['defaults']);
        }

        return $out;
    }

    /**
     * Merge the current configuration with the defaults
     * and copy replaced values to the new options.
     */
    public function merge_config()
    {
        $current      = $this->config;
        $this->config = [];

        foreach ($this->replaced_config as $prop => $replacement) {
            if (isset($current[$prop])) {
                if ($prop == 'skin_path') {
                    $this->config[$replacement] = preg_replace('#skins/(\w+)/?$#', '\\1', $current[$prop]);
                }
                else if ($prop == 'multiple_identities') {
                    $this->config[$replacement] = $current[$prop] ? 2 : 0;
                }
                else {
                    $this->config[$replacement] = $current[$prop];
                }

                unset($current[$prop]);
                unset($current[$replacement]);
            }
        }

        // Merge old *_port options into the new *_host options, where possible
        foreach (['default' => 'imap', 'smtp' => 'smtp'] as $prop => $type) {
            $old_prop = "{$prop}_port";
            $new_prop = "{$type}_host";
            if (!empty($current[$old_prop]) && !empty($this->config[$new_prop])
                && is_string($this->config[$new_prop])
                && !preg_match('/:[0-9]+$/', $this->config[$new_prop])
            ) {
                $this->config[$new_prop] .= ':' . $current[$old_prop];
            }

            unset($current[$old_prop]);
        }

        foreach ($this->obsolete_config as $prop) {
            unset($current[$prop]);
        }

        // add all ldap_public sources having global_search enabled to autocomplete_addressbooks
        if (!empty($current['ldap_public']) && is_array($current['ldap_public'])) {
            foreach ($current['ldap_public'] as $key => $ldap_public) {
                if (!empty($ldap_public['global_search'])) {
                    $this->config['autocomplete_addressbooks'][] = $key;
                    unset($current['ldap_public'][$key]['global_search']);
                }
            }
        }

        $this->config = array_merge($this->config, $current);
    }

    /**
     * Compare the local database schema with the reference schema
     * required for this version of Roundcube
     *
     * @param rcube_db $db Database object
     *
     * @return bool True if the schema is up-to-date, false if not or an error occurred
     */
    public function db_schema_check($db)
    {
        if (!$this->configured) {
            return false;
        }

        // read reference schema from mysql.initial.sql
        $engine    = $db->db_provider;
        $db_schema = $this->db_read_schema(INSTALL_PATH . "SQL/$engine.initial.sql", $schema_version);
        $errors    = [];

        // Just check the version
        if ($schema_version) {
            $version = rcmail_utils::db_version();

            if (empty($version)) {
                $errors[] = "Schema version not found";
            }
            else if ($schema_version != $version) {
                $errors[] = "Schema version: {$version} (required: {$schema_version})";
            }

            return !empty($errors) ? $errors : false;
        }

        // check list of tables
        $existing_tables = $db->list_tables();

        foreach ($db_schema as $table => $cols) {
            $table = $this->config['db_prefix'] . $table;

            if (!in_array($table, $existing_tables)) {
                $errors[] = "Missing table '".$table."'";
            }
            else {  // compare cols
                $db_cols = $db->list_cols($table);
                $diff    = array_diff(array_keys($cols), $db_cols);

                if (!empty($diff)) {
                    $errors[] = "Missing columns in table '$table': " . implode(',', $diff);
                }
            }
        }

        return !empty($errors) ? $errors : false;
    }

    /**
     * Utility function to read database schema from an .sql file
     */
    private function db_read_schema($schemafile, &$version = null)
    {
        $lines      = file($schemafile);
        $schema     = [];
        $keywords   = ['PRIMARY','KEY','INDEX','UNIQUE','CONSTRAINT','REFERENCES','FOREIGN'];
        $table_name = null;

        foreach ($lines as $line) {
            if (preg_match('/^\s*create table ([\S]+)/i', $line, $m)) {
                $table_name = explode('.', $m[1]);
                $table_name = end($table_name);
                $table_name = preg_replace('/[`"\[\]]/', '', $table_name);
            }
            else if (preg_match('/insert into/i', $line) && preg_match('/\'roundcube-version\',\s*\'([0-9]+)\'/', $line, $m)) {
                $version = $m[1];
            }
            else if ($table_name && ($line = trim($line))) {
                if ($line == 'GO' || $line[0] == ')' || $line[strlen($line)-1] == ';') {
                    $table_name = null;
                }
                else {
                    $items = explode(' ', $line);
                    $col   = $items[0];
                    $col   = preg_replace('/[`"\[\]]/', '', $col);

                    if (!in_array(strtoupper($col), $keywords)) {
                        $type = strtolower($items[1]);
                        $type = preg_replace('/[^a-zA-Z0-9()]/', '', $type);

                        $schema[$table_name][$col] = $type;
                    }
                }
            }
        }

        return $schema;
    }

    /**
     * Try to detect some file's mimetypes to test the correct behavior of fileinfo
     */
    public function check_mime_detection()
    {
        $errors = [];
        $files  = [
            'program/resources/tinymce/video.png' => 'image/png',
            'program/resources/blank.tiff'        => 'image/tiff',
            'program/resources/blocked.gif'       => 'image/gif',
        ];

        foreach ($files as $path => $expected) {
            $mimetype = rcube_mime::file_content_type(INSTALL_PATH . $path, basename($path));
            if ($mimetype != $expected) {
                $errors[] = [$path, $mimetype, $expected];
            }
        }

        return $errors;
    }

    /**
     * Check the correct configuration of the 'mime_types' mapping option
     */
    public function check_mime_extensions()
    {
        $errors = [];
        $types  = [
            'application/zip'   => 'zip',
            'text/css'          => 'css',
            'application/pdf'   => 'pdf',
            'image/gif'         => 'gif',
            'image/svg+xml'     => 'svg',
        ];

        foreach ($types as $mimetype => $expected) {
            $ext = rcube_mime::get_mime_extensions($mimetype);
            if (!in_array($expected, (array) $ext)) {
                $errors[] = [$mimetype, $ext, $expected];
            }
        }

        return $errors;
    }

    /**
     * Getter for the last error message
     *
     * @return string Error message or null if none exists
     */
    public function get_error()
    {
        return $this->last_error['message'] ?? null;
    }

    /**
     * Return a list with all imap/smtp hosts configured
     *
     * @return array Clean list with imap/smtp hosts
     */
    public function get_hostlist($prop = 'imap_host')
    {
        $hosts     = (array) $this->getprop($prop);
        $out       = [];
        $imap_host = '';

        if ($prop == 'smtp_host') {
            // Set the imap host name for the %h macro
            $default_hosts = $this->get_hostlist();
            $imap_host = !empty($default_hosts) ? $default_hosts[0] : '';
        }

        foreach ($hosts as $key => $name) {
            if (!empty($name)) {
                if ($prop == 'smtp_host') {
                    // SMTP host array uses `IMAP host => SMTP host` format
                    $host = $name;
                }
                else {
                    $host = is_numeric($key) ? $name : $key;
                }

                $out[] = rcube_utils::parse_host($host, $imap_host);
            }
        }

        return $out;
    }

    /**
     * Create a HTML dropdown to select a previous version of Roundcube
     */
    public function versions_select($attrib = [])
    {
        $select = new html_select($attrib);
        $select->add([
                '0.1-stable', '0.1.1',
                '0.2-alpha', '0.2-beta', '0.2-stable',
                '0.3-stable', '0.3.1',
                '0.4-beta', '0.4.2',
                '0.5-beta', '0.5', '0.5.1', '0.5.2', '0.5.3', '0.5.4',
                '0.6-beta', '0.6',
                '0.7-beta', '0.7', '0.7.1', '0.7.2', '0.7.3', '0.7.4',
                '0.8-beta', '0.8-rc', '0.8.0', '0.8.1', '0.8.2', '0.8.3', '0.8.4', '0.8.5', '0.8.6',
                '0.9-beta', '0.9-rc', '0.9-rc2',
                // Note: Do not add newer versions here
        ]);

        return $select;
    }

    /**
     * Return a list with available subfolders of the skin directory
     *
     * @return array List of available skins
     */
    public function list_skins()
    {
        $skins   = [];
        $skindir = INSTALL_PATH . 'skins/';

        foreach (glob($skindir . '*') as $path) {
            if (is_dir($path) && is_readable($path)) {
                $skins[] = substr($path, strlen($skindir));
            }
        }

        return $skins;
    }

    /**
     * Return a list with available subfolders of the plugins directory
     * (with their associated description in composer.json)
     *
     * @return array List of available plugins
     */
    public function list_plugins()
    {
        $plugins    = [];
        $plugin_dir = INSTALL_PATH . 'plugins/';
        $enabled    = isset($this->config['plugins']) ? (array) $this->config['plugins'] : [];

        foreach (glob($plugin_dir . '*') as $path) {
            if (!is_dir($path)) {
                continue;
            }

            if (is_readable($path . '/composer.json')) {
                $file_json   = json_decode(file_get_contents($path . '/composer.json'));
                $plugin_desc = $file_json->description ?: 'N/A';
            }
            else {
                $plugin_desc = 'N/A';
            }

            $name      = substr($path, strlen($plugin_dir));
            $plugins[] = [
                'name'    => $name,
                'desc'    => $plugin_desc,
                'enabled' => in_array($name, $enabled)
            ];
        }

        return $plugins;
    }

    /**
     * Display OK status
     *
     * @param string $name    Test name
     * @param string $message Confirm message
     */
    public function pass($name, $message = '')
    {
        echo rcube::Q($name) . ':&nbsp; <span class="success">OK</span>';
        $this->_showhint($message);
    }

    /**
     * Display an error status and increase failure count
     *
     * @param string $name     Test name
     * @param string $message  Error message
     * @param string $url      URL for details
     * @param bool   $optional Do not count this failure
     */
    public function fail($name, $message = '', $url = '', $optional = false)
    {
        if (!$optional) {
            $this->failures++;
        }

        echo rcube::Q($name) . ':&nbsp; <span class="fail">NOT OK</span>';
        $this->_showhint($message, $url);
    }

    /**
     * Display an error status for optional settings/features
     *
     * @param string $name    Test name
     * @param string $message Error message
     * @param string $url     URL for details
     */
    public function optfail($name, $message = '', $url = '')
    {
        echo rcube::Q($name) . ':&nbsp; <span class="na">NOT OK</span>';
        $this->_showhint($message, $url);
    }

    /**
     * Display warning status
     *
     * @param string $name    Test name
     * @param string $message Warning message
     * @param string $url     URL for details
     */
    public function na($name, $message = '', $url = '')
    {
        echo rcube::Q($name) . ':&nbsp; <span class="na">NOT AVAILABLE</span>';
        $this->_showhint($message, $url);
    }

    private function _showhint($message, $url = '')
    {
        $hint = rcube::Q($message);

        if ($url) {
            $hint .= ($hint ? '; ' : '') . 'See <a href="' . rcube::Q($url) . '" target="_blank">' . rcube::Q($url) . '</a>';
        }

        if ($hint) {
            echo '<span class="indent">(' . $hint . ')</span>';
        }
    }

    private static function _clean_array($arr)
    {
        $out = [];

        foreach (array_unique($arr) as $k => $val) {
            if (!empty($val)) {
                if (is_numeric($k)) {
                    $out[] = $val;
                }
                else {
                    $out[$k] = $val;
                }
            }
        }

        return $out;
    }

    private static function _dump_var($var, $name = null)
    {
        // special values
        switch ($name) {
        case 'syslog_facility':
            $list = [
                32 => 'LOG_AUTH', 80 => 'LOG_AUTHPRIV', 72 => ' LOG_CRON',
                24 => 'LOG_DAEMON', 0 => 'LOG_KERN', 128 => 'LOG_LOCAL0',
                136 => 'LOG_LOCAL1', 144 => 'LOG_LOCAL2', 152 => 'LOG_LOCAL3',
                160 => 'LOG_LOCAL4', 168 => 'LOG_LOCAL5', 176 => 'LOG_LOCAL6',
                184 => 'LOG_LOCAL7', 48 => 'LOG_LPR', 16 => 'LOG_MAIL',
                56 => 'LOG_NEWS', 40 => 'LOG_SYSLOG', 8 => 'LOG_USER', 64 => 'LOG_UUCP'
            ];

            if (!empty($list[$var])) {
                return $list[$var];
            }

            break;
        }

        if (is_array($var)) {
            if (empty($var)) {
                return '[]';
            }
            // check if all keys are numeric
            $isnum = true;
            foreach (array_keys($var) as $key) {
                if (!is_numeric($key)) {
                    $isnum = false;
                    break;
                }
            }

            if ($isnum) {
                return '[' . implode(', ', array_map(['rcmail_install', '_dump_var'], $var)) . ']';
            }
        }

        return var_export($var, true);
    }

    /**
     * Initialize the database with the according schema
     *
     * @param rcube_db $db Database connection
     *
     * @return bool True on success, False on error
     */
    public function init_db($db)
    {
        $engine = $db->db_provider;

        // read schema file from /SQL/*
        $fname = INSTALL_PATH . "SQL/$engine.initial.sql";
        if ($sql = @file_get_contents($fname)) {
            $db->set_option('table_prefix', $this->config['db_prefix']);
            $db->exec_script($sql);
        }
        else {
            $this->fail('DB Schema', "Cannot read the schema file: $fname");
            return false;
        }

        if ($err = $this->get_error()) {
            $this->fail('DB Schema', "Error creating database schema: $err");
            return false;
        }

        return true;
    }

    /**
     * Update database schema
     *
     * @param string $version Version to update from
     *
     * @return boolean True on success, False on error
     */
    public function update_db($version)
    {
        return rcmail_utils::db_update(INSTALL_PATH . 'SQL', 'roundcube', $version, ['quiet' => true]);
    }

    /**
     * Handler for Roundcube errors
     */
    public function raise_error($p)
    {
        $this->last_error = $p;
    }

    /**
     * Check if vendor/autoload.php was created by Roundcube and left untouched
     *
     * @param string $target_dir The target installation dir
     * @return string
     */
    public static function vendor_dir_untouched($target_dir)
    {
        system('grep -q "generated by Roundcube" ' . escapeshellarg($target_dir . '/vendor/autoload.php') . ' 2>/dev/null', $exit_code);
        return $exit_code === 0;
    }
}
