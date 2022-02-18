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
 |   Interface to the local address book database                        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Abstract skeleton of an address book/repository
 *
 * @package    Framework
 * @subpackage Addressbook
 */
abstract class rcube_addressbook
{
    // constants for error reporting
    const ERROR_READ_ONLY     = 1;
    const ERROR_NO_CONNECTION = 2;
    const ERROR_VALIDATE      = 3;
    const ERROR_SAVING        = 4;
    const ERROR_SEARCH        = 5;

    // search modes
    const SEARCH_ALL    = 0;
    const SEARCH_STRICT = 1;
    const SEARCH_PREFIX = 2;
    const SEARCH_GROUPS = 4;

    // contact types, note: some of these are used as addressbook source identifiers
    const TYPE_CONTACT        = 0;
    const TYPE_RECIPIENT      = 1;
    const TYPE_TRUSTED_SENDER = 2;
    const TYPE_DEFAULT        = 4;
    const TYPE_WRITEABLE      = 8;
    const TYPE_READONLY       = 16;

    // public properties (mandatory)

    /** @var string Name of the primary key field of this addressbook. Used to search for previously retrieved IDs. */
    public $primary_key;

    /** @var bool True if the addressbook supports contact groups. */
    public $groups = false;

    /**
     * @var bool True if the addressbook supports exporting contact groups. Requires the implementation of
     *              get_record_groups().
     */
    public $export_groups = true;

    /** @var bool True if the addressbook is read-only. */
    public $readonly = true;

    /**
     * @var bool True if the addressbook does not support listing all records but needs use of the search function.
     */
    public $searchonly = false;

    /** @var bool True if the addressbook supports restoring deleted contacts. */
    public $undelete = false;

    /** @var bool True if the addressbook is ready to be used. See rcmail_action_contacts_index::$CONTACT_COLTYPES */
    public $ready = false;

    /**
     * @var null|string|int If set, addressbook-specific identifier of the selected group. All contact listing and
     *                      contact searches will be limited to contacts that belong to this group.
     */
    public $group_id = null;

    /** @var int The current page of the listing. Numbering starts at 1. */
    public $list_page = 1;

    /** @var int The maximum number of records shown on a page. */
    public $page_size = 10;

    /** @var string Contact field by which to order listed records. */
    public $sort_col = 'name';

    /** @var string Whether sorting of records by $sort_col is done in ascending (ASC) or descending (DESC) order. */
    public $sort_order = 'ASC';

    /** @var string[] A list of record fields that contain dates. */
    public $date_cols = [];

    /** @var array Definition of the contact fields supported by the addressbook. */
    public $coltypes = [
        'name'      => ['limit' => 1],
        'firstname' => ['limit' => 1],
        'surname'   => ['limit' => 1],
        'email'     => ['limit' => 1]
    ];

    /**
     * @var string[] vCard additional fields mapping
     */
    public $vcard_map = [];

    /** @var ?array Error state - hash array with the following fields: type, message */
    protected $error;


    /**
     * Returns addressbook name (e.g. for addressbooks listing)
     * @return string
     */
    abstract function get_name();

    /**
     * Sets a search filter.
     *
     * This affects the contact set considered when using the count() and list_records() operations to those
     * contacts that match the filter conditions. If no search filter is set, all contacts in the addressbook are
     * considered.
     *
     * This filter mechanism is applied in addition to other filter mechanisms, see the description of the count()
     * operation.
     *
     * @param mixed $filter Search params to use in listing method, obtained by get_search_set()
     * @return void
     */
    abstract function set_search_set($filter);

    /**
     * Getter for saved search properties.
     *
     * The filter representation is opaque to roundcube, but can be set again using set_search_set().
     *
     * @return mixed Search properties used by this class
     */
    abstract function get_search_set();

    /**
     * Reset saved results and search parameters
     * @return void
     */
    abstract function reset();

    /**
     * Refresh saved search set after data has changed
     *
     * @return mixed New search set
     */
    function refresh_search()
    {
        return $this->get_search_set();
    }

