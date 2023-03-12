<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Listing of canned responses, and quick insert action handler        |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_responses extends rcmail_action_settings_index
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

        $rcmail->output->set_pagetitle($rcmail->gettext('responses'));
        $rcmail->output->include_script('list.js');
        $rcmail->output->add_label('deleteresponseconfirm');
        $rcmail->output->add_handlers(['responseslist' => [$this, 'responses_list']]);
        $rcmail->output->send('responses');
    }

    /**
     * Create template object 'responseslist'
     *
     * @param array $attrib Object attributes
     *
     * @return string HTML table output
     */
    public static function responses_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $plugin = $rcmail->plugins->exec_hook('responses_list', [
                'list' => $rcmail->get_compose_responses(),
                'cols' => ['name']
        ]);

        $out = self::table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

        $readonly_responses = [];
        foreach ($plugin['list'] as $item) {
            if (!empty($item['static'])) {
                $readonly_responses[] = $item['id'];
            }
        }

        // set client env
        $rcmail->output->add_gui_object('responseslist', $attrib['id']);
        $rcmail->output->set_env('readonly_responses', $readonly_responses);

        return $out;
    }
}
