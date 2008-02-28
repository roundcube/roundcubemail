<?php

/*
 +-----------------------------------------------------------------------+
 | rcube_html.inc                                                        |
 |                                                                       |
 | This file is part of the RoundCube PHP suite                          |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | CONTENTS:                                                             |
 |   Common Classes to create HTML output                                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/


/**
 * HTML page builder class
 *
 * @package HTML
 */
class rcube_html_page
{
  var $scripts_path = '';
  var $script_files = array();
  var $scripts = array();
  var $charset = 'UTF-8';
  
  var $script_tag_file = "<script type=\"text/javascript\" src=\"%s%s\"></script>\n";
  var $script_tag      = "<script type=\"text/javascript\">\n<!--\n%s\n\n//-->\n</script>\n";
  var $default_template = "<html>\n<head><title></title></head>\n<body></body>\n</html>";

  var $title = 'RoundCube Mail';
  var $header = '';
  var $footer = '';
  var $body = '';
  var $body_attrib = array();
  var $meta_tags = array();
  
  
  /**
   * Link an external script file
   *
   * @param string File URL
   * @param string Target position [head|foot]
   */
  function include_script($file, $position='head')
  {
    static $sa_files = array();
    
    if (in_array($file, $sa_files))
      return;
      
    if (!is_array($this->script_files[$position]))
      $this->script_files[$position] = array();
      
    $this->script_files[$position][] = $file;
  }
  
  /**
   * Add inline javascript code
   *
   * @param string JS code snippet
   * @param string Target position [head|head_top|foot]
   */
  function add_script($script, $position='head')
  {
    if (!isset($this->scripts[$position]))
      $this->scripts[$position] = "\n".rtrim($script);
    else
      $this->scripts[$position] .= "\n".rtrim($script);
  }

  /**
   * Add HTML code to the page header
   */
  function add_header($str)
  {
    $this->header .= "\n".$str;
  }

  /**
   * Add HTML code to the page footer
   * To be added right befor </body>
   */
  function add_footer($str)
  {
    $this->footer .= "\n".$str;
  }

  /**
   * Setter for page title
   */
  function set_title($t)
  {
    $this->title = $t;
  }


  /**
   * Setter for output charset.
   * To be specified in a meta tag and sent as http-header
   */
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

  /**
   * Getter for output charset
   */
  function get_charset()
  {
    return $this->charset;
  }


  /**
   * Reset all saved properties
   */
  function reset()
  {
    $this->script_files = array();
    $this->scripts = array();
    $this->title = '';
    $this->header = '';
    $this->footer = '';
  }


  /**
   * Process template and write to stdOut
   *
   * @param string HTML template
   * @param string Base for absolute paths
   */
  function write($templ='', $base_path='')
  {
    $output = empty($templ) ? $this->default_template : trim($templ);
    
    // replace specialchars in content
    $__page_title = Q($this->title, 'show', FALSE);
    $__page_header = $__page_body = $__page_footer = '';
    
    
    // include meta tag with charset
    if (!empty($this->charset))
    {
      header('Content-Type: text/html; charset='.$this->charset, true);
      $__page_header = '<meta http-equiv="content-type" content="text/html; charset='.$this->charset.'" />'."\n";
    }
  
  
    // definition of the code to be placed in the document header and footer
    if (is_array($this->script_files['head']))
      foreach ($this->script_files['head'] as $file)
        $__page_header .= sprintf($this->script_tag_file, $this->scripts_path, $file);

    $head_script = $this->scripts['head_top'] . $this->scripts['head'];
    if (!empty($head_script))
      $__page_header .= sprintf($this->script_tag, $head_script);

    if (!empty($this->header))
      $__page_header .= $this->header;

    if (is_array($this->script_files['foot']))
      foreach ($this->script_files['foot'] as $file)
        $__page_footer .= sprintf($this->script_tag_file, $this->scripts_path, $file);

    if (!empty($this->scripts['foot']))
      $__page_footer .= sprintf($this->script_tag, $this->scripts['foot']);
      
    if (!empty($this->footer))
      $__page_footer .= $this->footer;

    // find page header
    if ($hpos = strpos(strtolower($output), '</head>'))
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
    if ($hpos)
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
    $output_lc = strtolower($output);
    if(($fpos = strrstr($output_lc, '</body>')) ||
       ($fpos = strrstr($output_lc, '</html>')))
      $output = substr($output, 0, $fpos) . "$__page_footer\n" . substr($output, $fpos);
    else
      $output .= "\n$__page_footer";
  
  
    // reset those global vars
    $__page_header = $__page_footer = '';
  
  
    // correct absolute paths in images and other tags
    $output = preg_replace('/(src|href|background)=(["\']?)(\/[a-z0-9_\-]+)/Ui', "\\1=\\2$base_path\\3", $output);
    $output = str_replace('$__skin_path', $base_path, $output);
  
    print rcube_charset_convert($output, 'UTF-8', $this->charset);
  }
    
}  // end class rcube_html_page



