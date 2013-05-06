<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   IMAP Storage Engine                                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Interface class for accessing an IMAP server
 *
 * @package    Framework
 * @subpackage Storage
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 */
class rcube_imap extends rcube_storage
{
    /**
     * Instance of rcube_imap_generic
     *
     * @var rcube_imap_generic
     */
    public $conn;

    /**
     * Instance of rcube_imap_cache
     *
     * @var rcube_imap_cache
     */
    protected $mcache;

    /**
     * Instance of rcube_cache
     *
     * @var rcube_cache
     */
    protected $cache;

    /**
     * Internal (in-memory) cache
     *
     * @var array
     */
    protected $icache = array();

    protected $list_page = 1;
    protected $delimiter;
    protected $namespace;
    protected $sort_field = '';
    protected $sort_order = 'DESC';
    protected $struct_charset;
    protected $uid_id_map = array();
    protected $msg_headers = array();
    protected $search_set;
    protected $search_string = '';
    protected $search_charset = '';
    protected $search_sort_field = '';
    protected $search_threads = false;
    protected $search_sorted = false;
    protected $options = array('auth_method' => 'check');
    protected $caching = false;
    protected $messages_caching = false;
    protected $threading = false;


    /**
     * Object constructor.
     */
    public function __construct()
    {
        $this->conn = new rcube_imap_generic();

        // Set namespace and delimiter from session,
        // so some methods would work before connection
        if (isset($_SESSION['imap_namespace'])) {
            $this->namespace = $_SESSION['imap_namespace'];
        }
        if (isset($_SESSION['imap_delimiter'])) {
            $this->delimiter = $_SESSION['imap_delimiter'];
        }
    }


