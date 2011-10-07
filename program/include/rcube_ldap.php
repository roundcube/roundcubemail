<?php
/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_ldap.php                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Interface to an LDAP address directory                              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 |         Andreas Dick <andudi (at) gmx (dot) ch>                       |
 |         Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Model class to access an LDAP address directory
 *
 * @package Addressbook
 */
class rcube_ldap extends rcube_addressbook
{
    /** public properties */
    public $primary_key = 'ID';
    public $groups = false;
    public $readonly = true;
    public $ready = false;
    public $group_id = 0;
    public $list_page = 1;
    public $page_size = 10;
    public $coltypes = array();

    /** private properties */
    protected $conn;
    protected $prop = array();
    protected $fieldmap = array();

    protected $filter = '';
    protected $result = null;
    protected $ldap_result = null;
    protected $sort_col = '';
    protected $mail_domain = '';
    protected $debug = false;

    private $base_dn = '';
    private $groups_base_dn = '';
    private $group_url = null;
    private $group_members = array();
    private $cache;

    private $vlv_active = false;
    private $vlv_count = 0;


    /**
    * Object constructor
    *
    * @param array 	LDAP connection properties
    * @param boolean 	Enables debug mode
    * @param string 	Current user mail domain name
    * @param integer User-ID
    */
    function __construct($p, $debug=false, $mail_domain=NULL)
    {
        $this->prop = $p;

        if (isset($p['searchonly']))
            $this->searchonly = $p['searchonly'];

        // check if groups are configured
        if (is_array($p['groups']) && count($p['groups'])) {
            $this->groups = true;
            // set member field
            if (!empty($p['groups']['member_attr']))
                $this->prop['member_attr'] = strtolower($p['groups']['member_attr']);
            else if (empty($p['member_attr']))
                $this->prop['member_attr'] = 'member';
            // set default name attribute to cn
            if (empty($this->prop['groups']['name_attr']))
                $this->prop['groups']['name_attr'] = 'cn';
        }

        // fieldmap property is given
        if (is_array($p['fieldmap'])) {
            foreach ($p['fieldmap'] as $rf => $lf)
                $this->fieldmap[$rf] = $this->_attr_name(strtolower($lf));
        }
        else {
            // read deprecated *_field properties to remain backwards compatible
            foreach ($p as $prop => $value)
                if (preg_match('/^(.+)_field$/', $prop, $matches))
                    $this->fieldmap[$matches[1]] = $this->_attr_name(strtolower($value));
        }

        // use fieldmap to advertise supported coltypes to the application
        foreach ($this->fieldmap as $col => $lf) {
            list($col, $type) = explode(':', $col);
            if (!is_array($this->coltypes[$col])) {
                $subtypes = $type ? array($type) : null;
                $this->coltypes[$col] = array('limit' => 2, 'subtypes' => $subtypes);
            }
            elseif ($type) {
                $this->coltypes[$col]['subtypes'][] = $type;
                $this->coltypes[$col]['limit']++;
            }
            if ($type && !$this->fieldmap[$col])
                $this->fieldmap[$col] = $lf;
        }

        if ($this->fieldmap['street'] && $this->fieldmap['locality'])
            $this->coltypes['address'] = array('limit' => 1);
        else if ($this->coltypes['address'])
            $this->coltypes['address'] = array('type' => 'textarea', 'childs' => null, 'limit' => 1, 'size' => 40);

        // make sure 'required_fields' is an array
        if (!is_array($this->prop['required_fields']))
            $this->prop['required_fields'] = (array) $this->prop['required_fields'];

        foreach ($this->prop['required_fields'] as $key => $val)
            $this->prop['required_fields'][$key] = $this->_attr_name(strtolower($val));

        $this->sort_col    = is_array($p['sort']) ? $p['sort'][0] : $p['sort'];
        $this->debug       = $debug;
        $this->mail_domain = $mail_domain;

        // initialize cache
        $rcmail = rcmail::get_instance();
        $this->cache = $rcmail->get_cache('LDAP.' . asciiwords($this->prop['name']), 'db', 600);
        $this->cache->expunge();

        $this->_connect();
    }


