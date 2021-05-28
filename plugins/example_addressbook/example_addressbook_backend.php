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

    public function __construct($name)
    {
        $this->ready = true;
        $this->name = $name;
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
        $groups = [
            ['ID' => 'testgroup1', 'name' => "Testgroup"],
            ['ID' => 'testgroup2', 'name' => "Sample Group"],
        ];

        if (is_string($search) && strlen($search)) {
            // Return empty result for any searches, normally it should filter the existing groups
            return [];
        }

        return $groups;
    }

    public function list_records($cols = null, $subset = 0, $nocount = false)
    {
        $this->result = $this->count();
        $this->result->add([
                'ID'        => '111',
                'name'      => "Example Contact",
                'firstname' => "Example",
                'surname'   => "Contact",
                'email'     => "example@roundcube.net"
        ]);

        return $this->result;
    }

    public function search($fields, $value, $strict = false, $select = true, $nocount = false, $required = [])
    {
        // Note: we do not implement all possible search request modes and variants.
        //       We implement only the simplest searching case in "select" mode

        $result = new rcube_result_set();
        foreach ($this->list_records() as $record) {
            if (is_string($value)) {
                $found = false;

                foreach (array_keys($record) as $key) {
                    if (strpos(mb_strtolower((string) $record[$key]), mb_strtolower($value)) !== false) {
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
        return new rcube_result_set(1, ($this->list_page-1) * $this->page_size);
    }

    public function get_result()
    {
        return $this->result;
    }

    public function get_record($id, $assoc = false)
    {
        $this->list_records();
        $first   = $this->result->first();
        $sql_arr = $first['ID'] == $id ? $first : null;

        return $assoc && $sql_arr ? $sql_arr : $this->result;
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
