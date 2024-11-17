<?php

/*
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
 |   Mail Storage Engine                                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Abstract class for accessing mail messages storage server
 */
abstract class rcube_storage
{
    public const UNKNOWN = 0;
    public const NOPERM = 1;
    public const READONLY = 2;
    public const TRYCREATE = 3;
    public const INUSE = 4;
    public const OVERQUOTA = 5;
    public const ALREADYEXISTS = 6;
    public const NONEXISTENT = 7;
    public const CONTACTADMIN = 8;

    public const DUAL_USE_FOLDERS = 'X-DUAL-USE-FOLDERS';

    /** @var array List of supported special folder types */
    public static $folder_types = ['drafts', 'sent', 'junk', 'trash'];

    /** @var string Current folder */
    protected $folder = '';

    /** @var string Default character set name */
    protected $default_charset = RCUBE_CHARSET;

    /** @var array Object configuration options */
    protected $options = [
        'auth_type' => 'check',
        'language' => 'en_US',
        'skip_deleted' => false,
    ];

    /** @var int Page size */
    protected $page_size = 10;

    /** @var int Current page */
    protected $list_page = 1;

    /** @var bool Enabled/Disable threading mode */
    protected $threading = false;

    /** @var rcube_result_index|rcube_result_multifolder|rcube_result_thread|null Search result set */
    protected $search_set;

    /** @var array Internal (in-memory) cache */
    protected $icache = [];

    /**
     * All (additional) headers used (in any way) by Roundcube
     * Not listed here: DATE, FROM, TO, CC, REPLY-TO, SUBJECT, CONTENT-TYPE, LIST-POST
     * (used for messages listing) are hardcoded in rcube_imap_generic::fetchHeaders()
     *
     * @var array
     */
    protected $all_headers = [
        'CONTENT-TRANSFER-ENCODING',
        'BCC',
        'IN-REPLY-TO',
        'MAIL-FOLLOWUP-TO',
        'MAIL-REPLY-TO',
        'MESSAGE-ID',
        'REFERENCES',
        'RESENT-BCC',
        'RETURN-PATH',
        'SENDER',
        'X-DRAFT-INFO',
    ];

    /**
     * Connect to the server
     *
     * @param string $host    Host to connect
     * @param string $user    Username for IMAP account
     * @param string $pass    Password for IMAP account
     * @param int    $port    Port to connect to
     * @param string $use_ssl SSL schema (either ssl or tls) or null if plain connection
     *
     * @return bool True on success, False on failure
     */
    abstract public function connect($host, $user, $pass, $port = 143, $use_ssl = null);

    /**
     * Close connection. Usually done on script shutdown
     */
    abstract public function close();

    /**
     * Checks connection state.
     *
     * @return bool True on success, False on failure
     */
    abstract public function is_connected();

    /**
     * Check connection state, connect if not connected.
     *
     * @return bool connection state
     */
    abstract public function check_connection();

    /**
     * Returns code of last error
     *
     * @return int Error code
     */
    abstract public function get_error_code();

    /**
     * Returns message of last error
     *
     * @return string Error message
     */
    abstract public function get_error_str();

    /**
     * Returns code of last command response
     *
     * @return int Response code (class constant)
     */
    abstract public function get_response_code();

    /**
     * Returns storage server vendor name
     *
     * @return string|null Vendor name
     */
    public function get_vendor()
    {
        return null;
    }

    /**
     * Set connection and class options
     *
     * @param array $opt Options array
     */
    public function set_options($opt)
    {
        $this->options = array_merge($this->options, (array) $opt);
    }

    /**
     * Get connection/class option
     *
     * @param string $name Option name
     *
     * @return mixed Option value
     */
    public function get_option($name)
    {
        return $this->options[$name];
    }

    /**
     * Activate/deactivate debug mode.
     *
     * @param bool $dbg True if conversation with the server should be logged
     */
    abstract public function set_debug($dbg = true);

    /**
     * Set default message charset.
     *
     * This will be used for message decoding if a charset specification is not available
     *
     * @param string $cs Charset string
     */
    public function set_charset($cs)
    {
        $this->default_charset = $cs;
    }

    /**
     * Set internal folder reference.
     * All operations will be performed on this folder.
     *
     * @param string $folder Folder name
     */
    public function set_folder($folder)
    {
        if ($this->folder === $folder) {
            return;
        }

        $this->folder = $folder;
    }

