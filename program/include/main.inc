<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
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
 * Roundcube Webmail common functions
 *
 * @package Core
 * @author Thomas Bruederli <roundcube@gmail.com>
 */

require_once 'utf7.inc';
require_once INSTALL_PATH . 'program/include/rcube_shared.inc';

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
  // return sequence name if configured
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
 * @param string Domain to search in (e.g. plugin name)
 * @return string Localized text
 * @see rcmail::gettext()
 */
function rcube_label($p, $domain=null)
{
  return rcmail::get_instance()->gettext($p, $domain);
}


/**
 * Global wrapper of rcmail::text_exists()
 * to check whether a text label is defined
 *
 * @see rcmail::text_exists()
 */
function rcube_label_exists($name, $domain=null)
{
  return rcmail::get_instance()->text_exists($name, $domain);
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

  if ($dir = opendir($tmp)) {
    while (($fname = readdir($dir)) !== false) {
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
 * @return void
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
 * Catch an error and throw an exception.
 *
 * @param  int    Level of the error
 * @param  string Error message
 */ 
function rcube_error_handler($errno, $errstr)
{
  throw new ErrorException($errstr, 0, $errno);
}


/**
 * Convert a string from one charset to another.
 * Uses mbstring and iconv functions if possible
 *
 * @param  string Input string
 * @param  string Suspected charset of the input string
 * @param  string Target charset to convert to; defaults to RCMAIL_CHARSET
 * @return string Converted string
 */
function rcube_charset_convert($str, $from, $to=NULL)
{
  static $iconv_options = null;
  static $mbstring_loaded = null;
  static $mbstring_list = null;
  static $conv = null;

  $error = false;

  $to = empty($to) ? strtoupper(RCMAIL_CHARSET) : rcube_parse_charset($to);
  $from = rcube_parse_charset($from);

  if ($from == $to || empty($str) || empty($from))
    return $str;

  // convert charset using iconv module
  if (function_exists('iconv') && $from != 'UTF7-IMAP' && $to != 'UTF7-IMAP') {
    if ($iconv_options === null) {
      // ignore characters not available in output charset
      $iconv_options = '//IGNORE';
      if (iconv('', $iconv_options, '') === false) {
        // iconv implementation does not support options
        $iconv_options = '';
      }
    }

    // throw an exception if iconv reports an illegal character in input
    // it means that input string has been truncated
    set_error_handler('rcube_error_handler', E_NOTICE);
    try {
      $_iconv = iconv($from, $to . $iconv_options, $str);
    } catch (ErrorException $e) {
      $_iconv = false;
    }
    restore_error_handler();
    if ($_iconv !== false) {
      return $_iconv;
    }
  }

  if ($mbstring_loaded === null)
    $mbstring_loaded = extension_loaded('mbstring');

  // convert charset using mbstring module
  if ($mbstring_loaded) {
    $aliases['WINDOWS-1257'] = 'ISO-8859-13';

    if ($mbstring_list === null) {
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

  // return UTF-8 or original string
  return $str;
}


/**
 * Parse and validate charset name string (see #1485758).
 * Sometimes charset string is malformed, there are also charset aliases 
 * but we need strict names for charset conversion (specially utf8 class)
 *
 * @param  string Input charset name
 * @return string The validated charset name
 */
function rcube_parse_charset($input)
{
  static $charsets = array();
  $charset = strtoupper($input);

  if (isset($charsets[$input]))
    return $charsets[$input];

  $charset = preg_replace(array(
    '/^[^0-9A-Z]+/',    // e.g. _ISO-8859-JP$SIO
    '/\$.*$/',          // e.g. _ISO-8859-JP$SIO
    '/UNICODE-1-1-*/',  // RFC1641/1642
    '/^X-/',            // X- prefix (e.g. X-ROMAN8 => ROMAN8)
    ), '', $charset);

  if ($charset == 'BINARY')
    return $charsets[$input] = null;

  # Aliases: some of them from HTML5 spec.
  $aliases = array(
    'USASCII'       => 'WINDOWS-1252',
    'ANSIX31101983' => 'WINDOWS-1252',
    'ANSIX341968'   => 'WINDOWS-1252',
    'UNKNOWN8BIT'   => 'ISO-8859-15',
    'UNKNOWN'       => 'ISO-8859-15',
    'USERDEFINED'   => 'ISO-8859-15',
    'KSC56011987'   => 'EUC-KR',
    'GB2312' 	    => 'GBK',
    'GB231280'	    => 'GBK',
    'UNICODE'	    => 'UTF-8',
    'UTF7IMAP'	    => 'UTF7-IMAP',
    'TIS620'	    => 'WINDOWS-874',
    'ISO88599'	    => 'WINDOWS-1254',
    'ISO885911'	    => 'WINDOWS-874',
    'MACROMAN'	    => 'MACINTOSH',
    '77'            => 'MAC',
    '128'           => 'SHIFT-JIS',
    '129'           => 'CP949',
    '130'           => 'CP1361',
    '134'           => 'GBK',
    '136'           => 'BIG5',
    '161'           => 'WINDOWS-1253',
    '162'           => 'WINDOWS-1254',
    '163'           => 'WINDOWS-1258',
    '177'           => 'WINDOWS-1255',
    '178'           => 'WINDOWS-1256',
    '186'           => 'WINDOWS-1257',
    '204'           => 'WINDOWS-1251',
    '222'           => 'WINDOWS-874',
    '238'           => 'WINDOWS-1250',
    'MS950'         => 'CP950',
    'WINDOWS949'    => 'UHC',
  );

  // allow A-Z and 0-9 only
  $str = preg_replace('/[^A-Z0-9]/', '', $charset);

  if (isset($aliases[$str]))
    $result = $aliases[$str];
  // UTF
  else if (preg_match('/U[A-Z][A-Z](7|8|16|32)(BE|LE)*/', $str, $m))
    $result = 'UTF-' . $m[1] . $m[2];
  // ISO-8859
  else if (preg_match('/ISO8859([0-9]{0,2})/', $str, $m)) {
    $iso = 'ISO-8859-' . ($m[1] ? $m[1] : 1);
    // some clients sends windows-1252 text as latin1,
    // it is safe to use windows-1252 for all latin1
    $result = $iso == 'ISO-8859-1' ? 'WINDOWS-1252' : $iso;
  }
  // handle broken charset names e.g. WINDOWS-1250HTTP-EQUIVCONTENT-TYPE
  else if (preg_match('/(WIN|WINDOWS)([0-9]+)/', $str, $m)) {
    $result = 'WINDOWS-' . $m[2];
  }
  // LATIN
  else if (preg_match('/LATIN(.*)/', $str, $m)) {
    $aliases = array('2' => 2, '3' => 3, '4' => 4, '5' => 9, '6' => 10,
        '7' => 13, '8' => 14, '9' => 15, '10' => 16,
        'ARABIC' => 6, 'CYRILLIC' => 5, 'GREEK' => 7, 'GREEK1' => 7, 'HEBREW' => 8);

    // some clients sends windows-1252 text as latin1,
    // it is safe to use windows-1252 for all latin1
    if ($m[1] == 1) {
      $result = 'WINDOWS-1252';
    }
    // if iconv is not supported we need ISO labels, it's also safe for iconv
    else if (!empty($aliases[$m[1]])) {
      $result = 'ISO-8859-'.$aliases[$m[1]];
    }
    // iconv requires convertion of e.g. LATIN-1 to LATIN1
    else {
      $result = $str;
    }
  }
  else {
    $result = $charset;
  }

  $charsets[$input] = $result;

  return $result;
}


/**
 * Converts string from standard UTF-7 (RFC 2152) to UTF-8.
 *
 * @param  string  Input string
 * @return string  The converted string
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
 * @return string  The converted string
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
 * @return string  The quoted string
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

    $out = strtr($str, $encode_arr);

    // avoid douple quotation of &
    $out = preg_replace('/&amp;([A-Za-z]{2,6}|#[0-9]{2,4});/', '&\\1;', $out);

    return $newlines ? nl2br($out) : $out;
    }

  // if the replace tables for XML and JS are not yet defined
  if ($js_rep_table===false)
    {
    $js_rep_table = $xml_rep_table = array();
    $xml_rep_table['&'] = '&amp;';

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      $xml_rep_table[chr($c)] = "&#$c;";

    $xml_rep_table['"'] = '&quot;';
    $js_rep_table['"'] = '\\"';
    $js_rep_table["'"] = "\\'";
    $js_rep_table["\\"] = "\\\\";
    // Unicode line and paragraph separators (#1486310)
    $js_rep_table[chr(hexdec(E2)).chr(hexdec(80)).chr(hexdec(A8))] = '&#8232;';
    $js_rep_table[chr(hexdec(E2)).chr(hexdec(80)).chr(hexdec(A9))] = '&#8233;';
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

  return parse_input_value($value, $allow_html, $charset);
}

/**
 * Parse/validate input value. See get_input_value()
 * Performs stripslashes() and charset conversion if necessary
 * 
 * @param  string   Input value
 * @param  boolean  Allow HTML tags in field value
 * @param  string   Charset to convert into
 * @return string   Parsed value
 */
function parse_input_value($value, $allow_html=FALSE, $charset=NULL)
{
  global $OUTPUT;

  if (empty($value))
    return $value;

  if (is_array($value)) {
    foreach ($value as $idx => $val)
      $value[$idx] = parse_input_value($val, $allow_html, $charset);
    return $value;
  }

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
  if (is_object($OUTPUT) && $charset)
    return rcube_charset_convert($value, $OUTPUT->get_charset(), $charset);
  else
    return $value;
}

/**
 * Convert array of request parameters (prefixed with _)
 * to a regular array with non-prefixed keys.
 *
 * @param  int   Source to get value from (GPC)
 * @return array Hash array with all request parameters
 */
function request2param($mode = RCUBE_INPUT_GPC)
{
  $out = array();
  $src = $mode == RCUBE_INPUT_GET ? $_GET : ($mode == RCUBE_INPUT_POST ? $_POST : $_REQUEST);
  foreach ($src as $key => $value) {
    $fname = $key[0] == '_' ? substr($key, 1) : $key;
    $out[$fname] = get_input_value($key, $mode);
  }
  
  return $out;
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
 * Convert the given string into a valid HTML identifier
 * Same functionality as done in app.js with this.identifier_expr
 *
 */
function html_identifier($str)
{
  return asciiwords($str, true, '_');
}

/**
 * Remove single and double quotes from given string
 *
 * @param string Input value
 * @return string Dequoted string
 */
function strip_quotes($str)
{
  return str_replace(array("'", '"'), '', $str);
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
  if (!$attrib['noheader'])
    foreach ($a_show_cols as $col)
      $table->add_header($col, Q(rcube_label($col)));
  
  $c = 0;
  if (!is_array($table_data)) 
  {
    $db = $RCMAIL->get_dbh();
    while ($table_data && ($sql_arr = $db->fetch_assoc($table_data)))
    {
      $zebra_class = $c % 2 ? 'even' : 'odd';
      $table->add_row(array('id' => 'rcmrow' . html_identifier($sql_arr[$id_col]), 'class' => $zebra_class));

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
      if (!empty($row_data['class']))
        $zebra_class .= ' '.$row_data['class'];

      $table->add_row(array('id' => 'rcmrow' . html_identifier($row_data[$id_col]), 'class' => $zebra_class));

      // format each col
      foreach ($a_show_cols as $col)
        $table->add($col, Q(is_array($row_data[$col]) ? $row_data[$col][0] : $row_data[$col]));
        
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
  static $colcounts = array();
  
  $fname = '_'.$col;
  $attrib['name'] = $fname . ($attrib['array'] ? '[]' : '');
  $attrib['class'] = trim($attrib['class'] . ' ff_' . $col);
  
  if ($type == 'checkbox') {
    $attrib['value'] = '1';
    $input = new html_checkbox($attrib);
  }
  else if ($type == 'textarea') {
    $attrib['cols'] = $attrib['size'];
    $input = new html_textarea($attrib);
  }
  else if ($type == 'select') {
    $input = new html_select($attrib);
    $input->add('---', '');
    $input->add(array_values($attrib['options']), array_keys($attrib['options']));
  }
  else {
    if ($attrib['type'] != 'text' && $attrib['type'] != 'hidden')
        $attrib['type'] = 'text';
    $input = new html_inputfield($attrib);
  }

  // use value from post
  if (isset($_POST[$fname])) {
    $postvalue = get_input_value($fname, RCUBE_INPUT_POST, true);
    $value = $attrib['array'] ? $postvalue[intval($colcounts[$col]++)] : $postvalue;
  }

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
  $stripped = preg_replace('/[^a-z\(:;]/', '', rcmail_xss_entity_decode($source));
  if (preg_match('/expression|behavior|url\(|import[^a]/', $stripped))
    return '/* evil! */';

  // remove css comments (sometimes used for some ugly hacks)
  $source = preg_replace('!/\*(.+)\*/!Ums', '', $source);

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
      '/(^\s*|,\s*|\}\s*)([a-z0-9\._#\*][a-z0-9\.\-_]*)/im',
      '/'.preg_quote($container_id, '/').'\s+body/i',
    ),
    array(
      '',
      "\\1#$container_id \\2",
      $container_id,
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
 * Improved equivalent to strtotime()
 *
 * @param string Date string
 * @return int 
 */
function rcube_strtotime($date)
{
  // check for MS Outlook vCard date format YYYYMMDD
  if (preg_match('/^([12][90]\d\d)([01]\d)(\d\d)$/', trim($date), $matches)) {
    return mktime(0,0,0, intval($matches[2]), intval($matches[3]), intval($matches[1]));
  }
  else if (is_numeric($date))
    return $date;

  // support non-standard "GMTXXXX" literal
  $date = preg_replace('/GMT\s*([+-][0-9]+)/', '\\1', $date);

  // if date parsing fails, we have a date in non-rfc format.
  // remove token from the end and try again
  while ((($ts = @strtotime($date)) === false) || ($ts < 0)) {
    $d = explode(' ', $date);
    array_pop($d);
    if (!$d) break;
    $date = implode(' ', $d);
  }

  return $ts;
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

  if (!empty($date))
    $ts = rcube_strtotime($date);

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
  $week_limit  = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);

  // define date format depending on current time
  if (!$format) {
    if ($CONFIG['prettydate'] && $timestamp > $today_limit && $timestamp < $now) {
      $format = $CONFIG['date_today'] ? $CONFIG['date_today'] : 'H:i';
      $today  = true;
    }
    else if ($CONFIG['prettydate'] && $timestamp > $week_limit && $timestamp < $now)
      $format = $CONFIG['date_short'] ? $CONFIG['date_short'] : 'D H:i';
    else
      $format = $CONFIG['date_long'] ? $CONFIG['date_long'] : 'd.m.Y H:i';
  }

  // strftime() format
  if (preg_match('/%[a-z]+/i', $format)) {
    $format = strftime($format, $timestamp);
    return $today ? (rcube_label('today') . ' ' . $format) : $format;
  }

  // parse format string manually in order to provide localized weekday and month names
  // an alternative would be to convert the date() format string to fit with strftime()
  $out = '';
  for($i=0; $i<strlen($format); $i++) {
    if ($format[$i]=='\\')  // skip escape chars
      continue;

    // write char "as-is"
    if ($format[$i]==' ' || $format{$i-1}=='\\')
      $out .= $format[$i];
    // weekday (short)
    else if ($format[$i]=='D')
      $out .= rcube_label(strtolower(date('D', $timestamp)));
    // weekday long
    else if ($format[$i]=='l')
      $out .= rcube_label(strtolower(date('l', $timestamp)));
    // month name (short)
    else if ($format[$i]=='M')
      $out .= rcube_label(strtolower(date('M', $timestamp)));
    // month name (long)
    else if ($format[$i]=='F')
      $out .= rcube_label('long'.strtolower(date('M', $timestamp)));
    else if ($format[$i]=='x')
      $out .= strftime('%x %X', $timestamp);
    else
      $out .= date($format[$i], $timestamp);
  }

  if ($today) {
    $label = rcube_label('today');
    // replcae $ character with "Today" label (#1486120)
    if (strpos($out, '$') !== false) {
      $out = preg_replace('/\$/', $label, $out, 1);
    }
    else {
      $out = $label . ' ' . $out;
    }
  }

  return $out;
}


/**
 * Compose a valid representation of name and e-mail address
 *
 * @param string E-mail address
 * @param string Person name
 * @return string Formatted string
 */
function format_email_recipient($email, $name='')
{
  if ($name && $name != $email) {
    // Special chars as defined by RFC 822 need to in quoted string (or escaped).
    return sprintf('%s <%s>', preg_match('/[\(\)\<\>\\\.\[\]@,;:"]/', $name) ? '"'.addcslashes($name, '"').'"' : $name, trim($email));
  }

  return trim($email);
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

  $attrib += array('maxlength' => 100, 'realnames' => false);

  // add some labels to client
  $RCMAIL->output->add_label('purgefolderconfirm', 'deletemessagesconfirm');

  $type = $attrib['type'] ? $attrib['type'] : 'ul';
  unset($attrib['type']);

  if ($type=='ul' && !$attrib['id'])
    $attrib['id'] = 'rcmboxlist';

  if (empty($attrib['folder_name']))
    $attrib['folder_name'] = '*';

  // get mailbox list
  $mbox_name = $RCMAIL->imap->get_mailbox_name();

  // build the folders tree
  if (empty($a_mailboxes)) {
    // get mailbox list
    $a_folders = $RCMAIL->imap->list_mailboxes('', $attrib['folder_name'], $attrib['folder_filter']);
    $delimiter = $RCMAIL->imap->get_hierarchy_delimiter();
    $a_mailboxes = array();

    foreach ($a_folders as $folder)
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
  }

  // allow plugins to alter the folder tree or to localize folder names
  $hook = $RCMAIL->plugins->exec_hook('render_mailboxlist', array('list' => $a_mailboxes, 'delimiter' => $delimiter));

  if ($type == 'select') {
    $select = new html_select($attrib);

    // add no-selection option
    if ($attrib['noselection'])
      $select->add(rcube_label($attrib['noselection']), '');

    rcmail_render_folder_tree_select($hook['list'], $mbox_name, $attrib['maxlength'], $select, $attrib['realnames']);
    $out = $select->show();
  }
  else {
    $js_mailboxlist = array();
    $out = html::tag('ul', $attrib, rcmail_render_folder_tree_html($hook['list'], $mbox_name, $js_mailboxlist, $attrib), html::$common_attrib);

    $RCMAIL->output->add_gui_object('mailboxlist', $attrib['id']);
    $RCMAIL->output->set_env('mailboxes', $js_mailboxlist);
    $RCMAIL->output->set_env('collapsed_folders', (string)$RCMAIL->config->get('collapsed_folders'));
  }

  return $out;
}


/**
 * Return the mailboxlist as html_select object
 *
 * @param array Named parameters
 * @return html_select HTML drop-down object
 */
function rcmail_mailbox_select($p = array())
{
  global $RCMAIL;

  $p += array('maxlength' => 100, 'realnames' => false);
  $a_mailboxes = array();

  if (empty($p['folder_name']))
    $p['folder_name'] = '*';

  if ($p['unsubscribed'])
    $list = $RCMAIL->imap->list_unsubscribed('', $p['folder_name'], $p['folder_filter']);
  else
    $list = $RCMAIL->imap->list_mailboxes('', $p['folder_name'], $p['folder_filter']);

  $delimiter = $RCMAIL->imap->get_hierarchy_delimiter();

  foreach ($list as $folder) {
    if (empty($p['exceptions']) || !in_array($folder, $p['exceptions']))
      rcmail_build_folder_tree($a_mailboxes, $folder, $delimiter);
  }

  $select = new html_select($p);

  if ($p['noselection'])
    $select->add($p['noselection'], '');

  rcmail_render_folder_tree_select($a_mailboxes, $mbox, $p['maxlength'], $select, $p['realnames'], 0, $p['exceptions']);

  return $select;
}


/**
 * Create a hierarchical array of the mailbox list
 * @access private
 * @return void
 */
function rcmail_build_folder_tree(&$arrFolders, $folder, $delm='/', $path='')
{
  global $RCMAIL;

  // Handle namespace prefix
  $prefix = '';
  if (!$path) {
    $n_folder = $folder;
    $folder = $RCMAIL->imap->mod_mailbox($folder);

    if ($n_folder != $folder) {
      $prefix = substr($n_folder, 0, -strlen($folder));
    }
  }

  $pos = strpos($folder, $delm);

  if ($pos !== false) {
    $subFolders = substr($folder, $pos+1);
    $currentFolder = substr($folder, 0, $pos);

    // sometimes folder has a delimiter as the last character
    if (!strlen($subFolders))
      $virtual = false;
    else if (!isset($arrFolders[$currentFolder]))
      $virtual = true;
    else
      $virtual = $arrFolders[$currentFolder]['virtual'];
  }
  else {
    $subFolders = false;
    $currentFolder = $folder;
    $virtual = false;
  }

  $path .= $prefix.$currentFolder;

  if (!isset($arrFolders[$currentFolder])) {
    // Check \Noselect option (if options are in cache)
    if (!$virtual && ($opts = $RCMAIL->imap->mailbox_options($path))) {
      $virtual = in_array('\\Noselect', $opts);
    }

    $arrFolders[$currentFolder] = array(
      'id' => $path,
      'name' => rcube_charset_convert($currentFolder, 'UTF7-IMAP'),
      'virtual' => $virtual,
      'folders' => array());
  }
  else
    $arrFolders[$currentFolder]['virtual'] = $virtual;

  if (strlen($subFolders))
    rcmail_build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
}


/**
 * Return html for a structured list &lt;ul&gt; for the mailbox tree
 * @access private
 * @return string
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
    $folder_id = html_identifier($folder['id']);
    $classes = array('mailbox');

    // set special class for Sent, Drafts, Trash and Junk
    if ($folder['id'] == $CONFIG['sent_mbox'])
      $classes[] = 'sent';
    else if ($folder['id'] == $CONFIG['drafts_mbox'])
      $classes[] = 'drafts';
    else if ($folder['id'] == $CONFIG['trash_mbox'])
      $classes[] = 'trash';
    else if ($folder['id'] == $CONFIG['junk_mbox'])
      $classes[] = 'junk';
    else if ($folder['id'] == 'INBOX')
      $classes[] = 'inbox';
    else
      $classes[] = '_'.asciiwords($folder_class ? $folder_class : strtolower($folder['id']), true);

    $classes[] = $zebra_class;

    if ($folder['id'] == $mbox_name)
      $classes[] = 'selected';

    $collapsed = strpos($CONFIG['collapsed_folders'], '&'.rawurlencode($folder['id']).'&') !== false;
    $unread = $msgcounts ? intval($msgcounts[$folder['id']]['UNSEEN']) : 0;

    if ($folder['virtual'])
      $classes[] = 'virtual';
    else if ($unread)
      $classes[] = 'unread';

    $js_name = JQ($folder['id']);
    $html_name = Q($foldername) . ($unread ? html::span('unreadcount', " ($unread)") : '');
    $link_attrib = $folder['virtual'] ? array() : array(
      'href' => rcmail_url('', array('_mbox' => $folder['id'])),
      'onclick' => sprintf("return %s.command('list','%s',this)", JS_OBJECT_NAME, $js_name),
      'rel' => $folder['id'],
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
 * @return string
 */
function rcmail_render_folder_tree_select(&$arrFolders, &$mbox_name, $maxlength, &$select, $realnames=false, $nestLevel=0, $exceptions=array())
{
  $out = '';

  foreach ($arrFolders as $key => $folder) {
    if (empty($exceptions) || !in_array($folder['id'], $exceptions)) {
      if (!$realnames && ($folder_class = rcmail_folder_classname($folder['id'])))
        $foldername = rcube_label($folder_class);
      else {
        $foldername = $folder['name'];

        // shorten the folder name to a given length
        if ($maxlength && $maxlength>1)
          $foldername = abbreviate_string($foldername, $maxlength);
      }

      $select->add(str_repeat('&nbsp;', $nestLevel*4) . $foldername, $folder['id']);
    }
    else if ($nestLevel)
      continue;

    if (!empty($folder['folders']))
      $out .= rcmail_render_folder_tree_select($folder['folders'], $mbox_name, $maxlength,
        $select, $realnames, $nestLevel+1, $exceptions);
  }

  return $out;
}


/**
 * Return internal name for the given folder if it matches the configured special folders
 * @access private
 * @return string
 */
function rcmail_folder_classname($folder_id)
{
  global $CONFIG;

  if ($folder_id == 'INBOX')
    return 'inbox';

  // for these mailboxes we have localized labels and css classes
  foreach (array('sent', 'drafts', 'trash', 'junk') as $smbx)
  {
    if ($folder_id == $CONFIG[$smbx.'_mbox'])
      return $smbx;
  }
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


function rcmail_localize_folderpath($path)
{
    global $RCMAIL;

    $protect_folders = $RCMAIL->config->get('protect_default_folders');
    $default_folders = (array) $RCMAIL->config->get('default_imap_folders');
    $delimiter       = $RCMAIL->imap->get_hierarchy_delimiter();
    $path            = explode($delimiter, $path);
    $result          = array();

    foreach ($path as $idx => $dir) {
        $directory = implode($delimiter, array_slice($path, 0, $idx+1));
        if ($protect_folders && in_array($directory, $default_folders)) {
            unset($result);
            $result[] = rcmail_localize_foldername($directory);
        }
        else {
            $result[] = rcube_charset_convert($dir, 'UTF7-IMAP');
        }
    }

    return implode($delimiter, $result);
}


function rcmail_quota_display($attrib)
{
  global $OUTPUT;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmquotadisplay';

  if(isset($attrib['display']))
    $_SESSION['quota_display'] = $attrib['display'];

  $OUTPUT->add_gui_object('quotadisplay', $attrib['id']);

  $quota = rcmail_quota_content($attrib);

  $OUTPUT->add_script('rcmail.set_quota('.json_serialize($quota).');', 'docready');

  return html::span($attrib, '');
}


function rcmail_quota_content($attrib=NULL)
{
  global $RCMAIL;

  $quota = $RCMAIL->imap->get_quota();
  $quota = $RCMAIL->plugins->exec_hook('quota', $quota);

  $quota_result = (array) $quota;
  $quota_result['type'] = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';

  if (!$quota['total'] && $RCMAIL->config->get('quota_zero_as_unlimited')) {
    $quota_result['title'] = rcube_label('unlimited');
    $quota_result['percent'] = 0;
  }
  else if ($quota['total']) {
    if (!isset($quota['percent']))
      $quota_result['percent'] = min(100, round(($quota['used']/max(1,$quota['total']))*100));

    $title = sprintf('%s / %s (%.0f%%)',
        show_bytes($quota['used'] * 1024), show_bytes($quota['total'] * 1024),
        $quota_result['percent']);

    $quota_result['title'] = $title;

    if ($attrib['width'])
      $quota_result['width'] = $attrib['width'];
    if ($attrib['height'])
      $quota_result['height']	= $attrib['height'];
  }
  else {
    $quota_result['title'] = rcube_label('unknown');
    $quota_result['percent'] = 0;
  }

  return $quota_result;
}


/**
 * Outputs error message according to server error/response codes
 *
 * @param string Fallback message label
 * @param string Fallback message label arguments
 *
 * @return void
 */
function rcmail_display_server_error($fallback=null, $fallback_args=null)
{
    global $RCMAIL;

    $err_code = $RCMAIL->imap->get_error_code();
    $res_code = $RCMAIL->imap->get_response_code();

    if ($res_code == rcube_imap::NOPERM) {
        $RCMAIL->output->show_message('errornoperm', 'error');
    }
    else if ($res_code == rcube_imap::READONLY) {
        $RCMAIL->output->show_message('errorreadonly', 'error');
    }
    else if ($err_code && ($err_str = $RCMAIL->imap->get_error_str())) {
        $RCMAIL->output->show_message('servererrormsg', 'error', array('msg' => $err_str));
    }
    else if ($fallback) {
        $RCMAIL->output->show_message($fallback, 'error', $fallback_args);
    }

    return true;
}


/**
 * Output HTML editor scripts
 *
 * @param string Editor mode
 * @return void
 */
function rcube_html_editor($mode='')
{
  global $RCMAIL, $CONFIG;

  $hook = $RCMAIL->plugins->exec_hook('html_editor', array('mode' => $mode));

  if ($hook['abort'])
    return;  

  $lang = strtolower($_SESSION['language']);

  // TinyMCE uses 'tw' for zh_TW (which is wrong, because tw is a code of Twi language)
  $lang = ($lang == 'zh_tw') ? 'tw' : substr($lang, 0, 2);

  if (!file_exists(INSTALL_PATH . 'program/js/tiny_mce/langs/'.$lang.'.js'))
    $lang = 'en';

  $RCMAIL->output->include_script('tiny_mce/tiny_mce.js');
  $RCMAIL->output->include_script('editor.js');
  $RCMAIL->output->add_script(sprintf("rcmail_editor_init('\$__skin_path', '%s', %d, '%s');",
    JQ($lang), intval($CONFIG['enable_spellcheck']), $mode),
    'foot');
}


/**
 * Replaces TinyMCE's emoticon images with plain-text representation
 *
 * @param string HTML content
 * @return string HTML content
 */
function rcmail_replace_emoticons($html)
{
  $emoticons = array(
    '8-)' => 'smiley-cool',
    ':-#' => 'smiley-foot-in-mouth',
    ':-*' => 'smiley-kiss',
    ':-X' => 'smiley-sealed',
    ':-P' => 'smiley-tongue-out',
    ':-@' => 'smiley-yell',
    ":'(" => 'smiley-cry',
    ':-(' => 'smiley-frown',
    ':-D' => 'smiley-laughing',
    ':-)' => 'smiley-smile',
    ':-S' => 'smiley-undecided',
    ':-$' => 'smiley-embarassed',
    'O:-)' => 'smiley-innocent',
    ':-|' => 'smiley-money-mouth',
    ':-O' => 'smiley-surprised',
    ';-)' => 'smiley-wink',
  );

  foreach ($emoticons as $idx => $file) {
    // <img title="Cry" src="http://.../program/js/tiny_mce/plugins/emotions/img/smiley-cry.gif" border="0" alt="Cry" />
    $search[]  = '/<img title="[a-z ]+" src="https?:\/\/[a-z0-9_.\/-]+\/tiny_mce\/plugins\/emotions\/img\/'.$file.'.gif"[^>]+\/>/i';
    $replace[] = $idx;
  }

  return preg_replace($search, $replace, $html);
}


/**
 * Send the given message using the configured method
 *
 * @param object $message    Reference to Mail_MIME object
 * @param string $from       Sender address string
 * @param array  $mailto     Array of recipient address strings
 * @param array  $smtp_error SMTP error array (reference)
 * @param string $body_file  Location of file with saved message body (reference)
 * @param array  $smtp_opts  SMTP options (e.g. DSN request)
 *
 * @return boolean Send status.
 */
function rcmail_deliver_message(&$message, $from, $mailto, &$smtp_error, &$body_file, $smtp_opts=null)
{
  global $CONFIG, $RCMAIL;

  $headers = $message->headers();

  // send thru SMTP server using custom SMTP library
  if ($CONFIG['smtp_server']) {
    // generate list of recipients
    $a_recipients = array($mailto);

    if (strlen($headers['Cc']))
      $a_recipients[] = $headers['Cc'];
    if (strlen($headers['Bcc']))
      $a_recipients[] = $headers['Bcc'];

    // clean Bcc from header for recipients
    $send_headers = $headers;
    unset($send_headers['Bcc']);
    // here too, it because txtHeaders() below use $message->_headers not only $send_headers
    unset($message->_headers['Bcc']);

    $smtp_headers = $message->txtHeaders($send_headers, true);

    if ($message->getParam('delay_file_io')) {
      // use common temp dir
      $temp_dir = $RCMAIL->config->get('temp_dir');
      $body_file = tempnam($temp_dir, 'rcmMsg');
      if (PEAR::isError($mime_result = $message->saveMessageBody($body_file))) {
        raise_error(array('code' => 650, 'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not create message: ".$mime_result->getMessage()),
            TRUE, FALSE);
        return false;
      }
      $msg_body = fopen($body_file, 'r');
    } else {
      $msg_body = $message->get();
    }

    // send message
    if (!is_object($RCMAIL->smtp))
      $RCMAIL->smtp_init(true);

    $sent = $RCMAIL->smtp->send_mail($from, $a_recipients, $smtp_headers, $msg_body, $smtp_opts);
    $smtp_response = $RCMAIL->smtp->get_response();
    $smtp_error = $RCMAIL->smtp->get_error();

    // log error
    if (!$sent)
      raise_error(array('code' => 800, 'type' => 'smtp', 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "SMTP error: ".join("\n", $smtp_response)), TRUE, FALSE);
  }
  // send mail using PHP's mail() function
  else {
    // unset some headers because they will be added by the mail() function
    $headers_enc = $message->headers($headers);
    $headers_php = $message->_headers;
    unset($headers_php['To'], $headers_php['Subject']);

    // reset stored headers and overwrite
    $message->_headers = array();
    $header_str = $message->txtHeaders($headers_php);

    // #1485779
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      if (preg_match_all('/<([^@]+@[^>]+)>/', $headers_enc['To'], $m)) {
        $headers_enc['To'] = implode(', ', $m[1]);
      }
    }

    $msg_body = $message->get();

    if (PEAR::isError($msg_body))
      raise_error(array('code' => 650, 'type' => 'php',
            'file' => __FILE__, 'line' => __LINE__,
            'message' => "Could not create message: ".$msg_body->getMessage()),
            TRUE, FALSE);
    else {
      $delim   = $RCMAIL->config->header_delimiter();
      $to      = $headers_enc['To'];
      $subject = $headers_enc['Subject'];
      $header_str = rtrim($header_str);

      if ($delim != "\r\n") {
        $header_str = str_replace("\r\n", $delim, $header_str);
        $msg_body   = str_replace("\r\n", $delim, $msg_body);
        $to         = str_replace("\r\n", $delim, $to);
        $subject    = str_replace("\r\n", $delim, $subject);
      }

      if (ini_get('safe_mode'))
        $sent = mail($to, $subject, $msg_body, $header_str);
      else
        $sent = mail($to, $subject, $msg_body, $header_str, "-f$from");
    }
  }

  if ($sent) {
    $RCMAIL->plugins->exec_hook('message_sent', array('headers' => $headers, 'body' => $msg_body));

    // remove MDN headers after sending
    unset($headers['Return-Receipt-To'], $headers['Disposition-Notification-To']);

    // get all recipients
    if ($headers['Cc'])
      $mailto .= $headers['Cc'];
    if ($headers['Bcc'])
      $mailto .= $headers['Bcc'];
    if (preg_match_all('/<([^@]+@[^>]+)>/', $mailto, $m))
      $mailto = implode(', ', array_unique($m[1]));

    if ($CONFIG['smtp_log']) {
      write_log('sendmail', sprintf("User %s [%s]; Message for %s; %s",
        $RCMAIL->user->get_username(),
        $_SERVER['REMOTE_ADDR'],
        $mailto,
        !empty($smtp_response) ? join('; ', $smtp_response) : ''));
    }
  }

  if (is_resource($msg_body)) {
    fclose($msg_body);
  }

  $message->_headers = array();
  $message->headers($headers);

  return $sent;
}


// Returns unique Message-ID
function rcmail_gen_message_id()
{
  global $RCMAIL;

  $local_part  = md5(uniqid('rcmail'.mt_rand(),true));
  $domain_part = $RCMAIL->user->get_username('domain');

  // Try to find FQDN, some spamfilters doesn't like 'localhost' (#1486924)
  if (!preg_match('/\.[a-z]+$/i', $domain_part)) {
    if (($host = preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST']))
      && preg_match('/\.[a-z]+$/i', $host)) {
        $domain_part = $host;
    }
    else if (($host = preg_replace('/:[0-9]+$/', '', $_SERVER['SERVER_NAME']))
      && preg_match('/\.[a-z]+$/i', $host)) {
        $domain_part = $host;
    }
  }

  return sprintf('<%s@%s>', $local_part, $domain_part);
}


// Returns RFC2822 formatted current date in user's timezone
function rcmail_user_date()
{
  global $CONFIG;

  // get user's timezone
  if ($CONFIG['timezone'] === 'auto') {
    $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
  }
  else {
    $tz = $CONFIG['timezone'];
    if ($CONFIG['dst_active'])
      $tz++;
  }

  $date = time() + $tz * 60 * 60;
  $date = gmdate('r', $date);
  $tz   = sprintf('%+05d', intval($tz) * 100 + ($tz - intval($tz)) * 60);
  $date = preg_replace('/[+-][0-9]{4}$/', $tz, $date);

  return $date;
}


/**
 * Check if working in SSL mode
 *
 * @param integer HTTPS port number
 * @param boolean Enables 'use_https' option checking
 * @return boolean
 */
function rcube_https_check($port=null, $use_https=true)
{
  global $RCMAIL;

  if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
    return true;
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
    return true;
  if ($port && $_SERVER['SERVER_PORT'] == $port)
    return true;
  if ($use_https && isset($RCMAIL) && $RCMAIL->config->get('use_https'))
    return true;

  return false;
}


/**
 * For backward compatibility.
 *
 * @global rcmail $RCMAIL
 * @param string $var_name Variable name.
 * @return void
 */
function rcube_sess_unset($var_name=null)
{
  global $RCMAIL;

  $RCMAIL->session->remove($var_name);
}


/**
 * Replaces hostname variables
 *
 * @param string $name Hostname
 * @param string $host Optional IMAP hostname
 * @return string
 */
function rcube_parse_host($name, $host='')
{
  // %n - host
  $n = preg_replace('/:\d+$/', '', $_SERVER['SERVER_NAME']);
  // %d - domain name without first part, e.g. %n=mail.domain.tld, %d=domain.tld
  $d = preg_replace('/^[^\.]+\./', '', $n);
  // %h - IMAP host
  $h = $_SESSION['imap_host'] ? $_SESSION['imap_host'] : $host;
  // %z - IMAP domain without first part, e.g. %h=imap.domain.tld, %z=domain.tld
  $z = preg_replace('/^[^\.]+\./', '', $h);
  // %s - domain name after the '@' from e-mail address provided at login screen. Returns FALSE if an invalid email is provided
  if ( strpos($name, '%s') !== false ){
    $user_email = rcube_idn_convert(get_input_value('_user', RCUBE_INPUT_POST), true);
    if ( preg_match('/(.*)@([a-z0-9\.\-\[\]\:]+)/i', $user_email, $s) < 1 || filter_var($s[1]."@".$s[2], FILTER_VALIDATE_EMAIL) === false )
      return false;
  }

  $name = str_replace(array('%n', '%d', '%h', '%z', '%s'), array($n, $d, $h, $z, $s[2]), $name);
  return $name;
}


/**
 * E-mail address validation
 *
 * @param string $email Email address
 * @param boolean $dns_check True to check dns
 * @return boolean
 */
function check_email($email, $dns_check=true)
{
  // Check for invalid characters
  if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email))
    return false;

  // Check for length limit specified by RFC 5321 (#1486453)
  if (strlen($email) > 254) 
    return false;

  $email_array = explode('@', $email);

  // Check that there's one @ symbol
  if (count($email_array) < 2)
    return false;

  $domain_part = array_pop($email_array);
  $local_part = implode('@', $email_array);

  // from PEAR::Validate
  $regexp = '&^(?:
	("\s*(?:[^"\f\n\r\t\v\b\s]+\s*)+")| 			 	#1 quoted name
	([-\w!\#\$%\&\'*+~/^`|{}=]+(?:\.[-\w!\#\$%\&\'*+~/^`|{}=]+)*)) 	#2 OR dot-atom (RFC5322)
	$&xi';

  if (!preg_match($regexp, $local_part))
    return false;

  // Check domain part
  if (preg_match('/^\[*(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]*$/', $domain_part))
    return true; // IP address
  else {
    // If not an IP address
    $domain_array = explode('.', $domain_part);
    if (sizeof($domain_array) < 2)
      return false; // Not enough parts to be a valid domain

    foreach ($domain_array as $part)
      if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]))$/', $part))
        return false;

    if (!$dns_check || !rcmail::get_instance()->config->get('email_dns_check'))
      return true;

    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' && version_compare(PHP_VERSION, '5.3.0', '<')) {
      $lookup = array();
      @exec("nslookup -type=MX " . escapeshellarg($domain_part) . " 2>&1", $lookup);
      foreach ($lookup as $line) {
        if (strpos($line, 'MX preference'))
          return true;
      }
      return false;
    }

    // find MX record(s)
    if (getmxrr($domain_part, $mx_records))
      return true;

    // find any DNS record
    if (checkdnsrr($domain_part, 'ANY'))
      return true;
  }

  return false;
}

/*
 * Idn_to_ascii wrapper.
 * Intl/Idn modules version of this function doesn't work with e-mail address
 */
function rcube_idn_to_ascii($str)
{
  return rcube_idn_convert($str, true);
}

/*
 * Idn_to_ascii wrapper.
 * Intl/Idn modules version of this function doesn't work with e-mail address
 */
function rcube_idn_to_utf8($str)
{
  return rcube_idn_convert($str, false);
}

function rcube_idn_convert($input, $is_utf=false)
{
  if ($at = strpos($input, '@')) {
    $user   = substr($input, 0, $at);
    $domain = substr($input, $at+1);
  }
  else {
    $domain = $input;
  }

  $domain = $is_utf ? idn_to_ascii($domain) : idn_to_utf8($domain);

  if ($domain === false) {
    return '';
  }

  return $at ? $user . '@' . $domain : $domain;
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


/****** debugging and logging functions ********/

/**
 * Print or write debug messages
 *
 * @param mixed Debug message or data
 * @return void
 */
function console()
{
    $args = func_get_args();

    if (class_exists('rcmail', false)) {
        $rcmail = rcmail::get_instance();
        if (is_object($rcmail->plugins)) {
            $plugin = $rcmail->plugins->exec_hook('console', array('args' => $args));
            if ($plugin['abort'])
                return;
            $args = $plugin['args'];
        }
    }

    $msg = array();
    foreach ($args as $arg)
        $msg[] = !is_string($arg) ? var_export($arg, true) : $arg;

    write_log('console', join(";\n", $msg));
}


/**
 * Append a line to a logfile in the logs directory.
 * Date will be added automatically to the line.
 *
 * @param $name name of log file
 * @param line Line to append
 * @return void
 */
function write_log($name, $line)
{
  global $CONFIG, $RCMAIL;

  if (!is_string($line))
    $line = var_export($line, true);
 
  if (empty($CONFIG['log_date_format']))
    $CONFIG['log_date_format'] = 'd-M-Y H:i:s O';
  
  $date = date($CONFIG['log_date_format']);
  
  // trigger logging hook
  if (is_object($RCMAIL) && is_object($RCMAIL->plugins)) {
    $log = $RCMAIL->plugins->exec_hook('write_log', array('name' => $name, 'date' => $date, 'line' => $line));
    $name = $log['name'];
    $line = $log['line'];
    $date = $log['date'];
    if ($log['abort'])
      return true;
  }
 
  if ($CONFIG['log_driver'] == 'syslog') {
    $prio = $name == 'errors' ? LOG_ERR : LOG_INFO;
    syslog($prio, $line);
    return true;
  }
  else {
    $line = sprintf("[%s]: %s\n", $date, $line);

    // log_driver == 'file' is assumed here
    if (empty($CONFIG['log_dir']))
      $CONFIG['log_dir'] = INSTALL_PATH.'logs';

    // try to open specific log file for writing
    $logfile = $CONFIG['log_dir'].'/'.$name;
    if ($fp = @fopen($logfile, 'a')) {
      fwrite($fp, $line);
      fflush($fp);
      fclose($fp);
      return true;
    }
    else
      trigger_error("Error writing to log file $logfile; Please check permissions", E_USER_WARNING);
  }

  return false;
}


/**
 * Write login data (name, ID, IP address) to the 'userlogins' log file.
 *
 * @return void
 */
function rcmail_log_login()
{
  global $RCMAIL;

  if (!$RCMAIL->config->get('log_logins') || !$RCMAIL->user)
    return;

  write_log('userlogins', sprintf('Successful login for %s (ID: %d) from %s in session %s',
    $RCMAIL->user->get_username(), $RCMAIL->user->ID, rcmail_remote_ip(), session_id()));
}


/**
 * Returns remote IP address and forwarded addresses if found
 *
 * @return string Remote IP address(es)
 */
function rcmail_remote_ip()
{
    $address = $_SERVER['REMOTE_ADDR'];

    // append the NGINX X-Real-IP header, if set
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $remote_ip[] = 'X-Real-IP: ' . $_SERVER['HTTP_X_REAL_IP'];
    }
    // append the X-Forwarded-For header, if set
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $remote_ip[] = 'X-Forwarded-For: ' . $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (!empty($remote_ip))
        $address .= '(' . implode(',', $remote_ip) . ')';

    return $address;
}


/**
 * Check whether the HTTP referer matches the current request
 *
 * @return boolean True if referer is the same host+path, false if not
 */
function rcube_check_referer()
{
  $uri = parse_url($_SERVER['REQUEST_URI']);
  $referer = parse_url(rc_request_header('Referer'));
  return $referer['host'] == rc_request_header('Host') && $referer['path'] == $uri['path'];
}


/**
 * @access private
 * @return mixed
 */
function rcube_timer()
{
  return microtime(true);
}


/**
 * @access private
 * @return void
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
 * Throw system error and show error page
 *
 * @param array Named parameters
 *  - code: Error code (required)
 *  - type: Error type [php|db|imap|javascript] (required)
 *  - message: Error message
 *  - file: File where error occured
 *  - line: Line where error occured
 * @param boolean True to log the error
 * @param boolean Terminate script execution
 */
// may be defined in Installer
if (!function_exists('raise_error')) {
function raise_error($arg=array(), $log=false, $terminate=false)
{
    global $__page_content, $CONFIG, $OUTPUT, $ERROR_CODE, $ERROR_MESSAGE;

    // report bug (if not incompatible browser)
    if ($log && $arg['type'] && $arg['message'])
        rcube_log_bug($arg);

    // display error page and terminate script
    if ($terminate) {
        $ERROR_CODE = $arg['code'];
        $ERROR_MESSAGE = $arg['message'];
        include INSTALL_PATH . 'program/steps/utils/error.inc';
        exit;
    }
}
}


/**
 * Report error according to configured debug_level
 *
 * @param array Named parameters
 * @return void
 * @see raise_error()
 */
function rcube_log_bug($arg_arr)
{
    global $CONFIG;

    $program = strtoupper($arg_arr['type']);
    $level   = $CONFIG['debug_level'];

    // disable errors for ajax requests, write to log instead (#1487831)
    if (($level & 4) && !empty($_REQUEST['_remote'])) {
        $level = ($level ^ 4) | 1;
    }

    // write error to local log file
    if ($level & 1) {
        $post_query = ($_SERVER['REQUEST_METHOD'] == 'POST' ? '?_task='.urlencode($_POST['_task']).'&_action='.urlencode($_POST['_action']) : '');
        $log_entry = sprintf("%s Error: %s%s (%s %s)",
            $program,
            $arg_arr['message'],
            $arg_arr['file'] ? sprintf(' in %s on line %d', $arg_arr['file'], $arg_arr['line']) : '',
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI'] . $post_query);

        if (!write_log('errors', $log_entry)) {
            // send error to PHPs error handler if write_log didn't succeed
            trigger_error($arg_arr['message']);
        }
    }

    // report the bug to the global bug reporting system
    if ($level & 2) {
        // TODO: Send error via HTTP
    }

    // show error if debug_mode is on
    if ($level & 4) {
        print "<b>$program Error";

        if (!empty($arg_arr['file']) && !empty($arg_arr['line']))
            print " in $arg_arr[file] ($arg_arr[line])";

        print ':</b>&nbsp;';
        print nl2br($arg_arr['message']);
        print '<br />';
        flush();
    }
}

function rcube_upload_progress()
{
    global $RCMAIL;

    $prefix = ini_get('apc.rfc1867_prefix');
    $params = array(
        'action' => $RCMAIL->action,
        'name' => get_input_value('_progress', RCUBE_INPUT_GET),
    );

    if (function_exists('apc_fetch')) {
        $status = apc_fetch($prefix . $params['name']);

        if (!empty($status)) {
            $status['percent'] = round($status['current']/$status['total']*100);
            $params = array_merge($status, $params);
        }
    }

    if (isset($params['percent']))
        $params['text'] = rcube_label(array('name' => 'uploadprogress', 'vars' => array(
            'percent' => $params['percent'] . '%',
            'current' => show_bytes($params['current']),
            'total'   => show_bytes($params['total'])
        )));

    $RCMAIL->output->command('upload_progress_update', $params);
    $RCMAIL->output->send();
}

function rcube_upload_init()
{
    global $RCMAIL;

    // Enable upload progress bar
    if (($seconds = $RCMAIL->config->get('upload_progress')) && ini_get('apc.rfc1867')) {
        if ($field_name = ini_get('apc.rfc1867_name')) {
            $RCMAIL->output->set_env('upload_progress_name', $field_name);
            $RCMAIL->output->set_env('upload_progress_time', (int) $seconds);
        }
    }

    // find max filesize value
    $max_filesize = parse_bytes(ini_get('upload_max_filesize'));
    $max_postsize = parse_bytes(ini_get('post_max_size'));
    if ($max_postsize && $max_postsize < $max_filesize)
        $max_filesize = $max_postsize;

    $RCMAIL->output->set_env('max_filesize', $max_filesize);
    $max_filesize = show_bytes($max_filesize);
    $RCMAIL->output->set_env('filesizeerror', rcube_label(array(
        'name' => 'filesizeerror', 'vars' => array('size' => $max_filesize))));

    return $max_filesize;
}

/**
 * Initializes client-side autocompletion
 */
function rcube_autocomplete_init()
{
    global $RCMAIL;
    static $init;

    if ($init)
        return;

    $init = 1;

    if (($threads = (int)$RCMAIL->config->get('autocomplete_threads')) > 0) {
      $book_types = (array) $RCMAIL->config->get('autocomplete_addressbooks', 'sql');
      if (count($book_types) > 1) {
        $RCMAIL->output->set_env('autocomplete_threads', $threads);
        $RCMAIL->output->set_env('autocomplete_sources', $book_types);
      }
    }

    $RCMAIL->output->set_env('autocomplete_max', (int)$RCMAIL->config->get('autocomplete_max', 15));
    $RCMAIL->output->set_env('autocomplete_min_length', $RCMAIL->config->get('autocomplete_min_length'));
    $RCMAIL->output->add_label('autocompletechars');
}
