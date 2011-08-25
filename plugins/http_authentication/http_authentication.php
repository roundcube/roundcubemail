<?php

/**
 * HTTP Basic Authentication
 *
 * Make use of an existing HTTP authentication and perform login with the existing user credentials
 *
 * Configuration:
 * // redirect the client to this URL after logout. This page is then responsible to clear HTTP auth
 * $rcmail_config['logout_url'] = 'http://server.tld/logout.html';
 *
 * See logout.html (in this directory) for an example how HTTP auth can be cleared.
 *
 * @version 1.4
 * @author Thomas Bruederli
 */
class http_authentication extends rcube_plugin
{
  public $task = 'login|logout';

  function init()
  {
    $this->add_hook('startup', array($this, 'startup'));
    $this->add_hook('authenticate', array($this, 'authenticate'));
    $this->add_hook('logout_after', array($this, 'logout'));
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
    // Allow entering other user data in login form,
    // e.g. after log out (#1487953)
    if (!empty($args['user'])) {
        return $args;
    }

    if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
      $args['user'] = $_SERVER['PHP_AUTH_USER'];
      $args['pass'] = $_SERVER['PHP_AUTH_PW'];
    }

    $args['cookiecheck'] = false;
    $args['valid'] = true;

    return $args;
  }

  function logout($args)
  {
    // redirect to configured URL in order to clear HTTP auth credentials
    if (!empty($_SERVER['PHP_AUTH_USER']) && $args['user'] == $_SERVER['PHP_AUTH_USER'] && ($url = rcmail::get_instance()->config->get('logout_url'))) {
      header("Location: $url", true, 307);
    }
  }

}

