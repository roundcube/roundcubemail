<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_user.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This class represents a system user linked and provides access      |
 |   to the related database records.                                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: rcube_user.inc 933 2007-11-29 14:17:32Z thomasb $

*/


/**
 * Class representing a system user
 *
 * @package    Core
 * @author     Thomas Bruederli <roundcube@gmail.com>
 */
class rcube_user
{
  public $ID = null;
  public $data = null;
  public $language = null;
  
  private $db = null;
  
  
  /**
   * Object constructor
   *
   * @param object DB Database connection
   */
  function __construct($id = null, $sql_arr = null)
  {
    $this->db = rcmail::get_instance()->get_dbh();
    
    if ($id && !$sql_arr)
    {
      $sql_result = $this->db->query("SELECT * FROM ".get_table_name('users')." WHERE  user_id=?", $id);
      $sql_arr = $this->db->fetch_assoc($sql_result);
    }
    
    if (!empty($sql_arr))
    {
      $this->ID = $sql_arr['user_id'];
      $this->data = $sql_arr;
      $this->language = $sql_arr['language'];
    }
  }


  /**
   * Build a user name string (as e-mail address)
   *
   * @return string Full user name
   */
  function get_username()
  {
    return $this->data['username'] ? $this->data['username'] . (!strpos($this->data['username'], '@') ? '@'.$this->data['mail_host'] : '') : false;
  }
  
  
  /**
   * Get the preferences saved for this user
   *
   * @return array Hash array with prefs
   */
  function get_prefs()
  {
    if (!empty($this->language))
      $prefs = array('language' => $this->language);
    
    if ($this->ID && $this->data['preferences'])
      $prefs += (array)unserialize($this->data['preferences']);
    
    return $prefs;
  }
  
  
  /**
   * Write the given user prefs to the user's record
   *
   * @param array User prefs to save
   * @return boolean True on success, False on failure
   */
  function save_prefs($a_user_prefs)
  {
    if (!$this->ID)
      return false;
      
    $config = rcmail::get_instance()->config;
    $old_prefs = (array)$this->get_prefs();

    // merge (partial) prefs array with existing settings
    $save_prefs = $a_user_prefs + $old_prefs;
    unset($save_prefs['language']);
    
    // don't save prefs with default values if they haven't been changed yet
    foreach ($a_user_prefs as $key => $value) {
      if (!isset($old_prefs[$key]) && ($value == $config->get($key)))
        unset($save_prefs[$key]);
    }
    
    $this->db->query(
      "UPDATE ".get_table_name('users')."
       SET    preferences=?,
              language=?
       WHERE  user_id=?",
      serialize($save_prefs),
      $_SESSION['language'],
      $this->ID);

    $this->language = $_SESSION['language'];
    if ($this->db->affected_rows()) {
      $config->merge($a_user_prefs);
      return true;
    }

    return false;
  }
  
  
  /**
   * Get default identity of this user
   *
   * @param int  Identity ID. If empty, the default identity is returned
   * @return array Hash array with all cols of the 
   */
  function get_identity($id = null)
  {
    $sql_result = $this->list_identities($id ? sprintf('AND identity_id=%d', $id) : '');
    return $this->db->fetch_assoc($sql_result);
  }
  
  
  /**
   * Return a list of all identities linked with this user
   *
   * @return array List of identities
   */
  function list_identities($sql_add = '')
  {
    // get contacts from DB
    $sql_result = $this->db->query(
      "SELECT * FROM ".get_table_name('identities')."
       WHERE  del<>1
       AND    user_id=?
       $sql_add
       ORDER BY ".$this->db->quoteIdentifier('standard')." DESC, name ASC, identity_id ASC",
      $this->ID);
    
    return $sql_result;
  }
  
  
  /**
   * Update a specific identity record
   *
   * @param int    Identity ID
   * @param array  Hash array with col->value pairs to save
   * @return boolean True if saved successfully, false if nothing changed
   */
  function update_identity($iid, $data)
  {
    if (!$this->ID)
      return false;
    
    $write_sql = array();
    
    foreach ((array)$data as $col => $value)
    {
      $write_sql[] = sprintf("%s=%s",
        $this->db->quoteIdentifier($col),
        $this->db->quote($value));
    }
    
    $this->db->query(
      "UPDATE ".get_table_name('identities')."
       SET ".join(', ', $write_sql)."
       WHERE  identity_id=?
       AND    user_id=?
       AND    del<>1",
      $iid,
      $this->ID);
    
    return $this->db->affected_rows();
  }
  
  
  /**
   * Create a new identity record linked with this user
   *
   * @param array  Hash array with col->value pairs to save
   * @return int  The inserted identity ID or false on error
   */
  function insert_identity($data)
  {
    if (!$this->ID)
      return false;

    $insert_cols = $insert_values = array();
    foreach ((array)$data as $col => $value)
    {
      $insert_cols[] = $this->db->quoteIdentifier($col);
      $insert_values[] = $this->db->quote($value);
    }

    $this->db->query(
      "INSERT INTO ".get_table_name('identities')."
        (user_id, ".join(', ', $insert_cols).")
       VALUES (?, ".join(', ', $insert_values).")",
      $this->ID);

    return $this->db->insert_id(get_sequence_name('identities'));
  }
  
  
  /**
   * Mark the given identity as deleted
   *
   * @param int  Identity ID
   * @return boolean True if deleted successfully, false if nothing changed
   */
  function delete_identity($iid)
  {
    if (!$this->ID)
      return false;

    if (!$this->ID || $this->ID == '')
      return false;

    $sql_result = $this->db->query("SELECT count(*) AS ident_count FROM " .
      get_table_name('identities') .
      " WHERE user_id = ? AND del <> 1",
      $this->ID);

    $sql_arr = $this->db->fetch_assoc($sql_result);
    if ($sql_arr['ident_count'] <= 1)
      return false;
    
    $this->db->query(
      "UPDATE ".get_table_name('identities')."
       SET    del=1
       WHERE  user_id=?
       AND    identity_id=?",
      $this->ID,
      $iid);

    return $this->db->affected_rows();
  }
  
  
  /**
   * Make this identity the default one for this user
   *
   * @param int The identity ID
   */
  function set_default($iid)
  {
    if ($this->ID && $iid)
    {
      $this->db->query(
        "UPDATE ".get_table_name('identities')."
         SET ".$this->db->quoteIdentifier('standard')."='0'
         WHERE  user_id=?
         AND    identity_id<>?
         AND    del<>1",
        $this->ID,
        $iid);
    }
  }
  
  
  /**
   * Update user's last_login timestamp
   */
  function touch()
  {
    if ($this->ID)
    {
      $this->db->query(
        "UPDATE ".get_table_name('users')."
         SET    last_login=".$this->db->now()."
         WHERE  user_id=?",
        $this->ID);
    }
  }
  
  
  /**
   * Clear the saved object state
   */
  function reset()
  {
    $this->ID = null;
    $this->data = null;
  }
  
  
  /**
   * Find a user record matching the given name and host
   *
   * @param string IMAP user name
   * @param string IMAP host name
   * @return object rcube_user New user instance
   */
  static function query($user, $host)
  {
    $dbh = rcmail::get_instance()->get_dbh();
    
    // query for matching user name
    $query = "SELECT * FROM ".get_table_name('users')." WHERE mail_host=? AND %s=?";
    $sql_result = $dbh->query(sprintf($query, 'username'), $host, $user);
    
    // query for matching alias
    if (!($sql_arr = $dbh->fetch_assoc($sql_result))) {
      $sql_result = $dbh->query(sprintf($query, 'alias'), $host, $user);
      $sql_arr = $dbh->fetch_assoc($sql_result);
    }
    
    // user already registered -> overwrite username
    if ($sql_arr)
      return new rcube_user($sql_arr['user_id'], $sql_arr);
    else
      return false;
  }
  
  
  /**
   * Create a new user record and return a rcube_user instance
   *
   * @param string IMAP user name
   * @param string IMAP host
   * @return object rcube_user New user instance
   */
  static function create($user, $host)
  {
    $user_email = '';
    $rcmail = rcmail::get_instance();
    $dbh = $rcmail->get_dbh();

    // try to resolve user in virtuser table and file
    if (!strpos($user, '@')) {
      if ($email_list = self::user2email($user, false))
        $user_email = $email_list[0];
    }
    
    $dbh->query(
      "INSERT INTO ".get_table_name('users')."
        (created, last_login, username, mail_host, alias, language)
       VALUES (".$dbh->now().", ".$dbh->now().", ?, ?, ?, ?)",
      strip_newlines($user),
      strip_newlines($host),
      strip_newlines($user_email),
      $_SESSION['language']);

    if ($user_id = $dbh->insert_id(get_sequence_name('users')))
    {
      $mail_domain = $rcmail->config->mail_domain($host);

      if ($user_email=='')
        $user_email = strpos($user, '@') ? $user : sprintf('%s@%s', $user, $mail_domain);

      $user_name = $user != $user_email ? $user : '';

      if (empty($email_list))
        $email_list[] = strip_newlines($user_email); 

      // also create new identity records
      $standard = 1;
      foreach ($email_list as $email) {
        $dbh->query(
            "INSERT INTO ".get_table_name('identities')."
              (user_id, del, standard, name, email)
             VALUES (?, 0, ?, ?, ?)",
            $user_id,
	    $standard,
            strip_newlines($user_name),
            preg_replace('/^@/', $user . '@', $email));
	$standard = 0;
      }
    }
    else
    {
      raise_error(array(
        'code' => 500,
        'type' => 'php',
        'line' => __LINE__,
        'file' => __FILE__,
        'message' => "Failed to create new user"), true, false);
    }
    
    return $user_id ? new rcube_user($user_id) : false;
  }
  
  
  /**
   * Resolve username using a virtuser file
   *
   * @param string E-mail address to resolve
   * @return string Resolved IMAP username
   */
  static function email2user($email)
  {
    $r = self::findinvirtual('^' . quotemeta($email) . '[[:space:]]');

    for ($i=0; $i<count($r); $i++)
    {
      $data = trim($r[$i]);
      $arr = preg_split('/\s+/', $data);
      if (count($arr) > 0)
        return trim($arr[count($arr)-1]);
    }

    return NULL;
  }


