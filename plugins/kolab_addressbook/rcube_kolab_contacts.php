<?php


/**
 * Backend class for a custom address book
 *
 * This part of the Roundcube+Kolab integration and connects the
 * rcube_addressbook interface with the rcube_kolab wrapper for Kolab_Storage
 *
 * @author Thomas Bruederli
 * @see rcube_addressbook
 */
class rcube_kolab_contacts extends rcube_addressbook
{
    public $primary_key = 'ID';
    public $readonly = false;
    public $groups = true;
    public $coltypes = array(
      'name'         => array('limit' => 1),
      'firstname'    => array('limit' => 1),
      'surname'      => array('limit' => 1),
      'middlename'   => array('limit' => 1),
      'prefix'       => array('limit' => 1),
      'suffix'       => array('limit' => 1),
      'nickname'     => array('limit' => 1),
      'jobtitle'     => array('limit' => 1),
      'organization' => array('limit' => 1),
      'department'   => array('limit' => 1),
      'gender'       => array('limit' => 1),
      'initials'     => array('type' => 'text', 'size' => 6, 'limit' => 1, 'label' => 'kolab_addressbook.initials'),
      'email'        => array('subtypes' => null),
      'phone'        => array(),
      'im'           => array('limit' => 1),
      'website'      => array('limit' => 1, 'subtypes' => null),
      'address'      => array('limit' => 2, 'subtypes' => array('home','business')),
      'birthday'     => array('limit' => 1),
      'anniversary'  => array('type' => 'date', 'size' => 12, 'limit' => 1, 'label' => 'kolab_addressbook.anniversary'),
      // TODO: define more Kolab-specific fields such as: office-location, profession, manager-name, assistant, spouse-name, children, language, latitude, longitude, pgp-publickey, free-busy-url
      'notes'        => array(),
    );
    
    private $gid;
    private $imap;
    private $kolab;
    private $folder;
    private $contactstorage;
    private $liststorage;
    private $contacts;
    private $distlists;
    private $groupmembers;
    private $id2uid;
    private $filter;
    private $result;
    private $imap_folder = 'INBOX/Contacts';
    private $gender_map = array(0 => 'male', 1 => 'female');
    private $phonetypemap = array('home' => 'home1', 'work' => 'business1', 'work2' => 'business2', 'workfax' => 'businessfax');
    private $addresstypemap = array('work' => 'business');
    private $fieldmap = array(
      // kolab       => roundcube
      'full-name'    => 'name',
      'given-name'   => 'firstname',
      'middle-names' => 'middlename',
      'last-name'    => 'surname',
      'prefix'       => 'prefix',
      'suffix'       => 'suffix',
      'nick-name'    => 'nickname',
      'organization' => 'organization',
      'department'   => 'department',
      'job-title'    => 'jobtitle',
      'initials'     => 'initials',
      'birthday'     => 'birthday',
      'anniversary'  => 'anniversary',
      'im-address'   => 'im:aim',
      'web-page'     => 'website',
      'body'         => 'notes',
    );


    public function __construct($imap_folder = null)
    {
        if ($imap_folder)
            $this->imap_folder = $imap_folder;
            
        // extend coltypes configuration 
        $format = rcube_kolab::get_format('contact');
        $this->coltypes['phone']['subtypes'] = $format->_phone_types;
        $this->coltypes['address']['subtypes'] = $format->_address_types;
        
        // set localized labels for proprietary cols
        foreach ($this->coltypes as $col => $prop) {
            if (is_string($prop['label']))
                $this->coltypes[$col]['label'] = rcube_label($prop['label']);
        }
        
        // fetch objects from the given IMAP folder
        $this->contactstorage = rcube_kolab::get_storage($this->imap_folder);
        $this->liststorage = rcube_kolab::get_storage($this->imap_folder, 'distributionlist');

        $this->ready = !PEAR::isError($this->contactstorage) && !PEAR::isError($this->liststorage);
    }


    /**
     * Getter for the address book name to be displayed
     *
     * @return string Name of this address book
     */
    public function get_name()
    {
        return strtr(preg_replace('!^(INBOX|user)/!i', '', $this->imap_folder), '/', ':');
    }


    /**
     * Setter for the current group
     */
    public function set_group($gid)
    {
        $this->gid = $gid;
    }


    /**
     * Save a search string for future listings
     *
     * @param mixed Search params to use in listing method, obtained by get_search_set()
     */
    public function set_search_set($filter)
    {
        $this->filter = $filter;
    }


    /**
     * Getter for saved search properties
     *
     * @return mixed Search properties used by this class
     */
    public function get_search_set()
    {
        return $this->filter;
    }