    /**
     * Returns the currently used folder name
     *
     * @return string Name of the folder
     */
    public function get_folder()
    {
        return $this->folder;
    }

    /**
     * Set internal list page number.
     *
     * @param int $page Page number to list
     */
    public function set_page($page)
    {
        if ($page = intval($page)) {
            $this->list_page = $page;
        }
    }

    /**
     * Gets internal list page number.
     *
     * @return int Page number
     */
    public function get_page()
    {
        return $this->list_page;
    }

    /**
     * Set internal page size
     *
     * @param int $size Number of messages to display on one page
     */
    public function set_pagesize($size)
    {
        $this->page_size = (int) $size;
    }

    /**
     * Get internal page size
     *
     * @return int Number of messages to display on one page
     */
    public function get_pagesize()
    {
        return $this->page_size;
    }

    /**
     * Save a search result for future message listing methods.
     *
     * @param array $set Search set in driver specific format
     */
    abstract public function set_search_set($set);

    /**
     * Return the saved search set.
     *
     * @return array|null Search set in driver specific format, NULL if search wasn't initialized
     */
    abstract public function get_search_set();

    /**
     * Returns the storage server's (IMAP) capability
     *
     * @param string $cap Capability name
     *
     * @return mixed Capability value or True if supported, False if not
     */
    abstract public function get_capability($cap);

    /**
     * Sets threading flag to the best supported THREAD algorithm.
     * Enable/Disable threaded mode.
     *
     * @param bool $enable True to enable threading
     *
     * @return mixed Threading algorithm or False if THREAD is not supported
     */
    public function set_threading($enable = false)
    {
        $this->threading = false;

        if ($enable && ($caps = $this->get_capability('THREAD'))) {
            $methods = ['REFS', 'REFERENCES', 'ORDEREDSUBJECT'];
            $methods = array_intersect($methods, $caps);

            $this->threading = array_first($methods);
        }

        return $this->threading;
    }

    /**
     * Get current threading flag.
     *
     * @return mixed Threading algorithm or False if THREAD is not supported or disabled
     */
    public function get_threading()
    {
        return $this->threading;
    }

    /**
     * Checks the PERMANENTFLAGS capability of the current folder
     * and returns true if the given flag is supported by the server.
     *
     * @param string $flag Permanentflag name
     *
     * @return bool True if this flag is supported
     */
    abstract public function check_permflag($flag);

    /**
     * Returns the delimiter that is used by the server
     * for folder hierarchy separation.
     *
     * @return string Delimiter string
     */
    abstract public function get_hierarchy_delimiter();

    /**
     * Get namespace
     *
     * @param string $name Namespace array index: personal, other, shared, prefix
     *
     * @return string|array|null Namespace data
     */
    abstract public function get_namespace($name = null);

    /**
     * Get messages count for a specific folder.
     *
     * @param ?string $folder Folder name
     * @param string  $mode   Mode for count [ALL|THREADS|UNSEEN|RECENT|EXISTS]
     * @param bool    $force  Force reading from server and update cache
     * @param bool    $status Enables storing folder status info (max UID/count),
     *                        required for folder_status()
     *
     * @return int Number of messages
     */
    abstract public function count($folder = null, $mode = 'ALL', $force = false, $status = true);

    /**
     * Fetches messages headers (by UID)
     *
     * @param string $folder Folder name
     * @param array  $msgs   Message UIDs
     * @param bool   $sort   Enables result sorting by $msgs
     * @param bool   $force  Disables cache use
     *
     * @return array Messages headers indexed by UID
     */
    abstract public function fetch_headers($folder, $msgs, $sort = true, $force = false);

    /**
     * Public method for listing message flags
     *
     * @param ?string $folder  Folder name
     * @param array   $uids    Message UIDs
     * @param int     $mod_seq Optional MODSEQ value
     *
     * @return array Indexed array with message flags
     */
    abstract public function list_flags($folder, $uids, $mod_seq = null);

    /**
     * Public method for listing headers.
     *
     * @param ?string $folder     Folder name
     * @param int     $page       Current page to list
     * @param string  $sort_field Header field to sort by
     * @param string  $sort_order Sort order [ASC|DESC]
     * @param int     $slice      Number of slice items to extract from result array
     *
     * @return array Indexed array with message header objects
     */
    abstract public function list_messages($folder = null, $page = null, $sort_field = null, $sort_order = null, $slice = 0);

