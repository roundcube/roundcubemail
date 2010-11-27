<?php

/**
 * Display Emoticons
 *
 * Sample plugin to replace emoticons in plain text message body with real icons
 *
 * @version 1.2.0
 * @author Thomas Bruederli
 * @author Aleksander Machniak
 * @website http://roundcube.net
 */
class emoticons extends rcube_plugin
{
    public $task = 'mail';

    function init()
    {
        $this->add_hook('message_part_after', array($this, 'replace'));
    }

    function replace($args)
    {
        // This is a lookbehind assertion which will exclude html entities
        // E.g. situation when ";)" in "&quot;)" shouldn't be replaced by the icon
        // It's so long because of assertion format restrictions
        $entity = '(?<!&'
            . '[a-zA-Z0-9]{2}' . '|' . '#[0-9]{2}' . '|'
            . '[a-zA-Z0-9]{3}' . '|' . '#[0-9]{3}' . '|'
            . '[a-zA-Z0-9]{4}' . '|' . '#[0-9]{4}' . '|'
            . '[a-zA-Z0-9]{5}' . '|'
            . '[a-zA-Z0-9]{6}' . '|'
            . '[a-zA-Z0-9]{7}'
            . ')';

        // map of emoticon replacements
        $map = array(
            '/:\)/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif',
                'title' => ':)'
            )),
            '/:-\)/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-smile.gif',
                'title' => ':-)'
            )),
            '/(?<!mailto):D/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-laughing.gif',
                'title' => ':D'
            )),
            '/:-D/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-laughing.gif',
                'title' => ':-D'
            )),
            '/:\(/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-frown.gif',
                'title' => ':('
            )),
            '/:-\(/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-frown.gif',
                'title' => ':-('
            )),
            '/'.$entity.';\)/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-wink.gif',
                'title' => ';)'
            )),
            '/'.$entity.';-\)/' => html::img(array(
                'src'   => './program/js/tiny_mce/plugins/emotions/img/smiley-wink.gif',
                'title' => ';-)'
            )),
        );

        if ($args['type'] == 'plain') {
            $args['body'] = preg_replace(
                array_keys($map), array_values($map), $args['body']);
        }

        return $args;
    }

}
