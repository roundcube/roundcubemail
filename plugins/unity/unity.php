<?php

/**
 * Unity desktop integration
 *
 * Starting version 12.10, Ubuntu provides an API for so called
 * Web apps to integrate themselves with the desktop and thus provide
 * a smoother axperience to the end user.
 *
 * @version 1.0
 * @author Jean-Tiare LE BIGOT <admin@jtlebi.fr>
 * @url http://example.com
 */
class unity extends rcube_plugin
{
  function init()
  {
    $this->include_script('unity.js');
  }
}
