<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev, - Switzerland                 |
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
    {
    $db = &rcmail::get_instance()->db;
    
    if ($db->db_provider=='pgsql')
      {
      $db->db_handle->setOption('disable_smart_seqname', true);
      $db->db_handle->setOption('seqname_format', '%s');
      }
  
    return $opt;
    }
    
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
function rcube_label($p)
{
  return rcmail::get_instance()->gettext($p);
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
 * Add a localized label to the client environment
 * @deprecated
 */
function rcube_add_label()
  {
  global $OUTPUT;
  
  $arg_list = func_get_args();
  foreach ($arg_list as $i => $name)
    $OUTPUT->add_label($name);
  }


/**
 * Garbage collector function for temp files.
 * Remove temp files older than two days
 */
function rcmail_temp_gc()
  {
  $tmp = unslashify($CONFIG['temp_dir']);
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
function rcmail_message_cache_gc()
  {
  global $DB, $CONFIG;
  
  // no cache lifetime configured
  if (empty($CONFIG['message_cache_lifetime']))
    return;
  
  // get target timestamp
  $ts = get_offset_time($CONFIG['message_cache_lifetime'], -1);
  
  $DB->query("DELETE FROM ".get_table_name('messages')."
             WHERE  created < ".$DB->fromunixtime($ts));
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
  static $mbstring_loaded = null, $convert_warning = false;

  $from = strtoupper($from);
  $to = $to==NULL ? strtoupper(RCMAIL_CHARSET) : strtoupper($to);
  $error = false; $conv = null;

  if ($from==$to || $str=='' || empty($from))
    return $str;
    
  $aliases = array(
    'UNKNOWN-8BIT'   => 'ISO-8859-15',
    'X-UNKNOWN'      => 'ISO-8859-15',
    'X-USER-DEFINED' => 'ISO-8859-15',
    'ISO-8859-8-I'   => 'ISO-8859-8',
    'KS_C_5601-1987' => 'EUC-KR',
  );

  // convert charset using iconv module  
  if (function_exists('iconv') && $from != 'UTF-7' && $to != 'UTF-7')
    {
    $aliases['GB2312'] = 'GB18030';
    $_iconv = iconv(($aliases[$from] ? $aliases[$from] : $from), ($aliases[$to] ? $aliases[$to] : $to) . "//IGNORE", $str);
    if ($_iconv !== false)
      {
        return $_iconv;
      }
    }

  // settings for mbstring module (by Tadashi Jokagi)
  if (is_null($mbstring_loaded)) {
    if ($mbstring_loaded = extension_loaded("mbstring"))
      mb_internal_encoding(RCMAIL_CHARSET);
  }

  // convert charset using mbstring module
  if ($mbstring_loaded)
    {
    $aliases['UTF-7'] = 'UTF7-IMAP';
    $aliases['WINDOWS-1257'] = 'ISO-8859-13';
    
    // return if convert succeeded
    if (($out = mb_convert_encoding($str, ($aliases[$to] ? $aliases[$to] : $to), ($aliases[$from] ? $aliases[$from] : $from))) != '')
      return $out;
    }
    
  
  if (class_exists('utf8'))
    $conv = new utf8();

  // convert string to UTF-8
  if ($from == 'UTF-7')
    $str = utf7_to_utf8($str);
  else if (($from == 'ISO-8859-1') && function_exists('utf8_encode'))
    $str = utf8_encode($str);
  else if ($from != 'UTF-8' && $conv)
    {
    $conv->loadCharset($from);
    $str = $conv->strToUtf8($str);
    }
  else if ($from != 'UTF-8')
    $error = true;

  // encode string for output
  if ($to == 'UTF-7')
    return utf8_to_utf7($str);
  else if ($to == 'ISO-8859-1' && function_exists('utf8_decode'))
    return utf8_decode($str);
  else if ($to != 'UTF-8' && $conv)
    {
    $conv->loadCharset($to);
    return $conv->utf8ToStr($str);
    }
  else if ($to != 'UTF-8')
    $error = true;

  // report error
  if ($error && !$convert_warning)
    {
    raise_error(array(
      'code' => 500,
      'type' => 'php',
      'file' => __FILE__,
      'message' => "Could not convert string charset. Make sure iconv is installed or lib/utf8.class is available"
      ), true, false);
    
    $convert_warning = true;
    }
  
  // return UTF-8 string
  return $str;
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
  global $OUTPUT;
  static $html_encode_arr = false;
  static $js_rep_table = false;
  static $xml_rep_table = false;

  $charset = $OUTPUT->get_charset();
  $is_iso_8859_1 = false;
  if ($charset == 'ISO-8859-1') {
    $is_iso_8859_1 = true;
  }
  if (!$enctype)
    $enctype = $OUTPUT->type;

  // encode for plaintext
  if ($enctype=='text')
    return str_replace("\r\n", "\n", $mode=='remove' ? strip_tags($str) : $str);

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
    $out = preg_replace('/&amp;([a-z]{2,5}|#[0-9]{2,4});/', '&\\1;', strtr($str, $encode_arr));
      
    return $newlines ? nl2br($out) : $out;
    }

  if ($enctype=='url')
    return rawurlencode($str);

  // if the replace tables for XML and JS are not yet defined
  if ($js_rep_table===false)
    {
    $js_rep_table = $xml_rep_table = array();
    $xml_rep_table['&'] = '&amp;';

    for ($c=160; $c<256; $c++)  // can be increased to support more charsets
      {
      $xml_rep_table[Chr($c)] = "&#$c;";
      
      if ($is_iso_8859_1)
        $js_rep_table[Chr($c)] = sprintf("\\u%04x", $c);
      }

    $xml_rep_table['"'] = '&quot;';
    }

  // encode for XML
  if ($enctype=='xml')
    return strtr($str, $xml_rep_table);

  // encode for javascript use
  if ($enctype=='js')
    {
    if ($charset!='UTF-8')
      $str = rcube_charset_convert($str, RCMAIL_CHARSET,$charset);
      
    return preg_replace(array("/\r?\n/", "/\r/", '/<\\//'), array('\n', '\n', '<\\/'), addslashes(strtr($str, $js_rep_table)));
    }

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
  
  // strip slashes if magic_quotes enabled
  if ((bool)get_magic_quotes_gpc())
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
 * except . and -
 */
function asciiwords($str, $css_id = false)
{
  $allowed = 'a-z0-9\_\-' . (!$css_id ? '\.' : '');
  return preg_replace("/[^$allowed]/i", '', $str);
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
    $value = get_input_value($fname, RCUBE_INPUT_POST);

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
function rcmail_mod_css_styles($source, $container_id, $base_url = '')
  {
  $a_css_values = array();
  $last_pos = 0;
  
  // ignore the whole block if evil styles are detected
  $stripped = preg_replace('/[^a-z\(:]/', '', rcmail_xss_entitiy_decode($source));
  if (preg_match('/expression|behavior|url\(|import/', $stripped))
    return '';

  // cut out all contents between { and }
  while (($pos = strpos($source, '{', $last_pos)) && ($pos2 = strpos($source, '}', $pos)))
  {
    $key = sizeof($a_css_values);
    $a_css_values[$key] = substr($source, $pos+1, $pos2-($pos+1));
    $source = substr($source, 0, $pos+1) . "<<str_replacement[$key]>>" . substr($source, $pos2, strlen($source)-$pos2);
    $last_pos = $pos+2;
  }

  // remove html comments and add #container to each tag selector.
  // also replace body definition because we also stripped off the <body> tag
  $styles = preg_replace(
    array(
      '/(^\s*<!--)|(-->\s*$)/',
      '/(^\s*|,\s*|\}\s*)([a-z0-9\._#][a-z0-9\.\-_]*)/im',
      '/@import\s+(url\()?[\'"]?([^\)\'"]+)[\'"]?(\))?/ime',
      '/<<str_replacement\[([0-9]+)\]>>/e',
      "/$container_id\s+body/i"
    ),
    array(
      '',
      "\\1#$container_id \\2",
      "sprintf(\"@import url('./bin/modcss.php?u=%s&c=%s')\", urlencode(make_absolute_url('\\2','$base_url')), urlencode($container_id))",
      "\$a_css_values[\\1]",
      "$container_id div.rcmBody"
    ),
    $source);

  return $styles;
  }


/**
 * Decode escaped entities used by known XSS exploits.
 * See http://downloads.securityfocus.com/vulnerabilities/exploits/26800.eml for examples
 *
 * @param string CSS content to decode
 * @return string Decoded string
 */
function rcmail_xss_entitiy_decode($content)
{
  $out = html_entity_decode(html_entity_decode($content));
  $out = preg_replace('/\\\([a-z0-9]{4})/ie', "chr(hexdec('\\1'))", $out);
  $out = preg_replace('#/\*.*\*/#Um', '', $out);
  return $out;
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
  preg_match_all('/\s*([-_a-z]+)=(["\'])??(?(2)([^\2]+)\2|(\S+?))/Ui', stripslashes($str), $regs, PREG_SET_ORDER);

  // convert attributes to an associative array (name => value)
  if ($regs)
    foreach ($regs as $attr)
      {
      $attrib[strtolower($attr[1])] = $attr[3] . $attr[4];
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
    while (($ts = @strtotime($date))===false)
      {
        // if we have a date in non-rfc format
        // remove token from the end and try again
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
  $msg = array();
  foreach (func_get_args() as $arg)
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
  global $CONFIG;

  if (!is_string($line))
    $line = var_export($line, true);
  
  $log_entry = sprintf("[%s]: %s\n",
                 date("d-M-Y H:i:s O", mktime()),
                 $line);
                 
  if ($CONFIG['log_driver'] == 'syslog') {
    if ($name == 'errors')
      $prio = LOG_ERR;
    else
      $prio = LOG_INFO;
    syslog($prio, $log_entry);
  } else {
    // log_driver == 'file' is assumed here
    if (empty($CONFIG['log_dir']))
      $CONFIG['log_dir'] = INSTALL_PATH.'logs';
      
    // try to open specific log file for writing
    if ($fp = @fopen($CONFIG['log_dir'].'/'.$name, 'a')) {
      fwrite($fp, $log_entry);
      fclose($fp);
    }
  }
}


/**
 * @access private
 */
function rcube_timer()
  {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
  }
  

/**
 * @access private
 */
function rcube_print_time($timer, $label='Timer')
  {
  static $print_count = 0;
  
  $print_count++;
  $now = rcube_timer();
  $diff = $now-$timer;
  
  if (empty($label))
    $label = 'Timer '.$print_count;
  
  console(sprintf("%s: %0.4f sec", $label, $diff));
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
  rcube_add_label('purgefolderconfirm');
  rcube_add_label('deletemessagesconfirm');
  
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

  if ($type=='select') {
    $select = new html_select($attrib);
    
    // add no-selection option
    if ($attrib['noselection'])
      $select->add(rcube_label($attrib['noselection']), '0');
    
    rcmail_render_folder_tree_select($a_mailboxes, $mbox_name, $attrib['maxlength'], $select, $attrib['realnames']);
    $out = $select->show();
  }
  else {
    $js_mailboxlist = array();
    $out = html::tag('ul', $attrib, rcmail_render_folder_tree_html($a_mailboxes, $mbox_name, $js_mailboxlist, $attrib), html::$common_attrib);
    
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
      'name' => rcube_charset_convert($currentFolder, 'UTF-7'),
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
    $folder_id = asciiwords($folder['id'], true);
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
    else
      $classes[] = asciiwords($folder_class ? $folder_class : strtolower($folder['id']), true);
      
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

  $cname = null;
  $folder_lc = strtolower($folder_id);
  
  // for these mailboxes we have localized labels and css classes
  foreach (array('inbox', 'sent', 'drafts', 'trash', 'junk') as $smbx)
  {
    if ($folder_lc == $smbx || $folder_id == $CONFIG[$smbx.'_mbox'])
      $cname = $smbx;
  }
  
  return $cname;
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
    return rcube_charset_convert($name, 'UTF-7');
}


?>
