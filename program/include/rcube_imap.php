<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap.php                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   IMAP Engine                                                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Interface class for accessing an IMAP server
 *
 * @package    Mail
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 * @version    2.0
 */
class rcube_imap
{
    public $skip_deleted = false;
    public $page_size = 10;
    public $list_page = 1;
    public $threading = false;
    public $fetch_add_headers = '';
    public $get_all_headers = false;

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
    private $mcache;

    /**
     * Instance of rcube_cache
     *
     * @var rcube_cache
     */
    private $cache;

    /**
     * Internal (in-memory) cache
     *
     * @var array
     */
    private $icache = array();

    private $mailbox = 'INBOX';
    private $delimiter = NULL;
    private $namespace = NULL;
    private $sort_field = '';
    private $sort_order = 'DESC';
    private $default_charset = 'ISO-8859-1';
    private $struct_charset = NULL;
    private $default_folders = array('INBOX');
    private $uid_id_map = array();
    private $msg_headers = array();
    public  $search_set = NULL;
    public  $search_string = '';
    private $search_charset = '';
    private $search_sort_field = '';
    private $search_threads = false;
    private $search_sorted = false;
    private $options = array('auth_method' => 'check');
    private $host, $user, $pass, $port, $ssl;
    private $caching = false;
    private $messages_caching = false;

    /**
     * All (additional) headers used (in any way) by Roundcube
     * Not listed here: DATE, FROM, TO, CC, REPLY-TO, SUBJECT, CONTENT-TYPE, LIST-POST
     * (used for messages listing) are hardcoded in rcube_imap_generic::fetchHeaders()
     *
     * @var array
     * @see rcube_imap::fetch_add_headers
     */
    private $all_headers = array(
        'IN-REPLY-TO',
        'BCC',
        'MESSAGE-ID',
        'CONTENT-TRANSFER-ENCODING',
        'REFERENCES',
        'X-DRAFT-INFO',
        'MAIL-FOLLOWUP-TO',
        'MAIL-REPLY-TO',
        'RETURN-PATH',
    );

    const UNKNOWN       = 0;
    const NOPERM        = 1;
    const READONLY      = 2;
    const TRYCREATE     = 3;
    const INUSE         = 4;
    const OVERQUOTA     = 5;
    const ALREADYEXISTS = 6;
    const NONEXISTENT   = 7;
    const CONTACTADMIN  = 8;


    /**
     * Object constructor.
     */
    function __construct()
    {
        $this->conn = new rcube_imap_generic();

        // Set namespace and delimiter from session,
        // so some methods would work before connection
        if (isset($_SESSION['imap_namespace']))
            $this->namespace = $_SESSION['imap_namespace'];
        if (isset($_SESSION['imap_delimiter']))
            $this->delimiter = $_SESSION['imap_delimiter'];
    }


    /**
     * Connect to an IMAP server
     *
     * @param  string   $host    Host to connect
     * @param  string   $user    Username for IMAP account
     * @param  string   $pass    Password for IMAP account
     * @param  integer  $port    Port to connect to
     * @param  string   $use_ssl SSL schema (either ssl or tls) or null if plain connection
     * @return boolean  TRUE on success, FALSE on failure
     * @access public
     */
    function connect($host, $user, $pass, $port=143, $use_ssl=null)
    {
        // check for OpenSSL support in PHP build
        if ($use_ssl && extension_loaded('openssl'))
            $this->options['ssl_mode'] = $use_ssl == 'imaps' ? 'ssl' : $use_ssl;
        else if ($use_ssl) {
            raise_error(array('code' => 403, 'type' => 'imap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "OpenSSL not available"), true, false);
            $port = 143;
        }

        $this->options['port'] = $port;

        if ($this->options['debug']) {
            $this->conn->setDebug(true, array($this, 'debug_handler'));

            $this->options['ident'] = array(
                'name' => 'Roundcube Webmail',
                'version' => RCMAIL_VERSION,
                'php' => PHP_VERSION,
                'os' => PHP_OS,
                'command' => $_SERVER['REQUEST_URI'],
            );
        }

        $attempt = 0;
        do {
            $data = rcmail::get_instance()->plugins->exec_hook('imap_connect',
                array_merge($this->options, array('host' => $host, 'user' => $user,
                    'attempt' => ++$attempt)));

            if (!empty($data['pass']))
                $pass = $data['pass'];

            $this->conn->connect($data['host'], $data['user'], $pass, $data);
        } while(!$this->conn->connected() && $data['retry']);

        $this->host = $data['host'];
        $this->user = $data['user'];
        $this->pass = $pass;
        $this->port = $port;
        $this->ssl  = $use_ssl;

        if ($this->conn->connected()) {
            // get namespace and delimiter
            $this->set_env();
            return true;
        }
        // write error log
        else if ($this->conn->error) {
            if ($pass && $user) {
                $message = sprintf("Login failed for %s from %s. %s",
                    $user, rcmail_remote_ip(), $this->conn->error);

                raise_error(array('code' => 403, 'type' => 'imap',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => $message), true, false);
            }
        }

        return false;
    }


    /**
     * Close IMAP connection
     * Usually done on script shutdown
     *
     * @access public
     */
    function close()
    {
        $this->conn->closeConnection();
        if ($this->mcache)
            $this->mcache->close();
    }


    /**
     * Close IMAP connection and re-connect
     * This is used to avoid some strange socket errors when talking to Courier IMAP
     *
     * @access public
     */
    function reconnect()
    {
        $this->conn->closeConnection();
        $connected = $this->connect($this->host, $this->user, $this->pass, $this->port, $this->ssl);

        // issue SELECT command to restore connection status
        if ($connected && strlen($this->mailbox))
            $this->conn->select($this->mailbox);
    }


    /**
     * Returns code of last error
     *
     * @return int Error code
     */
    function get_error_code()
    {
        return $this->conn->errornum;
    }


    /**
     * Returns message of last error
     *
     * @return string Error message
     */
    function get_error_str()
    {
        return $this->conn->error;
    }


    /**
     * Returns code of last command response
     *
     * @return int Response code
     */
    function get_response_code()
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
     * Returns last command response
     *
     * @return string Response
     */
    function get_response_str()
    {
        return $this->conn->result;
    }


    /**
     * Set options to be used in rcube_imap_generic::connect()
     *
     * @param array $opt Options array
     */
    function set_options($opt)
    {
        $this->options = array_merge($this->options, (array)$opt);
    }


    /**
     * Activate/deactivate debug mode
     *
     * @param boolean $dbg True if IMAP conversation should be logged
     * @access public
     */
    function set_debug($dbg = true)
    {
        $this->options['debug'] = $dbg;
        $this->conn->setDebug($dbg, array($this, 'debug_handler'));
    }


    /**
     * Set default message charset
     *
     * This will be used for message decoding if a charset specification is not available
     *
     * @param  string $cs Charset string
     * @access public
     */
    function set_charset($cs)
    {
        $this->default_charset = $cs;
    }


    /**
     * This list of folders will be listed above all other folders
     *
     * @param  array $arr Indexed list of folder names
     * @access public
     */
    function set_default_mailboxes($arr)
    {
        if (is_array($arr)) {
            $this->default_folders = $arr;

            // add inbox if not included
            if (!in_array('INBOX', $this->default_folders))
                array_unshift($this->default_folders, 'INBOX');
        }
    }


    /**
     * Set internal mailbox reference.
     *
     * All operations will be perfomed on this mailbox/folder
     *
     * @param  string $mailbox Mailbox/Folder name
     * @access public
     */
    function set_mailbox($mailbox)
    {
        if ($this->mailbox == $mailbox)
            return;

        $this->mailbox = $mailbox;

        // clear messagecount cache for this mailbox
        $this->_clear_messagecount($mailbox);
    }


    /**
     * Forces selection of a mailbox
     *
     * @param  string $mailbox Mailbox/Folder name
     * @access public
     */
    function select_mailbox($mailbox=null)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        $selected = $this->conn->select($mailbox);

        if ($selected && $this->mailbox != $mailbox) {
            // clear messagecount cache for this mailbox
            $this->_clear_messagecount($mailbox);
            $this->mailbox = $mailbox;
        }
    }


    /**
     * Set internal list page
     *
     * @param  number $page Page number to list
     * @access public
     */
    function set_page($page)
    {
        $this->list_page = (int)$page;
    }


    /**
     * Set internal page size
     *
     * @param  number $size Number of messages to display on one page
     * @access public
     */
    function set_pagesize($size)
    {
        $this->page_size = (int)$size;
    }


    /**
     * Save a set of message ids for future message listing methods
     *
     * @param  string  IMAP Search query
     * @param  rcube_result_index|rcube_result_thread  Result set
     * @param  string  Charset of search string
     * @param  string  Sorting field
     * @param  string  True if set is sorted (SORT was used for searching)
     */
    function set_search_set($str=null, $msgs=null, $charset=null, $sort_field=null, $sorted=false)
    {
        if (is_array($str) && $msgs === null)
            list($str, $msgs, $charset, $sort_field, $sorted) = $str;

        $this->search_string     = $str;
        $this->search_set        = $msgs;
        $this->search_charset    = $charset;
        $this->search_sort_field = $sort_field;
        $this->search_sorted     = $sorted;
        $this->search_threads    = is_a($this->search_set, 'rcube_result_thread');
    }


    /**
     * Return the saved search set as hash array
     *
     * @param bool $clone Clone result object
     *
     * @return array Search set
     */
    function get_search_set()
    {
        return array(
            $this->search_string,
	        $this->search_set,
        	$this->search_charset,
        	$this->search_sort_field,
        	$this->search_sorted,
	    );
    }


    /**
     * Returns the currently used mailbox name
     *
     * @return  string Name of the mailbox/folder
     * @access  public
     */
    function get_mailbox_name()
    {
        return $this->mailbox;
    }


    /**
     * Returns the IMAP server's capability
     *
     * @param   string  $cap Capability name
     * @return  mixed   Capability value or TRUE if supported, FALSE if not
     * @access  public
     */
    function get_capability($cap)
    {
        return $this->conn->getCapability(strtoupper($cap));
    }


    /**
     * Sets threading flag to the best supported THREAD algorithm
     *
     * @param  boolean  $enable TRUE to enable and FALSE
     * @return string   Algorithm or false if THREAD is not supported
     * @access public
     */
    function set_threading($enable=false)
    {
        $this->threading = false;

        if ($enable && ($caps = $this->get_capability('THREAD'))) {
            if (in_array('REFS', $caps))
                $this->threading = 'REFS';
            else if (in_array('REFERENCES', $caps))
                $this->threading = 'REFERENCES';
            else if (in_array('ORDEREDSUBJECT', $caps))
                $this->threading = 'ORDEREDSUBJECT';
        }

        return $this->threading;
    }


    /**
     * Checks the PERMANENTFLAGS capability of the current mailbox
     * and returns true if the given flag is supported by the IMAP server
     *
     * @param   string  $flag Permanentflag name
     * @return  boolean True if this flag is supported
     * @access  public
     */
    function check_permflag($flag)
    {
        $flag = strtoupper($flag);
        $imap_flag = $this->conn->flags[$flag];
        return (in_array_nocase($imap_flag, $this->conn->data['PERMANENTFLAGS']));
    }


    /**
     * Returns the delimiter that is used by the IMAP server for folder separation
     *
     * @return  string  Delimiter string
     * @access  public
     */
    function get_hierarchy_delimiter()
    {
        return $this->delimiter;
    }


    /**
     * Get namespace
     *
     * @param string $name Namespace array index: personal, other, shared, prefix
     *
     * @return  array  Namespace data
     * @access  public
     */
    function get_namespace($name=null)
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
     *
     * @access private
     */
    private function set_env()
    {
        if ($this->delimiter !== null && $this->namespace !== null) {
            return;
        }

        $config = rcmail::get_instance()->config;
        $imap_personal  = $config->get('imap_ns_personal');
        $imap_other     = $config->get('imap_ns_other');
        $imap_shared    = $config->get('imap_ns_shared');
        $imap_delimiter = $config->get('imap_delimiter');

        if (!$this->conn->connected())
            return;

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

        // Find personal namespace prefix for mod_mailbox()
        // Prefix can be removed when there is only one personal namespace
        if (is_array($this->namespace['personal']) && count($this->namespace['personal']) == 1) {
            $this->namespace['prefix'] = $this->namespace['personal'][0][0];
        }

        $_SESSION['imap_namespace'] = $this->namespace;
        $_SESSION['imap_delimiter'] = $this->delimiter;
    }


    /**
     * Get message count for a specific mailbox
     *
     * @param  string  $mailbox Mailbox/folder name
     * @param  string  $mode    Mode for count [ALL|THREADS|UNSEEN|RECENT]
     * @param  boolean $force   Force reading from server and update cache
     * @param  boolean $status  Enables storing folder status info (max UID/count),
     *                          required for mailbox_status()
     * @return int     Number of messages
     * @access public
     */
    function messagecount($mailbox='', $mode='ALL', $force=false, $status=true)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        return $this->_messagecount($mailbox, $mode, $force, $status);
    }