    /**
     * Reset saved results and search parameters
     */
    public function reset()
    {
        $this->result = null;
        $this->filter = null;
    }


    /**
     * List all active contact groups of this source
     *
     * @param string  Optional search string to match group name
     * @return array  Indexed list of contact groups, each a hash array
     */
    function list_groups($search = null)
    {
        $this->_fetch_groups();
        $groups = array();
        foreach ((array)$this->distlists as $group) {
            if (!$search || strstr(strtolower($group['last-name']), strtolower($search)))
                $groups[] = array('ID' => $group['ID'], 'name' => $group['last-name']);
        }
        return $groups;
    }

    /**
     * List the current set of contact records
     *
     * @param  array  List of cols to show
     * @param  int    Only return this number of records, use negative values for tail
     * @return array  Indexed list of contact records, each a hash array
     */
    public function list_records($cols=null, $subset=0)
    {
        $this->result = $this->count();
        
        // list member of the selected group
        if ($this->gid) {
            $seen = array();
            $this->result->count = 0;
            foreach ((array)$this->distlists[$this->gid]['member'] as $member) {
                // skip member that don't match the search filter
                if ($this->filter && array_search($member['ID'], $this->filter) === false)
                    continue;
                if ($this->contacts[$member['ID']] && !$seen[$member['ID']]++)
                    $this->result->count++;
            }
            $ids = array_keys($seen);
        }
        else
            $ids = $this->filter ? $this->filter : array_keys($this->contacts);
        
        // fill contact data into the current result set
        $i = $j = 0;
        foreach ($ids as $id) {
            if ($i++ < $this->result->first)
                continue;
            $this->result->add($this->contacts[$id]);
            if (++$j == $this->page_size)
                break;
        }
        
        return $this->result;
    }


