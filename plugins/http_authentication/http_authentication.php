<?php

/**
 * HTTP Basic Authentication
 *
 * Make use of an existing HTTP authentication and perform login with the existing user credentials
 *
 * @version 1.0
 * @author Thomas Bruederli
 */
class http_authentication extends rcube_plugin
{
  public $task = 'login';

  function init()
  {
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('authenticate', array($this, 'authenticate'));
  }

  function startup($args)
  {
    // change action to login
    if (empty($args['action']) && empty($_SESSION['user_id'])
        && !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']))
      $args['action'] = 'login';

    return $args;
  }

  function authenticate($args)
  {
    if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
      $args['user'] = $_SERVER['PHP_AUTH_USER'];
      $args['pass'] = $_SERVER['PHP_AUTH_PW'];
    }
  
    return $args;
  }

}

