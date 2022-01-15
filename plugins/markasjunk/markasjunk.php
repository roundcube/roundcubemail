<?php

/**
 * MarkAsJunk
 *
 * A plugin that adds a new button to the mailbox toolbar
 * to mark the selected messages as Junk and move them to the Junk folder
 * or to move messages in the Junk folder to the inbox - moving only the
 * attachment if it is a Spamassassin spam report email
 *
 * @author Philip Weir
 * @author Thomas Bruederli
 *
 * Copyright (C) The Roundcube Dev Team
 * Copyright (C) Philip Weir
 *
 * This program is a Roundcube (https://roundcube.net) plugin.
 * For more information see README.md.
 * For configuration see config.inc.php.dist.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Roundcube. If not, see https://www.gnu.org/licenses/.
 */
class markasjunk extends rcube_plugin
{
    public $task = 'mail';

    private $rcube;
    private $spam_mbox;
    private $ham_mbox;
    private $driver;
    private $flags   = [
        'JUNK'    => 'Junk',
        'NONJUNK' => 'NonJunk'
    ];


    /**
     * Plugin initialization
     */
    public function init()
    {
        $this->register_action('plugin.markasjunk.junk', [$this, 'mark_message']);
        $this->register_action('plugin.markasjunk.not_junk', [$this, 'mark_message']);

        $this->rcube = rcube::get_instance();
        $this->load_config();
        $this->_load_host_config();

        // Host exceptions
        $hosts = $this->rcube->config->get('markasjunk_allowed_hosts');
        if (!empty($hosts) && !in_array($_SESSION['storage_host'], (array) $hosts)) {
            return;
        }

        $this->ham_mbox  = $this->rcube->config->get('markasjunk_ham_mbox', 'INBOX');
        $this->spam_mbox = $this->rcube->config->get('markasjunk_spam_mbox', $this->rcube->config->get('junk_mbox'));
        $toolbar         = $this->rcube->config->get('markasjunk_toolbar', true);
        $this->_init_flags();

        if ($this->rcube->action == '' || $this->rcube->action == 'show') {
            $this->include_script('markasjunk.js');
            $this->add_texts('localization', true);
            $this->include_stylesheet($this->local_skin_path() . '/markasjunk.css');

            if ($toolbar) {
                // add the buttons to the main toolbar
                $this->add_button([
                        'command'    => 'plugin.markasjunk.junk',
                        'type'       => 'link',
                        'class'      => 'button buttonPas junk disabled',
                        'classact'   => 'button junk',
                        'classsel'   => 'button junk pressed',
                        'title'      => 'markasjunk.buttonjunk',
                        'innerclass' => 'inner',
                        'label'      => 'junk'
                    ], 'toolbar');

                $this->add_button([
                        'command'    => 'plugin.markasjunk.not_junk',
                        'type'       => 'link',
                        'class'      => 'button buttonPas notjunk disabled',
                        'classact'   => 'button notjunk',
                        'classsel'   => 'button notjunk pressed',
                        'title'      => 'markasjunk.buttonnotjunk',
                        'innerclass' => 'inner',
                        'label'      => 'markasjunk.notjunk'
                    ], 'toolbar');
            }
            else {
                // add the buttons to the mark message menu
                $this->add_button([
                        'command'    => 'plugin.markasjunk.junk',
                        'type'       => 'link-menuitem',
                        'label'      => 'markasjunk.asjunk',
                        'id'         => 'markasjunk',
                        'class'      => 'icon junk disabled',
                        'classact'   => 'icon junk active',
                        'innerclass' => 'icon junk'
                    ], 'markmenu');

                $this->add_button([
                        'command'    => 'plugin.markasjunk.not_junk',
                        'type'       => 'link-menuitem',
                        'label'      => 'markasjunk.asnotjunk',
                        'id'         => 'markasnotjunk',
                        'class'      => 'icon notjunk disabled',
                        'classact'   => 'icon notjunk active',
                        'innerclass' => 'icon notjunk'
                    ], 'markmenu');
            }

            // add markasjunk folder settings to the env for JS
            $this->rcube->output->set_env('markasjunk_ham_mailbox', $this->ham_mbox);
            $this->rcube->output->set_env('markasjunk_spam_mailbox', $this->spam_mbox);
            $this->rcube->output->set_env('markasjunk_move_spam', $this->rcube->config->get('markasjunk_move_spam', false));
            $this->rcube->output->set_env('markasjunk_move_ham', $this->rcube->config->get('markasjunk_move_ham', false));
            $this->rcube->output->set_env('markasjunk_permanently_remove', $this->rcube->config->get('markasjunk_permanently_remove', false));
            $this->rcube->output->set_env('markasjunk_spam_only', $this->rcube->config->get('markasjunk_spam_only', false));
        }

        // init learning driver
        $this->_init_driver();
    }

    public function mark_message()
    {
        $this->add_texts('localization');

        $is_spam    = $this->rcube->action == 'plugin.markasjunk.junk';
        $uids       = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
        $mbox_name  = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST);
        $messageset = rcmail::get_uids($uids, $mbox_name, $multifolder);
        $dest_mbox  = $is_spam ? $this->spam_mbox : $this->ham_mbox;

        // special case when select all is used, uid is '*', and not in multi folder mode and we are using a driver
        // rcmail::get_uids does not handle this
        if ($uids == '*' && !$multifolder && is_object($this->driver)) {
            $storage      = $this->rcube->get_storage();
            $result_index = $storage->index($mbox_name);
            $messageset   = [$mbox_name => $result_index->get()];
        }

