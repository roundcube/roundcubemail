<?php

/**
 +-----------------------------------------------------------------------+
 | program/include/rcube_mime.php                                        |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   MIME message parsing utilities                                      |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Class for parsing MIME messages
 *
 * @package Mail
 * @author  Thomas Bruederli <roundcube@gmail.com>
 * @author  Aleksander Machniak <alec@alec.pl>
 */
class rcube_mime
{
    private static $default_charset = RCMAIL_CHARSET;


    /**
     * Object constructor.
     */
    function __construct($default_charset = null)
    {
        if ($default_charset) {
            self::$default_charset = $default_charset;
        }
        else {
            self::$default_charset = rcmail::get_instance()->config->get('default_charset', RCMAIL_CHARSET);
        }
    }


    /**
     * Split an address list into a structured array list
     *
     * @param string  $input    Input string
     * @param int     $max      List only this number of addresses
     * @param boolean $decode   Decode address strings
     * @param string  $fallback Fallback charset if none specified
     *
     * @return array  Indexed list of addresses
     */
    static function decode_address_list($input, $max = null, $decode = true, $fallback = null)
    {
        $a   = self::parse_address_list($input, $decode, $fallback);
        $out = array();
        $j   = 0;

        // Special chars as defined by RFC 822 need to in quoted string (or escaped).
        $special_chars = '[\(\)\<\>\\\.\[\]@,;:"]';

        if (!is_array($a))
            return $out;

        foreach ($a as $val) {
            $j++;
            $address = trim($val['address']);
            $name    = trim($val['name']);

            if ($name && $address && $name != $address)
                $string = sprintf('%s <%s>', preg_match("/$special_chars/", $name) ? '"'.addcslashes($name, '"').'"' : $name, $address);
            else if ($address)
                $string = $address;
            else if ($name)
                $string = $name;

            $out[$j] = array(
                'name'   => $name,
                'mailto' => $address,
                'string' => $string
            );

            if ($max && $j==$max)
                break;
        }

        return $out;
    }


    /**
     * Decode a message header value
     *
     * @param string  $input         Header value
     * @param string  $fallback      Fallback charset if none specified
     *
     * @return string Decoded string
     */
    public static function decode_header($input, $fallback = null)
    {
        $str = self::decode_mime_string((string)$input, $fallback);

        return $str;
    }


