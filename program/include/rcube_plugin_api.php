<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_plugin_api.php                                  |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Plugins repository                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * The plugin loader and global API
 *
 * @package PluginAPI
 */
class rcube_plugin_api
{
  static private $instance;
  
  public $dir;
  public $url = 'plugins/';
  public $output;
  
  public $handlers = array();
  private $plugins = array();
  private $tasks = array();
  private $actions = array();
  private $actionmap = array();
  private $objectsmap = array();
  private $template_contents = array();
  
  private $required_plugins = array('filesystem_attachments');
  private $active_hook = false;

  // Deprecated names of hooks, will be removed after 0.5-stable release
  private $deprecated_hooks = array(
    'create_user'       => 'user_create',
    'kill_session'      => 'session_destroy',
    'upload_attachment' => 'attachment_upload',
    'save_attachment'   => 'attachment_save',
    'get_attachment'    => 'attachment_get',
    'cleanup_attachments' => 'attachments_cleanup',
    'display_attachment' => 'attachment_display',
    'remove_attachment' => 'attachment_delete',
    'outgoing_message_headers' => 'message_outgoing_headers',
    'outgoing_message_body' => 'message_outgoing_body',
    'address_sources'   => 'addressbooks_list',
    'get_address_book'  => 'addressbook_get',
    'create_contact'    => 'contact_create',
    'save_contact'      => 'contact_save',
    'delete_contact'    => 'contact_delete',
    'manage_folders'    => 'folders_list',
    'list_mailboxes'    => 'mailboxes_list',
    'save_preferences'  => 'preferences_save',
    'user_preferences'  => 'preferences_list',
    'list_prefs_sections' => 'preferences_sections_list',
    'list_identities'   => 'identities_list',
    'create_identity'   => 'identity_create',
    'save_identity'     => 'identity_save',
  );

