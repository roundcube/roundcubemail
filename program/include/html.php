<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/html.php                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Helper class to create valid XHTML code                             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: $

 */


/**
 * Class for HTML code creation
 *
 * @package HTML
 */
class html
{
    protected $tagname;
    protected $attrib = array();
    protected $allowed = array();
    protected $content;

    public static $common_attrib = array('id','class','style','title','align');
    public static $containers = array('div','span','p','h1','h2','h3','form','textarea');
    public static $lc_tags = true;

    /**
     * Constructor
     *
     * @param array Hash array with tag attributes
     */
    public function __construct($attrib = array())
    {
        if (is_array($attrib)) {
            $this->attrib = $attrib;
        }
    }

    /**
     * Return the tag code
     *
     * @return string The finally composed HTML tag
     */
    public function show()
    {
        return self::tag($this->tagname, $this->attrib, $this->content, array_merge(self::$common_attrib, $this->allowed));
    }

    /****** STATIC METHODS *******/

    /**
     * Generic method to create a HTML tag
     *
     * @param string Tag name
     * @param array  Tag attributes as key/value pairs
     * @param string Optinal Tag content (creates a container tag)
     * @param array  List with allowed attributes, omit to allow all
     * @return string The XHTML tag
     */
    public static function tag($tagname, $attrib = array(), $content = null, $allowed_attrib = null)
    {
        $inline_tags = array('a','span','img');
        $suffix = $attrib['nl'] || ($content && $attrib['nl'] !== false && !in_array($tagname, $inline_tags)) ? "\n" : '';

        $tagname = self::$lc_tags ? strtolower($tagname) : $tagname;
        if ($content || in_array($tagname, self::$containers)) {
            $templ = $attrib['noclose'] ? "<%s%s>%s" : "<%s%s>%s</%s>%s";
            unset($attrib['noclose']);
            return sprintf($templ, $tagname, self::attrib_string($attrib, $allowed_attrib), $content, $tagname, $suffix);
        }
        else {
            return sprintf("<%s%s />%s", $tagname, self::attrib_string($attrib, $allowed_attrib), $suffix);
        }
    }

