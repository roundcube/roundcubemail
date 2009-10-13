<?php

/**
 * Enforce secure HTTPs connection for login
 *
 * Configuration:
 * // Port for https connection
 * $rcmail_config['force_https_port'] = 443;
 *
 * @version 1.0
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 */
class force_https extends rcube_plugin
{
  function init()
  {
    $this->add_hook('startup', array($this, 'redirect'));
  }

  function redirect($args)
  {
    $config = rcmail::get_instance()->config;
    
    $port = (int) $config->get('force_https_port', 443);

    // check if https is required (for login) and redirect if necessary
    if (empty($_SESSION['user_id']) && !$config->get('use_https')
	&& (!isset($_SERVER['HTTPS']) || $_SERVER['SERVER_PORT'] != $port))
    {
      header('Location: https://' . $_SERVER['HTTP_HOST'] . ($port != 443 ? ":$port" : '') . $_SERVER['REQUEST_URI']);
      exit;
    }
	
    return $args;
  }
}

?>
