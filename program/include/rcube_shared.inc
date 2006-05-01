<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_shared.inc                                                      |
 |                                                                       |
 | This file is part of the RoundCube PHP suite                          |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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


// ********* round cube schared classes *********

class rcube_html_page
  {
  var $css;
  
  var $scripts_path = '';
  var $script_files = array();
  var $scripts = array();
  var $charset = 'ISO-8859-1';
  
  var $script_tag_file = "<script type=\"text/javascript\" src=\"%s%s\"></script>\n";
  var $script_tag      = "<script type=\"text/javascript\">\n<!--\n%s\n\n//-->\n</script>\n";
  
  var $title = '';
  var $header = '';
  var $footer = '';
  var $body = '';
  var $body_attrib = array();
  var $meta_tags = array();


  // PHP 5 constructor
  function __construct()
    {
    $this->css = new rcube_css();
    }

  // PHP 4 compatibility
  function rcube_html_page()
    {
    $this->__construct();
    }


  function include_script($file, $position='head')
    {
    static $sa_files = array();
    
    if (in_array($file, $sa_files))
      return;
      
    if (!is_array($this->script_files[$position]))
      $this->script_files[$position] = array();
      
    $this->script_files[$position][] = $file;
    }
    
  
  function add_script($script, $position='head')
    {
    if (!isset($this->scripts[$position]))
      $this->scripts[$position] = '';

    $this->scripts[$position] .= "\n$script";
    }


  function set_title()
    {
    
    }

  function set_charset($charset)
    {
    global $MBSTRING;
    
    $this->charset = $charset;
    
    if ($MBSTRING && function_exists("mb_internal_encoding"))
      {
      if(!@mb_internal_encoding($charset))
        $MBSTRING = FALSE;
      }
    }
    
  function get_charset()
    {
    return $this->charset;
    }


  function write($templ='', $base_path='')
    {
    $output = trim($templ);
  
    // set default page title
    if (!strlen($this->title))
      $this->title = 'RoundCube Mail';
  
    // replace specialchars in content
    $__page_title = rep_specialchars_output($this->title, 'html', 'show', FALSE);
    $__page_header = $__page_body = $__page_footer = '';
    
    
    // include meta tag with charset
    if (!empty($this->charset))
      {
      header('Content-Type: text/html; charset='.$this->charset);
      $__page_header = '<meta http-equiv="content-type" content="text/html; charset='.$this->charset.'" />'."\n";
      }
  
  
    // definition of the code to be placed in the document header and footer
    if (is_array($this->script_files['head']))
      foreach ($this->script_files['head'] as $file)
        $__page_header .= sprintf($this->script_tag_file, $this->scripts_path, $file);

    if (strlen($this->scripts['head']))
      $__page_header .= sprintf($this->script_tag, $this->scripts['head']);
          
    if (is_array($this->script_files['foot']))
      foreach ($this->script_files['foot'] as $file)
        $__page_footer .= sprintf($this->script_tag_file, $this->scripts_path, $file);

    if (strlen($this->scripts['foot']))
      $__page_footer .= sprintf($this->script_tag, $this->scripts['foot']);


    $__page_header .= $this->css->show();

  
    // find page header
    if($hpos = strpos(strtolower($output), '</head>'))
      $__page_header .= "\n";
    else 
      {
      if (!is_numeric($hpos))
        $hpos = strpos(strtolower($output), '<body');
      if (!is_numeric($hpos) && ($hpos = strpos(strtolower($output), '<html')))
        {
        while($output[$hpos]!='>')
        $hpos++;
        $hpos++;
        }
  
      $__page_header = "<head>\n<title>$__page_title</title>\n$__page_header\n</head>\n";
      }
  
    // add page hader
    if($hpos)
      $output = substr($output,0,$hpos) . $__page_header . substr($output,$hpos,strlen($output));
    else
      $output = $__page_header . $output;
  
  
    // find page body
    if($bpos = strpos(strtolower($output), '<body'))
      {
      while($output[$bpos]!='>') $bpos++;
      $bpos++;
      }
    else
      $bpos = strpos(strtolower($output), '</head>')+7;
  
    // add page body
    if($bpos && $__page_body)
      $output = substr($output,0,$bpos) . "\n$__page_body\n" . substr($output,$bpos,strlen($output));
  
  
    // find and add page footer
    if(($fpos = strpos(strtolower($output), '</body>')) || ($fpos = strpos(strtolower($output), '</html>')))
      $output = substr($output,0,$fpos) . "$__page_footer\n" . substr($output,$fpos,strlen($output));
    else
      $output .= "\n$__page_footer";
  
  
    // reset those global vars
    $__page_header = $__page_footer = '';
  
  
    // correct absolute pathes in images and other tags
    $output = preg_replace('/(src|href|background)=(["\']?)(\/[a-z0-9_\-]+)/Ui', "\\1=\\2$base_path\\3", $output);
    $output = str_replace('$__skin_path', $base_path, $output);
  
    print rcube_charset_convert($output, 'UTF-8', $this->charset);
    }
    
    
  function _parse($templ)
    {
    
    }
  }




