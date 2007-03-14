<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2006, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   IMAP wrapper that implements the Iloha IMAP Library (IIL)           |
 |   See http://ilohamail.org/ for details                               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Obtain classes from the Iloha IMAP library
 */
require_once('lib/imap.inc');
require_once('lib/mime.inc');


/**
 * Interface class for accessing an IMAP server
 *
 * This is a wrapper that implements the Iloha IMAP Library (IIL)
 *
 * @package    RoundCube Webmail
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @version    1.36
 * @link       http://ilohamail.org
 */
class rcube_imap
  {
  var $db;
  var $conn;
  var $root_ns = '';
  var $root_dir = '';
  var $mailbox = 'INBOX';
  var $list_page = 1;
  var $page_size = 10;
  var $sort_field = 'date';
  var $sort_order = 'DESC';
  var $delimiter = NULL;
  var $caching_enabled = FALSE;
  var $default_folders = array('INBOX');
  var $default_folders_lc = array('inbox');
  var $cache = array();
  var $cache_keys = array();  
  var $cache_changes = array();
  var $uid_id_map = array();
  var $msg_headers = array();
  var $capabilities = array();
  var $skip_deleted = FALSE;
  var $search_set = NULL;
  var $search_subject = '';
  var $search_string = '';
  var $search_charset = '';
  var $debug_level = 1;


  /**
   * Object constructor
   *
   * @param  object  Database connection
   */
  function __construct($db_conn)
    {
    $this->db = $db_conn;
    }


  /**
   * PHP 4 object constructor
   *
   * @see  rcube_imap::__construct
   */
  function rcube_imap($db_conn)
    {
    $this->__construct($db_conn);
    }


  /**
   * Connect to an IMAP server
   *
   * @param  string   Host to connect
   * @param  string   Username for IMAP account
   * @param  string   Password for IMAP account
   * @param  number   Port to connect to
   * @param  boolean  Use SSL connection
   * @return boolean  TRUE on success, FALSE on failure
   * @access public
   */
  function connect($host, $user, $pass, $port=143, $use_ssl=FALSE)
    {
    global $ICL_SSL, $ICL_PORT, $IMAP_USE_INTERNAL_DATE;
    
    // check for Open-SSL support in PHP build
    if ($use_ssl && in_array('openssl', get_loaded_extensions()))
      $ICL_SSL = TRUE;
    else if ($use_ssl)
      {
      raise_error(array('code' => 403, 'type' => 'imap', 'file' => __FILE__,
                        'message' => 'Open SSL not available;'), TRUE, FALSE);
      $port = 143;
      }

    $ICL_PORT = $port;
    $IMAP_USE_INTERNAL_DATE = false;
    
    $this->conn = iil_Connect($host, $user, $pass, array('imap' => 'check'));
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->port = $port;
    $this->ssl = $use_ssl;
    
    // print trace mesages
    if ($this->conn && ($this->debug_level & 8))
      console($this->conn->message);
    
    // write error log
    else if (!$this->conn && $GLOBALS['iil_error'])
      {
      raise_error(array('code' => 403,
                       'type' => 'imap',
                       'message' => $GLOBALS['iil_error']), TRUE, FALSE);
      }

    // get server properties
    if ($this->conn)
      {
      $this->_parse_capability($this->conn->capability);
      
      if (!empty($this->conn->delimiter))
        $this->delimiter = $this->conn->delimiter;
      if (!empty($this->conn->rootdir))
        {
        $this->set_rootdir($this->conn->rootdir);
        $this->root_ns = ereg_replace('[\.\/]$', '', $this->conn->rootdir);
        }
      }

    return $this->conn ? TRUE : FALSE;
    }


  /**
   * Close IMAP connection
   * Usually done on script shutdown
   *
   * @access public
   */
  function close()
    {    
    if ($this->conn)
      iil_Close($this->conn);
    }


  /**
   * Close IMAP connection and re-connect
   * This is used to avoid some strange socket errors when talking to Courier IMAP
   *
   * @access public
   */
  function reconnect()
    {
    $this->close();
    $this->connect($this->host, $this->user, $this->pass, $this->port, $this->ssl);
    }


  /**
   * Set a root folder for the IMAP connection.
   *
   * Only folders within this root folder will be displayed
   * and all folder paths will be translated using this folder name
   *
   * @param  string   Root folder
   * @access public
   */
  function set_rootdir($root)
    {
    if (ereg('[\.\/]$', $root)) //(substr($root, -1, 1)==='/')
      $root = substr($root, 0, -1);

    $this->root_dir = $root;
    
    if (empty($this->delimiter))
      $this->get_hierarchy_delimiter();
    }


  /**
   * This list of folders will be listed above all other folders
   *
   * @param  array  Indexed list of folder names
   * @access public
   */
  function set_default_mailboxes($arr)
    {
    if (is_array($arr))
      {
      $this->default_folders = $arr;
      $this->default_folders_lc = array();

      // add inbox if not included
      if (!in_array_nocase('INBOX', $this->default_folders))
        array_unshift($this->default_folders, 'INBOX');

      // create a second list with lower cased names
      foreach ($this->default_folders as $mbox)
        $this->default_folders_lc[] = strtolower($mbox);
      }
    }


  /**
   * Set internal mailbox reference.
   *
   * All operations will be perfomed on this mailbox/folder
   *
   * @param  string  Mailbox/Folder name
   * @access public
   */
  function set_mailbox($new_mbox)
    {
    $mailbox = $this->_mod_mailbox($new_mbox);

    if ($this->mailbox == $mailbox)
      return;

    $this->mailbox = $mailbox;

    // clear messagecount cache for this mailbox
    $this->_clear_messagecount($mailbox);
    }


  /**
   * Set internal list page
   *
   * @param  number  Page number to list
   * @access public
   */
  function set_page($page)
    {
    $this->list_page = (int)$page;
    }


  /**
   * Set internal page size
   *
   * @param  number  Number of messages to display on one page
   * @access public
   */
  function set_pagesize($size)
    {
    $this->page_size = (int)$size;
    }
    

  /**
   * Save a set of message ids for future message listing methods
   *
   * @param  array  List of IMAP fields to search in
   * @param  string Search string
   * @param  array  List of message ids or NULL if empty
   */
  function set_search_set($subject, $str=null, $msgs=null, $charset=null)
    {
    if (is_array($subject) && $str == null && $msgs == null)
      list($subject, $str, $msgs, $charset) = $subject;
    if ($msgs != null && !is_array($msgs))
      $msgs = split(',', $msgs);
      
    $this->search_subject = $subject;
    $this->search_string = $str;
    $this->search_set = is_array($msgs) ? $msgs : NULL;
    $this->search_charset = $charset;
    }


  /**
   * Return the saved search set as hash array
   */
  function get_search_set()
    {
    return array($this->search_subject, $this->search_string, $this->search_set, $this->search_charset);
    }


  /**
   * Returns the currently used mailbox name
   *
   * @return  string Name of the mailbox/folder
   * @access  public
   */
  function get_mailbox_name()
    {
    return $this->conn ? $this->_mod_mailbox($this->mailbox, 'out') : '';
    }


  /**
   * Returns the IMAP server's capability
   *
   * @param   string  Capability name
   * @return  mixed   Capability value or TRUE if supported, FALSE if not
   * @access  public
   */
  function get_capability($cap)
    {
    $cap = strtoupper($cap);
    return $this->capabilities[$cap];
    }


  /**
   * Returns the delimiter that is used by the IMAP server for folder separation
   *
   * @return  string  Delimiter string
   * @access  public
   */
  function get_hierarchy_delimiter()
    {
    if ($this->conn && empty($this->delimiter))
      $this->delimiter = iil_C_GetHierarchyDelimiter($this->conn);

    if (empty($this->delimiter))
      $this->delimiter = '/';

    return $this->delimiter;
    }


  /**
   * Public method for mailbox listing.
   *
   * Converts mailbox name with root dir first
   *
   * @param   string  Optional root folder
   * @param   string  Optional filter for mailbox listing
   * @return  array   List of mailboxes/folders
   * @access  public
   */
  function list_mailboxes($root='', $filter='*')
    {
    $a_out = array();
    $a_mboxes = $this->_list_mailboxes($root, $filter);

    foreach ($a_mboxes as $mbox_row)
      {
      $name = $this->_mod_mailbox($mbox_row, 'out');
      if (strlen($name))
        $a_out[] = $name;
      }

    // INBOX should always be available
    if (!in_array_nocase('INBOX', $a_out))
      array_unshift($a_out, 'INBOX');

    // sort mailboxes
    $a_out = $this->_sort_mailbox_list($a_out);

    return $a_out;
    }


  /**
   * Private method for mailbox listing
   *
   * @return  array   List of mailboxes/folders
   * @access  private
   * @see     rcube_imap::list_mailboxes
   */
  function _list_mailboxes($root='', $filter='*')
    {
    $a_defaults = $a_out = array();
    
    // get cached folder list    
    $a_mboxes = $this->get_cache('mailboxes');
    if (is_array($a_mboxes))
      return $a_mboxes;

    // retrieve list of folders from IMAP server
    $a_folders = iil_C_ListSubscribed($this->conn, $this->_mod_mailbox($root), $filter);
    
    if (!is_array($a_folders) || !sizeof($a_folders))
      $a_folders = array();

    // write mailboxlist to cache
    $this->update_cache('mailboxes', $a_folders);
    
    return $a_folders;
    }


  /**
   * Get message count for a specific mailbox
   *
   * @param   string   Mailbox/folder name
   * @param   string   Mode for count [ALL|UNSEEN|RECENT]
   * @param   boolean  Force reading from server and update cache
   * @return  number   Number of messages
   * @access  public   
   */
  function messagecount($mbox_name='', $mode='ALL', $force=FALSE)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    return $this->_messagecount($mailbox, $mode, $force);
    }


  /**
   * Private method for getting nr of messages
   *
   * @access  private
   * @see     rcube_imap::messagecount
   */
  function _messagecount($mailbox='', $mode='ALL', $force=FALSE)
    {
    $a_mailbox_cache = FALSE;
    $mode = strtoupper($mode);

    if (empty($mailbox))
      $mailbox = $this->mailbox;
      
    // count search set
    if ($this->search_set && $mailbox == $this->mailbox && $mode == 'ALL')
      return count($this->search_set);

    $a_mailbox_cache = $this->get_cache('messagecount');
    
    // return cached value
    if (!$force && is_array($a_mailbox_cache[$mailbox]) && isset($a_mailbox_cache[$mailbox][$mode]))
      return $a_mailbox_cache[$mailbox][$mode];

    // RECENT count is fetched abit different      
    if ($mode == 'RECENT')
       $count = iil_C_CheckForRecent($this->conn, $mailbox);

    // use SEARCH for message counting
    else if ($this->skip_deleted)
      {
      $search_str = "ALL UNDELETED";

      // get message count and store in cache
      if ($mode == 'UNSEEN')
        $search_str .= " UNSEEN";

      // get message count using SEARCH
      // not very performant but more precise (using UNDELETED)
      $count = 0;
      $index = $this->_search_index($mailbox, $search_str);
      if (is_array($index))
        {
        $str = implode(",", $index);
        if (!empty($str))
          $count = count($index);
        }
      }
    else
      {
      if ($mode == 'UNSEEN')
        $count = iil_C_CountUnseen($this->conn, $mailbox);
      else
        $count = iil_C_CountMessages($this->conn, $mailbox);
      }

    if (!is_array($a_mailbox_cache[$mailbox]))
      $a_mailbox_cache[$mailbox] = array();
      
    $a_mailbox_cache[$mailbox][$mode] = (int)$count;

    // write back to cache
    $this->update_cache('messagecount', $a_mailbox_cache);

    return (int)$count;
    }


  /**
   * Public method for listing headers
   * convert mailbox name with root dir first
   *
   * @param   string   Mailbox/folder name
   * @param   number   Current page to list
   * @param   string   Header field to sort by
   * @param   string   Sort order [ASC|DESC]
   * @return  array    Indexed array with message header objects
   * @access  public   
   */
  function list_headers($mbox_name='', $page=NULL, $sort_field=NULL, $sort_order=NULL)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    return $this->_list_headers($mailbox, $page, $sort_field, $sort_order);
    }


  /**
   * Private method for listing message headers
   *
   * @access  private
   * @see     rcube_imap::list_headers
   */
  function _list_headers($mailbox='', $page=NULL, $sort_field=NULL, $sort_order=NULL, $recursive=FALSE)
    {
    if (!strlen($mailbox))
      return array();

    // use saved message set
    if ($this->search_set && $mailbox == $this->mailbox)
      return $this->_list_header_set($mailbox, $this->search_set, $page, $sort_field, $sort_order);

    if ($sort_field!=NULL)
      $this->sort_field = $sort_field;
    if ($sort_order!=NULL)
      $this->sort_order = strtoupper($sort_order);

    $max = $this->_messagecount($mailbox);
    $start_msg = ($this->list_page-1) * $this->page_size;

    list($begin, $end) = $this->_get_message_range($max, $page);

    // mailbox is empty
    if ($begin >= $end)
      return array();
      
    $headers_sorted = FALSE;
    $cache_key = $mailbox.'.msg';
    $cache_status = $this->check_cache_status($mailbox, $cache_key);

    // cache is OK, we can get all messages from local cache
    if ($cache_status>0)
      {
      $a_msg_headers = $this->get_message_cache($cache_key, $start_msg, $start_msg+$this->page_size, $this->sort_field, $this->sort_order);
      $headers_sorted = TRUE;
      }
    // cache is dirty, sync it
    else if ($this->caching_enabled && $cache_status==-1 && !$recursive)
      {
      $this->sync_header_index($mailbox);
      return $this->_list_headers($mailbox, $page, $this->sort_field, $this->sort_order, TRUE);
      }
    else
      {
      // retrieve headers from IMAP
      if ($this->get_capability('sort') && ($msg_index = iil_C_Sort($this->conn, $mailbox, $this->sort_field, $this->skip_deleted ? 'UNDELETED' : '')))
        {        
        $msgs = $msg_index[$begin];
        for ($i=$begin+1; $i < $end; $i++)
          $msgs = $msgs.','.$msg_index[$i];
        }
      else
        {
        $msgs = sprintf("%d:%d", $begin+1, $end);

        $i = 0;
        for ($msg_seqnum = $begin; $msg_seqnum <= $end; $msg_seqnum++)
          $msg_index[$i++] = $msg_seqnum;
        }

      // use this class for message sorting
      $sorter = new rcube_header_sorter();
      $sorter->set_sequence_numbers($msg_index);

      // fetch reuested headers from server
      $a_msg_headers = array();
      $deleted_count = $this->_fetch_headers($mailbox, $msgs, $a_msg_headers, $cache_key);

      // delete cached messages with a higher index than $max
      $this->clear_message_cache($cache_key, $max);


      // kick child process to sync cache
      // ...

      }


    // return empty array if no messages found
	if (!is_array($a_msg_headers) || empty($a_msg_headers))
		return array();


    // if not already sorted
    if (!$headers_sorted)
      {
      $sorter->sort_headers($a_msg_headers);

      if ($this->sort_order == 'DESC')
        $a_msg_headers = array_reverse($a_msg_headers);
      }

    return array_values($a_msg_headers);
    }



  /**
   * Public method for listing a specific set of headers
   * convert mailbox name with root dir first
   *
   * @param   string   Mailbox/folder name
   * @param   array    List of message ids to list
   * @param   number   Current page to list
   * @param   string   Header field to sort by
   * @param   string   Sort order [ASC|DESC]
   * @return  array    Indexed array with message header objects
   * @access  public   
   */
  function list_header_set($mbox_name='', $msgs, $page=NULL, $sort_field=NULL, $sort_order=NULL)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    return $this->_list_header_set($mailbox, $msgs, $page, $sort_field, $sort_order);    
    }
    

  /**
   * Private method for listing a set of message headers
   *
   * @access  private
   * @see     rcube_imap::list_header_set
   */
  function _list_header_set($mailbox, $msgs, $page=NULL, $sort_field=NULL, $sort_order=NULL)
    {
    // also accept a comma-separated list of message ids
    if (is_string($msgs))
      $msgs = split(',', $msgs);
      
    if (!strlen($mailbox) || empty($msgs))
      return array();

    if ($sort_field!=NULL)
      $this->sort_field = $sort_field;
    if ($sort_order!=NULL)
      $this->sort_order = strtoupper($sort_order);

    $max = count($msgs);
    $start_msg = ($this->list_page-1) * $this->page_size;

    // fetch reuested headers from server
    $a_msg_headers = array();
    $this->_fetch_headers($mailbox, join(',', $msgs), $a_msg_headers, NULL);

    // return empty array if no messages found
    if (!is_array($a_msg_headers) || empty($a_msg_headers))
      return array();

    // if not already sorted
    $a_msg_headers = iil_SortHeaders($a_msg_headers, $this->sort_field, $this->sort_order);

    // only return the requested part of the set
    return array_slice(array_values($a_msg_headers), $start_msg, min($max-$start_msg, $this->page_size));
    }


  /**
   * Helper function to get first and last index of the requested set
   *
   * @param  number  message count
   * @param  mixed   page number to show, or string 'all'
   * @return array   array with two values: first index, last index
   * @access private
   */
  function _get_message_range($max, $page)
    {
    $start_msg = ($this->list_page-1) * $this->page_size;
    
    if ($page=='all')
      {
      $begin = 0;
      $end = $max;
      }
    else if ($this->sort_order=='DESC')
      {
      $begin = $max - $this->page_size - $start_msg;
      $end =   $max - $start_msg;
      }
    else
      {
      $begin = $start_msg;
      $end   = $start_msg + $this->page_size;
      }

    if ($begin < 0) $begin = 0;
    if ($end < 0) $end = $max;
    if ($end > $max) $end = $max;
    
    return array($begin, $end);
    }
    
    

  /**
   * Fetches message headers
   * Used for loop
   *
   * @param  string  Mailbox name
   * @param  string  Message index to fetch
   * @param  array   Reference to message headers array
   * @param  array   Array with cache index
   * @return number  Number of deleted messages
   * @access private
   */
  function _fetch_headers($mailbox, $msgs, &$a_msg_headers, $cache_key)
    {
    // cache is incomplete
    $cache_index = $this->get_message_cache_index($cache_key);
    
    // fetch reuested headers from server
    $a_header_index = iil_C_FetchHeaders($this->conn, $mailbox, $msgs);
    $deleted_count = 0;
    
    if (!empty($a_header_index))
      {
      foreach ($a_header_index as $i => $headers)
        {
        if ($headers->deleted && $this->skip_deleted)
          {
          // delete from cache
          if ($cache_index[$headers->id] && $cache_index[$headers->id] == $headers->uid)
            $this->remove_message_cache($cache_key, $headers->id);

          $deleted_count++;
          continue;
          }

        // add message to cache
        if ($this->caching_enabled && $cache_index[$headers->id] != $headers->uid)
          $this->add_message_cache($cache_key, $headers->id, $headers);

        $a_msg_headers[$headers->uid] = $headers;
        }
      }
        
    return $deleted_count;
    }
    
  
  /**
   * Return sorted array of message UIDs
   *
   * @param string Mailbox to get index from
   * @param string Sort column
   * @param string Sort order [ASC, DESC]
   * @return array Indexed array with message ids
   */
  function message_index($mbox_name='', $sort_field=NULL, $sort_order=NULL)
    {
    if ($sort_field!=NULL)
      $this->sort_field = $sort_field;
    if ($sort_order!=NULL)
      $this->sort_order = strtoupper($sort_order);

    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    $key = "$mbox:".$this->sort_field.":".$this->sort_order.".msgi";

    // have stored it in RAM
    if (isset($this->cache[$key]))
      return $this->cache[$key];

    // check local cache
    $cache_key = $mailbox.'.msg';
    $cache_status = $this->check_cache_status($mailbox, $cache_key);

    // cache is OK
    if ($cache_status>0)
      {
      $a_index = $this->get_message_cache_index($cache_key, TRUE, $this->sort_field, $this->sort_order);
      return array_values($a_index);
      }


    // fetch complete message index
    $msg_count = $this->_messagecount($mailbox);
    if ($this->get_capability('sort') && ($a_index = iil_C_Sort($this->conn, $mailbox, $this->sort_field, '', TRUE)))
      {
      if ($this->sort_order == 'DESC')
        $a_index = array_reverse($a_index);

      $this->cache[$key] = $a_index;

      }
    else
      {
      $a_index = iil_C_FetchHeaderIndex($this->conn, $mailbox, "1:$msg_count", $this->sort_field);
      $a_uids = iil_C_FetchUIDs($this->conn, $mailbox);
    
      if ($this->sort_order=="ASC")
        asort($a_index);
      else if ($this->sort_order=="DESC")
        arsort($a_index);
        
      $i = 0;
      $this->cache[$key] = array();
      foreach ($a_index as $index => $value)
        $this->cache[$key][$i++] = $a_uids[$index];
      }

    return $this->cache[$key];
    }


  function sync_header_index($mailbox)
    {
    $cache_key = $mailbox.'.msg';
    $cache_index = $this->get_message_cache_index($cache_key);
    $msg_count = $this->_messagecount($mailbox);

    // fetch complete message index
    $a_message_index = iil_C_FetchHeaderIndex($this->conn, $mailbox, "1:$msg_count", 'UID');
        
    foreach ($a_message_index as $id => $uid)
      {
      // message in cache at correct position
      if ($cache_index[$id] == $uid)
        {
        unset($cache_index[$id]);
        continue;
        }
        
      // message in cache but in wrong position
      if (in_array((string)$uid, $cache_index, TRUE))
        {
        unset($cache_index[$id]);        
        }
      
      // other message at this position
      if (isset($cache_index[$id]))
        {
        $this->remove_message_cache($cache_key, $id);
        unset($cache_index[$id]);
        }
        

      // fetch complete headers and add to cache
      $headers = iil_C_FetchHeader($this->conn, $mailbox, $id);
      $this->add_message_cache($cache_key, $headers->id, $headers);
      }

    // those ids that are still in cache_index have been deleted      
    if (!empty($cache_index))
      {
      foreach ($cache_index as $id => $uid)
        $this->remove_message_cache($cache_key, $id);
      }
    }


  /**
   * Invoke search request to IMAP server
   *
   * @param  string  mailbox name to search in
   * @param  string  search criteria (ALL, TO, FROM, SUBJECT, etc)
   * @param  string  search string
   * @return array   search results as list of message ids
   * @access public
   */
  function search($mbox_name='', $criteria='ALL', $str=NULL, $charset=NULL)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;

    // have an array of criterias => execute multiple searches
    if (is_array($criteria) && $str)
      {
      $results = array();
      foreach ($criteria as $crit)
        if ($search_result = $this->search($mbox_name, $crit, $str, $charset))
          $results = array_merge($results, $search_result);
      
      $results = array_unique($results);
      $this->set_search_set($criteria, $str, $results, $charset);
      return $results;
      }
    else if ($str && $criteria)
      {
      $search = (!empty($charset) ? "CHARSET $charset " : '') . sprintf("%s {%d}\r\n%s", $criteria, strlen($str), $str);
      $results = $this->_search_index($mailbox, $search);

      // try search with ISO charset (should be supported by server)
      if (empty($results) && !empty($charset) && $charset!='ISO-8859-1')
        $results = $this->search($mbox_name, $criteria, rcube_charset_convert($str, $charset, 'ISO-8859-1'), 'ISO-8859-1');
      
      $this->set_search_set($criteria, $str, $results, $charset);
      return $results;
      }
    else
      return $this->_search_index($mailbox, $criteria);
    }    


  /**
   * Private search method
   *
   * @return array   search results as list of message ids
   * @access private
   * @see rcube_imap::search()
   */
  function _search_index($mailbox, $criteria='ALL')
    {
    $a_messages = iil_C_Search($this->conn, $mailbox, $criteria);
    // clean message list (there might be some empty entries)
    if (is_array($a_messages))
      {
      foreach ($a_messages as $i => $val)
        if (empty($val))
          unset($a_messages[$i]);
      }
        
    return $a_messages;
    }
    
  
  /**
   * Refresh saved search set
   */
  function refresh_search()
    {
    if (!empty($this->search_subject) && !empty($this->search_string))
      $this->search_set = $this->search('', $this->search_subject, $this->search_string, $this->search_charset);
      
    return $this->get_search_set();
    }


  /**
   * Return message headers object of a specific message
   *
   * @param int     Message ID
   * @param string  Mailbox to read from 
   * @param boolean True if $id is the message UID
   * @return object Message headers representation
   */
  function get_headers($id, $mbox_name=NULL, $is_uid=TRUE)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    $uid = $is_uid ? $id : $this->_id2uid($id);

    // get cached headers
    if ($uid && ($headers = &$this->get_cached_message($mailbox.'.msg', $uid)))
      return $headers;

    $headers = iil_C_FetchHeader($this->conn, $mailbox, $id, $is_uid);

    // write headers cache
    if ($headers)
      {
      if ($is_uid)
        $this->uid_id_map[$mbox_name][$uid] = $headers->id;

      $this->add_message_cache($mailbox.'.msg', $headers->id, $headers);
      }

    return $headers;
    }


  /**
   * Fetch body structure from the IMAP server and build
   * an object structure similar to the one generated by PEAR::Mail_mimeDecode
   *
   * @param Int Message UID to fetch
   * @return object Standard object tree or False on failure
   */
  function &get_structure($uid)
    {
    $cache_key = $this->mailbox.'.msg';
    $headers = &$this->get_cached_message($cache_key, $uid, true);

    // return cached message structure
    if (is_object($headers) && is_object($headers->structure))
      return $headers->structure;
    
    // resolve message sequence number
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;

	$structure_str = iil_C_FetchStructureString($this->conn, $this->mailbox, $msg_id); 
	$structure = iml_GetRawStructureArray($structure_str);
	$struct = false;
	
    // parse structure and add headers
    if (!empty($structure))
      {
      $this->_msg_id = $msg_id;
      $headers = $this->get_headers($msg_id, NULL, FALSE);
      
      $struct = &$this->_structure_part($structure);
      $struct->headers = get_object_vars($headers);

      // don't trust given content-type
      if (empty($struct->parts) && !empty($struct->headers['ctype']))
        {
        $struct->mime_id = '1';
        $struct->mimetype = strtolower($struct->headers['ctype']);
        list($struct->ctype_primary, $struct->ctype_secondary) = explode('/', $struct->mimetype);
        }

      // write structure to cache
      if ($this->caching_enabled)
        $this->add_message_cache($cache_key, $msg_id, $headers, $struct);
      }
	
	return $struct;
	}

  
  /**
   * Build message part object
   *
   * @access private
   */
  function &_structure_part($part, $count=0, $parent='')
    {
    $struct = new rcube_message_part;
    $struct->mime_id = empty($parent) ? (string)$count : "$parent.$count";
    
    // multipart
    if (is_array($part[0]))
      {
      $struct->ctype_primary = 'multipart';
      
      // find first non-array entry
      for ($i=1; count($part); $i++)
        if (!is_array($part[$i]))
          {
          $struct->ctype_secondary = strtolower($part[$i]);
          break;
          }
          
      $struct->mimetype = 'multipart/'.$struct->ctype_secondary;

      $struct->parts = array();
      for ($i=0, $count=0; $i<count($part); $i++)
        if (is_array($part[$i]) && count($part[$i]) > 5)
          $struct->parts[] = $this->_structure_part($part[$i], ++$count, $struct->mime_id);

      return $struct;      
      }
    
    
    // regular part
    $struct->ctype_primary = strtolower($part[0]);
    $struct->ctype_secondary = strtolower($part[1]);
    $struct->mimetype = $struct->ctype_primary.'/'.$struct->ctype_secondary;
	
    // read content type parameters
	if (is_array($part[2]))
	  {
	  $struct->ctype_parameters = array();
      for ($i=0; $i<count($part[2]); $i+=2)
        $struct->ctype_parameters[strtolower($part[2][$i])] = $part[2][$i+1];
        
      if (isset($struct->ctype_parameters['charset']))
        $struct->charset = $struct->ctype_parameters['charset'];
	  }
	  
	// read content encoding
	if (!empty($part[5]) && $part[5]!='NIL')
	  {
	  $struct->encoding = strtolower($part[5]);
	  $struct->headers['content-transfer-encoding'] = $struct->encoding;
	  }
	  
	// get part size
	if (!empty($part[6]) && $part[6]!='NIL')
	  $struct->size = intval($part[6]);

	// read part disposition
    $di = count($part) - 2;
    if ((is_array($part[$di]) && count($part[$di]) == 2 && is_array($part[$di][1])) ||
        (is_array($part[--$di]) && count($part[$di]) == 2))
      {
      $struct->disposition = strtolower($part[$di][0]);

      if (is_array($part[$di][1]))
        for ($n=0; $n<count($part[$di][1]); $n+=2)
          $struct->d_parameters[strtolower($part[$di][1][$n])] = $part[$di][1][$n+1];
      }
      
    // get child parts
    if (is_array($part[8]) && $di != 8)
      {
      $struct->parts = array();
      for ($i=0, $count=0; $i<count($part[8]); $i++)
        if (is_array($part[8][$i]) && count($part[8][$i]) > 5)
          $struct->parts[] = $this->_structure_part($part[8][$i], ++$count, $struct->mime_id);
      }
      
	// get part ID
	if (!empty($part[3]) && $part[3]!='NIL')
	  {
	  $struct->content_id = $part[3];
	  $struct->headers['content-id'] = $part[3];
	  
	  if (empty($struct->disposition))
	    $struct->disposition = 'inline';
	  }

    // fetch message headers if message/rfc822
    if ($struct->ctype_primary=='message')
      {
      $headers = iil_C_FetchPartBody($this->conn, $this->mailbox, $this->_msg_id, $struct->mime_id.'.HEADER');
      $struct->headers = $this->_parse_headers($headers);
      }
  
  	return $struct;
    }
    
  
  /**
   * Return a flat array with references to all parts, indexed by part numbers
   *
   * @param object Message body structure
   * @return Array with part number -> object pairs
   */
  function get_mime_numbers(&$structure)
    {
    $a_parts = array();
    $this->_get_part_numbers($structure, $a_parts);
    return $a_parts;
    }
  
  
  /**
   * Helper method for recursive calls
   *
   * @access 
   */
  function _get_part_numbers(&$part, &$a_parts)
    {
    if ($part->mime_id)
      $a_parts[$part->mime_id] = &$part;
      
    if (is_array($part->parts))
      for ($i=0; $i<count($part->parts); $i++)
        $this->_get_part_numbers($part->parts[$i], $a_parts);
    }
  

  /**
   * Fetch message body of a specific message from the server
   *
   * @param  int    Message UID
   * @param  string Part number
   * @param  object Part object created by get_structure()
   * @param  mixed  True to print part, ressource to write part contents in
   * @return Message/part body if not printed
   */
  function &get_message_part($uid, $part=1, $o_part=NULL, $print=NULL)
    {
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;
    
    // get part encoding if not provided
    if (!is_object($o_part))
      {
      $structure_str = iil_C_FetchStructureString($this->conn, $this->mailbox, $msg_id); 
      $structure = iml_GetRawStructureArray($structure_str);
      $part_type = iml_GetPartTypeCode($structure, $part);
      $o_part = new rcube_message_part;
      $o_part->ctype_primary = $part_type==0 ? 'text' : ($part_type==2 ? 'message' : 'other');
      $o_part->encoding = strtolower(iml_GetPartEncodingString($structure, $part));
      $o_part->charset = iml_GetPartCharset($structure, $part);
      }
      
    // TODO: Add caching for message parts

    if ($print)
      {
      iil_C_HandlePartBody($this->conn, $this->mailbox, $msg_id, $part, ($o_part->encoding=='base64'?3:2));
      $body = TRUE;
      }
    else
      {
      $body = iil_C_HandlePartBody($this->conn, $this->mailbox, $msg_id, $part, 1);

      // decode part body
      if ($o_part->encoding=='base64' || $o_part->encoding=='quoted-printable')
        $body = $this->mime_decode($body, $o_part->encoding);

      // convert charset (if text or message part)
      if ($o_part->ctype_primary=='text' || $o_part->ctype_primary=='message')
        {
        // assume ISO-8859-1 if no charset specified
        if (empty($o_part->charset))
          $o_part->charset = 'ISO-8859-1';

        $body = rcube_charset_convert($body, $o_part->charset);
        }
      }

    return $body;
    }


  /**
   * Fetch message body of a specific message from the server
   *
   * @param  int    Message UID
   * @return Message/part body
   * @see    ::get_message_part()
   */
  function &get_body($uid, $part=1)
    {
    return $this->get_message_part($uid, $part);
    }


  /**
   * Returns the whole message source as string
   *
   * @param int  Message UID
   * @return Message source string
   */
  function &get_raw_body($uid)
    {
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;

	$body = iil_C_FetchPartHeader($this->conn, $this->mailbox, $msg_id, NULL);
	$body .= iil_C_HandlePartBody($this->conn, $this->mailbox, $msg_id, NULL, 1);

    return $body;    
    }
    

  /**
   * Sends the whole message source to stdout
   *
   * @param int  Message UID
   */ 
  function print_raw_body($uid)
    {
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;

	print iil_C_FetchPartHeader($this->conn, $this->mailbox, $msg_id, NULL);
	flush();
	iil_C_HandlePartBody($this->conn, $this->mailbox, $msg_id, NULL, 2);
    }


  /**
   * Set message flag to one or several messages
   *
   * @param mixed  Message UIDs as array or as comma-separated string
   * @param string Flag to set: SEEN, UNDELETED, DELETED, RECENT, ANSWERED, DRAFT
   * @return True on success, False on failure
   */
  function set_flag($uids, $flag)
    {
    $flag = strtoupper($flag);
    $msg_ids = array();
    if (!is_array($uids))
      $uids = explode(',',$uids);
      
    foreach ($uids as $uid) {
      $msg_ids[$uid] = $this->_uid2id($uid);
    }
      
    if ($flag=='UNDELETED')
      $result = iil_C_Undelete($this->conn, $this->mailbox, join(',', array_values($msg_ids)));
    else if ($flag=='UNSEEN')
      $result = iil_C_Unseen($this->conn, $this->mailbox, join(',', array_values($msg_ids)));
    else
      $result = iil_C_Flag($this->conn, $this->mailbox, join(',', array_values($msg_ids)), $flag);

    // reload message headers if cached
    $cache_key = $this->mailbox.'.msg';
    if ($this->caching_enabled)
      {
      foreach ($msg_ids as $uid => $id)
        {
        if ($cached_headers = $this->get_cached_message($cache_key, $uid))
          {
          $this->remove_message_cache($cache_key, $id);
          //$this->get_headers($uid);
          }
        }

      // close and re-open connection
      // this prevents connection problems with Courier 
      $this->reconnect();
      }

    // set nr of messages that were flaged
    $count = count($msg_ids);

    // clear message count cache
    if ($result && $flag=='SEEN')
      $this->_set_messagecount($this->mailbox, 'UNSEEN', $count*(-1));
    else if ($result && $flag=='UNSEEN')
      $this->_set_messagecount($this->mailbox, 'UNSEEN', $count);
    else if ($result && $flag=='DELETED')
      $this->_set_messagecount($this->mailbox, 'ALL', $count*(-1));

    return $result;
    }


  // append a mail message (source) to a specific mailbox
  function save_message($mbox_name, &$message)
    {
    $mbox_name = stripslashes($mbox_name);
    $mailbox = $this->_mod_mailbox($mbox_name);

    // make sure mailbox exists
    if (in_array($mailbox, $this->_list_mailboxes()))
      $saved = iil_C_Append($this->conn, $mailbox, $message);

    if ($saved)
      {
      // increase messagecount of the target mailbox
      $this->_set_messagecount($mailbox, 'ALL', 1);
      }
          
    return $saved;
    }


  // move a message from one mailbox to another
  function move_message($uids, $to_mbox, $from_mbox='')
    {
    $to_mbox = stripslashes($to_mbox);
    $from_mbox = stripslashes($from_mbox);
    $to_mbox = $this->_mod_mailbox($to_mbox);
    $from_mbox = $from_mbox ? $this->_mod_mailbox($from_mbox) : $this->mailbox;

    // make sure mailbox exists
    if (!in_array($to_mbox, $this->_list_mailboxes()))
      {
      if (in_array(strtolower($to_mbox), $this->default_folders))
        $this->create_mailbox($to_mbox, TRUE);
      else
        return FALSE;
      }

    // convert the list of uids to array
    $a_uids = is_string($uids) ? explode(',', $uids) : (is_array($uids) ? $uids : NULL);
    
    // exit if no message uids are specified
    if (!is_array($a_uids))
      return false;

    // convert uids to message ids
    $a_mids = array();
    foreach ($a_uids as $uid)
      $a_mids[] = $this->_uid2id($uid, $from_mbox);

    $moved = iil_C_Move($this->conn, join(',', $a_mids), $from_mbox, $to_mbox);
    
    // send expunge command in order to have the moved message
    // really deleted from the source mailbox
    if ($moved)
      {
      $this->_expunge($from_mbox, FALSE);
      $this->_clear_messagecount($from_mbox);
      $this->_clear_messagecount($to_mbox);
      }
      
    // remove message ids from search set
    if ($moved && $this->search_set && $from_mbox == $this->mailbox)
      $this->search_set = array_diff($this->search_set, $a_mids);

    // update cached message headers
    $cache_key = $from_mbox.'.msg';
    if ($moved && ($a_cache_index = $this->get_message_cache_index($cache_key)))
      {
      $start_index = 100000;
      foreach ($a_uids as $uid)
        {
        if (($index = array_search($uid, $a_cache_index)) !== FALSE)
          $start_index = min($index, $start_index);
        }

      // clear cache from the lowest index on
      $this->clear_message_cache($cache_key, $start_index);
      }

    return $moved;
    }


  // mark messages as deleted and expunge mailbox
  function delete_message($uids, $mbox_name='')
    {
    $mbox_name = stripslashes($mbox_name);
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;

    // convert the list of uids to array
    $a_uids = is_string($uids) ? explode(',', $uids) : (is_array($uids) ? $uids : NULL);
    
    // exit if no message uids are specified
    if (!is_array($a_uids))
      return false;


    // convert uids to message ids
    $a_mids = array();
    foreach ($a_uids as $uid)
      $a_mids[] = $this->_uid2id($uid, $mailbox);
        
    $deleted = iil_C_Delete($this->conn, $mailbox, join(',', $a_mids));
    
    // send expunge command in order to have the deleted message
    // really deleted from the mailbox
    if ($deleted)
      {
      $this->_expunge($mailbox, FALSE);
      $this->_clear_messagecount($mailbox);
      }

    // remove message ids from search set
    if ($moved && $this->search_set && $mailbox == $this->mailbox)
      $this->search_set = array_diff($this->search_set, $a_mids);

    // remove deleted messages from cache
    $cache_key = $mailbox.'.msg';
    if ($deleted && ($a_cache_index = $this->get_message_cache_index($cache_key)))
      {
      $start_index = 100000;
      foreach ($a_uids as $uid)
        {
        if (($index = array_search($uid, $a_cache_index)) !== FALSE)
          $start_index = min($index, $start_index);
        }

      // clear cache from the lowest index on
      $this->clear_message_cache($cache_key, $start_index);
      }

    return $deleted;
    }


  // clear all messages in a specific mailbox
  function clear_mailbox($mbox_name=NULL)
    {
    $mbox_name = stripslashes($mbox_name);
    $mailbox = !empty($mbox_name) ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    $msg_count = $this->_messagecount($mailbox, 'ALL');
    
    if ($msg_count>0)
      {
      $cleared = iil_C_ClearFolder($this->conn, $mailbox);
      
      // make sure the message count cache is cleared as well
      if ($cleared)
        {
        $this->clear_message_cache($mailbox.'.msg');      
        $a_mailbox_cache = $this->get_cache('messagecount');
        unset($a_mailbox_cache[$mailbox]);
        $this->update_cache('messagecount', $a_mailbox_cache);
        }
        
      return $cleared;
      }
    else
      return 0;
    }


  // send IMAP expunge command and clear cache
  function expunge($mbox_name='', $clear_cache=TRUE)
    {
    $mbox_name = stripslashes($mbox_name);
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    return $this->_expunge($mailbox, $clear_cache);
    }


  // send IMAP expunge command and clear cache
  function _expunge($mailbox, $clear_cache=TRUE)
    {
    $result = iil_C_Expunge($this->conn, $mailbox);

    if ($result>=0 && $clear_cache)
      {
      //$this->clear_message_cache($mailbox.'.msg');
      $this->_clear_messagecount($mailbox);
      }
      
    return $result;
    }


  /* --------------------------------
   *        folder managment
   * --------------------------------*/


  /**
   * Get a list of all folders available on the IMAP server
   * 
   * @param string IMAP root dir
   * @return array Inbdexed array with folder names 
   */
  function list_unsubscribed($root='')
    {
    static $sa_unsubscribed;
    
    if (is_array($sa_unsubscribed))
      return $sa_unsubscribed;
      
    // retrieve list of folders from IMAP server
    $a_mboxes = iil_C_ListMailboxes($this->conn, $this->_mod_mailbox($root), '*');

    // modify names with root dir
    foreach ($a_mboxes as $mbox_name)
      {
      $name = $this->_mod_mailbox($mbox_name, 'out');
      if (strlen($name))
        $a_folders[] = $name;
      }

    // filter folders and sort them
    $sa_unsubscribed = $this->_sort_mailbox_list($a_folders);
    return $sa_unsubscribed;
    }


  /**
   * Get quota
   * added by Nuny
   */
  function get_quota()
    {
    if ($this->get_capability('QUOTA'))
      return iil_C_GetQuota($this->conn);
	
    return FALSE;
    }


  /**
   * subscribe to a specific mailbox(es)
   */ 
  function subscribe($mbox_name, $mode='subscribe')
    {
    if (is_array($mbox_name))
      $a_mboxes = $mbox_name;
    else if (is_string($mbox_name) && strlen($mbox_name))
      $a_mboxes = explode(',', $mbox_name);
    
    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'subscribe');
    }


  /**
   * unsubscribe mailboxes
   */
  function unsubscribe($mbox_name)
    {
    if (is_array($mbox_name))
      $a_mboxes = $mbox_name;
    else if (is_string($mbox_name) && strlen($mbox_name))
      $a_mboxes = explode(',', $mbox_name);

    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'unsubscribe');
    }


  /**
   * Create a new mailbox on the server and register it in local cache
   *
   * @param string  New mailbox name (as utf-7 string)
   * @param boolean True if the new mailbox should be subscribed
   * @param string  Name of the created mailbox, false on error
   */
  function create_mailbox($name, $subscribe=FALSE)
    {
    $result = FALSE;
    
    // replace backslashes
    $name = preg_replace('/[\\\]+/', '-', $name);

    // reduce mailbox name to 100 chars
    $name = substr($name, 0, 100);

    $abs_name = $this->_mod_mailbox($name);
    $a_mailbox_cache = $this->get_cache('mailboxes');

    if (strlen($abs_name) && (!is_array($a_mailbox_cache) || !in_array_nocase($abs_name, $a_mailbox_cache)))
      $result = iil_C_CreateFolder($this->conn, $abs_name);

    // try to subscribe it
    if ($subscribe)
      $this->subscribe($name);

    return $result ? $name : FALSE;
    }


  /**
   * Set a new name to an existing mailbox
   *
   * @param string Mailbox to rename (as utf-7 string)
   * @param string New mailbox name (as utf-7 string)
   * @param string Name of the renames mailbox, false on error
   */
  function rename_mailbox($mbox_name, $new_name)
    {
    $result = FALSE;

    // replace backslashes
    $name = preg_replace('/[\\\]+/', '-', $new_name);
        
    // encode mailbox name and reduce it to 100 chars
    $name = substr($new_name, 0, 100);

    // make absolute path
    $mailbox = $this->_mod_mailbox($mbox_name);
    $abs_name = $this->_mod_mailbox($name);
    
    // check if mailbox is subscribed
    $a_subscribed = $this->_list_mailboxes();
    $subscribed = in_array($mailbox, $a_subscribed);
    
    // unsubscribe folder
    if ($subscribed)
      iil_C_UnSubscribe($this->conn, $mailbox);

    if (strlen($abs_name))
      $result = iil_C_RenameFolder($this->conn, $mailbox, $abs_name);

    // clear cache
    if ($result)
      {
      $this->clear_message_cache($mailbox.'.msg');
      $this->clear_cache('mailboxes');      
      }

    // try to subscribe it
    if ($result && $subscribed)
      iil_C_Subscribe($this->conn, $abs_name);

    return $result ? $name : FALSE;
    }


  /**
   * remove mailboxes from server
   */
  function delete_mailbox($mbox_name)
    {
    $deleted = FALSE;

    if (is_array($mbox_name))
      $a_mboxes = $mbox_name;
    else if (is_string($mbox_name) && strlen($mbox_name))
      $a_mboxes = explode(',', $mbox_name);

    if (is_array($a_mboxes))
      foreach ($a_mboxes as $mbox_name)
        {
        $mailbox = $this->_mod_mailbox($mbox_name);

        // unsubscribe mailbox before deleting
        iil_C_UnSubscribe($this->conn, $mailbox);

        // send delete command to server
        $result = iil_C_DeleteFolder($this->conn, $mailbox);
        if ($result>=0)
          $deleted = TRUE;
        }

    // clear mailboxlist cache
    if ($deleted)
      {
      $this->clear_message_cache($mailbox.'.msg');
      $this->clear_cache('mailboxes');
      }

    return $deleted;
    }


  /**
   * Create all folders specified as default
   */
  function create_default_folders()
    {
    $a_folders = iil_C_ListMailboxes($this->conn, $this->_mod_mailbox(''), '*');
    $a_subscribed = iil_C_ListSubscribed($this->conn, $this->_mod_mailbox(''), '*');
    
    // create default folders if they do not exist
    foreach ($this->default_folders as $folder)
      {
      $abs_name = $this->_mod_mailbox($folder);
      if (!in_array_nocase($abs_name, $a_subscribed))
        {
        if (!in_array_nocase($abs_name, $a_folders))
          $this->create_mailbox($folder, TRUE);
        else
          $this->subscribe($folder);
        }
      }
    }



  /* --------------------------------
   *   internal caching methods
   * --------------------------------*/


  function set_caching($set)
    {
    if ($set && is_object($this->db))
      $this->caching_enabled = TRUE;
    else
      $this->caching_enabled = FALSE;
    }


  function get_cache($key)
    {
    // read cache
    if (!isset($this->cache[$key]) && $this->caching_enabled)
      {
      $cache_data = $this->_read_cache_record('IMAP.'.$key);
      $this->cache[$key] = strlen($cache_data) ? unserialize($cache_data) : FALSE;
      }
    
    return $this->cache[$key];
    }


  function update_cache($key, $data)
    {
    $this->cache[$key] = $data;
    $this->cache_changed = TRUE;
    $this->cache_changes[$key] = TRUE;
    }


  function write_cache()
    {
    if ($this->caching_enabled && $this->cache_changed)
      {
      foreach ($this->cache as $key => $data)
        {
        if ($this->cache_changes[$key])
          $this->_write_cache_record('IMAP.'.$key, serialize($data));
        }
      }    
    }


  function clear_cache($key=NULL)
    {
    if ($key===NULL)
      {
      foreach ($this->cache as $key => $data)
        $this->_clear_cache_record('IMAP.'.$key);

      $this->cache = array();
      $this->cache_changed = FALSE;
      $this->cache_changes = array();
      }
    else
      {
      $this->_clear_cache_record('IMAP.'.$key);
      $this->cache_changes[$key] = FALSE;
      unset($this->cache[$key]);
      }
    }



  function _read_cache_record($key)
    {
    $cache_data = FALSE;
    
    if ($this->db)
      {
      // get cached data from DB
      $sql_result = $this->db->query(
        "SELECT cache_id, data
         FROM ".get_table_name('cache')."
         WHERE  user_id=?
         AND    cache_key=?",
        $_SESSION['user_id'],
        $key);

      if ($sql_arr = $this->db->fetch_assoc($sql_result))
        {
        $cache_data = $sql_arr['data'];
        $this->cache_keys[$key] = $sql_arr['cache_id'];
        }
      }

    return $cache_data;    
    }
    

  function _write_cache_record($key, $data)
    {
    if (!$this->db)
      return FALSE;

    // check if we already have a cache entry for this key
    if (!isset($this->cache_keys[$key]))
      {
      $sql_result = $this->db->query(
        "SELECT cache_id
         FROM ".get_table_name('cache')."
         WHERE  user_id=?
         AND    cache_key=?",
        $_SESSION['user_id'],
        $key);
                                     
      if ($sql_arr = $this->db->fetch_assoc($sql_result))
        $this->cache_keys[$key] = $sql_arr['cache_id'];
      else
        $this->cache_keys[$key] = FALSE;
      }

    // update existing cache record
    if ($this->cache_keys[$key])
      {
      $this->db->query(
        "UPDATE ".get_table_name('cache')."
         SET    created=".$this->db->now().",
                data=?
         WHERE  user_id=?
         AND    cache_key=?",
        $data,
        $_SESSION['user_id'],
        $key);
      }
    // add new cache record
    else
      {
      $this->db->query(
        "INSERT INTO ".get_table_name('cache')."
         (created, user_id, cache_key, data)
         VALUES (".$this->db->now().", ?, ?, ?)",
        $_SESSION['user_id'],
        $key,
        $data);
      }
    }


  function _clear_cache_record($key)
    {
    $this->db->query(
      "DELETE FROM ".get_table_name('cache')."
       WHERE  user_id=?
       AND    cache_key=?",
      $_SESSION['user_id'],
      $key);
    }



  /* --------------------------------
   *   message caching methods
   * --------------------------------*/
   

  // checks if the cache is up-to-date
  // return: -3 = off, -2 = incomplete, -1 = dirty
  function check_cache_status($mailbox, $cache_key)
    {
    if (!$this->caching_enabled)
      return -3;

    $cache_index = $this->get_message_cache_index($cache_key, TRUE);
    $msg_count = $this->_messagecount($mailbox);
    $cache_count = count($cache_index);

    // console("Cache check: $msg_count !== ".count($cache_index));

    if ($cache_count==$msg_count)
      {
      // get highest index
      $header = iil_C_FetchHeader($this->conn, $mailbox, "$msg_count");
      $cache_uid = array_pop($cache_index);
      
      // uids of highest message matches -> cache seems OK
      if ($cache_uid == $header->uid)
        return 1;

      // cache is dirty
      return -1;
      }
    // if cache count differs less than 10% report as dirty
    else if (abs($msg_count - $cache_count) < $msg_count/10)
      return -1;
    else
      return -2;
    }



  function get_message_cache($key, $from, $to, $sort_field, $sort_order)
    {
    $cache_key = "$key:$from:$to:$sort_field:$sort_order";
    $db_header_fields = array('idx', 'uid', 'subject', 'from', 'to', 'cc', 'date', 'size');
    
    if (!in_array($sort_field, $db_header_fields))
      $sort_field = 'idx';
    
    if ($this->caching_enabled && !isset($this->cache[$cache_key]))
      {
      $this->cache[$cache_key] = array();
      $sql_result = $this->db->limitquery(
        "SELECT idx, uid, headers
         FROM ".get_table_name('messages')."
         WHERE  user_id=?
         AND    cache_key=?
         ORDER BY ".$this->db->quoteIdentifier($sort_field)." ".
         strtoupper($sort_order),
        $from,
        $to-$from,
        $_SESSION['user_id'],
        $key);

      while ($sql_arr = $this->db->fetch_assoc($sql_result))
        {
        $uid = $sql_arr['uid'];
        $this->cache[$cache_key][$uid] = unserialize($sql_arr['headers']);
        }
      }
      
    return $this->cache[$cache_key];
    }


  function &get_cached_message($key, $uid, $struct=false)
    {
    if (!$this->caching_enabled)
      return FALSE;

    $internal_key = '__single_msg';
    if ($this->caching_enabled && (!isset($this->cache[$internal_key][$uid]) ||
        ($struct && empty($this->cache[$internal_key][$uid]->structure))))
      {
      $sql_select = "idx, uid, headers" . ($struct ? ", structure" : '');
      $sql_result = $this->db->query(
        "SELECT $sql_select
         FROM ".get_table_name('messages')."
         WHERE  user_id=?
         AND    cache_key=?
         AND    uid=?",
        $_SESSION['user_id'],
        $key,
        $uid);

      if ($sql_arr = $this->db->fetch_assoc($sql_result))
        {
        $this->cache[$internal_key][$uid] = unserialize($sql_arr['headers']);
        if (is_object($this->cache[$internal_key][$uid]) && !empty($sql_arr['structure']))
          $this->cache[$internal_key][$uid]->structure = unserialize($sql_arr['structure']);
        }
      }

    return $this->cache[$internal_key][$uid];
    }

   
  function get_message_cache_index($key, $force=FALSE, $sort_col='idx', $sort_order='ASC')
    {
    static $sa_message_index = array();
    
    // empty key -> empty array
    if (empty($key))
      return array();
    
    if (!empty($sa_message_index[$key]) && !$force)
      return $sa_message_index[$key];
    
    $sa_message_index[$key] = array();
    $sql_result = $this->db->query(
      "SELECT idx, uid
       FROM ".get_table_name('messages')."
       WHERE  user_id=?
       AND    cache_key=?
       ORDER BY ".$this->db->quote_identifier($sort_col)." ".$sort_order,
      $_SESSION['user_id'],
      $key);

    while ($sql_arr = $this->db->fetch_assoc($sql_result))
      $sa_message_index[$key][$sql_arr['idx']] = $sql_arr['uid'];
      
    return $sa_message_index[$key];
    }


  function add_message_cache($key, $index, $headers, $struct=null)
    {
    if (empty($key) || !is_object($headers) || empty($headers->uid))
      return;
      
    // check for an existing record (probly headers are cached but structure not)
    $sql_result = $this->db->query(
        "SELECT message_id
         FROM ".get_table_name('messages')."
         WHERE  user_id=?
         AND    cache_key=?
         AND    uid=?
         AND    del<>1",
        $_SESSION['user_id'],
        $key,
        $headers->uid);

    // update cache record
    if ($sql_arr = $this->db->fetch_assoc($sql_result))
      {
      $this->db->query(
        "UPDATE ".get_table_name('messages')."
         SET   idx=?, headers=?, structure=?
         WHERE message_id=?",
        $index,
        serialize($headers),
        is_object($struct) ? serialize($struct) : NULL,
        $sql_arr['message_id']
        );
      }
    else  // insert new record
      {
      $this->db->query(
        "INSERT INTO ".get_table_name('messages')."
         (user_id, del, cache_key, created, idx, uid, subject, ".$this->db->quoteIdentifier('from').", ".$this->db->quoteIdentifier('to').", cc, date, size, headers, structure)
         VALUES (?, 0, ?, ".$this->db->now().", ?, ?, ?, ?, ?, ?, ".$this->db->fromunixtime($headers->timestamp).", ?, ?, ?)",
        $_SESSION['user_id'],
        $key,
        $index,
        $headers->uid,
        (string)substr($this->decode_header($headers->subject, TRUE), 0, 128),
        (string)substr($this->decode_header($headers->from, TRUE), 0, 128),
        (string)substr($this->decode_header($headers->to, TRUE), 0, 128),
        (string)substr($this->decode_header($headers->cc, TRUE), 0, 128),
        (int)$headers->size,
        serialize($headers),
        is_object($struct) ? serialize($struct) : NULL
        );
      }
    }
    
    
  function remove_message_cache($key, $index)
    {
    $this->db->query(
      "DELETE FROM ".get_table_name('messages')."
       WHERE  user_id=?
       AND    cache_key=?
       AND    idx=?",
      $_SESSION['user_id'],
      $key,
      $index);
    }


  function clear_message_cache($key, $start_index=1)
    {
    $this->db->query(
      "DELETE FROM ".get_table_name('messages')."
       WHERE  user_id=?
       AND    cache_key=?
       AND    idx>=?",
      $_SESSION['user_id'],
      $key,
      $start_index);
    }




  /* --------------------------------
   *   encoding/decoding methods
   * --------------------------------*/

  
  function decode_address_list($input, $max=NULL)
    {
    $a = $this->_parse_address_list($input);
    $out = array();
    
    if (!is_array($a))
      return $out;

    $c = count($a);
    $j = 0;

    foreach ($a as $val)
      {
      $j++;
      $address = $val['address'];
      $name = preg_replace(array('/^[\'"]/', '/[\'"]$/'), '', trim($val['name']));
      $string = $name!==$address ? sprintf('%s <%s>', strpos($name, ',')!==FALSE ? '"'.$name.'"' : $name, $address) : $address;
      
      $out[$j] = array('name' => $name,
                       'mailto' => $address,
                       'string' => $string);
              
      if ($max && $j==$max)
        break;
      }
    
    return $out;
    }


  function decode_header($input, $remove_quotes=FALSE)
    {
    $str = $this->decode_mime_string((string)$input);
    if ($str{0}=='"' && $remove_quotes)
      {
      $str = str_replace('"', '', $str);
      }
    
    return $str;
    }


  /**
   * Decode a mime-encoded string to internal charset
   *
   * @access static
   */
  function decode_mime_string($input, $recursive=false)
    {
    $out = '';

    $pos = strpos($input, '=?');
    if ($pos !== false)
      {
      $out = substr($input, 0, $pos);
  
      $end_cs_pos = strpos($input, "?", $pos+2);
      $end_en_pos = strpos($input, "?", $end_cs_pos+1);
      $end_pos = strpos($input, "?=", $end_en_pos+1);
  
      $encstr = substr($input, $pos+2, ($end_pos-$pos-2));
      $rest = substr($input, $end_pos+2);

      $out .= rcube_imap::_decode_mime_string_part($encstr);
      $out .= rcube_imap::decode_mime_string($rest);

      return $out;
      }
      
    // no encoding information, defaults to what is specified in the class header
    return rcube_charset_convert($input, 'ISO-8859-1');
    }


  /**
   * Decode a part of a mime-encoded string
   *
   * @access static
   */
  function _decode_mime_string_part($str)
    {
    $a = explode('?', $str);
    $count = count($a);

    // should be in format "charset?encoding?base64_string"
    if ($count >= 3)
      {
      for ($i=2; $i<$count; $i++)
        $rest.=$a[$i];

      if (($a[1]=="B")||($a[1]=="b"))
        $rest = base64_decode($rest);
      else if (($a[1]=="Q")||($a[1]=="q"))
        {
        $rest = str_replace("_", " ", $rest);
        $rest = quoted_printable_decode($rest);
        }

      return rcube_charset_convert($rest, $a[0]);
      }
    else
      return $str;    // we dont' know what to do with this  
    }


  function mime_decode($input, $encoding='7bit')
    {
    switch (strtolower($encoding))
      {
      case '7bit':
        return $input;
        break;
      
      case 'quoted-printable':
        return quoted_printable_decode($input);
        break;
      
      case 'base64':
        return base64_decode($input);
        break;
      
      default:
        return $input;
      }
    }


  function mime_encode($input, $encoding='7bit')
    {
    switch ($encoding)
      {
      case 'quoted-printable':
        return quoted_printable_encode($input);
        break;

      case 'base64':
        return base64_encode($input);
        break;

      default:
        return $input;
      }
    }


  // convert body chars according to the ctype_parameters
  function charset_decode($body, $ctype_param)
    {
    if (is_array($ctype_param) && !empty($ctype_param['charset']))
      return rcube_charset_convert($body, $ctype_param['charset']);

    // defaults to what is specified in the class header
    return rcube_charset_convert($body,  'ISO-8859-1');
    }




  /* --------------------------------
   *         private methods
   * --------------------------------*/


  function _mod_mailbox($mbox_name, $mode='in')
    {
    if ((!empty($this->root_ns) && $this->root_ns == $mbox_name) || $mbox_name == 'INBOX')
      return $mbox_name;

    if (!empty($this->root_dir) && $mode=='in') 
      $mbox_name = $this->root_dir.$this->delimiter.$mbox_name;
    else if (strlen($this->root_dir) && $mode=='out') 
      $mbox_name = substr($mbox_name, strlen($this->root_dir)+1);

    return $mbox_name;
    }


  // sort mailboxes first by default folders and then in alphabethical order
  function _sort_mailbox_list($a_folders)
    {
    $a_out = $a_defaults = array();

    // find default folders and skip folders starting with '.'
    foreach($a_folders as $i => $folder)
      {
      if ($folder{0}=='.')
      	continue;

      if (($p = array_search(strtolower($folder), $this->default_folders_lc))!==FALSE)
      	$a_defaults[$p] = $folder;
      else
        $a_out[] = $folder;
      }

    sort($a_out);
    ksort($a_defaults);
    
    return array_merge($a_defaults, $a_out);
    }

  function get_id($uid, $mbox_name=NULL) 
    {
      return $this->_uid2id($uid, $mbox_name);
    }
  
  function get_uid($id,$mbox_name=NULL)
    {
      return $this->_id2uid($id, $mbox_name);
    }

  function _uid2id($uid, $mbox_name=NULL)
    {
    if (!$mbox_name)
      $mbox_name = $this->mailbox;
      
    if (!isset($this->uid_id_map[$mbox_name][$uid]))
      $this->uid_id_map[$mbox_name][$uid] = iil_C_UID2ID($this->conn, $mbox_name, $uid);

    return $this->uid_id_map[$mbox_name][$uid];
    }

  function _id2uid($id, $mbox_name=NULL)
    {
    if (!$mbox_name)
      $mbox_name = $this->mailbox;
      
    return iil_C_ID2UID($this->conn, $mbox_name, $id);
    }


  // parse string or array of server capabilities and put them in internal array
  function _parse_capability($caps)
    {
    if (!is_array($caps))
      $cap_arr = explode(' ', $caps);
    else
      $cap_arr = $caps;
    
    foreach ($cap_arr as $cap)
      {
      if ($cap=='CAPABILITY')
        continue;

      if (strpos($cap, '=')>0)
        {
        list($key, $value) = explode('=', $cap);
        if (!is_array($this->capabilities[$key]))
          $this->capabilities[$key] = array();
          
        $this->capabilities[$key][] = $value;
        }
      else
        $this->capabilities[$cap] = TRUE;
      }
    }


  // subscribe/unsubscribe a list of mailboxes and update local cache
  function _change_subscription($a_mboxes, $mode)
    {
    $updated = FALSE;
    
    if (is_array($a_mboxes))
      foreach ($a_mboxes as $i => $mbox_name)
        {
        $mailbox = $this->_mod_mailbox($mbox_name);
        $a_mboxes[$i] = $mailbox;

        if ($mode=='subscribe')
          $result = iil_C_Subscribe($this->conn, $mailbox);
        else if ($mode=='unsubscribe')
          $result = iil_C_UnSubscribe($this->conn, $mailbox);

        if ($result>=0)
          $updated = TRUE;
        }
        
    // get cached mailbox list    
    if ($updated)
      {
      $a_mailbox_cache = $this->get_cache('mailboxes');
      if (!is_array($a_mailbox_cache))
        return $updated;

      // modify cached list
      if ($mode=='subscribe')
        $a_mailbox_cache = array_merge($a_mailbox_cache, $a_mboxes);
      else if ($mode=='unsubscribe')
        $a_mailbox_cache = array_diff($a_mailbox_cache, $a_mboxes);
        
      // write mailboxlist to cache
      $this->update_cache('mailboxes', $this->_sort_mailbox_list($a_mailbox_cache));
      }

    return $updated;
    }


  // increde/decrese messagecount for a specific mailbox
  function _set_messagecount($mbox_name, $mode, $increment)
    {
    $a_mailbox_cache = FALSE;
    $mailbox = $mbox_name ? $mbox_name : $this->mailbox;
    $mode = strtoupper($mode);

    $a_mailbox_cache = $this->get_cache('messagecount');
    
    if (!is_array($a_mailbox_cache[$mailbox]) || !isset($a_mailbox_cache[$mailbox][$mode]) || !is_numeric($increment))
      return FALSE;
    
    // add incremental value to messagecount
    $a_mailbox_cache[$mailbox][$mode] += $increment;
    
    // there's something wrong, delete from cache
    if ($a_mailbox_cache[$mailbox][$mode] < 0)
      unset($a_mailbox_cache[$mailbox][$mode]);

    // write back to cache
    $this->update_cache('messagecount', $a_mailbox_cache);
    
    return TRUE;
    }


  // remove messagecount of a specific mailbox from cache
  function _clear_messagecount($mbox_name='')
    {
    $a_mailbox_cache = FALSE;
    $mailbox = $mbox_name ? $mbox_name : $this->mailbox;

    $a_mailbox_cache = $this->get_cache('messagecount');

    if (is_array($a_mailbox_cache[$mailbox]))
      {
      unset($a_mailbox_cache[$mailbox]);
      $this->update_cache('messagecount', $a_mailbox_cache);
      }
    }


  // split RFC822 header string into an associative array
  function _parse_headers($headers)
    {
    $a_headers = array();
    $lines = explode("\n", $headers);
    $c = count($lines);
    for ($i=0; $i<$c; $i++)
      {
      if ($p = strpos($lines[$i], ': '))
        {
        $field = strtolower(substr($lines[$i], 0, $p));
        $value = trim(substr($lines[$i], $p+1));
        if (!empty($value))
          $a_headers[$field] = $value;
        }
      }
    
    return $a_headers;
    }


  function _parse_address_list($str)
    {
    // remove any newlines and carriage returns before
    $a = $this->_explode_quoted_string('[,;]', preg_replace( "/[\r\n]/", " ", $str));
    $result = array();
    
    foreach ($a as $key => $val)
      {
      $val = str_replace("\"<", "\" <", $val);
      $sub_a = $this->_explode_quoted_string(' ', $this->decode_header($val));
      $result[$key]['name'] = '';

      foreach ($sub_a as $k => $v)
        {
        if ((strpos($v, '@') > 0) && (strpos($v, '.') > 0)) 
          $result[$key]['address'] = str_replace('<', '', str_replace('>', '', $v));
        else
          $result[$key]['name'] .= (empty($result[$key]['name'])?'':' ').str_replace("\"",'',stripslashes($v));
        }
        
      if (empty($result[$key]['name']))
        $result[$key]['name'] = $result[$key]['address'];        
      }
    
    return $result;
    }


  function _explode_quoted_string($delimiter, $string)
    {
    $result = array();
    $strlen = strlen($string);
    for ($q=$p=$i=0; $i < $strlen; $i++)
    {
      if ($string{$i} == "\"" && $string{$i-1} != "\\")
        $q = $q ? false : true;
      else if (!$q && preg_match("/$delimiter/", $string{$i}))
      {
        $result[] = substr($string, $p, $i - $p);
        $p = $i + 1;
      }
    }
    
    $result[] = substr($string, $p);
    return $result;
    }
  }


