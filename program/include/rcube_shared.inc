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
 * Provide details about the client's browser
 *
 * @return array Key-value pairs of browser properties
 */
function rcube_browser()
{
  $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

  $bw['ver'] = 0;
  $bw['win'] = stristr($HTTP_USER_AGENT, 'win');
  $bw['mac'] = stristr($HTTP_USER_AGENT, 'mac');
  $bw['linux'] = stristr($HTTP_USER_AGENT, 'linux');
  $bw['unix']  = stristr($HTTP_USER_AGENT, 'unix');

  $bw['ns4'] = stristr($HTTP_USER_AGENT, 'mozilla/4') && !stristr($HTTP_USER_AGENT, 'msie');
  $bw['ns']  = ($bw['ns4'] || stristr($HTTP_USER_AGENT, 'netscape'));
  $bw['ie']  = stristr($HTTP_USER_AGENT, 'msie');
  $bw['mz']  = stristr($HTTP_USER_AGENT, 'mozilla/5');
  $bw['opera'] = stristr($HTTP_USER_AGENT, 'opera');
  $bw['safari'] = stristr($HTTP_USER_AGENT, 'safari');

  if($bw['ns'])
  {
    $test = eregi("mozilla\/([0-9\.]+)", $HTTP_USER_AGENT, $regs);
    $bw['ver'] = $test ? (float)$regs[1] : 0;
  }
  if($bw['mz'])
  {
    $test = ereg("rv:([0-9\.]+)", $HTTP_USER_AGENT, $regs);
    $bw['ver'] = $test ? (float)$regs[1] : 0;
  }
  if($bw['ie'])
  {
    $test = eregi("msie ([0-9\.]+)", $HTTP_USER_AGENT, $regs);
    $bw['ver'] = $test ? (float)$regs[1] : 0;
  }
  if($bw['opera'])
  {
    $test = eregi("opera ([0-9\.]+)", $HTTP_USER_AGENT, $regs);
    $bw['ver'] = $test ? (float)$regs[1] : 0;
  }

  if(eregi(" ([a-z]{2})-([a-z]{2})", $HTTP_USER_AGENT, $regs))
    $bw['lang'] =  $regs[1];
  else
    $bw['lang'] =  'en';

  $bw['dom'] = ($bw['mz'] || $bw['safari'] || ($bw['ie'] && $bw['ver']>=5) || ($bw['opera'] && $bw['ver']>=7));
  $bw['pngalpha'] = $bw['mz'] || $bw['safari'] || ($bw['ie'] && $bw['ver']>=5.5) ||
                    ($bw['ie'] && $bw['ver']>=5 && $bw['mac']) || ($bw['opera'] && $bw['ver']>=7) ? TRUE : FALSE;

  return $bw;
}


/**
 * Get localized text in the desired language
 *
 * @param mixed Named parameters array or label name
 * @return string Localized text
 */