class rcube_css
  {
  var $css_data = array();

  var $css_groups = array();

  var $include_files = array();

  var $grouped_output = TRUE;

  var $content_type = 'text/css';

  var $base_path = '';

  var $indent_chars = "\t";


  // add or overwrite a css definition
  // either pass porperty and value as separate arguments
  // or provide an associative array as second argument
  function set_style($selector, $property, $value='')
    {
    $a_elements = $this->_parse_selectors($selector);
    foreach ($a_elements as $element)
      {
      if (!is_array($property))
        $property = array($property => $value);

      foreach ($property as $name => $value)
        $this->css_data[$element][strtolower($name)] = $value;
      }

    // clear goups array
    $this->css_groups = array();
    }


  // unset a style property
  function remove_style($selector, $property)
    {
    if (!is_array($property))
      $property = array($property);

    foreach ($property as $key)
      unset($this->css_data[$selector][strtolower($key)]);

    // clear goups array
    $this->css_groups = array();
    }


  // define base path for external css files
  function set_basepath($path)
    {
    $this->base_path = preg_replace('/\/$/', '', $path);
    }


  // enable/disable grouped output
  function set_grouped_output($grouped)
    {
    $this->grouped_output = $grouped;
    }


  // add a css file as external source
  function include_file($filename, $media='')
    {
    // include multiple files
    if (is_array($filename))
      {
      foreach ($filename as $file)
        $this->include_file($file, $media);
      }
    // add single file
    else if (!in_array($filename, $this->include_files))
      $this->include_files[] = array('file' => $filename,
                                     'media' => $media);
    }


  // parse css code
  function import_string($str)
    {
    $ret = FALSE;
    if (strlen($str))
      $ret = $this->_parse($str);

    return $ret;
    }


  // open and parse a css file
  function import_file($file)
    {
    $ret = FALSE;

    if (!is_file($file))
      return $ret;

    // for php version >= 4.3.0
    if (function_exists('file_get_contents'))
      $ret = $this->_parse(file_get_contents($file));

    // for order php versions
    else if ($fp = fopen($file, 'r'))
      {
      $ret = $this->_parse(fread($fp, filesize($file)));
      fclose($fp);
      }

    return $ret;
    }


  // copy all properties inherited from superior styles to a specific selector
  function copy_inherited_styles($selector)
    {
    // get inherited props from body and tag/class selectors
    $css_props = $this->_get_inherited_styles($selector);

    // write modified props back and clear goups array
    if (sizeof($css_props))
      {
      $this->css_data[$selector] = $css_props;
      $this->css_groups = array();
      }
    }


  // return css definition for embedding in HTML
  function show()
    {
    $out = '';

    // include external css files
    if (sizeof($this->include_files))
      foreach ($this->include_files as $file_arr)
      $out .= sprintf('<link rel="stylesheet" type="%s" href="%s"%s>'."\n",
                        $this->content_type,
                        $this->_get_file_path($file_arr['file']),
                        $file_arr['media'] ? ' media="'.$file_arr['media'].'"' : '');


    // compose css string
    if (sizeof($this->css_data))
      $out .= sprintf("<style type=\"%s\">\n<!--\n\n%s-->\n</style>",
                      $this->content_type,
                      $this->to_string());


    return $out;
    }


  // return valid css code of the current styles grid
  function to_string($selector=NULL)
    {
    // return code for a single selector
    if ($selector)
      {
      $indent_str = $this->indent_chars;
      $this->indent_chars = '';

      $prop_arr = $this->to_array($selector);
      $out = $this->_style2string($prop_arr, TRUE);

      $this->indent_chars = $indent_str;
      }

    // compose css code for complete data grid
    else
      {
      $out = '';
      $css_data = $this->to_array();

      foreach ($css_data as $key => $prop_arr)
        $out .= sprintf("%s {\n%s}\n\n",
                        $key,
                        $this->_style2string($prop_arr, TRUE));
      }

    return $out;
    }


  // return a single-line string of a css definition
  function to_inline($selector)
    {
    if ($this->css_data[$selector])
      return str_replace('"', '\\"', $this->_style2string($this->css_data[$selector], FALSE));
    }


  // return an associative array with selector(s) as key and styles array as value
  function to_array($selector=NULL)
    {
    if (!$selector && $this->grouped_output)
      {
      // build groups if desired
      if (!sizeof($this->css_groups))
        $this->_build_groups();

      // modify group array to get an array(selector => properties)
      $out_arr = array();
      foreach ($this->css_groups as $group_arr)
        {
        $key = join(', ', $group_arr['selectors']);
        $out_arr[$key] = $group_arr['properties'];
        }
      }
    else
      $out_arr = $this->css_data;

    return $selector ? $out_arr[$selector] : $out_arr;
    }


  // create a css file
  function to_file($filepath)
    {
    if ($fp = fopen($filepath, 'w'))
      {
      fwrite($fp, $this->to_string());
      fclose($fp);
      return TRUE;
      }

    return FALSE;
    }


  // alias method for import_string() [DEPRECATED]
  function add($str)
    {
    $this->import_string($str);
    }

  // alias method for to_string() [DEPRECATED]
  function get()
    {
    return $this->to_string();
    }



  // ******** private methods ********


  // parse a string and add styles to internal data grid
  function _parse($str)
    {
    // remove comments
    $str = preg_replace("/\/\*(.*)?\*\//Usi", '', $str);

    // parse style definitions
    if (!preg_match_all ('/([a-z0-9\.#*:_][a-z0-9\.\-_#:*,\[\]\(\)\s\"\'\+\|>~=]+)\s*\{([^\}]*)\}/ims', $str, $matches, PREG_SET_ORDER))
      return FALSE;


    foreach ($matches as $match_arr)
      {
      // split selectors into array
      $a_keys = $this->_parse_selectors(trim($match_arr[1]));

      // parse each property of an element
      $codes = explode(";", trim($match_arr[2]));
      foreach ($codes as $code)
        {
        if (strlen(trim($code))>0)
          {
          // find the property and the value
          if (!($sep = strpos($code, ':')))
            continue;

          $property = strtolower(trim(substr($code, 0, $sep)));
          $value    = trim(substr($code, $sep+1));

          // add the property to the object array
          foreach ($a_keys as $key)
            $this->css_data[$key][$property] = $value;
          }
        }
      }

    // clear goups array
    if (sizeof($matches))
      {
      $this->css_groups = array();
      return TRUE;
      }

    return FALSE;
    }


  // split selector group
  function _parse_selectors($selector)
    {
    // trim selector and remove multiple spaces
    $selector = preg_replace('/\s+/', ' ', trim($selector));

    if (strpos($selector, ','))
      return preg_split('/[\t\s\n\r]*,[\t\s\n\r]*/mi', $selector);
    else
      return array($selector);
    }


  // compare identical styles and make groups
  function _build_groups()
    {
    // clear group array
    $this->css_groups = array();
    $string_group_map = array();

    // bulild css string for each selector and check if the same is already defines
    foreach ($this->css_data as $selector => $prop_arr)
      {
      // make shure to compare props in the same order
      ksort($prop_arr);
      $compare_str = preg_replace('/[\s\t]+/', '', $this->_style2string($prop_arr, FALSE));

      // add selector to extisting group
      if (isset($string_group_map[$compare_str]))
        {
        $group_index = $string_group_map[$compare_str];
        $this->css_groups[$group_index]['selectors'][] = $selector;
        }

      // create new group
      else
        {
        $i = sizeof($this->css_groups);
        $string_group_map[$compare_str] = $i;
        $this->css_groups[$i] = array('selectors' => array($selector),
                                      'properties' => $this->css_data[$selector]);
        }
      }
    }


  // convert the prop array into a valid css definition
  function _style2string($prop_arr, $multiline=TRUE)
    {
    $out = '';
    $delm   = $multiline ? "\n" : '';
    $spacer = $multiline ? ' ' : '';
    $indent = $multiline ? $this->indent_chars : '';

    if (is_array($prop_arr))
      foreach ($prop_arr as $prop => $value)
        if (strlen($value))
          $out .= sprintf('%s%s:%s%s;%s',
                          $indent,
                          $prop,
                          $spacer,
                          $value,
                          $delm);

    return $out;
    }


  // copy all properties inherited from superior styles to a specific selector
  function _get_inherited_styles($selector, $loop=FALSE)
    {
    $css_props = $this->css_data[$selector] ? $this->css_data[$selector] : array();

    // get styles from tag selector
    if (preg_match('/(([a-z0-9]*)(\.[^\s]+)?)$/i', $selector, $regs))
      {
      $sel = $regs[1];
      $tagname = $regs[2];
      $class = $regs[3];

      if ($sel && is_array($this->css_data[$sel]))
        $css_props = $this->_merge_styles($this->css_data[$sel], $css_props);

      if ($class && is_array($this->css_data[$class]))
        $css_props = $this->_merge_styles($this->css_data[$class], $css_props);

      if ($tagname && is_array($this->css_data[$tagname]))
        $css_props = $this->_merge_styles($this->css_data[$tagname], $css_props);
      }

    // analyse inheritance
    if (strpos($selector, ' '))
      {
      $a_hier = split(' ', $selector);
      if (sizeof($a_hier)>1)
        {
        array_pop($a_hier);
        $base_selector = join(' ', $a_hier);

        // call this method recursively
        $new_props = $this->_get_inherited_styles($base_selector, TRUE);
        $css_props = $this->_merge_styles($new_props, $css_props);
        }
      }

    // get body style
    if (!$loop && is_array($this->css_data['body']))
      $css_props = $this->_merge_styles($this->css_data['body'], $css_props);

    return $css_props;
    }


  // merge two arrays with style properties together like a browser would do
  function _merge_styles($one, $two)
    {
    // these properties are additive
    foreach (array('text-decoration') as $prop)
      if ($one[$prop] && $two[$prop])
        {
        // if value contains 'none' it's ignored
        if (strstr($one[$prop], 'none'))
          continue;
        else if (strstr($two[$prop], 'none'))
          unset($two[$prop]);

        $a_values_one = split(' ', $one[$prop]);
        $a_values_two = split(' ', $two[$prop]);
        $two[$prop] = join(' ', array_unique(array_merge($a_values_one, $a_values_two)));
        }

    return array_merge($one, $two);
    }


  // resolve file path
  function _get_file_path($file)
    {
    if (!$this->base_path && $GLOBALS['CSS_PATH'])
      $this->set_basepath($GLOBALS['CSS_PATH']);

    $base = ($file{0}=='/' || $file{0}=='.' || substr($file, 0, 7)=='http://') ? '' :
            ($this->base_path ? $this->base_path.'/' : '');
    return $base.$file;
    }

  }