/**
 * Class representing a message part
 */
class rcube_message_part
{
  var $mime_id = '';
  var $ctype_primary = 'text';
  var $ctype_secondary = 'plain';
  var $mimetype = 'text/plain';
  var $disposition = '';
  var $encoding = '8bit';
  var $charset = '';
  var $size = 0;
  var $headers = array();
  var $d_parameters = array();
  var $ctype_parameters = array();

}


/**
 * rcube_header_sorter
 * 
 * Class for sorting an array of iilBasicHeader objects in a predetermined order.
 *
 * @author Eric Stadtherr
 */
class rcube_header_sorter
{
   var $sequence_numbers = array();
   
   /**
    * set the predetermined sort order.
    *
    * @param array $seqnums numerically indexed array of IMAP message sequence numbers
    */
   function set_sequence_numbers($seqnums)
   {
      $this->sequence_numbers = $seqnums;
   }
 
   /**
    * sort the array of header objects
    *
    * @param array $headers array of iilBasicHeader objects indexed by UID
    */
   function sort_headers(&$headers)
   {
      /*
       * uksort would work if the keys were the sequence number, but unfortunately
       * the keys are the UIDs.  We'll use uasort instead and dereference the value
       * to get the sequence number (in the "id" field).
       * 
       * uksort($headers, array($this, "compare_seqnums")); 
       */
       uasort($headers, array($this, "compare_seqnums"));
   }
 