    /**
    * Establish a connection to the LDAP server
    */
    private function _connect()
    {
        global $RCMAIL;

        if (!function_exists('ldap_connect'))
            raise_error(array('code' => 100, 'type' => 'ldap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "No ldap support in this installation of PHP"),
                true, true);

        if (is_resource($this->conn))
            return true;

        if (!is_array($this->prop['hosts']))
            $this->prop['hosts'] = array($this->prop['hosts']);

        if (empty($this->prop['ldap_version']))
            $this->prop['ldap_version'] = 3;

        foreach ($this->prop['hosts'] as $host)
        {
            $host = idn_to_ascii(rcube_parse_host($host));
            $this->_debug("C: Connect [$host".($this->prop['port'] ? ':'.$this->prop['port'] : '')."]");

            if ($lc = @ldap_connect($host, $this->prop['port']))
            {
                if ($this->prop['use_tls']===true)
                    if (!ldap_start_tls($lc))
                        continue;

                $this->_debug("S: OK");

                ldap_set_option($lc, LDAP_OPT_PROTOCOL_VERSION, $this->prop['ldap_version']);
                $this->prop['host'] = $host;
                $this->conn = $lc;
                break;
            }
            $this->_debug("S: NOT OK");
        }

        if (is_resource($this->conn))
        {
            $this->ready = true;

            $bind_pass = $this->prop['bind_pass'];
            $bind_user = $this->prop['bind_user'];
            $bind_dn   = $this->prop['bind_dn'];

            $this->base_dn        = $this->prop['base_dn'];
            $this->groups_base_dn = ($this->prop['groups']['base_dn']) ?
                $this->prop['groups']['base_dn'] : $this->base_dn;

            // User specific access, generate the proper values to use.
            if ($this->prop['user_specific']) {
                // No password set, use the session password
                if (empty($bind_pass)) {
                    $bind_pass = $RCMAIL->decrypt($_SESSION['password']);
                }

                // Get the pieces needed for variable replacement.
                if ($fu = $RCMAIL->user->get_username())
                  list($u, $d) = explode('@', $fu);
                else
                  $d = $this->mail_domain;

                $dc = 'dc='.strtr($d, array('.' => ',dc=')); // hierarchal domain string

                $replaces = array('%dc' => $dc, '%d' => $d, '%fu' => $fu, '%u' => $u);

                if ($this->prop['search_base_dn'] && $this->prop['search_filter']) {
                    // Search for the dn to use to authenticate
                    $this->prop['search_base_dn'] = strtr($this->prop['search_base_dn'], $replaces);
                    $this->prop['search_filter'] = strtr($this->prop['search_filter'], $replaces);

                    $this->_debug("S: searching with base {$this->prop['search_base_dn']} for {$this->prop['search_filter']}");

                    $res = @ldap_search($this->conn, $this->prop['search_base_dn'], $this->prop['search_filter'], array('uid'));
                    if ($res && ($entry = ldap_first_entry($this->conn, $res))) {
                        $bind_dn = ldap_get_dn($this->conn, $entry);

                        $this->_debug("S: search returned dn: $bind_dn");

                        if ($bind_dn) {
                            $dn = ldap_explode_dn($bind_dn, 1);
                            $replaces['%dn'] = $dn[0];
                        }
                    }
                }
                // Replace the bind_dn and base_dn variables.
                $bind_dn              = strtr($bind_dn, $replaces);
                $this->base_dn        = strtr($this->base_dn, $replaces);
                $this->groups_base_dn = strtr($this->groups_base_dn, $replaces);

                if (empty($bind_user)) {
                    $bind_user = $u;
                }
            }

            if (!empty($bind_pass)) {
                if (!empty($bind_dn)) {
                    $this->ready = $this->bind($bind_dn, $bind_pass);
                }
                else if (!empty($this->prop['auth_cid'])) {
                    $this->ready = $this->sasl_bind($this->prop['auth_cid'], $bind_pass, $bind_user);
                }
                else {
                    $this->ready = $this->sasl_bind($bind_user, $bind_pass);
                }
            }
        }
        else
            raise_error(array('code' => 100, 'type' => 'ldap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Could not connect to any LDAP server, last tried $host:{$this->prop[port]}"), true);

        // See if the directory is writeable.
        if ($this->prop['writable']) {
            $this->readonly = false;
        } // end if
    }


    /**
     * Bind connection with (SASL-) user and password
     *
     * @param string $authc Authentication user
     * @param string $pass  Bind password
     * @param string $authz Autorization user
     *
     * @return boolean True on success, False on error
     */
    public function sasl_bind($authc, $pass, $authz=null)
    {
        if (!$this->conn) {
            return false;
        }

        if (!function_exists('ldap_sasl_bind')) {
            raise_error(array('code' => 100, 'type' => 'ldap',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Unable to bind: ldap_sasl_bind() not exists"),
                true, true);
        }

        if (!empty($authz)) {
            $authz = 'u:' . $authz;
        }

        if (!empty($this->prop['auth_method'])) {
            $method = $this->prop['auth_method'];
        }
        else {
            $method = 'DIGEST-MD5';
        }

        $this->_debug("C: Bind [mech: $method, authc: $authc, authz: $authz] [pass: $pass]");

        if (ldap_sasl_bind($this->conn, NULL, $pass, $method, NULL, $authc, $authz)) {
            $this->_debug("S: OK");
            return true;
        }

        $this->_debug("S: ".ldap_error($this->conn));

        raise_error(array(
            'code' => ldap_errno($this->conn), 'type' => 'ldap',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Bind failed for authcid=$authc ".ldap_error($this->conn)),
            true);

        return false;
    }


    /**
     * Bind connection with DN and password
     *
     * @param string Bind DN
     * @param string Bind password
     *
     * @return boolean True on success, False on error
     */
    public function bind($dn, $pass)
    {
        if (!$this->conn) {
            return false;
        }

        $this->_debug("C: Bind [dn: $dn] [pass: $pass]");

        if (@ldap_bind($this->conn, $dn, $pass)) {
            $this->_debug("S: OK");
            return true;
        }

        $this->_debug("S: ".ldap_error($this->conn));

        raise_error(array(
            'code' => ldap_errno($this->conn), 'type' => 'ldap',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Bind failed for dn=$dn: ".ldap_error($this->conn)),
            true);

        return false;
    }


    /**
     * Close connection to LDAP server
     */
    function close()
    {
        if ($this->conn)
        {
            $this->_debug("C: Close");
            ldap_unbind($this->conn);
            $this->conn = null;
        }
    }


    /**
     * Returns address book name
     *
     * @return string Address book name
     */
    function get_name()
    {
        return $this->prop['name'];
    }


    /**
     * Set internal list page
     *
     * @param number $page Page number to list
     */
    function set_page($page)
    {
        $this->list_page = (int)$page;
    }


    /**
     * Set internal page size
     *
     * @param number $size Number of messages to display on one page
     */
    function set_pagesize($size)
    {
        $this->page_size = (int)$size;
    }


    /**
     * Save a search string for future listings
     *
     * @param string $filter Filter string
     */
    function set_search_set($filter)
    {
        $this->filter = $filter;
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
     * Reset all saved results and search parameters
     */
    function reset()
    {
        $this->result = null;
        $this->ldap_result = null;
        $this->filter = '';
    }


    /**
     * List the current set of contact records
     *
     * @param  array  List of cols to show
     * @param  int    Only return this number of records
     *
     * @return array  Indexed list of contact records, each a hash array
     */
    function list_records($cols=null, $subset=0)
    {
        if ($this->prop['searchonly'] && empty($this->filter) && !$this->group_id)
        {
            $this->result = new rcube_result_set(0);
            $this->result->searchonly = true;
            return $this->result;
        }

        // add general filter to query
        if (!empty($this->prop['filter']) && empty($this->filter))
        {
            $filter = $this->prop['filter'];
            $this->set_search_set($filter);
        }

        // query URL is given by the selected group
        if ($this->group_id && $this->group_url)
        {
            // extract components from url
            if (preg_match('!ldap:///([^\?]+)\?\?(\w+)\?(.*)$!', $this->group_url, $m))
            {
                $this->base_dn = $m[1];
                $this->prop['scope'] = $m[2];
                $this->filter = $m[3];
            }
        }

        // exec LDAP search if no result resource is stored
        if ($this->conn && !$this->ldap_result)
            $this->_exec_search();

        // count contacts for this user
        $this->result = $this->count();

        // we have a search result resource
        if ($this->ldap_result && $this->result->count > 0)
        {
            // sorting still on the ldap server
            if ($this->sort_col && $this->prop['scope'] !== 'base' && !$this->vlv_active)
                ldap_sort($this->conn, $this->ldap_result, $this->sort_col);

            // start and end of the page
            $start_row = $this->vlv_active ? 0 : $this->result->first;
            $start_row = $subset < 0 ? $start_row + $this->page_size + $subset : $start_row;
            $last_row = $this->result->first + $this->page_size;
            $last_row = $subset != 0 ? $start_row + abs($subset) : $last_row;

            // get all entries from the ldap server
            $entries = ldap_get_entries($this->conn, $this->ldap_result);

            // filtering for group members
            if ($this->groups && $this->group_id && !$this->group_url)
            {
                $count = 0;
                $members = array();
                foreach ($entries as $entry)
                {
                    if ($this->group_members[self::dn_encode($entry['dn'])])
                    {
                        $members[] = $entry;
                        $count++;
                    }
                }
                $entries = $members;
                $entries['count'] = $count;
                $this->result->count = $count;
            }

            // filter entries for this page
            for ($i = $start_row; $i < min($entries['count'], $last_row); $i++)
                $this->result->add($this->_ldap2result($entries[$i]));
        }
        return $this->result;
    }


    /**
     * Search contacts
     *
     * @param mixed   $fields   The field name of array of field names to search in
     * @param mixed   $value    Search value (or array of values when $fields is array)
     * @param boolean $strict   True for strict, False for partial (fuzzy) matching
     * @param boolean $select   True if results are requested, False if count only
     * @param boolean $nocount  (Not used)
     * @param array   $required List of fields that cannot be empty
     *
     * @return array  Indexed list of contact records and 'count' value
     */
    function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array())
    {
        // special treatment for ID-based search
        if ($fields == 'ID' || $fields == $this->primary_key)
        {
            $ids = !is_array($value) ? explode(',', $value) : $value;
            $result = new rcube_result_set();
            foreach ($ids as $id)
            {
                if ($rec = $this->get_record($id, true))
                {
                    $result->add($rec);
                    $result->count++;
                }
            }
            return $result;
        }

        // use AND operator for advanced searches
        $filter = is_array($value) ? '(&' : '(|';
        $wc     = !$strict && $this->prop['fuzzy_search'] ? '*' : '';

        if ($fields == '*')
        {
            // search_fields are required for fulltext search
            if (empty($this->prop['search_fields']))
            {
                $this->set_error(self::ERROR_SEARCH, 'nofulltextsearch');
                $this->result = new rcube_result_set();
                return $this->result;
            }
            if (is_array($this->prop['search_fields']))
            {
                foreach ($this->prop['search_fields'] as $field) {
                    $filter .= "($field=$wc" . $this->_quote_string($value) . "$wc)";
                }
            }
        }
        else
        {
            foreach ((array)$fields as $idx => $field) {
                $val = is_array($value) ? $value[$idx] : $value;
                if ($f = $this->_map_field($field)) {
                    $filter .= "($f=$wc" . $this->_quote_string($val) . "$wc)";
                }
            }
        }
        $filter .= ')';

        // add required (non empty) fields filter
        $req_filter = '';
        foreach ((array)$required as $field)
            if ($f = $this->_map_field($field))
                $req_filter .= "($f=*)";

        if (!empty($req_filter))
            $filter = '(&' . $req_filter . $filter . ')';

        // avoid double-wildcard if $value is empty
        $filter = preg_replace('/\*+/', '*', $filter);

        // add general filter to query
        if (!empty($this->prop['filter']))
            $filter = '(&(' . preg_replace('/^\(|\)$/', '', $this->prop['filter']) . ')' . $filter . ')';

        // set filter string and execute search
        $this->set_search_set($filter);
        $this->_exec_search();

        if ($select)
            $this->list_records();
        else
            $this->result = $this->count();

        return $this->result;
    }


    /**
     * Count number of available contacts in database
     *
     * @return object rcube_result_set Resultset with values for 'count' and 'first'
     */
    function count()
    {
        $count = 0;
        if ($this->conn && $this->ldap_result) {
            $count = $this->vlv_active ? $this->vlv_count : ldap_count_entries($this->conn, $this->ldap_result);
        } // end if
        elseif ($this->conn) {
            // We have a connection but no result set, attempt to get one.
            if (empty($this->filter)) {
                // The filter is not set, set it.
                $this->filter = $this->prop['filter'];
            } // end if
            $this->_exec_search(true);
            if ($this->ldap_result) {
                $count = ldap_count_entries($this->conn, $this->ldap_result);
            } // end if
        } // end else

        return new rcube_result_set($count, ($this->list_page-1) * $this->page_size);
    }


    /**
     * Return the last result set
     *
     * @return object rcube_result_set Current resultset or NULL if nothing selected yet
     */
    function get_result()
    {
        return $this->result;
    }


    /**
     * Get a specific contact record
     *
     * @param mixed   Record identifier
     * @param boolean Return as associative array
     *
     * @return mixed  Hash array or rcube_result_set with all record fields
     */
    function get_record($dn, $assoc=false)
    {
        $res = null;
        if ($this->conn && $dn)
        {
            $dn = self::dn_decode($dn);

            $this->_debug("C: Read [dn: $dn] [(objectclass=*)]");

            if ($this->ldap_result = @ldap_read($this->conn, $dn, '(objectclass=*)', array_values($this->fieldmap)))
                $entry = ldap_first_entry($this->conn, $this->ldap_result);
            else
                $this->_debug("S: ".ldap_error($this->conn));

            if ($entry && ($rec = ldap_get_attributes($this->conn, $entry)))
            {
                $this->_debug("S: OK"/* . print_r($rec, true)*/);

                $rec = array_change_key_case($rec, CASE_LOWER);

                // Add in the dn for the entry.
                $rec['dn'] = $dn;
                $res = $this->_ldap2result($rec);
                $this->result = new rcube_result_set(1);
                $this->result->add($res);
            }
        }

        return $assoc ? $res : $this->result;
    }


    /**
     * Check the given data before saving.
     * If input not valid, the message to display can be fetched using get_error()
     *
     * @param array Assoziative array with data to save
     *
     * @return boolean True if input is valid, False if not.
     */
    public function validate($save_data)
    {
        // check for name input
        if (empty($save_data['name'])) {
            $this->set_error('warning', 'nonamewarning');
            return false;
        }

        // validate e-mail addresses
        return parent::validate($save_data);
    }


    /**
     * Create a new contact record
     *
     * @param array    Hash array with save data
     *
     * @return encoded record ID on success, False on error
     */
    function insert($save_cols)
    {
        // Map out the column names to their LDAP ones to build the new entry.
        $newentry = array();
        $newentry['objectClass'] = $this->prop['LDAP_Object_Classes'];
        foreach ($this->fieldmap as $col => $fld) {
            $val = $save_cols[$col];
            if (is_array($val))
                $val = array_filter($val);  // remove empty entries
            if ($fld && $val) {
                // The field does exist, add it to the entry.
                $newentry[$fld] = $val;
            } // end if
        } // end foreach

        // Verify that the required fields are set.
        $missing = null;
        foreach ($this->prop['required_fields'] as $fld) {
            if (!isset($newentry[$fld])) {
                $missing[] = $fld;
            }
        }

        // abort process if requiered fields are missing
        // TODO: generate message saying which fields are missing
        if ($missing) {
            $this->set_error(self::ERROR_INCOMPLETE, 'formincomplete');
            return false;
        }

        // Build the new entries DN.
        $dn = $this->prop['LDAP_rdn'].'='.$this->_quote_string($newentry[$this->prop['LDAP_rdn']], true).','.$this->base_dn;

        $this->_debug("C: Add [dn: $dn]: ".print_r($newentry, true));

        $res = ldap_add($this->conn, $dn, $newentry);
        if ($res === FALSE) {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return false;
        } // end if

        $this->_debug("S: OK");

        $dn = self::dn_encode($dn);

        // add new contact to the selected group
        if ($this->groups)
            $this->add_to_group($this->group_id, $dn);

        return $dn;
    }


    /**
     * Update a specific contact record
     *
     * @param mixed Record identifier
     * @param array Hash array with save data
     *
     * @return boolean True on success, False on error
     */
    function update($id, $save_cols)
    {
        $record = $this->get_record($id, true);
        $result = $this->get_result();
        $record = $result->first();

        $newdata = array();
        $replacedata = array();
        $deletedata = array();

        // flatten composite fields in $record
        if (is_array($record['address'])) {
          foreach ($record['address'] as $i => $struct) {
            foreach ($struct as $col => $val) {
              $record[$col][$i] = $val;
            }
          }
        }

        foreach ($this->fieldmap as $col => $fld) {
            $val = $save_cols[$col];
            if ($fld) {
                // remove empty array values
                if (is_array($val))
                    $val = array_filter($val);
                // The field does exist compare it to the ldap record.
                if ($record[$col] != $val) {
                    // Changed, but find out how.
                    if (!isset($record[$col])) {
                        // Field was not set prior, need to add it.
                        $newdata[$fld] = $val;
                    } // end if
                    elseif ($val == '') {
                        // Field supplied is empty, verify that it is not required.
                        if (!in_array($fld, $this->prop['required_fields'])) {
                            // It is not, safe to clear.
                            $deletedata[$fld] = $record[$col];
                        } // end if
                    } // end elseif
                    else {
                        // The data was modified, save it out.
                        $replacedata[$fld] = $val;
                    } // end else
                } // end if
            } // end if
        } // end foreach

        $dn = self::dn_decode($id);

        // Update the entry as required.
        if (!empty($deletedata)) {
            // Delete the fields.
            $this->_debug("C: Delete [dn: $dn]: ".print_r($deletedata, true));
            if (!ldap_mod_del($this->conn, $dn, $deletedata)) {
                $this->_debug("S: ".ldap_error($this->conn));
                $this->set_error(self::ERROR_SAVING, 'errorsaving');
                return false;
            }
            $this->_debug("S: OK");
        } // end if

        if (!empty($replacedata)) {
            // Handle RDN change
            if ($replacedata[$this->prop['LDAP_rdn']]) {
                $newdn = $this->prop['LDAP_rdn'].'='
                    .$this->_quote_string($replacedata[$this->prop['LDAP_rdn']], true)
                    .','.$this->base_dn;
                if ($dn != $newdn) {
                    $newrdn = $this->prop['LDAP_rdn'].'='
                    .$this->_quote_string($replacedata[$this->prop['LDAP_rdn']], true);
                    unset($replacedata[$this->prop['LDAP_rdn']]);
                }
            }
            // Replace the fields.
            if (!empty($replacedata)) {
                $this->_debug("C: Replace [dn: $dn]: ".print_r($replacedata, true));
                if (!ldap_mod_replace($this->conn, $dn, $replacedata)) {
                    $this->_debug("S: ".ldap_error($this->conn));
                    return false;
                }
                $this->_debug("S: OK");
            } // end if
        } // end if

        if (!empty($newdata)) {
            // Add the fields.
            $this->_debug("C: Add [dn: $dn]: ".print_r($newdata, true));
            if (!ldap_mod_add($this->conn, $dn, $newdata)) {
                $this->_debug("S: ".ldap_error($this->conn));
                $this->set_error(self::ERROR_SAVING, 'errorsaving');
                return false;
            }
            $this->_debug("S: OK");
        } // end if

        // Handle RDN change
        if (!empty($newrdn)) {
            $this->_debug("C: Rename [dn: $dn] [dn: $newrdn]");
            if (!ldap_rename($this->conn, $dn, $newrdn, NULL, TRUE)) {
                $this->_debug("S: ".ldap_error($this->conn));
                return false;
            }
            $this->_debug("S: OK");

            $dn    = self::dn_encode($dn);
            $newdn = self::dn_encode($newdn);

            // change the group membership of the contact
            if ($this->groups)
            {
                $group_ids = $this->get_record_groups($dn);
                foreach ($group_ids as $group_id)
                {
                    $this->remove_from_group($group_id, $dn);
                    $this->add_to_group($group_id, $newdn);
                }
            }

            return $newdn;
        }

        return true;
    }


    /**
     * Mark one or more contact records as deleted
     *
     * @param array   Record identifiers
     * @param boolean Remove record(s) irreversible (unsupported)
     *
     * @return boolean True on success, False on error
     */
    function delete($ids, $force=true)
    {
        if (!is_array($ids)) {
            // Not an array, break apart the encoded DNs.
            $ids = explode(',', $ids);
        } // end if

        foreach ($ids as $id) {
            $dn = self::dn_decode($id);
            $this->_debug("C: Delete [dn: $dn]");
            // Delete the record.
            $res = ldap_delete($this->conn, $dn);
            if ($res === FALSE) {
                $this->_debug("S: ".ldap_error($this->conn));
                $this->set_error(self::ERROR_SAVING, 'errorsaving');
                return false;
            } // end if
            $this->_debug("S: OK");

            // remove contact from all groups where he was member
            if ($this->groups) {
                $dn = self::dn_encode($dn);
                $group_ids = $this->get_record_groups($dn);
                foreach ($group_ids as $group_id) {
                    $this->remove_from_group($group_id, $dn);
                }
            }
        } // end foreach

        return count($ids);
    }


    /**
     * Execute the LDAP search based on the stored credentials
     */
    private function _exec_search($count = false)
    {
        if ($this->ready)
        {
            $filter = $this->filter ? $this->filter : '(objectclass=*)';
            $function = $this->prop['scope'] == 'sub' ? 'ldap_search' : ($this->prop['scope'] == 'base' ? 'ldap_read' : 'ldap_list');

            $this->_debug("C: Search [$filter]");

            // when using VLV, we get the total count by...
            if (!$count && $function != 'ldap_read' && $this->prop['vlv'] && !$this->group_id) {
                // ...either reading numSubOrdinates attribute
                if ($this->prop['numsub_filter'] && ($result_count = @$function($this->conn, $this->base_dn, $this->prop['numsub_filter'], array('numSubOrdinates'), 0, 0, 0))) {
                    $counts = ldap_get_entries($this->conn, $result_count);
                    for ($this->vlv_count = $j = 0; $j < $counts['count']; $j++)
                        $this->vlv_count += $counts[$j]['numsubordinates'][0];
                    $this->_debug("D: total numsubordinates = " . $this->vlv_count);
                }
                else  // ...or by fetching all records dn and count them
                    $this->vlv_count = $this->_exec_search(true);

                $this->vlv_active = $this->_vlv_set_controls();
            }

            // only fetch dn for count (should keep the payload low)
            $attrs = $count ? array('dn') : array_values($this->fieldmap);
            if ($this->ldap_result = @$function($this->conn, $this->base_dn, $filter,
                $attrs, 0, (int)$this->prop['sizelimit'], (int)$this->prop['timelimit']))
            {
                $this->_debug("S: ".ldap_count_entries($this->conn, $this->ldap_result)." record(s)");
                if ($err = ldap_errno($this->conn))
                    $this->_debug("S: Error: " .ldap_err2str($err));
                return true;
            }
            else
            {
                $this->_debug("S: ".ldap_error($this->conn));
            }
        }

        return false;
    }

    /**
     * Set server controls for Virtual List View (paginated listing)
     */
    private function _vlv_set_controls()
    {
        $sort_ctrl = array('oid' => "1.2.840.113556.1.4.473",  'value' => $this->_sort_ber_encode((array)$this->prop['sort']));
        $vlv_ctrl  = array('oid' => "2.16.840.1.113730.3.4.9", 'value' => $this->_vlv_ber_encode(($offset = ($this->list_page-1) * $this->page_size + 1), $this->page_size), 'iscritical' => true);

        $this->_debug("C: set controls sort=" . join(' ', unpack('H'.(strlen($sort_ctrl['value'])*2), $sort_ctrl['value'])) . " ({$this->sort_col});"
            . " vlv=" . join(' ', (unpack('H'.(strlen($vlv_ctrl['value'])*2), $vlv_ctrl['value']))) . " ($offset)");

        if (!ldap_set_option($this->conn, LDAP_OPT_SERVER_CONTROLS, array($sort_ctrl, $vlv_ctrl))) {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SEARCH, 'vlvnotsupported');
            return false;
        }

        return true;
    }


    /**
     * Converts LDAP entry into an array
     */
    private function _ldap2result($rec)
    {
        $out = array();

        if ($rec['dn'])
            $out[$this->primary_key] = self::dn_encode($rec['dn']);

        foreach ($this->fieldmap as $rf => $lf)
        {
            for ($i=0; $i < $rec[$lf]['count']; $i++) {
                if (!($value = $rec[$lf][$i]))
                    continue;
                if ($rf == 'email' && $this->mail_domain && !strpos($value, '@'))
                    $out[$rf][] = sprintf('%s@%s', $value, $this->mail_domain);
                else if (in_array($rf, array('street','zipcode','locality','country','region')))
                    $out['address'][$i][$rf] = $value;
                else if ($rec[$lf]['count'] > 1)
                    $out[$rf][] = $value;
                else
                    $out[$rf] = $value;
            }

            // Make sure name fields aren't arrays (#1488108)
            if (is_array($out[$rf]) && in_array($rf, array('name', 'surname', 'firstname', 'middlename', 'nickname'))) {
                $out[$rf] = $out[$rf][0];
            }
        }

        return $out;
    }


    /**
     * Return real field name (from fields map)
     */
    private function _map_field($field)
    {
        return $this->fieldmap[$field];
    }


    /**
     * Returns unified attribute name (resolving aliases)
     */
    private static function _attr_name($name)
    {
        // list of known attribute aliases
        $aliases = array(
            'gn' => 'givenname',
            'rfc822mailbox' => 'email',
            'userid' => 'uid',
            'emailaddress' => 'email',
            'pkcs9email' => 'email',
        );
        return isset($aliases[$name]) ? $aliases[$name] : $name;
    }


    /**
     * Prints debug info to the log
     */
    private function _debug($str)
    {
        if ($this->debug)
            write_log('ldap', $str);
    }


    /**
     * Quotes attribute value string
     *
     * @param string $str Attribute value
     * @param bool   $dn  True if the attribute is a DN
     *
     * @return string Quoted string
     */
    private static function _quote_string($str, $dn=false)
    {
        // take firt entry if array given
        if (is_array($str))
            $str = reset($str);

        if ($dn)
            $replace = array(','=>'\2c', '='=>'\3d', '+'=>'\2b', '<'=>'\3c',
                '>'=>'\3e', ';'=>'\3b', '\\'=>'\5c', '"'=>'\22', '#'=>'\23');
        else
            $replace = array('*'=>'\2a', '('=>'\28', ')'=>'\29', '\\'=>'\5c',
                '/'=>'\2f');

        return strtr($str, $replace);
    }


    /**
     * Setter for the current group
     * (empty, has to be re-implemented by extending class)
     */
    function set_group($group_id)
    {
        if ($group_id)
        {
            if (!($group_cache = $this->cache->get('groups')))
                $group_cache = $this->list_groups();

            $cache_members = $group_cache[$group_id]['members'];

            $members = array();
            for ($i=0; $i<$cache_members["count"]; $i++)
            {
                if (!empty($cache_members[$i]))
                    $members[self::dn_encode($cache_members[$i])] = 1;
            }
            $this->group_members = $members;
            $this->group_url = $group_cache[$group_id]['member_url'];
            $this->group_id = $group_id;
        }
        else
        {
            $this->group_id = 0;
            $this->group_url = null;
            $this->group_members = array();
        }
    }

    /**
     * List all active contact groups of this source
     *
     * @param string  Optional search string to match group name
     * @return array  Indexed list of contact groups, each a hash array
     */
    function list_groups($search = null)
    {
        if (!$this->groups)
            return array();

        $base_dn = $this->groups_base_dn;
        $filter = $this->prop['groups']['filter'];
        $name_attr = $this->prop['groups']['name_attr'];

        $this->_debug("C: Search [$filter][dn: $base_dn]");

        $res = @ldap_search($this->conn, $base_dn, $filter);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            return array();
        }

        $ldap_data = ldap_get_entries($this->conn, $res);
        $this->_debug("S: ".ldap_count_entries($this->conn, $res)." record(s)");

        $groups = array();
        $group_sortnames = array();
        for ($i=0; $i<$ldap_data["count"]; $i++)
        {
            $group_name = $ldap_data[$i][$name_attr][0];
            if (!$search || strstr(strtolower($group_name), strtolower($search)))
            {
                $group_id = self::dn_encode($group_name);
                $groups[$group_id]['ID'] = $group_id;
                $groups[$group_id]['name'] = $group_name;

                // default behavior: check members attribute
                if ($this->prop['member_attr']) {
                    $groups[$group_id]['members'] = $ldap_data[$i][$this->prop['member_attr']];
                    $groups[$group_id]['member_attr'] = $this->prop['member_attr'];
                }

                // check objectClass attributes of group and act accordingly
                for ($j=0; $j < $ldap_data[$i]['objectclass']['count']; $j++) {
                    switch (strtolower($ldap_data[$i]['objectclass'][$j])) {
                        case 'groupofnames':
                            $groups[$group_id]['members'] = $ldap_data[$i]['members'];
                            $groups[$group_id]['member_attr'] = 'member';
                            break;

                        case 'groupofuniquenames':
                            $groups[$group_id]['members'] = $ldap_data[$i]['uniqueMember'];
                            $groups[$group_id]['member_attr'] = 'uniqueMember';
                            break;

                        case 'groupofurls':
                            $groups[$group_id]['member_url'] = $ldap_data[$i]['memberurl'][0];
                            break;
                    }
                }

                $group_sortnames[] = strtolower($group_name);
            }
        }
        array_multisort($group_sortnames, SORT_ASC, SORT_STRING, $groups);

        // cache this
        $this->cache->set('groups', $groups);

        return $groups;
    }

    /**
     * Create a contact group with the given name
     *
     * @param string The group name
     * @return mixed False on error, array with record props in success
     */
    function create_group($group_name)
    {
        $base_dn = $this->groups_base_dn;
        $new_dn = "cn=$group_name,$base_dn";
        $new_gid = self::dn_encode($group_name);
        $name_attr = $this->prop['groups']['name_attr'];

        $new_entry = array(
            'objectClass' => $this->prop['groups']['object_classes'],
            $name_attr => $group_name,
        );

        $this->_debug("C: Add [dn: $new_dn]: ".print_r($new_entry, true));

        $res = ldap_add($this->conn, $new_dn, $new_entry);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return false;
        }

        $this->_debug("S: OK");
        $this->cache->remove('groups');

        return array('id' => $new_gid, 'name' => $group_name);
    }

    /**
     * Delete the given group and all linked group members
     *
     * @param string Group identifier
     * @return boolean True on success, false if no data was changed
     */
    function delete_group($group_id)
    {
        if (!($group_cache = $this->cache->get('groups')))
            $group_cache = $this->list_groups();

        $base_dn = $this->groups_base_dn;
        $group_name = $group_cache[$group_id]['name'];
        $del_dn = "cn=$group_name,$base_dn";

        $this->_debug("C: Delete [dn: $del_dn]");

        $res = ldap_delete($this->conn, $del_dn);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return false;
        }

        $this->_debug("S: OK");
        $this->cache->remove('groups');

        return true;
    }

    /**
     * Rename a specific contact group
     *
     * @param string Group identifier
     * @param string New name to set for this group
     * @param string New group identifier (if changed, otherwise don't set)
     * @return boolean New name on success, false if no data was changed
     */
    function rename_group($group_id, $new_name, &$new_gid)
    {
        if (!($group_cache = $this->cache->get('groups')))
            $group_cache = $this->list_groups();

        $base_dn = $this->groups_base_dn;
        $group_name = $group_cache[$group_id]['name'];
        $old_dn = "cn=$group_name,$base_dn";
        $new_rdn = "cn=$new_name";
        $new_gid = self::dn_encode($new_name);

        $this->_debug("C: Rename [dn: $old_dn] [dn: $new_rdn]");

        $res = ldap_rename($this->conn, $old_dn, $new_rdn, NULL, TRUE);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return false;
        }

        $this->_debug("S: OK");
        $this->cache->remove('groups');

        return $new_name;
    }

    /**
     * Add the given contact records the a certain group
     *
     * @param string  Group identifier
     * @param array   List of contact identifiers to be added
     * @return int    Number of contacts added
     */
    function add_to_group($group_id, $contact_ids)
    {
        if (!($group_cache = $this->cache->get('groups')))
            $group_cache = $this->list_groups();

        $base_dn     = $this->groups_base_dn;
        $group_name  = $group_cache[$group_id]['name'];
        $member_attr = $group_cache[$group_id]['member_attr'];
        $group_dn    = "cn=$group_name,$base_dn";

        $new_attrs = array();
        foreach (explode(",", $contact_ids) as $id)
            $new_attrs[$member_attr][] = self::dn_decode($id);

        $this->_debug("C: Add [dn: $group_dn]: ".print_r($new_attrs, true));

        $res = ldap_mod_add($this->conn, $group_dn, $new_attrs);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return 0;
        }

        $this->_debug("S: OK");
        $this->cache->remove('groups');

        return count($new_attrs['member']);
    }

