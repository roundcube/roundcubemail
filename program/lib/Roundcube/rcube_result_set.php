<?php

/*
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
 |   Class representing an address directory result set                  |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Roundcube result set class
 *
 * Representing an address directory result set.
 * Implements Iterator and can thus be used in foreach() loops.
 */
class rcube_result_set implements Iterator, ArrayAccess
{
    /**
     * @var int The number of total records. Note that when only a subset of records is requested,
     *          this number may be higher than the number of data records in this result set.
     */
    public $count = 0;

    /**
     * @var int When a subset of the total records is requested, this property gives the index into the total record
     *          set from that the data records in this result set start. This is normally a multiple of the
     *          user-configured page size.
     */
    public $first = 0;

    /**
     * @var bool true if the results are from an addressbook that does not support listing all records but
     *           requires the search function to be used
     */
    public $searchonly = false;

    /**
     * @var array The data records of the result set. May be a subset of the total records, e.g. for one page.
     */
    public $records = [];

    private $current = 0;

    public function __construct($count = 0, $first = 0)
    {
        $this->count = (int) $count;
        $this->first = (int) $first;
    }

    public function add($rec)
    {
        $this->records[] = $rec;
    }

    public function iterate()
    {
        $current = $this->current();

        $this->current++;

        return $current;
    }

    public function first()
    {
        $this->current = 0;
        return $this->current();
    }

    public function seek($i): void
    {
        $this->current = $i;
    }

    // Implement PHP ArrayAccess interface

    #[Override]
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $offset = count($this->records);
            $this->records[] = $value;
        } else {
            $this->records[$offset] = $value;
        }
    }

    #[Override]
    public function offsetExists($offset): bool
    {
        return isset($this->records[$offset]);
    }

    #[Override]
    public function offsetUnset($offset): void
    {
        unset($this->records[$offset]);
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->records[$offset];
    }

    // PHP 5 Iterator interface

    #[Override]
    public function rewind(): void
    {
        $this->current = 0;
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->records[$this->current] ?? null;
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->current;
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function next()
    {
        $this->current++;
    }

    #[Override]
    public function valid(): bool
    {
        return isset($this->records[$this->current]);
    }
}
