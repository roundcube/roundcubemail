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
 |   Class to read configuration settings                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Configuration class for Roundcube
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube_config
{
    const DEFAULT_SKIN = 'elastic';

    /** @var string A skin configured in the config file (before being replaced by a user preference) */
    public $system_skin = 'elastic';

    private $env       = '';
    private $paths     = [];
    private $prop      = [];
    private $errors    = [];
    private $userprefs = [];
    private $immutable = [];
    private $client_tz;


    /**
     * Renamed options
     *
     * @var array
     */
    private $legacy_props = [
        // new name => old name
        'mail_pagesize'        => 'pagesize',
        'addressbook_pagesize' => 'pagesize',
        'reply_mode'           => 'top_posting',
        'refresh_interval'     => 'keep_alive',
        'min_refresh_interval' => 'min_keep_alive',
        'messages_cache_ttl'   => 'message_cache_lifetime',
        'mail_read_time'       => 'preview_pane_mark_read',
        'session_debug'        => 'log_session',
        'redundant_attachments_cache_ttl' => 'redundant_attachments_memcache_ttl',
        'imap_host'            => 'default_host',
        'smtp_host'            => 'smtp_server',
    ];

    /**
     * Object constructor
     *
     * @param string $env Environment suffix for config files to load
     */
    public function __construct($env = '')
    {
        $this->env = $env;

        if ($paths = getenv('RCUBE_CONFIG_PATH')) {
            $this->paths = explode(PATH_SEPARATOR, $paths);
            // make all paths absolute
            foreach ($this->paths as $i => $path) {
                if (!rcube_utils::is_absolute_path($path)) {
                    if ($realpath = realpath(RCUBE_INSTALL_PATH . $path)) {
                        $this->paths[$i] = unslashify($realpath) . '/';
                    }
                    else {
                        unset($this->paths[$i]);
                    }
                }
                else {
                    $this->paths[$i] = unslashify($path) . '/';
                }
            }
        }

        if (defined('RCUBE_CONFIG_DIR') && !in_array(RCUBE_CONFIG_DIR, $this->paths)) {
            $this->paths[] = RCUBE_CONFIG_DIR;
        }

        if (empty($this->paths)) {
            $this->paths[] = RCUBE_INSTALL_PATH . 'config/';
        }

        $this->load();

        // Defaults, that we do not require you to configure,
        // but contain information that is used in various locations in the code:
        if (empty($this->prop['contactlist_fields'])) {
            $this->set('contactlist_fields', ['name', 'firstname', 'surname', 'email']);
        }
    }

    /**
     * Looks inside the string to determine what type might be best as a container.
     *
     * @param string $value The value to inspect
     *
     * @return string The guessed type.
     */
    private function guess_type($value)
    {
        if (preg_match('/^\d+$/', $value)) {
            return 'int';
        }

        if (preg_match('/^[-+]?(\d+(\.\d*)?|\.\d+)([eE][-+]?\d+)?$/', $value)) {
            return 'float';
        }

        if (preg_match('/^(t(rue)?)|(f(alse)?)$/i', $value)) {
            return 'bool';
        }

        // TODO: array/object

        return 'string';
    }

    /**
     * Parse environment variable into PHP type.
     *
     * @param string $string String to parse into PHP type
     * @param string $type   Type of value to return
     *
     * @return mixed Appropriately typed interpretation of $string.
     */
    private function parse_env($string, $type = null)
    {
        switch ($type) {
        case 'bool':
            return (bool) $string;

        case 'int':
            return (int) $string;

        case 'float':
            return (float) $string;

        case 'string':
            return $string;

        case 'array':
            return json_decode($string, true);

        case 'object':
            return json_decode($string, false);
        }

        return $this->parse_env($string, $this->guess_type($string));
    }

    /**
     * Get environment variable value.
     *
     * Retrieve an environment variable's value or if it's not found, return the
     * provided default value.
     *
     * @param string $varname       Environment variable name
     * @param mixed  $default_value Default value to return if necessary
     * @param string $type          Type of value to return
     *
     * @return mixed Value of the environment variable or default if not found.
     */
    private function getenv_default($varname, $default_value, $type = null)
    {
        $value = getenv($varname);

        if ($value === false) {
            $value = $default_value;
        }
        else {
            $value = $this->parse_env($value, $type ?: gettype($default_value));
        }

        return $value;
    }

    /**
     * Load config from local config file
     */
    private function load()
    {
        // Load default settings
        if (!$this->load_from_file('defaults.inc.php')) {
            $this->errors[] = 'defaults.inc.php was not found.';
        }

        // load main config file
        if (!$this->load_from_file('config.inc.php')) {
            // Old configuration files
            if (!$this->load_from_file('main.inc.php') || !$this->load_from_file('db.inc.php')) {
                $this->errors[] = 'config.inc.php was not found.';
            }
            else if (rand(1,100) == 10) {  // log warning on every 100th request (average)
                trigger_error("config.inc.php was not found. Please migrate your config by running bin/update.sh", E_USER_WARNING);
            }
        }

        // load host-specific configuration
        $this->load_host_config();

        // set skin (with fallback to old 'skin_path' property)
        if (empty($this->prop['skin'])) {
            if (!empty($this->prop['skin_path'])) {
                $this->prop['skin'] = str_replace('skins/', '', unslashify($this->prop['skin_path']));
            }
            else {
                $this->prop['skin'] = self::DEFAULT_SKIN;
            }
        }

        if ($this->prop['skin'] == 'default') {
            $this->prop['skin'] = self::DEFAULT_SKIN;
        }

        $this->system_skin = $this->prop['skin'];

        // fix paths
        foreach (['log_dir' => 'logs', 'temp_dir' => 'temp'] as $key => $dir) {
            foreach ([$this->prop[$key], '../' . $this->prop[$key], RCUBE_INSTALL_PATH . $dir] as $path) {
                if ($path && ($realpath = realpath(unslashify($path)))) {
                    $this->prop[$key] = $realpath;
                    break;
                }
            }
        }

        // fix default imap folders encoding
        foreach (['drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox'] as $folder) {
            $this->prop[$folder] = rcube_charset::convert($this->prop[$folder], RCUBE_CHARSET, 'UTF7-IMAP');
        }

        // set PHP error logging according to config
        $error_log = $this->prop['log_driver'] ?: 'file';
        if ($error_log == 'file') {
            $error_log  = $this->prop['log_dir'] . '/errors';
            $error_log .= $this->prop['log_file_ext'] ?? '.log';
        }

        if ($error_log && $error_log != 'stdout') {
            ini_set('error_log', $error_log);
        }

        // set default screen layouts
        $this->prop['supported_layouts'] = ['widescreen', 'desktop', 'list'];

        // remove deprecated properties
        unset($this->prop['dst_active']);
    }

    /**
     * Load a host-specific config file if configured
     * This will merge the host specific configuration with the given one
     */
    private function load_host_config()
    {
        if (empty($this->prop['include_host_config'])) {
            return;
        }

        foreach (['HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR'] as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }

            $fname = null;
            $name  = $_SERVER[$key];

            if (!empty($this->prop['include_host_config']) && is_array($this->prop['include_host_config'])) {
                if (isset($this->prop['include_host_config'][$name])) {
                    $fname = $this->prop['include_host_config'][$name];
                }
            }
            else {
                $fname = preg_replace('/[^a-z0-9\.\-_]/i', '', $name) . '.inc.php';
            }

            if ($fname && $this->load_from_file($fname)) {
                return;
            }
        }
    }

    /**
     * Read configuration from a file
     * and merge with the already stored config values
     *
     * @param string $file Name of the config file to be loaded
     *
     * @return bool True on success, false on failure
     */
    public function load_from_file($file)
    {
        $success = false;

        foreach ($this->resolve_paths($file) as $fpath) {
            if ($fpath && is_file($fpath) && is_readable($fpath)) {
                // use output buffering, we don't need any output here
                ob_start();
                include($fpath);
                ob_end_clean();

                if (isset($config) && is_array($config)) {
                    $this->merge($config);
                    $success = true;
                }
                // deprecated name of config variable
                if (isset($rcmail_config) && is_array($rcmail_config)) {
                    $this->merge($rcmail_config);
                    $success = true;
                }
            }
        }

        return $success;
    }

    /**
     * Helper method to resolve absolute paths to the given config file.
     * This also takes the 'env' property into account.
     *
     * @param string $file    Filename or absolute file path
     * @param bool   $use_env Return -$env file path if exists
     *
     * @return array List of candidates in config dir path(s)
     */
    public function resolve_paths($file, $use_env = true)
    {
        $files    = [];
        $abs_path = rcube_utils::is_absolute_path($file);

        foreach ($this->paths as $basepath) {
            $realpath = $abs_path ? $file : realpath($basepath . '/' . $file);

            // check if <file>-<env>.inc.php exists
            if ($use_env && !empty($this->env)) {
                $envfile = preg_replace('/\.(inc.php)$/', '-' . $this->env . '.\\1', $file);
                $envfile = $abs_path ? $envfile : realpath($basepath . '/' . $envfile);

                if (is_file($envfile)) {
                    $realpath = $envfile;
                }
            }

            if ($realpath) {
                $files[] = $realpath;

                // no need to continue the loop if an absolute file path is given
                if ($abs_path) {
                    break;
                }
            }
        }

        return $files;
    }

    /**
     * Getter for a specific config parameter
     *
     * @param string $name Parameter name
     * @param mixed  $def  Default value if not set
     *
     * @return mixed The requested config value
     */
    public function get($name, $def = null)
    {
        if (isset($this->prop[$name])) {
            $result = $this->prop[$name];
        }
        else {
            $result = $def;
        }

        $result = $this->getenv_default('ROUNDCUBE_' . strtoupper($name), $result);
        $rcube  = rcube::get_instance();

        if ($name == 'timezone') {
            if (empty($result) || $result == 'auto') {
                $result = $this->client_timezone();
            }
        }
        else if ($name == 'client_mimetypes') {
            if (!$result && !$def) {
                $result = 'text/plain,text/html'
                    . ',image/jpeg,image/gif,image/png,image/bmp,image/tiff,image/webp'
                    . ',application/x-javascript,application/pdf,application/x-shockwave-flash';
            }
            if ($result && is_string($result)) {
                $result = explode(',', $result);
            }
        }
        else if ($name == 'layout') {
            if (!in_array($result, $this->prop['supported_layouts'])) {
                $result = $this->prop['supported_layouts'][0];
            }
        }
        else if ($name == 'collected_senders') {
            if (is_bool($result)) {
                $result = $result ? rcube_addressbook::TYPE_TRUSTED_SENDER : '';
            }
            $result = (string) $result;
        }
        else if ($name == 'collected_recipients') {
            if (is_bool($result)) {
                $result = $result ? rcube_addressbook::TYPE_RECIPIENT : '';
            }
            $result = (string) $result;
        }

        $plugin = $rcube->plugins->exec_hook('config_get', [
                'name'    => $name,
                'default' => $def,
                'result'  => $result
        ]);

        return $plugin['result'];
    }

    /**
     * Setter for a config parameter
     *
     * @param string $name      Parameter name
     * @param mixed  $value     Parameter value
     * @param bool   $immutable Make the value immutable
     */
    public function set($name, $value, $immutable = false)
    {
        $this->prop[$name] = $value;

        if ($immutable) {
            $this->immutable[$name] = $value;
        }
    }

    /**
     * Override config options with the given values (e.g. user prefs)
     *
     * @param array $prefs Hash array with config props to merge over
     */
    public function merge($prefs)
    {
        $prefs = $this->fix_legacy_props($prefs);
        $this->prop = array_merge($this->prop, $prefs, $this->userprefs, $this->immutable);
    }

    /**
     * Merge the given prefs over the current config
     * and make sure that they survive further merging.
     *
     * @param array $prefs Hash array with user prefs
     */
    public function set_user_prefs($prefs)
    {
        $prefs = $this->fix_legacy_props($prefs);

        // Honor the dont_override setting for any existing user preferences
        $dont_override = $this->get('dont_override');
        if (is_array($dont_override) && !empty($dont_override)) {
            foreach ($dont_override as $key) {
                unset($prefs[$key]);
            }
        }

        if (isset($prefs['skin']) && $prefs['skin'] == 'default') {
            $prefs['skin'] = $this->system_skin;
        }

        $skins_allowed = $this->get('skins_allowed');

        if (!empty($prefs['skin']) && !empty($skins_allowed) && !in_array($prefs['skin'], (array) $skins_allowed)) {
            unset($prefs['skin']);
        }

        $this->userprefs = $prefs;
        $this->prop      = array_merge($this->prop, $prefs);
    }

    /**
     * Getter for all config options.
     *
     * Unlike get() this method does not resolve any special
     * values like e.g. 'timezone'.
     *
     * It is discouraged to use this method outside of Roundcube core.
     *
     * @return array Hash array containing all config properties
     */
    public function all()
    {
        $props = $this->prop;

        foreach ($props as $prop_name => $prop_value) {
            $props[$prop_name] = $this->getenv_default('ROUNDCUBE_' . strtoupper($prop_name), $prop_value);
        }

        $rcube  = rcube::get_instance();
        $plugin = $rcube->plugins->exec_hook('config_get', ['name' => '*', 'result' => $props]);

        return $plugin['result'];
    }

    /**
     * Some options set as immutable that are also listed
     * in dont_override should not be stored permanently
     * in user preferences. Here's the list of these
     *
     * @return array List of transient options
     */
    public function transient_options()
    {
        return array_intersect(array_keys($this->immutable), (array) $this->get('dont_override'));
    }

    /**
     * Special getter for user's timezone offset including DST
     *
     * @return float Timezone offset (in hours)
     * @deprecated
     */
    public function get_timezone()
    {
        if ($tz = $this->get('timezone')) {
            try {
                $tz = new DateTimeZone($tz);
                return $tz->getOffset(new DateTime('now')) / 3600;
            }
            catch (Exception $e) {
            }
        }

        return 0;
    }

    /**
     * Return requested DES crypto key.
     *
     * @param string $key Crypto key name
     *
     * @return string Crypto key
     */
    public function get_crypto_key($key)
    {
        // Bomb out if the requested key does not exist
        if (!array_key_exists($key, $this->prop) || empty($this->prop[$key])) {
            rcube::raise_error([
                    'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Request for unconfigured crypto key \"$key\""
                ], true, true);
        }

        return $this->prop[$key];
    }

    /**
     * Return configured crypto method.
     *
     * @return string Crypto method
     */
    public function get_crypto_method()
    {
        return $this->get('cipher_method') ?: 'DES-EDE3-CBC';
    }

    /**
     * Try to autodetect operating system and find the correct line endings
     *
     * @return string The appropriate mail header delimiter
     * @deprecated Since 1.3 we don't use mail()
     */
    public function header_delimiter()
    {
        // use the configured delimiter for headers
        if (!empty($this->prop['mail_header_delimiter'])) {
            $delim = $this->prop['mail_header_delimiter'];
            if ($delim == "\n" || $delim == "\r\n") {
                return $delim;
            }
            else {
                rcube::raise_error([
                        'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Invalid mail_header_delimiter setting"
                    ], true, false);
            }
        }

        $php_os = strtolower(substr(PHP_OS, 0, 3));

        if ($php_os == 'win') {
            return "\r\n";
        }

        if ($php_os == 'mac') {
            return "\r\n";
        }

        return "\n";
    }

    /**
     * Returns list of configured PGP key servers
     *
     * @return array|null List of keyservers' URLs
     */
    public function keyservers()
    {
        $list = (array) $this->prop['keyservers'];

        foreach ($list as $idx => $host) {
            if (!preg_match('|^[a-z]+://|', $host)) {
                $host = "https://$host";
            }

            $list[$idx] = slashify($host);
        }

        return !empty($list) ? $list : null;
    }

    /**
     * Return the mail domain configured for the given host
     *
     * @param string $host   IMAP host
     * @param bool   $encode If true, domain name will be converted to IDN ASCII
     *
     * @return string Resolved SMTP host
     */
    public function mail_domain($host, $encode = true)
    {
        $domain = $host;

        if (!empty($this->prop['mail_domain'])) {
            if (is_array($this->prop['mail_domain'])) {
                if (isset($this->prop['mail_domain'][$host])) {
                    $domain = $this->prop['mail_domain'][$host];
                }
            }
            else {
                $domain = rcube_utils::parse_host($this->prop['mail_domain']);
            }
        }

        if ($encode) {
            $domain = rcube_utils::idn_to_ascii($domain);
        }

        return $domain;
    }

    /**
     * Getter for error state
     *
     * @return mixed Error message on error, False if no errors
     */
    public function get_error()
    {
        return empty($this->errors) ? false : implode("\n", $this->errors);
    }

    /**
     * Internal getter for client's (browser) timezone identifier
     */
    private function client_timezone()
    {
        if ($this->client_tz) {
            return $this->client_tz;
        }

        // @TODO: remove this legacy timezone handling in the future
        if (isset($_SESSION['timezone'])) {
            $props = $this->fix_legacy_props(['timezone' => $_SESSION['timezone']]);
        }

        if (!empty($props['timezone'])) {
            // Prevent from using deprecated timezone names
            $props['timezone'] = $this->resolve_timezone_alias($props['timezone']);

            try {
                $tz = new DateTimeZone($props['timezone']);
                return $this->client_tz = $tz->getName();
            }
            catch (Exception $e) { /* gracefully ignore */ }
        }

        // fallback to server's timezone
        return date_default_timezone_get();
    }

    /**
     * Convert legacy options into new ones
     *
     * @param array $props Hash array with config props
     *
     * @return array Converted config props
     */
    private function fix_legacy_props($props)
    {
        foreach ($this->legacy_props as $new => $old) {
            if (isset($props[$old])) {
                if (!isset($props[$new])) {
                    $props[$new] = $props[$old];
                }
                unset($props[$old]);
            }
        }

        // convert deprecated numeric timezone value
        if (isset($props['timezone']) && is_numeric($props['timezone'])) {
            if ($tz = self::timezone_name_from_abbr($props['timezone'])) {
                $props['timezone'] = $tz;
            }
            else {
                unset($props['timezone']);
            }
        }

        // translate old `preview_pane` settings to `layout`
        if (isset($props['preview_pane']) && !isset($props['layout'])) {
            $props['layout'] = $props['preview_pane'] ? 'desktop' : 'list';
            unset($props['preview_pane']);
        }

        // translate old `display_version` settings to `display_product_info`
        if (isset($props['display_version']) && !isset($props['display_product_info'])) {
            $props['display_product_info'] = $props['display_version'] ? 2 : 1;
            unset($props['display_version']);
        }

        return $props;
    }

    /**
     * timezone_name_from_abbr() replacement. Converts timezone offset
     * into timezone name abbreviation.
     *
     * @param float $offset Timezone offset (in hours)
     *
     * @return string|null Timezone abbreviation
     */
    static public function timezone_name_from_abbr($offset)
    {
        // List of timezones here is not complete - https://bugs.php.net/bug.php?id=44780
        if ($tz = timezone_name_from_abbr('', $offset * 3600, 0)) {
            return $tz;
        }

        // try with more complete list (#1489261)
        $timezones = [
            '-660' => "Pacific/Apia",
            '-600' => "Pacific/Honolulu",
            '-570' => "Pacific/Marquesas",
            '-540' => "America/Anchorage",
            '-480' => "America/Los_Angeles",
            '-420' => "America/Denver",
            '-360' => "America/Chicago",
            '-300' => "America/New_York",
            '-270' => "America/Caracas",
            '-240' => "America/Halifax",
            '-210' => "Canada/Newfoundland",
            '-180' => "America/Sao_Paulo",
             '-60' => "Atlantic/Azores",
               '0' => "Europe/London",
              '60' => "Europe/Paris",
             '120' => "Europe/Helsinki",
             '180' => "Europe/Moscow",
             '210' => "Asia/Tehran",
             '240' => "Asia/Dubai",
             '270' => "Asia/Kabul",
             '300' => "Asia/Karachi",
             '330' => "Asia/Kolkata",
             '345' => "Asia/Katmandu",
             '360' => "Asia/Yekaterinburg",
             '390' => "Asia/Rangoon",
             '420' => "Asia/Krasnoyarsk",
             '480' => "Asia/Shanghai",
             '525' => "Australia/Eucla",
             '540' => "Asia/Tokyo",
             '570' => "Australia/Adelaide",
             '600' => "Australia/Melbourne",
             '630' => "Australia/Lord_Howe",
             '660' => "Asia/Vladivostok",
             '690' => "Pacific/Norfolk",
             '720' => "Pacific/Auckland",
             '765' => "Pacific/Chatham",
             '780' => "Pacific/Enderbury",
             '840' => "Pacific/Kiritimati",
        ];

        $key = (string) intval($offset * 60);

        return !empty($timezones[$key]) ? $timezones[$key] : null;
    }

    /**
     * Replace deprecated timezone name with a valid one.
     *
     * @param string $tzname Timezone name
     *
     * @return string Timezone name
     */
    static public function resolve_timezone_alias($tzname)
    {
        // https://www.php.net/manual/en/timezones.others.php
        // https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
        $deprecated_timezones = [
            'Australia/ACT'         => 'Australia/Sydney',
            'Australia/LHI'         => 'Australia/Lord_Howe',
            'Australia/North'       => 'Australia/Darwin',
            'Australia/NSW'         => 'Australia/Sydney',
            'Australia/Queensland'  => 'Australia/Brisbane',
            'Australia/South'       => 'Australia/Adelaide',
            'Australia/Adelaide'    => 'Australia/Hobart',
            'Australia/Tasmania'    => 'Australia/Hobart',
            'Australia/Victoria'    => 'Australia/Melbourne',
            'Australia/West'        => 'Australia/Perth',
            'Brazil/Acre'           => 'America/Rio_Branco',
            'Brazil/DeNoronha'      => 'America/Noronha',
            'Brazil/East'           => 'America/Sao_Paulo',
            'Brazil/West'           => 'America/Manaus',
            'Canada/Atlantic'       => 'America/Halifax',
            'Canada/Central'        => 'America/Winnipeg',
            'Canada/Eastern'        => 'America/Toronto',
            'Canada/Mountain'       => 'America/Edmonton',
            'Canada/Newfoundland'   => 'America/St_Johns',
            'Canada/Pacific'        => 'America/Vancouver',
            'Canada/Saskatchewan'   => 'America/Regina',
            'Canada/Yukon'          => 'America/Whitehorse',
            'CET'                   => 'Europe/Berlin',
            'Chile/Continental'     => 'America/Santiago',
            'Chile/EasterIsland'    => 'Pacific/Easter',
            'CST6CDT'               => 'America/Chicago',
            'Cuba'                  => ' America/Havana',
            'EET'                   => 'Europe/Berlin',
            'Egypt'                 => 'Africa/Cairo',
            'Eire'                  => 'Europe/Dublin',
            'EST'                   => 'America/New_York',
            'EST5EDT'               => 'America/New_York',
            'Factory'               => 'UTC', // ?
            'GB'                    => 'Europe/London',
            'GB-Eire'               => 'Europe/London',
            'GMT'                   => 'UTC',
            'GMT+0'                 => 'UTC',
            'GMT-0'                 => 'UTC',
            'GMT0'                  => 'UTC',
            'Greenwich'             => 'UTC',
            'Hongkong'              => 'Asia/Hong_Kong',
            'HST'                   => 'Pacific/Honolulu',
            'Iceland'               => 'Atlantic/Reykjavik',
            'Iran'                  => 'Asia/Tehran',
            'Israel'                => 'Asia/Jerusalem',
            'Jamaica'               => 'America/Jamaica',
            'Japan'                 => 'Asia/Tokyo',
            'Kwajalein'             => 'Pacific/Kwajalein',
            'Libya'                 => 'Africa/Tripoli',
            'MET'                   => 'Europe/Berlin',
            'Mexico/BajaNorte'      => 'America/Tijuana',
            'Mexico/BajaSur'        => 'America/Mazatlan',
            'Mexico/General'        => 'America/Mexico_City',
            'MST'                   => 'America/Denver',
            'MST7MDT'               => 'America/Denver',
            'Navajo'                => 'America/Denver',
            'NZ'                    => 'Pacific/Auckland',
            'NZ-CHAT'               => 'Pacific/Chatham',
            'Poland'                => 'Europe/Warsaw',
            'Portugal'              => 'Europe/Lisbon',
            'PRC'                   => 'Asia/Shanghai',
            'PST8PDT'               => 'America/Los_Angeles',
            'ROC'                   => 'Asia/Taipei',
            'ROK'                   => 'Asia/Seoul',
            'Singapore'             => 'Asia/Singapore',
            'Turkey'                => 'Europe/Istanbul',
            'UCT'                   => 'UTC',
            'Universal'             => 'UTC',
            'US/Alaska'             => 'America/Anchorage',
            'US/Aleutian'           => 'America/Adak',
            'US/Arizona'            => 'America/Phoenix',
            'US/Central'            => 'America/Chicago',
            'US/East-Indiana'       => 'America/Indiana/Indianapolis',
            'US/Eastern'            => 'America/New_York',
            'US/Hawaii'             => 'Pacific/Honolulu',
            'US/Indiana-Starke'     => 'America/Indiana/Knox',
            'US/Michigan'           => 'America/Detroit',
            'US/Mountain'           => 'America/Denver',
            'US/Pacific'            => 'America/Los_Angeles',
            'US/Pacific-New'        => 'America/Los_Angeles',
            'US/Samoa'              => 'Pacific/Pago_Pago',
            'W-SU'                  => 'Europe/Moscow',
            'WET'                   => 'Europe/Berlin',
            'Z'                     => 'UTC',
            'Zulu'                  => 'UTC',
            // Some of these Etc/X zones are not deprecated, but still problematic
            'Etc/GMT'           => 'UTC',
            'Etc/GMT+0'         => 'UTC',
            'Etc/GMT+1'         => 'Atlantic/Azores',
            'Etc/GMT+10'        => 'Pacific/Honolulu',
            'Etc/GMT+11'        => 'Pacific/Midway',
            'Etc/GMT+12'        => 'Pacific/Auckland',
            'Etc/GMT+2'         => 'America/Noronha',
            'Etc/GMT+3'         => 'America/Argentina/Buenos_Aires',
            'Etc/GMT+4'         => 'America/Manaus',
            'Etc/GMT+5'         => 'America/New_York',
            'Etc/GMT+6'         => 'America/Chicago',
            'Etc/GMT+7'         => 'America/Denver',
            'Etc/GMT+8'         => 'America/Los_Angeles',
            'Etc/GMT+9'         => 'America/Anchorage',
            'Etc/GMT-0'         => 'UTC',
            'Etc/GMT-1'         => 'Europe/Berlin',
            'Etc/GMT-10'        => 'Australia/Sydney',
            'Etc/GMT-11'        => 'Pacific/Norfolk',
            'Etc/GMT-12'        => 'Pacific/Auckland',
            'Etc/GMT-13'        => 'Pacific/Apia',
            'Etc/GMT-14'        => 'Pacific/Kiritimati',
            'Etc/GMT-2'         => 'Africa/Cairo',
            'Etc/GMT-3'         => 'Europe/Moscow',
            'Etc/GMT-4'         => 'Europe/Samara',
            'Etc/GMT-5'         => 'Asia/Yekaterinburg',
            'Etc/GMT-6'         => 'Asia/Almaty',
            'Etc/GMT-7'         => 'Asia/Bangkok',
            'Etc/GMT-8'         => 'Asia/Hong_Kong',
            'Etc/GMT-9'         => 'Asia/Tokyo',
            'Etc/GMT0'          => 'UTC',
            'Etc/Greenwich'     => 'UTC',
            'Etc/UCT'           => 'UTC',
            'Etc/Universal'     => 'UTC',
            'Etc/UTC'           => 'UTC',
            'Etc/Zulu'          => 'UTC',
        ];

        return $deprecated_timezones[$tzname] ?? $tzname;
    }
}
