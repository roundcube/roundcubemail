<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide basic functions for the webmail package                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * RoundCube Webmail common functions
 *
 * @package Core
 * @author Thomas Bruederli <roundcube@gmail.com>
 */

require_once('lib/utf7.inc');
require_once('include/rcube_shared.inc');

// fallback if not PHP modules are available
@include_once('lib/des.inc');
@include_once('lib/utf8.class.php');

// define constannts for input reading
define('RCUBE_INPUT_GET', 0x0101);
define('RCUBE_INPUT_POST', 0x0102);
define('RCUBE_INPUT_GPC', 0x0103);



/**
 * Return correct name for a specific database table
 *
 * @param string Table name
 * @return string Translated table name
 */
function get_table_name($table)
  {
  global $CONFIG;

  // return table name if configured
  $config_key = 'db_table_'.$table;

  if (strlen($CONFIG[$config_key]))
    return $CONFIG[$config_key];

  return $table;
  }


/**
 * Return correct name for a specific database sequence
 * (used for Postgres only)
 *
 * @param string Secuence name
 * @return string Translated sequence name
 */
function get_sequence_name($sequence)
  {
  // return table name if configured
  $config_key = 'db_sequence_'.$sequence;
  $opt = rcmail::get_instance()->config->get($config_key);

  if (!empty($opt))
    return $opt;
    
  return $sequence;
  }


/**
 * Get localized text in the desired language
 * It's a global wrapper for rcmail::gettext()
 *
 * @param mixed Named parameters array or label name
 * @return string Localized text
 * @see rcmail::gettext()
 */
function rcube_label($p, $domain=null)
{
  return rcmail::get_instance()->gettext($p, $domain);
}


/**
 * Overwrite action variable
 *
 * @param string New action value
 */
function rcmail_overwrite_action($action)
  {
  $app = rcmail::get_instance();
  $app->action = $action;
  $app->output->set_env('action', $action);
  }


/**
 * Compose an URL for a specific action
 *
 * @param string  Request action
 * @param array   More URL parameters
 * @param string  Request task (omit if the same)
 * @return The application URL
 */
function rcmail_url($action, $p=array(), $task=null)
{
  $app = rcmail::get_instance();
  return $app->url((array)$p + array('_action' => $action, 'task' => $task));
}


/**
 * Garbage collector function for temp files.
 * Remove temp files older than two days
 */
function rcmail_temp_gc()
  {
  $rcmail = rcmail::get_instance();

  $tmp = unslashify($rcmail->config->get('temp_dir'));
  $expire = mktime() - 172800;  // expire in 48 hours

  if ($dir = opendir($tmp))
    {
    while (($fname = readdir($dir)) !== false)
      {
      if ($fname{0} == '.')
        continue;

      if (filemtime($tmp.'/'.$fname) < $expire)
        @unlink($tmp.'/'.$fname);
      }

    closedir($dir);
    }
  }


/**
 * Garbage collector for cache entries.
 * Remove all expired message cache records
 */