    /**
     * Return sorted list of message UIDs
     *
     * @param ?string $folder     Folder to get index from
     * @param string  $sort_field Sort column
     * @param string  $sort_order Sort order [ASC, DESC]
     * @param bool    $no_threads Get not threaded index
     * @param bool    $no_search  Get index not limited to search result (optionally)
     *
     * @return rcube_result_index|rcube_result_thread List of messages (UIDs)
     */
    abstract public function index($folder = null, $sort_field = null, $sort_order = null, $no_threads = false, $no_search = false);

    /**
     * Invoke search request to the server.
     *
     * @param string $folder     Folder name to search in
     * @param string $str        Search criteria
     * @param string $charset    Search charset
     * @param string $sort_field Header field to sort by
     *
     * @todo: Search criteria should be provided in non-IMAP format, e.g. array
     */
    abstract public function search($folder = null, $str = 'ALL', $charset = null, $sort_field = null);

    /**
     * Direct (real and simple) search request (without result sorting and caching).
     *
     * @param array|string|null $folder Folder name to search in
     * @param string            $str    Search string
     *
     * @return rcube_result_index|rcube_result_multifolder Search result (UIDs)
     */
    abstract public function search_once($folder = null, $str = 'ALL');

    /**
     * Refresh saved search set
     *
     * @return array Current search set
     */
    abstract public function refresh_search();

    /* --------------------------------
     *        messages management
     * --------------------------------*/

    /**
     * Fetch message headers and body structure from the server and build
     * an object structure.
     *
     * @param int    $uid    Message UID to fetch
     * @param string $folder Folder to read from
     *
     * @return rcube_message_header|false Message data, False on error
     */
    abstract public function get_message($uid, $folder = null);

    /**
     * Return message headers object of a specific message
     *
     * @param int    $uid    Message sequence ID or UID
     * @param string $folder Folder to read from
     * @param bool   $force  True to skip cache
     *
     * @return rcube_message_header|false Message headers, False on error
     */
    abstract public function get_message_headers($uid, $folder = null, $force = false);

    /**
     * Fetch message body of a specific message from the server.
     *
     * @param int                $uid               Message UID
     * @param string             $part              Part number
     * @param rcube_message_part $o_part            Part object created by get_structure()
     * @param mixed              $print             True to print part, resource to write part contents in
     * @param resource           $fp                File pointer to save the message part
     * @param bool               $skip_charset_conv Disables charset conversion
     * @param int                $max_bytes         Only read this number of bytes
     * @param bool               $formatted         Enables formatting of text/* parts bodies
     *
     * @return string|bool Message/part body if not printed
     */
    abstract public function get_message_part($uid, $part, $o_part = null, $print = null, $fp = null,
        $skip_charset_conv = false, $max_bytes = 0, $formatted = true);

    /**
     * Fetch message body of a specific message from the server
     *
     * @param int $uid Message UID
     *
     * @return string|false $part Message/part body, False on error
     *
     * @see rcube_imap::get_message_part()
     */
    public function get_body($uid, $part = 1)
    {
        if ($headers = $this->get_message_headers($uid)) {
            return rcube_charset::convert($this->get_message_part($uid, $part, null),
                $headers->charset ?: $this->default_charset);
        }

        return false;
    }

    /**
     * Returns the whole message source as string (or saves to a file)
     *
     * @param int      $uid  Message UID
     * @param resource $fp   File pointer to save the message
     * @param string   $part Optional message part ID
     *
     * @return string|false Message source string
     */
    abstract public function get_raw_body($uid, $fp = null, $part = null);

    /**
     * Returns the message headers as string
     *
     * @param int    $uid  Message UID
     * @param string $part Optional message part ID
     *
     * @return string|false Message headers string
     */
    abstract public function get_raw_headers($uid, $part = null);

    /**
     * Sends the whole message source to stdout
     *
     * @param int  $uid       Message UID
     * @param bool $formatted Enables line-ending formatting
     */
    abstract public function print_raw_body($uid, $formatted = true);

    /**
     * Set message flag to one or several messages
     *
     * @param mixed  $uids       Message UIDs as array or comma-separated string, or '*'
     * @param string $flag       Flag to set: SEEN, UNDELETED, DELETED, RECENT, ANSWERED, DRAFT, MDNSENT
     * @param string $folder     Folder name
     * @param bool   $skip_cache True to skip message cache clean up
     *
     * @return bool Operation status
     */
    abstract public function set_flag($uids, $flag, $folder = null, $skip_cache = false);