    /**
     * Derrived method for <div> containers
     *
     * @param mixed  Hash array with tag attributes or string with class name
     * @param string Div content
     * @return string HTML code
     * @see html::tag()
     */
    public static function div($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('div', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derrived method for <p> blocks
     *
     * @param mixed  Hash array with tag attributes or string with class name
     * @param string Paragraph content
     * @return string HTML code
     * @see html::tag()
     */
    public static function p($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('p', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derrived method to create <img />
     *
     * @param mixed Hash array with tag attributes or string with image source (src)
     * @return string HTML code
     * @see html::tag()
     */
    public static function img($attr = null)
    {
        if (is_string($attr)) {
            $attr = array('src' => $attr);
        }
        return self::tag('img', $attr + array('alt' => ''), null, array_merge(self::$common_attrib, array('src','alt','width','height','border','usemap')));
    }

    /**
     * Derrived method for link tags
     *
     * @param mixed  Hash array with tag attributes or string with link location (href)
     * @param string Link content
     * @return string HTML code
     * @see html::tag()
     */
    public static function a($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('href' => $attr);
        }
        return self::tag('a', $attr, $cont, array_merge(self::$common_attrib, array('href','target','name','onclick','onmouseover','onmouseout')));
    }

    /**
     * Derrived method for inline span tags
     *
     * @param mixed  Hash array with tag attributes or string with class name
     * @param string Tag content
     * @return string HTML code
     * @see html::tag()
     */
    public static function span($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }
        return self::tag('span', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derrived method for form element labels
     *
     * @param mixed  Hash array with tag attributes or string with 'for' attrib
     * @param string Tag content
     * @return string HTML code
     * @see html::tag()
     */
    public static function label($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('for' => $attr);
        }
        return self::tag('label', $attr, $cont, array_merge(self::$common_attrib, array('for')));
    }

    /**
     * Derrived method for line breaks
     *
     * @return string HTML code
     * @see html::tag()
     */
    public static function br()
    {
        return self::tag('br');
    }

    /**
     * Create string with attributes
     *
     * @param array Associative arry with tag attributes
     * @param array List of allowed attributes
     * @return string Valid attribute string
     */
    public static function attrib_string($attrib = array(), $allowed = null)
    {
        if (empty($attrib)) {
            return '';
        }

        $allowed_f = array_flip((array)$allowed);
        $attrib_arr = array();
        foreach ($attrib as $key => $value) {
            // skip size if not numeric
            if (($key=='size' && !is_numeric($value))) {
                continue;
            }

            // ignore "internal" or not allowed attributes
            if ($key == 'nl' || ($allowed && !isset($allowed_f[$key])) || $value === null) {
                continue;
            }

            // skip empty eventhandlers
            if (preg_match('/^on[a-z]+/', $key) && !$value) {
                continue;
            }

            // attributes with no value
            if (in_array($key, array('checked', 'multiple', 'disabled', 'selected'))) {
                if ($value) {
                    $attrib_arr[] = sprintf('%s="%s"', $key, $key);
                }
            }
            else if ($key=='value') {
                $attrib_arr[] = sprintf('%s="%s"', $key, Q($value, 'strict', false));
            }
            else {
                $attrib_arr[] = sprintf('%s="%s"', $key, Q($value));
            }
        }
        return count($attrib_arr) ? ' '.implode(' ', $attrib_arr) : '';
    }
}

/**
 * Class to create an HTML input field
 *
 * @package HTML
 */
class html_inputfield extends html
{
    protected $tagname = 'input';
    protected $type = 'text';
    protected $allowed = array('type','name','value','size','tabindex','autocomplete','checked','onchange');

    public function __construct($attrib = array())
    {
        if (is_array($attrib)) {
            $this->attrib = $attrib;
        }

        if ($attrib['type']) {
            $this->type = $attrib['type'];
        }

        if ($attrib['newline']) {
            $this->newline = true;
        }
    }

    /**
     * Compose input tag
     *
     * @param string Field value
     * @param array Additional attributes to override
     * @return string HTML output
     */
    public function show($value = null, $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set value attribute
        if ($value !== null) {
            $this->attrib['value'] = $value;
        }
        // set type
        $this->attrib['type'] = $this->type;
        return parent::show();
    }
}

/**
 * Class to create an HTML password field
 *
 * @package HTML
 */
class html_passwordfield extends html_inputfield
{
    protected $type = 'password';
}

/**
 * Class to create an hidden HTML input field
 *
 * @package HTML
 */

class html_hiddenfield extends html_inputfield
{
    protected $type = 'hidden';
    protected $fields_arr = array();
    protected $newline = true;

    /**
     * Constructor
     *
     * @param array Named tag attributes
     */
    public function __construct($attrib = null)
    {
        if (is_array($attrib)) {
            $this->add($attrib);
        }
    }

    /**
     * Add a hidden field to this instance
     *
     * @param array Named tag attributes
     */
    public function add($attrib)
    {
        $this->fields_arr[] = $attrib;
    }

    /**
     * Create HTML code for the hidden fields
     *
     * @return string Final HTML code
     */
    public function show()
    {
        $out = '';
        foreach ($this->fields_arr as $attrib) {
            $out .= self::tag($this->tagname, array('type' => $this->type) + $attrib);
        }
        return $out;
    }
}

/**
 * Class to create HTML radio buttons
 *
 * @package HTML
 */
class html_radiobutton extends html_inputfield
{
    protected $type = 'radio';

    /**
     * Get HTML code for this object
     *
     * @param string Value of the checked field
     * @param array Additional attributes to override
     * @return string HTML output
     */
    public function show($value = '', $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set value attribute
        $this->attrib['checked'] = ((string)$value == (string)$this->attrib['value']);

        return parent::show();
    }
}

/**
 * Class to create HTML checkboxes
 *
 * @package HTML
 */
class html_checkbox extends html_inputfield
{
    protected $type = 'checkbox';

    /**
     * Get HTML code for this object
     *
     * @param string Value of the checked field
     * @param array Additional attributes to override
     * @return string HTML output
     */
    public function show($value = '', $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // set value attribute
        $this->attrib['checked'] = ((string)$value == (string)$this->attrib['value']);

        return parent::show();
    }
}

/**
 * Class to create an HTML textarea
 *
 * @package HTML
 */
class html_textarea extends html
{
    protected $tagname = 'textarea';
    protected $allowed = array('name','rows','cols','wrap','tabindex','onchange');

    /**
     * Get HTML code for this object
     *
     * @param string Textbox value
     * @param array Additional attributes to override
     * @return string HTML output
     */
    public function show($value = '', $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        // take value attribute as content
        if (empty($value) && !empty($this->attrib['value'])) {
            $value = $this->attrib['value'];
        }

        // make shure we don't print the value attribute
        if (isset($this->attrib['value'])) {
            unset($this->attrib['value']);
        }

        if (!empty($value) && !ereg('mce_editor', $this->attrib['class'])) {
            $value = Q($value, 'strict', false);
        }

        return self::tag($this->tagname, $this->attrib, $value, array_merge(self::$common_attrib, $this->allowed));
    }
}

/**
 * Builder for HTML drop-down menus
 * Syntax:<pre>
 * // create instance. arguments are used to set attributes of select-tag
 * $select = new html_select(array('name' => 'fieldname'));
 *
 * // add one option
 * $select->add('Switzerland', 'CH');
 *
 * // add multiple options
 * $select->add(array('Switzerland','Germany'), array('CH','DE'));
 *
 * // generate pulldown with selection 'Switzerland'  and return html-code
 * // as second argument the same attributes available to instanciate can be used
 * print $select->show('CH');
 * </pre>
 *
 * @package HTML
 */
class html_select extends html
{
    protected $tagname = 'select';
    protected $options = array();
    protected $allowed = array('name','size','tabindex','autocomplete','multiple','onchange');
    
    /**
     * Add a new option to this drop-down
     *
     * @param mixed Option name or array with option names
     * @param mixed Option value or array with option values
     */
    public function add($names, $values = null)
    {
        if (is_array($names)) {
            foreach ($names as $i => $text) {
                $this->options[] = array('text' => $text, 'value' => $values[$i]);
            }
        }
        else {
            $this->options[] = array('text' => $names, 'value' => $values);
        }
    }


    /**
     * Get HTML code for this object
     *
     * @param string Value of the selection option
     * @param array Additional attributes to override
     * @return string HTML output
     */
    public function show($select = array(), $attrib = null)
    {
        // overwrite object attributes
        if (is_array($attrib)) {
            $this->attrib = array_merge($this->attrib, $attrib);
        }

        $this->content = "\n";
        $select = (array)$select;
        foreach ($this->options as $option) {
            $attr = array(
                'value' => $option['value'],
                'selected' => (in_array($option['value'], $select, true) ||
                  in_array($option['text'], $select, true)) ? 1 : null);

            $this->content .= self::tag('option', $attr, Q($option['text']));
        }
        return parent::show();
    }
}


/**
 * Class to build an HTML table
 *
 * @package HTML
 */
class html_table extends html
{
    protected $tagname = 'table';
    protected $allowed = array('id','class','style','width','summary','cellpadding','cellspacing','border');
    private $header = array();
    private $rows = array();
    private $rowindex = 0;
    private $colindex = 0;


    public function __construct($attrib = array())
    {
        $this->attrib = array_merge($attrib, array('summary' => '', 'border' => 0));
    }

    /**
     * Add a table cell
     *
     * @param array Cell attributes
     * @param string Cell content
     */
    public function add($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = array('class' => $attr);
        }

        $cell = new stdClass;
        $cell->attrib = $attr;
        $cell->content = $cont;

        $this->rows[$this->rowindex]->cells[$this->colindex] = $cell;
        $this->colindex++;

        if ($this->attrib['cols'] && $this->colindex == $this->attrib['cols']) {
            $this->add_row();
        }
    }

    /**
     * Add a table header cell
     *
     * @param array Cell attributes
     * @param string Cell content
     */
    public function add_header($attr, $cont)
    {
        if (is_string($attr))
        $attr = array('class' => $attr);

        $cell = new stdClass;
        $cell->attrib = $attr;
        $cell->content = $cont;
        $this->header[] = $cell;
    }

    /**
     * Jump to next row
     *
     * @param array Row attributes
     */
    public function add_row($attr = array())
    {
        $this->rowindex++;
        $this->colindex = 0;
        $this->rows[$this->rowindex] = new stdClass;
        $this->rows[$this->rowindex]->attrib = $attr;
        $this->rows[$this->rowindex]->cells = array();
    }


    /**
     * Build HTML output of the table data
     *
     * @param array Table attributes
     * @return string The final table HTML code
     */
    public function show($attrib = null)
    {
	if (is_array($attrib))
    	    $this->attrib = array_merge($this->attrib, $attrib);
        
	$thead = $tbody = "";

        // include <thead>
        if (!empty($this->header)) {
            $rowcontent = '';
            foreach ($this->header as $c => $col) {
                $rowcontent .= self::tag('td', $col->attrib, $col->content);
            }
            $thead = self::tag('thead', null, self::tag('tr', null, $rowcontent));
        }

        foreach ($this->rows as $r => $row) {
            $rowcontent = '';
            foreach ($row->cells as $c => $col) {
                $rowcontent .= self::tag('td', $col->attrib, $col->content);
            }

            if ($r < $this->rowindex || count($row->cells)) {
                $tbody .= self::tag('tr', $row->attrib, $rowcontent);
            }
        }

        if ($this->attrib['rowsonly']) {
            return $tbody;
        }

        // add <tbody>
        $this->content = $thead . self::tag('tbody', null, $tbody);

        unset($this->attrib['cols'], $this->attrib['rowsonly']);
        return parent::show();
    }
}

?>