  /**
   * Resolve e-mail address from virtuser file/table
   *
   * @param string User name
   * @param boolean If true returns first found entry
   * @return mixed Resolved e-mail address string or array of strings
   */
  static function user2email($user, $first=true)
  {
    $result = array();
    $rcmail = rcmail::get_instance();
    $dbh = $rcmail->get_dbh();

    // SQL lookup
    if ($virtuser_query = $rcmail->config->get('virtuser_query')) {
      $sql_result = $dbh->query(preg_replace('/%u/', $dbh->escapeSimple($user), $virtuser_query));
      while ($sql_arr = $dbh->fetch_array($sql_result))
        if (strpos($sql_arr[0], '@')) {
          $result[] = $sql_arr[0];
	  if ($first)
	    return $result[0];
	}
    }
    // File lookup
    $r = self::findinvirtual('[[:space:]]' . quotemeta($user) . '[[:space:]]*$');
    for ($i=0; $i<count($r); $i++)
    {
      $data = $r[$i];
      $arr = preg_split('/\s+/', $data);
      if (count($arr) > 0 && strpos($arr[0], '@'))
      {
        $result[] = trim(str_replace('\\@', '@', $arr[0]));

	if ($first)
          return $result[0];
      }
    }
    
    return empty($result) ? NULL : $result;
  }
  
  
  /**
   * Find matches of the given pattern in virtuser file
   * 
   * @param string Regular expression to search for
   * @return array Matching entries
   */
  private static function findinvirtual($pattern)
  {
    $result = array();
    $virtual = null;
    
    if ($virtuser_file = rcmail::get_instance()->config->get('virtuser_file'))
      $virtual = file($virtuser_file);
    
    if (empty($virtual))
      return $result;
    
    // check each line for matches
    foreach ($virtual as $line)
    {
      $line = trim($line);
      if (empty($line) || $line{0}=='#')
        continue;
        
      if (eregi($pattern, $line))
        $result[] = $line;
    }
    
    return $result;
  }

}