    /**
     * Remove message flag for one or several messages
     *
     * @param mixed  $uids   Message UIDs as array or comma-separated string, or '*'
     * @param string $flag   Flag to unset: SEEN, DELETED, RECENT, ANSWERED, DRAFT, MDNSENT
     * @param string $folder Folder name
     *
     * @return bool Operation status
     *
     * @see set_flag
     */
    public function unset_flag($uids, $flag, $folder = null)
    {
        return $this->set_flag($uids, 'UN' . $flag, $folder);
    }

    /**
     * Append a mail message (source) to a specific folder.
     *
     * @param ?string      $folder  Target folder
     * @param string|array $message The message source string or filename
     *                              or array (of strings and file pointers)
     * @param string       $headers Headers string if $message contains only the body
     * @param bool         $is_file True if $message is a filename
     * @param array        $flags   Message flags
     * @param mixed        $date    Message internal date
     *
     * @return int|bool Appended message UID or True on success, False on error
     */
    abstract public function save_message($folder, &$message, $headers = '', $is_file = false, $flags = [], $date = null);

    /**
     * Move message(s) from one folder to another.
     *
     * @param mixed  $uids Message UIDs as array or comma-separated string, or '*'
     * @param string $to   Target folder
     * @param string $from Source folder
     *
     * @return bool True on success, False on error
     */
    abstract public function move_message($uids, $to, $from = null);

    /**
     * Copy message(s) from one mailbox to another.
     *
     * @param mixed  $uids Message UIDs as array or comma-separated string, or '*'
     * @param string $to   Target folder
     * @param string $from Source folder
     *
     * @return bool True on success, False on error
     */
    abstract public function copy_message($uids, $to, $from = null);

    /**
     * Mark message(s) as deleted and expunge.
     *
     * @param array|string $uids   Message UIDs as array or comma-separated string, or '*'
     * @param ?string      $folder Source folder
     *
     * @return bool True on success, False on error
     */
    abstract public function delete_message($uids, $folder = null);

    /**
     * Expunge message(s) and clear the cache.
     *
     * @param mixed  $uids        Message UIDs as array or comma-separated string, or '*'
     * @param string $folder      Folder name
     * @param bool   $clear_cache False if cache should not be cleared
     *
     * @return bool True on success, False on error
     */
    abstract public function expunge_message($uids, $folder = null, $clear_cache = true);

    /**
     * Parse message UIDs input
     *
     * @param mixed $uids Message UIDs as array or comma-separated string, or '*'
     *                    or rcube_result_index object
     *
     * @return array Two elements array with UIDs converted to list and ALL flag
     */
    protected function parse_uids($uids)
    {
        $all = false;

        if ($uids instanceof rcube_result_index) {
            $uids = $uids->get_compressed();
        } elseif ($uids === '*' || $uids === '1:*') {
            if (empty($this->search_set)) {
                $uids = '1:*';
                $all = true;
            }
            // get UIDs from current search set
            else {
                $uids = implode(',', $this->search_set->get());
            }
        } else {
            if (is_array($uids)) {
                $uids = implode(',', $uids);
            } elseif (strpos($uids, ':')) {
                $uids = implode(',', rcube_imap_generic::uncompressMessageSet($uids));
            }

            if (preg_match('/[^0-9,]/', $uids)) {
                $uids = '';
            }
        }

        return [$uids, $all];
    }

    /* --------------------------------
     *        folder management
     * --------------------------------*/

    /**
     * Get a list of subscribed folders.
     *
     * @param string $root      Optional root folder
     * @param string $name      Optional name pattern
     * @param string $filter    Optional filter
     * @param string $rights    Optional ACL requirements
     * @param bool   $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return array List of folders
     */
    abstract public function list_folders_subscribed($root = '', $name = '*', $filter = null, $rights = null, $skip_sort = false);

    /**
     * Get a list of all folders available on the server.
     *
     * @param string $root      IMAP root dir
     * @param string $name      Optional name pattern
     * @param mixed  $filter    Optional filter
     * @param string $rights    Optional ACL requirements
     * @param bool   $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return array Indexed array with folder names
     */
    abstract public function list_folders($root = '', $name = '*', $filter = null, $rights = null, $skip_sort = false);

    /**
     * Subscribe to a specific folder(s)
     *
     * @param array $folders Folder name(s)
     *
     * @return bool True on success
     */
    abstract public function subscribe($folders);

    /**
     * Unsubscribe folder(s)
     *
     * @param array $folders Folder name(s)
     *
     * @return bool True on success
     */
    abstract public function unsubscribe($folders);