        $result = $is_spam ? $this->_spam($messageset, $dest_mbox) : $this->_ham($messageset, $dest_mbox);
        if ($result) {
            if ($dest_mbox && ($mbox_name !== $dest_mbox || $multifolder)) {
                $this->rcube->output->command('markasjunk_move', $dest_mbox, $this->_messageset_to_uids($messageset, $multifolder));
            }
            else {
                $this->rcube->output->command('command', 'list', $mbox_name);
            }

            $this->rcube->output->command('display_message', $this->gettext($is_spam ? 'reportedasjunk' : 'reportedasnotjunk'), 'confirmation');
        }

        $this->rcube->output->send();
    }

    public function set_flags($p)
    {
        if (!empty($p['message_flags'])) {
            $p['message_flags'] = array_merge((array) $p['message_flags'], $this->flags);
        }
        else {
            $p['message_flags'] = $this->flags;
        }

        return $p;
    }

    private function _spam(&$messageset, $dest_mbox = null)
    {
        $storage = $this->rcube->get_storage();
        $result  = true;

        foreach ($messageset as $source_mbox => &$uids) {
            $storage->set_folder($source_mbox);

            $result = $this->_call_driver('spam', $uids, $source_mbox, $dest_mbox);

            // abort function of the driver says so
            if (!$result) {
                break;
            }

            if ($this->rcube->config->get('markasjunk_read_spam', false)) {
                $storage->set_flag($uids, 'SEEN', $source_mbox);
            }

            if (array_key_exists('JUNK', $this->flags)) {
                $storage->set_flag($uids, 'JUNK', $source_mbox);
            }

            if (array_key_exists('NONJUNK', $this->flags)) {
                $storage->unset_flag($uids, 'NONJUNK', $source_mbox);
            }
        }

        return $result;
    }

    private function _ham(&$messageset, $dest_mbox = null)
    {
        $storage = $this->rcube->get_storage();
        $result  = true;

        foreach ($messageset as $source_mbox => &$uids) {
            $storage->set_folder($source_mbox);

            $result = $this->_call_driver('ham', $uids, $source_mbox, $dest_mbox);

            // abort function of the driver says so
            if (!$result) {
                break;
            }

            if ($this->rcube->config->get('markasjunk_unread_ham', false)) {
                $storage->unset_flag($uids, 'SEEN', $source_mbox);
            }

            if (array_key_exists('JUNK', $this->flags)) {
                $storage->unset_flag($uids, 'JUNK', $source_mbox);
            }

            if (array_key_exists('NONJUNK', $this->flags)) {
                $storage->set_flag($uids, 'NONJUNK', $source_mbox);
            }
        }

        return $result;
    }

    private function _call_driver($action, &$uids = null, $source_mbox = null, $dest_mbox = null)
    {
        // already initialized
        if (!is_object($this->driver)) {
            return true;
        }

        if ($action == 'spam') {
            $this->driver->spam($uids, $source_mbox, $dest_mbox);
        }
        elseif ($action == 'ham') {
            $this->driver->ham($uids, $source_mbox, $dest_mbox);
        }

        return empty($this->driver->is_error);
    }

    private function _messageset_to_uids($messageset, $multifolder)
    {
        $a_uids = [];

        foreach ($messageset as $mbox => $uids) {
            if (is_array($uids)) {
                foreach ($uids as $uid) {
                    $a_uids[] = $multifolder ? $uid . '-' . $mbox : $uid;
                }
            }
        }

        return $a_uids;
    }

    private function _load_host_config()
    {
        $configs = $this->rcube->config->get('markasjunk_host_config');
        if (empty($configs) || !array_key_exists($_SESSION['storage_host'], (array) $configs)) {
            return;
        }

        $file = $configs[$_SESSION['storage_host']];
        $this->load_config($file);
    }

    private function _init_flags()
    {
        $spam_flag = $this->rcube->config->get('markasjunk_spam_flag');
        $ham_flag  = $this->rcube->config->get('markasjunk_ham_flag');

        if ($spam_flag === false) {
            unset($this->flags['JUNK']);
        }
        elseif (!empty($spam_flag)) {
            $this->flags['JUNK'] = $spam_flag;
        }

        if ($ham_flag === false) {
            unset($this->flags['NONJUNK']);
        }
        elseif (!empty($ham_flag)) {
            $this->flags['NONJUNK'] = $ham_flag;
        }

        if (count($this->flags) > 0) {
            // register the ham/spam flags with the core
            $this->add_hook('storage_init', [$this, 'set_flags']);
        }
    }

    private function _init_driver()
    {
        $driver_name = $this->rcube->config->get('markasjunk_learning_driver');

        if (empty($driver_name)) {
            return;
        }

        $driver = $this->home . "/drivers/$driver_name.php";
        $class  = "markasjunk_$driver_name";

        if (!is_readable($driver)) {
            rcube::raise_error([
                    'code' => 600,
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => "markasjunk plugin: Unable to open driver file $driver"
                ], true, false
            );
        }

        include_once $driver;

        if (!class_exists($class, false) || !method_exists($class, 'spam') || !method_exists($class, 'ham')) {
            rcube::raise_error([
                    'code' => 600,
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'message' => "markasjunk plugin: Broken driver: $driver"
                ], true, false
            );
        }

        // call the relevant function from the driver
        $this->driver = new $class();

        // method_exists check here for backwards compatibility
        if (method_exists($this->driver, 'init')) {
            $this->driver->init();
        }
    }
}
