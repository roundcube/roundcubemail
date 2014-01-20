<?php

/**
 * Display Emoticons
 *
 * Sample plugin to replace emoticons in plain text message body with real icons
 *
 * @version @package_version@
 * @license GNU GPLv3+
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
            '/:\)/'             => $this->img_tag('smiley-smile.gif',       ':)'    ),
            '/:-\)/'            => $this->img_tag('smiley-smile.gif',       ':-)'   ),
            '/(?<!mailto):D/'   => $this->img_tag('smiley-laughing.gif',    ':D'    ),
            '/:-D/'             => $this->img_tag('smiley-laughing.gif',    ':-D'   ),
            '/:\(/'             => $this->img_tag('smiley-frown.gif',       ':('    ),
            '/:-\(/'            => $this->img_tag('smiley-frown.gif',       ':-('   ),
            '/'.$entity.';\)/'  => $this->img_tag('smiley-wink.gif',        ';)'    ),
            '/'.$entity.';-\)/' => $this->img_tag('smiley-wink.gif',        ';-)'   ),
            '/8\)/'             => $this->img_tag('smiley-cool.gif',        '8)'    ),
            '/8-\)/'            => $this->img_tag('smiley-cool.gif',        '8-)'   ),
            '/(?<!mailto):O/i'  => $this->img_tag('smiley-surprised.gif',   ':O'    ),
            '/(?<!mailto):-O/i' => $this->img_tag('smiley-surprised.gif',   ':-O'   ),
            '/(?<!mailto):P/i'  => $this->img_tag('smiley-tongue-out.gif',  ':P'    ),
            '/(?<!mailto):-P/i' => $this->img_tag('smiley-tongue-out.gif',  ':-P'   ),
            '/(?<!mailto):@/i'  => $this->img_tag('smiley-yell.gif',        ':@'    ),
            '/(?<!mailto):-@/i' => $this->img_tag('smiley-yell.gif',        ':-@'   ),
            '/O:\)/i'           => $this->img_tag('smiley-innocent.gif',    'O:)'   ),
            '/O:-\)/i'          => $this->img_tag('smiley-innocent.gif',    'O:-)'  ),
            '/(?<!mailto):$/'   => $this->img_tag('smiley-embarassed.gif',  ':$'    ),
            '/(?<!mailto):-$/'  => $this->img_tag('smiley-embarassed.gif',  ':-$'   ),
            '/(?<!mailto):\*/i'  => $this->img_tag('smiley-kiss.gif',       ':*'    ),
            '/(?<!mailto):-\*/i' => $this->img_tag('smiley-kiss.gif',       ':-*'   ),
            '/(?<!mailto):S/i'  => $this->img_tag('smiley-undecided.gif',   ':S'    ),
            '/(?<!mailto):-S/i' => $this->img_tag('smiley-undecided.gif',   ':-S'   ),
        );

        if ($args['type'] == 'plain') {
            $args['body'] = preg_replace(
                array_keys($map), array_values($map), $args['body']);
        }

        return $args;
    }

    private function img_tag($ico, $title)
    {
        $path = './program/js/tinymce/plugins/emoticons/img/';
        return html::img(array('src' => $path.$ico, 'title' => $title));
    }
}