class base_form_element
  {
  var $uppertags = FALSE;
  var $upperattribs = FALSE;
  var $upperprops = FALSE;
  var $newline = FALSE;
  
  var $attrib = array();


  // create string with attributes
  function create_attrib_string($tagname='')
    {
    if (!sizeof($this->attrib))
      return '';

    if ($this->name!='')
      $this->attrib['name'] = $this->name;

    $attrib_arr = array();
    foreach ($this->attrib as $key => $value)
      {
      // don't output some internally used attributes
      if (in_array($key, array('form', 'quicksearch')))
        continue;

      // skip if size if not numeric
      if (($key=='size' && !is_numeric($value)))
        continue;
        
      // skip empty eventhandlers
      if ((strpos($key,'on')===0 && $value==''))
        continue;

      // encode textarea content
      if ($key=='value')
        $value = rep_specialchars_output($value, 'html', 'replace', FALSE);

      // attributes with no value
      if (in_array($key, array('checked', 'multiple', 'disabled', 'selected')))
        {
        if ($value)
          $attrib_arr[] = $key;
        }
      // don't convert size of value attribute
      else if ($key=='value')
        $attrib_arr[] = sprintf('%s="%s"', $this->_conv_case($key, 'attrib'), $value, 'value');
        
      // regular tag attributes
      else
        $attrib_arr[] = sprintf('%s="%s"', $this->_conv_case($key, 'attrib'), $this->_conv_case($value, 'value'));
      }

    return sizeof($attrib_arr) ? ' '.implode(' ', $attrib_arr) : '';
    }
    
    
  // convert tags and attributes to upper-/lowercase
  // $type can either be "tag" or "attrib"
  function _conv_case($str, $type='attrib')
    {
    if ($type == 'tag')
      return $this->uppertags ? strtoupper($str) : strtolower($str);
    else if ($type == 'attrib')
      return $this->upperattribs ? strtoupper($str) : strtolower($str);
    else if ($type == 'value')
      return $this->upperprops ? strtoupper($str) : strtolower($str);
    }    
  }


