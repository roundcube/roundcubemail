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

class rcmail_action_settings_responses extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        if (!empty($_POST['_insert'])) {
            $name = trim(rcube_utils::get_input_value('_name', rcube_utils::INPUT_POST));
            $text = trim(rcube_utils::get_input_value('_text', rcube_utils::INPUT_POST, true));

            if (!empty($name) && !empty($text)) {
                $dupes = 0;
                $responses = $rcmail->get_compose_responses(false, true);
                foreach ($responses as $resp) {
                    if (strcasecmp($name, preg_replace('/\s\(\d+\)$/', '', $resp['name'])) == 0) {
                        $dupes++;
                    }
                }
                if ($dupes) {  // require a unique name
                    $name .= ' (' . ++$dupes . ')';
                }

                $response = ['name' => $name, 'text' => $text, 'format' => 'text', 'key' => substr(md5($name), 0, 16)];
                $responses[] = $response;

                if ($rcmail->user->save_prefs(['compose_responses' => $responses])) {
                    $rcmail->output->command('add_response_item', $response);
                    $rcmail->output->command('display_message', $rcmail->gettext('successfullysaved'), 'confirmation');
                }
                else {
                    $rcmail->output->command('display_message', $rcmail->gettext('errorsaving'), 'error');
                }
            }

            $rcmail->output->send();
        }

        $rcmail->output->set_pagetitle($rcmail->gettext('responses'));
        $rcmail->output->include_script('list.js');
        $rcmail->output->add_label('deleteresponseconfirm');

        $rcmail->output->add_handlers([
                'responseslist' => [$this, 'responses_list'],
        ]);

        $rcmail->output->send('responses');
    }

    /**
     * Create template object 'responseslist'
     */
    public static function responses_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $plugin = $rcmail->plugins->exec_hook('responses_list', [
                'list' => $rcmail->get_compose_responses(true),
                'cols' => ['name']
        ]);

        $out = self::table_output($attrib, $plugin['list'], $plugin['cols'], 'key');

        $readonly_responses = [];
        foreach ($plugin['list'] as $item) {
            if (!empty($item['static'])) {
                $readonly_responses[] = $item['key'];
            }
        }

        // set client env
        $rcmail->output->add_gui_object('responseslist', $attrib['id']);
        $rcmail->output->set_env('readonly_responses', $readonly_responses);

        return $out;
    }
}
