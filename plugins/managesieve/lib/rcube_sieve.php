<?php

/*
  Classes for managesieve operations (using PEAR::Net_Sieve)

  Author: Aleksander Machniak <alec@alec.pl>

  $Id$

*/

//  Sieve Language Basics: http://www.ietf.org/rfc/rfc5228.txt

define('SIEVE_ERROR_CONNECTION', 1);
define('SIEVE_ERROR_LOGIN', 2);
define('SIEVE_ERROR_NOT_EXISTS', 3);	// script not exists
define('SIEVE_ERROR_INSTALL', 4);	// script installation
define('SIEVE_ERROR_ACTIVATE', 5);	// script activation
define('SIEVE_ERROR_DELETE', 6);	// script deletion
define('SIEVE_ERROR_INTERNAL', 7);	// internal error
define('SIEVE_ERROR_OTHER', 255);	// other/unknown error


class rcube_sieve
{
    private $sieve;			// Net_Sieve object
    private $error = false; 		// error flag 
    private $list = array(); 		// scripts list 

    public $script;			// rcube_sieve_script object
    public $current;			// name of currently loaded script
    private $disabled;			// array of disabled extensions

    /**
    * Object constructor
    *
    * @param  string  Username (to managesieve login)
    * @param  string  Password (to managesieve login)
    * @param  string  Managesieve server hostname/address
    * @param  string  Managesieve server port number
    * @param  string  Enable/disable TLS use
    * @param  array   Disabled extensions
    */
    public function __construct($username, $password='', $host='localhost', $port=2000,
			    $usetls=true, $disabled=array(), $debug=false)
    {
	$this->sieve = new Net_Sieve();
      
        if ($debug)
    	    $this->sieve->setDebug(true, array($this, 'debug_handler'));
      
        if (PEAR::isError($this->sieve->connect($host, $port, NULL, $usetls)))
    	    return $this->_set_error(SIEVE_ERROR_CONNECTION);

        if (PEAR::isError($this->sieve->login($username, $password)))
    	    return $this->_set_error(SIEVE_ERROR_LOGIN);

        $this->disabled = $disabled;
    }

    /**
    * Getter for error code
    */
    public function error()
    {
	return $this->error ? $this->error : false;
    }
			    
    /**
    * Saves current script into server
    */
    public function save($name = null)
    {
        if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);
	
	if (!$this->script)
	    return $this->_set_error(SIEVE_ERROR_INTERNAL);
	
	if (!$name)
	    $name = $this->current;

        $script = $this->script->as_text();

