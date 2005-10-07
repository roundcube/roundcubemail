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


require_once('lib/imap.inc');
require_once('lib/mime.inc');


class rcube_imap
  {
  var $conn;
  var $root_dir = '';
  var $mailbox = 'INBOX';
  var $list_page = 1;
  var $page_size = 10;
  var $delimiter = NULL;
  var $caching_enabled = FALSE;
  var $default_folders = array('inbox', 'drafts', 'sent', 'junk', 'trash');
  var $cache = array();
  var $cache_changes = array();  
  var $uid_id_map = array();
  var $msg_headers = array();


  // PHP 5 constructor
  function __construct()
    {
    
    }

  // PHP 4 compatibility
  function rcube_imap()
    {
    $this->__construct();
    }


  function iloha_imap($connection='')
    {
    if ($connection)
      {
      $a_url = parse_url($connection);
      $scheme = $a_url['scheme'] ? $a_url['scheme'] : 'imap';
      $port = $a_url['port'] ? $a_url['port'] : ($scheme=='imaps' ? 993 : 143);
      $host = $a_url['host'];
      $user = $a_url['user'];
      $pass = $a_url['pass'];
      
      //var_dump($a_url);
      
      $this->connect($host, $user, $pass, $port);
      }
    }


  function connect($host, $user, $pass, $port=143, $use_ssl=FALSE)
    {
    global $ICL_PORT, $CONFIG;
    
    // check for Open-SSL support in PHP build
    if ($use_ssl && in_array('openssl', get_loaded_extensions()))
      $ICL_SSL = TRUE;

    $ICL_PORT = $port;
    $this->conn = iil_Connect($host, $user, $pass, array('imap' => 'check'));
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    
    if ($this->conn && ($CONFIG['debug_level'] & 8))
      print $this->conn->message;
      
    else if (!$this->conn && $GLOBALS['iil_error'])
      {
      raise_error(array('code' => 403,
                       'type' => 'imap',
                       'message' => $GLOBALS['iil_error']), TRUE, FALSE);
      }

    return $this->conn ? TRUE : FALSE;
    }


  function close()
    {    
    if ($this->conn)
      iil_Close($this->conn);
    }


  function set_rootdir($root)
    {
    if (substr($root, -1, 1)==='/')
      $root = substr($root, 0, -1);

    $this->root_dir = $root;
    }


  function set_default_mailboxes($arr)
    {
    if (is_array($arr))
      {
      $this->default_folders = array();
      
      // add mailbox names lower case
      foreach ($arr as $mbox)
        $this->default_folders[] = strtolower($mbox);
      
      // add inbox if not included
      if (!in_array('inbox', $this->default_folders))
        array_unshift($arr, 'inbox');
      }
    }


  function set_mailbox($mbox)
    {
    $mailbox = $this->_mod_mailbox($mbox);

    if ($this->mailbox == $mailbox)
      return;

    $this->mailbox = $mailbox;

    // clear messagecount cache for this mailbox
    $this->_clear_messagecount($mailbox);
    }


  function set_page($page)
    {
    $this->list_page = (int)$page;
    }


  function set_pagesize($size)
    {
    $this->page_size = (int)$size;
    }


  function get_mailbox_name()
    {
    return $this->conn ? $this->_mod_mailbox($this->mailbox, 'out') : '';
    }


  function get_hierarchy_delimiter()
    {
    if ($this->conn && empty($this->delimiter))
      $this->delimiter = iil_C_GetHierarchyDelimiter($this->conn);

    return $this->delimiter;
    }

  // public method for mailbox listing
  // convert mailbox name with root dir first
  function list_mailboxes($root='', $filter='*')
    {
    $a_out = array();
    $a_mboxes = $this->_list_mailboxes($root, $filter);

    foreach ($a_mboxes as $mbox)
      {
      $name = $this->_mod_mailbox($mbox, 'out');
      if (strlen($name))
        $a_out[] = $name;
      }

    // sort mailboxes
    $a_out = $this->_sort_mailbox_list($a_out);

    return $a_out;
    }

  // private method for mailbox listing
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

    // create INBOX if it does not exist
    if (!in_array_nocase('INBOX', $a_folders))
      {
      $this->create_mailbox('INBOX', TRUE);
      array_unshift($a_folders, 'INBOX');
      }

    $a_mailbox_cache = array();

    // write mailboxlist to cache
    $this->update_cache('mailboxes', $a_folders);
    
    return $a_folders;
    }


  // get message count for a specific mailbox; acceptes modes are: ALL, UNSEEN
  function messagecount($mbox='', $mode='ALL', $force=FALSE)
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    return $this->_messagecount($mailbox, $mode, $force);
    }

  // private method for getting nr of mesages
  function _messagecount($mailbox='', $mode='ALL', $force=FALSE)
    {
    $a_mailbox_cache = FALSE;
    $mode = strtoupper($mode);

    if (!$mailbox)
      $mailbox = $this->mailbox;

    $a_mailbox_cache = $this->get_cache('messagecount');
    
    // return cached value
    if (!$force && is_array($a_mailbox_cache[$mailbox]) && isset($a_mailbox_cache[$mailbox][$mode]))
      return $a_mailbox_cache[$mailbox][$mode];      

    // get message count and store in cache
    if ($mode == 'UNSEEN')
      $count = iil_C_CountUnseen($this->conn, $mailbox);
    else
      $count = iil_C_CountMessages($this->conn, $mailbox);

//print "/**** get messagecount for $mailbox ($mode): $count ****/\n";

    if (is_array($a_mailbox_cache[$mailbox]))
      $a_mailbox_cache[$mailbox] = array();
      
    $a_mailbox_cache[$mailbox][$mode] = (int)$count;
    
    // write back to cache
    $this->update_cache('messagecount', $a_mailbox_cache);

//var_dump($a_mailbox_cache);

    return (int)$count;
    }


  // public method for listing headers
  // convert mailbox name with root dir first
  function list_headers($mbox='', $page=NULL, $sort_field='date', $sort_order='DESC')
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    return $this->_list_headers($mailbox, $page, $sort_field, $sort_order);
    }


  // private method for listing message header
  function _list_headers($mailbox='', $page=NULL, $sort_field='date', $sort_order='DESC')
    {
    $max = $this->_messagecount($mailbox /*, 'ALL', TRUE*/);
    $a_out = array();
    
    if (!strlen($mailbox))
      return $a_out;


    // get cached headers
    $a_msg_headers = $this->get_cache($mailbox.'.msg');

// print "/**** count = $max; headers = ".sizeof($a_msg_headers)." ****/\n";

    // retrieve headers from IMAP
    if (!is_array($a_msg_headers) || sizeof($a_msg_headers) != $max)
      {
      $a_header_index = iil_C_FetchHeaders($this->conn, $mailbox, "1:$max");
      $a_msg_headers = array();
      foreach ($a_header_index as $i => $headers)
        $a_msg_headers[$headers->uid] = $headers;
        
// print "/**** fetch headers ****/\n";
      }
    else
      $headers_cached = TRUE;

    // sort headers by a specific col
    $a_headers = iil_SortHeaders($a_msg_headers, $sort_field, $sort_order);
    
    // write headers list to cache
    if (!$headers_cached)
      $this->update_cache($mailbox.'.msg', $a_msg_headers);

    if (is_array($a_headers))
      foreach ($a_headers as $header)
        if (!$header->deleted)
          $a_out[] = $header;

    // return complete list of messages
    if (strtolower($page)=='all')
      return $a_out;

    $start_msg = ($this->list_page-1) * $this->page_size;
    return array_slice($a_out, $start_msg, $this->page_size);
    }


  // return sorted array of message UIDs
  function message_index($mbox='', $sort_field='date', $sort_order='DESC')
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    $a_out = array();

    // get array of message headers
    $a_headers = $this->_list_headers($mailbox, 'all', $sort_field, $sort_order);

    if (is_array($a_headers))
      foreach ($a_headers as $header)
        $a_out[] = $header->uid;

    return $a_out;
    }


  function sync_header_index($mbox=NULL)
    {
    
    }


  function search($mbox='', $criteria='ALL')
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    $a_messages = iil_C_Search($this->conn, $mailbox, $criteria);
    return $a_messages;
    }


  function get_headers($uid, $mbox=NULL)
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    
    // get cached headers
    $a_msg_headers = $this->get_cache($mailbox.'.msg');
    
    // return cached header
    if ($a_msg_headers[$uid])
      return $a_msg_headers[$uid];
    
    $msg_id = $this->_uid2id($uid);
    $header = iil_C_FetchHeader($this->conn, $mailbox, $msg_id);
    
    // write headers cache
    $a_msg_headers[$uid] = $header;
    $this->update_cache($mailbox.'.msg', $a_msg_headers);

    return $header;
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
  // possible flgs are: SEEN, DELETED, RECENT, ANSWERED, DRAFT
  function set_flag($uids, $flag)
    {
    $flag = strtoupper($flag);
    $msg_ids = array();
    if (!is_array($uids))
      $uids = array($uids);
      
    foreach ($uids as $uid)
      $msg_ids[] = $this->_uid2id($uid);
      
    if ($flag=='UNSEEN')
      $result = iil_C_Unseen($this->conn, $this->mailbox, join(',', $msg_ids));
    else
      $result = iil_C_Flag($this->conn, $this->mailbox, join(',', $msg_ids), $flag);


    // reload message headers if cached
    $cache_key = $this->mailbox.'.msg';
    if ($result && ($a_cached_headers = $this->get_cache($cache_key)))
      {
      foreach ($uids as $uid)
        {
        if (isset($a_cached_headers[$uid]))
          {
          unset($this->cache[$cache_key][$uid]);
          $this->get_headers($uid);
          }
        }
      }

    // set nr of messages that were flaged
    $count = sizeof($msg_ids);

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
  function save_message($mbox, $message)
    {
    $mailbox = $this->_mod_mailbox($mbox);

    // make shure mailbox exists
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
    $to_mbox = $this->_mod_mailbox($to_mbox);
    $from_mbox = $from_mbox ? $this->_mod_mailbox($from_mbox) : $this->mailbox;

    // make shure mailbox exists
    if (!in_array($to_mbox, $this->_list_mailboxes()))
      return FALSE;
    
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
      $this->expunge($from_mbox, FALSE);
      $this->clear_cache($to_mbox.'.msg');
      $this->_clear_messagecount($from_mbox);
      $this->_clear_messagecount($to_mbox);
      }

    // update cached message headers
    $cache_key = $from_mbox.'.msg';
    if ($moved && ($a_cached_headers = $this->get_cache($cache_key)))
      {
      foreach ($a_uids as $uid)
        unset($a_cached_headers[$uid]);

      $this->update_cache($cache_key, $a_cached_headers);
      }

    return $moved;
    }


  // mark messages as deleted and expunge mailbox
  function delete_message($uids, $mbox='')
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;

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
      $this->expunge($mailbox, FALSE);
      $this->_clear_messagecount($mailbox);
      }

    // remove deleted messages from cache
    if ($deleted && ($a_cached_headers = $this->get_cache($mailbox.'.msg')))
      {
      foreach ($a_uids as $uid)
        unset($a_cached_headers[$uid]);

      $this->update_cache($mailbox.'.msg', $a_cached_headers);
      }

    return $deleted;

    }


  // send IMAP expunge command and clear cache
  function expunge($mbox='', $clear_cache=TRUE)
    {
    $mailbox = $mbox ? $this->_mod_mailbox($mbox) : $this->mailbox;
    
    $result = iil_C_Expunge($this->conn, $mailbox);

    if ($result>=0 && $clear_cache)
      {
      $this->clear_cache($mailbox.'.msg');
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
    foreach ($a_mboxes as $mbox)
      {
      $name = $this->_mod_mailbox($mbox, 'out');
      if (strlen($name))
        $a_folders[] = $name;
      }

    // filter folders and sort them
    $sa_unsubscribed = $this->_sort_mailbox_list($a_folders);
    return $sa_unsubscribed;
    }


  // subscribe to a specific mailbox(es)
  function subscribe($mbox, $mode='subscribe')
    {
    if (is_array($mbox))
      $a_mboxes = $mbox;
    else if (is_string($mbox) && strlen($mbox))
      $a_mboxes = explode(',', $mbox);
    
    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'subscribe');
    }


  // unsubscribe mailboxes
  function unsubscribe($mbox)
    {
    if (is_array($mbox))
      $a_mboxes = $mbox;
    else if (is_string($mbox) && strlen($mbox))
      $a_mboxes = explode(',', $mbox);

    // let this common function do the main work
    return $this->_change_subscription($a_mboxes, 'unsubscribe');
    }


  // create a new mailbox on the server and register it in local cache
  function create_mailbox($name, $subscribe=FALSE)
    {
    $result = FALSE;
    $abs_name = $this->_mod_mailbox($name);
    $a_mailbox_cache = $this->get_cache('mailboxes');

    if (strlen($abs_name) && (!is_array($a_mailbox_cache) || !in_array($abs_name, $a_mailbox_cache)))
      $result = iil_C_CreateFolder($this->conn, $abs_name);

    // update mailboxlist cache
    if ($result && $subscribe)
      $this->subscribe($name);

    return $result;
    }


  // set a new name to an existing mailbox
  function rename_mailbox($mbox, $new_name)
    {
    // not implemented yet
    }


  // remove mailboxes from server
  function delete_mailbox($mbox)
    {
    $deleted = FALSE;

    if (is_array($mbox))
      $a_mboxes = $mbox;
    else if (is_string($mbox) && strlen($mbox))
      $a_mboxes = explode(',', $mbox);

    if (is_array($a_mboxes))
      foreach ($a_mboxes as $mbox)
        {
        $mailbox = $this->_mod_mailbox($mbox);

        // unsubscribe mailbox before deleting
        iil_C_UnSubscribe($this->conn, $mailbox);
        
        // send delete command to server
        $result = iil_C_DeleteFolder($this->conn, $mailbox);
        if ($result>=0)
          $deleted = TRUE;
        }

    // clear mailboxlist cache
    if ($deleted)
      $this->clear_cache('mailboxes');

    return $updated;
    }




  /* --------------------------------
   *   internal caching functions
   * --------------------------------*/


  function set_caching($set)
    {
    if ($set && function_exists('rcube_read_cache'))
      $this->caching_enabled = TRUE;
    else
      $this->caching_enabled = FALSE;
    }

  function get_cache($key)
    {
    // read cache
    if (!isset($this->cache[$key]) && $this->caching_enabled)
      {
      $cache_data = rcube_read_cache('IMAP.'.$key);
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
          rcube_write_cache('IMAP.'.$key, serialize($data));
        }
      }    
    }


  function clear_cache($key=NULL)
    {
    if ($key===NULL)
      {
      foreach ($this->cache as $key => $data)
        rcube_clear_cache('IMAP.'.$key);

      $this->cache = array();
      $this->cache_changed = FALSE;
      $this->cache_changes = array();
      }
    else
      {
      rcube_clear_cache('IMAP.'.$key);
      $this->cache_changes[$key] = FALSE;
      unset($this->cache[$key]);
      }
    }



  /* --------------------------------
   *   encoding/decoding functions
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


  function decode_header($input)
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

      $out .= $this->decode_mime_string($encstr);
      $out .= $this->decode_header($rest);

      return $out;
      }
    else
      return $input;
    }


  function decode_mime_string($str)
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

      return decode_specialchars($rest, $a[0]);
      }
    else
      return $str;    //we dont' know what to do with this  
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
    if (is_array($ctype_param) && strlen($ctype_param['charset']))
      return decode_specialchars($body, $ctype_param['charset']);

    return $body;
    }


  /* --------------------------------
   *         private methods
   * --------------------------------*/


  function _mod_mailbox($mbox, $mode='in')
    {
    if ($this->root_dir && $mode=='in')
      $mbox = $this->root_dir.'/'.$mbox;
    else if ($this->root_dir && $mode=='out')
      $mbox = substr($mbox, strlen($this->root_dir)+1);

    return $mbox;
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


  function _uid2id($uid, $mbox=NULL)
    {
    if (!$mbox)
      $mbox = $this->mailbox;
      
    if (!isset($this->uid_id_map[$mbox][$uid]))
      $this->uid_id_map[$mbox][$uid] = iil_C_UID2ID($this->conn, $mbox, $uid);

    return $this->uid_id_map[$mbox][$uid];
    }


  // subscribe/unsubscribe a list of mailboxes and update local cache
  function _change_subscription($a_mboxes, $mode)
    {
    $updated = FALSE;
    
    if (is_array($a_mboxes))
      foreach ($a_mboxes as $i => $mbox)
        {
        $mailbox = $this->_mod_mailbox($mbox);
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
  function _set_messagecount($mbox, $mode, $increment)
    {
    $a_mailbox_cache = FALSE;
    $mailbox = $mbox ? $mbox : $this->mailbox;
    $mode = strtoupper($mode);

    $a_mailbox_cache = $this->get_cache('messagecount');
    
    if (!is_array($a_mailbox_cache[$mailbox]) || !isset($a_mailbox_cache[$mailbox][$mode]) || !is_numeric($increment))
      return FALSE;
    
    // add incremental value to messagecount
    $a_mailbox_cache[$mailbox][$mode] += $increment;

    // write back to cache
    $this->update_cache('messagecount', $a_mailbox_cache);
    
    return TRUE;
    }


  // remove messagecount of a specific mailbox from cache
  function _clear_messagecount($mbox='')
    {
    $a_mailbox_cache = FALSE;
    $mailbox = $mbox ? $mbox : $this->mailbox;

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