    /**
     * Search records
     *
     * @param array   List of fields to search in
     * @param string  Search value
     * @param boolean True if results are requested, False if count only
     * @param boolean True to skip the count query (select only)
     * @param array   List of fields that cannot be empty
     * @return object rcube_result_set List of contact records and 'count' value
     */
    public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array())
    {
        $this->_fetch_contacts();
        
        // search by ID
        if ($fields == $this->primary_key) {
            return $this->get_record($value);
        }

        $value = strtolower($value);
        if (!is_array($fields))
            $fields = array($fields);
        if (!is_array($required) && !empty($required))
            $required = array($required);
        
        $this->filter = array();
        
        // search be iterating over all records in memory
        foreach ($this->contacts as $id => $contact) {
            // check if current contact has required values, otherwise skip it
            if ($required) {
                foreach ($required as $f)
                    if (empty($contact[$f]))
                        continue 2;
            }
            foreach ($fields as $f) {
                foreach ((array)$contact[$f] as $val) {
                    $val = strtolower($val);
                    if (($strict && $val == $value) || (!$strict && strstr($val, $value))) {
                        $this->filter[] = $id;
                        break 2;
                    }
                }
            }
        }

        // list records (now limited by $this->filter)
        return $this->list_records();
    }


    /**
     * Count number of available contacts in database
     *
     * @return rcube_result_set Result set with values for 'count' and 'first'
     */
    public function count()
    {
        $this->_fetch_contacts();
        $this->_fetch_groups();
        $count = $this->gid ? count($this->distlists[$this->gid]['member']) : ($this->filter ? count($this->filter) : count($this->contacts));
        return new rcube_result_set($count, ($this->list_page-1) * $this->page_size);
    }


    /**
     * Return the last result set
     *
     * @return rcube_result_set Current result set or NULL if nothing selected yet
     */
    public function get_result()
    {
        return $this->result;
    }

    /**
     * Get a specific contact record
     *
     * @param mixed record identifier(s)
     * @param boolean True to return record as associative array, otherwise a result set is returned
     * @return mixed Result object with all record fields or False if not found
     */
    public function get_record($id, $assoc=false)
    {
        $this->_fetch_contacts();
        if ($this->contacts[$id]) {
            $this->result = new rcube_result_set(1);
            $this->result->add($this->contacts[$id]);
            return $assoc ? $this->contacts[$id] : $this->result;
        }

        return false;
    }


    /**
     * Get group assignments of a specific contact record
     *
     * @param mixed Record identifier
     * @return array List of assigned groups as ID=>Name pairs
     */
    public function get_record_groups($id)
    {
        $out = array();
        $this->_fetch_groups();
        
        foreach ((array)$this->groupmembers[$id] as $gid) {
            if ($group = $this->distlists[$gid])
                $out[$gid] = $group['last-name'];
        }
        
        return $out;
    }


    /**
     * Create a new contact record
     *
     * @param array Assoziative array with save data
     *  Keys:   Field name with optional section in the form FIELD:SECTION
     *  Values: Field value. Can be either a string or an array of strings for multiple values
     * @param boolean True to check for duplicates first
     * @return mixed The created record ID on success, False on error
     */
    public function insert($save_data, $check=false)
    {
        if (!is_array($save_data))
            return false;

        $insert_id = $existing = false;

        // check for existing records by e-mail comparison
        if ($check) {
            foreach ($this->get_col_values('email', $save_data, true) as $email) {
                if (($res = $this->search('email', $email, true, false)) && $res->count) {
                    $existing = true;
                    break;
                }
            }
        }
        
        if (!$existing) {
            // generate new Kolab contact item
            $object = $this->_from_rcube_contact($save_data);
            $object['uid'] = $this->contactstorage->generateUID();

            $saved = $this->contactstorage->save($object);

            if (PEAR::isError($saved)) {
                raise_error(array(
                  'code' => 600, 'type' => 'php',
                  'file' => __FILE__, 'line' => __LINE__,
                  'message' => "Error saving contact object to Kolab server:" . $saved->getMessage()),
                true, false);
            }
            else {
                $contact = $this->_to_rcube_contact($object);
                $id = $contact['ID'];
                $this->contacts[$id] = $contact;
                $this->id2uid[$id] = $object['uid'];
                $insert_id = $id;
            }
        }
        
        return $insert_id;
    }


    /**
     * Update a specific contact record
     *
     * @param mixed Record identifier
     * @param array Assoziative array with save data
     *  Keys:   Field name with optional section in the form FIELD:SECTION
     *  Values: Field value. Can be either a string or an array of strings for multiple values
     * @return boolean True on success, False on error
     */
    public function update($id, $save_data)
    {
        $updated = false;
        $this->_fetch_contacts();
        if ($this->contacts[$id] && ($uid = $this->id2uid[$id])) {
            $old = $this->contactstorage->getObject($uid);
            $object = array_merge($old, $this->_from_rcube_contact($save_data));

            $saved = $this->contactstorage->save($object, $uid);
            if (PEAR::isError($saved)) {
                raise_error(array(
                  'code' => 600, 'type' => 'php',
                  'file' => __FILE__, 'line' => __LINE__,
                  'message' => "Error saving contact object to Kolab server:" . $saved->getMessage()),
                true, false);
            }
            else {
                $this->contacts[$id] = $this->_to_rcube_contact($object);
                $updated = true;
            }
        }
        
        return $updated;
    }

    /**
     * Mark one or more contact records as deleted
     *
     * @param array  Record identifiers
     */
    public function delete($ids)
    {
        $this->_fetch_contacts();
        $this->_fetch_groups();
        
        if (!is_array($ids))
            $ids = explode(',', $ids);

        $count = 0;
        foreach ($ids as $id) {
            if ($uid = $this->id2uid[$id]) {
                $deleted = $this->contactstorage->delete($uid);

                if (PEAR::isError($deleted)) {
                    raise_error(array(
                      'code' => 600, 'type' => 'php',
                      'file' => __FILE__, 'line' => __LINE__,
                      'message' => "Error deleting a contact object from the Kolab server:" . $deleted->getMessage()),
                    true, false);
                }
                else {
                    // remove from distribution lists
                    foreach ((array)$this->groupmembers[$id] as $gid)
                        $this->remove_from_group($gid, $id);
                    
                    // clear internal cache
                    unset($this->contacts[$id], $this->id2uid[$id], $this->groupmembers[$id]);
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Remove all records from the database
     */
    public function delete_all()
    {
        if (!PEAR::isError($this->contactstorage->deleteAll())) {
            $this->contacts = array();
            $this->id2uid = array();
            $this->result = null;
        }
    }

    
    /**
     * Close connection to source
     * Called on script shutdown
     */
    public function close()
    {
        rcube_kolab::shutdown();
    }


    /**
     * Create a contact group with the given name
     *
     * @param string The group name
     * @return mixed False on error, array with record props in success
     */
    function create_group($name)
    {
        $this->_fetch_groups();
        $result = false;
        
        $list = array(
            'uid' => $this->liststorage->generateUID(),
            'last-name' => $name,
            'member' => array(),
        );
        $saved = $this->liststorage->save($list);

        if (PEAR::isError($saved)) {
            raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => "Error saving distribution-list object to Kolab server:" . $saved->getMessage()),
            true, false);
            return false;
        }
        else {
            $id = md5($list['uid']);
            $this->distlists[$record['ID']] = $list;
            $result = array('id' => $id, 'name' => $name);
        }

        return $result;
    }

    /**
     * Delete the given group and all linked group members
     *
     * @param string Group identifier
     * @return boolean True on success, false if no data was changed
     */
    function delete_group($gid)
    {
        $this->_fetch_groups();
        $result = false;
        
        if ($list = $this->distlists[$gid])
            $deleted = $this->liststorage->delete($list['uid']);

        if (PEAR::isError($deleted)) {
            raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => "Error deleting distribution-list object from the Kolab server:" . $deleted->getMessage()),
            true, false);
        }
        else
            $result = true;
        
        return $result;
    }

    /**
     * Rename a specific contact group
     *
     * @param string Group identifier
     * @param string New name to set for this group
     * @return boolean New name on success, false if no data was changed
     */
    function rename_group($gid, $newname)
    {
        $this->_fetch_groups();
        $list = $this->distlists[$gid];
        
        if ($newname != $list['last-name']) {
            $list['last-name'] = $newname;
            $saved = $this->liststorage->save($list, $list['uid']);
        }

        if (PEAR::isError($saved)) {
            raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => "Error saving distribution-list object to Kolab server:" . $saved->getMessage()),
            true, false);
            return false;
        }

        return $newname;
    }

    /**
     * Add the given contact records the a certain group
     *
     * @param string  Group identifier
     * @param array   List of contact identifiers to be added
     * @return int    Number of contacts added
     */
    function add_to_group($gid, $ids)
    {
        if (!is_array($ids))
            $ids = explode(',', $ids);

        $added = 0;
        $exists = array();
        
        $this->_fetch_groups();
        $this->_fetch_contacts();
        $list = $this->distlists[$gid];

        foreach ((array)$list['member'] as $i => $member)
            $exists[] = $member['ID'];
        
        // substract existing assignments from list
        $ids = array_diff($ids, $exists);

        foreach ($ids as $contact_id) {
            if ($uid = $this->id2uid[$contact_id]) {
                $contact = $this->contacts[$contact_id];
                foreach ($this->get_col_values('email', $contact, true) as $email) {
                    $list['member'][] = array(
                        'uid' => $uid,
                        'display-name' => $contact['name'],
                        'smtp-address' => $email,
                    );
                }
                $this->groupmembers[$contact_id][] = $gid;
                $added++;
            }
        }
        
        if ($added)
            $saved = $this->liststorage->save($list, $list['uid']);
        
        if (PEAR::isError($saved)) {
            raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => "Error saving distribution-list to Kolab server:" . $saved->getMessage()),
            true, false);
            $added = false;
        }
        else {
            $this->distlists[$gid] = $list;
        }
        
        return $added;
    }

    /**
     * Remove the given contact records from a certain group
     *
     * @param string  Group identifier
     * @param array   List of contact identifiers to be removed
     * @return int    Number of deleted group members
     */
    function remove_from_group($gid, $ids)
    {
        if (!is_array($ids))
            $ids = explode(',', $ids);
        
        $this->_fetch_groups();
        if (!($list = $this->distlists[$gid]))
            return false;

        $new_member = array();
        foreach ((array)$list['member'] as $member) {
            if (!in_array($member['ID'], $ids))
                $new_member[] = $member;
        }

        // write distribution list back to server
        $list['member'] = $new_member;
        $saved = $this->liststorage->save($list, $list['uid']);
        
        if (PEAR::isError($saved)) {
            raise_error(array(
              'code' => 600, 'type' => 'php',
              'file' => __FILE__, 'line' => __LINE__,
              'message' => "Error saving distribution-list object to Kolab server:" . $saved->getMessage()),
            true, false);
        }
        else {
            // remove group assigments in local cache
            foreach ($ids as $id) {
                $j = array_search($gid, $this->groupmembers[$id]);
                unset($this->groupmembers[$id][$j]);
            }
            $this->distlists[$gid] = $list;
            return true;
        }

        return false;
    }


    /**
     * Simply fetch all records and store them in private member vars
     */
    private function _fetch_contacts()
    {
        if (!isset($this->contacts)) {
            // read contacts
            $this->contacts = $this->id2uid = array();
            foreach ((array)$this->contactstorage->getObjects() as $record) {
                $contact = $this->_to_rcube_contact($record);
                $id = $contact['ID'];
                $this->contacts[$id] = $contact;
                $this->id2uid[$id] = $record['uid'];
            }

            // TODO: sort data arrays according to desired list sorting
        }
    }
    
    
    /**
     * Read distribution-lists AKA groups from server
     */
    private function _fetch_groups()
    {
        if (!isset($this->distlists)) {
            $this->distlists = $this->groupmembers = array();
            foreach ((array)$this->liststorage->getObjects() as $record) {
                // FIXME: folders without any distribution-list objects return contacts instead ?!
                if ($record['__type'] != 'Group')
                    continue;
                $record['ID'] = md5($record['uid']);
                foreach ((array)$record['member'] as $i => $member) {
                    $mid = md5($member['uid']);
                    $record['member'][$i]['ID'] = $mid;
                    $this->groupmembers[$mid][] = $record['ID'];
                }
                $this->distlists[$record['ID']] = $record;
            }
        }
    }
    
    
    /**
     * Map fields from internal Kolab_Format to Roundcube contact format
     */
    private function _to_rcube_contact($record)
    {
        $out = array(
          'ID' => md5($record['uid']),
          'email' => array(),
          'phone' => array(),
        );
        
        foreach ($this->fieldmap as $kolab => $rcube) {
          if (strlen($record[$kolab]))
            $out[$rcube] = $record[$kolab];
        }
        
        if (isset($record['gender']))
            $out['gender'] = $this->gender_map[$record['gender']];

        foreach ((array)$record['email'] as $i => $email)
            $out['email'][] = $email['smtp-address'];
            
        if (!$record['email'] && $record['emails'])
            $out['email'] = preg_split('/,\s*/', $record['emails']);

        foreach ((array)$record['phone'] as $i => $phone)
            $out['phone:'.$phone['type']][] = $phone['number'];

        if (is_array($record['address'])) {
            foreach ($record['address'] as $i => $adr) {
                $key = 'address:' . $adr['type'];
                $out[$key][] = array(
                    'street' => $adr['street'],
                    'locality' => $adr['locality'],
                    'zipcode' => $adr['postal-code'],
                    'region' => $adr['region'],
                    'country' => $adr['country'],
                );
            }
        }

        // remove empty fields
        return array_filter($out);
    }

    private function _from_rcube_contact($contact)
    {
        $object = array();

        foreach (array_flip($this->fieldmap) as $rcube => $kolab) {
            if (isset($contact[$rcube]))
                $object[$kolab] = is_array($contact[$rcube]) ? $contact[$rcube][0] : $contact[$rcube];
            else if ($rcube .= ':home' && isset($contact[$rcube]))
                $object[$kolab] = is_array($contact[$rcube]) ? $contact[$rcube][0] : $contact[$rcube];
        }

        // format dates
        if ($object['birthday'] && ($date = @strtotime($object['birthday'])))
            $object['birthday'] = date('Y-m-d', $date);
        if ($object['anniversary'] && ($date = @strtotime($object['anniversary'])))
            $object['anniversary'] = date('Y-m-d', $date);

        $gendermap = array_flip($this->gender_map);
        if (isset($contact['gender']))
            $object['gender'] = $gendermap[$contact['gender']];

        $emails = $this->get_col_values('email', $contact, true);
        $object['emails'] = join(', ', $emails);

        foreach ($this->get_col_values('phone', $contact) as $type => $values) {
            if ($this->phonetypemap[$type])
                $type = $this->phonetypemap[$type];
            foreach ((array)$values as $phone)
                $object['phone'][] = array('number' => $phone, 'type' => $type);
        }

        foreach ($this->get_col_values('address', $contact) as $type => $values) {
            if ($this->addresstypemap[$type])
                $type = $this->addresstypemap[$type];
            
            $basekey = 'addr-' . $type . '-';
            foreach ((array)$values as $adr) {
                // switch type if slot is already taken
                if (isset($object[$basekey . 'type'])) {
                    $type = $type == 'home' ? 'business' : 'home';
                    $basekey = 'addr-' . $type . '-';
                }
                
                if (!isset($object[$basekey . 'type'])) {
                    $object[$basekey . 'type'] = $type;
                    $object[$basekey . 'street'] = $adr['street'];
                    $object[$basekey . 'locality'] = $adr['locality'];
                    $object[$basekey . 'postal-code'] = $adr['zipcode'];
                    $object[$basekey . 'region'] = $adr['region'];
                    $object[$basekey . 'country'] = $adr['country'];
                }
                else {
                    $object['address'][] = array(
                        'type' => $type,
                        'street' => $adr['street'],
                        'locality' => $adr['locality'],
                        'postal-code' => $adr['zipcode'],
                        'region' => $adr['region'],
                        'country' => $adr['country'],
                    );
                }
            }
        }

        return $object;
    }

}
