<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_shared.inc                                                      |
 |                                                                       |
 | This file is part of the RoundCube PHP suite                          |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
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
 * RoundCube shared functions
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
  header("Cache-Control: private, no-cache, must-revalidate, post-check=0, pre-check=0");
  header("Pragma: no-cache");
  // Request browser to disable DNS prefetching (CVE-2010-0464)
  header("X-DNS-Prefetch-Control: off");

  // We need to set the following headers to make downloads work using IE in HTTPS mode.
  if ($OUTPUT->browser->ie && rcube_https_check()) {
    header('Pragma: private');
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
 * Check request for If-Modified-Since and send an according response.
 * This will terminate the current script if headers match the given values
 *
 * @param int Modified date as unix timestamp
 * @param string Etag value for caching
 */
function send_modified_header($mdate, $etag=null, $skip_check=false)
{
  if (headers_sent())
    return;
    
  $iscached = false;
  $etag = $etag ? "\"$etag\"" : null;

  if (!$skip_check)
  {
    if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mdate)
      $iscached = true;
  
    if ($etag)
      $iscached = ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag);
  }
  
  if ($iscached)
    header("HTTP/1.x 304 Not Modified");
  else
    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $mdate)." GMT");
  
  header("Cache-Control: private, must-revalidate, max-age=0");
  header("Expires: ");
  header("Pragma: ");
  
  if ($etag)
    header("Etag: $etag");
  
  if ($iscached)
    {
    ob_end_clean();
    exit;
    }
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
  if (in_array($str, array('false', '0', 'no', 'nein', ''), TRUE))
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
  if ($bytes > 1073741824)
  {
    $gb = $bytes/1073741824;
    $str = sprintf($gb>=10 ? "%d " : "%.1f ", $gb) . rcube_label('GB');
  }
  else if ($bytes > 1048576)
  {
    $mb = $bytes/1048576;
    $str = sprintf($mb>=10 ? "%d " : "%.1f ", $mb) . rcube_label('MB');
  }
  else if ($bytes > 1024)
    $str = sprintf("%d ",  round($bytes/1024)) . rcube_label('KB');
  else
    $str = sprintf('%d ', $bytes) . rcube_label('B');

  return $str;
}


/**
 * Convert paths like ../xxx to an absolute path using a base url
 *
 * @param string Relative path
 * @param string Base URL
 * @return string Absolute URL
 */
