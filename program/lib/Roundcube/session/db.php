<?php

/**
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
 |   Provide database supported session management                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Cor Bosman <cor@roundcu.be>                                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide database session storage
 *
 * @package    Framework
 * @subpackage Core
 */
class rcube_session_db extends rcube_session
{
    /** @var rcube_db Database handler */
    private $db;

    /** @var string Session table name (quoted) */
    private $table_name;


    /**
     * Object constructor
     *
     * @param rcube_config $config Configuration
     */
    public function __construct($config)
    {
        parent::__construct($config);

        // get db instance
        $this->db = rcube::get_instance()->get_dbh();

        // session table name
        $this->table_name = $this->db->table_name('session', true);

        // register sessions handler
        $this->register_session_handler();

        // register db gc handler
        $this->register_gc_handler([$this, 'gc_db']);
    }

    /**
     * Opens the session
     *
     * @param string $save_path    Session save path
     * @param string $session_name Session name
     *
     * @return bool True on success, False on failure
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * Close the session
     *
     * @return bool True on success, False on failure
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy the session
     *
     * @param string $key Session identifier
     *
     * @return bool True on success, False on failure
     */
    public function destroy($key)
    {
        if ($key) {
            $this->db->query("DELETE FROM {$this->table_name} WHERE `sess_id` = ?", $key);
        }

        return true;
    }

    /**
     * Read session data from database
     *
     * @param string $key Session identifier
     *
     * @return string Session vars (serialized string)
     */
    public function read($key)
    {
        if ($this->lifetime) {
            $expire_time  = $this->db->now(-$this->lifetime);
            $expire_check = "CASE WHEN `changed` < $expire_time THEN 1 ELSE 0 END AS expired";
        }

        $sql_result = $this->db->query(
            "SELECT `vars`, `ip`, `changed`, " . $this->db->now() . " AS ts"
            . (isset($expire_check) ? ", $expire_check" : '')
            . " FROM {$this->table_name} WHERE `sess_id` = ?", $key
        );

        if ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            // Remove expired sessions (we use gc, but it may not be precise enough or disabled)
            if (!empty($sql_arr['expired'])) {
                $this->destroy($key);
                return '';
            }

            $time_diff = time() - strtotime($sql_arr['ts']);

            $this->changed   = strtotime($sql_arr['changed']) + $time_diff; // local (PHP) time
            $this->ip        = $sql_arr['ip'];
            $this->vars      = base64_decode($sql_arr['vars']);
            $this->key       = $key;

            $this->db->reset();

            return !empty($this->vars) ? (string) $this->vars : '';
        }

        return '';
    }

    /**
     * Insert new data into db session store
     *
     * @param string $key  Session identifier
     * @param string $vars Serialized data string
     *
     * @return bool True on success, False on failure
     */
    public function write($key, $vars)
    {
        if ($this->ignore_write) {
            return true;
        }

        $now = $this->db->now();

        $this->db->query("INSERT INTO {$this->table_name}"
            . " (`sess_id`, `vars`, `ip`, `changed`)"
            . " VALUES (?, ?, ?, $now)",
            $key, base64_encode($vars), (string)$this->ip
        );

        return true;
    }

    /**
     * Update session data
     *
     * @param string $key     Session identifier
     * @param string $newvars New session data string
     * @param string $oldvars Old session data string
     *
     * @return bool True on success, False on failure
     */
    public function update($key, $newvars, $oldvars)
    {
        $now = $this->db->now();
        $ts  = microtime(true);

        // if new and old data are not the same, update data
        // else update expire timestamp only when certain conditions are met
        if ($newvars !== $oldvars) {
            $this->db->query("UPDATE {$this->table_name} "
                . "SET `changed` = $now, `vars` = ? WHERE `sess_id` = ?",
                base64_encode($newvars), $key);
        }
        else if ($ts - $this->changed > $this->lifetime / 2) {
            $this->db->query("UPDATE {$this->table_name} SET `changed` = $now"
                . " WHERE `sess_id` = ?", $key);
        }

        return true;
    }

    /**
     * Clean up db sessions.
     */
    public function gc_db()
    {
        // just clean all old sessions when this GC is called
        $this->db->query("DELETE FROM " . $this->db->table_name('session')
            . " WHERE `changed` < " . $this->db->now(-$this->gc_enabled));

        $this->log("Session GC (DB): remove records < "
            . date('Y-m-d H:i:s', time() - $this->gc_enabled)
            . '; rows = ' . intval($this->db->affected_rows()));
    }
}
