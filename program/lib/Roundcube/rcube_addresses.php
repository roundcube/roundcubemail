<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to the collected addresses database                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Collected addresses database
 *
 * @package    Framework
 * @subpackage Addressbook
 */
class rcube_addresses extends rcube_contacts
{
    protected $db_name       = 'collected_addresses';
    protected $type          = 0;
    protected $table_cols    = ['name', 'email'];
    protected $fulltext_cols = ['name'];

    // public properties
    public $primary_key = 'address_id';
    public $readonly    = true;
    public $groups      = false;
    public $undelete    = false;
    public $deletable   = true;
    public $coltypes    = ['name', 'email'];
    public $date_cols   = [];


    /**
     * Object constructor
     *
     * @param object $dbconn Instance of the rcube_db class
     * @param int    $user   User-ID
     * @param int    $type   Type of the address (1 - recipient, 2 - trusted sender)
     */
    public function __construct($dbconn, $user, $type)
    {
        $this->db      = $dbconn;
        $this->user_id = $user;
        $this->type    = $type;
        $this->ready   = $this->db && !$this->db->is_error();
    }

    /**
     * Returns addressbook name
     *
     * @return string
     */
    public function get_name()
    {
        if ($this->type == self::TYPE_RECIPIENT) {
            return rcube::get_instance()->gettext('collectedrecipients');
        }

        if ($this->type == self::TYPE_TRUSTED_SENDER) {
            return rcube::get_instance()->gettext('trustedsenders');
        }

        return '';
    }

    /**
     * List the current set of contact records
     *
     * @param  array $cols    List of cols to show, Null means all
     * @param  int   $subset  Only return this number of records, use negative values for tail
     * @param  bool  $nocount True to skip the count query (select only)
     *
     * @return array Indexed list of contact records, each a hash array
     */
    public function list_records($cols = null, $subset = 0, $nocount = false)
    {
        if ($nocount || $this->list_page <= 1) {
            // create dummy result, we don't need a count now
            $this->result = new rcube_result_set();
        }
        else {
            // count all records
            $this->result = $this->count();
        }

        $start_row  = $subset < 0 ? $this->result->first + $this->page_size + $subset : $this->result->first;
        $length     = $subset != 0 ? abs($subset) : $this->page_size;

        $sql_result = $this->db->limitquery(
            "SELECT * FROM " . $this->db->table_name($this->db_name, true)
            . " WHERE `user_id` = ? AND `type` = ?"
            . ($this->filter ? " AND ".$this->filter : "")
            . " ORDER BY `name` " . $this->sort_order . ", `email` " . $this->sort_order,
            $start_row,
            $length,
            $this->user_id,
            $this->type
        );

        while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            $sql_arr['ID'] = $sql_arr[$this->primary_key];
            $this->result->add($sql_arr);
        }

        $cnt = count($this->result->records);

        // update counter
        if ($nocount) {
            $this->result->count = $cnt;
        }
        else if ($this->list_page <= 1) {
            if ($cnt < $this->page_size && $subset == 0) {
                $this->result->count = $cnt;
            }
            else if (isset($this->cache['count'])) {
                $this->result->count = $this->cache['count'];
            }
            else {
                $this->result->count = $this->_count();
            }
        }

