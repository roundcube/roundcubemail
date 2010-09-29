<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_config.php                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2008-2010, Roundcube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class to read configuration settings                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * Configuration class for Roundcube
 *
 * @package Core
 */
class rcube_config
{
    private $prop = array();
    private $errors = array();
    private $userprefs = array();


    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->load();
    }


    /**
     * Load config from local config file
     *
     * @todo Remove global $CONFIG
     */
    private function load()
    {
        // start output buffering, we don't need any output yet, 
        // it'll be cleared after reading of config files, etc.
        ob_start();
    
        // load main config file
        if (!$this->load_from_file(RCMAIL_CONFIG_DIR . '/main.inc.php'))
            $this->errors[] = 'main.inc.php was not found.';

        // load database config
        if (!$this->load_from_file(RCMAIL_CONFIG_DIR . '/db.inc.php'))
            $this->errors[] = 'db.inc.php was not found.';
    
        // load host-specific configuration
        $this->load_host_config();

        // set skin (with fallback to old 'skin_path' property)
        if (empty($this->prop['skin']) && !empty($this->prop['skin_path']))
            $this->prop['skin'] = str_replace('skins/', '', unslashify($this->prop['skin_path']));
        else if (empty($this->prop['skin']))
            $this->prop['skin'] = 'default';

        // fix paths
        $this->prop['log_dir'] = $this->prop['log_dir'] ? realpath(unslashify($this->prop['log_dir'])) : INSTALL_PATH . 'logs';
        $this->prop['temp_dir'] = $this->prop['temp_dir'] ? realpath(unslashify($this->prop['temp_dir'])) : INSTALL_PATH . 'temp';
    
        // fix default imap folders encoding
        foreach (array('drafts_mbox', 'junk_mbox', 'sent_mbox', 'trash_mbox') as $folder)
            $this->prop[$folder] = rcube_charset_convert($this->prop[$folder], RCMAIL_CHARSET, 'UTF7-IMAP');

        if (!empty($this->prop['default_imap_folders']))
            foreach ($this->prop['default_imap_folders'] as $n => $folder)
                $this->prop['default_imap_folders'][$n] = rcube_charset_convert($folder, RCMAIL_CHARSET, 'UTF7-IMAP');

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
        if ($this->prop['debug_level'] & 4) {
            ini_set('display_errors', 1);
        }
        else {
            ini_set('display_errors', 0);
        }

        // clear output buffer
        ob_end_clean();

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
            $this->load_from_file(RCMAIL_CONFIG_DIR . '/' . $fname);
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
            include($fpath);
            if (is_array($rcmail_config)) {
                $this->prop = array_merge($this->prop, $rcmail_config, $this->userprefs);
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
        return isset($this->prop[$name]) ? $this->prop[$name] : $def;
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
    }


    /**
     * Merge the given prefs over the current config
     * and make sure that they survive further merging.
     *
     * @param array $prefs Hash array with user prefs
     */
    public function set_user_prefs($prefs)
    {
        $this->userprefs = $prefs;
        $this->prop = array_merge($this->prop, $prefs);
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
     * Return requested DES crypto key.
     *
     * @param string $key Crypto key name
     * @return string Crypto key
     */
    public function get_crypto_key($key)
    {
        // Bomb out if the requested key does not exist
        if (!array_key_exists($key, $this->prop)) {
            raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Request for unconfigured crypto key \"$key\""
            ), true, true);
        }

        $key = $this->prop[$key];

        // Bomb out if the configured key is not exactly 24 bytes long
        if (strlen($key) != 24) {
            raise_error(array(
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
                raise_error(array(
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
        else if (!empty($this->prop['mail_domain']))
            $domain = rcube_parse_host($this->prop['mail_domain']);

        if ($encode)
            $domain = idn_to_ascii($domain);

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

}