class input_field extends base_form_element
  {
  var $type = 'text';
  
  // PHP 5 constructor
  function __construct($attrib=NULL)
    {
    if (is_array($attrib))
      $this->attrib = $attrib;

    if ($attrib['type'])
      $this->type = $attrib['type'];    

    if ($attrib['newline'])
      $this->newline = TRUE;    
    }

  // PHP 4 compatibility
  function input_field($attrib=array())
    {
    $this->__construct($attrib);
    }  

  // compose input tag
  function show($value=NULL, $attrib=NULL)
    {
    // overwrite object attributes
    if (is_array($attrib))
      $this->attrib = array_merge($this->attrib, $attrib);

    // set value attribute
    if ($value!==NULL)
      $this->attrib['value'] = $value;

    $this->attrib['type'] = $this->type;

    // return final tag
    return sprintf('<%s%s />%s',
                   $this->_conv_case('input', 'tag'),
                   $this->create_attrib_string(),
                   ($this->newline ? "\n" : ""));    
    }  
  }


class textfield extends input_field
  {
  var $type = 'text';
  }

class passwordfield extends input_field
  {
  var $type = 'password';
  }

class radiobutton extends input_field
  {
  var $type = 'radio';
  }

class checkbox extends input_field
  {
  var $type = 'checkbox';


  function show($value='', $attrib=NULL)
    {
    // overwrite object attributes
    if (is_array($attrib))
      $this->attrib = array_merge($this->attrib, $attrib);    

    $this->attrib['type'] = $this->type;

    if ($value && (string)$value==(string)$this->attrib['value'])
      $this->attrib['checked'] = TRUE;
    else
      $this->attrib['checked'] = FALSE;

    // return final tag
    return sprintf('<%s%s />%s',
                   $this->_conv_case('input', 'tag'),
                   $this->create_attrib_string(),
                   ($this->newline ? "\n" : ""));    
    }
  }


