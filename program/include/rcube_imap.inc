<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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
require_once('lib/utf7.inc');


/**
 * Interface class for accessing an IMAP server
 *
 * This is a wrapper that implements the Iloha IMAP Library (IIL)
 *
 * @package    RoundCube Webmail
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @version    1.26
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
  var $default_folders = array('inbox', 'drafts', 'sent', 'junk', 'trash');
  var $cache = array();
  var $cache_keys = array();  
  var $cache_changes = array();
  var $uid_id_map = array();
  var $msg_headers = array();
  var $capabilities = array();
  var $skip_deleted = FALSE;
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

    // get account namespace
    if ($this->conn)
      {
      $this->_parse_capability($this->conn->capability);
      iil_C_NameSpace($this->conn);
      
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
      $this->default_folders = array();
      
      // add mailbox names lower case
      foreach ($arr as $mbox_row)
        $this->default_folders[] = strtolower($mbox_row);
      
      // add inbox if not included
      if (!in_array('inbox', $this->default_folders))
        array_unshift($arr, 'inbox');
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

    // create Default folders if they do not exist
    global $CONFIG;
    foreach ($CONFIG['default_imap_folders'] as $folder)
      {
      if (!in_array_nocase($folder, $a_folders))
        {
        $this->create_mailbox($folder, TRUE);
        $this->subscribe($folder);
        }
      }

    $a_folders = iil_C_ListSubscribed($this->conn, $this->_mod_mailbox($root), $filter);
    $a_mailbox_cache = array();

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
      
    if ($sort_field!=NULL)
      $this->sort_field = $sort_field;
    if ($sort_order!=NULL)
      $this->sort_order = strtoupper($sort_order);

    $max = $this->_messagecount($mailbox);
    $start_msg = ($this->list_page-1) * $this->page_size;
    
    list($begin, $end) = $this->_get_message_range($max, $page);
    
    /*    
    if ($page=='all')
      {
      $begin = 0;
      $end = $max;
      }
    else if (!$this->get_capability('sort') && $this->sort_order=='DESC')
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
	*/
	
//console("fetch headers $start_msg to ".($start_msg+$this->page_size)." (msg $begin to $end)");

    $headers_sorted = FALSE;
    $cache_key = $mailbox.'.msg';
    $cache_status = $this->check_cache_status($mailbox, $cache_key);

//console("Cache status = $cache_status");
    
    // cache is OK, we can get all messages from local cache
    if ($cache_status>0)
      {
      $a_msg_headers = $this->get_message_cache($cache_key, $start_msg, $start_msg+$this->page_size, $this->sort_field, $this->sort_order);
      $headers_sorted = TRUE;
      }
    else
      {
      // retrieve headers from IMAP
      if ($this->get_capability('sort') && ($msg_index = iil_C_Sort($this->conn, $mailbox, $this->sort_field, $this->skip_deleted ? 'UNDELETED' : '')))
        {
//console("$mailbox: ".join(',', $msg_index));
        
        $msgs = $msg_index[$begin];
        for ($i=$begin+1; $i < $end; $i++)
          $msgs = $msgs.','.$msg_index[$i];

        $sorted = TRUE;
        }
      else
        {
        $msgs = sprintf("%d:%d", $begin+1, $end);
        $sorted = FALSE;
        }


      // cache is dirty, sync it
      if ($this->caching_enabled && $cache_status==-1 && !$recursive)
        {
        $this->sync_header_index($mailbox);
        return $this->_list_headers($mailbox, $page, $this->sort_field, $this->sort_order, TRUE);
        }      

        
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
//    if (!$headers_sorted)
//      $a_msg_headers = iil_SortHeaders($a_msg_headers, $this->sort_field, $this->sort_order);


      if (!$headers_sorted && $this->sort_order == 'DESC')
        $a_msg_headers = array_reverse($a_msg_headers);
      

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
    
  
  // return sorted array of message UIDs
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
// console("$id / $uid: OK");
        unset($cache_index[$id]);
        continue;
        }
        
      // message in cache but in wrong position
      if (in_array((string)$uid, $cache_index, TRUE))
        {
// console("$id / $uid: Moved");
        unset($cache_index[$id]);        
        }
      
      // other message at this position
      if (isset($cache_index[$id]))
        {
// console("$id / $uid: Delete");
        $this->remove_message_cache($cache_key, $id);
        unset($cache_index[$id]);
        }
        