    /**
     * Lists the current set of contact records.
     *
     * See the description of count() for the criteria determining which contacts are considered for the listing.
     *
     * The actual records returned may be fewer, as only the records for the current page are returned. The returned
     * records may be further limited by the $subset parameter, which means that only the first or last $subset records
     * of the page are returned, depending on whether $subset is positive or negative. If $subset is 0, all records of
     * the page are returned. The returned records are found in the $records property of the returned result set.
     *
     * Finally, the $first property of the returned result set contains the index into the total set of filtered records
     * (i.e. not considering the segmentation into pages) of the first returned record before applying the $subset
     * parameter (i.e., $first is always a multiple of the page size).
     *
     * The $nocount parameter is an optimization that allows to skip querying the total amount of records of the
     * filtered set if the caller is only interested in the records. In this case, the $count property of the returned
     * result set will simply contain the number of returned records, but the filtered set may contain more records than
     * this.
     *
     * The result of the operation is internally cached for later retrieval using get_result().
     *
     * @param ?array $cols    List of columns to include in the returned records (null means all)
     * @param int    $subset  Only return this number of records of the current page, use negative values for tail
     * @param bool   $nocount True to skip the count query (select only)
     *
     * @return rcube_result_set Indexed list of contact records, each a hash array
     */
    abstract function list_records($cols = null, $subset = 0, $nocount = false);

    /**
     * Search records
     *
     * Depending on the given parameters the search() function operates in different ways (in the order listed):
     *
     * "Direct ID search" - when $fields is either 'ID' or $this->primary_key
     *     - $values is either a string of contact IDs separated by self::SEPARATOR (,) or an array of contact IDs
     *     - Any contact with one of the given IDs is returned
     *
     * "Advanced search" - when $value is an array
     *     - Each value in $values is the search value for the field in $fields at the same index
     *     - All fields must match their value to be included in the result ("AND" semantics)
     *
     * "Search all fields" - when $fields is '*' (note: $value is a single string)
     *     - Any field must match the value to be included in the result ("OR" semantics)
     *
     * "Search given fields" - if none of the above matches
     *     - Any of the given fields must match the value to be included in the result ("OR" semantics)
     *
     * All matching is done case insensitive.
     *
     * The search settings are remembered (see set_search_set()) until reset using the reset() function. They can be
     * retrieved using get_search_set(). The remembered search settings must be considered by list_records() and
     * count().
     *
     * The search mode can be set by the admin via the config.inc.php setting addressbook_search_mode.
     * It is used as a bit mask, but the search modes are exclusive (SEARCH_GROUPS is combined with one of other modes):
     *   SEARCH_ALL: substring search (*abc*)
     *   SEARCH_STRICT: Exact match search (case insensitive =)
     *   SEARCH_PREFIX: Prefix search (abc*)
     *   SEARCH_GROUPS: include groups in search results (if supported)
     *
     * When records are requested in the returned rcube_result_set ($select is true), the results will only include the
     * contacts of the current page (see list_page, page_size). The behavior is as described with the list_records
     * function, and search() can be thought of as a sequence of set_search_set() and list_records() under that filter.
     *
     * If $nocount is true, the count property of the returned rcube_result_set will contain the amount of records
     * contained within that set. Calling search() with $select=false and $nocount=true is not a meaningful use case and
     * will result in an empty result set without records and a count property of 0, which gives no indication on the
     * actual record set matching the given filter.
     *
     * The result of the operation is internally cached for later retrieval using get_result().
     *
     * @param string|string[] $fields   Field names to search in
     * @param string|string[] $value    Search value, or array of values, one for each field in $fields
     * @param int             $mode     Search mode. Sum of rcube_addressbook::SEARCH_*.
     * @param bool            $select   True if records are requested in the result, false if count only
     * @param bool            $nocount  True to skip the count query (select only)
     * @param string|string[] $required Field or list of fields that cannot be empty
     *
     * @return rcube_result_set Contact records and 'count' value
     */
    abstract function search($fields, $value, $mode = 0, $select = true, $nocount = false, $required = []);

    /**
     * Count the number of contacts in the database matching the current filter criteria.
     *
     * The current filter criteria are defined by the search filter (see search()/set_search_set()) and the currently
     * active group (see set_group()), if applicable.
     *
     * @return rcube_result_set Result set with values for 'count' and 'first'
     */
    abstract function count();