class textarea extends base_form_element
  {
  // PHP 5 constructor
  function __construct($attrib=array())
    {
    $this->attrib = $attrib;

    if ($attrib['newline'])
      $this->newline = TRUE;    
    }

  // PHP 4 compatibility
  function textarea($attrib=array())
    {
    $this->__construct($attrib);
    }
    
  function show($value='', $attrib=NULL)
    {
    // overwrite object attributes
    if (is_array($attrib))
      $this->attrib = array_merge($this->attrib, $attrib);
    
    // take value attribute as content
    if ($value=='')
      $value = $this->attrib['value'];
    
    // make shure we don't print the value attribute
    if (isset($this->attrib['value']))
      unset($this->attrib['value']);

    if (strlen($value))
      $value = rep_specialchars_output($value, 'html', 'replace', FALSE);
    
    // return final tag
    return sprintf('<%s%s>%s</%s>%s',
                   $this->_conv_case('textarea', 'tag'),
                   $this->create_attrib_string(),
                   $value,
                   $this->_conv_case('textarea', 'tag'),
                   ($this->newline ? "\n" : ""));       
    }
  }


class hiddenfield extends base_form_element
  {
  var $fields_arr = array();
  var $newline = TRUE;

  // PHP 5 constructor
  function __construct($attrib=NULL)
    {
    if (is_array($attrib))
      $this->add($attrib);
    }

  // PHP 4 compatibility
  function hiddenfield($attrib=NULL)
    {
    $this->__construct($attrib);
    }

  // add a hidden field to this instance
  function add($attrib)
    {
    $this->fields_arr[] = $attrib;
    }


  function show()
    {
    $out = '';
    foreach ($this->fields_arr as $attrib)
      {
      $this->attrib = $attrib;
      $this->attrib['type'] = 'hidden';
      
      $out .= sprintf('<%s%s />%s',
                   $this->_conv_case('input', 'tag'),
                   $this->create_attrib_string(),
                   ($this->newline ? "\n" : ""));   
      }

    return $out;
    }
  }