    /**
     * Magic getter for backward compat.
     *
     * @deprecated.
     */
    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }


    /**
     * Connect to an IMAP server
     *
     * @param  string   $host    Host to connect
     * @param  string   $user    Username for IMAP account
     * @param  string   $pass    Password for IMAP account
     * @param  integer  $port    Port to connect to
     * @param  string   $use_ssl SSL schema (either ssl or tls) or null if plain connection
     *
     * @return boolean  TRUE on success, FALSE on failure
     */
    public function connect($host, $user, $pass, $port=143, $use_ssl=null)
    {
        // check for OpenSSL support in PHP build
        if ($use_ssl && extension_loaded('openssl')) {
            $this->options['ssl_mode'] = $use_ssl == 'imaps' ? 'ssl' : $use_ssl;
        }
        else if ($use_ssl) {
            rcube::raise_error(array('code' => 403, 'type' => 'imap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "OpenSSL not available"), true, false);
            $port = 143;
        }

        $this->options['port'] = $port;

        if ($this->options['debug']) {
            $this->set_debug(true);

            $this->options['ident'] = array(
                'name'    => 'Roundcube',
                'version' => RCUBE_VERSION,
                'php'     => PHP_VERSION,
                'os'      => PHP_OS,
                'command' => $_SERVER['REQUEST_URI'],
            );
        }

        $attempt = 0;
        do {
            $data = rcube::get_instance()->plugins->exec_hook('storage_connect',
                array_merge($this->options, array('host' => $host, 'user' => $user,
                    'attempt' => ++$attempt)));

            if (!empty($data['pass'])) {
                $pass = $data['pass'];
            }

            $this->conn->connect($data['host'], $data['user'], $pass, $data);
        } while(!$this->conn->connected() && $data['retry']);

        $config = array(
            'host'     => $data['host'],
            'user'     => $data['user'],
            'password' => $pass,
            'port'     => $port,
            'ssl'      => $use_ssl,
        );

        $this->options      = array_merge($this->options, $config);
        $this->connect_done = true;

        if ($this->conn->connected()) {
            // get namespace and delimiter
            $this->set_env();
            return true;
        }
        // write error log
        else if ($this->conn->error) {
            if ($pass && $user) {
                $message = sprintf("Login failed for %s from %s. %s",
                    $user, rcube_utils::remote_ip(), $this->conn->error);

                rcube::raise_error(array('code' => 403, 'type' => 'imap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => $message), true, false);
            }
        }

        return false;
    }


    /**
     * Close IMAP connection.
     * Usually done on script shutdown
     */
    public function close()
    {
        $this->conn->closeConnection();
        if ($this->mcache) {
            $this->mcache->close();
        }
    }


    /**
     * Check connection state, connect if not connected.
     *
     * @return bool Connection state.
     */
    public function check_connection()
    {
        // Establish connection if it wasn't done yet
        if (!$this->connect_done && !empty($this->options['user'])) {
            return $this->connect(
                $this->options['host'],
                $this->options['user'],
                $this->options['password'],
                $this->options['port'],
                $this->options['ssl']
            );
        }

        return $this->is_connected();
    }


    /**
     * Checks IMAP connection.
     *
     * @return boolean  TRUE on success, FALSE on failure
     */
    public function is_connected()
    {
        return $this->conn->connected();
    }


    /**
     * Returns code of last error
     *
     * @return int Error code
     */
    public function get_error_code()
    {
        return $this->conn->errornum;
    }


    /**
     * Returns text of last error
     *
     * @return string Error string
     */
    public function get_error_str()
    {
        return $this->conn->error;
    }


    /**
     * Returns code of last command response
     *
     * @return int Response code
     */
    public function get_response_code()
    {
        switch ($this->conn->resultcode) {
            case 'NOPERM':
                return self::NOPERM;
            case 'READ-ONLY':
                return self::READONLY;
            case 'TRYCREATE':
                return self::TRYCREATE;
            case 'INUSE':
                return self::INUSE;
            case 'OVERQUOTA':
                return self::OVERQUOTA;
            case 'ALREADYEXISTS':
                return self::ALREADYEXISTS;
            case 'NONEXISTENT':
                return self::NONEXISTENT;
            case 'CONTACTADMIN':
                return self::CONTACTADMIN;
            default:
                return self::UNKNOWN;
        }
    }


    /**
     * Activate/deactivate debug mode
     *
     * @param boolean $dbg True if IMAP conversation should be logged
     */
    public function set_debug($dbg = true)
    {
        $this->options['debug'] = $dbg;
        $this->conn->setDebug($dbg, array($this, 'debug_handler'));
    }


    /**
     * Set internal folder reference.
     * All operations will be perfomed on this folder.
     *
     * @param  string $folder Folder name
     */
    public function set_folder($folder)
    {
        if ($this->folder == $folder) {
            return;
        }

        $this->folder = $folder;

        // clear messagecount cache for this folder
        $this->clear_messagecount($folder);
    }


    /**
     * Save a search result for future message listing methods
     *
     * @param  array  $set  Search set, result from rcube_imap::get_search_set():
     *                      0 - searching criteria, string
     *                      1 - search result, rcube_result_index|rcube_result_thread
     *                      2 - searching character set, string
     *                      3 - sorting field, string
     *                      4 - true if sorted, bool
     */
    public function set_search_set($set)
    {
        $set = (array)$set;

        $this->search_string     = $set[0];
        $this->search_set        = $set[1];
        $this->search_charset    = $set[2];
        $this->search_sort_field = $set[3];
        $this->search_sorted     = $set[4];
        $this->search_threads    = is_a($this->search_set, 'rcube_result_thread');
    }


    /**
     * Return the saved search set as hash array
     *
     * @return array Search set
     */
    public function get_search_set()
    {
        if (empty($this->search_set)) {
            return null;
        }

        return array(
            $this->search_string,
            $this->search_set,
            $this->search_charset,
            $this->search_sort_field,
            $this->search_sorted,
        );
    }


    /**
     * Returns the IMAP server's capability.
     *
     * @param   string  $cap Capability name
     *
     * @return  mixed   Capability value or TRUE if supported, FALSE if not
     */
    public function get_capability($cap)
    {
        $cap      = strtoupper($cap);
        $sess_key = "STORAGE_$cap";

        if (!isset($_SESSION[$sess_key])) {
            if (!$this->check_connection()) {
                return false;
            }

            $_SESSION[$sess_key] = $this->conn->getCapability($cap);
        }

        return $_SESSION[$sess_key];
    }


    /**
     * Checks the PERMANENTFLAGS capability of the current folder
     * and returns true if the given flag is supported by the IMAP server
     *
     * @param   string  $flag Permanentflag name
     *
     * @return  boolean True if this flag is supported
     */
    public function check_permflag($flag)
    {
        $flag       = strtoupper($flag);
        $imap_flag  = $this->conn->flags[$flag];
        $perm_flags = $this->get_permflags($this->folder);

        return in_array_nocase($imap_flag, $perm_flags);
    }


    /**
     * Returns PERMANENTFLAGS of the specified folder
     *
     * @param  string $folder Folder name
     *
     * @return array Flags
     */
    public function get_permflags($folder)
    {
        if (!strlen($folder)) {
            return array();
        }
/*
        Checking PERMANENTFLAGS is rather rare, so we disable caching of it
        Re-think when we'll use it for more than only MDNSENT flag

        $cache_key = 'mailboxes.permanentflags.' . $folder;
        $permflags = $this->get_cache($cache_key);

        if ($permflags !== null) {
            return explode(' ', $permflags);
        }
*/
        if (!$this->check_connection()) {
            return array();
        }

        if ($this->conn->select($folder)) {
            $permflags = $this->conn->data['PERMANENTFLAGS'];
        }
        else {
            return array();
        }

        if (!is_array($permflags)) {
            $permflags = array();
        }
/*
        // Store permflags as string to limit cached object size
        $this->update_cache($cache_key, implode(' ', $permflags));
*/
        return $permflags;
    }


    /**
     * Returns the delimiter that is used by the IMAP server for folder separation
     *
     * @return  string  Delimiter string
     * @access  public
     */
    public function get_hierarchy_delimiter()
    {
        return $this->delimiter;
    }


    /**
     * Get namespace
     *
     * @param string $name Namespace array index: personal, other, shared, prefix
     *
     * @return  array  Namespace data
     */
    public function get_namespace($name = null)
    {
        $ns = $this->namespace;

        if ($name) {
            return isset($ns[$name]) ? $ns[$name] : null;
        }

        unset($ns['prefix']);
        return $ns;
    }


    /**
     * Sets delimiter and namespaces
     */
    protected function set_env()
    {
        if ($this->delimiter !== null && $this->namespace !== null) {
            return;
        }

        $config = rcube::get_instance()->config;
        $imap_personal  = $config->get('imap_ns_personal');
        $imap_other     = $config->get('imap_ns_other');
        $imap_shared    = $config->get('imap_ns_shared');
        $imap_delimiter = $config->get('imap_delimiter');

        if (!$this->check_connection()) {
            return;
        }

        $ns = $this->conn->getNamespace();

        // Set namespaces (NAMESPACE supported)
        if (is_array($ns)) {
            $this->namespace = $ns;
        }
        else {
            $this->namespace = array(
                'personal' => NULL,
                'other'    => NULL,
                'shared'   => NULL,
            );
        }

        if ($imap_delimiter) {
            $this->delimiter = $imap_delimiter;
        }
        if (empty($this->delimiter)) {
            $this->delimiter = $this->namespace['personal'][0][1];
        }
        if (empty($this->delimiter)) {
            $this->delimiter = $this->conn->getHierarchyDelimiter();
        }
        if (empty($this->delimiter)) {
            $this->delimiter = '/';
        }

        // Overwrite namespaces
        if ($imap_personal !== null) {
            $this->namespace['personal'] = NULL;
            foreach ((array)$imap_personal as $dir) {
                $this->namespace['personal'][] = array($dir, $this->delimiter);
            }
        }
        if ($imap_other !== null) {
            $this->namespace['other'] = NULL;
            foreach ((array)$imap_other as $dir) {
                if ($dir) {
                    $this->namespace['other'][] = array($dir, $this->delimiter);
                }
            }
        }
        if ($imap_shared !== null) {
            $this->namespace['shared'] = NULL;
            foreach ((array)$imap_shared as $dir) {
                if ($dir) {
                    $this->namespace['shared'][] = array($dir, $this->delimiter);
                }
            }
        }

        // Find personal namespace prefix for mod_folder()
        // Prefix can be removed when there is only one personal namespace
        if (is_array($this->namespace['personal']) && count($this->namespace['personal']) == 1) {
            $this->namespace['prefix'] = $this->namespace['personal'][0][0];
        }

        $_SESSION['imap_namespace'] = $this->namespace;
        $_SESSION['imap_delimiter'] = $this->delimiter;
    }


    /**
     * Get message count for a specific folder
     *
     * @param  string  $folder  Folder name
     * @param  string  $mode    Mode for count [ALL|THREADS|UNSEEN|RECENT|EXISTS]
     * @param  boolean $force   Force reading from server and update cache
     * @param  boolean $status  Enables storing folder status info (max UID/count),
     *                          required for folder_status()
     *
     * @return int     Number of messages
     */
    public function count($folder='', $mode='ALL', $force=false, $status=true)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        return $this->countmessages($folder, $mode, $force, $status);
    }


    /**
     * protected method for getting nr of messages
     *
     * @param string  $folder  Folder name
     * @param string  $mode    Mode for count [ALL|THREADS|UNSEEN|RECENT|EXISTS]
     * @param boolean $force   Force reading from server and update cache
     * @param boolean $status  Enables storing folder status info (max UID/count),
     *                         required for folder_status()
     *
     * @return int Number of messages
     * @see rcube_imap::count()
     */
    protected function countmessages($folder, $mode='ALL', $force=false, $status=true)
    {
        $mode = strtoupper($mode);

        // count search set, assume search set is always up-to-date (don't check $force flag)
        if ($this->search_string && $folder == $this->folder && ($mode == 'ALL' || $mode == 'THREADS')) {
            if ($mode == 'ALL') {
                return $this->search_set->count_messages();
            }
            else {
                return $this->search_set->count();
            }
        }

        // EXISTS is a special alias for ALL, it allows to get the number
        // of all messages in a folder also when search is active and with
        // any skip_deleted setting

        $a_folder_cache = $this->get_cache('messagecount');

        // return cached value
        if (!$force && is_array($a_folder_cache[$folder]) && isset($a_folder_cache[$folder][$mode])) {
            return $a_folder_cache[$folder][$mode];
        }

        if (!is_array($a_folder_cache[$folder])) {
            $a_folder_cache[$folder] = array();
        }

        if ($mode == 'THREADS') {
            $res   = $this->fetch_threads($folder, $force);
            $count = $res->count();

            if ($status) {
                $msg_count = $res->count_messages();
                $this->set_folder_stats($folder, 'cnt', $msg_count);
                $this->set_folder_stats($folder, 'maxuid', $msg_count ? $this->id2uid($msg_count, $folder) : 0);
            }
        }
        // Need connection here
        else if (!$this->check_connection()) {
            return 0;
        }
        // RECENT count is fetched a bit different
        else if ($mode == 'RECENT') {
            $count = $this->conn->countRecent($folder);
        }
        // use SEARCH for message counting
        else if ($mode != 'EXISTS' && !empty($this->options['skip_deleted'])) {
            $search_str = "ALL UNDELETED";
            $keys       = array('COUNT');

            if ($mode == 'UNSEEN') {
                $search_str .= " UNSEEN";
            }
            else {
                if ($this->messages_caching) {
                    $keys[] = 'ALL';
                }
                if ($status) {
                    $keys[]   = 'MAX';
                }
            }

            // @TODO: if $force==false && $mode == 'ALL' we could try to use cache index here

            // get message count using (E)SEARCH
            // not very performant but more precise (using UNDELETED)
            $index = $this->conn->search($folder, $search_str, true, $keys);
            $count = $index->count();

            if ($mode == 'ALL') {
                // Cache index data, will be used in index_direct()
                $this->icache['undeleted_idx'] = $index;

                if ($status) {
                    $this->set_folder_stats($folder, 'cnt', $count);
                    $this->set_folder_stats($folder, 'maxuid', $index->max());
                }
            }
        }
        else {
            if ($mode == 'UNSEEN') {
                $count = $this->conn->countUnseen($folder);
            }
            else {
                $count = $this->conn->countMessages($folder);
                if ($status && $mode == 'ALL') {
                    $this->set_folder_stats($folder, 'cnt', $count);
                    $this->set_folder_stats($folder, 'maxuid', $count ? $this->id2uid($count, $folder) : 0);
                }
            }
        }

        $a_folder_cache[$folder][$mode] = (int)$count;

        // write back to cache
        $this->update_cache('messagecount', $a_folder_cache);

        return (int)$count;
    }


    /**
     * Public method for listing headers
     *
     * @param   string   $folder     Folder name
     * @param   int      $page       Current page to list
     * @param   string   $sort_field Header field to sort by
     * @param   string   $sort_order Sort order [ASC|DESC]
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     */
    public function list_messages($folder='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $slice=0)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        return $this->_list_messages($folder, $page, $sort_field, $sort_order, $slice);
    }


    /**
     * protected method for listing message headers
     *
     * @param   string   $folder     Folder name
     * @param   int      $page       Current page to list
     * @param   string   $sort_field Header field to sort by
     * @param   string   $sort_order Sort order [ASC|DESC]
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @see     rcube_imap::list_messages
     */
    protected function _list_messages($folder='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $slice=0)
    {
        if (!strlen($folder)) {
            return array();
        }

        $this->set_sort_order($sort_field, $sort_order);
        $page = $page ? $page : $this->list_page;

        // use saved message set
        if ($this->search_string && $folder == $this->folder) {
            return $this->list_search_messages($folder, $page, $slice);
        }

        if ($this->threading) {
            return $this->list_thread_messages($folder, $page, $slice);
        }

        // get UIDs of all messages in the folder, sorted
        $index = $this->index($folder, $this->sort_field, $this->sort_order);

        if ($index->is_empty()) {
            return array();
        }

        $from = ($page-1) * $this->page_size;
        $to   = $from + $this->page_size;

        $index->slice($from, $to - $from);

        if ($slice) {
            $index->slice(-$slice, $slice);
        }

        // fetch reqested messages headers
        $a_index = $index->get();
        $a_msg_headers = $this->fetch_headers($folder, $a_index);

        return array_values($a_msg_headers);
    }


    /**
     * protected method for listing message headers using threads
     *
     * @param   string   $folder     Folder name
     * @param   int      $page       Current page to list
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @see     rcube_imap::list_messages
     */
    protected function list_thread_messages($folder, $page, $slice=0)
    {
        // get all threads (not sorted)
        if ($mcache = $this->get_mcache_engine()) {
            $threads = $mcache->get_thread($folder);
        }
        else {
            $threads = $this->fetch_threads($folder);
        }

        return $this->fetch_thread_headers($folder, $threads, $page, $slice);
    }

    /**
     * Method for fetching threads data
     *
     * @param  string $folder  Folder name
     * @param  bool   $force   Use IMAP server, no cache
     *
     * @return rcube_imap_thread Thread data object
     */
    function fetch_threads($folder, $force = false)
    {
        if (!$force && ($mcache = $this->get_mcache_engine())) {
            // don't store in self's internal cache, cache has it's own internal cache
            return $mcache->get_thread($folder);
        }

        if (empty($this->icache['threads'])) {
            if (!$this->check_connection()) {
                return new rcube_result_thread();
            }

            // get all threads
            $result = $this->conn->thread($folder, $this->threading,
                $this->options['skip_deleted'] ? 'UNDELETED' : '', true);

            // add to internal (fast) cache
            $this->icache['threads'] = $result;
        }

        return $this->icache['threads'];
    }


    /**
     * protected method for fetching threaded messages headers
     *
     * @param string              $folder     Folder name
     * @param rcube_result_thread $threads    Threads data object
     * @param int                 $page       List page number
     * @param int                 $slice      Number of threads to slice
     *
     * @return array  Messages headers
     */
    protected function fetch_thread_headers($folder, $threads, $page, $slice=0)
    {
        // Sort thread structure
        $this->sort_threads($threads);

        $from = ($page-1) * $this->page_size;
        $to   = $from + $this->page_size;

        $threads->slice($from, $to - $from);

        if ($slice) {
            $threads->slice(-$slice, $slice);
        }

        // Get UIDs of all messages in all threads
        $a_index = $threads->get();

        // fetch reqested headers from server
        $a_msg_headers = $this->fetch_headers($folder, $a_index);

        unset($a_index);

        // Set depth, has_children and unread_children fields in headers
        $this->set_thread_flags($a_msg_headers, $threads);

        return array_values($a_msg_headers);
    }


    /**
     * protected method for setting threaded messages flags:
     * depth, has_children and unread_children
     *
     * @param  array               $headers  Reference to headers array indexed by message UID
     * @param  rcube_result_thread $threads  Threads data object
     *
     * @return array Message headers array indexed by message UID
     */
    protected function set_thread_flags(&$headers, $threads)
    {
        $parents = array();

        list ($msg_depth, $msg_children) = $threads->get_thread_data();

        foreach ($headers as $uid => $header) {
            $depth = $msg_depth[$uid];
            $parents = array_slice($parents, 0, $depth);

            if (!empty($parents)) {
                $headers[$uid]->parent_uid = end($parents);
                if (empty($header->flags['SEEN']))
                    $headers[$parents[0]]->unread_children++;
            }
            array_push($parents, $uid);

            $headers[$uid]->depth = $depth;
            $headers[$uid]->has_children = $msg_children[$uid];
        }
    }


    /**
     * protected method for listing a set of message headers (search results)
     *
     * @param   string   $folder   Folder name
     * @param   int      $page     Current page to list
     * @param   int      $slice    Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     */
    protected function list_search_messages($folder, $page, $slice=0)
    {
        if (!strlen($folder) || empty($this->search_set) || $this->search_set->is_empty()) {
            return array();
        }

        // use saved messages from searching
        if ($this->threading) {
            return $this->list_search_thread_messages($folder, $page, $slice);
        }

        // search set is threaded, we need a new one
        if ($this->search_threads) {
            $this->search('', $this->search_string, $this->search_charset, $this->sort_field);
        }

        $index = clone $this->search_set;
        $from  = ($page-1) * $this->page_size;
        $to    = $from + $this->page_size;

        // return empty array if no messages found
        if ($index->is_empty()) {
            return array();
        }

        // quickest method (default sorting)
        if (!$this->search_sort_field && !$this->sort_field) {
            $got_index = true;
        }
        // sorted messages, so we can first slice array and then fetch only wanted headers
        else if ($this->search_sorted) { // SORT searching result
            $got_index = true;
            // reset search set if sorting field has been changed
            if ($this->sort_field && $this->search_sort_field != $this->sort_field) {
                $this->search('', $this->search_string, $this->search_charset, $this->sort_field);

                $index = clone $this->search_set;

                // return empty array if no messages found
                if ($index->is_empty()) {
                    return array();
                }
            }
        }

        if ($got_index) {
            if ($this->sort_order != $index->get_parameters('ORDER')) {
                $index->revert();
            }

            // get messages uids for one page
            $index->slice($from, $to-$from);

            if ($slice) {
                $index->slice(-$slice, $slice);
            }

            // fetch headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($folder, $a_index);

            return array_values($a_msg_headers);
        }

        // SEARCH result, need sorting
        $cnt = $index->count();

        // 300: experimantal value for best result
        if (($cnt > 300 && $cnt > $this->page_size) || !$this->sort_field) {
            // use memory less expensive (and quick) method for big result set
            $index = clone $this->index('', $this->sort_field, $this->sort_order);
            // get messages uids for one page...
            $index->slice($from, min($cnt-$from, $this->page_size));

            if ($slice) {
                $index->slice(-$slice, $slice);
            }

            // ...and fetch headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($folder, $a_index);

            return array_values($a_msg_headers);
        }
        else {
            // for small result set we can fetch all messages headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($folder, $a_index, false);

            // return empty array if no messages found
            if (!is_array($a_msg_headers) || empty($a_msg_headers)) {
                return array();
            }

            if (!$this->check_connection()) {
                return array();
            }

            // if not already sorted
            $a_msg_headers = $this->conn->sortHeaders(
                $a_msg_headers, $this->sort_field, $this->sort_order);

            // only return the requested part of the set
            $slice_length  = min($this->page_size, $cnt - ($to > $cnt ? $from : $to));
            $a_msg_headers = array_slice(array_values($a_msg_headers), $from, $slice_length);

            if ($slice) {
                $a_msg_headers = array_slice($a_msg_headers, -$slice, $slice);
            }

            return $a_msg_headers;
        }
    }


    /**
     * protected method for listing a set of threaded message headers (search results)
     *
     * @param   string   $folder     Folder name
     * @param   int      $page       Current page to list
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @see rcube_imap::list_search_messages()
     */
    protected function list_search_thread_messages($folder, $page, $slice=0)
    {
        // update search_set if previous data was fetched with disabled threading
        if (!$this->search_threads) {
            if ($this->search_set->is_empty()) {
                return array();
            }
            $this->search('', $this->search_string, $this->search_charset, $this->sort_field);
        }

        return $this->fetch_thread_headers($folder, clone $this->search_set, $page, $slice);
    }


    /**
     * Fetches messages headers (by UID)
     *
     * @param  string  $folder   Folder name
     * @param  array   $msgs     Message UIDs
     * @param  bool    $sort     Enables result sorting by $msgs
     * @param  bool    $force    Disables cache use
     *
     * @return array Messages headers indexed by UID
     */
    function fetch_headers($folder, $msgs, $sort = true, $force = false)
    {
        if (empty($msgs)) {
            return array();
        }

        if (!$force && ($mcache = $this->get_mcache_engine())) {
            $headers = $mcache->get_messages($folder, $msgs);
        }
        else if (!$this->check_connection()) {
            return array();
        }
        else {
            // fetch reqested headers from server
            $headers = $this->conn->fetchHeaders(
                $folder, $msgs, true, false, $this->get_fetch_headers());
        }

        if (empty($headers)) {
            return array();
        }

        foreach ($headers as $h) {
            $a_msg_headers[$h->uid] = $h;
        }

        if ($sort) {
            // use this class for message sorting
            $sorter = new rcube_message_header_sorter();
            $sorter->set_index($msgs);
            $sorter->sort_headers($a_msg_headers);
        }

        return $a_msg_headers;
    }


    /**
     * Returns current status of folder
     *
     * We compare the maximum UID to determine the number of
     * new messages because the RECENT flag is not reliable.
     *
     * @param string $folder Folder name
     *
     * @return int   Folder status
     */
    public function folder_status($folder = null)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }
        $old = $this->get_folder_stats($folder);

        // refresh message count -> will update
        $this->countmessages($folder, 'ALL', true);

        $result = 0;

        if (empty($old)) {
            return $result;
        }

        $new = $this->get_folder_stats($folder);

        // got new messages
        if ($new['maxuid'] > $old['maxuid']) {
            $result += 1;
        }
        // some messages has been deleted
        if ($new['cnt'] < $old['cnt']) {
            $result += 2;
        }

        // @TODO: optional checking for messages flags changes (?)
        // @TODO: UIDVALIDITY checking

        return $result;
    }


    /**
     * Stores folder statistic data in session
     * @TODO: move to separate DB table (cache?)
     *
     * @param string $folder  Folder name
     * @param string $name    Data name
     * @param mixed  $data    Data value
     */
    protected function set_folder_stats($folder, $name, $data)
    {
        $_SESSION['folders'][$folder][$name] = $data;
    }


    /**
     * Gets folder statistic data
     *
     * @param string $folder Folder name
     *
     * @return array Stats data
     */
    protected function get_folder_stats($folder)
    {
        if ($_SESSION['folders'][$folder]) {
            return (array) $_SESSION['folders'][$folder];
        }

        return array();
    }


    /**
     * Return sorted list of message UIDs
     *
     * @param string $folder     Folder to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     *
     * @return rcube_result_index|rcube_result_thread List of messages (UIDs)
     */
    public function index($folder = '', $sort_field = NULL, $sort_order = NULL)
    {
        if ($this->threading) {
            return $this->thread_index($folder, $sort_field, $sort_order);
        }

        $this->set_sort_order($sort_field, $sort_order);

        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        // we have a saved search result, get index from there
        if ($this->search_string) {
            if ($this->search_threads) {
                $this->search($folder, $this->search_string, $this->search_charset, $this->sort_field);
            }

            // use message index sort as default sorting
            if (!$this->sort_field || $this->search_sorted) {
                if ($this->sort_field && $this->search_sort_field != $this->sort_field) {
                    $this->search($folder, $this->search_string, $this->search_charset, $this->sort_field);
                }
                $index = $this->search_set;
            }
            else if (!$this->check_connection()) {
                return new rcube_result_index();
            }
            else {
                $index = $this->conn->index($folder, $this->search_set->get(),
                    $this->sort_field, $this->options['skip_deleted'], true, true);
            }

            if ($this->sort_order != $index->get_parameters('ORDER')) {
                $index->revert();
            }

            return $index;
        }

        // check local cache
        if ($mcache = $this->get_mcache_engine()) {
            $index = $mcache->get_index($folder, $this->sort_field, $this->sort_order);
        }
        // fetch from IMAP server
        else {
            $index = $this->index_direct(
                $folder, $this->sort_field, $this->sort_order);
        }

        return $index;
    }


    /**
     * Return sorted list of message UIDs ignoring current search settings.
     * Doesn't uses cache by default.
     *
     * @param string $folder     Folder to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     * @param bool   $skip_cache Disables cache usage
     *
     * @return rcube_result_index Sorted list of message UIDs
     */
    public function index_direct($folder, $sort_field = null, $sort_order = null, $skip_cache = true)
    {
        if (!$skip_cache && ($mcache = $this->get_mcache_engine())) {
            $index = $mcache->get_index($folder, $sort_field, $sort_order);
        }
        // use message index sort as default sorting
        else if (!$sort_field) {
            // use search result from count() if possible
            if ($this->options['skip_deleted'] && !empty($this->icache['undeleted_idx'])
                && $this->icache['undeleted_idx']->get_parameters('ALL') !== null
                && $this->icache['undeleted_idx']->get_parameters('MAILBOX') == $folder
            ) {
                $index = $this->icache['undeleted_idx'];
            }
            else if (!$this->check_connection()) {
                return new rcube_result_index();
            }
            else {
                $index = $this->conn->search($folder,
                    'ALL' .($this->options['skip_deleted'] ? ' UNDELETED' : ''), true);
            }
        }
        else if (!$this->check_connection()) {
            return new rcube_result_index();
        }
        // fetch complete message index
        else {
            if ($this->get_capability('SORT')) {
                $index = $this->conn->sort($folder, $sort_field,
                    $this->options['skip_deleted'] ? 'UNDELETED' : '', true);
            }

            if (empty($index) || $index->is_error()) {
                $index = $this->conn->index($folder, "1:*", $sort_field,
                    $this->options['skip_deleted'], false, true);
            }
        }

        if ($sort_order != $index->get_parameters('ORDER')) {
            $index->revert();
        }

        return $index;
    }


    /**
     * Return index of threaded message UIDs
     *
     * @param string $folder     Folder to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     *
     * @return rcube_result_thread Message UIDs
     */
    public function thread_index($folder='', $sort_field=NULL, $sort_order=NULL)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        // we have a saved search result, get index from there
        if ($this->search_string && $this->search_threads && $folder == $this->folder) {
            $threads = $this->search_set;
        }
        else {
            // get all threads (default sort order)
            $threads = $this->fetch_threads($folder);
        }

        $this->set_sort_order($sort_field, $sort_order);
        $this->sort_threads($threads);

        return $threads;
    }


    /**
     * Sort threaded result, using THREAD=REFS method
     *
     * @param rcube_result_thread $threads  Threads result set
     */
    protected function sort_threads($threads)
    {
        if ($threads->is_empty()) {
            return;
        }

        // THREAD=ORDEREDSUBJECT: sorting by sent date of root message
        // THREAD=REFERENCES:     sorting by sent date of root message
        // THREAD=REFS:           sorting by the most recent date in each thread

        if ($this->sort_field && ($this->sort_field != 'date' || $this->get_capability('THREAD') != 'REFS')) {
            $index = $this->index_direct($this->folder, $this->sort_field, $this->sort_order, false);

            if (!$index->is_empty()) {
                $threads->sort($index);
            }
        }
        else {
            if ($this->sort_order != $threads->get_parameters('ORDER')) {
                $threads->revert();
            }
        }
    }


    /**
     * Invoke search request to IMAP server
     *
     * @param  string  $folder     Folder name to search in
     * @param  string  $str        Search criteria
     * @param  string  $charset    Search charset
     * @param  string  $sort_field Header field to sort by
     *
     * @todo: Search criteria should be provided in non-IMAP format, eg. array
     */
    public function search($folder='', $str='ALL', $charset=NULL, $sort_field=NULL)
    {
        if (!$str) {
            $str = 'ALL';
        }

        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        $results = $this->search_index($folder, $str, $charset, $sort_field);

        $this->set_search_set(array($str, $results, $charset, $sort_field,
            $this->threading || $this->search_sorted ? true : false));
    }


    /**
     * Direct (real and simple) SEARCH request (without result sorting and caching).
     *
     * @param  string  $mailbox Mailbox name to search in
     * @param  string  $str     Search string
     *
     * @return rcube_result_index  Search result (UIDs)
     */
    public function search_once($folder = null, $str = 'ALL')
    {
        if (!$str) {
            return 'ALL';
        }

        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        if (!$this->check_connection()) {
            return new rcube_result_index();
        }

        $index = $this->conn->search($folder, $str, true);

        return $index;
    }


    /**
     * protected search method
     *
     * @param string $folder     Folder name
     * @param string $criteria   Search criteria
     * @param string $charset    Charset
     * @param string $sort_field Sorting field
     *
     * @return rcube_result_index|rcube_result_thread  Search results (UIDs)
     * @see rcube_imap::search()
     */
    protected function search_index($folder, $criteria='ALL', $charset=NULL, $sort_field=NULL)
    {
        $orig_criteria = $criteria;

        if (!$this->check_connection()) {
            if ($this->threading) {
                return new rcube_result_thread();
            }
            else {
                return new rcube_result_index();
            }
        }

        if ($this->options['skip_deleted'] && !preg_match('/UNDELETED/', $criteria)) {
            $criteria = 'UNDELETED '.$criteria;
        }

        // unset CHARSET if criteria string is ASCII, this way
        // SEARCH won't be re-sent after "unsupported charset" response
        if ($charset && $charset != 'US-ASCII' && is_ascii($criteria)) {
            $charset = 'US-ASCII';
        }

        if ($this->threading) {
            $threads = $this->conn->thread($folder, $this->threading, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen that Courier doesn't support UTF-8)
            if ($threads->is_error() && $charset && $charset != 'US-ASCII') {
                $threads = $this->conn->thread($folder, $this->threading,
                    $this->convert_criteria($criteria, $charset), true, 'US-ASCII');
            }

            return $threads;
        }

        if ($sort_field && $this->get_capability('SORT')) {
            $charset  = $charset ? $charset : $this->default_charset;
            $messages = $this->conn->sort($folder, $sort_field, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen Courier with disabled UTF-8 support)
            if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
                $messages = $this->conn->sort($folder, $sort_field,
                    $this->convert_criteria($criteria, $charset), true, 'US-ASCII');
            }

            if (!$messages->is_error()) {
                $this->search_sorted = true;
                return $messages;
            }
        }

        $messages = $this->conn->search($folder,
            ($charset && $charset != 'US-ASCII' ? "CHARSET $charset " : '') . $criteria, true);

        // Error, try with US-ASCII (some servers may support only US-ASCII)
        if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
            $messages = $this->conn->search($folder,
                $this->convert_criteria($criteria, $charset), true);
        }

        $this->search_sorted = false;

        return $messages;
    }


    /**
     * Converts charset of search criteria string
     *
     * @param  string  $str          Search string
     * @param  string  $charset      Original charset
     * @param  string  $dest_charset Destination charset (default US-ASCII)
     *
     * @return string  Search string
     */
    protected function convert_criteria($str, $charset, $dest_charset='US-ASCII')
    {
        // convert strings to US_ASCII
        if (preg_match_all('/\{([0-9]+)\}\r\n/', $str, $matches, PREG_OFFSET_CAPTURE)) {
            $last = 0; $res = '';
            foreach ($matches[1] as $m) {
                $string_offset = $m[1] + strlen($m[0]) + 4; // {}\r\n
                $string = substr($str, $string_offset - 1, $m[0]);
                $string = rcube_charset::convert($string, $charset, $dest_charset);
                if ($string === false) {
                    continue;
                }
                $res .= substr($str, $last, $m[1] - $last - 1) . rcube_imap_generic::escape($string);
                $last = $m[0] + $string_offset - 1;
            }
            if ($last < strlen($str)) {
                $res .= substr($str, $last, strlen($str)-$last);
            }
        }
        // strings for conversion not found
        else {
            $res = $str;
        }

        return $res;
    }


    /**
     * Refresh saved search set
     *
     * @return array Current search set
     */
    public function refresh_search()
    {
        if (!empty($this->search_string)) {
            $this->search('', $this->search_string, $this->search_charset, $this->search_sort_field);
        }

        return $this->get_search_set();
    }


    /**
     * Return message headers object of a specific message
     *
     * @param int     $id       Message UID
     * @param string  $folder   Folder to read from
     * @param bool    $force    True to skip cache
     *
     * @return rcube_message_header Message headers
     */
    public function get_message_headers($uid, $folder = null, $force = false)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        // get cached headers
        if (!$force && $uid && ($mcache = $this->get_mcache_engine())) {
            $headers = $mcache->get_message($folder, $uid);
        }
        else if (!$this->check_connection()) {
            $headers = false;
        }
        else {
            $headers = $this->conn->fetchHeader(
                $folder, $uid, true, true, $this->get_fetch_headers());
        }

        return $headers;
    }


    /**
     * Fetch message headers and body structure from the IMAP server and build
     * an object structure similar to the one generated by PEAR::Mail_mimeDecode
     *
     * @param int     $uid      Message UID to fetch
     * @param string  $folder   Folder to read from
     *
     * @return object rcube_message_header Message data
     */
    public function get_message($uid, $folder = null)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        // Check internal cache
        if (!empty($this->icache['message'])) {
            if (($headers = $this->icache['message']) && $headers->uid == $uid) {
                return $headers;
            }
        }

        $headers = $this->get_message_headers($uid, $folder);

        // message doesn't exist?
        if (empty($headers)) {
            return null;
        }

        // structure might be cached
        if (!empty($headers->structure)) {
            return $headers;
        }

        $this->msg_uid = $uid;

        if (!$this->check_connection()) {
            return $headers;
        }

        if (empty($headers->bodystructure)) {
            $headers->bodystructure = $this->conn->getStructure($folder, $uid, true);
        }

        $structure = $headers->bodystructure;

        if (empty($structure)) {
            return $headers;
        }

        // set message charset from message headers
        if ($headers->charset) {
            $this->struct_charset = $headers->charset;
        }
        else {
            $this->struct_charset = $this->structure_charset($structure);
        }

        $headers->ctype = strtolower($headers->ctype);

        // Here we can recognize malformed BODYSTRUCTURE and
        // 1. [@TODO] parse the message in other way to create our own message structure
        // 2. or just show the raw message body.
        // Example of structure for malformed MIME message:
        // ("text" "plain" NIL NIL NIL "7bit" 2154 70 NIL NIL NIL)
        if ($headers->ctype && !is_array($structure[0]) && $headers->ctype != 'text/plain'
            && strtolower($structure[0].'/'.$structure[1]) == 'text/plain'
        ) {
            // A special known case "Content-type: text" (#1488968)
            if ($headers->ctype == 'text') {
                $structure[1]   = 'plain';
                $headers->ctype = 'text/plain';
            }
            // we can handle single-part messages, by simple fix in structure (#1486898)
            else if (preg_match('/^(text|application)\/(.*)/', $headers->ctype, $m)) {
                $structure[0] = $m[1];
                $structure[1] = $m[2];
            }
            else {
                // Try to parse the message using Mail_mimeDecode package
                // We need a better solution, Mail_mimeDecode parses message
                // in memory, which wouldn't work for very big messages,
                // (it uses up to 10x more memory than the message size)
                // it's also buggy and not actively developed
                if ($headers->size && rcube_utils::mem_check($headers->size * 10)) {
                    $raw_msg = $this->get_raw_body($uid);
                    $struct = rcube_mime::parse_message($raw_msg);
                }
                else {
                    return $headers;
                }
            }
        }

        if (empty($struct)) {
            $struct = $this->structure_part($structure, 0, '', $headers);
        }

        // some workarounds on simple messages...
        if (empty($struct->parts)) {
            // ...don't trust given content-type
            if (!empty($headers->ctype)) {
                $struct->mime_id  = '1';
                $struct->mimetype = strtolower($headers->ctype);
                list($struct->ctype_primary, $struct->ctype_secondary) = explode('/', $struct->mimetype);
            }

            // ...and charset (there's a case described in #1488968 where invalid content-type
            // results in invalid charset in BODYSTRUCTURE)
            if (!empty($headers->charset) && $headers->charset != $struct->ctype_parameters['charset']) {
                $struct->charset                     = $headers->charset;
                $struct->ctype_parameters['charset'] = $headers->charset;
            }
        }

        $headers->structure = $struct;

        return $this->icache['message'] = $headers;
    }


    /**
     * Build message part object
     *
     * @param array  $part
     * @param int    $count
     * @param string $parent
     */
    protected function structure_part($part, $count=0, $parent='', $mime_headers=null)
    {
        $struct = new rcube_message_part;
        $struct->mime_id = empty($parent) ? (string)$count : "$parent.$count";

        // multipart
        if (is_array($part[0])) {
            $struct->ctype_primary = 'multipart';

        /* RFC3501: BODYSTRUCTURE fields of multipart part
            part1 array
            part2 array
            part3 array
            ....
            1. subtype
            2. parameters (optional)
            3. description (optional)
            4. language (optional)
            5. location (optional)
        */

            // find first non-array entry
            for ($i=1; $i<count($part); $i++) {
                if (!is_array($part[$i])) {
                    $struct->ctype_secondary = strtolower($part[$i]);
                    break;
                }
            }

            $struct->mimetype = 'multipart/'.$struct->ctype_secondary;

            // build parts list for headers pre-fetching
            for ($i=0; $i<count($part); $i++) {
                if (!is_array($part[$i])) {
                    break;
                }
                // fetch message headers if message/rfc822
                // or named part (could contain Content-Location header)
                if (!is_array($part[$i][0])) {
                    $tmp_part_id = $struct->mime_id ? $struct->mime_id.'.'.($i+1) : $i+1;
                    if (strtolower($part[$i][0]) == 'message' && strtolower($part[$i][1]) == 'rfc822') {
                        $mime_part_headers[] = $tmp_part_id;
                    }
                    else if (in_array('name', (array)$part[$i][2]) && empty($part[$i][3])) {
                        $mime_part_headers[] = $tmp_part_id;
                    }
                }
            }

            // pre-fetch headers of all parts (in one command for better performance)
            // @TODO: we could do this before _structure_part() call, to fetch
            // headers for parts on all levels
            if ($mime_part_headers) {
                $mime_part_headers = $this->conn->fetchMIMEHeaders($this->folder,
                    $this->msg_uid, $mime_part_headers);
            }

            $struct->parts = array();
            for ($i=0, $count=0; $i<count($part); $i++) {
                if (!is_array($part[$i])) {
                    break;
                }
                $tmp_part_id = $struct->mime_id ? $struct->mime_id.'.'.($i+1) : $i+1;
                $struct->parts[] = $this->structure_part($part[$i], ++$count, $struct->mime_id,
                    $mime_part_headers[$tmp_part_id]);
            }

            return $struct;
        }

        /* RFC3501: BODYSTRUCTURE fields of non-multipart part
            0. type
            1. subtype
            2. parameters
            3. id
            4. description
            5. encoding
            6. size
          -- text
            7. lines
          -- message/rfc822
            7. envelope structure
            8. body structure
            9. lines
          --
            x. md5 (optional)
            x. disposition (optional)
            x. language (optional)
            x. location (optional)
        */

        // regular part
        $struct->ctype_primary = strtolower($part[0]);
        $struct->ctype_secondary = strtolower($part[1]);
        $struct->mimetype = $struct->ctype_primary.'/'.$struct->ctype_secondary;

        // read content type parameters
        if (is_array($part[2])) {
            $struct->ctype_parameters = array();
            for ($i=0; $i<count($part[2]); $i+=2) {
                $struct->ctype_parameters[strtolower($part[2][$i])] = $part[2][$i+1];
            }

            if (isset($struct->ctype_parameters['charset'])) {
                $struct->charset = $struct->ctype_parameters['charset'];
            }
        }

        // #1487700: workaround for lack of charset in malformed structure
        if (empty($struct->charset) && !empty($mime_headers) && $mime_headers->charset) {
            $struct->charset = $mime_headers->charset;
        }

        // read content encoding
        if (!empty($part[5])) {
            $struct->encoding = strtolower($part[5]);
            $struct->headers['content-transfer-encoding'] = $struct->encoding;
        }

        // get part size
        if (!empty($part[6])) {
            $struct->size = intval($part[6]);
        }

        // read part disposition
        $di = 8;
        if ($struct->ctype_primary == 'text') {
            $di += 1;
        }
        else if ($struct->mimetype == 'message/rfc822') {
            $di += 3;
        }

        if (is_array($part[$di]) && count($part[$di]) == 2) {
            $struct->disposition = strtolower($part[$di][0]);

            if (is_array($part[$di][1])) {
                for ($n=0; $n<count($part[$di][1]); $n+=2) {
                    $struct->d_parameters[strtolower($part[$di][1][$n])] = $part[$di][1][$n+1];
                }
            }
        }

        // get message/rfc822's child-parts
        if (is_array($part[8]) && $di != 8) {
            $struct->parts = array();
            for ($i=0, $count=0; $i<count($part[8]); $i++) {
                if (!is_array($part[8][$i])) {
                    break;
                }
                $struct->parts[] = $this->structure_part($part[8][$i], ++$count, $struct->mime_id);
            }
        }

        // get part ID
        if (!empty($part[3])) {
            $struct->content_id = $part[3];
            $struct->headers['content-id'] = $part[3];

            if (empty($struct->disposition)) {
                $struct->disposition = 'inline';
            }
        }

        // fetch message headers if message/rfc822 or named part (could contain Content-Location header)
        if ($struct->ctype_primary == 'message' || ($struct->ctype_parameters['name'] && !$struct->content_id)) {
            if (empty($mime_headers)) {
                $mime_headers = $this->conn->fetchPartHeader(
                    $this->folder, $this->msg_uid, true, $struct->mime_id);
            }

            if (is_string($mime_headers)) {
                $struct->headers = rcube_mime::parse_headers($mime_headers) + $struct->headers;
            }
            else if (is_object($mime_headers)) {
                $struct->headers = get_object_vars($mime_headers) + $struct->headers;
            }

            // get real content-type of message/rfc822
            if ($struct->mimetype == 'message/rfc822') {
                // single-part
                if (!is_array($part[8][0])) {
                    $struct->real_mimetype = strtolower($part[8][0] . '/' . $part[8][1]);
                }
                // multi-part
                else {
                    for ($n=0; $n<count($part[8]); $n++) {
                        if (!is_array($part[8][$n])) {
                            break;
                        }
                    }
                    $struct->real_mimetype = 'multipart/' . strtolower($part[8][$n]);
                }
            }

            if ($struct->ctype_primary == 'message' && empty($struct->parts)) {
                if (is_array($part[8]) && $di != 8) {
                    $struct->parts[] = $this->structure_part($part[8], ++$count, $struct->mime_id);
                }
            }
        }

        // normalize filename property
        $this->set_part_filename($struct, $mime_headers);

        return $struct;
    }


    /**
     * Set attachment filename from message part structure
     *
     * @param  rcube_message_part $part    Part object
     * @param  string             $headers Part's raw headers
     */
    protected function set_part_filename(&$part, $headers=null)
    {
        if (!empty($part->d_parameters['filename'])) {
            $filename_mime = $part->d_parameters['filename'];
        }
        else if (!empty($part->d_parameters['filename*'])) {
            $filename_encoded = $part->d_parameters['filename*'];
        }
        else if (!empty($part->ctype_parameters['name*'])) {
            $filename_encoded = $part->ctype_parameters['name*'];
        }
        // RFC2231 value continuations
        // TODO: this should be rewrited to support RFC2231 4.1 combinations
        else if (!empty($part->d_parameters['filename*0'])) {
            $i = 0;
            while (isset($part->d_parameters['filename*'.$i])) {
                $filename_mime .= $part->d_parameters['filename*'.$i];
                $i++;
            }
            // some servers (eg. dovecot-1.x) have no support for parameter value continuations
            // we must fetch and parse headers "manually"
            if ($i<2) {
                if (!$headers) {
                    $headers = $this->conn->fetchPartHeader(
                        $this->folder, $this->msg_uid, true, $part->mime_id);
                }
                $filename_mime = '';
                $i = 0;
                while (preg_match('/filename\*'.$i.'\s*=\s*"*([^"\n;]+)[";]*/', $headers, $matches)) {
                    $filename_mime .= $matches[1];
                    $i++;
                }
            }
        }
        else if (!empty($part->d_parameters['filename*0*'])) {
            $i = 0;
            while (isset($part->d_parameters['filename*'.$i.'*'])) {
                $filename_encoded .= $part->d_parameters['filename*'.$i.'*'];
                $i++;
            }
            if ($i<2) {
                if (!$headers) {
                    $headers = $this->conn->fetchPartHeader(
                            $this->folder, $this->msg_uid, true, $part->mime_id);
                }
                $filename_encoded = '';
                $i = 0; $matches = array();
                while (preg_match('/filename\*'.$i.'\*\s*=\s*"*([^"\n;]+)[";]*/', $headers, $matches)) {
                    $filename_encoded .= $matches[1];
                    $i++;
                }
            }
        }
        else if (!empty($part->ctype_parameters['name*0'])) {
            $i = 0;
            while (isset($part->ctype_parameters['name*'.$i])) {
                $filename_mime .= $part->ctype_parameters['name*'.$i];
                $i++;
            }
            if ($i<2) {
                if (!$headers) {
                    $headers = $this->conn->fetchPartHeader(
                        $this->folder, $this->msg_uid, true, $part->mime_id);
                }
                $filename_mime = '';
                $i = 0; $matches = array();
                while (preg_match('/\s+name\*'.$i.'\s*=\s*"*([^"\n;]+)[";]*/', $headers, $matches)) {
                    $filename_mime .= $matches[1];
                    $i++;
                }
            }
        }
        else if (!empty($part->ctype_parameters['name*0*'])) {
            $i = 0;
            while (isset($part->ctype_parameters['name*'.$i.'*'])) {
                $filename_encoded .= $part->ctype_parameters['name*'.$i.'*'];
                $i++;
            }
            if ($i<2) {
                if (!$headers) {
                    $headers = $this->conn->fetchPartHeader(
                        $this->folder, $this->msg_uid, true, $part->mime_id);
                }
                $filename_encoded = '';
                $i = 0; $matches = array();
                while (preg_match('/\s+name\*'.$i.'\*\s*=\s*"*([^"\n;]+)[";]*/', $headers, $matches)) {
                    $filename_encoded .= $matches[1];
                    $i++;
                }
            }
        }
        // read 'name' after rfc2231 parameters as it may contains truncated filename (from Thunderbird)
        else if (!empty($part->ctype_parameters['name'])) {
            $filename_mime = $part->ctype_parameters['name'];
        }
        // Content-Disposition
        else if (!empty($part->headers['content-description'])) {
            $filename_mime = $part->headers['content-description'];
        }
        else {
            return;
        }

        // decode filename
        if (!empty($filename_mime)) {
            if (!empty($part->charset)) {
                $charset = $part->charset;
            }
            else if (!empty($this->struct_charset)) {
                $charset = $this->struct_charset;
            }
            else {
                $charset = rcube_charset::detect($filename_mime, $this->default_charset);
            }

            $part->filename = rcube_mime::decode_mime_string($filename_mime, $charset);
        }
        else if (!empty($filename_encoded)) {
            // decode filename according to RFC 2231, Section 4
            if (preg_match("/^([^']*)'[^']*'(.*)$/", $filename_encoded, $fmatches)) {
                $filename_charset = $fmatches[1];
                $filename_encoded = $fmatches[2];
            }

            $part->filename = rcube_charset::convert(urldecode($filename_encoded), $filename_charset);
        }
    }


    /**
     * Get charset name from message structure (first part)
     *
     * @param  array $structure Message structure
     *
     * @return string Charset name
     */
    protected function structure_charset($structure)
    {
        while (is_array($structure)) {
            if (is_array($structure[2]) && $structure[2][0] == 'charset') {
                return $structure[2][1];
            }
            $structure = $structure[0];
        }
    }


    /**
     * Fetch message body of a specific message from the server
     *
     * @param  int                $uid    Message UID
     * @param  string             $part   Part number
     * @param  rcube_message_part $o_part Part object created by get_structure()
     * @param  mixed              $print  True to print part, ressource to write part contents in
     * @param  resource           $fp     File pointer to save the message part
     * @param  boolean            $skip_charset_conv Disables charset conversion
     * @param  int                $max_bytes  Only read this number of bytes
     *
     * @return string Message/part body if not printed
     */
    public function get_message_part($uid, $part=1, $o_part=NULL, $print=NULL, $fp=NULL, $skip_charset_conv=false, $max_bytes=0)
    {
        if (!$this->check_connection()) {
            return null;
        }

        // get part data if not provided
        if (!is_object($o_part)) {
            $structure = $this->conn->getStructure($this->folder, $uid, true);
            $part_data = rcube_imap_generic::getStructurePartData($structure, $part);

            $o_part = new rcube_message_part;
            $o_part->ctype_primary = $part_data['type'];
            $o_part->encoding      = $part_data['encoding'];
            $o_part->charset       = $part_data['charset'];
            $o_part->size          = $part_data['size'];
        }

        if ($o_part && $o_part->size) {
            $body = $this->conn->handlePartBody($this->folder, $uid, true,
                $part ? $part : 'TEXT', $o_part->encoding, $print, $fp, $o_part->ctype_primary == 'text', $max_bytes);
        }

        if ($fp || $print) {
            return true;
        }

        // convert charset (if text or message part)
        if ($body && preg_match('/^(text|message)$/', $o_part->ctype_primary)) {
            // Remove NULL characters if any (#1486189)
            if (strpos($body, "\x00") !== false) {
                $body = str_replace("\x00", '', $body);
            }

            if (!$skip_charset_conv) {
                if (!$o_part->charset || strtoupper($o_part->charset) == 'US-ASCII') {
                    // try to extract charset information from HTML meta tag (#1488125)
                    if ($o_part->ctype_secondary == 'html' && preg_match('/<meta[^>]+charset=([a-z0-9-_]+)/i', $body, $m)) {
                        $o_part->charset = strtoupper($m[1]);
                    }
                    else {
                        $o_part->charset = $this->default_charset;
                    }
                }
                $body = rcube_charset::convert($body, $o_part->charset);
            }
        }

        return $body;
    }


    /**
     * Returns the whole message source as string (or saves to a file)
     *
     * @param int      $uid Message UID
     * @param resource $fp  File pointer to save the message
     *
     * @return string Message source string
     */
    public function get_raw_body($uid, $fp=null)
    {
        if (!$this->check_connection()) {
            return null;
        }

        return $this->conn->handlePartBody($this->folder, $uid,
            true, null, null, false, $fp);
    }


    /**
     * Returns the message headers as string
     *
     * @param int $uid  Message UID
     *
     * @return string Message headers string
     */
    public function get_raw_headers($uid)
    {
        if (!$this->check_connection()) {
            return null;
        }

        return $this->conn->fetchPartHeader($this->folder, $uid, true);
    }


    /**
     * Sends the whole message source to stdout
     *
     * @param int  $uid       Message UID
     * @param bool $formatted Enables line-ending formatting
     */
    public function print_raw_body($uid, $formatted = true)
    {
        if (!$this->check_connection()) {
            return;
        }

        $this->conn->handlePartBody($this->folder, $uid, true, null, null, true, null, $formatted);
    }


    /**
     * Set message flag to one or several messages
     *
     * @param mixed   $uids       Message UIDs as array or comma-separated string, or '*'
     * @param string  $flag       Flag to set: SEEN, UNDELETED, DELETED, RECENT, ANSWERED, DRAFT, MDNSENT
     * @param string  $folder    Folder name
     * @param boolean $skip_cache True to skip message cache clean up
     *
     * @return boolean  Operation status
     */
    public function set_flag($uids, $flag, $folder=null, $skip_cache=false)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        if (!$this->check_connection()) {
            return false;
        }

        $flag = strtoupper($flag);
        list($uids, $all_mode) = $this->parse_uids($uids);

        if (strpos($flag, 'UN') === 0) {
            $result = $this->conn->unflag($folder, $uids, substr($flag, 2));
        }
        else {
            $result = $this->conn->flag($folder, $uids, $flag);
        }

        if ($result && !$skip_cache) {
            // reload message headers if cached
            // update flags instead removing from cache
            if ($mcache = $this->get_mcache_engine()) {
                $status = strpos($flag, 'UN') !== 0;
                $mflag  = preg_replace('/^UN/', '', $flag);
                $mcache->change_flag($folder, $all_mode ? null : explode(',', $uids),
                    $mflag, $status);
            }

            // clear cached counters
            if ($flag == 'SEEN' || $flag == 'UNSEEN') {
                $this->clear_messagecount($folder, 'SEEN');
                $this->clear_messagecount($folder, 'UNSEEN');
            }
            else if ($flag == 'DELETED' || $flag == 'UNDELETED') {
                $this->clear_messagecount($folder, 'DELETED');
                // remove cached messages
                if ($this->options['skip_deleted']) {
                    $this->clear_message_cache($folder, $all_mode ? null : explode(',', $uids));
                }
            }
        }

        return $result;
    }


    /**
     * Append a mail message (source) to a specific folder
     *
     * @param string  $folder  Target folder
     * @param string  $message The message source string or filename
     * @param string  $headers Headers string if $message contains only the body
     * @param boolean $is_file True if $message is a filename
     * @param array   $flags   Message flags
     * @param mixed   $date    Message internal date
     * @param bool    $binary  Enables BINARY append
     *
     * @return int|bool Appended message UID or True on success, False on error
     */
    public function save_message($folder, &$message, $headers='', $is_file=false, $flags = array(), $date = null, $binary = false)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        if (!$this->check_connection()) {
            return false;
        }

        // make sure folder exists
        if (!$this->folder_exists($folder)) {
            return false;
        }

        $date = $this->date_format($date);

        if ($is_file) {
            $saved = $this->conn->appendFromFile($folder, $message, $headers, $flags, $date, $binary);
        }
        else {
            $saved = $this->conn->append($folder, $message, $flags, $date, $binary);
        }

        if ($saved) {
            // increase messagecount of the target folder
            $this->set_messagecount($folder, 'ALL', 1);
        }

        return $saved;
    }


    /**
     * Move a message from one folder to another
     *
     * @param mixed  $uids      Message UIDs as array or comma-separated string, or '*'
     * @param string $to_mbox   Target folder
     * @param string $from_mbox Source folder
     *
     * @return boolean True on success, False on error
     */
    public function move_message($uids, $to_mbox, $from_mbox='')
    {
        if (!strlen($from_mbox)) {
            $from_mbox = $this->folder;
        }

        if ($to_mbox === $from_mbox) {
            return false;
        }

        list($uids, $all_mode) = $this->parse_uids($uids);

        // exit if no message uids are specified
        if (empty($uids)) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        // make sure folder exists
        if ($to_mbox != 'INBOX' && !$this->folder_exists($to_mbox)) {
            if (in_array($to_mbox, $this->default_folders)) {
                if (!$this->create_folder($to_mbox, true)) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        $config = rcube::get_instance()->config;
        $to_trash = $to_mbox == $config->get('trash_mbox');

        // flag messages as read before moving them
        if ($to_trash && $config->get('read_when_deleted')) {
            // don't flush cache (4th argument)
            $this->set_flag($uids, 'SEEN', $from_mbox, true);
        }

        // move messages
        $moved = $this->conn->move($uids, $from_mbox, $to_mbox);

        // send expunge command in order to have the moved message
        // really deleted from the source folder
        if ($moved) {
            $this->expunge_message($uids, $from_mbox, false);
            $this->clear_messagecount($from_mbox);
            $this->clear_messagecount($to_mbox);
        }
        // moving failed
        else if ($to_trash && $config->get('delete_always', false)) {
            $moved = $this->delete_message($uids, $from_mbox);
        }

        if ($moved) {
            // unset threads internal cache
            unset($this->icache['threads']);

            // remove message ids from search set
            if ($this->search_set && $from_mbox == $this->folder) {
                // threads are too complicated to just remove messages from set
                if ($this->search_threads || $all_mode) {
                    $this->refresh_search();
                }
                else {
                    $this->search_set->filter(explode(',', $uids));
                }
            }

            // remove cached messages
            // @TODO: do cache update instead of clearing it
            $this->clear_message_cache($from_mbox, $all_mode ? null : explode(',', $uids));
        }

        return $moved;
    }


    /**
     * Copy a message from one folder to another
     *
     * @param mixed  $uids      Message UIDs as array or comma-separated string, or '*'
     * @param string $to_mbox   Target folder
     * @param string $from_mbox Source folder
     *
     * @return boolean True on success, False on error
     */
    public function copy_message($uids, $to_mbox, $from_mbox='')
    {
        if (!strlen($from_mbox)) {
            $from_mbox = $this->folder;
        }

        list($uids, $all_mode) = $this->parse_uids($uids);

        // exit if no message uids are specified
        if (empty($uids)) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        // make sure folder exists
        if ($to_mbox != 'INBOX' && !$this->folder_exists($to_mbox)) {
            if (in_array($to_mbox, $this->default_folders)) {
                if (!$this->create_folder($to_mbox, true)) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        // copy messages
        $copied = $this->conn->copy($uids, $from_mbox, $to_mbox);

        if ($copied) {
            $this->clear_messagecount($to_mbox);
        }

        return $copied;
    }


    /**
     * Mark messages as deleted and expunge them
     *
     * @param mixed  $uids    Message UIDs as array or comma-separated string, or '*'
     * @param string $folder  Source folder
     *
     * @return boolean True on success, False on error
     */
    public function delete_message($uids, $folder='')
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        list($uids, $all_mode) = $this->parse_uids($uids);

        // exit if no message uids are specified
        if (empty($uids)) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        $deleted = $this->conn->flag($folder, $uids, 'DELETED');

        if ($deleted) {
            // send expunge command in order to have the deleted message
            // really deleted from the folder
            $this->expunge_message($uids, $folder, false);
            $this->clear_messagecount($folder);
            unset($this->uid_id_map[$folder]);

            // unset threads internal cache
            unset($this->icache['threads']);

            // remove message ids from search set
            if ($this->search_set && $folder == $this->folder) {
                // threads are too complicated to just remove messages from set
                if ($this->search_threads || $all_mode) {
                    $this->refresh_search();
                }
                else {
                    $this->search_set->filter(explode(',', $uids));
                }
            }

            // remove cached messages
            $this->clear_message_cache($folder, $all_mode ? null : explode(',', $uids));
        }

        return $deleted;
    }


    /**
     * Send IMAP expunge command and clear cache
     *
     * @param mixed   $uids        Message UIDs as array or comma-separated string, or '*'
     * @param string  $folder      Folder name
     * @param boolean $clear_cache False if cache should not be cleared
     *
     * @return boolean True on success, False on failure
     */
    public function expunge_message($uids, $folder = null, $clear_cache = true)
    {
        if ($uids && $this->get_capability('UIDPLUS')) {
            list($uids, $all_mode) = $this->parse_uids($uids);
        }
        else {
            $uids = null;
        }

        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        if (!$this->check_connection()) {
            return false;
        }

        // force folder selection and check if folder is writeable
        // to prevent a situation when CLOSE is executed on closed
        // or EXPUNGE on read-only folder
        $result = $this->conn->select($folder);
        if (!$result) {
            return false;
        }

        if (!$this->conn->data['READ-WRITE']) {
            $this->conn->setError(rcube_imap_generic::ERROR_READONLY, "Folder is read-only");
            return false;
        }

        // CLOSE(+SELECT) should be faster than EXPUNGE
        if (empty($uids) || $all_mode) {
            $result = $this->conn->close();
        }
        else {
            $result = $this->conn->expunge($folder, $uids);
        }

        if ($result && $clear_cache) {
            $this->clear_message_cache($folder, $all_mode ? null : explode(',', $uids));
            $this->clear_messagecount($folder);
        }

        return $result;
    }


    /* --------------------------------
     *        folder managment
     * --------------------------------*/

    /**
     * Public method for listing subscribed folders.
     *
     * @param   string  $root      Optional root folder
     * @param   string  $name      Optional name pattern
     * @param   string  $filter    Optional filter
     * @param   string  $rights    Optional ACL requirements
     * @param   bool    $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return  array   List of folders
     */
    public function list_folders_subscribed($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
    {
        $cache_key = $root.':'.$name;
        if (!empty($filter)) {
            $cache_key .= ':'.(is_string($filter) ? $filter : serialize($filter));
        }
        $cache_key .= ':'.$rights;
        $cache_key = 'mailboxes.'.md5($cache_key);

        // get cached folder list
        $a_mboxes = $this->get_cache($cache_key);
        if (is_array($a_mboxes)) {
            return $a_mboxes;
        }

        // Give plugins a chance to provide a list of folders
        $data = rcube::get_instance()->plugins->exec_hook('storage_folders',
            array('root' => $root, 'name' => $name, 'filter' => $filter, 'mode' => 'LSUB'));

        if (isset($data['folders'])) {
            $a_mboxes = $data['folders'];
        }
        else {
            $a_mboxes = $this->list_folders_subscribed_direct($root, $name);
        }

        if (!is_array($a_mboxes)) {
            return array();
        }

        // filter folders list according to rights requirements
        if ($rights && $this->get_capability('ACL')) {
            $a_mboxes = $this->filter_rights($a_mboxes, $rights);
        }

        // INBOX should always be available
        if ((!$filter || $filter == 'mail') && !in_array('INBOX', $a_mboxes)) {
            array_unshift($a_mboxes, 'INBOX');
        }

        // sort folders (always sort for cache)
        if (!$skip_sort || $this->cache) {
            $a_mboxes = $this->sort_folder_list($a_mboxes);
        }

        // write folders list to cache
        $this->update_cache($cache_key, $a_mboxes);

        return $a_mboxes;
    }


    /**
     * Method for direct folders listing (LSUB)
     *
     * @param   string  $root   Optional root folder
     * @param   string  $name   Optional name pattern
     *
     * @return  array   List of subscribed folders
     * @see     rcube_imap::list_folders_subscribed()
     */
    public function list_folders_subscribed_direct($root='', $name='*')
    {
        if (!$this->check_connection()) {
           return null;
        }

        $config = rcube::get_instance()->config;

        // Server supports LIST-EXTENDED, we can use selection options
        // #1486225: Some dovecot versions returns wrong result using LIST-EXTENDED
        $list_extended = !$config->get('imap_force_lsub') && $this->get_capability('LIST-EXTENDED');
        if ($list_extended) {
            // This will also set folder options, LSUB doesn't do that
            $a_folders = $this->conn->listMailboxes($root, $name,
                NULL, array('SUBSCRIBED'));
        }
        else {
            // retrieve list of folders from IMAP server using LSUB
            $a_folders = $this->conn->listSubscribed($root, $name);
        }

        if (!is_array($a_folders)) {
            return array();
        }

        // #1486796: some server configurations doesn't return folders in all namespaces
        if ($root == '' && $name == '*' && $config->get('imap_force_ns')) {
            $this->list_folders_update($a_folders, ($list_extended ? 'ext-' : '') . 'subscribed');
        }

        if ($list_extended) {
            // unsubscribe non-existent folders, remove from the list
            // we can do this only when LIST response is available
            if (is_array($a_folders) && $name == '*' && !empty($this->conn->data['LIST'])) {
                foreach ($a_folders as $idx => $folder) {
                    if (($opts = $this->conn->data['LIST'][$folder])
                        && in_array('\\NonExistent', $opts)
                    ) {
                        $this->conn->unsubscribe($folder);
                        unset($a_folders[$idx]);
                    }
                }
            }
        }
        else {
            // unsubscribe non-existent folders, remove them from the list,
            // we can do this only when LIST response is available
            if (is_array($a_folders) && $name == '*' && !empty($this->conn->data['LIST'])) {
                foreach ($a_folders as $idx => $folder) {
                    if (!isset($this->conn->data['LIST'][$folder])
                        || in_array('\\Noselect', $this->conn->data['LIST'][$folder])
                    ) {
                        // Some servers returns \Noselect for existing folders
                        if (!$this->folder_exists($folder)) {
                            $this->conn->unsubscribe($folder);
                            unset($a_folders[$idx]);
                        }
                    }
                }
            }
        }

        return $a_folders;
    }


    /**
     * Get a list of all folders available on the server
     *
     * @param string  $root      IMAP root dir
     * @param string  $name      Optional name pattern
     * @param mixed   $filter    Optional filter
     * @param string  $rights    Optional ACL requirements
     * @param bool    $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return array Indexed array with folder names
     */
    public function list_folders($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
    {
        $cache_key = $root.':'.$name;
        if (!empty($filter)) {
            $cache_key .= ':'.(is_string($filter) ? $filter : serialize($filter));
        }
        $cache_key .= ':'.$rights;
        $cache_key = 'mailboxes.list.'.md5($cache_key);

        // get cached folder list
        $a_mboxes = $this->get_cache($cache_key);
        if (is_array($a_mboxes)) {
            return $a_mboxes;
        }

        // Give plugins a chance to provide a list of folders
        $data = rcube::get_instance()->plugins->exec_hook('storage_folders',
            array('root' => $root, 'name' => $name, 'filter' => $filter, 'mode' => 'LIST'));

        if (isset($data['folders'])) {
            $a_mboxes = $data['folders'];
        }
        else {
            // retrieve list of folders from IMAP server
            $a_mboxes = $this->list_folders_direct($root, $name);
        }

        if (!is_array($a_mboxes)) {
            $a_mboxes = array();
        }

        // INBOX should always be available
        if ((!$filter || $filter == 'mail') && !in_array('INBOX', $a_mboxes)) {
            array_unshift($a_mboxes, 'INBOX');
        }

        // cache folder attributes
        if ($root == '' && $name == '*' && empty($filter) && !empty($this->conn->data)) {
            $this->update_cache('mailboxes.attributes', $this->conn->data['LIST']);
        }

        // filter folders list according to rights requirements
        if ($rights && $this->get_capability('ACL')) {
            $a_mboxes = $this->filter_rights($a_mboxes, $rights);
        }

        // filter folders and sort them
        if (!$skip_sort) {
            $a_mboxes = $this->sort_folder_list($a_mboxes);
        }

        // write folders list to cache
        $this->update_cache($cache_key, $a_mboxes);

        return $a_mboxes;
    }


    /**
     * Method for direct folders listing (LIST)
     *
     * @param   string  $root   Optional root folder
     * @param   string  $name   Optional name pattern
     *
     * @return  array   List of folders
     * @see     rcube_imap::list_folders()
     */
    public function list_folders_direct($root='', $name='*')
    {
        if (!$this->check_connection()) {
            return null;
        }

        $result = $this->conn->listMailboxes($root, $name);

        if (!is_array($result)) {
            return array();
        }

        $config = rcube::get_instance()->config;

        // #1486796: some server configurations doesn't return folders in all namespaces
        if ($root == '' && $name == '*' && $config->get('imap_force_ns')) {
            $this->list_folders_update($result);
        }

        return $result;
    }


    /**
     * Fix folders list by adding folders from other namespaces.
     * Needed on some servers eg. Courier IMAP
     *
     * @param array  $result  Reference to folders list
     * @param string $type    Listing type (ext-subscribed, subscribed or all)
     */
    private function list_folders_update(&$result, $type = null)
    {
        $delim     = $this->get_hierarchy_delimiter();
        $namespace = $this->get_namespace();
        $search    = array();

        // build list of namespace prefixes
        foreach ((array)$namespace as $ns) {
            if (is_array($ns)) {
                foreach ($ns as $ns_data) {
                    if (strlen($ns_data[0])) {
                        $search[] = $ns_data[0];
                    }
                }
            }
        }

        if (!empty($search)) {
            // go through all folders detecting namespace usage
            foreach ($result as $folder) {
                foreach ($search as $idx => $prefix) {
                    if (strpos($folder, $prefix) === 0) {
                        unset($search[$idx]);
                    }
                }
                if (empty($search)) {
                    break;
                }
            }

            // get folders in hidden namespaces and add to the result
            foreach ($search as $prefix) {
                if ($type == 'ext-subscribed') {
                    $list = $this->conn->listMailboxes('', $prefix . '*', null, array('SUBSCRIBED'));
                }
                else if ($type == 'subscribed') {
                    $list = $this->conn->listSubscribed('', $prefix . '*');
                }
                else {
                    $list = $this->conn->listMailboxes('', $prefix . '*');
                }

                if (!empty($list)) {
                    $result = array_merge($result, $list);
                }
            }
        }
    }


    /**
     * Filter the given list of folders according to access rights
     */
    protected function filter_rights($a_folders, $rights)
    {
        $regex = '/('.$rights.')/';
        foreach ($a_folders as $idx => $folder) {
            $myrights = join('', (array)$this->my_rights($folder));
            if ($myrights !== null && !preg_match($regex, $myrights)) {
                unset($a_folders[$idx]);
            }
        }

        return $a_folders;
    }


    /**
     * Get mailbox quota information
     * added by Nuny
     *
     * @return mixed Quota info or False if not supported
     */
    public function get_quota()
    {
        if ($this->get_capability('QUOTA') && $this->check_connection()) {
            return $this->conn->getQuota();
        }

        return false;
    }


    /**
     * Get folder size (size of all messages in a folder)
     *
     * @param string $folder Folder name
     *
     * @return int Folder size in bytes, False on error
     */
    public function folder_size($folder)
    {
        if (!$this->check_connection()) {
            return 0;
        }

        // @TODO: could we try to use QUOTA here?
        $result = $this->conn->fetchHeaderIndex($folder, '1:*', 'SIZE', false);

        if (is_array($result)) {
            $result = array_sum($result);
        }

        return $result;
    }


    /**
     * Subscribe to a specific folder(s)
     *
     * @param array $folders Folder name(s)
     *
     * @return boolean True on success
     */
    public function subscribe($folders)
    {
        // let this common function do the main work
        return $this->change_subscription($folders, 'subscribe');
    }


    /**
     * Unsubscribe folder(s)
     *
     * @param array $a_mboxes Folder name(s)
     *
     * @return boolean True on success
     */
    public function unsubscribe($folders)
    {
        // let this common function do the main work
        return $this->change_subscription($folders, 'unsubscribe');
    }


    /**
     * Create a new folder on the server and register it in local cache
     *
     * @param string  $folder    New folder name
     * @param boolean $subscribe True if the new folder should be subscribed
     *
     * @return boolean True on success
     */
    public function create_folder($folder, $subscribe=false)
    {
        if (!$this->check_connection()) {
            return false;
        }

        $result = $this->conn->createFolder($folder);

        // try to subscribe it
        if ($result) {
            // clear cache
            $this->clear_cache('mailboxes', true);

            if ($subscribe) {
                $this->subscribe($folder);
            }
        }

        return $result;
    }


    /**
     * Set a new name to an existing folder
     *
     * @param string $folder   Folder to rename
     * @param string $new_name New folder name
     *
     * @return boolean True on success
     */
    public function rename_folder($folder, $new_name)
    {
        if (!strlen($new_name)) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        $delm = $this->get_hierarchy_delimiter();

        // get list of subscribed folders
        if ((strpos($folder, '%') === false) && (strpos($folder, '*') === false)) {
            $a_subscribed = $this->list_folders_subscribed('', $folder . $delm . '*');
            $subscribed   = $this->folder_exists($folder, true);
        }
        else {
            $a_subscribed = $this->list_folders_subscribed();
            $subscribed   = in_array($folder, $a_subscribed);
        }

        $result = $this->conn->renameFolder($folder, $new_name);

        if ($result) {
            // unsubscribe the old folder, subscribe the new one
            if ($subscribed) {
                $this->conn->unsubscribe($folder);
                $this->conn->subscribe($new_name);
            }

            // check if folder children are subscribed
            foreach ($a_subscribed as $c_subscribed) {
                if (strpos($c_subscribed, $folder.$delm) === 0) {
                    $this->conn->unsubscribe($c_subscribed);
                    $this->conn->subscribe(preg_replace('/^'.preg_quote($folder, '/').'/',
                        $new_name, $c_subscribed));

                    // clear cache
                    $this->clear_message_cache($c_subscribed);
                }
            }

            // clear cache
            $this->clear_message_cache($folder);
            $this->clear_cache('mailboxes', true);
        }

        return $result;
    }


    /**
     * Remove folder from server
     *
     * @param string $folder Folder name
     *
     * @return boolean True on success
     */
    function delete_folder($folder)
    {
        $delm = $this->get_hierarchy_delimiter();

        if (!$this->check_connection()) {
            return false;
        }

        // get list of folders
        if ((strpos($folder, '%') === false) && (strpos($folder, '*') === false)) {
            $sub_mboxes = $this->list_folders('', $folder . $delm . '*');
        }
        else {
            $sub_mboxes = $this->list_folders();
        }

        // send delete command to server
        $result = $this->conn->deleteFolder($folder);

        if ($result) {
            // unsubscribe folder
            $this->conn->unsubscribe($folder);

            foreach ($sub_mboxes as $c_mbox) {
                if (strpos($c_mbox, $folder.$delm) === 0) {
                    $this->conn->unsubscribe($c_mbox);
                    if ($this->conn->deleteFolder($c_mbox)) {
                        $this->clear_message_cache($c_mbox);
                    }
                }
            }

            // clear folder-related cache
            $this->clear_message_cache($folder);
            $this->clear_cache('mailboxes', true);
        }

        return $result;
    }


    /**
     * Create all folders specified as default
     */
    public function create_default_folders()
    {
        // create default folders if they do not exist
        foreach ($this->default_folders as $folder) {
            if (!$this->folder_exists($folder)) {
                $this->create_folder($folder, true);
            }
            else if (!$this->folder_exists($folder, true)) {
                $this->subscribe($folder);
            }
        }
    }


    /**
     * Checks if folder exists and is subscribed
     *
     * @param string   $folder       Folder name
     * @param boolean  $subscription Enable subscription checking
     *
     * @return boolean TRUE or FALSE
     */
    public function folder_exists($folder, $subscription=false)
    {
        if ($folder == 'INBOX') {
            return true;
        }

        $key  = $subscription ? 'subscribed' : 'existing';

        if (is_array($this->icache[$key]) && in_array($folder, $this->icache[$key])) {
            return true;
        }

        if (!$this->check_connection()) {
            return false;
        }

        if ($subscription) {
            $a_folders = $this->conn->listSubscribed('', $folder);
        }
        else {
            $a_folders = $this->conn->listMailboxes('', $folder);
        }

        if (is_array($a_folders) && in_array($folder, $a_folders)) {
            $this->icache[$key][] = $folder;
            return true;
        }

        return false;
    }


    /**
     * Returns the namespace where the folder is in
     *
     * @param string $folder Folder name
     *
     * @return string One of 'personal', 'other' or 'shared'
     */
    public function folder_namespace($folder)
    {
        if ($folder == 'INBOX') {
            return 'personal';
        }

        foreach ($this->namespace as $type => $namespace) {
            if (is_array($namespace)) {
                foreach ($namespace as $ns) {
                    if ($len = strlen($ns[0])) {
                        if (($len > 1 && $folder == substr($ns[0], 0, -1))
                            || strpos($folder, $ns[0]) === 0
                        ) {
                            return $type;
                        }
                    }
                }
            }
        }

        return 'personal';
    }


    /**
     * Modify folder name according to namespace.
     * For output it removes prefix of the personal namespace if it's possible.
     * For input it adds the prefix. Use it before creating a folder in root
     * of the folders tree.
     *
     * @param string $folder Folder name
     * @param string $mode    Mode name (out/in)
     *
     * @return string Folder name
     */
    public function mod_folder($folder, $mode = 'out')
    {
        if (!strlen($folder)) {
            return $folder;
        }

        $prefix     = $this->namespace['prefix']; // see set_env()
        $prefix_len = strlen($prefix);

        if (!$prefix_len) {
            return $folder;
        }

        // remove prefix for output
        if ($mode == 'out') {
            if (substr($folder, 0, $prefix_len) === $prefix) {
                return substr($folder, $prefix_len);
            }
        }
        // add prefix for input (e.g. folder creation)
        else {
            return $prefix . $folder;
        }

        return $folder;
    }


    /**
     * Gets folder attributes from LIST response, e.g. \Noselect, \Noinferiors
     *
     * @param string $folder Folder name
     * @param bool   $force   Set to True if attributes should be refreshed
     *
     * @return array Options list
     */
    public function folder_attributes($folder, $force=false)
    {
        // get attributes directly from LIST command
        if (!empty($this->conn->data['LIST']) && is_array($this->conn->data['LIST'][$folder])) {
            $opts = $this->conn->data['LIST'][$folder];
        }
        // get cached folder attributes
        else if (!$force) {
            $opts = $this->get_cache('mailboxes.attributes');
            $opts = $opts[$folder];
        }

        if (!is_array($opts)) {
            if (!$this->check_connection()) {
                return array();
            }

            $this->conn->listMailboxes('', $folder);
            $opts = $this->conn->data['LIST'][$folder];
        }

        return is_array($opts) ? $opts : array();
    }


    /**
     * Gets connection (and current folder) data: UIDVALIDITY, EXISTS, RECENT,
     * PERMANENTFLAGS, UIDNEXT, UNSEEN
     *
     * @param string $folder Folder name
     *
     * @return array Data
     */
    public function folder_data($folder)
    {
        if (!strlen($folder)) {
            $folder = $this->folder !== null ? $this->folder : 'INBOX';
        }

        if ($this->conn->selected != $folder) {
            if (!$this->check_connection()) {
                return array();
            }

            if ($this->conn->select($folder)) {
                $this->folder = $folder;
            }
            else {
                return null;
            }
        }

        $data = $this->conn->data;

        // add (E)SEARCH result for ALL UNDELETED query
        if (!empty($this->icache['undeleted_idx'])
            && $this->icache['undeleted_idx']->get_parameters('MAILBOX') == $folder
        ) {
            $data['UNDELETED'] = $this->icache['undeleted_idx'];
        }

        return $data;
    }


    /**
     * Returns extended information about the folder
     *
     * @param string $folder Folder name
     *
     * @return array Data
     */
    public function folder_info($folder)
    {
        if ($this->icache['options'] && $this->icache['options']['name'] == $folder) {
            return $this->icache['options'];
        }

        // get cached metadata
        $cache_key = 'mailboxes.folder-info.' . $folder;
        $cached = $this->get_cache($cache_key);

        if (is_array($cached)) {
            return $cached;
        }

        $acl       = $this->get_capability('ACL');
        $namespace = $this->get_namespace();
        $options   = array();

        // check if the folder is a namespace prefix
        if (!empty($namespace)) {
            $mbox = $folder . $this->delimiter;
            foreach ($namespace as $ns) {
                if (!empty($ns)) {
                    foreach ($ns as $item) {
                        if ($item[0] === $mbox) {
                            $options['is_root'] = true;
                            break 2;
                        }
                    }
                }
            }
        }
        // check if the folder is other user virtual-root
        if (!$options['is_root'] && !empty($namespace) && !empty($namespace['other'])) {
            $parts = explode($this->delimiter, $folder);
            if (count($parts) == 2) {
                $mbox = $parts[0] . $this->delimiter;
                foreach ($namespace['other'] as $item) {
                    if ($item[0] === $mbox) {
                        $options['is_root'] = true;
                        break;
                    }
                }
            }
        }

        $options['name']       = $folder;
        $options['attributes'] = $this->folder_attributes($folder, true);
        $options['namespace']  = $this->folder_namespace($folder);
        $options['special']    = in_array($folder, $this->default_folders);

        // Set 'noselect' flag
        if (is_array($options['attributes'])) {
            foreach ($options['attributes'] as $attrib) {
                $attrib = strtolower($attrib);
                if ($attrib == '\noselect' || $attrib == '\nonexistent') {
                    $options['noselect'] = true;
                }
            }
        }
        else {
            $options['noselect'] = true;
        }

        // Get folder rights (MYRIGHTS)
        if ($acl && ($rights = $this->my_rights($folder))) {
            $options['rights'] = $rights;
        }

        // Set 'norename' flag
        if (!empty($options['rights'])) {
            $options['norename'] = !in_array('x', $options['rights']) && !in_array('d', $options['rights']);

            if (!$options['noselect']) {
                $options['noselect'] = !in_array('r', $options['rights']);
            }
        }
        else {
            $options['norename'] = $options['is_root'] || $options['namespace'] != 'personal';
        }

        // update caches
        $this->icache['options'] = $options;
        $this->update_cache($cache_key, $options);

        return $options;
    }


    /**
     * Synchronizes messages cache.
     *
     * @param string $folder Folder name
     */
    public function folder_sync($folder)
    {
        if ($mcache = $this->get_mcache_engine()) {
            $mcache->synchronize($folder);
        }
    }


    /**
     * Get message header names for rcube_imap_generic::fetchHeader(s)
     *
     * @return string Space-separated list of header names
     */
    protected function get_fetch_headers()
    {
        if (!empty($this->options['fetch_headers'])) {
            $headers = explode(' ', $this->options['fetch_headers']);
        }
        else {
            $headers = array();
        }

        if ($this->messages_caching || $this->options['all_headers']) {
            $headers = array_merge($headers, $this->all_headers);
        }

        return $headers;
    }


    /* -----------------------------------------
     *   ACL and METADATA/ANNOTATEMORE methods
     * ----------------------------------------*/

    /**
     * Changes the ACL on the specified folder (SETACL)
     *
     * @param string $folder  Folder name
     * @param string $user    User name
     * @param string $acl     ACL string
     *
     * @return boolean True on success, False on failure
     * @since 0.5-beta
     */
    public function set_acl($folder, $user, $acl)
    {
        if (!$this->get_capability('ACL')) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        $this->clear_cache('mailboxes.folder-info.' . $folder);

        return $this->conn->setACL($folder, $user, $acl);
    }


    /**
     * Removes any <identifier,rights> pair for the
     * specified user from the ACL for the specified
     * folder (DELETEACL)
     *
     * @param string $folder  Folder name
     * @param string $user    User name
     *
     * @return boolean True on success, False on failure
     * @since 0.5-beta
     */
    public function delete_acl($folder, $user)
    {
        if (!$this->get_capability('ACL')) {
            return false;
        }

        if (!$this->check_connection()) {
            return false;
        }

        return $this->conn->deleteACL($folder, $user);
    }


    /**
     * Returns the access control list for folder (GETACL)
     *
     * @param string $folder Folder name
     *
     * @return array User-rights array on success, NULL on error
     * @since 0.5-beta
     */
    public function get_acl($folder)
    {
        if (!$this->get_capability('ACL')) {
            return null;
        }

        if (!$this->check_connection()) {
            return null;
        }

        return $this->conn->getACL($folder);
    }


    /**
     * Returns information about what rights can be granted to the
     * user (identifier) in the ACL for the folder (LISTRIGHTS)
     *
     * @param string $folder  Folder name
     * @param string $user    User name
     *
     * @return array List of user rights
     * @since 0.5-beta
     */
    public function list_rights($folder, $user)
    {
        if (!$this->get_capability('ACL')) {
            return null;
        }

        if (!$this->check_connection()) {
            return null;
        }

        return $this->conn->listRights($folder, $user);
    }


    /**
     * Returns the set of rights that the current user has to
     * folder (MYRIGHTS)
     *
     * @param string $folder Folder name
     *
     * @return array MYRIGHTS response on success, NULL on error
     * @since 0.5-beta
     */
    public function my_rights($folder)
    {
        if (!$this->get_capability('ACL')) {
            return null;
        }

        if (!$this->check_connection()) {
            return null;
        }

        return $this->conn->myRights($folder);
    }


    /**
     * Sets IMAP metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entry-value array (use NULL value as NIL)
     *
     * @return boolean True on success, False on failure
     * @since 0.5-beta
     */
    public function set_metadata($folder, $entries)
    {
        if (!$this->check_connection()) {
            return false;
        }

        $this->clear_cache('mailboxes.metadata.', true);

        if ($this->get_capability('METADATA') ||
            (!strlen($folder) && $this->get_capability('METADATA-SERVER'))
        ) {
            return $this->conn->setMetadata($folder, $entries);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            foreach ((array)$entries as $entry => $value) {
                list($ent, $attr) = $this->md2annotate($entry);
                $entries[$entry] = array($ent, $attr, $value);
            }
            return $this->conn->setAnnotation($folder, $entries);
        }

        return false;
    }


    /**
     * Unsets IMAP metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entry names array
     *
     * @return boolean True on success, False on failure
     * @since 0.5-beta
     */
    public function delete_metadata($folder, $entries)
    {
        if (!$this->check_connection()) {
            return false;
        }

        $this->clear_cache('mailboxes.metadata.', true);

        if ($this->get_capability('METADATA') ||
            (!strlen($folder) && $this->get_capability('METADATA-SERVER'))
        ) {
            return $this->conn->deleteMetadata($folder, $entries);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            foreach ((array)$entries as $idx => $entry) {
                list($ent, $attr) = $this->md2annotate($entry);
                $entries[$idx] = array($ent, $attr, NULL);
            }
            return $this->conn->setAnnotation($folder, $entries);
        }

        return false;
    }


    /**
     * Returns IMAP metadata/annotations (GETMETADATA/GETANNOTATION)
     *
     * @param string $folder  Folder name (empty for server metadata)
     * @param array  $entries Entries
     * @param array  $options Command options (with MAXSIZE and DEPTH keys)
     *
     * @return array Metadata entry-value hash array on success, NULL on error
     * @since 0.5-beta
     */
    public function get_metadata($folder, $entries, $options=array())
    {
        $entries = (array)$entries;

        // create cache key
        // @TODO: this is the simplest solution, but we do the same with folders list
        //        maybe we should store data per-entry and merge on request
        sort($options);
        sort($entries);
        $cache_key = 'mailboxes.metadata.' . $folder;
        $cache_key .= '.' . md5(serialize($options).serialize($entries));

        // get cached data
        $cached_data = $this->get_cache($cache_key);

        if (is_array($cached_data)) {
            return $cached_data;
        }

        if (!$this->check_connection()) {
            return null;
        }

        if ($this->get_capability('METADATA') ||
            (!strlen($folder) && $this->get_capability('METADATA-SERVER'))
        ) {
            $res = $this->conn->getMetadata($folder, $entries, $options);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            $queries = array();
            $res     = array();

            // Convert entry names
            foreach ($entries as $entry) {
                list($ent, $attr) = $this->md2annotate($entry);
                $queries[$attr][] = $ent;
            }

            // @TODO: Honor MAXSIZE and DEPTH options
            foreach ($queries as $attrib => $entry) {
                if ($result = $this->conn->getAnnotation($folder, $entry, $attrib)) {
                    $res = array_merge_recursive($res, $result);
                }
            }
        }

        if (isset($res)) {
            $this->update_cache($cache_key, $res);
            return $res;
        }

        return null;
    }


    /**
     * Converts the METADATA extension entry name into the correct
     * entry-attrib names for older ANNOTATEMORE version.
     *
     * @param string $entry Entry name
     *
     * @return array Entry-attribute list, NULL if not supported (?)
     */
    protected function md2annotate($entry)
    {
        if (substr($entry, 0, 7) == '/shared') {
            return array(substr($entry, 7), 'value.shared');
        }
        else if (substr($entry, 0, 8) == '/private') {
            return array(substr($entry, 8), 'value.priv');
        }

        // @TODO: log error
        return null;
    }


    /* --------------------------------
     *   internal caching methods
     * --------------------------------*/

    /**
     * Enable or disable indexes caching
     *
     * @param string $type Cache type (@see rcube::get_cache)
     */
    public function set_caching($type)
    {
        if ($type) {
            $this->caching = $type;
        }
        else {
            if ($this->cache) {
                $this->cache->close();
            }
            $this->cache   = null;
            $this->caching = false;
        }
    }

    /**
     * Getter for IMAP cache object
     */
    protected function get_cache_engine()
    {
        if ($this->caching && !$this->cache) {
            $rcube = rcube::get_instance();
            $ttl = $rcube->config->get('message_cache_lifetime', '10d');
            $this->cache = $rcube->get_cache('IMAP', $this->caching, $ttl);
        }

        return $this->cache;
    }

    /**
     * Returns cached value
     *
     * @param string $key Cache key
     *
     * @return mixed
     */
    public function get_cache($key)
    {
        if ($cache = $this->get_cache_engine()) {
            return $cache->get($key);
        }
    }

    /**
     * Update cache
     *
     * @param string $key  Cache key
     * @param mixed  $data Data
     */
    public function update_cache($key, $data)
    {
        if ($cache = $this->get_cache_engine()) {
            $cache->set($key, $data);
        }
    }

    /**
     * Clears the cache.
     *
     * @param string  $key         Cache key name or pattern
     * @param boolean $prefix_mode Enable it to clear all keys starting
     *                             with prefix specified in $key
     */
    public function clear_cache($key = null, $prefix_mode = false)
    {
        if ($cache = $this->get_cache_engine()) {
            $cache->remove($key, $prefix_mode);
        }
    }

    /**
     * Delete outdated cache entries
     */
    public function expunge_cache()
    {
        if ($this->mcache) {
            $ttl = rcube::get_instance()->config->get('message_cache_lifetime', '10d');
            $this->mcache->expunge($ttl);
        }

        if ($this->cache) {
            $this->cache->expunge();
        }
    }


    /* --------------------------------
     *   message caching methods
     * --------------------------------*/

    /**
     * Enable or disable messages caching
     *
     * @param boolean $set Flag
     */
    public function set_messages_caching($set)
    {
        if ($set) {
            $this->messages_caching = true;
        }
        else {
            if ($this->mcache) {
                $this->mcache->close();
            }
            $this->mcache = null;
            $this->messages_caching = false;
        }
    }


    /**
     * Getter for messages cache object
     */
    protected function get_mcache_engine()
    {
        if ($this->messages_caching && !$this->mcache) {
            $rcube = rcube::get_instance();
            if (($dbh = $rcube->get_dbh()) && ($userid = $rcube->get_user_id())) {
                $this->mcache = new rcube_imap_cache(
                    $dbh, $this, $userid, $this->options['skip_deleted']);
            }
        }

        return $this->mcache;
    }


    /**
     * Clears the messages cache.
     *
     * @param string $folder Folder name
     * @param array  $uids    Optional message UIDs to remove from cache
     */
    protected function clear_message_cache($folder = null, $uids = null)
    {
        if ($mcache = $this->get_mcache_engine()) {
            $mcache->clear($folder, $uids);
        }
    }


    /* --------------------------------
     *         protected methods
     * --------------------------------*/

    /**
     * Validate the given input and save to local properties
     *
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order
     */
    protected function set_sort_order($sort_field, $sort_order)
    {
        if ($sort_field != null) {
            $this->sort_field = asciiwords($sort_field);
        }
        if ($sort_order != null) {
            $this->sort_order = strtoupper($sort_order) == 'DESC' ? 'DESC' : 'ASC';
        }
    }


    /**
     * Sort folders first by default folders and then in alphabethical order
     *
     * @param array $a_folders Folders list
     */
    protected function sort_folder_list($a_folders)
    {
        $a_out = $a_defaults = $folders = array();

        $delimiter = $this->get_hierarchy_delimiter();

        // find default folders and skip folders starting with '.'
        foreach ($a_folders as $i => $folder) {
            if ($folder[0] == '.') {
                continue;
            }

            if (($p = array_search($folder, $this->default_folders)) !== false && !$a_defaults[$p]) {
                $a_defaults[$p] = $folder;
            }
            else {
                $folders[$folder] = rcube_charset::convert($folder, 'UTF7-IMAP');
            }
        }

        // sort folders and place defaults on the top
        asort($folders, SORT_LOCALE_STRING);
        ksort($a_defaults);
        $folders = array_merge($a_defaults, array_keys($folders));

        // finally we must rebuild the list to move
        // subfolders of default folders to their place...
        // ...also do this for the rest of folders because
        // asort() is not properly sorting case sensitive names
        while (list($key, $folder) = each($folders)) {
            // set the type of folder name variable (#1485527)
            $a_out[] = (string) $folder;
            unset($folders[$key]);
            $this->rsort($folder, $delimiter, $folders, $a_out);
        }

        return $a_out;
    }


    /**
     * Recursive method for sorting folders
     */
    protected function rsort($folder, $delimiter, &$list, &$out)
    {
        while (list($key, $name) = each($list)) {
            if (strpos($name, $folder.$delimiter) === 0) {
                // set the type of folder name variable (#1485527)
                $out[] = (string) $name;
                unset($list[$key]);
                $this->rsort($name, $delimiter, $list, $out);
            }
        }
        reset($list);
    }


    /**
     * Find UID of the specified message sequence ID
     *
     * @param int    $id       Message (sequence) ID
     * @param string $folder   Folder name
     *
     * @return int Message UID
     */
    public function id2uid($id, $folder = null)
    {
        if (!strlen($folder)) {
            $folder = $this->folder;
        }

        if ($uid = array_search($id, (array)$this->uid_id_map[$folder])) {
            return $uid;
        }

        if (!$this->check_connection()) {
            return null;
        }

        $uid = $this->conn->ID2UID($folder, $id);

        $this->uid_id_map[$folder][$uid] = $id;

        return $uid;
    }


    /**
     * Subscribe/unsubscribe a list of folders and update local cache
     */
    protected function change_subscription($folders, $mode)
    {
        $updated = false;

        if (!empty($folders)) {
            if (!$this->check_connection()) {
                return false;
            }

            foreach ((array)$folders as $i => $folder) {
                $folders[$i] = $folder;

                if ($mode == 'subscribe') {
                    $updated = $this->conn->subscribe($folder);
                }
                else if ($mode == 'unsubscribe') {
                    $updated = $this->conn->unsubscribe($folder);
                }
            }
        }

        // clear cached folders list(s)
        if ($updated) {
            $this->clear_cache('mailboxes', true);
        }

        return $updated;
    }


    /**
     * Increde/decrese messagecount for a specific folder
     */
    protected function set_messagecount($folder, $mode, $increment)
    {
        if (!is_numeric($increment)) {
            return false;
        }

        $mode = strtoupper($mode);
        $a_folder_cache = $this->get_cache('messagecount');

        if (!is_array($a_folder_cache[$folder]) || !isset($a_folder_cache[$folder][$mode])) {
            return false;
        }

        // add incremental value to messagecount
        $a_folder_cache[$folder][$mode] += $increment;

        // there's something wrong, delete from cache
        if ($a_folder_cache[$folder][$mode] < 0) {
            unset($a_folder_cache[$folder][$mode]);
        }

        // write back to cache
        $this->update_cache('messagecount', $a_folder_cache);

        return true;
    }


    /**
     * Remove messagecount of a specific folder from cache
     */
    protected function clear_messagecount($folder, $mode=null)
    {
        $a_folder_cache = $this->get_cache('messagecount');

        if (is_array($a_folder_cache[$folder])) {
            if ($mode) {
                unset($a_folder_cache[$folder][$mode]);
            }
            else {
                unset($a_folder_cache[$folder]);
            }
            $this->update_cache('messagecount', $a_folder_cache);
        }
    }


    /**
     * Converts date string/object into IMAP date/time format
     */
    protected function date_format($date)
    {
        if (empty($date)) {
            return null;
        }

        if (!is_object($date) || !is_a($date, 'DateTime')) {
            try {
                $timestamp = rcube_utils::strtotime($date);
                $date      = new DateTime("@".$timestamp);
            }
            catch (Exception $e) {
                return null;
            }
        }

        return $date->format('d-M-Y H:i:s O');
    }


    /**
     * This is our own debug handler for the IMAP connection
     * @access public
     */
    public function debug_handler(&$imap, $message)
    {
        rcube::write_log('imap', $message);
    }


    /**
     * Deprecated methods (to be removed)
     */

    public function decode_address_list($input, $max = null, $decode = true, $fallback = null)
    {
        return rcube_mime::decode_address_list($input, $max, $decode, $fallback);
    }

    public function decode_header($input, $fallback = null)
    {
        return rcube_mime::decode_mime_string((string)$input, $fallback);
    }

    public static function decode_mime_string($input, $fallback = null)
    {
        return rcube_mime::decode_mime_string($input, $fallback);
    }

    public function mime_decode($input, $encoding = '7bit')
    {
        return rcube_mime::decode($input, $encoding);
    }

    public static function explode_header_string($separator, $str, $remove_comments = false)
    {
        return rcube_mime::explode_header_string($separator, $str, $remove_comments);
    }

    public function select_mailbox($mailbox)
    {
        // do nothing
    }

    public function set_mailbox($folder)
    {
        $this->set_folder($folder);
    }

    public function get_mailbox_name()
    {
        return $this->get_folder();
    }

    public function list_headers($folder='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $slice=0)
    {
        return $this->list_messages($folder, $page, $sort_field, $sort_order, $slice);
    }

    public function get_headers($uid, $folder = null, $force = false)
    {
        return $this->get_message_headers($uid, $folder, $force);
    }

    public function mailbox_status($folder = null)
    {
        return $this->folder_status($folder);
    }

    public function message_index($folder = '', $sort_field = NULL, $sort_order = NULL)
    {
        return $this->index($folder, $sort_field, $sort_order);
    }

    public function message_index_direct($folder, $sort_field = null, $sort_order = null, $skip_cache = true)
    {
        return $this->index_direct($folder, $sort_field, $sort_order, $skip_cache);
    }

    public function list_mailboxes($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
    {
        return $this->list_folders_subscribed($root, $name, $filter, $rights, $skip_sort);
    }

    public function list_unsubscribed($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
    {
        return $this->list_folders($root, $name, $filter, $rights, $skip_sort);
    }

    public function get_mailbox_size($folder)
    {
        return $this->folder_size($folder);
    }

    public function create_mailbox($folder, $subscribe=false)
    {
        return $this->create_folder($folder, $subscribe);
    }

    public function rename_mailbox($folder, $new_name)
    {
        return $this->rename_folder($folder, $new_name);
    }

    function delete_mailbox($folder)
    {
        return $this->delete_folder($folder);
    }

    function clear_mailbox($folder = null)
    {
        return $this->clear_folder($folder);
    }

    public function mailbox_exists($folder, $subscription=false)
    {
        return $this->folder_exists($folder, $subscription);
    }

    public function mailbox_namespace($folder)
    {
        return $this->folder_namespace($folder);
    }

    public function mod_mailbox($folder, $mode = 'out')
    {
        return $this->mod_folder($folder, $mode);
    }

    public function mailbox_attributes($folder, $force=false)
    {
        return $this->folder_attributes($folder, $force);
    }

    public function mailbox_data($folder)
    {
        return $this->folder_data($folder);
    }

    public function mailbox_info($folder)
    {
        return $this->folder_info($folder);
    }

    public function mailbox_sync($folder)
    {
        return $this->folder_sync($folder);
    }

    public function expunge($folder='', $clear_cache=true)
    {
        return $this->expunge_folder($folder, $clear_cache);
    }

}