    /**
     * Remove the given contact records from a certain group
     *
     * @param string  Group identifier
     * @param array   List of contact identifiers to be removed
     * @return int    Number of deleted group members
     */
    function remove_from_group($group_id, $contact_ids)
    {
        if (!($group_cache = $this->cache->get('groups')))
            $group_cache = $this->list_groups();

        $base_dn     = $this->groups_base_dn;
        $group_name  = $group_cache[$group_id]['name'];
        $member_attr = $group_cache[$group_id]['member_attr'];
        $group_dn    = "cn=$group_name,$base_dn";

        $del_attrs = array();
        foreach (explode(",", $contact_ids) as $id)
            $del_attrs[$member_attr][] = self::dn_decode($id);

        $this->_debug("C: Delete [dn: $group_dn]: ".print_r($del_attrs, true));

        $res = ldap_mod_del($this->conn, $group_dn, $del_attrs);
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            $this->set_error(self::ERROR_SAVING, 'errorsaving');
            return 0;
        }

        $this->_debug("S: OK");
        $this->cache->remove('groups');

        return count($del_attrs['member']);
    }

    /**
     * Get group assignments of a specific contact record
     *
     * @param mixed Record identifier
     *
     * @return array List of assigned groups as ID=>Name pairs
     * @since 0.5-beta
     */
    function get_record_groups($contact_id)
    {
        if (!$this->groups)
            return array();

        $base_dn     = $this->groups_base_dn;
        $contact_dn  = self::dn_decode($contact_id);
        $name_attr   = $this->prop['groups']['name_attr'];
        $member_attr = $this->prop['member_attr'];
        $add_filter  = '';
        if ($member_attr != 'member' && $member_attr != 'uniqueMember')
            $add_filter = "($member_attr=$contact_dn)";
        $filter = strtr("(|(member=$contact_dn)(uniqueMember=$contact_dn)$add_filter)", array('\\' => '\\\\'));

        $this->_debug("C: Search [$filter][dn: $base_dn]");

        $res = @ldap_search($this->conn, $base_dn, $filter, array($name_attr));
        if ($res === false)
        {
            $this->_debug("S: ".ldap_error($this->conn));
            return array();
        }
        $ldap_data = ldap_get_entries($this->conn, $res);
        $this->_debug("S: ".ldap_count_entries($this->conn, $res)." record(s)");

        $groups = array();
        for ($i=0; $i<$ldap_data["count"]; $i++)
        {
            $group_name = $ldap_data[$i][$name_attr][0];
            $group_id = self::dn_encode($group_name);
            $groups[$group_id] = $group_id;
        }
        return $groups;
    }


    /**
     * Generate BER encoded string for Virtual List View option
     *
     * @param integer List offset (first record)
     * @param integer Records per page
     * @return string BER encoded option value
     */
    private function _vlv_ber_encode($offset, $rpp)
    {
        # this string is ber-encoded, php will prefix this value with:
        # 04 (octet string) and 10 (length of 16 bytes)
        # the code behind this string is broken down as follows:
        # 30 = ber sequence with a length of 0e (14) bytes following
        # 20 = type integer (in two's complement form) with 2 bytes following (beforeCount): 01 00 (ie 0)
        # 20 = type integer (in two's complement form) with 2 bytes following (afterCount):  01 18 (ie 25-1=24)
        # a0 = type context-specific/constructed with a length of 06 (6) bytes following
        # 20 = type integer with 2 bytes following (offset): 01 01 (ie 1)
        # 20 = type integer with 2 bytes following (contentCount):  01 00
        # the following info was taken from the ISO/IEC 8825-1:2003 x.690 standard re: the
        # encoding of integer values (note: these values are in
        # two-complement form so since offset will never be negative bit 8 of the
        # leftmost octet should never by set to 1):
        # 8.3.2: If the contents octets of an integer value encoding consist
        # of more than one octet, then the bits of the first octet (rightmost) and bit 8
        # of the second (to the left of first octet) octet:
        # a) shall not all be ones; and
        # b) shall not all be zero

        # construct the string from right to left
        $str = "020100"; # contentCount

        $ber_val = self::_ber_encode_int($offset);  // returns encoded integer value in hex format

        // calculate octet length of $ber_val
        $str = self::_ber_addseq($ber_val, '02') . $str;

        // now compute length over $str
        $str = self::_ber_addseq($str, 'a0');

        // now tack on records per page
        $str = sprintf("0201000201%02x", min(255, $rpp)-1) . $str;

        // now tack on sequence identifier and length
        $str = self::_ber_addseq($str, '30');

        return pack('H'.strlen($str), $str);
    }


    /**
     * create ber encoding for sort control
     *
     * @param array List of cols to sort by
     * @return string BER encoded option value
     */
    private function _sort_ber_encode($sortcols)
    {
        $str = '';
        foreach (array_reverse((array)$sortcols) as $col) {
            $ber_val = self::_string2hex($col);

            # 30 = ber sequence with a length of octet value
            # 04 = octet string with a length of the ascii value
            $oct = self::_ber_addseq($ber_val, '04');
            $str = self::_ber_addseq($oct, '30') . $str;
        }

        // now tack on sequence identifier and length
        $str = self::_ber_addseq($str, '30');

        return pack('H'.strlen($str), $str);
    }

    /**
     * Add BER sequence with correct length and the given identifier
     */
    private static function _ber_addseq($str, $identifier)
    {
        $len = dechex(strlen($str)/2);
        if (strlen($len) % 2 != 0)
            $len = '0'.$len;

        return $identifier . $len . $str;
    }

    /**
     * Returns BER encoded integer value in hex format
     */
    private static function _ber_encode_int($offset)
    {
        $val = dechex($offset);
        $prefix = '';

        // check if bit 8 of high byte is 1
        if (preg_match('/^[89abcdef]/', $val))
            $prefix = '00';

        if (strlen($val)%2 != 0)
            $prefix .= '0';

        return $prefix . $val;
    }

    /**
     * Returns ascii string encoded in hex
     */
    private static function _string2hex($str)
    {
        $hex = '';
        for ($i=0; $i < strlen($str); $i++)
            $hex .= dechex(ord($str[$i]));
        return $hex;
    }

    /**
     * HTML-safe DN string encoding
     *
     * @param string $str DN string
     *
     * @return string Encoded HTML identifier string
     */
    static function dn_encode($str)
    {
        // @TODO: to make output string shorter we could probably
        //        remove dc=* items from it
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    /**
     * Decodes DN string encoded with _dn_encode()
     *
     * @param string $str Encoded HTML identifier string
     *
     * @return string DN string
     */
    static function dn_decode($str)
    {
        $str = str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($str);
    }
}
