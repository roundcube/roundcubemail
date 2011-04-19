<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_user.inc                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This class represents a system user linked and provides access      |
 |   to the related database records.                                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

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

    /**
     * Holds database connection.
     *
     * @var rcube_mdb2
     */
    private $db = null;


    /**
     * Object constructor
     *
     * @param int   $id      User id
     * @param array $sql_arr SQL result set
     */
    function __construct($id = null, $sql_arr = null)
    {
        $this->db = rcmail::get_instance()->get_dbh();

        if ($id && !$sql_arr) {
            $sql_result = $this->db->query(
                "SELECT * FROM ".get_table_name('users')." WHERE user_id = ?", $id);
            $sql_arr = $this->db->fetch_assoc($sql_result);
        }

        if (!empty($sql_arr)) {
            $this->ID       = $sql_arr['user_id'];
            $this->data     = $sql_arr;
            $this->language = $sql_arr['language'];
        }
    }


    /**
     * Build a user name string (as e-mail address)
     *
     * @param  string $part Username part (empty or 'local' or 'domain')
     * @return string Full user name or its part
     */
    function get_username($part = null)
    {
        if ($this->data['username']) {
            list($local, $domain) = explode('@', $this->data['username']);

            // at least we should always have the local part
            if ($part == 'local') {
                return $local;
            }
            // if no domain was provided...
            if (empty($domain)) {
                $rcmail = rcmail::get_instance();
                $domain = $rcmail->config->mail_domain($this->data['mail_host']);
            }

            if ($part == 'domain') {
                return $domain;
            }

            if (!empty($domain))
                return $local . '@' . $domain;
            else
                return $local;
        }

        return false;
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
     * @param array $a_user_prefs User prefs to save
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

        $save_prefs = serialize($save_prefs);

        $this->db->query(
            "UPDATE ".get_table_name('users').
            " SET preferences = ?".
                ", language = ?".
            " WHERE user_id = ?",
            $save_prefs,
            $_SESSION['language'],
            $this->ID);

        $this->language = $_SESSION['language'];

        if ($this->db->affected_rows()) {
            $config->set_user_prefs($a_user_prefs);
            $this->data['preferences'] = $save_prefs;
            return true;
        }

        return false;
    }


    /**
     * Get default identity of this user
     *
     * @param  int   $id Identity ID. If empty, the default identity is returned
     * @return array Hash array with all cols of the identity record
     */
    function get_identity($id = null)
    {
        $result = $this->list_identities($id ? sprintf('AND identity_id = %d', $id) : '');
        return $result[0];
    }


    /**
     * Return a list of all identities linked with this user
     *
     * @param string $sql_add Optional WHERE clauses
     * @return array List of identities
     */
    function list_identities($sql_add = '')
    {
        $result = array();

        $sql_result = $this->db->query(
            "SELECT * FROM ".get_table_name('identities').
            " WHERE del <> 1 AND user_id = ?".
            ($sql_add ? " ".$sql_add : "").
            " ORDER BY ".$this->db->quoteIdentifier('standard')." DESC, name ASC, identity_id ASC",
            $this->ID);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $result[] = $sql_arr;
        }

        return $result;
    }


    /**
     * Update a specific identity record
     *
     * @param int    $iid  Identity ID
     * @param array  $data Hash array with col->value pairs to save
     * @return boolean True if saved successfully, false if nothing changed
     */
    function update_identity($iid, $data)
    {
        if (!$this->ID)
            return false;

        $query_cols = $query_params = array();

        foreach ((array)$data as $col => $value) {
            $query_cols[]   = $this->db->quoteIdentifier($col) . ' = ?';
            $query_params[] = $value;
        }
        $query_params[] = $iid;
        $query_params[] = $this->ID;

        $sql = "UPDATE ".get_table_name('identities').
            " SET changed = ".$this->db->now().", ".join(', ', $query_cols).
            " WHERE identity_id = ?".
                " AND user_id = ?".
                " AND del <> 1";

        call_user_func_array(array($this->db, 'query'),
            array_merge(array($sql), $query_params));

        return $this->db->affected_rows();
    }


    /**
     * Create a new identity record linked with this user
     *
     * @param array $data Hash array with col->value pairs to save
     * @return int  The inserted identity ID or false on error
     */
    function insert_identity($data)
    {
        if (!$this->ID)
            return false;

        unset($data['user_id']);

        $insert_cols = $insert_values = array();
        foreach ((array)$data as $col => $value) {
            $insert_cols[]   = $this->db->quoteIdentifier($col);
            $insert_values[] = $value;
        }
        $insert_cols[]   = 'user_id';
        $insert_values[] = $this->ID;

        $sql = "INSERT INTO ".get_table_name('identities').
            " (changed, ".join(', ', $insert_cols).")".
            " VALUES (".$this->db->now().", ".join(', ', array_pad(array(), sizeof($insert_values), '?')).")";

        call_user_func_array(array($this->db, 'query'),
            array_merge(array($sql), $insert_values));

        return $this->db->insert_id('identities');
    }


    /**
     * Mark the given identity as deleted
     *
     * @param  int     $iid Identity ID
     * @return boolean True if deleted successfully, false if nothing changed
     */
    function delete_identity($iid)
    {
        if (!$this->ID)
            return false;

        $sql_result = $this->db->query(
            "SELECT count(*) AS ident_count FROM ".get_table_name('identities').
            " WHERE user_id = ? AND del <> 1",
            $this->ID);

        $sql_arr = $this->db->fetch_assoc($sql_result);

        // we'll not delete last identity
        if ($sql_arr['ident_count'] <= 1)
            return -1;

        $this->db->query(
            "UPDATE ".get_table_name('identities').
            " SET del = 1, changed = ".$this->db->now().
            " WHERE user_id = ?".
                " AND identity_id = ?",
            $this->ID,
            $iid);

        return $this->db->affected_rows();
    }


    /**
     * Make this identity the default one for this user
     *
     * @param int $iid The identity ID
     */
    function set_default($iid)
    {
        if ($this->ID && $iid) {
            $this->db->query(
                "UPDATE ".get_table_name('identities').
                " SET ".$this->db->quoteIdentifier('standard')." = '0'".
                " WHERE user_id = ?".
                    " AND identity_id <> ?".
                    " AND del <> 1",
                $this->ID,
                $iid);
        }
    }


    /**
     * Update user's last_login timestamp
     */
    function touch()
    {
        if ($this->ID) {
            $this->db->query(
                "UPDATE ".get_table_name('users').
                " SET last_login = ".$this->db->now().
                " WHERE user_id = ?",
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
     * @param string $user IMAP user name
     * @param string $host IMAP host name
     * @return rcube_user New user instance
     */
    static function query($user, $host)
    {
        $dbh = rcmail::get_instance()->get_dbh();

        // use BINARY (case-sensitive) comparison on MySQL, other engines are case-sensitive
        $mod = preg_match('/^mysql/', $dbh->db_provider) ? 'BINARY' : '';

        // query for matching user name
        $query = "SELECT * FROM ".get_table_name('users')." WHERE mail_host = ? AND %s = $mod ?";
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
     * @param string $user IMAP user name
     * @param string $host IMAP host
     * @return rcube_user New user instance
     */
    static function create($user, $host)
    {
        $user_name  = '';
        $user_email = '';
        $rcmail = rcmail::get_instance();

        // try to resolve user in virtuser table and file
        if ($email_list = self::user2email($user, false, true)) {
            $user_email = is_array($email_list[0]) ? $email_list[0]['email'] : $email_list[0];
        }

        $data = $rcmail->plugins->exec_hook('user_create',
	        array('user'=>$user, 'user_name'=>$user_name, 'user_email'=>$user_email));

        // plugin aborted this operation
        if ($data['abort'])
            return false;

        $user_name  = $data['user_name'];
        $user_email = $data['user_email'];

        $dbh = $rcmail->get_dbh();

        $dbh->query(
            "INSERT INTO ".get_table_name('users').
            " (created, last_login, username, mail_host, alias, language)".
            " VALUES (".$dbh->now().", ".$dbh->now().", ?, ?, ?, ?)",
            strip_newlines($user),
            strip_newlines($host),
            strip_newlines($data['alias'] ? $data['alias'] : $user_email),
            strip_newlines($data['language'] ? $data['language'] : $_SESSION['language']));

        if ($user_id = $dbh->insert_id('users')) {
            // create rcube_user instance to make plugin hooks work
            $user_instance = new rcube_user($user_id);
            $rcmail->user  = $user_instance;

            $mail_domain = $rcmail->config->mail_domain($host);

            if ($user_email == '') {
                $user_email = strpos($user, '@') ? $user : sprintf('%s@%s', $user, $mail_domain);
            }
            if ($user_name == '') {
                $user_name = $user != $user_email ? $user : '';
            }

            if (empty($email_list))
                $email_list[] = strip_newlines($user_email);
            // identities_level check
            else if (count($email_list) > 1 && $rcmail->config->get('identities_level', 0) > 1)
                $email_list = array($email_list[0]);

            // create new identities records
            $standard = 1;
            foreach ($email_list as $row) {
	            $record = array();

                if (is_array($row)) {
	                $record = $row;
                }
                else {
                    $record['email'] = $row;
                }

	            if (empty($record['name']))
	                $record['name'] = $user_name;
                $record['name'] = strip_newlines($record['name']);
                $record['user_id'] = $user_id;
                $record['standard'] = $standard;

                $plugin = $rcmail->plugins->exec_hook('identity_create',
	                array('login' => true, 'record' => $record));

                if (!$plugin['abort'] && $plugin['record']['email']) {
                    $rcmail->user->insert_identity($plugin['record']);
                }
                $standard = 0;
            }
        }
        else {
            raise_error(array(
                'code' => 500,
                'type' => 'php',
                'line' => __LINE__,
                'file' => __FILE__,
                'message' => "Failed to create new user"), true, false);
        }

        return $user_id ? $user_instance : false;
    }


    /**
     * Resolve username using a virtuser plugins
     *
     * @param string $email E-mail address to resolve
     * @return string Resolved IMAP username
     */
    static function email2user($email)
    {
        $rcmail = rcmail::get_instance();
        $plugin = $rcmail->plugins->exec_hook('email2user',
            array('email' => $email, 'user' => NULL));

        return $plugin['user'];
    }


    /**
     * Resolve e-mail address from virtuser plugins
     *
     * @param string $user User name
     * @param boolean $first If true returns first found entry
     * @param boolean $extended If true returns email as array (email and name for identity)
     * @return mixed Resolved e-mail address string or array of strings
     */
    static function user2email($user, $first=true, $extended=false)
    {
        $rcmail = rcmail::get_instance();
        $plugin = $rcmail->plugins->exec_hook('user2email',
            array('email' => NULL, 'user' => $user,
                'first' => $first, 'extended' => $extended));

        return empty($plugin['email']) ? NULL : $plugin['email'];
    }

}
