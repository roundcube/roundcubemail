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
 * Class for HTML code creation
 */
class html
{
    protected $tagname;
    protected $content;
    protected $attrib = [];
    protected $allowed = [];

    public static $doctype = 'xhtml';
    public static $lc_tags = true;
    public static $common_attrib = ['id', 'class', 'style', 'title', 'align', 'unselectable', 'tabindex', 'role'];
    public static $containers = ['iframe', 'div', 'span', 'p', 'h1', 'h2', 'h3', 'ul', 'form', 'textarea', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'style', 'script', 'a'];
    public static $bool_attrib = ['checked', 'multiple', 'disabled', 'selected', 'autofocus', 'readonly', 'required'];

    /**
     * Constructor
     *
     * @param array $attrib Hash array with tag attributes
     */
    public function __construct($attrib = [])
    {
        // @phpstan-ignore-next-line
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

    // STATIC METHODS

    /**
     * Generic method to create a HTML tag
     *
     * @param string       $tagname Tag name
     * @param array|string $attrib  Tag attributes as key/value pairs, or 'class' attribute value
     * @param string       $content Optional Tag content (creates a container tag)
     * @param array        $allowed List with allowed attributes, omit to allow all
     *
     * @return string The XHTML tag
     */
    public static function tag($tagname, $attrib = [], $content = null, $allowed = null)
    {
        if (is_string($attrib)) {
            $attrib = ['class' => $attrib];
        }

        $inline_tags = ['a', 'span', 'img'];
        $suffix = (isset($attrib['nl']) && $content && $attrib['nl'] && !in_array($tagname, $inline_tags)) ? "\n" : '';

        $tagname = self::$lc_tags ? strtolower($tagname) : $tagname;
        if (isset($content) || in_array($tagname, self::$containers)) {
            $suffix = !empty($attrib['noclose']) ? $suffix : '</' . $tagname . '>' . $suffix;
            unset($attrib['noclose'], $attrib['nl']);

            return '<' . $tagname . self::attrib_string($attrib, $allowed) . '>' . $content . $suffix;
        }

        return '<' . $tagname . self::attrib_string($attrib, $allowed) . '>' . $suffix;
    }

    /**
     * Return DOCTYPE tag of specified type
     *
     * @param string $type Document type (html5, xhtml, 'xhtml-trans, xhtml-strict)
     */
    public static function doctype($type)
    {
        $doctypes = [
            'html5' => '<!DOCTYPE html>',
            'xhtml' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml-trans' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml-strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        ];

        if (!empty($doctypes[$type])) {
            self::$doctype = preg_replace('/-\w+$/', '', $type);
            return $doctypes[$type];
        }

        return '';
    }

    /**
     * Derived method for <div> containers
     *
     * @param mixed  $attr Hash array with tag attributes or string with class name
     * @param string $cont Div content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function div($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        return self::tag('div', $attr, $cont, array_merge(self::$common_attrib, ['onclick']));
    }

    /**
     * Derived method for <p> blocks
     *
     * @param mixed  $attr Hash array with tag attributes or string with class name
     * @param string $cont Paragraph content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function p($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        return self::tag('p', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derived method to create <img />
     *
     * @param string|array $attr Hash array with tag attributes or string with image source (src)
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function img($attr = null)
    {
        if (is_string($attr)) {
            $attr = ['src' => $attr];
        }

        $allowed = ['src', 'alt', 'width', 'height', 'border', 'usemap', 'onclick', 'onerror', 'onload'];

        return self::tag('img', $attr + ['alt' => ''], null, array_merge(self::$common_attrib, $allowed));
    }

    /**
     * Derived method for link tags
     *
     * @param string|array $attr Hash array with tag attributes or string with link location (href)
     * @param string       $cont Link content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function a($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = ['href' => $attr];
        }

        $allowed = ['href', 'target', 'name', 'rel', 'onclick', 'onmouseover', 'onmouseout', 'onmousedown', 'onmouseup'];

        return self::tag('a', $attr, $cont, array_merge(self::$common_attrib, $allowed));
    }

    /**
     * Derived method for inline span tags
     *
     * @param string|array $attr Hash array with tag attributes or string with class name
     * @param string       $cont Tag content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function span($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = ['class' => $attr];
        }

        return self::tag('span', $attr, $cont, self::$common_attrib);
    }

    /**
     * Derived method for form element labels
     *
     * @param string|array $attr Hash array with tag attributes or string with 'for' attrib
     * @param string       $cont Tag content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function label($attr, $cont)
    {
        if (is_string($attr)) {
            $attr = ['for' => $attr];
        }

        return self::tag('label', $attr, $cont, array_merge(self::$common_attrib, ['for', 'onkeypress']));
    }

    /**
     * Derived method to create <iframe></iframe>
     *
     * @param string|array $attr Hash array with tag attributes or string with frame source (src)
     * @param string       $cont Tag content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function iframe($attr = null, $cont = null)
    {
        if (is_string($attr)) {
            $attr = ['src' => $attr];
        }

        $allowed = ['src', 'name', 'width', 'height', 'border', 'frameborder', 'onload', 'allowfullscreen'];

        return self::tag('iframe', $attr, $cont, array_merge(self::$common_attrib, $allowed));
    }

    /**
     * Derived method to create <script> tags
     *
     * @param string|array $attr Hash array with tag attributes or string with script source (src)
     * @param string       $cont Javascript code to be placed as tag content
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function script($attr, $cont = null)
    {
        if (is_string($attr)) {
            $attr = ['src' => $attr];
        }

        if ($cont) {
            if (self::$doctype == 'xhtml') {
                $cont = "/* <![CDATA[ */\n{$cont}\n/* ]]> */";
            }

            $cont = "\n{$cont}\n";
        }

