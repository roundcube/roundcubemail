<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_user.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
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
 * @package    core
 * @author     Thomas Bruederli <roundcube@gmail.com>
 */
class rcube_user
{
  var $ID = null;
  var $data = null;
  
  
  /**
   * Object constructor
   *
   * @param object DB Database connection
   */
  function __construct($id = null, $sql_arr = null)
  {
    global $DB;
    
    if ($id && !$sql_arr)
    {
      $sql_result = $DB->query("SELECT * FROM ".get_table_name('users')." WHERE  user_id=?", $id);
      $sql_arr = $DB->fetch_assoc($sql_result);
    }
    
    if (!empty($sql_arr))
    {
      $this->ID = $sql_arr['user_id'];
      $this->data = $sql_arr;
    }
  }

  /**
   * PHP 4 object constructor
   *
   * @see  rcube_user::__construct
   */
  function rcube_user($id = null, $sql_arr = null)
  {
    $this->__construct($id, $sql_arr);
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
    if ($this->ID && $this->data['preferences'])
      return unserialize($this->data['preferences']);
    else
      return array();
  }
  
  
  /**
   * Write the given user prefs to the user's record
   *
   * @param mixed User prefs to save
   * @return boolean True on success, False on failure
   */
  function save_prefs($a_user_prefs)
  {
    global $DB, $CONFIG, $sess_user_lang;
    
    if (!$this->ID)
      return false;

    // merge (partial) prefs array with existing settings
    $a_user_prefs += (array)$this->get_prefs();

    $DB->query(
      "UPDATE ".get_table_name('users')."
       SET    preferences=?,
              language=?
       WHERE  user_id=?",
      serialize($a_user_prefs),
      $sess_user_lang,
      $this->ID);

    if ($DB->affected_rows())
    {
      $CONFIG = array_merge($CONFIG, $a_user_prefs);
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
    global $DB;
    
    $sql_result = $this->list_identities($id ? sprintf('AND identity_id=%d', $id) : '');
    return $DB->fetch_assoc($sql_result);
  }
  
  
  /**
   * Return a list of all identities linked with this user
   *
   * @return array List of identities
   */
  function list_identities($sql_add = '')
  {
    global $DB;
    
    // get contacts from DB
    $sql_result = $DB->query(
      "SELECT * FROM ".get_table_name('identities')."
       WHERE  del<>1
       AND    user_id=?
       $sql_add
       ORDER BY ".$DB->quoteIdentifier('standard')." DESC, name ASC",
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
    global $DB;
    
    if (!$this->ID)
      return false;
    
    $write_sql = array();
    
    foreach ((array)$data as $col => $value)
    {
      $write_sql[] = sprintf("%s=%s",
        $DB->quoteIdentifier($col),
        $DB->quote($value));
    }
    
    $DB->query(
      "UPDATE ".get_table_name('identities')."
       SET ".join(', ', $write_sql)."
       WHERE  identity_id=?
       AND    user_id=?
       AND    del<>1",
      $iid,
      $this->ID);
    
    return $DB->affected_rows();
  }
  
  
  /**
   * Create a new identity record linked with this user
   *
   * @param array  Hash array with col->value pairs to save
   * @return int  The inserted identity ID or false on error
   */
  function insert_identity($data)
  {
    global $DB;
    
    if (!$this->ID)
      return false;

    $insert_cols = $insert_values = array();
    foreach ((array)$data as $col => $value)
    {
      $insert_cols[] = $DB->quoteIdentifier($col);
      $insert_values[] = $DB->quote($value);
    }

    $DB->query(
      "INSERT INTO ".get_table_name('identities')."
        (user_id, ".join(', ', $insert_cols).")
       VALUES (?, ".join(', ', $insert_values).")",
      $this->ID);

    return $DB->insert_id(get_sequence_name('identities'));
  }
  
  
  /**
   * Mark the given identity as deleted
   *
   * @param int  Identity ID
   * @return boolean True if deleted successfully, false if nothing changed
   */
  function delete_identity($iid)
  {
    global $DB;
    
    if (!$this->ID)
      return false;

    $sql_result = $DB->query("SELECT count(*) AS ident_count FROM " .
      get_table_name('identities') .
      " WHERE user_id = ? AND del != 1",
      $this->ID);

    $sql_arr = $DB->fetch_assoc($sql_result);
    if ($sql_arr['ident_count'] <= 1)
      return false;
    
    $DB->query(
      "UPDATE ".get_table_name('identities')."
       SET    del=1
       WHERE  user_id=?
       AND    identity_id=?",
      $this->ID,
      $iid);

    return $DB->affected_rows();
  }
  
  
  /**
   * Make this identity the default one for this user
   *
   * @param int The identity ID
   */
  function set_default($iid)
  {
    global $DB;
    
    if ($this->ID && $iid)
    {
      $DB->query(
        "UPDATE ".get_table_name('identities')."
         SET ".$DB->quoteIdentifier('standard')."='0'
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
    global $DB;
    
    if ($this->ID)
    {
      $DB->query(
        "UPDATE ".get_table_name('users')."
         SET    last_login=".$DB->now()."
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
   * @static
   */
  function query($user, $host)
  {
    global $DB;
    
    // query if user already registered
    $sql_result = $DB->query(
      "SELECT * FROM ".get_table_name('users')."
       WHERE  mail_host=? AND (username=? OR alias=?)",
      $host,
      $user,
      $user);
      
    // user already registered -> overwrite username
    if ($sql_arr = $DB->fetch_assoc($sql_result))
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
   * @static
   */
  function create($user, $host)
  {
    global $DB, $CONFIG;
    
    $user_email = '';

    // try to resolve user in virtusertable
    if (!empty($CONFIG['virtuser_file']) && !strpos($user, '@'))
      $user_email = self::user2email($user);

    $DB->query(
      "INSERT INTO ".get_table_name('users')."
        (created, last_login, username, mail_host, alias, language)
       VALUES (".$DB->now().", ".$DB->now().", ?, ?, ?, ?)",
      strip_newlines($user),
      strip_newlines($host),
      strip_newlines($user_email),
      $_SESSION['user_lang']);

    if ($user_id = $DB->insert_id(get_sequence_name('users')))
    {
      $mail_domain = rcmail_mail_domain($host);

      if ($user_email=='')
        $user_email = strpos($user, '@') ? $user : sprintf('%s@%s', $user, $mail_domain);

      $user_name = $user != $user_email ? $user : '';

      // try to resolve the e-mail address from the virtuser table
      if (!empty($CONFIG['virtuser_query']) &&
          ($sql_result = $DB->query(preg_replace('/%u/', $DB->escapeSimple($user), $CONFIG['virtuser_query']))) &&
          ($DB->num_rows()>0))
      {
        while ($sql_arr = $DB->fetch_array($sql_result))
        {
          $DB->query(
            "INSERT INTO ".get_table_name('identities')."
              (user_id, del, standard, name, email)
             VALUES (?, 0, 1, ?, ?)",
            $user_id,
            strip_newlines($user_name),
            preg_replace('/^@/', $user . '@', $sql_arr[0]));
        }
      }
      else
      {
        // also create new identity records
        $DB->query(
          "INSERT INTO ".get_table_name('identities')."
            (user_id, del, standard, name, email)
           VALUES (?, 0, 1, ?, ?)",
          $user_id,
          strip_newlines($user_name),
          strip_newlines($user_email));
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
   * Resolve username using a virtuser table
   *
   * @param string E-mail address to resolve
   * @return string Resolved IMAP username
   * @static
   */
  function email2user($email)
  {
    $user = $email;
    $r = rcmail_findinvirtual("^$email");

    for ($i=0; $i<count($r); $i++)
    {
      $data = $r[$i];
      $arr = preg_split('/\s+/', $data);
      if (count($arr) > 0)
      {
        $user = trim($arr[count($arr)-1]);
        break;
      }
    }

    return $user;
  }


  /**
   * Resolve e-mail address from virtuser table
   *
   * @param string User name
   * @return string Resolved e-mail address
   * @static
   */
  function user2email($user)
  {
    $email = "";
    $r = rcmail_findinvirtual("$user$");

    for ($i=0; $i<count($r); $i++)
    {
      $data = $r[$i];
      $arr = preg_split('/\s+/', $data);
      if (count($arr) > 0)
      {
        $email = trim($arr[0]);
        break;
      }
    }

    return $email;
  }

}


?>