function rcmail_cache_gc()
  {
  $rcmail = rcmail::get_instance();
  $db = $rcmail->get_dbh();
  
  // get target timestamp
  $ts = get_offset_time($rcmail->config->get('message_cache_lifetime', '30d'), -1);
  
  $db->query("DELETE FROM ".get_table_name('messages')."
             WHERE  created < " . $db->fromunixtime($ts));

  $db->query("DELETE FROM ".get_table_name('cache')."
              WHERE  created < " . $db->fromunixtime($ts));
  }


/**
 * Convert a string from one charset to another.
 * Uses mbstring and iconv functions if possible
 *
 * @param  string Input string
 * @param  string Suspected charset of the input string
 * @param  string Target charset to convert to; defaults to RCMAIL_CHARSET
 * @return Converted string
 */
function rcube_charset_convert($str, $from, $to=NULL)
  {
  static $mbstring_loaded = null;
  static $mbstring_list = null;
  static $convert_warning = false;
  static $conv = null;
  
  $error = false;

  $to = empty($to) ? $to = strtoupper(RCMAIL_CHARSET) : rcube_parse_charset($to);
  $from = rcube_parse_charset($from);

  if ($from == $to || empty($str) || empty($from))
    return $str;

  // convert charset using iconv module  
  if (function_exists('iconv') && $from != 'UTF-7' && $to != 'UTF-7') {
    $_iconv = iconv($from, $to . '//IGNORE', $str);
    if ($_iconv !== false) {
        return $_iconv;
    }
  }

  if (is_null($mbstring_loaded))
    $mbstring_loaded = extension_loaded('mbstring');
    
  // convert charset using mbstring module
  if ($mbstring_loaded) {
    $aliases['WINDOWS-1257'] = 'ISO-8859-13';
    
    if (is_null($mbstring_list)) {
      $mbstring_list = mb_list_encodings();
      $mbstring_list = array_map('strtoupper', $mbstring_list);
    }

    $mb_from = $aliases[$from] ? $aliases[$from] : $from;
    $mb_to = $aliases[$to] ? $aliases[$to] : $to;
    
    // return if encoding found, string matches encoding and convert succeeded
    if (in_array($mb_from, $mbstring_list) && in_array($mb_to, $mbstring_list)) {
      if (mb_check_encoding($str, $mb_from) && ($out = mb_convert_encoding($str, $mb_to, $mb_from)))
        return $out;
    }
  }

  // convert charset using bundled classes/functions
  if ($to == 'UTF-8') {
    if ($from == 'UTF7-IMAP') {
      if ($_str = utf7_to_utf8($str))
        return $_str;
    }
    else if ($from == 'UTF-7') {
      if ($_str = rcube_utf7_to_utf8($str))
        return $_str;
    }
    else if (($from == 'ISO-8859-1') && function_exists('utf8_encode')) {
      return utf8_encode($str);
    }
    else if (class_exists('utf8')) {
      if (!$conv)
        $conv = new utf8($from);
      else
        $conv->loadCharset($from);

      if($_str = $conv->strToUtf8($str))
        return $_str;
    }
    $error = true;
  }
  
  // encode string for output
  if ($from == 'UTF-8') {
    // @TODO: we need a function for UTF-7 (RFC2152) conversion
    if ($to == 'UTF7-IMAP' || $to == 'UTF-7') {
      if ($_str = utf8_to_utf7($str))
        return $_str;
    }
    else if ($to == 'ISO-8859-1' && function_exists('utf8_decode')) {
      return utf8_decode($str);
    }
    else if (class_exists('utf8')) {
      if (!$conv)
        $conv = new utf8($to);
      else
        $conv->loadCharset($from);

      if ($_str = $conv->strToUtf8($str))
        return $_str;
    }
    $error = true;
  }
  
  // report error
  if ($error && !$convert_warning) {
    raise_error(array(
      'code' => 500,
      'type' => 'php',
      'file' => __FILE__,
      'line' => __LINE__,
      'message' => "Could not convert string from $from to $to. Make sure iconv/mbstring is installed or lib/utf8.class is available."
      ), true, false);
    
    $convert_warning = true;
  }
  
  // return UTF-8 or original string
  return $str;
  }


/**
 * Parse and validate charset name string (see #1485758).
 * Sometimes charset string is malformed, there are also charset aliases 
 * but we need strict names for charset conversion (specially utf8 class)
 *
 * @param  string  Input charset name
 * @return The validated charset name
 */
function rcube_parse_charset($charset)
  {
  $charset = strtoupper($charset);

  # RFC1642
  $charset = str_replace('UNICODE-1-1-', '', $charset);

  # Aliases: some of them from HTML5 spec.
  $aliases = array(
    'USASCII'       => 'WINDOWS-1252',
    'ANSIX31101983' => 'WINDOWS-1252',
    'ANSIX341968'   => 'WINDOWS-1252',
    'UNKNOWN8BIT'   => 'ISO-8859-15',
    'XUNKNOWN'      => 'ISO-8859-15',
    'XUSERDEFINED'  => 'ISO-8859-15',
    'KSC56011987'   => 'EUC-KR',
    'GB2312' 	    => 'GBK',
    'GB231280'	    => 'GBK',
    'UNICODE'	    => 'UTF-8',
    'UTF7IMAP'	    => 'UTF7-IMAP',
    'XXBIG5'	    => 'BIG5',
    'TIS620'	    => 'WINDOWS-874',
    'ISO88599'	    => 'WINDOWS-1254',
    'ISO885911'	    => 'WINDOWS-874',
  );

  $str = preg_replace('/[^a-z0-9]/i', '', $charset);

  if (isset($aliases[$str]))
    return $aliases[$str];

  if (preg_match('/UTF(7|8|16|32)(BE|LE)*/', $str, $m))
    return 'UTF-' . $m[1] . $m[2];

  if (preg_match('/ISO8859([0-9]{0,2})/', $str, $m)) {
    $iso = 'ISO-8859-' . ($m[1] ? $m[1] : 1);
    # some clients sends windows-1252 text as latin1,
    # it is safe to use windows-1252 for all latin1
    return $iso == 'ISO-8859-1' ? 'WINDOWS-1252' : $iso;
    }

  return $charset;
  }


/**
 * Converts string from standard UTF-7 (RFC 2152) to UTF-8.
 *
 * @param  string  Input string
 * @return The converted string
 */
function rcube_utf7_to_utf8($str)
{
  $Index_64 = array(
    0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0,
    0,0,0,0, 0,0,0,0, 0,0,0,0, 0,0,0,0,
    0,0,0,0, 0,0,0,0, 0,0,0,1, 0,0,0,0,
    1,1,1,1, 1,1,1,1, 1,1,0,0, 0,0,0,0,
    0,1,1,1, 1,1,1,1, 1,1,1,1, 1,1,1,1,
    1,1,1,1, 1,1,1,1, 1,1,1,0, 0,0,0,0,
    0,1,1,1, 1,1,1,1, 1,1,1,1, 1,1,1,1,
    1,1,1,1, 1,1,1,1, 1,1,1,0, 0,0,0,0,
  );

  $u7len = strlen($str);
  $str = strval($str);
  $res = '';

  for ($i=0; $u7len > 0; $i++, $u7len--)
  {
    $u7 = $str[$i];
    if ($u7 == '+')
    {
      $i++;
      $u7len--;
      $ch = '';

      for (; $u7len > 0; $i++, $u7len--)
      {
        $u7 = $str[$i];

        if (!$Index_64[ord($u7)])
          break;

	$ch .= $u7;
      }

      if ($ch == '') {
        if ($u7 == '-')
          $res .= '+';
        continue;
      }

      $res .= rcube_utf16_to_utf8(base64_decode($ch));
    }
    else
    {
      $res .= $u7;
    }
  }

  return $res;
}

/**
 * Converts string from UTF-16 to UTF-8 (helper for utf-7 to utf-8 conversion)
 *
 * @param  string  Input string
 * @return The converted string
 */
function rcube_utf16_to_utf8($str)
{
  $len = strlen($str);
  $dec = '';

  for ($i = 0; $i < $len; $i += 2) {
    $c = ord($str[$i]) << 8 | ord($str[$i + 1]);
    if ($c >= 0x0001 && $c <= 0x007F) {
      $dec .= chr($c);
    } else if ($c > 0x07FF) {
      $dec .= chr(0xE0 | (($c >> 12) & 0x0F));
      $dec .= chr(0x80 | (($c >>  6) & 0x3F));
      $dec .= chr(0x80 | (($c >>  0) & 0x3F));
    } else {
      $dec .= chr(0xC0 | (($c >>  6) & 0x1F));
      $dec .= chr(0x80 | (($c >>  0) & 0x3F));
    }
  }
  return $dec;
}


/**
 * Replacing specials characters to a specific encoding type
 *
 * @param  string  Input string
 * @param  string  Encoding type: text|html|xml|js|url
 * @param  string  Replace mode for tags: show|replace|remove
 * @param  boolean Convert newlines
 * @return The quoted string
 */
function rep_specialchars_output($str, $enctype='', $mode='', $newlines=TRUE)
  {
  static $html_encode_arr = false;
  static $js_rep_table = false;
  static $xml_rep_table = false;

  if (!$enctype)
    $enctype = $OUTPUT->type;

  // encode for HTML output
  if ($enctype=='html')
    {
    if (!$html_encode_arr)
      {
      $html_encode_arr = get_html_translation_table(HTML_SPECIALCHARS);        
      unset($html_encode_arr['?']);
      }

    $ltpos = strpos($str, '<');
    $encode_arr = $html_encode_arr;

    // don't replace quotes and html tags
    if (($mode=='show' || $mode=='') && $ltpos!==false && strpos($str, '>', $ltpos)!==false)
      {
      unset($encode_arr['"']);
      unset($encode_arr['<']);
      unset($encode_arr['>']);
      unset($encode_arr['&']);
      }
    else if ($mode=='remove')
      $str = strip_tags($str);
    
    // avoid douple quotation of &
    $out = preg_replace('/&amp;([A-Za-z]{2,6}|#[0-9]{2,4});/', '&\\1;', strtr($str, $encode_arr));
      
    return $newlines ? nl2br($out) : $out;
    }

  // if the replace tables for XML and JS are not yet defined
  if ($js_rep_table===false)
    {
    $js_rep_table = $xml_rep_table = array();
    $xml_rep_table['&'] = '&amp;';

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      $xml_rep_table[Chr($c)] = "&#$c;";

    $xml_rep_table['"'] = '&quot;';
    $js_rep_table['"'] = '\\"';
    $js_rep_table["'"] = "\\'";
    $js_rep_table["\\"] = "\\\\";
    }

  // encode for javascript use
  if ($enctype=='js')
    return preg_replace(array("/\r?\n/", "/\r/", '/<\\//'), array('\n', '\n', '<\\/'), strtr($str, $js_rep_table));

  // encode for plaintext
  if ($enctype=='text')
    return str_replace("\r\n", "\n", $mode=='remove' ? strip_tags($str) : $str);

  if ($enctype=='url')
    return rawurlencode($str);

  // encode for XML
  if ($enctype=='xml')
    return strtr($str, $xml_rep_table);

  // no encoding given -> return original string
  return $str;
  }
  
/**
 * Quote a given string.
 * Shortcut function for rep_specialchars_output
 *
 * @return string HTML-quoted string
 * @see rep_specialchars_output()
 */
function Q($str, $mode='strict', $newlines=TRUE)
  {
  return rep_specialchars_output($str, 'html', $mode, $newlines);
  }

/**
 * Quote a given string for javascript output.
 * Shortcut function for rep_specialchars_output
 * 
 * @return string JS-quoted string
 * @see rep_specialchars_output()
 */
function JQ($str)
  {
  return rep_specialchars_output($str, 'js');
  }


/**
 * Read input value and convert it for internal use
 * Performs stripslashes() and charset conversion if necessary
 * 
 * @param  string   Field name to read
 * @param  int      Source to get value from (GPC)
 * @param  boolean  Allow HTML tags in field value
 * @param  string   Charset to convert into
 * @return string   Field value or NULL if not available
 */
function get_input_value($fname, $source, $allow_html=FALSE, $charset=NULL)
  {
  global $OUTPUT;
  $value = NULL;
  
  if ($source==RCUBE_INPUT_GET && isset($_GET[$fname]))
    $value = $_GET[$fname];
  else if ($source==RCUBE_INPUT_POST && isset($_POST[$fname]))
    $value = $_POST[$fname];
  else if ($source==RCUBE_INPUT_GPC)
    {
    if (isset($_POST[$fname]))
      $value = $_POST[$fname];
    else if (isset($_GET[$fname]))
      $value = $_GET[$fname];
    else if (isset($_COOKIE[$fname]))
      $value = $_COOKIE[$fname];
    }

  if (empty($value))
    return $value;

  // strip single quotes if magic_quotes_sybase is enabled
  if (ini_get('magic_quotes_sybase'))
    $value = str_replace("''", "'", $value);
  // strip slashes if magic_quotes enabled
  else if (get_magic_quotes_gpc() || get_magic_quotes_runtime())
    $value = stripslashes($value);

  // remove HTML tags if not allowed    
  if (!$allow_html)
    $value = strip_tags($value);
  
  // convert to internal charset
  if (is_object($OUTPUT))
    return rcube_charset_convert($value, $OUTPUT->get_charset(), $charset);
  else
    return $value;
  }

/**
 * Remove all non-ascii and non-word chars
 * except ., -, _
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
 * @return string Dequoted string
 */
function strip_quotes($str)
{
  return preg_replace('/[\'"]/', '', $str);
}


/**
 * Remove new lines characters from given string
 *
 * @param string Input value
 * @return string Stripped string
 */
function strip_newlines($str)
{
  return preg_replace('/[\r\n]/', '', $str);
}


/**
 * Create a HTML table based on the given data
 *
 * @param  array  Named table attributes
 * @param  mixed  Table row data. Either a two-dimensional array or a valid SQL result set
 * @param  array  List of cols to show
 * @param  string Name of the identifier col
 * @return string HTML table code
 */
function rcube_table_output($attrib, $table_data, $a_show_cols, $id_col)
  {
  global $RCMAIL;
  
  $table = new html_table(/*array('cols' => count($a_show_cols))*/);
    
  // add table header
  foreach ($a_show_cols as $col)
    $table->add_header($col, Q(rcube_label($col)));
  
  $c = 0;
  if (!is_array($table_data)) 
  {
    $db = $RCMAIL->get_dbh();
    while ($table_data && ($sql_arr = $db->fetch_assoc($table_data)))
    {
      $zebra_class = $c % 2 ? 'even' : 'odd';
      $table->add_row(array('id' => 'rcmrow' . $sql_arr[$id_col], 'class' => "contact $zebra_class"));

      // format each col
      foreach ($a_show_cols as $col)
        $table->add($col, Q($sql_arr[$col]));
      
      $c++;
    }
  }
  else 
  {
    foreach ($table_data as $row_data)
    {
      $zebra_class = $c % 2 ? 'even' : 'odd';
      $table->add_row(array('id' => 'rcmrow' . $row_data[$id_col], 'class' => "contact $zebra_class"));

      // format each col
      foreach ($a_show_cols as $col)
        $table->add($col, Q($row_data[$col]));
        
      $c++;
    }
  }

  return $table->show($attrib);
  }


/**
 * Create an edit field for inclusion on a form
 * 
 * @param string col field name
 * @param string value field value
 * @param array attrib HTML element attributes for field
 * @param string type HTML element type (default 'text')
 * @return string HTML field definition
 */
function rcmail_get_edit_field($col, $value, $attrib, $type='text')
  {
  $fname = '_'.$col;
  $attrib['name'] = $fname;
  
  if ($type=='checkbox')
    {
    $attrib['value'] = '1';
    $input = new html_checkbox($attrib);
    }
  else if ($type=='textarea')
    {
    $attrib['cols'] = $attrib['size'];
    $input = new html_textarea($attrib);
    }
  else
    $input = new html_inputfield($attrib);

  // use value from post
  if (!empty($_POST[$fname]))
    $value = get_input_value($fname, RCUBE_INPUT_POST,
	    $type == 'textarea' && strpos($attrib['class'], 'mce_editor')!==false ? true : false);

  $out = $input->show($value);
         
  return $out;
  }


/**
 * Replace all css definitions with #container [def]
 * and remove css-inlined scripting
 *
 * @param string CSS source code
 * @param string Container ID to use as prefix
 * @return string Modified CSS source
 */
function rcmail_mod_css_styles($source, $container_id)
  {
  $last_pos = 0;
  $replacements = new rcube_string_replacer;
  
  // ignore the whole block if evil styles are detected
  $stripped = preg_replace('/[^a-z\(:]/', '', rcmail_xss_entity_decode($source));
  if (preg_match('/expression|behavior|url\(|import/', $stripped))
    return '/* evil! */';

  // cut out all contents between { and }
  while (($pos = strpos($source, '{', $last_pos)) && ($pos2 = strpos($source, '}', $pos)))
  {
    $key = $replacements->add(substr($source, $pos+1, $pos2-($pos+1)));
    $source = substr($source, 0, $pos+1) . $replacements->get_replacement($key) . substr($source, $pos2, strlen($source)-$pos2);
    $last_pos = $pos+2;
  }
  
  // remove html comments and add #container to each tag selector.
  // also replace body definition because we also stripped off the <body> tag
  $styles = preg_replace(
    array(
      '/(^\s*<!--)|(-->\s*$)/',
      '/(^\s*|,\s*|\}\s*)([a-z0-9\._#][a-z0-9\.\-_]*)/im',
      "/$container_id\s+body/i",
    ),
    array(
      '',
      "\\1#$container_id \\2",
      "$container_id div.rcmBody",
    ),
    $source);
  
  // put block contents back in
  $styles = $replacements->resolve($styles);

  return $styles;
  }


/**
 * Decode escaped entities used by known XSS exploits.
 * See http://downloads.securityfocus.com/vulnerabilities/exploits/26800.eml for examples
 *
 * @param string CSS content to decode
 * @return string Decoded string
 */
function rcmail_xss_entity_decode($content)
{
  $out = html_entity_decode(html_entity_decode($content));
  $out = preg_replace_callback('/\\\([0-9a-f]{4})/i', 'rcmail_xss_entity_decode_callback', $out);
  $out = preg_replace('#/\*.*\*/#Um', '', $out);
  return $out;
}


/**
 * preg_replace_callback callback for rcmail_xss_entity_decode_callback
 *
 * @param array matches result from preg_replace_callback
 * @return string decoded entity
 */ 
function rcmail_xss_entity_decode_callback($matches)
{ 
  return chr(hexdec($matches[1]));
}

/**
 * Compose a valid attribute string for HTML tags
 *
 * @param array Named tag attributes
 * @param array List of allowed attributes
 * @return string HTML formatted attribute string
 */
function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
  {
  // allow the following attributes to be added to the <iframe> tag
  $attrib_str = '';
  foreach ($allowed_attribs as $a)
    if (isset($attrib[$a]))
      $attrib_str .= sprintf(' %s="%s"', $a, str_replace('"', '&quot;', $attrib[$a]));

  return $attrib_str;
  }


/**
 * Convert a HTML attribute string attributes to an associative array (name => value)
 *
 * @param string Input string
 * @return array Key-value pairs of parsed attributes
 */
function parse_attrib_string($str)
  {
  $attrib = array();
  preg_match_all('/\s*([-_a-z]+)=(["\'])??(?(2)([^\2]*)\2|(\S+?))/Ui', stripslashes($str), $regs, PREG_SET_ORDER);

  // convert attributes to an associative array (name => value)
  if ($regs) {
    foreach ($regs as $attr) {
      $attrib[strtolower($attr[1])] = html_entity_decode($attr[3] . $attr[4]);
    }
  }

  return $attrib;
  }


/**
 * Convert the given date to a human readable form
 * This uses the date formatting properties from config
 *
 * @param mixed Date representation (string or timestamp)
 * @param string Date format to use
 * @return string Formatted date string
 */
function format_date($date, $format=NULL)
  {
  global $CONFIG;
  
  $ts = NULL;

  if (is_numeric($date))
    $ts = $date;
  else if (!empty($date))
    {
    // support non-standard "GMTXXXX" literal
    $date = preg_replace('/GMT\s*([+-][0-9]+)/', '\\1', $date);
    // if date parsing fails, we have a date in non-rfc format.
    // remove token from the end and try again
    while ((($ts = @strtotime($date))===false) || ($ts < 0))
      {
        $d = explode(' ', $date);
        array_pop($d);
        if (!$d) break;
        $date = implode(' ', $d);
      }
    }

  if (empty($ts))
    return '';
   
  // get user's timezone
  if ($CONFIG['timezone'] === 'auto')
    $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
  else {
    $tz = $CONFIG['timezone'];
    if ($CONFIG['dst_active'])
      $tz++;
  }

  // convert time to user's timezone
  $timestamp = $ts - date('Z', $ts) + ($tz * 3600);
  
  // get current timestamp in user's timezone
  $now = time();  // local time
  $now -= (int)date('Z'); // make GMT time
  $now += ($tz * 3600); // user's time
  $now_date = getdate($now);

  $today_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year']);
  $week_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);

  // define date format depending on current time  
  if ($CONFIG['prettydate'] && !$format && $timestamp > $today_limit && $timestamp < $now)
    return sprintf('%s %s', rcube_label('today'), date($CONFIG['date_today'] ? $CONFIG['date_today'] : 'H:i', $timestamp));
  else if ($CONFIG['prettydate'] && !$format && $timestamp > $week_limit && $timestamp < $now)
    $format = $CONFIG['date_short'] ? $CONFIG['date_short'] : 'D H:i';
  else if (!$format)
    $format = $CONFIG['date_long'] ? $CONFIG['date_long'] : 'd.m.Y H:i';

  // strftime() format
  if (preg_match('/%[a-z]+/i', $format))
    return strftime($format, $timestamp);

  // parse format string manually in order to provide localized weekday and month names
  // an alternative would be to convert the date() format string to fit with strftime()
  $out = '';
  for($i=0; $i<strlen($format); $i++)
    {
    if ($format{$i}=='\\')  // skip escape chars
      continue;
    
    // write char "as-is"
    if ($format{$i}==' ' || $format{$i-1}=='\\')
      $out .= $format{$i};
    // weekday (short)
    else if ($format{$i}=='D')
      $out .= rcube_label(strtolower(date('D', $timestamp)));
    // weekday long
    else if ($format{$i}=='l')
      $out .= rcube_label(strtolower(date('l', $timestamp)));
    // month name (short)
    else if ($format{$i}=='M')
      $out .= rcube_label(strtolower(date('M', $timestamp)));
    // month name (long)
    else if ($format{$i}=='F')
      $out .= rcube_label('long'.strtolower(date('M', $timestamp)));
    else if ($format{$i}=='x')
      $out .= strftime('%x %X', $timestamp);
    else
      $out .= date($format{$i}, $timestamp);
    }
  
  return $out;
  }


/**
 * Compose a valid representaion of name and e-mail address
 *
 * @param string E-mail address
 * @param string Person name
 * @return string Formatted string
 */
function format_email_recipient($email, $name='')
  {
  if ($name && $name != $email)
    {
    // Special chars as defined by RFC 822 need to in quoted string (or escaped).
    return sprintf('%s <%s>', preg_match('/[\(\)\<\>\\\.\[\]@,;:"]/', $name) ? '"'.addcslashes($name, '"').'"' : $name, $email);
    }
  else
    return $email;
  }



/****** debugging functions ********/


/**
 * Print or write debug messages
 *
 * @param mixed Debug message or data
 */
function console()
  {
  $args = func_get_args();

  if (class_exists('rcmail', false))
    rcmail::get_instance()->plugins->exec_hook('console', $args);

  $msg = array();
  foreach ($args as $arg)
    $msg[] = !is_string($arg) ? var_export($arg, true) : $arg;

  if (!($GLOBALS['CONFIG']['debug_level'] & 4))
    write_log('console', join(";\n", $msg));
  else if ($GLOBALS['OUTPUT']->ajax_call)
    print "/*\n " . join(";\n", $msg) . " \n*/\n";
  else
    {
    print '<div style="background:#eee; border:1px solid #ccc; margin-bottom:3px; padding:6px"><pre>';
    print join(";<br/>\n", $msg);
    print "</pre></div>\n";
    }
  }


/**
 * Append a line to a logfile in the logs directory.
 * Date will be added automatically to the line.
 *
 * @param $name name of log file
 * @param line Line to append
 */
function write_log($name, $line)
  {
  global $CONFIG, $RCMAIL;

  if (!is_string($line))
    $line = var_export($line, true);
 
  if (empty($CONFIG['log_date_format']))
    $CONFIG['log_date_format'] = 'd-M-Y H:i:s O';
  
  // trigger logging hook
  if (is_object($RCMAIL) && is_object($RCMAIL->plugins)) {
    $log = $RCMAIL->plugins->exec_hook('write_log', array('name' => $name, 'line' => $line));
    $name = $log['name'];
    $line = $log['line'];
    if ($log['abort'])
      return;
  }
 
  $log_entry = sprintf("[%s]: %s\n", date($CONFIG['log_date_format']), $line);

  if ($CONFIG['log_driver'] == 'syslog') {
    $prio = $name == 'errors' ? LOG_ERR : LOG_INFO;
    syslog($prio, $log_entry);
  }
  else {
    // log_driver == 'file' is assumed here
    if (empty($CONFIG['log_dir']))
      $CONFIG['log_dir'] = INSTALL_PATH.'logs';

    // try to open specific log file for writing
    if ($fp = @fopen($CONFIG['log_dir'].'/'.$name, 'a')) {
      fwrite($fp, $log_entry);
      fflush($fp);
      fclose($fp);
    }
  }
}


/**
 * @access private
 */
function rcube_timer()
{
  return microtime(true);
}
  

/**
 * @access private
 */
function rcube_print_time($timer, $label='Timer', $dest='console')
{
  static $print_count = 0;
  
  $print_count++;
  $now = rcube_timer();
  $diff = $now-$timer;
  
  if (empty($label))
    $label = 'Timer '.$print_count;
  
  write_log($dest, sprintf("%s: %0.4f sec", $label, $diff));
}


/**
 * Return the mailboxlist in HTML
 *
 * @param array Named parameters
 * @return string HTML code for the gui object
 */
function rcmail_mailbox_list($attrib)
{
  global $RCMAIL;
  static $a_mailboxes;
  
  $attrib += array('maxlength' => 100, 'relanames' => false);

  // add some labels to client
  $RCMAIL->output->add_label('purgefolderconfirm', 'deletemessagesconfirm');
  
  $type = $attrib['type'] ? $attrib['type'] : 'ul';
  unset($attrib['type']);

  if ($type=='ul' && !$attrib['id'])
    $attrib['id'] = 'rcmboxlist';

  // get mailbox list
  $mbox_name = $RCMAIL->imap->get_mailbox_name();
  
  // build the folders tree
  if (empty($a_mailboxes)) {
    // get mailbox list
    $a_folders = $RCMAIL->imap->list_mailboxes();
    $delimiter = $RCMAIL->imap->get_hierarchy_delimiter();
    $a_mailboxes = array();

    foreach ($a_folders as $folder)
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
  }
  
  // allow plugins to alter the folder tree or to localize folder names
  $hook = $RCMAIL->plugins->exec_hook('render_mailboxlist', array('list' => $a_mailboxes, 'delimiter' => $delimiter));

  if ($type=='select') {
    $select = new html_select($attrib);
    
    // add no-selection option
    if ($attrib['noselection'])
      $select->add(rcube_label($attrib['noselection']), '0');
    
    rcmail_render_folder_tree_select($hook['list'], $mbox_name, $attrib['maxlength'], $select, $attrib['realnames']);
    $out = $select->show();
  }
  else {
    $js_mailboxlist = array();
    $out = html::tag('ul', $attrib, rcmail_render_folder_tree_html($hook['list'], $mbox_name, $js_mailboxlist, $attrib), html::$common_attrib);
    
    $RCMAIL->output->add_gui_object('mailboxlist', $attrib['id']);
    $RCMAIL->output->set_env('mailboxes', $js_mailboxlist);
    $RCMAIL->output->set_env('collapsed_folders', $RCMAIL->config->get('collapsed_folders'));
  }

  return $out;
}


/**
 * Return the mailboxlist as html_select object
 *
 * @param array Named parameters
 * @return object html_select HTML drop-down object
 */
function rcmail_mailbox_select($p = array())
{
  global $RCMAIL;
  
  $p += array('maxlength' => 100, 'relanames' => false);
  $a_mailboxes = array();
  
  foreach ($RCMAIL->imap->list_mailboxes() as $folder)
    rcmail_build_folder_tree($a_mailboxes, $folder, $RCMAIL->imap->get_hierarchy_delimiter());

  $select = new html_select($p);
  
  if ($p['noselection'])
    $select->add($p['noselection'], '');
    
  rcmail_render_folder_tree_select($a_mailboxes, $mbox, $p['maxlength'], $select, $p['realnames']);
  
  return $select;
}


/**
 * Create a hierarchical array of the mailbox list
 * @access private
 */
function rcmail_build_folder_tree(&$arrFolders, $folder, $delm='/', $path='')
{
  $pos = strpos($folder, $delm);
  if ($pos !== false) {
    $subFolders = substr($folder, $pos+1);
    $currentFolder = substr($folder, 0, $pos);
    $virtual = !isset($arrFolders[$currentFolder]);
  }
  else {
    $subFolders = false;
    $currentFolder = $folder;
    $virtual = false;
  }

  $path .= $currentFolder;

  if (!isset($arrFolders[$currentFolder])) {
    $arrFolders[$currentFolder] = array(
      'id' => $path,
      'name' => rcube_charset_convert($currentFolder, 'UTF7-IMAP'),
      'virtual' => $virtual,
      'folders' => array());
  }
  else
    $arrFolders[$currentFolder]['virtual'] = $virtual;

  if (!empty($subFolders))
    rcmail_build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
}
  

/**
 * Return html for a structured list &lt;ul&gt; for the mailbox tree
 * @access private
 */
function rcmail_render_folder_tree_html(&$arrFolders, &$mbox_name, &$jslist, $attrib, $nestLevel=0)
{
  global $RCMAIL, $CONFIG;
  
  $maxlength = intval($attrib['maxlength']);
  $realnames = (bool)$attrib['realnames'];
  $msgcounts = $RCMAIL->imap->get_cache('messagecount');

  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key => $folder) {
    $zebra_class = (($nestLevel+1)*$idx) % 2 == 0 ? 'even' : 'odd';
    $title = null;

    if (($folder_class = rcmail_folder_classname($folder['id'])) && !$realnames) {
      $foldername = rcube_label($folder_class);
    }
    else {
      $foldername = $folder['name'];

      // shorten the folder name to a given length
      if ($maxlength && $maxlength > 1) {
        $fname = abbreviate_string($foldername, $maxlength);
        if ($fname != $foldername)
          $title = $foldername;
        $foldername = $fname;
      }
    }

    // make folder name safe for ids and class names
    $folder_id = asciiwords($folder['id'], true, '_');
    $classes = array('mailbox');

    // set special class for Sent, Drafts, Trash and Junk
    if ($folder['id']==$CONFIG['sent_mbox'])
      $classes[] = 'sent';
    else if ($folder['id']==$CONFIG['drafts_mbox'])
      $classes[] = 'drafts';
    else if ($folder['id']==$CONFIG['trash_mbox'])
      $classes[] = 'trash';
    else if ($folder['id']==$CONFIG['junk_mbox'])
      $classes[] = 'junk';
    else if ($folder['id']=='INBOX')
      $classes[] = 'inbox';
    else
      $classes[] = '_'.asciiwords($folder_class ? $folder_class : strtolower($folder['id']), true);
      
    $classes[] = $zebra_class;
    
    if ($folder['id'] == $mbox_name)
      $classes[] = 'selected';

    $collapsed = preg_match('/&'.rawurlencode($folder['id']).'&/', $RCMAIL->config->get('collapsed_folders'));
    $unread = $msgcounts ? intval($msgcounts[$folder['id']]['UNSEEN']) : 0;
    
    if ($folder['virtual'])
      $classes[] = 'virtual';
    else if ($unread)
      $classes[] = 'unread';

    $js_name = JQ($folder['id']);
    $html_name = Q($foldername . ($unread ? " ($unread)" : ''));
    $link_attrib = $folder['virtual'] ? array() : array(
      'href' => rcmail_url('', array('_mbox' => $folder['id'])),
      'onclick' => sprintf("return %s.command('list','%s',this)", JS_OBJECT_NAME, $js_name),
      'title' => $title,
    );

    $out .= html::tag('li', array(
        'id' => "rcmli".$folder_id,
        'class' => join(' ', $classes),
        'noclose' => true),
      html::a($link_attrib, $html_name) .
      (!empty($folder['folders']) ? html::div(array(
        'class' => ($collapsed ? 'collapsed' : 'expanded'),
        'style' => "position:absolute",
        'onclick' => sprintf("%s.command('collapse-folder', '%s')", JS_OBJECT_NAME, $js_name)
      ), '&nbsp;') : ''));
    
    $jslist[$folder_id] = array('id' => $folder['id'], 'name' => $foldername, 'virtual' => $folder['virtual']);
    
    if (!empty($folder['folders'])) {
      $out .= html::tag('ul', array('style' => ($collapsed ? "display:none;" : null)),
        rcmail_render_folder_tree_html($folder['folders'], $mbox_name, $jslist, $attrib, $nestLevel+1));
    }

    $out .= "</li>\n";
    $idx++;
  }

  return $out;
}