        if (!$script)
	    $script = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $script)))
    	    return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
    * Saves text script into server
    */
    public function save_script($name, $content = null)
    {
        if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);
	
        if (!$content)
	    $content = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $content)))
    	    return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
    * Activates specified script
    */
    public function activate($name = null)
    {
	if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);

	if (!$name)
	    $name = $this->current;

        if (PEAR::isError($this->sieve->setActive($name)))
    	    return $this->_set_error(SIEVE_ERROR_ACTIVATE);

        return true;
    }

    /**
    * Removes specified script
    */
    public function remove($name = null)
    {
	if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);

	if (!$name)
	    $name = $this->current;

	// script must be deactivated first
	if ($name == $this->sieve->getActive())
            if (PEAR::isError($this->sieve->setActive('')))
    		return $this->_set_error(SIEVE_ERROR_DELETE);

        if (PEAR::isError($this->sieve->removeScript($name)))
    	    return $this->_set_error(SIEVE_ERROR_DELETE);

	if ($name == $this->current)
	    $this->current = null;

        return true;
    }

    /**
    * Gets list of supported by server Sieve extensions
    */
    public function get_extensions()
    {
        if (!$this->sieve)
	    return $this->_set_error(SIEVE_ERROR_INTERNAL);
	
	$ext = $this->sieve->getExtensions();
	// we're working on lower-cased names
	$ext = array_map('strtolower', (array) $ext); 

	if ($this->script) {
	    $supported = $this->script->get_extensions();
	    foreach ($ext as $idx => $ext_name)
	        if (!in_array($ext_name, $supported))
		    unset($ext[$idx]);
	}

    	return array_values($ext);
    }

    /**
    * Gets list of scripts from server
    */
    public function get_scripts()
    {
        if (!$this->list) {

    	    if (!$this->sieve)
        	return $this->_set_error(SIEVE_ERROR_INTERNAL);
    
    	    $this->list = $this->sieve->listScripts();

    	    if (PEAR::isError($this->list))
    		return $this->_set_error(SIEVE_ERROR_OTHER);
	}

        return $this->list;
    }

    /**
    * Returns active script name
    */
    public function get_active()
    {
	if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);

	return $this->sieve->getActive();
    }
    
    /**
    * Loads script by name
    */
    public function load($name)
    {
        if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if ($this->current == $name)
    	    return true;
    
    	$script = $this->sieve->getScript($name);
    
        if (PEAR::isError($script))
    	    return $this->_set_error(SIEVE_ERROR_OTHER);

	// try to parse from Roundcube format
        $this->script = new rcube_sieve_script($script, $this->disabled);

        // ... else try Squirrelmail format
        if (empty($this->script->content) && $name == 'phpscript') {

    	    $script = $this->sieve->getScript('phpscript');
    	    $script = $this->_convert_from_squirrel_rules($script);

    	    $this->script = new rcube_sieve_script($script, $this->disabled);
    	}

    	$this->current = $name;

    	return true;
    }

    /**
    * Creates empty script or copy of other script
    */
    public function copy($name, $copy)
    {
        if (!$this->sieve)
    	    return $this->_set_error(SIEVE_ERROR_INTERNAL);

	if ($copy) {
    	    $content = $this->sieve->getScript($copy);
    
    	    if (PEAR::isError($content))
    		return $this->_set_error(SIEVE_ERROR_OTHER);
	}
	
	return $this->save_script($name, $content);
    }


    private function _convert_from_squirrel_rules($script)
    {
        $i = 0;
        $name = array();

        // tokenize rules
        if ($tokens = preg_split('/(#START_SIEVE_RULE.*END_SIEVE_RULE)\n/', $script, -1, PREG_SPLIT_DELIM_CAPTURE))
    	    foreach($tokens as $token) {
        	if (preg_match('/^#START_SIEVE_RULE.*/', $token, $matches)) {
	    	    $name[$i] = "unnamed rule ".($i+1);
            	    $content .= "# rule:[".$name[$i]."]\n";
                }
        	elseif (isset($name[$i])) {
	    	    $content .= "if $token\n";
		    $i++;
                }
            }

        return $content;
    }

    private function _set_error($error)
    {
	$this->error = $error;
        return false;
    }

    /**
    * This is our own debug handler for connection
    * @access public
    */
    public function debug_handler(&$sieve, $message)
    {
        write_log('sieve', preg_replace('/\r\n$/', '', $message));
    }		        
}

class rcube_sieve_script
{
  public $content = array();	// script rules array   

  private $supported = array(	// extensions supported by class
    'fileinto',
    'reject',
    'ereject',
    'vacation', 	// RFC5230
    // TODO: (most wanted first) body, imapflags, notify, regex
    );
  
  /**
    * Object constructor
    *
    * @param  string  Script's text content
    * @param  array   Disabled extensions
    */
  public function __construct($script, $disabled=NULL)
    {
      if (!empty($disabled))
        foreach ($disabled as $ext)
          if (($idx = array_search($ext, $this->supported)) !== false)
	    unset($this->supported[$idx]);

      $this->content = $this->_parse_text($script);
    }

  /**
    * Adds script contents as text to the script array (at the end)
    *
    * @param	string	Text script contents
    */
  public function add_text($script)
    {
      $content = $this->_parse_text($script);
      $result = false;
      
      // check existsing script rules names
      foreach ($this->content as $idx => $elem)
        $names[$elem['name']] = $idx;
      
      foreach ($content as $elem)
        if (!isset($names[$elem['name']]))
	  {
            array_push($this->content, $elem);
	    $result = true;
	  }

      return $result;
    }