function rcube_label($attrib)
{
  global $sess_user_lang, $OUTPUT;
  static $sa_text_data, $s_language, $utf8_decode;

  // extract attributes
  if (is_string($attrib))
    $attrib = array('name' => $attrib);
    
  $nr = is_numeric($attrib['nr']) ? $attrib['nr'] : 1;
  $vars = isset($attrib['vars']) ? $attrib['vars'] : '';

  $command_name = !empty($attrib['command']) ? $attrib['command'] : NULL;
  $alias = $attrib['name'] ? $attrib['name'] : ($command_name && $command_label_map[$command_name] ? $command_label_map[$command_name] : '');


  // load localized texts
  if (!$sa_text_data || $s_language != $sess_user_lang)
    {
    $sa_text_data = array();
    
    // get english labels (these should be complete)
    @include(INSTALL_PATH.'program/localization/en_US/labels.inc');
    @include(INSTALL_PATH.'program/localization/en_US/messages.inc');

    if (is_array($labels))
      $sa_text_data = $labels;
    if (is_array($messages))
      $sa_text_data = array_merge($sa_text_data, $messages);
    
    // include user language files
    if ($sess_user_lang!='en' && is_dir(INSTALL_PATH.'program/localization/'.$sess_user_lang))
    {
      include_once(INSTALL_PATH.'program/localization/'.$sess_user_lang.'/labels.inc');
      include_once(INSTALL_PATH.'program/localization/'.$sess_user_lang.'/messages.inc');

      if (is_array($labels))
        $sa_text_data = array_merge($sa_text_data, $labels);
      if (is_array($messages))
        $sa_text_data = array_merge($sa_text_data, $messages);
    }
      
    $s_language = $sess_user_lang;
  }

  // text does not exist
  if (!($text_item = $sa_text_data[$alias]))
  {
    /*
    raise_error(array(
      'code' => 500,
      'type' => 'php',
      'line' => __LINE__,
      'file' => __FILE__,
      'message' => "Missing localized text for '$alias' in '$sess_user_lang'"), TRUE, FALSE);
    */
    return "[$alias]";
  }

  // make text item array 
  $a_text_item = is_array($text_item) ? $text_item : array('single' => $text_item);

  // decide which text to use
  if ($nr==1)
    $text = $a_text_item['single'];
  else if ($nr>0)
    $text = $a_text_item['multiple'];
  else if ($nr==0)
  {
    if ($a_text_item['none'])
      $text = $a_text_item['none'];
    else if ($a_text_item['single'])
      $text = $a_text_item['single'];
    else if ($a_text_item['multiple'])
      $text = $a_text_item['multiple'];
  }

  // default text is single
  if ($text=='')
    $text = $a_text_item['single'];

  // replace vars in text
  if (is_array($attrib['vars']))
  {
    foreach ($attrib['vars'] as $var_key=>$var_value)
      $a_replace_vars[substr($var_key, 0, 1)=='$' ? substr($var_key, 1) : $var_key] = $var_value;
  }

  if ($a_replace_vars)
    $text = preg_replace('/\${?([_a-z]{1}[_a-z0-9]*)}?/ei', '$a_replace_vars["\1"]', $text);

  // remove variables in text which were not available in arg $vars and $nr
  eval("\$text = <<<EOF
$text
EOF;
");

  // format output
  if (($attrib['uppercase'] && strtolower($attrib['uppercase']=='first')) || $attrib['ucfirst'])
    return ucfirst($text);
  else if ($attrib['uppercase'])
    return strtoupper($text);
  else if ($attrib['lowercase'])
    return strtolower($text);

  return $text;
}


/**
 * Send HTTP headers to prevent caching this page
 */
function send_nocacheing_headers()
{
  if (headers_sent())
    return;

  header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
  header("Pragma: no-cache");
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
  
  header("Cache-Control: max-age=0");
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
 * Convert a variable into a javascript object notation
 *
 * @param mixed Input value
 * @return string Serialized JSON string
 */
function json_serialize($var)
{
  if (is_object($var))
    $var = get_object_vars($var);

  if (is_array($var))
  {
    // empty array
    if (!sizeof($var))
      return '[]';
    else
    {
      $keys_arr = array_keys($var);
      $is_assoc = $have_numeric = 0;

      for ($i=0; $i<sizeof($keys_arr); ++$i)
      {
        if (is_numeric($keys_arr[$i]))
          $have_numeric = 1;
        if (!is_numeric($keys_arr[$i]) || $keys_arr[$i] != $i)
          $is_assoc = 1;
        if ($is_assoc && $have_numeric)
          break;
      }
      
      $brackets = $is_assoc ? '{}' : '[]';
      $pairs = array();

      foreach ($var as $key => $value)
      {
        // enclose key with quotes if it is not variable-name conform
        if (!ereg("^[_a-zA-Z]{1}[_a-zA-Z0-9]*$", $key) /* || is_js_reserved_word($key) */)
          $key = "'$key'";

        $pairs[] = sprintf("%s%s", $is_assoc ? "$key:" : '', json_serialize($value));
      }

      return $brackets{0} . implode(',', $pairs) . $brackets{1};
    }
  }
  else if (is_numeric($var) && strval(intval($var)) === strval($var))
    return $var;
  else if (is_bool($var))
    return $var ? '1' : '0';
  else
    return "'".JQ($var)."'";

}

/**
 * Function to convert an array to a javascript array
 * Actually an alias function for json_serialize()
 * @deprecated
 */
function array2js($arr, $type='')
{
  return json_serialize($arr);
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
  foreach ($haystack as $value)
    if (strtolower($needle)===strtolower($value))
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
  if(in_array($str, array('false', '0', 'no', 'nein', ''), TRUE))
    return FALSE;
  else
    return TRUE;
}


/**
 * Parse a human readable string for a number of bytes
 *
 * @param string Input string
 * @return int Number of bytes
 */
function parse_bytes($str)
{
  if (is_numeric($str))
    return intval($str);
    
  if (preg_match('/([0-9]+)([a-z])/i', $str, $regs))
  {
    $bytes = floatval($regs[1]);
    switch (strtolower($regs[2]))
    {
      case 'g':
        $bytes *= 1073741824;
        break;
      case 'm':
        $bytes *= 1048576;
        break;
      case 'k':
        $bytes *= 1024;
        break;
    }
  }

  return intval($bytes);
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
    $str = sprintf($gb>=10 ? "%d GB" : "%.1f GB", $gb);
  }
  else if ($bytes > 1048576)
  {
    $mb = $bytes/1048576;
    $str = sprintf($mb>=10 ? "%d MB" : "%.1f MB", $mb);
  }
  else if ($bytes > 1024)
    $str = sprintf("%d KB",  round($bytes/1024));
  else
    $str = sprintf('%d B', $bytes);

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
  if (strpos($base_url, '/')>7)
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
 * Wrapper function for strlen
 */
function rc_strlen($str)
{
  if (function_exists('mb_strlen'))
    return mb_strlen($str);
  else
    return strlen($str);
}
  
/**
 * Wrapper function for strtolower
 */
function rc_strtolower($str)
{
  if (function_exists('mb_strtolower'))
    return mb_strtolower($str);
  else
    return strtolower($str);
}

/**
 * Wrapper function for substr
 */
function rc_substr($str, $start, $len=null)
{
  if (function_exists('mb_substr'))
    return mb_substr($str, $start, $len);
  else
    return substr($str, $start, $len);
}

/**
 * Wrapper function for strpos
 */
function rc_strpos($haystack, $needle, $offset=0)
{
  if (function_exists('mb_strpos'))
    return mb_strpos($haystack, $needle, $offset);
  else
    return strpos($haystack, $needle, $offset);
}

/**
 * Wrapper function for strrpos
 */
function rc_strrpos($haystack, $needle, $offset=0)
{
  if (function_exists('mb_strrpos'))
    return mb_strrpos($haystack, $needle, $offset);
  else
    return strrpos($haystack, $needle, $offset);
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
  $length = rc_strlen($str);
  $first_part_length = floor($maxlength/2) - rc_strlen($place_holder);
  
  if ($length > $maxlength)
  {
    $second_starting_location = $length - $maxlength + $first_part_length + 1;
    $str = rc_substr($str, 0, $first_part_length) . $place_holder . rc_substr($str, $second_starting_location, $length);
  }

  return $str;
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
 * A method to guess the mime_type of an attachment.
 *
 * @param string $path     Path to the file.
 * @param string $failover Mime type supplied for failover.
 *
 * @return string
 * @author Till Klampaeckel <till@php.net>
 * @see    http://de2.php.net/manual/en/ref.fileinfo.php
 * @see    http://de2.php.net/mime_content_type
 */
function rc_mime_content_type($path, $failover = 'unknown/unknown')
{
    global $CONFIG;

    $mime_magic = $CONFIG['mime_magic'];

    if (function_exists('mime_content_type')) {
        return mime_content_type($path);
    }
    if (!extension_loaded('fileinfo')) { 
        if (!dl('fileinfo.' . PHP_SHLIB_SUFFIX)) {
            return $failover;
        }
    }
    $finfo = finfo_open(FILEINFO_MIME, $mime_magic);
    if (!$finfo) {
        return $failover;
    }
    $mime_type = finfo_file($finfo,$path);
    if (!$mime_type) {
        return $failover;
    }
    finfo_close($finfo);

    return $mime_type;
}

?>