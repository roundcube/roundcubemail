<?php
/**
 * Attachment Reminder
 *
 * A plugin that reminds a user to attach the files
 *
 * @author Thomas Yu - Sian, Liu
 * @author Aleksander Machniak <machniak@kolabsys.com>
 *
 * Copyright (C) 2013 Thomas Yu - Sian, Liu
 * Copyright (C) Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>
 */

class attachment_reminder extends rcube_plugin
{
    public $task   = 'mail|settings';
    public $noajax = true;


    /**
     * Plugin initialization
     */
    function init()
    {
        $rcmail = rcube::get_instance();

        if ($rcmail->task == 'mail' && $rcmail->action == 'compose') {
            if ($rcmail->config->get('attachment_reminder')) {
                $this->include_script('attachment_reminder.js');
                $this->add_texts('localization/', ['keywords', 'forgotattachment', 'missingattachment']);
                $rcmail->output->add_label('addattachment', 'send');
            }
        }

        if ($rcmail->task == 'settings') {
            $dont_override = $rcmail->config->get('dont_override', []);

            if (!in_array('attachment_reminder', $dont_override)) {
                $this->add_hook('preferences_list', [$this, 'prefs_list']);
                $this->add_hook('preferences_save', [$this, 'prefs_save']);
            }
        }
    }

    /**
     * 'preferences_list' hook handler
     *
     * @param array $args Hook arguments
     *
     * @return array Hook arguments
     */
    function prefs_list($args)
    {
        if ($args['section'] == 'compose') {
            $this->add_texts('localization/');
            $reminder = rcube::get_instance()->config->get('attachment_reminder');
            $field_id = 'rcmfd_attachment_reminder';
            $checkbox = new html_checkbox(['name' => '_attachment_reminder', 'id' => $field_id, 'value' => 1]);

            $args['blocks']['main']['options']['attachment_reminder'] = [
                'title'   => html::label($field_id, rcube::Q($this->gettext('reminderoption'))),
                'content' => $checkbox->show($reminder ? 1 : 0),
            ];
        }

        return $args;
    }

    /**
     * 'preferences_save' hook handler
     *
     * @param array $args Hook arguments
     *
     * @return array Hook arguments
     */
    function prefs_save($args)
    {
        if ($args['section'] == 'compose') {
            $dont_override = rcube::get_instance()->config->get('dont_override', []);
            if (!in_array('attachment_reminder', $dont_override)) {
                $args['prefs']['attachment_reminder'] = !empty($_POST['_attachment_reminder']);
            }
        }

        return $args;
    }
}
