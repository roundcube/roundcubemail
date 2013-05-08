<?php
/**
 * Attachement Reminder
 *
 * A plugin that reminds a user to attach the files
 *
 * @version @package_version@
 * @author Thomas Yu - Sian, Liu
 * @author Aleksander Machniak <machniak@kolabsys.com>
 *
 * Copyright (C) 2013 Thomas Yu - Sian, Liu
 * Copyright (C) 2013, Kolab Systems AG
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

class attachment_reminder extends rcube_plugin
{
    public $task = 'mail|settings';
    public $noajax = true;


    function init()
    {
        $rcmail = rcube::get_instance();

        if ($rcmail->task == 'mail' && $rcmail->action == 'compose') {
            $this->include_script('attachment_reminder.js');
            $this->add_texts('localization/', array('keywords', 'forgotattachment'));
        }

        if ($rcmail->task == 'settings') {
            $dont_override = $rcmail->config->get('dont_override', array());

            if (!in_array('attachment_reminder', $dont_override)) {
                $this->add_hook('preferences_list', array($this, 'prefs_list'));
                $this->add_hook('preferences_save', array($this, 'prefs_save'));
            }
        }
    }

    function prefs_list($args)
    {
        if ($args['section'] == 'compose') {
            $this->add_texts('localization/');
            $reminder = rcube::get_instance()->config->get('attachment_reminder');
            $field_id = 'rcmfd_attachment_reminder';
            $checkbox = new html_checkbox(array('name' => '_attachment_reminder', 'id' => $field_id, 'value' => 1));

            $args['blocks']['main']['options']['attachment_reminder'] = array(
                'title' => html::label($field_id, rcube::Q($this->gettext('reminderoption'))),
                'content' => $checkbox->show($reminder ? 1 : 0),
            );
        }

        return $args;
    }

    function prefs_save($args)
    {
        if ($args['section'] == 'compose') {
            $dont_override = rcube::get_instance()->config->get('dont_override', array());
            if (!in_array('attachment_reminder', $dont_override)) {
                $args['prefs']['attachment_reminder'] = !empty($_POST['_attachment_reminder']);
            }
        }
        return $args;
    }

}
