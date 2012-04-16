<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_shared.inc                                      |
 |                                                                       |
 | This file is part of the Roundcube PHP suite                          |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | CONTENTS:                                                             |
 |   Shared functions used by Roundcube Framework                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Roundcube shared functions
 *
 * @package Core
 */


/**
 * Similar function as in_array() but case-insensitive
 *
 * @param string $needle    Needle value
 * @param array  $heystack  Array to search in
 *
 * @return boolean True if found, False if not
 */
function in_array_nocase($needle, $haystack)
{
    $needle = mb_strtolower($needle);
    foreach ($haystack as $value) {
        if ($needle === mb_strtolower($value)) {
            return true;
        }
    }

    return false;
}


/**
 * Find out if the string content means true or false
 *
 * @param string $str  Input value
 *
 * @return boolean Boolean value
 */
function get_boolean($str)
{
    $str = strtolower($str);

    return !in_array($str, array('false', '0', 'no', 'off', 'nein', ''), true);
}


/**
 * Parse a human readable string for a number of bytes.
 *
 * @param string $str  Input string
 *
 * @return float Number of bytes
 */
function parse_bytes($str)
{
    if (is_numeric($str)) {
        return floatval($str);
    }

    if (preg_match('/([0-9\.]+)\s*([a-z]*)/i', $str, $regs)) {
        $bytes = floatval($regs[1]);
        switch (strtolower($regs[2])) {
        case 'g':
        case 'gb':
            $bytes *= 1073741824;
            break;
        case 'm':
        case 'mb':
            $bytes *= 1048576;
            break;
        case 'k':
        case 'kb':
            $bytes *= 1024;
            break;
        }
    }

    return floatval($bytes);
}


/**
 * Make sure the string ends with a slash
 */
function slashify($str)
{
  return unslashify($str).'/';
}


/**
 * Remove slash at the end of the string
 */
function unslashify($str)
{
  return preg_replace('/\/$/', '', $str);
}


/**
 * Delete all files within a folder
 *
 * @param string Path to directory
 *
 * @return boolean True on success, False if directory was not found
 */
function clear_directory($dir_path)
{
    $dir = @opendir($dir_path);
    if (!$dir) {
        return false;
    }

    while ($file = readdir($dir)) {
        if (strlen($file) > 2) {
            unlink("$dir_path/$file");
        }
    }

    closedir($dir);

    return true;
}


/**
 * Create a unix timestamp with a specified offset from now.
 *
 * @param string $offset_str  String representation of the offset (e.g. 20min, 5h, 2days)
 * @param int    $factor      Factor to multiply with the offset
 *
 * @return int Unix timestamp
 */
function get_offset_time($offset_str, $factor=1)
{
    if (preg_match('/^([0-9]+)\s*([smhdw])/i', $offset_str, $regs)) {
        $amount = (int)$regs[1];
        $unit   = strtolower($regs[2]);
    }
    else {
        $amount = (int)$offset_str;
        $unit   = 's';
    }

    $ts = mktime();
    switch ($unit) {
    case 'w':
        $amount *= 7;
    case 'd':
        $amount *= 24;
    case 'h':
        $amount *= 60;
    case 'm':
        $amount *= 60;
    case 's':
        $ts += $amount * $factor;
    }

    return $ts;
}


/**
 * Truncate string if it is longer than the allowed length.
 * Replace the middle or the ending part of a string with a placeholder.
 *
 * @param string $str         Input string
 * @param int    $maxlength   Max. length
 * @param string $placeholder Replace removed chars with this
 * @param bool   $ending      Set to True if string should be truncated from the end
 *
 * @return string Abbreviated string
 */
function abbreviate_string($str, $maxlength, $placeholder='...', $ending=false)
{
    $length = mb_strlen($str);

    if ($length > $maxlength) {
        if ($ending) {
            return mb_substr($str, 0, $maxlength) . $placeholder;
        }

        $placeholder_length = mb_strlen($placeholder);
        $first_part_length  = floor(($maxlength - $placeholder_length)/2);
        $second_starting_location = $length - $maxlength + $first_part_length + $placeholder_length;

        $str = mb_substr($str, 0, $first_part_length) . $placeholder . mb_substr($str, $second_starting_location);
    }

    return $str;
}


/**
 * Get all keys from array (recursive).
 *
 * @param array $array  Input array
 *
 * @return array List of array keys
 */
