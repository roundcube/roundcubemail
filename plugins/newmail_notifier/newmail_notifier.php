<?php

/**
 * New Mail Notifier plugin
 *
 * Supports two methods of notification:
 * 1. Basic - focus browser window and change favicon
 * 2. Sound - play wav file
 *
 * @version 0.2
 * @author Aleksander Machniak <alec@alec.pl>
 *
 *
 * Copyright (C) 2011, Kolab Systems AG
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class newmail_notifier extends rcube_plugin
{
    public $task = 'mail|settings';

    private $rc;

    /**
     * Plugin initialization
     */
    function init()
    {
        $this->rc = rcmail::get_instance();

        // Preferences hooks
        if ($this->rc->task == 'settings') {
            $this->add_hook('preferences_list', array($this, 'prefs_list'));
            $this->add_hook('preferences_save', array($this, 'prefs_save'));
        }
        else { // if ($this->rc->task == 'mail') {
            $this->add_hook('new_messages', array($this, 'notify'));
            // add script when not in ajax and not in frame
            if (is_a($this->rc->output, 'rcube_template') && empty($_REQUEST['_framed'])) {
                $this->include_script('newmail_notifier.js');
            }
        }
    }

    /**
     * Handler for user preferences form (preferences_list hook)
     */
    function prefs_list($args)
    {
        if ($args['section'] != 'mailbox') {
            return $args;
        }

        // Load configuration
        $this->load_config();

        // Load localization and configuration
        $this->add_texts('localization/');

        // Check that configuration is not disabled
        $dont_override  = (array) $this->rc->config->get('dont_override', array());
        $basic_override = in_array('newmail_notifier_basic', $dont_override);
        $sound_override = in_array('newmail_notifier_sound', $dont_override);

        if (!$basic_override) {
            $field_id = '_newmail_notifier_basic';
            $input    = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
            $args['blocks']['new_message']['options']['newmail_notifier_basic'] = array(
                'title' => html::label($field_id, Q($this->gettext('basic'))),
                'content' => $input->show($this->rc->config->get('newmail_notifier_basic')),
            );
        }

        if (!$sound_override) {
            $field_id = '_newmail_notifier_sound';
            $input    = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
            $args['blocks']['new_message']['options']['newmail_notifier_sound'] = array(
                'title' => html::label($field_id, Q($this->gettext('sound'))),
                'content' => $input->show($this->rc->config->get('newmail_notifier_sound')),
            );
        }

        return $args;
    }

    /**
     * Handler for user preferences save (preferences_save hook)
     */
    function prefs_save($args)
    {
        if ($args['section'] != 'mailbox') {
            return $args;
        }

        // Load configuration
        $this->load_config();

        // Check that configuration is not disabled
        $dont_override  = (array) $this->rc->config->get('dont_override', array());
        $basic_override = in_array('newmail_notifier_basic', $dont_override);
        $sound_override = in_array('newmail_notifier_sound', $dont_override);

        if (!$basic_override) {
            $key = 'newmail_notifier_basic';
            $args['prefs'][$key] = get_input_value('_'.$key, RCUBE_INPUT_POST) ? true : false;
        }
        if (!$sound_override) {
            $key = 'newmail_notifier_sound';
            $args['prefs'][$key] = get_input_value('_'.$key, RCUBE_INPUT_POST) ? true : false;
        }

        return $args;
    }

    /**
     * Handler for new message action (new_messages hook)
     */
    function notify($args)
    {
        // Load configuration
        $this->load_config();

        $basic = $this->rc->config->get('newmail_notifier_basic');
        $sound = $this->rc->config->get('newmail_notifier_sound');

        if ($basic || $sound) {
            $this->rc->output->command('plugin.newmail_notifier',
                array('basic' => $basic, 'sound' => $sound));
        }

        return $args;
    }
}