  /**
    * Adds rule to the script (at the end)
    *
    * @param	string	Rule name
    * @param	array	Rule content (as array)
    */
  public function add_rule($content)
    {
      // TODO: check this->supported
      array_push($this->content, $content);
      return sizeof($this->content)-1;
    }

  public function delete_rule($index)
    {
      if(isset($this->content[$index]))
        {
          unset($this->content[$index]);
	  return true;
	}
      return false;
    }

  public function size()
    {
      return sizeof($this->content);
    }

  public function update_rule($index, $content)
    {
      // TODO: check this->supported
      if ($this->content[$index])
        {
	  $this->content[$index] = $content;
	  return $index;
	}
      return false;
    }

  /**
    * Returns script as text
    */
  public function as_text()
    {
      $script = '';
      $exts = array();
      $idx = 0;
      
      // rules
      foreach ($this->content as $rule)
        {
	  $extension = '';
	  $tests = array();
	  $i = 0;
	  
	  // header
	  $script .= '# rule:[' . $rule['name'] . "]\n";
  
	  // constraints expressions
	  foreach ($rule['tests'] as $test)
	    {
	      $tests[$i] = '';
	      switch ($test['test'])
	        {
		  case 'size':
		    $tests[$i] .= ($test['not'] ? 'not ' : '');
		    $tests[$i] .= 'size :' . ($test['type']=='under' ? 'under ' : 'over ') . $test['arg'];
		  break;
		  case 'true':
		    $tests[$i] .= ($test['not'] ? 'not true' : 'true');
		  break;
		  case 'exists':
		    $tests[$i] .= ($test['not'] ? 'not ' : '');
		    if (is_array($test['arg']))
			$tests[$i] .= 'exists ["' . implode('", "', $this->_escape_string($test['arg'])) . '"]';
		    else
			$tests[$i] .= 'exists "' . $this->_escape_string($test['arg']) . '"';
		  break;    
		  case 'header':
		    $tests[$i] .= ($test['not'] ? 'not ' : '');
		    $tests[$i] .= 'header :' . $test['type'];
		    if (is_array($test['arg1']))
			$tests[$i] .= ' ["' . implode('", "', $this->_escape_string($test['arg1'])) . '"]';
		    else
			$tests[$i] .= ' "' . $this->_escape_string($test['arg1']) . '"';
		    if (is_array($test['arg2']))
			$tests[$i] .= ' ["' . implode('", "', $this->_escape_string($test['arg2'])) . '"]';
		    else
			$tests[$i] .= ' "' . $this->_escape_string($test['arg2']) . '"';
		  break;
		}
	      $i++;
	    }
  
	  $script .= ($idx>0 ? 'els' : '').($rule['join'] ? 'if allof (' : 'if anyof (');
	  if (sizeof($tests) > 1)
	    $script .= implode(",\n\t", $tests);
	  elseif (sizeof($tests))
	    $script .= $tests[0];
	  else
	    $script .= 'true';
	  $script .= ")\n{\n";
  
	  // action(s)
	  foreach ($rule['actions'] as $action)
	    switch ($action['type'])
	    {
	      case 'fileinto':
	        $extension = 'fileinto';
		$script .= "\tfileinto \"" . $this->_escape_string($action['target']) . "\";\n";
	      break;
	      case 'redirect':
		$script .= "\tredirect \"" . $this->_escape_string($action['target']) . "\";\n";
	      break;
	      case 'reject':
	      case 'ereject':
	        $extension = $action['type'];
		if (strpos($action['target'], "\n")!==false)
		  $script .= "\t".$action['type']." text:\n" . $action['target'] . "\n.\n;\n";
		else
		  $script .= "\t".$action['type']." \"" . $this->_escape_string($action['target']) . "\";\n";
	      break;
	      case 'keep':
	      case 'discard':
	      case 'stop':
	        $script .= "\t" . $action['type'] .";\n";
	      break;
	      case 'vacation':
                $extension = 'vacation';
                $script .= "\tvacation";
		if ($action['days'])
		  $script .= " :days " . $action['days'];
		if ($action['addresses'])
		  $script .= " :addresses " . $this->_print_list($action['addresses']);
		if ($action['subject'])
		  $script .= " :subject \"" . $this->_escape_string($action['subject']) . "\"";
		if ($action['handle'])
		  $script .= " :handle \"" . $this->_escape_string($action['handle']) . "\"";
		if ($action['from'])
		  $script .= " :from \"" . $this->_escape_string($action['from']) . "\"";
		if ($action['mime'])
		  $script .= " :mime";
		if (strpos($action['reason'], "\n")!==false)
		  $script .= " text:\n" . $action['reason'] . "\n.\n;\n";
		else
		  $script .= " \"" . $this->_escape_string($action['reason']) . "\";\n";
	      break;
	    }
	  
	  $script .= "}\n";
	  $idx++;

	  if ($extension && !isset($exts[$extension]))
	    $exts[$extension] = $extension;
	}
      
      // requires
      if (sizeof($exts))
        $script = 'require ["' . implode('","', $exts) . "\"];\n" . $script;

      return $script;
    }