/**
 * Return html for a flat list <select> for the mailbox tree
 * @access private
 */
function rcmail_render_folder_tree_select(&$arrFolders, &$mbox_name, $maxlength, &$select, $realnames=false, $nestLevel=0)
  {
  $idx = 0;
  $out = '';
  foreach ($arrFolders as $key=>$folder)
    {
    if (!$realnames && ($folder_class = rcmail_folder_classname($folder['id'])))
      $foldername = rcube_label($folder_class);
    else
      {
      $foldername = $folder['name'];
      
      // shorten the folder name to a given length
      if ($maxlength && $maxlength>1)
        $foldername = abbreviate_string($foldername, $maxlength);
      }

    $select->add(str_repeat('&nbsp;', $nestLevel*4) . $foldername, $folder['id']);

    if (!empty($folder['folders']))
      $out .= rcmail_render_folder_tree_select($folder['folders'], $mbox_name, $maxlength, $select, $realnames, $nestLevel+1);

    $idx++;
    }

  return $out;
  }


/**
 * Return internal name for the given folder if it matches the configured special folders
 * @access private
 */
function rcmail_folder_classname($folder_id)
{
  global $CONFIG;

  // for these mailboxes we have localized labels and css classes
  foreach (array('sent', 'drafts', 'trash', 'junk') as $smbx)
  {
    if ($folder_id == $CONFIG[$smbx.'_mbox'])
      return $smbx;
  }

  if ($folder_id == 'INBOX')
    return 'inbox';
}