/**
 * Base class to build a HTML for element
 *
 * @package HTML
 */
class rcube_form_element
  {
  var $uppertags = FALSE;
  var $upperattribs = FALSE;
  var $upperprops = FALSE;
  var $newline = FALSE;
  
  var $attrib = array();


  /**
   * Create string with saved attributes
   *
   * @return string HTML formatted tag attributes
   */
  function create_attrib_string()
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

      // attributes with no value
      if (in_array($key, array('checked', 'multiple', 'disabled', 'selected', 'nowrap')))
      {
        if ($value)
          $attrib_arr[] = sprintf('%s="%s"', $this->_conv_case($key, 'attrib'), $key);
      }
      // don't convert size of value attribute
      else if ($key=='value')
        $attrib_arr[] = sprintf('%s="%s"', $this->_conv_case($key, 'attrib'), Q($value, 'strict', false));
        
      // regular tag attributes
      else
        $attrib_arr[] = sprintf('%s="%s"', $this->_conv_case($key, 'attrib'), $this->_conv_case(Q($value), 'value'));
    }

    return sizeof($attrib_arr) ? ' '.implode(' ', $attrib_arr) : '';
  }
    
    
  /**
   * Convert tags and attributes to upper-/lowercase
   *
   * @param string Input string
   * @param string Value type (can either be "tag" or "attrib")
   * @return string Converted output string
   * @access private
   */
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


/**
 * Builder for an <input> field
 *
 * @package HTML
 */
class input_field extends rcube_form_element
{
  var $type = 'text';
  
  /**
   * Constructor
   * @param array Named tag attributes
   */
  function input_field($attrib=array())
  {
    if (is_array($attrib))
      $this->attrib = $attrib;

    if ($attrib['type'])
      $this->type = $attrib['type'];    

    if ($attrib['newline'])
      $this->newline = TRUE;    
  }  

  /**
   * Compose input tag
   *
   * @param string Field value
   * @param array  Additional tag attributes
   * @return string Final HTML code
   */
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
    return sprintf(
      '<%s%s />%s',
      $this->_conv_case('input', 'tag'),
      $this->create_attrib_string(),
      ($this->newline ? "\n" : ""));
  }  
}


/**
 * Builder for a <input type="text"> field
 *
 * @package HTML
 */
class textfield extends input_field
{
  var $type = 'text';
}

/**
 * Builder for a <input type="password"> field
 *
 * @package HTML
 */
class passwordfield extends input_field
{
  var $type = 'password';
}

/**
 * Builder for <input type="radio"> fields
 *
 * @package HTML
 */
class radiobutton extends input_field
{
  var $type = 'radio';
}

/**
 * Builder for <input type="checkbox"> fields
 *
 * @package HTML
 */
class checkbox extends input_field
{
  var $type = 'checkbox';


  /**
   * Compose input tag
   *
   * @param string Field value
   * @param array  Additional tag attributes
   * @return string Final HTML code
   */
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
    return sprintf(
      '<%s%s />%s',
      $this->_conv_case('input', 'tag'),
      $this->create_attrib_string(),
      ($this->newline ? "\n" : ""));
  }
}


/**
 * Builder for a <textarea> field
 *
 * @package HTML
 */