    /**
     * Return the last result set
     *
     * @return ?rcube_result_set Current result set or NULL if nothing selected yet
     */
    abstract function get_result();

    /**
     * Get a specific contact record
     *
     * @param mixed $id    Record identifier(s)
     * @param bool  $assoc True to return record as associative array, otherwise a result set is returned
     *
     * @return rcube_result_set|array Result object with all record fields
     */
    abstract function get_record($id, $assoc = false);

    /**
     * Returns the last error occurred (e.g. when updating/inserting failed)
     *
     * @return ?array Hash array with the following fields: type, message. Null if no error set.
     */
    function get_error()
    {
        return $this->error;
    }

    /**
     * Setter for errors for internal use
     *
     * @param int    $type    Error type (one of this class' error constants)
     * @param string $message Error message (name of a text label)
     */
    protected function set_error($type, $message)
    {
        $this->error = ['type' => $type, 'message' => $message];
    }

    /**
     * Close connection to source
     * Called on script shutdown
     */
    function close() { }

    /**
     * Set internal list page
     *
     * @param int $page Page number to list
     */
    function set_page($page)
    {
        $this->list_page = (int) $page;
    }

    /**
     * Set internal page size
     *
     * @param int $size Number of messages to display on one page
     */
    function set_pagesize($size)
    {
        $this->page_size = (int) $size;
    }

    /**
     * Set internal sort settings
     *
     * @param ?string $sort_col   Sort column
     * @param ?string $sort_order Sort order
     */
    function set_sort_order($sort_col, $sort_order = null)
    {
        if ($sort_col && (array_key_exists($sort_col, $this->coltypes) || in_array($sort_col, $this->coltypes))) {
            $this->sort_col = $sort_col;
        }

        if ($sort_order) {
            $this->sort_order = strtoupper($sort_order) == 'DESC' ? 'DESC' : 'ASC';
        }
    }

    /**
     * Check the given data before saving.
     * If input isn't valid, the message to display can be fetched using get_error()
     *
     * @param array &$save_data Associative array with data to save
     * @param bool  $autofix    Attempt to fix/complete record automatically
     *
     * @return bool True if input is valid, False if not.
     */
    public function validate(&$save_data, $autofix = false)
    {
        $rcube = rcube::get_instance();
        $valid = true;

        // check validity of email addresses
        foreach ($this->get_col_values('email', $save_data, true) as $email) {
            if (strlen($email)) {
                if (!rcube_utils::check_email(rcube_utils::idn_to_ascii($email))) {
                    $error = $rcube->gettext(['name' => 'emailformaterror', 'vars' => ['email' => $email]]);
                    $this->set_error(self::ERROR_VALIDATE, $error);
                    $valid = false;
                    break;
                }
            }
        }

        // allow plugins to do contact validation and auto-fixing
        $plugin = $rcube->plugins->exec_hook('contact_validate', [
                'record'  => $save_data,
                'autofix' => $autofix,
                'valid'   => $valid,
        ]);

        if ($valid && !$plugin['valid']) {
            $this->set_error(self::ERROR_VALIDATE, $plugin['error']);
        }

        if (is_array($plugin['record'])) {
            $save_data = $plugin['record'];
        }

        return $plugin['valid'];
    }

    /**
     * Create a new contact record
     *
     * @param array $save_data Associative array with save data
     *                         Keys:   Field name with optional section in the form FIELD:SECTION
     *                         Values: Field value. Can be either a string or an array of strings for multiple values
     * @param bool  $check     True to check for duplicates first
     *
     * @return mixed The created record ID on success, False on error
     */
    function insert($save_data, $check = false)
    {
        // empty for read-only address books
    }

    /**
     * Create new contact records for every item in the record set
     *
     * @param rcube_result_set $recset Recordset to insert
     * @param bool             $check  True to check for duplicates first
     *
     * @return array List of created record IDs
     */
    function insertMultiple($recset, $check = false)
    {
        $ids = [];
        if ($recset instanceof rcube_result_set) {
            while ($row = $recset->next()) {
                if ($insert = $this->insert($row, $check)) {
                    $ids[] = $insert;
                }
            }
        }

        return $ids;
    }

