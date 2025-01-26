<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display license information about program and enabled plugins       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_about extends rcmail_action
{
    protected static $mode = self::MODE_HTTP;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $rcmail->output->set_pagetitle($rcmail->gettext('about'));

        $rcmail->output->add_handlers([
                'supportlink' => [$this, 'supportlink'],
                'pluginlist'  => [$this, 'plugins_list'],
                'copyright'   => function() {
                    return 'Copyright &copy; 2005-2025, The Roundcube Dev Team';
                },
                'license' => function() {
                    return 'This program is free software; you can redistribute it and/or modify it under the terms '
                        . 'of the <a href="https://www.gnu.org/licenses/gpl.html" target="_blank">GNU General Public License</a> '
                        . 'as published by the Free Software Foundation, either version 3 of the License, '
                        . 'or (at your option) any later version.<br/>'
                        . 'Some <a href="https://roundcube.net/license" target="_blank">exceptions</a> '
                        . 'for skins &amp; plugins apply.';
                },
        ]);

        $rcmail->output->send('about');
    }

    public static function supportlink($attrib)
    {
        $rcmail = rcmail::get_instance();

        if ($url = $rcmail->config->get('support_url')) {
            $label = !empty($attrib['label']) ? $attrib['label'] : 'support';
            $attrib['href'] = $url;

            return html::a($attrib, $rcmail->gettext($label));
        }
    }

    public static function plugins_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmpluginlist';
        }

        $plugins     = array_filter($rcmail->plugins->active_plugins);
        $plugin_info = [];

        foreach ($plugins as $name) {
            if ($info = $rcmail->plugins->get_info($name)) {
                $plugin_info[$name] = $info;
            }
        }

        // load info from required plugins, too
        foreach ($plugin_info as $name => $info) {
            if (!empty($info['require']) && is_array($info['require'])) {
                foreach ($info['require'] as $req_name) {
                    if (!isset($plugin_info[$req_name]) && ($req_info = $rcmail->plugins->get_info($req_name))) {
                        $plugin_info[$req_name] = $req_info;
                    }
                }
            }
        }

        if (empty($plugin_info)) {
            return '';
        }

        ksort($plugin_info, SORT_LOCALE_STRING);

        $table = new html_table($attrib);

        // add table header
        $table->add_header('name', $rcmail->gettext('plugin'));
        $table->add_header('version', $rcmail->gettext('version'));
        $table->add_header('license', $rcmail->gettext('license'));
        $table->add_header('source', $rcmail->gettext('source'));

        foreach ($plugin_info as $name => $data) {
            $uri = !empty($data['src_uri']) ? $data['src_uri'] : ($data['uri'] ?? '');
            if ($uri && stripos($uri, 'http') !== 0) {
                $uri = 'http://' . $uri;
            }

            if ($uri) {
                $uri = html::a([
                        'target' => '_blank',
                        'href'   => rcube::Q($uri)
                    ],
                    rcube::Q($rcmail->gettext('download'))
                );
            }

            $license = isset($data['license']) ? $data['license'] : '';

            if (!empty($data['license_uri'])) {
                $license = html::a([
                        'target' => '_blank',
                        'href' => rcube::Q($data['license_uri'])
                    ],
                    rcube::Q($data['license'])
                );
            }
            else {
                $license = rcube::Q($license);
            }

            $table->add_row();
            $table->add('name', rcube::Q(!empty($data['name']) ? $data['name'] : $name));
            $table->add('version', !empty($data['version']) ? rcube::Q($data['version']) : '');
            $table->add('license', $license);
            $table->add('source', $uri);
        }

        return $table->show();
    }
}
