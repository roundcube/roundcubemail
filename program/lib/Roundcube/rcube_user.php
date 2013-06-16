<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   This class represents a system user linked and provides access      |
 |   to the related database records.                                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class representing a system user
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube_user
{
    public $ID;
    public $data;
    public $language;

    /**
     * Holds database connection.
     *
     * @var rcube_db
     */
    private $db;

    /**
     * Framework object.
     *
     * @var rcube
     */
    private $rc;

    /**
     * Internal identities cache
     *
     * @var array
     */
    private $identities = array();

    const SEARCH_ADDRESSBOOK = 1;
    const SEARCH_MAIL = 2;

    /**
     * Object constructor
     *
     * @param int   $id      User id
     * @param array $sql_arr SQL result set
     */
    function __construct($id = null, $sql_arr = null)
    {
        $this->rc = rcube::get_instance();
        $this->db = $this->rc->get_dbh();

        if ($id && !$sql_arr) {
            $sql_result = $this->db->query(
                "SELECT * FROM ".$this->db->table_name('users')." WHERE user_id = ?", $id);
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
     * @param  string $part Username part (empty or 'local' or 'domain', 'mail')
     * @return string Full user name or its part
     */
    function get_username($part = null)
    {
        if ($this->data['username']) {
            // return real name
            if (!$part) {
                return $this->data['username'];
            }

            list($local, $domain) = explode('@', $this->data['username']);

            // at least we should always have the local part
            if ($part == 'local') {
                return $local;
            }
            // if no domain was provided...
            if (empty($domain)) {
                $domain = $this->rc->config->mail_domain($this->data['mail_host']);
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
        $prefs = array();
        if (!empty($this->language))
            $prefs['language'] = $this->language;

        if ($this->ID) {
            // Preferences from session (write-master is unavailable)
            if (!empty($_SESSION['preferences'])) {
                // Check last write attempt time, try to write again (every 5 minutes)
                if ($_SESSION['preferences_time'] < time() - 5 * 60) {
                    $saved_prefs = unserialize($_SESSION['preferences']);
                    $this->rc->session->remove('preferences');
                    $this->rc->session->remove('preferences_time');
                    $this->save_prefs($saved_prefs);
                }
                else {
                    $this->data['preferences'] = $_SESSION['preferences'];
                }
            }

            if ($this->data['preferences']) {
                $prefs += (array)unserialize($this->data['preferences']);
            }
        }

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

        $config    = $this->rc->config;
        $old_prefs = (array)$this->get_prefs();

        // merge (partial) prefs array with existing settings
        $save_prefs = $a_user_prefs + $old_prefs;
        unset($save_prefs['language']);

        // don't save prefs with default values if they haven't been changed yet
        foreach ($a_user_prefs as $key => $value) {
            if ($value === null || (!isset($old_prefs[$key]) && ($value == $config->get($key))))
                unset($save_prefs[$key]);
        }

        $save_prefs = serialize($save_prefs);

        $this->db->query(
            "UPDATE ".$this->db->table_name('users').
            " SET preferences = ?".
                ", language = ?".
            " WHERE user_id = ?",
            $save_prefs,
            $_SESSION['language'],
            $this->ID);

        $this->language = $_SESSION['language'];

        // Update success
        if ($this->db->affected_rows() !== false) {
            $config->set_user_prefs($a_user_prefs);
            $this->data['preferences'] = $save_prefs;

            if (isset($_SESSION['preferences'])) {
                $this->rc->session->remove('preferences');
                $this->rc->session->remove('preferences_time');
            }
            return true;
        }
        // Update error, but we are using replication (we have read-only DB connection)
        // and we are storing session not in the SQL database
        // we can store preferences in session and try to write later (see get_prefs())
        else if ($this->db->is_replicated() && $config->get('session_storage', 'db') != 'db') {
            $_SESSION['preferences'] = $save_prefs;
            $_SESSION['preferences_time'] = time();
            $config->set_user_prefs($a_user_prefs);
            $this->data['preferences'] = $save_prefs;
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
        $id = (int)$id;
        // cache identities for better performance
        if (!array_key_exists($id, $this->identities)) {
            $result = $this->list_identities($id ? 'AND identity_id = ' . $id : '');
            $this->identities[$id] = $result[0];
        }

        return $this->identities[$id];
    }


    /**
     * Return a list of all identities linked with this user
     *
     * @param string $sql_add   Optional WHERE clauses
     * @param bool   $formatted Format identity email and name
     *
     * @return array List of identities
     */
    function list_identities($sql_add = '', $formatted = false)
    {
        $result = array();

        $sql_result = $this->db->query(
            "SELECT * FROM ".$this->db->table_name('identities').
            " WHERE del <> 1 AND user_id = ?".
            ($sql_add ? " ".$sql_add : "").
            " ORDER BY ".$this->db->quoteIdentifier('standard')." DESC, name ASC, identity_id ASC",
            $this->ID);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            if ($formatted) {
                $ascii_email = format_email($sql_arr['email']);
                $utf8_email  = format_email(rcube_utils::idn_to_utf8($ascii_email));

                $sql_arr['email_ascii'] = $ascii_email;
                $sql_arr['email']       = $utf8_email;
                $sql_arr['ident']       = format_email_recipient($ascii_email, $sql_arr['name']);
            }

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

        $sql = "UPDATE ".$this->db->table_name('identities').
            " SET changed = ".$this->db->now().", ".join(', ', $query_cols).
            " WHERE identity_id = ?".
                " AND user_id = ?".
                " AND del <> 1";

        call_user_func_array(array($this->db, 'query'),
            array_merge(array($sql), $query_params));

        $this->identities = array();

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

        $sql = "INSERT INTO ".$this->db->table_name('identities').
            " (changed, ".join(', ', $insert_cols).")".
            " VALUES (".$this->db->now().", ".join(', ', array_pad(array(), sizeof($insert_values), '?')).")";

        call_user_func_array(array($this->db, 'query'),
            array_merge(array($sql), $insert_values));

        $this->identities = array();

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
            "SELECT count(*) AS ident_count FROM ".$this->db->table_name('identities').
            " WHERE user_id = ? AND del <> 1",
            $this->ID);

        $sql_arr = $this->db->fetch_assoc($sql_result);

        // we'll not delete last identity
        if ($sql_arr['ident_count'] <= 1)
            return -1;

        $this->db->query(
            "UPDATE ".$this->db->table_name('identities').
            " SET del = 1, changed = ".$this->db->now().
            " WHERE user_id = ?".
                " AND identity_id = ?",
            $this->ID,
            $iid);

        $this->identities = array();

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
                "UPDATE ".$this->db->table_name('identities').
                " SET ".$this->db->quoteIdentifier('standard')." = '0'".
                " WHERE user_id = ?".
                    " AND identity_id <> ?".
                    " AND del <> 1",
                $this->ID,
                $iid);

            unset($this->identities[0]);
        }
    }


    /**
     * Update user's last_login timestamp
     */
    function touch()
    {
        if ($this->ID) {
            $this->db->query(
                "UPDATE ".$this->db->table_name('users').
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
        $dbh    = rcube::get_instance()->get_dbh();
        $config = rcube::get_instance()->config;

        // query for matching user name
        $sql_result = $dbh->query("SELECT * FROM " . $dbh->table_name('users')
            ." WHERE mail_host = ? AND username = ?", $host, $user);

        $sql_arr = $dbh->fetch_assoc($sql_result);

        // username not found, try aliases from identities
        if (empty($sql_arr) && $config->get('user_aliases') && strpos($user, '@')) {
            $sql_result = $dbh->limitquery("SELECT u.*"
                ." FROM " . $dbh->table_name('users') . " u"
                ." JOIN " . $dbh->table_name('identities') . " i ON (i.user_id = u.user_id)"
                ." WHERE email = ? AND del <> 1", 0, 1, $user);

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
        $rcube      = rcube::get_instance();
        $dbh        = $rcube->get_dbh();

        // try to resolve user in virtuser table and file
        if ($email_list = self::user2email($user, false, true)) {
            $user_email = is_array($email_list[0]) ? $email_list[0]['email'] : $email_list[0];
        }

        $data = $rcube->plugins->exec_hook('user_create', array(
            'host'       => $host,
            'user'       => $user,
            'user_name'  => $user_name,
            'user_email' => $user_email,
            'email_list' => $email_list,
            'language'   =>  $_SESSION['language'],
        ));

        // plugin aborted this operation
        if ($data['abort']) {
            return false;
        }

        $dbh->query(
            "INSERT INTO ".$dbh->table_name('users').
            " (created, last_login, username, mail_host, language)".
            " VALUES (".$dbh->now().", ".$dbh->now().", ?, ?, ?)",
            $data['user'],
            $data['host'],
            $data['language']);

        if ($user_id = $dbh->insert_id('users')) {
            // create rcube_user instance to make plugin hooks work
            $user_instance = new rcube_user($user_id, array(
                'user_id'   => $user_id,
                'username'  => $data['user'],
                'mail_host' => $data['host'],
                'language'  => $data['language'],
            ));
            $rcube->user = $user_instance;
            $mail_domain = $rcube->config->mail_domain($data['host']);
            $user_name   = $data['user_name'];
            $user_email  = $data['user_email'];
            $email_list  = $data['email_list'];

            if (empty($email_list)) {
                if (empty($user_email)) {
                    $user_email = strpos($data['user'], '@') ? $user : sprintf('%s@%s', $data['user'], $mail_domain);
                }
                $email_list[] = $user_email;
            }
            // identities_level check
            else if (count($email_list) > 1 && $rcube->config->get('identities_level', 0) > 1) {
                $email_list = array($email_list[0]);
            }

            if (empty($user_name)) {
                $user_name = $data['user'];
            }

            // create new identities records
            $standard = 1;
            foreach ($email_list as $row) {
                $record = array();

                if (is_array($row)) {
                    if (empty($row['email'])) {
                        continue;
                    }
                    $record = $row;
                }
                else {
                    $record['email'] = $row;
                }

                if (empty($record['name'])) {
                    $record['name'] = $user_name != $record['email'] ? $user_name : '';
                }

                $record['user_id']  = $user_id;
                $record['standard'] = $standard;

                $plugin = $rcube->plugins->exec_hook('identity_create',
                    array('login' => true, 'record' => $record));

                if (!$plugin['abort'] && $plugin['record']['email']) {
                    $rcube->user->insert_identity($plugin['record']);
                }
                $standard = 0;
            }
        }
        else {
            rcube::raise_error(array(
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
        $rcube = rcube::get_instance();
        $plugin = $rcube->plugins->exec_hook('email2user',
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
        $rcube = rcube::get_instance();
        $plugin = $rcube->plugins->exec_hook('user2email',
            array('email' => NULL, 'user' => $user,
                'first' => $first, 'extended' => $extended));

        return empty($plugin['email']) ? NULL : $plugin['email'];
    }


    /**
     * Return a list of saved searches linked with this user
     *
     * @param int  $type  Search type
     *
     * @return array List of saved searches indexed by search ID
     */
    function list_searches($type)
    {
        $plugin = $this->rc->plugins->exec_hook('saved_search_list', array('type' => $type));

        if ($plugin['abort']) {
            return (array) $plugin['result'];
        }

        $result = array();

        $sql_result = $this->db->query(
            "SELECT search_id AS id, ".$this->db->quoteIdentifier('name')
            ." FROM ".$this->db->table_name('searches')
            ." WHERE user_id = ?"
                ." AND ".$this->db->quoteIdentifier('type')." = ?"
            ." ORDER BY ".$this->db->quoteIdentifier('name'),
            (int) $this->ID, (int) $type);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $sql_arr['data'] = unserialize($sql_arr['data']);
            $result[$sql_arr['id']] = $sql_arr;
        }

        return $result;
    }


    /**
     * Return saved search data.
     *
     * @param int  $id  Row identifier
     *
     * @return array Data
     */
    function get_search($id)
    {
        $plugin = $this->rc->plugins->exec_hook('saved_search_get', array('id' => $id));

        if ($plugin['abort']) {
            return $plugin['result'];
        }

        $sql_result = $this->db->query(
            "SELECT ".$this->db->quoteIdentifier('name')
                .", ".$this->db->quoteIdentifier('data')
                .", ".$this->db->quoteIdentifier('type')
            ." FROM ".$this->db->table_name('searches')
            ." WHERE user_id = ?"
                ." AND search_id = ?",
            (int) $this->ID, (int) $id);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            return array(
                'id'   => $id,
                'name' => $sql_arr['name'],
                'type' => $sql_arr['type'],
                'data' => unserialize($sql_arr['data']),
            );
        }

        return null;
    }


    /**
     * Deletes given saved search record
     *
     * @param  int  $sid  Search ID
     *
     * @return boolean True if deleted successfully, false if nothing changed
     */
    function delete_search($sid)
    {
        if (!$this->ID)
            return false;

        $this->db->query(
            "DELETE FROM ".$this->db->table_name('searches')
            ." WHERE user_id = ?"
                ." AND search_id = ?",
            (int) $this->ID, $sid);

        return $this->db->affected_rows();
    }


    /**
     * Create a new saved search record linked with this user
     *
     * @param array $data Hash array with col->value pairs to save
     *
     * @return int  The inserted search ID or false on error
     */
    function insert_search($data)
    {
        if (!$this->ID)
            return false;

        $insert_cols[]   = 'user_id';
        $insert_values[] = (int) $this->ID;
        $insert_cols[]   = $this->db->quoteIdentifier('type');
        $insert_values[] = (int) $data['type'];
        $insert_cols[]   = $this->db->quoteIdentifier('name');
        $insert_values[] = $data['name'];
        $insert_cols[]   = $this->db->quoteIdentifier('data');
        $insert_values[] = serialize($data['data']);

        $sql = "INSERT INTO ".$this->db->table_name('searches')
            ." (".join(', ', $insert_cols).")"
            ." VALUES (".join(', ', array_pad(array(), sizeof($insert_values), '?')).")";

        call_user_func_array(array($this->db, 'query'),
            array_merge(array($sql), $insert_values));

        return $this->db->insert_id('searches');
    }

}
