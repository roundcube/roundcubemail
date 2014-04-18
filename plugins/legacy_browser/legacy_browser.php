<?php

/**
 * Plugin which adds support for legacy browsers (IE 7/8)
 *
 * @author Aleksander Machniak <alec@alec.pl>
 * @license GNU GPLv3+
 */
class legacy_browser extends rcube_plugin
{
    public $noajax = true;

    public function init()
    {
        $rcube = rcube::get_instance();

        if ($rcube->output->browser->ie && $rcube->output->browser->ver < 9) {
            $this->add_hook('send_page', array($this, 'send_page'));
            $this->add_hook('render_page', array($this, 'render_page'));
        }
    }

    function send_page($args)
    {
        // replace jQuery 2.x with 1.x
        $ts1 = filemtime($this->home . '/js/jquery.min.js');
        $ts2 = filemtime($this->home . '/js/iehacks.js');
        $args['content'] = preg_replace(
            '|<script src="program/js/jquery\.min\.js\?s=[0-9]+" type="text/javascript"></script>|',
            '<script src="plugins/legacy_browser/js/jquery.min.js?s=' . $ts1 . '" type="text/javascript"></script>'."\n"
            .'<script src="plugins/legacy_browser/js/iehacks.js?s=' . $ts2 . '" type="text/javascript"></script>',
            $args['content'], 1);

        return $args;
    }

    function render_page($args)
    {
        $rcube = rcube::get_instance();
        $skin  = $this->skin();

        if ($skin == 'classic') {
            $minified = file_exists(INSTALL_PATH . '/plugins/legacy_browser/skins/classic/iehacks.min.css') ? '.min' : '';
            $rcube->output->add_header(
                '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/classic/iehacks' . $minified . '.css" />'
            );
        }
        else if ($skin == 'larry') {
            $minified = file_exists(INSTALL_PATH . '/plugins/legacy_browser/skins/larry/iehacks.min.css') ? '.min' : '';
            $rcube->output->add_header(
                '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/larry/iehacks' . $minified . '.css" />'
            );

            if ($rcube->output->browser->ver < 8) {
                $rcube->output->add_header(
                    '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/larry/ie7hacks' . $minified . '.css" />'
                );
            }
        }
    }

    private function skin()
    {
        $rcube = rcube::get_instance();
        $skin  = $rcube->config->get('skin');

        // external skin, find if it inherits from other skin
        if ($skin != 'larry' && $skin != 'classic') {
            $json = @file_get_contents(INSTALL_PATH . "/skins/$skin/meta.json");
            $json = @json_decode($json, true);

            if (!empty($json['extends'])) {
                return $json['extends'];
            }
        }

        return $skin;
    }
}
