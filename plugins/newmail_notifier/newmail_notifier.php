<?php

/**
 * New Mail Notifier plugin
 *
 * Supports two methods of notification:
 * 1. Basic - focus browser window and change favicon
 * 2. Sound - play wav file
 * 3. Desktop - display desktop notification (using webkitNotifications feature,
 *              supported by Chrome and Firefox with 'HTML5 Notifications' plugin)
 *
 * @version 0.3
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
    private $notified;

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
                $this->add_texts('localization/');
                $this->rc->output->add_label('newmail_notifier.title', 'newmail_notifier.body');
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

        if (!empty($_REQUEST['_framed'])) {
            $this->rc->output->add_label('newmail_notifier.title', 'newmail_notifier.testbody',
                'newmail_notifier.desktopunsupported', 'newmail_notifier.desktopenabled', 'newmail_notifier.desktopdisabled');
            $this->include_script('newmail_notifier.js');
        }

        // Check that configuration is not disabled
        $dont_override = (array) $this->rc->config->get('dont_override', array());

        foreach (array('basic', 'desktop', 'sound') as $type) {
            $key = 'newmail_notifier_' . $type;
            if (!in_array($key, $dont_override)) {
                $field_id = '_' . $key;
                $input    = new html_checkbox(array('name' => $field_id, 'id' => $field_id, 'value' => 1));
                $content  = $input->show($this->rc->config->get($key))
                    . ' ' . html::a(array('href' => '#', 'onclick' => 'newmail_notifier_test_'.$type.'()'),
                        $this->gettext('test'));

                $args['blocks']['new_message']['options'][$key] = array(
                    'title' => html::label($field_id, Q($this->gettext($type))),
                    'content' => $content
                );
            }
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
        $dont_override = (array) $this->rc->config->get('dont_override', array());

        foreach (array('basic', 'desktop', 'sound') as $type) {
            $key = 'newmail_notifier_' . $type;
            if (!in_array($key, $dont_override)) {
                $args['prefs'][$key] = get_input_value('_'.$key, RCUBE_INPUT_POST) ? true : false;
            }
        }

        return $args;
    }

    /**
     * Handler for new message action (new_messages hook)
     */
    function notify($args)
    {
        if ($this->notified || !empty($_GET['_refresh'])) {
            return $args;
        }

        $this->notified = true;

        // Load configuration
        $this->load_config();

        $basic   = $this->rc->config->get('newmail_notifier_basic');
        $sound   = $this->rc->config->get('newmail_notifier_sound');
        $desktop = $this->rc->config->get('newmail_notifier_desktop');

        if ($basic || $sound || $desktop) {
            $this->rc->output->command('plugin.newmail_notifier',
                array('basic' => $basic, 'sound' => $sound, 'desktop' => $desktop));
        }

        return $args;
    }
}
