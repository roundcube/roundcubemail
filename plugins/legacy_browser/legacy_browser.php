<?php

/**
 * Plugin which adds support for legacy browsers (IE 7/8, Firefox < 4)
 *
 * @author Aleksander Machniak <alec@alec.pl>
 * @license GNU GPLv3+
 */
class legacy_browser extends rcube_plugin
{
    public $noajax = true;
    private $rc;

    public function init()
    {
        $this->rc = $rcube = rcube::get_instance();

        if (
            // IE < 9
            ($rcube->output->browser->ie && $rcube->output->browser->ver < 9)
            // Firefox < 4 (Firefox 4 is recognized as 2.0)
            || ($rcube->output->browser->mz && $rcube->output->browser->ver < 2)
        ) {
            $this->add_hook('send_page', array($this, 'send_page'));
            $this->add_hook('render_page', array($this, 'render_page'));
        }
    }

    function send_page($args)
    {
        $p1 = $this->rc->output->asset_url('program/js');
        $p2 = $this->rc->output->asset_url('plugins/legacy_browser/js');

        $assets_dir = $this->rc->config->get('assets_dir');

        $ts1 = filemtime($this->home . '/js/jquery.min.js');
        $ts2 = filemtime($this->home . '/js/iehacks.js');

        if (!$ts1 && $assets_dir) {
            $ts1 = filemtime($assets_dir . '/plugins/legacy_browser/js/jquery.min.js');
        }
        if (!$ts2 && $assets_dir) {
            $ts2 = filemtime($assets_dir . '/plugins/legacy_browser/js/iehacks.js');
        }

        // put iehacks.js after app.js
        if ($this->rc->output->browser->ie) {
            $args['content'] = preg_replace(
                '|(<script src="' . preg_quote($p1, '|') . '/app(\.min)?\.js(\?s=[0-9]+)?" type="text/javascript"></script>)|',
                '\\1<script src="' . $p2 . '/iehacks.js?s=' . $ts2 . '" type="text/javascript"></script>',
                $args['content'], 1, $count);
        }
        else {
            $count = 1;
        }

        // replace jQuery 2.x with 1.x
        $args['content'] = preg_replace(
            '|<script src="' . preg_quote($p1, '|') . '/jquery\.min\.js(\?s=[0-9]+)?" type="text/javascript"></script>|',
            '<script src="' . $p2 . '/jquery.min.js?s=' . $ts1 . '" type="text/javascript"></script>'
            // add iehacks.js if it is IE and it wasn't added yet
            . ($count ? '' : "\n".'<script src="' . $p2 . '/iehacks.js?s=' . $ts2 . '" type="text/javascript"></script>'),
            $args['content'], 1);

        return $args;
    }

    function render_page($args)
    {
        if (!$this->rc->output->browser->ie) {
            return $args;
        }

        $skin  = $this->skin();

        if ($skin == 'classic') {
            $minified = file_exists(INSTALL_PATH . '/plugins/legacy_browser/skins/classic/iehacks.min.css') ? '.min' : '';
            $this->rc->output->add_header(
                '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/classic/iehacks' . $minified . '.css" />'
            );
        }
        else if ($skin == 'larry') {
            $minified = file_exists(INSTALL_PATH . '/plugins/legacy_browser/skins/larry/iehacks.min.css') ? '.min' : '';
            $this->rc->output->add_header(
                '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/larry/iehacks' . $minified . '.css" />'
            );

            if ($this->rc->output->browser->ver < 8) {
                $this->rc->output->add_header(
                    '<link rel="stylesheet" type="text/css" href="plugins/legacy_browser/skins/larry/ie7hacks' . $minified . '.css" />'
                );
            }
        }
    }

    private function skin()
    {
        $skin  = $this->rc->config->get('skin');

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
