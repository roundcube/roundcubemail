<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_shared.inc                                                      |
 |                                                                       |
 | This file is part of the Roundcube PHP suite                          |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | CONTENTS:                                                             |
 |   Shared functions and classes used in PHP projects                   |
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
 * Send HTTP headers to prevent caching this page
 */
function send_nocacheing_headers()
{
  global $OUTPUT;

  if (headers_sent())
    return;

  header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  // Request browser to disable DNS prefetching (CVE-2010-0464)
  header("X-DNS-Prefetch-Control: off");

  // We need to set the following headers to make downloads work using IE in HTTPS mode.
  if ($OUTPUT->browser->ie && rcube_https_check()) {
    header('Pragma: private');
    header("Cache-Control: private, must-revalidate");
  } else {
    header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
  }
}


/**
 * Send header with expire date 30 days in future
 *
 * @param int Expiration time in seconds
 */
function send_future_expire_header($offset=2600000)
{
  if (headers_sent())
    return;

  header("Expires: ".gmdate("D, d M Y H:i:s", mktime()+$offset)." GMT");
  header("Cache-Control: max-age=$offset");
  header("Pragma: ");
}


/**
 * Similar function as in_array() but case-insensitive
 *
 * @param mixed Needle value
 * @param array Array to search in
 * @return boolean True if found, False if not
 */
function in_array_nocase($needle, $haystack)
{
  $needle = mb_strtolower($needle);
  foreach ($haystack as $value)
    if ($needle===mb_strtolower($value))
      return true;

  return false;
}


/**
 * Find out if the string content means TRUE or FALSE
 *
 * @param string Input value
 * @return boolean Imagine what!
 */
function get_boolean($str)
{
  $str = strtolower($str);
  if (in_array($str, array('false', '0', 'no', 'off', 'nein', ''), TRUE))
    return FALSE;
  else
    return TRUE;
}


/**
 * Parse a human readable string for a number of bytes
 *
 * @param string Input string
 * @return float Number of bytes
 */