    /**
     * Create a new folder on the server.
     *
     * @param string $folder    New folder name
     * @param bool   $subscribe True if the new folder should be subscribed
     * @param string $type      Optional folder type (junk, trash, drafts, sent, archive)
     * @param bool   $noselect  Make the folder \NoSelect folder by adding hierarchy
     *                          separator at the end (useful for server that do not support
     *                          both folders and messages as folder children)
     *
     * @return bool True on success, False on error
     */
    abstract public function create_folder($folder, $subscribe = false, $type = null, $noselect = false);

    /**
     * Set a new name to an existing folder
     *
     * @param string $folder   Folder to rename
     * @param string $new_name New folder name
     *
     * @return bool True on success, False on error
     */
    abstract public function rename_folder($folder, $new_name);

    /**
     * Remove a folder from the server.
     *
     * @param string $folder Folder name
     *
     * @return bool True on success, False on error
     */
    abstract public function delete_folder($folder);

    /**
     * Send expunge command and clear the cache.
     *
     * @param string $folder      Folder name
     * @param bool   $clear_cache False if cache should not be cleared
     *
     * @return bool True on success, False on error
     */
    public function expunge_folder($folder = null, $clear_cache = true)
    {
        return $this->expunge_message('*', $folder, $clear_cache);
    }

    /**
     * Remove all messages in a folder.
     *
     * @param string $folder Folder name
     *
     * @return bool True on success, False on error
     */
    public function clear_folder($folder = null)
    {
        return $this->delete_message('*', $folder);
    }

    /**
     * Checks if folder exists and is subscribed
     *
     * @param string $folder       Folder name
     * @param bool   $subscription Enable subscription checking
     *
     * @return bool True if folder exists, False otherwise
     */
    abstract public function folder_exists($folder, $subscription = false);

    /**
     * Get folder size (size of all messages in a folder)
     *
     * @param string $folder Folder name
     *
     * @return int|false Folder size in bytes, False on error
     */
    abstract public function folder_size($folder);

    /**
     * Returns the namespace where the folder is in
     *
     * @param string $folder Folder name
     *
     * @return string One of 'personal', 'other' or 'shared'
     */
    abstract public function folder_namespace($folder);

    /**
     * Gets folder attributes (from LIST response, e.g. \Noselect, \Noinferiors).
     *
     * @param string $folder Folder name
     * @param bool   $force  Set to True if attributes should be refreshed
     *
     * @return array Options list
     */
    abstract public function folder_attributes($folder, $force = false);

    /**
     * Gets connection (and current folder) data: UIDVALIDITY, EXISTS, RECENT,
     * PERMANENTFLAGS, UIDNEXT, UNSEEN
     *
     * @param string $folder Folder name
     *
     * @return array Data
     */
    abstract public function folder_data($folder);

    /**
     * Returns extended information about the folder.
     *
     * @param string $folder Folder name
     *
     * @return array Data
     */
    abstract public function folder_info($folder);

    /**
     * Returns current status of a folder (compared to the last time use)
     *
     * @param string $folder Folder name
     * @param array  $diff   Difference data
     *
     * @return int Folder status
     */
    abstract public function folder_status($folder = null, &$diff = []);

    /**
     * Synchronizes messages cache.
     *
     * @param string $folder Folder name
     */
    abstract public function folder_sync($folder);

    /**
     * Modify folder name according to namespace.
     * For output it removes prefix of the personal namespace if it's possible.
     * For input it adds the prefix. Use it before creating a folder in root
     * of the folders tree.
     *
     * @param string $folder Folder name
     * @param string $mode   Mode name (out/in)
     *
     * @return string Folder name
     */
    abstract public function mod_folder($folder, $mode = 'out');

    /**
     * Check if the folder name is valid
     *
     * @param string $folder Folder name (UTF-8)
     * @param string &$char  First forbidden character found
     *
     * @return bool True if the name is valid, False otherwise
     */
    public function folder_validate($folder, &$char = null)
    {
        $delim = $this->get_hierarchy_delimiter();

        if (strpos($folder, $delim) !== false) {
            $char = $delim;
            return false;
        }

        return true;
    }

    /**
     * Create all folders specified as default
     */
    public function create_default_folders()
    {
        $rcube = rcube::get_instance();

        // create default folders if they do not exist
        foreach (self::$folder_types as $type) {
            if ($folder = $rcube->config->get($type . '_mbox')) {
                if (!$this->folder_exists($folder)) {
                    $this->create_folder($folder, true, $type);
                } elseif (!$this->folder_exists($folder, true)) {
                    $this->subscribe($folder);
                }
            }
        }
    }

