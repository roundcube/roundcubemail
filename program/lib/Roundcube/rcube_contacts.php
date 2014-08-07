<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to the local address book database                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/


/**
 * Model class for the local address book database
 *
 * @package    Framework
 * @subpackage Addressbook
 */
class rcube_contacts extends rcube_addressbook
{
    // protected for backward compat. with some plugins
    protected $db_name = 'contacts';
    protected $db_groups = 'contactgroups';
    protected $db_groupmembers = 'contactgroupmembers';
    protected $vcard_fieldmap = array();

    /**
     * Store database connection.
     *
     * @var rcube_db
     */
    private $db = null;
    private $user_id = 0;
    private $filter = null;
    private $result = null;
    private $cache;
    private $table_cols = array('name', 'email', 'firstname', 'surname');
    private $fulltext_cols = array('name', 'firstname', 'surname', 'middlename', 'nickname',
      'jobtitle', 'organization', 'department', 'maidenname', 'email', 'phone',
      'address', 'street', 'locality', 'zipcode', 'region', 'country', 'website', 'im', 'notes');

    // public properties
    public $primary_key = 'contact_id';
    public $name;
    public $readonly = false;
    public $groups = true;
    public $undelete = true;
    public $list_page = 1;
    public $page_size = 10;
    public $group_id = 0;
    public $ready = false;
    public $coltypes = array('name', 'firstname', 'surname', 'middlename', 'prefix', 'suffix', 'nickname',
      'jobtitle', 'organization', 'department', 'assistant', 'manager',
      'gender', 'maidenname', 'spouse', 'email', 'phone', 'address',
      'birthday', 'anniversary', 'website', 'im', 'notes', 'photo');
    public $date_cols = array('birthday', 'anniversary');

    const SEPARATOR = ',';


    /**
     * Object constructor
     *
     * @param object  Instance of the rcube_db class
     * @param integer User-ID
     */
    function __construct($dbconn, $user)
    {
        $this->db = $dbconn;
        $this->user_id = $user;
        $this->ready = $this->db && !$this->db->is_error();
    }


    /**
     * Returns addressbook name
     */
     function get_name()
     {
        return $this->name;
     }


    /**
     * Save a search string for future listings
     *
     * @param string SQL params to use in listing method
     */
    function set_search_set($filter)
    {
        $this->filter = $filter;
        $this->cache = null;
    }


    /**
     * Getter for saved search properties
     *
     * @return mixed Search properties used by this class
     */
    function get_search_set()
    {
        return $this->filter;
    }


    /**
     * Setter for the current group
     * (empty, has to be re-implemented by extending class)
     */
    function set_group($gid)
    {
        $this->group_id = $gid;
        $this->cache = null;
    }


    /**
     * Reset all saved results and search parameters
     */
    function reset()
    {
        $this->result = null;
        $this->filter = null;
        $this->cache = null;
    }


    /**
     * List all active contact groups of this source
     *
     * @param string  Search string to match group name
     * @param int     Matching mode:
     *                0 - partial (*abc*),
     *                1 - strict (=),
     *                2 - prefix (abc*)
     *
     * @return array  Indexed list of contact groups, each a hash array
     */
    function list_groups($search = null, $mode = 0)
    {
        $results = array();

        if (!$this->groups)
            return $results;

        if ($search) {
            switch (intval($mode)) {
            case 1:
                $sql_filter = $this->db->ilike('name', $search);
                break;
            case 2:
                $sql_filter = $this->db->ilike('name', $search . '%');
                break;
            default:
                $sql_filter = $this->db->ilike('name', '%' . $search . '%');
            }

            $sql_filter = " AND $sql_filter";
        }

        $sql_result = $this->db->query(
            "SELECT * FROM ".$this->db->table_name($this->db_groups).
            " WHERE del<>1".
            " AND user_id=?".
            $sql_filter.
            " ORDER BY name",
            $this->user_id);

        while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            $sql_arr['ID'] = $sql_arr['contactgroup_id'];
            $results[]     = $sql_arr;
        }