    /**
     * Private method for getting nr of messages
     *
     * @param string  $mailbox Mailbox name
     * @param string  $mode    Mode for count [ALL|THREADS|UNSEEN|RECENT]
     * @param boolean $force   Force reading from server and update cache
     * @param boolean $status  Enables storing folder status info (max UID/count),
     *                         required for mailbox_status()
     * @return int Number of messages
     * @access  private
     * @see     rcube_imap::messagecount()
     */
    private function _messagecount($mailbox, $mode='ALL', $force=false, $status=true)
    {
        $mode = strtoupper($mode);

        // count search set
        if ($this->search_string && $mailbox == $this->mailbox && ($mode == 'ALL' || $mode == 'THREADS') && !$force) {
            if ($mode == 'ALL')
                return $this->search_set->countMessages();
            else
                return $this->search_set->count();
        }

        $a_mailbox_cache = $this->get_cache('messagecount');

        // return cached value
        if (!$force && is_array($a_mailbox_cache[$mailbox]) && isset($a_mailbox_cache[$mailbox][$mode]))
            return $a_mailbox_cache[$mailbox][$mode];

        if (!is_array($a_mailbox_cache[$mailbox]))
            $a_mailbox_cache[$mailbox] = array();

        if ($mode == 'THREADS') {
            $res   = $this->fetch_threads($mailbox, $force);
            $count = $res->count();

            if ($status) {
                $msg_count = $res->countMessages();
                $this->set_folder_stats($mailbox, 'cnt', $msg_count);
                $this->set_folder_stats($mailbox, 'maxuid', $msg_count ? $this->id2uid($msg_count, $mailbox) : 0);
            }
        }
        // RECENT count is fetched a bit different
        else if ($mode == 'RECENT') {
            $count = $this->conn->countRecent($mailbox);
        }
        // use SEARCH for message counting
        else if ($this->skip_deleted) {
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
            $index = $this->conn->search($mailbox, $search_str, true, $keys);
            $count = $index->count();

            if ($mode == 'ALL') {
                // Cache index data, will be used in message_index_direct()
                $this->icache['undeleted_idx'] = $index;

                if ($status) {
                    $this->set_folder_stats($mailbox, 'cnt', $count);
                    $this->set_folder_stats($mailbox, 'maxuid', $index->max());
                }
            }
        }
        else {
            if ($mode == 'UNSEEN')
                $count = $this->conn->countUnseen($mailbox);
            else {
                $count = $this->conn->countMessages($mailbox);
                if ($status) {
                    $this->set_folder_stats($mailbox,'cnt', $count);
                    $this->set_folder_stats($mailbox, 'maxuid', $count ? $this->id2uid($count, $mailbox) : 0);
                }
            }
        }

        $a_mailbox_cache[$mailbox][$mode] = (int)$count;

        // write back to cache
        $this->update_cache('messagecount', $a_mailbox_cache);

        return (int)$count;
    }


    /**
     * Public method for listing headers
     * convert mailbox name with root dir first
     *
     * @param   string   $mailbox    Mailbox/folder name
     * @param   int      $page       Current page to list
     * @param   string   $sort_field Header field to sort by
     * @param   string   $sort_order Sort order [ASC|DESC]
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @access  public
     */
    public function list_headers($mailbox='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $slice=0)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        return $this->_list_headers($mailbox, $page, $sort_field, $sort_order, $slice);
    }


    /**
     * Private method for listing message headers
     *
     * @param   string   $mailbox    Mailbox name
     * @param   int      $page       Current page to list
     * @param   string   $sort_field Header field to sort by
     * @param   string   $sort_order Sort order [ASC|DESC]
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @see     rcube_imap::list_headers
     */
    private function _list_headers($mailbox='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $slice=0)
    {
        if (!strlen($mailbox)) {
            return array();
        }

        $this->set_sort_order($sort_field, $sort_order);
        $page = $page ? $page : $this->list_page;

        // use saved message set
        if ($this->search_string && $mailbox == $this->mailbox) {
            return $this->_list_header_set($mailbox, $page, $slice);
        }

        if ($this->threading) {
            return $this->_list_thread_headers($mailbox, $page, $slice);
        }

        // get UIDs of all messages in the folder, sorted
        $index = $this->message_index($mailbox, $this->sort_field, $this->sort_order);

        if ($index->isEmpty()) {
            return array();
        }

        $from = ($page-1) * $this->page_size;
        $to   = $from + $this->page_size;

        $index->slice($from, $to - $from);

        if ($slice)
            $index->slice(-$slice, $slice);

        // fetch reqested messages headers
        $a_index = $index->get();
        $a_msg_headers = $this->fetch_headers($mailbox, $a_index);

        return array_values($a_msg_headers);
    }


    /**
     * Private method for listing message headers using threads
     *
     * @param   string   $mailbox    Mailbox/folder name
     * @param   int      $page       Current page to list
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @see     rcube_imap::list_headers
     */
    private function _list_thread_headers($mailbox, $page, $slice=0)
    {
        // get all threads (not sorted)
        if ($mcache = $this->get_mcache_engine())
            $threads = $mcache->get_thread($mailbox);
        else
            $threads = $this->fetch_threads($mailbox);

        return $this->_fetch_thread_headers($mailbox, $threads, $page, $slice);
    }


    /**
     * Method for fetching threads data
     *
     * @param  string $mailbox  Folder name
     * @param  bool   $force    Use IMAP server, no cache
     *
     * @return rcube_imap_thread Thread data object
     */
    function fetch_threads($mailbox, $force = false)
    {
        if (!$force && ($mcache = $this->get_mcache_engine())) {
            // don't store in self's internal cache, cache has it's own internal cache
            return $mcache->get_thread($mailbox);
        }

        if (empty($this->icache['threads'])) {
            // get all threads
            $result = $this->conn->thread($mailbox, $this->threading,
                $this->skip_deleted ? 'UNDELETED' : '', true);

            // add to internal (fast) cache
            $this->icache['threads'] = $result;
        }

        return $this->icache['threads'];
    }


    /**
     * Private method for fetching threaded messages headers
     *
     * @param string              $mailbox    Mailbox name
     * @param rcube_result_thread $threads    Threads data object
     * @param int                 $page       List page number
     * @param int                 $slice      Number of threads to slice
     *
     * @return array  Messages headers
     * @access  private
     */
    private function _fetch_thread_headers($mailbox, $threads, $page, $slice=0)
    {
        // Sort thread structure
        $this->sort_threads($threads);

        $from = ($page-1) * $this->page_size;
        $to   = $from + $this->page_size;

        $threads->slice($from, $to - $from);

        if ($slice)
            $threads->slice(-$slice, $slice);

        // Get UIDs of all messages in all threads
        $a_index = $threads->get();

        // fetch reqested headers from server
        $a_msg_headers = $this->fetch_headers($mailbox, $a_index);

        unset($a_index);

        // Set depth, has_children and unread_children fields in headers
        $this->_set_thread_flags($a_msg_headers, $threads);

        return array_values($a_msg_headers);
    }


    /**
     * Private method for setting threaded messages flags:
     * depth, has_children and unread_children
     *
     * @param  array             $headers Reference to headers array indexed by message UID
     * @param  rcube_imap_result $threads Threads data object
     *
     * @return array Message headers array indexed by message UID
     * @access private
     */
    private function _set_thread_flags(&$headers, $threads)
    {
        $parents = array();

        list ($msg_depth, $msg_children) = $threads->getThreadData();

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
     * Private method for listing a set of message headers (search results)
     *
     * @param   string   $mailbox  Mailbox/folder name
     * @param   int      $page     Current page to list
     * @param   int      $slice    Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @access  private
     */
    private function _list_header_set($mailbox, $page, $slice=0)
    {
        if (!strlen($mailbox) || empty($this->search_set) || $this->search_set->isEmpty()) {
            return array();
        }

        // use saved messages from searching
        if ($this->threading) {
            return $this->_list_thread_header_set($mailbox, $page, $slice);
        }

        // search set is threaded, we need a new one
        if ($this->search_threads) {
            $this->search('', $this->search_string, $this->search_charset, $this->sort_field);
        }

        $index = clone $this->search_set;
        $from  = ($page-1) * $this->page_size;
        $to    = $from + $this->page_size;

        // return empty array if no messages found
        if ($index->isEmpty())
            return array();

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
                if ($index->isEmpty())
                    return array();
            }
        }

        if ($got_index) {
            if ($this->sort_order != $index->getParameters('ORDER')) {
                $index->revert();
            }

            // get messages uids for one page
            $index->slice($from, $to-$from);

            if ($slice)
                $index->slice(-$slice, $slice);

            // fetch headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($mailbox, $a_index);

            return array_values($a_msg_headers);
        }

        // SEARCH result, need sorting
        $cnt = $index->count();

        // 300: experimantal value for best result
        if (($cnt > 300 && $cnt > $this->page_size) || !$this->sort_field) {
            // use memory less expensive (and quick) method for big result set
            $index = clone $this->message_index('', $this->sort_field, $this->sort_order);
            // get messages uids for one page...
            $index->slice($start_msg, min($cnt-$from, $this->page_size));

            if ($slice)
                $index->slice(-$slice, $slice);

            // ...and fetch headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($mailbox, $a_index);

            return array_values($a_msg_headers);
        }
        else {
            // for small result set we can fetch all messages headers
            $a_index       = $index->get();
            $a_msg_headers = $this->fetch_headers($mailbox, $a_index, false);

            // return empty array if no messages found
            if (!is_array($a_msg_headers) || empty($a_msg_headers))
                return array();

            // if not already sorted
            $a_msg_headers = $this->conn->sortHeaders(
                $a_msg_headers, $this->sort_field, $this->sort_order);

            // only return the requested part of the set
            $a_msg_headers = array_slice(array_values($a_msg_headers),
                $from, min($cnt-$to, $this->page_size));

            if ($slice)
                $a_msg_headers = array_slice($a_msg_headers, -$slice, $slice);

            return $a_msg_headers;
        }
    }


    /**
     * Private method for listing a set of threaded message headers (search results)
     *
     * @param   string   $mailbox    Mailbox/folder name
     * @param   int      $page       Current page to list
     * @param   int      $slice      Number of slice items to extract from result array
     *
     * @return  array    Indexed array with message header objects
     * @access  private
     * @see     rcube_imap::list_header_set()
     */
    private function _list_thread_header_set($mailbox, $page, $slice=0)
    {
        // update search_set if previous data was fetched with disabled threading
        if (!$this->search_threads) {
            if ($this->search_set->isEmpty())
                return array();
            $this->search('', $this->search_string, $this->search_charset, $this->sort_field);
        }

        return $this->_fetch_thread_headers($mailbox, clone $this->search_set, $page, $slice);
    }


    /**
     * Fetches messages headers (by UID)
     *
     * @param  string  $mailbox  Mailbox name
     * @param  array   $msgs     Message UIDs
     * @param  bool    $sort     Enables result sorting by $msgs
     * @param  bool    $force    Disables cache use
     *
     * @return array Messages headers indexed by UID
     * @access private
     */
    function fetch_headers($mailbox, $msgs, $sort = true, $force = false)
    {
        if (empty($msgs))
            return array();

        if (!$force && ($mcache = $this->get_mcache_engine())) {
            $headers = $mcache->get_messages($mailbox, $msgs);
        }
        else {
            // fetch reqested headers from server
            $headers = $this->conn->fetchHeaders(
                $mailbox, $msgs, true, false, $this->get_fetch_headers());
        }

        if (empty($headers))
            return array();

        foreach ($headers as $h) {
            $a_msg_headers[$h->uid] = $h;
        }

        if ($sort) {
            // use this class for message sorting
            $sorter = new rcube_header_sorter();
            $sorter->set_index($msgs);
            $sorter->sort_headers($a_msg_headers);
        }

        return $a_msg_headers;
    }