        return $this->result;
    }

    /**
     * Search contacts
     *
     * @param mixed $fields   The field name or array of field names to search in
     * @param mixed $value    Search value (or array of values when $fields is array)
     * @param int   $mode     Search mode. Sum of rcube_addressbook::SEARCH_*
     * @param bool  $select   True if results are requested, False if count only
     * @param bool  $nocount  True to skip the count query (select only)
     * @param array $required List of fields that cannot be empty
     *
     * @return rcube_result_set Contact records and 'count' value
     */
    public function search($fields, $value, $mode = 0, $select = true, $nocount = false, $required = [])
    {
        if (!is_array($required) && !empty($required)) {
            $required = [$required];
        }

        $where = $post_search = [];
        $mode  = intval($mode);

        // direct ID search
        if ($fields == 'ID' || $fields == $this->primary_key) {
            $ids     = !is_array($value) ? explode(self::SEPARATOR, $value) : $value;
            $ids     = $this->db->array2list($ids, 'integer');
            $where[] = $this->primary_key . ' IN (' . $ids . ')';
        }
        else if (is_array($value)) {
            foreach ((array) $fields as $idx => $col) {
                $val = $value[$idx];

                if (!strlen($val)) {
                    continue;
                }

                // table column
                if ($col == 'email' && ($mode & rcube_addressbook::SEARCH_STRICT)) {
                    $where[] = $this->db->ilike($col, $val);
                }
                else if (in_array($col, $this->table_cols)) {
                    $where[] = $this->fulltext_sql_where($val, $mode, $col);
                }
                else {
                    $where[] = '1 = 0'; // unsupported column
                }
            }
        }
        else {
            // fulltext search in all fields
            if ($fields == '*') {
                $fields = ['name', 'email'];
            }

            // require each word in to be present in one of the fields
            $words = ($mode & rcube_addressbook::SEARCH_STRICT) ? [$value] : rcube_utils::tokenize_string($value, 1);
            foreach ($words as $word) {
                $groups = [];
                foreach ((array) $fields as $idx => $col) {
                    if ($col == 'email' && ($mode & rcube_addressbook::SEARCH_STRICT)) {
                        $groups[] = $this->db->ilike($col, $word);
                    }
                    else if (in_array($col, $this->table_cols)) {
                        $groups[] = $this->fulltext_sql_where($word, $mode, $col);
                    }
                }
                $where[] = '(' . implode(' OR ', $groups) . ')';
            }
        }

        foreach (array_intersect($required, $this->table_cols) as $col) {
            $where[] = $this->db->quote_identifier($col) . ' <> ' . $this->db->quote('');
        }

        if (!empty($where)) {
            // use AND operator for advanced searches
            $where = implode(' AND ', $where);

            $this->set_search_set($where);

            if ($select) {
                $this->list_records(null, 0, $nocount);
            }
            else {
                $this->result = $this->count();
            }
        }
        else {
            $this->result = new rcube_result_set();
        }

        return $this->result;
    }

    /**
     * Count number of available contacts in database
     *
     * @return int Contacts count
     */
    protected function _count()
    {
        // count contacts for this user
        $sql_result = $this->db->query(
            "SELECT COUNT(`address_id`) AS cnt"
            . " FROM " . $this->db->table_name($this->db_name, true)
            . " WHERE `user_id` = ? AND `type` = ?"
            . ($this->filter ? " AND (" . $this->filter . ")" : ""),
            $this->user_id,
            $this->type
        );

        $sql_arr = $this->db->fetch_assoc($sql_result);

        $this->cache['count'] = (int) $sql_arr['cnt'];

        return $this->cache['count'];
    }

    /**
     * Get a specific contact record
     *
     * @param mixed $id    Record identifier(s)
     * @param bool  $assoc Enables returning associative array
     *
     * @return rcube_result_set|array Result object with all record fields
     */
    function get_record($id, $assoc = false)
    {
        // return cached result
        if ($this->result && ($first = $this->result->first()) && $first[$this->primary_key] == $id) {
            return $assoc ? $first : $this->result;
        }

        $this->db->query(
            "SELECT * FROM " . $this->db->table_name($this->db_name, true)
            . " WHERE `address_id` = ? AND `user_id` = ?",
            $id,
            $this->user_id
        );

        $this->result = null;

        if ($record = $this->db->fetch_assoc()) {
            $record['ID'] = $record['address_id'];
            $this->result = new rcube_result_set(1);
            $this->result->add($record);
        }

        return $assoc && !empty($record) ? $record : $this->result;
    }

    /**
     * Check the given data before saving.
     * If input not valid, the message to display can be fetched using get_error()
     *
     * @param array &$save_data Associative array with data to save
     * @param bool  $autofix    Try to fix/complete record automatically
     *
     * @return bool True if input is valid, False if not.
     */
    public function validate(&$save_data, $autofix = false)
    {
        $email = array_filter($this->get_col_values('email', $save_data, true));

        // require email
        if (empty($email) || count($email) > 1) {
            $this->set_error(self::ERROR_VALIDATE, 'noemailwarning');
            return false;
        }

        $email = $email[0];

        // check validity of the email address
        if (!rcube_utils::check_email(rcube_utils::idn_to_ascii($email))) {
            $rcube = rcube::get_instance();
            $error = $rcube->gettext(['name' => 'emailformaterror', 'vars' => ['email' => $email]]);
            $this->set_error(self::ERROR_VALIDATE, $error);
            return false;
        }

        return true;
    }

    /**
     * Create a new contact record
     *
     * @param array $save_data Associative array with save data
     * @param bool  $check     Enables validity checks
     *
     * @return int|bool The created record ID on success, False on error
     */
    function insert($save_data, $check = false)
    {
        if (!is_array($save_data)) {
            return false;
        }

        if ($check && ($existing = $this->search('email', $save_data['email'], false, false))) {
            if ($existing->count) {
                return false;
            }
        }

        $this->cache = null;

        $this->db->query(
            "INSERT INTO " . $this->db->table_name($this->db_name, true)
            . " (`user_id`, `changed`, `type`, `name`, `email`)"
            . " VALUES (?, " . $this->db->now() . ", ?, ?, ?)",
            $this->user_id,
            $this->type,
            $save_data['name'],
            $save_data['email']
        );

        return $this->db->insert_id($this->db_name);
    }

    /**
     * Update a specific contact record
     *
     * @param mixed $id        Record identifier
     * @param array $save_cols Associative array with save data
     *
     * @return bool True on success, False on error
     */
    function update($id, $save_cols)
    {
        return false;
    }

    /**
     * Delete one or more contact records
     *
     * @param array $ids   Record identifiers
     * @param bool  $force Remove record(s) irreversible (unsupported)
     *
     * @return int|false Number of removed records
     */
    function delete($ids, $force = true)
    {
        if (!is_array($ids)) {
            $ids = explode(self::SEPARATOR, $ids);
        }

        $ids = $this->db->array2list($ids, 'integer');

        // flag record as deleted (always)
        $this->db->query(
            "DELETE FROM " . $this->db->table_name($this->db_name, true)
            . " WHERE `user_id` = ? AND `type` = ? AND `address_id` IN ($ids)",
            $this->user_id, $this->type
        );

        $this->cache = null;

        return $this->db->affected_rows();
    }

    /**
     * Remove all records from the database
     *
     * @param bool $with_groups Remove also groups
     *
     * @return int Number of removed records
     */
    function delete_all($with_groups = false)
    {
        $this->db->query("DELETE FROM " . $this->db->table_name($this->db_name, true)
            . " WHERE `user_id` = ? AND `type` = ?",
            $this->user_id, $this->type
        );

        $this->cache = null;

        return $this->db->affected_rows();
    }
}