class select extends base_form_element
  {
  var $options = array();

  /*
  syntax:
  -------
  // create instance. arguments are used to set attributes of select-tag
  $select = new select(array('name' => 'fieldname'));

  // add one option
  $select->add('Switzerland', 'CH');

  // add multiple options
  $select->add(array('Switzerland', 'Germany'),
               array('CH', 'DE'));

  // add 10 blank options with 50 chars
  // used to fill with javascript (necessary for 4.x browsers)
  $select->add_blank(10, 50);

  // generate pulldown with selection 'Switzerland'  and return html-code
  // as second argument the same attributes available to instanciate can be used
  print $select->show('CH');
  */

  // PHP 5 constructor
  function __construct($attrib=NULL)
    {
    if (is_array($attrib))
      $this->attrib = $attrib;

    if ($attrib['newline'])
      $this->newline = TRUE;
    }

  // PHP 4 compatibility
  function select($attrib=NULL)
    {
    $this->__construct($attrib);
    }


  function add($names, $values=NULL)
    {
    if (is_array($names))
      {
      foreach ($names as $i => $text)
        $this->options[] = array('text' => $text, 'value' => (string)$values[$i]);
      }
    else
      {
      $this->options[] = array('text' => $names, 'value' => (string)$values);
      }
    }

    
  function add_blank($nr, $width=0)
    {
    $text = $width ? str_repeat('&nbsp;', $width) : '';
    
    for ($i=0; $i < $nr; $i++)
      $this->options[] = array('text' => $text);
    }

  
  function show($select=array(), $attrib=NULL)
    {
    $options_str = "\n";
    $value_str = $this->_conv_case(' value="%s"', 'attrib');
    
    if (!is_array($select))
      $select = array((string)$select);
    
    foreach ($this->options as $option)
      {
      $selected = ((strlen($option['value']) && in_array($option['value'], $select, TRUE)) ||
                   (in_array($option['text'], $select, TRUE))) ? $this->_conv_case(' selected', 'attrib') : '';
                  
      $options_str .= sprintf("<%s%s%s>%s</%s>\n",
                             $this->_conv_case('option', 'tag'),
                             strlen($option['value']) ? sprintf($value_str, $option['value']) : '',
                             $selected, 
                             rep_specialchars_output($option['text'], 'html', 'replace', FALSE),
                             $this->_conv_case('option', 'tag'));
      }
                             
    // return final tag
    return sprintf('<%s%s>%s</%s>%s',
                   $this->_conv_case('select', 'tag'),
                   $this->create_attrib_string(),
                   $options_str,
                   $this->_conv_case('select', 'tag'),
                   ($this->newline ? "\n" : ""));    
    }
  }




// ********* rcube schared functions *********


// provide details about the client's browser
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