    /**
     * Update a specific contact record
     *
     * @param mixed $id        Record identifier
     * @param array $save_cols Associative array with save data
     *                         Keys:   Field name with optional section in the form FIELD:SECTION
     *                         Values: Field value. Can be either a string or an array of strings for multiple values
     *
     * @return mixed On success if ID has been changed returns ID, otherwise True, False on error
     */
    function update($id, $save_cols)
    {
        // empty for read-only address books
    }

    /**
     * Mark one or more contact records as deleted
     *
     * @param array $ids   Record identifiers
     * @param bool  $force Remove records irreversible (see self::undelete)
     *
     * @return int|false Number of removed records, False on failure
     */
    function delete($ids, $force = true)
    {
        // empty for read-only address books
    }

    /**
     * Unmark delete flag on contact record(s)
     *
     * @param array $ids Record identifiers
     */
    function undelete($ids)
    {
        // empty for read-only address books
    }

    /**
     * Mark all records in database as deleted
     *
     * @param bool $with_groups Remove also groups
     */
    function delete_all($with_groups = false)
    {
        // empty for read-only address books
    }

    /**
     * Sets/clears the current group.
     *
     * This affects the contact set considered when using the count(), list_records() and search() operations to those
     * contacts that belong to the given group. If no current group is set, all contacts in the addressbook are
     * considered.
     *
     * This filter mechanism is applied in addition to other filter mechanisms, see the description of the count()
     * operation.
     *
     * @param null|int|string $gid Database identifier of the group. Use 0/"0"/null to reset the group filter.
     */
    function set_group($group_id)
    {
        // empty for address books don't supporting groups
    }

    /**
     * List all active contact groups of this source
     *
     * @param ?string $search Optional search string to match group name
     * @param int     $mode   Search mode. Sum of self::SEARCH_*
     *
     * @return array Indexed list of contact groups, each a hash array
     */
    function list_groups($search = null, $mode = 0)
    {
        // empty for address books don't supporting groups
        return [];
    }

    /**
     * Get group properties such as name and email address(es)
     *
     * @param string $group_id Group identifier
     *
     * @return ?array Group properties as hash array, null in case of error.
     */
    function get_group($group_id)
    {
        // empty for address books don't supporting groups
        return null;
    }

    /**
     * Create a contact group with the given name
     *
     * @param string $name The group name
     *
     * @return array|false False on error, array with record props in success
     */
    function create_group($name)
    {
        // empty for address books don't supporting groups
        return false;
    }

    /**
     * Delete the given group and all linked group members
     *
     * @param string $group_id Group identifier
     *
     * @return bool True on success, false if no data was changed
     */
    function delete_group($group_id)
    {
        // empty for address books don't supporting groups
        return false;
    }

    /**
     * Rename a specific contact group
     *
     * @param string $group_id Group identifier
     * @param string $newname  New name to set for this group
     * @param string &$newid   New group identifier (if changed, otherwise don't set)
     *
     * @return string|false New name on success, false if no data was changed
     */
    function rename_group($group_id, $newname, &$newid)
    {
        // empty for address books don't supporting groups
        return false;
    }

    /**
     * Add the given contact records the a certain group
     *
     * @param string       $group_id Group identifier
     * @param array|string $ids      List of contact identifiers to be added
     *
     * @return int Number of contacts added
     */
    function add_to_group($group_id, $ids)
    {
        // empty for address books don't supporting groups
        return 0;
    }

    /**
     * Remove the given contact records from a certain group
     *
     * @param string       $group_id Group identifier
     * @param array|string $ids      List of contact identifiers to be removed
     *
     * @return int Number of deleted group members
     */
    function remove_from_group($group_id, $ids)
    {
        // empty for address books don't supporting groups
        return 0;
    }

    /**
     * Get group assignments of a specific contact record
     *
     * @param mixed $id Record identifier
     *
     * @return array List of assigned groups indexed by a group ID.
     *               Every array element can be just a group name (string), or an array
     *               with 'ID' and 'name' elements.
     * @since 0.5-beta
     */
    function get_record_groups($id)
    {
        // empty for address books don't supporting groups
        return [];
    }