  /**
    * Returns script object
    *
    */
  public function as_array()
    {
      return $this->content;
    }

  /**
    * Returns array of supported extensions
    *
    */
  public function get_extensions()
    {
      return array_values($this->supported);
    }

  /**
    * Converts text script to rules array
    *
    * @param	string	Text script
    */
  private function _parse_text($script)
    {
      $i = 0;
      $content = array();

      // remove C comments
      $script = preg_replace('|/\*.*?\*/|sm', '', $script);

      // tokenize rules
      if ($tokens = preg_split('/(# rule:\[.*\])\r?\n/', $script, -1, PREG_SPLIT_DELIM_CAPTURE))
        foreach($tokens as $token)
	  {
	    if (preg_match('/^# rule:\[(.*)\]/', $token, $matches))
	      {
	        $content[$i]['name'] = $matches[1];
	      }
	    elseif (isset($content[$i]['name']) && sizeof($content[$i]) == 1)
	      {
	        if ($rule = $this->_tokenize_rule($token))
		  {
	    	    $content[$i] = array_merge($content[$i], $rule);
	    	    $i++;
		  }
		else // unknown rule format
		    unset($content[$i]);
	      }
	  }

      return $content;
    }

  /**
    * Convert text script fragment to rule object
    *
    * @param	string	Text rule
    */
  private function _tokenize_rule($content)
    {
      $result = NULL;
    
      if (preg_match('/^(if|elsif|else)\s+((true|not\s+true|allof|anyof|exists|header|not|size)(.*))\s+\{(.*)\}$/sm', trim($content), $matches))
        {
	  list($tests, $join) = $this->_parse_tests(trim($matches[2]));
	  $actions = $this->_parse_actions(trim($matches[5]));

	  if ($tests && $actions)
	    $result = array(
		    'tests' => $tests,
		    'actions' => $actions,
		    'join' => $join,
	    );
	}

      return $result;
    }    

