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

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $title  = $rcmail->gettext($rcmail->action == 'add-response' ? 'addresponse' : 'editresponse');

        if (!empty($args['post'])) {
            self::$response = $args['post'];
        }
        else if ($id = rcube_utils::get_input_string('_id', rcube_utils::INPUT_GP)) {
            self::$response = $rcmail->get_compose_response($id);

            if (!is_array(self::$response)) {
                $rcmail->output->show_message('dberror', 'error');
                $rcmail->output->send('iframe');
            }
        }

        $rcmail->output->set_pagetitle($title);
        $rcmail->output->set_env('readonly', !empty(self::$response['static']));
        $rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $rcmail->output->send('responseedit');
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

        // add some labels to client
        $rcmail->output->add_label('converting', 'editorwarning');

        // Set form tags and hidden fields
        $readonly = !empty(self::$response['static']);
        $is_html  = self::$response['is_html'] ?? false;
        $id       = self::$response['id'] ?? '';
        $hidden   = ['name' => '_id', 'value' => $id];

        list($form_start, $form_end) = self::get_form_tags($attrib, 'save-response', $id, $hidden);
        unset($attrib['form'], $attrib['id']);

        $name_attr = [
            'id'       => 'ffname',
            'size'     => $attrib['size'] ?? null,
            'readonly' => $readonly,
            'required' => true,
        ];

        $text_attr = [
            'id'       => 'fftext',
            'size'     => $attrib['textareacols'] ?? null,
            'rows'     => $attrib['textarearows'] ?? null,
            'readonly' => $readonly,
            'spellcheck'       => true,
            'data-html-editor' => true
        ];

        $chk_attr = [
            'id'       => 'ffis_html',
            'disabled' => $readonly,
            'onclick'  => "return rcmail.command('toggle-editor', {id: 'fftext', html: this.checked}, '', event)"
        ];

        // Add HTML editor script(s)
        self::html_editor('response', 'fftext');

        // Enable TinyMCE editor
        if ($is_html) {
            $text_attr['class']      = 'mce_editor';
            $text_attr['is_escaped'] = true;

            // Correctly handle HTML entities in HTML editor (#1488483)
            self::$response['data'] = htmlspecialchars(self::$response['data'], ENT_NOQUOTES, RCUBE_CHARSET);
        }

        $table = new html_table(['cols' => 2]);

        $table->add('title', html::label('ffname', rcube::Q($rcmail->gettext('responsename'))));
        $table->add(null, rcube_output::get_edit_field('name', self::$response['name'] ?? '', $name_attr, 'text'));

        $table->add('title', html::label('fftext', rcube::Q($rcmail->gettext('responsetext'))));
        $table->add(null, rcube_output::get_edit_field('text', self::$response['data'] ?? '', $text_attr, 'textarea'));

        $table->add('title', html::label('ffis_html', rcube::Q($rcmail->gettext('htmltoggle'))));
        $table->add(null, rcube_output::get_edit_field('is_html', $is_html, $chk_attr, 'checkbox'));

        // return the complete edit form as table
        return "$form_start\n" . $table->show($attrib) . $form_end;
    }
}