// console("$id / $uid: Add");

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
  function search($mbox_name='', $criteria='ALL', $str=NULL)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;
    if ($str && $criteria)
      {
      $criteria = 'CHARSET UTF-8 '.$criteria.' "'.UTF7EncodeString($str).'"';
      return $this->_search_index($mailbox, $criteria);
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


  function get_headers($id, $mbox_name=NULL, $is_uid=TRUE)
    {
    $mailbox = $mbox_name ? $this->_mod_mailbox($mbox_name) : $this->mailbox;

    // get cached headers
    if ($is_uid && ($headers = $this->get_cached_message($mailbox.'.msg', $id)))
      return $headers;

    $msg_id = $is_uid ? $this->_uid2id($id) : $id;
    $headers = iil_C_FetchHeader($this->conn, $mailbox, $msg_id);

    // write headers cache
    if ($headers)
      $this->add_message_cache($mailbox.'.msg', $msg_id, $headers);

    return $headers;
    }


  function get_body($uid, $part=1)
    {
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;

	$structure_str = iil_C_FetchStructureString($this->conn, $this->mailbox, $msg_id); 
	$structure = iml_GetRawStructureArray($structure_str);
    $body = iil_C_FetchPartBody($this->conn, $this->mailbox, $msg_id, $part);

    $encoding = iml_GetPartEncodingCode($structure, $part);
    
    if ($encoding==3) $body = $this->mime_decode($body, 'base64');
    else if ($encoding==4) $body = $this->mime_decode($body, 'quoted-printable');

    return $body;
    }


  function get_raw_body($uid)
    {
    if (!($msg_id = $this->_uid2id($uid)))
      return FALSE;

	$body = iil_C_FetchPartHeader($this->conn, $this->mailbox, $msg_id, NULL);
	$body .= iil_C_HandlePartBody($this->conn, $this->mailbox, $msg_id, NULL, 1);

    return $body;    
    }


  // set message flag to one or several messages
  // possible flags are: SEEN, UNDELETED, DELETED, RECENT, ANSWERED, DRAFT
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

    // update cached message headers
    $cache_key = $from_mbox.'.msg';
    if ($moved && ($a_cache_index = $this->get_message_cache_index($cache_key)))
      {
      $start_index = 100000;
      foreach ($a_uids as $uid)
        {
        if(($index = array_search($uid, $a_cache_index)) !== FALSE)
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

    // remove deleted messages from cache
    $cache_key = $mailbox.'.msg';
    if ($deleted && ($a_cache_index = $this->get_message_cache_index($cache_key)))
      {
      $start_index = 100000;
      foreach ($a_uids as $uid)
        {
        $index = array_search($uid, $a_cache_index);
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


  // return an array with all folders available in IMAP server
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
      {
      $result = iil_C_GetQuota($this->conn);
      if ($result["total"])
        return sprintf("%.2fMB / %.2fMB (%.0f%%)", $result["used"] / 1000.0, $result["total"] / 1000.0, $result["percent"]);       
      }

    return FALSE;
    }


  // subscribe to a specific mailbox(es)
  function subscribe($mbox_name, $mode='subscribe')
    {
    if (is_array($mbox_name))
      $a_mboxes = $mbox_name;
    else if (is_string($mbox_name) && strlen($mbox_name))
      $a_mboxes = explode(',', $mbox_name);
    
    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'subscribe');
    }


  // unsubscribe mailboxes
  function unsubscribe($mbox_name)
    {
    if (is_array($mbox_name))
      $a_mboxes = $mbox_name;
    else if (is_string($mbox_name) && strlen($mbox_name))
      $a_mboxes = explode(',', $mbox_name);

    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'unsubscribe');
    }


  // create a new mailbox on the server and register it in local cache
  function create_mailbox($name, $subscribe=FALSE)
    {
    $result = FALSE;
    
    // replace backslashes
    $name = preg_replace('/[\\\]+/', '-', $name);

    $name_enc = UTF7EncodeString($name);

    // reduce mailbox name to 100 chars
    $name_enc = substr($name_enc, 0, 100);

    $abs_name = $this->_mod_mailbox($name_enc);
    $a_mailbox_cache = $this->get_cache('mailboxes');
        
    if (strlen($abs_name) && (!is_array($a_mailbox_cache) || !in_array($abs_name, $a_mailbox_cache)))
      $result = iil_C_CreateFolder($this->conn, $abs_name);

    // update mailboxlist cache
    if ($result && $subscribe)
      $this->subscribe($name_enc);

    return $result ? $name : FALSE;
    }


  // set a new name to an existing mailbox
  function rename_mailbox($mbox_name, $new_name, $subscribe=TRUE)
    {
    $result = FALSE;

    // replace backslashes
    $name = preg_replace('/[\\\]+/', '-', $new_name);

    $name_enc = UTF7EncodeString($new_name);

    // reduce mailbox name to 100 chars
    $name_enc = substr($name_enc, 0, 100);

    $abs_name = $this->_mod_mailbox($name_enc);
    $a_mailbox_cache = $this->get_cache('mailboxes');

    if (strlen($abs_name) && (!is_array($a_mailbox_cache) || !in_array($abs_name, $a_mailbox_cache)))
      $result = iil_C_RenameFolder($this->conn, $mbox_name, $abs_name);

    // update mailboxlist cache
    if ($result && $subscribe)
      $this->unsubscribe($mbox_name);
      $this->subscribe($name_enc);

    return $result ? $name : FALSE;
    }


  // remove mailboxes from server
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
         SET    created=now(),
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
         VALUES (now(), ?, ?, ?)",
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


  function get_cached_message($key, $uid, $body=FALSE)
    {
    if (!$this->caching_enabled)
      return FALSE;

    $internal_key = '__single_msg';
    if ($this->caching_enabled && (!isset($this->cache[$internal_key][$uid]) || $body))
      {
      $sql_select = "idx, uid, headers";
      if ($body)
        $sql_select .= ", body";
      
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
        $headers = unserialize($sql_arr['headers']);
        if (is_object($headers) && !empty($sql_arr['body']))
          $headers->body = $sql_arr['body'];

        $this->cache[$internal_key][$uid] = $headers;
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


  function add_message_cache($key, $index, $headers)
    {
    if (!$key || !is_object($headers) || empty($headers->uid))
      return;

    $this->db->query(
      "INSERT INTO ".get_table_name('messages')."
       (user_id, del, cache_key, created, idx, uid, subject, ".$this->db->quoteIdentifier('from').", ".$this->db->quoteIdentifier('to').", cc, date, size, headers)
       VALUES (?, 0, ?, now(), ?, ?, ?, ?, ?, ?, ".$this->db->fromunixtime($headers->timestamp).", ?, ?)",
      $_SESSION['user_id'],
      $key,
      $index,
      $headers->uid,
      (string)substr($this->decode_header($headers->subject, TRUE), 0, 128),
      (string)substr($this->decode_header($headers->from, TRUE), 0, 128),
      (string)substr($this->decode_header($headers->to, TRUE), 0, 128),
      (string)substr($this->decode_header($headers->cc, TRUE), 0, 128),
      (int)$headers->size,
      serialize($headers));
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
  function decode_mime_string($input)
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
    if ((!empty($this->root_ns) && $this->root_ns == $mbox_name) || ($mbox_name == 'INBOX' && $mode == 'in'))
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
      	
      if (($p = array_search(strtolower($folder), $this->default_folders))!==FALSE)
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


  function _parse_address_list($str)
    {
    $a = $this->_explode_quoted_string(',', $str);
    $result = array();

    foreach ($a as $key => $val)
      {
      $val = str_replace("\"<", "\" <", $val);
      $sub_a = $this->_explode_quoted_string(' ', $val);
      
      foreach ($sub_a as $k => $v)
        {
        if ((strpos($v, '@') > 0) && (strpos($v, '.') > 0)) 
          $result[$key]['address'] = str_replace('<', '', str_replace('>', '', $v));
        else
          $result[$key]['name'] .= (empty($result[$key]['name'])?'':' ').str_replace("\"",'',stripslashes($v));
        }
        
      if (empty($result[$key]['name']))
        $result[$key]['name'] = $result[$key]['address'];
        
      $result[$key]['name'] = $this->decode_header($result[$key]['name']);
      }
    
    return $result;
    }


  function _explode_quoted_string($delimiter, $string)
    {
    $quotes = explode("\"", $string);
    foreach ($quotes as $key => $val)
      if (($key % 2) == 1)
        $quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
        
    $string = implode("\"", $quotes);

    $result = explode($delimiter, $string);
    foreach ($result as $key => $val) 
      $result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);
    
    return $result;
    }
  }





function quoted_printable_encode($input="", $line_max=76, $space_conv=false)
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