        return $results;
    }


    /**
     * Get group properties such as name and email address(es)
     *
     * @param string Group identifier
     * @return array Group properties as hash array
     */
    function get_group($group_id)
    {
        $sql_result = $this->db->query(
            "SELECT * FROM ".$this->db->table_name($this->db_groups).
            " WHERE del<>1".
            " AND contactgroup_id=?".
            " AND user_id=?",
            $group_id, $this->user_id);

        if ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            $sql_arr['ID'] = $sql_arr['contactgroup_id'];
            return $sql_arr;
        }

        return null;
    }

    /**
     * List the current set of contact records
     *
     * @param  array   List of cols to show, Null means all
     * @param  int     Only return this number of records, use negative values for tail
     * @param  boolean True to skip the count query (select only)
     * @return array  Indexed list of contact records, each a hash array
     */
    function list_records($cols=null, $subset=0, $nocount=false)
    {
        if ($nocount || $this->list_page <= 1) {
            // create dummy result, we don't need a count now
            $this->result = new rcube_result_set();
        } else {
            // count all records
            $this->result = $this->count();
        }

        $start_row = $subset < 0 ? $this->result->first + $this->page_size + $subset : $this->result->first;
        $length = $subset != 0 ? abs($subset) : $this->page_size;

        if ($this->group_id)
            $join = " LEFT JOIN ".$this->db->table_name($this->db_groupmembers)." AS m".
                " ON (m.contact_id = c.".$this->primary_key.")";

        $order_col = (in_array($this->sort_col, $this->table_cols) ? $this->sort_col : 'name');
        $order_cols = array('c.'.$order_col);
        if ($order_col == 'firstname')
            $order_cols[] = 'c.surname';
        else if ($order_col == 'surname')
            $order_cols[] = 'c.firstname';
        if ($order_col != 'name')
            $order_cols[] = 'c.name';
        $order_cols[] = 'c.email';

        $sql_result = $this->db->limitquery(
            "SELECT * FROM ".$this->db->table_name($this->db_name)." AS c" .
            $join .
            " WHERE c.del<>1" .
                " AND c.user_id=?" .
                ($this->group_id ? " AND m.contactgroup_id=?" : "").
                ($this->filter ? " AND (".$this->filter.")" : "") .
            " ORDER BY ". $this->db->concat($order_cols) .
            " " . $this->sort_order,
            $start_row,
            $length,
            $this->user_id,
            $this->group_id);

        // determine whether we have to parse the vcard or if only db cols are requested
        $read_vcard = !$cols || count(array_intersect($cols, $this->table_cols)) < count($cols);

        while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            $sql_arr['ID'] = $sql_arr[$this->primary_key];

            if ($read_vcard)
                $sql_arr = $this->convert_db_data($sql_arr);
            else {
                $sql_arr['email'] = $sql_arr['email'] ? explode(self::SEPARATOR, $sql_arr['email']) : array();
                $sql_arr['email'] = array_map('trim', $sql_arr['email']);
            }

            $this->result->add($sql_arr);
        }

        $cnt = count($this->result->records);

        // update counter
        if ($nocount)
            $this->result->count = $cnt;
        else if ($this->list_page <= 1) {
            if ($cnt < $this->page_size && $subset == 0)
                $this->result->count = $cnt;
            else if (isset($this->cache['count']))
                $this->result->count = $this->cache['count'];
            else
                $this->result->count = $this->_count();
        }

        return $this->result;
    }


    /**
     * Search contacts
     *
     * @param mixed   $fields   The field name of array of field names to search in
     * @param mixed   $value    Search value (or array of values when $fields is array)
     * @param int     $mode     Matching mode:
     *                          0 - partial (*abc*),
     *                          1 - strict (=),
     *                          2 - prefix (abc*)
     * @param boolean $select   True if results are requested, False if count only
     * @param boolean $nocount  True to skip the count query (select only)
     * @param array   $required List of fields that cannot be empty
     *
     * @return object rcube_result_set Contact records and 'count' value
     */
    function search($fields, $value, $mode=0, $select=true, $nocount=false, $required=array())
    {
        if (!is_array($fields))
            $fields = array($fields);
        if (!is_array($required) && !empty($required))
            $required = array($required);

        $where = $and_where = array();
        $mode = intval($mode);
        $WS = ' ';
        $AS = self::SEPARATOR;

        foreach ($fields as $idx => $col) {
            // direct ID search
            if ($col == 'ID' || $col == $this->primary_key) {
                $ids     = !is_array($value) ? explode(self::SEPARATOR, $value) : $value;
                $ids     = $this->db->array2list($ids, 'integer');
                $where[] = 'c.' . $this->primary_key.' IN ('.$ids.')';
                continue;
            }
            // fulltext search in all fields
            else if ($col == '*') {
                $words = array();
                foreach (explode($WS, rcube_utils::normalize_string($value)) as $word) {
                    switch ($mode) {
                    case 1: // strict
                        $words[] = '(' . $this->db->ilike('words', $word . '%')
                            . ' OR ' . $this->db->ilike('words', '%' . $WS . $word . $WS . '%')
                            . ' OR ' . $this->db->ilike('words', '%' . $WS . $word) . ')';
                        break;
                    case 2: // prefix
                        $words[] = '(' . $this->db->ilike('words', $word . '%')
                            . ' OR ' . $this->db->ilike('words', '%' . $WS . $word . '%') . ')';
                        break;
                    default: // partial
                        $words[] = $this->db->ilike('words', '%' . $word . '%');
                    }
                }
                $where[] = '(' . join(' AND ', $words) . ')';
            }
            else {
                $val = is_array($value) ? $value[$idx] : $value;
                // table column
                if (in_array($col, $this->table_cols)) {
                    switch ($mode) {
                    case 1: // strict
                        $where[] = '(' . $this->db->quote_identifier($col) . ' = ' . $this->db->quote($val)
                            . ' OR ' . $this->db->ilike($col, $val . $AS . '%')
                            . ' OR ' . $this->db->ilike($col, '%' . $AS . $val . $AS . '%')
                            . ' OR ' . $this->db->ilike($col, '%' . $AS . $val) . ')';
                        break;
                    case 2: // prefix
                        $where[] = '(' . $this->db->ilike($col, $val . '%')
                            . ' OR ' . $this->db->ilike($col, $AS . $val . '%') . ')';
                        break;
                    default: // partial
                        $where[] = $this->db->ilike($col, '%' . $val . '%');
                    }
                }
                // vCard field
                else {
                    if (in_array($col, $this->fulltext_cols)) {
                        foreach (rcube_utils::normalize_string($val, true) as $word) {
                            switch ($mode) {
                            case 1: // strict
                                $words[] = '(' . $this->db->ilike('words', $word . $WS . '%')
                                    . ' OR ' . $this->db->ilike('words', '%' . $AS . $word . $WS .'%')
                                    . ' OR ' . $this->db->ilike('words', '%' . $AS . $word) . ')';
                                break;
                            case 2: // prefix
                                $words[] = '(' . $this->db->ilike('words', $word . '%')
                                    . ' OR ' . $this->db->ilike('words', $AS . $word . '%') . ')';
                                break;
                            default: // partial
                                $words[] = $this->db->ilike('words', '%' . $word . '%');
                            }
                        }
                        $where[] = '(' . join(' AND ', $words) . ')';
                    }
                    if (is_array($value))
                        $post_search[$col] = mb_strtolower($val);
                }
            }
        }

        foreach (array_intersect($required, $this->table_cols) as $col) {
            $and_where[] = $this->db->quote_identifier($col).' <> '.$this->db->quote('');
        }

        if (!empty($where)) {
            // use AND operator for advanced searches
            $where = join(is_array($value) ? ' AND ' : ' OR ', $where);
        }

        if (!empty($and_where))
            $where = ($where ? "($where) AND " : '') . join(' AND ', $and_where);

        // Post-searching in vCard data fields
        // we will search in all records and then build a where clause for their IDs
        if (!empty($post_search)) {
            $ids = array(0);
            // build key name regexp
            $regexp = '/^(' . implode(array_keys($post_search), '|') . ')(?:.*)$/';
            // use initial WHERE clause, to limit records number if possible
            if (!empty($where))
                $this->set_search_set($where);

            // count result pages
            $cnt   = $this->count();
            $pages = ceil($cnt / $this->page_size);
            $scnt  = count($post_search);

            // get (paged) result
            for ($i=0; $i<$pages; $i++) {
                $this->list_records(null, $i, true);
                while ($row = $this->result->next()) {
                    $id    = $row[$this->primary_key];
                    $found = array();
                    foreach (preg_grep($regexp, array_keys($row)) as $col) {
                        $pos     = strpos($col, ':');
                        $colname = $pos ? substr($col, 0, $pos) : $col;
                        $search  = $post_search[$colname];
                        foreach ((array)$row[$col] as $value) {
                            if ($this->compare_search_value($colname, $value, $search, $mode)) {
                                $found[$colname] = true;
                                break 2;
                            }
                        }
                    }
                    // all fields match
                    if (count($found) >= $scnt) {
                        $ids[] = $id;
                    }
                }
            }

            // build WHERE clause
            $ids = $this->db->array2list($ids, 'integer');
            $where = 'c.' . $this->primary_key.' IN ('.$ids.')';
            // reset counter
            unset($this->cache['count']);

            // when we know we have an empty result
            if ($ids == '0') {
                $this->set_search_set($where);
                return ($this->result = new rcube_result_set(0, 0));
            }
        }

        if (!empty($where)) {
            $this->set_search_set($where);
            if ($select)
                $this->list_records(null, 0, $nocount);
            else
                $this->result = $this->count();
        }

        return $this->result;
    }


    /**
     * Count number of available contacts in database
     *
     * @return rcube_result_set Result object
     */
    function count()
    {
        $count = isset($this->cache['count']) ? $this->cache['count'] : $this->_count();

        return new rcube_result_set($count, ($this->list_page-1) * $this->page_size);
    }


    /**
     * Count number of available contacts in database
     *
     * @return int Contacts count
     */
    private function _count()
    {
        if ($this->group_id)
            $join = " LEFT JOIN ".$this->db->table_name($this->db_groupmembers)." AS m".
                " ON (m.contact_id=c.".$this->primary_key.")";

        // count contacts for this user
        $sql_result = $this->db->query(
            "SELECT COUNT(c.contact_id) AS rows".
            " FROM ".$this->db->table_name($this->db_name)." AS c".
                $join.
            " WHERE c.del<>1".
            " AND c.user_id=?".
            ($this->group_id ? " AND m.contactgroup_id=?" : "").
            ($this->filter ? " AND (".$this->filter.")" : ""),
            $this->user_id,
            $this->group_id
        );

        $sql_arr = $this->db->fetch_assoc($sql_result);

        $this->cache['count'] = (int) $sql_arr['rows'];

        return $this->cache['count'];
    }


    /**
     * Return the last result set
     *
     * @return mixed Result array or NULL if nothing selected yet
     */
    function get_result()
    {
        return $this->result;
    }


    /**
     * Get a specific contact record
     *
     * @param mixed record identifier(s)
     * @return mixed Result object with all record fields or False if not found
     */
    function get_record($id, $assoc=false)
    {
        // return cached result
        if ($this->result && ($first = $this->result->first()) && $first[$this->primary_key] == $id)
            return $assoc ? $first : $this->result;

        $this->db->query(
            "SELECT * FROM ".$this->db->table_name($this->db_name).
            " WHERE contact_id=?".
                " AND user_id=?".
                " AND del<>1",
            $id,
            $this->user_id
        );

        if ($sql_arr = $this->db->fetch_assoc()) {
            $record = $this->convert_db_data($sql_arr);
            $this->result = new rcube_result_set(1);
            $this->result->add($record);
        }

        return $assoc && $record ? $record : $this->result;
    }


    /**
     * Get group assignments of a specific contact record
     *
     * @param mixed Record identifier
     * @return array List of assigned groups as ID=>Name pairs
     */
    function get_record_groups($id)
    {
      $results = array();

      if (!$this->groups)
          return $results;

      $sql_result = $this->db->query(
        "SELECT cgm.contactgroup_id, cg.name FROM " . $this->db->table_name($this->db_groupmembers) . " AS cgm" .
        " LEFT JOIN " . $this->db->table_name($this->db_groups) . " AS cg ON (cgm.contactgroup_id = cg.contactgroup_id AND cg.del<>1)" .
        " WHERE cgm.contact_id=?",
        $id
      );
      while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
        $results[$sql_arr['contactgroup_id']] = $sql_arr['name'];
      }

      return $results;
    }


    /**
     * Check the given data before saving.
     * If input not valid, the message to display can be fetched using get_error()
     *
     * @param array Assoziative array with data to save
     * @param boolean Try to fix/complete record automatically
     * @return boolean True if input is valid, False if not.
     */
    public function validate(&$save_data, $autofix = false)
    {
        // validate e-mail addresses
        $valid = parent::validate($save_data, $autofix);

        // require at least one email address or a name
        if ($valid && !strlen($save_data['firstname'].$save_data['surname'].$save_data['name']) && !array_filter($this->get_col_values('email', $save_data, true))) {
            $this->set_error(self::ERROR_VALIDATE, 'noemailwarning');
            $valid = false;
        }

        return $valid;
    }


    /**
     * Create a new contact record
     *
     * @param array Associative array with save data
     * @return integer|boolean The created record ID on success, False on error
     */
    function insert($save_data, $check=false)
    {
        if (!is_array($save_data))
            return false;

        $insert_id = $existing = false;

        if ($check) {
            foreach ($save_data as $col => $values) {
                if (strpos($col, 'email') === 0) {
                    foreach ((array)$values as $email) {
                        if ($existing = $this->search('email', $email, false, false))
                            break 2;
                    }
                }
            }
        }

        $save_data     = $this->convert_save_data($save_data);
        $a_insert_cols = $a_insert_values = array();

        foreach ($save_data as $col => $value) {
            $a_insert_cols[]   = $this->db->quote_identifier($col);
            $a_insert_values[] = $this->db->quote($value);
        }

        if (!$existing->count && !empty($a_insert_cols)) {
            $this->db->query(
                "INSERT INTO ".$this->db->table_name($this->db_name).
                " (user_id, changed, del, ".join(', ', $a_insert_cols).")".
                " VALUES (".intval($this->user_id).", ".$this->db->now().", 0, ".join(', ', $a_insert_values).")"
            );

            $insert_id = $this->db->insert_id($this->db_name);
        }

        $this->cache = null;

        return $insert_id;
    }


    /**
     * Update a specific contact record
     *
     * @param mixed Record identifier
     * @param array Assoziative array with save data
     *
     * @return boolean True on success, False on error
     */
    function update($id, $save_cols)
    {
        $updated   = false;
        $write_sql = array();
        $record    = $this->get_record($id, true);
        $save_cols = $this->convert_save_data($save_cols, $record);

        foreach ($save_cols as $col => $value) {
            $write_sql[] = sprintf("%s=%s", $this->db->quote_identifier($col), $this->db->quote($value));
        }

        if (!empty($write_sql)) {
            $this->db->query(
                "UPDATE ".$this->db->table_name($this->db_name).
                " SET changed=".$this->db->now().", ".join(', ', $write_sql).
                " WHERE contact_id=?".
                    " AND user_id=?".
                    " AND del<>1",
                $id,
                $this->user_id
            );

            $updated = $this->db->affected_rows();
            $this->result = null;  // clear current result (from get_record())
        }

        return $updated ? true : false;
    }


    private function convert_db_data($sql_arr)
    {
        $record = array();
        $record['ID'] = $sql_arr[$this->primary_key];

        if ($sql_arr['vcard']) {
            unset($sql_arr['email']);
            $vcard = new rcube_vcard($sql_arr['vcard'], RCUBE_CHARSET, false, $this->vcard_fieldmap);
            $record += $vcard->get_assoc() + $sql_arr;
        }
        else {
            $record += $sql_arr;
            $record['email'] = explode(self::SEPARATOR, $record['email']);
            $record['email'] = array_map('trim', $record['email']);
        }

        return $record;
    }


    private function convert_save_data($save_data, $record = array())
    {
        $out = array();
        $words = '';

        // copy values into vcard object
        $vcard = new rcube_vcard($record['vcard'] ? $record['vcard'] : $save_data['vcard'], RCUBE_CHARSET, false, $this->vcard_fieldmap);
        $vcard->reset();
        foreach ($save_data as $key => $values) {
            list($field, $section) = explode(':', $key);
            $fulltext = in_array($field, $this->fulltext_cols);
            // avoid casting DateTime objects to array
            if (is_object($values) && is_a($values, 'DateTime')) {
                $values = array(0 => $values);
            }
            foreach ((array)$values as $value) {
                if (isset($value))
                    $vcard->set($field, $value, $section);
                if ($fulltext && is_array($value))
                    $words .= ' ' . rcube_utils::normalize_string(join(" ", $value));
                else if ($fulltext && strlen($value) >= 3)
                    $words .= ' ' . rcube_utils::normalize_string($value);
            }
        }
        $out['vcard'] = $vcard->export(false);

        foreach ($this->table_cols as $col) {
            $key = $col;
            if (!isset($save_data[$key]))
                $key .= ':home';
            if (isset($save_data[$key])) {
                if (is_array($save_data[$key]))
                    $out[$col] = join(self::SEPARATOR, $save_data[$key]);
                else
                    $out[$col] = $save_data[$key];
            }
        }

        // save all e-mails in database column
        $out['email'] = join(self::SEPARATOR, $vcard->email);

        // join words for fulltext search
        $out['words'] = join(" ", array_unique(explode(" ", $words)));

        return $out;
    }


    /**
     * Mark one or more contact records as deleted
     *
     * @param array   Record identifiers
     * @param boolean Remove record(s) irreversible (unsupported)
     */
    function delete($ids, $force=true)
    {
        if (!is_array($ids))
            $ids = explode(self::SEPARATOR, $ids);

        $ids = $this->db->array2list($ids, 'integer');

        // flag record as deleted (always)
        $this->db->query(
            "UPDATE ".$this->db->table_name($this->db_name).
            " SET del=1, changed=".$this->db->now().
            " WHERE user_id=?".
                " AND contact_id IN ($ids)",
            $this->user_id
        );

        $this->cache = null;

        return $this->db->affected_rows();
    }


    /**
     * Undelete one or more contact records
     *
     * @param array  Record identifiers
     */
    function undelete($ids)
    {
        if (!is_array($ids))
            $ids = explode(self::SEPARATOR, $ids);

        $ids = $this->db->array2list($ids, 'integer');

        // clear deleted flag
        $this->db->query(
            "UPDATE ".$this->db->table_name($this->db_name).
            " SET del=0, changed=".$this->db->now().
            " WHERE user_id=?".
                " AND contact_id IN ($ids)",
            $this->user_id
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
        $this->cache = null;

        $this->db->query("UPDATE " . $this->db->table_name($this->db_name)
            . " SET del = 1, changed = " . $this->db->now()
            . " WHERE user_id = ?", $this->user_id);

        $count = $this->db->affected_rows();

        if ($with_groups) {
            $this->db->query("UPDATE " . $this->db->table_name($this->db_groups)
                . " SET del = 1, changed = " . $this->db->now()
                . " WHERE user_id = ?", $this->user_id);

            $count += $this->db->affected_rows();
        }

        return $count;
    }


    /**
     * Create a contact group with the given name
     *
     * @param string The group name
     * @return mixed False on error, array with record props in success
     */
    function create_group($name)
    {
        $result = false;

        // make sure we have a unique name
        $name = $this->unique_groupname($name);

        $this->db->query(
            "INSERT INTO ".$this->db->table_name($this->db_groups).
            " (user_id, changed, name)".
            " VALUES (".intval($this->user_id).", ".$this->db->now().", ".$this->db->quote($name).")"
        );

        if ($insert_id = $this->db->insert_id($this->db_groups))
            $result = array('id' => $insert_id, 'name' => $name);

        return $result;
    }


    /**
     * Delete the given group (and all linked group members)
     *
     * @param string Group identifier
     * @return boolean True on success, false if no data was changed
     */
    function delete_group($gid)
    {
        // flag group record as deleted
        $this->db->query(
            "UPDATE " . $this->db->table_name($this->db_groups)
            . " SET del = 1, changed = " . $this->db->now()
            . " WHERE contactgroup_id = ?"
            . " AND user_id = ?",
            $gid, $this->user_id
        );

        $this->cache = null;

        return $this->db->affected_rows();
    }

    /**
     * Rename a specific contact group
     *
     * @param string Group identifier
     * @param string New name to set for this group
     * @return boolean New name on success, false if no data was changed
     */
    function rename_group($gid, $newname, &$new_gid)
    {
        // make sure we have a unique name
        $name = $this->unique_groupname($newname);

        $sql_result = $this->db->query(
            "UPDATE ".$this->db->table_name($this->db_groups).
            " SET name=?, changed=".$this->db->now().
            " WHERE contactgroup_id=?".
            " AND user_id=?",
            $name, $gid, $this->user_id
        );

        return $this->db->affected_rows() ? $name : false;
    }


    /**
     * Add the given contact records the a certain group
     *
     * @param string       Group identifier
     * @param array|string List of contact identifiers to be added
     *
     * @return int Number of contacts added
     */
    function add_to_group($group_id, $ids)
    {
        if (!is_array($ids))
            $ids = explode(self::SEPARATOR, $ids);

        $added = 0;
        $exists = array();

        // get existing assignments ...
        $sql_result = $this->db->query(
            "SELECT contact_id FROM ".$this->db->table_name($this->db_groupmembers).
            " WHERE contactgroup_id=?".
                " AND contact_id IN (".$this->db->array2list($ids, 'integer').")",
            $group_id
        );
        while ($sql_result && ($sql_arr = $this->db->fetch_assoc($sql_result))) {
            $exists[] = $sql_arr['contact_id'];
        }
        // ... and remove them from the list
        $ids = array_diff($ids, $exists);

        foreach ($ids as $contact_id) {
            $this->db->query(
                "INSERT INTO ".$this->db->table_name($this->db_groupmembers).
                " (contactgroup_id, contact_id, created)".
                " VALUES (?, ?, ".$this->db->now().")",
                $group_id,
                $contact_id
            );

            if ($error = $this->db->is_error())
                $this->set_error(self::ERROR_SAVING, $error);
            else
                $added++;
        }

        return $added;
    }


    /**
     * Remove the given contact records from a certain group
     *
     * @param string       Group identifier
     * @param array|string List of contact identifiers to be removed
     *
     * @return int Number of deleted group members
     */
    function remove_from_group($group_id, $ids)
    {
        if (!is_array($ids))
            $ids = explode(self::SEPARATOR, $ids);

        $ids = $this->db->array2list($ids, 'integer');

        $sql_result = $this->db->query(
            "DELETE FROM ".$this->db->table_name($this->db_groupmembers).
            " WHERE contactgroup_id=?".
                " AND contact_id IN ($ids)",
            $group_id
        );

        return $this->db->affected_rows();
    }


    /**
     * Check for existing groups with the same name
     *
     * @param string Name to check
     * @return string A group name which is unique for the current use
     */
    private function unique_groupname($name)
    {
        $checkname = $name;
        $num = 2; $hit = false;

        do {
            $sql_result = $this->db->query(
                "SELECT 1 FROM ".$this->db->table_name($this->db_groups).
                " WHERE del<>1".
                    " AND user_id=?".
                    " AND name=?",
                $this->user_id,
                $checkname);

            // append number to make name unique
            if ($hit = $this->db->fetch_array($sql_result)) {
                $checkname = $name . ' ' . $num++;
            }
        } while ($hit);

        return $checkname;
    }

}