    /**
     * Utility function to return all values of a certain data column
     * either as flat list or grouped by subtype
     *
     * @param string $col  Col name
     * @param array  $data Record data array as used for saving
     * @param bool   $flat True to return one array with all values,
     *                     False for hash array with values grouped by type
     *
     * @return array List of column values
     */
    public static function get_col_values($col, $data, $flat = false)
    {
        $out = [];
        foreach ((array) $data as $c => $values) {
            if ($c === $col || strpos($c, $col.':') === 0) {
                if ($flat) {
                    $out = array_merge($out, (array) $values);
                }
                else {
                    list(, $type) = rcube_utils::explode(':', $c);
                    if ($type !== null && isset($out[$type])) {
                        $out[$type] = array_merge((array) $out[$type], (array) $values);
                    }
                    else {
                        $out[$type] = (array) $values;
                    }
                }
            }
        }

        // remove duplicates
        if ($flat && !empty($out)) {
            $out = array_unique($out);
        }

        return $out;
    }

    /**
     * Compose a valid display name from the given structured contact data
     *
     * @param array $contact    Hash array with contact data as key-value pairs
     * @param bool  $full_email Don't attempt to extract components from the email address
     *
     * @return string Display name
     */
    public static function compose_display_name($contact, $full_email = false)
    {
        $contact = rcube::get_instance()->plugins->exec_hook('contact_displayname', $contact);
        $fn      = $contact['name'] ?? '';

        // default display name composition according to vcard standard
        if (!$fn) {
            $keys = ['prefix', 'firstname', 'middlename', 'surname', 'suffix'];
            $fn   = implode(' ', array_filter(array_intersect_key($contact, array_flip($keys))));
            $fn   = trim(preg_replace('/\s+/u', ' ', $fn));
        }

        // use email address part for name
        $email = self::get_col_values('email', $contact, true);
        $email = $email[0] ?? null;

        if ($email && (empty($fn) || $fn == $email)) {
            // return full email
            if ($full_email) {
                return $email;
            }

            list($emailname) = explode('@', $email);

            if (preg_match('/(.*)[\.\-\_](.*)/', $emailname, $match)) {
                $fn = trim(ucfirst($match[1]).' '.ucfirst($match[2]));
            }
            else {
                $fn = ucfirst($emailname);
            }
        }

        return $fn;
    }

    /**
     * Compose the name to display in the contacts list for the given contact record.
     * This respects the settings parameter how to list contacts.
     *
     * @param array $contact Hash array with contact data as key-value pairs
     *
     * @return string List name
     */
    public static function compose_list_name($contact)
    {
        static $compose_mode;

        if (!isset($compose_mode)) {
            $compose_mode = (int) rcube::get_instance()->config->get('addressbook_name_listing', 0);
        }

        $get_names = function ($contact, $fields) {
            $result = [];
            foreach ($fields as $field) {
                if (!empty($contact[$field])) {
                    $result[] = $contact[$field];
                }
            }
            return $result;
        };

        switch ($compose_mode) {
        case 3:
            $names = $get_names($contact, ['firstname', 'middlename']);
            if (!empty($contact['surname'])) {
                array_unshift($names, $contact['surname'] . ',');
            }
            $fn = implode(' ', $names);
            break;
        case 2:
            $keys = ['surname', 'firstname', 'middlename'];
            $fn   = implode(' ', $get_names($contact, $keys));
            break;
        case 1:
            $keys = ['firstname', 'middlename', 'surname'];
            $fn   = implode(' ', $get_names($contact, $keys));
            break;
        case 0:
            if (!empty($contact['name'])) {
                $fn = $contact['name'];
            }
            else {
                $keys = ['prefix', 'firstname', 'middlename', 'surname', 'suffix'];
                $fn   = implode(' ', $get_names($contact, $keys));
            }
            break;
        default:
            $plugin = rcube::get_instance()->plugins->exec_hook('contact_listname', ['contact' => $contact]);
            $fn     = $plugin['fn'];
        }

        $fn = trim($fn, ', ');
        $fn = preg_replace('/\s+/u', ' ', $fn);

        // fallbacks...
        if ($fn === '') {
            // ... display name
            if (isset($contact['name']) && ($name = trim($contact['name']))) {
                $fn = $name;
            }
            // ... organization
            else if (isset($contact['organization']) && ($org = trim($contact['organization']))) {
                $fn = $org;
            }
            // ... email address
            else if (($email = self::get_col_values('email', $contact, true)) && !empty($email)) {
                $fn = $email[0];
            }
        }

        return $fn;
    }