/**
 * Try to localize the given IMAP folder name.
 * UTF-7 decode it in case no localized text was found
 *
 * @param string Folder name
 * @return string Localized folder name in UTF-8 encoding
 */
function rcmail_localize_foldername($name)
{
  if ($folder_class = rcmail_folder_classname($name))
    return rcube_label($folder_class);
  else
    return rcube_charset_convert($name, 'UTF7-IMAP');
}


/**
 * Output HTML editor scripts
 *
 * @param string Editor mode
 */
function rcube_html_editor($mode='')
{
  global $OUTPUT, $CONFIG;

  $lang = $tinylang = strtolower(substr($_SESSION['language'], 0, 2));
  if (!file_exists(INSTALL_PATH . 'program/js/tiny_mce/langs/'.$tinylang.'.js'))
    $tinylang = 'en';

  $OUTPUT->include_script('tiny_mce/tiny_mce.js');
  $OUTPUT->include_script('editor.js');
  $OUTPUT->add_script('rcmail_editor_init("$__skin_path", "'.JQ($tinylang).'", '.intval($CONFIG['enable_spellcheck']).', "'.$mode.'");');
}



/**
 * Helper class to turn relative urls into absolute ones
 * using a predefined base
 */
class rcube_base_replacer
{
  private $base_url;
  
  public function __construct($base)
  {
    $this->base_url = $base;
  }
  
  public function callback($matches)
  {
    return $matches[1] . '="' . make_absolute_url($matches[3], $this->base_url) . '"';
  }
}

?>
