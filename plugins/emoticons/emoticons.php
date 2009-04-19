<?php

/**
 * Display Emoticons
 *
 * Sample plugin to replace emoticons in plain text message body with real icons
 *
 * @version 1.0.1
 * @author Thomas Bruederli
 * @website http://roundcube.net
 */
class emoticons extends rcube_plugin
{
  public $task = 'mail';
  private $map;

  function init()
  {
    $this->task = 'mail';
    $this->add_hook('message_part_after', array($this, 'replace'));
  
    $this->map = array(
      ':)'  => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif', 'alt' => ':)')),
      ':-)' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif', 'alt' => ':-)')),
      ':('  => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-cry.gif', 'alt' => ':(')),
      ':-(' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-cry.gif', 'alt' => ':-(')),
    );
  }

  function replace($args)
  {
    if ($args['type'] == 'plain')
      return array('body' => strtr($args['body'], $this->map));
  
    return null;
  }

}