        if (self::$doctype == 'xhtml') {
            $attr += ['type' => 'text/javascript'];
        }

        return self::tag('script', $attr + ['nl' => true], $cont,
            array_merge(self::$common_attrib, ['src', 'type', 'charset']));
    }

    /**
     * Derived method for line breaks
     *
     * @param array $attrib Associative array with tag attributes
     *
     * @return string HTML code
     *
     * @see html::tag()
     */
    public static function br($attrib = [])
    {
        return self::tag('br', $attrib);
    }

    /**
     * Create string with attributes
     *
     * @param array $attrib  Associative array with tag attributes
     * @param array $allowed List of allowed attributes
     *
     * @return string Valid attribute string
     */
    public static function attrib_string($attrib = [], $allowed = null)
    {
        if (empty($attrib)) {
            return '';
        }

        $allowed_f = array_flip((array) $allowed);
        $attrib_arr = [];

        foreach ($attrib as $key => $value) {
            // skip size if not numeric
            if ($key == 'size' && !is_numeric($value)) {
                continue;
            }

            // ignore "internal" or empty attributes
            if ($key == 'nl' || $value === null) {
                continue;
            }

            // ignore not allowed attributes, except aria-* and data-*
            if (!empty($allowed)) {
                $is_data_attr = @substr_compare($key, 'data-', 0, 5) === 0;
                $is_aria_attr = @substr_compare($key, 'aria-', 0, 5) === 0;
                if (!$is_aria_attr && !$is_data_attr && !isset($allowed_f[$key])) {
                    continue;
                }
            }

            // skip empty eventhandlers
            if (preg_match('/^on[a-z]+/', $key) && !$value) {
                continue;
            }

            // attributes with no value
            if (in_array($key, self::$bool_attrib)) {
                if (!empty($value)) {
                    $value = $key;
                    if (self::$doctype == 'xhtml') {
                        $value .= '="' . $value . '"';
                    }

                    $attrib_arr[] = $value;
                }
            } else {
                $attrib_arr[] = $key . '="' . self::quote((string) $value) . '"';
            }
        }

        return count($attrib_arr) ? ' ' . implode(' ', $attrib_arr) : '';
    }

    /**
     * Convert a HTML attribute string attributes to an associative array (name => value)
     *
     * @param string $str Input string
     *
     * @return array Key-value pairs of parsed attributes
     */
    public static function parse_attrib_string($str)
    {
        $attrib = [];
        $html = '<html>'
            . '<head><meta http-equiv="Content-Type" content="text/html; charset=' . RCUBE_CHARSET . '" /></head>'
            . '<body><div ' . rtrim($str, '/ ') . ' /></body>'
            . '</html>';

        $document = new \DOMDocument('1.0', RCUBE_CHARSET);
        @$document->loadHTML($html);

        if ($node = $document->getElementsByTagName('div')->item(0)) {
            foreach ($node->attributes as $name => $attr) {
                $attrib[strtolower($name)] = $attr->nodeValue;
            }
        }

        return $attrib;
    }

    /**
     * Replacing specials characters in html attribute value
     *
     * @param string $str Input string
     *
     * @return string The quoted string
     */
    public static function quote(string $str)
    {
        return @htmlspecialchars($str, \ENT_COMPAT | \ENT_SUBSTITUTE, RCUBE_CHARSET);
    }
}