  /**
    * Parse body of actions section
    *
    * @param	string	Text body
    * @return	array	Array of parsed action type/target pairs
    */
  private function _parse_actions($content)
    {
      $result = NULL;

      // supported actions
      $patterns[] = '^\s*discard;';
      $patterns[] = '^\s*keep;';
      $patterns[] = '^\s*stop;';
      $patterns[] = '^\s*redirect\s+(.*?[^\\\]);';
      if (in_array('fileinto', $this->supported))
        $patterns[] = '^\s*fileinto\s+(.*?[^\\\]);';
      if (in_array('reject', $this->supported)) {
        $patterns[] = '^\s*reject\s+text:(.*)\n\.\n;';
        $patterns[] = '^\s*reject\s+(.*?[^\\\]);';
        $patterns[] = '^\s*ereject\s+text:(.*)\n\.\n;';
        $patterns[] = '^\s*ereject\s+(.*?[^\\\]);';
      }
      if (in_array('vacation', $this->supported))
        $patterns[] = '^\s*vacation\s+(.*?[^\\\]);';

      $pattern = '/(' . implode('$)|(', $patterns) . '$)/ms';

      // parse actions body
      if (preg_match_all($pattern, $content, $mm, PREG_SET_ORDER))
      {
    	foreach ($mm as $m)
	{
	  $content = trim($m[0]);
	  
    	  if(preg_match('/^(discard|keep|stop)/', $content, $matches))
    	    {
	      $result[] = array('type' => $matches[1]);
	    }
          elseif(preg_match('/^fileinto/', $content))
    	    {
	      $result[] = array('type' => 'fileinto', 'target' => $this->_parse_string($m[sizeof($m)-1])); 
	    }
          elseif(preg_match('/^redirect/', $content))
    	    {
	      $result[] = array('type' => 'redirect', 'target' => $this->_parse_string($m[sizeof($m)-1])); 
	    }
    	  elseif(preg_match('/^(reject|ereject)\s+(.*);$/sm', $content, $matches))
    	    {
	      $result[] = array('type' => $matches[1], 'target' => $this->_parse_string($matches[2])); 
	    }
          elseif(preg_match('/^vacation\s+(.*);$/sm', $content, $matches))
            {
	      $vacation = array('type' => 'vacation');

    	      if (preg_match('/:(days)\s+([0-9]+)/', $content, $vm)) {
	        $vacation['days'] = $vm[2];
		$content = preg_replace('/:(days)\s+([0-9]+)/', '', $content); 
	      }
    	      if (preg_match('/:(subject)\s+(".*?[^\\\]")/', $content, $vm)) {
	        $vacation['subject'] = $vm[2];
		$content = preg_replace('/:(subject)\s+(".*?[^\\\]")/', '', $content); 
	      }
    	      if (preg_match('/:(addresses)\s+\[(.*?[^\\\])\]/', $content, $vm)) {
	        $vacation['addresses'] = $this->_parse_list($vm[2]);
		$content = preg_replace('/:(addresses)\s+\[(.*?[^\\\])\]/', '', $content); 
	      }
    	      if (preg_match('/:(handle)\s+(".*?[^\\\]")/', $content, $vm)) {
	        $vacation['handle'] = $vm[2];
		$content = preg_replace('/:(handle)\s+(".*?[^\\\]")/', '', $content); 
	      }
    	      if (preg_match('/:(from)\s+(".*?[^\\\]")/', $content, $vm)) {
	        $vacation['from'] = $vm[2];
		$content = preg_replace('/:(from)\s+(".*?[^\\\]")/', '', $content); 
	      }
	      $content = preg_replace('/^vacation/', '', $content);	     
	      $content = preg_replace('/;$/', '', $content);
	      $content = trim($content);
    	      if (preg_match('/^:(mime)/', $content, $vm)) {
	        $vacation['mime'] = true;
		$content = preg_replace('/^:mime/', '', $content); 
	      }

	      $vacation['reason'] = $this->_parse_string($content);

              $result[] = $vacation;
            }
        }
      }

      return $result;
    }    
    
   /**
    * Parse test/conditions section
    *
    * @param	string	Text 
    */

