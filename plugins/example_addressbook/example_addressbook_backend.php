<?php

/**
 * Example backend class for a custom address book
 *
 * This one just holds a static list of address records
 *
 * @author Thomas Bruederli
 */
class example_addressbook_backend extends rcube_addressbook
{
    public $primary_key = 'ID';
    public $readonly    = true;
    public $groups      = true;

    private $filter;
    private $result;
    private $name;

    private $db_groups = [
        [
            'ID'   => 'testgroup1',
            'name' => "Testgroup"
        ],
        [
            'ID'   => 'testgroup2',
            'name' => "Sample Group"
        ],
    ];

    private $db_users = [
        [
            'ID'        => '111',
            'name'      => "John Doe",
            'firstname' => "John",
            'surname'   => "Doe",
            'email'     => "example1@roundcube.net",
            'groups'    => ['testgroup1']
        ],
        [
            'ID'        => '112',
            'name'      => "Jane Example",
            'firstname' => "Jane",
            'surname'   => "Example",
            'email'     => "example2@roundcube.net",
            'groups'    => ['testgroup2']
        ]
    ];

    public function __construct($name)
    {
        $this->ready = true;
        $this->name = $name;
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
        foreach ($this->db_groups as $group) {
            if ($group['ID'] == $group_id) {
                return $group;
            }
        }
    }

    public function get_name()
    {
        return $this->name;
    }

    public function set_search_set($filter)
    {
        $this->filter = $filter;
    }

    public function get_search_set()
    {
        return $this->filter;
    }

    public function reset()
    {
        $this->result = null;
        $this->filter = null;
    }

    function list_groups($search = null, $mode = 0)
    {
        if (is_string($search) && strlen($search)) {
            $result = [];

            foreach ($this->db_groups as $group) {
                if (stripos($group['name'], $search) !== false) {
                    $result[] = $group;
                }
            }

            return $result;
        }

        return $this->db_groups;
    }

    public function list_records($cols = null, $subset = 0, $nocount = false)
    {
        // Note: Paging is not implemented

        return $this->result = $this->count();
    }

    public function search($fields, $value, $strict = false, $select = true, $nocount = false, $required = [])
    {
        // Note: we do not implement all possible search request modes and variants.
        //       We implement only the simplest searching case in "select" mode

        $result = new rcube_result_set();
        foreach ($this->list_records() as $record) {
            if (is_string($value)) {
                $found = false;

                foreach ($record as $key => $data) {
                    $data = is_array($data) ? implode(' ', $data) : (string) $data;
                    if (strpos(mb_strtolower($data), mb_strtolower($value)) !== false) {
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    $result->add($record);
                }
            }
        }

        return $result;
    }

    public function count()
    {
        // Note: Paging is not implemented

        $result = new rcube_result_set(0, ($this->list_page-1) * $this->page_size);
        $count  = 0;

        foreach ($this->db_users as $user) {
            if ($this->group_id && (empty($user['groups']) || !in_array($this->group_id, $user['groups']))) {
                continue;
            }

            // TODO: This should consider current search filter

            $result->add($user);
            $count++;
        }

        $result->count = $count;

        return $result;
    }

    public function get_result()
    {
        return $this->result;
    }

    public function get_record($id, $assoc = false)
    {
        $result = new rcube_result_set(0);

        foreach ($this->db_users as $user) {
            if ($user['ID'] == $id) {
                if ($assoc) {
                    return $user;
                }

                $result->add($user);
                $result->count = 1;
            }
        }

        return $result;
    }

    /**
     * Get group assignments of a specific contact record
     *
     * @param mixed $id Record identifier
     *
     * @return array List of assigned groups, indexed by group ID
     */
    function get_record_groups($id)
    {
        $result = [];

        foreach ($this->db_users as $user) {
            if ($user['ID'] == $id) {
                foreach ($this->db_groups as $group) {
                    if (!empty($user['groups']) && in_array($group['ID'], $user['groups'])) {
                        $result[$group['ID']] = $group['name'];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Setter for the current group
     */
    function set_group($gid)
    {
        $this->group_id = $gid;
    }

    function create_group($name)
    {
        $result = false;

        return $result;
    }

    function delete_group($gid)
    {
        return false;
    }

    function rename_group($gid, $newname, &$newid)
    {
        return $newname;
    }

    function add_to_group($group_id, $ids)
    {
        return false;
    }

    function remove_from_group($group_id, $ids)
    {
        return false;
    }
}