function make_absolute_url($path, $base_url)
{
  $host_url = $base_url;
  $abs_path = $path;
  
  // check if path is an absolute URL
  if (preg_match('/^[fhtps]+:\/\//', $path))
    return $path;

  // cut base_url to the last directory
  if (strrpos($base_url, '/')>7)
  {
    $host_url = substr($base_url, 0, strpos($base_url, '/'));
    $base_url = substr($base_url, 0, strrpos($base_url, '/'));
  }

  // $path is absolute
  if ($path{0}=='/')
    $abs_path = $host_url.$path;
  else
  {
    // strip './' because its the same as ''
    $path = preg_replace('/^\.\//', '', $path);

    if (preg_match_all('/\.\.\//', $path, $matches, PREG_SET_ORDER))
      foreach ($matches as $a_match)
      {
        if (strrpos($base_url, '/'))
          $base_url = substr($base_url, 0, strrpos($base_url, '/'));
        
        $path = substr($path, 3);
      }

    $abs_path = $base_url.'/'.$path;
  }
    
  return $abs_path;
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
 * Replace the middle part of a string with ...
 * if it is longer than the allowed length
 *
 * @param string Input string
 * @param int    Max. length
 * @param string Replace removed chars with this
 * @return string Abbreviated string
 */
function abbreviate_string($str, $maxlength, $place_holder='...')
{
  $length = mb_strlen($str);
  
  if ($length > $maxlength)
  {
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
    $suffix = $name ? substr($name, strrpos($name, '.')+1) : '*';

    // use file name suffix with hard-coded mime-type map
    if (is_array($mime_ext)) {
        $mime_type = $mime_ext[$suffix];
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

    return $mime_type;
}

/**
 * A method to guess encoding of a string.
 *
 * @param string $string     	String.
 * @param string $failover 	Default result for failover.
 *
 * @return string
 */
function rc_detect_encoding($string, $failover='')
{
    if (!function_exists('mb_detect_encoding')) {
        return $failover;
    }

    // FIXME: the order is important, because sometimes 
    // iso string is detected as euc-jp and etc.
    $enc = array(
      'UTF-8', 'SJIS', 'BIG5', 'GB2312',
      'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4',
      'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9',
      'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
      'WINDOWS-1252', 'WINDOWS-1251', 'EUC-JP', 'EUC-TW', 'KOI8-R', 
      'ISO-2022-KR', 'ISO-2022-JP'
    );

    $result = mb_detect_encoding($string, join(',', $enc));

    return $result ? $result : $failover;
}

/**
 * Removes non-unicode characters from input
 *
 * @param mixed $input String or array.
 * @return string
 */
function rc_utf8_clean($input)
{
  // handle input of type array
  if (is_array($input)) {
    foreach ($input as $idx => $val)
      $input[$idx] = rc_utf8_clean($val);
    return $input;
  }
  
  if (!is_string($input) || $input == '')
    return $input;

  // iconv/mbstring are much faster (especially with long strings)
  if (function_exists('mb_convert_encoding') && ($res = mb_convert_encoding($input, 'UTF-8', 'UTF-8')) !== false)
    return $res;

  if (function_exists('iconv') && ($res = @iconv('UTF-8', 'UTF-8//IGNORE', $input)) !== false)
    return $res;

  $regexp = '/^('.
//    '[\x00-\x7F]'.                                  // UTF8-1
    '|[\xC2-\xDF][\x80-\xBF]'.                      // UTF8-2
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.                  // UTF8-3
    '|[\xE1-\xEC][\x80-\xBF][\x80-\xBF]'.           // UTF8-3
    '|\xED[\x80-\x9F][\x80-\xBF]'.                  // UTF8-3
    '|[\xEE-\xEF][\x80-\xBF][\x80-\xBF]'.           // UTF8-3
    '|\xF0[\x90-\xBF][\x80-\xBF][\x80-\xBF]'.       // UTF8-4
    '|[\xF1-\xF3][\x80-\xBF][\x80-\xBF][\x80-\xBF]'.// UTF8-4
    '|\xF4[\x80-\x8F][\x80-\xBF][\x80-\xBF]'.       // UTF8-4
    ')$/';
  
  $seq = '';
  $out = '';

  for ($i = 0, $len = strlen($input); $i < $len; $i++) {
    $chr = $input[$i];
    $ord = ord($chr);
    // 1-byte character
    if ($ord <= 0x7F) {
      if ($seq)
        $out .= preg_match($regexp, $seq) ? $seq : '';
      $seq = '';
      $out .= $chr;
    // first (or second) byte of multibyte sequence
    } else if ($ord >= 0xC0) {
      if (strlen($seq)>1) {
	$out .= preg_match($regexp, $seq) ? $seq : '';
        $seq = '';
      } else if ($seq && ord($seq) < 0xC0) {
        $seq = '';
      }
      $seq .= $chr;
    // next byte of multibyte sequence
    } else if ($seq) {
      $seq .= $chr;
    }
  }

  if ($seq)
    $out .= preg_match($regexp, $seq) ? $seq : '';

  return $out;
}


/**
 * Convert a variable into a javascript object notation
 *
 * @param mixed Input value
 * @return string Serialized JSON string
 */
function json_serialize($input)
{
  $input = rc_utf8_clean($input);

  // sometimes even using rc_utf8_clean() the input contains invalid UTF-8 sequences
  // that's why we have @ here
  return @json_encode($input);
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
      if ($children = array_keys_recursive($child))
        $keys = array_merge($keys, $children);
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