class textarea extends rcube_form_element
  {

  /**
   * Constructor
   * @param array Named tag attributes
   */
  function textarea($attrib=array())
  {
    $this->attrib = $attrib;

    if ($attrib['newline'])
      $this->newline = TRUE;
  }
  
  /**
   * Create HTML representation for this field
   *
   * @param string Field value
   * @param array  Additional tag attributes
   * @return string Final HTML code
   */
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

    if (!empty($value) && !isset($this->attrib['mce_editable']))
      $value = Q($value, 'strict', FALSE);

    // return final tag
    return sprintf(
      '<%s%s>%s</%s>%s',
      $this->_conv_case('textarea', 'tag'),
      $this->create_attrib_string(),
      $value,
      $this->_conv_case('textarea', 'tag'),
      ($this->newline ? "\n" : ""));       
  }
}


/**
 * Builder for group of hidden form fields
 *
 * @package HTML
 */
class hiddenfield extends rcube_form_element
{
  var $fields_arr = array();
  var $newline = TRUE;

  /**
   * Constructor
   *
   * @param array Named tag attributes
   */
  function hiddenfield($attrib=NULL)
  {
    if (is_array($attrib))
      $this->add($attrib);
  }

  /**
   * Add a hidden field to this instance
   * @param array Named tag attributes
   */
  function add($attrib)
  {
    $this->fields_arr[] = $attrib;
  }


  /**
   * Create HTML code for the hidden fields
   *
   * @return string Final HTML code
   */
  function show()
  {
    $out = '';
    foreach ($this->fields_arr as $attrib)
    {
      $this->attrib = $attrib;
      $this->attrib['type'] = 'hidden';
      
      $out .= sprintf(
        '<%s%s />%s',
        $this->_conv_case('input', 'tag'),
        $this->create_attrib_string(),
        ($this->newline ? "\n" : ""));
    }

    return $out;
  }
}


/**
 * Builder for HTML drop-down menus
 * Syntax:<pre>
 * // create instance. arguments are used to set attributes of select-tag
 * $select = new select(array('name' => 'fieldname'));
 *
 * // add one option
 * $select->add('Switzerland', 'CH');
 *
 * // add multiple options
 * $select->add(array('Switzerland','Germany'), array('CH','DE'));
 *
 * // generate pulldown with selection 'Switzerland'  and return html-code
 * // as second argument the same attributes available to instanciate can be used
 * print $select->show('CH');
 * </pre>
 *
 * @package HTML
 */
class select extends rcube_form_element
{
  var $options = array();

  /**
   * Constructor
   *
   * @param array Named tag attributes
   */
  function select($attrib=NULL)
  {
    if (is_array($attrib))
      $this->attrib = $attrib;

    if ($attrib['newline'])
      $this->newline = TRUE;
  }


  /**
   * Add one ore more menu options
   *
   * @param mixed Array with names or single option name
   * @param mixed Array with values or single option value
   */
  function add($names, $values=NULL)
  {
    if (is_array($names))
    {
      foreach ($names as $i => $text)
        $this->options[] = array('text' => $text, 'value' => (string)$values[$i]);
    }
    else
      $this->options[] = array('text' => $names, 'value' => (string)$values);
  }


  /**
   * Generate HTML code for this drop-down menu
   *
   * @param string Value of the selected option
   * @param array Additional tag attributes
   * @return string Final HTML code
   */
  function show($select=array(), $attrib=NULL)
  {
    $options_str = "\n";
    $value_str = $this->_conv_case(' value="%s"', 'attrib');
    
    if (!is_array($select))
      $select = array((string)$select);

    foreach ($this->options as $option)
    {
      $selected = ((isset($option['value']) &&
                    in_array($option['value'], $select, TRUE)) ||
                   (in_array($option['text'], $select, TRUE))) ?
        $this->_conv_case(' selected="selected"', 'attrib') : '';
                   
      $options_str .= sprintf("<%s%s%s>%s</%s>\n",
                             $this->_conv_case('option', 'tag'),
                             isset($option['value']) ? sprintf($value_str, Q($option['value'])) : '',
                             $selected, 
                             Q($option['text'], 'strict', FALSE),
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


?>
