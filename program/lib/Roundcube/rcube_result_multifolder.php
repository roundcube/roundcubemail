<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   SORT/SEARCH/ESEARCH response handler                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Class holding a set of rcube_result_index instances that together form a
 * result set of a multi-folder search
 *
 * @package    Framework
 * @subpackage Storage
 */
class rcube_result_multifolder
{
    public $multi = true;
    public $sets = array();

    protected $meta = array();
    protected $order = 'ASC';


    /**
     * Object constructor.
     */
    public function __construct()
    {
        $this->meta = array('count' => 0);
    }


    /**
     * Initializes object with SORT command response
     *
     * @param string $data IMAP response string
     */
    public function add($result)
    {
        $this->sets[] = $result;
        $this->meta['count'] += $result->count();
    }


    /**
     * Checks the result from IMAP command
     *
     * @return bool True if the result is an error, False otherwise
     */
    public function is_error()
    {
        return false;
    }


    /**
     * Checks if the result is empty
     *
     * @return bool True if the result is empty, False otherwise
     */
    public function is_empty()
    {
        return empty($this->sets) || $this->meta['count'] == 0;
    }


    /**
     * Returns number of elements in the result
     *
     * @return int Number of elements
     */
    public function count()
    {
        return $this->meta['count'];
    }


    /**
     * Returns number of elements in the result.
     * Alias for count() for compatibility with rcube_result_thread
     *
     * @return int Number of elements
     */
    public function count_messages()
    {
        return $this->count();
    }


    /**
     * Reverts order of elements in the result
     */
    public function revert()
    {
        $this->order = $this->order == 'ASC' ? 'DESC' : 'ASC';
    }


    /**
     * Check if the given message ID exists in the object
     *
     * @param int  $msgid     Message ID
     * @param bool $get_index When enabled element's index will be returned.
     *                        Elements are indexed starting with 0
     * @return mixed False if message ID doesn't exist, True if exists or
     *               index of the element if $get_index=true
     */
    public function exists($msgid, $get_index = false)
    {
        return false;
    }


    /**
     * Filters data set. Removes elements listed in $ids list.
     *
     * @param array $ids List of IDs to remove.
     * @param string $folder IMAP folder
     */
    public function filter($ids = array(), $folder = null)
    {
        $this->meta['count'] = 0;
        foreach ($this->sets as $set) {
            if ($set->get_parameters('MAILBOX') == $folder) {
                $set->filter($ids);
            }
            $this->meta['count'] += $set->count();
        }
    }

    /**
     * Filters data set. Removes elements not listed in $ids list.
     *
     * @param array $ids List of IDs to keep.
     */
    public function intersect($ids = array())
    {
        // not implemented
    }

    /**
     * Return all messages in the result.
     *
     * @return array List of message IDs
     */
    public function get()
    {
        return array();
    }


    /**
     * Return all messages in the result.
     *
     * @return array List of message IDs
     */
    public function get_compressed()
    {
        return '';
    }


    /**
     * Return result element at specified index
     *
     * @param int|string  $index  Element's index or "FIRST" or "LAST"
     *
     * @return int Element value
     */
    public function get_element($index)
    {
        return null;
    }


    /**
     * Returns response parameters, e.g. ESEARCH's MIN/MAX/COUNT/ALL/MODSEQ
     * or internal data e.g. MAILBOX, ORDER
     *
     * @param string $param  Parameter name
     *
     * @return array|string Response parameters or parameter value
     */
    public function get_parameters($param=null)
    {
        return $params;
    }


    /**
     * Returns length of internal data representation
     *
     * @return int Data length
     */
    protected function length()
    {
        return $this->count();
    }
}
