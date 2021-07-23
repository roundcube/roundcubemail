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
 |   Manage identities of a user account                                 |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_identities extends rcmail_action
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

        $rcmail->output->set_pagetitle($rcmail->gettext('identities'));
        $rcmail->output->include_script('list.js');
        $rcmail->output->set_env('identities_level', (int) $rcmail->config->get('identities_level', 0));
        $rcmail->output->add_label('deleteidentityconfirm');
        $rcmail->output->add_handlers([
                'identitieslist' => [$this, 'identities_list'],
        ]);

        $rcmail->output->send('identities');
    }

    public static function identities_list($attrib)
    {
        $rcmail = rcmail::get_instance();

        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmIdentitiesList';
        }

        // get identities list and define 'mail' column
        $list = $rcmail->user->list_emails();
        foreach ($list as $idx => $row) {
            $list[$idx]['mail'] = trim($row['name'] . ' <' . rcube_utils::idn_to_utf8($row['email']) . '>');
        }

        // get all identities from DB and define list of cols to be displayed
        $plugin = $rcmail->plugins->exec_hook('identities_list', [
                'list' => $list,
                'cols' => ['mail']
        ]);

        // @TODO: use <UL> instead of <TABLE> for identities list
        $out = self::table_output($attrib, $plugin['list'], $plugin['cols'], 'identity_id');

        // set client env
        $rcmail->output->add_gui_object('identitieslist', $attrib['id']);

        return $out;
    }
}
