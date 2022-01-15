<?php

/**
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
 |   Converts plain text to HTML                                         |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
 */

/**
 * Converts plain text to HTML
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_text2html
{
    /** @var string Contains the HTML content after conversion */
    protected $html;

    /** @var string Contains the plain text */
    protected $text;

    /** @var array Configuration */
    protected $config = [
        // non-breaking space
        'space' => "\xC2\xA0",
        // enables format=flowed parser
        'flowed' => false,
        // enables delsp=yes parser
        'delsp' => false,
        // enables wrapping for non-flowed text
        'wrap' => true,
        // line-break tag
        'break' => "<br>\n",
        // prefix and suffix (wrapper element)
        'begin' => '<div class="pre">',
        'end'   => '</div>',
        // enables links replacement
        'links' => true,
        // string replacer class
        'replacer' => 'rcube_string_replacer',
        // prefix and suffix of unwrappable line
        'nobr_start' => '<span style="white-space:nowrap">',
        'nobr_end'   => '</span>',
    ];

    /** @var bool Internal state */
    protected $converted = false;

    /** @var bool Internal no-wrap mode state */
    protected $nowrap = false;


    /**
     * Constructor.
     *
     * If the plain text source string (or file) is supplied, the class
     * will instantiate with that source propagated, all that has
     * to be done it to call get_html().
     *
     * @param string $source    Plain text
     * @param bool   $from_file Indicates $source is a file to pull content from
     * @param array  $config    Class configuration
     */
    function __construct($source = '', $from_file = false, $config = [])
    {
        if (!empty($source)) {
            $this->set_text($source, $from_file);
        }

        if (!empty($config) && is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * Loads source text into memory, either from $source string or a file.
     *
     * @param string $source    Plain text
     * @param bool   $from_file Indicates $source is a file to pull content from
     */
    function set_text($source, $from_file = false)
    {
        if ($from_file && file_exists($source)) {
            $this->text = file_get_contents($source);
        }
        else {
            $this->text = $source;
        }

        $this->converted = false;
    }

    /**
     * Returns the HTML content.
     *
     * @return string HTML content
     */
    function get_html()
    {
        if (!$this->converted) {
            $this->convert();
        }

        return $this->html;
    }

    /**
     * Prints the HTML.
     */
    function print_html()
    {
        print $this->get_html();
    }

    /**
     * Workhorse function that does actual conversion (calls converter() method).
     */
    protected function convert()
    {
        // Convert TXT to HTML
        $this->html      = $this->converter($this->text);
        $this->converted = true;
    }

    /**
     * Workhorse function that does actual conversion.
     *
     * @param string $text Plain text
     *
     * @return string HTML content
     */
    protected function converter($text)
    {
        // make links and email-addresses clickable
        $attribs  = ['link_attribs' => ['rel' => 'noreferrer', 'target' => '_blank']];
        $replacer = new $this->config['replacer']($attribs);

        if ($this->config['flowed']) {
            $delsp = $this->config['delsp'];
            $text  = rcube_mime::unfold_flowed($text, null, $delsp);
        }

        // search for patterns like links and e-mail addresses and replace with tokens
        if ($this->config['links']) {
            $text = $replacer->replace($text);
        }

        // split body into single lines
        $text        = preg_split('/\r?\n/', $text);
        $quote_level = 0;
        $last        = null;
        $length      = 0;

        // wrap quoted lines with <blockquote>
        for ($n = 0, $cnt = count($text); $n < $cnt; $n++) {
            $first  = $text[$n][0] ?? '';

            if ($first == '>' && preg_match('/^(>+ {0,1})+/', $text[$n], $regs)) {
                $q        = substr_count($regs[0], '>');
                $text[$n] = substr($text[$n], strlen($regs[0]));
                $text[$n] = $this->convert_line($text[$n]);
                $_length  = strlen(str_replace(' ', '', $text[$n]));

                if ($q > $quote_level) {
                    if ($last !== null) {
                        $text[$last] .= (!$length ? "\n" : '')
                            . $replacer->get_replacement($replacer->add(
                                str_repeat('<blockquote>', $q - $quote_level)))
                            . $text[$n];

                        unset($text[$n]);
                    }
                    else {
                        $text[$n] = $replacer->get_replacement($replacer->add(
                            str_repeat('<blockquote>', $q - $quote_level))) . $text[$n];

                        $last = $n;
                    }
                }
                else if ($q < $quote_level) {
                    $text[$last] .= (!$length ? "\n" : '')
                        . $replacer->get_replacement($replacer->add(
                            str_repeat('</blockquote>', $quote_level - $q)))
                        . $text[$n];

                    unset($text[$n]);
                }
                else {
                    $last = $n;
                }
            }
            else {
                $text[$n] = $this->convert_line($text[$n]);
                $q        = 0;
                $_length  = strlen(str_replace(' ', '', $text[$n]));

                if ($quote_level > 0) {
                    $text[$last] .= (!$length ? "\n" : '')
                        . $replacer->get_replacement($replacer->add(
                            str_repeat('</blockquote>', $quote_level)))
                        . $text[$n];

                    unset($text[$n]);
                }
                else {
                    $last = $n;
                }
            }

            $quote_level = $q;
            $length      = $_length;
        }

        if ($quote_level > 0) {
            $text[$last] .= $replacer->get_replacement($replacer->add(
                str_repeat('</blockquote>', $quote_level)));
        }

        $text = implode("\n", $text);

        // colorize signature (up to <sig_max_lines> lines)
        $len           = strlen($text);
        $sig_sep       = "--" . $this->config['space'] . "\n";
        $sig_max_lines = rcube::get_instance()->config->get('sig_max_lines', 15);

        while (($sp = strrpos($text, $sig_sep, !empty($sp) ? -$len+$sp-1 : 0)) !== false) {
            if ($sp == 0 || $text[$sp-1] == "\n") {
                // do not touch blocks with more that X lines
                if (substr_count($text, "\n", $sp) < $sig_max_lines) {
                    $text = substr($text, 0, max(0, $sp))
                        .'<span class="sig">'.substr($text, $sp).'</span>';
                }

                break;
            }
        }

        // insert url/mailto links and citation tags
        $text = $replacer->resolve($text);

        // replace line breaks
        $text = str_replace("\n", $this->config['break'], $text);

        return $this->config['begin'] . $text . $this->config['end'];
    }

    /**
     * Converts spaces in line of text
     *
     * @param string $text Plain text
     *
     * @return string Converted text
     */
    protected function convert_line($text)
    {
        static $table;

        if (empty($table)) {
            $table = get_html_translation_table(HTML_SPECIALCHARS);
            unset($table['?']);

            // replace some whitespace characters
            $table["\r"] = '';
            $table["\t"] = '    ';
        }

        // empty line?
        if ($text === '') {
            return $text;
        }

        // skip signature separator
        if ($text == '-- ') {
            return '--' . $this->config['space'];
        }

        if ($this->nowrap) {
            if (!in_array($text[0], [' ', '-', '+', '@'])) {
                $this->nowrap = false;
            }
        }
        else {
            // Detect start of a unified diff
            // TODO: Support normal diffs
            // TODO: Support diff header and comment
            if (
                ($text[0] === '-' && preg_match('/^--- \S+/', $text))
                || ($text[0] === '+' && preg_match('/^\+\+\+ \S+/', $text))
                || ($text[0] === '@' && preg_match('/^@@ [0-9 ,+-]+ @@/', $text))
            ) {
                $this->nowrap = true;
            }
        }

        // replace HTML special and whitespace characters
        $text = strtr($text, $table);

        $nbsp      = $this->config['space'];
        $wrappable = !$this->nowrap && ($this->config['flowed'] || $this->config['wrap']);

        // make the line wrappable
        if ($wrappable) {
            $pos  = 0;
            $diff = 0;
            $last = -2;
            $len  = strlen($nbsp);
            $copy = $text;

            while (($pos = strpos($text, ' ', $pos)) !== false) {
                if (($pos == 0 || $text[$pos-1] == ' ') && $pos - 1 != $last) {
                    $last = $pos;
                    $copy = substr_replace($copy, $nbsp, $pos + $diff, 1);
                    $diff += $len - 1;
                }
                $pos++;
            }

            $text = $copy;
        }
        // make the whole line non-breakable if needed
        else if ($text !== '' && preg_match('/[^a-zA-Z0-9_]/', $text)) {
            // use non-breakable spaces to correctly display
            // trailing/leading spaces and multi-space inside
            $text = str_replace(' ', $nbsp, $text);
            // wrap in nobr element, so it's not wrapped on e.g. - or /
            $text = $this->config['nobr_start'] . $text .  $this->config['nobr_end'];
        }

        return $text;
    }
}