  private function _parse_tests($content)
    {
      $result = NULL;

      // lists
      if (preg_match('/^(allof|anyof)\s+\((.*)\)$/sm', $content, $matches))
	{
	  $content = $matches[2];
	  $join = $matches[1]=='allof' ? true : false;
	}
      else
	  $join = false;
      
      // supported tests regular expressions
      // TODO: comparators, envelope
      $patterns[] = '(not\s+)?(exists)\s+\[(.*?[^\\\])\]';
      $patterns[] = '(not\s+)?(exists)\s+(".*?[^\\\]")';
      $patterns[] = '(not\s+)?(true)';
      $patterns[] = '(not\s+)?(size)\s+:(under|over)\s+([0-9]+[KGM]{0,1})';
      $patterns[] = '(not\s+)?(header)\s+:(contains|is|matches)\s+\[(.*?[^\\\]")\]\s+\[(.*?[^\\\]")\]';
      $patterns[] = '(not\s+)?(header)\s+:(contains|is|matches)\s+(".*?[^\\\]")\s+(".*?[^\\\]")';
      $patterns[] = '(not\s+)?(header)\s+:(contains|is|matches)\s+\[(.*?[^\\\]")\]\s+(".*?[^\\\]")';
      $patterns[] = '(not\s+)?(header)\s+:(contains|is|matches)\s+(".*?[^\\\]")\s+\[(.*?[^\\\]")\]';
      
      // join patterns...
      $pattern = '/(' . implode(')|(', $patterns) . ')/';

      // ...and parse tests list
      if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER))
        {
	  foreach ($matches as $match)
	    {
	      $size = sizeof($match);
	      
	      if (preg_match('/^(not\s+)?size/', $match[0]))
	        {
		  $result[] = array(
		    'test' 	=> 'size',
		    'not' 	=> $match[$size-4] ? true : false,
		    'type' 	=> $match[$size-2], // under/over
		    'arg'	=> $match[$size-1], // value
		  );
		}
	      elseif (preg_match('/^(not\s+)?header/', $match[0]))
	    	{
		  $result[] = array(
		    'test'	=> 'header',
		    'not' 	=> $match[$size-5] ? true : false,
		    'type'	=> $match[$size-3], // is/contains/matches
		    'arg1' 	=> $this->_parse_list($match[$size-2]), // header(s)
		    'arg2'	=> $this->_parse_list($match[$size-1]), // string(s)
		  );  
		}
	      elseif (preg_match('/^(not\s+)?exists/', $match[0]))
		{
		  $result[] = array(
		    'test' 	=> 'exists',
		    'not' 	=> $match[$size-3] ? true : false,
		    'arg' 	=> $this->_parse_list($match[$size-1]), // header(s)
		  );
	        }
	      elseif (preg_match('/^(not\s+)?true/', $match[0]))
		{
		  $result[] = array(
		    'test' 	=> 'true',
		    'not' 	=> $match[$size-2] ? true : false,
		  );
	        }
	    }
	}

      return array($result, $join);
    }    

   /**
    * Parse string value
    *
    * @param	string	Text 
    */
  private function _parse_string($content)
    {
      $text = '';
      $content = trim($content);

      if (preg_match('/^text:(.*)\.$/sm', $content, $matches))
        $text = trim($matches[1]);
      elseif (preg_match('/^"(.*)"$/', $content, $matches))
        $text = str_replace('\"', '"', $matches[1]);

      return $text;
    }    

   /**
    * Escape special chars in string value
    *
    * @param	string	Text 
    */
  private function _escape_string($content)
    {
      $replace['/"/'] = '\\"';
      
      if (is_array($content))
        {
	  for ($x=0, $y=sizeof($content); $x<$y; $x++)
	    $content[$x] = preg_replace(array_keys($replace), array_values($replace), $content[$x]);
        
	  return $content;
	}
      else
        return preg_replace(array_keys($replace), array_values($replace), $content);
    }

   /**
    * Parse string or list of strings to string or array of strings
    *
    * @param	string	Text 
    */
  private function _parse_list($content)
    {
      $result = array();
      
      for ($x=0, $len=strlen($content); $x<$len; $x++)
        {
	  switch ($content[$x])
	    {
	      case '\\':
	        $str .= $content[++$x];
              break;
	      case '"':
	        if (isset($str))
	    	  {
		    $result[] = $str;
		    unset($str);
		  }
	        else
	          $str = '';
	      break;
	      default:
	        if(isset($str))
	          $str .= $content[$x];
	      break;
	    }
	}
      
      if (sizeof($result)>1)
        return $result;
      elseif (sizeof($result) == 1)
	return $result[0];
      else
        return NULL;
    }    

   /**
    * Convert array of elements to list of strings
    *
    * @param	string	Text 
    */
  private function _print_list($list)
    {
      $list = (array) $list;
      foreach($list as $idx => $val)
        $list[$idx] = $this->_escape_string($val);
    
      return '["' . implode('","', $list) . '"]';
    }
}

?>