    /**
     * Check if specified folder is a special folder
     */
    public function is_special_folder($name)
    {
        return $name == 'INBOX' || in_array($name, $this->get_special_folders());
    }

    /**
     * Return configured special folders
     */
    public function get_special_folders($forced = false)
    {
        // getting config might be expensive, store special folders in memory
        if (!isset($this->icache['special-folders'])) {
            $rcube = rcube::get_instance();
            $this->icache['special-folders'] = [];

            foreach (self::$folder_types as $type) {
                if ($folder = $rcube->config->get($type . '_mbox')) {
                    $this->icache['special-folders'][$type] = $folder;
                }
            }
        }

        return $this->icache['special-folders'];
    }

    /**
     * Set special folder associations stored in backend
     */
    public function set_special_folders($specials)
    {
        // should be overridden by storage class if backend supports special folders (SPECIAL-USE)
        unset($this->icache['special-folders']);
    }

    /**
     * Get mailbox quota information.
     *
     * @param string $folder Folder name
     *
     * @return mixed Quota info or False if not supported
     */
    abstract public function get_quota($folder = null);

    /* -----------------------------------------
     *   ACL and METADATA methods
     * ----------------------------------------*/

    /**
     * Changes the ACL on the specified folder (SETACL)
     *
     * @param string $folder Folder name
     * @param string $user   User name
     * @param string $acl    ACL string
     *
     * @return bool True on success, False on failure
     */
    abstract public function set_acl($folder, $user, $acl);

    /**
     * Removes any <identifier,rights> pair for the
     * specified user from the ACL for the specified
     * folder (DELETEACL).
     *
     * @param string $folder Folder name
     * @param string $user   User name
     *
     * @return bool True on success, False on failure
     */
    abstract public function delete_acl($folder, $user);

    /**
     * Returns the access control list for a folder (GETACL).
     *
     * @param string $folder Folder name
     *
     * @return array|null User-rights array on success, NULL on error
     */
    abstract public function get_acl($folder);

    /**
     * Returns information about what rights can be granted to the
     * user (identifier) in the ACL for the folder (LISTRIGHTS).
     *
     * @param string $folder Folder name
     * @param string $user   User name
     *
     * @return array|null List of user rights
     */
    abstract public function list_rights($folder, $user);

    /**
     * Returns the set of rights that the current user has to a folder (MYRIGHTS).
     *
     * @param string $folder Folder name
     *
     * @return array|null MYRIGHTS response on success, NULL on error
     */
    abstract public function my_rights($folder);

    /**
     * Sets metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entry-value array (use NULL value as NIL)
     *
     * @return bool True on success, False on failure
     */
    abstract public function set_metadata($folder, $entries);

    /**
     * Unsets metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entry names array
     *
     * @return bool True on success, False on failure
     */
    abstract public function delete_metadata($folder, $entries);

    /**
     * Returns folder metadata/annotations (GETMETADATA/GETANNOTATION).
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entries
     * @param array  $options Command options (with MAXSIZE and DEPTH keys)
     * @param bool   $force   Disables cache use
     *
     * @return array|null Metadata entry-value hash array on success, NULL on error
     */
    abstract public function get_metadata($folder, $entries, $options = [], $force = false);

    /* -----------------------------------------
     *   Cache related functions
     * ----------------------------------------*/

    /**
     * Enable or disable indexes caching
     *
     * @param string $type Cache type (@see rcube::get_cache)
     */
    public function set_caching($type)
    {
        // NOP
    }

    /**
     * Enable or disable messages caching
     *
     * @param bool $set  Flag
     * @param int  $mode Cache mode
     */
    public function set_messages_caching($set, $mode = null)
    {
        // NOP
    }

    /**
     * Clears the cache.
     *
     * @param string $key         Cache key name or pattern
     * @param bool   $prefix_mode Enable it to clear all keys starting
     *                            with prefix specified in $key
     */
    public function clear_cache($key = null, $prefix_mode = false)
    {
        // NOP
    }

    /**
     * Returns cached value
     *
     * @param string $key Cache key
     *
     * @return mixed Cached value
     */
    public function get_cache($key)
    {
        return null;
    }

    /**
     * Delete outdated cache entries
     */
    public function cache_gc()
    {
        // NOP
    }
}
