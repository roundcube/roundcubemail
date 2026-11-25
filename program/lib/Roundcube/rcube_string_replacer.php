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
 |   Handle string replacements based on preg_replace_callback           |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Helper class for string replacements based on preg_replace_callback
 */
class rcube_string_replacer
{
    public $pattern;
    public $mailto_pattern;
    public $mailto_uri_pattern;
    public $link_pattern;
    public $linkref_index;
    public $linkref_pattern;

    protected $values = [];
    protected $options = [];
    protected $linkrefs = [];
    protected $urls = [];
    protected $link_texts = [];
    protected $noword = '[^\w@.#-]';

    /**
     * Object constructor
     *
     * @param array $options Configuration options
     */
    public function __construct($options = [])
    {
        // Create hard-to-guess replacement string
        $uniq_ident = sprintf('%010d%010d', mt_rand(), mt_rand());
        $this->pattern = '/##' . $uniq_ident . '##(\d+)##/';

        // Simplified domain expression for UTF8 characters handling
        // Support unicode/punycode in top-level domain part
        $utf_domain = '[^?&@"\'\/()<>\s\r\t\n#:,]+\.?([^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]{2,}|xn--[a-zA-Z0-9]{2,})';

        $ip_address = '([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})|\[[a-f0-9A-F:]+\]';

        // Supported link prefixes
        $link_prefix = "[\\w]+:\\/\\/|{$this->noword}[Ww][Ww][Ww]\\.|^[Ww][Ww][Ww]\\.";

        $this->options = $options;
        $this->linkref_index = '/\[([^<>\]#]+)\](:?\s*' . substr($this->pattern, 1, -1) . ')/';
        $this->linkref_pattern = '/\[([^<>\]#]+)\]/';
        $this->link_pattern = "/({$link_prefix})(({$ip_address}|{$utf_domain})(:[0-9]{1,5})?([\\/?#][^\\s<>\"']*[^\\s.:;,<>\"'])*[\\/?#]?)/";
        $email = '[-\w!\#$%&*+~\/^`|{}=]+(?:\.[-\w!\#$%&*+~\/^`|{}=]+)*' // local-part
            . '@' . $utf_domain;                                     // domain-part

        // E-mail address, a query after it (e.g. ?subject=test) is not a part of it
        $this->mailto_pattern = '/(' . $email . ')/';
        // mailto: URI with an optional query, the query ends like a link
        // (it is searched after links, so the address or the query may hold their replacement strings)
        $this->mailto_uri_pattern = '/\b(?<mailto>[Mm][Aa][Ii][Ll][Tt][Oo]:)'
            . '(?<href>' . $email . '(\?[^\s<>"\']*[^\s.:;,<>"\'])?)/';
    }

    /**
     * Add a string to the internal list
     *
     * @param string $str String value
     *
     * @return int Index of value for retrieval
     */
    public function add($str)
    {
        $i = count($this->values);
        $this->values[$i] = $str;
        return $i;
    }

    /**
     * Build replacement string
     *
     * @param string|int $i Replacement index
     *
     * @return string Replacement string
     */
    public function get_replacement($i)
    {
        return str_replace('(\d+)', $i, substr($this->pattern, 1, -1));
    }

    /**
     * Callback function used to build HTML links around URL strings
     *
     * @param array $matches Matches result from preg_replace_callback
     *
     * @return string A valid link for recognized schemes, otherwise the unmodified URL
     */
    protected function link_callback($matches)
    {
        $i = -1;
        $scheme = strtolower($matches[1]);
        $url_prefix = '';
        $prefix = '';

        if (preg_match('!^(http|ftp|file)s?://!i', $scheme)) {
            $url = $matches[1] . $matches[2];
        } elseif (preg_match("/^({$this->noword}*)(www\\.)$/i", $matches[1], $m)) {
            $url = $m[2] . $matches[2];
            $url_prefix = 'http://';
            $prefix = $m[1];
        }

        if (!empty($url)) {
            $suffix = $this->parse_url_brackets($url);
            $attrib = isset($this->options['link_attribs']) ? (array) $this->options['link_attribs'] : [];
            $attrib['href'] = $url_prefix . $url;

            $i = $this->add(html::a($attrib, rcube::Q($url)) . $suffix);
            $this->urls[$i] = $attrib['href'];
            $this->link_texts[$i] = substr($matches[0], strlen($prefix));
        }

        return $i >= 0 ? $prefix . $this->get_replacement($i) : $matches[0];
    }

    /**
     * Callback to add an entry to the link index
     *
     * @param array $matches Matches result from preg_replace_callback with PREG_OFFSET_CAPTURE
     *
     * @return string Replacement string
     */
    protected function linkref_addindex($matches)
    {
        $key = $matches[1][0];

        if (!isset($this->linkrefs[$key])) {
            $this->linkrefs[$key] = [];
        }

        // Store the reference and its occurrence position
        $this->linkrefs[$key][] = [
            $this->urls[$matches[3][0]] ?? null,
            $matches[0][1],
        ];

        return $this->get_replacement($this->add('[' . $key . ']')) . $matches[2][0];
    }

    /**
     * Callback to replace link references with real links
     *
     * @param array $matches Matches result from preg_replace_callback with PREG_OFFSET_CAPTURE
     *
     * @return string Replacement string
     */
    protected function linkref_callback($matches)
    {
        $i = 0;
        $key = $matches[1][0];

        if (!empty($this->linkrefs[$key])) {
            $attrib = isset($this->options['link_attribs']) ? (array) $this->options['link_attribs'] : [];

            foreach ($this->linkrefs[$key] as $linkref) {
                $attrib['href'] = $linkref[0];
                if ($linkref[1] >= $matches[1][1]) {
                    break;
                }
            }

            $i = $this->add(html::a($attrib, rcube::Q($matches[1][0])));
        }

        return $i > 0 ? '[' . $this->get_replacement($i) . ']' : $matches[0][0];
    }

