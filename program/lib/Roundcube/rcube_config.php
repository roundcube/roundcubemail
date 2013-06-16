<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2012, The Roundcube Dev Team                       |
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
    const DEFAULT_SKIN = 'larry';

    private $prop = array();
    private $errors = array();
    private $userprefs = array();

    /**
     * Renamed options
     *
     * @var array
     */
    private $legacy_props = array(
        // new name => old name
        'default_folders'      => 'default_imap_folders',
        'mail_pagesize'        => 'pagesize',
        'addressbook_pagesize' => 'pagesize',
        'reply_mode'           => 'top_posting',
        'refresh_interval'     => 'keep_alive',
        'min_refresh_interval' => 'min_keep_alive',
        'messages_cache_ttl'   => 'message_cache_lifetime',
        'redundant_attachments_cache_ttl' => 'redundant_attachments_memcache_ttl',
    );


    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->load();

        // Defaults, that we do not require you to configure,
        // but contain information that is used in various
        // locations in the code:
        $this->set('contactlist_fields', array('name', 'firstname', 'surname', 'email'));
    }


    /**
     * Load config from local config file
     *
     * @todo Remove global $CONFIG
     */
    private function load()
    {
        // load main config file
        if (!$this->load_from_file(RCUBE_CONFIG_DIR . 'main.inc.php'))
            $this->errors[] = 'main.inc.php was not found.';

        // load database config
        if (!$this->load_from_file(RCUBE_CONFIG_DIR . 'db.inc.php'))
            $this->errors[] = 'db.inc.php was not found.';

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

        // larry is the new default skin :-)
        if ($this->prop['skin'] == 'default')
            $this->prop['skin'] = self::DEFAULT_SKIN;

        // fix paths
        $this->prop['log_dir'] = $this->prop['log_dir'] ? realpath(unslashify($this->prop['log_dir'])) : RCUBE_INSTALL_PATH . 'logs';
        $this->prop['temp_dir'] = $this->prop['temp_dir'] ? realpath(unslashify($this->prop['temp_dir'])) : RCUBE_INSTALL_PATH . 'temp';

        // fix default imap folders encoding
        foreach (array('drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox') as $folder)
            $this->prop[$folder] = rcube_charset::convert($this->prop[$folder], RCUBE_CHARSET, 'UTF7-IMAP');

        if (!empty($this->prop['default_folders']))
            foreach ($this->prop['default_folders'] as $n => $folder)
                $this->prop['default_folders'][$n] = rcube_charset::convert($folder, RCUBE_CHARSET, 'UTF7-IMAP');

        // set PHP error logging according to config
        if ($this->prop['debug_level'] & 1) {
            ini_set('log_errors', 1);

            if ($this->prop['log_driver'] == 'syslog') {
                ini_set('error_log', 'syslog');
            }
            else {
                ini_set('error_log', $this->prop['log_dir'].'/errors');
            }
        }

        // enable display_errors in 'show' level, but not for ajax requests
        ini_set('display_errors', intval(empty($_REQUEST['_remote']) && ($this->prop['debug_level'] & 4)));

        // set timezone auto settings values
        if ($this->prop['timezone'] == 'auto') {
          $this->prop['_timezone_value'] = $this->client_timezone();
        }
        else if (is_numeric($this->prop['timezone']) && ($tz = timezone_name_from_abbr("", $this->prop['timezone'] * 3600, 0))) {
          $this->prop['timezone'] = $tz;
        }
        else if (empty($this->prop['timezone'])) {
          $this->prop['timezone'] = 'UTC';
        }

        // remove deprecated properties
        unset($this->prop['dst_active']);

        // export config data
        $GLOBALS['CONFIG'] = &$this->prop;
    }

    /**
     * Load a host-specific config file if configured
     * This will merge the host specific configuration with the given one
     */
    private function load_host_config()
    {
        $fname = null;

        if (is_array($this->prop['include_host_config'])) {
            $fname = $this->prop['include_host_config'][$_SERVER['HTTP_HOST']];
        }
        else if (!empty($this->prop['include_host_config'])) {
            $fname = preg_replace('/[^a-z0-9\.\-_]/i', '', $_SERVER['HTTP_HOST']) . '.inc.php';
        }

        if ($fname) {
            $this->load_from_file(RCUBE_CONFIG_DIR . $fname);
        }
    }


    /**
     * Read configuration from a file
     * and merge with the already stored config values
     *
     * @param string $fpath Full path to the config file to be loaded
     * @return booelan True on success, false on failure
     */
    public function load_from_file($fpath)
    {
        if (is_file($fpath) && is_readable($fpath)) {
            // use output buffering, we don't need any output here 
            ob_start();
            include($fpath);
            ob_end_clean();

            if (is_array($rcmail_config)) {
                $this->merge($rcmail_config);
                return true;
            }
        }

        return false;
    }


    /**
     * Getter for a specific config parameter
     *
     * @param  string $name Parameter name
     * @param  mixed  $def  Default value if not set
     * @return mixed  The requested config value
     */
    public function get($name, $def = null)
    {
        if (isset($this->prop[$name])) {
            $result = $this->prop[$name];
        }
        else {
            $result = $def;
        }

        $rcube = rcube::get_instance();

        if ($name == 'timezone' && isset($this->prop['_timezone_value'])) {
            $result = $this->prop['_timezone_value'];
        }
        else if ($name == 'client_mimetypes') {
            if ($result == null && $def == null)
                $result = 'text/plain,text/html,text/xml,image/jpeg,image/gif,image/png,image/bmp,image/tiff,application/x-javascript,application/pdf,application/x-shockwave-flash';
            if ($result && is_string($result))
                $result = explode(',', $result);
        }

        $plugin = $rcube->plugins->exec_hook('config_get', array(
            'name' => $name, 'default' => $def, 'result' => $result));

        return $plugin['result'];
    }


    /**
     * Setter for a config parameter
     *
     * @param string $name  Parameter name
     * @param mixed  $value Parameter value
     */
    public function set($name, $value)
    {
        $this->prop[$name] = $value;
    }


    /**
     * Override config options with the given values (eg. user prefs)
     *
     * @param array $prefs Hash array with config props to merge over
     */
    public function merge($prefs)
    {
        $this->prop = array_merge($this->prop, $prefs, $this->userprefs);
        $this->fix_legacy_props();
    }


    /**
     * Merge the given prefs over the current config
     * and make sure that they survive further merging.
     *
     * @param array $prefs Hash array with user prefs
     */
    public function set_user_prefs($prefs)
    {
        // Honor the dont_override setting for any existing user preferences
        $dont_override = $this->get('dont_override');
        if (is_array($dont_override) && !empty($dont_override)) {
            foreach ($dont_override as $key) {
                unset($prefs[$key]);
            }
        }

        // convert user's timezone into the new format
        if (isset($prefs['timezone']) && is_numeric($prefs['timezone']) && ($tz = timezone_name_from_abbr('', $prefs['timezone'] * 3600, 0))) {
            $prefs['timezone'] = $tz;
        }

        // larry is the new default skin :-)
        if (isset($prefs['skin']) && ($prefs['skin'] == 'default')) {
            $prefs['skin'] = self::DEFAULT_SKIN;
        }

        $this->userprefs = $prefs;
        $this->prop      = array_merge($this->prop, $prefs);

        $this->fix_legacy_props();

        // override timezone settings with client values
        if ($this->prop['timezone'] == 'auto') {
            $this->prop['_timezone_value'] = isset($_SESSION['timezone']) ? $this->client_timezone() : $this->prop['_timezone_value'];
        }
        else if (isset($this->prop['_timezone_value']))
           unset($this->prop['_timezone_value']);
    }


    /**
     * Getter for all config options
     *
     * @return array  Hash array containg all config properties
     */
    public function all()
    {
        return $this->prop;
    }

    /**
     * Special getter for user's timezone offset including DST
     *
     * @return float  Timezone offset (in hours)
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
     * @return string Crypto key
     */
    public function get_crypto_key($key)
    {
        // Bomb out if the requested key does not exist
        if (!array_key_exists($key, $this->prop)) {
            rcube::raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Request for unconfigured crypto key \"$key\""
            ), true, true);
        }

        $key = $this->prop[$key];

        // Bomb out if the configured key is not exactly 24 bytes long
        if (strlen($key) != 24) {
            rcube::raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Configured crypto key '$key' is not exactly 24 bytes long"
            ), true, true);
        }

        return $key;
    }


    /**
     * Try to autodetect operating system and find the correct line endings
     *
     * @return string The appropriate mail header delimiter
     */
    public function header_delimiter()
    {
        // use the configured delimiter for headers
        if (!empty($this->prop['mail_header_delimiter'])) {
            $delim = $this->prop['mail_header_delimiter'];
            if ($delim == "\n" || $delim == "\r\n")
                return $delim;
            else
                rcube::raise_error(array(
                    'code' => 500, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Invalid mail_header_delimiter setting"
                ), true, false);
        }

        $php_os = strtolower(substr(PHP_OS, 0, 3));

        if ($php_os == 'win')
            return "\r\n";

        if ($php_os == 'mac')
            return "\r\n";

        return "\n";
    }


    /**
     * Return the mail domain configured for the given host
     *
     * @param string  $host   IMAP host
     * @param boolean $encode If true, domain name will be converted to IDN ASCII
     * @return string Resolved SMTP host
     */
    public function mail_domain($host, $encode=true)
    {
        $domain = $host;

        if (is_array($this->prop['mail_domain'])) {
            if (isset($this->prop['mail_domain'][$host]))
                $domain = $this->prop['mail_domain'][$host];
        }
        else if (!empty($this->prop['mail_domain'])) {
            $domain = rcube_utils::parse_host($this->prop['mail_domain']);
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
        return empty($this->errors) ? false : join("\n", $this->errors);
    }


    /**
     * Internal getter for client's (browser) timezone identifier
     */
    private function client_timezone()
    {
        if (isset($_SESSION['timezone']) && is_numeric($_SESSION['timezone'])
              && ($ctz = timezone_name_from_abbr("", $_SESSION['timezone'] * 3600, 0))) {
            return $ctz;
        }
        else if (!empty($_SESSION['timezone'])) {
            try {
                $tz = timezone_open($_SESSION['timezone']);
                return $tz->getName();
            }
            catch (Exception $e) { /* gracefully ignore */ }
        }

        // fallback to server's timezone
        return date_default_timezone_get();
    }

    /**
     * Convert legacy options into new ones
     */
    private function fix_legacy_props()
    {
        foreach ($this->legacy_props as $new => $old) {
            if (isset($this->prop[$old])) {
                if (!isset($this->prop[$new])) {
                    $this->prop[$new] = $this->prop[$old];
                }
                unset($this->prop[$old]);
            }
        }
    }
}
