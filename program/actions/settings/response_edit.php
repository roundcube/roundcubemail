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
 |   Show edit form for a canned response record                         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_response_edit extends rcmail_action_settings_responses
{
    protected static $mode = self::MODE_HTTP;
    protected static $response;
    protected static $responses;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $title  = $rcmail->gettext($rcmail->action == 'add-response' ? 'addresponse' : 'editresponse');

        self::set_response();

        $rcmail->output->set_pagetitle($title);
        $rcmail->output->set_env('readonly', !empty(self::$response['static']));
        $rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $rcmail->output->send('responseedit');
    }

    /**
     * Set internal data for a requested response item
     *
     * @param array|null Response data
     */
    public static function set_response()
    {
        $rcmail = rcmail::get_instance();

        self::$responses = $rcmail->get_compose_responses();

        // edit-response
        if (($key = rcube_utils::get_input_value('_key', rcube_utils::INPUT_GPC))) {
            foreach (self::$responses as $i => $response) {
                if ($response['key'] == $key) {
                    self::$response = $response;
                    self::$response['index'] = $i;
                    break;
                }
            }
        }

        return self::$response;
    }

    /**
     * Get content of a response editing/adding form
     *
     * @param array $attrib Template object attributes
     *
     * @return string HTML content
     */
    public static function response_form($attrib)
    {
        $rcmail = rcmail::get_instance();

        // Set form tags and hidden fields
        $disabled = !empty(self::$response['static']);
        $key      = isset(self::$response['key']) ? self::$response['key'] : null;
        $hidden   = ['name' => '_key', 'value' => $key];

        list($form_start, $form_end) = self::get_form_tags($attrib, 'save-response', $key, $hidden);
        unset($attrib['form'], $attrib['id']);

        $table = new html_table(['cols' => 2]);

        $name = isset(self::$response['name']) ? self::$response['name'] : '';
        $name_attr = [
            'id'       => 'ffname',
            'size'     => !empty($attrib['size']) ? $attrib['size'] : null,
            'disabled' => $disabled
        ];

        $table->add('title', html::label('ffname', rcube::Q($rcmail->gettext('responsename'))));
        $table->add(null, rcube_output::get_edit_field('name', $name, $name_attr, 'text'));

        $text = isset(self::$response['text']) ? self::$response['text'] : '';
        $text_attr = [
            'id'       => 'fftext',
            'size'     => !empty($attrib['textareacols']) ? $attrib['textareacols'] : null,
            'rows'     => !empty($attrib['textarearows']) ? $attrib['textarearows'] : null,
            'disabled' => $disabled
        ];

        $table->add('title', html::label('fftext', rcube::Q($rcmail->gettext('responsetext'))));
        $table->add(null, rcube_output::get_edit_field('text', $text, $text_attr, 'textarea'));

        // return the complete edit form as table
        return "$form_start\n" . $table->show($attrib) . $form_end;
    }
}
