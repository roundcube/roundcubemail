<?php

/**
 * Managesieve (Sieve Filters)
 *
 * Plugin that adds a possibility to manage Sieve filters in Thunderbird's style.
 * It's clickable interface which operates on text scripts and communicates
 * with server using managesieve protocol. Adds Filters tab in Settings.
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Configuration (see config.inc.php.dist)
 *
 * Copyright (C) The Roundcube Dev Team
 * Copyright (C) Kolab Systems AG
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
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

class managesieve extends rcube_plugin
{
    public $task = 'mail|settings';

    private $rc;
    private $engine;
    private $ui_initialized    = false;
    private $mail_headers_done = false;

    /**
     * Plugin initialization
     */
    function init()
    {
        $this->rc = rcube::get_instance();

        $this->load_config();

        $allowed_hosts = $this->rc->config->get('managesieve_allowed_hosts');
        if (!empty($allowed_hosts) && !in_array($_SESSION['storage_host'], (array) $allowed_hosts)) {
            return;
        }

        // register actions
        $this->register_action('plugin.managesieve', [$this, 'managesieve_actions']);
        $this->register_action('plugin.managesieve-action', [$this, 'managesieve_actions']);
        $this->register_action('plugin.managesieve-vacation', [$this, 'managesieve_actions']);
        $this->register_action('plugin.managesieve-forward', [$this, 'managesieve_actions']);
        $this->register_action('plugin.managesieve-save', [$this, 'managesieve_save']);
        $this->register_action('plugin.managesieve-saveraw', [$this, 'managesieve_saveraw']);

        if ($this->rc->task == 'settings') {
            $this->add_hook('settings_actions', [$this, 'settings_actions']);
            $this->init_ui();
        }
        else if ($this->rc->task == 'mail') {
            // register message hook
            if ($this->rc->action == 'show') {
                $this->add_hook('message_headers_output', [$this, 'mail_headers']);
            }

            // inject Create Filter popup stuff
            if (empty($this->rc->action) || $this->rc->action == 'show'
                || strpos($this->rc->action, 'plugin.managesieve') === 0
            ) {
                $this->mail_task_handler();
            }
        }
    }

    /**
     * Initializes plugin's UI (localization, js script)
     */
    function init_ui()
    {
        if (!empty($this->ui_initialized)) {
            return;
        }

        // load localization
        $this->add_texts('localization/');

        $sieve_action = strpos($this->rc->action, 'plugin.managesieve') === 0;

        if ($this->rc->task == 'mail' || $sieve_action) {
            $this->include_script('managesieve.js');
        }

        // include styles
        $skin_path = $this->local_skin_path();
        if ($sieve_action || ($this->rc->task == 'settings' && empty($_REQUEST['_framed']))) {
            $this->include_stylesheet("$skin_path/managesieve.css");
        }
        else if ($this->rc->task == 'mail') {
            $this->include_stylesheet("$skin_path/managesieve_mail.css");
        }

        $this->ui_initialized = true;
    }

    /**
     * Adds Filters section in Settings
     */
    function settings_actions($args)
    {
        $vacation_mode = (int) $this->rc->config->get('managesieve_vacation');
        $forward_mode  = (int) $this->rc->config->get('managesieve_forward');

        // register Filters action
        if ($vacation_mode != 2 && $forward_mode != 2) {
            $args['actions'][] = [
                'action' => 'plugin.managesieve',
                'class'  => 'filter',
                'label'  => 'filters',
                'domain' => 'managesieve',
                'title'  => 'filterstitle',
            ];
        }

        // register Vacation action
        if ($vacation_mode > 0) {
            $args['actions'][] = [
                'action' => 'plugin.managesieve-vacation',
                'class'  => 'vacation',
                'label'  => 'vacation',
                'domain' => 'managesieve',
                'title'  => 'vacationtitle',
            ];
        }

        // register Forward action
        if ($forward_mode > 0) {
            $args['actions'][] = [
                'action' => 'plugin.managesieve-forward',
                'class'  => 'forward',
                'label'  => 'forward',
                'domain' => 'managesieve',
                'title'  => 'forwardtitle',
            ];
        }

        return $args;
    }

    /**
     * Add UI elements to the 'mailbox view' and 'show message' UI.
     */
    function mail_task_handler()
    {
        // make sure we're not in ajax request
        if ($this->rc->output->type != 'html') {
            return;
        }

        $vacation_mode = (int) $this->rc->config->get('managesieve_vacation');
        $forward_mode  = (int) $this->rc->config->get('managesieve_forward');

        if ($vacation_mode == 2 || $forward_mode == 2) {
            return;
        }

        // include js script and localization
        $this->init_ui();

        // add 'Create filter' item to message menu
        $this->add_button([
                'command'  => 'managesieve-create',
                'label'    => 'managesieve.filtercreate',
                'type'     => 'link-menuitem',
                'classact' => 'icon filterlink active',
                'class'    => 'icon filterlink disabled',
                'innerclass' => 'icon filterlink',
            ], 'messagemenu'
        );

        // register some labels/messages
        $this->rc->output->add_label('managesieve.newfilter', 'managesieve.usedata',
            'managesieve.nodata', 'managesieve.nextstep', 'save');

        $this->rc->session->remove('managesieve_current');
    }

    /**
     * Get message headers for popup window
     */
    function mail_headers($args)
    {
        // this hook can be executed many times
        if (!empty($this->mail_headers_done)) {
            return $args;
        }

        $this->mail_headers_done = true;

        $headers = $this->parse_headers($args['headers']);

        if ($this->rc->action == 'preview') {
            $this->rc->output->command('parent.set_env', ['sieve_headers' => $headers]);
        }
        else {
            $this->rc->output->set_env('sieve_headers', $headers);
        }

        return $args;
    }

    /**
     * Plugin action handler
     */
    function managesieve_actions()
    {
        $uids = rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST);

        // handle fetching email headers for the new filter form
        if (!empty($uids)) {
            $mailbox = key($uids);
            $message = new rcube_message($uids[$mailbox][0], $mailbox);
            $headers = $this->parse_headers($message->headers);

            $this->rc->output->set_env('sieve_headers', $headers);
            $this->rc->output->command('managesieve_create', true);
            $this->rc->output->send();
        }

        // handle other actions
        $engine_type = $this->rc->action == 'plugin.managesieve-vacation' ? 'vacation' : '';
        $engine_type = $this->rc->action == 'plugin.managesieve-forward' ? 'forward' : $engine_type;
        $engine      = $this->get_engine($engine_type);

        $this->init_ui();

        $engine->actions();
    }

    /**
     * Forms save action handler
     */
    function managesieve_save()
    {
        // load localization
        $this->add_texts('localization/', ['filters', 'managefilters']);

        // include main js script
        if ($this->api->output->type == 'html') {
            $this->include_script('managesieve.js');
        }

        $engine = $this->get_engine();
        $engine->save();
    }

    /**
     * Raw form save action handler
     */
    function managesieve_saveraw()
    {
        $engine = $this->get_engine();

        if (!$this->rc->config->get('managesieve_raw_editor', true)) {
            return;
        }

        // load localization
        $this->add_texts('localization/', ['filters','managefilters']);

        $engine->saveraw();
    }

    /**
     * Initializes engine object
     */
    public function get_engine($type = null)
    {
        if (!$this->engine) {
            // Add include path for internal classes
            $include_path = $this->home . '/lib' . PATH_SEPARATOR;
            $include_path .= ini_get('include_path');
            set_include_path($include_path);

            $class_name   = 'rcube_sieve_' . ($type ?: 'engine');
            $this->engine = new $class_name($this);
        }

        return $this->engine;
    }

    /**
     * Extract mail headers for new filter form
     */
    private function parse_headers($headers)
    {
        $result = [];

        if ($headers->subject) {
            $result[] = ['Subject', rcube_mime::decode_header($headers->subject)];
        }

        // @TODO: List-Id, others?
        foreach (['From', 'To'] as $h) {
            $hl = strtolower($h);
            if (!empty($headers->$hl)) {
                $list = rcube_mime::decode_address_list($headers->$hl);
                foreach ($list as $item) {
                    if (!empty($item['mailto'])) {
                        $result[] = [$h, $item['mailto']];
                    }
                }
            }
        }

        return $result;
    }
}