   /**
    * get the position of a message sequence number in my sequence_numbers array
    *
    * @param integer $seqnum message sequence number contained in sequence_numbers  
    */
   function position_of($seqnum)
   {
      $c = count($this->sequence_numbers);
      for ($pos = 0; $pos <= $c; $pos++)
      {
         if ($this->sequence_numbers[$pos] == $seqnum)
            return $pos;
      }
      return -1;
   }
 
   /**
    * Sort method called by uasort()
    */
   function compare_seqnums($a, $b)
   {
      // First get the sequence number from the header object (the 'id' field).
      $seqa = $a->id;
      $seqb = $b->id;
      
      // then find each sequence number in my ordered list
      $posa = $this->position_of($seqa);
      $posb = $this->position_of($seqb);
      
      // return the relative position as the comparison value
      $ret = $posa - $posb;
      return $ret;
   }
}


/**
 * Add quoted-printable encoding to a given string
 * 
 * @param string  $input      string to encode
 * @param int     $line_max   add new line after this number of characters
 * @param boolena $space_conf true if spaces should be converted into =20
 * @return encoded string
 */
function quoted_printable_encode($input, $line_max=76, $space_conv=false)
  {
  $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
  $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
  $eol = "\r\n";
  $escape = "=";
  $output = "";

  while( list(, $line) = each($lines))
    {
    //$line = rtrim($line); // remove trailing white space -> no =20\r\n necessary
    $linlen = strlen($line);
    $newline = "";
    for($i = 0; $i < $linlen; $i++)
      {
      $c = substr( $line, $i, 1 );
      $dec = ord( $c );
      if ( ( $i == 0 ) && ( $dec == 46 ) ) // convert first point in the line into =2E
        {
        $c = "=2E";
        }
      if ( $dec == 32 )
        {
        if ( $i == ( $linlen - 1 ) ) // convert space at eol only
          {
          $c = "=20";
          }
        else if ( $space_conv )
          {
          $c = "=20";
          }
        }
      else if ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) )  // always encode "\t", which is *not* required
        {
        $h2 = floor($dec/16);
        $h1 = floor($dec%16);
        $c = $escape.$hex["$h2"].$hex["$h1"];
        }
         
      if ( (strlen($newline) + strlen($c)) >= $line_max )  // CRLF is not counted
        {
        $output .= $newline.$escape.$eol; // soft line break; " =\r\n" is okay
        $newline = "";
        // check if newline first character will be point or not
        if ( $dec == 46 )
          {
          $c = "=2E";
          }
        }
      $newline .= $c;
      } // end of for
    $output .= $newline.$eol;
    } // end of while

  return trim($output);
  }


?>
