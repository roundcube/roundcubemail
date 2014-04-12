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
        $ts = filemtime($this->home . '/js/jquery.min.js');
        $args['content'] = preg_replace(
            '|"program/js/jquery\.min\.js\?s=[0-9]+"|',
            '"plugins/legacy_browser/js/jquery.min.js?s=' . $ts . '"',
            $args['content'], 1);

        return $args;
    }

    function render_page($args)
    {
        $rcube = rcube::get_instance();
        $skin  = $this->skin();

        if ($skin == 'classic') {
            $rcube->output->add_header(
                '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/classic/iehacks.css" />'
            );
        }
        else if ($skin == 'larry') {
            if ($rcube->output->browser->ver < 8) {
                $rcube->output->add_header(
                    '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/larry/ie7hacks.css" />'
                );
            }
            else {
                $rcube->output->add_header(
                    '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/larry/iehacks.css" />'
                );
            }

            // fix missing :last-child selectors
            $rcube->output->add_footer(implode("\n", array(
                '<script type="text/javascript">',
                '$(document).ready(function() {',
                '    $(\'ul.treelist ul\').each(function(i,ul) {',
                '        $(\'li:last-child\', ul).css(\'border-bottom\', 0);',
                '    });',
                '});',
                '</script>'
            )));
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