  /**
   * This implements the 'singleton' design pattern
   *
   * @return object rcube_plugin_api The one and only instance if this class
   */
  static function get_instance()
  {
    if (!self::$instance) {
      self::$instance = new rcube_plugin_api();
    }

    return self::$instance;
  }
  
  
  /**
   * Private constructor
   */
  private function __construct()
  {
    $this->dir = INSTALL_PATH . $this->url;
  }
  
  
  /**
   * Load and init all enabled plugins
   *
   * This has to be done after rcmail::load_gui() or rcmail::json_init()
   * was called because plugins need to have access to rcmail->output
   */
  public function init()
  {
    $rcmail = rcmail::get_instance();
    $this->output = $rcmail->output;

    $plugins_dir = dir($this->dir);
    $plugins_dir = unslashify($plugins_dir->path);
    $plugins_enabled = (array)$rcmail->config->get('plugins', array());

    foreach ($plugins_enabled as $plugin_name) {
      $fn = $plugins_dir . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . $plugin_name . '.php';

      if (file_exists($fn)) {
        include($fn);

        // instantiate class if exists
        if (class_exists($plugin_name, false)) {
          $plugin = new $plugin_name($this);
          // check inheritance and task specification
          if (is_subclass_of($plugin, 'rcube_plugin') && (!$plugin->task || preg_match('/^('.$plugin->task.')$/i', $rcmail->task))) {
            $plugin->init();
            $this->plugins[] = $plugin;
          }
        }
        else {
          raise_error(array('code' => 520, 'type' => 'php',
	    'file' => __FILE__, 'line' => __LINE__,
	    'message' => "No plugin class $plugin_name found in $fn"), true, false);
        }
      }
      else {
        raise_error(array('code' => 520, 'type' => 'php',
	  'file' => __FILE__, 'line' => __LINE__,
	  'message' => "Failed to load plugin file $fn"), true, false);
      }
    }
    
    // check existance of all required core plugins
    foreach ($this->required_plugins as $plugin_name) {
      $loaded = false;
      foreach ($this->plugins as $plugin) {
        if ($plugin instanceof $plugin_name) {
          $loaded = true;
          break;
        }
      }
      
      // load required core plugin if no derivate was found
      if (!$loaded) {
        $fn = $plugins_dir . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . $plugin_name . '.php';

        if (file_exists($fn)) {
          include_once($fn);
          
          if (class_exists($plugin_name, false)) {
            $plugin = new $plugin_name($this);
            // check inheritance
            if (is_subclass_of($plugin, 'rcube_plugin')) {
	      if (!$plugin->task || preg_match('/('.$plugin->task.')/i', $rcmail->task)) {
                $plugin->init();
                $this->plugins[] = $plugin;
              }
	      $loaded = true;
            }
          }
        }
      }
      
      // trigger fatal error if still not loaded
      if (!$loaded) {
        raise_error(array('code' => 520, 'type' => 'php',
	  'file' => __FILE__, 'line' => __LINE__,
	  'message' => "Requried plugin $plugin_name was not loaded"), true, true);
      }
    }

    // register an internal hook
    $this->register_hook('template_container', array($this, 'template_container_hook'));
    
    // maybe also register a shudown function which triggers shutdown functions of all plugin objects
  }
  
  
  /**
   * Allows a plugin object to register a callback for a certain hook
   *
   * @param string Hook name
   * @param mixed String with global function name or array($obj, 'methodname')
   */
  public function register_hook($hook, $callback)
  {
    if (is_callable($callback)) {
      if (isset($this->deprecated_hooks[$hook])) {
        /* Uncoment after 0.4-stable release
        raise_error(array('code' => 522, 'type' => 'php',
          'file' => __FILE__, 'line' => __LINE__,
          'message' => "Deprecated hook name. ".$hook.' -> '.$this->deprecated_hooks[$hook]), true, false);
        */
        $hook = $this->deprecated_hooks[$hook];
      }
      $this->handlers[$hook][] = $callback;
    }
    else
      raise_error(array('code' => 521, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Invalid callback function for $hook"), true, false);
  }
  
  
  /**
   * Triggers a plugin hook.
   * This is called from the application and executes all registered handlers
   *
   * @param string Hook name
   * @param array Named arguments (key->value pairs)
   * @return array The (probably) altered hook arguments
   */
  public function exec_hook($hook, $args = array())
  {
    if (!is_array($args))
      $args = array('arg' => $args);

    $args += array('abort' => false);
    $this->active_hook = $hook;
    
    foreach ((array)$this->handlers[$hook] as $callback) {
      $ret = call_user_func($callback, $args);
      if ($ret && is_array($ret))
        $args = $ret + $args;
      
      if ($args['abort'])
        break;
    }
    
    $this->active_hook = false;
    return $args;
  }


  /**
   * Let a plugin register a handler for a specific request
   *
   * @param string Action name (_task=mail&_action=plugin.foo)
   * @param string Plugin name that registers this action
   * @param mixed Callback: string with global function name or array($obj, 'methodname')
   * @param string Task name registered by this plugin
   */
  public function register_action($action, $owner, $callback, $task = null)
  {
    // check action name
    if ($task)
      $action = $task.'.'.$action;
    else if (strpos($action, 'plugin.') !== 0)
      $action = 'plugin.'.$action;
    
    // can register action only if it's not taken or registered by myself
    if (!isset($this->actionmap[$action]) || $this->actionmap[$action] == $owner) {
      $this->actions[$action] = $callback;
      $this->actionmap[$action] = $owner;
    }
    else {
      raise_error(array('code' => 523, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Cannot register action $action; already taken by another plugin"), true, false);
    }
  }


  /**
   * This method handles requests like _task=mail&_action=plugin.foo
   * It executes the callback function that was registered with the given action.
   *
   * @param string Action name
   */
  public function exec_action($action)
  {
    if (isset($this->actions[$action])) {
      call_user_func($this->actions[$action]);
    }
    else {
      raise_error(array('code' => 524, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "No handler found for action $action"), true, true);
    }
  }


  /**
   * Register a handler function for template objects
   *
   * @param string Object name
   * @param string Plugin name that registers this action
   * @param mixed Callback: string with global function name or array($obj, 'methodname')
   */
  public function register_handler($name, $owner, $callback)
  {
    // check name
    if (strpos($name, 'plugin.') !== 0)
      $name = 'plugin.'.$name;
    
    // can register handler only if it's not taken or registered by myself
    if (!isset($this->objectsmap[$name]) || $this->objectsmap[$name] == $owner) {
      $this->output->add_handler($name, $callback);
      $this->objectsmap[$name] = $owner;
    }
    else {
      raise_error(array('code' => 525, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Cannot register template handler $name; already taken by another plugin"), true, false);
    }
  }
  
  
  /**
   * Register this plugin to be responsible for a specific task
   *
   * @param string Task name (only characters [a-z0-9_.-] are allowed)
   * @param string Plugin name that registers this action
   */
  public function register_task($task, $owner)
  {
    if ($task != asciiwords($task)) {
      raise_error(array('code' => 526, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Invalid task name: $task. Only characters [a-z0-9_.-] are allowed"), true, false);
    }
    else if (in_array($task, rcmail::$main_tasks)) {
      raise_error(array('code' => 526, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => "Cannot register taks $task; already taken by another plugin or the application itself"), true, false);
    }
    else {
      $this->tasks[$task] = $owner;
      rcmail::$main_tasks[] = $task;
      return true;
    }
    
    return false;
  }


  /**
   * Checks whether the given task is registered by a plugin
   *
   * @return boolean True if registered, otherwise false
   */
  public function is_plugin_task($task)
  {
    return $this->tasks[$task] ? true : false;
  }


  /**
   * Check if a plugin hook is currently processing.
   * Mainly used to prevent loops and recursion.
   *
   * @param string Hook to check (optional)
   * @return boolean True if any/the given hook is currently processed, otherwise false
   */
  public function is_processing($hook = null)
  {
    return $this->active_hook && (!$hook || $this->active_hook == $hook);
  }
  
  /**
   * Include a plugin script file in the current HTML page
   */
  public function include_script($fn)
  {
    if ($this->output->type == 'html') {
      $src = $this->resource_url($fn);
      $this->output->add_header(html::tag('script', array('type' => "text/javascript", 'src' => $src)));
    }
  }

  /**
   * Include a plugin stylesheet in the current HTML page
   */
  public function include_stylesheet($fn)
  {
    if ($this->output->type == 'html') {
      $src = $this->resource_url($fn);
      $this->output->add_header(html::tag('link', array('rel' => "stylesheet", 'type' => "text/css", 'href' => $src)));
    }
  }
  
  /**
   * Save the given HTML content to be added to a template container
   */
  public function add_content($html, $container)
  {
    $this->template_contents[$container] .= $html . "\n";
  }
  
  /**
   * Callback for template_container hooks
   */
  private function template_container_hook($attrib)
  {
    $container = $attrib['name'];
    return array('content' => $attrib['content'] . $this->template_contents[$container]);
  }
  
  /**
   * Make the given file name link into the plugins directory
   */
  private function resource_url($fn)
  {
    if ($fn[0] != '/' && !preg_match('|^https?://|i', $fn))
      return $this->url . $fn;
    else
      return $fn;
  }

}

