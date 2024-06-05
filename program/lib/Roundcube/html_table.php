<?php

namespace Roundcube\WIP;

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
 |   Helper class to create valid XHTML code                             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to build an HTML table
 */
class html_table extends \html
{
    protected $tagname = 'table';
    protected $allowed = ['id', 'class', 'style', 'width', 'summary',
        'cellpadding', 'cellspacing', 'border'];

    private $header;
    private $rows = [];
    private $rowindex = 0;
    private $colindex = 0;

    /**
     * Constructor
     *
     * @param array $attrib Named tag attributes
     */
    public function __construct($attrib = [])
    {
        parent::__construct($attrib);

        if (self::$doctype == 'xhtml') {
            $this->attrib['border'] = '0';
        }

        if (!empty($attrib['tagname']) && $attrib['tagname'] != 'table') {
            $this->tagname = $attrib['tagname'];
            $this->allowed = self::$common_attrib;
        }
    }

    /**
     * Add a table cell
     *
     * @param array|string $attr Cell attributes or 'class' attribute value
     * @param string       $cont Cell content
     */
    public function add($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        $cell = new \stdClass();
        $cell->attrib = $attr;
        $cell->content = $cont;

        if (!isset($this->rows[$this->rowindex])) {
            $this->rows[$this->rowindex] = new \stdClass();
            $this->rows[$this->rowindex]->attrib = [];
        }

        $this->rows[$this->rowindex]->cells[$this->colindex] = $cell;
        $this->colindex += max(1, isset($attr['colspan']) ? intval($attr['colspan']) : 0);

        if (!empty($this->attrib['cols']) && $this->colindex >= $this->attrib['cols']) {
            $this->add_row();
        }
    }

    /**
     * Add a table header cell
     *
     * @param string|array $attr Cell attributes array or class name
     * @param string       $cont Cell content
     */
    public function add_header($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        $cell = new \stdClass();
        $cell->attrib = $attr;
        $cell->content = $cont;

        if (empty($this->header)) {
            $this->header = new \stdClass();
            $this->header->attrib = [];
        }

        $this->header->cells[] = $cell;
    }

    /**
     * Remove a column from a table
     * Useful for plugins making alterations
     *
     * @param string $class Class name
     */
    public function remove_column($class)
    {
        // Remove the header
        foreach ($this->header->cells as $index => $header) {
            if ($header->attrib['class'] == $class) {
                unset($this->header[$index]);
                break;
            }
        }

        // Remove cells from rows
        foreach ($this->rows as $i => $row) {
            foreach ($row->cells as $j => $cell) {
                if ($cell->attrib['class'] == $class) {
                    unset($this->rows[$i]->cells[$j]);
                    break;
                }
            }
        }
    }

    /**
     * Jump to next row
     *
     * @param array $attr Row attributes
     */
    public function add_row($attr = [])
    {
        $this->rowindex++;
        $this->colindex = 0;
        $this->rows[$this->rowindex] = new \stdClass();
        $this->rows[$this->rowindex]->attrib = $attr;
        $this->rows[$this->rowindex]->cells = [];
    }

    /**
     * Set header attributes
     *
     * @param string|array $attr Row attributes array or class name
     */
    public function set_header_attribs($attr = [])
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        if (empty($this->header)) {
            $this->header = new \stdClass();
        }

        $this->header->attrib = $attr;
    }

    /**
     * Set row attributes
     *
     * @param string|array $attr  Row attributes array or class name
     * @param int          $index Optional row index (default current row index)
     */
    public function set_row_attribs($attr = [], $index = null)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        if ($index === null) {
            $index = $this->rowindex;
        }

        // make sure row object exists (#1489094)
        if (empty($this->rows[$index])) {
            $this->rows[$index] = new \stdClass();
        }

        $this->rows[$index]->attrib = $attr;
    }

    /**
     * Get row attributes
     *
     * @param int $index Row index
     *
     * @return array Row attributes
     */
    public function get_row_attribs($index = null)
    {
        if ($index === null) {
            $index = $this->rowindex;
        }

        return !empty($this->rows[$index]) ? $this->rows[$index]->attrib : null;
    }

    /**
     * Build HTML output of the table data
     *
     * @param array $attrib Table attributes
     *
     * @return string The final table HTML code
     */
    #[\Override]
    public function show($attrib = null)
    {
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $thead = '';
        $tbody = '';
        $col_tagname = $this->_col_tagname();
        $row_tagname = $this->_row_tagname();
        $head_tagname = $this->_head_tagname();

        // include <thead>
        if (!empty($this->header)) {
            $rowcontent = '';
            foreach ($this->header->cells as $c => $col) {
                $rowcontent .= self::tag($head_tagname, $col->attrib, $col->content);
            }
            $thead = $this->tagname == 'table' ? self::tag('thead', null, self::tag('tr', $this->header->attrib ?: null, $rowcontent, parent::$common_attrib)) :
                self::tag($row_tagname, ['class' => 'thead'], $rowcontent, parent::$common_attrib);
        }

        foreach ($this->rows as $r => $row) {
            $rowcontent = '';
            foreach ($row->cells as $c => $col) {
                if ($row_tagname == 'li' && empty($col->attrib) && count($row->cells) == 1) {
                    $rowcontent .= $col->content;
                } else {
                    $rowcontent .= self::tag($col_tagname, $col->attrib, $col->content);
                }
            }

            if ($r < $this->rowindex || count($row->cells)) {
                $tbody .= self::tag($row_tagname, $row->attrib, $rowcontent, parent::$common_attrib);
            }
        }

        if (!empty($this->attrib['rowsonly'])) {
            return $tbody;
        }

        // add <tbody>
        $this->content = $thead . ($this->tagname == 'table' ? self::tag('tbody', null, $tbody) : $tbody);

        unset($this->attrib['cols'], $this->attrib['rowsonly']);

        return parent::show();
    }

    /**
     * Count number of rows
     *
     * @return int The number of rows
     */
    public function size()
    {
        return count($this->rows);
    }

    /**
     * Remove table body (all rows)
     */
    public function remove_body()
    {
        $this->rows = [];
        $this->rowindex = 0;
    }

    /**
     * Getter for the corresponding tag name for table row elements
     */
    private function _row_tagname()
    {
        static $row_tagnames = ['table' => 'tr', 'ul' => 'li', '*' => 'div'];
        return !empty($row_tagnames[$this->tagname]) ? $row_tagnames[$this->tagname] : $row_tagnames['*'];
    }

    /**
     * Getter for the corresponding tag name for table row elements
     */
    private function _head_tagname()
    {
        static $head_tagnames = ['table' => 'th', '*' => 'span'];
        return !empty($head_tagnames[$this->tagname]) ? $head_tagnames[$this->tagname] : $head_tagnames['*'];
    }

    /**
     * Getter for the corresponding tag name for table cell elements
     */
    private function _col_tagname()
    {
        static $col_tagnames = ['table' => 'td', '*' => 'span'];
        return !empty($col_tagnames[$this->tagname]) ? $col_tagnames[$this->tagname] : $col_tagnames['*'];
    }
}