    /**
     * Returns current status of mailbox
     *
     * We compare the maximum UID to determine the number of
     * new messages because the RECENT flag is not reliable.
     *
     * @param string $mailbox Mailbox/folder name
     * @return int   Folder status
     */
    public function mailbox_status($mailbox = null)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }
        $old = $this->get_folder_stats($mailbox);

        // refresh message count -> will update
        $this->_messagecount($mailbox, 'ALL', true);

        $result = 0;

        if (empty($old)) {
            return $result;
        }

        $new = $this->get_folder_stats($mailbox);

        // got new messages
        if ($new['maxuid'] > $old['maxuid'])
            $result += 1;
        // some messages has been deleted
        if ($new['cnt'] < $old['cnt'])
            $result += 2;

        // @TODO: optional checking for messages flags changes (?)
        // @TODO: UIDVALIDITY checking

        return $result;
    }


    /**
     * Stores folder statistic data in session
     * @TODO: move to separate DB table (cache?)
     *
     * @param string $mailbox Mailbox name
     * @param string $name    Data name
     * @param mixed  $data    Data value
     */
    private function set_folder_stats($mailbox, $name, $data)
    {
        $_SESSION['folders'][$mailbox][$name] = $data;
    }


    /**
     * Gets folder statistic data
     *
     * @param string $mailbox Mailbox name
     *
     * @return array Stats data
     */
    private function get_folder_stats($mailbox)
    {
        if ($_SESSION['folders'][$mailbox])
            return (array) $_SESSION['folders'][$mailbox];
        else
            return array();
    }


    /**
     * Return sorted list of message UIDs
     *
     * @param string $mailbox    Mailbox to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     *
     * @return rcube_result_index|rcube_result_thread List of messages (UIDs)
     */
    public function message_index($mailbox='', $sort_field=NULL, $sort_order=NULL)
    {
        if ($this->threading)
            return $this->thread_index($mailbox, $sort_field, $sort_order);

        $this->set_sort_order($sort_field, $sort_order);

        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // we have a saved search result, get index from there
        if ($this->search_string) {
            if ($this->search_threads) {
                $this->search($mailbox, $this->search_string, $this->search_charset, $this->sort_field);
            }

            // use message index sort as default sorting
            if (!$this->sort_field || $this->search_sorted) {
                if ($this->sort_field && $this->search_sort_field != $this->sort_field) {
                    $this->search($mailbox, $this->search_string, $this->search_charset, $this->sort_field);
                }
                $index = $this->search_set;
            }
            else {
                $index = $this->conn->index($mailbox, $this->search_set->get(),
                    $this->sort_field, $this->skip_deleted, true, true);
            }

            if ($this->sort_order != $index->getParameters('ORDER')) {
                $index->revert();
            }

            return $index;
        }

        // check local cache
        if ($mcache = $this->get_mcache_engine()) {
            $index = $mcache->get_index($mailbox, $this->sort_field, $this->sort_order);
        }
        // fetch from IMAP server
        else {
            $index = $this->message_index_direct(
                $mailbox, $this->sort_field, $this->sort_order);
        }

        return $index;
    }


    /**
     * Return sorted list of message UIDs ignoring current search settings.
     * Doesn't uses cache by default.
     *
     * @param string $mailbox    Mailbox to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     * @param bool   $skip_cache Disables cache usage
     *
     * @return rcube_result_index Sorted list of message UIDs
     */
    public function message_index_direct($mailbox, $sort_field = null, $sort_order = null, $skip_cache = true)
    {
        if (!$skip_cache && ($mcache = $this->get_mcache_engine())) {
            $index = $mcache->get_index($mailbox, $sort_field, $sort_order);
        }
        // use message index sort as default sorting
        else if (!$sort_field) {
            if ($this->skip_deleted && !empty($this->icache['undeleted_idx'])
                && $this->icache['undeleted_idx']->getParameters('MAILBOX') == $mailbox
            ) {
                $index = $this->icache['undeleted_idx'];
            }
            else {
                $index = $this->conn->search($mailbox,
                    'ALL' .($this->skip_deleted ? ' UNDELETED' : ''), true);
            }
        }
        // fetch complete message index
        else {
            if ($this->get_capability('SORT')) {
                $index = $this->conn->sort($mailbox, $sort_field,
                    $this->skip_deleted ? 'UNDELETED' : '', true);
            }

            if (empty($index) || $index->isError()) {
                $index = $this->conn->index($mailbox, "1:*", $sort_field,
                    $this->skip_deleted, false, true);
            }
        }

        if ($sort_order != $index->getParameters('ORDER')) {
            $index->revert();
        }

        return $index;
    }


    /**
     * Return index of threaded message UIDs
     *
     * @param string $mailbox    Mailbox to get index from
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order [ASC, DESC]
     *
     * @return rcube_result_thread Message UIDs
     */
    function thread_index($mailbox='', $sort_field=NULL, $sort_order=NULL)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // we have a saved search result, get index from there
        if ($this->search_string && $this->search_threads && $mailbox == $this->mailbox) {
            $threads = $this->search_set;
        }
        else {
            // get all threads (default sort order)
            $threads = $this->fetch_threads($mailbox);
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
    private function sort_threads($threads)
    {
        if ($threads->isEmpty()) {
            return;
        }

        // THREAD=ORDEREDSUBJECT: sorting by sent date of root message
        // THREAD=REFERENCES:     sorting by sent date of root message
        // THREAD=REFS:           sorting by the most recent date in each thread

        if ($this->sort_field && ($this->sort_field != 'date' || $this->get_capability('THREAD') != 'REFS')) {
            $index = $this->message_index_direct($this->mailbox, $this->sort_field, $this->sort_order, false);

            if (!$index->isEmpty()) {
                $threads->sort($index);
            }
        }
        else {
            if ($this->sort_order != $threads->getParameters('ORDER')) {
                $threads->revert();
            }
        }
    }


    /**
     * Invoke search request to IMAP server
     *
     * @param  string  $mailbox    Mailbox name to search in
     * @param  string  $str        Search criteria
     * @param  string  $charset    Search charset
     * @param  string  $sort_field Header field to sort by
     * @access public
     */
    function search($mailbox='', $str='ALL', $charset=NULL, $sort_field=NULL)
    {
        if (!$str)
            $str = 'ALL';

        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        $results = $this->_search_index($mailbox, $str, $charset, $sort_field);

        $this->set_search_set($str, $results, $charset, $sort_field,
            $this->threading || $this->search_sorted ? true : false);
    }


    /**
     * Private search method
     *
     * @param string $mailbox    Mailbox name
     * @param string $criteria   Search criteria
     * @param string $charset    Charset
     * @param string $sort_field Sorting field
     *
     * @return rcube_result_index|rcube_result_thread  Search results (UIDs)
     * @see rcube_imap::search()
     */
    private function _search_index($mailbox, $criteria='ALL', $charset=NULL, $sort_field=NULL)
    {
        $orig_criteria = $criteria;

        if ($this->skip_deleted && !preg_match('/UNDELETED/', $criteria))
            $criteria = 'UNDELETED '.$criteria;

        if ($this->threading) {
            $threads = $this->conn->thread($mailbox, $this->threading, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen that Courier doesn't support UTF-8)
            if ($threads->isError() && $charset && $charset != 'US-ASCII')
                $threads = $this->conn->thread($mailbox, $this->threading,
                    $this->convert_criteria($criteria, $charset), true, 'US-ASCII');

            return $threads;
        }

        if ($sort_field && $this->get_capability('SORT')) {
            $charset  = $charset ? $charset : $this->default_charset;
            $messages = $this->conn->sort($mailbox, $sort_field, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen Courier with disabled UTF-8 support)
            if ($messages->isError() && $charset && $charset != 'US-ASCII')
                $messages = $this->conn->sort($mailbox, $sort_field,
                    $this->convert_criteria($criteria, $charset), true, 'US-ASCII');

            if (!$messages->isError()) {
                $this->search_sorted = true;
                return $messages;
            }
        }

        $messages = $this->conn->search($mailbox,
            ($charset ? "CHARSET $charset " : '') . $criteria, true);

        // Error, try with US-ASCII (some servers may support only US-ASCII)
        if ($messages->isError() && $charset && $charset != 'US-ASCII')
            $messages = $this->conn->search($mailbox,
                'CHARSET US-ASCII ' . $this->convert_criteria($criteria, $charset), true);

        $this->search_sorted = false;

        return $messages;
    }


    /**
     * Direct (real and simple) SEARCH request to IMAP server,
     * without result sorting and caching
     *
     * @param  string  $mailbox Mailbox name to search in
     * @param  string  $str     Search string
     * @param  boolean $ret_uid True if UIDs should be returned
     *
     * @return rcube_result_index  Search result (UIDs)
     */
    function search_once($mailbox='', $str='ALL')
    {
        if (!$str)
            return 'ALL';

        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        $index = $this->conn->search($mailbox, $str, true);

        return $index;
    }


    /**
     * Converts charset of search criteria string
     *
     * @param  string  $str          Search string
     * @param  string  $charset      Original charset
     * @param  string  $dest_charset Destination charset (default US-ASCII)
     * @return string  Search string
     * @access private
     */
    private function convert_criteria($str, $charset, $dest_charset='US-ASCII')
    {
        // convert strings to US_ASCII
        if (preg_match_all('/\{([0-9]+)\}\r\n/', $str, $matches, PREG_OFFSET_CAPTURE)) {
            $last = 0; $res = '';
            foreach ($matches[1] as $m) {
                $string_offset = $m[1] + strlen($m[0]) + 4; // {}\r\n
                $string = substr($str, $string_offset - 1, $m[0]);
                $string = rcube_charset_convert($string, $charset, $dest_charset);
                if (!$string)
                    continue;
                $res .= sprintf("%s{%d}\r\n%s", substr($str, $last, $m[1] - $last - 1), strlen($string), $string);
                $last = $m[0] + $string_offset - 1;
            }
            if ($last < strlen($str))
                $res .= substr($str, $last, strlen($str)-$last);
        }
        else // strings for conversion not found
            $res = $str;

        return $res;
    }


    /**
     * Refresh saved search set
     *
     * @return array Current search set
     */
    function refresh_search()
    {
        if (!empty($this->search_string)) {
            $this->search('', $this->search_string, $this->search_charset, $this->search_sort_field);
        }

        return $this->get_search_set();
    }


    /**
     * Check if the given message UID is part of the current search set
     *
     * @param string $msgid Message UID
     *
     * @return boolean True on match or if no search request is stored
     */
    function in_searchset($uid)
    {
        if (!empty($this->search_string)) {
            return $this->search_set->exists($uid);
        }
        return true;
    }


    /**
     * Return message headers object of a specific message
     *
     * @param int     $id       Message sequence ID or UID
     * @param string  $mailbox  Mailbox to read from
     * @param bool    $force    True to skip cache
     *
     * @return rcube_mail_header Message headers
     */
    function get_headers($uid, $mailbox = null, $force = false)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // get cached headers
        if (!$force && $uid && ($mcache = $this->get_mcache_engine())) {
            $headers = $mcache->get_message($mailbox, $uid);
        }
        else {
            $headers = $this->conn->fetchHeader(
                $mailbox, $uid, true, true, $this->get_fetch_headers());
        }

        return $headers;
    }


    /**
     * Fetch message headers and body structure from the IMAP server and build
     * an object structure similar to the one generated by PEAR::Mail_mimeDecode
     *
     * @param int     $uid      Message UID to fetch
     * @param string  $mailbox  Mailbox to read from
     *
     * @return object rcube_mail_header Message data
     */
    function get_message($uid, $mailbox = null)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // Check internal cache
        if (!empty($this->icache['message'])) {
            if (($headers = $this->icache['message']) && $headers->uid == $uid) {
                return $headers;
            }
        }

        $headers = $this->get_headers($uid, $mailbox);

        // message doesn't exist?
        if (empty($headers))
            return null;

        // structure might be cached
        if (!empty($headers->structure))
            return $headers;

        $this->_msg_uid = $uid;

        if (empty($headers->bodystructure)) {
            $headers->bodystructure = $this->conn->getStructure($mailbox, $uid, true);
        }

        $structure = $headers->bodystructure;

        if (empty($structure))
            return $headers;

        // set message charset from message headers
        if ($headers->charset)
            $this->struct_charset = $headers->charset;
        else
            $this->struct_charset = $this->_structure_charset($structure);

        $headers->ctype = strtolower($headers->ctype);

        // Here we can recognize malformed BODYSTRUCTURE and
        // 1. [@TODO] parse the message in other way to create our own message structure
        // 2. or just show the raw message body.
        // Example of structure for malformed MIME message:
        // ("text" "plain" NIL NIL NIL "7bit" 2154 70 NIL NIL NIL)
        if ($headers->ctype && !is_array($structure[0]) && $headers->ctype != 'text/plain'
            && strtolower($structure[0].'/'.$structure[1]) == 'text/plain') {
            // we can handle single-part messages, by simple fix in structure (#1486898)
            if (preg_match('/^(text|application)\/(.*)/', $headers->ctype, $m)) {
                $structure[0] = $m[1];
                $structure[1] = $m[2];
            }
            else
                return $headers;
        }

        $struct = &$this->_structure_part($structure, 0, '', $headers);

        // don't trust given content-type
        if (empty($struct->parts) && !empty($headers->ctype)) {
            $struct->mime_id = '1';
            $struct->mimetype = strtolower($headers->ctype);
            list($struct->ctype_primary, $struct->ctype_secondary) = explode('/', $struct->mimetype);
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
     * @access private
     */
    function &_structure_part($part, $count=0, $parent='', $mime_headers=null)
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
                if (!is_array($part[$i]))
                    break;
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
                $mime_part_headers = $this->conn->fetchMIMEHeaders($this->mailbox,
                    $this->_msg_uid, $mime_part_headers);
            }

            $struct->parts = array();
            for ($i=0, $count=0; $i<count($part); $i++) {
                if (!is_array($part[$i]))
                    break;
                $tmp_part_id = $struct->mime_id ? $struct->mime_id.'.'.($i+1) : $i+1;
                $struct->parts[] = $this->_structure_part($part[$i], ++$count, $struct->mime_id,
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
            for ($i=0; $i<count($part[2]); $i+=2)
                $struct->ctype_parameters[strtolower($part[2][$i])] = $part[2][$i+1];

            if (isset($struct->ctype_parameters['charset']))
                $struct->charset = $struct->ctype_parameters['charset'];
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
        if (!empty($part[6]))
            $struct->size = intval($part[6]);

        // read part disposition
        $di = 8;
        if ($struct->ctype_primary == 'text') $di += 1;
        else if ($struct->mimetype == 'message/rfc822') $di += 3;

        if (is_array($part[$di]) && count($part[$di]) == 2) {
            $struct->disposition = strtolower($part[$di][0]);

            if (is_array($part[$di][1]))
                for ($n=0; $n<count($part[$di][1]); $n+=2)
                    $struct->d_parameters[strtolower($part[$di][1][$n])] = $part[$di][1][$n+1];
        }

        // get message/rfc822's child-parts
        if (is_array($part[8]) && $di != 8) {
            $struct->parts = array();
            for ($i=0, $count=0; $i<count($part[8]); $i++) {
                if (!is_array($part[8][$i]))
                    break;
                $struct->parts[] = $this->_structure_part($part[8][$i], ++$count, $struct->mime_id);
            }
        }

        // get part ID
        if (!empty($part[3])) {
            $struct->content_id = $part[3];
            $struct->headers['content-id'] = $part[3];

            if (empty($struct->disposition))
                $struct->disposition = 'inline';
        }

        // fetch message headers if message/rfc822 or named part (could contain Content-Location header)
        if ($struct->ctype_primary == 'message' || ($struct->ctype_parameters['name'] && !$struct->content_id)) {
            if (empty($mime_headers)) {
                $mime_headers = $this->conn->fetchPartHeader(
                    $this->mailbox, $this->_msg_uid, true, $struct->mime_id);
            }

            if (is_string($mime_headers))
                $struct->headers = $this->_parse_headers($mime_headers) + $struct->headers;
            else if (is_object($mime_headers))
                $struct->headers = get_object_vars($mime_headers) + $struct->headers;

            // get real content-type of message/rfc822
            if ($struct->mimetype == 'message/rfc822') {
                // single-part
                if (!is_array($part[8][0]))
                    $struct->real_mimetype = strtolower($part[8][0] . '/' . $part[8][1]);
                // multi-part
                else {
                    for ($n=0; $n<count($part[8]); $n++)
                        if (!is_array($part[8][$n]))
                            break;
                    $struct->real_mimetype = 'multipart/' . strtolower($part[8][$n]);
                }
            }

            if ($struct->ctype_primary == 'message' && empty($struct->parts)) {
                if (is_array($part[8]) && $di != 8)
                    $struct->parts[] = $this->_structure_part($part[8], ++$count, $struct->mime_id);
            }
        }

        // normalize filename property
        $this->_set_part_filename($struct, $mime_headers);

        return $struct;
    }


    /**
     * Set attachment filename from message part structure
     *
     * @param  rcube_message_part $part    Part object
     * @param  string             $headers Part's raw headers
     * @access private
     */
    private function _set_part_filename(&$part, $headers=null)
    {
        if (!empty($part->d_parameters['filename']))
            $filename_mime = $part->d_parameters['filename'];
        else if (!empty($part->d_parameters['filename*']))
            $filename_encoded = $part->d_parameters['filename*'];
        else if (!empty($part->ctype_parameters['name*']))
            $filename_encoded = $part->ctype_parameters['name*'];
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
                        $this->mailbox, $this->_msg_uid, true, $part->mime_id);
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
                            $this->mailbox, $this->_msg_uid, true, $part->mime_id);
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
                        $this->mailbox, $this->_msg_uid, true, $part->mime_id);
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
                        $this->mailbox, $this->_msg_uid, true, $part->mime_id);
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
        else if (!empty($part->ctype_parameters['name']))
            $filename_mime = $part->ctype_parameters['name'];
        // Content-Disposition
        else if (!empty($part->headers['content-description']))
            $filename_mime = $part->headers['content-description'];
        else
            return;

        // decode filename
        if (!empty($filename_mime)) {
            if (!empty($part->charset))
                $charset = $part->charset;
            else if (!empty($this->struct_charset))
                $charset = $this->struct_charset;
            else
                $charset = rc_detect_encoding($filename_mime, $this->default_charset);

            $part->filename = rcube_imap::decode_mime_string($filename_mime, $charset);
        }
        else if (!empty($filename_encoded)) {
            // decode filename according to RFC 2231, Section 4
            if (preg_match("/^([^']*)'[^']*'(.*)$/", $filename_encoded, $fmatches)) {
                $filename_charset = $fmatches[1];
                $filename_encoded = $fmatches[2];
            }

            $part->filename = rcube_charset_convert(urldecode($filename_encoded), $filename_charset);
        }
    }


    /**
     * Get charset name from message structure (first part)
     *
     * @param  array $structure Message structure
     * @return string Charset name
     * @access private
     */
    private function _structure_charset($structure)
    {
        while (is_array($structure)) {
            if (is_array($structure[2]) && $structure[2][0] == 'charset')
                return $structure[2][1];
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
     *
     * @return string Message/part body if not printed
     */
    function &get_message_part($uid, $part=1, $o_part=NULL, $print=NULL, $fp=NULL, $skip_charset_conv=false)
    {
        // get part data if not provided
        if (!is_object($o_part)) {
            $structure = $this->conn->getStructure($this->mailbox, $uid, true);
            $part_data = rcube_imap_generic::getStructurePartData($structure, $part);

            $o_part = new rcube_message_part;
            $o_part->ctype_primary = $part_data['type'];
            $o_part->encoding      = $part_data['encoding'];
            $o_part->charset       = $part_data['charset'];
            $o_part->size          = $part_data['size'];
        }

        if ($o_part && $o_part->size) {
            $body = $this->conn->handlePartBody($this->mailbox, $uid, true,
                $part ? $part : 'TEXT', $o_part->encoding, $print, $fp);
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
                    if ($o_part->ctype_secondary == 'html' && preg_match('/<meta[^>]+charset=([a-z0-9-_]+)/i', $body, $m))
                        $o_part->charset = strtoupper($m[1]);
                    else
                        $o_part->charset = $this->default_charset;
                }
                $body = rcube_charset_convert($body, $o_part->charset);
            }
        }

        return $body;
    }


    /**
     * Fetch message body of a specific message from the server
     *
     * @param  int    $uid  Message UID
     * @return string $part Message/part body
     * @see    rcube_imap::get_message_part()
     */
    function &get_body($uid, $part=1)
    {
        $headers = $this->get_headers($uid);
        return rcube_charset_convert($this->get_message_part($uid, $part, NULL),
            $headers->charset ? $headers->charset : $this->default_charset);
    }


    /**
     * Returns the whole message source as string (or saves to a file)
     *
     * @param int      $uid Message UID
     * @param resource $fp  File pointer to save the message
     *
     * @return string Message source string
     */
    function &get_raw_body($uid, $fp=null)
    {
        return $this->conn->handlePartBody($this->mailbox, $uid,
            true, null, null, false, $fp);
    }


    /**
     * Returns the message headers as string
     *
     * @param int $uid  Message UID
     * @return string Message headers string
     */
    function &get_raw_headers($uid)
    {
        return $this->conn->fetchPartHeader($this->mailbox, $uid, true);
    }


    /**
     * Sends the whole message source to stdout
     *
     * @param int $uid Message UID
     */
    function print_raw_body($uid)
    {
        $this->conn->handlePartBody($this->mailbox, $uid, true, NULL, NULL, true);
    }


    /**
     * Set message flag to one or several messages
     *
     * @param mixed   $uids       Message UIDs as array or comma-separated string, or '*'
     * @param string  $flag       Flag to set: SEEN, UNDELETED, DELETED, RECENT, ANSWERED, DRAFT, MDNSENT
     * @param string  $mailbox    Folder name
     * @param boolean $skip_cache True to skip message cache clean up
     *
     * @return boolean  Operation status
     */
    function set_flag($uids, $flag, $mailbox=null, $skip_cache=false)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        $flag = strtoupper($flag);
        list($uids, $all_mode) = $this->_parse_uids($uids, $mailbox);

        if (strpos($flag, 'UN') === 0)
            $result = $this->conn->unflag($mailbox, $uids, substr($flag, 2));
        else
            $result = $this->conn->flag($mailbox, $uids, $flag);

        if ($result) {
            // reload message headers if cached
            // @TODO: update flags instead removing from cache
            if (!$skip_cache && ($mcache = $this->get_mcache_engine())) {
                $status = strpos($flag, 'UN') !== 0;
                $mflag  = preg_replace('/^UN/', '', $flag);
                $mcache->change_flag($mailbox, $all_mode ? null : explode(',', $uids),
                    $mflag, $status);
            }

            // clear cached counters
            if ($flag == 'SEEN' || $flag == 'UNSEEN') {
                $this->_clear_messagecount($mailbox, 'SEEN');
                $this->_clear_messagecount($mailbox, 'UNSEEN');
            }
            else if ($flag == 'DELETED') {
                $this->_clear_messagecount($mailbox, 'DELETED');
            }
        }

        return $result;
    }


    /**
     * Remove message flag for one or several messages
     *
     * @param mixed  $uids    Message UIDs as array or comma-separated string, or '*'
     * @param string $flag    Flag to unset: SEEN, DELETED, RECENT, ANSWERED, DRAFT, MDNSENT
     * @param string $mailbox Folder name
     *
     * @return int   Number of flagged messages, -1 on failure
     * @see set_flag
     */
    function unset_flag($uids, $flag, $mailbox=null)
    {
        return $this->set_flag($uids, 'UN'.$flag, $mailbox);
    }


    /**
     * Append a mail message (source) to a specific mailbox
     *
     * @param string  $mailbox Target mailbox
     * @param string  $message The message source string or filename
     * @param string  $headers Headers string if $message contains only the body
     * @param boolean $is_file True if $message is a filename
     *
     * @return int|bool Appended message UID or True on success, False on error
     */
    function save_message($mailbox, &$message, $headers='', $is_file=false)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // make sure mailbox exists
        if ($this->mailbox_exists($mailbox)) {
            if ($is_file)
                $saved = $this->conn->appendFromFile($mailbox, $message, $headers);
            else
                $saved = $this->conn->append($mailbox, $message);
        }

        if ($saved) {
            // increase messagecount of the target mailbox
            $this->_set_messagecount($mailbox, 'ALL', 1);
        }

        return $saved;
    }


    /**
     * Move a message from one mailbox to another
     *
     * @param mixed  $uids      Message UIDs as array or comma-separated string, or '*'
     * @param string $to_mbox   Target mailbox
     * @param string $from_mbox Source mailbox
     * @return boolean True on success, False on error
     */
    function move_message($uids, $to_mbox, $from_mbox='')
    {
        if (!strlen($from_mbox)) {
            $from_mbox = $this->mailbox;
        }

        if ($to_mbox === $from_mbox) {
            return false;
        }

        list($uids, $all_mode) = $this->_parse_uids($uids, $from_mbox);

        // exit if no message uids are specified
        if (empty($uids))
            return false;

        // make sure mailbox exists
        if ($to_mbox != 'INBOX' && !$this->mailbox_exists($to_mbox)) {
            if (in_array($to_mbox, $this->default_folders)) {
                if (!$this->create_mailbox($to_mbox, true)) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        $config = rcmail::get_instance()->config;
        $to_trash = $to_mbox == $config->get('trash_mbox');

        // flag messages as read before moving them
        if ($to_trash && $config->get('read_when_deleted')) {
            // don't flush cache (4th argument)
            $this->set_flag($uids, 'SEEN', $from_mbox, true);
        }

        // move messages
        $moved = $this->conn->move($uids, $from_mbox, $to_mbox);

        // send expunge command in order to have the moved message
        // really deleted from the source mailbox
        if ($moved) {
            $this->_expunge($from_mbox, false, $uids);
            $this->_clear_messagecount($from_mbox);
            $this->_clear_messagecount($to_mbox);
        }
        // moving failed
        else if ($to_trash && $config->get('delete_always', false)) {
            $moved = $this->delete_message($uids, $from_mbox);
        }

        if ($moved) {
            // unset threads internal cache
            unset($this->icache['threads']);

            // remove message ids from search set
            if ($this->search_set && $from_mbox == $this->mailbox) {
                // threads are too complicated to just remove messages from set
                if ($this->search_threads || $all_mode)
                    $this->refresh_search();
                else
                    $this->search_set->filter(explode(',', $uids));
            }

            // remove cached messages
            // @TODO: do cache update instead of clearing it
            $this->clear_message_cache($from_mbox, $all_mode ? null : explode(',', $uids));
        }

        return $moved;
    }


    /**
     * Copy a message from one mailbox to another
     *
     * @param mixed  $uids      Message UIDs as array or comma-separated string, or '*'
     * @param string $to_mbox   Target mailbox
     * @param string $from_mbox Source mailbox
     * @return boolean True on success, False on error
     */
    function copy_message($uids, $to_mbox, $from_mbox='')
    {
        if (!strlen($from_mbox)) {
            $from_mbox = $this->mailbox;
        }

        list($uids, $all_mode) = $this->_parse_uids($uids, $from_mbox);

        // exit if no message uids are specified
        if (empty($uids)) {
            return false;
        }

        // make sure mailbox exists
        if ($to_mbox != 'INBOX' && !$this->mailbox_exists($to_mbox)) {
            if (in_array($to_mbox, $this->default_folders)) {
                if (!$this->create_mailbox($to_mbox, true)) {
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
            $this->_clear_messagecount($to_mbox);
        }

        return $copied;
    }


    /**
     * Mark messages as deleted and expunge mailbox
     *
     * @param mixed  $uids    Message UIDs as array or comma-separated string, or '*'
     * @param string $mailbox Source mailbox
     *
     * @return boolean True on success, False on error
     */
    function delete_message($uids, $mailbox='')
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        list($uids, $all_mode) = $this->_parse_uids($uids, $mailbox);

        // exit if no message uids are specified
        if (empty($uids))
            return false;

        $deleted = $this->conn->delete($mailbox, $uids);

        if ($deleted) {
            // send expunge command in order to have the deleted message
            // really deleted from the mailbox
            $this->_expunge($mailbox, false, $uids);
            $this->_clear_messagecount($mailbox);
            unset($this->uid_id_map[$mailbox]);

            // unset threads internal cache
            unset($this->icache['threads']);

            // remove message ids from search set
            if ($this->search_set && $mailbox == $this->mailbox) {
                // threads are too complicated to just remove messages from set
                if ($this->search_threads || $all_mode)
                    $this->refresh_search();
                else
                    $this->search_set->filter(explode(',', $uids));
            }

            // remove cached messages
            $this->clear_message_cache($mailbox, $all_mode ? null : explode(',', $uids));
        }

        return $deleted;
    }


    /**
     * Clear all messages in a specific mailbox
     *
     * @param string $mailbox Mailbox name
     *
     * @return int Above 0 on success
     */
    function clear_mailbox($mailbox=null)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        // SELECT will set messages count for clearFolder()
        if ($this->conn->select($mailbox)) {
            $cleared = $this->conn->clearFolder($mailbox);
        }

        // make sure the cache is cleared as well
        if ($cleared) {
            $this->clear_message_cache($mailbox);
            $a_mailbox_cache = $this->get_cache('messagecount');
            unset($a_mailbox_cache[$mailbox]);
            $this->update_cache('messagecount', $a_mailbox_cache);
        }

        return $cleared;
    }


    /**
     * Send IMAP expunge command and clear cache
     *
     * @param string  $mailbox     Mailbox name
     * @param boolean $clear_cache False if cache should not be cleared
     *
     * @return boolean True on success
     */
    function expunge($mailbox='', $clear_cache=true)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        return $this->_expunge($mailbox, $clear_cache);
    }


    /**
     * Send IMAP expunge command and clear cache
     *
     * @param string  $mailbox     Mailbox name
     * @param boolean $clear_cache False if cache should not be cleared
     * @param mixed   $uids        Message UIDs as array or comma-separated string, or '*'
     * @return boolean True on success
     * @access private
     * @see rcube_imap::expunge()
     */
    private function _expunge($mailbox, $clear_cache=true, $uids=NULL)
    {
        if ($uids && $this->get_capability('UIDPLUS'))
            list($uids, $all_mode) = $this->_parse_uids($uids, $mailbox);
        else
            $uids = null;

        // force mailbox selection and check if mailbox is writeable
        // to prevent a situation when CLOSE is executed on closed
        // or EXPUNGE on read-only mailbox
        $result = $this->conn->select($mailbox);
        if (!$result) {
            return false;
        }
        if (!$this->conn->data['READ-WRITE']) {
            $this->conn->setError(rcube_imap_generic::ERROR_READONLY, "Mailbox is read-only");
            return false;
        }

        // CLOSE(+SELECT) should be faster than EXPUNGE
        if (empty($uids) || $all_mode)
            $result = $this->conn->close();
        else
            $result = $this->conn->expunge($mailbox, $uids);

        if ($result && $clear_cache) {
            $this->clear_message_cache($mailbox, $all_mode ? null : explode(',', $uids));
            $this->_clear_messagecount($mailbox);
        }

        return $result;
    }


    /**
     * Parse message UIDs input
     *
     * @param mixed  $uids    UIDs array or comma-separated list or '*' or '1:*'
     * @param string $mailbox Mailbox name
     * @return array Two elements array with UIDs converted to list and ALL flag
     * @access private
     */
    private function _parse_uids($uids, $mailbox)
    {
        if ($uids === '*' || $uids === '1:*') {
            if (empty($this->search_set)) {
                $uids = '1:*';
                $all = true;
            }
            // get UIDs from current search set
            else {
                $uids = join(',', $this->search_set->get());
            }
        }
        else {
            if (is_array($uids))
                $uids = join(',', $uids);

            if (preg_match('/[^0-9,]/', $uids))
                $uids = '';
        }

        return array($uids, (bool) $all);
    }


    /* --------------------------------
     *        folder managment
     * --------------------------------*/

    /**
     * Public method for listing subscribed folders
     *
     * @param   string  $root      Optional root folder
     * @param   string  $name      Optional name pattern
     * @param   string  $filter    Optional filter
     * @param   string  $rights    Optional ACL requirements
     * @param   bool    $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return  array   List of folders
     * @access  public
     */
    function list_mailboxes($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
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

        $a_mboxes = $this->_list_mailboxes($root, $name, $filter, $rights);

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

        // sort mailboxes (always sort for cache)
        if (!$skip_sort || $this->cache) {
            $a_mboxes = $this->_sort_mailbox_list($a_mboxes);
        }

        // write mailboxlist to cache
        $this->update_cache($cache_key, $a_mboxes);

        return $a_mboxes;
    }


    /**
     * Private method for mailbox listing (LSUB)
     *
     * @param   string  $root   Optional root folder
     * @param   string  $name   Optional name pattern
     * @param   mixed   $filter Optional filter
     * @param   string  $rights Optional ACL requirements
     *
     * @return  array   List of subscribed folders
     * @see     rcube_imap::list_mailboxes()
     * @access  private
     */
    private function _list_mailboxes($root='', $name='*', $filter=null, $rights=null)
    {
        $a_defaults = $a_out = array();

        // Give plugins a chance to provide a list of mailboxes
        $data = rcmail::get_instance()->plugins->exec_hook('mailboxes_list',
            array('root' => $root, 'name' => $name, 'filter' => $filter, 'mode' => 'LSUB'));

        if (isset($data['folders'])) {
            $a_folders = $data['folders'];
        }
        else if (!$this->conn->connected()) {
           return null;
        }
        else {
            // Server supports LIST-EXTENDED, we can use selection options
            $config = rcmail::get_instance()->config;
            // #1486225: Some dovecot versions returns wrong result using LIST-EXTENDED
            if (!$config->get('imap_force_lsub') && $this->get_capability('LIST-EXTENDED')) {
                // This will also set mailbox options, LSUB doesn't do that
                $a_folders = $this->conn->listMailboxes($root, $name,
                    NULL, array('SUBSCRIBED'));

                // unsubscribe non-existent folders, remove from the list
                if (is_array($a_folders) && $name == '*') {
                    foreach ($a_folders as $idx => $folder) {
                        if ($this->conn->data['LIST'] && ($opts = $this->conn->data['LIST'][$folder])
                            && in_array('\\NonExistent', $opts)
                        ) {
                            $this->conn->unsubscribe($folder);
                            unset($a_folders[$idx]);
                        }
                    }
                }
            }
            // retrieve list of folders from IMAP server using LSUB
            else {
                $a_folders = $this->conn->listSubscribed($root, $name);

                // unsubscribe non-existent folders, remove from the list
                if (is_array($a_folders) && $name == '*') {
                    foreach ($a_folders as $idx => $folder) {
                        if ($this->conn->data['LIST'] && ($opts = $this->conn->data['LIST'][$folder])
                            && in_array('\\Noselect', $opts)
                        ) {
                            // Some servers returns \Noselect for existing folders
                            if (!$this->mailbox_exists($folder)) {
                                $this->conn->unsubscribe($folder);
                                unset($a_folders[$idx]);
                            }
                        }
                    }
                }
            }
        }

        if (!is_array($a_folders) || !sizeof($a_folders)) {
            $a_folders = array();
        }

        return $a_folders;
    }


    /**
     * Get a list of all folders available on the IMAP server
     *
     * @param string  $root      IMAP root dir
     * @param string  $name      Optional name pattern
     * @param mixed   $filter    Optional filter
     * @param string  $rights    Optional ACL requirements
     * @param bool    $skip_sort Enable to return unsorted list (for better performance)
     *
     * @return array Indexed array with folder names
     */
    function list_unsubscribed($root='', $name='*', $filter=null, $rights=null, $skip_sort=false)
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

        // Give plugins a chance to provide a list of mailboxes
        $data = rcmail::get_instance()->plugins->exec_hook('mailboxes_list',
            array('root' => $root, 'name' => $name, 'filter' => $filter, 'mode' => 'LIST'));

        if (isset($data['folders'])) {
            $a_mboxes = $data['folders'];
        }
        else {
            // retrieve list of folders from IMAP server
            $a_mboxes = $this->_list_unsubscribed($root, $name);
        }

        if (!is_array($a_mboxes)) {
            $a_mboxes = array();
        }

        // INBOX should always be available
        if ((!$filter || $filter == 'mail') && !in_array('INBOX', $a_mboxes)) {
            array_unshift($a_mboxes, 'INBOX');
        }

        // cache folder attributes
        if ($root == '' && $name == '*' && empty($filter)) {
            $this->update_cache('mailboxes.attributes', $this->conn->data['LIST']);
        }

        // filter folders list according to rights requirements
        if ($rights && $this->get_capability('ACL')) {
            $a_folders = $this->filter_rights($a_folders, $rights);
        }

        // filter folders and sort them
        if (!$skip_sort) {
            $a_mboxes = $this->_sort_mailbox_list($a_mboxes);
        }

        // write mailboxlist to cache
        $this->update_cache($cache_key, $a_mboxes);

        return $a_mboxes;
    }


    /**
     * Private method for mailbox listing (LIST)
     *
     * @param   string  $root   Optional root folder
     * @param   string  $name   Optional name pattern
     *
     * @return  array   List of folders
     * @see     rcube_imap::list_unsubscribed()
     */
    private function _list_unsubscribed($root='', $name='*')
    {
        $result = $this->conn->listMailboxes($root, $name);

        if (!is_array($result)) {
            return array();
        }

        // #1486796: some server configurations doesn't
        // return folders in all namespaces, we'll try to detect that situation
        // and ask for these namespaces separately
        if ($root == '' && $name == '*') {
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
                    $list = $this->conn->listMailboxes($prefix, $name);

                    if (!empty($list)) {
                        $result = array_merge($result, $list);
                    }
                }
            }
        }

        return $result;
    }


    /**
     * Filter the given list of folders according to access rights
     */
    private function filter_rights($a_folders, $rights)
    {
        $regex = '/('.$rights.')/';
        foreach ($a_folders as $idx => $folder) {
            $myrights = join('', (array)$this->my_rights($folder));
            if ($myrights !== null && !preg_match($regex, $myrights))
                unset($a_folders[$idx]);
        }

        return $a_folders;
    }


    /**
     * Get mailbox quota information
     * added by Nuny
     *
     * @return mixed Quota info or False if not supported
     */
    function get_quota()
    {
        if ($this->get_capability('QUOTA'))
            return $this->conn->getQuota();

        return false;
    }


    /**
     * Get mailbox size (size of all messages in a mailbox)
     *
     * @param string $mailbox Mailbox name
     *
     * @return int Mailbox size in bytes, False on error
     */
    function get_mailbox_size($mailbox)
    {
        // @TODO: could we try to use QUOTA here?
        $result = $this->conn->fetchHeaderIndex($mailbox, '1:*', 'SIZE', false);

        if (is_array($result))
            $result = array_sum($result);

        return $result;
    }


    /**
     * Subscribe to a specific mailbox(es)
     *
     * @param array $a_mboxes Mailbox name(s)
     * @return boolean True on success
     */
    function subscribe($a_mboxes)
    {
        if (!is_array($a_mboxes))
            $a_mboxes = array($a_mboxes);

        // let this common function do the main work
        return $this->_change_subscription($a_mboxes, 'subscribe');
    }


    /**
     * Unsubscribe mailboxes
     *
     * @param array $a_mboxes Mailbox name(s)
     * @return boolean True on success
     */
    function unsubscribe($a_mboxes)
    {
        if (!is_array($a_mboxes))
            $a_mboxes = array($a_mboxes);

        // let this common function do the main work
        return $this->_change_subscription($a_mboxes, 'unsubscribe');
    }


    /**
     * Create a new mailbox on the server and register it in local cache
     *
     * @param string  $mailbox   New mailbox name
     * @param boolean $subscribe True if the new mailbox should be subscribed
     *
     * @return boolean True on success
     */
    function create_mailbox($mailbox, $subscribe=false)
    {
        $result = $this->conn->createFolder($mailbox);

        // try to subscribe it
        if ($result) {
            // clear cache
            $this->clear_cache('mailboxes', true);

            if ($subscribe)
                $this->subscribe($mailbox);
        }

        return $result;
    }


    /**
     * Set a new name to an existing mailbox
     *
     * @param string $mailbox  Mailbox to rename
     * @param string $new_name New mailbox name
     *
     * @return boolean True on success
     */
    function rename_mailbox($mailbox, $new_name)
    {
        if (!strlen($new_name)) {
            return false;
        }

        $delm = $this->get_hierarchy_delimiter();

        // get list of subscribed folders
        if ((strpos($mailbox, '%') === false) && (strpos($mailbox, '*') === false)) {
            $a_subscribed = $this->_list_mailboxes('', $mailbox . $delm . '*');
            $subscribed   = $this->mailbox_exists($mailbox, true);
        }
        else {
            $a_subscribed = $this->_list_mailboxes();
            $subscribed   = in_array($mailbox, $a_subscribed);
        }

        $result = $this->conn->renameFolder($mailbox, $new_name);

        if ($result) {
            // unsubscribe the old folder, subscribe the new one
            if ($subscribed) {
                $this->conn->unsubscribe($mailbox);
                $this->conn->subscribe($new_name);
            }

            // check if mailbox children are subscribed
            foreach ($a_subscribed as $c_subscribed) {
                if (preg_match('/^'.preg_quote($mailbox.$delm, '/').'/', $c_subscribed)) {
                    $this->conn->unsubscribe($c_subscribed);
                    $this->conn->subscribe(preg_replace('/^'.preg_quote($mailbox, '/').'/',
                        $new_name, $c_subscribed));

                    // clear cache
                    $this->clear_message_cache($c_subscribed);
                }
            }

            // clear cache
            $this->clear_message_cache($mailbox);
            $this->clear_cache('mailboxes', true);
        }

        return $result;
    }


    /**
     * Remove mailbox from server
     *
     * @param string $mailbox Mailbox name
     *
     * @return boolean True on success
     */
    function delete_mailbox($mailbox)
    {
        $delm = $this->get_hierarchy_delimiter();

        // get list of folders
        if ((strpos($mailbox, '%') === false) && (strpos($mailbox, '*') === false))
            $sub_mboxes = $this->list_unsubscribed('', $mailbox . $delm . '*');
        else
            $sub_mboxes = $this->list_unsubscribed();

        // send delete command to server
        $result = $this->conn->deleteFolder($mailbox);

        if ($result) {
            // unsubscribe mailbox
            $this->conn->unsubscribe($mailbox);

            foreach ($sub_mboxes as $c_mbox) {
                if (preg_match('/^'.preg_quote($mailbox.$delm, '/').'/', $c_mbox)) {
                    $this->conn->unsubscribe($c_mbox);
                    if ($this->conn->deleteFolder($c_mbox)) {
	                    $this->clear_message_cache($c_mbox);
                    }
                }
            }

            // clear mailbox-related cache
            $this->clear_message_cache($mailbox);
            $this->clear_cache('mailboxes', true);
        }

        return $result;
    }


    /**
     * Create all folders specified as default
     */
    function create_default_folders()
    {
        // create default folders if they do not exist
        foreach ($this->default_folders as $folder) {
            if (!$this->mailbox_exists($folder))
                $this->create_mailbox($folder, true);
            else if (!$this->mailbox_exists($folder, true))
                $this->subscribe($folder);
        }
    }


    /**
     * Checks if folder exists and is subscribed
     *
     * @param string   $mailbox      Folder name
     * @param boolean  $subscription Enable subscription checking
     *
     * @return boolean TRUE or FALSE
     */
    function mailbox_exists($mailbox, $subscription=false)
    {
        if ($mailbox == 'INBOX') {
            return true;
        }

        $key  = $subscription ? 'subscribed' : 'existing';

        if (is_array($this->icache[$key]) && in_array($mailbox, $this->icache[$key]))
            return true;

        if ($subscription) {
            $a_folders = $this->conn->listSubscribed('', $mailbox);
        }
        else {
            $a_folders = $this->conn->listMailboxes('', $mailbox);
        }

        if (is_array($a_folders) && in_array($mailbox, $a_folders)) {
            $this->icache[$key][] = $mailbox;
            return true;
        }

        return false;
    }


    /**
     * Returns the namespace where the folder is in
     *
     * @param string $mailbox Folder name
     *
     * @return string One of 'personal', 'other' or 'shared'
     * @access public
     */
    function mailbox_namespace($mailbox)
    {
        if ($mailbox == 'INBOX') {
            return 'personal';
        }

        foreach ($this->namespace as $type => $namespace) {
            if (is_array($namespace)) {
                foreach ($namespace as $ns) {
                    if ($len = strlen($ns[0])) {
                        if (($len > 1 && $mailbox == substr($ns[0], 0, -1))
                            || strpos($mailbox, $ns[0]) === 0
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
     * @param string $mailbox Folder name
     * @param string $mode    Mode name (out/in)
     *
     * @return string Folder name
     */
    function mod_mailbox($mailbox, $mode = 'out')
    {
        if (!strlen($mailbox)) {
            return $mailbox;
        }

        $prefix     = $this->namespace['prefix']; // see set_env()
        $prefix_len = strlen($prefix);

        if (!$prefix_len) {
            return $mailbox;
        }

        // remove prefix for output
        if ($mode == 'out') {
            if (substr($mailbox, 0, $prefix_len) === $prefix) {
                return substr($mailbox, $prefix_len);
            }
        }
        // add prefix for input (e.g. folder creation)
        else {
            return $prefix . $mailbox;
        }

        return $mailbox;
    }


    /**
     * Gets folder attributes from LIST response, e.g. \Noselect, \Noinferiors
     *
     * @param string $mailbox Folder name
     * @param bool   $force   Set to True if attributes should be refreshed
     *
     * @return array Options list
     */
    function mailbox_attributes($mailbox, $force=false)
    {
        // get attributes directly from LIST command
        if (!empty($this->conn->data['LIST']) && is_array($this->conn->data['LIST'][$mailbox])) {
            $opts = $this->conn->data['LIST'][$mailbox];
        }
        // get cached folder attributes
        else if (!$force) {
            $opts = $this->get_cache('mailboxes.attributes');
            $opts = $opts[$mailbox];
        }

        if (!is_array($opts)) {
            $this->conn->listMailboxes('', $mailbox);
            $opts = $this->conn->data['LIST'][$mailbox];
        }

        return is_array($opts) ? $opts : array();
    }


    /**
     * Gets connection (and current mailbox) data: UIDVALIDITY, EXISTS, RECENT,
     * PERMANENTFLAGS, UIDNEXT, UNSEEN
     *
     * @param string $mailbox Folder name
     *
     * @return array Data
     */
    function mailbox_data($mailbox)
    {
        if (!strlen($mailbox))
            $mailbox = $this->mailbox !== null ? $this->mailbox : 'INBOX';

        if ($this->conn->selected != $mailbox) {
            if ($this->conn->select($mailbox))
                $this->mailbox = $mailbox;
            else
                return null;
        }

        $data = $this->conn->data;

        // add (E)SEARCH result for ALL UNDELETED query
        if (!empty($this->icache['undeleted_idx'])
            && $this->icache['undeleted_idx']->getParameters('MAILBOX') == $mailbox
        ) {
            $data['UNDELETED'] = $this->icache['undeleted_idx'];
        }

        return $data;
    }


    /**
     * Returns extended information about the folder
     *
     * @param string $mailbox Folder name
     *
     * @return array Data
     */
    function mailbox_info($mailbox)
    {
        if ($this->icache['options'] && $this->icache['options']['name'] == $mailbox) {
            return $this->icache['options'];
        }

        $acl       = $this->get_capability('ACL');
        $namespace = $this->get_namespace();
        $options   = array();

        // check if the folder is a namespace prefix
        if (!empty($namespace)) {
            $mbox = $mailbox . $this->delimiter;
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
            $parts = explode($this->delimiter, $mailbox);
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

        $options['name']       = $mailbox;
        $options['attributes'] = $this->mailbox_attributes($mailbox, true);
        $options['namespace']  = $this->mailbox_namespace($mailbox);
        $options['rights']     = $acl && !$options['is_root'] ? (array)$this->my_rights($mailbox) : array();
        $options['special']    = in_array($mailbox, $this->default_folders);

        // Set 'noselect' and 'norename' flags
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

        if (!empty($options['rights'])) {
            $options['norename'] = !in_array('x', $options['rights']) && !in_array('d', $options['rights']);

            if (!$options['noselect']) {
                $options['noselect'] = !in_array('r', $options['rights']);
            }
        }
        else {
            $options['norename'] = $options['is_root'] || $options['namespace'] != 'personal';
        }

        $this->icache['options'] = $options;

        return $options;
    }


    /**
     * Synchronizes messages cache.
     *
     * @param string $mailbox Folder name
     */
    public function mailbox_sync($mailbox)
    {
        if ($mcache = $this->get_mcache_engine()) {
            $mcache->synchronize($mailbox);
        }
    }


    /**
     * Get message header names for rcube_imap_generic::fetchHeader(s)
     *
     * @return string Space-separated list of header names
     */
    private function get_fetch_headers()
    {
        $headers = explode(' ', $this->fetch_add_headers);
        $headers = array_map('strtoupper', $headers);

        if ($this->messages_caching || $this->get_all_headers)
            $headers = array_merge($headers, $this->all_headers);

        return implode(' ', array_unique($headers));
    }


    /* -----------------------------------------
     *   ACL and METADATA/ANNOTATEMORE methods
     * ----------------------------------------*/

    /**
     * Changes the ACL on the specified mailbox (SETACL)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     * @param string $acl     ACL string
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function set_acl($mailbox, $user, $acl)
    {
        if ($this->get_capability('ACL'))
            return $this->conn->setACL($mailbox, $user, $acl);

        return false;
    }


    /**
     * Removes any <identifier,rights> pair for the
     * specified user from the ACL for the specified
     * mailbox (DELETEACL)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function delete_acl($mailbox, $user)
    {
        if ($this->get_capability('ACL'))
            return $this->conn->deleteACL($mailbox, $user);

        return false;
    }


    /**
     * Returns the access control list for mailbox (GETACL)
     *
     * @param string $mailbox Mailbox name
     *
     * @return array User-rights array on success, NULL on error
     * @access public
     * @since 0.5-beta
     */
    function get_acl($mailbox)
    {
        if ($this->get_capability('ACL'))
            return $this->conn->getACL($mailbox);

        return NULL;
    }


    /**
     * Returns information about what rights can be granted to the
     * user (identifier) in the ACL for the mailbox (LISTRIGHTS)
     *
     * @param string $mailbox Mailbox name
     * @param string $user    User name
     *
     * @return array List of user rights
     * @access public
     * @since 0.5-beta
     */
    function list_rights($mailbox, $user)
    {
        if ($this->get_capability('ACL'))
            return $this->conn->listRights($mailbox, $user);

        return NULL;
    }


    /**
     * Returns the set of rights that the current user has to
     * mailbox (MYRIGHTS)
     *
     * @param string $mailbox Mailbox name
     *
     * @return array MYRIGHTS response on success, NULL on error
     * @access public
     * @since 0.5-beta
     */
    function my_rights($mailbox)
    {
        if ($this->get_capability('ACL'))
            return $this->conn->myRights($mailbox);

        return NULL;
    }


    /**
     * Sets IMAP metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $mailbox Mailbox name (empty for server metadata)
     * @param array  $entries Entry-value array (use NULL value as NIL)
     *
     * @return boolean True on success, False on failure
     * @access public
     * @since 0.5-beta
     */
    function set_metadata($mailbox, $entries)
    {
        if ($this->get_capability('METADATA') ||
            (!strlen($mailbox) && $this->get_capability('METADATA-SERVER'))
        ) {
            return $this->conn->setMetadata($mailbox, $entries);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            foreach ((array)$entries as $entry => $value) {
                list($ent, $attr) = $this->md2annotate($entry);
                $entries[$entry] = array($ent, $attr, $value);
            }
            return $this->conn->setAnnotation($mailbox, $entries);
        }

        return false;
    }


    /**
     * Unsets IMAP metadata/annotations (SETMETADATA/SETANNOTATION)
     *
     * @param string $mailbox Mailbox name (empty for server metadata)
     * @param array  $entries Entry names array
     *
     * @return boolean True on success, False on failure
     *
     * @access public
     * @since 0.5-beta
     */
    function delete_metadata($mailbox, $entries)
    {
        if ($this->get_capability('METADATA') || 
            (!strlen($mailbox) && $this->get_capability('METADATA-SERVER'))
        ) {
            return $this->conn->deleteMetadata($mailbox, $entries);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            foreach ((array)$entries as $idx => $entry) {
                list($ent, $attr) = $this->md2annotate($entry);
                $entries[$idx] = array($ent, $attr, NULL);
            }
            return $this->conn->setAnnotation($mailbox, $entries);
        }

        return false;
    }


    /**
     * Returns IMAP metadata/annotations (GETMETADATA/GETANNOTATION)
     *
     * @param string $mailbox Mailbox name (empty for server metadata)
     * @param array  $entries Entries
     * @param array  $options Command options (with MAXSIZE and DEPTH keys)
     *
     * @return array Metadata entry-value hash array on success, NULL on error
     *
     * @access public
     * @since 0.5-beta
     */
    function get_metadata($mailbox, $entries, $options=array())
    {
        if ($this->get_capability('METADATA') || 
            (!strlen($mailbox) && $this->get_capability('METADATA-SERVER'))
        ) {
            return $this->conn->getMetadata($mailbox, $entries, $options);
        }
        else if ($this->get_capability('ANNOTATEMORE') || $this->get_capability('ANNOTATEMORE2')) {
            $queries = array();
            $res     = array();

            // Convert entry names
            foreach ((array)$entries as $entry) {
                list($ent, $attr) = $this->md2annotate($entry);
                $queries[$attr][] = $ent;
            }

            // @TODO: Honor MAXSIZE and DEPTH options
            foreach ($queries as $attrib => $entry)
                if ($result = $this->conn->getAnnotation($mailbox, $entry, $attrib))
                    $res = array_merge_recursive($res, $result);

            return $res;
        }

        return NULL;
    }


    /**
     * Converts the METADATA extension entry name into the correct
     * entry-attrib names for older ANNOTATEMORE version.
     *
     * @param string $entry Entry name
     *
     * @return array Entry-attribute list, NULL if not supported (?)
     */
    private function md2annotate($entry)
    {
        if (substr($entry, 0, 7) == '/shared') {
            return array(substr($entry, 7), 'value.shared');
        }
        else if (substr($entry, 0, 8) == '/private') {
            return array(substr($entry, 8), 'value.priv');
        }

        // @TODO: log error
        return NULL;
    }


    /* --------------------------------
     *   internal caching methods
     * --------------------------------*/

    /**
     * Enable or disable indexes caching
     *
     * @param string $type Cache type (@see rcmail::get_cache)
     * @access public
     */
    function set_caching($type)
    {
        if ($type) {
            $this->caching = $type;
        }
        else {
            if ($this->cache)
                $this->cache->close();
            $this->cache   = null;
            $this->caching = false;
        }
    }

    /**
     * Getter for IMAP cache object
     */
    private function get_cache_engine()
    {
        if ($this->caching && !$this->cache) {
            $rcmail = rcmail::get_instance();
            $this->cache = $rcmail->get_cache('IMAP', $this->caching);
        }

        return $this->cache;
    }

    /**
     * Returns cached value
     *
     * @param string $key Cache key
     * @return mixed
     * @access public
     */
    function get_cache($key)
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
     * @access public
     */
    function update_cache($key, $data)
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
     * @access public
     */
    function clear_cache($key=null, $prefix_mode=false)
    {
        if ($cache = $this->get_cache_engine()) {
            $cache->remove($key, $prefix_mode);
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
    function set_messages_caching($set)
    {
        if ($set) {
            $this->messages_caching = true;
        }
        else {
            if ($this->mcache)
                $this->mcache->close();
            $this->mcache = null;
            $this->messages_caching = false;
        }
    }

    /**
     * Getter for messages cache object
     */
    private function get_mcache_engine()
    {
        if ($this->messages_caching && !$this->mcache) {
            $rcmail = rcmail::get_instance();
            if ($dbh = $rcmail->get_dbh()) {
                $this->mcache = new rcube_imap_cache(
                    $dbh, $this, $rcmail->user->ID, $this->skip_deleted);
            }
        }

        return $this->mcache;
    }

    /**
     * Clears the messages cache.
     *
     * @param string $mailbox Folder name
     * @param array  $uids    Optional message UIDs to remove from cache
     */
    function clear_message_cache($mailbox = null, $uids = null)
    {
        if ($mcache = $this->get_mcache_engine()) {
            $mcache->clear($mailbox, $uids);
        }
    }



    /* --------------------------------
     *   encoding/decoding methods
     * --------------------------------*/

    /**
     * Split an address list into a structured array list
     *
     * @param string  $input  Input string
     * @param int     $max    List only this number of addresses
     * @param boolean $decode Decode address strings
     * @return array  Indexed list of addresses
     */
    function decode_address_list($input, $max=null, $decode=true)
    {
        $a = $this->_parse_address_list($input, $decode);
        $out = array();
        // Special chars as defined by RFC 822 need to in quoted string (or escaped).
        $special_chars = '[\(\)\<\>\\\.\[\]@,;:"]';

        if (!is_array($a))
            return $out;

        $c = count($a);
        $j = 0;

        foreach ($a as $val) {
            $j++;
            $address = trim($val['address']);
            $name    = trim($val['name']);

            if ($name && $address && $name != $address)
                $string = sprintf('%s <%s>', preg_match("/$special_chars/", $name) ? '"'.addcslashes($name, '"').'"' : $name, $address);
            else if ($address)
                $string = $address;
            else if ($name)
                $string = $name;

            $out[$j] = array(
                'name'   => $name,
                'mailto' => $address,
                'string' => $string
            );

            if ($max && $j==$max)
                break;
        }

        return $out;
    }


    /**
     * Decode a message header value
     *
     * @param string  $input         Header value
     * @param boolean $remove_quotas Remove quotes if necessary
     * @return string Decoded string
     */
    function decode_header($input, $remove_quotes=false)
    {
        $str = rcube_imap::decode_mime_string((string)$input, $this->default_charset);
        if ($str[0] == '"' && $remove_quotes)
            $str = str_replace('"', '', $str);

        return $str;
    }


    /**
     * Decode a mime-encoded string to internal charset
     *
     * @param string $input    Header value
     * @param string $fallback Fallback charset if none specified
     *
     * @return string Decoded string
     * @static
     */
    public static function decode_mime_string($input, $fallback=null)
    {
        if (!empty($fallback)) {
            $default_charset = $fallback;
        }
        else {
            $default_charset = rcmail::get_instance()->config->get('default_charset', 'ISO-8859-1');
        }

        // rfc: all line breaks or other characters not found
        // in the Base64 Alphabet must be ignored by decoding software
        // delete all blanks between MIME-lines, differently we can
        // receive unnecessary blanks and broken utf-8 symbols
        $input = preg_replace("/\?=\s+=\?/", '?==?', $input);

        // encoded-word regexp
        $re = '/=\?([^?]+)\?([BbQq])\?([^\n]*?)\?=/';

        // Find all RFC2047's encoded words
        if (preg_match_all($re, $input, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            // Initialize variables
            $tmp   = array();
            $out   = '';
            $start = 0;

            foreach ($matches as $idx => $m) {
                $pos      = $m[0][1];
                $charset  = $m[1][0];
                $encoding = $m[2][0];
                $text     = $m[3][0];
                $length   = strlen($m[0][0]);

                // Append everything that is before the text to be decoded
                if ($start != $pos) {
                    $substr = substr($input, $start, $pos-$start);
                    $out   .= rcube_charset_convert($substr, $default_charset);
                    $start  = $pos;
                }
                $start += $length;

                // Per RFC2047, each string part "MUST represent an integral number
                // of characters . A multi-octet character may not be split across
                // adjacent encoded-words." However, some mailers break this, so we
                // try to handle characters spanned across parts anyway by iterating
                // through and aggregating sequential encoded parts with the same
                // character set and encoding, then perform the decoding on the
                // aggregation as a whole.

                $tmp[] = $text;
                if ($next_match = $matches[$idx+1]) {
                    if ($next_match[0][1] == $start
                        && $next_match[1][0] == $charset
                        && $next_match[2][0] == $encoding
                    ) {
                        continue;
                    }
                }

                $count = count($tmp);
                $text  = '';

                // Decode and join encoded-word's chunks
                if ($encoding == 'B' || $encoding == 'b') {
                    // base64 must be decoded a segment at a time
                    for ($i=0; $i<$count; $i++)
                        $text .= base64_decode($tmp[$i]);
                }
                else { //if ($encoding == 'Q' || $encoding == 'q') {
                    // quoted printable can be combined and processed at once
                    for ($i=0; $i<$count; $i++)
                        $text .= $tmp[$i];

                    $text = str_replace('_', ' ', $text);
                    $text = quoted_printable_decode($text);
                }

                $out .= rcube_charset_convert($text, $charset);
                $tmp = array();
            }

            // add the last part of the input string
            if ($start != strlen($input)) {
                $out .= rcube_charset_convert(substr($input, $start), $default_charset);
            }

            // return the results
            return $out;
        }

        // no encoding information, use fallback
        return rcube_charset_convert($input, $default_charset);
    }


    /**
     * Decode a mime part
     *
     * @param string $input    Input string
     * @param string $encoding Part encoding
     * @return string Decoded string
     */
    function mime_decode($input, $encoding='7bit')
    {
        switch (strtolower($encoding)) {
        case 'quoted-printable':
            return quoted_printable_decode($input);
        case 'base64':
            return base64_decode($input);
        case 'x-uuencode':
        case 'x-uue':
        case 'uue':
        case 'uuencode':
            return convert_uudecode($input);
        case '7bit':
        default:
            return $input;
        }
    }


    /* --------------------------------
     *         private methods
     * --------------------------------*/

    /**
     * Validate the given input and save to local properties
     *
     * @param string $sort_field Sort column
     * @param string $sort_order Sort order
     * @access private
     */
    private function set_sort_order($sort_field, $sort_order)
    {
        if ($sort_field != null)
            $this->sort_field = asciiwords($sort_field);
        if ($sort_order != null)
            $this->sort_order = strtoupper($sort_order) == 'DESC' ? 'DESC' : 'ASC';
    }


    /**
     * Sort mailboxes first by default folders and then in alphabethical order
     *
     * @param array $a_folders Mailboxes list
     * @access private
     */
    private function _sort_mailbox_list($a_folders)
    {
        $a_out = $a_defaults = $folders = array();

        $delimiter = $this->get_hierarchy_delimiter();

        // find default folders and skip folders starting with '.'
        foreach ($a_folders as $i => $folder) {
            if ($folder[0] == '.')
                continue;

            if (($p = array_search($folder, $this->default_folders)) !== false && !$a_defaults[$p])
                $a_defaults[$p] = $folder;
            else
                $folders[$folder] = rcube_charset_convert($folder, 'UTF7-IMAP');
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
            $this->_rsort($folder, $delimiter, $folders, $a_out);
        }

        return $a_out;
    }


    /**
     * @access private
     */
    private function _rsort($folder, $delimiter, &$list, &$out)
    {
        while (list($key, $name) = each($list)) {
	        if (strpos($name, $folder.$delimiter) === 0) {
	            // set the type of folder name variable (#1485527)
    	        $out[] = (string) $name;
	            unset($list[$key]);
	            $this->_rsort($name, $delimiter, $list, $out);
	        }
        }
        reset($list);
    }


    /**
     * Find UID of the specified message sequence ID
     *
     * @param int    $id       Message (sequence) ID
     * @param string $mailbox  Mailbox name
     *
     * @return int Message UID
     */
    function id2uid($id, $mailbox = null)
    {
        if (!strlen($mailbox)) {
            $mailbox = $this->mailbox;
        }

        if ($uid = array_search($id, (array)$this->uid_id_map[$mailbox])) {
            return $uid;
        }

        $uid = $this->conn->ID2UID($mailbox, $id);

        $this->uid_id_map[$mailbox][$uid] = $id;

        return $uid;
    }


    /**
     * Subscribe/unsubscribe a list of mailboxes and update local cache
     * @access private
     */
    private function _change_subscription($a_mboxes, $mode)
    {
        $updated = false;

        if (is_array($a_mboxes))
            foreach ($a_mboxes as $i => $mailbox) {
                $a_mboxes[$i] = $mailbox;

                if ($mode == 'subscribe')
                    $updated = $this->conn->subscribe($mailbox);
                else if ($mode == 'unsubscribe')
                    $updated = $this->conn->unsubscribe($mailbox);
            }

        // clear cached mailbox list(s)
        if ($updated) {
            $this->clear_cache('mailboxes', true);
        }

        return $updated;
    }


    /**
     * Increde/decrese messagecount for a specific mailbox
     * @access private
     */
    private function _set_messagecount($mailbox, $mode, $increment)
    {
        $mode = strtoupper($mode);
        $a_mailbox_cache = $this->get_cache('messagecount');

        if (!is_array($a_mailbox_cache[$mailbox]) || !isset($a_mailbox_cache[$mailbox][$mode]) || !is_numeric($increment))
            return false;

        // add incremental value to messagecount
        $a_mailbox_cache[$mailbox][$mode] += $increment;

        // there's something wrong, delete from cache
        if ($a_mailbox_cache[$mailbox][$mode] < 0)
            unset($a_mailbox_cache[$mailbox][$mode]);

        // write back to cache
        $this->update_cache('messagecount', $a_mailbox_cache);

        return true;
    }


    /**
     * Remove messagecount of a specific mailbox from cache
     * @access private
     */
    private function _clear_messagecount($mailbox, $mode=null)
    {
        $a_mailbox_cache = $this->get_cache('messagecount');

        if (is_array($a_mailbox_cache[$mailbox])) {
            if ($mode) {
                unset($a_mailbox_cache[$mailbox][$mode]);
            }
            else {
                unset($a_mailbox_cache[$mailbox]);
            }
            $this->update_cache('messagecount', $a_mailbox_cache);
        }
    }


    /**
     * Split RFC822 header string into an associative array
     * @access private
     */
    private function _parse_headers($headers)
    {
        $a_headers = array();
        $headers = preg_replace('/\r?\n(\t| )+/', ' ', $headers);
        $lines = explode("\n", $headers);
        $c = count($lines);

        for ($i=0; $i<$c; $i++) {
            if ($p = strpos($lines[$i], ': ')) {
                $field = strtolower(substr($lines[$i], 0, $p));
                $value = trim(substr($lines[$i], $p+1));
                if (!empty($value))
                    $a_headers[$field] = $value;
            }
        }

        return $a_headers;
    }


    /**
     * @access private
     */
    private function _parse_address_list($str, $decode=true)
    {
        // remove any newlines and carriage returns before
        $str = preg_replace('/\r?\n(\s|\t)?/', ' ', $str);

        // extract list items, remove comments
        $str = self::explode_header_string(',;', $str, true);
        $result = array();

        // simplified regexp, supporting quoted local part
        $email_rx = '(\S+|("\s*(?:[^"\f\n\r\t\v\b\s]+\s*)+"))@\S+';

        foreach ($str as $key => $val) {
            $name    = '';
            $address = '';
            $val     = trim($val);

            if (preg_match('/(.*)<('.$email_rx.')>$/', $val, $m)) {
                $address = $m[2];
                $name    = trim($m[1]);
            }
            else if (preg_match('/^('.$email_rx.')$/', $val, $m)) {
                $address = $m[1];
                $name    = '';
            }
            else {
                $name = $val;
            }

            // dequote and/or decode name
            if ($name) {
                if ($name[0] == '"' && $name[strlen($name)-1] == '"') {
                    $name = substr($name, 1, -1);
                    $name = stripslashes($name);
                }
                if ($decode) {
                    $name = $this->decode_header($name);
                }
            }

            if (!$address && $name) {
                $address = $name;
            }

            if ($address) {
                $result[$key] = array('name' => $name, 'address' => $address);
            }
        }

        return $result;
    }


    /**
     * Explodes header (e.g. address-list) string into array of strings
     * using specified separator characters with proper handling
     * of quoted-strings and comments (RFC2822)
     *
     * @param string $separator       String containing separator characters
     * @param string $str             Header string
     * @param bool   $remove_comments Enable to remove comments
     *
     * @return array Header items
     */
    static function explode_header_string($separator, $str, $remove_comments=false)
    {
        $length  = strlen($str);
        $result  = array();
        $quoted  = false;
        $comment = 0;
        $out     = '';

        for ($i=0; $i<$length; $i++) {
            // we're inside a quoted string
            if ($quoted) {
                if ($str[$i] == '"') {
                    $quoted = false;
                }
                else if ($str[$i] == '\\') {
                    if ($comment <= 0) {
                        $out .= '\\';
                    }
                    $i++;
                }
            }
            // we're inside a comment string
            else if ($comment > 0) {
                    if ($str[$i] == ')') {
                        $comment--;
                    }
                    else if ($str[$i] == '(') {
                        $comment++;
                    }
                    else if ($str[$i] == '\\') {
                        $i++;
                    }
                    continue;
            }
            // separator, add to result array
            else if (strpos($separator, $str[$i]) !== false) {
                    if ($out) {
                        $result[] = $out;
                    }
                    $out = '';
                    continue;
            }
            // start of quoted string
            else if ($str[$i] == '"') {
                    $quoted = true;
            }
            // start of comment
            else if ($remove_comments && $str[$i] == '(') {
                    $comment++;
            }

            if ($comment <= 0) {
                $out .= $str[$i];
            }
        }

        if ($out && $comment <= 0) {
            $result[] = $out;
        }

        return $result;
    }


    /**
     * This is our own debug handler for the IMAP connection
     * @access public
     */
    public function debug_handler(&$imap, $message)
    {
        write_log('imap', $message);
    }

}  // end class rcube_imap


/**
 * Class representing a message part
 *
 * @package Mail
 */
class rcube_message_part
{
    var $mime_id = '';
    var $ctype_primary = 'text';
    var $ctype_secondary = 'plain';
    var $mimetype = 'text/plain';
    var $disposition = '';
    var $filename = '';
    var $encoding = '8bit';
    var $charset = '';
    var $size = 0;
    var $headers = array();
    var $d_parameters = array();
    var $ctype_parameters = array();

    function __clone()
    {
        if (isset($this->parts))
            foreach ($this->parts as $idx => $part)
                if (is_object($part))
	                $this->parts[$idx] = clone $part;
    }
}


/**
 * Class for sorting an array of rcube_mail_header objects in a predetermined order.
 *
 * @package Mail
 * @author Eric Stadtherr
 */
class rcube_header_sorter
{
    private $uids = array();


    /**
     * Set the predetermined sort order.
     *
     * @param array $index  Numerically indexed array of IMAP UIDs
     */
    function set_index($index)
    {
        $index = array_flip($index);

        $this->uids = $index;
    }

    /**
     * Sort the array of header objects
     *
     * @param array $headers Array of rcube_mail_header objects indexed by UID
     */
    function sort_headers(&$headers)
    {
        uksort($headers, array($this, "compare_uids"));
    }

    /**
     * Sort method called by uksort()
     *
     * @param int $a Array key (UID)
     * @param int $b Array key (UID)
     */
    function compare_uids($a, $b)
    {
        // then find each sequence number in my ordered list
        $posa = isset($this->uids[$a]) ? intval($this->uids[$a]) : -1;
        $posb = isset($this->uids[$b]) ? intval($this->uids[$b]) : -1;

        // return the relative position as the comparison value
        return $posa - $posb;
    }
}