function parse_bytes($str)
{
  if (is_numeric($str))
    return floatval($str);

  if (preg_match('/([0-9\.]+)\s*([a-z]*)/i', $str, $regs))
  {
    $bytes = floatval($regs[1]);
    switch (strtolower($regs[2]))
    {
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
 * Create a human readable string for a number of bytes
 *
 * @param int Number of bytes
 * @return string Byte string
 */
function show_bytes($bytes)
{
  if ($bytes >= 1073741824)
  {
    $gb = $bytes/1073741824;
    $str = sprintf($gb>=10 ? "%d " : "%.1f ", $gb) . rcube_label('GB');
  }
  else if ($bytes >= 1048576)
  {
    $mb = $bytes/1048576;
    $str = sprintf($mb>=10 ? "%d " : "%.1f ", $mb) . rcube_label('MB');
  }
  else if ($bytes >= 1024)
    $str = sprintf("%d ",  round($bytes/1024)) . rcube_label('KB');
  else
    $str = sprintf('%d ', $bytes) . rcube_label('B');

  return $str;
}

/**
 * Wrapper function for wordwrap
 */
function rc_wordwrap($string, $width=75, $break="\n", $cut=false)
{
  $para = explode($break, $string);
  $string = '';
  while (count($para)) {
    $line = array_shift($para);
    if ($line[0] == '>') {
      $string .= $line.$break;
      continue;
    }
    $list = explode(' ', $line);
    $len = 0;
    while (count($list)) {
      $line = array_shift($list);
      $l = mb_strlen($line);
      $newlen = $len + $l + ($len ? 1 : 0);

      if ($newlen <= $width) {
        $string .= ($len ? ' ' : '').$line;
        $len += (1 + $l);
      } else {
        if ($l > $width) {
          if ($cut) {
            $start = 0;
            while ($l) {
              $str = mb_substr($line, $start, $width);
              $strlen = mb_strlen($str);
              $string .= ($len ? $break : '').$str;
              $start += $strlen;
              $l -= $strlen;
              $len = $strlen;
            }
          } else {
                $string .= ($len ? $break : '').$line;
            if (count($list)) $string .= $break;
            $len = 0;
          }
        } else {
          $string .= $break.$line;
          $len = $l;
        }
      }
    }
    if (count($para)) $string .= $break;
  }
  return $string;
}

/**
 * Read a specific HTTP request header
 *
 * @access static
 * @param  string $name Header name
 * @return mixed  Header value or null if not available
 */
function rc_request_header($name)
{
  if (function_exists('getallheaders'))
  {
    $hdrs = array_change_key_case(getallheaders(), CASE_UPPER);
    $key  = strtoupper($name);
  }
  else
  {
    $key  = 'HTTP_' . strtoupper(strtr($name, '-', '_'));
    $hdrs = array_change_key_case($_SERVER, CASE_UPPER);
  }

  return $hdrs[$key];
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
 * @return boolean True on success, False if directory was not found
 */
function clear_directory($dir_path)
{
  $dir = @opendir($dir_path);
  if(!$dir) return FALSE;

  while ($file = readdir($dir))
    if (strlen($file)>2)
      unlink("$dir_path/$file");

  closedir($dir);
  return TRUE;
}


/**
 * Create a unix timestamp with a specified offset from now
 *
 * @param string String representation of the offset (e.g. 20min, 5h, 2days)
 * @param int Factor to multiply with the offset
 * @return int Unix timestamp
 */
function get_offset_time($offset_str, $factor=1)
{
  if (preg_match('/^([0-9]+)\s*([smhdw])/i', $offset_str, $regs))
  {
    $amount = (int)$regs[1];
    $unit = strtolower($regs[2]);
  }
  else
  {
    $amount = (int)$offset_str;
    $unit = 's';
  }

  $ts = mktime();
  switch ($unit)
  {
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
 * Truncate string if it is longer than the allowed length
 * Replace the middle or the ending part of a string with a placeholder
 *
 * @param string Input string
 * @param int    Max. length
 * @param string Replace removed chars with this
 * @param bool   Set to True if string should be truncated from the end
 * @return string Abbreviated string
 */
function abbreviate_string($str, $maxlength, $place_holder='...', $ending=false)
{
  $length = mb_strlen($str);

  if ($length > $maxlength)
  {
    if ($ending)
      return mb_substr($str, 0, $maxlength) . $place_holder;

    $place_holder_length = mb_strlen($place_holder);
    $first_part_length = floor(($maxlength - $place_holder_length)/2);
    $second_starting_location = $length - $maxlength + $first_part_length + $place_holder_length;
    $str = mb_substr($str, 0, $first_part_length) . $place_holder . mb_substr($str, $second_starting_location);
  }

  return $str;
}


/**
 * A method to guess the mime_type of an attachment.
 *
 * @param string $path      Path to the file.
 * @param string $name      File name (with suffix)
 * @param string $failover  Mime type supplied for failover.
 * @param string $is_stream Set to True if $path contains file body
 *
 * @return string
 * @author Till Klampaeckel <till@php.net>
 * @see    http://de2.php.net/manual/en/ref.fileinfo.php
 * @see    http://de2.php.net/mime_content_type
 */
function rc_mime_content_type($path, $name, $failover = 'application/octet-stream', $is_stream=false)
{
    $mime_type = null;
    $mime_magic = rcmail::get_instance()->config->get('mime_magic');
    $mime_ext = @include(RCMAIL_CONFIG_DIR . '/mimetypes.php');

    // use file name suffix with hard-coded mime-type map
    if (is_array($mime_ext) && $name) {
        if ($suffix = substr($name, strrpos($name, '.')+1)) {
            $mime_type = $mime_ext[strtolower($suffix)];
        }
    }

    // try fileinfo extension if available
    if (!$mime_type && function_exists('finfo_open')) {
        if ($finfo = finfo_open(FILEINFO_MIME, $mime_magic)) {
            if ($is_stream)
                $mime_type = finfo_buffer($finfo, $path);
            else
                $mime_type = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
    }

    // try PHP's mime_content_type
    if (!$mime_type && !$is_stream && function_exists('mime_content_type')) {
      $mime_type = @mime_content_type($path);
    }

    // fall back to user-submitted string
    if (!$mime_type) {
        $mime_type = $failover;
    }
    else {
        // Sometimes (PHP-5.3?) content-type contains charset definition,
        // Remove it (#1487122) also "charset=binary" is useless
        $mime_type = array_shift(preg_split('/[; ]/', $mime_type));
    }

    return $mime_type;
}


/**
 * Detect image type of the given binary data by checking magic numbers
 *
 * @param string  Binary file content
 * @return string Detected mime-type or jpeg as fallback
 */
function rc_image_content_type($data)
{
    $type = 'jpeg';
    if      (preg_match('/^\x89\x50\x4E\x47/', $data)) $type = 'png';
    else if (preg_match('/^\x47\x49\x46\x38/', $data)) $type = 'gif';
    else if (preg_match('/^\x00\x00\x01\x00/', $data)) $type = 'ico';
//  else if (preg_match('/^\xFF\xD8\xFF\xE0/', $data)) $type = 'jpeg';

    return 'image/' . $type;
}


/**
 * Explode quoted string
 * 
 * @param string Delimiter expression string for preg_match()
 * @param string Input string
 */
function rcube_explode_quoted_string($delimiter, $string)
{
  $result = array();
  $strlen = strlen($string);

  for ($q=$p=$i=0; $i < $strlen; $i++) {
    if ($string[$i] == "\"" && $string[$i-1] != "\\") {
      $q = $q ? false : true;
    } 
    else if (!$q && preg_match("/$delimiter/", $string[$i])) {
      $result[] = substr($string, $p, $i - $p);
      $p = $i + 1;
    }
  }

  $result[] = substr($string, $p);
  return $result;
}


/**
 * Get all keys from array (recursive)
 * 
 * @param array Input array
 * @return array
 */
function array_keys_recursive($array)
{
  $keys = array();

  if (!empty($array))
    foreach ($array as $key => $child) {
      $keys[] = $key;
      foreach (array_keys_recursive($child) as $val)
        $keys[] = $val;
    }
  return $keys;
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