// get text in the desired language from the language file
function rcube_label($attrib)
  {
  global $sess_user_lang, $INSTALL_PATH, $OUTPUT;
  static $sa_text_data, $s_language, $utf8_decode;

  // extract attributes
  if (is_string($attrib))
    $attrib = array('name' => $attrib);
    
  $nr = is_numeric($attrib['nr']) ? $attrib['nr'] : 1;
  $vars = isset($attrib['vars']) ? $attrib['vars'] : '';

  $command_name = strlen($attrib['command']) ? $attrib['command'] : NULL;
  $alias = $attrib['name'] ? $attrib['name'] : ($command_name && $command_label_map[$command_name] ? $command_label_map[$command_name] : '');


  // load localized texts
  if (!$sa_text_data || $s_language != $sess_user_lang)
    {
    $sa_text_data = array();
    
    // get english labels (these should be complete)
    @include($INSTALL_PATH.'program/localization/en_US/labels.inc');
    @include($INSTALL_PATH.'program/localization/en_US/messages.inc');

    if (is_array($labels))
      $sa_text_data = $labels;
    if (is_array($messages))
      $sa_text_data = array_merge($sa_text_data, $messages);
    
    // include user language files
    if ($sess_user_lang!='en' && is_dir($INSTALL_PATH.'program/localization/'.$sess_user_lang))
      {
      include_once($INSTALL_PATH.'program/localization/'.$sess_user_lang.'/labels.inc');
      include_once($INSTALL_PATH.'program/localization/'.$sess_user_lang.'/messages.inc');

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
    raise_error(array('code' => 500,
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
  else
    return $text;

  return $text;
  }


// send HTTP header for no-cacheing steps
function send_nocacheing_headers()
  {
  if (headers_sent())
    return;

  header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
  header("Pragma: no-cache");
  }


// send header with expire date 30 days in future
function send_future_expire_header()
  {
  if (!headers_sent())
    header("Expires: ".gmdate("D, d M Y H:i:s", mktime()+2600000)." GMT");
  }


// function to convert an array to a javascript array
function array2js($arr, $type='')
  {
  if (!$type)
    $type = 'mixed';

  if (is_array($arr))
    {
    // no items in array
    if (!sizeof($arr))
      return 'new Array()';
    else
      {
      $a_pairs = array();
      $keys_arr = array_keys($arr);
      $is_assoc = $have_numeric = 0;

      for ($i=0; $i<sizeof($keys_arr); ++$i)
        {
        if(is_numeric($keys_arr[$i]))
          $have_numeric = 1;
        if (!is_numeric($keys_arr[$i]) || $keys_arr[$i]!=$i)
          $is_assoc = 1;
        if($is_assoc && $have_numeric)
          break;
        }

      $previous_was_array = false;
      while (list($key, $value) = each($arr))
        {
        // enclose key with quotes if it is not variable-name conform
        if (!ereg("^[_a-zA-Z]{1}[_a-zA-Z0-9]*$", $key) /* || is_js_reserved_word($key) */)
          $key = "'$key'";

        if (!is_array($value))
          {
          $value = str_replace("\r\n", '\n', $value);
          $value = str_replace("\n", '\n', $value);
          }

        $is_string = false;
        if (!is_array($value))
          {
          if ($type=='string')
            $is_string = true;
          else if ((($type=='mixed' && is_numeric($value)) || $type=='int') && strlen($value)<16)   // js interprets numbers with digits >15 as ...e+... 
            $is_string = FALSE;
          else
            $is_string = TRUE;
          }

        if ($is_string)
          $value = "'".preg_replace("/(?<!\\\)'/", "\'", $value)."'";

        $a_pairs[] = sprintf("%s%s",
                             $is_assoc ? "$key:" : '',
                             is_array($value) ? array2js($value, $type) : $value);
        }

      if ($a_pairs)
        {
        if ($is_assoc)
          $return = '{'.implode(',', $a_pairs).'}';
        else
          $return = '['.implode(',', $a_pairs).']';
        }

      return $return;
      }
    }
  else
    return $arr;
  }


// similar function as in_array() ut case-insensitive
function in_array_nocase($needle, $haystack)
  {
  foreach ($haystack as $value)
    {
    if (strtolower($needle)===strtolower($value))
      return TRUE;
    }
    
  return FALSE;
  }



// find out if the string content means TRUE or FALSE
function get_boolean($str)
  {
  $str = strtolower($str);
  if(in_array($str, array('false', '0', 'no', 'nein', ''), TRUE))
    return FALSE;
  else
    return TRUE;
  }


function show_bytes($numbytes)
  {
  if ($numbytes > 1024)
    return sprintf('%d KB', round($numbytes/1024));
  else
    return sprintf('%d B', $numbytes);
  }


// convert paths like ../xxx to an absolute path using a base url
function make_absolute_url($path, $base_url)
    {
    $host_url = $base_url;
    $abs_path = $path;

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

      if(preg_match_all('/\.\.\//', $path, $matches, PREG_SET_ORDER))
        foreach($matches as $a_match)
          {
          if (strrpos($base_url, '/'))
            $base_url = substr($base_url, 0, strrpos($base_url, '/'));
          
          $path = substr($path, 3);
          }

      $abs_path = $base_url.'/'.$path;
      }
      
    return $abs_path;
    }



function abbrevate_string($str, $maxlength, $place_holder='...')
  {
  $length = strlen($str);
  $first_part_length = floor($maxlength/2) - strlen($place_holder);
  
  if ($length > $maxlength)
    {
    $second_starting_location = $length - $maxlength + $first_part_length + 1;
    $str = substr($str, 0, $first_part_length) . $place_holder . substr($str, $second_starting_location, $length);
    }

  return $str;
  }


// delete all files within a folder
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


// create a unix timestamp with a specified offset from now
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
    case 'h':
      $amount *= 60;
    case 's':
      $ts += $amount * $factor;
    }

  return $ts;
  }


?>
