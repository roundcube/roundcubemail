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
 |   Provide functionality of folder rename                              |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_folder_rename extends rcmail_action_settings_folders
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail  = rcmail::get_instance();
        $name    = trim(rcube_utils::get_input_string('_folder_newname', rcube_utils::INPUT_POST, true));
        $oldname = rcube_utils::get_input_string('_folder_oldname', rcube_utils::INPUT_POST, true);

        if (strlen($name) && strlen($oldname)) {
            $rename = self::rename_folder($oldname, $name);
        }

        if (!empty($rename)) {
            self::update_folder_row($name, $oldname);
        }
        else {
            self::display_server_error('errorsaving');
        }

        $rcmail->output->send();
    }

    public static function rename_folder($oldname, $newname)
    {
        $rcmail    = rcmail::get_instance();
        $storage   = $rcmail->get_storage();

        $plugin = $rcmail->plugins->exec_hook('folder_rename', [
            'oldname' => $oldname, 'newname' => $newname]);

        if (empty($plugin['abort'])) {
            $renamed =  $storage->rename_folder($oldname, $newname);
        }
        else {
            $renamed = $plugin['result'];
        }

        // update per-folder options for modified folder and its subfolders
        if ($renamed) {
            $delimiter  = $storage->get_hierarchy_delimiter();
            $a_threaded = (array) $rcmail->config->get('message_threading', []);
            $oldprefix  = '/^' . preg_quote($oldname . $delimiter, '/') . '/';

            foreach ($a_threaded as $key => $val) {
                if ($key == $oldname) {
                    unset($a_threaded[$key]);
                    $a_threaded[$newname] = $val;
                }
                else if (preg_match($oldprefix, $key)) {
                    unset($a_threaded[$key]);
                    $a_threaded[preg_replace($oldprefix, $newname . $delimiter, $key)] = $val;
                }
            }

            $rcmail->user->save_prefs(['message_threading' => $a_threaded]);

            // #1488692: update session
            if (isset($_SESSION['mbox']) && $_SESSION['mbox'] === $oldname) {
                $_SESSION['mbox'] = $newname;
            }

            return true;
        }

        return false;
    }
}
