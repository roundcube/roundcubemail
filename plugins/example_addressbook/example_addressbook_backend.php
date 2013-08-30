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
    public $readonly = true;
    public $groups = true;

    private $filter;
    private $result;
    private $name;

    public function __construct($name)
    {
        $this->ready = true;
        $this->name  = $name;
    }

    /**
     * @see rcube_addressbook::get_name
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @see rcube_addressbook::set_search_set
     */
    public function set_search_set($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @see rcube_addressbook::get_search_set
     */
    public function get_search_set()
    {
        return $this->filter;
    }

    /**
     * @see rcube_addressbook::reset
     */
    public function reset()
    {
        $this->result = null;
        $this->filter = null;
    }

    /**
     * @see rcube_addressbook::list_groups
     */
    function list_groups($search = null)
    {
        return array(
            array('ID' => 'testgroup1', 'name' => "Testgroup"),
            array('ID' => 'testgroup2', 'name' => "Sample Group"),
        );
    }

    /**
     * @see rcube_addressbook::list_records
     */
    public function list_records($cols = null, $subset = 0)
    {
        $this->result = $this->count();

        $this->result->add(
            array(
                'ID' => '111',
                'name' => "Example Contact",
                'firstname' => "Example",
                'surname' => "Contact",
                'email' => "example@roundcube.net"
            )
        );

        return $this->result;
    }

    /**
     * @see rcube_addressbook::search
     */
    public function search($fields, $value, $strict = false, $select = true, $nocount = false, $required = array())
    {
        return $this->list_records();
    }

    /**
     * @see rcube_addressbook::count
     */
    public function count()
    {
        return new rcube_result_set(1, ($this->list_page - 1) * $this->page_size);
    }

    /**
     * @see rcube_addressbook::get_result
     */
    public function get_result()
    {
        return $this->result;
    }

    /**
     * @see rcube_addressbook::get_record
     */
    public function get_record($id, $assoc = false)
    {
        $this->list_records();

        $first   = $this->result->first();

        $sql_arr = $first['ID'] == $id ? $first : null;

        return $assoc && $sql_arr ? $sql_arr : $this->result;
    }

    /**
     * @see rcube_addressbook::create_group
     */
    function create_group($name)
    {
        $result = false;

        return $result;
    }

    /**
     * @see rcube_addressbook::delete_group
     */
    function delete_group($gid)
    {
        return false;
    }

    /**
     * @see rcube_addressbook::rename_group
     */
    function rename_group($gid, $newname)
    {
        return $newname;
    }

    /**
     * @see rcube_addressbook::add_to_group
     */
    function add_to_group($group_id, $ids)
    {
        return false;
    }

    /**
     * @see rcube_addressbook::remove_from_group
     */
    function remove_from_group($group_id, $ids)
    {
        return false;
    }
}
