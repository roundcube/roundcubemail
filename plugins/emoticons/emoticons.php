<?php

/**
 * Display Emoticons
 *
 * Sample plugin to replace emoticons in plain text message body with real icons
 *
 * @version 1.1.0
 * @author Thomas Bruederli
 * @author Aleksander Machniak
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
            '/:\)/'  => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif', 'title' => ':)')),
            '/:-\)/' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif', 'title' => ':-)')),
            '/(?<!mailto):D/' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-laughing.gif', 'title' => ':D')),
            '/:-D/' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-laughing.gif', 'title' => ':-D')),
            '/;\)/'  => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-wink.gif', 'title' => ';)')),
            '/;-\)/' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-wink.gif', 'title' => ';-)')),
            '/:\(/'  => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-frown.gif', 'title' => ':(')),
            '/:-\(/' => html::img(array('src' => './program/js/tiny_mce/plugins/emotions/img/smiley-frown.gif', 'title' => ':-(')),
        );
    }

    function replace($args)
    {
        if ($args['type'] == 'plain') {
            $args['body'] = preg_replace(
                array_keys($this->map), array_values($this->map), $args['body']);
        }
        return $args;
    }

}
