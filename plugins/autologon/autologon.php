<?php

/**
 * Sample plugin to try out some hooks.
 * This performs an automatic login if accessed from localhost
 */
class autologon extends rcube_plugin
{

  function init()
  {
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('authenticate', array($this, 'authenticate'));
  }

  function startup($args)
  {
    $rcmail = rcmail::get_instance();

    // change action to login
    if ($args['task'] == 'mail' && empty($args['action']) && empty($_SESSION['user_id']) && !empty($_GET['_autologin']) && $this->is_localhost())
      $args['action'] = 'login';

    return $args;
  }

  function authenticate($args)
  {
    if (!empty($_GET['_autologin']) && $this->is_localhost()) {
      $args['user'] = 'me';
      $args['pass'] = '******';
      $args['host'] = 'localhost';
    }
  
    return $args;
  }

  function is_localhost()
  {
    return $_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1';
  }

}