    /**
     * Decode a mime-encoded string to internal charset
     *
     * @param string $input    Header value
     * @param string $fallback Fallback charset if none specified
     *
     * @return string Decoded string
     */
    public static function decode_mime_string($input, $fallback = null)
    {
        $default_charset = !empty($fallback) ? $fallback : self::$default_charset;

        // rfc: all line breaks or other characters not found
        // in the Base64 Alphabet must be ignored by decoding software
        // delete all blanks between MIME-lines, differently we can
        // receive unnecessary blanks and broken utf-8 symbols
        $input = preg_replace("/\?=\s+=\?/", '?==?', $input);

        // encoded-word regexp
        $re = '/=\?([^?]+)\?([BbQq])\?([^\n]*?)\?=/';

        // Find all RFC2047's encoded words
        if (preg_match_all($re, $input, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            // Initialize variables
            $tmp   = array();
            $out   = '';
            $start = 0;

            foreach ($matches as $idx => $m) {
                $pos      = $m[0][1];
                $charset  = $m[1][0];
                $encoding = $m[2][0];
                $text     = $m[3][0];
                $length   = strlen($m[0][0]);

                // Append everything that is before the text to be decoded
                if ($start != $pos) {
                    $substr = substr($input, $start, $pos-$start);
                    $out   .= rcube_charset_convert($substr, $default_charset);
                    $start  = $pos;
                }
                $start += $length;

                // Per RFC2047, each string part "MUST represent an integral number
                // of characters . A multi-octet character may not be split across
                // adjacent encoded-words." However, some mailers break this, so we
                // try to handle characters spanned across parts anyway by iterating
                // through and aggregating sequential encoded parts with the same
                // character set and encoding, then perform the decoding on the
                // aggregation as a whole.

                $tmp[] = $text;
                if ($next_match = $matches[$idx+1]) {
                    if ($next_match[0][1] == $start
                        && $next_match[1][0] == $charset
                        && $next_match[2][0] == $encoding
                    ) {
                        continue;
                    }
                }

                $count = count($tmp);
                $text  = '';

                // Decode and join encoded-word's chunks
                if ($encoding == 'B' || $encoding == 'b') {
                    // base64 must be decoded a segment at a time
                    for ($i=0; $i<$count; $i++)
                        $text .= base64_decode($tmp[$i]);
                }
                else { //if ($encoding == 'Q' || $encoding == 'q') {
                    // quoted printable can be combined and processed at once
                    for ($i=0; $i<$count; $i++)
                        $text .= $tmp[$i];

                    $text = str_replace('_', ' ', $text);
                    $text = quoted_printable_decode($text);
                }

                $out .= rcube_charset_convert($text, $charset);
                $tmp = array();
            }

            // add the last part of the input string
            if ($start != strlen($input)) {
                $out .= rcube_charset_convert(substr($input, $start), $default_charset);
            }

            // return the results
            return $out;
        }

        // no encoding information, use fallback
        return rcube_charset_convert($input, $default_charset);
    }


    /**
     * Decode a mime part
     *
     * @param string $input    Input string
     * @param string $encoding Part encoding
     * @return string Decoded string
     */
    public static function decode($input, $encoding = '7bit')
    {
        switch (strtolower($encoding)) {
        case 'quoted-printable':
            return quoted_printable_decode($input);
        case 'base64':
            return base64_decode($input);
        case 'x-uuencode':
        case 'x-uue':
        case 'uue':
        case 'uuencode':
            return convert_uudecode($input);
        case '7bit':
        default:
            return $input;
        }
    }


    /**
     * Split RFC822 header string into an associative array
     * @access private
     */
    public static function parse_headers($headers)
    {
        $a_headers = array();
        $headers = preg_replace('/\r?\n(\t| )+/', ' ', $headers);
        $lines = explode("\n", $headers);
        $c = count($lines);

        for ($i=0; $i<$c; $i++) {
            if ($p = strpos($lines[$i], ': ')) {
                $field = strtolower(substr($lines[$i], 0, $p));
                $value = trim(substr($lines[$i], $p+1));
                if (!empty($value))
                    $a_headers[$field] = $value;
            }
        }

        return $a_headers;
    }


    /**
     * @access private
     */
    private static function parse_address_list($str, $decode = true, $fallback = null)
    {
        // remove any newlines and carriage returns before
        $str = preg_replace('/\r?\n(\s|\t)?/', ' ', $str);

        // extract list items, remove comments
        $str = self::explode_header_string(',;', $str, true);
        $result = array();

        // simplified regexp, supporting quoted local part
        $email_rx = '(\S+|("\s*(?:[^"\f\n\r\t\v\b\s]+\s*)+"))@\S+';

        foreach ($str as $key => $val) {
            $name    = '';
            $address = '';
            $val     = trim($val);

            if (preg_match('/(.*)<('.$email_rx.')>$/', $val, $m)) {
                $address = $m[2];
                $name    = trim($m[1]);
            }
            else if (preg_match('/^('.$email_rx.')$/', $val, $m)) {
                $address = $m[1];
                $name    = '';
            }
            else {
                $name = $val;
            }

            // dequote and/or decode name
            if ($name) {
                if ($name[0] == '"' && $name[strlen($name)-1] == '"') {
                    $name = substr($name, 1, -1);
                    $name = stripslashes($name);
                }
                if ($decode) {
                    $name = self::decode_header($name, $fallback);
                }
            }

            if (!$address && $name) {
                $address = $name;
            }

            if ($address) {
                $result[$key] = array('name' => $name, 'address' => $address);
            }
        }

        return $result;
    }


    /**
     * Explodes header (e.g. address-list) string into array of strings
     * using specified separator characters with proper handling
     * of quoted-strings and comments (RFC2822)
     *
     * @param string $separator       String containing separator characters
     * @param string $str             Header string
     * @param bool   $remove_comments Enable to remove comments
     *
     * @return array Header items
     */
    public static function explode_header_string($separator, $str, $remove_comments = false)
    {
        $length  = strlen($str);
        $result  = array();
        $quoted  = false;
        $comment = 0;
        $out     = '';

        for ($i=0; $i<$length; $i++) {
            // we're inside a quoted string
            if ($quoted) {
                if ($str[$i] == '"') {
                    $quoted = false;
                }
                else if ($str[$i] == "\\") {
                    if ($comment <= 0) {
                        $out .= "\\";
                    }
                    $i++;
                }
            }
            // we are inside a comment string
            else if ($comment > 0) {
                if ($str[$i] == ')') {
                    $comment--;
                }
                else if ($str[$i] == '(') {
                    $comment++;
                }
                else if ($str[$i] == "\\") {
                    $i++;
                }
                continue;
            }
            // separator, add to result array
            else if (strpos($separator, $str[$i]) !== false) {
                if ($out) {
                    $result[] = $out;
                }
                $out = '';
                continue;
            }
            // start of quoted string
            else if ($str[$i] == '"') {
                $quoted = true;
            }
            // start of comment
            else if ($remove_comments && $str[$i] == '(') {
                $comment++;
            }

            if ($comment <= 0) {
                $out .= $str[$i];
            }
        }

        if ($out && $comment <= 0) {
            $result[] = $out;
        }

        return $result;
    }


    /**
     * Interpret a format=flowed message body according to RFC 2646
     *
     * @param string  $text Raw body formatted as flowed text
     *
     * @return string Interpreted text with unwrapped lines and stuffed space removed
     */
    public static function unfold_flowed($text)
    {
        $text = preg_split('/\r?\n/', $text);
        $last = -1;
        $q_level = 0;

        foreach ($text as $idx => $line) {
            if ($line[0] == '>' && preg_match('/^(>+\s*)/', $line, $regs)) {
                $q = strlen(str_replace(' ', '', $regs[0]));
                $line = substr($line, strlen($regs[0]));

                if ($q == $q_level && $line
                    && isset($text[$last])
                    && $text[$last][strlen($text[$last])-1] == ' '
                ) {
                    $text[$last] .= $line;
                    unset($text[$idx]);
                }
                else {
                    $last = $idx;
                }
            }
            else {
                $q = 0;
                if ($line == '-- ') {
                    $last = $idx;
                }
                else {
                    // remove space-stuffing
                    $line = preg_replace('/^\s/', '', $line);

                    if (isset($text[$last]) && $line
                        && $text[$last] != '-- '
                        && $text[$last][strlen($text[$last])-1] == ' '
                    ) {
                        $text[$last] .= $line;
                        unset($text[$idx]);
                    }
                    else {
                        $text[$idx] = $line;
                        $last = $idx;
                    }
                }
            }
            $q_level = $q;
        }

        return implode("\r\n", $text);
    }


    /**
     * Wrap the given text to comply with RFC 2646
     *
     * @param string $text Text to wrap
     * @param int $length Length
     *
     * @return string Wrapped text
     */
    public static function format_flowed($text, $length = 72)
    {
        $text = preg_split('/\r?\n/', $text);

        foreach ($text as $idx => $line) {
            if ($line != '-- ') {
                if ($line[0] == '>' && preg_match('/^(>+)/', $line, $regs)) {
                    $prefix = $regs[0];
                    $level = strlen($prefix);
                    $line  = rtrim(substr($line, $level));
                    $line  = $prefix . rc_wordwrap($line, $length - $level - 2, " \r\n$prefix ");
                }
                else if ($line) {
                    $line = rc_wordwrap(rtrim($line), $length - 2, " \r\n");
                    // space-stuffing
                    $line = preg_replace('/(^|\r\n)(From| |>)/', '\\1 \\2', $line);
                }

                $text[$idx] = $line;
            }
        }

        return implode("\r\n", $text);
    }

}
