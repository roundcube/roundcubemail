<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_plugin.php                                      |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |  Abstract plugins interface/class                                     |
 |  All plugins need to extend this class                                |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: $

*/

/**
 * Plugin interface class
 *
 * @package Core
 */
abstract class rcube_plugin
{
  public $ID;
  public $api;
  public $task;
  protected $home;
  protected $urlbase;

  /**
   * Default constructor.
   */
  public function __construct($api)
  {
    $this->ID = get_class($this);
    $this->api = $api;
    $this->home = $api->dir . DIRECTORY_SEPARATOR . $this->ID;
    $this->urlbase = $api->url . $this->ID . '/';
  }
  
  /**
   * Initialization method, needs to be implemented by the plugin itself
   */
  abstract function init();
  
  /**
   * Load local config file from plugins directory.
   * The loaded values are patched over the global configuration.
   *
   * @param string Config file name relative to the plugin's folder
   * @return boolean True on success, false on failure
   */
  public function load_config($fname = 'config.inc.php')
  {
    $fpath = $this->home.'/'.$fname;
    $rcmail = rcmail::get_instance();
    if (!$rcmail->config->load_from_file($fpath)) {
      raise_error(array('code' => 527, 'type' => 'php', 'message' => "Failed to load config from $fpath"), true, false);
      return false;
    }
    
    return true;
  }

  /**
   * Register a callback function for a specific (server-side) hook
   *
   * @param string Hook name
   * @param mixed Callback function as string or array with object reference and method name
   */
  public function add_hook($hook, $callback)
  {
    $this->api->register_hook($hook, $callback);
  }
  
  /**
   * Load localized texts from the plugins dir
   *
   * @param string Directory to search in
   * @param mixed Make texts also available on the client (array with list or true for all)
   */
  public function add_texts($dir, $add2client = false)
  {
    $domain = $this->ID;
    
    $lang = $_SESSION['language'];
    $locdir = slashify(realpath(slashify($this->home) . $dir));
    $texts = array();
    
    foreach (array('en_US', $lang) as $lng) {
      @include($locdir . $lng . '.inc');
      $texts = (array)$labels + (array)$messages + (array)$texts;
    }

    // prepend domain to text keys and add to the application texts repository
    if (!empty($texts)) {
      $add = array();
      foreach ($texts as $key => $value)
        $add[$domain.'.'.$key] = $value;

      $rcmail = rcmail::get_instance();
      $rcmail->load_language($lang, $add);
      
      // add labels to client
      if ($add2client) {
        $js_labels = is_array($add2client) ? array_map(array($this, 'label_map_callback'), $add2client) : array_keys($add);
        $rcmail->output->add_label($js_labels);
      }
    }
  }
  
  /**
   * Wrapper for rcmail::gettext() adding the plugin ID as domain
   *
   * @return string Localized text
   * @see rcmail::gettext()
   */
  public function gettext($p)
  {
    return rcmail::get_instance()->gettext($p, $this->ID);
  }

  /**
   * Register this plugin to be responsible for a specific task
   *
   * @param string Task name (only characters [a-z0-9_.-] are allowed)
   */
  public function register_task($task)
  {
    if ($task != asciiwords($task)) {
      raise_error(array('code' => 526, 'type' => 'php', 'message' => "Invalid task name: $task. Only characters [a-z0-9_.-] are allowed"), true, false);
    }
    else if (in_array(rcmail::$main_tasks, $task)) {
      raise_error(array('code' => 526, 'type' => 'php', 'message' => "Cannot register taks $task; already taken by another plugin or the application itself"), true, false);
    }
    else {
      rcmail::$main_tasks[] = $task;
    }
  }

  /**
    * Register a handler for a specific client-request action
    *
    * The callback will be executed upon a request like /?_task=mail&_action=plugin.myaction
    *
    * @param string Action name (should be unique)
    * @param mixed Callback function as string or array with object reference and method name
   */
  public function register_action($action, $callback)
  {
    $this->api->register_action($action, $this->ID, $callback);
  }

  /**
   * Register a handler function for a template object
   *
   * When parsing a template for display, tags like <roundcube:object name="plugin.myobject" />
   * will be replaced by the return value if the registered callback function.
   *
   * @param string Object name (should be unique and start with 'plugin.')
   * @param mixed Callback function as string or array with object reference and method name
   */
  public function register_handler($name, $callback)
  {
    $this->api->register_handler($name, $this->ID, $callback);
  }

  /**
   * Make this javascipt file available on the client
   *
   * @param string File path; absolute or relative to the plugin directory
   */
  public function include_script($fn)
  {
    $this->api->include_script($this->ressource_url($fn));
  }

  /**
   * Make this stylesheet available on the client
   *
   * @param string File path; absolute or relative to the plugin directory
   */
  public function include_stylesheet($fn)
  {
    $this->api->include_stylesheet($this->ressource_url($fn));
  }
  
  /**
   * Append a button to a certain container
   *
   * @param array Hash array with named parameters (as used in skin templates)
   * @param string Container name where the buttons should be added to
   * @see rcube_remplate::button()
   */
  public function add_button($p, $container)
  {
    if ($this->api->output->type == 'html') {
      // fix relative paths
      foreach (array('imagepas', 'imageact', 'imagesel') as $key)
        if ($p[$key])
          $p[$key] = $this->api->url . $this->ressource_url($p[$key]);
      
      $this->api->add_content($this->api->output->button($p), $container);
    }
  }

  /**
   * Make the given file name link into the plugin directory
   */
  private function ressource_url($fn)
  {
    if ($fn[0] != '/' && !preg_match('|^https?://|i', $fn))
      return $this->ID . '/' . $fn;
    else
      return $fn;
  }

  /**
   * Callback function for array_map
   */
  private function label_map_callback($key)
  {
    return $this->ID.'.'.$key;
  }


}