    /**
     * Build contact display name for autocomplete listing
     *
     * @param array  $contact Hash array with contact data as key-value pairs
     * @param string $email   Optional email address
     * @param string $name    Optional name (self::compose_list_name() result)
     * @param string $templ   Optional template to use (defaults to the 'contact_search_name' config option)
     *
     * @return string Display name
     */
    public static function compose_search_name($contact, $email = null, $name = null, $templ = null)
    {
        static $template;

        if (empty($templ) && !isset($template)) {  // cache this
            $template = rcube::get_instance()->config->get('contact_search_name');
            if (empty($template)) {
                $template = '{name} <{email}>';
            }
        }

        $result = $templ ?: $template;

        if (preg_match_all('/\{[a-z]+\}/', $result, $matches)) {
            foreach ($matches[0] as $key) {
                $key   = trim($key, '{}');
                $value = '';

                switch ($key) {
                case 'name':
                    $value = $name ?: self::compose_list_name($contact);

                    // If name(s) are undefined compose_list_name() may return an email address
                    // here we prevent from returning the same name and email
                    if ($name === $email && strpos($result, '{email}') !== false) {
                        $value = '';
                    }

                    break;

                case 'email':
                    $value = $email;
                    break;
                }

                if (empty($value)) {
                    $value = strpos($key, ':') ? $contact[$key] : self::get_col_values($key, $contact, true);
                    if (is_array($value) && isset($value[0])) {
                        $value = $value[0];
                    }
                }

                if (!is_string($value)) {
                    $value = '';
                }

                $result = str_replace('{' . $key . '}', $value, $result);
            }
        }

        $result = preg_replace('/\s+/u', ' ', $result);
        $result = preg_replace('/\s*(<>|\(\)|\[\])/u', '', $result);
        $result = trim($result, '/ ');

        return $result;
    }

    /**
     * Create a unique key for sorting contacts
     *
     * @param array  $contact  Contact record
     * @param string $sort_col Sorting column name
     *
     * @return string Unique key
     */
    public static function compose_contact_key($contact, $sort_col)
    {
        $key = isset($contact[$sort_col]) ? $contact[$sort_col] : null;

        // add email to a key to not skip contacts with the same name (#1488375)
        if (($email = self::get_col_values('email', $contact, true)) && !empty($email)) {
            $key .= ':' . implode(':', (array)$email);
        }

        // Make the key really unique (as we e.g. support contacts with no email)
        $key .= ':' . $contact['sourceid'] . ':' . $contact['ID'];

        return $key;
    }

    /**
     * Compare search value with contact data
     *
     * @param string       $colname Data name
     * @param string|array $value   Data value
     * @param string       $search  Search value
     * @param int          $mode    Search mode
     *
     * @return bool Comparison result
     */
    protected function compare_search_value($colname, $value, $search, $mode)
    {
        // The value is a date string, for date we'll
        // use only strict comparison (mode = 1)
        // @TODO: partial search, e.g. match only day and month
        if (in_array($colname, $this->date_cols)) {
            return (($value = rcube_utils::anytodatetime($value))
                && ($search = rcube_utils::anytodatetime($search))
                && $value->format('Ymd') == $search->format('Ymd'));
        }

        // Gender is a special value, must use strict comparison (#5757)
        if ($colname == 'gender') {
            $mode = self::SEARCH_STRICT;
        }

        // composite field, e.g. address
        foreach ((array) $value as $val) {
            $val = mb_strtolower($val);

            if ($mode & self::SEARCH_STRICT) {
                $got = ($val == $search);
            }
            else if ($mode & self::SEARCH_PREFIX) {
                $got = ($search == substr($val, 0, strlen($search)));
            }
            else {
                $got = (strpos($val, $search) !== false);
            }

            if ($got) {
                return true;
            }
        }

        return false;
    }
}