function array_keys_recursive($array)
{
    $keys = array();

    if (!empty($array)) {
        foreach ($array as $key => $child) {
            $keys[] = $key;
            foreach (array_keys_recursive($child) as $val) {
                $keys[] = $val;
            }
        }
    }

    return $keys;
}


/**
 * Remove all non-ascii and non-word chars except ., -, _
 */
function asciiwords($str, $css_id = false, $replace_with = '')
{
    $allowed = 'a-z0-9\_\-' . (!$css_id ? '\.' : '');
    return preg_replace("/[^$allowed]/i", $replace_with, $str);
}


/**
 * Remove single and double quotes from given string
 *
 * @param string Input value
 *
 * @return string Dequoted string
 */
function strip_quotes($str)
{
    return str_replace(array("'", '"'), '', $str);
}


/**
 * Remove new lines characters from given string
 *
 * @param string $str  Input value
 *
 * @return string Stripped string
 */
function strip_newlines($str)
{
    return preg_replace('/[\r\n]/', '', $str);
}


/**
 * Compose a valid representation of name and e-mail address
 *
 * @param string $email  E-mail address
 * @param string $name   Person name
 *
 * @return string Formatted string
 */
function format_email_recipient($email, $name = '')
{
    $email = trim($email);

    if ($name && $name != $email) {
        // Special chars as defined by RFC 822 need to in quoted string (or escaped).
        if (preg_match('/[\(\)\<\>\\\.\[\]@,;:"]/', $name)) {
            $name = '"'.addcslashes($name, '"').'"';
        }

        return "$name <$email>";
    }

    return $email;
}


/**
 * mbstring replacement functions
 */
if (!extension_loaded('mbstring'))
{
    function mb_strlen($str)
    {
        return strlen($str);
    }

    function mb_strtolower($str)
    {
        return strtolower($str);
    }

    function mb_strtoupper($str)
    {
        return strtoupper($str);
    }

    function mb_substr($str, $start, $len=null)
    {
        return substr($str, $start, $len);
    }

    function mb_strpos($haystack, $needle, $offset=0)
    {
        return strpos($haystack, $needle, $offset);
    }

    function mb_strrpos($haystack, $needle, $offset=0)
    {
        return strrpos($haystack, $needle, $offset);
    }
}

/**
 * intl replacement functions
 */

if (!function_exists('idn_to_utf8'))
{
    function idn_to_utf8($domain, $flags=null)
    {
        static $idn, $loaded;

        if (!$loaded) {
            $idn = new Net_IDNA2();
            $loaded = true;
        }

        if ($idn && $domain && preg_match('/(^|\.)xn--/i', $domain)) {
            try {
                $domain = $idn->decode($domain);
            }
            catch (Exception $e) {
            }
        }
        return $domain;
    }
}

if (!function_exists('idn_to_ascii'))
{
    function idn_to_ascii($domain, $flags=null)
    {
        static $idn, $loaded;

        if (!$loaded) {
            $idn = new Net_IDNA2();
            $loaded = true;
        }

        if ($idn && $domain && preg_match('/[^\x20-\x7E]/', $domain)) {
            try {
                $domain = $idn->encode($domain);
            }
            catch (Exception $e) {
            }
        }
        return $domain;
    }
}

/**
 * Use PHP5 autoload for dynamic class loading
 *
 * @todo Make Zend, PEAR etc play with this
 * @todo Make our classes conform to a more straight forward CS.
 */
function rcube_autoload($classname)
{
    $filename = preg_replace(
        array(
            '/MDB2_(.+)/',
            '/Mail_(.+)/',
            '/Net_(.+)/',
            '/Auth_(.+)/',
            '/^html_.+/',
            '/^utf8$/',
        ),
        array(
            'MDB2/\\1',
            'Mail/\\1',
            'Net/\\1',
            'Auth/\\1',
            'html',
            'utf8.class',
        ),
        $classname
    );

    if ($fp = @fopen("$filename.php", 'r', true)) {
        fclose($fp);
        include_once "$filename.php";
        return true;
    }

    return false;
}

/**
 * Local callback function for PEAR errors
 */
function rcube_pear_error($err)
{
    error_log(sprintf("%s (%s): %s",
        $err->getMessage(),
        $err->getCode(),
        $err->getUserinfo()), 0);
}