    /**
     * Callback function used to build mailto: links around e-mail strings
     *
     * @param array $matches Matches result from preg_replace_callback: $matches[1] is the e-mail
     *                       address with an optional query, $matches['mailto'] is set for mailto: URIs
     *
     * @return string Replacement string
     */
    protected function mailto_callback($matches)
    {
        $href = $matches[1];
        $suffix = $this->parse_url_brackets($href);
        $text = ($matches['mailto'] ?? '') . $href;
        $i = $this->add(html::a('mailto:' . $href, rcube::Q($text)) . $suffix);

        return $i >= 0 ? $this->get_replacement($i) : '';
    }

    /**
     * Callback function used to build mailto: links around mailto: URIs,
     * a link found in the URI before is a part of it
     *
     * @param array $matches Matches result from preg_replace_callback
     *
     * @return string Replacement string
     */
    protected function mailto_uri_callback($matches)
    {
        // put back the original text of the links found in the URI
        $text = preg_replace_callback($this->pattern, function ($m) {
            return $this->link_texts[$m[1]] ?? $m[0];
        }, $matches['href']);

        // the URI ends at an unpaired bracket, search the rest for links again
        $href = $text;
        $this->parse_url_brackets($href);
        $suffix = preg_replace_callback($this->link_pattern, [$this, 'link_callback'], substr($text, strlen($href)));

        $uri = $matches['mailto'] . $href;
        $result = $this->mailto_callback([$uri, $href, 'mailto' => $matches['mailto']]);

        // the e-mail address was not accepted, keep the links found in the URI
        return $result === $uri ? $matches[0] : $result . $suffix;
    }

    /**
     * Look up the index from the preg_replace matches array
     * and return the substitution value.
     *
     * @param array $matches Matches result from preg_replace_callback
     *
     * @return string Value at index $matches[1]
     */
    protected function replace_callback($matches)
    {
        return $this->values[$matches[1]] ?? null;
    }

    /**
     * Replace all defined (link|mailto) patterns with replacement string
     *
     * @param string $str Text
     *
     * @return string Text
     */
    public function replace($str)
    {
        // @phpstan-ignore-next-line
        if (!is_string($str)) {
            return '';
        }

        // search for patterns like links and e-mail addresses
        $str = preg_replace_callback($this->link_pattern, [$this, 'link_callback'], $str);

        // search for mailto: URIs, a link in the query of a mailto: URI is a part of it
        $str = preg_replace_callback($this->mailto_uri_pattern, [$this, 'mailto_uri_callback'], $str);

        // search for e-mail addresses only between the replacement strings,
        // so that an e-mail address never takes in (a part of) a replacement string
        $parts = preg_split($this->pattern, $str, -1, \PREG_SPLIT_DELIM_CAPTURE);
        $str = '';

        foreach ($parts as $idx => $part) {
            $str .= $idx % 2 ? $this->get_replacement($part)
                : preg_replace_callback($this->mailto_pattern, [$this, 'mailto_callback'], $part);
        }

        // resolve link references
        $str = preg_replace_callback($this->linkref_index,
            [$this, 'linkref_addindex'], $str, -1, $count, \PREG_OFFSET_CAPTURE
        );
        $str = preg_replace_callback($this->linkref_pattern,
            [$this, 'linkref_callback'], $str, -1, $count, \PREG_OFFSET_CAPTURE
        );

        return $str;
    }

    /**
     * Replace substituted strings with original values
     *
     * @param string $str Text
     *
     * @return string Text
     */
    public function resolve($str)
    {
        return preg_replace_callback($this->pattern, [$this, 'replace_callback'], $str);
    }

    /**
     * Fixes bracket characters in URL handling
     *
     * @param string &$url URL
     *
     * @return string Suffix (the rest of the URL input)
     */
    protected static function parse_url_brackets(&$url)
    {
        // #1487672: special handling of square brackets,
        // URL regexp allows [] characters in URL, for example:
        // "http://example.com/?a[b]=c". However we need to handle
        // properly situation when a bracket is placed at the end
        // of the link e.g. "[http://example.com]"
        // Yes, this is not perfect handles correctly only paired characters
        // but it should work for common cases

        $suffix = '';

        if (preg_match('/(\[|\])/', $url)) {
            $in = false;
            for ($i = 0, $len = strlen($url); $i < $len; $i++) {
                if ($url[$i] == '[') {
                    if ($in) {
                        break;
                    }
                    $in = true;
                } elseif ($url[$i] == ']') {
                    if (!$in) {
                        break;
                    }
                    $in = false;
                }
            }

            if ($i < $len) {
                $suffix = substr($url, $i);
                $url = substr($url, 0, $i);
            }
        }

        // Do the same for parentheses
        if (preg_match('/(\(|\))/', $url)) {
            $in = false;
            for ($i = 0, $len = strlen($url); $i < $len; $i++) {
                if ($url[$i] == '(') {
                    if ($in) {
                        break;
                    }
                    $in = true;
                } elseif ($url[$i] == ')') {
                    if (!$in) {
                        break;
                    }
                    $in = false;
                }
            }

            if ($i < $len) {
                $suffix = substr($url, $i);
                $url = substr($url, 0, $i);
            }
        }

        return $suffix;
    }
}
